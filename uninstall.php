<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Coupolic
 * @since 1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Clear any cached data that may be stored
wp_cache_flush();