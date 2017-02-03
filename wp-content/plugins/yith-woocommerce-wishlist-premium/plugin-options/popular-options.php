<?php
/**
 * Lists options page
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCWL' ) ) {
	exit;
} // Exit if accessed directly

return array(
	'popular' => array(
		'table' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wcwl_popular_table'
		)
	)
);