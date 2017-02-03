<?php
/**
 * Frontend class
 *
 * @author Yithemes
 * @package YITH WooCommerce Frequently Bought Together Premium
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WFBT' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WFBT_Frontend' ) ) {
	/**
	 * Frontend class.
	 * The class manage all the frontend behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WFBT_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WFBT_Frontend
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $version = YITH_WFBT_VERSION;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WFBT_Frontend
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

			// enqueue scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

			add_action( 'template_redirect', array( $this, 'before_add_form' ) );

			// ajax update product variation
			add_action( 'wp_ajax_yith_update_variation_product', array( $this, 'update_variation_product_ajax' ) );
			add_action( 'wp_ajax_nopriv_yith_update_variation_product', array( $this, 'update_variation_product_ajax' ) );
			
			add_shortcode( 'ywfbt_form', array( $this, 'wfbt_shortcode' ) );

			// register shortcode
			add_shortcode( 'yith_wfbt', array( $this, 'bought_together_shortcode' ) );
		}

		/**
		 * Register scripts and styles for plugin
		 *
		 * @since 1.0.4
		 * @author Francesco Licandro
		 */
		public function register_scripts(){

			$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$assets_path  = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';

			wp_register_script( 'jquery-blockui', $assets_path . 'js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.60' );

			$paths      = apply_filters( 'yith_wfbt_stylesheet_paths', array( WC()->template_path() . 'yith-wfbt-frontend.css', 'yith-wfbt-frontend.css' ) );
			$located    = locate_template( $paths, false, false );
			$search     = array( get_stylesheet_directory(), get_template_directory() );
			$replace    = array( get_stylesheet_directory_uri(), get_template_directory_uri() );
			$stylesheet = ! empty( $located ) ? str_replace( $search, $replace, $located ) : YITH_WFBT_ASSETS_URL . '/css/yith-wfbt.css';

			wp_register_style( 'yith-wfbt-style', $stylesheet );
			wp_register_script( 'yith-wfbt', YITH_WFBT_ASSETS_URL . '/js/yith-wfbt' . $suffix . '.js', array( 'jquery', 'jquery-blockui' ), $this->version, true );

			// register script for carousel
			wp_register_style( 'yith-wfbt-carousel-style', YITH_WFBT_ASSETS_URL . '/css/owl.carousel.css' );
			wp_register_script( 'yith-wfbt-carousel-js', YITH_WFBT_ASSETS_URL . '/js/owl.carousel.min.js', array('jquery'), false, true );
		}

		/**
		 * Enqueue scripts
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function enqueue_scripts(){

			wp_enqueue_script( 'jquery-blockui' );
			wp_enqueue_script( 'yith-wfbt' );

			wp_localize_script( 'yith-wfbt', 'yith_wfbt', array(
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'currency_symbol' 	=> get_woocommerce_currency_symbol(),
				'loader'            => get_option( 'yith-wfbt-loader' ),
				'currency_pos'      => get_option( 'woocommerce_currency_pos' ),
				'label_single'      => esc_html( get_option( 'yith-wfbt-button-single-label' ) ),
				'label_double'      => esc_html( get_option( 'yith-wfbt-button-double-label' ) ),
				'label_three'       => esc_html( get_option( 'yith-wfbt-button-three-label' ) ),
				'label_multi'       => esc_html( get_option( 'yith-wfbt-button-multi-label' ) ),
				'total_single'      => esc_html( get_option( 'yith-wfbt-total-single-label' ) ),
				'total_double'      => esc_html( get_option( 'yith-wfbt-total-double-label' ) ),
				'total_three'       => esc_html( get_option( 'yith-wfbt-total-three-label' ) ),
				'total_multi'       => esc_html( get_option( 'yith-wfbt-total-multi-label' ) ),
				'visible_elem'      => get_option( 'yith-wfbt-slider-elems' )
			));

			wp_enqueue_style( 'yith-wfbt-style' );

			$form_background    = get_option( 'yith-wfbt-form-background-color' );
			$background         = get_option( "yith-wfbt-button-color" );
			$background_hover   = get_option( "yith-wfbt-button-color-hover" );
			$text_color         = get_option( "yith-wfbt-button-text-color" );
			$text_color_hover   = get_option( "yith-wfbt-button-text-color-hover" );

			$inline_css = "
                .yith-wfbt-submit-block .yith-wfbt-submit-button{background: {$background} !important;color: {$text_color} !important;border-color: {$background} !important;}
                .yith-wfbt-submit-block .yith-wfbt-submit-button:hover{background: {$background_hover} !important;color: {$text_color_hover} !important;border-color: {$background_hover} !important;}
                .yith-wfbt-form{background: {$form_background};}";

			wp_add_inline_style( 'yith-wfbt-style', $inline_css );
		}

		/**
		 * Handle action before print form
		 *
		 * @since 1.0.4
		 * @author Francesco Licandro
		 */
		public function before_add_form(){

			global $post;

			if( is_null( $post ) ){
				return;
			}

			$position = get_option( 'yith-wfbt-form-position' );

			if( $post->post_type != 'product' || $position == 4 ) {
				return;
			}

			// include style and scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );

			// print form

			switch( $position ){
				case 1:
					add_action( 'woocommerce_single_product_summary', array( $this, 'add_bought_together_form' ), 99 );
					break;
				case 2:
					add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_bought_together_form' ), 5 );
					break;
				case 3:
					add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_bought_together_form' ), 99 );
					break;
			}
		}

		/**
		 * Form Template
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function add_bought_together_form( $product_id = false ){

			global $sitepress;

			if( ! $product_id ) {
				global $product;
			}
			else {
				// make sure to get always translated products
				$product_id = function_exists('wpml_object_id_filter') ? wpml_object_id_filter( $product_id, 'product', true ) : $product_id;
				$product = wc_get_product( $product_id );
			}

			if( ! $product )
				return;

			$product_id = $product->id;

			// exit if grouped or external
			if( $product->product_type == 'grouped' || $product->product_type == 'external' || ! $this->can_be_added( $product ) ) {
				return;
			}

			// get meta for current product
			$group      = get_post_meta( $product_id, YITH_WFBT_META, true );
			$num        = intval( get_post_meta( $product_id, YITH_WFBT_META_NUM, true ) );
			$variation  = false;

			// if group is empty
			// first get group of original product
			$original_product_id = false;
			if( empty( $group ) && function_exists( 'wpml_object_id_filter' ) && get_option( 'yith-wcfbt-wpml-association', 'yes' ) == 'yes' ) {
				$original_product_id = wpml_object_id_filter( $product_id, 'product', true, $sitepress->get_default_language() );
				$group               = get_post_meta( $original_product_id, YITH_WFBT_META, true );
				$num                 = intval( get_post_meta( $original_product_id, YITH_WFBT_META_NUM, true ) );
			}

			if( empty( $group ) ) {
				return;
			}

			if( $product->product_type == 'variable' ) {

				$default_variation = '';
				
				if( $original_product_id ){
					$default_variation = get_post_meta( $original_product_id, YITH_WFBT_META_VARIATION, true );
					$default_variation = function_exists( 'wpml_object_id_filter' ) ? wpml_object_id_filter( $default_variation, 'product', false ) : $original_product_id;
				}
				else {
					$default_variation = get_post_meta( $product_id, YITH_WFBT_META_VARIATION, true );
				}

				// if empty exit
				if( ! $default_variation || is_null( $default_variation ) ) {
				    return;
                }

				// get product variation if is variable
				$product_variation = wc_get_product( $default_variation );
				
				if( ! $this->can_be_added( $product_variation ) ) {
					$variations = $product->get_children();
					$find = false;

					if( is_array( $variations ) ){
						foreach ( $variations as $variation ) {
							if( $variation == $default_variation ){
								continue;
							}
							$product_variation = wc_get_product( $variation );
							if( $this->can_be_added( $product_variation ) ) {
								$find = true;
								break;
							}
						}
					}
					
					if( ! $find ) {
						return;
					}
				}
				
				$product = $product_variation;				
			}

			// if $num is empty set it to 2
			! $num && $num = 2;
			// sort random array key products
			shuffle( $group );

			$products[] = $product;
			$index = 0; 
			foreach( $group as $the_id ) {
				if( $index >= $num ) {
					break;
				}
				$the_id = function_exists( 'wpml_object_id_filter' ) ? wpml_object_id_filter( $the_id, 'product', false ) : $the_id;
				if( is_null( $the_id ) ){
					continue;
				}
				$current = wc_get_product( $the_id );
				if( ! $this->can_be_added( $current ) ) {
					continue;
				}
				// add to main array
				$products[] = $current;
				$index++;
			}

			// exit if $products have only one element
			if( count( $products ) < 2 ) {
				return;
			}

			wc_get_template( 'yith-wfbt-form.php', array( 'products' => $products ), '', YITH_WFBT_DIR . 'templates/' );
		}

		/**
		 * Check if product can be added to frequently form
		 *
		 * @access public
		 * @since 1.0.5
		 * @author Francesco Licandro
		 * @param object|int $product
		 * @return boolean
		 */
		public function can_be_added( $product ) {

			if( ! is_object( $product ) ) {
				$product = wc_get_product( intval( $product ) );
			}

			$can = ( $product->is_in_stock() || $product->backorders_allowed() ) && $product->is_purchasable();

			return apply_filters( 'yith_wfbt_product_can_be_added', $can, $product);
		}

		/**
		 * Frequently Bought Together Shortcode
		 *
		 * @since 1.0.5
		 * @param array $atts
		 * @author Francesco Licandro
		 */
		public function wfbt_shortcode( $atts ){

			$atts = shortcode_atts(array(
					'product_id' => 0
			), $atts );

			extract( $atts );

			// include style and scripts
			$this->enqueue_scripts();

			$this->add_bought_together_form( intval( $product_id ) );
		}

		/**
		 * Get variation thumbnail in ajax
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function update_variation_product_ajax(){

			if( ! isset( $_REQUEST[ 'product_id' ] ) ) {
				die();
			}

			$id = intval( $_REQUEST[ 'product_id' ] );

			// get product
			$product = wc_get_product( $id );
			$attributes_html = '';

			if( $product->product_type == 'variation' ) {
				$attributes = $product->get_variation_attributes();
				$variations = array();

				foreach( $attributes as $key => $attribute ) {
					$key = str_replace( 'attribute_', '', $key );

					$terms = get_terms( sanitize_title( $key ), array(
						'menu_order' => 'ASC',
						'hide_empty' => false
					) );

					foreach ( $terms as $term ) {
						if ( ! is_object( $term ) || ! in_array( $term->slug, array( $attribute ) ) ) {
							continue;
						}
						$variations[] = $term->name;
					}
				}

				if( ! empty( $variations ) )
					$attributes_html = ' &ndash; ' . implode( ', ', $variations );
			}

			echo json_encode( array (
				"image"         => $product->get_image( 'yith_wfbt_image_size' ),
				"attributes"    => $attributes_html
			));

			die();
		}


		/**
		 * Register Frequently Bought Together shortcode
		 *
		 * @since 1.0.0
		 * @param mixed $atts
		 * @param null $content
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function bought_together_shortcode( $atts, $content = null ) {

			extract(shortcode_atts(array(
					"products"      => ""
			), $atts) );

			$products = explode( ",", $products );
			$elems = array();

			// take products to show
			foreach( $products as $product_id ) {
				$product_metas = get_post_meta( $product_id, YITH_WFBT_META, true );

				if( ! $product_metas ) {
					continue;
				}

				foreach( $product_metas as $meta ) {
					$meta_obj = wc_get_product( $meta );
					// add elem only if is not present in array products
					if( ! in_array( $meta_obj->id, $products ) ) {
						$elems[] = $meta;
					}
				}
			}
			// remove duplicate
			$elems = array_unique( $elems );

			if( empty( $elems ) )
				return;

			$this->enqueue_scripts();

			wc_get_template( 'yith-wfbt-shortcode.php', array( 'products' => $elems ), '', YITH_WFBT_DIR . 'templates/' );
		}
	}
}
/**
 * Unique access to instance of YITH_WFBT_Frontend class
 *
 * @return \YITH_WFBT_Frontend
 * @since 1.0.0
 */
function YITH_WFBT_Frontend(){
	return YITH_WFBT_Frontend::get_instance();
}