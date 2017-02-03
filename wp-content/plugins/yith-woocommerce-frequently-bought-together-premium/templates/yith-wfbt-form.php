<?php
/**
 * Form template
 *
 * @author Yithemes
 * @package YITH WooCommerce Frequently Bought Together Premium
 * @version 1.0.5
 */

global $product;


$index  = 0;
$thumbs = $checks = '';
$total  = 0;

if( ! isset( $products ) ) {
	return;
}

// set query
$url = ! is_null( $product ) ? get_permalink( $product->id ) : '';
$url = add_query_arg( 'action', 'yith_bought_together', $url );
$url = wp_nonce_url( $url, 'yith_bought_together' );


foreach( $products as $current ) {

	// get correct id if product is variation
	$id = $current->product_type == 'variation' ? $current->variation_id : $current->id;

	if( $index > 0 )
		$thumbs .= '<td class="image_plus image_plus_' . $index . '" data-rel="offeringID_' . $index . '">+</td>';
	$thumbs .= '<td class="image-td" data-rel="offeringID_' . $index . '"><a href="' . get_permalink( $current->id ) . '">' . $current->get_image( 'yith_wfbt_image_size' ) . '</a></td>';

	ob_start();
	?>
	<li class="yith-wfbt-item">
		<label for="offeringID_<?php echo $index ?>">
			<input type="checkbox" name="offeringID[]" id="offeringID_<?php echo $index ?>" class="active" value="<?php echo $id ?>"
		           data-price="<?php echo $current->get_display_price() ?>" checked="checked">

			<?php if( $index ) : ?>
				<a href="<?php echo get_permalink( $current->id ) ?>">
			<?php endif ?>

			<span class="product-name">
				<?php echo ! $index ? __( 'This Product', 'yith-woocommerce-frequently-bought-together' ) . ': ' . $current->get_title() : $current->get_title(); ?>
			</span>

			<?php

			if( $current->product_type == 'variation' ) {
				$attributes = $current->get_variation_attributes();
				$variations = array();

				foreach( $attributes as $key => $attribute ) {
					$key = str_replace( 'attribute_', '', $key );

					$terms = get_terms( sanitize_title( $key ), array(
						'menu_order' => 'ASC',
						'hide_empty' => false
					) );

					foreach ( $terms as $term ) {
						if ( ! is_object( $term ) || ! in_array( $term->slug, array( $attribute ) ) ) {
							continue;
						}
						$variations[] = $term->name;
					}
				}

				if( ! empty( $variations ) )
					echo '<span class="product-attributes"> &ndash; ' . implode( ', ', $variations ) . '</span>';
			}

			if( $index )
				echo '</a>'; // close a tag line 44

			// echo product price
			echo ' &ndash; <span class="price">' . $current->get_price_html() . '</span>';
			?>

		</label>
	</li>
	<?php
	$checks .= ob_get_clean();
	// increment total
	$total += floatval( $current->get_display_price() );

	// increment index
	$index++;
}

if( $index < 2 ) {
	return; // exit if only available product is current
}

// set button label
$label       = ( $index == 2 ) ? get_option( 'yith-wfbt-button-double-label' ) : ( $index == 3 ? get_option( 'yith-wfbt-button-three-label' ) : get_option( 'yith-wfbt-button-multi-label' ) );
$label_total = ( $index == 2 ) ? get_option( 'yith-wfbt-total-double-label' ) : ( $index == 3 ? get_option( 'yith-wfbt-total-three-label' ) : get_option( 'yith-wfbt-total-multi-label' ) );
$title       = get_option( 'yith-wfbt-form-title' );

?>

<div class="yith-wfbt-section woocommerce">
	<?php if( $title != '' ) {
		echo '<h3>' . esc_html( $title ) . '</h3>';
	}
	?>

	<form class="yith-wfbt-form" method="post" action="<?php echo esc_url( $url ) ?>">

		<table class="yith-wfbt-images">
			<tbody>
				<tr>
					<?php echo $thumbs ?>
				</tr>
			</tbody>
		</table>

		<div class="yith-wfbt-submit-block">
			<div class="price_text">
				<span class="total_price_label">
					<?php echo esc_html( $label_total ) ?>:
				</span>
				&nbsp;
				<span class="total_price" data-total="<?php echo $total ?>">
					<?php echo wc_price( $total ) ?>
				</span>
			</div>
			<input type="submit" class="yith-wfbt-submit-button button" value="<?php echo esc_html( $label ); ?>">
		</div>

		<ul class="yith-wfbt-items">
			<?php echo $checks ?>
		</ul>

	</form>
</div>