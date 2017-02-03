<?php

/**
==========================
     Enqueue Scripts
==========================
*/
if (!defined('ABSPATH')) {
	return;
}
function xoo_wishlist_admin_script($hook){
	if('toplevel_page_xoo_wishlist' != $hook){
		return;
	}
	wp_enqueue_style('xoo_wishlist_admin_css',plugins_url('/css/xoo_wishlist_admin_css.css',__FILE__));
	wp_enqueue_script('xoo_wishlist_admin_js',plugins_url('/js/xoo_wishlist_admin_js.js',__FILE__),array('jquery'),'1.0.0',true);
	wp_localize_script('xoo_wishlist_admin_js','xoo_admin_localize',
		array(
			'ajaxurl' =>admin_url(). 'admin-ajax.php'
		));
}
add_action( 'admin_enqueue_scripts', 'xoo_wishlist_admin_script' );
?>