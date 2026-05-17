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
     * Generate bulk coupons with rollback capability
     *
     * @param array $data Coupon data
     * @param string $batch_id Unique batch identifier
     * @return array|WP_Error
     */
    public function generate_bulk_coupons( $data, $batch_id ) {
        $generated_coupons = array();
        $rollback_ids = array();
        $quantity = absint( $data['quantity'] );

        // Initialize progress tracking
        $this->update_progress( $batch_id, 0, $quantity, 'initializing', 'Starting generation...' );

        try {
            for ( $i = 1; $i <= $quantity; $i++ ) {
                // Generate unique coupon code
                $coupon_code = $this->generate_unique_code( $data['prefix'] ?? 'COUPON', $data['character_count'] ?? 5 );

                // Create coupon post
                $coupon_post = array(
                    'post_title'   => $coupon_code,
                    'post_content' => $data['description'] ?? '',
                    'post_excerpt' => $data['description'] ?? '',
                    'post_status'  => 'publish',
                    'post_author'  => get_current_user_id(),
                    'post_type'    => 'shop_coupon',
                );

                $new_coupon_id = wp_insert_post( $coupon_post );

                if ( is_wp_error( $new_coupon_id ) ) {
                    throw new Exception( $new_coupon_id->get_error_message() );
                }

                $rollback_ids[] = $new_coupon_id;

                // Add coupon meta
                $this->add_coupon_meta( $new_coupon_id, $data );

                // Add to generated list
                $generated_coupons[] = array(
                    'id'   => $new_coupon_id,
                    'code' => $coupon_code,
                    'link' => admin_url( 'post.php?post=' . $new_coupon_id . '&action=edit' ),
                );

                // Update progress with current coupon info
                $this->update_progress( $batch_id, $i, $quantity, 'generating',
                    sprintf( 'Generated coupon %d of %d', $i, $quantity ),
                    $generated_coupons
                );
            }

            // All coupons created successfully - log success
            $this->log_generation_success( $batch_id, $generated_coupons, $data );

            // Final progress update
            $this->update_progress( $batch_id, $quantity, $quantity, 'completed', 'Generation completed successfully' );

            return array(
                'success' => true,
                'batch_id' => $batch_id,
                'coupons' => $generated_coupons,
                'count'   => count( $generated_coupons ),
            );

        } catch ( Exception $e ) {
            // Rollback - delete all coupons created in this batch
            foreach ( $rollback_ids as $coupon_id ) {
                wp_delete_post( $coupon_id, true );
            }

            // Log failure
            $this->log_generation_failure( $batch_id, $e->getMessage(), $data );

            // Update progress with error
            $this->update_progress( $batch_id, count( $generated_coupons ), $quantity, 'failed', $e->getMessage() );

            return array(
                'success' => false,
                'error' => $e->getMessage(),
                'batch_id' => $batch_id,
                'generated_count' => count( $generated_coupons ),
            );
        }
    }

    /**
     * Add coupon meta data
     */
    private function add_coupon_meta( $coupon_id, $data ) {
        // Basic discount settings
        update_post_meta( $coupon_id, 'discount_type', $data['discount_type'] ?? 'fixed_cart' );
        update_post_meta( $coupon_id, 'coupon_amount', $data['coupon_amount'] ?? 0 );
        update_post_meta( $coupon_id, 'individual_use', ( $data['individual_use'] ?? false ) ? 'yes' : 'no' );
        update_post_meta( $coupon_id, 'usage_limit', absint( $data['usage_limit'] ?? 1 ) );
        update_post_meta( $coupon_id, 'usage_count', 0 );

        // Expiry date
        if ( ! empty( $data['expiry_date'] ) ) {
            update_post_meta( $coupon_id, 'date_expires', strtotime( $data['expiry_date'] ) );
        }

        // Amount restrictions
        if ( ! empty( $data['minimum_amount'] ) ) {
            update_post_meta( $coupon_id, 'minimum_amount', floatval( $data['minimum_amount'] ) );
        }

        if ( ! empty( $data['maximum_amount'] ) ) {
            update_post_meta( $coupon_id, 'maximum_amount', floatval( $data['maximum_amount'] ) );
        }

        // Helper function to parse array data
        $parse_array = function( $value ) {
            if ( is_array( $value ) ) {
                return $value;
            }
            if ( is_string( $value ) ) {
                // Check if it's JSON encoded
                $decoded = json_decode( $value, true );
                if ( is_array( $decoded ) ) {
                    return $decoded;
                }
                // Otherwise, split by comma and trim whitespace
                $parts = explode( ',', $value );
                return array_map( 'trim', array_filter( $parts ) );
            }
            return array();
        };

        // Product restrictions
        $product_ids = ! empty( $data['product_ids'] ) ? $parse_array( $data['product_ids'] ) : array();
        update_post_meta( $coupon_id, 'product_ids', $product_ids );

        $exclude_product_ids = ! empty( $data['exclude_product_ids'] ) ? $parse_array( $data['exclude_product_ids'] ) : array();
        update_post_meta( $coupon_id, 'exclude_product_ids', $exclude_product_ids );

        // Category restrictions
        $product_categories = ! empty( $data['product_categories'] ) ? $parse_array( $data['product_categories'] ) : array();
        update_post_meta( $coupon_id, 'product_categories', $product_categories );

        $exclude_product_categories = ! empty( $data['exclude_product_categories'] ) ? $parse_array( $data['exclude_product_categories'] ) : array();
        update_post_meta( $coupon_id, 'exclude_product_categories', $exclude_product_categories );

        // Brand restrictions (custom taxonomy)
        $product_brands = ! empty( $data['product_brands'] ) ? $parse_array( $data['product_brands'] ) : array();
        update_post_meta( $coupon_id, 'product_brands', $product_brands );

        $exclude_product_brands = ! empty( $data['exclude_product_brands'] ) ? $parse_array( $data['exclude_product_brands'] ) : array();
        update_post_meta( $coupon_id, 'exclude_product_brands', $exclude_product_brands );

        // Email restrictions
        $customer_emails = ! empty( $data['customer_email'] ) ? $parse_array( $data['customer_email'] ) : array();
        update_post_meta( $coupon_id, 'customer_email', $customer_emails );

        // Additional settings
        update_post_meta( $coupon_id, 'free_shipping', ( $data['free_shipping'] ?? false ) ? 'yes' : 'no' );
        update_post_meta( $coupon_id, 'exclude_sale_items', ( $data['exclude_sale_items'] ?? false ) ? 'yes' : 'no' );
        update_post_meta( $coupon_id, 'usage_limit_per_user', absint( $data['usage_limit_per_user'] ?? 0 ) );

        // Debug logging
        error_log( 'Coupolic: Added coupon meta for ID ' . $coupon_id );
        error_log( 'Coupolic: Product IDs: ' . implode( ',', $product_ids ) );
        error_log( 'Coupolic: Categories: ' . implode( ',', $product_categories ) );
    }

    /**
     * Update generation progress
     */
    private function update_progress( $batch_id, $current, $total, $status, $message, $generated_coupons = array() ) {
        $progress_data = array(
            'current' => $current,
            'total' => $total,
            'status' => $status,
            'message' => $message,
            'percentage' => ( $current / $total ) * 100,
        );

        // Add generated coupons if available
        if ( ! empty( $generated_coupons ) ) {
            $progress_data['generated_coupons'] = $generated_coupons;
        }

        update_option( 'coupolic_progress_' . $batch_id, $progress_data );

        // Set option to expire in 1 hour
        set_transient( 'coupolic_progress_' . $batch_id, $progress_data, 3600 );
    }

    /**
     * Log generation success
     */
    private function log_generation_success( $batch_id, $coupons, $settings ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'coupolic_logs';

        $user_id = get_current_user_id();
        $user = get_userdata( $user_id );

        // Calculate cleanup date
        $retention_days = get_option( 'coupolic_log_retention', 90 );
        $cleanup_date = date( 'Y-m-d H:i:s', strtotime( "+{$retention_days} days" ) );

        $wpdb->insert( $table_name, array(
            'batch_id' => $batch_id,
            'user_id' => $user_id,
            'user_login' => $user->user_login,
            'generation_time' => current_time( 'mysql' ),
            'coupon_count' => count( $coupons ),
            'success_count' => count( $coupons ),
            'failed_count' => 0,
            'status' => 'completed',
            'settings' => json_encode( $settings ),
            'coupon_codes' => json_encode( $coupons ),
            'cleanup_date' => $cleanup_date,
        ) );
    }

    /**
     * Log generation failure
     */
    private function log_generation_failure( $batch_id, $error_message, $settings ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'coupolic_logs';

        $user_id = get_current_user_id();
        $user = get_userdata( $user_id );

        // Calculate cleanup date
        $retention_days = get_option( 'coupolic_log_retention', 90 );
        $cleanup_date = date( 'Y-m-d H:i:s', strtotime( "+{$retention_days} days" ) );

        $wpdb->insert( $table_name, array(
            'batch_id' => $batch_id,
            'user_id' => $user_id,
            'user_login' => $user->user_login,
            'generation_time' => current_time( 'mysql' ),
            'coupon_count' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'status' => 'failed',
            'settings' => json_encode( $settings ),
            'coupon_codes' => json_encode( array() ),
            'error_message' => $error_message,
            'cleanup_date' => $cleanup_date,
        ) );
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