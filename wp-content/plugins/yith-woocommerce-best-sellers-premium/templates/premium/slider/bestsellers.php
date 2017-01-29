<?php
/**
 * Template of Best Sellers SLIDER
 *
 * @author  Yithemes
 * @package YITH WooCommerce Best Sellers
 * @version 1.0.0
 */

$reports      = new YITH_WCBSL_Reports_Premium();
$best_sellers = array();

$range      = get_option( 'yith-wcpsc-update-time', '7day' );
$range_args = array();
$limit      = YITH_WCBSL_Premium()->get_limit();

$best_sellers = $reports->get_best_sellers( $range, array( 'range_args' => $range_args, 'limit' => $limit ) );

$bs_title = get_option( 'yith-wcbsl-slider-title', _x( 'Best Sellers', 'Text of "Bestsellers" Slider', 'yith-woocommerce-best-sellers' ) );

if ( !empty( $best_sellers ) ) {
    echo '<div class="yith-wcbsl-bestsellers-slider"><div class="yith-wcbsl-bestsellers-slider-title"><div class="yith-wcbsl-bestsellers-slider-title-centered">' . $bs_title . '</div></div>';
    echo '<div class="yith-wcbsl-bestsellers-slider-wrapper">';
    echo '<div class="yith-wcbsl-bestsellers-slider-container">';
    $loop = 0;
    foreach ( $best_sellers as $product ) {
        $loop++;
        $bs_id  = absint( $product->product_id );
        $bs_qty = $product->order_item_qty;
        $args   = array(
            'id'   => $bs_id,
            'loop' => $loop
        );

        wc_get_template( '/slider/bestseller.php', $args, YITH_WCBSL_TEMPLATE_PATH, YITH_WCBSL_TEMPLATE_PATH );
    }
    echo '</div>';
    echo '</div>';
    echo '<span class="yith-wcbsl-bestseller-slider-left dashicons dashicons-arrow-left-alt2"></span>';
    echo '<span class="yith-wcbsl-bestseller-slider-right dashicons dashicons-arrow-right-alt2"></span>';
    echo '</div>';
}