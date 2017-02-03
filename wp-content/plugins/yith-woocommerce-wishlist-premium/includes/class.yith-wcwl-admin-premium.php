<?php
/**
 * Init premium admin features of the plugin
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 1.1.5
 */

if ( !defined( 'YITH_WCWL' ) ) {
	exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCWL_Admin_Premium' ) ) {
	/**
	 * WooCommerce Wishlist admin Premium
	 *
	 * @since 1.0.0
	 */
	class YITH_WCWL_Admin_Premium {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCWL_Admin_Init
		 * @since 2.0.0
		 */
		protected static $instance;

		/**
		 * Various links
		 *
		 * @var string
		 * @access public
		 * @since 1.0.0
		 */
		public $showcase_images = array();

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCWL_Admin_Premium
		 * @since 2.0.0
		 */
		public static function get_instance(){
			if( is_null( self::$instance ) ){
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor of the class
		 *
		 * @return YITH_WCWL_Admin_Premium
		 * @since 2.0.0
		 */
		public function __construct() {
			$this->showcase_images = array(
				'yith_wcwl_multi_wishlist_enable' => YITH_WCWL_URL . 'assets/images/landing/multiple-wishlist.png',
				'yith_wcwl_show_estimate_button' => YITH_WCWL_URL . 'assets/images/landing/ask-an-estimate.png',
				'yith_wcwl_show_additional_info_textarea' => YITH_WCWL_URL . 'assets/images/landing/ask-an-estimate-additional-info.png'
			);

			// register admin notices
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			// modify plugin settings array
			add_filter( 'yith_wcwl_admin_options', array( $this, 'enable_premium_options' ) );
			add_filter( 'yith_wcwl_admin_options', array( $this, 'remove_video_box' ) );

			// modify available admin views of the wishlist panel
			add_filter( 'yith_wcwl_available_admin_tabs', array( $this, 'enable_premium_views' ) );
			add_action( 'yith_wcwl_promotion_email_settings', array( $this, 'print_promotion_email_panel' ) );
			add_action( 'yith_wcwl_ask_an_estimate_email_settings', array( $this, 'print_ask_an_estimate_email_panel' ) );

			// modify params to use in templates files
			add_filter( 'yith_wcwl_wishlist_table', array( $this, 'print_wishlist_table' ) );
			add_filter( 'yith_wcwl_popular_table', array( $this, 'print_popular_table' ) );

			// register admin actions
			add_action( 'admin_action_delete_wishlist', array( $this, 'delete_wishlist_from_actions' ) );

			// adds column to product page
			add_filter( 'manage_edit-product_columns', array( $this, 'add_product_columns' ) );
			add_filter( 'manage_edit-product_sortable_columns', array( $this, 'product_sortable_columns' ) );
			add_action( 'manage_product_posts_custom_column', array( $this, 'render_product_columns' ) );
			add_filter( 'request', array( $this, 'product_request_query' ) );

			// send promotion email
			add_action( 'admin_init', array( $this, 'preview_promotion_email' ) );
			add_action( 'admin_init', array( $this, 'trigger_promotion_email' ) );

			// register plugin to licence/update system
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			// filter pointer content for premium version
			add_filter( 'yith_wcwl_activated_pointer_content', array( $this, 'filter_activated_pointer_content' ) );
			add_filter( 'yith_wcwl_updated_pointer_content', array( $this, 'filter_updated_pointer_content' ) );

			// compatibility with email templates
			add_filter( 'yith_wcet_email_template_types', array( $this, 'register_emails_for_custom_templates' ) );
		}

		/**
		 * Print admin notices for wishlist settings page
		 *
		 * @return void
		 * @since 2.0.7
		 */
		public function admin_notices() {
			if( isset( $_GET['email_sent'] ) ){
				$res = $_GET['email_sent'];

				if( $res ){
					?>
					<div class="updated fade">
						<p><?php _e( 'Promotional email sent correctly', 'yith-woocommerce-wishlist' ) ?></p>
					</div>
				<?php
				}
				else{
					?>
					<div class="updated fade">
						<p><?php _e( 'There was an error while sending emails; please, try again later', 'yith-woocommerce-wishlist' ) ?></p>
					</div>
				<?php
				}
			}
		}

		/**
		 * Enables premium features
		 *
		 * @param $settings Array of plugin settings
		 * @return array New filtered array of settings
		 * @since 2.0.0
		 */
		public function enable_premium_options( $settings ) {
			$show_cb_column = array(
				'name'    => __( 'Show "Add to Cart" checkbox', 'yith-woocommerce-wishlist' ),
				'desc'    => __( 'Show the checkbox to add multiple items to the cart with one click', 'yith-woocommerce-wishlist' ),
				'id'      => 'yith_wcwl_cb_show',
				'std'     => 'no', // for woocommerce < 2.0
				'default' => 'no', // for woocommerce >= 2.0
				'type'    => 'checkbox',
				'css'     => 'min-width:300px;',
			);

			$show_move_to_another_wishlist = array(
				'name'    => __( 'Show "Move to another wishlist" dropdown menu', 'yith-woocommerce-wishlist' ),
				'desc'    => __( 'Show the dropdown menu to move one item to another user defined wishlist', 'yith-woocommerce-wishlist' ),
				'id'      => 'yith_wcwl_show_move_to_another_wishlist',
				'std'     => 'no', // for woocommerce < 2.0
				'default' => 'no', // for woocommerce >= 2.0
				'type'    => 'checkbox',
				'css'     => 'min-width:300px;',
			);

			$section_end = array_pop( $settings['general_settings'] );

			$settings['general_settings']['show_cb'] = $show_cb_column;
			$settings['general_settings']['show_move_to_another_wishlist'] = $show_move_to_another_wishlist;
			$settings['general_settings']['general_section_end'] = $section_end;

			$settings['premium'] = array(

				'premium_section_start' => array(
					'name' => __( 'Premium Settings', 'yith-woocommerce-wishlist' ),
					'type' => 'title',
					'desc' => '',
					'id' => 'yith_wcwl_premium_settings'
				),

				'disable_wishlist_for_unauthenticated_users' => array(
					'name'    => __( 'Disable the wishlist for unauthenticated users', 'yith-woocommerce-wishlist' ),
					'desc'    => __( 'Disable the wishlist features for unauthenticated users, redirecting them to the login page', 'yith-woocommerce-wishlist' ),
					'id'      => 'yith_wcwl_disable_wishlist_for_unauthenticated_users',
					'std'     => 'no', // for woocommerce < 2.0
					'default' => 'no', // for woocommerce >= 2.0
					'type'    => 'checkbox'
				),

				'show_login_notice' => array(
					'name'    => __( 'Show login notice before wishlist table', 'yith-woocommerce-wishlist' ),
					'id'      => 'yith_wcwl_show_login_notice',
					'desc'    => __( 'This option lets you add a notice before the wishlist table to ask unauthorized users to login (use %login_anchor% placeholder to show login link; leave empty to hide)', 'yith-woocommerce-wishlist' ),
					'std'     => __( 'Please %login_anchor% to use all wishlist features', 'yith-woocommerce-wishlist' ), // for woocommerce < 2.0
					'default' => __( 'Please %login_anchor% to use all wishlist features', 'yith-woocommerce-wishlist' ), // for woocommerce >= 2.0
					'type'    => 'text',
					'css'     => 'min-width:300px;'
				),

				'login_anchor_text' => array(
					'name'    => __( 'Login anchor text', 'yith-woocommerce-wishlist' ),
					'id'      => 'yith_wcwl_login_anchor_text',
					'desc'    => __( 'This options lets you customize the login anchor text shown in wishlist login notice', 'yith-woocommerce-wishlist' ),
					'std'     => __( 'login', 'yith-woocommerce-wishlist' ), // for woocommerce < 2.0
					'default' => __( 'login', 'yith-woocommerce-wishlist' ), // for woocommerce >= 2.0
					'type'    => 'text',
					'css'     => 'min-width:300px;'
				),

				'search_wishlist_page_title' => array(
					'name'    => __( 'Wishlist search page title', 'yith-woocommerce-wishlist' ),
					'id'      => 'yith_wcwl_wishlist_search_title',
					'desc'    => __( 'This option lets you customize the title of the wishlist search page', 'yith-woocommerce-wishlist' ),
					'std'     => sprintf( __( 'Search a wishlist on %s', 'yith-woocommerce-wishlist' ),  get_bloginfo( 'name' ) ), // for woocommerce < 2.0
					'default' => sprintf( __( 'Search a wishlist on %s', 'yith-woocommerce-wishlist' ),  get_bloginfo( 'name' ) ), // for woocommerce >= 2.0
					'type'    => 'text',
					'css'     => 'min-width:300px;'
				),
				'enable_multi_wishlist' => array(
					'name'    => __( 'Enable multi-wishlist support', 'yith-woocommerce-wishlist' ),
					'desc'              => sprintf( __( 'Enable multi-wishlist support for logged in users (<a href="%s" class="thickbox">Example</a>)', 'yith-woocommerce-wishlist' ), $this->showcase_images['yith_wcwl_multi_wishlist_enable'] ),
					'id'      => 'yith_wcwl_multi_wishlist_enable',
					'std'     => 'no', // for woocommerce < 2.0
					'default' => 'no', // for woocommerce >= 2.0
					'type'    => 'checkbox'
				),
				'show_exists_in_a_wishlist' => array(
					'name'    => __( 'Show "Product already in wishlist" on multi-wishlist', 'yith-woocommerce-wishlist' ),
					'desc'    => __( 'Show "Product already in wishlist" message also when a multi-wishlist is activated', 'yith-woocommerce-wishlist' ),
					'id'      => 'yith_wcwl_show_exists_in_a_wishlist',
					'std'     => 'no', // for woocommerce < 2.0
					'default' => 'no', // for woocommerce >= 2.0
					'type'    => 'checkbox'
				),
				'add_to_wishlist_popup_text' => array(
					'name'    => __( '"Add to Wishlist" popup button text', 'yith-woocommerce-wishlist' ),
					'id'      => 'yith_wcwl_add_to_wishlist_popup_text',
					'desc'    => __( 'Text of the "Add to Wishlist" button in the popup', 'yith-woocommerce-wishlist' ),
					'std'     => __( 'Add to wishlist', 'yith-woocommerce-wishlist' ), // for woocommerce < 2.0
					'default' => __( 'Add to wishlist', 'yith-woocommerce-wishlist' ), // for woocommerce >= 2.0
					'type'    => 'text',
					'css'     => 'min-width:300px;'
				),
				'enable_ask_an_estimate'                 => array(
					'name'    => __( 'Enable "Ask for an estimate" button', 'yith-woocommerce-wishlist' ),
					'desc'    => sprintf( __( 'Shows "Ask for an estimate" button on wishlist page (<a href="%s" class="thickbox">Example</a>)', 'yith-woocommerce-wishlist' ), $this->showcase_images['yith_wcwl_show_estimate_button'] ),
					'id'      => 'yith_wcwl_show_estimate_button',
					'std'     => 'no', // for woocommerce < 2.0
					'default' => 'no', // for woocommerce < 2.0
					'type'    => 'checkbox'
				),
				'enable_ask_an_estimate_additional_info' => array(
					'name'    => __( 'Show additional notes when asking for an estimante', 'yith-woocommerce-wishlist' ),
					'desc'    => sprintf( __( 'Shows "Additional notes" text area to let users add more information when asking for an estimate (<a href="%s" class="thickbox">Example</a>)', 'yith-woocommerce-wishlist' ), $this->showcase_images['yith_wcwl_show_additional_info_textarea'] ),
					'id'      => 'yith_wcwl_show_additional_info_textarea',
					'std'     => 'no', // for woocommerce < 2.0
					'default' => 'no', // for woocommerce < 2.0
					'type'    => 'checkbox'
				),
				'additional_info_textarea_label'         => array(
					'name'    => __( 'Label for "Additional notes" text area', 'yith-woocommerce-wishlist' ),
					'id'      => 'yith_wcwl_additional_info_textarea_label',
					'desc'    => __( 'This option lets you customize the label for "Additional notes" text area', 'yith-woocommerce-wishlist' ),
					'std'     => __( 'Additional notes', 'yith-woocommerce-wishlist' ), // for woocommerce < 2.0
					'default' => __( 'Additional notes', 'yith-woocommerce-wishlist' ), // for woocommerce >= 2.0
					'type'    => 'text',
					'css'     => 'min-width:300px;'
				),
				'enable_wishlist_links' => array(
					'name'    => __( 'Show links', 'yith-woocommerce-wishlist' ),
					'desc'    => __( 'Show the links for the "manage", "create", and "search" pages after the wishlist table', 'yith-woocommerce-wishlist' ),
					'id'      => 'yith_wcwl_enable_wishlist_links',
					'std'     => 'no', // for woocommerce < 2.0
					'default' => 'no', // for woocommerce < 2.0
					'type'    => 'checkbox'
				),
				'create_wishlist_page_title'             => array(
					'name'    => __( 'Title of the "Create wishlist" page', 'yith-woocommerce-wishlist' ),
					'id'      => 'yith_wcwl_wishlist_create_title',
					'desc'    => __( 'This option lets you customize the title of the page where you can create a new wishlist (only when additional info textarea is enabled)', 'yith-woocommerce-wishlist' ),
					'std'     => __( 'Create a new wishlist', 'yith-woocommerce-wishlist' ), // for woocommerce < 2.0
					'default' => __( 'Create a new wishlist', 'yith-woocommerce-wishlist' ), // for woocommerce >= 2.0
					'type'    => 'text',
					'css'     => 'min-width:300px;'
				),
				'manage_wishlist_page_title' => array(
					'name'    => __( 'Title of the "Manage wishlist" page', 'yith-woocommerce-wishlist' ),
					'id'      => 'yith_wcwl_wishlist_manage_title',
					'desc'    => __( 'This option lets you customize the title of the page where you can manege the wishlist', 'yith-woocommerce-wishlist' ),
					'std'     => sprintf( __( 'Manage your wishlists on %s', 'yith-woocommerce-wishlist' ),  get_bloginfo( 'name' ) ), // for woocommerce < 2.0
					'default' => sprintf( __( 'Manage your wishlists on %s', 'yith-woocommerce-wishlist' ),  get_bloginfo( 'name' ) ),// for woocommerce >= 2.0
					'type'    => 'text',
					'css'     => 'min-width:300px;'
				),

				'premium_section_end' => array(
					'type' => 'sectionend',
					'id' => 'yith_wcwl_premium_settings'
				)
			);

			return $settings;
		}

		/**
		 * Removes promotional videobox from premium options
		 *
		 * @param $settings Array of plugin settings
		 * @return array New filtered array of settings
		 * @since 2.0.0
		 */
		public function remove_video_box( $settings ) {
			if( isset( $settings['general_settings']['section_general_settings_videobox'] ) ){
				unset( $settings['general_settings']['section_general_settings_videobox'] );
			}

			return $settings;
		}

		/**
		 * Filters available views for admin screen
		 *
		 * @param $views Array of available admin tabs
		 * @return array
		 * @since 2.0.0
		 */
		public function enable_premium_views( $views ) {
			// remove premium tab
			if( isset( $views['premium'] ) ){
				unset( $views['premium'] );
			}

			// add list tab
			$views = array_merge(
				array(
					'lists' => __( 'Wishlists', 'yith-woocommerce-wishlist' ),
					'popular' => __( 'Popular', 'yith-woocommerce-wishlist' ),
					'ask_an_estimate_email' => __( '"Ask for an estimate" email', 'yith-woocommerce-wishlist' ),
					'promotion_email' => __( 'Promotional email', 'yith-woocommerce-wishlist' )
				),
				$views
			);

			return $views;
		}

		/**
		 * Adds params to use in admin template files
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function print_wishlist_table() {
			if( ! class_exists( 'WP_List_Table' ) ){
				require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			}

			require_once( YITH_WCWL_INC . 'class.yith-wcwl-admin-table.php' );
			$wishlist_table = new YITH_WCWL_Admin_Table();
			$wishlist_table->prepare_items();

			$additional_info = array();
			$additional_info['wishlist_table'] = $wishlist_table;
			$additional_info['current_tab'] = 'lists';

			yith_wcwl_get_template( 'admin/wishlist-panel-lists.php', $additional_info );
		}

		/**
		 * Adds params to use in admin template files
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function print_popular_table() {
			if( ! class_exists( 'WP_List_Table' ) ){
				require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			}

			$additional_info = array();

			if( ! isset( $_GET['action'] ) || ( isset( $_GET['action'] ) && $_GET['action'] == 'show_popular' ) ) {
				require_once( YITH_WCWL_INC . 'class.yith-wcwl-popular-table.php' );
				$popular_table = new YITH_WCWL_Popular_Table();
				$popular_table->prepare_items();

				$additional_info['title'] = __( 'Popular products', 'yith-woocommerce-wishlist' );
				$additional_info['table']       = $popular_table;
				$additional_info['current_tab'] = 'popular';
				$additional_info['search_text'] = __( 'Search by product', 'yith-woocommerce-wishlist' );

				yith_wcwl_get_template( 'admin/wishlist-panel-popular.php', $additional_info );
			}
			elseif( isset( $_GET['action'] ) && $_GET['action'] == 'show_users' ){
				require_once( YITH_WCWL_INC . 'class.yith-wcwl-users-popular-table.php' );
				$users_table = new YITH_WCWL_Users_Popular_Table();
				$users_table->prepare_items();
				$additional_info['search_text'] = __( 'Search by user', 'yith-woocommerce-wishlist' );

				$product = wc_get_product( $users_table->product_id );

				$additional_info['title'] = sprintf( __( 'Users that have added "%s" <a href="%s" class="add-new-h2">%s</a>', 'yith-woocommerce-wishlist' ), $product->get_title(), esc_url( add_query_arg( array( 'page' => 'yith_wcwl_panel', 'tab' => 'popular' ), admin_url( 'admin.php' ) ) ), __( 'Back to popular', 'yith-woocommerce-wishlist' ) );
				$additional_info['product_id'] = $users_table->product_id;
				$additional_info['table'] = $users_table;
				$additional_info['current_tab'] = 'popular';
				$additional_info['search_text'] = __( 'Search by user', 'yith-woocommerce-wishlist' );

				yith_wcwl_get_template( 'admin/wishlist-panel-popular.php', $additional_info );
			}
			elseif( isset( $_GET['action'] ) && $_GET['action'] == 'send_promotional_email' ){
				$emails = WC_Emails::instance()->get_emails();
				$promotion_email = $emails['YITH_WCWL_Promotion_Email'];

				$additional_info['current_tab'] = 'popular';
				$additional_info['product_id'] = isset( $_REQUEST['product_id'] ) ? $_REQUEST['product_id'] : false;
				$additional_info['promotional_email_html_content'] = $promotion_email->get_option( 'content_html' );
				$additional_info['promotional_email_text_content'] = $promotion_email->get_option( 'content_text' );
				$additional_info['coupons'] = get_posts( array(
					'post_type' => 'shop_coupon',
					'posts_per_page' => -1,
					'post_status' => 'publish'
				) );

				yith_wcwl_get_template( 'admin/wishlist-panel-send-promotional-email.php', $additional_info );
			}
		}

		/**
		 * Print "Promotion Email" admin panel template
		 *
		 * @return void
		 * @since 2.0.7
		 */
		public function print_promotion_email_panel() {
			if( file_exists( YITH_WCWL_DIR . '/templates/admin/wishlist-panel-email.php' ) ) {
				global $current_section;
				$current_section = 'yith_wcwl_promotion_email';

				$mailer = WC()->mailer();
				$class = $mailer->emails['YITH_WCWL_Promotion_Email'];

				WC_Admin_Settings::get_settings_pages();

				if( ! empty( $_POST ) ) {
					$class->process_admin_options();
				}

				include_once( YITH_WCWL_DIR . '/templates/admin/wishlist-panel-email.php' );
			}
		}

		/**
		 * Print "Estimate Email" admin panel template
		 *
		 * @return void
		 * @since 2.0.7
		 */
		public function print_ask_an_estimate_email_panel() {
			if( file_exists( YITH_WCWL_DIR . '/templates/admin/wishlist-panel-email.php' ) ) {
				global $current_section;
				$current_section = 'yith_wcwl_estimate_email';

				$mailer = WC()->mailer();
				$class = $mailer->emails['YITH_WCWL_Estimate_Email'];

				WC_Admin_Settings::get_settings_pages();

				if( ! empty( $_POST ) ) {
					$class->process_admin_options();
				}

				include_once( YITH_WCWL_DIR . '/templates/admin/wishlist-panel-email.php' );
			}
		}

		/**
		 * Filter content of the pointer for wishlist
		 *
		 * @param $content string Filter pointer
		 * @return string New content
		 * @aince 2.0.0
		 */
		public function filter_activated_pointer_content( $content ){
			return __( 'In the YIT Plugin tab you can find the Wishlist options. With this menu, you can access to all the settings of our plugins that you have activated.', 'yith-woocommerce-wishlist' );
		}

		/**
		 * Filter content of the pointer for wishlist
		 *
		 * @param $content string Filter pointer
		 * @return string New content
		 * @aince 2.0.0
		 */
		public function filter_updated_pointer_content( $content ){
			return __( 'From now on, you can find all the options of Wishlist under YIT Plugin -> Wishlist instead of WooCommerce -> Settings -> Wishlist, as in the previous version. When one of our plugins is updated, a new voice will be added to this menu.', 'yith-woocommerce-wishlist' );
		}

		/* === REQUEST HANDLING === */

		/**
		 * Handle admin requests to delete a wishlist
		 *
		 * @return void
		 * @since 2.0.6
		 */
		public function delete_wishlist_from_actions() {
			if( ! empty( $_REQUEST['wishlist_id'] ) ) {
				if ( isset( $_REQUEST['delete_wishlist'] ) && wp_verify_nonce( $_REQUEST['delete_wishlist'], 'delete_wishlist' ) ) {
					$wishlist_id = $_REQUEST['wishlist_id'];
					YITH_WCWL_Premium()->remove_wishlist( $wishlist_id );
				}
			}

			wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'yith_wcwl_panel', 'tab' => 'list' ), admin_url( 'admin.php' ) ) ) );
		}

		/* === WISHLIST COUNT PRODUCT COLUMN === */

		/**
		 * Add column to product table, to show product occurrences in wishlists
		 *
		 * @param $columns array
		 * @return array
		 * @since 2.0.0
		 */
		public function add_product_columns( $columns ) {
			$columns['wishlist_count'] = __( 'Wishlist Count', 'yith-woocommerce-wishlist' );
			return $columns;
		}

		/**
		 * Render column of occurrences in product table
		 *
		 * @param $column string
		 * @return void
		 * @since 2.0.0
		 */
		public function render_product_columns( $column ){
			global $post;

			if( $column == 'wishlist_count' ){
				echo YITH_WCWL()->count_product_occurrencies( $post->ID );
			}
		}

		/**
		 * Register column of occurrences in wishlist as sortable
		 *
		 * @param $columns array
		 * @return array
		 * @since 2.0.0
		 */
		public function product_sortable_columns( $columns ){
			$columns[ 'wishlist_count' ] = 'wishlist_count';
			return $columns;
		}

		/**
		 * Alter post query when ordering for wishlist occurrences
		 *
		 * @param $vars array
		 * @return array
		 * @since 2.0.0
		 */
		public function product_request_query( $vars ) {
			global $typenow, $wp_query;

			if ( 'product' === $typenow ) {
				// Sorting
				if ( isset( $vars['orderby'] ) ) {
					if ( 'wishlist_count' == $vars['orderby'] ) {
						add_filter( 'posts_join', array( $this, 'filter_join_for_wishlist_count' ) );
						add_filter( 'posts_orderby', array( $this, 'filter_orderby_for_wishlist_count' ) );
					}
				}
			}

			return $vars;
		}

		/**
		 * Alter join section of the query, for ordering purpose
		 *
		 * @param $join string
		 * @return string
		 * @since 2.0.0
		 */
		public function filter_join_for_wishlist_count( $join ) {
			global $wpdb;
			$join .= " LEFT JOIN ( SELECT COUNT(*) AS wishlist_count, prod_id FROM {$wpdb->yith_wcwl_items} GROUP BY prod_id ) AS i ON ID = i.prod_id";
			return $join;
		}

		/**
		 * Alter orderby section of the query, for ordering purpose
		 *
		 * @param $orderby string
		 * @return string
		 * @since 2.0.0
		 */
		public function filter_orderby_for_wishlist_count( $orderby ) {
			global $wpdb;
			$orderby = "i.wishlist_count " . ( isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'ASC' );
			return $orderby;
		}

		/* === SEND PROMOTION EMAIL === */

		/**
		 * Preview promotional email template
		 *
		 * @return string
		 * @since 2.0.7
		 */
		public function preview_promotion_email() {

			if ( isset( $_GET['preview_yith_wcwl_promotion_email'] ) ) {
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'preview-promotion-mail') ) {
					die( 'Security check' );
				}

				$template = isset( $_GET['template'] ) ? $_GET['template'] : false;
				$product_id = isset( $_GET['product_id'] ) ? $_GET['product_id'] : false;
				$template_path = '';

				if( $template == 'plain' ){
					$template_path = 'plain/';
				}

				// load the mailer class
				$mailer = WC()->mailer();
				$email = $mailer->emails['YITH_WCWL_Promotion_Email'];
				$email->user = get_user_by( 'id', get_current_user_id() );
				$email->object = wc_get_product( $product_id );

				// get the preview email subject
				$email_heading = $email->heading;
				$email_content = $email->{'get_custom_content_' . $template}();

				// get the preview email content
				ob_start();
				include( YITH_WCWL_DIR . 'templates/emails/' . $template_path . 'promotion.php' );
				$message = ob_get_clean();

				if( $template == 'plain' ){
					$message = nl2br( $message );
				}

				$message = $email->style_inline( $message );

				// print the preview email
				echo $message;
				exit;
			}
		}

		/**
		 * Trigger event to send the promotion email
		 *
		 * @return void
		 * @since 2.0.7
		 */
		public function trigger_promotion_email() {
			global $wpdb;

			if( ! isset( $_REQUEST['send_promotion_email'] ) || ! wp_verify_nonce( $_REQUEST['send_promotion_email'], 'send_promotion_email_action' ) ){
				return;
			}

			if( ! isset( $_GET['product_id'] ) && ! isset( $_GET['user_id'] ) ){
				return;
			}

			$product_id = isset( $_GET['product_id'] ) ? $_GET['product_id'] : false;
			$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : false;
			$html_content = isset( $_REQUEST['yith_wcwl_promotional_email_html_content'] ) ? $_REQUEST['yith_wcwl_promotional_email_html_content'] : false;
			$text_content = isset( $_REQUEST['yith_wcwl_promotional_email_text_content'] ) ? $_REQUEST['yith_wcwl_promotional_email_text_content'] : false;
			$use_coupon = isset( $_REQUEST['yith_wcwl_promotional_email_use_coupon'] ) ? $_REQUEST['yith_wcwl_promotional_email_use_coupon'] : false;
			$coupon_code = isset( $_REQUEST['yith_wcwl_promotional_email_coupon'] ) ? $_REQUEST['yith_wcwl_promotional_email_coupon'] : false;
			$receivers_ids = array();

			if( ! empty( $user_id ) ){
				$receivers_ids[] = $user_id;
			}
			elseif( ! empty( $product_id ) ){
				$query = "SELECT
		             u.ID AS id
		             FROM {$wpdb->users} AS u
		             LEFT JOIN {$wpdb->yith_wcwl_items} AS i ON u.ID = i.user_id
		             WHERE i.prod_id = %d
		             GROUP BY u.ID";

				$query_args = array( $product_id );

				// retrieve data for table
				$product_users = $wpdb->get_col( $wpdb->prepare( $query, $query_args ) );

				$receivers_ids = array_merge( $receivers_ids, $product_users );
			}

			$additional_info = apply_filters( 'yith_wcwl_promotional_email_additional_info', array(
				'html_content' => $html_content,
				'text_content' => $text_content,
				'use_coupon' => $use_coupon,
				'coupon_code' => $coupon_code,
				'product_id' => $product_id
			) );

			do_action( 'send_promotion_mail', $receivers_ids, $additional_info );
			$res =  apply_filters( 'yith_wcwl_promotional_email_send_result', true );

			wp_redirect(
				esc_url_raw(
					add_query_arg(
						array(
							'page' => 'yith_wcwl_panel',
							'tab' => 'popular',
							'email_sent' => ! empty( $res ) ? 'true' : 'false',
							'action' => $user_id ? 'show_users' : false,
							'product_id' => $user_id ? $product_id : false
						),
						admin_url( 'admin.php' )
					)
				)
			);
			exit;
		}

		/* === YITH WOOCOMMERCE EMAIL TEMPLATES INTEGRATION === */

		/**
		 * Filters email template available on yith-wcet
		 *
		 * @param $templates mixed Currently available templates
		 * @return mixed Fitlered templates
		 * @since 2.0.13
		 */
		public function register_emails_for_custom_templates( $templates ) {
			$templates[] = array(
				'id'        => 'yith-wcwl-ask-an-estimate-mail',
				'name'      => __( 'Wishlist "Ask an estimate"', 'yith-woocommerce-wishlist' ),
			);
			$templates[] = array(
				'id'        => 'yith-wcwl-promotion-mail',
				'name'      => __( 'Wishlist Promotion', 'yith-woocommerce-wishlist' ),
			);

			return $templates;
		}

		/* === WISHLIST LICENCE HANDLING === */

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_plugin_for_activation() {
			if( ! class_exists( 'YIT_Plugin_Licence' ) ){
				require_once( YITH_WCWL_DIR . 'plugin-fw/licence/lib/yit-licence.php' );
				require_once( YITH_WCWL_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php' );
			}

			YIT_Plugin_Licence()->register( YITH_WCWL_INIT, YITH_WCWL_SECRET_KEY, YITH_WCWL_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_plugin_for_updates() {
			if( ! class_exists( 'YIT_Upgrade' ) ){
				require_once( YITH_WCWL_DIR . 'plugin-fw/lib/yit-upgrade.php' );
			}

			YIT_Upgrade()->register( YITH_WCWL_SLUG, YITH_WCWL_INIT );
		}
	}
}

/**
 * Unique access to instance of YITH_WCWL_Admin_Premium class
 *
 * @return \YITH_WCWL_Admin_Premium
 * @since 2.0.0
 */
function YITH_WCWL_Admin_Premium(){
	return YITH_WCWL_Admin_Premium::get_instance();
}