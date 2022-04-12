<?php

function register_room_product_type() {

    class Room extends WC_Product_Simple {

        public function __construct( $product ) {
            die("That happened");
            $this->product_type = 'room';
            parent::__construct( $product );
        }
    }
}

add_action( 'plugins_loaded', 'register_room_product_type' );

