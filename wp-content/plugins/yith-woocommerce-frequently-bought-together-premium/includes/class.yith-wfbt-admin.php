<?php
/**
 * Admin class
 *
 * @author Yithemes
 * @package YITH WooCommerce Frequently Bought Together Premium
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WFBT' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WFBT_Admin' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WFBT_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WFBT_Admin
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugin options
		 *
		 * @var array
		 * @access public
		 * @since 1.0.0
		 */
		public $options = array();

		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $version = YITH_WFBT_VERSION;

		/**
		 * @var $_panel Panel Object
		 */
		protected $_panel;

		/**
		 * @var string Waiting List panel page
		 */
		protected $_panel_page = 'yith_wfbt_panel';

		/**
		 * Various links
		 *
		 * @var string
		 * @access public
		 * @since 1.0.0
		 */
		public $doc_url = 'http://yithemes.com/docs-plugins/yith-woocommerce-frequently-bought-together/';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WFBT_Admin
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

			add_action( 'admin_menu', array( $this, 'register_panel' ), 5) ;

			//Add action links
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WFBT_DIR . '/' . basename( YITH_WFBT_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

			// register plugin to licence/update system
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			// enqueue style and scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// custom tab
			add_action( 'yith_wfbt_data_table', array( $this, 'data_table' ) );

			// add section in product edit page
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_bought_together_tab' ), 10, 1 );
			add_action( 'woocommerce_product_data_panels', array( $this, 'add_bought_together_panel') );
			// ajax update list of variation for variable product
			add_action( 'wp_ajax_yith_update_variation_list', array( $this, 'yith_ajax_update_variation_list' ) );
			add_action( 'wp_ajax_nopriv_yith_update_variation_list', array( $this, 'yith_ajax_update_variation_list' ) );
			// search product
			add_action( 'wp_ajax_yith_ajax_search_product', array( $this, 'yith_ajax_search_product' ) );
			add_action( 'wp_ajax_nopriv_yith_ajax_search_product', array( $this, 'yith_ajax_search_product' ) );
			// save tabs options
			add_action( 'woocommerce_process_product_meta_simple', array( $this, 'save_bought_together_tab' ), 10, 1 );
			add_action( 'woocommerce_process_product_meta_variable', array( $this, 'save_bought_together_tab' ), 10, 1 );
			add_action( 'woocommerce_process_product_meta_grouped', array( $this, 'save_bought_together_tab' ), 10, 1 );
			add_action( 'woocommerce_process_product_meta_external', array( $this, 'save_bought_together_tab' ), 10, 1 );

			// add custom image size type
			add_action( 'woocommerce_admin_field_yith_image_size', array( $this, 'custom_image_size' ), 10, 1 );
		}

		/**
		 * Action Links
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $links | links plugin array
		 *
		 * @return   mixed Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return mixed
		 * @use plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-frequently-bought-together' ) . '</a>';

			return $links;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			$admin_tabs = array(
				'general' => __( 'Settings', 'yith-woocommerce-frequently-bought-together' ),
				'data'    => __( 'Linked Products', 'yith-woocommerce-frequently-bought-together' )
			);

			if( defined('YITH_WCWL') && YITH_WCWL ) {
				$admin_tabs['slider'] = __( 'Slider in Wishlist', 'yith-woocommerce-frequently-bought-together' );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => _x( 'Frequently Bought Together', 'plugin name in admin page title', 'yith-woocommerce-frequently-bought-together' ),
				'menu_title'       => _x( 'Frequently Bought Together', 'plugin name in admin WP menu', 'yith-woocommerce-frequently-bought-together' ),
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => apply_filters( 'yith-wfbt-admin-tabs', $admin_tabs ),
				'options-path'     => YITH_WFBT_DIR . '/plugin-options'
			);

			/* === Fixed: not updated theme  === */
			if( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once( YITH_WFBT_DIR . '/plugin-fw/lib/yit-plugin-panel-wc.php' );
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

			add_action( 'woocommerce_admin_field_yith_wfbt_upload', array( $this->_panel, 'yit_upload' ), 10, 1 );
		}

		/**
		 * plugin_row_meta
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $plugin_meta
		 * @param $plugin_file
		 * @param $plugin_data
		 * @param $status
		 *
		 * @return   Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

			if ( defined( 'YITH_WFBT_INIT') && YITH_WFBT_INIT == $plugin_file ) {
				$plugin_meta[] = '<a href="' . $this->doc_url . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-frequently-bought-together' ) . '</a>';
			}

			return $plugin_meta;
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once( YITH_WFBT_DIR . 'plugin-fw/licence/lib/yit-licence.php' );
				require_once( YITH_WFBT_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php' );
			}

			YIT_Plugin_Licence()->register( YITH_WFBT_INIT, YITH_WFBT_SECRET_KEY, YITH_WFBT_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_plugin_for_updates() {
			if( ! class_exists( 'YIT_Plugin_Licence' ) ){
				require_once( YITH_WFBT_DIR . 'plugin-fw/lib/yit-upgrade.php' );
			}

			YIT_Upgrade()->register( YITH_WFBT_SLUG, YITH_WFBT_INIT );
		}

		/**
		 * Add custom image size to standard WC types
		 *
		 * @since 1.0.0
		 * @access public
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function custom_image_size( $value ){


			$option_values = get_option( 'yith-wfbt-image-size' );
			$width  = isset( $option_values['width'] ) ? $option_values['width'] : $value['default']['width'];
			$height = isset( $option_values['height'] ) ? $option_values['height'] : $value['default']['height'];
			$crop   = isset( $option_values['crop'] ) ? $option_values['crop'] : $value['default']['crop'];

			?><tr valign="top">
			<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
			<td class="forminp yith_image_size_settings">

				<input name="<?php echo esc_attr( $value['id'] ); ?>[width]" id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3" value="<?php echo $width; ?>" /> &times; <input name="<?php echo esc_attr( $value['id'] ); ?>[height]" id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3" value="<?php echo $height; ?>" />px

				<label><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]" id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox" value="1" <?php checked( 1, $crop ); ?> /> <?php _e( 'Hard Crop?', 'woocommerce' ); ?></label>

				<div><span class="description"><?php echo $value['desc'] ?></span></div>

			</td>
			</tr><?php

		}

		/**
		 * Enqueue scripts
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function enqueue_scripts(){

			global $post;

			if( isset( $post ) && get_post_type( $post->ID ) == 'product' ) {

				wp_enqueue_script( 'yith-wfbt-admin', YITH_WFBT_ASSETS_URL . '/js/yith-wfbt-admin.js', array( 'jquery' ), false, true );

				wp_localize_script( 'yith-wfbt-admin', 'yith_wfbt', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'postID'  => $post->ID
				));
			}

			wp_enqueue_style( 'yith-wfbt-admin-scripts', YITH_WFBT_ASSETS_URL . '/css/yith-wfbt-admin.css' );

		}

		/**
		 * Print data table
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function data_table() {
			if( file_exists( YITH_WFBT_DIR . '/templates/admin/data-tab.php' ) ) {
				include_once( YITH_WFBT_DIR . '/templates/admin/data-tab.php' );
			}
		}

		/**
		 * Add bought together tab in edit product page
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $tabs
		 * @return mixed
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function add_bought_together_tab( $tabs ){

			$tabs['yith-wfbt'] = array(
				'label'  => _x( 'Frequently Bought Together', 'tab in product data box', 'yith-woocommerce-frequently-bought-together' ),
				'target' => 'yith_wfbt_data_option',
				'class'  => array( 'hide_if_grouped', 'hide_if_external' ),
			);

			return $tabs;
		}

		/**
		 * Add bought together panel in edit product page
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function add_bought_together_panel(){

			global $post;

			?>

			<div id="yith_wfbt_data_option" class="panel woocommerce_options_panel">

				<div class="options_group show_if_variable">

					<p class="form-field">
						<label for="yith_wfbt_default_variation"><?php _e( 'Select default variation', 'yith-woocommerce-frequently-bought-together' ); ?></label>
						<select id="yith_wfbt_default_variation" name="yith_wfbt_default_variation">
							<?php
							$variations = $this->get_variations( $post->ID );
							$selected = get_post_meta( $post->ID, YITH_WFBT_META_VARIATION, true );
							foreach( $variations as $variation ) :
							?>
								<option value="<?php echo $variation['id'] ?>" <?php selected( $variation['id'], $selected ) ?>><?php echo $variation['name'] ?></option>
							<?php
							endforeach;
							?>
						</select>
					</p>

				</div>

				<div class="options_group">

					<p class="form-field"><label for="yith_wfbt_ids"><?php _e( 'Select products', 'yith-woocommerce-frequently-bought-together' ); ?></label>
						<input type="hidden" class="wc-product-search" style="width: 50%;" id="yith_wfbt_ids" name="yith_wfbt_ids" data-placeholder="<?php _e( 'Search for a product&hellip;', 'yith-woocommerce-frequently-bought-together' ); ?>" data-multiple="true" data-action="yith_ajax_search_product" data-selected="<?php
						$product_ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, YITH_WFBT_META, true ) ) );
						$json_ids    = array();

						foreach ( $product_ids as $product_id ) {
							$product = wc_get_product( $product_id );
							if ( is_object( $product ) ) {
								$json_ids[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
							}
						}

						echo esc_attr( json_encode( $json_ids ) );
						?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" />
						<img class="help_tip" data-tip='<?php _e( 'Select products for "Frequently bought together" group', 'yith-woocommerce-frequently-bought-together' ) ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
					</p>

					<p class="form-field"><label for="yith_wfbt_num"><?php _e( 'Select number of products', 'yith-woocommerce-frequently-bought-together' ); ?></label>
						<?php
							$num = get_post_meta( $post->ID, YITH_WFBT_META_NUM, true );
						?>
						<input type="number" class="wc-product-number" id="yith_wfbt_num" name="yith_wfbt_num" value="<?php echo $num ?>" min="1" />
						<img class="help_tip" data-tip='<?php _e( 'Select the number of products to show excluding current one.', 'yith-woocommerce-frequently-bought-together' ) ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
					</p>

				</div>

			</div>

			<?php
		}

		/**
		 * Get variations id for variable post
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string $post_id
		 * @param bool $only_id get only id
		 * @return mixed
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function get_variations( $post_id, $only_id = false ){

			// Get variations
			$args = array(
				'post_type'   => 'product_variation',
				'post_status' => array( 'private', 'publish' ),
				'numberposts' => -1,
				'orderby'     => 'menu_order',
				'order'       => 'asc',
				'post_parent' => $post_id
			);

			$posts = get_posts( $args );
			$return = array();

			foreach( $posts as $post ) {
				$product = wc_get_product( $post->ID );

				if( $only_id ) {
					$variation = $post->ID;
				}
				else {
					$variation['id']   = $post->ID;
					$variation['name'] = '#' . $post->ID;

					$attrs = $product->get_variation_attributes();
					foreach ( $attrs as $attr ) {
						$variation['name'] .= ' - ' . $attr;
					}
				}

				$return[] = $variation;
			}

			return $return;

		}
		/**
		 * Ajax action search product
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function yith_ajax_search_product(){
			ob_start();

			check_ajax_referer( 'search-products', 'security' );

			$term = (string) wc_clean( stripslashes( $_GET['term'] ) );
			$post_types = array( 'product', 'product_variation' );

			if ( empty( $term ) ) {
				die();
			}

			$args = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				's'              => $term,
				'fields'         => 'ids'
			);

			if ( is_numeric( $term ) ) {

				$args2 = array(
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'post__in'       => array( 0, $term ),
					'fields'         => 'ids'
				);

				$args3 = array(
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'post_parent'    => $term,
					'fields'         => 'ids'
				);

				$args4 = array(
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => '_sku',
							'value'   => $term,
							'compare' => 'LIKE'
						)
					),
					'fields'         => 'ids'
				);

				$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ), get_posts( $args3 ), get_posts( $args4 ) ) );

			} else {

				$args2 = array(
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => '_sku',
							'value'   => $term,
							'compare' => 'LIKE'
						)
					),
					'fields'         => 'ids'
				);

				$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ) ) );

			}

			$found_products = array();

			if ( $posts ) {
				foreach ( $posts as $post ) {
					$product = wc_get_product( $post );
					// exclude variable product
					if( $product->product_type == 'variable' ) {
						continue;
					}

					$found_products[ $post ] = rawurldecode( $product->get_formatted_name() );
				}
			}

			wp_send_json( apply_filters( 'yith_wfbt_ajax_search_product_result', $found_products ) );
		}

		/**
		 * Save options in upselling tab
		 *
		 * @since 1.0.0
		 * @param $post_id
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 */
		public function save_bought_together_tab( $post_id ){

			// save default variation is product is variable
			$selected_variation = isset( $_POST['yith_wfbt_default_variation'] ) ? $_POST['yith_wfbt_default_variation'] : '';
			$variations = $this->get_variations( $post_id, true );

			// save selected if is valid
			if( ! empty( $variations ) && in_array( $selected_variation, $variations, false ) ) {
				update_post_meta( $post_id, YITH_WFBT_META_VARIATION, $selected_variation );
			}
			// else save first
			elseif( ! empty( $variations ) ){
				$first = array_shift( $variations );
				update_post_meta( $post_id, YITH_WFBT_META_VARIATION, $first );
			}
			// else false
			else {
				update_post_meta( $post_id, YITH_WFBT_META_VARIATION, false );
			}


			// save products group
			$products = isset( $_POST['yith_wfbt_ids'] ) ? array_filter( array_map( 'intval', explode( ',', $_POST['yith_wfbt_ids'] ) ) ) : array();
			update_post_meta( $post_id, YITH_WFBT_META, $products );

			// save number of products to show
			$num = isset( $_POST['yith_wfbt_num'] ) ? $_POST['yith_wfbt_num'] : '';
			update_post_meta( $post_id, YITH_WFBT_META_NUM, $num );

		}

		/**
		 * Update variation list after a var
		 */
		public function yith_ajax_update_variation_list(){

			if( ! isset( $_POST['productID'] ) ) {
				die();
			}

			$id = intval( $_POST['productID'] );

			ob_start();

			$variations = $this->get_variations( $id );
			$selected   = get_post_meta( $id, YITH_WFBT_META_VARIATION, true );
			foreach ( $variations as $variation ) :
				?>
				<option value="<?php echo $variation['id'] ?>" <?php selected( $variation['id'], $selected ) ?>><?php echo $variation['name'] ?></option>
			<?php
			endforeach;

			echo ob_get_clean();
			die();
		}

	}
}
/**
 * Unique access to instance of YITH_WFBT_Admin class
 *
 * @return \YITH_WFBT_Admin
 * @since 1.0.0
 */
function YITH_WFBT_Admin(){
	return YITH_WFBT_Admin::get_instance();
}