<?php
/**
 * Template of Best Seller
 *
 * @author  Yithemes
 * @package YITH WooCommerce Best Sellers
 * @version 1.0.0
 */


$bs = wc_get_product( $id );

if ( $bs ) : ?>
    <div class="yith-wcbsl-bestseller-wrapper">
        <a href="<?php echo get_permalink( $bs->post->ID ); ?>">
            <div class="yith-wcbsl-bestseller-container">
                <span class="yith-wcbsl-bestseller-position"><?php echo $loop ?></span>
                <?php
                $thumb_id = get_post_thumbnail_id( $id );
                if ( $thumb_id ) :
                    ?>

                    <div class="yith-wcbsl-bestseller-thumb-wrapper">
                        <?php $image_title = esc_attr( get_the_title( $thumb_id ) );
                        $image_caption     = get_post( $thumb_id )->post_excerpt;
                        $image_link        = wp_get_attachment_url( $thumb_id );
                        $image             = get_the_post_thumbnail( $id, 'shop_catalog' );
                        $resized_link      = wp_get_attachment_image_src( $thumb_id, 'shop_catalog' );

                        $image_link = !empty( $resized_link ) ? $resized_link[ 0 ] : $image_link;

                        echo "<img src='{$image_link}' title='{$image_title}' alt='{$image_title}' />";
                        //echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto">%s</a>', $image_link, $image_caption, $image ), $id );
                        ?>
                    </div>
                <?php endif; ?>
                <div class="yith-wcbsl-bestseller-content-wrapper">
                    <h3><?php echo $bs->post->post_title; ?></h3>

                    <span class="price"> <?php echo $bs->get_price_html(); ?></span>

                    <!--<p>Quantity: <?php echo $qty; ?></p>-->
                </div>
            </div>
        </a>
    </div>
<?php endif; ?>

