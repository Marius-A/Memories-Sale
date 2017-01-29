<?php
/**
 * Admin class
 *
 * @author Yithemes
 * @package YITH WooCommerce Quick View
 * @version 1.1.1
 */

if ( ! defined( 'YITH_WCQV' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCQV_Admin' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WCQV_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCQV_Admin
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugin options
		 *
		 * @var array
		 * @access public
		 * @since  1.0.0
		 */
		public $options = array();

		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $version = YITH_WCQV_VERSION;

		/**
		 * @var $_panel Panel Object
		 */
		protected $_panel;

		/**
		 * @var string Quick View panel page
		 */
		protected $_panel_page = 'yith_wcqv_panel';

		/**
		 * Various links
		 *
		 * @var string
		 * @access public
		 * @since  1.0.0
		 */
		public $doc_url = 'http://yithemes.com/docs-plugins/yith-woocommerce-quick-view/';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCQV
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __construct() {

			// Add panel options
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

			// Add action links
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCQV_DIR . '/' . basename( YITH_WCQV_FILE ) ), array(
				$this,
				'action_links'
			) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

			// Register plugin to licence/update system
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			// enqueue scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );

			// YITH WCQV Loaded
			do_action( 'yith_wcqv_loaded' );

			// set image size
			$width  = get_option( 'yith-quick-view-product-image-width' );
			$height = get_option( 'yith-quick-view-product-image-height' );

			add_image_size( 'quick_view_image_size', $width, $height, false );
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
		 * @use      plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {

			$links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-wcqv' ) . '</a>';

			return $links;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			$admin_tabs = array(
				'general'  => __( 'General', 'yith-woocommerce-quick-view' ),
				'product'  => __( 'Product', 'yith-woocommerce-quick-view' ),
				'style'    => __( 'Style', 'yith-woocommerce-quick-view' )
			);

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => __( 'Quick View', 'yith-woocommerce-quick-view' ),
				'menu_title'       => __( 'Quick View', 'yith-woocommerce-quick-view' ),
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YITH_WCQV_DIR . '/plugin-options'
			);


			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once( 'plugin-fw/lib/yit-plugin-panel-wc.php' );
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

			add_action( 'woocommerce_admin_field_yith_wcqv_upload', array( $this->_panel, 'yit_upload' ), 10, 1 );
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
		 * @use      plugin_row_meta
		 */
		public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

			if ( defined( 'YITH_WCQV_INIT' ) && YITH_WCQV_INIT == $plugin_file ) {
				$plugin_meta[] = '<a href="' . $this->doc_url . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-quick-view' ) . '</a>';
			}

			return $plugin_meta;
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_activation() {
			if( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once 'plugin-fw/licence/lib/yit-licence.php';
				require_once 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YITH_WCQV_INIT, YITH_WCQV_SECRET_KEY, YITH_WCQV_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_updates() {
			if( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once( 'plugin-fw/lib/yit-upgrade.php' );
			}
			YIT_Upgrade()->register( YITH_WCQV_SLUG, YITH_WCQV_INIT );
		}


		/**
		 * Enqueue plugin scripts and styles
		 *
		 * @access public
		 * @author Francesco Licandro
		 */
		public function enqueue_script() {
			wp_enqueue_script( 'yith-wcqv-admin', YITH_WCQV_ASSETS_URL . '/js/admin.js', array('jquery','select2'), $this->version, true );
		}

	}
}

/**
 * Unique access to instance of YITH_WCQV_Admin class
 *
 * @return \YITH_WCQV_Admin
 * @since 1.0.0
 */
function YITH_WCQV_Admin(){
	return YITH_WCQV_Admin::get_instance();
}