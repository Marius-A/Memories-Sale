jQuery(function($) {
	
	"use strict";

	function setCookie(key, value) {
	    var expires = new Date();
	    expires.setTime(expires.getTime() + 604800); //1 week  
	    document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
	}

	$('.notice_update_theme button.notice-dismiss').live('click', function(e) {
		setCookie('notice_update_theme', '1');
	});

	$('.notice_product_key button.notice-dismiss').live('click', function(e) {
		setCookie('notice_product_key', '1');
	});

	//setCookie('notice_update_theme', '0');
	//setCookie('notice_product_key', '0');

	/*========================*/
	// Demos
	/*========================*/

	$('.getbowtied-install-demo-button').live('click', function(e) {
			
		var selected_demo = jQuery(this).data('demo-id');
		var loading_img = jQuery('.preview-'+selected_demo);
		var disable_preview = jQuery('.preview-all');

		var confirm = window.confirm('This process will get you the pre-built layouts you see in the demo, a few dummy blog posts, products and it sets the theme’s options from the live theme’s demo. It will take a little while so please remember that patience is a virtue.\n\n*****\n\nREQUIREMENTS:\n\n• WooCommerce and Visual Composer must be a activated before the import is started\n• Memory Limit on your server to be set to: 256MB\n• PHP Max Execution Time to be set to: 300 seconds');
		
		if(confirm == true) {

			loading_img.show();
			disable_preview.show();

			var data = {
				action: 'getbowtied_demo_importer',
				demo_type: selected_demo
			};

			jQuery('.importer-notice').hide();

			jQuery.post(ajaxurl, data, function(response) {
				// alert(response);
				if( response && response.indexOf('imported') == -1 ) {
					jQuery('.importer-notice-1').attr('style','display:block !important');
				} else {
					jQuery('.importer-notice-2').attr('style','display:block !important');
				}

				loading_img.hide();
				disable_preview.hide();

			}).fail(function() {
				
				jQuery('.importer-notice-2').attr('style','display:block !important');

				loading_img.hide();
				disable_preview.hide();

			});
		}

		e.preventDefault();

	});

});