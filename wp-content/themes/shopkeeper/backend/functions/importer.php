<?php

defined( 'ABSPATH' ) or die( 'You cannot access this script directly' );

// Don't resize images
function getbowtied_filter_image_sizes( $sizes ) {
	return array();
}

// Hook importer into admin init
add_action( 'wp_ajax_getbowtied_demo_importer', 'getbowtied_demo_importer' );
function getbowtied_demo_importer() {
	global $wpdb, $getbowtied_settings;

	// var_dump($_POST);
	// die();

	if ( current_user_can( 'manage_options' ) ) {
		
		if ( !defined('WP_LOAD_IMPORTERS') ) define('WP_LOAD_IMPORTERS', true); // we are loading importers

		if ( ! class_exists( 'WP_Importer' ) ) { // if main importer class doesn't exist
			include ABSPATH . 'wp-admin/includes/class-wp-importer.php';
		}



		if ( ! class_exists('WP_Import') ) { // if WP importer doesn't exist
			include get_template_directory() . '/backend/functions/wordpress-importer/wordpress-importer.php';
		}

		if ( class_exists( 'WP_Importer' ) && class_exists( 'WP_Import' ) ) { // check for main import class and wp import class

			$demo_type = $_POST['demo_type'];

			if (THEME_SLUG == 'the_retailer') {

				// reading settings
				$homepage_title = 'Home V1 - Ecommerce';
				$blog_title 	= 'Blog';

			} else if (THEME_SLUG == 'shopkeeper') {

				// reading settings
				$homepage_title = 'Home V1 — Full-screen Slider';
				$blog_title 	= 'FASHION REPORT';

			} else if (THEME_SLUG == 'mr_tailor') {

				switch($demo_type) {

					case 'default':

						// reading settings
						$homepage_title = 'Home V1 / About V1';
						$blog_title 	= 'Blog';

						break;

					case 'indie-store':

						// reading settings
						$homepage_title = 'Home Page';
						$blog_title 	= 'Blog';

						break;

					case 'startup':

						// reading settings
						$homepage_title = 'Home';
						$blog_title 	= 'Blog';

						break;
				
					default:

						// reading settings
						$homepage_title = 'Home';
						$blog_title 	= 'Blog';

				}

			} else {

			}

			$version = '';

			switch($demo_type) {

				case 'default':

					$version = '';

					break;

				case 'indie-store':

					$version = '_indie';

					break;

				case 'startup':

					$version = '_startup';

					break;
			
				default:

					$version = '';

			}

			add_filter('intermediate_image_sizes_advanced', 'getbowtied_filter_image_sizes');

			if (!is_dir(get_home_path().'/wp-content/uploads/demos/'))
			{
				mkdir(get_home_path().'/wp-content/uploads/demos/');
			}
			/* 
			** Download and save GZ file
			*/

			$theme_demo_xml_file_url = $getbowtied_settings['demo_xml_file_url'.$version];


			$theme_demo_xml_file = get_home_path().'/wp-content/uploads/demos/demo.gz';
			$remote = gzopen($theme_demo_xml_file_url, "rb");
			$home = fopen($theme_demo_xml_file, "w");

			while ($string = gzread($remote, 4096)) {
			    fwrite($home, $string, strlen($string));
			}

			gzclose($remote);
			fclose($home);

			/*
			** Download and save Theme Options .txt file
			*/

			$theme_options_file_url = $getbowtied_settings['options_file_url'.$version];
			$rsp = wp_remote_get($theme_options_file_url);
			$file = $rsp['body'];

			$theme_options_file = get_home_path().'/wp-content/uploads/demos/theme_options.txt';

			$fp = fopen($theme_options_file, "w");
			fwrite($fp, $file);
			fclose($fp);

			$importer = new WP_Import();
			$importer->fetch_attachments = true;
			ob_start();
			$importer->import($theme_demo_xml_file);
			ob_end_clean();

			if( class_exists('Woocommerce') ) {
				
				$woopages = array(
					'woocommerce_shop_page_id' => 'Shop',
					'woocommerce_cart_page_id' => 'Cart',
					'woocommerce_checkout_page_id' => 'Checkout',
					//'woocommerce_pay_page_id' => 'Checkout &#8594; Pay',
					//'woocommerce_thanks_page_id' => 'Order Received',
					'woocommerce_myaccount_page_id' => 'My Account',
					//'woocommerce_edit_address_page_id' => 'Edit My Address',
					//'woocommerce_view_order_page_id' => 'View Order',
					//'woocommerce_change_password_page_id' => 'Change Password',
					//'woocommerce_logout_page_id' => 'Logout',
					//'woocommerce_lost_password_page_id' => 'Lost Password'
				);
				
				foreach($woopages as $woo_page_name => $woo_page_title) {
					$woopage = get_page_by_title( $woo_page_title );
					if(isset( $woopage ) && $woopage->ID) {
						update_option($woo_page_name, $woopage->ID); // Front Page
					}
				}

				// We no longer need to install pages
				//delete_option( '_wc_needs_pages' );
				//delete_transient( '_wc_activation_redirect' );

				// Flush rules after install
				flush_rewrite_rules();
			}

			// Set imported menus to registered theme locations
			$locations = get_theme_mod( 'nav_menu_locations' ); // registered menu locations in theme
			$menus = wp_get_nav_menus(); // registered menus

			if($menus) {
				
				foreach($menus as $menu) { // assign menus to theme locations
					
					if (THEME_SLUG == 'the_retailer') {

						if( $demo_type == 'default' ) {
							
							if ( $menu->name == 'Main Navigation' ) 			$locations['primary'] 		= $menu->term_id;
							else if ( $menu->name == 'Secondary Navigation' ) 	$locations['secondary']		= $menu->term_id;

						}

					} else if (THEME_SLUG == 'shopkeeper') {

						if( $demo_type == 'default' ) {
							
							if ( $menu->name == 'Main Navigation' ) $locations['main-navigation'] = $menu->term_id;

						}

					} else if (THEME_SLUG == 'mr_tailor') {

						// if( $demo_type == 'default' ) {
							
							if ( $menu->name == 'Main Navigation' ) 	$locations['main-navigation'] 		= $menu->term_id;
							else if ( $menu->name == 'Top Bar Menu' ) 	$locations['top-bar-navigation']	= $menu->term_id;

						// }

					} else {

					}

				}

			}


			// Import Theme Options
			if (THEME_SLUG == 'the_retailer'):
				$theme_options_txt = $theme_options_file; // theme options data file
				$theme_options_txt = file_get_contents( $theme_options_txt );
				$imported_smof_data = unserialize( base64_decode( $theme_options_txt )  );
				of_save_options($imported_smof_data);
			else:
				$file_contents = file_get_contents( $theme_options_file );
	            $options = json_decode($file_contents, true);
	            $redux = ReduxFrameworkInstances::get_instance(THEME_SLUG.'_theme_options');
	            $redux->set_options($options);
			endif;


			unlink($theme_options_file);
			unlink($theme_demo_xml_file);

			// Set reading options
			$homepage 	= get_page_by_title( $homepage_title );
			$blog 		= get_page_by_title( $blog_title );

			//echo "Homepage: " . $homepage->ID;
			//echo "Blog: " . $blog->ID;

			if( isset($homepage) && $homepage->ID) {
				update_option( 'show_on_front', 	'page' );
				update_option( 'page_on_front', 	$homepage->ID ); 	// Front Page
				update_option( 'page_for_posts', 	$blog->ID ); 	// Posts Page
			}

			set_theme_mod( 'nav_menu_locations', $locations ); // set menus to locations

			// echo 'imported';

			exit;
		}
	}
}