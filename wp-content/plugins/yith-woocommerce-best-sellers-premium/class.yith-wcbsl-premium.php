<?php
/**
 * Main class
 *
 * @author  Yithemes
 * @package YITH WooCommerce Best Sellers Premium
 * @version 1.0.0
 */


if ( !defined( 'YITH_WCBSL' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCBSL_Premium' ) ) {
    /**
     * YITH WooCommerce Best Sellers Premium
     *
     * @since 1.0.0
     */
    class YITH_WCBSL_Premium extends YITH_WCBSL {

        private $_bestseller_limit;

        /**
         * Constructor
         *
         * @return mixed| YITH_WCBSL_Admin | YITH_WCBSL_Frontend
         * @since 1.0.0
         */
        public function __construct() {
            $this->_bestseller_limit = get_option( 'yith-wcbsl-bestsellers-limit', 100 );

            // Load Plugin Framework
            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

            // Add widget for Best Sellers
            add_action( 'widgets_init', array( $this, 'register_widgets' ) );

            // Class admin
            if ( is_admin() ) {
                YITH_WCBSL_Admin_Premium();
            } // Class frontend
            else {
                YITH_WCBSL_Frontend_Premium();
            }
        }

        public function get_limit() {
            return $this->_bestseller_limit;
        }

        /**
         * register Widget for Best Sellers
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function register_widgets() {
            register_widget( 'YITH_WCBSL_Bestsellers_Widget' );
            register_widget( 'YITH_WCBSL_Bestsellers_Categories_Widget' );
        }
    }
}

/**
 * Unique access to instance of YITH_WCBSL class
 *
 * @return \YITH_WCBSL_Premium
 * @since 1.0.0
 */
function YITH_WCBSL_Premium() {
    return YITH_WCBSL_Premium::get_instance();
}

?>