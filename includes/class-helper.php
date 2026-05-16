<?php 

/**
 * @package Coupolic
 * @since 1.0.1
 */

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die;
}

class Heper {

	function __construct() {
		add_filter("plugin_action_links", array($this, 'coupolic_plugin_action_links'), 10, 2);
		add_filter( 'woocommerce_enable_setup_wizard', '__return_false' );
	}


	public function coupolic_plugin_action_links($links, $file) {

		if( $file == COUPOLIC_PLUGIN_BASENAME ) {
			$settings_link = '<b><a style="color: #312c85;" href="' . admin_url('admin.php?page=coupolic') . '">' . __('Start Generating Coupons', 'coupolic') . '</a></b>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}



}

new Heper();