<?php

class Room extends WC_Product_Simple {

    public function get_type() {
        return "room";
    }

    public function get_price( $context = 'view' ) {

        if ( current_user_can('manage_options') ) {
            $price = $this->get_meta( '_member_price', true );
            if ( is_numeric( $price ) ) {
                return $price;
            }
        
        }
        return $this->get_prop( 'price', $context );
    }
}
