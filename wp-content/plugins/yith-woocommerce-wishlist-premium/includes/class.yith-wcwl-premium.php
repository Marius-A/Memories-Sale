<?php
/**
 * Init premium features of the plugin
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 1.1.5
 */

if ( !defined( 'YITH_WCWL' ) ) {
	exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCWL_Premium' ) ) {
	/**
	 * WooCommerce Wishlist Premium
	 *
	 * @since 1.0.0
	 */
	class YITH_WCWL_Premium extends YITH_WCWL{

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCWL_Premium
		 * @since 2.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCWL_Premium
		 * @since 2.0.0
		 */
		public static function get_instance(){
			if( is_null( self::$instance ) ){
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @return \YITH_WCWL_Premium
		 * @since 1.0.0
		 */
		public function __construct() {
			YITH_WCWL();

			// inits widget
			add_action( 'widgets_init', array( $this, 'register_widget' ) );

			add_action( 'init', array( $this, 'create_wishlist' ) );
			add_action( 'init', array( $this, 'manage_wishlists' ) );
			add_action( 'init', array( $this, 'delete_wishlists' ) );
			add_action( 'init', array( $this, 'change_wishlist_title' ) );
			add_action( 'init', array( $this, 'ask_an_estimate' ) );

			if( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
				add_action( 'wp_loaded', array( $this, 'bulk_add_to_cart' ), 30 );
			}

			add_action( 'yith_wcwl_after_wishlist', array( $this, 'add_wishlist_links' ) );
			add_action( 'yith_wcwl_after_wishlist_manage', array( $this, 'add_wishlist_links' ) );
			add_action( 'yith_wcwl_after_wishlist_search', array( $this, 'add_wishlist_links' ) );
			add_action( 'yith_wcwl_after_wishlist_create', array( $this, 'add_wishlist_links' ) );
			add_action( 'template_redirect', array( $this, 'redirect_unauthenticated_users' ) );
			add_action( 'template_redirect', array( $this, 'add_wishlist_login_notice' ) );
			add_action( 'init', array( $this, 'add_wishlist_notice' ) );
			add_filter( 'woocommerce_login_redirect', array( $this, 'login_register_redirect' ) );
			add_filter( 'woocommerce_registration_redirect', array( $this, 'login_register_redirect' ) );

			add_filter( 'woocommerce_email_classes', array( $this, 'add_woocommerce_emails' ) );
			add_action( 'woocommerce_init', array( $this, 'load_wc_mailer' ) );
			add_filter( 'woocommerce_locate_core_template', array( $this, 'filter_woocommerce_template' ), 10, 3 );
			add_filter( 'woocommerce_locate_template', array( $this, 'filter_woocommerce_template' ), 10, 3 );


			// handle ajax requests
			add_action( 'wp_ajax_bulk_add_to_cart', array( $this, 'bulk_add_to_cart' ) );
			add_action( 'wp_ajax_nopriv_bulk_add_to_cart', array( $this, 'bulk_add_to_cart' ) );
			add_action( 'wp_ajax_move_to_another_wishlsit', array( $this, 'move_to_another_wishlist' ) );
		}

		/**
		 * Registers widget used to show wishlist list
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_widget() {
			register_widget( 'YITH_WCWL_Widget' );
		}

		/* === WISHLIST METHODS === */

		/**
		 * Add a new wishlist for the user.
		 *
		 * @return string "error", "exists" or id of the inserted wishlist
		 * @since 2.0.0
		 */
		public function add_wishlist() {
			global $wpdb;

			$wishlist_name = ( ! empty( YITH_WCWL()->details['wishlist_name'] ) ) ? YITH_WCWL()->details['wishlist_name'] : false;
			$wishlist_visibility = ( isset( YITH_WCWL()->details['wishlist_visibility'] ) && is_numeric( YITH_WCWL()->details['wishlist_visibility'] ) && YITH_WCWL()->details['wishlist_visibility'] >= 0 && YITH_WCWL()->details['wishlist_visibility'] <= 2 ) ? YITH_WCWL()->details['wishlist_visibility'] : 0;
			$user_id = ( ! empty( YITH_WCWL()->details['user_id'] ) ) ? YITH_WCWL()->details['user_id'] : false;

			if( $user_id == false ){
				YITH_WCWL()->errors[] = __( 'You need to log in before creating a new wishlist', 'yith-woocommerce-wishlist' );
				return "error";
			}

			if( $wishlist_name == false ){
				YITH_WCWL()->errors[] = __( 'Wishlist name is required', 'yith-woocommerce-wishlist' );
				return "error";
			}
			elseif( strlen( $wishlist_name ) >= 65535 ){
				YITH_WCWL()->errors[] = __( 'Wishlist name exceeds the maximum number of characters allowed', 'yith-woocommerce-wishlist' );
				return "error";
			}

			$wishlist_name = sanitize_text_field( $wishlist_name );
			$wishlist_slug = sanitize_title_with_dashes( $wishlist_name );

			// avoid slug duplicate, adding -n to the end of the string
			while( YITH_WCWL()->wishlist_exists( $wishlist_slug, $user_id ) ){
				$match = array();

				if ( ! preg_match( '/([a-z]+)-([0-9]+)/', $wishlist_slug, $match ) ) {
					$i = 2;
				} else {
					$i = intval( $match[2] ) + 1;
					$wishlist_slug = $match[1];
				}

				$wishlist_slug = $wishlist_slug . '-' . $i;
			}

			$token = YITH_WCWL()->generate_wishlist_token();
			YITH_WCWL()->last_operation_token = $token;

			$insert_args = array(
				'user_id' => $user_id,
				'wishlist_slug' => $wishlist_slug,
				'wishlist_token' => $token,
				'wishlist_name' => $wishlist_name,
				'wishlist_privacy' => $wishlist_visibility
			);

			$result = $wpdb->insert( $wpdb->yith_wcwl_wishlists, $insert_args );

			if( $result ){
				YITH_WCWL()->details['wishlist_token'] = $token;
				return $wpdb->insert_id;
			}
			else{
				YITH_WCWL()->errors[] = __( 'Error occurred while creating a new wishlist.', 'yith-woocommerce-wishlist' );
				return "error";
			}
		}

		/**
		 * Update wishlist with arguments passed as second parameter
		 *
		 * @param $wishlist_id int
		 * @param $args array Array of parameters to user in $wpdb->update
		 * @return bool
		 * @since 2.0.0
		 */
		public function update_wishlist( $wishlist_id, $args = array() ) {
			global $wpdb;

			return $wpdb->update( $wpdb->yith_wcwl_wishlists, $args, array( 'ID' => $wishlist_id ) );
		}

		/**
		 * Delete indicated wishlist
		 *
		 * @param $wishlist_id int
		 * @return bool
		 * @since 2.0.0
		 */
		public function remove_wishlist( $wishlist_id ) {
			global $wpdb;

			$res = $wpdb->delete( $wpdb->yith_wcwl_wishlists, array( 'ID' => $wishlist_id ) );

			if( $res ){
				$wpdb->delete( $wpdb->yith_wcwl_items, array( 'wishlist_id' => $wishlist_id ) );
			}

			return $res;
		}

		/* === TEMPLATE MODIFICATIONS === */

		/**
		 * Add wishlist anchors after wishlist table
		 *
		 * @return void
		 * @since 2.0.5
		 */
		public function add_wishlist_links() {
			$add_wishlist_link = get_option( 'yith_wcwl_enable_wishlist_links' );
			$multi_wishlist_enabled = get_option( 'yith_wcwl_multi_wishlist_enable' );

			if( $add_wishlist_link == 'yes' ){
				$manage_url = YITH_WCWL()->get_wishlist_url( 'manage' );
				$create_url = YITH_WCWL()->get_wishlist_url( 'create' );
				$search_url = YITH_WCWL()->get_wishlist_url( 'search' );

				$manage_anchor = sprintf( '<a href="%s" title="%s">%s</a>', $manage_url, __( 'Manage wishlists', 'yith-woocommerce-wishlist' ), __( 'Manage', 'yith-woocommerce-wishlist' ) );
				$create_anchor = sprintf( '<a href="%s" title="%s">%s</a>', $create_url, __( 'Create wishlist', 'yith-woocommerce-wishlist' ), __( 'Create', 'yith-woocommerce-wishlist' ) );
				$search_anchor = sprintf( '<a href="%s" title="%s">%s</a>', $search_url, __( 'Search wishlists', 'yith-woocommerce-wishlist' ), __( 'Search', 'yith-woocommerce-wishlist' ) );

				$action_links = array( $search_anchor );

				if( $multi_wishlist_enabled == 'yes' && is_user_logged_in() ){
					$action_links = array_merge( array( $create_anchor, $manage_anchor ), $action_links );
				}

				$action_links = apply_filters( 'yith_wcwl_action_links', $action_links );

				echo '<div class="wishlist-page-links">' . implode( ' | ', $action_links ) . '</div>';
			}
		}

		/**
		 * Add login notice
		 *
		 * @return void
		 * @since 2.0.5
		 */
		public function add_wishlist_login_notice(){
			$login_notice = get_option( 'yith_wcwl_show_login_notice' );
			$login_text = get_option( 'yith_wcwl_login_anchor_text' );
			$wishlist_page_id = get_option( 'yith_wcwl_wishlist_page_id' );
			$wishlist_page_id = function_exists( 'icl_object_id' ) ? icl_object_id( $wishlist_page_id, 'page', true ) : $wishlist_page_id;

			if( empty( $login_notice ) || ( strpos( $login_notice, '%login_anchor%' ) !== false && empty( $login_text ) ) || ! is_page( $wishlist_page_id ) || is_user_logged_in() ){
				return;
			}

			$login_notice = str_replace( '%login_anchor%', sprintf( '<a href="%s">%s</a>', get_permalink( wc_get_page_id( 'myaccount' ) ), apply_filters( 'yith_wcwl_login_in_text', $login_text ) ), $login_notice );
			wc_add_notice( $login_notice, 'notice' );
		}

		/**
		 * Redirect unauthenticated users to login page
		 *
		 * @return void
		 * @since 2.0.5
		 */
		public function redirect_unauthenticated_users() {
			$disable_wishlist = get_option( 'yith_wcwl_disable_wishlist_for_unauthenticated_users' );
			$wishlist_page_id = get_option( 'yith_wcwl_wishlist_page_id' );
			$wishlist_page_id = function_exists( 'icl_object_id' ) ? icl_object_id( $wishlist_page_id, 'page', true ) : $wishlist_page_id;

			if( $disable_wishlist == 'yes' && ! is_user_logged_in() && is_page( $wishlist_page_id ) ){
				wp_redirect( esc_url_raw( add_query_arg( 'wishlist_notice', 'true', get_permalink( wc_get_page_id( 'myaccount' ) ) ) ) );
				die();
			}
		}

		/**
		 * Add login notice after wishlist redirect
		 *
		 * @return void
		 * @since 2.0.5
		 */
		public function add_wishlist_notice() {
			$disable_wishlist = get_option( 'yith_wcwl_disable_wishlist_for_unauthenticated_users' );
			if( $disable_wishlist == 'yes' && isset( $_GET['wishlist_notice'] ) && $_GET['wishlist_notice'] == true && ! isset( $_POST['login'] ) && ! isset( $_POST['register'] ) ){
				wc_add_notice( apply_filters( 'yith_wcwl_wishlist_disabled_for_unauthenticated_user_message', __( 'Please, log in to use the wishlist features', 'yith-woocommerce-wishlist' ) ), 'error' );
			}
		}

		/**
		 * Add login redirect for wishlist
		 *
		 * @param $redirect string Url where to redirect after login
		 *
		 * @return string
		 * @since 2.0.6
		 */
		public function login_register_redirect( $redirect ) {
			if( isset( $_GET['wishlist_notice'] ) && $_GET['wishlist_notice'] == true ){
				$redirect = YITH_WCWL()->get_wishlist_url();

				if( isset( $_GET['add_to_wishlist'] ) ){
					$redirect = add_query_arg( 'add_to_wishlist', $_GET['add_to_wishlist'], $redirect );
				}
			}

			return $redirect;
		}

		/* === WOOCOMMERCE EMAIL METHODS === */

		/**
		 * Locate default templates of woocommerce in plugin, if exists
		 *
		 * @param $core_file     string
		 * @param $template      string
		 * @param $template_base string
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function filter_woocommerce_template( $core_file, $template, $template_base ) {
			$located = yith_wcwl_locate_template( $template );

			if( $located ){
				return $located;
			}
			else{
				return $core_file;
			}
		}

		/**
		 * Filters woocommerce available mails, to add wishlist related ones
		 *
		 * @param $emails array
		 * @return array
		 * @since 2.0.0
		 */
		public function add_woocommerce_emails( $emails ) {
			$emails[ 'YITH_WCWL_Estimate_Email' ] = include( YITH_WCWL_INC . 'class.yith-wcwl-estimate-email.php' );
			$emails[ 'YITH_WCWL_Promotion_Email' ] = include( YITH_WCWL_INC . 'class.yith-wcwl-promotion-email.php' );

			return $emails;
		}

		/**
		 * Loads WC Mailer when needed
		 *
		 * @return void
		 * @since 1.0
		 * @author Antonio La Rocca <antonio.larocca@yithemes.it>
		 */
		public function load_wc_mailer() {
			global $woocommerce;
			add_action( 'send_estimate_mail', array( 'WC_Emails', 'send_transactional_email' ), 10, 2 );
			add_action( 'send_promotion_mail', array( 'WC_Emails', 'send_transactional_email' ), 10, 2 );
		}

		/* === REQUEST PROCESSING METHODS === */

		/**
		 * Change wishlist title
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function change_wishlist_title() {
			if( is_user_logged_in() && isset( $_POST['yith_wcwl_edit_wishlist'] ) && wp_verify_nonce( $_POST['yith_wcwl_edit_wishlist'], 'yith_wcwl_edit_wishlist_action' ) && ! empty( $_POST['wishlist_name'] ) ){
				$wishlist_name = isset( $_POST['wishlist_name'] ) ? $_POST['wishlist_name'] : false;
				$wishlist_id   = isset( $_POST['wishlist_id'] ) ? $_POST['wishlist_id'] : false;

				$wishlist = YITH_WCWL()->get_wishlist_detail_by_token( $wishlist_id );
				if ( $wishlist_name == false || strlen( $wishlist_name ) >= 65535 ) {
					return;
				}

				$this->update_wishlist( $wishlist['ID'], array( 'wishlist_name' => $wishlist_name ) );
			}
		}

		/**
		 * Create a new wishlist from request
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function create_wishlist() {
			if( is_user_logged_in() && isset( $_POST['yith_wcwl_create'] ) && wp_verify_nonce( $_POST['yith_wcwl_create'], 'yith_wcwl_create_action' ) && isset( $_POST['wishlist_name'] ) ){
				$res = $this->add_wishlist();

				if( $res == 'error' ){
					$messages = YITH_WCWL()->get_errors();
					if ( function_exists( 'wc_add_notice' ) ) {
						wc_add_notice( $messages, 'error' );
					}
					else {
						global $woocommerce;
						$woocommerce->add_error( $messages );
					}
				}
				else{
					$messages = __( 'Wishlist correctly created', 'yith-woocommerce-wishlist' );
					if ( function_exists( 'wc_add_notice' ) ) {
						wc_add_notice( $messages, 'success' );
					}
					else {
						global $woocommerce;
						$woocommerce->add_message( $messages );
					}
				}
			}
		}

		/**
		 * Update or delete wishlist basing on request data
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function manage_wishlists(){
			if( is_user_logged_in() && isset( $_POST['yith_wcwl_manage'] ) && wp_verify_nonce( $_POST['yith_wcwl_manage'], 'yith_wcwl_manage_action' ) && ! empty( $_POST['wishlist_options'] ) ){
				foreach( $_POST['wishlist_options'] as $wishlist_id => $wishlist ){
					if( isset( $wishlist['delete'] ) ){
						$this->remove_wishlist( $wishlist_id );
					}
					else{
						$this->update_wishlist( $wishlist_id, $wishlist );
					}
				}
			}
		}

		/**
		 * Delete wishlist when "Delete" button is selected on manage view
		 *
		 * @return void
		 * @since 2.0.7
		 */
		public function delete_wishlists() {
			$res = true;

			if( is_user_logged_in() && isset( $_GET['yith_wcwl_delete'] ) && wp_verify_nonce( $_GET['yith_wcwl_delete'], 'yith_wcwl_delete_action' ) && ! empty( $_GET['wishlist_id'] ) ){
				$wishlist_id = $_GET['wishlist_id'];
				$res = $this->remove_wishlist( $wishlist_id );

				$message = $res ? __( 'Wishlist successfully deleted','yith-woocommerce-wishlist' ) : __( 'There was an error while deleting the wishlist; please try again later!', 'yith-woocommerce-wishlist' );
				$message_type = $res ? 'success' : 'error';

				wc_add_notice( $message, $message_type );

				wp_redirect( YITH_WCWL()->get_wishlist_url( 'manage' ) );
				die();
			}
		}

		/**
		 * Triggers action that sends an email when users ask an estimate
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function ask_an_estimate() {
			if( isset( $_GET['ask_an_estimate'] ) && isset( $_GET['estimate_nonce'] ) && wp_verify_nonce( $_GET['estimate_nonce'], 'ask_an_estimate' ) ){
				$wishlist_id = $_GET['ask_an_estimate'];
				$wishlist_id = ( $wishlist_id == 'false' ) ? false : $wishlist_id;

				$additional_notes = ! empty( $_POST['additional_notes'] ) ? sanitize_text_field( $_POST['additional_notes'] ) : false;

				do_action( 'send_estimate_mail', $wishlist_id, $additional_notes );

				wc_add_notice( __( 'Estimate request sent', 'yith-woocommerce-wishlist' ), 'success' );

				wp_redirect( YITH_WCWL()->get_wishlist_url( 'view' . '/' . ( ( ! empty( $wishlist_id ) ) ? $wishlist_id : '' ) ), 301 );
				exit();
			}
		}

		/**
		 * Adds multiple items to the cart from wishlist page
		 * If AJAX, prints wishlist shortcode content
		 *
		 * @return void
		 * @since 2.0.5
		 */
		public function bulk_add_to_cart() {
			if( isset( $_REQUEST['wishlist_products_to_add_to_cart'] ) ){
				$ids = array_filter( explode( ',', $_REQUEST['wishlist_products_to_add_to_cart'] ) );
				$remove_after_add_to_cart = 'yes' == get_option( 'yith_wcwl_remove_after_add_to_cart' );
				$redirect_to_cart = 'yes' == get_option( 'yith_wcwl_redirect_cart' );
				$wishlist_token = isset( $_REQUEST['wishlist_token'] ) ? $_REQUEST['wishlist_token'] : false;
				$result = true;

				if( ! empty( $ids ) ){
					foreach( $ids as $id ){
						$result = ( WC()->cart->add_to_cart( $id ) ) ? $result : false;

						if( $remove_after_add_to_cart ){
							YITH_WCWL()->details['remove_from_wishlist'] = $id;
							YITH_WCWL()->remove();
						}
					}

					if( $result ){
						wc_add_to_cart_message( $ids );
					}
				}
				else{
					$result = false;
					wc_add_notice( __( 'You have to select at least one product', 'yith-woocommerce-wishlist' ), 'error' );
				}

				$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : WC()->cart->get_cart_url();
				$url_to_redirect = $redirect_to_cart ? $cart_url : YITH_WCWL()->get_wishlist_url( 'view/' . $wishlist_token );
				$url_to_redirect = $result ? $url_to_redirect : YITH_WCWL()->get_wishlist_url( 'view/' . $wishlist_token );

				wp_redirect( $url_to_redirect );
				die();
			}
		}

		/**
		 * Move an item to another wishlist on an ajax call
		 *
		 * @return void
		 * @since 2.0.7
		 */
		public function move_to_another_wishlist(){
			global $wpdb;

			$wishlist_token = isset( $_POST['wishlist_token'] ) ? $_POST['wishlist_token'] : false;
			$wishlist_id = isset( $_POST['wishlist_id'] ) ? $_POST['wishlist_id'] : false;
			$destination_wishlist_token = isset( $_POST['destination_wishlist_token'] ) ? $_POST['destination_wishlist_token'] : false;
			$item_id = isset( $_POST['item_id'] ) ? $_POST['item_id'] : false;
			$res = false;

			if( is_user_logged_in() && $destination_wishlist_token && $item_id ){
				if( empty( $wishlist_id ) ){
					$from_wishlist = YITH_WCWL()->get_wishlist_detail_by_token( $wishlist_token );
					$from_wishlist_id = isset( $from_wishlist['ID'] ) ? $from_wishlist['ID'] : false;
				}
				else{
					$from_wishlist_id = $wishlist_id;
				}

				$to_wishlist = YITH_WCWL()->get_wishlist_detail_by_token( $destination_wishlist_token );
				$to_wishlist_id = isset( $to_wishlist['ID'] ) ? $to_wishlist['ID'] : false;

				if( ! empty( $from_wishlist_id ) && ! empty( $to_wishlist_id ) ){
					if( ! YITH_WCWL()->is_product_in_wishlist( $item_id, $to_wishlist_id ) ) {
						$res = $wpdb->update(
							$wpdb->yith_wcwl_items,
							array(
								'wishlist_id' => $to_wishlist_id,
								'dateadded' => current_time( 'mysql' )
							),
							array(
								'wishlist_id' => $from_wishlist_id,
								'prod_id'     => $item_id
							),
							array( '%d', '%s' ),
							array( '%d', '%d' )
						);
					}
					else{
						YITH_WCWL()->details['remove_from_wishlist'] = $item_id;
						YITH_WCWL()->details['user_id'] = get_current_user_id();
						$res = YITH_WCWL()->remove();

						if( $res ) {
							$res = $wpdb->update(
								$wpdb->yith_wcwl_items,
								array(
									'dateadded' => current_time( 'mysql' )
								),
								array(
									'wishlist_id' => $to_wishlist_id,
									'prod_id'     => $item_id
								),
								array( '%s' ),
								array( '%d', '%d' )
							);
						}
					}
				}

				$message = ( $res !== false ) ? sprintf( __( 'Element correctly moved to %s', 'yith-woocommerce-wishlist' ), $to_wishlist['wishlist_name'] ) : __( 'There was an error while moving an item to another wishlist. Please, try again later', 'yith-woocommerce-wishlist' );
				$message_type = ( $res !== false ) ? 'success' : 'error';

				wc_add_notice( $message, $message_type );
			}

			$atts = array( 'wishlist_id' => $wishlist_token );
			if( isset( $this->details['pagination'] ) ){
				$atts['pagination'] = $this->details['pagination'];
			}

			if( isset( $this->details['per_page'] ) ){
				$atts['per_page'] = $this->details['per_page'];
			}

			echo YITH_WCWL_Shortcode::wishlist( $atts );
			die();
		}
	}
}

/**
 * Unique access to instance of YITH_WCWL_Premium class
 *
 * @return \YITH_WCWL_Premium
 * @since 2.0.0
 */
function YITH_WCWL_Premium(){
	return YITH_WCWL_Premium::get_instance();
}