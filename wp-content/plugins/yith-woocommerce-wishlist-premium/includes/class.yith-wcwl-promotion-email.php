<?php
/**
 * Quote email class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 1.1.5
 */

if ( !defined( 'YITH_WCWL' ) ) {
	exit;
} // Exit if accessed directly

if( !class_exists( 'YITH_WCWL_Promotion_Email' ) ) {
	/**
	 * WooCommerce Wishlist Promotion Email
	 *
	 * @since 2.0.7
	 */
	class YITH_WCWL_Promotion_Email extends WC_Email {

		/**
		 * Current user
		 *
		 * @var \WP_User
		 */
		public $user;

		/**
		 * True when the email notification is sent manually only.
		 * @var bool
		 */
		protected $manual = true;

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @return \YITH_WCWL_Promotion_Email
		 * @since 2.0.7
		 */
		public function __construct() {
			$this->id 				   = 'promotion_mail';
			$this->title 			   = __( 'Wishlist Promotional Email', 'yith-woocommerce-wishlist' );
			$this->description		   = __( 'This email is sent to users to inform them about a promotion on a product that was added to their wishlist', 'yith-woocommerce-wishlist' );

			$this->heading 			   = __( 'There is a deal for you!', 'yith-woocommerce-wishlist' );
			$this->subject      	   = __( 'A product of your wishlist is on sale', 'yith-woocommerce-wishlist' );

			$this->content_html        = $this->get_option( 'content_html' );
			$this->content_text        = $this->get_option( 'content_text' );

			$this->template_html 	= 'emails/promotion.php';
			$this->template_plain 	= 'emails/plain/promotion.php';

			// Triggers for this email
			add_action( 'send_promotion_mail_notification', array( $this, 'trigger' ), 15, 2 );

			// Call parent constructor
			parent::__construct();
		}

		/**
		 * Method triggered to send email
		 *
		 * @param $receivers_ids array Array of receivers ids
		 *
		 * @return void
		 * @since 2.0.7
		 */
		public function trigger( $receivers_ids, $additional_info ) {
			$result = false;
			// set promotion product
			if( ! empty( $additional_info['product_id'] ) ){
				$this->object = wc_get_product( $additional_info['product_id'] );
			}
			// if no product set, no promotion email can be sent; return false
			else{
				add_filter( 'yith_wcwl_promotional_email_send_result', '__return_false' );
				return;
			}

			// set promotional code
			if( $additional_info['use_coupon'] && ! empty( $additional_info['coupon_code'] ) ){
				$this->coupon = new WC_Coupon( $additional_info['coupon_code'] );
			}

			// set html content, to use customized content from send view
			if( ! empty( $additional_info['html_content'] ) ){
				$this->content_html = $additional_info['html_content'];
			}

			// set text content, to use customized content from send view
			if( ! empty( $additional_info['text_content'] ) ){
				$this->content_text = $additional_info['text_content'];
			}

			// init cart for admin
			if( ! isset( WC()->cart ) ){
				WC()->cart = new WC_Cart();
			}

			$recipients = $this->get_recipients( $receivers_ids );

			if( ! empty( $recipients ) ){
				foreach( $recipients as $recipient ){
					$this->recipient = $recipient;
					$this->user = get_user_by( 'email', $recipient );

					$res = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
					$result = $res ? $res : false;
				}
			}

			add_filter( 'yith_wcwl_promotional_email_send_result', $result ? '__return_true' : '__return_false' );
		}

		/**
		 * Retrieve recipients for the promotion email
		 *
		 * @param $users_ids array Array of user id which should receive the mail
		 * @return array Array of email addresses
		 * @since 2.0.7
		 */
		public function get_recipients( $users_ids ) {
			$recipients = array();
			if( ! empty( $users_ids ) ){
				foreach( $users_ids as $uid ){
					$user = get_user_by( 'id', $uid );

					if( ! $user ){
						continue;
					}

					$recipients[] = $user->user_email;
				}
			}

			return apply_filters( 'woocommerce_email_recipient_' . $this->id, $recipients, $this->object );
		}

		/**
		 * Get HTML content for the mail
		 *
		 * @return string HTML content of the mail
		 * @since 2.0.7
		 */
		public function get_content_html(){
			ob_start();
			wc_get_template( $this->template_html, array(
				'email' => $this,
				'email_heading' => $this->get_heading(),
				'email_content' => $this->get_custom_content_html(),
				'sent_to_admin' => true,
				'plain_text'    => false
			) );
			return ob_get_clean();
		}

		/**
		 * Get plain text content of the mail
		 *
		 * @return string Plain text content of the mail
		 * @since 2.0.7
		 */
		public function get_content_plain() {
			ob_start();
			wc_get_template( $this->template_plain, array(
				'email' => $this,
				'email_heading' => $this->get_heading(),
				'email_content' => $this->get_custom_content_plain(),
				'sent_to_admin' => true,
				'plain_text'    => true
			) );
			return ob_get_clean();
		}

		/**
		 * Retrieve custom email HTML content
		 *
		 * @return string custom content, with replaced values
		 * @since 2.0.7
		 */
		public function get_custom_content_html() {
			// fix for Fatal Error: call to undefined function wc_cart_round_discount
			if( ! function_exists( 'wc_cart_round_discount' ) ){
				include_once( trailingslashit( WC()->plugin_path() ) . 'includes/wc-cart-functions.php' );
			}

			$this->find = array_merge(
				$this->find,
				array(
					'user_name' => '{user_name}',
					'user_email' => '{user_email}',
					'user_first_name' => '{user_first_name}',
					'user_last_name' => '{user_last_name}',
					'product_image' => '{product_image}',
					'product_name' => '{product_name}',
					'product_price' => '{product_price}',
					'add_to_cart_url' => '{add_to_cart_url}',
				),
				! isset( $this->coupon ) ? array() : array(
					'coupon_code' => '{coupon_code}',
					'coupon_amount' => '{coupon_amount}',
					'coupon_value' => '{coupon_value}'
				)
			);

			$this->replace = array_merge(
				$this->replace,
				array(
					'user_name' => $this->user->user_login,
					'user_email' => $this->user->user_email,
					'user_first_name' => $this->user->billing_first_name,
					'user_last_name' => $this->user->billing_first_name,
					'product_image' => $this->object->get_image(),
					'product_name' => $this->object->get_title(),
					'product_price' => $this->object->get_price_html(),
					'add_to_cart_url' => esc_url( add_query_arg( 'add-to-cart', $this->object->id, get_permalink( $this->object->id ) ) ),
				),
				! isset( $this->coupon ) ? array() : array(
					'coupon_code' => $this->coupon->code,
					'coupon_amount' => $this->coupon->coupon_amount,
					'coupon_value' => wc_price( $this->coupon->get_discount_amount( $this->object->get_price() ) )
				)
			);

			$custom_content = apply_filters( 'yith_wcwl_custom_html_content_' . $this->id, $this->format_string( stripcslashes( $this->content_html ) ), $this->object );
			return $custom_content;
		}

		/**
		 * Retrieve custom email text content
		 *
		 * @return string custom content, with replaced values
		 * @since 2.0.7
		 */
		public function get_custom_content_plain() {
			$this->find = array_merge(
				$this->find,
				array(
					'user_name' => '{user_name}',
					'user_email' => '{user_email}',
					'user_first_name' => '{user_first_name}',
					'user_last_name' => '{user_last_name}',
					'product_name' => '{product_name}',
					'product_price' => '{product_price}',
					'add_to_cart_url' => '{add_to_cart_url}',
				),
				! isset( $this->coupon ) ? array() : array(
					'coupon_code' => '{coupon_code}',
					'coupon_amount' => '{coupon_amount}',
					'coupon_value' => '{coupon_value}'
				)
			);

			$this->replace = array_merge(
				$this->find,
				array(
					'user_name' => $this->user->user_login,
					'user_email' => $this->user->user_email,
					'user_first_name' => $this->user->billing_first_name,
					'user_last_name' => $this->user->billing_first_name,
					'product_name' => $this->object->get_title(),
					'product_price' => $this->object->get_price(),
					'add_to_cart_url' => esc_url( add_query_arg( 'add-to-cart', $this->object->id, get_permalink( $this->object->id ) ) ),
				),
				! isset( $this->coupon ) ? array() : array(
					'coupon_code' => $this->coupon->code,
					'coupon_amount' => $this->coupon->coupon_amount,
					'coupon_value' => $this->coupon->get_discount_amount( $this->object->get_price() )
				)
			);

			$custom_content = apply_filters( 'yith_wcwl_custom_text_content_' . $this->id, $this->format_string( stripcslashes( $this->content_text ) ), $this->object );

			return $custom_content;
		}

		/**
		 * Init form fields to display in WC admin pages
		 *
		 * @return void
		 * @since 2.0.7
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'subject' => array(
					'title' 		=> __( 'Subject', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> sprintf( __( 'This field lets you modify the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'yith-woocommerce-wishlist' ), $this->subject ),
					'placeholder' 	=> '',
					'default' 		=> ''
				),
				'heading' => array(
					'title' 		=> __( 'Email Heading', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> sprintf( __( 'This field lets you modify the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'yith-woocommerce-wishlist' ), $this->heading ),
					'placeholder' 	=> '',
					'default' 		=> ''
				),
				'email_type' => array(
					'title' 		=> __( 'Email type', 'woocommerce' ),
					'type' 			=> 'select',
					'description' 	=> __( 'Choose which type of email to send.', 'woocommerce' ),
					'default' 		=> 'html',
					'class'			=> 'email_type',
					'options'		=> array(
						'plain'		 	=> __( 'Plain text', 'woocommerce' ),
						'html' 			=> __( 'HTML', 'woocommerce' ),
						'multipart' 	=> __( 'Multipart', 'woocommerce' ),
					)
				),
				'content_html' => array(
					'title' 		=> __( 'Email HTML Content', 'yith-woocommerce-wishlist' ),
					'type' 			=> 'textarea',
					'description' 	=> __( 'This field lets you modify the main content of the HTML email. You can use the following placeholder: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{product_image}</code> <code>{product_name}</code> <code>{product_price}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{add_to_cart_url}</code>', 'yith-woocommerce-wishlist' ),
					'placeholder' 	=> '',
					'css'         => 'min-height: 250px;',
					'default' 		=> "<p>Hi {user_name}</p>
<p>A product of your wishlist is on sale!</p>
<p>
	<table>
	    <tr>
	        <td>{product_image}</td>
	        <td>{product_name}</td>
	        <td>{product_price}</td>
	    </tr>
	</table>
</p>
<p>Use this coupon code <b><a href='{add_to_cart_url}' >{coupon_code}</a></b> to obtain a wonderful discount!</p>"
				),
				'content_text' => array(
					'title' 		=> __( 'Email Text Content', 'yith-woocommerce-wishlist' ),
					'type' 			=> 'textarea',
					'description' 	=> __( 'This field lets you modify the main content of the text email. You can use the following placeholder: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{product_name}</code> <code>{product_price}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{coupon_value}</code> <code>{add_to_cart_url}</code>', 'yith-woocommerce-wishlist' ),
					'placeholder' 	=> '',
					'css'         => 'min-height: 250px;',
					'default' 		=> "Hi {user_name}
A product of your wishlist is on sale!

{product_name} {product_price}

Visit {add_to_cart_url} and use this coupon code
{coupon_code}
to obtain a wonderful discount!"
				)
			);
		}
	}
}

// returns instance of the mail on file include
return new YITH_WCWL_Promotion_Email();