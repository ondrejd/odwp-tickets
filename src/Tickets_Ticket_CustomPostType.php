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
    const SLUG = TICKETS_SLUG . '-ticket';

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
     */
    public static function init_cpt() {

        /**
         * @var array $labels Labels for new custom post type.
         */
        $labels = array(
            'name' => _x( 'Wizards', 'post type general name', TICKETS_SLUG ),
            'singular_name' => _x( 'New wizard', 'post type singular name', TICKETS_SLUG ),
            'add_new' => _x( 'Add wizard', 'add new course', TICKETS_SLUG ),
            'add_new_item' => __( 'Add new wizard', TICKETS_SLUG ),
            'edit_item' => __( 'Edit wizard', TICKETS_SLUG ),
            'new_item' => __( 'New wizard', TICKETS_SLUG ),
            'view_item' => __( 'Show wizard', TICKETS_SLUG ),
            'search_items' => __( 'Search wizards', TICKETS_SLUG ),
            'not_found' => __( 'No wizard was found.', TICKETS_SLUG ),
            'not_found_in_trash' => __( 'No wizard was found in trash.', TICKETS_SLUG ),
            'all_items' => __( 'Finished', TICKETS_SLUG ),
            'archives' => __( 'Wizards archive', TICKETS_SLUG ),
            'menu_name' => __( 'Wizards', TICKETS_SLUG ),
        );

        /**
         * @var array $args Custom post type arguments.
         */
        $args = array(
            'labels' => $labels,
            'description' => __( 'Wizards created by DevHelper.', TICKETS_SLUG ),
            'public' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-welcome-learn-more',
            'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'author' ),
            'taxonomies' => array(),
            'has_archive' => true,
            'show_in_admin_bar' => false,
        );

        /**
         * Filter wizard post type arguments.
         * 
         * @param array $arguments Wizard post type arguments.
         * @since 0.1.0
         */
        $args = apply_filters( 'devhelper_' . self::SLUG . '_post_type_arguments', $args );
        register_post_type( self::SLUG, $args );
    }

    /**
     * Insert new item (from one of wizard forms). 
     * Given values SHOULD be already sanitized!
     * 
     * @param array $values Wizard values from the submitted form.
     * @return integer ID of the new post (wizard).
     * @since 0.1.0
     * @todo Values for `comment_status`, `ping_status` and `post_status` should be taken from plugin options!
     * @uses sanitize_title()
     * @uses wp_insert_post()
     */
    public static function insert_new( $args ) {

        // Finalize new post options
        $post_options = array(
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_status' => 'publish',
            'post_type' => self::SLUG,
        );

        // Create new post
        return wp_insert_post( array_merge( $post_options, $args ) );
    }

}

endif;