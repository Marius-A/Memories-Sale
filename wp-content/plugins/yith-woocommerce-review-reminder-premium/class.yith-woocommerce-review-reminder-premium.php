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

if ( ! class_exists( 'YWRR_Review_Reminder_Premium' ) ) {

	/**
	 * Implements features of YWRR plugin
	 *
	 * @class   YWRR_Review_Reminder_Premium
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YWRR_Review_Reminder_Premium extends YWRR_Review_Reminder {

		/**
		 * Returns single instance of the class
		 *
		 * @return \YWRR_Review_Reminder
		 * @since 1.1.5
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self;

			}

			return self::$instance;

		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since   1.0.0
		 * @return  mixed
		 * @author  Alberto Ruggiero
		 */
		public function __construct() {

			if ( ! function_exists( 'WC' ) ) {
				return;
			}

			parent::__construct();

			$this->_email_templates = array(
				'premium-1' => array(
					'folder' => 'emails/premium-1',
					'path'   => YWRR_TEMPLATE_PATH
				),
				'premium-2' => array(
					'folder' => 'emails/premium-2',
					'path'   => YWRR_TEMPLATE_PATH
				),
				'premium-3' => array(
					'folder' => 'emails/premium-3',
					'path'   => YWRR_TEMPLATE_PATH
				)
			);

			// register plugin to licence/update system
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );
			add_action( 'init', array( $this, 'ywrr_image_sizes' ) );

			// Include required files
			$this->includes();

			add_filter( 'yith_wcet_email_template_types', array( $this, 'add_yith_wcet_template' ) );
			add_filter( 'ywrr_product_permalink', array( $this, 'ywrr_product_permalink' ) );

			if ( get_option( 'ywrr_schedule_order_column' ) == 'yes' ) {

				add_filter( 'manage_shop_order_posts_columns', array( $this, 'add_ywrr_column' ), 11 );
				add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_ywrr_column' ), 3 );

			}

			add_action( 'admin_footer', array( $this, 'ywrr_admin_footer' ), 11 );
			add_action( 'load-edit.php', array( $this, 'ywrr_bulk_action' ) );
			add_action( 'admin_notices', array( $this, 'ywrr_bulk_admin_notices' ) );

			if ( is_admin() ) {

				add_filter( 'ywrr_admin_scripts_filter', array( $this, 'ywrr_admin_scripts_filter' ), 10, 2 );

				add_action( 'admin_notices', array( $this, 'ywrr_admin_scripts_premium' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'ywrr_admin_scripts' ) );
				add_action( 'ywrr_schedulelist', array( YWRR_Schedule_Table(), 'output' ) );

			} else {

				add_action( 'wp_enqueue_scripts', array( $this, 'ywrr_scripts' ) );

				if ( get_option( 'ywrr_enable_plugin' ) == 'yes' ) {

					if ( get_option( 'ywrr_refuse_requests' ) == 'yes' ) {

						add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'ywrr_show_request_option' ) );
						add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'ywrr_save_request_option' ) );
						add_action( 'woocommerce_edit_account_form', array( $this, 'ywrr_show_request_option_my_account' ) );
						add_action( 'woocommerce_save_account_details', array( $this, 'ywrr_save_request_option_my_account' ) );

					}

				}

			}

		}

		/**
		 * Files inclusion
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		private function includes() {

			include_once( 'includes/class-ywrr-emails-premium.php' );
			include_once( 'includes/class-ywrr-schedule-premium.php' );
			include_once( 'includes/emails/class-ywrr-mandrill-premium.php' );


			if ( is_admin() ) {
				include_once( 'includes/admin/class-ywrr-ajax-premium.php' );
				include_once( 'includes/admin/meta-boxes/class-ywrr-meta-box.php' );
				include_once( 'templates/admin/schedule-table.php' );
				include_once( 'templates/admin/class-ywrr-custom-schedule.php' );
				include_once( 'templates/admin/class-ywrr-custom-mailskin.php' );
				include_once( 'templates/admin/class-yith-wc-custom-checklist.php' );
			}

		}

		/**
		 * Check if current user is a vendor
		 *
		 * @since   1.2.3
		 * @return  boolean
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_vendor_check() {

			$is_vendor = false;

			if ( defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM ) {

				$vendor = yith_get_vendor( 'current', 'user' );

				$is_vendor = ( $vendor->id != 0 );

			}

			return $is_vendor;

		}

		/**
		 * Add the schedule column
		 *
		 * @since   1.2.2
		 *
		 * @param   $columns
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function add_ywrr_column( $columns ) {

			if ( ! $this->ywrr_vendor_check() ) {

				$columns['ywrr_status'] = __( 'Review Reminder', 'yith-woocommerce-review-reminder' );

			}

			return $columns;

		}

		/**
		 * Check if order has reviewable items
		 *
		 * @since   1.2.7
		 *
		 * @param   $post_id
		 *
		 * @return  int
		 * @author  Alberto Ruggiero
		 */
		public function check_reviewable_items( $post_id ) {

			$order            = wc_get_order( $post_id );
			$order_items      = $order->get_items();
			$reviewable_items = 0;

			foreach ( $order_items as $item ) {

				if ( ! YWRR_Emails()->items_has_comments_closed( $item['product_id'] ) ) {

					$reviewable_items ++;

				}

			}

			return $reviewable_items;

		}

		/**
		 * Render the schedule column
		 *
		 * @since   1.2.2
		 *
		 * @param   $column
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function render_ywrr_column( $column ) {

			if ( ! $this->ywrr_vendor_check() && 'ywrr_status' == $column ) {

				global $post;

				$customer_id    = get_post_meta( $post->ID, '_customer_user', true );
				$customer_email = get_post_meta( $post->ID, '_billing_email', true );

				if ( YWRR_Blocklist()->check_blocklist( $customer_id, $customer_email ) == true ) {

					if ( $this->check_reviewable_items( $post->ID ) == 0 ) {

						?>
						<div class="toolbar">
							<?php _e( 'There are no reviewable items in this order', 'yith-woocommerce-review-reminder' ) ?>
						</div>
						<?php

					} else {

						global $wpdb;

						$schedule = $wpdb->get_var( $wpdb->prepare( "SELECT scheduled_date FROM {$wpdb->prefix}ywrr_email_schedule WHERE order_id = %d AND mail_status = 'pending'", $post->ID ) );

						?>

						<div class="ywrr-send-box">
							<div class="buttons">
								<button type="button" class="button tips do-send-email" data-tip="<?php _e( 'Send Email', 'yith-woocommerce-review-reminder' ); ?>"><?php _e( 'Send Email', 'yith-woocommerce-review-reminder' ); ?></button>
								<button type="button" class="button tips do-reschedule-email" data-tip="<?php _e( 'Reschedule Email', 'yith-woocommerce-review-reminder' ); ?>"><?php _e( 'Reschedule Email', 'yith-woocommerce-review-reminder' ); ?></button>
								<button type="button" class="button tips do-cancel-email" data-tip="<?php _e( 'Cancel Email', 'yith-woocommerce-review-reminder' ); ?>"><?php _e( 'Cancel Email', 'yith-woocommerce-review-reminder' ); ?></button>
								<input class="ywrr-order-id" type="hidden" value="<?php echo $post->ID ?>">
								<input class="ywrr-order-date" type="hidden" value="<?php echo $post->post_modified ?>">
							</div>
							<div class="clear"></div>
							<div class="ywrr-send-title" style="display: <?php echo( $schedule ? 'block' : 'none' ) ?>">

								<?php printf( __( 'The request will be sent on %s', 'yith-woocommerce-review-reminder' ), '<span class="ywrr-send-date">' . $schedule . '</span>' ); ?>

							</div>
							<div class="clear"></div>
							<div class="ywrr-send-result send-progress"></div>
							<div class="clear"></div>

						</div>

						<?php

					}

				} else {

					_e( 'This customer doesn\'t want to receive any more review requests', 'yith-woocommerce-review-reminder' );

				}


			}

		}

		/**
		 * Add bulk actions to orders
		 *
		 * @since   1.2.2
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_admin_footer() {
			global $post_type;

			if ( ! $this->ywrr_vendor_check() && 'shop_order' == $post_type ) {
				?>
				<script type="text/javascript">
					jQuery(function () {
						jQuery('<option>').val('ywrr_send').text('<?php _e( 'Review Reminder: Send Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action"]');
						jQuery('<option>').val('ywrr_send').text('<?php _e( 'Review Reminder: Send Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action2"]');

						jQuery('<option>').val('ywrr_reschedule').text('<?php _e( 'Review Reminder: Reschedule Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action"]');
						jQuery('<option>').val('ywrr_reschedule').text('<?php _e( 'Review Reminder: Reschedule Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action2"]');

						jQuery('<option>').val('ywrr_cancel').text('<?php _e( 'Review Reminder: Cancel Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action"]');
						jQuery('<option>').val('ywrr_cancel').text('<?php _e( 'Review Reminder: Cancel Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action2"]');
					});
				</script>
				<?php
			}
		}

		/**
		 * Trigger bulk actions to orders
		 *
		 * @since   1.2.2
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_bulk_action() {

			if ( $this->ywrr_vendor_check() ) {
				return;
			}

			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();

			// Bail out if this is not a status-changing action
			if ( strpos( $action, 'ywrr_' ) === false ) {
				return;
			}

			$processed = 0;

			$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );

			foreach ( $post_ids as $post_id ) {

				$customer_id    = get_post_meta( $post_id, '_customer_user', true );
				$customer_email = get_post_meta( $post_id, '_billing_email', true );

				if ( YWRR_Blocklist()->check_blocklist( $customer_id, $customer_email ) == true ) {

					if ( $this->check_reviewable_items( $post_id ) == 0 ) {
						continue;
					}

					$order = wc_get_order( $post_id );

					switch ( substr( $action, 5 ) ) {

						case 'send':

							$today        = new DateTime( current_time( 'mysql' ) );
							$pay_date     = new DateTime( $order->order_date );
							$days         = $pay_date->diff( $today );
							$email_result = YWRR_Emails()->send_email( $post_id, $days->days );

							break;

						case 'reschedule':

							$scheduled_date = date( 'Y-m-d', strtotime( current_time( 'mysql' ) . ' + ' . get_option( 'ywrr_mail_schedule_day' ) . ' days' ) );

							if ( YWRR_Schedule()->check_exists_schedule( $post_id ) != 0 ) {

								YWRR_Schedule_Premium()->reschedule( $post_id, $scheduled_date );

							} else {

								YWRR_Schedule()->schedule_mail( $post_id );

							}

							break;

						case 'cancel':

							if ( YWRR_Schedule()->check_exists_schedule( $post_id ) != 0 ) {

								YWRR_Schedule()->change_schedule_status( $post_id );

							}

							break;

					}

					$processed ++;

				}

			}

			$sendback = add_query_arg( array( 'post_type' => 'shop_order', 'ywrr_action' => substr( $action, 5 ), 'processed' => $processed, 'ids' => join( ',', $post_ids ) ), '' );

			wp_redirect( esc_url_raw( $sendback ) );
			exit();
		}

		/**
		 * Show notification after bulk actions
		 *
		 * @since   1.2.2
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_bulk_admin_notices() {

			if ( $this->ywrr_vendor_check() ) {
				return;
			}

			global $post_type, $pagenow;

			// Bail out if not on shop order list page
			if ( 'edit.php' !== $pagenow || 'shop_order' !== $post_type ) {
				return;
			}

			if ( isset( $_REQUEST['ywrr_action'] ) ) {

				$number = isset( $_REQUEST['processed'] ) ? absint( $_REQUEST['processed'] ) : 0;

				switch ( $_REQUEST['ywrr_action'] ) {

					case'send':
						$message = sprintf( _n( 'Review Reminder: Email sent.', 'Review Reminder: %s emails sent', $number, 'yith-woocommerce-review-reminder' ), number_format_i18n( $number ) );
						break;

					case'reschedule':
						$message = sprintf( _n( 'Review Reminder: Email rescheduled.', 'Review Reminder: %s emails rescheduled.', $number, 'yith-woocommerce-review-reminder' ), number_format_i18n( $number ) );
						break;

					case'cancel':
						$message = sprintf( _n( 'Review Reminder: Email cancelled.', 'Review Reminder: %s emails cancelled.', $number, 'yith-woocommerce-review-reminder' ), number_format_i18n( $number ) );
						break;

					default:
						$message = '';
				}

				if ( $message ) {

					echo '<div class="updated"><p>' . $message . '</p></div>';

				}

			}

		}

		/**
		 * Set image sizes for email
		 *
		 * @since   1.0.4
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_image_sizes() {

			add_image_size( 'ywrr_picture', 135, 135, true );

		}

		/**
		 * If is active YITH WooCommerce Email Templates, add YWRR to list
		 *
		 * @since   1.0.0
		 *
		 * @param   $templates
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function add_yith_wcet_template( $templates ) {

			$templates[] = array(
				'id'   => 'yith-review-reminder',
				'name' => 'YITH WooCommerce Review Reminder',
			);

			return $templates;

		}

		/**
		 * Set the link to the product
		 *
		 * @since   1.0.4
		 *
		 * @param   $permalink
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_product_permalink( $permalink ) {

			$link_type = get_option( 'ywrr_mail_item_link' );

			switch ( $link_type ) {
				case 'custom':
					$link_hash = get_option( 'ywrr_mail_item_link_hash' );

					if ( ! empty( $link_hash ) ) {

						if ( substr( $link_hash, 0, 1 ) === '#' ) {

							$permalink .= $link_hash;

						} else {

							$permalink .= '#' . $link_hash;

						}

					}

					break;

				case 'review':

					$permalink .= '#tab-reviews';

					break;

				default:

			}

			if ( get_option( 'ywrr_enable_analytics' ) == 'yes' ) {

				$campaign_source  = str_replace( ' ', '%20', get_option( 'ywrr_campaign_source' ) );
				$campaign_medium  = str_replace( ' ', '%20', get_option( 'ywrr_campaign_medium' ) );
				$campaign_term    = str_replace( ',', '+', get_option( 'ywrr_campaign_term' ) );
				$campaign_content = str_replace( ' ', '%20', get_option( 'ywrr_campaign_content' ) );
				$campaign_name    = str_replace( ' ', '%20', get_option( 'ywrr_campaign_name' ) );

				$query_args = array(
					'utm_source' => $campaign_source,
					'utm_medium' => $campaign_medium,
				);

				if ( $campaign_term != '' ) {

					$query_args['utm_term'] = $campaign_term;

				}

				if ( $campaign_content != '' ) {

					$query_args['utm_content'] = $campaign_content;

				}

				$query_args['utm_name'] = $campaign_name;

				$permalink = add_query_arg( $query_args, $permalink );

			}


			return $permalink;

		}

		/**
		 * ADMIN FUNCTIONS
		 */

		/**
		 * Advise if the plugin cannot be performed
		 *
		 * @since   1.0.3
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_admin_notices() {
			if ( get_option( 'ywrr_mandrill_enable' ) == 'yes' && get_option( 'ywrr_mandrill_apikey' ) == '' ) : ?>
				<div class="error">
					<p>
						<?php _e( 'Please enter Mandrill API Key for YITH Woocommerce Review Reminder', 'yith-woocommerce-review-reminder' ); ?>
					</p>
				</div>
				<?php
			endif;
		}

		/**
		 * Initializes Javascript with localization
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_admin_scripts_premium() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'ywrr-admin-premium', YWRR_ASSETS_URL . 'css/ywrr-admin-premium' . $suffix . '.css' );
			wp_enqueue_script( 'ywrr-admin-premium', YWRR_ASSETS_URL . 'js/ywrr-admin-premium' . $suffix . '.js', array( 'jquery', 'ywrr-admin' ) );

		}

		/**
		 * Add premium strings for localization
		 *
		 * @since   1.1.5
		 *
		 * @param   $strings
		 * @param   $post
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_admin_scripts_filter( $strings, $post ) {

			$strings['post_id']                = isset( $post->ID ) ? $post->ID : '';
			$strings['order_date']             = isset( $post->post_modified ) ? $post->post_modified : '';
			$strings['is_order_page']          = isset( $_GET['post_type'] ) && $_GET['post_type'] == 'shop_order';
			$strings['do_send_email']          = __( 'Do you want to send remind email?', 'yith-woocommerce-review-reminder' );
			$strings['after_send_email']       = __( 'Reminder email has been sent successfully!', 'yith-woocommerce-review-reminder' );
			$strings['do_reschedule_email']    = __( 'Do you want to reschedule reminder email?', 'yith-woocommerce-review-reminder' );
			$strings['after_reschedule_email'] = __( 'Reminder email has been rescheduled successfully!', 'yith-woocommerce-review-reminder' );
			$strings['do_cancel_email']        = __( 'Do you want to cancel reminder email?', 'yith-woocommerce-review-reminder' );
			$strings['after_cancel_email']     = __( 'Reminder email has been cancelled!', 'yith-woocommerce-review-reminder' );
			$strings['not_found_cancel']       = __( 'There is no email to unschedule', 'yith-woocommerce-review-reminder' );
			$strings['please_wait']            = __( 'Please wait...', 'yith-woocommerce-review-reminder' );

			return $strings;

		}

		/**
		 * FRONTEND FUNCTIONS
		 */

		/**
		 * Initializes Javascript
		 *
		 * @since   1.0.4
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_scripts() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'ywrr-footer', YWRR_ASSETS_URL . 'js/ywrr-footer' . $suffix . '.js', array(), false, true );

		}

		/**
		 * Show email request checkbox in checkout page
		 *
		 * @since   1.2.6
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_show_request_option() {

			if ( ! YWRR_Blocklist()->check_blocklist( get_current_user_id(), '' ) ) {
				return;
			}

			$label = apply_filters( 'ywrr_checkout_option_label', __( 'I accept to receive review requests via email', 'yith-woocommerce-review-reminder' ) ); //@since 1.2.6

			if ( ! empty( $label ) ) {

				woocommerce_form_field( 'ywrr_receive_requests', array(
					'type'  => 'checkbox',
					'class' => array( 'form-row-wide' ),
					'label' => $label,
				), 1 );

			}

		}

		/**
		 * Save email request checkbox in checkout page
		 *
		 * @since   1.2.6
		 *
		 * @param   $order_id
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_save_request_option( $order_id ) {

			if ( empty( $_POST['ywrr_receive_requests'] ) ) {

				YWRR_Blocklist()->add_to_blocklist( get_current_user_id(), $_POST['billing_email'] );

			}

		}

		/**
		 * Add customer request option to edit account page
		 *
		 * @since   1.2.6
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_show_request_option_my_account() {

			$label = apply_filters( 'ywrr_checkout_option_label', __( 'I accept to receive review requests via email', 'yith-woocommerce-review-reminder' ) ); //@since 1.2.6

			?>

			<p class="form-row form-row-wide">

				<label for="ywrr_receive_requests">
					<input
						name="ywrr_receive_requests"
						type="checkbox"
						class=""
						value="1"
						<?php checked( YWRR_Blocklist()->check_blocklist( get_current_user_id(), '' ) ); ?>
					/> <?php echo $label ?>
				</label>

			</p>

			<?php

		}

		/**
		 * Save customer request option from edit account page
		 *
		 * @since   1.2.6
		 *
		 * @param   $customer_id
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_save_request_option_my_account( $customer_id ) {

			if ( isset( $_POST['ywrr_receive_requests'] ) ) {

				YWRR_Blocklist()->remove_from_blocklist( $customer_id );

			} else {

				$email = get_user_meta( $customer_id, 'billing_email' );

				YWRR_Blocklist()->add_to_blocklist( $customer_id, $email );

			}

		}

		/**
		 * YITH FRAMEWORK
		 */

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once 'plugin-fw/licence/lib/yit-licence.php';
				require_once 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YWRR_INIT, YWRR_SECRET_KEY, YWRR_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once( 'plugin-fw/lib/yit-upgrade.php' );
			}
			YIT_Upgrade()->register( YWRR_SLUG, YWRR_INIT );
		}

	}

}

if ( ! function_exists( 'wc_get_template_html' ) ) {

	/**
	 * Added for backward compatibility
	 * Like wc_get_template, but returns the HTML instead of outputting.
	 * @see   wc_get_template
	 * @since 2.5.0
	 */
	function wc_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		ob_start();
		wc_get_template( $template_name, $args, $template_path, $default_path );

		return ob_get_clean();
	}

}