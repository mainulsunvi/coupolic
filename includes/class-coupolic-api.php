<?php
/**
 * API handlers for Coupolic
 *
 * @package Coupolic
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * API class
 */
class Coupolic_API {

    /**
     * Constructor
     */
    public function __construct() {
        // Wizard operations
        add_action( 'wp_ajax_coupolic_save_draft', array( $this, 'ajax_save_draft' ) );
        add_action( 'wp_ajax_coupolic_load_draft', array( $this, 'ajax_load_draft' ) );
        add_action( 'wp_ajax_coupolic_delete_draft', array( $this, 'ajax_delete_draft' ) );

        // Generation
        add_action( 'wp_ajax_coupolic_generate_coupons', array( $this, 'ajax_generate_coupons' ) );
        add_action( 'wp_ajax_coupolic_check_progress', array( $this, 'ajax_check_progress' ) );

        // Validation
        add_action( 'wp_ajax_coupolic_validate_settings', array( $this, 'ajax_validate_settings' ) );
        add_action( 'wp_ajax_coupolic_check_user_limits', array( $this, 'ajax_check_user_limits' ) );

        // Logs
        add_action( 'wp_ajax_coupolic_get_logs', array( $this, 'ajax_get_logs' ) );
        add_action( 'wp_ajax_coupolic_get_batch_details', array( $this, 'ajax_get_batch_details' ) );
        add_action( 'wp_ajax_coupolic_delete_batch', array( $this, 'ajax_delete_batch' ) );
        add_action( 'wp_ajax_coupolic_export_batch', array( $this, 'ajax_export_batch' ) );

        // Settings
        add_action( 'wp_ajax_coupolic_save_settings', array( $this, 'ajax_save_settings' ) );
    }

    /**
     * Verify nonce and permissions
     */
    private function verify_request() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'coupolic_nonce' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Security check failed', 'coupolic' ) ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions', 'coupolic' ) ) );
        }
    }

    /**
     * Check user limits
     */
    private function check_user_limits( $quantity = 0 ) {
        $user_id = get_current_user_id();
        $user = get_userdata( $user_id );
        $user_limits = get_option( 'coupolic_user_limits', array() );

        // Get user role limits
        $role = $user->roles[0] ?? 'subscriber';
        $limits = $user_limits[$role] ?? $user_limits['editor'] ?? array( 'daily_limit' => 100, 'batch_limit' => 50 );

        // Check daily limit
        if ( $limits['daily_limit'] !== -1 ) {
            $today = date( 'Y-m-d' );
            global $wpdb;

            $count = $wpdb->get_var( $wpdb->prepare(
                "SELECT SUM(coupon_count) FROM {$wpdb->prefix}coupolic_logs
                WHERE user_id = %d AND DATE(generation_time) = %s AND status = 'completed'",
                $user_id,
                $today
            ) );

            if ( (int) $count + $quantity > $limits['daily_limit'] ) {
                return new WP_Error( 'daily_limit_exceeded',
                    sprintf( esc_html__( 'Daily limit exceeded. You can generate %d more coupons today.', 'coupolic' ),
                    $limits['daily_limit'] - (int) $count )
                );
            }
        }

        // Check batch limit
        if ( $limits['batch_limit'] !== -1 && $quantity > $limits['batch_limit'] ) {
            return new WP_Error( 'batch_limit_exceeded',
                sprintf( esc_html__( 'Batch limit exceeded. Maximum %d coupons per batch.', 'coupolic' ),
                $limits['batch_limit'] )
            );
        }

        return $limits;
    }

    /**
     * AJAX: Save draft
     */
    public function ajax_save_draft() {
        $this->verify_request();

        $data = $this->sanitize_draft_data( wp_unslash( $_POST ) );

        $draft_id = wp_insert_post( array(
            'post_title'   => 'Coupolic Draft - ' . date( 'Y-m-d H:i:s' ),
            'post_content' => json_encode( $data ),
            'post_status'  => 'draft',
            'post_type'    => 'coupolic_draft',
            'post_author'  => get_current_user_id(),
        ) );

        if ( is_wp_error( $draft_id ) ) {
            wp_send_json_error( array( 'message' => $draft_id->get_error_message() ) );
        }

        wp_send_json_success( array(
            'draft_id' => $draft_id,
            'message' => esc_html__( 'Draft saved successfully', 'coupolic' )
        ) );
    }

    /**
     * Sanitize draft data
     */
    private function sanitize_draft_data( $data ) {
        $sanitized = array();

        $fields = array(
            'discount_type', 'coupon_amount', 'expiry_date', 'free_shipping',
            'minimum_amount', 'maximum_amount', 'individual_use', 'exclude_sale_items',
            'product_ids', 'exclude_product_ids', 'product_categories', 'exclude_product_categories',
            'product_brands', 'exclude_product_brands', 'customer_email',
            'usage_limit', 'usage_limit_per_user', 'prefix', 'character_count', 'quantity', 'description'
        );

        foreach ( $fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
            }
        }

        return $sanitized;
    }

    /**
     * AJAX: Load draft
     */
    public function ajax_load_draft() {
        $this->verify_request();

        $draft_id = isset( $_POST['draft_id'] ) ? absint( $_POST['draft_id'] ) : 0;

        if ( ! $draft_id ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid draft ID', 'coupolic' ) ) );
        }

        $draft = get_post( $draft_id );

        if ( ! $draft || $draft->post_type !== 'coupolic_draft' || $draft->post_author !== get_current_user_id() ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Draft not found', 'coupolic' ) ) );
        }

        $data = json_decode( $draft->post_content, true );

        wp_send_json_success( array( 'data' => $data ) );
    }

    /**
     * AJAX: Delete draft
     */
    public function ajax_delete_draft() {
        $this->verify_request();

        $draft_id = isset( $_POST['draft_id'] ) ? absint( $_POST['draft_id'] ) : 0;

        if ( ! $draft_id ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid draft ID', 'coupolic' ) ) );
        }

        $draft = get_post( $draft_id );

        if ( ! $draft || $draft->post_type !== 'coupolic_draft' || $draft->post_author !== get_current_user_id() ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Draft not found', 'coupolic' ) ) );
        }

        wp_delete_post( $draft_id, true );

        wp_send_json_success( array( 'message' => esc_html__( 'Draft deleted successfully', 'coupolic' ) ) );
    }

    /**
     * AJAX: Validate settings
     */
    public function ajax_validate_settings() {
        $this->verify_request();

        $data = $this->sanitize_draft_data( wp_unslash( $_POST ) );
        $errors = array();

        // Validate discount type
        $valid_types = array( 'percent', 'fixed_cart', 'fixed_product' );
        if ( empty( $data['discount_type'] ) || ! in_array( $data['discount_type'], $valid_types, true ) ) {
            $errors['discount_type'] = esc_html__( 'Invalid discount type', 'coupolic' );
        }

        // Validate amount
        if ( empty( $data['coupon_amount'] ) || floatval( $data['coupon_amount'] ) <= 0 ) {
            $errors['coupon_amount'] = esc_html__( 'Coupon amount must be greater than 0', 'coupolic' );
        }

        // Validate quantity
        if ( empty( $data['quantity'] ) || absint( $data['quantity'] ) <= 0 ) {
            $errors['quantity'] = esc_html__( 'Quantity must be greater than 0', 'coupolic' );
        }

        // Check user limits
        $limits_check = $this->check_user_limits( absint( $data['quantity'] ?? 0 ) );
        if ( is_wp_error( $limits_check ) ) {
            $errors['user_limits'] = $limits_check->get_error_message();
        }

        if ( empty( $errors ) ) {
            wp_send_json_success( array( 'message' => esc_html__( 'Settings are valid', 'coupolic' ) ) );
        } else {
            wp_send_json_error( array( 'errors' => $errors ) );
        }
    }

    /**
     * AJAX: Check user limits
     */
    public function ajax_check_user_limits() {
        $this->verify_request();

        $limits = $this->check_user_limits( 0 );

        if ( is_wp_error( $limits ) ) {
            wp_send_json_error( array( 'message' => $limits->get_error_message() ) );
        }

        wp_send_json_success( array( 'limits' => $limits ) );
    }

    /**
     * AJAX: Generate coupons (enhanced version)
     */
    public function ajax_generate_coupons() {
        $this->verify_request();

        $data = $this->sanitize_draft_data( wp_unslash( $_POST ) );
        $quantity = absint( $data['quantity'] ?? 10 );

        $limits_check = $this->check_user_limits( $quantity );

        if ( is_wp_error( $limits_check ) ) {
            wp_send_json_error( array( 'message' => $limits_check->get_error_message() ) );
        }

        // Generate unique batch ID
        $batch_id = 'CMP_' . time() . '_' . rand( 1000, 9999 );

        // Initialize progress tracking
        update_option( 'coupolic_progress_' . $batch_id, array(
            'current' => 0,
            'total' => $quantity,
            'status' => 'initializing',
            'message' => esc_html__( 'Initializing generation...', 'coupolic' ),
            'percentage' => 0,
        ) );

        // Start generation in background
        $generator = new Coupolic_Generator();
        $result = $generator->generate_bulk_coupons( $data, $batch_id );

        if ( $result['success'] ) {
            wp_send_json_success( array(
                'batch_id' => $batch_id,
                'count' => $result['count'],
                'message' => esc_html__( 'Generation completed successfully', 'coupolic' )
            ) );
        } else {
            wp_send_json_error( array(
                'message' => $result['error'],
                'batch_id' => $batch_id
            ) );
        }
    }

    /**
     * AJAX: Check progress
     */
    public function ajax_check_progress() {
        $this->verify_request();

        $batch_id = isset( $_POST['batch_id'] ) ? sanitize_text_field( $_POST['batch_id'] ) : '';

        if ( ! $batch_id ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid batch ID', 'coupolic' ) ) );
        }

        $progress = get_option( 'coupolic_progress_' . $batch_id );

        if ( ! $progress ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Progress not found', 'coupolic' ) ) );
        }

        wp_send_json_success( array( 'progress' => $progress ) );
    }

    /**
     * AJAX: Get logs
     */
    public function ajax_get_logs() {
        $this->verify_request();

        global $wpdb;
        $table_name = $wpdb->prefix . 'coupolic_logs';

        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20;
        $offset = ( $page - 1 ) * $per_page;

        $where = "WHERE 1=1";
        $params = array();

        // Filter by status
        if ( isset( $_POST['status'] ) && ! empty( $_POST['status'] ) ) {
            $status = sanitize_text_field( $_POST['status'] );
            $where .= " AND status = %s";
            $params[] = $status;
        }

        // Filter by user
        if ( isset( $_POST['user_id'] ) && ! empty( $_POST['user_id'] ) ) {
            $user_id = absint( $_POST['user_id'] );
            $where .= " AND user_id = %d";
            $params[] = $user_id;
        }

        // Search
        if ( isset( $_POST['search'] ) && ! empty( $_POST['search'] ) ) {
            $search = '%' . $wpdb->esc_like( sanitize_text_field( $_POST['search'] ) ) . '%';
            $where .= " AND (batch_id LIKE %s OR user_login LIKE %s)";
            $params[] = $search;
            $params[] = $search;
        }

        $query = "SELECT * FROM $table_name $where ORDER BY generation_time DESC LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;

        if ( ! empty( $params ) ) {
            $logs = $wpdb->get_results( $wpdb->prepare( $query, $params ) );
        } else {
            $logs = $wpdb->get_results( $query );
        }

        $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where" );

        wp_send_json_success( array(
            'logs' => $logs,
            'total' => (int) $total,
            'pages' => ceil( $total / $per_page )
        ) );
    }

    /**
     * AJAX: Get batch details
     */
    public function ajax_get_batch_details() {
        $this->verify_request();

        $batch_id = isset( $_POST['batch_id'] ) ? sanitize_text_field( $_POST['batch_id'] ) : '';

        if ( ! $batch_id ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid batch ID', 'coupolic' ) ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'coupolic_logs';

        $log = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE batch_id = %s",
            $batch_id
        ) );

        if ( ! $log ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Batch not found', 'coupolic' ) ) );
        }

        $log->settings = json_decode( $log->settings, true );
        $log->coupon_codes = json_decode( $log->coupon_codes, true );

        wp_send_json_success( array( 'batch' => $log ) );
    }

    /**
     * AJAX: Delete batch
     */
    public function ajax_delete_batch() {
        $this->verify_request();

        $batch_id = isset( $_POST['batch_id'] ) ? sanitize_text_field( $_POST['batch_id'] ) : '';

        if ( ! $batch_id ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid batch ID', 'coupolic' ) ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'coupolic_logs';

        // Get batch details
        $log = $wpdb->get_row( $wpdb->prepare(
            "SELECT coupon_codes FROM $table_name WHERE batch_id = %s",
            $batch_id
        ) );

        if ( ! $log ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Batch not found', 'coupolic' ) ) );
        }

        $coupon_codes = json_decode( $log->coupon_codes, true );
        $deleted_count = 0;

        // Delete associated coupons
        if ( is_array( $coupon_codes ) ) {
            foreach ( $coupon_codes as $coupon ) {
                if ( isset( $coupon['id'] ) ) {
                    $result = wp_delete_post( $coupon['id'], true );
                    if ( $result ) {
                        $deleted_count++;
                    }
                }
            }
        }

        // Delete log entry
        $wpdb->delete( $table_name, array( 'batch_id' => $batch_id ), array( '%s' ) );

        wp_send_json_success( array(
            'message' => sprintf( esc_html__( 'Batch deleted successfully. %d coupons removed.', 'coupolic' ), $deleted_count ),
            'deleted_count' => $deleted_count
        ) );
    }

    /**
     * AJAX: Export batch
     */
    public function ajax_export_batch() {
        $this->verify_request();

        $batch_id = isset( $_POST['batch_id'] ) ? sanitize_text_field( $_POST['batch_id'] ) : '';
        $format = isset( $_POST['format'] ) ? sanitize_text_field( $_POST['format'] ) : 'csv';

        if ( ! $batch_id ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid batch ID', 'coupolic' ) ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'coupolic_logs';

        $log = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE batch_id = %s",
            $batch_id
        ) );

        if ( ! $log ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Batch not found', 'coupolic' ) ) );
        }

        if ( $format === 'json' ) {
            $data = array(
                'batch_id' => $log->batch_id,
                'generation_time' => $log->generation_time,
                'settings' => json_decode( $log->settings, true ),
                'coupon_codes' => json_decode( $log->coupon_codes, true ),
            );

            wp_send_json_success( array( 'data' => json_encode( $data ) ) );
        } else {
            // CSV format
            $coupon_codes = json_decode( $log->coupon_codes, true );
            $settings = json_decode( $log->settings, true );
            $csv = "Coupon Code,Discount Type,Amount,Expiry Date\n";

            if ( is_array( $coupon_codes ) ) {
                foreach ( $coupon_codes as $coupon ) {
                    $csv .= sprintf( "%s,%s,%s,%s\n",
                        $coupon['code'],
                        $settings['discount_type'] ?? '',
                        $settings['coupon_amount'] ?? '',
                        $settings['expiry_date'] ?? ''
                    );
                }
            }

            wp_send_json_success( array( 'data' => $csv ) );
        }
    }

    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        $this->verify_request();

        // Only super admins can modify settings
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions', 'coupolic' ) ) );
        }

        $retention = isset( $_POST['log_retention'] ) ? absint( $_POST['log_retention'] ) : 90;
        $schedule = isset( $_POST['cleanup_schedule'] ) ? sanitize_text_field( $_POST['cleanup_schedule'] ) : 'daily';

        update_option( 'coupolic_log_retention', $retention );
        update_option( 'coupolic_cleanup_schedule', $schedule );

        wp_send_json_success( array( 'message' => esc_html__( 'Settings saved successfully', 'coupolic' ) ) );
    }
}