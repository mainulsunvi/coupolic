<?php
/**
 * Coupon generator functionality
 *
 * @package Coupolic
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Generator class
 */
class Coupolic_Generator {

    /**
     * Generate bulk coupons
     *
     * @param array $data Coupon data
     * @return array|WP_Error
     */
    public function generate_bulk_coupons( $data ) {
        $generated_coupons = array();

        for ( $i = 0; $i < $data['quantity']; $i++ ) {
            $coupon_code = $this->generate_unique_code( $data['prefix'], $data['character_count'] );

            $coupon = array(
                'post_title'   => $coupon_code,
                'post_content' => $data['description'],
                'post_excerpt' => $data['description'],
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
                'post_type'    => 'shop_coupon',
            );
            
            $new_coupon_id = wp_insert_post( $coupon );
            
            if ( is_wp_error( $new_coupon_id ) ) {
                return $new_coupon_id;
            }
            
            // Add coupon meta
            update_post_meta( $new_coupon_id, 'discount_type', $data['discount_type'] );
            update_post_meta( $new_coupon_id, 'coupon_amount', $data['amount'] );
            update_post_meta( $new_coupon_id, 'individual_use', $data['individual_use'] ? 'yes' : 'no' );
            update_post_meta( $new_coupon_id, 'usage_limit', $data['usage_limit'] );
            update_post_meta( $new_coupon_id, 'usage_count', 0 );
            
            if ( ! empty( $data['expiry_date'] ) ) {
                update_post_meta( $new_coupon_id, 'date_expires', strtotime( $data['expiry_date'] ) );
            }
            
            if ( $data['minimum_amount'] > 0 ) {
                update_post_meta( $new_coupon_id, 'minimum_amount', $data['minimum_amount'] );
            }
            
            // Default meta values
            update_post_meta( $new_coupon_id, 'product_ids', '' );
            update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
            update_post_meta( $new_coupon_id, 'usage_limit_per_user', '' );
            update_post_meta( $new_coupon_id, 'limit_usage_to_x_items', '' );
            update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
            update_post_meta( $new_coupon_id, 'exclude_sale_items', 'no' );
            update_post_meta( $new_coupon_id, 'product_categories', array() );
            update_post_meta( $new_coupon_id, 'exclude_product_categories', array() );
            update_post_meta( $new_coupon_id, 'customer_email', array() );
            
            $generated_coupons[] = array(
                'id'   => $new_coupon_id,
                'code' => $coupon_code,
                'link' => admin_url( 'post.php?post=' . $new_coupon_id . '&action=edit' ),
            );
        }
        
        return array(
            'coupons' => $generated_coupons,
            'count'   => count( $generated_coupons ),
        );
    }
    
    /**
     * Generate unique coupon code
     *
     * @param string $prefix Coupon prefix
     * @return string
     */
    private function generate_unique_code( $prefix = 'COUPON', $character_count = 5 ) {
        $attempts = 0;
        $max_attempts = 10;
        
        do {
            $random_string = strtoupper( wp_generate_password( $character_count, false ) );
            $coupon_code = !empty($prefix) ? $prefix . '_' . $random_string : $random_string;
            $attempts++;
            
            // Check if code already exists
            $exists = get_posts( array(
                'post_type'      => 'shop_coupon',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'title'          => $coupon_code,
                'fields'         => 'ids',
            ) );
            
        } while ( ! empty( $exists ) && $attempts < $max_attempts );
        
        return $coupon_code;
    }
    
    /**
     * Export coupons to CSV
     *
     * @param array $coupon_ids Array of coupon IDs
     * @return string CSV content
     */
    public function export_to_csv( $coupon_ids ) {
        $csv_data = array();
        $csv_data[] = array(
            'Coupon Code',
            'Discount Type',
            'Amount',
            'Usage Limit',
            'Expiry Date',
            'Minimum Amount',
            'Individual Use',
        );
        
        foreach ( $coupon_ids as $coupon_id ) {
            $coupon = new WC_Coupon( $coupon_id );
            
            $csv_data[] = array(
                $coupon->get_code(),
                $coupon->get_discount_type(),
                $coupon->get_amount(),
                $coupon->get_usage_limit() ?: 'Unlimited',
                $coupon->get_date_expires() ? $coupon->get_date_expires()->date( 'Y-m-d' ) : 'No expiry',
                $coupon->get_minimum_amount() ?: '0',
                $coupon->get_individual_use() ? 'Yes' : 'No',
            );
        }
        
        // Convert to CSV format
        $output = '';
        foreach ( $csv_data as $row ) {
            $output .= '"' . implode( '","', array_map( 'esc_attr', $row ) ) . '"' . "\n";
        }
        
        return $output;
    }
}