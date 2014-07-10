<?php

namespace WordPress;

/**
 * Class: AddMenuPage
 * 
 * Use this to quicly define admin pages in WordPress.
 * By simply passing a few arrays we can create a simple sub-menu-page 
 * 
 * @author Frank McCoy <frankmccoy.d@gmail.com>
 * @version 0.1
 * @package WordPress\AddMenuPage
 */
class AddMenuPage
{
	/**
	 * This holds the options array.
	 */
	private $options;

	/**
	 * The Name of the page we are defining.
	 */
	public $page_name;

	/**
	 * Page Args
	 * 
	 * This holds an array of values used to generate the page.
	 * @example 	array( parent_slug' => 'tools.php' )
	 */
	public $page_args = array();

	/**
	 * Setting Args
	 * 
	 * This holds an array of values used to generate settings.
	 * @example = $settings = array()
	 * @todo Not currently implemented.
	 */
	public $setting_args = array();

	/**
	 * Section Args
	 * 
	 * This holds an array of values used to generate sections.
	 * @example = $sections = array( array( 'title' => 'Section 1' ) )
	 */
	public $section_args = array();

	/**
	 * Field Args
	 * 
	 * This holds an array of values used to generate fields.
	 * @example 
	 		$fields = array(
	 			array(
	 				'title' => 'Field 1',
	 				'type' => 'text',
	 				'section' => 'Section 1'
	 			)
	 		)
	 */
	public $field_args = array();

	public function __construct($name, $args = array(), $section_args = array() )
	{
		$this->page_name = $name;
		if( !empty($args) ) {
			$this->page_args = $args;
		}

		if( !empty($args) ) {
			$this->section_args = $args;
		}
		$this->adminMenu(array(&$this, 'registerSubMenuPage') );
		$this->adminInit(array(&$this, 'registerSetting') );
		$this->adminInit(array(&$this, 'addSettingsSection' ) ); 
		$this->adminInit(array(&$this, 'addSettingsField' ) ); 
	}

	public function adminMenu( $cb )
	{

		add_action( 'admin_menu', $cb );
	}

	public function adminInit( $cb )
	{

		add_action( 'admin_init', $cb );
	}


	/**
	 * Define Submenu Page
	 * 
	 * This creates and registers the submenu page generated from $this->page_args
	 * 
	 * @example 	array( parent_slug' => 'tools.php' )
	 * @uses [wp]add_menu_page()
	 */
	public function registerSubMenuPage( $args = array() )
	{
		$args = $this->page_args;

		$defaults = array(
			'parent_slug' => 'options-general.php',
			'page_title' => $this->page_name,
			'menu_title' => $this->page_name,
			'capability' => 'manage_options',
			'menu_slug' => strtolower(str_replace( " ", "-", $this->page_name ) ),
			'function' => array(&$this, 'displaySubMenuPage' )
		);

		if( ! empty( $args) ){
			$settings = array_merge( $defaults, $args );
		} else {
			$settings = $defaults;
		}

		add_submenu_page(
			$settings['parent_slug'],
			$settings['page_title'],
			$settings['menu_title'],
			$settings['capability'],
			$settings['menu_slug'],
			$settings['function']
		);
	}

	/**
	 * Render the homepage
	 * 
	 * This is what generates the view for defined page.
	 * 
	 * @uses [wp]get_option()
	 * @uses [wp]settings_fields()
	 * @uses [wp]do_settings_sections()
	 * @uses [wp]submit_button()
	 * @uses [wp]get_admin_page_title()
	 */
	public function displaySubMenuPage()
	{	
		$option_name = strtolower(str_replace( " ", "_", $this->page_name ) ) . '_options';
		$page_name = strtolower(str_replace( " ", "-", $this->page_name ) );
		$this->options = get_option( $option_name );
		?>

		<div class="wrap">
		    <h2><?php echo get_admin_page_title(); ?></h2>           
		    <form method="post" action="options.php">
		    <?php
		        // This prints out all hidden setting fields
		        settings_fields( $option_name ); 
		        do_settings_sections( $page_name );
		        submit_button(); 
		    ?>
		    </form>
		</div>

		<?php
	}

	/**
	 * Register Settings
	 * 
	 * Registers the settings generated from $this->settings_args
	 * @todo fix the sanitize_callback feature.
	 * @uses [wp]register_setting()
	 */
	public function registerSetting()
	{
		// Get the user defined settings.
		$args = $this->setting_args;

		// Establish default settings.
		$defaults = array(
			'option_group' => strtolower(str_replace( " ", "_", $this->page_name ) ) . '_options',
			'option_name' => strtolower(str_replace( " ", "_", $this->page_name ) ) . '_options',
			'sanitize_callback' => ''
		);

		// Override default from settings_
		if( ! empty( $args) ){
			$settings = array_merge( $defaults, $args );
		} else {
			$settings = $defaults;
		}

		// These are passed to the callback.
		$fields = $this->field_args;
		$input = $this->options;

		register_setting(
			$settings['option_group'],
			$settings['option_name'],
			$settings['sanitize_callback']
			/*
			function() use ($fields, $input){
				$new_input = array();

				foreach( $fields as $field ){
					$option = strtolower(str_replace( " ", "_", $this->page_name ) ) .'_'. $field['title'] .'_field';
					if( isset( $input[trim($option)] ) ) {
						$new_input[$option] = sanitize_text_field( $input[$option] );
					}
				}
				return $new_input;
				*/

				/*

				if( isset( $input['id_number'] ) )
					$new_input['id_number'] = absint( $input['id_number'] );

				if( isset( $input['title'] ) )
					$new_input['title'] = sanitize_text_field( $input['title'] );

				return $new_input;

				*/
				/*

			}
			*/
		);
	}

	/**
	 * Set Section Args
	 * 
	 * Takes user input and saves it to $this->section_args
	 * 
	 * @param array $sections defines $section_args
	 */
	public function setSections($sections)
	{
		$this->section_args = $sections;
		return $this->section_args;
	}

	/**
	 * Add Settings Section
	 * 
	 * Registers the sections generated from $this->section_args
	 *  
	 * @uses [wp]add_settings_section
	 */
	public function addSettingsSection()
	{
		$sections = $this->section_args;

		foreach( $sections as $section ){
			$section_id = strtolower(str_replace( " ", "_", $section['title'] ) );
			$section_title = $section['title'];

			$settings = array(
				'id' => strtolower(str_replace( " ", "_", $this->page_name ) ) .'_'.$section_id.'_section',
				'title' => $section_title,
				'callback' => array($this, 'displaySettingsSection'),
				'page' => strtolower(str_replace( " ", "-", $this->page_name ) )
			);

			add_settings_section(
				$settings['id'],
				$settings['title'],
				$settings['callback'],
				$settings['page']
			);
		}
	}

	/**
	 * Display Section
	 * 
	 * Renders section display for $this->addSettingsSection() callback.
	 * 
	 * @todo Add ability to define section description here.
	 */
	public function displaySettingsSection()
	{

		//echo "something";
	}

	/**
	 * Set Field Args
	 * 
	 * Takes user input and saves it to $this->field_args
	 * 
	 * @param array $fields defines $field_args
	 */
	public function setFields($fields)
	{
		$this->field_args = $fields;
		return $this->field_args;
	}

	/**
	 * Add Settings Field
	 * 
	 * Registers the fields generated from $this->field_args
	 *  
	 * @uses [wp]add_settings_field
	 * @todo Replace closure with $this->displaySettingsField() and designate field types. 
	 */
	public function addSettingsField()
	{
		$fields = $this->field_args;

		foreach( $fields as $field ){

			$field_id = strtolower(str_replace( " ", "_", $field['title'] ) );
			$field_title = $field['title'];
			$section_id = strtolower(str_replace( " ", "_", $field['section'] ) );
			$section = strtolower(str_replace( " ", "_", $this->page_name ) ) .'_'. $section_id .'_section';
			$args = isset( $field['args'] ) ? $field['args'] : '';

			$settings = array(
				'id' => strtolower(str_replace( " ", "_", $this->page_name ) ) .'_'. $field_id .'_field',
				'title' => $field_title,
				'callback' => array($this, 'displaySettingsField'),
				'page' => strtolower(str_replace( " ", "-", $this->page_name ) ),
				'section' => $section,
				'args' => $args
			);

			add_settings_field(
				$settings['id'],
				$settings['title'],
				//$settings['callback'],
				function() use( $settings ){

					$base = strtolower(str_replace( " ", "_", $this->page_name ) ) . '_options';
					$id = $settings['id'];
					$name = $settings['id'];
					$allopts = $this->options;
					$sample = $allopts['\''.$name.'\''];


					$value = isset( $sample ) ? esc_attr( $sample ) : '';

					echo '<input type="text" id="'.$name.'" name="'. $base. '[\''.trim($name).'\']" value="'.$value.'" />';
				},
				$settings['page'],
				$settings['section'],
				$settings['args']
			);
		}
	}

	/**
	 * Display Section
	 * 
	 * Renders section display for $this->addSettingsField() callback.
	 * 
	 * @todo Add ability to define field types here.
	 * @todo This currently is being overridden from $this->addSettingsField()
	 */
	public function displaySettingsField()
	{
		//$fields = $this->field_args;

		printf(
		    '<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
		    isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
		);
		$options = $this->options;
		print_r($options);
	}
}