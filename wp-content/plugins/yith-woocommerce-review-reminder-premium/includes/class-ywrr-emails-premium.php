<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YWRR_Emails_Premium' ) ) {

	/**
	 * Implements email functions for YWRR plugin
	 *
	 * @class   YWRR_Emails_Premium
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 *
	 */
	class YWRR_Emails_Premium {

		/**
		 * Single instance of the class
		 *
		 * @var \YWRR_Emails_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YWRR_Emails_Premium
		 * @since 1.0.0
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self( $_REQUEST );

			}

			return self::$instance;

		}

		/**
		 * Constructor
		 *
		 * @since   1.0.0
		 * @return  mixed
		 * @author  Alberto Ruggiero
		 */
		public function __construct() {

		}

		/**
		 * Prepares the list of items to review from stored options
		 *
		 * @since   1.0.0
		 *
		 * @param   $order_id int the order id
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_review_list( $order_id ) {

			global $wpdb;

			$criteria       = ( get_option( 'ywrr_request_type' ) ) != 'all' ? get_option( 'ywrr_request_criteria' ) : '';
			$amount         = get_option( 'ywrr_request_number' );
			$args           = array( $order_id, $amount );
			$items          = array();
			$excluded_items = implode( apply_filters( 'ywrr_excluded_items', array( 0 ) ), ', ' );

			switch ( $criteria ) {
				case 'first' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY  a.order_item_id ASC
                    LIMIT     %d
                    ", $args ) );
					break;
				case 'last' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY  a.order_item_id DESC
                    LIMIT     %d
                    ", $args ) );
					break;
				case 'highest_quantity' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY  MAX(CASE WHEN b.meta_key = '_qty' THEN b.meta_value ELSE NULL END) DESC
                    LIMIT     %d
                    ", $args ) );
					break;
				case 'lowest_quantity' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY  MAX(CASE WHEN b.meta_key = '_qty' THEN b.meta_value ELSE NULL END) ASC
                    LIMIT     %d
                    ", $args ) );
					break;
				case 'most_reviewed' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY (SELECT COUNT(*) FROM {$wpdb->prefix}comments WHERE comment_post_ID = MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AND user_id > 0) DESC
                    LIMIT     %d
                    ", $args ) );
					break;
				case 'least_reviewed' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY (SELECT COUNT(*) FROM {$wpdb->prefix}comments WHERE comment_post_ID = MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AND user_id > 0) ASC
                    LIMIT     %d
                    ", $args ) );
					break;
				case 'highest_priced' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY  (MAX(CASE WHEN b.meta_key = '_line_total' THEN b.meta_value ELSE NULL END) / MAX(CASE WHEN b.meta_key = '_qty' THEN b.meta_value ELSE NULL END)) DESC
                    LIMIT     %d
                    ", $args ) );
					break;
				case 'lowest_priced' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY  (MAX(CASE WHEN b.meta_key = '_line_total' THEN b.meta_value ELSE NULL END) / MAX(CASE WHEN b.meta_key = '_qty' THEN b.meta_value ELSE NULL END)) ASC
                    LIMIT     %d
                    ", $args ) );
					break;
				case 'highest_total_value' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY  MAX(CASE WHEN b.meta_key = '_line_total' THEN b.meta_value ELSE NULL END) DESC
                    LIMIT     %d
                    ", $args ) );
					break;
				case 'lowest_total_value' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY  MAX(CASE WHEN b.meta_key = '_line_total' THEN b.meta_value ELSE NULL END) ASC
                    LIMIT     %d
                    ", $args ) );
					break;
				case 'random' :
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY  RAND()
                    LIMIT     %d
                    ", $args ) );
					break;
				default:
					$line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id = b.order_item_id INNER JOIN {$wpdb->prefix}posts c ON  b.meta_value = c.ID
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item' AND (b.meta_key = '_product_id' AND b.meta_value NOT IN ({$excluded_items}) ) AND c.comment_status = 'open'
                    GROUP BY  a.order_item_id
                    ORDER BY  a.order_item_id ASC
                    ", $order_id ) );

			}

			foreach ( $line_items as $item ) {

				$items[ $item->product_id ]['name'] = $item->order_item_name;
				$items[ $item->product_id ]['id']   = $item->product_id;

			}

			return $items;

		}

		/**
		 * Prepares the list of items from selected items in order page
		 *
		 * @since   1.0.0
		 *
		 * @param   $items_to_review array the list of items to request a review
		 * @param   $order_id        int the order id
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_review_list_forced( $items_to_review, $order_id ) {

			$items       = array();
			$order       = wc_get_order( $order_id );
			$order_items = $order->get_items();

			foreach ( $items_to_review as $item ) {

				$product_id = wc_get_order_item_meta( $item, '_product_id' );

				if ( ! YWRR_Emails()->items_has_comments_closed( $product_id ) ) {

					$items[ $product_id ]['name'] = $order_items[ $item ]['name'];
					$items[ $product_id ]['id']   = $product_id;

				}

			}

			return $items;

		}

	}

	/**
	 * Unique access to instance of YWRR_Emails_Premium class
	 *
	 * @return \YWRR_Emails_Premium
	 */
	function YWRR_Emails_Premium() {
		return YWRR_Emails_Premium::get_instance();
	}

}