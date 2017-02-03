<?php
require_once('pages/global/settings.php');
require_once('functions/importer.php');
require_once 'functions/getbowtied-tgm/class-tgm-plugin-activation.php';
require_once 'functions/getbowtied-tgm/plugins.php';


if( ! class_exists( 'Getbowtied_Admin_Pages' ) ) {
	
	class Getbowtied_Admin_Pages {		
		
		protected $settings;

		// =============================================================================
		// Construct
		// =============================================================================

		function __construct() {	

			if (file_exists(get_template_directory().'/backend/run_once.php'))
			{
				require_once(get_template_directory().'/backend/run_once.php');
				unlink(get_template_directory().'/backend/run_once.php');
			}

			global $getbowtied_settings;

			$this->settings = $getbowtied_settings;

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			add_action( 'register_sidebar', 		array( $this, 'getbowtied_theme_admin_init' ) );

			add_action( 'admin_menu', 				array( $this, 'getbowtied_theme_admin_menu' ) );
			add_action( 'admin_menu', 				array( $this, 'getbowtied_theme_admin_submenu_registration' ) );
			add_action( 'admin_menu', 				array( $this, 'getbowtied_theme_admin_submenu_plugins' ) );
			add_action( 'admin_menu',				array( $this, 'getbowtied_theme_admin_submenu_demos' ) );
			//add_action( 'admin_menu', 				array( $this, 'getbowtied_theme_admin_submenu_help_center' ), 30 );			
			//add_action( 'admin_menu', 				array( $this, 'getbowtied_admin_menu' ), 99 );
			add_action( 'admin_menu', 				array( $this, 'getbowtied_edit_admin_menus' ) );
			
			add_action( 'admin_init', 				array( $this, 'getbowtied_theme_update') );
			
			add_action( 'after_switch_theme', 		array( $this, 'getbowtied_activation_redirect' ) );

			add_action( 'admin_notices', 			array( $this, 'getbowtied_admin_notices' ) );
			add_action( 'admin_notices', 			array( $this, 'update_notice' ) );
			
			add_action( 'admin_enqueue_scripts', 	array( $this, 'getbowtied_theme_admin_pages' ) );
			add_action( 'admin_enqueue_scripts', 	array( $this, 'getbowtied_theme_intercom' ) );

			if (!get_option("getbowtied_".THEME_SLUG."_license_expired"))
			{
				update_option("getbowtied_".THEME_SLUG."_license_expired", 0);
			}
		}

		function settings()
		{
			return $this->settings;
		}
		
		
		// =============================================================================
		// Menus
		// =============================================================================

		function getbowtied_theme_admin_menu() {			
			$getbowtied_menu_welcome = add_menu_page(
				getbowtied_theme_name(),
				getbowtied_theme_name(),
				'administrator',
				'getbowtied_theme',
				array( $this, 'getbowtied_theme_welcome_page' ),
				'',
				3
			);
		}

		function getbowtied_theme_admin_submenu_registration() {
			$getbowtied_submenu_welcome = add_submenu_page(
				'getbowtied_theme',
				__( 'Product Activation', 'getbowtied' ),
				__( 'Product Activation', 'getbowtied' ),
				'administrator',
				'getbowtied_theme_registration',
				array( $this, 'getbowtied_theme_registration_page' )
			);
		}

		function getbowtied_theme_admin_submenu_plugins() {	
			$getbowtied_submenu_plugins = add_submenu_page(
				'getbowtied_theme',
				__( 'Plugins', 'getbowtied' ),
				__( 'Plugins', 'getbowtied' ),
				'administrator',
				'getbowtied_theme_plugins',
				array( $this, 'getbowtied_theme_plugins_page' )
			);
		}

		function getbowtied_theme_admin_submenu_demos() {				
			$getbowtied_submenu_demos = add_submenu_page(
				'getbowtied_theme',
				__( 'Demo', 'getbowtied' ),
				__( 'Demo', 'getbowtied' ),
				'administrator',
				'getbowtied_theme_demos',
				array( $this, 'getbowtied_theme_demos_page' )
			);
		}

		function getbowtied_theme_admin_submenu_help_center() {					
			$getbowtied_submenu_help_center = add_submenu_page(
				'getbowtied_theme',
				__( 'Help Center', 'getbowtied' ),
				__( 'Help Center', 'getbowtied' ),
				'administrator',
				'getbowtied_theme_help_center',
				array( $this, 'getbowtied_theme_help_center_page' )
			);
		}

		function getbowtied_admin_menu() {						
			$getbowtied_welcome = add_submenu_page(
				'getbowtied_theme',
				__( 'Get Bowtied', 'getbowtied' ),
				__( 'Get Bowtied', 'getbowtied' ),
				'administrator',
				'getbowtied',
				array( $this, 'getbowtied_welcome_page' )
			);
		}


		// =============================================================================
		// Pages
		// =============================================================================

		function getbowtied_theme_welcome_page() {
			require_once( 'pages/welcome_theme.php' );
		}

		function getbowtied_theme_registration_page(){
			require_once( 'pages/registration.php' );
		}

		function getbowtied_theme_plugins_page(){
			require_once( 'pages/plugins.php' );
		}

		function getbowtied_theme_demos_page(){
			require_once( 'pages/demos.php' );
		}

		function getbowtied_theme_help_center_page(){
			require_once( 'pages/help-center.php' );
		}

		function getbowtied_welcome_page() {
			require_once( 'pages/welcome.php' );
		}


		// =============================================================================
		// Styles / Scripts
		// =============================================================================

		function getbowtied_theme_admin_pages() {
			wp_enqueue_style(	"getbowtied_theme_admin_css",				get_template_directory_uri() . "/backend/css/styles.css", 	false, null, "all" );
			wp_enqueue_script(	"getbowtied_theme_demos_js", 				get_template_directory_uri() . "/backend/js/scripts.js", 	array(), false, null );			
		}

		function getbowtied_theme_intercom() {

			if (get_option("getbowtied_".THEME_SLUG."_intercom_email"))
			{

				echo "
			
				<script>
				  window.intercomSettings = {
				    app_id: 'e6oj1xlj',
				    email: '".get_option("getbowtied_".THEME_SLUG."_intercom_email")."', // Email address
				    created_at: '".time()."' // Signup date as a Unix timestamp
				  };
				</script>
				<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==='function'){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/e6oj1xlj';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>
				
				";

			}

		}


		// =============================================================================
		// Plug-ins
		// =============================================================================

		function getbowtied_theme_plugin_links( $item ) 
		{
			$installed_plugins = get_plugins();

			$item['sanitized_plugin'] = $item['name'];

			// We have a repo plugin
			if ( ! $item['version'] ) {
				$item['version'] = TGM_Plugin_Activation::$instance->does_plugin_have_update( $item['slug'] );
			}

			/** We need to display the 'Install' hover link */
			if ( ! isset( $installed_plugins[$item['file_path']] ) ) {
				$actions = array(
					'install' => sprintf(
						'<a href="%1$s" class="button button-primary" title="Install %2$s">Install</a>',
						esc_url( wp_nonce_url(
							add_query_arg(
								array(
									'page'          => urlencode( TGM_Plugin_Activation::$instance->menu ),
									'plugin'        => urlencode( $item['slug'] ),
									'plugin_name'   => urlencode( $item['sanitized_plugin'] ),
									'plugin_source' => urlencode( $item['source'] ),
									'tgmpa-install' => 'install-plugin',
									'return_url'    => network_admin_url( 'admin.php?page=getbowtied_theme_plugins' )
								),
								TGM_Plugin_Activation::$instance->get_tgmpa_url()
							),
							'tgmpa-install',
							'tgmpa-nonce'
						) ),
						$item['sanitized_plugin']
					),
				);
			}
			/** We need to display the 'Activate' hover link */
			elseif ( is_plugin_inactive( $item['file_path'] ) ) {
				$actions = array(
					'activate' => sprintf(
						'<a href="%1$s" class="button button-primary" title="Activate %2$s">Activate</a>',
						esc_url( add_query_arg(
							array(
								'plugin'               => urlencode( $item['slug'] ),
								'plugin_name'          => urlencode( $item['sanitized_plugin'] ),
								'plugin_source'        => urlencode( $item['source'] ),
								'getbowtied-activate'       => 'activate-plugin',
								'getbowtied-activate-nonce' => wp_create_nonce( 'getbowtied-activate' ),
							),
							admin_url( 'admin.php?page=getbowtied_theme_plugins' )
						) ),
						$item['sanitized_plugin']
					),
				);
			}
			/** We need to display the 'Update' hover link */
			elseif ( version_compare( $installed_plugins[$item['file_path']]['Version'], $item['version'], '<' ) ) {
				$actions = array(
					'update' => sprintf(
						'<a href="%1$s" class="button button-primary" title="Install %2$s">Update</a>',
						wp_nonce_url(
							add_query_arg(
								array(
									'page'          => urlencode( TGM_Plugin_Activation::$instance->menu ),
									'plugin'        => urlencode( $item['slug'] ),

									'tgmpa-update'  => 'update-plugin',
									'plugin_source' => urlencode( $item['source'] ),
									'version'       => urlencode( $item['version'] ),
									'return_url'    => network_admin_url( 'admin.php?page=getbowtied_theme_plugins' )
								),
								TGM_Plugin_Activation::$instance->get_tgmpa_url()
							),
							'tgmpa-update',
							'tgmpa-nonce'
						),
						$item['sanitized_plugin']
					),
				);
			} elseif ( is_plugin_active( $item['file_path'] ) ) {
				$actions = array(
					'deactivate' => sprintf(
						'<a href="%1$s" class="button button-primary" title="Deactivate %2$s">Deactivate</a>',
						esc_url( add_query_arg(
							array(
								'plugin'                 => urlencode( $item['slug'] ),
								'plugin_name'            => urlencode( $item['sanitized_plugin'] ),
								'plugin_source'          => urlencode( $item['source'] ),
								'getbowtied-deactivate'       => 'deactivate-plugin',
								'getbowtied-deactivate-nonce' => wp_create_nonce( 'getbowtied-deactivate' ),
							),
							admin_url( 'admin.php?page=getbowtied_theme_plugins' )
						) ),
						$item['sanitized_plugin']
					),
				);
			}

			return $actions;
		}

		// =============================================================================
		// Theme Updater
		// =============================================================================

		function getbowtied_theme_update() 
		{
			global $wp_filesystem;

				if (get_option("getbowtied_".THEME_SLUG."_license") && (get_option("getbowtied_".THEME_SLUG."_license_expired") == 0))
				{
					$license_key = get_option("getbowtied_".THEME_SLUG."_license");
				}
				else
				{
					$license_key = '';
				}
				
				require_once( get_template_directory() . '/backend/functions/_class-updater.php' );
				
				$theme_update = new GetBowtiedUpdater( $license_key );
		
				// if (get_option("getbowtied_".THEME_SLUG."_license_expired") == 1 )
				// {
				// 	add_action( 'admin_notices', array(&$this, 'expired_notice') );
				// }
		}

		// function expired_notice() {
			
		// 	if ( ! isset($_COOKIE["notice_update_theme"]) || $_COOKIE["notice_update_theme"] != "1" ) {
		// 		echo '<div class="notice is-dismissible error getbowtied_admin_notices notice_update_theme">
		// 		<p>This site will no longer receive automatic theme updates. Theme\'s <a href="' . admin_url( 'admin.php?page=getbowtied_theme_registration' ) . '">Product Key</a> is no longer active on this domain.</p>
		// 		</div>';
		// 	}
		// }

		function getbowtied_admin_notices() {

			$remote_ver = get_option("getbowtied_".THEME_SLUG."_remote_ver") ? get_option("getbowtied_".THEME_SLUG."_remote_ver") : getbowtied_theme_version();
			$local_ver = getbowtied_theme_version();

			if(!version_compare($local_ver, $remote_ver, '<'))
		    {
				if ( (!get_option("getbowtied_".THEME_SLUG."_license") && ( get_option("getbowtied_".THEME_SLUG."_license_expired") == 0 ) )
					|| (get_option("getbowtied_".THEME_SLUG."_license") && ( get_option("getbowtied_".THEME_SLUG."_license_expired") == 1 )) ){
					
					if( function_exists('wp_get_theme') ) {
						$theme_name = '<strong>'. wp_get_theme() .'</strong>';
					}

					if ( ! isset($_COOKIE["notice_product_key"]) || $_COOKIE["notice_product_key"] != "1" ) {
						echo '<div class="notice is-dismissible error getbowtied_admin_notices notice_product_key">
						<p>' . $theme_name . ' - Enter your product key to start receiving automatic updates and support. Go to <a href="' . admin_url( 'admin.php?page=getbowtied_theme_registration' ) . '">Product Activation</a>.</p>
						</div>';
					}

				}
			}

		}

		function validate_license($license_key)
		{
			if (empty($license_key))
			{
				return FALSE;
			}
			else
			{
				// $api_url = "http://local.dev/dashboard/api/api_listener.php";
				$api_url = "http://my.getbowtied.com/api/api_listener.php";
				$theme = wp_get_theme();

				$args = array(
								'method' => 'POST',
								'timeout' => 30,
								'body' => array( 'l' => $license_key,  'd' => get_site_url(), 't' => THEME_NAME )
						);
				
				$response = wp_remote_post( $api_url, $args );

				if ( is_wp_error( $response ) ) {
				    $error_message = $response->get_error_message();
				    $request_msg = 'Something went wrong:'.$error_message.'. Please try again!';
				} else {
				  	$rsp = json_decode($response['body']);
				  	$request_msg = '';
				  	// print_r($rsp);
				  	// die();
				  	
				  	switch ($rsp->status)
				  	{
				  		case '0':
				  		$request_msg = 'Something went wrong. Please try again!';
				  		break;

				  		case '1':
				  		update_option("getbowtied_".THEME_SLUG."_license", $license_key);
				  		update_option("getbowtied_".THEME_SLUG."_license_expired", 0);
				  		if (!empty($rsp->email))
				  		{
				  			update_option("getbowtied_".THEME_SLUG."_intercom_email", $rsp->email);
				  		}
				  		break;

				  		case '2':
				  		$request_msg = 'The product key you entered is not valid.';
				  		break;

				  		case '3':
				  		$request_msg = '<strong>Site URL mismatch:</strong><br/>';
				  		$request_msg .= 'Your actual URL is: <strong>'.get_site_url().'</strong><br/>';
				  		$request_msg .= 'so please <a href="http://my.getbowtied.com" target="_blank">generate a new key</a> using this one.';
				  		break;

				  	}

				  	// echo '<h2>'.$request_msg.'</h2>';
				}

			 	return $request_msg;

			}
		}
		
		function update_notice()
		{
			$remote_ver = get_option("getbowtied_".THEME_SLUG."_remote_ver") ? get_option("getbowtied_".THEME_SLUG."_remote_ver") : getbowtied_theme_version();
			$local_ver = getbowtied_theme_version();

		    if(version_compare($local_ver, $remote_ver, '<'))
		    {
				if( function_exists('wp_get_theme') ) {
					$theme_name = '<strong>'. wp_get_theme(get_template()) .'</strong>';
				}

				if ( ( !get_option("getbowtied_".THEME_SLUG."_license") && ( get_option("getbowtied_".THEME_SLUG."_license_expired") == 0 ) )
				|| (get_option("getbowtied_".THEME_SLUG."_license") && ( get_option("getbowtied_".THEME_SLUG."_license_expired") == 1 )) ) {

					echo '<div class="notice is-dismissible error getbowtied_admin_notices">
					<p>There is an update available for the ' . $theme_name . ' theme. Go to <a href="' . admin_url( 'admin.php?page=getbowtied_theme_registration' ) . '">Product Activation</a> to enable theme updates.</p>
					</div>';

				}

				if ( get_option("getbowtied_".THEME_SLUG."_license") && ( get_option("getbowtied_".THEME_SLUG."_license_expired") == 0 ) ) {

				echo '<div class="notice is-dismissible error getbowtied_admin_notices">
				<p>There is an update available for the ' . $theme_name . ' theme. <a href="' . admin_url() . 'update-core.php">Update now</a>.</p>
				</div>';

				}
		    }
		}

		// =============================================================================
		// Admin Redirect
		// =============================================================================

		function getbowtied_activation_redirect(){
			if ( current_user_can( 'edit_theme_options' ) ) {
				header('Location:'.admin_url().'admin.php?page=getbowtied_theme');
			}
		}

		// =============================================================================
		// Admin Init
		// =============================================================================

		function getbowtied_theme_admin_init() {

			if ( isset( $_GET['getbowtied-deactivate'] ) && $_GET['getbowtied-deactivate'] == 'deactivate-plugin' ) {
				
				check_admin_referer( 'getbowtied-deactivate', 'getbowtied-deactivate-nonce' );

				$plugins = get_plugins();

				foreach ( $plugins as $plugin_name => $plugin ) {
					if ( $plugin['Name'] == $_GET['plugin_name'] ) {
							deactivate_plugins( $plugin_name );
					}
				}

			} 

			if ( isset( $_GET['getbowtied-activate'] ) && $_GET['getbowtied-activate'] == 'activate-plugin' ) {
				
				check_admin_referer( 'getbowtied-activate', 'getbowtied-activate-nonce' );

				$plugins = get_plugins();

				foreach ( $plugins as $plugin_name => $plugin ) {
					if ( $plugin['Name'] == $_GET['plugin_name'] ) {
						activate_plugin( $plugin_name );
					}
				}

			}

		}

		
		// =============================================================================
		// Edit Menus
		// =============================================================================

		function getbowtied_edit_admin_menus() {
			global $submenu;
			$submenu['getbowtied_theme'][0][0] = __( 'Welcome', 'getbowtied' );
		}

		
		// =============================================================================
		// Let to num
		// =============================================================================

		function let_to_num( $size ) {
			$l   = substr( $size, -1 );
			$ret = substr( $size, 0, -1 );
			switch ( strtoupper( $l ) ) {
				case 'P':
					$ret *= 1024;
				case 'T':
					$ret *= 1024;
				case 'G':
					$ret *= 1024;
				case 'M':
					$ret *= 1024;
				case 'K':
					$ret *= 1024;
			}
			return $ret;
		}
	}
	
	new Getbowtied_Admin_Pages;

}