<?php

	function load_settings($theme_slug)
	{
		$settings = array(); 

		/*
		** 	STANDARD SETTINGS
		*/

		$settings['woocommerce_docs'] 			= 'http://support.getbowtied.com/hc/en-us/categories/200549461';
		$settings['wordpress_docs'] 			= 'http://support.getbowtied.com/hc/en-us/categories/200561452-WordPress-for-Beginners';
		$settings['getbowtied_url']	 			= 'http://my.getbowtied.com/';
		$settings['getbowtied_update_url'] 		= 'http://my.getbowtied.com/api/update_theme.php';
		$settings['getbowtied_validate_url']	= 'http://my.getbowtied.com/api/api_listener.php';

		switch ($theme_slug)
		{
			case 'shopkeeper':

				$settings['theme_docs'] 		= 'https://shopkeeper-help.zendesk.com/hc/en-us';
				$settings['release_notes'] 		= 'https://shopkeeper-help.zendesk.com/hc/en-us/articles/207365265-Updates-History';
				$settings['purchase'] 			= 'http://themeforest.net/item/shopkeeper-responsive-wordpress-theme/9553045?license=regular&open_purchase_for_item_id=9553045&purchasable=source';
				$settings['dummy_data_preview'] = 'http://import.getbowtied.com/shopkeeper-v1.1/';
				$settings['demo_xml_file_url']  = 'http://my.getbowtied.com/api/shopkeeper/demos/demo.gz';
				$settings['options_file_url']	= 'http://my.getbowtied.com/api/shopkeeper/theme_options/theme_options.json';
				$settings['theme_logo']			= get_template_directory_uri().'/backend/img/shopkeeper.png';
				$settings['demo_image']			= get_template_directory_uri().'/backend/img/demos/shopkeeper/default.png';

			break;

			case 'mr_tailor':

				$settings['theme_docs'] 		= 'https://mr-tailor-help.zendesk.com/hc/en-us';
				$settings['release_notes'] 		= 'https://mr-tailor-help.zendesk.com/hc/en-us/articles/207382215-Updates-History';
				$settings['purchase'] 			= 'http://themeforest.net/item/mr-tailor-responsive-woocommerce-theme/7292110?license=regular&open_purchase_for_item_id=7292110&purchasable=source';
				$settings['theme_logo']			= get_template_directory_uri().'/backend/img/mr_tailor.png';

				// DEFAULT DEMO
				$settings['demo_image']			= get_template_directory_uri().'/backend/img/demos/mr_tailor/main-demo.jpg';
				$settings['dummy_data_preview'] = 'http://import.getbowtied.com/mr-tailor2/';
				$settings['demo_xml_file_url']  = 'http://my.getbowtied.com/api/mr_tailor/demos/default/demo.gz';
				$settings['options_file_url']	= 'http://my.getbowtied.com/api/mr_tailor/demos/default/theme_options.json';

				// INDIE STORE
				$settings['demo_image_indie']			= get_template_directory_uri().'/backend/img/demos/mr_tailor/indie-store.jpg';
				$settings['dummy_data_preview_indie'] 	= 'http://import.getbowtied.com/mr-tailor-indie/';
				$settings['demo_xml_file_url_indie']  	= 'http://my.getbowtied.com/api/mr_tailor/demos/indie/demo.gz';
				$settings['options_file_url_indie']		= 'http://my.getbowtied.com/api/mr_tailor/demos/indie/theme_options.json';

				// STARTUP
				$settings['demo_image_startup']			= get_template_directory_uri().'/backend/img/demos/mr_tailor/startup-demo.jpg';
				$settings['dummy_data_preview_startup'] = 'http://import.getbowtied.com/mr-tailor-startup/';
				$settings['demo_xml_file_url_startup']  = 'http://my.getbowtied.com/api/mr_tailor/demos/startup/demo.gz';
				$settings['options_file_url_startup']	= 'http://my.getbowtied.com/api/mr_tailor/demos/startup/theme_options.json';

			break;

			case 'the_retailer':

				$settings['theme_docs'] 		= 'https://the-retailer-help.zendesk.com/hc/en-us';
				$settings['release_notes'] 		= 'https://the-retailer-help.zendesk.com/hc/en-us/articles/206694069-Updates-History';
				$settings['purchase'] 			= 'http://themeforest.net/item/the-retailer-responsive-wordpress-theme/4287447?license=regular&open_purchase_for_item_id=4287447&purchasable=source&ref=getbowtied';
				$settings['dummy_data_preview'] = 'http://import.getbowtied.com/the-retailer-v1.1/';
				$settings['demo_xml_file_url']  = 'http://my.getbowtied.com/api/the_retailer/demos/demo.gz';
				$settings['options_file_url']	= 'http://my.getbowtied.com/api/the_retailer/theme_options/theme_options.txt';
				$settings['theme_logo']			= get_template_directory_uri().'/backend/img/the_retailer.png';
				$settings['demo_image']			= get_template_directory_uri().'/backend/img/demos/the_retailer/default.png';

			break;

			default:
			break;
		}

		return $settings;
	}

	$getbowtied_settings = load_settings(THEME_SLUG);
?>