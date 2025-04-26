<?php

/**
 *
 * @package           Coupolic - Ultimate Coupon Generator for WooCommerce
 * @author            Mainul Sunvi
 * @description       Ultimate Coupon Generator and Management Plugin for WooCommerce
 * @license           GPL-3.0-or-later
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: Coupolic - Ultimate Coupon Generator for WooCommerce
 * Plugin URI: https://msunvi.com
 * Description: Ultimate Coupon Generator and Management Plugin for WooCommerce
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Mainul Sunvi
 * Author URI: https://profiles.wordpress.org/mainulsunvi/
 * Text Domain:
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Update URI: https://msunvi.com
 * Domain Path: /languages
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( !defined( 'COUPOLIC_VERSION' ) ) {
    define( 'COUPOLIC_VERSION', '1.0.0' );
}

if ( !defined( 'COUPOLIC_FILE' ) ) {
    define( 'COUPOLIC_FILE', __FILE__ );
}

if ( !defined( 'COUPOLIC_DIR' ) ) {
    define( 'COUPOLIC_DIR', plugin_dir_path( __FILE__ ) );
}

if ( !defined( 'COUPOLIC_URL' ) ) {
    define( 'COUPOLIC_URL', plugin_dir_url( __FILE__ ) );
}

if ( !defined( 'COUPOLIC_ASSETS_URL' ) ) {
    define( 'COUPOLIC_ASSETS_URL', COUPOLIC_URL . 'assets/' );
}

add_action( 'plugins_loaded', 'coupolic_load_textdomain' );
/**
 * Load plugin textdomain.
 */
function coupolic_load_textdomain() {
    load_plugin_textdomain( 'coupolic', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( "admin_menu", "coupolic_add_admin_menu" );


function add_cors_http_header() {
    header( 'Access-Control-Allow-Origin:  *' );
    header( 'Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE' );
    header( 'Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization' );
}
add_action( 'admin_init', 'add_cors_http_header' );

function coupolic_add_admin_menu() {
    add_menu_page(
        __( 'Coupolic', 'coupolic' ),
        __( 'Coupolic', 'coupolic' ),
        'manage_options',
        'coupolic',
        'coupolic_admin_page',
        'dashicons-tickets-alt',
        58
    );
}

function coupolic_admin_page() {
    ?>
        <div id="coupolic-app" class="wrap"></div>
    <?php
}

function coupolic_admin_scripts() {
    // wp_enqueue_script( 'coupolic-admin-ui', 'http://localhost:5173/src/main.js', array(), time(), false );

    wp_enqueue_script( 'coupolic-admin-ui', COUPOLIC_URL . '/assets/build/assets/coupolic-admin-app-script.js', array(), time(), false );
    wp_enqueue_style( 'coupolic-admin-ui-style', COUPOLIC_URL . '/assets/build/assets/coupolic-admin-app.css', array(), time() );

    wp_enqueue_style( 'coupolic-admin-style', COUPOLIC_URL . '/assets/admin/css/style.css', array(), time() );
}
add_action( 'admin_enqueue_scripts', 'coupolic_admin_scripts' );

function thb_loadScriptAsModule( $tag, $handle, $src ) {
if ( 'coupolic-admin-ui' !== $handle ) {
    return $tag;
}
$tag = '<script id="coupolic-admin-ui" type="module" src="' . esc_url( $src ) . '"></script>';
return $tag;
}
add_filter( 'script_loader_tag', 'thb_loadScriptAsModule', 10, 3 );