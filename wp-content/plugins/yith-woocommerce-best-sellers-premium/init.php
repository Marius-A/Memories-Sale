<?php
/**
* Plugin Name: YITH WooCommerce Best Sellers Premium
* Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-best-sellers/
* Description: YITH WooCommerce Best Sellers Premium allows you to create a bestsellers page and widget, that will make sales in your WooCommerce shop increase.
* Version: 1.0.8
* Author: YITHEMES
* Author URI: http://yithemes.com/
* Text Domain: yith-woocommerce-best-sellers
* Domain Path: /languages/
*
* @author yithemes
* @package YITH WooCommerce Best Sellers Premium
* @version 1.0.8
*/
/*  Copyright 2015  Your Inspiration Themes  (email : plugins@yithemes.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// Free version deactivation if installed __________________

if( ! function_exists( 'yit_deactive_free_version' ) ) {
    require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_WCBSL_FREE_INIT', plugin_basename( __FILE__ ) );

function yith_wcbsl_pr_install_woocommerce_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'YITH WooCommerce Best Sellers Premium is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-best-sellers' ); ?></p>
    </div>
    <?php
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
    require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );


if ( ! defined( 'YITH_WCBSL_VERSION' ) ){
    define( 'YITH_WCBSL_VERSION', '1.0.8' );
}

if ( ! defined( 'YITH_WCBSL_PREMIUM' ) ) {
    define( 'YITH_WCBSL_PREMIUM', '1' );
}

if ( ! defined( 'YITH_WCBSL_INIT' ) ) {
    define( 'YITH_WCBSL_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_WCBSL' ) ) {
    define( 'YITH_WCBSL', true );
}

if ( ! defined( 'YITH_WCBSL_FILE' ) ) {
    define( 'YITH_WCBSL_FILE', __FILE__ );
}

if ( ! defined( 'YITH_WCBSL_URL' ) ) {
    define( 'YITH_WCBSL_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YITH_WCBSL_DIR' ) ) {
    define( 'YITH_WCBSL_DIR', plugin_dir_path( __FILE__ )  );
}

if ( ! defined( 'YITH_WCBSL_TEMPLATE_PATH' ) ) {
    define( 'YITH_WCBSL_TEMPLATE_PATH', YITH_WCBSL_DIR . 'templates/premium' );
}

if ( ! defined( 'YITH_WCBSL_ASSETS_URL' ) ) {
    define( 'YITH_WCBSL_ASSETS_URL', YITH_WCBSL_URL . 'assets' );
}

if ( ! defined( 'YITH_WCBSL_ASSETS_PATH' ) ) {
    define( 'YITH_WCBSL_ASSETS_PATH', YITH_WCBSL_DIR . 'assets' );
}

if ( ! defined( 'YITH_WCBSL_SLUG' ) ) {
	define( 'YITH_WCBSL_SLUG', 'yith-woocommerce-best-sellers' );
}

if ( ! defined( 'YITH_WCBSL_SECRET_KEY' ) ) {
	define( 'YITH_WCBSL_SECRET_KEY', 'Dji5UDvfJsFOXvXi30jc' );
}

if ( ! defined( 'YITH_WCBSL_DEBUG' ) ) {
    define( 'YITH_WCBSL_DEBUG', false );
}

function yith_wcbsl_pr_init() {

    load_plugin_textdomain( 'yith-woocommerce-best-sellers', false, dirname( plugin_basename( __FILE__ ) ). '/languages/' );

    // Load required classes and functions
    require_once('includes/functions.yith-wcbsl-functions.php');
    require_once('includes/class.yith-wcbsl-bestsellers-widget-premium.php');
    require_once('includes/class.yith-wcbsl-bestsellers-categories-widget.php');
    require_once('includes/class.yith-wcbsl-walker-category.php');
    require_once('includes/class.yith-wcbsl-reports.php');
    require_once('includes/class.yith-wcbsl-reports-premium.php');
    require_once('includes/class.yith-wcbsl-install.php');
    require_once('class.yith-wcbsl-admin.php');
    require_once('class.yith-wcbsl-frontend.php');
    require_once('class.yith-wcbsl.php');
    require_once('class.yith-wcbsl-admin-premium.php');
    require_once('class.yith-wcbsl-frontend-premium.php');
    require_once('class.yith-wcbsl-premium.php');


    // Let's start the game!
    YITH_WCBSL_Premium();
}
add_action( 'yith_wcbsl_pr_init', 'yith_wcbsl_pr_init' );


function yith_wcbsl_pr_install() {

    if ( ! function_exists( 'WC' ) ) {
        add_action( 'admin_notices', 'yith_wcbsl_pr_install_woocommerce_admin_notice' );
    }
    else {
        do_action( 'yith_wcbsl_pr_init' );
    }
}
add_action( 'plugins_loaded', 'yith_wcbsl_pr_install', 11 );

/* Plugin Framework Version Check */
if ( !function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
    require_once( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( plugin_dir_path( __FILE__ ) );