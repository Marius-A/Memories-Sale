<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

foreach ( $item_list as $item ) {
	
	$image = '';

	if ( has_post_thumbnail( $item['id'] ) ) {

		$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $item['id'] ), 'ywrr_picture' );
		list( $src, $width, $height ) = $product_image;

		$image = $src;

	} elseif ( wc_placeholder_img_src() ) {

		$image = wc_placeholder_img_src();

	}

	$product_link = apply_filters( 'ywrr_product_permalink', get_permalink( $item['id'] ) );

	?>

	<a class="items" href="<?php echo $product_link ?>"><img src="<?php echo $image ?>" /><span class="title"><?php echo $item['name'] ?> &gt;</span><span class="stars"><?php _e( 'Your Vote', 'yith-woocommerce-review-reminder' ) ?></span></a>

	<?php

}