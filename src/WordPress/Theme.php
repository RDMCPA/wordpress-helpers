<?php

namespace WordPress;

/**
 * Wordpress Theme Class
 *
 * @author Frank McCoy <frankmccoy.d@gmail.com>
 * @version 0.1
 */
class Theme
{
    public $theme_name;
    public $scripts;

    public function __construct($name, $setup = array() )
    {
        $this->theme_name = $name;
        $this->themeSetup($setup);
        $this->themeInit();

    }
    /**
     * Setup the Theme here
     */
    public function themeSetup($setup = array() )
    {

        if( ! empty( $setup ) and $setup != array() ) :

            // Theme Support
            if ( array_key_exists('theme_support', $setup ) and $setup['theme_support'] != "" ) {

                    $supports = $setup['theme_support'];

                    foreach ($supports as $feature => $value) {

                        if ( is_array( $value ) and $value != "" ) {
                            add_theme_support( $feature, $value );
                        } else {
                            add_theme_support( $feature );
                        }

                    }

            }

            register_nav_menu( 'default-menu', 'Default Menu' );

            // Nav Menus
            if ( array_key_exists('nav_menus', $setup ) and $setup['nav_menus'] != "" ) {

                $navmenus = $setup['nav_menus'];

                foreach ($navmenus as $menu) {
                    register_nav_menus( $menu );
                }

            }

        endif;

        add_action( 'after_setup_theme', array( $this, 'themeSetup') );
    }

    public function themeInit()
    {

    }

    /**
     * Helper: wp_enqueue_scripts()
     *
     */
    public function wpEnqueueScripts()
    {
        add_action('wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
    }

    public function addThemeSupport($cb = '')
    {
        add_theme_support( $cb );
    }

    /**
     * Add scripts to the front-end
     *
     */
    public function enqueueScripts()
    {
        $scripts = $this->scripts;
        

        foreach ($scripts as $script) {

            // Vars
            $method = isset($script['method']) ? $script['method'] : 'enqueue';
            $location = isset($script['location']) ? $script['location'] : '';
            $deps = isset($script['dependencies']) ? $script['dependencies'] : array();
            $vers = isset($script['version']) ? $script['version'] : false;
            $admin = isset($script['admin']) ? $script['admin'] : false;

            // Check Type: Style
            if ($script['type'] == 'style') {

                // Check Method
                if ($method == 'register') {
                    wp_register_style($script['name'], $location, $deps, $vers );
                } else {
                    wp_enqueue_style($script['name'], $location, $deps, $vers );
                }

            }
            // Check Type: Script
            else {

                // Check Method
                if ($method == 'register') {
                    wp_register_script($script['name'], $location, $deps, $vers );
                } else {
                    wp_enqueue_script( $script['name'], $location, $deps, $vers );
                }
            }

            //$this->wpEnqueueScripts($load);
        }

        //return $this->scripts;
    }

    public function addWidgets($widgets)
    {
        //print_r($widgets);
        foreach ($widgets as $widget) {
            register_sidebar( $widget );
        }
    }

    public function loadScripts($load){
        $this->addScripts($load);
        $this->wpEnqueueScripts();
    }

    public function addScripts($scripts){
        $this->scripts = $scripts;
        return $this->scripts;
    }

}