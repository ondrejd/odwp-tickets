<?php
/**
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-tickets for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-tickets
 * @since 0.1.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'Tickets_Plugin' ) ) :

/**
 * Main class.
 * 
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @since 0.1.0
 */
class Tickets_Plugin {

    /**
     * @const string
     * @since 0.1.0
     */
    const SETTINGS_KEY = TICKETS_SLUG . '_settings';

    /**
     * @const string
     * @since 0.1.0
     */
    const TABLE_NAME = TICKETS_SLUG;

    /**
     * @var array $admin_screens Array with admin screens.
     * @since 0.1.0
     */
    public static $admin_screens = array();

    /**
     * @var string
     * @since 0.1.0
     */
    public static $options_page_hook;

    /**
     * @internal Activates the plugin.
     * @return void
     * @since 0.1.0
     */
    public static function activate() {
        //...
    }

    /**
     * @internal Deactivates the plugin.
     * @return void
     * @since 0.1.0
     */
    public static function deactivate() {
        //...
    }

    /**
     * @return array Default values for settings of the plugin.
     * @since 0.1.0
     */
    public static function get_default_options() {
        return array(
            'show_advanced_options' => true,
            'generate_full_plugin' => false,
        );
    }

    /**
     * @return array Settings of the plugin.
     * @since 0.1.0
     */
    public static function get_options() {
        $defaults = self::get_default_options();
        $options = get_option( self::SETTINGS_KEY, array() );
        $update = false;

        // Fill defaults for the options that are not set yet
        foreach( $defaults as $key => $val ) {
            if( ! array_key_exists( $key, $options ) ) {
                $options[$key] = $val;
                $update = true;
            }
        }

        // Updates options if needed
        if( $update === true) {
            update_option( self::SETTINGS_KEY, $options );
        }

        return $options;
    }

    /**
     * Returns value of option with given key.
     * @param string $key Option's key.
     * @param mixed $default Option's default value.
     * @return mixed Option's value.
     * @since 0.1.0
     */
    public static function get_option( $key, $default = null ) {
        $options = self::get_options();

        if( array_key_exists( $key, $options ) ) {
            return $options[$key];
        }

        return $default;
    }

    /**
     * Initializes the plugin.
     * @return void
     * @since 0.1.0
     */
    public static function initialize() {
        register_activation_hook( TICKETS_FILE, [__CLASS__, 'activate'] );
        register_deactivation_hook( TICKETS_FILE, [__CLASS__, 'deactivate'] );
        register_uninstall_hook( TICKETS_FILE, [__CLASS__, 'uninstall'] );

        add_action( 'init', [__CLASS__, 'init'] );
        add_action( 'admin_init', [__CLASS__, 'admin_init'] );
        add_action( 'admin_menu', [__CLASS__, 'admin_menu'] );
        add_action( 'admin_bar_menu', [__CLASS__, 'admin_menu_bar'], 100 );
        add_action( 'plugins_loaded', [__CLASS__, 'plugins_loaded'] );
        add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts'] );
        add_action( 'admin_enqueue_scripts', [__CLASS__, 'admin_enqueue_scripts'] );
    }

    /**
     * Hook for "init" action.
     * @return void
     * @since 0.1.0
     */
    public static function init() {
        // Initialize locales
        load_plugin_textdomain( TICKETS_SLUG, false, TICKETS_NAME . '/languages' );

        // Initialize options
        $options = self::get_options();

        // Initialize custom post types
        self::init_custom_post_types();

        // Initialize shortcodes
        self::init_shortcodes();

        // Initialize admin screens
        self::init_screens();
        self::screens_call_method( 'init' );
    }

    /**
     * Initialize custom post types.
     * @return void
     * @since 0.1.0
     */
    public static function init_custom_post_types() {
        include( TICKETS_PATH . 'src/Tickets_Ticket_CustomPostType.php' );

        /**
         * @var Tickets_Ticket_CustomPostType $cpt_wizard_screen
         */
        $cpt_wizard = new Tickets_Ticket_CustomPostType();
        $cpt_wizard->init_cpt();
    }

    /**
     * Registers our shortcodes.
     * @return void
     * @since 1.0.O
     */
    public static function init_shortcodes() {
        //...
    }

    /**
     * Initialize settings using <b>WordPress Settings API</b>.
     * @link https://developer.wordpress.org/plugins/settings/settings-api/
     * @return void
     * @since 0.1.0
     */
    protected static function init_settings() {
        $section1 = self::SETTINGS_KEY . '_section_1';
        add_settings_section(
                $section1,
                __( 'Wizards options' ),
                [__CLASS__, 'render_settings_section_1'],
                TICKETS_SLUG
        );

        add_settings_field(
                'show_advanced_options',
                __( 'Show advanced options', TICKETS_SLUG ),
                [__CLASS__, 'render_setting_show_advanced_options'],
                TICKETS_SLUG,
                $section1
        );

        add_settings_field(
                'generate_full_plugin',
                __( 'Always generate full plugin', TICKETS_SLUG ),
                [__CLASS__, 'render_setting_generate_full_plugin'],
                TICKETS_SLUG,
                $section1
        );
    }

    /**
     * Initialize admin screens.
     * @return void
     * @since 0.1.0
     */
    protected static function init_screens() {
        include( TICKETS_PATH . 'src/Tickets_Screen_Prototype.php' );
        include( TICKETS_PATH . 'src/DevHelper_Wizard_Screen_Prototype.php' );
        include( TICKETS_PATH . 'src/DevHelper_Options_Screen.php' );
        include( TICKETS_PATH . 'src/DevHelper_CustomPostType_Wizard_Screen.php' );
        include( TICKETS_PATH . 'src/DevHelper_DashboardWidget_Wizard_Screen.php' );
        include( TICKETS_PATH . 'src/DevHelper_Table_Wizard_Screen.php' );
        include( TICKETS_PATH . 'src/DevHelper_Plugin_Wizard_Screen.php' );
        include( TICKETS_PATH . 'src/DevHelper_Theme_Wizard_Screen.php' );
        include( TICKETS_PATH . 'src/DevHelper_Widget_Wizard_Screen.php' );

        /**
         * @var DevHelper_Options_Screen $options_screen
         */
        $options_screen = new DevHelper_Options_Screen();
        self::$admin_screens[$options_screen->get_slug()] = $options_screen;


        /**
         * @var DevHelper_CustomPostType_Wizard_Screen $cpt_wizard_screen
         */
        $cpt_wizard_screen = new DevHelper_CustomPostType_Wizard_Screen();
        self::$admin_screens[$cpt_wizard_screen->get_slug()] = $cpt_wizard_screen;

        /**
         * @var DevHelper_DashboardWidget_Wizard_Screen $dashboard_wizard_screen
         */
        $dashboard_wizard_screen = new DevHelper_DashboardWidget_Wizard_Screen();
        self::$admin_screens[$dashboard_wizard_screen->get_slug()] = $dashboard_wizard_screen;

        /**
         * @var DevHelper_Table_Wizard_Screen $table_wizard_screen
         */
        $table_wizard_screen = new DevHelper_Table_Wizard_Screen();
        self::$admin_screens[$table_wizard_screen->get_slug()] = $table_wizard_screen;

        /**
         * @var DevHelper_Plugin_Wizard_Screen $plugin_wizard_screen
         */
        $plugin_wizard_screen = new DevHelper_Plugin_Wizard_Screen();
        self::$admin_screens[$plugin_wizard_screen->get_slug()] = $plugin_wizard_screen;

        /**
         * @var DevHelper_Theme_Wizard_Screen $theme_wizard_screen
         */
        $theme_wizard_screen = new DevHelper_Theme_Wizard_Screen();
        self::$admin_screens[$theme_wizard_screen->get_slug()] = $theme_wizard_screen;

        /**
         * @var DevHelper_Widget_Wizard_Screen $widget_wizard_screen
         */
        $widget_wizard_screen = new DevHelper_Widget_Wizard_Screen();
        self::$admin_screens[$widget_wizard_screen->get_slug()] = $widget_wizard_screen;
    }

    /**
     * Hook for "admin_init" action.
     * @return void
     * @since 0.1.0
     */
    public static function admin_init() {
        register_setting( TICKETS_SLUG, self::SETTINGS_KEY );

        // Just show the message that post was successfully created
        if( isset( $_GET['created_new'] ) ) {
            Tickets_Plugin::print_admin_notice(
                __( 'New wizard was successfully inserted. [2]', TICKETS_SLUG ),
                'success', true
            );
        }

        self::check_environment();
        self::init_settings();
        self::screens_call_method( 'admin_init' );
        self::admin_init_widgets();
    }

    /**
     * @internal Initializes WP admin dashboard widgets.
     * @return void
     * @since 0.1.0
     */
    public static function admin_init_widgets() {
        //...
    }

    /**
     * Hook for "admin_menu" action.
     * @return void
     * @since 0.1.0
     */
    public static function admin_menu() {
        // Call action for `admin_menu` hook on all screens.
        self::screens_call_method( 'admin_menu' );
    }

    /**
     * Hook for "admin_menu_bar" action.
     * @link https://codex.wordpress.org/Class_Reference/WP_Admin_Bar/add_menu
     * @param \WP_Admin_Bar $bar
     * @return void
     * @since 0.1.0
     */
    public static function admin_menu_bar( \WP_Admin_Bar $bar ) {
        $bar->add_node( [
            'id'     => 'odwpdh-adminbar_item',
            'href'   => admin_url( 'tools.php?page=' . TICKETS_SLUG . '-log' ),
            'parent' => 'top-secondary',
            'title'  => '<span class="ab-icon"></span>',
            'meta'   => [
                'title' => __( 'Show Debug Log Viewer', TICKETS_SLUG ),
            ],
        ] );
    }

    /**
     * Hook for "admin_enqueue_scripts" action.
     * @param string $hook
     * @return void
     * @since 0.1.0
     */
    public static function admin_enqueue_scripts( $hook ) {

        // Common files

        $js_file = 'assets/js/admin.js';
        $js_path = TICKETS_PATH . $js_file;

        if( file_exists( $js_path ) && is_readable( $js_path ) ) {
	        wp_enqueue_script( TICKETS_SLUG, plugins_url( $js_file, TICKETS_FILE ), ['jquery'] );
            wp_localize_script( TICKETS_SLUG, 'odwpdl', [
                // Put variables you want to pass into JS here...
            ] );
        }

        $css_file = 'assets/css/admin.css';
        $css_path = TICKETS_PATH . $css_file;

        if( file_exists( $css_path ) && is_readable( $css_path ) ) {
            wp_enqueue_style( TICKETS_SLUG, plugins_url( $css_file, TICKETS_FILE ) );
        }

        // Prism

        $js_prism_file = 'assets/js/prism.js';
        $js_prism_path = TICKETS_PATH . $js_prism_file;

        if( file_exists( $js_prism_path ) && is_readable( $js_prism_path ) ) {
            wp_enqueue_script( TICKETS_SLUG . '-prism', plugins_url( $js_prism_file, TICKETS_FILE ), ['jquery'] );
        }

        $css_prism_file = 'assets/css/prism.css';
        $css_prism_path = TICKETS_PATH . $css_prism_file;

        if( file_exists( $css_prism_path ) && is_readable( $css_prism_path ) ) {
            wp_enqueue_style( TICKETS_SLUG . '-prism', plugins_url( $css_prism_file, TICKETS_FILE ) );
        }

        self::screens_call_method( 'admin_enqueue_scripts' );
    }

    /**
     * Checks environment we're running and prints admin messages if needed.
     * @return void
     * @since 0.1.0
     */
    public static function check_environment() {
        //...
    }

    /**
     * Loads specified template with given arguments.
     * @param string $template
     * @param array  $args (Optional.)
     * @return string Output created by rendering template.
     * @since 0.1.0
     * @todo Add filter hook using `apply_filter` for returned template.
     */
    public static function load_template( $template, array $args = [] ) {
        extract( $args );
        $path = sprintf( '%spartials/%s.phtml', TICKETS_PATH, $template );
        ob_start( function() {} );
        include( $path );

        return ob_get_flush();
    }

    /**
     * Hook for "plugins_loaded" action.
     * @return void
     * @since 0.1.0
     */
    public static function plugins_loaded() {
        //...
    }

    /**
     * Hook for "wp_enqueue_scripts" action.
     * @return void
     * @since 0.1.0
     */
    public static function enqueue_scripts() {

        // Common front-end files

        $js_file = 'assets/js/public.js';
        $js_path = TICKETS_FILE . $js_file;

        if( file_exists( $js_path ) && is_readable( $js_path ) ) {
            wp_enqueue_script( TICKETS_SLUG, plugins_url( $js_file, TICKETS_FILE ), ['jquery'] );
            wp_localize_script( TICKETS_SLUG, 'odwpdl', [
                // Put variables you want to pass into JS here...
            ] );
        }

        $css_file = 'assets/css/public.css';
        $css_path = TICKETS_FILE . $css_file;

        if( file_exists( $css_path ) && is_readable( $css_path ) ) {
            wp_enqueue_style( TICKETS_SLUG, plugins_url( $css_file, TICKETS_FILE ) );
        }
    }

    /**
     * @internal Renders the first settings section.
     * @return void
     * @since 0.1.0
     */
    public static function render_settings_section_1() {
        echo self::load_template( 'setting-section_1' );
    }

    /**
     * @internal Renders setting `show_advanced_options`.
     * @return void
     * @since 0.1.0
     */
    public static function render_setting_show_advanced_options() {
        echo self::load_template( 'setting-show_advanced_options', [
            'show_advanced_options' => self::get_option( 'show_advanced_options' ),
        ] );
    }

    /**
     * @internal Renders setting `generate_full_plugin`.
     * @return void
     * @since 0.1.0
     */
    public static function render_setting_generate_full_plugin() {
        echo self::load_template( 'setting-generate_full_plugin', [
            'generate_full_plugin' => self::get_option( 'generate_full_plugin' ),
        ] );
    }

    /**
     * @internal Uninstalls the plugin.
     * @return void
     * @since 0.1.0
     */
    public static function uninstall() {
        if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }

        //...
    }

    /**
     * @internal Prints error message in correct WP amin style.
     * @param string $msg Error message.
     * @param string $type (Optional.) One of ['error','info','success','warning'].
     * @param boolean $dismissible (Optional.) Is notice dismissible?
     * @return void
     * @since 0.1.0
     * @todo Using lambda function maybe overheads our minimal PHP version!
     */
    public static function print_admin_notice( $msg, $type = 'info', $dismissible = true ) {
        add_action( 'admin_notices', function() use ( $msg, $type, $dismissible ) {
            $class = 'notice';
    
            if( in_array( $type, ['error','info','success','warning'] ) ) {
                $class .= ' notice-' . $type;
            } else {
                $class .= ' notice-info';
            }
    
            if( $dismissible === true) {
                $class .= ' s-dismissible';
            }
    
            printf( '<div class="%s"><p>%s</p></div>', $class, $msg );
        } );
    }

    /**
     * On all screens call method with given name.
     *
     * Used for calling hook's actions of the existing screens.
     * See {@see Tickets_Plugin::admin_menu} for an example how is used.
     *
     * If method doesn't exist in the screen object it means that screen
     * do not provide action for the hook.
     *
     * @access private
     * @param  string  $method
     * @return void
     * @since 0.1.0
     */
    private static function screens_call_method( $method ) {
        foreach ( self::$admin_screens as $slug => $screen ) {
            if( method_exists( $screen, $method ) ) {
                call_user_func( [ $screen, $method ] );
            }
        }
    }
}

endif;
