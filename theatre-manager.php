<?php
/**
 * Plugin Name: Theatre Manager
 * Plugin URI: https://github.com/Elleo/theatre-manager
 * Description: Handles ticketing and room hire for theatres based on Woocommerce.
 * Version: 0.1.0
 * Author: Mike Sheldon
 * Author URI: https://mikeasoft.com
 * Developer: Mike Sheldon
 * Developer URI: https://mikeasoft.com
 *
 * Text Domain: theatre-manager
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WooCommerce\Admin
 */

use Automattic\WooCommerce\Admin\Features\Navigation\Menu;
use Automattic\WooCommerce\Admin\Features\Navigation\Screen;
use Automattic\WooCommerce\Admin\Features\Features;

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'TheatreManager' ) ) :

    class TheatreManager {

        /**
         * The single instance of the class.
         */
        protected static $_instance = null;

        protected function __construct() {
            $this->includes();
            $this->init();
        }

        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Cloning is forbidden.
         */
        public function __clone() {
            wc_doing_it_wrong();
        }

        /**
         * Unserializing instances of this class is forbidden.
         */
        public function __wakeup() {
            wc_doing_it_wrong();
        }

        /**
        * Function for loading dependencies.
        */
        private function includes() {
            require_once 'includes/room.php';
        }

        private function init() {
            add_action( 'admin_enqueue_scripts', array( $this, 'add_extension_register_script' ) );
            add_action( 'admin_menu', array( $this, 'register_menu_items' ) );
            add_filter( 'product_type_selector', array( $this, 'add_type' ) );
            add_action( 'woocommerce_product_options_general_product_data', function(){
                echo '<div class="options_group theatre_product clear"></div>';
            } );
            add_action( 'woocommerce_product_options_pricing', array( $this, 'add_advanced_pricing' ) );
            add_action( 'admin_footer', array( $this, 'enable_js_on_products' ) );
            add_action( 'woocommerce_single_product_summary', array( $this, 'add_booking_form'), 20 );
        }

        public function add_booking_form() {
            $today = date('Y-m-d');
        ?>
            <label>Booking date: <input type='date' id='date' value='<?=$today?>' /></label>
        <?php
        }

        public function enable_js_on_products() {
            global $post, $product_object;

            if ( ! $post ) { return; }

            if ( 'product' != $post->post_type ) { return; }

            $is_theatre_product = $product_object && in_array($product_object->get_type(), ["simple", "room", "workshop", "performance"]);

            ?>
            <script type='text/javascript'>
                jQuery(document).ready(function () {
                    //for Price tab
                    jQuery('#general_product_data .pricing').addClass('theatre_product');

                    <?php if ( $is_theatre_product ) { ?>
                        jQuery('#general_product_data .pricing').show();
                    <?php } ?>
                });
           </script>
           <?php
        }

        public function add_advanced_pricing() {
            global $product_object;
            ?>
            <div class='options_group theatre_product'>

            <?php

                woocommerce_wp_text_input(
                    array(
                        'id'          => '_member_price',
                        'label'       => __( 'Pricing only for members', 'theatre_manager' ),
                        'value'       => $product_object->get_meta( '_member_price', true ),
                        'default'     => '',
                        'placeholder' => 'Add pricing',
                        'data_type' => 'price',
                    )
                );
            ?>
            </div>

            <?php
        }

        public function add_extension_register_script() {
            if ( ! class_exists( 'Automattic\WooCommerce\Admin\PageController' ) || ! \Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page() ) {
                return;
            }

            $script_path       = '/build/index.js';
            $script_asset_path = dirname( __FILE__ ) . '/build/index.asset.php';
            $script_asset      = file_exists( $script_asset_path )
                ? require( $script_asset_path )
                : array( 'dependencies' => array(), 'version' => filemtime( $script_path ) );
            $script_url = plugins_url( $script_path, __FILE__ );

            wp_register_script(
                'theatre-manager',
                $script_url,
                $script_asset['dependencies'],
                $script_asset['version'],
                true
            );

            wp_register_style(
                'theatre-manager',
                plugins_url( '/build/index.css', __FILE__ ),
                // Add any dependencies styles may have, such as wp-components.
                array(),
                filemtime( dirname( __FILE__ ) . '/build/index.css' )
            );

            wp_enqueue_script( 'theatre-manager' );
            wp_enqueue_style( 'theatre-manager' );
        }

        public function register_menu_items() {

           add_menu_page(
                'Performances',
                'Performances',
                'read',
                'performances',
                '',
                'dashicons-tickets',
                5);

            add_menu_page(
                'Room Bookings',
                'Room Bookings',
                'read',
                'room-bookings',
                '',
                'dashicons-calendar-alt',
                6);

            add_menu_page(
                'Workshops',
                'Workshops',
                'read',
                'workshops',
                '',
                'dashicons-art',
                7);

        }

        public function add_type( $types ) {
            $types = array();
            $types['workshop'] = __( 'Workshop', 'theatre-manager' );
            $types['performance'] = __( 'Performance', 'theatre-manager' );
            $types['room'] = __( 'Room', 'theatre-manager' );

            return $types;
        }

    }
endif;

function theatre_manager_initialize() {

    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    $GLOBALS['theatre_manager'] = TheatreManager::instance();
}

add_action( 'plugins_loaded', 'theatre_manager_initialize', 10 );

register_activation_hook( __FILE__, 'install' );

function install() {
    global $wpdb;
    if ( !get_term_by( 'slug', 'room', 'product_type' ) ) {
        wp_insert_term( 'room', 'product_type' );
    }
    if ( !get_term_by( 'slug', 'workshop', 'product_type' ) ) {
        wp_insert_term( 'workshop', 'product_type' );
    }
    if ( !get_term_by( 'slug', 'performance', 'product_type' ) ) {
        wp_insert_term( 'performance', 'product_type' );
    }

    $bookings_table = $wpdb->prefix . "bookings";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $bookings_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id bigint(20) unsigned,
        order_id bigint(20) unsigned,
        start_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        end_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY  (product_id) REFERENCES wp_posts(ID),
        FOREIGN KEY  (order_id) REFERENCES wp_wc_order_stats(order_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
