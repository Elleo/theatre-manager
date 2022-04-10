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
                echo '<div class="options_group room clear"></div>';
            } );
            add_action( 'woocommerce_product_options_pricing', array( $this, 'add_advanced_pricing' ) );
        }

        public function add_advanced_pricing() {
            global $product_object;
            ?>
            <div class='options_group room'>
            <?php

                woocommerce_wp_text_input(
                    array(
                        'id'          => '_member_price',
                        'label'       => __( 'Pricing only for members', 'your_textdomain' ),
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

