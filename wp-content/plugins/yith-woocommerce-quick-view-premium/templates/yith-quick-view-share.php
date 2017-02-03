<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

$socials = get_option( 'yith-wcqv-share-socials' );
$fb_appid = get_option( 'yith-wcqv-facebook-appid' );
$link = get_the_permalink( $product->id );
$title = get_the_title( $product->id );
$attrs = '';

if( empty( $socials ) ) {
	return;
}
?>

<div class="yith-quick-view-share">

	<?php foreach( $socials as $social ) {

		if( $social == 'facebook' && $fb_appid ) {
			$url   = 'https://www.facebook.com/dialog/share?app_id='. $fb_appid .'&display=popup&href=' . $link;
			$attrs = " onclick=\"javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;\"";
		}
		elseif( $social == 'twitter' ) {
			$url   = 'https://twitter.com/share?url=' . $link . '&text=' . $title . '';
			$attrs = " onclick=\"javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=417,width=600');return false;\"";
		}
		elseif( $social == 'google-plus' ) {
			$url   = 'https://plus.google.com/share?url=' . $link;
			$attrs = " onclick=\"javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;\"";
		}
		elseif( $social == 'pinterest' ) {
			$src   = wp_get_attachment_image_src( get_post_thumbnail_id( $product->id ), 'full' );

			$url   = 'http://pinterest.com/pin/create/button/?url=' . $link . '&media=' . $src[0];
			$attrs = " onclick=\"javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;\"";
		}
		elseif( $social == 'mail' ) {
			$subject = urlencode( apply_filters( 'yith_wcqv_share_mail_subject', __( 'May I ask you to see this product, please?', 'yith-woocommerce-quick-view' ) ) );
			$url = 'mailto:?subject='.$subject.'&amp;body= ' . $link . '&amp;title=' . $title;
		}
		else {
			continue;
		}

		$url = apply_filters( 'yith_wcqv_share_' . $social, $url );

		echo '<a href="' . esc_url( $url ) .'" title="' . $social . '" target="_blank" ' . $attrs .' class="social-' . $social . '">' . $social .'</a>';

	}

	?>
</div>