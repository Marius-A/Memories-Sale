<?php
/**
 * Admin ask estimate email
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 2.0.13
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php do_action('woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( __( 'You have received an estimate request from %s. The request is the following:', 'yith-woocommerce-wishlist' ), $wishlist_data['user_name'] ); ?></p>

<?php do_action( 'yith_wcwl_email_before_wishlist_table', $wishlist_data ); ?>

<h2><a href="<?php echo YITH_WCWL()->get_wishlist_url( 'view' . '/' . $wishlist_data['wishlist_token'] ) ?>"><?php printf( __( 'Wishlist: %s', 'yith-woocommerce-wishlist'), $wishlist_data['wishlist_token'] ); ?></a></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
    <thead>
    <tr>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'woocommerce' ); ?></th>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
    </tr>
    </thead>
    <tbody>
        <?php
        if( ! empty( $wishlist_data['wishlist_items'] ) ):
            foreach( $wishlist_data['wishlist_items'] as $item ):
                $product = wc_get_product( $item['prod_id'] );
        ?>
            <tr>
                <td scope="col" style="text-align:left;"><a href="<?php echo get_edit_post_link( $product->id )?>"><?php echo $product->post->post_title ?></a></td>
                <td scope="col" style="text-align:left;"><?php echo $item['quantity'] ?></td>
            </tr>
        <?php
            endforeach;
        endif;
        ?>
    </tbody>
</table>

<?php if( ! empty( $additional_notes ) ): ?>

	<h2><?php echo __( "\nAdditional info:\n" );?></h2>

	<p>
		<?php echo  $additional_notes . "\n"; ?>
	</p>

<?php endif; ?>

<?php do_action( 'yith_wcwl_email_after_wishlist_table', $wishlist_data ); ?>

<h2><?php _e( 'Customer details', 'yith-woocommerce-wishlist' ); ?></h2>

<p><strong><?php _e( 'Email:', 'yith-woocommerce-wishlist' ); ?></strong> <a href="mailto:<?php echo $wishlist_data['user_email']; ?>"><?php echo $wishlist_data['user_email']; ?></a></p>

<?php  do_action( 'woocommerce_email_footer', $email ); ?>
