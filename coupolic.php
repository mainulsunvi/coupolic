<?php
    /**
     * @package           Coupolic
     * @author            Mainul Sunvi
     * @description       Coupon Management Plugin for WordPress
     * @license           GPL-3.0-or-later
     * @since             1.0.0
     *
     * @wordpress-plugin
     * Plugin Name: Coupolic
     * Plugin URI: https://wordpress.org/plugins/coupolic/
     * Description: Coupon Management Plugin for WordPress
     * Version: 1.0.1
     * Requires at least: 6.0
     * Requires PHP: 7.4
     * Author: Mainul Sunvi
     * Author URI: https://profiles.wordpress.org/mainulsunvi/
     * Text Domain: coupolic
     * License: GPL v3 or later
     * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
     * Domain Path: /lang
     */

    // If this file is called directly, abort.
    if ( !defined( 'ABSPATH' ) ) {
        die;
    }

    if ( ! function_exists( 'get_plugin_data' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    define( 'COUPILIC_DATA', get_plugin_data( __FILE__ ) );


    // Define plugin constants
    define( 'COUPOLIC_VERSION', COUPILIC_DATA['Version'] );
    define( 'COUPOLIC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    define( 'COUPOLIC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    define( 'COUPOLIC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

    /**
     * Check if WooCommerce is active
     */
    function coupolic_check_woocommerce() {
        if ( !class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', 'coupolic_woocommerce_missing_notice' );
            return false;
        }
        return true;
    }

    /**
     * Admin notice for missing WooCommerce
     */
    function coupolic_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e( 'Coupolic requires WooCommerce to be installed and active.', 'coupolic' ); ?></p>
    </div>
    <?php
        }

        /**
         * Load plugin textdomain
         */
        function coupolic_load_textdomain() {
            // $locale = apply_filters( 'plugin_locale', get_locale(), 'coupolic' );

            // load_textdomain( 'coupolic', WP_LANG_DIR . '/coupolic/coupolic-' . $locale . '.mo' );
            // load_plugin_textdomain( 'coupolic', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
        }
        add_action( 'plugins_loaded', 'coupolic_load_textdomain' );

        /**
         * Initialize the plugin
         */
        function coupolic_init() {
            if ( !coupolic_check_woocommerce() ) {
                return;
            }

            // Include required files
            require_once COUPOLIC_PLUGIN_DIR . 'includes/class-coupolic-admin.php';
            require_once COUPOLIC_PLUGIN_DIR . 'includes/class-coupolic-generator.php';

            // Initialize admin class
            if ( is_admin() ) {
                new Coupolic_Admin();
            }
        }
        add_action( 'plugins_loaded', 'coupolic_init', 15 );

        /**
         * Activation hook
         */
        function coupolic_activate() {
            // Check PHP version
            if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
                deactivate_plugins( plugin_basename( __FILE__ ) );
                wp_die( esc_html__( 'Coupolic requires PHP version 7.4 or higher.', 'coupolic' ) );
            }

            // Flush rewrite rules
            flush_rewrite_rules();
        }
        register_activation_hook( __FILE__, 'coupolic_activate' );

        /**
         * Deactivation hook
         */
        function coupolic_deactivate() {
            // Flush rewrite rules
            flush_rewrite_rules();
    }
    register_deactivation_hook( __FILE__, 'coupolic_deactivate' );