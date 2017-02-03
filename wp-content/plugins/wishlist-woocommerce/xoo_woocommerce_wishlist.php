<?php
/**
 Plugin name: Wishlist WooCommerce
 Plugin URI: http://xootix.com
 Author: XootiX
 Autor URI: http://xootix.com
 Version: 1.0.0
 Description: Wishlist WooCommerce adds wishlist button to product and helps customers to save their favorite products in a wishlist page.
 Tags:Woocommerce wishlist , add to favorites , add to wishlist , favorites , wishlist for woocommerce
 */

if (!defined('ABSPATH')) {
	return;
}

include(plugin_dir_path(__FILE__). 'inc/xoo_wishlist_enqueue.php');
include(plugin_dir_path(__FILE__). 'inc/xoo_wishlist_admin.php');
require_once (plugin_dir_path(__FILE__) . 'xoo_wishlist_core.php');
//Dialog Box
function xoo_wishlist_dialogbox(){
	global $xoo_wishlist_shortcode_page_value;

	$xoo_dialogbox  = '<div class="xoo-wishlist-dialog">';
	$xoo_dialogbox .= '<div class="xoo-dialog-icon icon-heart xoo-middle"></div>';
	$xoo_dialogbox .= '<div class="xoo-dialog-close icon-cross"></div>';
	$xoo_dialogbox .= '<div class="xoo-dialog-info xoo-middle">Product added to wishlist <br> <a href ="'.get_permalink($xoo_wishlist_shortcode_page_value).'" style = "text-decoration: underline">View Wishlist</a></div>';
	$xoo_dialogbox .= '</div>';
	echo $xoo_dialogbox;

}
add_action('wp_head','xoo_wishlist_dialogbox');

//Creating page on first time activation
function xoo_wishlist_activate_option() {

  add_option( 'xoo_wishlist_activate', 'xoo_wishlist' );
  add_option( 'xoo_wishlist_activate_page', '' );

  
}
register_activation_hook( __FILE__, 'xoo_wishlist_activate_option' );
function xoo_activate_function(){
	if (get_option('xoo_wishlist_activate_page') == null) {
        $xoo_page = array(
            
            'post_status' => 'publish',
            'post_title' => 'Wishlist',
            'post_type' => 'page',
            'post_content' => '[xoo_wishlist]'
        );
        //insert page and save the id
        $pageid = wp_insert_post($xoo_page, false);
        update_option('xoo_wishlist_activate_page',$pageid);
    }
}

function xoo_wishlist_activate() {

    if ( is_admin() && get_option( 'xoo_wishlist_activate' ) == 'xoo_wishlist' ) {

        delete_option( 'xoo_wishlist_activate' );


        add_action('admin_head','xoo_activate_function');
    }
}
add_action( 'admin_init', 'xoo_wishlist_activate' );


//Enqueue Scripts
function xoo_enqueue_scripts(){
	if(isset($_COOKIE['xoo_wishlist_cookie'])){
		$xoo_set_wishlist_cookie  = $_COOKIE['xoo_wishlist_cookie'];
	}
	else{
		$xoo_set_wishlist_cookie  = '';
	}

	wp_enqueue_style('xoo_wishlist_style_font', 'https://fonts.googleapis.com/css?family=Raleway:600,400');
	wp_enqueue_style('xoo_wishlist_style', plugins_url('assets/css/wishlist_style.css', __FILE__));
	wp_enqueue_script('xoo_wishlist_js', plugins_url('assets/js/wishlist_js.js', __FILE__) , array(
		'jquery'
	) , '1.0.0', true);
	wp_localize_script('xoo_wishlist_js', 'xoo_data', array(
		'adminurl' => admin_url() . 'admin-ajax.php',
		// Generating Product ids For jquery (adding active class)
		'button_id' => str_replace(',', ',#xoo-wishlist-', $xoo_set_wishlist_cookie )
	));
	
}

add_action('wp_enqueue_scripts', 'xoo_enqueue_scripts');
//Closing woocommerce product link
add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 11);

function xoo_wishlist_button_html(){
	
    $xoo_wishlist_button_html ='<div id = "xoo-wishlist-' . get_the_ID() . '" post-id =  "' . get_the_ID() . '" class="xoo_wishlist_add xoo_wishlist_button icon-heart">';
    if(is_single()){
     $xoo_wishlist_button_html .='<div class="xoo-wishlist-text">Add to Wishlist</div>';
    }
    $xoo_wishlist_button_html .='</div>';
    return $xoo_wishlist_button_html;
}
// Displaying Wishlist button 
function xoo_wishlist_button()

{
	global $xoo_wishlist_pages_value; //xoo_wishlist_admin.php 
	
   if(!is_single() && (is_shop() || is_product_category() || is_page(explode(',',$xoo_wishlist_pages_value)))){
   	echo xoo_wishlist_button_html();
   

   }
remove_action('xoo_wishlist_button','woocommerce_template_loop_product_link_close', 1);
}

// Changing Wishlist Button Positions.
global $xoo_wishlist_position_icon_value; //xoo_wishlist_admin.php
$xoo_wishlist_woocommerce_hook_position = substr($xoo_wishlist_position_icon_value, strpos( $xoo_wishlist_position_icon_value, '+')+1);
$xoo_wishlist_woocommerce_hook = substr($xoo_wishlist_position_icon_value,0, strpos( $xoo_wishlist_position_icon_value, '+'));

// $xoo_wishlist_woocommerce_hook_position == Position (Setting Positions)
	add_action($xoo_wishlist_woocommerce_hook, 'xoo_wishlist_button',$xoo_wishlist_woocommerce_hook_position);

//Wishlist on single-page
function xoo_wishlist_button_singlepage(){
	global $xoo_wishlist_single_value; //xoo_wishlist_admin.php 

   if(is_single() && $xoo_wishlist_single_value == "true"){
   	echo xoo_wishlist_button_html();
   }
   

   }
 add_action('woocommerce_single_product_summary', 'xoo_wishlist_button_singlepage',6);

// Adding Front end CSS to Head.
function xoo_wishlist_head_style()
{
	global $xoo_wishlist_woocommerce_hook;
	global $xoo_wishlist_color_icon_value;
	global $xoo_wishlist_woocommerce_hook_position;
	$style = '<style>
          .active{color: ' . $xoo_wishlist_color_icon_value . ';}';

 // Inside Rating Style.         
	$style.= $xoo_wishlist_woocommerce_hook == 'woocommerce_after_shop_loop_item_title' && $xoo_wishlist_woocommerce_hook_position == 6 ? '.star-rating{display:inline-block!important;}.xoo_wishlist_button{display: inline-block;}' : '';

// On Top Style.
	$style.= $xoo_wishlist_woocommerce_hook == 'woocommerce_before_shop_loop_item' && $xoo_wishlist_woocommerce_hook_position == 1 ? '.xoo_wishlist_button{text-align: right;}' : '';
	$style.= '</style>';
	echo $style;
}
add_action('wp_head','xoo_wishlist_head_style');

?>