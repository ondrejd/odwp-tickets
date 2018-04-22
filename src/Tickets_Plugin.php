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
    const CUSTOMER_USER_ROLE = TICKETS_SLUG . '_customer';

    /**
     * @var array $admin_screens Array with admin screens.
     * @since 0.1.0
     */
    public static $admin_screens = [];

    /**
     * Activates the plugin.
     *
     * @return void
     * @since 0.1.0
     * @uses add_role()
     */
    public static function activate() {

        // Add customer user role
        $result = add_role( self::CUSTOMER_USER_ROLE, __( 'Customer', TICKETS_SLUG ), [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'publish_posts' => false,
            'upload_files' => true,
        ] );
    }

    /**
     * Deactivates the plugin.
     *
     * @return void
     * @since 0.1.0
     * @uses get_role()
     * @uses remove_role()
     */
    public static function deactivate() {
        
        // Remove user role "Customer"
        if( get_role( self::CUSTOMER_USER_ROLE ) ) {
            remove_role( self::CUSTOMER_USER_ROLE );
        }
    }

    /**
     * Initializes the plugin.
     * 
     * @return void
     * @since 0.1.0
     * @uses add_action()
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses register_uninstall_hook()
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
     * 
     * @return void
     * @since 0.1.0
     * @uses load_plugin_textdomain()
     */
    public static function init() {

        // Initialize locales
        load_plugin_textdomain( TICKETS_SLUG, false, TICKETS_NAME . '/languages' );

        // Initialize options
        if( method_exists( __CLASS__, 'get_options' ) ) {
            $options = self::get_options();
        }

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
     * 
     * @return void
     * @since 0.1.0
     */
    public static function init_custom_post_types() {
        include( TICKETS_PATH . 'src/Tickets_Ticket_CustomPostType.php' );

        /**
         * @var Tickets_Ticket_CustomPostType $cpt_ticket
         */
        $cpt_ticket = new Tickets_Ticket_CustomPostType();
        $cpt_ticket->init_cpt();
    }

    /**
     * Registers our shortcodes.
     * 
     * @return void
     * @since 1.0.O
     */
    public static function init_shortcodes() {
        //...
    }

    /**
     * Initialize admin screens.
     * 
     * @return void
     * @since 0.1.0
     */
    protected static function init_screens() {
        include( TICKETS_PATH . 'src/Tickets_Screen_Prototype.php' );
        //include( TICKETS_PATH . 'src/Tickets_Options_Screen.php' );

        /**
         * @var Tickets_Options_Screen $options_screen
         */
        //$options_screen = new Tickets_Options_Screen();
        //self::$admin_screens[$options_screen->get_slug()] = $options_screen;
    }

    /**
     * Hook for "admin_init" action.
     * 
     * @return void
     * @since 0.1.0
     */
    public static function admin_init() {
        self::check_environment();

        if( method_exists( __CLASS__, 'init_settings' ) ) {
            self::init_settings();
        }

        self::admin_init_widgets();
        self::screens_call_method( 'admin_init' );

        // ==============================================
        // Add the roles you'd like to administer the custom post types
        $roles = ['administrator', self::CUSTOMER_USER_ROLE];
        
        // Loop through each role and assign capabilities
        foreach( $roles as $the_role ) {
            $role = get_role( $the_role );

            if( ( $role instanceof \WP_Role ) ) {
                $role->add_cap( 'read' );

                $role->add_cap( 'edit_odwptickets_ticket' );
                $role->add_cap( 'publish_odwptickets_tickets' );
                $role->add_cap( 'read_odwptickets_ticket');

                $role->add_cap( 'edit_odwptickets_tickets' );
                $role->add_cap( 'publish_odwptickets_tickets' );

                $role->add_cap( 'read_private_odwptickets_tickets' );
                //$role->add_cap( 'edit_others_odwptickets_tickets' );
                //$role->add_cap( 'edit_published_odwptickets_tickets' );
                $role->add_cap( 'delete_others_odwptickets_tickets' );
                $role->add_cap( 'delete_private_odwptickets_tickets' );
                $role->add_cap( 'delete_published_odwptickets_tickets' );

                $role->add_cap( 'create_odwptickets_tickets' );
                $role->add_cap( 'create_odwptickets_ticket' );

            }
            else {
                if( function_exists( 'odwpdl_write_log' ) ) {
                    odwpdl_write_log( 'Role "' . $the_role . '" was not found!' );
                }
            }
        }
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
     * 
     * @return void
     * @since 0.1.0
     */
    public static function admin_menu() {
        // Call action for `admin_menu` hook on all screens.
        self::screens_call_method( 'admin_menu' );
    }

    /**
     * Hook for "admin_menu_bar" action.
     * 
     * @link https://codex.wordpress.org/Class_Reference/WP_Admin_Bar/add_menu
     * @param \WP_Admin_Bar $bar
     * @return void
     * @since 0.1.0
     */
    public static function admin_menu_bar( \WP_Admin_Bar $bar ) {
        //...
    }

    /**
     * Hook for "admin_enqueue_scripts" action.
     * 
     * @param string $hook
     * @return void
     * @since 0.1.0
     * @uses plugins_url()
     * @uses wp_enqueue_script()
     * @uses wp_enqueue_style()
     * @uses wp_localize_script()
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

        self::screens_call_method( 'admin_enqueue_scripts' );
    }

    /**
     * Checks environment we're running and prints admin messages if needed.
     * 
     * @return void
     * @since 0.1.0
     */
    public static function check_environment() {
        //...
    }

    /**
     * Loads specified template with given arguments.
     * 
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
     * 
     * @return void
     * @since 0.1.0
     */
    public static function plugins_loaded() {
        //...
    }

    /**
     * Hook for "wp_enqueue_scripts" action.
     * 
     * @return void
     * @since 0.1.0
     * @uses plugins_url()
     * @uses wp_enqueue_script()
     * @uses wp_enqueue_style()
     * @uses wp_localize_script()
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
