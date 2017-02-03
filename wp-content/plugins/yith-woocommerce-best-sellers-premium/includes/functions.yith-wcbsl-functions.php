<?php

/**
 * Functions
 *
 * @author  Yithemes
 * @package YITH WooCommerce Best Sellers
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCBSL' ) ) {
    exit;
} // Exit if accessed directly

if (! function_exists( 'yith_wcbsl_rss_text_limit')) {
    function yith_wcbsl_rss_text_limit( $string, $length, $replacer = '...' ) {
        $string = strip_tags( $string );
        if ( strlen( $string ) > $length )
            return ( preg_match( '/^(.*)\W.*$/', substr( $string, 0, $length + 1 ), $matches ) ? $matches[ 1 ] : substr( $string, 0, $length ) ) . $replacer;

        return $string;
    }
}

