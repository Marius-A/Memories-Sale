=== YITH WooCommerce Wishlist ===

Contributors: yithemes
Tags: wishlist, woocommerce, products, themes, yit, e-commerce, shop
Requires at least: 4.0
Tested up to: 4.5.2
Stable tag: 2.0.16
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Documentation: http://yithemes.com/docs-plugins/yith-woocommerce-wishlist

== Changelog ==

= 2.0.16 - Released: Jun, 14 - 2016 =

* Added: WooCommerce 2.6 support
* Tweak: changed uninstall procedure to work with multisite and delete plugin options
* Tweak: removed description and image from facebook share link (fb doesn't allow anymore)
* Fixed: product query (GROUP By and LIMIT statement conflicting)
* Fixed: to print "Sent Manually" on WC Emails

= 2.0.15 - Released: Apr, 04 - 2016 =

* Added: filter yith_wcwl_is_product_in_wishlist to choose whether a product is in wishlist or not
* Added: filter yith_wcwl_cookie_expiration to set default wishlist cookie expiration time in seconds
* Tweak: updated plugin-fw
* Fixed: get_products query returning product multiple times when product has more then one visibility meta

= 2.0.14 - Released: 21/03/2016

Added: yith_wcwl_is_wishlist_page function to identify if current page is wishlist page
Added: filter yith_wcwl_settings_panel_capability for panel capability
Added: filter yith_wcwl_current_wishlist_view_params for shortcode view params
Added: "defined YITH_WCWL" check before every template
Added: check over existance of $.prettyPhoto.close before using it
Added: method count_add_to_wishlist to YITH_WCWL class
Added: function yith_wcwl_count_add_to_wishlist
Tweak: Changed ajax url to "relative"
Tweak: Removed yit-common (old plugin-fw) deprecated since 2.0
Tweak: Removed deprecated WC functions
Tweak: Skipped removed_from_wishlist query arg adding, when external product
Tweak: Added transients for wishist counts
Tweak: Removed DOM structure dependencies from js for wishlist table handling
Tweak: All methods/functions that prints/counts products in wishlist now skip trashed or not visible products
Fixed: shortcode callback setting global product in some conditions
Fixed: typo in hook yith_wccl_table_after_product_name (now set to yith_wcwl_table_after_product_name)
Fixed: notice appearing when wishlist page slug is empty
Fixed: "Please login" notice appearing right after login
Fixed: email template for WC 2.5 and WCET compatibility

= 2.0.13 - Released: 17/12/2015 =

* Added check over adding_to_cart event data existance in js procedures
* Added compatibility with YITH WooCommerce Email Templates
* Added 'yith_wcwl_added_to_cart_message' filter, to customize added to cart message in wishlist page
* Added 'yith_wcwl_action_links' filter, to customize action link at the end of wishlist pages
* Added nofollow to "Add to Wishlist" links, where missing
* Added 'yith_wcwl_email_share_subject' filter to customize share by email subject
* Added 'yith_wcwl_email_share_body' filter to customize share by email body
* Added function "yith_wcwl_count_all_products"
* Fixed plugin-fw loading

= 2.0.12 - Released: 23/10/2015 =

* Added: method to count all products in wishlist
* Tweak: Added wishlist js handling on 'yith_wcwl_init' triggered on document
* Tweak: Performance improved with new plugin core 2.0
* Fixed: occasional fatal error for users with outdated version of plugin-fw on their theme

= 2.0.11 - Released: 21/09/2015 =

* Updated: changed text domain from yit to yith-woocommerce-wishlist
* Updated: changed all language file for the new text domain

= 2.0.10 - Released: 12/08/2015 =

* Added: Compatibility with WC 2.4.2
* Tweak: added nonce field to wishlist-view form
* Tweak: added yith_wcwl_custom_add_to_cart_text and yith_wcwl_ask_an_estimate_text filters
* Tweak: added check for presence of required function in wishlist script
* Fixed: admin colorpicker field (for WC 2.4.x compatibility)

= 2.0.9 - Released: 24/07/2015 =

* Added: WooCommerce class to wishlist view form
* Added: spinner to plugin assets
* Added: check on "user_logged_in" for sub-templates in wishlist-view
* Added: WordPress 4.2.3 compatibility
* Added: WPML 3.2.2 compatibility (removed deprecated function)
* Added: new check on is_product_in_wishlist (for unlogged users/default wishlist)
* Tweak: escaped urls on share template
* Tweak: removed new line between html attributes, to improve themes compatibility
* Updated: italian translation
* Fixed: WPML 3.2.2 compatibility (fix suggested by Konrad)
* Fixed: regex used to find class attr in "Add to Cart" button
* Fixed: usage of product_id for add_to_wishlist shortcode, when global $product is not defined
* Fixed: icon attribute for yith_wcwl_add_to_wishlist shortcode

= 2.0.8 - Released: 29/05/2015 =

* Added: support WP 4.2.2
* Added: redirect to wishlist after login
* Added: check on cookie content
* Added: Frequently Bought Together integration
* Added: text domain to page links
* Tweak: moved cookie update before first cookie usage
* Updated: Italian translation
* Removed: control to unable admin to delete default wishlists
* Removed: login_redirect_url variable

= 2.0.7 - Released: 30/04/2015 =

* Added: WP 4.2.1 support
* Added: WC 2.3.8 support
* Added: "Added to cart" message in wishlist page
* Added: promotional email functionality
* Added: email tab under wishlist panel
* Added: "Move to another wishlist" select
* Added: option to show "Already in wishlist" when multi-wishlist enabled
* Updated: revision of all templates
* Fixed: vulnerability for unserialize of cookie content (Warning: in this way all the old serialized plugins will be deleted and all the wishlists of the non-logged users will be lost)
* Fixed: Escaped add_query_arg() and remove_query_arg()
* Fixed: wishlist count on admin table
* Removed: use of pretty permalinks if WPML enabled

= 2.0.6 - Released: 2015/04/07 =

* Added: system to overwrite wishlist js
* Added: trailingslashit() to wishlist permalink
* Added: "show_empty" filter to get_wishlists() method
* Added: "user that added this product" view
* Added: admin capability to delete default wishlist
* Tweak: removed email from wishlist search
* Tweak: removed empty wishlist from admin table
* Tweak: removed "Save" button from manage template, when not needed
* Fixed: "user/user_id" endpoint
* Fixed: count wishlist items
* Fixed: problem with price inclusive of tax
* Fixed: remove from wishlist for not logged user
* Fixed: twitter share summary

= 2.0.5 - Released: 2015/03/18 =

* Added: option to show create/manage/search links after wishlist table
* Added: option to let only logged user to use wishlist
* Added: option to show a notice to invite users to log in, before wishlist table
* Added: option to add additional notes textarea when sendin e quote request
* Added: popular section on backend
* Added: checkbox to add multiple items to cart from wishlist
* Added: icl_object_id to wishlist page id, to translate pages
* Tweak: updated rewrite rules, to include child pages as wishlist pages
* Tweak: moved WC notices from wishlist template to yith_wcwl_before_wishlist_title hook
* Tweak: added wishlist table id to .load(), to update only that part of template
* Fixed: yith_wcwl_locate_template causing 500 Internal Server Error

= 2.0.4 - Released: 2015/03/04 =

* Added: Options for browse wishlist/already in wishlist/product added strings
* Added: rel nofollow to add to wishlist button
* Tweak: moved wishlist response popup handling to separate js file
* Updated: WPML xml configuration
* Updated: string revision

= 2.0.3 - Released: 2015/02/19 =

* Tweak: set correct protocol for admin-ajax requests
* Tweak: used wc core function to set cookie
* Tweak: let customization of add_to_wishlist shortcodes
* Fixed: show add to cart column when stock status disabled
* Fixed: product existing in wishlist

= 2.0.2 - Released: 2015/02/16 =

* Updated: font-awesome library
* Fixed: option with old font-awesome classes

= 2.0.1 - Released: 2015/02/13 =

* Added: spinner image on loading
* Added: flush rewrite rules on database upgrade
* Fixed: wc_add_to_cart_params not defined issue


= 2.0.0 - Released: 2015/02/12 =

* Initial release