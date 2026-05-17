# Coupolic Comprehensive Coupon Generator Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a comprehensive 6-step wizard-based coupon generator system for WooCommerce with detailed logging, user restrictions, and batch management capabilities.

**Architecture:** Vue.js frontend with Composition API using provide/inject pattern for state management, WordPress backend with custom database table for optimized logging, transaction-based coupon generation with rollback capability, and role-based access control with configurable limits.

**Tech Stack:** Vue 3 (Composition API, long-form functions only), Vue Router, WordPress 6.x, WooCommerce 8.x, Custom MySQL tables, AJAX/REST API, PHP 8.x

---

## File Structure Map

### Backend Files (WordPress/PHP)
- `includes/class-coupolic-install.php` - Database installation and updates
- `includes/class-coupolic-logs.php` - Logs management class
- `includes/class-coupolic-api.php` - AJAX handlers and API endpoints
- `includes/class-coupolic-generator.php` - Enhanced generator with rollback
- `includes/class-coupolic-admin.php` - Updated admin menu structure

### Frontend Files (Vue.js)
- `ui/src/components/wizard/CouponWizard.vue` - Master wizard component
- `ui/src/components/wizard/WizardTabs.vue` - Tab navigation
- `ui/src/components/wizard/WizardNavigation.vue` - Back/Next/Save buttons
- `ui/src/components/wizard/steps/BasicSettings.vue` - Step 1 component
- `ui/src/components/wizard/steps/DateShipping.vue` - Step 2 component
- `ui/src/components/wizard/steps/UsageRestrictions.vue` - Step 3 component
- `ui/src/components/wizard/steps/SpendingRules.vue` - Step 4 component
- `ui/src/components/wizard/steps/GeneratorOptions.vue` - Step 5 component
- `ui/src/components/wizard/steps/ReviewGenerate.vue` - Step 6 component
- `ui/src/components/logs/LogsView.vue` - Main logs interface
- `ui/src/components/logs/LogsTable.vue` - Logs data table
- `ui/src/components/logs/LogDetails.vue` - Detailed batch view
- `ui/src/components/logs/LogsSettings.vue` - Admin configuration
- `ui/src/components/shared/ProgressBar.vue` - Generation progress
- `ui/src/components/shared/CouponSummary.vue` - Review summary
- `ui/src/composables/useWizardState.js` - Wizard state management
- `ui/src/composables/useValidation.js` - Validation logic
- `ui/src/composables/useCouponGeneration.js` - Generation API calls
- `ui/src/router/index.js` - Updated routes

---

## Phase 1: Foundation

### Task 1: Create database installation class

**Files:**
- Create: `includes/class-coupolic-install.php`
- Modify: `includes/class-coupolic-admin.php:22-27` (Add installation hook)

- [ ] **Step 1: Create the installation class file**

```php
<?php
/**
 * Database installation and updates for Coupolic
 *
 * @package Coupolic
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Installation class
 */
class Coupolic_Install {

    /**
     * Database version
     */
    const DB_VERSION = '1.0.0';

    /**
     * Initialize installation
     */
    public function __construct() {
        register_activation_hook( COUPOLIC_FILE, array( $this, 'activate' ) );
        add_action( 'admin_init', array( $this, 'check_updates' ) );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        $this->create_logs_table();
        $this->set_default_options();
        update_option( 'coupolic_db_version', self::DB_VERSION );
    }

    /**
     * Create custom logs table
     */
    private function create_logs_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'coupolic_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            batch_id varchar(50) NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            user_login varchar(100) NOT NULL,
            generation_time datetime NOT NULL,
            coupon_count int(11) NOT NULL,
            success_count int(11) NOT NULL,
            failed_count int(11) DEFAULT 0,
            status varchar(20) NOT NULL,
            settings longtext NOT NULL,
            coupon_codes longtext,
            error_message text,
            cleanup_date datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY batch_id (batch_id),
            KEY user_id (user_id),
            KEY status (status),
            KEY cleanup_date (cleanup_date)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Set default options
     */
    private function set_default_options() {
        if ( get_option( 'coupolic_log_retention' ) === false ) {
            update_option( 'coupolic_log_retention', 90 );
        }

        if ( get_option( 'coupolic_user_limits' ) === false ) {
            $default_limits = array(
                'administrator' => array(
                    'daily_limit' => -1,
                    'batch_limit' => -1,
                    'can_modify_limits' => true
                ),
                'shop_manager' => array(
                    'daily_limit' => 500,
                    'batch_limit' => 100,
                    'can_modify_limits' => false
                ),
                'editor' => array(
                    'daily_limit' => 100,
                    'batch_limit' => 50,
                    'can_modify_limits' => false
                )
            );
            update_option( 'coupolic_user_limits', $default_limits );
        }

        if ( get_option( 'coupolic_cleanup_schedule' ) === false ) {
            update_option( 'coupolic_cleanup_schedule', 'daily' );
        }
    }

    /**
     * Check for database updates
     */
    public function check_updates() {
        $current_version = get_option( 'coupolic_db_version' );

        if ( $current_version !== self::DB_VERSION ) {
            $this->create_logs_table();
            update_option( 'coupolic_db_version', self::DB_VERSION );
        }
    }
}
```

- [ ] **Step 2: Update admin class to initialize installation**

Add to `class-coupolic-admin.php` constructor after line 23:
```php
// Initialize installation
require_once COUPOLIC_PATH . 'includes/class-coupolic-install.php';
new Coupolic_Install();
```

- [ ] **Step 3: Activate plugin to test installation**

Run: Go to WordPress admin → Plugins → Activate Coupolic
Expected: Database table `wp_coupolic_logs` created, options set in wp_options table

- [ ] **Step 4: Verify table creation**

Run SQL check:
```sql
SHOW TABLES LIKE 'wp_coupolic_logs';
```
Expected: Table exists with proper structure

- [ ] **Step 5: Commit foundation**

```bash
git add includes/class-coupolic-install.php includes/class-coupolic-admin.php
git commit -m "feat: add database installation class with custom logs table"
```

### Task 2: Update admin menu structure

**Files:**
- Modify: `includes/class-coupolic-admin.php:32-50`

- [ ] **Step 1: Update admin menu method**

Replace existing `add_admin_menu` method with:
```php
/**
 * Add admin menu
 */
public function add_admin_menu() {
    // Main menu
    add_menu_page(
        esc_html__( 'Coupolic', 'coupolic' ),
        esc_html__( 'Coupolic', 'coupolic' ),
        'manage_woocommerce',
        'coupolic',
        array( $this, 'render_admin_page' ),
        'dashicons-tickets-alt',
        58
    );

    // Coupon Generator submenu
    add_submenu_page(
        'coupolic',
        esc_html__( 'Coupon Generator', 'coupolic' ),
        esc_html__( 'Coupon Generator', 'coupolic' ),
        'manage_woocommerce',
        'coupolic'
    );

    // Logs & History submenu
    add_submenu_page(
        'coupolic',
        esc_html__( 'Logs & History', 'coupolic' ),
        esc_html__( 'Logs & History', 'coupolic' ),
        'manage_woocommerce',
        'coupolic-logs',
        array( $this, 'render_logs_page' )
    );

    // Settings submenu
    add_submenu_page(
        'coupolic',
        esc_html__( 'Settings', 'coupolic' ),
        esc_html__( 'Settings', 'coupolic' ),
        'manage_options',
        'coupolic-settings',
        array( $this, 'render_settings_page' )
    );
}
```

- [ ] **Step 2: Add logs page render method**

Add after `render_admin_page` method:
```php
/**
 * Render logs page
 */
public function render_logs_page() {
    ?>
    <div class="wrap coupolic-wrap">
        <h1><?php esc_html_e( 'Coupolic: Logs & History', 'coupolic' ); ?></h1>
        <div id="coupolic-logs-app" class="coupolic-logs-container"></div>
    </div>
    <?php
}
```

- [ ] **Step 3: Add settings page render method**

Add after logs page method:
```php
/**
 * Render settings page
 */
public function render_settings_page() {
    ?>
    <div class="wrap coupolic-wrap">
        <h1><?php esc_html_e( 'Coupolic: Settings', 'coupolic' ); ?></h1>
        <div id="coupolic-settings-app" class="coupolic-settings-container"></div>
    </div>
    <?php
}
```

- [ ] **Step 4: Update enqueue logic for new pages**

Update `enqueue_admin_assets` method condition at line 56:
```php
public function enqueue_admin_assets( $hook ) {
    $valid_hooks = array(
        'toplevel_page_coupolic',
        'coupolic_page_coupolic-logs',
        'coupolic_page_coupolic-settings'
    );

    if ( ! in_array( $hook, $valid_hooks, true ) ) {
        return;
    }

    // ... rest of existing enqueuing logic
```

- [ ] **Step 5: Test menu structure**

Go to: WordPress admin → Coupolic menu
Expected: See main page + Logs & History + Settings submenus

- [ ] **Step 6: Commit menu structure**

```bash
git add includes/class-coupolic-admin.php
git commit -m "feat: add logs and settings submenu pages"
```

---

## Phase 2: Backend API

### Task 3: Create API class with AJAX handlers

**Files:**
- Create: `includes/class-coupolic-api.php`
- Modify: `includes/class-coupolic-admin.php:26` (Initialize API class)

- [ ] **Step 1: Create API class with AJAX endpoints**

```php
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
            $global $wpdb;

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
        $limits_check = $this->check_user_limits( absint( $data['quantity'] ?? 10 ) );

        if ( is_wp_error( $limits_check ) ) {
            wp_send_json_error( array( 'message' => $limits_check->get_error_message() ) );
        }

        // Generate unique batch ID
        $batch_id = 'CMP_' . time() . '_' . rand( 1000, 9999 );

        // Store generation progress
        update_option( 'coupolic_progress_' . $batch_id, array(
            'current' => 0,
            'total' => absint( $data['quantity'] ),
            'status' => 'initializing',
            'message' => esc_html__( 'Initializing generation...', 'coupolic' )
        ) );

        wp_send_json_success( array(
            'batch_id' => $batch_id,
            'message' => esc_html__( 'Generation started', 'coupolic' )
        ) );
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
            $csv = "Coupon Code,Discount Type,Amount,Expiry Date\n";

            if ( is_array( $coupon_codes ) ) {
                foreach ( $coupon_codes as $coupon ) {
                    $csv .= sprintf( "%s,%s,%s,%s\n",
                        $coupon['code'],
                        $log->settings['discount_type'] ?? '',
                        $log->settings['coupon_amount'] ?? '',
                        $log->settings['expiry_date'] ?? ''
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
```

- [ ] **Step 2: Initialize API class in admin**

Add to `class-coupolic-admin.php` constructor after installation initialization:
```php
// Initialize API
require_once COUPOLIC_PATH . 'includes/class-coupolic-api.php';
new Coupolic_API();
```

- [ ] **Step 3: Test nonce verification**

Run browser test: Make AJAX request without nonce
Expected: Error response "Security check failed"

- [ ] **Step 4: Test user limit check**

Run browser test: Call `coupolic_check_user_limits` as admin
Expected: Success with user limits array

- [ ] **Step 5: Commit API foundation**

```bash
git add includes/class-coupolic-api.php includes/class-coupolic-admin.php
git commit -m "feat: add API class with AJAX handlers and user restrictions"
```

### Task 4: Enhance generator with rollback

**Files:**
- Modify: `includes/class-coupolic-generator.php:25-83` (Replace generate_bulk_coupons method)

- [ ] **Step 1: Replace generation method with rollback version**

```php
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

            // Update progress
            $this->update_progress( $batch_id, $i, $quantity, 'generating',
                sprintf( 'Generated coupon %d of %d', $i, $quantity )
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

    // Product restrictions
    if ( ! empty( $data['product_ids'] ) ) {
        update_post_meta( $coupon_id, 'product_ids', explode( ',', $data['product_ids'] ) );
    } else {
        update_post_meta( $coupon_id, 'product_ids', array() );
    }

    if ( ! empty( $data['exclude_product_ids'] ) ) {
        update_post_meta( $coupon_id, 'exclude_product_ids', explode( ',', $data['exclude_product_ids'] ) );
    } else {
        update_post_meta( $coupon_id, 'exclude_product_ids', array() );
    }

    // Category restrictions
    if ( ! empty( $data['product_categories'] ) ) {
        update_post_meta( $coupon_id, 'product_categories', explode( ',', $data['product_categories'] ) );
    } else {
        update_post_meta( $coupon_id, 'product_categories', array() );
    }

    if ( ! empty( $data['exclude_product_categories'] ) ) {
        update_post_meta( $coupon_id, 'exclude_product_categories', explode( ',', $data['exclude_product_categories'] ) );
    } else {
        update_post_meta( $coupon_id, 'exclude_product_categories', array() );
    }

    // Brand restrictions (custom taxonomy)
    if ( ! empty( $data['product_brands'] ) ) {
        update_post_meta( $coupon_id, 'product_brands', explode( ',', $data['product_brands'] ) );
    }

    if ( ! empty( $data['exclude_product_brands'] ) ) {
        update_post_meta( $coupon_id, 'exclude_product_brands', explode( ',', $data['exclude_product_brands'] ) );
    }

    // Email restrictions
    if ( ! empty( $data['customer_email'] ) ) {
        update_post_meta( $coupon_id, 'customer_email', explode( ',', $data['customer_email'] ) );
    } else {
        update_post_meta( $coupon_id, 'customer_email', array() );
    }

    // Additional settings
    update_post_meta( $coupon_id, 'free_shipping', ( $data['free_shipping'] ?? false ) ? 'yes' : 'no' );
    update_post_meta( $coupon_id, 'exclude_sale_items', ( $data['exclude_sale_items'] ?? false ) ? 'yes' : 'no' );
    update_post_meta( $coupon_id, 'usage_limit_per_user', absint( $data['usage_limit_per_user'] ?? 0 ) );
}

/**
 * Update generation progress
 */
private function update_progress( $batch_id, $current, $total, $status, $message ) {
    update_option( 'coupolic_progress_' . $batch_id, array(
        'current' => $current,
        'total' => $total,
        'status' => $status,
        'message' => $message,
        'percentage' => ( $current / $total ) * 100,
    ) );

    // Set option to expire in 1 hour
    set_transient( 'coupolic_progress_' . $batch_id, array(
        'current' => $current,
        'total' => $total,
        'status' => $status,
        'message' => $message,
        'percentage' => ( $current / $total ) * 100,
    ), 3600 );
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
```

- [ ] **Step 2: Update AJAX handler to use new method**

Update `ajax_generate_coupons` method in `class-coupolic-api.php`:
```php
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
```

- [ ] **Step 3: Test generation with rollback**

Run browser test: Generate 5 coupons with valid settings
Expected: Success response, coupons created in WooCommerce

- [ ] **Step 4: Test rollback scenario**

Run browser test: Generate with invalid product ID
Expected: Failure response, no coupons created, rollback successful

- [ ] **Step 5: Commit enhanced generator**

```bash
git add includes/class-coupolic-generator.php includes/class-coupolic-api.php
git commit -m "feat: enhance generator with rollback capability and progress tracking"
```

---

## Phase 3: Frontend Foundation

### Task 5: Update router with new routes

**Files:**
- Modify: `ui/src/router/index.js`

- [ ] **Step 1: Add new routes for wizard and logs**

```javascript
import { createRouter, createWebHashHistory } from 'vue-router'

import GeneralPage from '@/pages/GeneralOptions.vue'
import CouponOptions from '@/pages/CouponOptions.vue'
import GeneratorOptions from '@/pages/GeneratorOptions.vue'
import GeneratingCoupons from '@/pages/GeneratingCoupons.vue'
import ExportOptions from '@/pages/ExportOptions.vue'

// NEW: Import wizard components
import CouponWizard from '@/components/wizard/CouponWizard.vue'

// NEW: Import logs components
import LogsView from '@/components/logs/LogsView.vue'
import LogsSettings from '@/components/logs/LogsSettings.vue'

import General from '@/pages/GeneralOptions/General.vue'
import UsageRestriction from '@/pages/GeneralOptions/UsageRestriction.vue'
import UsageLimit from '@/pages/GeneralOptions/UsageLimit.vue'

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      redirect: { path: '/coupon-wizard' },
    },
    // NEW: Coupon wizard route
    {
      path: '/coupon-wizard',
      name: 'coupon-wizard',
      component: CouponWizard,
    },
    // NEW: Logs routes
    {
      path: '/logs',
      name: 'logs',
      component: LogsView,
    },
    {
      path: '/logs/settings',
      name: 'logs-settings',
      component: LogsSettings,
    },
    {
      path: '/general-options',
      name: 'general-options',
      redirect: { path: '/general-options/general' },
      component: GeneralPage,
      children: [
        {
          path: 'general',
          name: 'general-options-general',
          component: General,
        },
        {
          path: 'restriction',
          name: 'general-options-restriction',
          component: UsageRestriction,
        },
        {
          path: 'limit',
          name: 'general-options-limit',
          component: UsageLimit,
        },
      ]
    },
    {
      path: '/coupon-options',
      name: 'coupon-options',
      component: CouponOptions,
    },
    {
      path: '/generator-options',
      name: 'generator-options',
      component: GeneratorOptions,
    },
    {
      path: '/generating-coupons',
      name: 'generating-coupons',
      component: GeneratingCoupons,
    },
    {
      path: '/export-options',
      name: 'export-options',
      component: ExportOptions,
    },
  ],
})

export default router
```

- [ ] **Step 2: Test new routes**

Run: npm run dev → Navigate to /coupon-wizard and /logs
Expected: Routes load without 404 errors

- [ ] **Step 3: Commit router updates**

```bash
git add ui/src/router/index.js
git commit -m "feat: add routes for coupon wizard and logs interface"
```

### Task 6: Create wizard state composable

**Files:**
- Create: `ui/src/composables/useWizardState.js`

- [ ] **Step 1: Create wizard state management composable**

```javascript
import { provide, inject, ref, reactive, computed } from 'vue'

const WIZARD_STATE_KEY = 'wizardState'

export function useWizardState() {
  // Inject existing state or create new
  const existingState = inject(WIZARD_STATE_KEY, null)

  if (existingState) {
    return existingState
  }

  // Reactive state
  const currentStep = ref(1)
  const isGenerating = ref(false)
  const generationProgress = ref({
    current: 0,
    total: 0,
    status: '',
    message: '',
    percentage: 0,
  })

  // Form data using long-form functions
  const formData = reactive({
    // Basic settings
    discount_type: 'fixed_cart',
    coupon_amount: 0,
    description: '',
    free_shipping: false,

    // Date & shipping
    expiry_date: '',

    // Usage restrictions
    minimum_amount: '',
    maximum_amount: '',
    individual_use: false,
    exclude_sale_items: false,
    product_ids: [],
    exclude_product_ids: [],
    product_categories: [],
    exclude_product_categories: [],
    product_brands: [],
    exclude_product_brands: [],
    customer_email: '',

    // Generator options
    prefix: 'COUPON',
    character_count: 5,
    quantity: 10,

    // Usage limits
    usage_limit: 1,
    usage_limit_per_user: 0,
  })

  // User limits
  const userLimits = ref({
    daily_limit: 100,
    batch_limit: 50,
    remaining_daily: 100,
  })

  // Draft management
  const draftId = ref(null)
  const hasUnsavedChanges = ref(false)

  // Computed properties
  const canGoNext = computed(function() {
    return currentStep.value < 6
  })

  const canGoBack = computed(function() {
    return currentStep.value > 1
  })

  const isLastStep = computed(function() {
    return currentStep.value === 6
  })

  const progressPercentage = computed(function() {
    return Math.round((currentStep.value / 6) * 100)
  })

  // Methods using long-form functions
  function setCurrentStep(step) {
    if (step >= 1 && step <= 6) {
      currentStep.value = step
    }
  }

  function goToNextStep() {
    if (canGoNext.value) {
      currentStep.value++
    }
  }

  function goToPreviousStep() {
    if (canGoBack.value) {
      currentStep.value--
    }
  }

  function updateFormData(field, value) {
    if (field in formData) {
      formData[field] = value
      hasUnsavedChanges.value = true
    }
  }

  function updateMultipleFields(data) {
    Object.keys(data).forEach(function(key) {
      if (key in formData) {
        formData[key] = data[key]
      }
    })
    hasUnsavedChanges.value = true
  }

  function resetFormData() {
    Object.assign(formData, {
      discount_type: 'fixed_cart',
      coupon_amount: 0,
      description: '',
      free_shipping: false,
      expiry_date: '',
      minimum_amount: '',
      maximum_amount: '',
      individual_use: false,
      exclude_sale_items: false,
      product_ids: [],
      exclude_product_ids: [],
      product_categories: [],
      exclude_product_categories: [],
      product_brands: [],
      exclude_product_brands: [],
      customer_email: '',
      prefix: 'COUPON',
      character_count: 5,
      quantity: 10,
      usage_limit: 1,
      usage_limit_per_user: 0,
    })
    currentStep.value = 1
    hasUnsavedChanges.value = false
  }

  function setUserLimits(limits) {
    userLimits.value = limits
  }

  function setGenerating(status) {
    isGenerating.value = status
  }

  function updateGenerationProgress(progress) {
    generationProgress.value = progress
  }

  function setDraftId(id) {
    draftId.value = id
  }

  function clearUnsavedChanges() {
    hasUnsavedChanges.value = false
  }

  // Provide state for child components
  const state = {
    // State
    currentStep,
    formData,
    isGenerating,
    generationProgress,
    userLimits,
    draftId,
    hasUnsavedChanges,

    // Computed
    canGoNext,
    canGoBack,
    isLastStep,
    progressPercentage,

    // Methods
    setCurrentStep,
    goToNextStep,
    goToPreviousStep,
    updateFormData,
    updateMultipleFields,
    resetFormData,
    setUserLimits,
    setGenerating,
    updateGenerationProgress,
    setDraftId,
    clearUnsavedChanges,
  }

  provide(WIZARD_STATE_KEY, state)

  return state
}
```

- [ ] **Step 2: Test state composable**

Create test component that uses composable and logs state changes
Expected: State updates properly, methods work as expected

- [ ] **Step 3: Commit state management**

```bash
git add ui/src/composables/useWizardState.js
git commit -m "feat: add wizard state management composable with long-form functions"
```

### Task 7: Create validation composable

**Files:**
- Create: `ui/src/composables/useValidation.js`

- [ ] **Step 1: Create validation composable**

```javascript
import { ref, computed } from 'vue'

export function useValidation() {
  const errors = ref({})
  const warnings = ref({})

  // Validation rules for each field
  const validationRules = {
    discount_type: {
      required: true,
      validate: function(value) {
        const validTypes = ['percent', 'fixed_cart', 'fixed_product']
        return validTypes.includes(value)
      },
      message: 'Please select a valid discount type',
    },
    coupon_amount: {
      required: true,
      validate: function(value) {
        return !isNaN(value) && parseFloat(value) > 0
      },
      message: 'Coupon amount must be greater than 0',
    },
    quantity: {
      required: true,
      validate: function(value) {
        return !isNaN(value) && parseInt(value) > 0
      },
      message: 'Quantity must be greater than 0',
    },
    prefix: {
      required: false,
      validate: function(value) {
        return value === '' || /^[A-Z0-9_-]+$/i.test(value)
      },
      message: 'Prefix can only contain letters, numbers, hyphens, and underscores',
    },
    character_count: {
      required: true,
      validate: function(value) {
        const count = parseInt(value)
        return !isNaN(count) && count >= 5 && count <= 20
      },
      message: 'Character count must be between 5 and 20',
    },
    expiry_date: {
      required: false,
      validate: function(value) {
        if (value === '') return true
        const selectedDate = new Date(value)
        const today = new Date()
        today.setHours(0, 0, 0, 0)
        return selectedDate >= today
      },
      message: 'Expiry date must be today or in the future',
    },
    minimum_amount: {
      required: false,
      validate: function(value) {
        return value === '' || (!isNaN(value) && parseFloat(value) >= 0)
      },
      message: 'Minimum amount must be 0 or greater',
    },
    maximum_amount: {
      required: false,
      validate: function(value, formData) {
        if (value === '') return true
        if (isNaN(value) || parseFloat(value) <= 0) return false
        if (formData.minimum_amount && formData.minimum_amount !== '') {
          return parseFloat(value) > parseFloat(formData.minimum_amount)
        }
        return true
      },
      message: 'Maximum amount must be greater than minimum amount',
    },
  }

  // Validate single field
  function validateField(fieldName, value, formData = {}) {
    const rule = validationRules[fieldName]
    if (!rule) return true

    // Check required fields
    if (rule.required && (value === '' || value === null || value === undefined)) {
      errors.value[fieldName] = rule.message
      return false
    }

    // Skip validation for empty optional fields
    if (!rule.required && (value === '' || value === null || value === undefined)) {
      delete errors.value[fieldName]
      return true
    }

    // Run validation rule
    const isValid = rule.validate(value, formData)
    if (!isValid) {
      errors.value[fieldName] = rule.message
      return false
    }

    delete errors.value[fieldName]
    return true
  }

  // Validate all form data
  function validateAll(formData) {
    let isValid = true
    Object.keys(validationRules).forEach(function(fieldName) {
      const fieldValid = validateField(fieldName, formData[fieldName], formData)
      if (!fieldValid) {
        isValid = false
      }
    })
    return isValid
  }

  // Validate specific step
  function validateStep(stepNumber, formData) {
    const stepFields = {
      1: ['discount_type', 'coupon_amount'],
      2: ['expiry_date'],
      3: [], // Usage restrictions - all optional
      4: ['minimum_amount', 'maximum_amount'],
      5: ['prefix', 'character_count', 'quantity'],
      6: [], // Review step - no validation
    }

    const fields = stepFields[stepNumber] || []
    let isValid = true

    fields.forEach(function(fieldName) {
      const fieldValid = validateField(fieldName, formData[fieldName], formData)
      if (!fieldValid) {
        isValid = false
      }
    })

    return isValid
  }

  // Check for warnings (soft validation)
  function checkWarnings(formData) {
    const foundWarnings = {}

    // Warn if no expiry date set
    if (!formData.expiry_date) {
      foundWarnings.expiry_date = 'Consider setting an expiry date for better security'
    }

    // Warn if no usage restrictions
    if ((!formData.minimum_amount || formData.minimum_amount === '') &&
        (!formData.product_ids || formData.product_ids.length === 0)) {
      foundWarnings.restrictions = 'Consider adding usage restrictions for better control'
    }

    // Warn about large quantities
    if (formData.quantity > 50) {
      foundWarnings.quantity = 'Large batch sizes may take longer to generate'
    }

    warnings.value = foundWarnings
    return foundWarnings
  }

  // Clear all errors
  function clearErrors() {
    errors.value = {}
  }

  // Clear specific field error
  function clearFieldError(fieldName) {
    delete errors.value[fieldName]
  }

  // Computed properties
  const hasErrors = computed(function() {
    return Object.keys(errors.value).length > 0
  })

  const hasWarnings = computed(function() {
    return Object.keys(warnings.value).length > 0
  })

  const errorCount = computed(function() {
    return Object.keys(errors.value).length
  })

  return {
    // State
    errors,
    warnings,

    // Computed
    hasErrors,
    hasWarnings,
    errorCount,

    // Methods
    validateField,
    validateAll,
    validateStep,
    checkWarnings,
    clearErrors,
    clearFieldError,
  }
}
```

- [ ] **Step 2: Test validation logic**

Create test cases for various validation scenarios
Expected: Proper error messages, warnings work correctly

- [ ] **Step 3: Commit validation composable**

```bash
git add ui/src/composables/useValidation.js
git commit -m "feat: add validation composable with step-by-step validation"
```

---

## Phase 4: Wizard Components

### Task 8: Create master wizard component

**Files:**
- Create: `ui/src/components/wizard/CouponWizard.vue`

- [ ] **Step 1: Create master wizard component**

```vue
<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useWizardState } from '@/composables/useWizardState.js'
import { useValidation } from '@/composables/useValidation.js'
import WizardTabs from './WizardTabs.vue'
import WizardNavigation from './WizardNavigation.vue'
import BasicSettings from './steps/BasicSettings.vue'
import DateShipping from './steps/DateShipping.vue'
import UsageRestrictions from './steps/UsageRestrictions.vue'
import SpendingRules from './steps/SpendingRules.vue'
import GeneratorOptions from './steps/GeneratorOptions.vue'
import ReviewGenerate from './steps/ReviewGenerate.vue'

// Initialize wizard state
const wizardState = useWizardState()
const validation = useValidation()
const router = useRouter()

// Load user limits on mount
onMounted(function() {
  loadUserLimits()
  loadExistingDraft()

  // Warn before leaving with unsaved changes
  window.addEventListener('beforeunload', handleBeforeUnload)
})

onUnmounted(function() {
  window.removeEventListener('beforeunload', handleBeforeUnload)
})

function handleBeforeUnload(event) {
  if (wizardState.hasUnsavedChanges) {
    event.preventDefault()
    event.returnValue = ''
  }
}

function loadUserLimits() {
  // Make AJAX request to get user limits
  const data = new FormData()
  data.append('action', 'coupolic_check_user_limits')
  data.append('nonce', coupolic.nonce)

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success) {
      wizardState.setUserLimits(result.data.limits)
    }
  })
  .catch(function(error) {
    console.error('Failed to load user limits:', error)
  })
}

function loadExistingDraft() {
  // Check for saved draft in localStorage
  const savedDraft = localStorage.getItem('coupolic_draft')
  if (savedDraft) {
    try {
      const draftData = JSON.parse(savedDraft)
      wizardState.updateMultipleFields(draftData)
      wizardState.setDraftId(draftData.id)
    } catch (error) {
      console.error('Failed to parse saved draft:', error)
    }
  }
}

function saveDraft() {
  const data = new FormData()
  data.append('action', 'coupolic_save_draft')
  data.append('nonce', coupolic.nonce)

  Object.keys(wizardState.formData).forEach(function(key) {
    const value = wizardState.formData[key]
    if (Array.isArray(value)) {
      data.append(key, JSON.stringify(value))
    } else {
      data.append(key, value)
    }
  })

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success) {
      wizardState.setDraftId(result.data.draft_id)
      wizardState.clearUnsavedChanges()
      alert('Draft saved successfully!')
    } else {
      alert('Failed to save draft: ' + result.data.message)
    }
  })
  .catch(function(error) {
    console.error('Failed to save draft:', error)
    alert('Failed to save draft')
  })
}

function handleNextClick() {
  // Validate current step
  const currentStep = wizardState.currentStep.value
  const isValid = validation.validateStep(currentStep, wizardState.formData)

  if (!isValid) {
    alert('Please fix the errors before proceeding')
    return
  }

  // Check for milestone saves
  if (currentStep === 2 || currentStep === 4) {
    saveDraft()
  }

  wizardState.goToNextStep()
}

function handleBackClick() {
  wizardState.goToPreviousStep()
}

function handleStepClick(stepNumber) {
  // Only allow going back or to next step, not jumping ahead
  if (stepNumber <= wizardState.currentStep.value) {
    wizardState.setCurrentStep(stepNumber)
  } else {
    // Validate all steps up to the target step
    let canProceed = true
    for (let i = wizardState.currentStep.value; i < stepNumber; i++) {
      if (!validation.validateStep(i, wizardState.formData)) {
        canProceed = false
        break
      }
    }

    if (canProceed) {
      wizardState.setCurrentStep(stepNumber)
    } else {
      alert('Please complete and validate current step first')
    }
  }
}

function getCurrentStepComponent() {
  const stepComponents = {
    1: BasicSettings,
    2: DateShipping,
    3: UsageRestrictions,
    4: SpendingRules,
    5: GeneratorOptions,
    6: ReviewGenerate,
  }
  return stepComponents[wizardState.currentStep.value] || BasicSettings
}

function saveToLocalStorage() {
  localStorage.setItem('coupolic_draft', JSON.stringify({
    id: wizardState.draftId.value,
    ...wizardState.formData
  }))
}
</script>

<template>
  <div class="coupolic-wizard">
    <WizardTabs
      :current-step="wizardState.currentStep.value"
      :progress-percentage="wizardState.progressPercentage.value"
      @step-click="handleStepClick"
    />

    <div class="wizard-content">
      <component :is="getCurrentStepComponent()" />
    </div>

    <WizardNavigation
      :can-go-back="wizardState.canGoBack.value"
      :can-go-next="wizardState.canGoNext.value"
      :is-last-step="wizardState.isLastStep.value"
      :current-step="wizardState.currentStep.value"
      :has-unsaved-changes="wizardState.hasUnsavedChanges.value"
      @next="handleNextClick"
      @back="handleBackClick"
      @save-draft="saveDraft"
      @save-local="saveToLocalStorage"
    />
  </div>
</template>

<style scoped>
.coupolic-wizard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.wizard-content {
  min-height: 400px;
  margin: 20px 0;
}
</style>
```

- [ ] **Step 2: Test wizard component navigation**

Run: npm run dev → Navigate to wizard, test step navigation
Expected: Steps change properly, validation works, save draft functions

- [ ] **Step 3: Commit master wizard component**

```bash
git add ui/src/components/wizard/CouponWizard.vue
git commit -m "feat: add master wizard component with state management"
```

### Task 9: Create wizard tabs navigation

**Files:**
- Create: `ui/src/components/wizard/WizardTabs.vue`

- [ ] **Step 1: Create tabs navigation component**

```vue
<script setup>
import { Icon } from '@iconify/vue'

const props = defineProps({
  currentStep: {
    type: Number,
    required: true,
  },
  progressPercentage: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['step-click'])

const steps = [
  { number: 1, name: 'Basic Settings', icon: 'proicons:settings' },
  { number: 2, name: 'Date & Shipping', icon: 'proicons:calendar' },
  { number: 3, name: 'Usage Restrictions', icon: 'proicons:alert-rhombus' },
  { number: 4, name: 'Spending Rules', icon: 'proicons:wallet' },
  { number: 5, name: 'Generator Options', icon: 'proicons:ticket' },
  { number: 6, name: 'Review & Generate', icon: 'proicons:check-circle' },
]

function getStepClass(stepNumber) {
  if (stepNumber === props.currentStep) {
    return 'step-active'
  } else if (stepNumber < props.currentStep) {
    return 'step-completed'
  } else {
    return 'step-pending'
  }
}

function handleStepClick(stepNumber) {
  emit('step-click', stepNumber)
}
</script>

<template>
  <div class="wizard-tabs">
    <div class="progress-bar">
      <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
    </div>

    <div class="tabs-container">
      <div
        v-for="step in steps"
        :key="step.number"
        :class="['tab-item', getStepClass(step.number)]"
        @click="handleStepClick(step.number)"
      >
        <div class="tab-icon">
          <Icon :icon="step.icon" width="20" height="20" />
        </div>
        <div class="tab-content">
          <div class="step-number">Step {{ step.number }}</div>
          <div class="step-name">{{ step.name }}</div>
        </div>
        <div class="tab-status">
          <span v-if="step.number < currentStep" class="completed-badge">✓</span>
          <span v-else-if="step.number === currentStep" class="current-badge">Active</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.wizard-tabs {
  margin-bottom: 30px;
}

.progress-bar {
  height: 4px;
  background-color: #e0e0e0;
  border-radius: 2px;
  margin-bottom: 20px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #4CAF50, #45a049);
  transition: width 0.3s ease;
}

.tabs-container {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.tab-item {
  flex: 1;
  min-width: 150px;
  padding: 15px;
  background: #f5f5f5;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 10px;
  border: 2px solid transparent;
}

.tab-item:hover {
  background: #eeeeee;
}

.tab-item.step-active {
  background: #e8f5e9;
  border-color: #4CAF50;
}

.tab-item.step-completed {
  background: #f1f8f4;
  border-color: #45a049;
}

.tab-item.step-pending {
  opacity: 0.7;
}

.tab-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: white;
}

.step-active .tab-icon {
  background: #4CAF50;
  color: white;
}

.tab-content {
  flex: 1;
}

.step-number {
  font-size: 12px;
  color: #666;
  margin-bottom: 2px;
}

.step-name {
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.tab-status {
  display: flex;
  align-items: center;
}

.completed-badge {
  color: #4CAF50;
  font-weight: bold;
  font-size: 18px;
}

.current-badge {
  background: #4CAF50;
  color: white;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}

@media (max-width: 768px) {
  .tabs-container {
    flex-direction: column;
  }

  .tab-item {
    min-width: 100%;
  }
}
</style>
```

- [ ] **Step 2: Test tabs navigation and styling**

Run: npm run dev → Check tab appearance and click behavior
Expected: Tabs display correctly, clicking updates wizard step

- [ ] **Step 3: Commit tabs component**

```bash
git add ui/src/components/wizard/WizardTabs.vue
git commit -m "feat: add wizard tabs navigation with progress indicator"
```

### Task 10: Create wizard navigation buttons

**Files:**
- Create: `ui/src/components/wizard/WizardNavigation.vue`

- [ ] **Step 1: Create navigation buttons component**

```vue
<script setup>
import { inject } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
  canGoBack: {
    type: Boolean,
    required: true,
  },
  canGoNext: {
    type: Boolean,
    required: true,
  },
  isLastStep: {
    type: Boolean,
    required: true,
  },
  currentStep: {
    type: Number,
    required: true,
  },
  hasUnsavedChanges: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['back', 'next', 'save-draft', 'save-local'])

function handleBackClick() {
  emit('back')
}

function handleNextClick() {
  if (props.isLastStep) {
    emit('generate')
  } else {
    emit('next')
  }
}

function handleSaveDraft() {
  emit('save-draft')
}

function handleSaveLocal() {
  emit('save-local')
}
</script>

<template>
  <div class="wizard-navigation">
    <div class="navigation-left">
      <button
        v-if="hasUnsavedChanges"
        @click="handleSaveLocal"
        class="nav-button save-local-button"
        type="button"
      >
        <Icon icon="proicons:save" width="16" height="16" />
        Save Locally
      </button>

      <button
        @click="handleSaveDraft"
        class="nav-button save-draft-button"
        type="button"
      >
        <Icon icon="proicons:cloud-save" width="16" height="16" />
        Save Draft
      </button>
    </div>

    <div class="navigation-right">
      <button
        v-if="canGoBack"
        @click="handleBackClick"
        class="nav-button back-button"
        type="button"
      >
        <Icon icon="proicons:arrow-left" width="16" height="16" />
        Back
      </button>

      <button
        v-if="canGoNext"
        @click="handleNextClick"
        class="nav-button next-button"
        type="button"
      >
        <Icon v-if="!isLastStep" icon="proicons:arrow-right" width="16" height="16" />
        <Icon v-else icon="proicons:check" width="16" height="16" />
        {{ isLastStep ? 'Generate Coupons' : 'Next' }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.wizard-navigation {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
  margin-top: 30px;
  gap: 20px;
}

.navigation-left,
.navigation-right {
  display: flex;
  gap: 10px;
}

.nav-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.back-button {
  background: white;
  color: #666;
  border: 1px solid #ddd;
}

.back-button:hover:not(:disabled) {
  background: #f5f5f5;
  border-color: #ccc;
}

.next-button {
  background: #4CAF50;
  color: white;
}

.next-button:hover:not(:disabled) {
  background: #45a049;
}

.save-draft-button {
  background: #2196F3;
  color: white;
}

.save-draft-button:hover {
  background: #1976D2;
}

.save-local-button {
  background: #FF9800;
  color: white;
}

.save-local-button:hover {
  background: #F57C00;
}

.nav-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .wizard-navigation {
    flex-direction: column;
    align-items: stretch;
  }

  .navigation-left,
  .navigation-right {
    flex-direction: column;
  }

  .nav-button {
    width: 100%;
    justify-content: center;
  }
}
</style>
```

- [ ] **Step 2: Test navigation button functionality**

Run: npm run dev → Test back, next, save buttons
Expected: Buttons work properly, emit correct events

- [ ] **Step 3: Commit navigation component**

```bash
git add ui/src/components/wizard/WizardNavigation.vue
git commit -m "feat: add wizard navigation buttons with save functionality"
```

---

This implementation plan continues with detailed steps for creating the wizard step components, logs interface, and remaining functionality. Due to the length, I'll create a continuation document for the remaining phases.

Would you like me to continue with the remaining phases (Steps 11-20 covering wizard step components, generation system, and logs interface)?