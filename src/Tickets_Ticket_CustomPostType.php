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

if( ! class_exists( 'Tickets_Ticket_CustomPostType' ) ) :

/**
 * Our custom post type.
 * 
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @since 0.1.0
 */
class Tickets_Ticket_CustomPostType {

    /**
     * @var string
     * @since 0.1.0
     */
    const SLUG = TICKETS_SLUG . '_ticket';//odwptickets_ticket

    /**
     * Constructor.
     * 
     * @author Ondřej Doněk, <ondrejd@gmail.com>
     * @return void
     */
    public function __construct() {

        // Nothing to do...
    }

    /**
     * Initialize our custom post type.
     * 
     * @return void
     * @since 0.1.0
     * @uses register_post_type()
     */
    public static function init_cpt() {

        /**
         * @var array $labels Labels for new custom post type.
         */
        $labels = array(
            'name' => _x( 'Tickets', 'post type general name', TICKETS_SLUG ),
            'singular_name' => _x( 'Ticket', 'post type singular name', TICKETS_SLUG ),
            'add_new' => _x( 'Add ticket', 'add new course', TICKETS_SLUG ),
            'add_new_item' => __( 'Add new ticket', TICKETS_SLUG ),
            'edit_item' => __( 'Edit ticket', TICKETS_SLUG ),
            'new_item' => __( 'New ticket', TICKETS_SLUG ),
            'view_item' => __( 'Show ticket', TICKETS_SLUG ),
            'search_items' => __( 'Search wizards', TICKETS_SLUG ),
            'not_found' => __( 'No ticket was found.', TICKETS_SLUG ),
            'not_found_in_trash' => __( 'No ticket was found in trash.', TICKETS_SLUG ),
            'all_items' => __( 'All Tickets', TICKETS_SLUG ),
            'archives' => __( 'Tickets archive', TICKETS_SLUG ),
            'menu_name' => __( 'Tickets', TICKETS_SLUG ),
        );

        /**
         * @var array $args Custom post type arguments.
         */
        $args = array(
            'label' => __( 'odwptickets_ticket', TICKETS_SLUG ),
            'labels' => $labels,
            'description' => __( 'Customer tickets.', TICKETS_SLUG ),
            'public' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-tickets-alt',
            'supports' => ['title', 'editor', 'comments', 'author'],
            'capability_type' => ['odwptickets_ticket','odwptickets_tickets'],
            'has_archive' => false,
            'show_in_admin_bar' => true,
            'hierarchical' => false,
            //'register_meta_box_cb' => [__CLASS__, 'register_meta_box'],
            //'delete_with_user' => true,
            //'show_in_rest' => false,
            'map_meta_cap' => true,
        );

        register_post_type( self::SLUG, $args );
    }

    public static function register_meta_box( $boxes ) {
        echo '<pre>';var_dump( $boxes );exit();
    }

}

endif;