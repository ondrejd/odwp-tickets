<?php
/**
 * Plugin Name: Ondrejd\'s Tickets
 * Plugin URI: https://github.com/ondrejd/odwp-tickets
 * Description: Plugin that provides simple ticket system that I'm using with some of my clients. 
 * Version: 0.1.0
 * Author: Ondřej Doněk
 * Author URI: https://ondrejd.com/
 * License: GPLv3
 * Requires at least: 4.8
 * Tested up to: 4.8.5
 * Tags: custom post type,ticketing-system,e-commerce,custommers care
 * Donate link: https://www.paypal.me/ondrejd
 * Text Domain: odwp-tickets
 * Domain Path: /languages/
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-tickets for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-tickets
 * @since 0.0.1
 */

/**
 * This file is just a bootstrap. It checks if requirements of plugins
 * are met and accordingly either allow activating the plugin or stops
 * the activation process.
 *
 * Requirements can be specified either for PHP interperter or for
 * the WordPress self. In both cases you can specify minimal required
 * version and required extensions/plugins.
 *
 * If you are using copy of original file in your plugin you should change
 * prefix "odwpt" and name "odwp-tickets" to your own values.
 *
 * To set the requirements go down to line 200 and define array that
 * is used as a parameter for `odwpt_check_requirements` function.
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Some constants
defined( 'TICKETS_SLUG' ) || define( 'TICKETS_SLUG', 'odwpt' );
defined( 'TICKETS_NAME' ) || define( 'TICKETS_NAME', 'odwp-tickets' );
defined( 'TICKETS_PATH' ) || define( 'TICKETS_PATH', dirname( __FILE__ ) . '/' );
defined( 'TICKETS_FILE' ) || define( 'TICKETS_FILE', __FILE__ );


if( ! function_exists( 'odwpt_check_requirements' ) ) :
    /**
     * Checks requirements of our plugin.
     * @global string $wp_version
     * @param array $requirements
     * @return array
     * @since 1.0.0
     */
    function odwpt_check_requirements( array $requirements ) {
        global $wp_version;

        // Initialize locales
        load_plugin_textdomain( TICKETS_NAME, false, TICKETS_NAME . '/languages' );

        /**
         * @var array Hold requirement errors
         */
        $errors = [];

        // Check PHP version
        if( ! empty( $requirements['php']['version'] ) ) {
            if( version_compare( phpversion(), $requirements['php']['version'], '<' ) ) {
                $errors[] = sprintf(
                        __( 'Used PHP interpreter doesn\'t meet requirements of this plugin (is required version <b>%1$s</b> at least)!'),
                        $requirements['php']['version']
                );
            }
        }

        // Check PHP extensions
        if( count( $requirements['php']['extensions'] ) > 0 ) {
            foreach( $requirements['php']['extensions'] as $req_ext ) {
                if( ! extension_loaded( $req_ext ) ) {
                    $errors[] = sprintf(
                            __( 'PHP extension <b>%1$s</b> is required but not installed!', TICKETS_SLUG ),
                            $req_ext
                    );
                }
            }
        }

        // Check WP version
        if( ! empty( $requirements['wp']['version'] ) ) {
            if( version_compare( $wp_version, $requirements['wp']['version'], '<' ) ) {
                $errors[] = sprintf(
                        __( 'This plugin requires higher version of <b>WordPress</b> (at least version <b>%1$s</b>)!', TICKETS_SLUG ),
                        $requirements['wp']['version']
                );
            }
        }

        // Check WP plugins
        if( count( $requirements['wp']['plugins'] ) > 0 ) {
            $active_plugins = (array) get_option( 'active_plugins', [] );
            foreach( $requirements['wp']['plugins'] as $req_plugin ) {
                if( ! in_array( $req_plugin, $active_plugins ) ) {
                    $errors[] = sprintf(
                            __( 'The plugin <b>%1$s</b> is required but not installed!', TICKETS_SLUG ),
                            $req_plugin
                    );
                }
            }
        }

        return $errors;
    }
endif;


if( ! function_exists( 'odwpt_deactivate_raw' ) ) :
    /**
     * Deactivate plugin by the raw way (it updates directly WP options).
     * @return void
     * @since 1.0.0
     */
    function odwpt_deactivate_raw() {
        $active_plugins = get_option( 'active_plugins' );
        $out = [];
        foreach( $active_plugins as $key => $val ) {
            if( $val != TICKETS_NAME . '/' . TICKETS_NAME . '.php' ) {
                $out[$key] = $val;
            }
        }
        update_option( 'active_plugins', $out );
    }
endif;


if( ! function_exists( 'readonly' ) ) :
    /**
     * Prints HTML readonly attribute. It's an addition to WP original
     * functions {@see disabled()} and {@see checked()}.
     * @param mixed $value
     * @param mixed $current (Optional.) Defaultly TRUE.
     * @return string
     * @since 1.0.0
     */
    function readonly( $current, $value = true ) {
        if( $current == $value ) {
            echo ' readonly';
        }
    }
endif;


/**
 * Errors from the requirements check
 * @var array
 */
$odwpt_errs = odwpt_check_requirements( [
    'php' => [
        // Enter minimum PHP version you needs
        // TODO But we uses lambda functions in screen classes!!!
        'version' => '5.6',
        // Enter extensions that your plugin needs
        'extensions' => [
            //'gd',
        ],
    ],
    'wp' => [
        // Enter minimum WP version you need
        'version' => '4.7',
        // Enter WP plugins that your plugin needs
        'plugins' => [
            //'woocommerce/woocommerce.php',
        ],
    ],
] );

// Check if requirements are met or not
if( count( $odwpt_errs ) > 0 ) {
    // Requirements are not met
    odwpt_deactivate_raw();

    // In administration print errors
    if( is_admin() ) {
        add_action( 'admin_notices', function() use ( $odwpt_errs ) {
            $err_head = __( '<b>Ondrejd\'s Tickets</b>: ', TICKETS_SLUG );

            foreach( $odwpt_errs as $err ) {
                printf( '<div class="error"><p>%1$s</p></div>', $err_head . $err );
            }
        } );
    }
} else {
    // Requirements are met so initialize the plugin.
    include( TICKETS_PATH . 'src/Tickets_Screen_Prototype.php' );
	include( TICKETS_PATH . 'src/Tickets_Plugin.php' );
	
    Tickets_Plugin::initialize();
}
