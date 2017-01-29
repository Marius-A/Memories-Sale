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
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YWRR_Ajax_Premium' ) ) {

	/**
	 * Implements AJAX for YWRR plugin
	 *
	 * @class   YWRR_Ajax_Premium
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 *
	 */
	class YWRR_Ajax_Premium {

		/**
		 * Constructor
		 *
		 * @since   1.0.0
		 * @return  mixed
		 * @author  Alberto Ruggiero
		 */
		public function __construct() {

			add_action( 'wp_ajax_ywrr_send_request_mail', array( $this, 'send_request_mail' ) );
			add_action( 'wp_ajax_ywrr_reschedule_mail', array( $this, 'reschedule_mail' ) );
			add_action( 'wp_ajax_ywrr_cancel_mail', array( $this, 'cancel_mail' ) );
			add_action( 'wp_ajax_ywrr_mass_schedule', array( $this, 'mass_schedule' ) );

		}

		/**
		 * Send a request mail from order details page
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function send_request_mail() {

			ob_start();
			$order_id        = $_POST['order_id'];
			$items_to_review = json_decode( sanitize_text_field( stripslashes( $_POST['items_to_review'] ) ), true );
			$today           = new DateTime( current_time( 'mysql' ) );
			$pay_date        = new DateTime( $_POST['order_date'] );
			$days            = $pay_date->diff( $today );

			try {

				$email_result = YWRR_Emails()->send_email( $order_id, $days->days, $items_to_review );

				if ( ! $email_result ) {

					wp_send_json( array( 'error' => __( 'There was an error while sending the email', 'yith-woocommerce-review-reminder' ) ) );

				} else {

					if ( $email_result !== true ) {

						throw new Exception( $email_result );

					}

					if ( YWRR_Schedule()->check_exists_schedule( $order_id ) != 0 ) {

						YWRR_Schedule()->change_schedule_status( $order_id, 'sent' );

					}

					wp_send_json( true );
				}

			} catch ( Exception $e ) {

				wp_send_json( array( 'error' => $e->getMessage() ) );

			}

		}

		/**
		 * Reschedule mail from order details page
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function reschedule_mail() {

			ob_start();
			$order_id        = $_POST['order_id'];
			$items_to_review = json_decode( sanitize_text_field( stripslashes( $_POST['items_to_review'] ) ), true );
			$scheduled_date  = date( 'Y-m-d', strtotime( current_time( 'mysql' ) . ' + ' . get_option( 'ywrr_mail_schedule_day' ) . ' days' ) );
			$list            = '';
			$message         = '';

			try {

				if ( ! empty( $items_to_review ) ) {

					$list = YWRR_Emails_Premium()->get_review_list_forced( $items_to_review, $order_id );

					if ( empty( $list ) ) {

						$message = __( 'There are no reviewable items in this order', 'yith-woocommerce-review-reminder' );

					}

				} else {

					if ( YWRR_Schedule()->check_exists_schedule( $order_id ) != 0 ) {

						$message = YWRR_Schedule_Premium()->reschedule( $order_id, $scheduled_date, $list );

					} else {

						$message = YWRR_Schedule()->schedule_mail( $order_id, $list );

					}

				}

				if ( $message != '' ) {

					throw new Exception( $message );

				}

				global $wpdb;

				$schedule = $wpdb->get_var( $wpdb->prepare( "SELECT scheduled_date FROM {$wpdb->prefix}ywrr_email_schedule WHERE order_id = %d AND mail_status = 'pending'", $order_id ) );

				wp_send_json( array( 'success' => true, 'schedule' => $schedule ) );

			} catch ( Exception $e ) {

				wp_send_json( array( 'error' => $e->getMessage() ) );

			}

		}

		/**
		 * Cancel schedule mail from order details page
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function cancel_mail() {

			ob_start();
			$order_id = $_POST['order_id'];

			try {

				if ( YWRR_Schedule()->check_exists_schedule( $order_id ) != 0 ) {

					YWRR_Schedule()->change_schedule_status( $order_id );
					wp_send_json( true );

				} else {

					wp_send_json( 'notfound' );

				}

			} catch ( Exception $e ) {

				wp_send_json( array( 'error' => $e->getMessage() ) );

			}

		}

		/**
		 * Mass schedule mail from options panel
		 *
		 * @since   1.2.3
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function mass_schedule() {

			ob_start();

			try {

				global $wpdb;

				//Get the list of already scheduled orders
				$scheduled_list = $wpdb->get_col( "
                    SELECT    order_id
                    FROM      {$wpdb->prefix}ywrr_email_schedule
                    " );

				//Get never scheduled orders
				$args = array(
					'post_type'      => 'shop_order',
					'post__not_in'   => $scheduled_list,
					'post_parent'    => 0,
					'post_status'    => array( 'wc-completed' ),
					'posts_per_page' => - 1,

				);

				$query = new WP_Query( $args );
				$count = 0;

				if ( $query->have_posts() ) {

					while ( $query->have_posts() ) {

						$count ++;
						$query->the_post();

						YWRR_Schedule()->schedule_mail( $query->post->ID );

					}

					wp_send_json( array( 'success' => true, 'message' => sprintf( _n( '1 scheduled order', '%s scheduled orders', $count, 'yith-woocommerce-review-reminder' ), $count ) ) );

				} else {

					wp_send_json( array( 'success' => true, 'message' => __( 'No scheduled order', 'yith-woocommerce-review-reminder' ) ) );

				}


			} catch ( Exception $e ) {

				wp_send_json( array( 'error' => $e->getMessage() ) );

			}

		}

	}

	new YWRR_Ajax_Premium();

}

