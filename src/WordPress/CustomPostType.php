<?php

namespace WordPress;

session_start();

class CustomPostType{
  /**
   * The name of the post type.
   * @var string
   */
  public $post_type_name;
  
  /**
   * A list of user-specific options for the post type.
   * @var array
   */
  public $post_type_args;
  
  /**
   * Sets default values, registers the passed post type, and
   * listens for when the post is saved.
   *
   * @param string $name The name of the desired post type.
   * @param array @post_type_args Override the options.
   */
  function __construct($name, $post_type_args = array())
  {
    if (!isset($_SESSION["taxonomy_data"]))
    {
      $_SESSION['taxonomy_data'] = array();
    }
    
    $this->post_type_name = strtolower($name);
    $this->post_type_args = (array)$post_type_args;
    
    // First step, register that new post type
    $this->init(array(&$this, "register_post_type"));
    $this->save_post();
  }
  
  /**
   * Helper method, that attaches a passed function to the 'init' WP action
   * @param function $cb Passed callback function.
   */
  function init($cb)
  {
    add_action("init", $cb);
  }
  
  /**
   * Helper method, that attaches a passed function to the 'admin_init' WP action
   * @param function $cb Passed callback function.
   */
  function admin_init($cb)
  {
    add_action("admin_init", $cb);
  }
  
  /**
   * Registers a new post type in the WP db.
   */
  function register_post_type()
  {
    $n = ucwords($this->post_type_name);
    $args = array(
      "label" => $n . 's',
      "singular_name" => $n,
      "public" => true,
      "publicly_queryable" => true,
      "query_var" => true,
      #"menu_icon" => get_stylesheet_directory_uri() . "/article16.png",
      "rewrite" => true,
      "capability_type" => "post",
      "hierarchical" => false,
      "menu_position" => null,
      "supports" => array("title", "editor", "thumbnail"),
      'has_archive' => true
    );
    
    // Take user provided options, and override the defaults.
    $args = array_merge($args, $this->post_type_args);
    
    register_post_type($this->post_type_name, $args);
  }
  
  /**
   * Registers a new taxonomy, associated with the instantiated post type.
   *
   * @param string $taxonomy_name The name of the desired taxonomy
   * @param string $plural The plural form of the taxonomy name. (Optional)
   * @param array $options A list of overrides
   */
  function add_taxonomy($taxonomy_name, $plural = '', $options = array())
  {
    // Create local reference so we can pass it to the init cb.
    $post_type_name = $this->post_type_name;
    
    // If no plural form of the taxonomy was provided, do a crappy fix. :)
    if (empty($plural))
    {
      $plural = $taxonomy_name . 's';
    }
    
    // Taxonomies need to be lowercase, but displaying them will look better this way...
    $taxonomy_name = ucwords($taxonomy_name);
    
    // At WordPress' init, register the taxonomy
    $this->init(function() use($taxonomy_name, $plural, $post_type_name, $options)
    {
      // Override defaults with user provided options
      $options = array_merge(
        array(
          "hierarchical" => false,
          "label" => $taxonomy_name,
          "singular_label" => $plural,
          "show_ui" => true,
          "query_var" => true,
          "rewrite" => array("slug" => strtolower($taxonomy_name))
        ),
        $options
        );
        
        // name of taxonomy, associated post type, options
        register_taxonomy(strtolower($taxonomy_name), $post_type_name, $options);
    });
    
  }
  
  /**
   * Creates a new custom meta box in the New 'post_type' page.
   *
   * @param string $title
   * @param array $form_fields Associated array that contains the label of the input, and the desired input type. 'Title' => 'text'
   */
  function add_meta_box($title, $form_fields = array())
  {
    $post_type_name = $this->post_type_name;
      
    // end update_edit_form
    add_action('post_edit_form_tag', function()
    {
      echo ' enctype="multipart/form-data"';
    });
      
    // At WordPress' admin_init action, add any applicable metaboxes.
    $this->admin_init(function() use($title, $form_fields, $post_type_name)
    {
      //global $post;
      add_meta_box(
      	strtolower(str_replace(' ', '_', $title)), // id
      	$title, // title
      	array( &$this, 'render_metabox_content'), // // function that displays the form fields
      	$post_type_name, // associated post type
      	'normal', // location/context. normal, side, etc.
      	'default', // priority level
      	array($form_fields) // optional passed arguments.
      ); // end add_meta_box
    });
  }
  /**
   * When a post saved/updated in the database, this methods updates the meta box params in the db as well.
   */
  function save_post()
  {
    add_action('save_post', function()
    {
      // Only do the following if we physically submit the form,
      // and now when autosave occurs.
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
      
      global $post;
      
      if ($_POST && !wp_verify_nonce($_POST['cpt_nonce'], plugin_basename(__FILE__)))
      {
        return;
      }
      
      // Get all the form fields that were saved in the session,
      // and update their values in the db.
      if (isset($_SESSION['taxonomy_data']))
      {
        foreach ($_SESSION['taxonomy_data'] as $form_name)
        {
          if (!empty($_FILES[$form_name]) )
          {
            if ( !empty($_FILES[$form_name]['tmp_name']) )
            {
              $upload = wp_upload_bits($_FILES[$form_name]['name'], null, file_get_contents($_FILES[$form_name]['tmp_name']));
                
              if (isset($upload['error']) && $upload['error'] != 0)
              {
                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
              }
              else
              {
                update_post_meta($post->ID, $form_name, $upload['url']);
              }
            }
          }
          else
          {
            // Make better. Have to do this, because I can't figure
            // out a better way to deal with checkboxes. If deselected,
            // they won't be represented here, but I still need to
            // update the value to false to blank in the table. Hmm...
            if (!isset($_POST[$form_name])) $_POST[$form_name] = '';
            if (isset($post->ID) )
            {
              update_post_meta($post->ID, $form_name, $_POST[$form_name]);
            }
          }
        }
        $_SESSION['taxonomy_data'] = array();
      }
    });
  }
  
  function render_metabox_content($post, $data)
  {	
    global $post;
  	wp_nonce_field(plugin_basename(__FILE__), 'cpt_nonce');

  	// List of all the specified form fields
  	$inputs = $data['args'][0];

  	// Get the saved field values
  	$meta = get_post_custom($post->ID);
    echo '<span class="fmcpt_form_container clearfix">';
  	//print_r($inputs);
  	
  	foreach($inputs as $input)
  	{
  	  
  		foreach($input as $key => $value)
  		{
  			$i[$key] = $value;
  			$label = $i['label'];
  			$slug = $data['id'] . '_' . strtolower(str_replace(' ', '_', $label));
  			$desc = $i['description'];
  			//$id = $i['id'];
  			$type = $i['type'];
  		}

  		switch($type)
  		{
        case 'date' :
  			case 'text' :
      	case 'tel' :
      	case 'email' :
  				echo '<p>';
  				echo '  <label for="'.esc_attr($slug).'">'.esc_attr($label).'</label>';
  				echo '  <input type="' . $type . '" name="' . esc_attr( $slug ) . '" id="' . esc_attr( $slug ) . '" value="' . esc_attr( $meta[$slug][0] ) . '" class="regular-text" size="30" /><br />' . '<span class="description">' . $desc . '</span><br />';
  				echo '</p>';
  				break;
  			case 'textarea' :
  				echo '<p>';
  				echo '  <label for="'.esc_attr($slug).'">'.esc_attr($label).'</label>';
  				echo '  <textarea type="' . $type . '" name="' . esc_attr( $slug ) . '" id="' . esc_attr( $slug ) . '" class="regular-text" size="30">'.esc_attr( $meta[$slug][0] ).'</textarea><br />' . '<span class="description">' . $desc . '</span><br />';
  				echo '</p>';
  				break;
  			case 'file' :
  				echo '<p class="file-uploader">';
  				echo '  <label for="'.esc_attr($slug).'_button" class="selectit">'.esc_attr($label).'</label>';
  				echo '  <input type="text" id="'.esc_attr($slug).'" name="'.esc_attr($slug).'" value="'.esc_attr( $meta[$slug][0] ).'" class="file-upload-text" />';
  				echo '  <input name="'.esc_attr($slug).'_button" class="button" id="'.esc_attr($slug).'_button" type="button" value="Upload Image" />';
  				echo '</p>';
  				break;
  			default :
  				echo '';
  				break;
  		}
  		array_push($_SESSION['taxonomy_data'], $slug );
  	}
  	
  	echo '</span>';          
  }
  
}