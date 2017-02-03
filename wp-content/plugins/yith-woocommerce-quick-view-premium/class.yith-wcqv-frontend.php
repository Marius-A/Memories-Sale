<?php
/**
 * Frontend class
 *
 * @author Yithemes
 * @package YITH WooCommerce Quick View
 * @version 1.1.1
 */

if ( ! defined( 'YITH_WCQV' ) ) {
	exit;
} // Exit if accessed directly

if( ! class_exists( 'YITH_WCQV_Frontend' ) ) {
	/**
	 * Admin class.
	 * The class manage all the Frontend behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WCQV_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCQV_Frontend
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $version = YITH_WCQV_VERSION;

		/**
		 * Button quick view position
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $position = '';

		/**
		 * Enable zoom magnifier
		 *
		 * @var boolean
		 * @since 1.0.0
		 */
		public $enable_zoom = false;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCQV_Frontend
		 * @since 1.0.0
		 */
		public static function get_instance(){
			if( is_null( self::$instance ) ){
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct() {

			$this->position = get_option( 'yith-wcqv-button-position' );
			// enable zoom option
			$this->enable_zoom = ( get_option( 'yith-wcqv-enable-zoom-magnifier' ) == 'yes' && get_option('yith-wcqv-product-images-mode') != 'slider' );

			// custom styles and javascripts
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 20 );

			// quick view ajax
			add_action('wp_ajax_yith_load_product_quick_view', array($this, 'yith_load_product_quick_view_ajax'));
			add_action('wp_ajax_nopriv_yith_load_product_quick_view', array($this, 'yith_load_product_quick_view_ajax'));
			

			// add button
			if( $this->position == 'add-cart' ) {
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'yith_add_quick_view_button' ), 15 );
			}
			else {
				add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'yith_add_quick_view_button' ), 15 );
			}

			add_action( 'yith_wcwl_table_after_product_name', array( $this, 'add_quick_view_button_in_wishlist' ), 15 );

			// load action for product template
			add_action( 'init', array( $this , 'yith_quick_view_action_template' ) );

			// filter image size
			add_filter( 'single_product_large_thumbnail_size', array( $this, 'yith_set_image_size' ) );

			// add attachment id to variation data
			add_filter( 'woocommerce_available_variation', array( $this, 'add_attachment_id' ), 10, 3 );

			// woocommerce multilingual currency
			add_filter( 'wcml_multi_currency_is_ajax', array( $this, 'set_correct_currency' ), 10, 1 );

			// add redirect to checkout
			add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'get_checkout_url' ), 99, 1 );

			add_shortcode( 'yith_quick_view', array( $this, 'quick_view_shortcode' ) );
		}


		/**
		 * Enqueue styles and scripts
		 *
		 * @access public
		 * @return void
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function enqueue_styles_scripts() {

			$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$assets_path  = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';

			wp_register_script( 'jquery-blockui', $assets_path . 'js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.60', true );

			wp_register_script( 'yith-wcqv-frontend', YITH_WCQV_ASSETS_URL . '/js/frontend' . $suffix . '.js', array('jquery'), $this->version, true );

			$paths      = apply_filters( 'yith_wcqv_stylesheet_paths', array( WC()->template_path() . 'yith-quick-view.css', 'yith-quick-view.css' ) );
			$located    = locate_template( $paths, false, false );
			$search     = array( get_stylesheet_directory(), get_template_directory() );
			$replace    = array( get_stylesheet_directory_uri(), get_template_directory_uri() );
			$stylesheet = ! empty( $located ) ? str_replace( $search, $replace, $located ) : YITH_WCQV_ASSETS_URL . '/css/yith-quick-view.css';

			wp_register_style( 'yith-quick-view', $stylesheet, array(), $this->version );

			// third part plugin
			wp_register_style( 'external-plugin', YITH_WCQV_ASSETS_URL . '/css/style-external.css', array(), $this->version );
			wp_register_script( 'external-plugin', YITH_WCQV_ASSETS_URL . '/js/scripts-external.min.js', array('jquery'), $this->version, true );

			// enqueue style
			wp_enqueue_style( 'external-plugin' );
			wp_enqueue_style( 'yith-quick-view' );

			if( $this->enable_zoom ){
				wp_enqueue_style( 'yith-magnifier');
			}

			// set variables
			$content_w = get_option( 'yith-quick-view-modal-width' );
			$image_w   = get_option( 'yith-quick-view-product-image-width' );
			$background_modal = get_option( 'yith-wcqv-background-modal' );
			$button_text_color = get_option( 'yith-wcqv-button-quick-view-text-color' );
			$button_text_color_hover = get_option( 'yith-wcqv-button-quick-view-text-color-hover' );
			$button_color = get_option( 'yith-wcqv-button-quick-view-color');
			$button_color_hover = get_option( 'yith-wcqv-button-quick-view-color-hover' );
			$main_text_color = get_option( 'yith-wcqv-main-text-color' );
			$star_color = get_option( 'yith-wcqv-star-color' );
			$cart_color = get_option( 'yith-wcqv-button-cart-color' );
			$cart_color_hover = get_option( 'yith-wcqv-button-cart-color-hover' );
			$cart_text_color = get_option( 'yith-wcqv-button-cart-text-color' );
			$cart_text_color_hover = get_option( 'yith-wcqv-button-cart-text-color-hover' );
			$details_color = get_option( 'yith-wcqv-button-details-color' );
			$details_color_hover = get_option( 'yith-wcqv-button-details-color-hover' );
			$details_text_color = get_option( 'yith-wcqv-button-details-text-color' );
			$details_text_color_hover = get_option( 'yith-wcqv-button-details-text-color-hover' );

			$image_w = ( 100 * $image_w ) / $content_w;
			$summary_w = 100 - $image_w;

			$inline_css = " .yith-quick-view.yith-modal .yith-wcqv-main{background:{$background_modal};}
			.yith-wcqv-button.inside-thumb span, .yith-wcqv-button.button{color:{$button_text_color} !important;background:{$button_color} !important;}
			.yith-wcqv-button.inside-thumb:hover span, .yith-wcqv-button.button:hover{color:{$button_text_color_hover} !important;background:{$button_color_hover} !important;}
			.yith-quick-view-content.woocommerce div.summary h1,.yith-quick-view-content.woocommerce div.summary div[itemprop=\"description\"],.yith-quick-view-content.woocommerce div.summary .product_meta,.yith-quick-view-content.woocommerce div.summary .price,.yith-quick-view-content.woocommerce div.summary .price ins {color: {$main_text_color};}
			.yith-quick-view-content.woocommerce div.summary .woocommerce-product-rating .star-rating,.yith-quick-view-content.woocommerce div.summary .woocommerce-product-rating .star-rating:before {color: {$star_color};}
			.yith-quick-view-content.woocommerce div.summary button.button.alt{background: {$cart_color};color: {$cart_text_color};}
			.yith-quick-view-content.woocommerce div.summary button.button.alt:hover{background: {$cart_color_hover};color: {$cart_text_color_hover};}
			.yith-quick-view-content.woocommerce div.summary .yith-wcqv-view-details{background: {$details_color};color: {$details_text_color};}
			.yith-quick-view-content.woocommerce div.summary .yith-wcqv-view-details:hover{background: {$details_color_hover};color: {$details_text_color_hover};}
			.yith-quick-view.yith-modal .yith-quick-view-content div.images{width:{$image_w}%;}
			.yith-quick-view.yith-modal .yith-quick-view-content div.summary{width:{$summary_w}%;}";

			wp_add_inline_style( 'yith-quick-view', $inline_css );
		}

		/**
		 * Enqueue scripts if quick view is added
		 *
		 * @access public
		 * @since 1.0.7
		 * @author Francesco Licandro
		 */
		public function enqueue_scripts() {

			wp_enqueue_script('jquery-blockui');
			wp_enqueue_script('yith-wcqv-frontend');
			wp_enqueue_script( 'external-plugin' );

			if( $this->enable_zoom ){
				wp_enqueue_script('yith-magnifier');
			}
		}

		/**
		 * Add quick view button in wc product loop
		 *
		 * @access public
		 * @param int $product_id
		 * @param string $label
		 * @param string $type
		 * @param boolean $return
		 * @return string
		 * @since  1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_add_quick_view_button( $product_id = 0, $label = '', $type = '', $return = false ) {

			global $product;

			if( ! $product_id ){
				$product_id = $product->id;
			}

			// enqueue scripts
			if( ! has_action( 'wp_footer', array( $this, 'enqueue_scripts' ) ) ) {
				add_action( 'wp_footer', array( $this, 'enqueue_scripts' ) );
			}
			// add modal template
			if( ! has_action( 'wp_footer', array( $this, 'yith_quick_view' ) ) ) {
				add_action( 'wp_footer', array( $this, 'yith_quick_view' ) );
			}

			$content = $button = '';
			! $type && $type = get_option( 'yith-wcqv-button-type' );

			if( $type === 'icon' ) {
				$icon = get_option( 'yith-wcqv-button-icon' );
				$content = '<img src="' . esc_url( $icon ) . '" class="yith-wcqv-icon"/>';
			}
			else {
				! $label && $label = get_option( 'yith-wcqv-button-label' );
				$content = '<span>' . esc_html( $label ) . '</span>';
			}

			if( $this->position == 'image' ) {
				$button = '<div class="yith-wcqv-button inside-thumb" data-product_id="' . $product_id . '">'. $content . '</div>';
			}
			else {
				$class = ( $type === 'button' ) ? 'button' : 'qvicon';
				$button = '<a href="#" class="yith-wcqv-button ' . $class .'" data-product_id="' . $product_id . '">' . $content . '</a>';
			}

			// let's third part filter button html
			$button = apply_filters( 'yith_wcqv_button_html', $button, $product_id, $content );

			if( $return ) {
				return $button;
			}
			else {
				echo $button;
			}
		}

		/**
		 * Add quick view button in wishlist table
		 *
		 * @access public
		 * @since 1.0.7
		 * @author Francesco Licandro
		 */
		public function add_quick_view_button_in_wishlist() {
			global $product;

			if( get_option( 'yith-wcqv-enable-wishlist' ) != 'yes' || get_option( 'yith-wcqv-modal-type' ) != 'yith-modal' ){
				return;
			}

			// enqueue scripts
			if( ! has_action( 'wp_footer', array( $this, 'enqueue_scripts' ) ) ) {
				add_action( 'wp_footer', array( $this, 'enqueue_scripts' ) );
			}
			// add modal template
			if( ! has_action( 'wp_footer', array( $this, 'yith_quick_view' ) ) ) {
				add_action( 'wp_footer', array( $this, 'yith_quick_view' ) );
			}

			$label = esc_html( get_option( 'yith-wcqv-button-label' ) );

			echo '<a href="#" class="yith-wcqv-button button" data-product_id="' . $product->id . '">' . $label . '</a>';
		}

		/**
		 * Enqueue scripts and pass variable to js used in quick view
		 *
		 * @access public
		 * @return bool
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_woocommerce_quick_view() {

			wp_enqueue_script( 'wc-add-to-cart-variation' );

			// enqueue wc color e label variation style
			wp_enqueue_script( 'yith_wccl_frontend' );
			wp_enqueue_style( 'yith_wccl_frontend' );

			$lightbox_en = get_option( 'yith-wcqv-enable-lightbox' ) == 'yes';
			$mobile      = wp_is_mobile();
			$type 		 = get_option( 'yith-wcqv-modal-type' );

			// if enabled load prettyPhoto css
			if( $lightbox_en ) {

				$assets_path = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';

				wp_enqueue_script( 'prettyPhoto', $assets_path . 'js/prettyPhoto/jquery.prettyPhoto.min.js', array( 'jquery' ), false, true );
				wp_enqueue_style( 'woocommerce_prettyPhoto_css', $assets_path . 'css/prettyPhoto.css' );
			}

			$version = version_compare( preg_replace( '/-beta-([0-9]+)/', '', WC()->version ), '2.3.0', '<' );

			// loader gif
			$loader = apply_filters( 'yith_quick_view_loader_gif', YITH_WCQV_ASSETS_URL . '/image/qv-loader.gif' );

			// ajax add to cart
			$ajax_cart = get_option( 'yith-wcqv-ajax-add-to-cart' ) == 'yes';

			// added compatibility with woocommerce-quantity-increment plugin
			$increment_plugin = class_exists( 'WooCommerce_Quantity_Increment' );

			// Allow user to load custom style and scripts
			do_action( 'yith_quick_view_custom_style_scripts' );

			// selectors
			$main_product = 'li.product';
			$main_product_link = 'li.product > a';

			wp_localize_script( 'yith-wcqv-frontend', 'yith_qv', array (
					'ajaxurl'           => admin_url( 'admin-ajax.php', 'relative' ),
					'loader'            => $loader,
					'is2_2'             => $version,
					'increment_plugin'  => $increment_plugin, // added compatibility with woocommerce-quantity-increment plugin
					'type'              => $type,
					'ismobile'          => $mobile,
					'ajaxcart'          => $ajax_cart,
					'enable_loading'    => get_option( 'yith-wcqv-enable-loading-effect' ) == 'yes',
					'loading_text'      => get_option( 'yith-wcqv-enable-loading-text' ),
					'enable_zoom'       => get_option( 'yith-wcqv-enable-zoom-magnifier' ) == 'yes',
					'redirect_checkout' => get_option( 'yith-wcqv-ajax-redirect-to-checkout' ) == 'yes',
					'checkout_url'      => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : WC()->cart->get_checkout_url(),
					'main_product'      => apply_filters( 'yith_wcqv_main_product_selector', $main_product ),
					'main_product_link' => apply_filters( 'yith_wcqv_main_product_link_selector', $main_product_link ),
					'popup_size_width'  => get_option( 'yith-quick-view-modal-width', '1000' ),
					'popup_size_height' => get_option( 'yith-quick-view-modal-height', '500' ),
				)
			);

			return true;
		}

		/**
		 * Ajax action to load product in quick view
		 *
		 * @access public
		 * @return void
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_load_product_quick_view_ajax() {

			if ( ! isset( $_REQUEST['product_id'] ) ) {
				die();
			}

			$product_id = intval( $_REQUEST['product_id'] );

			$images_type = get_option('yith-wcqv-product-images-mode');
			$nav = get_option( 'yith-wcqv-enable-nav' ) == 'yes';
			$in_same_cat = get_option( 'yith-wcqv-enable-nav-same-category') == 'yes';
			$style = get_option( 'yith-wcqv-nav-style' );


			// remove product thumbnails gallery
			remove_all_actions( 'woocommerce_product_thumbnails' );
			// remove badge from nav
			if( function_exists( 'YITH_WCBM_Frontend' ) ) {
				remove_filter( 'post_thumbnail_html', array( YITH_WCBM_Frontend(), 'add_box_thumb' ), 999 );
			}

			// set the main wp query for the product
			wp( 'p=' . $product_id . '&post_type=product' );

			$prev_product_id = $next_product_id = false;

			if( $nav ) {
				// if in same cat get next e prev post
				if( $in_same_cat ) {
					// prev
					$prev_product       = wc_get_product( get_previous_post( $in_same_cat, '', 'product_cat' ) );
					( $prev_product && ! post_password_required( $prev_product->id ) ) && $prev_product_id = $prev_product->id;

					// next
					$next_product       = wc_get_product( get_next_post( $in_same_cat, '', 'product_cat' ) );
					( $next_product && ! post_password_required( $next_product->id ) ) && $next_product_id = $next_product->id;
				}
				else {
					isset( $_REQUEST[ 'prev_product_id'] ) && $prev_product_id = intval( $_REQUEST[ 'prev_product_id'] );
					isset( $_REQUEST[ 'next_product_id'] ) && $next_product_id = intval( $_REQUEST[ 'next_product_id'] );
				}
			}

			// Prev Product Preview
			$prev_product_preview   = $prev_product_id ? get_the_post_thumbnail( $prev_product_id, 'shop_thumbnail' ) : '';
			$prev_product_preview   .= ( $prev_product_id && $style != 'diamond' ) ? '<h4>' . get_the_title( $prev_product_id ) . '</h4>' : '';

			// Next Product Preview
			$next_product_preview   = $next_product_id ? get_the_post_thumbnail( $next_product_id, 'shop_thumbnail' ) : '';
			$next_product_preview   .= ( $next_product_id && $style != 'diamond' ) ? '<h4>' . get_the_title( $next_product_id ) . '</h4>' : '';


			// re-add badge from nav
			if( function_exists( 'YITH_WCBM_Frontend' ) ) {
				add_filter( 'post_thumbnail_html', array( YITH_WCBM_Frontend(), 'add_box_thumb' ), 999, 2 );
			}

			switch ($images_type) {
				case 'classic' :
					add_action( 'woocommerce_product_thumbnails', array( $this, 'yith_quick_view_attachments_images' ) );
					break;
				case 'slider' :
					add_filter( 'woocommerce_single_product_image_html', array( $this, 'yith_quick_view_images_slider' ), 99, 2 );
					break;
				case 'none':
				default:
					break;
			}

			// add hidden to add to cart form
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_hidden_to_cart_form' ) );

			// change template for variable products
			$attributes = false;
			if ( isset( $GLOBALS['yith_wccl'] ) ) {
				$GLOBALS['yith_wccl']->obj = new YITH_WCCL_Frontend( YITH_WCCL_VERSION );
				$GLOBALS['yith_wccl']->obj->override();
			}
			elseif( defined( 'YITH_WCCL_PREMIUM' ) && YITH_WCCL_PREMIUM && class_exists( 'YITH_WCCL_Frontend' ) ) {
				$attributes = YITH_WCCL_Frontend()->create_attributes_json( $product_id, true );
			}

			// Allow custom action for user
			do_action( 'yith_load_product_quick_view_custom_action' );

			$product_link = get_permalink( $product_id );
			if( is_ssl() ){
				$product_link = str_replace('http://', 'https://', $product_link );
			}

			ob_start();

			// load content template
			wc_get_template( 'yith-quick-view-content.php', array(), '', YITH_WCQV_DIR . 'templates/' );

			$html = ob_get_clean();

			echo json_encode( array (
				"html"                  => $html,
				"prod_attr"             => $attributes,
				"prev_product"          => $prev_product_id,
				"prev_product_preview"  => $prev_product_preview,
				"next_product"          => $next_product_id,
				"next_product_preview"  => $next_product_preview,
				'images_type'           => $images_type,
				'product_link'          => $product_link
			));

			die();
		}

		/**
		 * Add hidden to add to cart form
		 *
		 * @since 1.1.0
		 * @author Francesco Licandro
		 */
		public function add_hidden_to_cart_form(){
			echo '<input type="hidden" name="yith_is_quick_view" id="yith_is_quick_view" value="1"/>';
		}

		/**
		 * Return the checkout url
		 *
		 * @since 1.1.0
		 * @param string $url
		 * @return string
		 * @author Francesco Licandro
		 */
		public function get_checkout_url( $url ){
			// add redirect to checkout
			if( get_option( 'yith-wcqv-ajax-redirect-to-checkout' ) != 'yes' || ! isset( $_REQUEST['yith_is_quick_view'] ) ){
				return $url;
			}

			return function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : WC()->cart->get_checkout_url();
		}

		/**
		 * Load quick view template
		 *
		 * @access public
		 * @return void
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_quick_view() {
			$this->yith_woocommerce_quick_view();

			$type = get_option( 'yith-wcqv-modal-type' );

			$args = array(
				'type'      => $type,
				'effect'    => ( $type == 'yith-modal' ) ? get_option( 'yith-quick-view-modal-effect' ) : '',
				'nav'       => get_option( 'yith-wcqv-enable-nav' ) == 'yes',
				'nav_style' => get_option( 'yith-wcqv-nav-style' ),
				'is_mobile'  => wp_is_mobile() ? 'is-mobile' : ''
			);

			$args = apply_filters( 'yith_args_quick_view_template', $args );

			wc_get_template( 'yith-quick-view.php', $args, '', YITH_WCQV_DIR . 'templates/' );
		}

		/**
		 * Load share template for single product in quick view
		 *
		 * @access public
		 * @return void
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_quick_view_share() {
			wc_get_template( 'yith-quick-view-share.php', array(), '', YITH_WCQV_DIR . 'templates/' );
		}

		/**
		 * Load wc action for quick view product template
		 *
		 * @access public
		 * @return void
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_quick_view_action_template() {

			global $yith_wcmg;

			if( get_option( 'yith-wcqv-product-show-thumb') == 'yes' ) {

				add_action( 'yith_wcqv_product_image', 'woocommerce_show_product_sale_flash', 10 );

				if( defined( 'YITH_WCMG' ) && YITH_WCMG && $this->enable_zoom && $yith_wcmg && method_exists( $yith_wcmg->obj, 'show_product_images' ) ) {
					add_action( 'yith_wcqv_product_image', array( $yith_wcmg->obj, 'show_product_images' ), 20 );
				}
				elseif( get_option( 'yith-wcqv-custom-images-html' ) == 'yes' ) {
					add_action( 'yith_wcqv_product_image', array( $this, 'custom_thumb_html' ) );
				}
				else {
					add_action( 'yith_wcqv_product_image', 'woocommerce_show_product_images', 20 );
				}
			}
			if( get_option( 'yith-wcqv-product-show-title') == 'yes' ) {
				add_action( 'yith_wcqv_product_summary', 'woocommerce_template_single_title', 5 );
			}
			if( get_option( 'yith-wcqv-product-show-rating') == 'yes' ) {
				add_action( 'yith_wcqv_product_summary', 'woocommerce_template_single_rating', 10 );
			}
			if( get_option( 'yith-wcqv-product-show-price') == 'yes' ) {
				add_action( 'yith_wcqv_product_summary', 'woocommerce_template_single_price', 15 );
			}
			if( get_option( 'yith-wcqv-product-show-excerpt') == 'yes' ) {

				if( get_option( 'yith-wcqv-product-full-description' ) == 'no' ) {
					add_action( 'yith_wcqv_product_summary', 'woocommerce_template_single_excerpt', 20 );
				}
				else {
					add_action( 'yith_wcqv_product_summary', array( $this, 'get_full_description' ), 20 );
				}
			}
			if( get_option( 'yith-wcqv-product-show-add-to-cart') == 'yes' ) {
				add_action( 'yith_wcqv_product_summary', 'woocommerce_template_single_add_to_cart', 25 );
			}
			if( get_option( 'yith-wcqv-details-button') == 'yes' ) {
				add_action( 'yith_wcqv_product_summary',  array( $this, 'yith_add_view_details_button' ), 27 );
			}
			if( defined( 'YITH_WCWL' ) && YITH_WCWL && get_option( 'yith-wcqv-product-show-wishlist', 'yes' ) == 'yes' ) {
				add_action( 'yith_wcqv_product_summary', array( $this, 'yith_wishlist_quick_view' ), 27 );
			}
			if ( shortcode_exists( 'yith_compare_button' ) && get_option( 'yith-wcqv-product-show-compare', 'yes' ) == 'yes' ) {
				add_action( 'yith_wcqv_product_summary', array( $this, 'yith_compare_quick_view' ), 27 );
			}
			if( defined( 'YITH_YWRAQ_VERSION' ) && class_exists( 'YITH_YWRAQ_Frontend' ) && get_option( 'yith-wcqv-product-show-quote', 'yes' ) == 'yes' ) {
				add_action( 'yith_wcqv_product_summary', array( YITH_YWRAQ_Frontend(), 'add_button_single_page' ), 30 );
				add_action( 'yith_wcqv_product_summary', array( $this, 'hide_add_to_cart' ), 30 );
			}
			if( get_option( 'yith-wcqv-product-show-meta') == 'yes' ) {
				add_action( 'yith_wcqv_product_summary', 'woocommerce_template_single_meta', 30 );
			}
			if( get_option( 'yith-wcqv-enable-share' ) == 'yes'  ) {
				add_action( 'yith_wcqv_product_summary', array( $this, 'yith_quick_view_share' ), 35 );
			}
		}

		/**
		 * Print custom image thumb html instead of woocommerce standard
		 *
		 * @access public
		 * @since 1.0.7
		 * @author Francesco Licandro
		 */
		public function custom_thumb_html(){
			global $post, $product;

			$images_type = get_option( 'yith-wcqv-product-images-mode' );
			$html = '<div class="images">';

			if( $images_type == 'slider' ) {
				$html .= $this->yith_quick_view_images_slider( '', 0, true );
			}
			else {
				// get main image
				$image_id 		= $product->get_image_id();
				$image_title 	= esc_attr( get_the_title( $image_id ) );
				$image_caption 	= get_post( $image_id )->post_excerpt;
				$image_link  	= wp_get_attachment_url( $image_id );
				$image 			= $product->get_image( 'quick_view_image_size', array( 'title' => $image_title, 'alt' => $image_title ));

				$html .= '<a href="' . $image_link . '" itemprop="image" class="woocommerce-main-image zoom" title="'. $image_caption. '" data-rel="prettyPhoto[product-gallery]">' . $image . '</a>';

				if( $images_type == 'classic' ) {
					// get attachments
					$attachments    = array_merge( array( $image_id ), $product->get_gallery_attachment_ids() );
					$html .= $this->yith_quick_view_attachments_images( $attachments, true );
				}
			}

			$html .= '</div>';

			// let's third part filter html
			$html = apply_filters( 'yith_wcqv_product_image_html', $html );

			echo $html;
		}

		/**
		 * Get full description instead of short
		 *
		 * @access public
		 * @since 1.0.7
		 * @author Francesco Licandro
		 */
		public function get_full_description(){
			global $post;

			ob_start();
			?>

			<div itemprop="description">
				<?php the_content(); ?>
			</div>

			<?php
			echo ob_get_clean();
		}

		/**
		 * Print wishlist shortcode in quick view
		 *
		 * @access  public
		 * @since 1.0.0
		 * @return void
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_wishlist_quick_view() {
			echo do_shortcode('[yith_wcwl_add_to_wishlist]');
		}

		/**
		 * Print compare shortcode in quick view
		 *
		 * @access  public
		 * @since 1.0.3
		 * @return void
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_compare_quick_view() {
			echo do_shortcode( '[yith_compare_button type="link"]' );
		}

		/**
		 * Add attachments images to a single image in quick view
		 *
		 * @access public
		 * @param array $attachments
		 * @param boolean $return
		 * @return void | string
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_quick_view_attachments_images( $attachments = array(), $return = false ) {

			global $post, $product;

			if( empty( $attachments ) ) {
				$attachments 	= array();
				$attachments[]  = get_post_thumbnail_id();
				$attachments    = array_merge( $attachments, $product->get_gallery_attachment_ids() );
			}

			$first = true;

			if( $return ) {
				ob_start();
			}

			?>
			<div class="yith-quick-view-thumbs">
					<?php foreach ( $attachments as $attachment ) : ?>

						<?php
						$src        = wp_get_attachment_image_src( $attachment, 'quick_view_image_size' );
						$image_link = wp_get_attachment_url( $attachment );
						?>

						<div class="yith-quick-view-single-thumb <?php echo $first ? 'active' : '' ?>" data-rel="prettyPhoto[product-gallery]"
						     data-img="<?php echo $src[0] ?>"
						     data-href="<?php echo $image_link ?>"
						     data-attachment_id="<?php echo $attachment ?>" >
							<?php echo wp_get_attachment_image( $attachment, 'shop_thumbnail' ); ?>
						</div>

						<?php $first = false; ?>
					<?php endforeach; ?>
			</div>
			<?php

			if( $return ) {
				return ob_get_clean();
			}
		}

		/**
		 * Add image slider instead a single image in quick view
		 *
		 * @access public
		 * @param string $html
		 * @param int $post_id
		 * @param boolean $return
		 * @return void | string
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_quick_view_images_slider( $html = '', $post_id = 0, $return = false ) {

			global $post, $product;

			$image = get_the_post_thumbnail( $post->ID, 'quick_view_image_size' );
			$attachments = $product->get_gallery_attachment_ids();

			if( $return ) {
				ob_start();
			}
			?>

			<div class="yith-quick-view-images-slider">
				<div class="yith-quick-view-slides">

					<?php echo $image ?>

					<?php foreach ( $attachments as $attachment ) : ?>
						<?php echo wp_get_attachment_image( $attachment, 'quick_view_image_size' ); ?>
					<?php endforeach; ?>

				</div>

				<?php if ( ! empty( $attachments ) ) : ?>
					<div class="es-nav">
						<div class="es-nav-prev"></div>
						<div class="es-nav-next"></div>
					</div>
				<?php endif; ?>

			</div>

		<?php

			if( $return ) {
				return ob_get_clean();
			}
		}

		/**
		 * Filter image size if is in quick view
		 *
		 * @access  public
		 * @param $size string
		 * @return string
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_set_image_size( $size ) {

			if( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return 'quick_view_image_size';
			}

			return $size;
		}

		/**
		 * Add View Details button in quick view
		 *
		 * @access public
		 * @return void
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_add_view_details_button() {

			global $product;

			$label = esc_html( get_option( 'yith-wcqv-details-button-label' ) );
			$link  = get_permalink( $product->id );

			echo '<a href="' . $link . '" class="yith-wcqv-view-details button">' . $label . '</a>';
		}

		/**
		 * Check if is quick view
		 *
		 * @access public
		 * @return bool
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_is_quick_view() {
			return ( defined('DOING_AJAX') && DOING_AJAX && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'yith_load_product_quick_view' ) ? true : false;
		}

		/**
		 * Add attachment id to variation data in select variation form
		 *
		 * @since 1.0.4
		 *
		 * @param mixed $attr default array
		 * @param $variable
		 * @param $variation
		 * @return mixed
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function add_attachment_id( $attr, $variable, $variation ) {
			if( ! $this->yith_is_quick_view() ) {
				return $attr;
			}

			if ( has_post_thumbnail( $variation->get_variation_id() ) ) {
				$attachment_id = get_post_thumbnail_id( $variation->get_variation_id() );
				$attr['attachment_id'] = $attachment_id;
			}

			return $attr;
		}

		/**
		 * Set action for get correnct currency in woocommerce multilingual
		 *
		 * @access public
		 * @param array $action
		 * @return array
		 * @author Francesco Licandro
		 */
		public function set_correct_currency( $action ){
			$action[] = 'yith_load_product_quick_view';

			return $action;
		}

		/**
		 * Quick View shortcode button
		 *
		 * @access public
		 * @since 1.0.7
		 * @param array $atts
		 * @return string
		 * @author Francesco Licandro
		 */
		public function quick_view_shortcode( $atts ) {

			$atts = shortcode_atts(array(
				'product_id' => 0,
				'label'		 => '',
				'type'       => ''
			), $atts );

			extract( $atts );

			if( ! intval( $product_id ) ) {
				return '';
			}

			return $this->yith_add_quick_view_button( $product_id, $label, $type, true );
		}

		/**
		 * Hide add to cart for plugin Request a Quote on quick view
		 *
		 * @since 1.1.5
		 * @author Francesco Licandro
		 */
		public function hide_add_to_cart(){
			global $product;

			if ( get_option( 'ywraq_hide_add_to_cart' ) == 'yes' ) {
				if ( $product->product_type == 'variable' ) {
					$css = ".single_variation_wrap .variations_button button{
	                 display:none!important;
	                }";
				} else {
					$css = ".cart button.single_add_to_cart_button{
	                 display:none!important;
	                }";
				}

				echo '<style>'.$css.'</style>';
			}
		}
	}
}
/**
 * Unique access to instance of YITH_WCQV_Frontend class
 *
 * @return \YITH_WCQV_Frontend
 * @since 1.0.0
 */
function YITH_WCQV_Frontend(){
	return YITH_WCQV_Frontend::get_instance();
}