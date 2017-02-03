<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version 2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop, $shopkeeper_theme_options;

//woocommerce_before_shop_loop_item
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );

//woocommerce_after_shop_loop_item_title
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

add_action( 'woocommerce_after_shop_loop_item_title_loop_price', 'woocommerce_template_loop_price', 10 );
add_action( 'woocommerce_after_shop_loop_item_title_loop_rating', 'woocommerce_template_loop_rating', 5 );

//woocommerce_before_shop_loop_item_title
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ) {
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) ) {
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
}

// Ensure visibility
if ( ! $product || ! $product->is_visible() ) {
	return;
}

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 === ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 === $woocommerce_loop['columns'] ) {
	$classes[] = 'first';
}
if ( 0 === $woocommerce_loop['loop'] % $woocommerce_loop['columns'] ) {
	$classes[] = 'last';
}
?>

<li class="<?php if ( (isset($shopkeeper_theme_options['catalog_mode'])) && ($shopkeeper_theme_options['catalog_mode'] == 1) ) : ?>catalog_mode<?php endif;
				 if ( !$shopkeeper_theme_options['add_to_cart_display']) echo 'display_buttons' ?>">
   
	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>	

		<?php
			$attachment_ids = $product->get_gallery_attachment_ids();
			if ( $attachment_ids ) {
				$loop = 0;
				foreach ( $attachment_ids as $attachment_id ) {
					$image_link = wp_get_attachment_url( $attachment_id );
					if (!$image_link) continue;
					$loop++;
					$product_thumbnail_second = wp_get_attachment_image_src($attachment_id, 'shop_catalog');
					if ($loop == 1) break;
				}
			}
		?>
		
		<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
		
		<?php

		$style = '';
		$class = '';        
		if (isset($product_thumbnail_second[0])) {            
			$style = 'background-image:url(' . $product_thumbnail_second[0] . ')';
			$class = 'with_second_image';     
		}
		
		if ( (isset($shopkeeper_theme_options['second_image_product_listing'])) && ($shopkeeper_theme_options['second_image_product_listing'] == "0" ) ) {
			$style = '';
			$class = '';
		}
		
		?>
		
		<div class="product_thumbnail_wrapper <?php if ( !$product->is_in_stock() ) : ?>outofstock<?php endif; ?>">
			
			<div class="product_thumbnail <?php echo $class; ?>">
				<a href="<?php the_permalink(); ?>">
					<span class="product_thumbnail_background" style="<?php echo $style; ?>"></span>
					<?php
						if ( has_post_thumbnail( $post->ID ) ) { 	
							echo  get_the_post_thumbnail( $post->ID, 'shop_catalog');
						}else{
							 echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="Placeholder" />', wc_placeholder_img_src() ), $post->ID );
						}
					?>
				</a>
			</div><!--.product_thumbnail-->
			
			<?php if ( (isset($shopkeeper_theme_options['catalog_mode'])) && ($shopkeeper_theme_options['catalog_mode'] == 0) ) : ?>
				<?php wc_get_template( 'loop/sale-flash.php' ); ?>
            <?php endif; ?>
			
			<?php if ( (isset($shopkeeper_theme_options['catalog_mode'])) && ($shopkeeper_theme_options['catalog_mode'] == 0) ) : ?>
				<?php if ( !$product->is_in_stock() ) : ?>            
                    <div class="out_of_stock_badge_loop"><?php _e( 'Out of stock', 'woocommerce' ); ?></div>            
                <?php endif; ?>
            <?php endif; ?>
			
			<?php if (class_exists('YITH_WCWL')) : ?>
			<?php echo do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>
            <?php endif; ?>

            <?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
			
		</div><!--.product_thumbnail_wrapper-->
				
		<h3><a class="product-title-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		
        <?php if ( (isset($shopkeeper_theme_options['ratings_catalog_page'])) && ($shopkeeper_theme_options['ratings_catalog_page'] == "1" ) ) : ?>
        <div class="archive-product-rating">
			<?php do_action( 'woocommerce_after_shop_loop_item_title_loop_rating' ); ?>
		</div>
        <?php endif; ?>
	
		<div class="product_after_shop_loop">
			
			<div class="product_after_shop_loop_switcher">
				
				<div class="product_after_shop_loop_price">
					<?php do_action( 'woocommerce_after_shop_loop_item_title_loop_price' ); ?>
				</div>
				
				<div class="product_after_shop_loop_buttons">
					<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
				</div>
				
			</div>
			
		</div>
	
</li>