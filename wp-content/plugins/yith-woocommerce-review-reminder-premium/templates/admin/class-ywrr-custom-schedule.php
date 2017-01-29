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

/**
 * Outputs a custom template for email mass schedule in plugin options panel
 *
 * @class   YWRR_Custom_Schedule
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 *
 */
class YWRR_Custom_Schedule {

	/**
	 * Single instance of the class
	 *
	 * @var \YWRR_Custom_Schedule
	 * @since 1.0.0
	 */
	protected static $instance;

	/**
	 * Returns single instance of the class
	 *
	 * @return \YWRR_Custom_Schedule
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

		add_action( 'woocommerce_admin_field_ywrr-schedule', array( $this, 'output' ) );

	}

	/**
	 * Outputs a custom template for email mass schedule in plugin options panel
	 *
	 * @since   1.2.3
	 *
	 * @param   $option
	 *
	 * @return  void
	 * @author  Alberto Ruggiero
	 */
	public function output( $option ) {

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label><?php _e( 'Bulk Schedule', 'yith-woocommerce-review-reminder' ) ?></label>
			</th>
			<td class="forminp forminp-custom-send">
				<button type="button" class="button-secondary ywrr-schedule-email"><?php _e( 'Schedule Orders', 'yith-woocommerce-review-reminder' ); ?></button>
				<div class="ywrr-schedule-result send-progress"><?php _e( 'Please wait...', 'yith-woocommerce-review-reminder' ); ?></div>
				<span class="description" style="display: block;"><?php _e( 'Use this option to schedule all the orders that have never been scheduled. This option is useful if you use external tool to manage your e-commerce, such as Linnworks, that could bypass some WooCommerce functionalities.', 'yith-woocommerce-review-reminder' ); ?></span>
			</td>
		</tr>
		<?php
	}

}

/**
 * Unique access to instance of YWRR_Custom_Schedule class
 *
 * @return \YWRR_Custom_Schedule
 */
function YWRR_Custom_Schedule() {

	return YWRR_Custom_Schedule::get_instance();

}

new YWRR_Custom_Schedule();