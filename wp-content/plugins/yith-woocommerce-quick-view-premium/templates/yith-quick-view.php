<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
?>

<div class="yith-quick-view <?php echo $type . ' ' . $effect . ' ' . $is_mobile ?>">

	<div class="yith-quick-view-overlay"></div>

	<div class="yith-wcqv-wrapper">

		<div class="yith-wcqv-main">

			<div class="yith-wcqv-head">
				<a href="#" class="yith-quick-view-close">X</a>
			</div>

			<div class="yith-quick-view-content woocommerce single-product"></div>

		</div>

	</div>

	<?php if ( $nav && $type != 'yith-inline' ) : ?>
		<div class="yith-quick-view-nav <?php echo $nav_style ?>">
			<a href="#" class="yith-wcqv-prev" data-product_id=""><div></div></a>
			<a href="#" class="yith-wcqv-next" data-product_id=""><div></div></a>
		</div>
	<?php endif; ?>

</div>