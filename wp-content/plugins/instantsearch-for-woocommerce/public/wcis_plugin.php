<?php
/**
 * InstantSearch+ for WooCommerce.
 *
 * @package   WCISPlugin
 * @author    InstantSearchPlus
 * @license   GPL-2.0+
 * @link      http://www.instantsearchplus.com
 * @copyright 2014 InstantSearchPlus
 */

if ( ! defined( 'ABSPATH' ) ) 
	exit; // Exit if accessed directly

/**
 * @package WCISPlugin
 * @author  InstantSearchPlus <support@instantsearchplus.com>
 */
class WCISPlugin {      
    const SERVER_URL = 'https://acp-magento.appspot.com/';
    const DASHBOARD_URL = 'https://woo.instantsearchplus.com/';
    
//   	const SERVER_URL = 'http://0-2vk.acp-magento.appspot.com/';

	const VERSION = '1.7.3';
	
	// cron const variables
	const CRON_THRESHOLD_TIME 				 = 1200; 	// -> 20 minutes
	const CRON_EXECUTION_TIME 				 = 900; 	// -> 15 minutes
	const CRON_EXECUTION_TIME_RETRY			 = 600; 	// -> 10 minutes
	const SINGLES_TO_BATCH_THRESHOLD		 = 10;		// if more then 10 products send as batch
	const CRON_SEND_CATEGORIES_TIME_INTERVAL = 30;      // -> 30 secunds
	
	const ELEMENT_TYPE_PRODUCT				 = 0;
	const ELEMENT_TYPE_CATEGORY				 = 1;

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'WCISPlugin';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;  
	
	/*
	 * Full text search parameters
	 */
	private $wcis_fulltext_ids = null;
	private $wcis_total_results = 0;
	private $wcis_did_you_mean_fields = null;
	private $wcis_search_query = null;
	private $products_per_page = null;
	
	private $fulltext_disabled = null;
	private $just_created_site = false;
	
	private $facets = null;
	private $facets_completed = null;
	private $facets_required = null;
	private $facets_narrow = null;
	private $stem_words = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'on_init_hook' ) );

		/**
		 * smart navigation hooks
		 */
        add_filter('request', array( $this, 'on_request_hook' ));
		/**
		 * fires when rewrite rule are created/refreshed
		 */
        add_filter('page_rewrite_rules', array( $this, 'on_page_rewrite_rules' ));
		/**
		 * fires when permalink structure changed to different pattern
		 */
        add_action( 'permalink_structure_changed', array( $this, 'on_permalink_structure_changed'), 100, 2);

		// Activate plugin when new blog is added
        add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
                
//         add_action( 'publish_post', array( $this, 'on_product_update' ));

        // product change handlers
        add_action('woocommerce_product_quick_edit_save', array($this, 'on_product_quick_save'));
        add_action('save_post', array( $this, 'on_product_save'));
        add_action('trashed_post', array( $this, 'on_product_delete'));
        add_action('before_delete_post',array( $this, 'on_product_delete'));
        /*
        add_action('delete_post',array( $this, 'on_delete_post'));
        add_action('deleted_post', array( $this, 'on_deleted_post' ) );
        */
        add_action('woocommerce_order_status_on-hold', array( $this, 'quantity_change_handler'), 501);
        add_action('woocommerce_order_status_processing', array( $this, 'quantity_change_handler'), 501);
        add_action('woocommerce_order_status_pending', array( $this, 'quantity_change_handler'), 501);
        
        // NEW - cart webhook
        add_action('woocommerce_add_to_cart', array( $this, 'on_add_to_cart'));        
        add_action('woocommerce_checkout_order_processed', array( $this, 'on_checkout_order_processed' ));
        add_action('woocommerce_cart_item_removed', array($this, 'on_remove_from_cart'));
        
		add_action('edit_product_cat', array( $this, 'on_category_edit')); 
		add_action('create_product_cat', array( $this, 'on_category_create')); 
		add_action('delete_product_cat', array( $this, 'on_category_delete'));
            // profile changes (url/email update handler)
//             add_action( 'profile_update', array( $this, 'on_profile_update') );
//             add_action('admin_init', array( $this, 'on_profile_update'));

        // Load public-facing style sheet and JavaScript.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        
        add_filter('script_loader_tag', array( $this, 'add_async_defer_attributes'), 10, 2);
            
        add_action('parse_request', array($this, 'process_instantsearchplus_request'));
        add_filter('query_vars', array($this, 'filter_instantsearchplus_request'));   

        // cron
        add_action('instantsearchplus_cron_request_event', array( $this, 'execute_update_request' ) );   
        add_action('instantsearchplus_cron_request_event_backup', array( $this, 'execute_update_request' ) );
        add_action('instantsearchplus_cron_check_alerst', array( $this, 'check_for_alerts' ) );
        add_action('instantsearchplus_send_logging_record', array($this, 'send_logging_record'), 10, 1);
        
        add_action('instantsearchplus_send_all_categories', array($this, 'send_categories_as_batch'));
        add_action('instantsearchplus_send_batches_if_unreachable', 
        		array($this, 'send_batches_if_unreachable'), 10, 1);
        // FullText search
        add_filter( 'posts_search', array( $this, 'posts_search_handler' ) );
        add_action( 'pre_get_posts', array( $this, 'pre_get_posts_handler' ) );
        add_filter( 'post_limits', array( $this, 'post_limits_handler' ) );
        add_filter( 'the_posts', array( $this, 'the_posts_handler' ) );
        // Highlight search terms
        add_filter( 'the_title', array($this, 'highlight_result_handler'), 50);
        add_filter( 'the_content', array($this, 'highlight_result_handler'), 50);
        add_filter( 'the_excerpt', array($this, 'highlight_result_handler'), 50);
        add_filter( 'the_tags', array($this, 'highlight_result_handler'), 50);
        
        // admin message     
        add_action('admin_notices',  array( $this, 'show_admin_message'));
        add_action('admin_init', array( $this, 'admin_init_handler'));
        // Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        add_action( 'admin_head', array( $this, 'add_plugin_admin_head' ) );
        
        // WooCommerce Integration
        add_filter( 'woocommerce_integrations', array( $this, 'add_woocommerce_integrations_handler' ) );
        
        // InstantSearch+ search box widget
        add_action( 'widgets_init', array( $this, 'widgets_registration_handler' ) );
        
        add_action( 'woocommerce_scheduled_sales', array( $this, 'on_scheduled_sales'));
        add_action( 'wp_ajax_wcis_dismiss_just_created', array( $this, 'wcis_dismiss_just_created') );
        add_action( 'wp_ajax_wcis_dismiss_alert', array( $this, 'wcis_dismiss_alert') );
        add_action( 'wp_ajax_wcis_dismiss_notification', array( $this, 'wcis_dismiss_notification') );
        
	}
    
	private function isp_update_option($option_key, $option_value){
	    wp_cache_delete($option_key, 'options');
	    update_option($option_key, $option_value);
	}
	
	private function isp_delete_option($option_key){
	    wp_cache_delete($option_key, 'options');
	    delete_option($option_key);
	}

	/**
	 * Create search results page if not exists - saves it in 'wcis_general_settings' option
	 *
	 * @since     1.5.0
	 */
	private function wcis_serp_activation() {
		$options = get_option( 'wcis_general_settings' );
		if ($options && array_key_exists('serp_page_id', $options) && $options['serp_page_id'] != null &&
                get_post($options['serp_page_id']) != null){
		    return;   // search results page already exist
		}
        
		$options = array();
		$wcis_serp_page = array(
            //'ID'             => [ <post id> ] // Are you updating an existing post?
            'post_content'   => '[wcis_serp_results]', // The full text of the post.
            'post_name'      => 'search-results', //[ <string> ] // The name (slug) for your post
            'post_title'     => __('Search Results','WCISPlugin'), //[ <string> ] // The title of your post.
            'post_status'    => 'publish', //[ 'draft' | 'publish' | 'pending'| 'future' | 'private' | custom registered status ] // Default 'draft'.
            'post_type'      => 'page', //[ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] // Default 'post'.
            'post_author'    => get_current_user_id(), //[ <user ID> ] // The user ID number of the author. Default is the current user ID.
//             'post_excerpt'   => __('Search Results','WCISPlugin'), //[ <string> ] // For all your post excerpt needs.
            'post_date'      => the_date('Y-m-d H:i:s'), //[ Y-m-d H:i:s ] // The time post was made.
            //'post_date_gmt'  => [ Y-m-d H:i:s ] // The time post was made, in GMT.
            //'comment_status' => [ 'closed' | 'open' ] // Default is the option 'default_comment_status', or 'closed'.
            //'post_category'  => [ array(<category id>, ...) ] // Default empty.
            //'tags_input'     => [ '<tag>, <tag>, ...' | array ] // Default empty.
            //'tax_input'      => [ array( <taxonomy> => <array | string> ) ] // For custom taxonomies. Default empty.
            //'page_template'  => [ <string> ] // Default empty.
		);
		
		$serp_page_id = wp_insert_post( $wcis_serp_page );
		if (!$serp_page_id || is_wp_error($serp_page_id)){  // failure
		    $err_msg = "wcis_serp_activation - wp_insert_post failed";
		    self::send_error_report($err_msg);
		    return;
		}
		
		$options['serp_page_id'] = $serp_page_id ;
        // This option is not needed in case the WCIS SERP permalink changes by the user
		$options['serp_page_url'] = get_page_link( $serp_page_id );
		$options['is_serp_enabled'] = false;
			
		self::isp_update_option( 'wcis_general_settings', $options );
	}
	

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}
	
	public static function wcis_add_action_links( $links ) {	
		$url = self::DASHBOARD_URL.'wc_dashboard';
		$params = '?site_id=' . get_option( 'wcis_site_id' );
		$params .= '&authentication_key=' . get_option('authentication_key');
		$params .= '&new_tab=1';
		$params .= '&v=' . WCISPlugin::VERSION;
		$params .= '&store_id='. get_current_blog_id(); 
		$params .= '&site='.get_option('siteurl');
				
		return array_merge(
				array(
						'Settings' => '<a href="' . $url . $params . '" target="_blank">'. 
							__( 'Settings', WCISPlugin::get_instance()->get_plugin_slug() ) .'</a>'
				),
				$links
		);
		
	}
	
	public function widgets_registration_handler(){
		register_widget('WCISPluginWidget');
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function on_init_hook() {
	    self::load_plugin_textdomain();    // Load plugin text domain
	    self::wcis_set_session();
	}

	/**
	 * on_permalink_structure_changed
	 * checks if permalinks changed to plain mode
	 * if yes disables smart navigation in wp and in isp server
	 * 
	 * @since 1.7.0
	 * 
	 * @param $old_permalink_structure
	 * @param $permalink_structure
	 *
	 * @return void
	 */
    public function on_permalink_structure_changed($old_permalink_structure, $permalink_structure) {
        if ($permalink_structure == '') {

            $enable_rewrite_links = get_option('wcis_enable_rewrite_links');

            if ($enable_rewrite_links != 1) {
                return;
            }
            self::isp_update_option('wcis_enable_rewrite_links', 0);
            self::isp_delete_option('wcis_serp_page_name');
            $url = self::SERVER_URL . 'wc_disable_permalinks';

            $args = array(
                'body' => array(
                    'site' => get_option('siteurl'),
                    'site_id' => get_option( 'wcis_site_id' ),
                    'authentication_key' => get_option('authentication_key'),
                    'err_desc' => 'User switched to plain navigation'
                ),
                'timeout' => 15,
            );

            $resp = wp_remote_post( $url, $args );
        }
    }

	/**
	 * on_page_rewrite_rules
	 * when rewrite rules are refreshed or changed creates the rule for
	 * smart navigation
	 * @since 1.7.0
	 * 
	 * @param $page_rewrite
	 *
	 * @return array
	 */
    public function on_page_rewrite_rules($page_rewrite) {
		$options = get_option( 'wcis_general_settings' );

        if (is_array($options) && $options['is_serp_enabled'] === true) {
			$serp_page = get_post($options['serp_page_id']);
			$serp_page_name = $serp_page->post_name;
            
            if ($serp_page_name != null && $serp_page_name != '') {
				$page_rewrite = $this->set_serp_rewrite_rule($serp_page_name, $page_rewrite);
			}

        }

        return $page_rewrite;
    }

	/**
	 * on_request_hook
	 * overrides product_cut link structure if Smart Navigation is enabled
	 * 
	 * @since 1.7.0
	 *
	 * @param $query_vars
	 *
	 * @return mixed
	 */
    public function on_request_hook($query_vars) {
        $enable_rewrite_links = get_option('wcis_enable_rewrite_links');

        if ($enable_rewrite_links == 1) {
            $serp_page_name = get_option('wcis_serp_page_name');

			if ($serp_page_name != null && $serp_page_name != '') {
				global $wp_rewrite;

				$rewrite = $wp_rewrite->wp_rewrite_rules();

				if ($wp_rewrite->using_permalinks() && isset($rewrite[$serp_page_name.'/(.?.+?)(?:/([0-9]+))?/?$'])) {
					$wp_rewrite->add_permastruct('product_cat', $serp_page_name.'/category/%product_cat%', array(
						'with_front' => false,
						'ep_mask' => 0,
						'paged' => true,
						'feed' => true,
						'forcomments' => false,
						'walk_dirs' => true,
						'endpoints' => true,
					));
				}
			}

        }
        return $query_vars;
    }

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public function activate( $network_wide )
	{			
		self::single_activate();
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public function deactivate( $network_wide ) {	
		if(self::is_network_admin()) {
			foreach(self::get_blog_ids() as $blog_id) {
				self::switch_to_blog($blog_id);
				self::single_deactivate($network_wide);
				self::restore_current_blog();
			}
		} else {
			self::single_deactivate($network_wide);
		}
        
	}
	
	private function single_deactivate($network_wide) {
		wp_clear_scheduled_hook( 'instantsearchplus_cron_request_event' );
		wp_clear_scheduled_hook( 'instantsearchplus_cron_request_event_backup' );
		wp_clear_scheduled_hook( 'instantsearchplus_cron_check_alerst' );
		wp_clear_scheduled_hook( 'instantsearchplus_send_all_categories' );
		wp_clear_scheduled_hook( 'instantsearchplus_send_batches_if_unreachable' );
		
		self::isp_delete_option('is_activation_triggered');

		$url = self::SERVER_URL . 'wc_update_site_state';

		$args = array (
				'body' => array (
						'site' => get_option('siteurl'), 
						'site_id' => get_option('wcis_site_id'), 
						'store_id' => get_current_blog_id(),
						'authentication_key' => get_option ( 'authentication_key' ),
						'email' => get_option ( 'admin_email' ),
						'site_status' => 'deactivate' 
				),
				'timeout' => 10 
		);
		
		
		$resp = wp_remote_post( $url, $args );
		if (is_wp_error($resp) || $resp['response']['code'] != 200){
		    $err_msg = "wc_update_site_state deactivate status failed";
		    self::send_error_report($err_msg);
		} else {
		    $response_json = json_decode($resp['body'], true);
		    if (is_array($response_json) && array_key_exists('reset', $response_json)){
		        if ($response_json['reset'] == '1' || $response_json['reset'] == 1){
		            WCISPlugin::reset_database();
		            $err_msg = "reseting DB!!!";
		            self::send_error_report($err_msg);
		        }
		    }
		}
	}

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function uninstall( $network_wide ) {	
		if (function_exists ( 'is_multisite' ) && is_multisite()) {
			$blog_ids = array();
			$sites = wp_get_sites();
			foreach ( $sites as $site ) {
				$blog_ids[]=$site['blog_id'];
			}
			
			foreach($blog_ids as $blog_id) {
				switch_to_blog($blog_id);
				WCISPlugin::single_uninstall($network_wide);
				restore_current_blog();
			}
		} else {
			WCISPlugin::single_uninstall($network_wide);
		}
	}
		
	private static function single_uninstall($network_wide) {
		if ( ! current_user_can( 'activate_plugins' ) ){
			return;
		}
		
		$url = self::SERVER_URL . 'wc_update_site_state';
		
		$args = array (
				'body' => array (
						'site' => get_option('siteurl'), 
						'site_id' => get_option('wcis_site_id'),
						'store_id' => get_current_blog_id(),
						'authentication_key' => get_option( 'authentication_key' ),
						'email' => get_option( 'admin_email' ),
						'site_status' => 'uninstall' 
				) 
		);
		
		
		$resp = wp_remote_post( $url, $args );
		
		WCISPlugin::reset_database();
	}
	
	private static function reset_database(){
	    // deleting the database values
	    self::isp_delete_option('wcis_site_id');
	    self::isp_delete_option('wcis_batch_size');
	    self::isp_delete_option('authentication_key');
	    self::isp_delete_option('wcis_timeframe');
	    self::isp_delete_option('max_num_of_batches');
	    self::isp_delete_option('wcis_total_results');
	    
	    // compatibility
	    self::isp_delete_option('fulltext_disabled');
	    self::isp_delete_option('just_created_site');
	    self::isp_delete_option('wcis_fulltext_ids');
	    self::isp_delete_option('wcis_did_you_mean_enabled');
	    self::isp_delete_option('wcis_did_you_mean_fields');
	    self::isp_delete_option('wcis_search_query');
	    
	    self::isp_delete_option('wcis_enable_highlight');
	    
	    self::isp_delete_option('cron_product_list');
	    self::isp_delete_option('cron_category_list');
	    self::isp_delete_option('cron_in_progress');
	    self::isp_delete_option('cron_send_batches_disable');
	    self::isp_delete_option('wcis_site_alert');
	    self::isp_delete_option('wcis_just_created_alert');
	    self::isp_delete_option('cron_update_product_list_by_date');

		/**
		 * deletes Smart Navigation rule and options
		 */
        $serp_page_name = get_option('wcis_serp_page_name');
        if ($serp_page_name != null && $serp_page_name != '') {
            $regex = $serp_page_name.'/(.?.+?)(?:/([0-9]+))?/?$';
            $rules = get_option('rewrite_rules');
            unset($rules[$regex]);
            self::isp_update_option('rewrite_rules', $rules);
        }
        self::isp_delete_option('wcis_enable_rewrite_links');
        self::isp_delete_option('wcis_serp_page_name');
	    
	    $options = get_option( 'wcis_general_settings' );
	    if ($options && array_key_exists('serp_page_id', $options) && $options['serp_page_id'] != null &&
	           get_post($options['serp_page_id']) != null){
	        wp_delete_post($options['serp_page_id'], true);    // second parameter is force_delete - Whether to bypass trash and force deletion
	    }
	    self::isp_delete_option('wcis_general_settings');
	    
	}
	
	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		self::switch_to_blog( $blog_id );
		self::single_activate();
		self::restore_current_blog();
	}
	
	const MAIN_SITE_BLOG_ID='1'; 
	private function get_blog_ids() {
		$blog_ids = array();
		if (function_exists ( 'is_multisite' ) && is_multisite()) {
			$sites = wp_get_sites();
			foreach ( $sites as $site ) {
				$blog_ids[]=$site['blog_id'];
			}
		} else {
			$blog_ids[] = get_current_blog_id();
		}
		return $blog_ids;
	}
	
	private function is_main_site() {
		return get_current_blog_id() == self::MAIN_SITE_BLOG_ID;
	}

	private function switch_to_blog($blog_id) {
		if (function_exists( 'is_multisite' ) && is_multisite()) {
			switch_to_blog($blog_id);
		}
	}
	
	private function restore_current_blog() {
		if (function_exists( 'is_multisite' ) && is_multisite()) {
			restore_current_blog();
		}
	}
	
	private function is_network_admin() {
		if (function_exists( 'is_multisite' ) && is_multisite()) {
			return is_network_admin();
		}
		
		return false;
	}
	
    private function is_post_valid($element_id, $post_type = 'product'){
		$enabled_languages = get_option('isp_wpml_languages');
		if ($enabled_languages){
			include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			if (is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) || 
			         is_plugin_active('sitepress-multilingual-cms/sitepress.php' )){     // || function_exists('icl_object_id') || ICL_SITEPRESS_VERSION constant check
				if ($post_type == 'product' && function_exists('wpml_get_language_information')){
					$language_info = wpml_get_language_information($element_id);
					if (!$language_info || !is_array($language_info)){    // WP_Error is returned from wpml_get_language_information(...)
						return true;
					}
					if (in_array($language_info['locale'], $enabled_languages)){
						return true;
					} else {
						return false;
					}
				
				} elseif ($post_type == 'category'){
					global $sitepress;
					$language_code = $sitepress->get_language_for_element($element_id, 'tax_product_cat');
					if (!$language_code){    // not valid return value
						return true;
					}
					foreach ($enabled_languages as $lang){
						if (substr($lang, 0, 2) == $language_code){
							return true;
						}
					}
					return false;
				}
			}
		}
	    return true;
	}
    
/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private function single_activate($is_retry = false) {
		if (get_option('is_activation_triggered')){
			return;
		}
		
		self::isp_update_option( 'is_activation_triggered', true );
		self::isp_update_option( 'wcis_just_created_alert', true );
		
		if (! wp_next_scheduled ( 'instantsearchplus_cron_check_alerst' )) {
			wp_schedule_event ( time(), 'daily', 'instantsearchplus_cron_check_alerst' );
		}
		
		self::wcis_serp_activation();
		
		$url = self::SERVER_URL . 'wc_install';
		$stores_array = array();
		
		try {
			if (function_exists ( 'is_multisite' ) && is_multisite()) {
				$is_multisite_on = true;
			} else {
				$is_multisite_on = false;
			}
			
			// $json_multisite = json_encode ( $multisite_array );
		} catch ( Exception $e ) {
			$is_multisite_on = false;
		}
		// end multisite
		
		if(self::is_network_admin()) {

			foreach ( self::get_blog_ids() as $blog_id ) {
				self::switch_to_blog($blog_id);
				
				$products_count = 0;
				if (property_exists(wp_count_posts( 'product' ), 'publish')){
				    $products_count = wp_count_posts( 'product' )->publish;
				}
				
				$stores_array[$blog_id] = array();
				$stores_array[$blog_id]['product_count'] = $products_count;
				$stores_array[$blog_id]['site'] = get_option( 'siteurl' );
				
				self::restore_current_blog();
	 		}
		} 

		$json_stores = json_encode( $stores_array );
		$products_count = 0;
		if (property_exists(wp_count_posts( 'product' ), 'publish')){
		    $products_count = wp_count_posts( 'product' )->publish;
		}

		$args = array (
		
				'body' => array (
						'site' => get_option('siteurl'),
						'product_count' => $products_count,
						'store_id' => get_current_blog_id(),
						'email' => get_option( 'admin_email' ),
						'is_multisite' => $is_multisite_on,
						'is_network_admin' => is_network_admin(),
						'version' => self::VERSION
				),
				'timeout' => 30
		);
		
		if(self::is_network_admin()) {
			
			$args['body']['stores'] = $json_stores;
		}
		
		self::switch_to_blog('1');
		if(get_option('wcis_site_id')) {
			$args['body']['site_id'] = get_option('wcis_site_id'); // the group uuid always stored in main site
		}
		self::restore_current_blog();

		$resp = wp_remote_post( $url, $args );
		
		if (is_wp_error ( $resp ) || $resp['response']['code'] != 200) {
			$err_msg = "install req returned with an error code, sending retry install request: " . $is_retry;
			try {
				if (is_wp_error ( $resp )) {
					$err_msg = $err_msg . " - error msg: " . $resp->get_error_message();
				}
			} catch ( Exception $e ) {
			}
			
			self::send_error_report ( $err_msg );
			if (! $is_retry) {
				self::single_activate ( true );
			}
		} else { // $resp['response']['code'] == 200
		         // the server returns site id in the body of the response, save it in the options
			
			$response_json = json_decode ( $resp['body'] );
			if ($response_json == Null) {
				$err_msg = "After install json_decode returned null";
				self::send_error_report ( $err_msg );
				if (! $is_retry) {
					self::single_activate ( true );
				}
				return;
			}
			
			$send_products_need_batches=array();
			$send_products = null;
			
			$last_blog = get_current_blog_id();
			$site_uuid = null;
			foreach ( $response_json as $store_id => $result ) {
				
				self::switch_to_blog ( $store_id );
	
				try {
					
					$site_id = $result->{'site_id'};
					$batch_size = $result->{'batch_size'};
					self::isp_update_option( 'wcis_site_id', $site_id );
					if($site_uuid == null) {
						$site_uuid = $site_id;
					}
					self::isp_update_option( 'wcis_batch_size', $batch_size );
					$max_num_of_batches = $result->{'max_num_of_batches'};
					self::isp_update_option( 'max_num_of_batches', $max_num_of_batches );
					
					$authentication_key = $result->{'authentication_key'};
					self::isp_update_option( 'authentication_key', $authentication_key );
					
					$update_product_timeframe = $result->{'wcis_timeframe'};
					self::isp_update_option( 'wcis_timeframe', $update_product_timeframe );
				} catch ( Exception $e ) {
					$err_msg = "After install internal exception raised msg: " . $e->getMessage();
					self::send_error_report ( $err_msg );
				}
				
				$additional_fetch_info = self::push_wc_products();
				if($additional_fetch_info!=null) {
					$send_products_need_batches[$store_id] = $additional_fetch_info;
				}

				wp_schedule_single_event ( time() + self::CRON_SEND_CATEGORIES_TIME_INTERVAL, 'instantsearchplus_send_all_categories' );
				
				self::restore_current_blog();
			}
			
			self::switch_to_blog('1');
			if($site_uuid != null) {
				self::isp_update_option('wcis_site_id', $site_uuid);
			}
			self::restore_current_blog();
			
			$additional_fetch_array = array();
			foreach($send_products_need_batches as $store_id=>$additional_fetch_info) {
				self::switch_to_blog($store_id);
				$additional_fetch_array[] = self::get_additional_fetch_args($additional_fetch_info);

				self::restore_current_blog();
			}
			
			unset($send_products_need_batches);
			
			if(count($additional_fetch_array) > 0) {
				$additional_fetch_array_encoded = json_encode($additional_fetch_array);
				self::request_additional_fetch($additional_fetch_array_encoded);
			}
			
			self::switch_to_blog($last_blog);
		}

	}
	
	private function request_additional_fetch($additional_fetch_array_encoded) {
		$resp = wp_remote_post(
				 self::SERVER_URL . 'wc_additional_fetch', 
				 array('body' => 
				 		array(
				 				'infos' => $additional_fetch_array_encoded
				 				),
				 		'timeout' => 10
				 ));
		if (is_wp_error($resp) || $resp['response']['code'] != 200){
			$err_msg = "additional_fetch_info request failed";
			self::send_error_report($err_msg);
		}
	}
	
	public function on_category_edit($category_id = null){
		if ($category_id == null)
			return;		
		self::on_category_update($category_id, 'edit');
	}
	
	public function on_category_create($category_id){
		if ($category_id == null)
			return;		
		self::on_category_update($category_id, 'create');
	}
	
	public function on_category_delete($category_id){
		if ($category_id == null)
			return;		
		self::on_category_update($category_id, 'delete');
	}
	
	public function on_category_update($category_id, $action){
		if (!self::is_post_valid($category_id, 'category')){
	        return;
	    }
	    $categorys_list = get_option('cron_category_list');
	    $timestamp = wp_next_scheduled( 'instantsearchplus_cron_request_event' );
	    if(get_option('wcis_timeframe')){
	        $timeframe = get_option('wcis_timeframe');
	    } else {
	        $timeframe = self::CRON_EXECUTION_TIME;
	    }
	    
	    if ($timestamp != false){ // event already scheduled
	        if ($categorys_list){ // category cron list is not empty
	            self::insert_element_to_cron_list($category_id, $action, self::ELEMENT_TYPE_CATEGORY);
	        } else {
	            wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
	            $err_msg = "event scheduled to Cron but category's list is empty";
	            self::send_error_report($err_msg);
	        }
	    } else {  // event not scheduled
	        if ($categorys_list){ 
	            if(!get_option('cron_in_progress') || (time() - intval(get_option('cron_in_progress')) >= self::CRON_EXECUTION_TIME_RETRY)){	                
	                self::execute_update_request();
	                wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
	            }
	            // add current category to the list and schedule cron event
	            self::insert_element_to_cron_list($category_id, $action, self::ELEMENT_TYPE_CATEGORY);
	        } else {
	            self::insert_element_to_cron_list($category_id, $action, self::ELEMENT_TYPE_CATEGORY);
	            wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
	        }
	    }	
	}
	

	private function query_products($store_id,$page = 1) { 
		self::switch_to_blog($store_id);
			
		$query_args = array();
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		if (is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' )) {
			// set base query arguments for multi-language site
			$query_args = array (
					'fields' => 'ids',
					'post_type' => 'product',
					'post_status' => 'publish',
					'posts_per_page' => get_option('wcis_batch_size'),
					'meta_query' => array(),
					'paged' => $page,
					'suppress_filters' => true
			);
		} else {
			// set base query arguments
			$query_args = array(
					'fields' => 'ids',
					'post_type' => 'product',
					'post_status' => 'publish',
					'posts_per_page' => get_option('wcis_batch_size'),
					'meta_query' => array(),
					'paged' => $page 
			);
		}
		
		self::restore_current_blog(); 
		
		return new WP_Query($query_args);
	}
	
	private function get_additional_fetch_args($additional_fetch_info, $is_products_install_batch = true) {
		$total_products 				= $additional_fetch_info['total_products'];
		$total_pages 					= $additional_fetch_info['total_pages'];
		 
		if ($total_products == 0) {
			$batch_number = 0;
		} else {
			$batch_number = $additional_fetch_info['current_page'];
		}
		
		return array (
			'site' => get_option('siteurl'),
			'site_id' => get_option('wcis_site_id'),
			'store_id' => get_current_blog_id(),
			'total_batches' => $total_pages,
			'wcis_batch_size' => get_option( 'wcis_batch_size' ),
			'total_products' => $total_products,
			'batch_number' => $batch_number,
		);
	}
    
    private function push_wc_products($is_products_install_batch = true)
    {
        /**
         * Check if WooCommerce is active
         **/
    
        try{
	        include_once (ABSPATH . 'wp-admin/includes/plugin.php');
			if (in_array ( 'woocommerce/woocommerce.php', apply_filters ( 'active_plugins', get_option ( 'active_plugins' ) ) ) 
					|| is_plugin_active ( 'woocommerce/woocommerce.php' )) {    	        		
				try {
					$product_array = array();
		            $loop = self::query_products(get_current_blog_id()); 
		            $page        = $loop->get( 'paged' );	// batch number
					$total       = $loop->found_posts;		// total number of products
					$total_pages = $loop->max_num_pages;	// total number of batches

		            $max_num_of_batches = get_option('max_num_of_batches');
		            $is_additional_fetch_required = false;
		            
		            while ($page <= $total_pages){
		            	if ($loop->have_posts()){
		            		foreach( $loop->posts as $id ){
		            		    if (self::is_post_valid($id)){
    		            			$product = self::get_product_from_post($id);
    		            			$product_array[] = $product;
		            		    }
		            		}
		            	}
		            	
		                if($max_num_of_batches == $page && $total_pages > $max_num_of_batches){
		                	// need to schedule request from server side to send the rest of the batches after activation ends
		                    wp_schedule_single_event(time() + (self::CRON_SEND_CATEGORIES_TIME_INTERVAL * 10 /*5 minutes*/),
		                    		 'instantsearchplus_send_batches_if_unreachable',
		                    		array($page + 1)); 
		                    
		                    $is_additional_fetch_required = true;
		                }
		                
		                $send_products = array(
		                		'total_pages' 				   	=> $total_pages, 
		                		'total_products'				=> $total, 
		                		'current_page' 					=> $page, 
		                		'products'						=> $product_array,
		                		// in new version we never send additional fetch in send batch
		                		
		                );

		                self::send_products_batch($send_products, $is_products_install_batch);
		                
		                // clearing array
		                unset($product_array);	
		                
		                $product_array = array();
		                unset($send_products);
		                if($is_additional_fetch_required) {
		                	return array(
		                			'total_pages' => $total_pages,
		                			'total_products' => $total,
		                			'current_page' => $page
		                	);
		                }
		                
		                $page = $page + 1;
		                
		                $loop = self::query_products( get_current_blog_id(), $page); 
		            }
		           
	            } catch (Exception $e) {
	            	$err_msg = "exception on woocommerce check, msg: " . $e->getMessage();
	            	self::send_error_report($err_msg);
	            }
	                        
	        } else {        	
	        	// alternative way  
	        	try{
		        	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
		        	if (is_plugin_active( 'woocommerce/woocommerce.php')){
		        		$is_woo = 'true';
		        	} else { 
		        		$is_woo = 'false';
		        	}
	        	} catch (Exception $e){
	        		$is_woo = 'false (Exception)';
	        	}
	        	
	        	$err_msg = "can't find active plugin of woocommerce, alternative check: " . $is_woo;
	        	self::send_error_report($err_msg);
	        }
        } catch (Exception $e){
        	$err_msg = "before fetch products Exception was raised";
        	self::send_error_report($err_msg);
        }
        
        return null;

    }
    
    private function push_wc_batch($batch_num, $store_id){ 
    	$loop = self::query_products($store_id, $batch_num);
    	self::switch_to_blog($store_id); 
    	$product_array = array();
    	$total       = $loop->found_posts;		// total number of products
    	$total_pages = $loop->max_num_pages;	// total number of batches
    	while ( $loop->have_posts() ){
    		$loop->the_post();
    		$product_id = get_the_ID();
    		if (self::is_post_valid($product_id)){
        		$product = self::get_product_from_post($product_id);
        		$product_array[] = $product;
    		}
    	}
    	
    	$send_products = array(
    			'total_pages' 				   	=> $total_pages,
    			'total_products'				=> $total,
    			'current_page' 					=> $batch_num,
    			'products'						=> $product_array 
    	);    	
    	wp_reset_postdata();
    	self::send_products_batch($send_products);	
    	self::restore_current_blog(); 
    }
    
    
    private function get_product_from_post($post_id)
    {
    	$woocommerce_ver_below_2_1 = false;
    	if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ){
    		$woocommerce_ver_below_2_1 = true;
    	}

    	if ($woocommerce_ver_below_2_1){
    		$product = 	new WC_Product_Simple($post_id);
    	} else {
        	$product = new WC_Product( $post_id );
    	}
        
        //$post_categories = wp_get_post_categories( $post_id );
        //$categories = get_the_category();
    	try{
        	$thumbnail = $product->get_image();
        	
        	if ($thumbnail){
        	    if (preg_match('/data-lazy-src="([^\"]+)"/s', $thumbnail, $match) && strpos($match[1], "blank.gif") == false){
        	        $thumbnail = $match[1];
        	    } else if (preg_match('/data-lazy-original="([^\"]+)"/s', $thumbnail, $match) && strpos($match[1], "blank.gif") == false){
        	        $thumbnail = $match[1];
        	    } else if (preg_match('/lazy-src="([^\"]+)"/s', $thumbnail, $match) && strpos($match[1], "blank.gif") == false){           // Animate Lazy Load Wordpress Plugin
        	        $thumbnail = $match[1];
        	    } else if(preg_match('/data-echo="([^\"]+)"/s', $thumbnail, $match) && strpos($match[1], "blank.gif") == false) {
        	    	$thumbnail = $match[1];
    	    	} else if(preg_match('/data-src="([^\"]+)"/s', $thumbnail, $match) && strpos($match[1], "blank.gif") == false) {          // EWWW Image Optimizer
    	    	    $thumbnail = $match[1];
	    	    } else if(preg_match('/data-original="([^\"]+)"/s', $thumbnail, $match) && strpos($match[1], "blank.gif") == false) {     // http://www.appelsiini.net/projects/lazyload
	    	        $thumbnail = $match[1];
        	    } else {
        	        preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $thumbnail, $result);
        	        $thumbnail = array_pop($result);
        	    }	
        	}
    	} catch (Exception $e){
    	    $err_msg = "exception raised in thumbnails";
    	    self::send_error_report($err_msg);
    	    $thumbnail = '';
    	}
    	try{
    	    $serp_image = $product->get_image(array(500, 500));
    	     
    	    if ($serp_image){
    	        if (preg_match('/data-lazy-src="([^\"]+)"/s', $serp_image, $match) && strpos($match[1], "blank.gif") == false){
    	            $serp_image = $match[1];
    	        } else if (preg_match('/data-lazy-original="([^\"]+)"/s', $serp_image, $match) && strpos($match[1], "blank.gif") == false){
    	            $serp_image = $match[1];
    	        } else if (preg_match('/lazy-src="([^\"]+)"/s', $serp_image, $match) && strpos($match[1], "blank.gif") == false){          // Animate Lazy Load Wordpress Plugin
    	            $serp_image = $match[1];
    	        } else if(preg_match('/data-echo="([^\"]+)"/s', $serp_image, $match) && strpos($match[1], "blank.gif") == false){
    	            $serp_image = $match[1];
	            } else if(preg_match('/data-src="([^\"]+)"/s', $serp_image, $match) && strpos($match[1], "blank.gif") == false){           // EWWW Image Optimizer
	                $serp_image = $match[1];
                } else if(preg_match('/data-original="([^\"]+)"/s', $serp_image, $match) && strpos($match[1], "blank.gif") == false){      // http://www.appelsiini.net/projects/lazyload
                    $serp_image = $match[1];
    	        } else {
    	            preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $serp_image, $result);
    	            $serp_image = array_pop($result);
    	        }
    	    }
    	     
    	} catch (Exception $e){
    	    $err_msg = "exception raised in serp_image";
    	    self::send_error_report($err_msg);
    	    $serp_image = '';
    	}

    	// WP Rocket support - in order to get CDN image
    	if ($thumbnail != '' && function_exists('get_rocket_cdn_url')) {
    	    $thumbnail = get_rocket_cdn_url($thumbnail);
    	    if ($serp_image != ''){
    	        $serp_image = get_rocket_cdn_url($serp_image);
    	    }
    	}
    	
    	// handling scheduled sale price update
    	if (!$woocommerce_ver_below_2_1){
    	    $sale_price_dates_from = get_post_meta( $post_id, '_sale_price_dates_from', true );
    	    $sale_price_dates_to = get_post_meta( $post_id, '_sale_price_dates_to', true );
    	    if ($sale_price_dates_from || $sale_price_dates_to){
    	        self::schedule_sale_price_update($post_id, null);
    	    }
    	    if ($sale_price_dates_from){
    	        self::schedule_sale_price_update($post_id, $sale_price_dates_from);
    	    } 
    	    if ($sale_price_dates_to){
    	        self::schedule_sale_price_update($post_id, $sale_price_dates_to);
    	    }
    	}
    	
    	$product_tags = array();
    	foreach (wp_get_post_terms($post_id, 'product_tag') as $tag){
    	    $product_tags[] = $tag->name;
    	}
    	
    	$product_brands = array();
    	if (taxonomy_exists('product_brand')){
        	foreach (wp_get_post_terms($post_id, 'product_brand') as $brand){
        	    $product_brands[] = $brand->name;
        	}
    	}
    	
    	$taxonomies = array();
    	try{
        	$all_taxonomies = get_option('wcis_taxonomies');
        	if (is_array($all_taxonomies)){
        	    foreach ($all_taxonomies as $taxonomy){
        	        if (taxonomy_exists($taxonomy) && !array_key_exists($taxonomy, $taxonomies)){
        	            foreach (wp_get_post_terms($post_id, $taxonomy) as $taxonomy_value){
        	                if (!array_key_exists($taxonomy, $taxonomies)){
        	                    $taxonomies[$taxonomy] = array();
        	                }
        	                $taxonomies[$taxonomy][] = $taxonomy_value->name;
        	            }
        	        }
        	    }
        	}
    	} catch (Exception $e){}
    	
    	$acf_fields = array();
    	try{
    	    if (class_exists('acf') && function_exists('get_field')){
    	        $all_acf_fields = get_option('wcis_acf_fields');
    	        if (is_array($all_acf_fields)){
    	            foreach ($all_acf_fields as $acf_field_name){
    	                $acf_field_value = get_field($acf_field_name, $post_id);
    	                if ($acf_field_value){
        	                $acf_fields[$acf_field_name] = $acf_field_value;
    	                }
    	            }
    	        }
    	    }
    	} catch (Exception $e){}

		$visibility = $product->is_visible()? true : $product->visibility == 'search'? true : false;

    	$send_product = array('product_id' => $product->id,
    			'currency' => get_woocommerce_currency(),
    			'price' =>$product->get_price(),
    			'url' =>get_permalink($product->id),
    			'thumbnail_url' =>$thumbnail,
    	        'serp_image' => $serp_image,
    			'action'=>'insert',
    			'description'=>$product->get_post_data()->post_content,
    			'short_description'=>$product->get_post_data()->post_excerpt,
    			'name'=>$product->get_title(),
    			'sku' => $product->get_sku(),
//     			'categories'=>$product->get_categories(),
    	        'tag' => $product_tags,
    			'store_id'=>get_current_blog_id(),
    			'identifier' => (string)$product->id,
    	        
    	        'product_brand' => $product_brands,
    	        'taxonomies' => $taxonomies,
    	        'acf_fields' => $acf_fields,
    	
    			'sellable' => $product->is_purchasable(),
    			'visibility' => $visibility,
    	
    			'stock_quantity' => $product->get_stock_quantity(),
    			'is_managing_stock' => $product->managing_stock(),
    			'is_backorders_allowed' => $product->backorders_allowed(),
    			'is_purchasable' => $product->is_purchasable(),
    			'is_in_stock' => $product->is_in_stock( ),
    			'product_status' => get_post_status($post_id),
    	);
    	
    	try{
    	    $variable = new WC_Product_Variable($post_id);
    	     
    	    $variations = $variable->get_available_variations();
    	    $variations_sku = '';
    	    $variations_products = array();
    	    if (!empty($variations)){
    	        foreach ($variations as $variation){
    	            $variations_products[$variation['variation_id']] = array(
                            'sellable'                  => $variation['is_in_stock'],
                            'visibility'                => $variation['variation_is_visible'],
                            'attributes'                => $variation['attributes'],
                            'sku'                       => $variation['sku'],
    	                    'price'                     => $variation['display_price'],
    	                    'price_compare_at_price'    => $variation['display_regular_price'],
    	                    'image'                     => $variation['image_src']            // $variation['image_link'] for full size image
    	            );
    	            if ($product->get_sku() != $variation['sku']){
    	                $variations_sku .= $variation['sku'] . ' ';
    	            }
    	        }
    	    }
    	    $send_product['variations_sku'] = $variations_sku;
    	    $send_product['variations'] = $variations_products;
    	    
    	     
    	    $all_attributes = $product->get_attributes();
    	    $attributes = array();
    	    if (!empty($all_attributes)){
    	        foreach ($all_attributes as $attr_mame => $value){
    	            if ($all_attributes[$attr_mame]['is_taxonomy']){
    	                if (!$woocommerce_ver_below_2_1){
    	                    $attributes[$attr_mame] = wc_get_product_terms( $post_id, $attr_mame, array( 'fields' => 'names'));
    	                } else {
    	                    $attributes[$attr_mame] = woocommerce_get_product_terms( $post_id, $attr_mame, 'names');
    	                }
    	            } else {
    	                $attributes[$attr_mame] = $product->get_attribute($attr_mame);
    	            }
    	        }
    	    }

    	    $send_product['attributes'] = $attributes;
    	    $send_product['total_variable_stock'] = $variable->get_total_stock();

			try{
				if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) ){
					if (function_exists('wc_get_product')){
						$original_product = wc_get_product($product->id);
						if (is_object($original_product)){
							$visibility = $original_product->is_visible()? true : $original_product->visibility == 'search'? true : false;
							$send_product['visibility'] = $visibility;
							$send_product['product_type'] = $original_product->product_type;
						}
					}
				} else {
					if(function_exists('get_product')){
						$original_product = get_product($product->id);
						if (is_object($original_product)){
							$visibility = $original_product->is_visible()? true : $original_product->visibility == 'search'? true : false;
							$send_product['visibility'] = $visibility;
							$send_product['product_type'] = $original_product->product_type;
						}
					}
				}
			} catch (Exception $e){}
    	    
    	}catch (Exception $e){
    	    $err_msg = "exception raised in attributes";
    	    self::send_error_report($err_msg);
    	}
    	
    	if(!$woocommerce_ver_below_2_1){
	    	try{	    		
	    	    $send_product['price_compare_at_price'] = $product->get_regular_price();
	    		$send_product['price_min'] = $variable->get_variation_price('min');
	    		$send_product['price_max'] = $variable->get_variation_price('max');
	    		$send_product['price_min_compare_at_price'] = $variable->get_variation_regular_price('min');
	    		$send_product['price_max_compare_at_price'] = $variable->get_variation_regular_price('max');
	    	}catch (Exception $e){
	    		$send_product['price_compare_at_price'] = null;
	    		$send_product['price_min'] = null;
	    		$send_product['price_max'] = null;
	    		$send_product['price_min_compare_at_price'] = null;
	    		$send_product['price_max_compare_at_price'] = null;
	    	}
    	} else {
    		$send_product['price_compare_at_price'] = null;
    		$send_product['price_min'] = null;
    		$send_product['price_max'] = null;
    		$send_product['price_min_compare_at_price'] = null;
    		$send_product['price_max_compare_at_price'] = null;
    	}
    	
    	$send_product['description'] = self::content_filter_shortcode_with_content($send_product['description']);
    	$send_product['short_description'] = self::content_filter_shortcode_with_content($send_product['short_description']);
    	
    	$send_product['description'] = self::content_filter_shortcode($send_product['description']);
    	$send_product['short_description'] = self::content_filter_shortcode($send_product['short_description']);
    	
    	try{
    		if (defined('ICL_SITEPRESS_VERSION') && is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) && 
    				function_exists('wpml_get_language_information')){
    			if (version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' )) {
    				$language_info = apply_filters( 'wpml_post_language_details', NULL, $post_id);
    			} else {
    				$language_info = wpml_get_language_information($post_id);
    			}
    			if ($language_info && is_array($language_info) && array_key_exists('locale', $language_info)){    // WP_Error could be returned from wpml_get_language_information(...)
    				$send_product['lang'] = $language_info['locale'];
    			}
    		}
    	} catch (Exception $e){}

    	// get product's categories
    	$categories_array = get_the_terms($product->id, 'product_cat');
    	$product_categories = array();
    	if ($categories_array && !is_wp_error($categories_array)){
        	foreach ($categories_array as $category){
        	    $category_hierarchy = array();
        	    $current_category_id = $category->term_id;
        	    $category_hierarchy[] = $current_category_id;
        	    
        	    $parent_actegory_id = $category->parent;
        	    while ($parent_actegory_id != 0){
        	        $parent_category = get_term_by('id', $parent_actegory_id, 'product_cat');
        	        if ($parent_category){
        	            $category_hierarchy[] = $parent_category->term_id;
        	            $parent_actegory_id = $parent_category->parent;
        	        } else {
        	            break;
        	        }
        	    }
        	    $product_categories[] = $category_hierarchy;
        	}
    	}
    	$send_product['categories'] = $product_categories;

        return $send_product;
    }

    public function get_category_by_id($category_id){
        $product_category = get_term_by( 'id', $category_id, 'product_cat');
//         $category = get_term_by( 'id', $category_id, 'product_cat', 'ARRAY_A' );
        if ($product_category == false){
            $err_msg = "in get_category_by_id() - product_category == false";
            self::send_error_report($err_msg);
            return null;
        }
        
        $thumbnail_id = get_woocommerce_term_meta( $product_category->term_id, 'thumbnail_id', true );
        $image = wp_get_attachment_url( $thumbnail_id );
        $children = get_term_children($product_category->term_id, 'product_cat');
        
        $category = array(  
                'category_id'   => (string)$product_category->term_id,
                'name'          => $product_category->name,
			    'slug'          => $product_category->slug,
			    'is_active'     => $product_category->count > 0,
                'description'   => $product_category->description,
                'thumbnail'     => $image,
                'children'      => $children,
                'parent_id'     => (string)$product_category->parent,
                'url_path'      => get_term_link((int)$category_id, 'product_cat'),                    
        );
        
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) && is_plugin_active('sitepress-multilingual-cms/sitepress.php' )){
            try{
                global $sitepress;
                if ($sitepress){
                    $language_code = $sitepress->get_language_for_element($category_id, 'tax_product_cat');
                    if ($language_code){
                        $category['lang'] = $language_code;
                    }
                }
            } catch (Exception $e) {}
        }   
        
        return $category;
    }
    
    public function on_product_quick_save($product) {
        self::on_product_save($product->id);
    }

    public function on_product_save($post_id){
    	$post = get_post( $post_id );

        $wcis_enable_rewrite_links = get_option('wcis_enable_rewrite_links');
        global $wp_rewrite;
        if ($wcis_enable_rewrite_links == 1 && 'page' ==  $post->post_type && $wp_rewrite->using_permalinks()) {
			$this->check_serp_page_slug_changed($post);
            return;
        }

    	if ( 'product' !=  $post->post_type || get_post_status($post_id) == 'trash'){
    		return;
    	}
    	$action = 'insert';
        self::on_product_update($post_id, $action);
    }
    
    public function on_product_delete($post_id ){
        $post = get_post( $post_id );
        if ( 'product' !=  $post->post_type){
            return;
        }
        $action = 'delete';
        self::on_product_update($post_id, $action);
    }
    
    public function quantity_change_handler($order_id){
        if (!version_compare( WOOCOMMERCE_VERSION, '2.1', '<' )){
            $order = new WC_Order( $order_id );
            foreach($order->get_items() as $item){
                $product_id = $item['product_id'];
                $product = new WC_Product($product_id);
                if (!$product->managing_stock()){
                    continue;
                }
                $quantity = $product->get_stock_quantity();
                if ($item['qty'] == $quantity){
                    // update out of stock
                    self::on_product_update($product_id, 'update');
                }    
            }
        }
    } 
    
    public function on_product_update($post_id, $action){
        if (!self::is_post_valid($post_id)){
            return;
        }
        $products_list = get_option('cron_product_list');
        $timestamp = wp_next_scheduled( 'instantsearchplus_cron_request_event' );
        
        if(get_option('wcis_timeframe')){
            $timeframe = get_option('wcis_timeframe');
        } else {
            $timeframe = self::CRON_EXECUTION_TIME;
        }
         
        if ($timestamp != false){	// event already scheduled
            if ($products_list){	// if there is at least one product in the list
                // checking time-stamp diff (current time - first product's time-stamp)
                $delta = time() - $products_list[0]['time_stamp'];
                
                if (($delta > ($timeframe + self::CRON_THRESHOLD_TIME)) && 
                        (!get_option('cron_in_progress') || (time() - intval(get_option('cron_in_progress')) >= self::CRON_EXECUTION_TIME_RETRY))){
                    wp_clear_scheduled_hook( 'instantsearchplus_cron_request_event' ); 	// removing task from cron
                    self::execute_update_request();										// executing request for all waiting products
                    // reschedule current product to be executed by cron
                    wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
                }                
            } else {
                wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
                $err_msg = "event scheduled to Cron but product's list is empty";
                self::send_error_report($err_msg);
                
                wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
            }
            self::insert_element_to_cron_list($post_id, $action, self::ELEMENT_TYPE_PRODUCT);
        } else {
            // $timestamp == false - event not scheduled
            if ($products_list){
                // no event in cron, but the cron's list has products -> update all products that are in the list
                if (!get_option('cron_in_progress') || (time() - intval(get_option('cron_in_progress')) >= self::CRON_EXECUTION_TIME_RETRY)){
                    // if cron is currently not in progress -> update all products that are in the list
                    self::execute_update_request();
                    wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
                }
                // add current product to the list and schedule cron event
                self::insert_element_to_cron_list($post_id, $action, self::ELEMENT_TYPE_PRODUCT);
                
                 
            } else {
                // updating product's list and activating cron event
                self::insert_element_to_cron_list($post_id, $action, self::ELEMENT_TYPE_PRODUCT);
                wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
            
            }
        }
        
        /*
         *     $cat->count is the number of products that are under this ($cat) category.
         *     if there is only one product under $cat (meaning only this product) then update category -
         *         catches category change from active -> not active
         */
        foreach (wp_get_post_terms($post_id, 'product_cat') as $cat){
            if ($cat->count == 1){
                self::insert_element_to_cron_list($cat->term_id, 'edit', self::ELEMENT_TYPE_CATEGORY);
            }
        }
    }
    
    // element can be either product or category
    function insert_element_to_cron_list($element_id, $action, $type_of_element = self::ELEMENT_TYPE_PRODUCT){
    	if ($type_of_element == self::ELEMENT_TYPE_CATEGORY){
    		$elements_list = get_option('cron_category_list');
    	} else { 
    		$elements_list = get_option('cron_product_list');
    	} 
    	
    	if ($elements_list){	// the list already has products
    		$is_unique = true;
    		foreach ($elements_list as $p){
    			if ($element_id == $p['element_id'] && $action == $p['action']){
    				$is_unique = false;
    				break;
    			}
    		}
    		if ($is_unique){
    			$element_node = array(
    					'element_id' 	=> $element_id,
    					'action' 		=> $action,
    					'time_stamp' 	=> time()
    			);
    			array_push($elements_list, $element_node);
    			if ($type_of_element == self::ELEMENT_TYPE_CATEGORY){
    				self::isp_update_option('cron_category_list', $elements_list);
    			} else {
    				self::isp_update_option('cron_product_list', $elements_list);
    			}

    		}
    	} else {	// first product in the list
    		$elements_list = array(0 =>
    				array(
    						'element_id' 	=> $element_id,
    						'action' 		=> $action,
    						'time_stamp' 	=> time()
    				)
    		);
    	    if ($type_of_element == self::ELEMENT_TYPE_CATEGORY){
    			self::isp_update_option('cron_category_list', $elements_list);
    		} else {
    			self::isp_update_option('cron_product_list', $elements_list);
    		};
    	}
    }

    private function schedule_sale_price_update($post_id, $sale_price_dates = null){
        if ($sale_price_dates != null && $sale_price_dates < time())
            return;
        $product_list_by_date = get_option('cron_update_product_list_by_date');
        if ($sale_price_dates == null && !$product_list_by_date)
            return;
        if ($sale_price_dates != null){
            if ($product_list_by_date){
                $element_node = array(
                        'id' 	     => $post_id,
                        'time_stamp' => $sale_price_dates
                );
                array_push($product_list_by_date, $element_node);
        
            } else{
                $product_list_by_date = array(0 =>
                    				array(
                    				        'id' 	       => $post_id,
                    				        'time_stamp'   => $sale_price_dates
                    				)
                );
            } 
        } else { // removing all post's scheduled updates
            if ($product_list_by_date){
                foreach ($product_list_by_date as $key => $value){
                    if ($value['id'] == $post_id){
                        unset($product_list_by_date[$key]);
                    }
                }
            }
        }
        self::isp_update_option('cron_update_product_list_by_date', $product_list_by_date);
    }
    
    private function send_products_batch($products, $is_products_install_batch = true, $is_retry = false){
    	$total_products 				= $products['total_products'];
    	$total_pages 					= $products['total_pages'];
    	$product_chunks 				= $products['products'];   	
    	
    	if ($total_products == 0) {
    		$batch_number = 0;
    	} else {
    		$batch_number = $products['current_page'];
    	}
    	
    	$json_products = json_encode($product_chunks);

		$url = self::SERVER_URL . 'wc_install_products';
		
		$args = array (
				'body' => array (
						'site' => get_option('siteurl'), 
						'site_id' => get_option('wcis_site_id'), 
						'store_id' => get_current_blog_id(),
						'products' => $json_products,
						'total_batches' => $total_pages,
						'wcis_batch_size' => get_option( 'wcis_batch_size' ),
						'authentication_key' => get_option( 'authentication_key' ),
						'total_products' => $total_products,
						'batch_number' => $batch_number,
						'is_products_install_batch' => $is_products_install_batch,
				        'version' => self::VERSION
						
				),
				'timeout' => 15 
		);
		
    	$resp = wp_remote_post( $url, $args );
    	
    	if (is_wp_error($resp) || $resp['response']['code'] != 200){
    		$err_msg = "send_products_batch request failed batch: " . $batch_number;  
    		self::send_error_report($err_msg);
    		if (!$is_retry){
    			self::send_products_batch($products, $is_products_install_batch, true);
    		}
    	} else {
    	    $response_json = json_decode($resp['body'], true);
    	    if(isset($response_json['error'])){
    	        self::isp_update_option('wcis_site_notification', $response_json['error']);
    	    } else {
    	        self::isp_delete_option('wcis_site_notification');
    	    }
    	}
    }

	function send_categories_as_batch($categorys_list = null){
		$category_array = array();
		if ($categorys_list != null){
			foreach($categorys_list as $key => $element){
				if ($element['action'] == 'delete'){
					$category = array('category_id' => $element['element_id']);
				} else {
					$category = self::get_category_by_id($element['element_id']);
				}
				unset($categorys_list[$key]);
				if ($category == null){
					continue;
				}

				$category['action'] = $element['action'];
				$category_array[] = $category;
			}
			self::isp_update_option('cron_category_list', $categorys_list);
		} else {    // fetch all categories
			$args = array(
				'orderby'    => 'count',
				'hide_empty' => 1,  // sending empty categories!
				'fields'     => 'ids',
			);
			//add wpml support
			$product_category_ids = array();
			if (function_exists('icl_get_languages')) {
				$langs = icl_get_languages('skip_missing=0&orderby=KEY&order=DIR&link_empty_to=str');
				foreach($langs as $lang) {
					do_action( 'wpml_switch_language', $lang['language_code']);
					$product_category_ids = get_terms( 'product_cat', $args );
					if (!empty($product_category_ids) && !is_wp_error($product_category_ids) && count($product_category_ids) > 0) {
						foreach($product_category_ids as $key => $category_id){

							if (!self::is_post_valid($category_id, 'category')){
								continue;
							}
							$category = self::get_category_by_id($category_id);

							if ($category == null){
								continue;
							}

							$category['action'] = 'edit';
							$category_array[] = $category;
						}
					}
				}
			} else {
				$product_category_ids = get_terms( 'product_cat', $args );

				if (!empty($product_category_ids) && !is_wp_error($product_category_ids) && count($product_category_ids) > 0) {
					foreach($product_category_ids as $key => $category_id) {

						if (!self::is_post_valid($category_id, 'category')) {
							continue;
						}
						$category = self::get_category_by_id($category_id);

						if ($category == null) {
							continue;
						}

						$category['action'] = 'edit';
						$category_array[] = $category;
					}
				}
			}

		}

		if ($category_array){       // if there are categories to send
			$url = self::SERVER_URL . 'wc_update_categories';


			$args = array (
					'body' => array (
							'site' => get_option('siteurl'), 
							'site_id' => get_option('wcis_site_id'), 
							'store_id' => get_current_blog_id(),
							'categories' => json_encode( $category_array ),
							'authentication_key' => get_option( 'authentication_key' ) 
					),
					'timeout' => 10 
			);
			 
            $resp = wp_remote_post( $url, $args );
            
            if (is_wp_error($resp) || $resp['response']['code'] != 200){	// != 200
                $err_msg = "ERROR!!! update category request failed (response != 200)";
                self::send_error_report($err_msg);   
            }
        }
    }
    
    const CRON_SEND_BATCHES_UNREACHABLE_INTERVAL = 10;
    
    function send_batches_if_unreachable($batch_num){  
        $err_msg = "site: " . get_option('siteurl') . " unreachable, sending batches...";
        self::send_error_report($err_msg);
   
        $loop = self::query_products(get_current_blog_id(), $batch_num); 
        $total_pages = $loop->max_num_pages;	// total number of batches

        $max_batch = get_option('max_num_of_batches') + $batch_num;
    	if($max_batch < $total_pages) {
    		$min = $max_batch;
    	} else {
    		$min = $total_pages;
    	}

        while ($min >= $batch_num){
            if (get_option('cron_send_batches_disable')){
                return;
            }
            self::push_wc_batch($batch_num, get_current_blog_id()); 
            $batch_num++;
        }
       
        if($max_batch < $total_pages) {
            
        	wp_schedule_single_event(time() + self::CRON_SEND_BATCHES_UNREACHABLE_INTERVAL,
        			'instantsearchplus_send_batches_if_unreachable',
        			array($batch_num));
        }
 
    }
    
    private function send_product_update($post_id, $action)
    {
        $product = self::get_product_from_post($post_id);
        $product_update = array('topic'=>$action, 'product'=>$product);
        $json_product_update = json_encode($product_update); 
        $url = self::SERVER_URL . 'wc_update_products';
        
        $out_of_sync = $post_id;
        
		$args = array(
				'body' => array(
						'site' => get_option('siteurl'), 
						'site_id' => get_option('wcis_site_id'), 
						'store_id' => get_current_blog_id(),
						'product_update' => $json_product_update,
						'authentication_key' => get_option( 'authentication_key' ),
				        'version' => self::VERSION
				),
				'timeout' => 15 
		);
		
        
        $resp = wp_remote_post( $url, $args );     

        if (is_wp_error($resp) || $resp['response']['code'] != 200){	// != 200    
        	$err_msg = "update product request failed (response != 200)"; 
			self::send_error_report($err_msg);    	       	
        	
        } else { 	// $resp['response']['code'] == 200
    	    $response_json = json_decode($resp['body'], true);
    	    if(isset($response_json['error'])){
    	        self::isp_update_option('wcis_site_notification', $response_json['error']);
    	    } else {
    	        self::isp_delete_option('wcis_site_notification');
    	    }
    	}      

    }


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( 'WCISPlugin', plugin_dir_path( dirname( __FILE__ ) ) . 'languages/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( 'WCISPlugin', FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/wcis_plugin.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
// 		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
        $script_url = 'https://acp-magento.appspot.com/js/acp-magento.js';
        $args = self::get_main_script_params();
        
        wp_enqueue_script( $this->plugin_slug . '-inject3', $script_url . '?' . $args, false);
        
		global $wp_query;
        if (isset($wp_query) && is_search() && !$this->fulltext_disabled){
        	$script_url = 'https://acp-magento.appspot.com/js/wcis-results.js';
	        wp_enqueue_script( $this->plugin_slug . '-fulltext', $script_url . '?' . self::get_full_text_script_params(), array('jquery'), self::VERSION );
	        
	        if (!$this->just_created_site && $this->facets_required == true){
	            $isp_facets_fields = array(); //$this->facets_narrow
	            $isp_facets_fields['facets'] = $this->facets;
	            if ($this->facets_narrow){
	                $isp_facets_fields['narrow'] = $this->facets_narrow;
	            }

	            wp_localize_script( $this->plugin_slug . '-fulltext', 'isp_facets_fields', $isp_facets_fields );
	        }
	        if (!$this->just_created_site && $this->stem_words){
	            wp_localize_script( $this->plugin_slug . '-fulltext', 'isp_stem_words', $this->stem_words );
	        }
        }
	}
	
	function add_async_defer_attributes($tag, $handle){
	    if ( $handle !== $this->plugin_slug . '-inject3' && $handle !== $this->plugin_slug . '-fulltext'){
	        return $tag;
	    }

	    if ($handle == $this->plugin_slug . '-inject3' && strpos($tag, 'acp-magento.js?mode=woocommerce&') === false && (strpos($tag, 'acp-magento.js') !== false)){
	        $tag = str_replace('acp-magento.js', 'acp-magento.js?' . self::get_main_script_params(), $tag);
	    }
	    if ($handle == $this->plugin_slug . '-fulltext' && strpos($tag, 'wcis-results.js?') === false && (strpos($tag, 'wcis-results.js') !== false)){
	        $tag = str_replace('wcis-results.js', 'wcis-results.js?' . self::get_full_text_script_params(), $tag);
	    }
	    return str_replace( ' src', ' data-cfasync="false" async src', $tag );
	}
	
	function is_woocommerce_installed_and_supported($version = '2.1'){
	    include_once (ABSPATH . 'wp-admin/includes/plugin.php');	        
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){      
	        if ( version_compare( WOOCOMMERCE_VERSION, $version, '>=' ) ){
	            return true;
	        }
	    }
	    return false;
	}
	
	function get_main_script_params(){
	    global $product;
	    $args = "mode=woocommerce&";
	    $args = $args . "UUID=" . get_option('wcis_site_id') ."&";
	    $args = $args . "store=" . get_current_blog_id() ."&";
	    
	    try{
    	    if (self::is_woocommerce_installed_and_supported() && function_exists('WC')){
    	        $user_session = WC()->session->get_session_cookie();   // $user_session = [$customer_id, $session_expiration, $session_expiring, $cookie_hash]
    	        if ($user_session && count($user_session) > 3){
        	        $args = $args . "st=" . $user_session[0] ."&";
        	        $args = $args . "cart_token=" . $user_session[3] ."&";
    	        }
    	    }
	    } catch (Exception $e) {}

	    if (is_admin_bar_showing()){
	        $is_admin_bar_showing = "is_admin_bar_showing=1&";
	    } else {
	        $is_admin_bar_showing = "is_admin_bar_showing=0&";
	    }
	    $args .= $is_admin_bar_showing;
	    
	    if (is_user_logged_in()){
	        $args .= 'is_user_logged_in=1&';
	    } else {
	        $args .= 'is_user_logged_in=0&';
	    }
	    
	    if ($this->products_per_page){
	        $products_per_page = $this->products_per_page;
	    } else {
	        $products_per_page = get_option('posts_per_page');
	    }
	    $args .= "products_per_page=" . (string)$products_per_page . "&";
	    if ($product){
	        $args .= 'product_url=' . get_permalink() .'&';
	        $args .= 'product_id=' . get_the_ID() .'&';
	    }
	    
	    $options = get_option( 'wcis_general_settings' );
	    if ($options && array_key_exists('serp_page_id', $options) && array_key_exists('is_serp_enabled', $options) && $options['is_serp_enabled']){
	        $args .= 'serp_path=' . esc_url(str_replace(home_url(), "", get_permalink($options['serp_page_id'])));
	    }
	        
	    return $args;
	}
	
	function get_full_text_script_params(){
	    if (is_admin_bar_showing()){
	        $is_admin_bar_showing = "is_admin_bar_showing=1&";
	    } else {
	        $is_admin_bar_showing = "is_admin_bar_showing=0&";
	    }
	    $args = $is_admin_bar_showing;
	     
	    if ($this->just_created_site){
	        $args .= 'just_created_site=true&';
	    }else if ($this->wcis_did_you_mean_fields != null && !empty($this->wcis_did_you_mean_fields)){
	        // did you mean injection
	        $args .= 'did_you_mean_enabled=true&';
	    
	        $did_you_mean_fields = $this->wcis_did_you_mean_fields;
	        if (array_key_exists('alternative_terms', $did_you_mean_fields)){
	            $alternative_terms_arr = $did_you_mean_fields['alternative_terms'];
	             
	            $did_you_mean_patams = '';
	            for ($i = 0; $i < count($alternative_terms_arr); $i++){
	                $did_you_mean_patams .=  'did_you_mean_term' . (string)$i . '=' . urlencode($alternative_terms_arr[$i]) . '&';
	            }
	             
	            if ($did_you_mean_patams != ''){
	                $args .= $did_you_mean_patams . '&';
	            }
	        }
	        if (array_key_exists('original_query', $did_you_mean_fields) && array_key_exists('fixed_query', $did_you_mean_fields)){
	            $args .= 'original_query=' . urlencode($did_you_mean_fields['original_query']) . '&';
	            $args .= 'fixed_query=' . urlencode($did_you_mean_fields['fixed_query']) . '&';
	        } else if (array_key_exists('original_query', $did_you_mean_fields)){
	            $args .= 'original_query=' . urlencode($did_you_mean_fields['original_query']) . '&';
	        }
	        // clearing did you mean parameters
	        $this->wcis_did_you_mean_fields = null;
	    } else if ($this->wcis_search_query != null){
	        $args .= 'original_query=' . urlencode($this->wcis_search_query) . '&';
	        $this->wcis_search_query = null;
	    }
	     
	    if (!$this->just_created_site && $this->facets_required == true){
	        $args .= 'facets_required=1&';
	    }
	    if (!$this->just_created_site && $this->facets_completed == true){
	        $args .= 'facets_completed=1&';
	    } else if (!$this->just_created_site && !$this->facets_completed){
	        $args .= 'facets_completed=0&';
	    }
	    return $args;
	}
	
	function filter_instantsearchplus_request($vars){
		$vars[] = 'instantsearchplus';
		$vars[] = 'instantsearchplus_parameter';
		$vars[] = 'instantsearchplus_second_parameter'; 
		return $vars;
	}
	
	function process_instantsearchplus_request($req){
		if (array_key_exists('instantsearchplus', $req->query_vars)){
			if ($req->query_vars['instantsearchplus'] == 'version'){
				if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
					$woocommerce_exists = true;
				} else { 
					$woocommerce_exists = false;
				}
				try {
					if ( ! function_exists( 'get_plugins' ) ){
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					}
					$plugin_folder = get_plugins( '/' . 'woocommerce' );
					$plugin_file = 'woocommerce.php';
					if ( isset( $plugin_folder[$plugin_file]['Version'] ) ){
						$wooVer = $plugin_folder[$plugin_file]['Version'];
					} else {
						$wooVer = 'NULL';
					}
				} catch (Exception $e) {
					$wooVer = 'Error - could not get WooCommerce version';
				}
				
				if ( function_exists( 'is_multisite' ) && is_multisite() ){
					$is_multisite = "multisite";
				} else { 
					$is_multisite = "not multisite";
				}
				$server_endpoing = get_option('isp_server_endpoint');
				if (!$server_endpoing){
				    $server_endpoing = self::SERVER_URL;
				}
				
				$options = get_option( 'wcis_general_settings' );
				$is_premium_serp_enabled = false;
				$serp_path = '';
				if ($options && array_key_exists('serp_page_id', $options) && array_key_exists('is_serp_enabled', $options) && $options['is_serp_enabled']){
				    $is_premium_serp_enabled = true;
				    $serp_path = esc_url(str_replace(home_url(), "", get_permalink($options['serp_page_id'])));
				}
				    
				$response = array(
						'wordpress_version' 	=> get_bloginfo('version'),
						'woocommerce_version' 	=> $wooVer,
						'extension_version'		=> self::VERSION,
						'site_id' 				=> get_option('wcis_site_id'),
						'email'					=> get_option('admin_email'),
				        'site_url'              => get_option('siteurl'),
						'num_of_products'		=> wp_count_posts('product')->publish,
						'store_id'				=> get_current_blog_id(),
						'req_status'			=> 'OK',
						'woocommerce_exists'	=> $woocommerce_exists,
						'is_multisite'			=> $is_multisite,
				        'server_endpoint'       => $server_endpoing,
				        'WP_HTTP_BLOCK_EXTERNAL'=> defined('WP_HTTP_BLOCK_EXTERNAL') ? WP_HTTP_BLOCK_EXTERNAL : false,     # in defined - block external requests
				        'premium_serp_enabled'  => $is_premium_serp_enabled,
				        'serp_path'             => $serp_path,
				        'wcis_serp_page_name'   => get_option('wcis_serp_page_name'),
				        'batch_size'            => get_option('wcis_batch_size'),
				        'category_takeover'     => get_option('wcis_enable_rewrite_links')
				);
				exit(json_encode($response));
				
			} elseif ($req->query_vars['instantsearchplus'] == 'sync'){ 
				$additional_fetch_info = self::push_wc_products(false);
				if($additional_fetch_info != null) {
					$additional_fetch_array[] = self::get_additional_fetch_args($additional_fetch_info, false);
					$additional_fetch_array_encoded = json_encode($additional_fetch_array);
					self::request_additional_fetch($additional_fetch_array_encoded);
					unset($additional_fetch_array);
				}		
				
				status_header(200);
				exit();		
			} elseif ($req->query_vars['instantsearchplus'] == 'on_demand_sync'){ 
			    $err_msg = "on_demand_sync has been executed";
			    self::send_error_report($err_msg);
				self::execute_update_request();
				exit();		
			} elseif ($req->query_vars['instantsearchplus'] == 'category_sync'){ 
				$is_store_id_exists = true;
				$store_id = get_current_blog_id();
				try {
					$store_id = $req->query_vars['instantsearchplus_parameter'];
				} catch(Exception $e) {
					$is_store_id_exists = false;
				}
				
				if($is_store_id_exists) {
					self::switch_to_blog($store_id);
				}
			    self::send_categories_as_batch();
			    
			    if($is_store_id_exists) {
			    	self::restore_current_blog();
			    }
			    
			    status_header(200);
			    exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'get_batches'){
			    add_action('pre_get_posts', array($this, 'wcis_remove_all_filters'), 1);
				$batch_num = $req->query_vars['instantsearchplus_parameter'];
				$last_blog = get_current_blog_id();
				if (array_key_exists('instantsearchplus_second_parameter', $req->query_vars)){
				    $store_id = $req->query_vars['instantsearchplus_second_parameter'];
    				$last_blog = get_current_blog_id();
    				self::switch_to_blog($store_id);
				}

			    wp_clear_scheduled_hook( 'instantsearchplus_send_batches_if_unreachable' );
			    if (!get_option('cron_send_batches_disable')){
			        self::isp_update_option('cron_send_batches_disable', true);
			    }
			    
				self::push_wc_batch($batch_num, $store_id);
				if (array_key_exists('instantsearchplus_second_parameter', $req->query_vars)){
				    self::switch_to_blog($last_blog);
				}
				$response = array(
				        'batch_number'          => $batch_num,
				        'total_batches'         => ceil(wp_count_posts('product')->publish / get_option('wcis_batch_size')),
				        'batch_size'            => get_option('wcis_batch_size')
				);
				exit(json_encode($response));
			}elseif ($req->query_vars['instantsearchplus'] == 'get_product'){			    
				$identifier = $req->query_vars['instantsearchplus_parameter'];			
				self::send_product_update($identifier, 'update');
				print_r(self::get_product_from_post($identifier));
				status_header(200);
				exit();
			}elseif ($req->query_vars['instantsearchplus'] == 'show_product'){
			    $identifier = $req->query_vars['instantsearchplus_parameter'];
			    print_r(json_encode(self::get_product_from_post($identifier)));
			    status_header(200);
			    exit();
			}elseif ($req->query_vars['instantsearchplus'] == 'update_batch_size'){
			    $new_size = $req->query_vars['instantsearchplus_parameter'];
			    self::isp_update_option('wcis_batch_size', $new_size);
			    status_header(200);
			    exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'change_timeframe'){
				$timeframe = $req->query_vars['instantsearchplus_parameter'];
				self::update_timeframe($timeframe);
				exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'remove_admin_alerts'){
				self::isp_delete_option('wcis_site_alert');
				self::isp_delete_option('wcis_just_created_alert');
				self::isp_delete_option('wcis_site_notification');
				exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'check_admin_message'){
				self::check_for_alerts();
				exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'disable_highlight'){
				if (get_option('wcis_enable_highlight') == false){
					self::isp_update_option('wcis_enable_highlight', true);
				} else {
					self::isp_delete_option('wcis_enable_highlight');
				}
			} elseif ($req->query_vars['instantsearchplus'] == 'clear_cron'){
			    self::isp_delete_option('cron_product_list');
			    self::isp_delete_option('cron_category_list');
			    self::isp_delete_option('cron_in_progress');
			    wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
			    wp_clear_scheduled_hook('instantsearchplus_cron_request_event_backup');
			} elseif ($req->query_vars['instantsearchplus'] == 'get_additional_info'){
			    if (!array_key_exists('instantsearchplus_parameter', $req->query_vars) ||
			             $req->query_vars['instantsearchplus_parameter'] != get_option ('authentication_key')){
			        exit(json_encode(array(
		        	    'error' => 'permission error - wrong authentication_key'
			        )));
			    }
		        try{
					/**
					 * check that we are not using fskopen with 4.6
					 */
					$curl_disabled = strpos(ini_get('disable_functions'), 'curl_exec');
					$wordpress_vers = get_bloginfo('version');
					/**
					 * checks if there known or suspected conflicts
					 */
					$active_plugins = get_option('active_plugins');

					$conflicts_detected = false;

					$conflicting_plugins_installed = array();

					/**
					 * @var array an array of plugins we conflict with
					 */
					$conflicting_plugins = array('wpsolr-search-engine/wpsolr_search_engine.php');

					$conflicts_suspect_detected = false;

					$conflicting_suspected_plugins = array();

					foreach ($active_plugins as $pl) {
						if (in_array($pl, $conflicting_plugins)) {
							$conflicts_detected = true;

							$conflicting_plugins_installed[] = $pl;

						} else {
							if ($pl != 'instantsearch-for-woocommerce/instantsearch-for-woocommerce.php'
								&& strpos($pl, 'search') !== false
							) {
								$conflicts_suspect_detected = true;

								$conflicting_suspected_plugins[] = $pl;
							}
						}
					}

					$response = array(
						'curl_exec_disabled'    => $wordpress_vers == '4.6' && $curl_disabled,
                        'num_of_cron_tasks'     => count(_get_cron_array()),
	                    'DISABLE_WP_CRON'       => defined('DISABLE_WP_CRON') ? DISABLE_WP_CRON : false,
	                    'cron_array'            => _get_cron_array(),
	                    'WP_HTTP_BLOCK_EXTERNAL'=> defined('WP_HTTP_BLOCK_EXTERNAL') ? WP_HTTP_BLOCK_EXTERNAL : false,     # in defined - block external requests
						'conflicts_detected'            => $conflicts_detected,
						'conflict_plugins'              => $conflicting_plugins_installed,
						'conflicts_suspect_detected'    => $conflicts_suspect_detected,
						'conflict_plugins_suspected'    => $conflicting_suspected_plugins,
		            );
		        } catch (Exception $e){
		            exit(json_encode(array(
	                    'cron_disabled'         => defined('DISABLE_WP_CRON') ? DISABLE_WP_CRON : false,
	                    'exception'             => $e->getMessage()
		            )));
		        }
		        exit(json_encode($response));
			} elseif ($req->query_vars['instantsearchplus'] == 'add_taxonomy'){
			    if (array_key_exists('instantsearchplus_second_parameter', $req->query_vars) && 
			             $req->query_vars['instantsearchplus_second_parameter'] == get_option ('authentication_key')){
    			    $taxonomy = $req->query_vars['instantsearchplus_parameter'];
    			    $all_taxonomies = get_option('wcis_taxonomies');
    			    if (!is_array($all_taxonomies)){
    			        $all_taxonomies = array();
    			    }
    			    if (!in_array($taxonomy, $all_taxonomies)){
    			        $all_taxonomies[] = $taxonomy;
    			        self::isp_update_option('wcis_taxonomies', $all_taxonomies);
    			    }
			    } else {
			        print_r('failed to add taxonomy!');
			    }
			    exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'remove_taxonomy'){  
			    if (array_key_exists('instantsearchplus_second_parameter', $req->query_vars) &&
			             $req->query_vars['instantsearchplus_second_parameter'] == get_option ('authentication_key')){ 
			        self::isp_delete_option('wcis_taxonomies');
			    } else {
			        print_r('failed to remove taxonomy!');
			    }
			    exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'get_taxonomy'){   
			    print_r(get_option('wcis_taxonomies'));
			    exit();
			    
			} elseif ($req->query_vars['instantsearchplus'] == 'add_acf_field'){
			    if (array_key_exists('instantsearchplus_second_parameter', $req->query_vars) &&
			             $req->query_vars['instantsearchplus_second_parameter'] == get_option ('authentication_key')){
			        $acf_field = $req->query_vars['instantsearchplus_parameter'];
			        $wcis_acf_fields = get_option('wcis_acf_fields');
			        if (!is_array($wcis_acf_fields)){
			            $wcis_acf_fields = array();
			        }
			        if (!in_array($acf_field, $wcis_acf_fields)){
			            $wcis_acf_fields[] = $acf_field;
			            self::isp_update_option('wcis_acf_fields', $wcis_acf_fields);
			        }
			    } else {
			        print_r('failed to add advanced custom field!');
			    }
			    exit();
		    } elseif ($req->query_vars['instantsearchplus'] == 'remove_acf_field'){
		        if (array_key_exists('instantsearchplus_second_parameter', $req->query_vars) &&
			             $req->query_vars['instantsearchplus_second_parameter'] == get_option ('authentication_key')){
		            self::isp_delete_option('wcis_acf_fields');
		        } else {
		            print_r('failed to remove advanced custom field!');
		        }
		        exit();
	        } elseif ($req->query_vars['instantsearchplus'] == 'get_acf_field'){
	            print_r(get_option('wcis_acf_fields'));
	            exit();
            } elseif ($req->query_vars['instantsearchplus'] == 'enable_serp'){
                if (array_key_exists('instantsearchplus_parameter', $req->query_vars) &&
                        $req->query_vars['instantsearchplus_parameter'] == get_option ('authentication_key')){
                    $options = get_option( 'wcis_general_settings' );
                    if (!$options){
                        $serp_page = get_page_by_title(__('Search Results','WCISPlugin'));
                        if ($serp_page){  // the page exists!
                            $options = array('serp_page_id'     => $serp_page->ID,
                                             'serp_page'       => get_page_link($serp_page->ID),
                                             'is_serp_enabled'  => true
                            );
                        } else {
                            self::wcis_serp_activation();
                            $options = get_option( 'wcis_general_settings' );
                            $options['is_serp_enabled'] = true;
                            
                        }
                    } else {
                        $options['is_serp_enabled'] = true;
                    }
                    global $wp_rewrite;
                    
                    if ($wp_rewrite->using_permalinks()) {
						$serp_page = get_post($options['serp_page_id']);
						$serp_page_name = $serp_page->post_name;
						$rules = get_option('rewrite_rules');
						$rules = $this->set_serp_rewrite_rule($serp_page_name, $rules);
						self::isp_update_option('rewrite_rules', $rules);
					}

                    self::isp_update_option('wcis_general_settings', $options);
                } else {
					$response_js = array();
					$response_js['success'] = false;
					$response_js['msg'] = 'Not authenticated';

					echo json_encode($response_js);
				}
                exit();
            } elseif ($req->query_vars['instantsearchplus'] == 'disable_serp'){
                if (array_key_exists('instantsearchplus_parameter', $req->query_vars) &&
                        $req->query_vars['instantsearchplus_parameter'] == get_option('authentication_key')){
                    $options = get_option( 'wcis_general_settings' );
                    if (!$options){
                        $serp_page = get_page_by_title(__('Search Results','WCISPlugin'));
                        if ($serp_page){  // the page exists!
                            $options = array('serp_page_id'     => $serp_page->ID,
                                             'serp_page'       => get_page_link($serp_page->ID),
                                             'is_serp_enabled'  => false
                            );
                        } else {
                            self::wcis_serp_activation();
                            $options = get_option( 'wcis_general_settings' );
                            $options['is_serp_enabled'] = false;
                            
                        }
                    } else {
                        $options['is_serp_enabled'] = false;
                    }
                    self::isp_update_option('wcis_general_settings', $options);
                } else {
					$response_js = array();
					$response_js['success'] = false;
					$response_js['msg'] = 'Not authenticated';

					echo json_encode($response_js);
				}
                exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'update_robots'){
			    if (array_key_exists('instantsearchplus_parameter', $req->query_vars) &&
			             $req->query_vars['instantsearchplus_parameter'] == get_option('authentication_key')){
			        self::update_robots();
			    } else {
		            print_r('failed to update_robots');
		        }
			    exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'remove_robots'){
			    if (array_key_exists('instantsearchplus_parameter', $req->query_vars) &&
			    $req->query_vars['instantsearchplus_parameter'] == get_option('authentication_key')){
			        self::remove_robots();
			    } else {
		            print_r('failed to remove_robots');
		        }
			    exit();
		    } elseif ($req->query_vars['instantsearchplus'] == 'get_robots'){
                $robots_path = ABSPATH . 'robots.txt';
                if(file_exists($robots_path)){
                    $robots_file = file_get_contents($robots_path);
                    print_r($robots_file);
                }
		        exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'tmp'){
			    exit();
            } elseif ($req->query_vars['instantsearchplus'] == 'check_rewrite_links'){
                if (array_key_exists('instantsearchplus_parameter', $req->query_vars) &&
                    $req->query_vars['instantsearchplus_parameter'] == get_option('authentication_key')) {

					/**
					 * checks if website is valid for Smart Navigation
					 */
                    $gensets = get_option('wcis_general_settings');
                    global $wp_rewrite;
                    $response_js = array();

                    if (!$wp_rewrite->using_permalinks()) {

                        $response_js['success'] = false;
                        $response_js['msg'] = 'Your website is not using permalinks.';

                        echo json_encode($response_js);
                        exit();
                    }

                    if ($gensets == null || !isset($gensets['is_serp_enabled']) || $gensets['is_serp_enabled'] !== true) {

                        $response_js['success'] = false;
                        $response_js['msg'] = 'Serp is disabled.';

                        echo json_encode($response_js);
                        exit();
                    }

                    $response_js['success'] = true;
                    $response_js['msg'] = 'OK';

                    echo json_encode($response_js);
                } else {
						$response_js = array();
						$response_js['success'] = false;
						$response_js['msg'] = 'Not authenticated';

					echo json_encode($response_js);
                }
                exit();
            } elseif ($req->query_vars['instantsearchplus'] == 'enable_rewrite_links'){
                if (array_key_exists('instantsearchplus_parameter', $req->query_vars) &&
                    $req->query_vars['instantsearchplus_parameter'] == get_option('authentication_key')) {
					/**
					 * enables Smart Navigation if website uses permalinks
					 */
					$response_js = array();

					$gensets = get_option('wcis_general_settings');
					if ($gensets == null) {

						$response_js['success'] = false;
						$response_js['msg'] = 'Wcis general settings object is null';

						echo json_encode($response_js);
						exit();
					}
					
                    global $wp_rewrite;

                    if (!$wp_rewrite->using_permalinks()) {

                        $response_js['success'] = false;
                        $response_js['msg'] = 'Your website is not using permalinks.';

                        echo json_encode($response_js);
                        exit();
                    }

                    self::isp_update_option('wcis_enable_rewrite_links', 1);
                    $serp_page_id = $gensets['serp_page_id'];
					$serp_page_name = get_post_field('post_name', $serp_page_id);
                    self::isp_update_option('wcis_serp_page_name', $serp_page_name);
					$rules = get_option('rewrite_rules');
					$rules = $this->set_serp_rewrite_rule($serp_page_name, $rules);
					self::isp_update_option('rewrite_rules', $rules);

                    $response_js['success'] = true;
                    $response_js['msg'] = 'OK';
                    echo json_encode($response_js);
                } else {
					$response_js = array();
					$response_js['success'] = false;
					$response_js['msg'] = 'Not authenticated';

					echo json_encode($response_js);
				}
                exit();
            }  elseif ($req->query_vars['instantsearchplus'] == 'disable_rewrite_links'){
                if (array_key_exists('instantsearchplus_parameter', $req->query_vars) &&
                    $req->query_vars['instantsearchplus_parameter'] == get_option('authentication_key')) {

					/**
					 * delete Smart Navigation options and rule
					 */
                    self::isp_delete_option('wcis_enable_rewrite_links');
                    self::isp_delete_option('wcis_serp_page_name');

                    $response_js = array();
                    $response_js['success'] = true;
                    $response_js['msg'] = 'OK';
                    echo json_encode($response_js);
                } else {
					$response_js = array();
					$response_js['success'] = false;
					$response_js['msg'] = 'Not authenticated';

					echo json_encode($response_js);
				}
                exit();
			}
		}
	}
	
	// compatible to an old version | due to CRON request that is already scheduled
	function handle_cron_request(){
	    self::execute_update_request();
	}
	
	function execute_update_request(){
	    if (get_option('cron_in_progress') && (time() - intval(get_option('cron_in_progress')) < self::CRON_EXECUTION_TIME_RETRY)){
			// locked less than 10 minutes
	        return;
	    }
	    self::isp_update_option('cron_in_progress', time());
		try {
    		$products_list = get_option('cron_product_list');
    		$categorys_list = get_option('cron_category_list');
    		if (!$products_list && !$categorys_list){
    			wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
    			wp_clear_scheduled_hook('instantsearchplus_cron_request_event_backup');
    			self::isp_delete_option('cron_in_progress');
    			return;
    		}
    		// schedule retry in 2x10 minutes! (if this task will clean the products & categories lists than the next task won't schedule new CRON)
    		wp_schedule_single_event(time() + 2 * self::CRON_EXECUTION_TIME_RETRY, 'instantsearchplus_cron_request_event_backup');
    		
    		if ($products_list){
        		if (count($products_list) <= self::SINGLES_TO_BATCH_THRESHOLD){
        			foreach ($products_list as $key => $product_node){
        				self::send_product_update($product_node['element_id'], $product_node['action']);
        				unset($products_list[$key]);
        			}
        		} else {	// sending the products as a batch
        			self::send_cron_products_as_batch($products_list);
        			$products_list = get_option('cron_product_list');
        		}
        		
        		if (count($products_list) == 0){
        			self::isp_delete_option('cron_product_list');
        			wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
        			wp_clear_scheduled_hook('instantsearchplus_cron_request_event_backup');
        		} else {
        			$err_msg = "not managed to send " . count($products_list) . " products"; 
        			self::send_error_report($err_msg);
        		}
    		}
        
    		if ($categorys_list){
        		if (count($categorys_list) >= 0){    		    
        		    self::send_categories_as_batch($categorys_list);
        		    $categorys_list = get_option('cron_category_list');
        		}
        		if (count($categorys_list) == 0){
        		    self::isp_delete_option('cron_category_list');
        		    wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
        		    wp_clear_scheduled_hook('instantsearchplus_cron_request_event_backup');
        		} else {
        		    $err_msg = "not managed to send " . count($categorys_list) . " categories";
        		    self::send_error_report($err_msg);
        		}
    		}
		} catch (Exception $e) {
        	$err_msg = "execute_update_request exception raised msg: ". $e->getMessage() . ", from url: " . get_option('siteurl');
        	self::send_error_report($err_msg);
		}
		
		self::isp_delete_option('cron_in_progress');
	}

	
	function send_cron_products_as_batch($products_list){
		$batch_size = get_option( 'wcis_batch_size' );
		if (!$batch_size){
		    $batch_size = 50;
		    self::isp_update_option('wcis_batch_size', $batch_size);
		}
		$total_num_of_products = count($products_list);
		$total_num_of_batches = ceil($total_num_of_products / $batch_size);
		$iteration = 1;
		$product_array = array();
		
		foreach ($products_list as $key => $product_node){
			$product = self::get_product_from_post($product_node['element_id']);
			$product['topic'] = $product_node['action'];	// insert/update/remove
			$product_array[] = $product;
			
			if ((($iteration % $batch_size) == 0) || ($iteration == $total_num_of_products)){		// sending the batch
				$send_products = array(
						'total_pages' 				   	=> $total_num_of_batches,
						'total_products'				=> $total_num_of_products,
						'current_page' 					=> ceil($iteration / $batch_size),
						'products'						=> $product_array,
						'is_additional_fetch_required' 	=> false,
				);
				self::send_products_batch($send_products, false);
				
				// clearing array
				unset($product_array);
				$product_array = array();
				unset($send_products);
				unset($products_list[$key]);
				break;
			} else {
			    unset($products_list[$key]);
			}
			$iteration++;
		}
		self::isp_update_option('cron_product_list', $products_list);
		wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
		wp_clear_scheduled_hook('instantsearchplus_cron_request_event_backup');
		if ($iteration != $total_num_of_products || count($products_list) != 0){
		    wp_schedule_single_event(time() + 10, 'instantsearchplus_cron_request_event');
		}
	}
	
	function wcis_remove_all_filters(){
	    remove_all_filters('the_posts');
	}
	
	function send_error_report($str){
		$url = self::SERVER_URL . 'wc_error_log';
		
		$args = array(
				'body' => array( 'site' => get_option('siteurl'),
						'site_id' => get_option( 'wcis_site_id' ),
						'authentication_key' => get_option('authentication_key'),
						'err_desc' => $str),
		        'timeout' => 15,
		);
		
		$resp = wp_remote_post( $url, $args );
	}
	
	
	function send_logging_record($url_params_arr){
	}
	
	// FullText search Section
	function pre_get_posts_handler( $wp_query){

		if(isset($wp_query) && is_search() && $wp_query->is_main_query() && !is_admin()){            
			$query = $wp_query->query_vars;			
			$url_args = add_query_arg(null, null);
			
			$url_params = array();
			parse_str(add_query_arg(null, null), $url_params); 
			foreach ($url_params as $key => $value){
				if (strpos($key, '/?') === 0){ 		// first url parameter that starts with '/?'
					$new_key = str_replace('/?', '', $key);
					$url_params[$new_key] = $url_params[$key];
					unset($url_params[$key]);
				}
			}
			
			if (strpos($url_args, 'min_price=') !== false && strpos($url_args, 'max_price=') !== false){
				return self::on_fulltext_disable_query($wp_query);
			}

			if (strpos($url_args, 'orderby=') !== false){
				return self::on_fulltext_disable_query($wp_query);
			}
			
// 			$q = urldecode((isset($query['s'])) ? $query['s'] : get_search_query());
			$q = (isset($query['s'])) ? $query['s'] : get_search_query();
			// cleaning search query from '\'
			$q = str_replace('\\', '', $q);
			
			// initializing class's full text related fields
			$this->facets_required = null;
			$this->facets = null;
			$this->facets_completed = null;
			$this->facets_narrow = null;
			$this->stem_words = null;
			
			if (strpos($url_args, 'freshsearch=1') !== false){
			    $wp_query->query_vars['paged'] = 1;
			    $page_num = 1;
			} else {
			    $page_num = ($query['paged'] == 0) ? 1 : $query['paged'];
			}
			
			$post_type = (array_key_exists('post_type', $query)) ? $query['post_type'] : 'post';
			$server_endpoing = get_option('isp_server_endpoint');
			if (!$server_endpoing || strlen($server_endpoing) < 10){
			    $server_endpoing = self::SERVER_URL;
			}
			
			$url = $server_endpoing . 'wc_search';
			$params_array = array('s' 					   => get_option('siteurl'),
								  'h' 					   => get_option('siteurl'),	
								  'UUID' 				   => get_option( 'wcis_site_id' ),
							      'q'					   => urlencode($q),
								  'v' 					   => self::VERSION,
								  'store_id' 			   => get_current_blog_id(),
								  'p' 					   => $page_num,				// requested page number
								  'is_admin_view'		   => is_admin_bar_showing(),
					              'post_type'              => $post_type,
					              'facets_required'        => 1,
			                      'lang'                   => (defined('ICL_LANGUAGE_CODE')) ? ICL_LANGUAGE_CODE : ''
			);

			$params = '';
			foreach ($params_array as $key => $value){
			    $params .= $key . '=' . $value . '&';
			}
			$args = array(
					'timeout' => 15,
			);
			
			$narrow = null;
			if (strpos($url_args, '&narrow=') !== false){
			    $match = array();
			    $pattern = '/\&narrow=([^\&\#\/]*)/';
			    preg_match($pattern, $url_args, $match);
			    $narrow =  $match[1];
                $params .= 'narrow=' . $narrow;
			}

// 			$resp = wp_remote_post( $url, $args );
			$resp = wp_remote_get( $url .'?'.$params, $args );
			
			if (is_wp_error($resp) || $resp['response']['code'] != 200){			
				$err_msg = "/wc_search request failed";
				self::send_error_report($err_msg);
				return self::on_fulltext_disable_query($wp_query);				
			} else {
				$response_json = json_decode($resp['body'], true);
				
				if (array_key_exists('fulltext_disabled', $response_json)){
					return self::on_fulltext_disable_query($wp_query);
				} elseif (array_key_exists('just_created_site', $response_json)){
				    $this->just_created_site = true;
					$this->fulltext_disabled = false;
					return $wp_query;
				} elseif (array_key_exists('premium_serp_enabled', $response_json) && $response_json['premium_serp_enabled']){
				    $options = get_option( 'wcis_general_settings' );
				    if (!$options){
				        $serp_page = get_page_by_title(__('Search Results','WCISPlugin'));
				        if ($serp_page){  // the page exists!
				            $options = array('serp_page_id'    => $serp_page->ID,
        				                     'serp_page'       => get_page_link($serp_page->ID)
				            );
				            self::isp_update_option('wcis_general_settings', $options);
				        } else {
				            self::wcis_serp_activation();
				            $options = get_option( 'wcis_general_settings' );
				        }
				    }
				    if ($options){
    				    $wcis_serp_page_url = add_query_arg( 'q', urlencode($q), get_permalink($options['serp_page_id']) );
    				    wp_redirect( $wcis_serp_page_url);
    				    
    				    exit();
				    }
				}
				
                if ($this->just_created_site){
                    $this->just_created_site = false;
                } 

				$product_ids = array();
                
				$remove_wpml_wrong_lang = defined('ICL_LANGUAGE_CODE') && 
				                          function_exists('wpml_get_language_information') && 
				                          defined('ICL_SITEPRESS_VERSION');
				if (array_key_exists('id_list', $response_json)){
					foreach ($response_json['id_list'] as $product_id){
					    if ($remove_wpml_wrong_lang){
					        // WPML is installed, we don't want to display products that are in a different language from the loaded site
					        if (version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' )) {
					            $language_info = apply_filters( 'wpml_post_language_details', NULL, $product_id);
					        } else {
					            $language_info = wpml_get_language_information($product_id);
					        }
					        if ($language_info && is_array($language_info) && array_key_exists('language_code', $language_info)){    // WP_Error is not returned
					            if ($language_info['language_code'] != ICL_LANGUAGE_CODE){
					                continue;      # product with a wrong language, don't display this product in the results page
					            }
					        }    
					    }
					    $product_ids[] = $product_id;	 
					}
				} else {
					return self::on_fulltext_disable_query($wp_query);
				}
					
                $this->wcis_fulltext_ids = $product_ids;
                
				if (array_key_exists('total_results', $response_json) && $response_json['total_results'] != 0){
				    $this->wcis_total_results = $response_json['total_results'];
				} else {
				    $this->wcis_total_results = -1;
				}
				
				if (array_key_exists('products_per_page', $response_json) && $response_json['products_per_page'] != 0){
				    $this->products_per_page = $response_json['products_per_page'];
				} else {
				    $this->products_per_page = get_option('posts_per_page');
				}
				
				// did you mean section
// 				$wp_query->query_vars['s'] = urldecode($q);
				self::handle_did_you_mean_result($response_json);
				
				// facets section
				if (array_key_exists('facets', $response_json) && count($response_json['facets']) > 0){
				    $this->facets_required = true;
				    $this->facets = $response_json['facets'];
				    if (array_key_exists('facets_completed', $response_json)){
				        $this->facets_completed = $response_json['facets_completed'];
				    }
				    if (array_key_exists('narrow', $response_json)){
				        $this->facets_narrow = $response_json['narrow'];
				    }
				}
				
				// stem section
				if (array_key_exists('stem_words', $response_json) && count($response_json['stem_words']) > 0){
				    $this->stem_words = $response_json['stem_words'];
				}
				
				if (array_key_exists('server_endpoint', $response_json)){
				    if ($server_endpoing != $response_json['server_endpoint']){
				        self::isp_update_option('isp_server_endpoint', $response_json['server_endpoint']);
				    }
				}
			}
					
		} else {
			if (isset($wp_query) && is_search() && !is_admin()){
				return $wp_query;
			} else {
				return self::on_fulltext_disable_query($wp_query);
			}
		}
		// full text search enable
		$this->fulltext_disabled = false;
		return $wp_query;
	}
	
	public function on_fulltext_disable_query($wp_query){
	    if ($this->just_created_site){
	        $this->just_created_site = false;
	    }
		$this->fulltext_disabled = true;
		return $wp_query;
	}
	
	public function handle_did_you_mean_result($response_json){
		global $wp_query;
		$did_you_mean_fields = array();
		// if original search query is different from the result's query
		if (array_key_exists('results_for', $response_json)   && 
		        $response_json['results_for'] != null         && 
		        $response_json['results_for'] != ''           &&
				!self::did_you_mean_is_same_words($wp_query->query_vars['s'], 
												  $response_json['results_for'])){

			$did_you_mean_fields['original_query'] = $wp_query->query_vars['s'];
			$wp_query->query_vars['s'] = $response_json['results_for'];
			$did_you_mean_fields['fixed_query'] = $response_json['results_for'];
		} 
		if (array_key_exists('alternatives', $response_json) && (count($response_json['alternatives']) > 0)){
			$alternative_terms = array();
			foreach($response_json['alternatives'] as $term){
				$alternative_terms[] = $term;
			}
			$did_you_mean_fields['alternative_terms'] = $alternative_terms;
		}
		if (empty($did_you_mean_fields)){
		    $this->wcis_search_query = $wp_query->query_vars['s'];
		} else { 
// 		    if there are alternatives(did you mean) or search query not equals to "result for query" (typo)
            if (!array_key_exists('original_query', $did_you_mean_fields)){
                $did_you_mean_fields['original_query'] = $wp_query->query_vars['s'];
            }
		    $this->wcis_did_you_mean_fields = $did_you_mean_fields;
		}
	}
	
	public function did_you_mean_is_same_words($original, $fixed){
		if (($original == $fixed) ||
			($fixed == null)	  ||
			($fixed == '')		  || 		
			(str_replace('\\', '', $original) == str_replace('\\', '', $fixed)) ||
			(strcasecmp($original, $fixed) == 0)		// case-insensitive comparison
		){
			return true;
		}
		
		return false;
	}
	
	
	public function posts_search_handler($search){
		global $wp_query;
		if(isset($wp_query) && is_search() && !is_admin() && !$this->fulltext_disabled){
		    $search = ''; // disable WordPress search
		}
		return $search;	
	}
	
	function post_limits_handler($limit){
		global $wp_query;
		if(isset($wp_query) && is_search() && !$this->fulltext_disabled){
			$limit = 'LIMIT 0, ' . $this->products_per_page;
		}
		return $limit;
	}
	
	function the_posts_handler($posts){
		global $wp_query;

		if(isset($wp_query) && is_search() && !$this->fulltext_disabled && is_array($this->wcis_fulltext_ids)){
			$total_results = $this->wcis_total_results;	
			if ($total_results == -1){
				$total_results = 0;
			}
			
			$wp_query->found_posts = $total_results;			
			$wp_query->max_num_pages = ceil($total_results / $this->products_per_page);
// 			$wp_query->query_vars['post_type'] = 'product';
			$wp_query->query_vars['posts_per_page'] = $this->products_per_page;
			
			unset($posts);
			$posts = array();
			if ($total_results > 0){
			    foreach ($this->wcis_fulltext_ids as $product_id){
					$post = get_post($product_id);
					$posts[] = $post;
				}
			}
			unset($this->wcis_fulltext_ids);
			$this->wcis_fulltext_ids = null;
			$this->wcis_total_results = 0;
		}
		return $posts;
	}	
	
	
	// highlighting title/content/tags/excerpt according to the search terms
	function highlight_result_handler($current_text){
		global $wp_query;

		if(isset($wp_query) && is_search() && in_the_loop() && get_option('wcis_enable_highlight') && !$this->fulltext_disabled){
			$query = $wp_query->query_vars;
			
			$search_terms = preg_replace('!\s+!', ' ', $query['s']);
			$search_terms = explode(' ', $search_terms);
			
			foreach ($search_terms as $term){
				$current_text = preg_replace('/(' . $term . ')/i',
						'<span class="wcis_isp_marked_word">$1</span>',
						$current_text
				);
			}

			$current_text = '<span class="wcis_isp_text_content">' .  $current_text . '</span>';
		}
		
		return $current_text;
	}
	// FullText search Section end

	function admin_init_handler(){
		if ( isset($_GET['instantsearchplus']) && $_GET['instantsearchplus'] == 'remove_over_capacity_alert' ){
			self::isp_delete_option('wcis_site_alert');
			wp_redirect(remove_query_arg('instantsearchplus'), 301);
		} else if ( isset($_GET['instantsearchplus']) && $_GET['instantsearchplus'] == 'remove_just_created_alert' ){
			self::isp_delete_option('wcis_just_created_alert');
			wp_redirect(remove_query_arg('instantsearchplus'), 301);
		} else if ( isset($_GET['instantsearchplus']) && $_GET['instantsearchplus'] == 'remove_site_notification' ){
			self::isp_delete_option('wcis_site_notification');
			wp_redirect(remove_query_arg('instantsearchplus'), 301);
		}
	}

    function wcis_dismiss_notification(){
        self::isp_delete_option('wcis_site_notification');
        wp_die();
    }

    function wcis_dismiss_alert(){
        self::isp_delete_option('wcis_site_alert');
        wp_die();
    }

    function wcis_dismiss_just_created(){
        self::isp_delete_option('wcis_just_created_alert');
        wp_die();
    }

	// admin quota exceeded message
	function show_admin_message(){			
		if (is_admin()){
			$dashboard_url = self::DASHBOARD_URL.'wc_dashboard';
			$dashboard_url .= '?site_id=' . get_option( 'wcis_site_id' );
			$dashboard_url .= '&authentication_key=' . get_option('authentication_key');
			$dashboard_url .= '&new_tab=1';
			$dashboard_url .= '&v=' . WCISPlugin::VERSION;
			$dashboard_url .= '&store_id=' . get_current_blog_id(); 
			$dashboard_url .= '&site='. get_option('siteurl');
			
			if (get_option('wcis_just_created_alert')){
				echo '<div class="updated notice notice-success is-dismissible wcis_just_created"><p>';

				printf( __( '<b>InstantSearch+ for WooCommerce is installed :-)  </b><u><a href="%1$s" target="_blank"> Choose your settings </a></u>', 'WCISPlugin' ),
        				$dashboard_url,
        				add_query_arg('instantsearchplus', 'remove_just_created_alert'));
				
				echo "</p></div>
              <script>
				jQuery(document).on( 'click', '.wcis_just_created .notice-dismiss', function() {		
		 			var data = {		
					'action': 'wcis_dismiss_just_created'		
				};		
				jQuery.post(ajaxurl, data, function(response) {		
				});		
			});		
			</script>";				
				
			}
			if (get_option('wcis_site_alert')){
                $alert = get_option('wcis_site_alert');
                $msg = $alert['alerts'][0]['message'];

                echo '<div class="error notice-error notice is-dismissible wcis_dismiss_alert" ><p>';
                printf(__('<b> %1$s </b> | <b><a href="%2$s" target="_blank">Upgrade now</a></b> to enable back ', 'WCISPlugin'),
                    $msg,
                    $dashboard_url);
                echo "</p></div>
                 <script>
                    jQuery(document).on( 'click', '.wcis_dismiss_alert .notice-dismiss', function() {		
                        var data = {		
                        'action': 'wcis_dismiss_alert'		
                    };		
                    jQuery.post(ajaxurl, data, function(response) {		
                    });		
                });		
                </script>";

            } else if (get_option('wcis_site_notification')){
                $msg = get_option('wcis_site_notification');

                echo '<div class="error notice-error notice is-dismissible wcis_dismiss_notification"><p>';
                printf(__('<b>InstantSearch+ for WooCommerce</b>: %1$s', 'WCISPlugin'),
                    $msg);
                echo "</p></div>
                 <script>
                    jQuery(document).on( 'click', '.wcis_dismiss_notification .notice-dismiss', function() {		
                        var data = {		
                        'action': 'wcis_dismiss_notification'		
                    };		
                    jQuery.post(ajaxurl, data, function(response) {		
                    });		
                });		
                </script>";
			}
			
		}	
	}
	
	function check_for_alerts($is_from_page_load = false){
		$url = self::SERVER_URL . 'ext_info';
		$args = array(
			'body' => array('site_id' 				=> get_option( 'wcis_site_id' ),
							'version' 				=> self::VERSION,
					),
					'timeout' => 10,
		);	
		$resp = wp_remote_post( $url, $args );
		
		if (is_wp_error($resp) || $resp['response']['code'] != 200){	
			$err_msg = "check_for_alerts failed";
			self::send_error_report($err_msg); 
		} else {	
			$response_json = json_decode($resp['body'], true);
			if (!$is_from_page_load){
				if (!empty($response_json['alerts'])){
					self::isp_update_option('wcis_site_alert', $response_json);
				} else { 
					if (get_option('wcis_site_alert')){
						self::isp_delete_option('wcis_site_alert');
					}
				}
			}
				
			if (array_key_exists('timeframe', $response_json)){
				self::update_timeframe($response_json['timeframe']);
			}
		}
		
		if (get_option('cron_in_progress')){
		    self::isp_delete_option('cron_in_progress');
		}

		$options = get_option( 'wcis_general_settings' );
		global $wp_rewrite;
		
		if ($wp_rewrite->using_permalinks() && is_array($options) && $options['is_serp_enabled'] === true) {
			$serp_page = get_post($options['serp_page_id']);
			$serp_page_name = $serp_page->post_name;
			$rules = get_option('rewrite_rules');
			if ($serp_page_name != null && $serp_page_name != '') {
				$rules = $this->set_serp_rewrite_rule($serp_page_name, $rules);
				self::isp_update_option('rewrite_rules', $rules);
			}
		}
	}
	
	function update_timeframe($new_timeframe){	
		if (get_option('wcis_timeframe') && ($new_timeframe < get_option('wcis_timeframe'))){	// on subscription upgrade
			wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
			wp_clear_scheduled_hook('instantsearchplus_cron_request_event_backup');
			if (get_option('cron_product_list')){
				self::execute_update_request();	
			}		
		}
		self::isp_update_option('wcis_timeframe', $new_timeframe);
	}
	
	
	function add_woocommerce_integrations_handler($integrations){
		require_once( plugin_dir_path( __FILE__ ) . 'wcis_integration.php' );
		$integrations[] = 'WCISIntegration';
		return $integrations;
	}
	
	
	/* admin menu */
	public function add_plugin_admin_menu(){
	    $this->plugin_screen_hook_suffix = add_menu_page(
	            __( 'WooCommerce InstantSearch', $this->plugin_slug ),
	            __( 'InstantSearch+', $this->plugin_slug ),
	            'manage_options',
	            $this->plugin_slug,
	            '',
	            plugins_url( '/assets/images/instantsearchplus_logo_16x16.png', __FILE__ ),
	            56.15
	    );
	}
	
	public function add_plugin_admin_head(){
	    $wc_admin_url = self::DASHBOARD_URL.'wc_dashboard?site_id='. get_option( 'wcis_site_id' ) .
	     '&authentication_key=' . get_option('authentication_key') . '&new_tab=1&v=' .
	     WCISPlugin::VERSION .'&store_id='.get_current_blog_id()
	   	 .'&site='.get_option('siteurl'); 
		?>
		    <script type="text/javascript">
	    	    jQuery(document).ready( function($) {
	    	        jQuery('ul li#toplevel_page_WCISPlugin a').attr('target','_blank');
	    	        jQuery('ul li#toplevel_page_WCISPlugin a').attr("href", "<?php echo($wc_admin_url); ?>")
	    	    });
		    </script>
		<?php
	}

	function content_filter_shortcode($content){
//             $pattern = '/\[(.+?)[^\]]*\](.*?)\[\/\\1\]/s';
        $pattern = '/\[([A-Za-z0-9_-]+)[^\]]*\](.*?)\[\/\\1\]/s';
        while(preg_match($pattern, $content)){
            $content = preg_replace($pattern, '$2', $content);
        }
        
// 		global $shortcode_tags;
// 		if ($content != ''){
// 			foreach ($shortcode_tags as $shortcode_name => $shortcode_function){
// 				$content = preg_replace ('/\['. (string)$shortcode_name .'[^\]]*\](.*?)\[\/'. (string)$shortcode_name .'\]/', '$1', $content);
// 			}				 
// 		}

		// removing all content that looks like shortcode 
		$pattern = '/\[[^\]]+\]/s';
		while(preg_match($pattern, $content)){
			$content = preg_replace($pattern, '', $content);
		}
		
		return $content;
	}
	
	function content_filter_shortcode_with_content($content){
		$const_shortcode = array("php", "insert_php");
		if ($content != ''){
			foreach ($const_shortcode as $filter){
				$content = preg_replace ('/\['. (string)$filter .'[^\]]*\](.*?)\[\/'. (string)$filter .'\]/', '', $content);
			}
		}
		return $content;
	}
	
	
	function on_scheduled_sales(){
	    $product_list_by_date = get_option('cron_update_product_list_by_date');
	    if (!$product_list_by_date){
	        return;
	    }
	    
	    foreach ($product_list_by_date as $key => $value){
	        if ($value['time_stamp'] <= time()){
	            self::on_product_update($value['id'], 'update');
	            unset($product_list_by_date[$key]);
	        }
	    }
	    self::isp_update_option('cron_update_product_list_by_date', $product_list_by_date);
	}
	

	function get_isp_search_box_form( $attr ) {   
        $attr = shortcode_atts(
                array(
                        'inner_text'  => WCISPluginWidget::$default_search_box_fields['search_box_inner_text'],
                        'height'	  => WCISPluginWidget::$default_search_box_fields['search_box_height'],
                        'width'  	  => WCISPluginWidget::$default_search_box_fields['search_box_width'],
                        'text_size'   => WCISPluginWidget::$default_search_box_fields['search_box_text_size'],
                        'unit'        => WCISPluginWidget::$default_search_box_fields['search_box_units']
                ), $attr, 'isp_search_box' );
        
        if (!is_numeric($attr['width']) || $attr['width'] <= 0){
            $attr['width'] = WCISPluginWidget::$default_search_box_fields['search_box_width'];
        }
        if (!is_numeric($attr['height']) || $attr['height'] <= 0){
            $attr['height'] = WCISPluginWidget::$default_search_box_fields['search_box_height'];
        }
        if (!is_numeric($attr['text_size']) || $attr['text_size'] <= 0){
            $attr['text_size'] = WCISPluginWidget::$default_search_box_fields['search_box_text_size'];
        }
        
        $search_box_width_form = $attr['width'];
        $search_box_width_input = $attr['width'];
        if ($attr['unit'] == '%' || $attr['unit'] == '%%' || str_replace(' ', '', $attr['unit']) == '%' || $attr['unit'] == 'percent' || $attr['unit'] == 'Percent'){
            $attr['unit'] = '%';
            $search_box_width_input = '100';
        } else {
            $attr['unit'] = 'rem';
        }
    
        $action_url = esc_url(home_url('/'));
        $premium_serp_enabled = false;
        $options = get_option( 'wcis_general_settings' );
        if ($options && array_key_exists('serp_page_id', $options) && array_key_exists('is_serp_enabled', $options) && $options['is_serp_enabled']){
            $action_url = esc_url(str_replace(home_url(), "", get_permalink($options['serp_page_id'])));
            $premium_serp_enabled = true;
        }
        
	    $form = '<form class="isp_search_box_form" isp_src="shortcode" name="isp_search_box" action="' . $action_url . '" style="width:'.$search_box_width_form.$attr['unit'].'; float:none;">';
        $form .=    '<input type="text" name="s" class="isp_search_box_input" placeholder="'.$attr['inner_text'].'" style="outline: none; width:'.$search_box_width_input.$attr['unit'].'; height:'.$attr['height'].$attr['unit'].'; font-size:'.$attr['text_size'].'em;">';
        if (!$premium_serp_enabled){
            $form .=    '<input type="hidden" name="post_type" value="product">';
        }
        $form .=    '<input type="image" src="' . plugins_url('widget/assets/images/magnifying_glass.png', dirname(__FILE__) ) . '" class="isp_widget_btn" value="">';
        $form .= '</form>';

	    return $form;
	}
	
	function wcis_serp_results_shortcode(){
        $html = '<script>var __isp_fulltext_search_obj = { uuid: "'. get_option('wcis_site_id').'",
                                                           store_id: '.get_current_blog_id().'}
        </script>';
        
        $url = self::SERVER_URL . 'wc_load_search_page';
		$args = array (
			'body' => array(
				'uuid' =>           get_option( 'wcis_site_id' ),
				'store_id' =>       get_current_blog_id(),
				'isp_platform' =>   'woocommerce',
			),
			'timeout' => 15
		);
        $resp = wp_remote_post($url, $args);
        
        $did_request_failed = false;
        if (!$resp || is_wp_error($resp)){
            $did_request_failed = true;
        }
        if (!$did_request_failed){
            try{
                $response_json = json_decode($resp['body'], true);
            } catch (Exception $e) {
                $did_request_failed = true;
            }
        }
        
        if ($did_request_failed){
            $resp = wp_remote_post($url, $args);
            $response_json = json_decode($resp['body'], true);
        }
        
        return $html . $response_json['html'];
    }
	
    // cart/order webhooks
    public function on_add_to_cart() {
        self::wcis_cart_order_request('cart', NULL);
    }
    
    public function on_remove_from_cart() {
        self::wcis_cart_order_request('cart', NULL);
    }

    public function on_checkout_order_processed($order_id) {
        self::wcis_cart_order_request('success', $order_id);
    }

    public function wcis_cart_order_request($event, $order_id){
        if (!self::is_woocommerce_installed_and_supported() || !function_exists('WC')){
            return;
        }
        try{
//             $session_id = WC()->session->get_customer_id( );        // session_token
            $user_session = WC()->session->get_session_cookie( );   // $user_session = [$customer_id, $session_expiration, $session_expiring, $cookie_hash]
            	
            $url = self::SERVER_URL . 'wc_webhook';
            $currency = get_woocommerce_currency();
            $cart_product = array();
            
            if ($event == 'success'){
                $order = new WC_Order( $order_id );
                $order_array = $order->get_items();
                foreach($order_array as $product){
                    $cart_product[] = array('product_id' =>      $product['product_id'],
                                            'price' =>           $product['line_subtotal'] / $product['qty'], 
                                            'quantity' =>        $product['qty'], 
                                            'currency' =>        "$currency" );
                }
            } else {  // $event == 'cart'
                $cart_array = WC()->cart->cart_contents;
                foreach($cart_array as  $value) {
                    $cart_product[] = array('product_id' =>       $value['product_id'],
                                            'price' =>            $value['data']->price, 
                                            'quantity' =>         $value['quantity'], 
                                            'currency' =>         "$currency" );
                }
            }
            
            $cart_token = ($user_session && count($user_session) > 3) ? $user_session[3] : '';
            $session_id = ($user_session && count($user_session) > 0) ? $user_session[0] : '';
            $args = array (
                    'body' => array('event' =>          $event,
                                    'st' =>             $session_id,
                                    'UUID' =>           get_option( 'wcis_site_id' ),
                                    'store_id' =>       get_current_blog_id(),
                                    'key' =>            get_option ('authentication_key'),
                                    'cart_token' =>     $cart_token,
                                    'cart_product' =>   json_encode($cart_product),
                    ),
                    'timeout' => 15
            );
    
            $resp = wp_remote_post($url, $args);
            	
            if (is_wp_error($resp) || $resp['response']['code'] != 200){
                $err_msg = "/webhook request failed";
                self::send_error_report($err_msg);
            }
        } catch (Exception $e) {            
            $err_msg = "wcis_cart_order_request raised exception: " . $e->getMessage();
            self::send_error_report($err_msg);
        }
    }
     
    public function wcis_set_session() {
        if (self::is_woocommerce_installed_and_supported() && function_exists('WC') && !is_admin()){
            if(!WC()->session->has_session()){
                WC()->session->set_customer_session_cookie(true);
            }
        }
    }
    
    public function update_robots(){
        $robots_path = ABSPATH . 'robots.txt';
        $delimiter = '#';
        $startTag = '# START Instantsearch+';
        $endTag = '# END Instantsearch+';
        
        if(file_exists($robots_path)){
            $robots_file = file_get_contents($robots_path);
            $regex = $delimiter . preg_quote($startTag, $delimiter)
                    . '(.*?)'
                    . preg_quote($endTag, $delimiter)
                    . $delimiter
                    . 's';
        
            $default_content = "SITEMAP: https://woo.instantsearchplus.com/ext_sitemap?u=".get_option( 'wcis_site_id' );
            $robots_update = (preg_replace($regex, $startTag . "\n" . $default_content . "\n" . $endTag, $robots_file, -1, $count));
                        
            if(!$count){
                $robots_update = $robots_file . "\n" . $startTag . "\n" . $default_content . "\n" . $endTag;
            }
            
            file_put_contents($robots_path, ($robots_update));
        } else {
            $default_content = "SITEMAP: https://woo.instantsearchplus.com/ext_sitemap?u=".get_option( 'wcis_site_id' );
            $robots_update = $startTag . "\n" . $default_content . "\n" . $endTag;
            file_put_contents($robots_path, $robots_update);
        }
    }
    
    public function remove_robots(){
        $robots_path = ABSPATH . 'robots.txt';
        $delimiter = '#';
        $startTag = '# START Instantsearch+';
        $endTag = '# END Instantsearch+';
        
        if(!file_exists($robots_path)){
            return;
        }
        $robots_file = file_get_contents($robots_path);
        $regex = $delimiter . preg_quote($startTag, $delimiter)
                . '(.*?)'
                . preg_quote($endTag, $delimiter)
                . $delimiter
                . 's';
        $robots_update = preg_replace($regex, "", $robots_file, -1, $count);
        
        if ($count){
            file_put_contents($robots_path, ($robots_update));
        }
    }

	/**
	 * checks if serp page name (slug) was changed
	 * if so - updates Smart Navigation rule
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @return void
	 */
	private function check_serp_page_slug_changed($post)
	{
		$gensets = get_option('wcis_general_settings');
		$wcis_serp_page_name = get_option('wcis_serp_page_name');
		$serp_page_name = $post->post_name;

		if ($gensets != null && $post->ID == $gensets['serp_page_id'] && $wcis_serp_page_name != $serp_page_name) {
			$old_regex = $wcis_serp_page_name . '/(.?.+?)(?:/([0-9]+))?/?$';
			$rules = get_option('rewrite_rules');
			unset($rules[$old_regex]);
			$rules = $this->set_serp_rewrite_rule($serp_page_name, $rules);
			self::isp_update_option('rewrite_rules', $rules);
			self::isp_update_option('wcis_serp_page_name', $serp_page_name);
		}
	}

	/**
	 * set_serp_rewrite_rule sets serp page rewrite rule
	 * that enable searches like /serp_page_name/query
	 *
	 * @param $serp_page_name
	 * @param null $rules
	 *
	 * @return void
	 */
	private function set_serp_rewrite_rule($serp_page_name, $rules)
	{
		$regex = $serp_page_name.'/(.?.+?)(?:/([0-9]+))?/?$';
		$path = 'index.php?pagename='.$serp_page_name.'&q=$matches[1]&page=$matches[2]';

		if (!array_key_exists($regex, $rules)) {
			$rules = array($regex => $path) + $rules;
		}
		
		return $rules;
	}

}

?>