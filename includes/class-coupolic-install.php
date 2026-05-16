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
        register_activation_hook( COUPOLIC_PLUGIN_BASENAME, array( $this, 'activate' ) );
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