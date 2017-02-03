<?php
/**
 * Cart Page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<style>
	.page-title {
		margin-bottom: 32px;
	}
	
	@media only screen and (min-width: 641px) {
		.page-title {
			margin-bottom: 40px;
		}
	}

	@media only screen and (min-width: 1025px) {
		.page-title {
			margin-bottom: 47px;
		}	
	}
	
	.page-title:after {
		display: none;
	}
</style>

<?php remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 ); ?>

<?php do_action( 'woocommerce_before_cart' ); ?>

<div class="cart_container">
    
    <form class="cart_form" action="<?php echo esc_url( WC()->cart->get_cart_url() ); ?>" method="post">
    
        <?php do_action( 'woocommerce_before_cart_table' ); ?>
            
        <div class="row">
        
	        <div class="xxlarge-9 xlarge-10 large-12 large-centered columns">
			
				<?php wc_print_notices(); ?>
			
				<div class="row">
				
					<div class="large-7 columns">	
				 
						<div class="cart_left_wrapper">
				
							<table class="shop_table cart" cellspacing="0">
								
								<thead>
									<tr>
										<th class="product-thumbnail-thead"><?php _e( 'Product', 'woocommerce' ); ?></th>
										<th class="product-name-thead">&nbsp;</th>
										<th class="product-price-thead">&nbsp;</th>
										<th class="product-quantity-thead">
											<span class="show-for-small-only text-center product_quantity_mobile"><?php _e( 'Qty', 'woocommerce' ); ?></span>
											<span class="show-for-medium-up"><?php _e( 'Qty', 'woocommerce' ); ?></span>
										</th>	
										<th class="product-subtotal-thead"><?php _e( 'Total', 'woocommerce' ); ?></th>
										<th class="product-remove-thead">&nbsp;</th>

									</tr>
								</thead>
								
								<tbody>
									
									<?php do_action( 'woocommerce_before_cart_contents' ); ?>
							
									<?php
									foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
										$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
										$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
							
										if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
											?>
											<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
												
												<td class="product-thumbnail">
													<?php
														$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

														if ( ! $_product->is_visible() )
															echo $thumbnail;
														else
															printf( '<a href="%s">%s</a>', $_product->get_permalink( $cart_item ), $thumbnail );
													?>
												</td>
							
												<td class="product-name">
													<?php
														if ( ! $_product->is_visible() )
										echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) . '&nbsp;';
														else
															echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', $_product->get_permalink( $cart_item ), $_product->get_title() ), $cart_item, $cart_item_key );

														// Meta data
														echo WC()->cart->get_item_data( $cart_item );

							               				// Backorder notification
							               				if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
							               					echo '<p class="backorder_notification">' . __( 'Available on backorder', 'woocommerce' ) . '</p>';
													?>
												</td>
							
												<td class="product-price">
													<?php
														echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
													?>
												</td>
							
												<td class="product-quantity">
													<?php
														if ( $_product->is_sold_individually() ) {
															$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
														} else {
															$product_quantity = woocommerce_quantity_input( array(
																'input_name'  => "cart[{$cart_item_key}][qty]",
																'input_value' => $cart_item['quantity'],
																'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
																'min_value'   => '0'
															), $_product, false );
														}

														echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key );
													?>
												</td>
							
												<td class="product-subtotal">
													<?php
														echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
													?>
												</td>
												
												<td class="product-remove">
													<?php
														echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s"><i class="fa fa-times"></i></a>', esc_url( WC()->cart->get_remove_url( $cart_item_key ) ), __( 'Remove this item', 'woocommerce' ) ), $cart_item_key );
													?>
												</td>
												
											</tr>
											<?php
										}
									}
							
									do_action( 'woocommerce_cart_contents' );
									?>
									
									<?php do_action( 'woocommerce_after_cart_contents' ); ?>
									
								</tbody>
							</table>
							
							<?php if ( WC()->cart->coupons_enabled() ) { ?>
								<div class="coupon_code_wrapper">
									
									<p class="coupon_code_text"><?php _e( 'Coupon', 'woocommerce' ) ?></p>
									
									<div class="coupon_code_wrapper_inner">
			
										<input name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php _e( 'Coupon code', 'woocommerce' ); ?>" /><input type="submit" class="button apply_coupon" name="apply_coupon" value="<?php _e( 'Apply Coupon', 'woocommerce' ); ?>" />
										<?php do_action('woocommerce_cart_coupon'); ?>
			
									</div>
								</div>
							<?php } ?>
							
							<?php do_action( 'woocommerce_after_cart_table' ); ?>
		        
						</div><!--.cart_left_wrapper-->
				
		            </div><!-- .large-7 -->
		       
					<div class="large-5 columns">
					
						<div class="cart_right_wrapper custom_border">
					
							<div class="cart-collaterals">
				
								<div class="cart-totals-wrapper">
			
									<?php woocommerce_cart_totals(); ?>
			
								</div>
						
								<div class="cart-buttons">                
									
									<div class="update_and_checkout">                
										<input type="submit" class="button update_cart" name="update_cart" value="<?php _e( 'Update Cart', 'woocommerce' ); ?>" />               
									</div>
							
									<?php do_action('woocommerce_proceed_to_checkout'); ?>
									
									<?php do_action( 'woocommerce_cart_actions' ); ?>

									<?php wp_nonce_field( 'woocommerce-cart') ?>
								
								</div><!--.cart-buttons-->
								
							</div><!-- .cart-collaterals -->
						
						</div><!--.cart_right_wrapper-->
						
					</div><!-- .large-5 -->
				
				</div><!-- .row -->
				
			</div><!-- .large-9 -->
		
		</div><!-- .row -->	
    
    </form>
    
    <?php do_action( 'woocommerce_after_cart' ); ?>
    
	
	
	<div class="row">
        <div class="large-9 large-centered columns">
	
		<?php do_action('woocommerce_cart_collaterals'); ?>
    
		</div><!-- .large-10 -->
	</div><!-- .row -->	
	

</div><!-- .cart_container-->