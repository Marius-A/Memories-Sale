<?php

if (!defined('ABSPATH')) {
	return;
}

function xoo_wishlist_setcookie()
{
	if(isset($_POST['xoo_product_id'])){
		$xoo_wishlist_single_add_id = intval($_POST['xoo_product_id']);
	}
	else{
		$xoo_wishlist_single_add_id = '';
	}

	 
	if (isset($_COOKIE['xoo_wishlist_cookie']) && !isset($_POST['xoo_remove_id'])) {
		$xoo_wishlist_ids = $xoo_wishlist_single_add_id . ',' . $_COOKIE['xoo_wishlist_cookie'];
	}
	else {
		$xoo_wishlist_ids = $xoo_wishlist_single_add_id;
	}

	if (isset($_POST['xoo_remove_id'])) {
		$xoo_remove_product = intval($_POST['xoo_remove_id']) . ',';
		if (substr($_COOKIE['xoo_wishlist_cookie'], strlen($_COOKIE['xoo_wishlist_cookie']) - 1) != ',') {
			$cookie_remove_wishlist = $_COOKIE['xoo_wishlist_cookie'] . ',';
		}
		else {
			$cookie_remove_wishlist = $_COOKIE['xoo_wishlist_cookie'];
		}

		$xoo_wishlist_cookie_value = str_replace($xoo_remove_product, '', $cookie_remove_wishlist);
	}
	else {
		$xoo_wishlist_cookie_value = $xoo_wishlist_ids;
	}

	setcookie('xoo_wishlist_cookie', $xoo_wishlist_cookie_value, time() + (86400 * 30) , "/"); // 86400 = 1 day
	
}

add_action('wp_ajax_wishlist_cookie', 'xoo_wishlist_setcookie');
add_action('wp_ajax_nopriv_wishlist_cookie', 'xoo_wishlist_setcookie');


function xoo_wishlist_remove_button(){
	echo '<div class="xoo_wishlist_remove xoo-wishlist-remove-cross xoo_wishlist_button icon-cross" remove-id =  "' . get_the_ID() . '" ></div>';
}


function xoo_wishlist_shortcode(){
	add_action('woocommerce_before_shop_loop_item','xoo_wishlist_remove_button',1);
	remove_action('woocommerce_after_shop_loop_item_title', 'xoo_wishlist_button');
	if(isset($_COOKIE['xoo_wishlist_cookie'])) {
		
		$xoo_wishlist_elements = '<div class="xoo_wishlist_products">';
		$xoo_wishlist_elements .= '<div class ="xoo_wishlist_message"></div>';

		$xoo_wishlist_elements .= do_shortcode('[products ids="' . esc_attr($_COOKIE['xoo_wishlist_cookie']) . '"]');
		$xoo_wishlist_elements .='</div>';
		return $xoo_wishlist_elements;

		/*return '<div class="xoo_wishlist_products">'.do_shortcode('[products ids="' . esc_attr($_COOKIE['xoo_wishlist_cookie']) . '"]').'</div>';
		*/
	}

	else{
		return 'No Product Found';
	}
	
}

add_shortcode('xoo_wishlist', 'xoo_wishlist_shortcode');


?>