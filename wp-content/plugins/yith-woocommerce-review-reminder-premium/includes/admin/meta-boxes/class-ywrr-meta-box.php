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

if ( ! class_exists( 'YWRR_Meta_Box' ) ) {

	/**
	 * Shows Meta Box in order's details page
	 *
	 * @class   YWRR_Meta_Box
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 *
	 */
	class YWRR_Meta_Box {

		/**
		 * Single instance of the class
		 *
		 * @var \YWRR_Meta_Box
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YWRR_Meta_Box
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

			add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );

		}

		/**
		 * Add a metabox on order page
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function add_metabox() {

			if ( ! YITH_WRR()->ywrr_vendor_check() ) {

				foreach ( wc_get_order_types( 'order-meta-boxes' ) as $type ) {
					add_meta_box( 'ywrr-metabox', __( 'Ask for a review', 'yith-woocommerce-review-reminder' ), array( $this, 'output' ), $type, 'normal', 'high' );
				}

			}

		}

		/**
		 * Output Meta Box
		 *
		 * The function to be called to output the meta box in order details page.
		 *
		 * @since   1.0.0
		 *
		 * @param   $post object the current order
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function output( $post ) {

			$customer_id    = get_post_meta( $post->ID, '_customer_user', true );
			$customer_email = get_post_meta( $post->ID, '_billing_email', true );

			if ( YWRR_Blocklist()->check_blocklist( $customer_id, $customer_email ) == true ) {

				if ( YITH_WRR()->check_reviewable_items( $post->ID ) == 0 ) {

					?>
					<div class="toolbar">
						<?php _e( 'There are no reviewable items in this order', 'yith-woocommerce-review-reminder' ) ?>
					</div>
					<?php

				} else {

					$items        = get_option( 'ywrr_request_number' );
					$request_type = get_option( 'ywrr_request_type' );

					if ( $request_type == 'all' ) {

						$criteria = __( 'Reviews will be requested for all items in the order', 'yith-woocommerce-review-reminder' );

					} else {

						if ( $items > 1 ) {

							$criteria = sprintf( __( 'Reviews will be requested for %d items in the order', 'yith-woocommerce-review-reminder' ), $items );

						} else {

							$criteria = __( 'Reviews will be requested for 1 item in the order', 'yith-woocommerce-review-reminder' );

						}

					}

					global $wpdb;

					$schedule = $wpdb->get_var( $wpdb->prepare( "SELECT scheduled_date FROM {$wpdb->prefix}ywrr_email_schedule WHERE order_id = %d AND mail_status = 'pending'", $post->ID ) )

					?>

					<div class="ywrr-send-box toolbar">

						<h3 class="ywrr-send-title" style="display: <?php echo( $schedule ? 'block' : 'none' ) ?>">

							<?php printf( __( 'The request will be sent on %s', 'yith-woocommerce-review-reminder' ), '<span class="ywrr-send-date">' . $schedule . '</span>' ); ?>

						</h3>

						<?php echo $criteria; ?>
						<br />
						<?php _e( 'You can override this setting by selecting the products from the list below.', 'yith-woocommerce-review-reminder' ) ?>
						<p class="buttons">
							<button type="button" class="button-primary do-send-email"><?php _e( 'Send Email', 'yith-woocommerce-review-reminder' ); ?></button>
							<button type="button" class="button-secondary do-reschedule-email"><?php _e( 'Reschedule Email', 'yith-woocommerce-review-reminder' ); ?></button>
							<button type="button" class="button-secondary do-cancel-email"><?php _e( 'Cancel Email', 'yith-woocommerce-review-reminder' ); ?></button>
						</p>
						<div class="ywrr-send-result send-progress"></div>
						<div class="clear"></div>
					</div>

					<?php

				}

			} else {

				?>
				<div class="toolbar">
					<?php _e( 'This customer doesn\'t want to receive any more review requests', 'yith-woocommerce-review-reminder' ) ?>
				</div>
				<?php

			}

		}

	}

	/**
	 * Unique access to instance of YWRR_Meta_Box class
	 *
	 * @return \YWRR_Meta_Box
	 */
	function YWRR_Meta_Box() {
		return YWRR_Meta_Box::get_instance();
	}

	new YWRR_Meta_Box();

}