<?php
/*
Plugin Name: UserPro - NULL24.NET
Plugin URI: http://codecanyon.net/user/DeluxeThemes/portfolio?ref=DeluxeThemes
Description: The ultimate user profiles and community plugin for WordPress.
Version: 4.7
Author: Deluxe Themes
Author URI: http://codecanyon.net/user/DeluxeThemes/portfolio?ref=DeluxeThemes
*/
define('userpro_url',plugin_dir_url(__FILE__ ));
define('userpro_path',plugin_dir_path(__FILE__ ));

	/* init */


//Start Yogesh added usermeta entry for search members
function userpro_add_userin_meta() {
	// Activation code here...
	global $wpdb;
	$usermetatable = $wpdb->prefix.'usermeta';
	$usertable = $wpdb->prefix.'users';
	$query = "UPDATE $usermetatable INNER JOIN $usertable ON $usermetatable.user_id = $usertable.ID
SET $usermetatable.meta_value = $usertable.display_name
WHERE  $usermetatable.user_id = $usertable.ID AND $usermetatable.meta_key = 'display_name'";
	$wpdb->query($query);
}
register_activation_hook( __FILE__, 'userpro_add_userin_meta' );

//End Yogesh usermeta entry for search members

	function userpro_init() {
		
		if(!isset($_SESSION))
		{
			session_start();
		}
		
		global $userpro;
		
		$result=get_option("userpro_invite_check");
		if(empty($result))
		{	
			
			$user_invite_template=userpro_get_option('userpro_invite_emails_template');
			$userpro_options = get_option('userpro');
			$userpro_options['userpro_invite_emails_template']=str_replace("inivitelink","invitelink",userpro_get_option('userpro_invite_emails_template'));
			update_option('userpro',$userpro_options);
			update_option("userpro_invite_check","1");
		}
		if(get_option('userpro_publish_page_link')=='')
		{	
			global $wpdb;
			$userpropost = $wpdb->base_prefix."posts";
			$query ="SELECT ID FROM $userpropost WHERE (post_content LIKE '%template=publish%')";
			$result=$wpdb->get_results($query);
			if(isset($result[0]->ID))
			update_option('userpro_publish_page_link',$result[0]->ID);
		}
		$userpro->do_uploads_dir();
		
		load_plugin_textdomain('userpro', false, dirname(plugin_basename(__FILE__)) . '/languages');
		
		/* include libs */
		if (!class_exists('UserProMailChimp')){
			require_once userpro_path . '/lib/mailchimp/MailChimp.php';
		}
		
	}
 
 

		add_action('init', 'userpro_init');
		

	/* functions */
		require_once userpro_path . "functions/_trial.php";
		require_once userpro_path . "functions/ajax.php";
		require_once userpro_path . "functions/api.php";
		require_once userpro_path . "functions/badge-functions.php";
		require_once userpro_path . "functions/common-functions.php";
		require_once userpro_path . "functions/custom-alerts.php";
		require_once userpro_path . "functions/defaults.php";
		require_once userpro_path . "functions/fields-filters.php";
		require_once userpro_path . "functions/fields-functions.php";
		require_once userpro_path . "functions/fields-hooks.php";
		require_once userpro_path . "functions/fields-setup.php";
		require_once userpro_path . "functions/global-actions.php";
		require_once userpro_path . "functions/buddypress.php";
		require_once userpro_path . "functions/hooks-actions.php";
		require_once userpro_path . "functions/hooks-filters.php";
		require_once userpro_path . "functions/icons-functions.php";
		require_once userpro_path . "functions/initial-setup.php";
		require_once userpro_path . "functions/mail-functions.php";
		require_once userpro_path . "functions/msg-functions.php";
		require_once userpro_path . "functions/security.php";
		require_once userpro_path . "functions/shortcode-extras.php";
		require_once userpro_path . "functions/invite_users_widgets.php";
		require_once userpro_path . "functions/shortcode-functions.php";
		require_once userpro_path . "functions/shortcode-main.php";
		require_once userpro_path . "functions/shortcode-private-content.php";
		require_once userpro_path . "functions/shortcode-social-connect.php";
		require_once userpro_path . "functions/social-connect.php";
		require_once userpro_path . "functions/template-redirects.php";
		require_once userpro_path . "functions/terms-agreement.php";
		require_once userpro_path . "functions/user-functions.php";

	/* administration */


	if (is_admin()){
		foreach (glob(userpro_path . 'admin/*.php') as $filename) { include $filename; }
	}
	
	/* updates */
	foreach (glob(userpro_path . 'updates/*.php') as $filename) { include $filename; }
	
	/* load addons */
	require_once userpro_path . 'addons/multiforms/index.php';
	require_once userpro_path . 'addons/badges/index.php';
	require_once userpro_path . 'addons/social/index.php';
	require_once userpro_path . 'addons/emd/index.php';
	require_once userpro_path . 'addons/redirects/index.php';
	require_once userpro_path . 'addons/requests/index.php';	
	
	require_once(userpro_path . "admin/class-up-updates-plugin.php");
	new WPUpdatesPluginUpdater_1146( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__) );
