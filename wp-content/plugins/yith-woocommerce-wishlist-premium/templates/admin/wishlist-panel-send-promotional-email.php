<h3><?php _e( 'Set email content', 'yith-woocommerce-wishlist' ) ?></h3>
<form id="plugin-fw-wc" method="post">
	<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
	<input type="hidden" name="tab" value="<?php echo $current_tab ?>" />

	<table class="form-table">
		<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'Email HTML content', 'yith-woocommerce-wishlist' ) ?></th>
			<td class="forminp forminp-textarea">
				<textarea name="yith_wcwl_promotional_email_html_content" id="yith_wcwl_promotional_email_html_content" style="width:100%; height: 250px;"><?php echo $promotional_email_html_content ?></textarea>
				<p class="description">
					<?php _e( 'This field lets you modify the main content of the HTML email. You can use the following placeholder: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{product_image}</code> <code>{product_name}</code> <code>{product_price}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{coupon_value}</code> <code>{add_to_cart_url}</code>', 'yith-woocommerce-wishlist' ) ?><br/>
					<?php echo sprintf( '<a target="_blank" href="%s">%s</a>', esc_url( add_query_arg( array( 'preview_yith_wcwl_promotion_email' => 'true', 'template' => 'html', 'product_id' => $product_id ), wp_nonce_url( admin_url(), 'preview-promotion-mail' ) ) ), __( 'View preview', 'yith-woocommerce-wishlist' ) ) ?>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'Email plain text content', 'yith-woocommerce-wishlist' ) ?></th>
			<td class="forminp forminp-textarea">
				<textarea name="yith_wcwl_promotional_email_text_content" id="yith_wcwl_promotional_email_text_content" style="width:100%; height: 250px;"><?php echo $promotional_email_text_content ?></textarea>
				<p class="description">
					<?php _e( 'This field lets you modify the main content of the text email. You can use the following placeholder: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{product_name}</code> <code>{product_price}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{coupon_value}</code> <code>{add_to_cart_url}</code>', 'yith-woocommerce-wishlist' ) ?><br/>
					<?php echo sprintf( '<a target="_blank" href="%s">%s</a>', esc_url( add_query_arg( array( 'preview_yith_wcwl_promotion_email' => 'true', 'template' => 'plain', 'product_id' => $product_id ), wp_nonce_url( admin_url(), 'preview-promotion-mail' ) ) ), __( 'View preview', 'yith-woocommerce-wishlist' ) ) ?>
				</p>
			</td>
		</tr>
		<?php if( ! empty( $coupons ) ): ?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'Use coupon', 'yith-woocommerce-wishlist' ) ?></th>
			<td class="forminp forminp-textarea">
				<label for="yith_wcwl_promotional_email_use_coupon">
					<input type="checkbox" name="yith_wcwl_promotional_email_use_coupon" id="yith_wcwl_promotional_email_use_coupon" value="yes" checked="checked"/>
					<?php _e( 'Select whether you want to use a coupon in your email or not (<code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{coupon_value}</code> it won\'t be replaced if you don\'t check this option)', 'yith-woocommerce-wishlist' ) ?>
				</label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'Email related coupon', 'yith-woocommerce-wishlist' ) ?></th>
			<td class="forminp forminp-textarea">
				<select name="yith_wcwl_promotional_email_coupon" class="wc-enhanced-select-nostd" data-placeholder="<?php _e( 'Select a coupon', 'yith-woocommerce-wishlist' ) ?>" data-multiple="true">
					<?php foreach( $coupons as $coupon ): ?>
						<option value="<?php echo $coupon->post_title ?>"><?php echo $coupon->post_title ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php endif; ?>
		</tbody>
	</table>

	<?php wp_nonce_field( 'send_promotion_email_action', 'send_promotion_email' ) ?>
	<input type="submit" value="<?php _e( 'Send emails', 'yith-woocommerce-wishlist' ) ?>" class="button-primary"/>
	<a href="<?php echo esc_url( add_query_arg( array( 'tab' => $current_tab, 'page' => $_REQUEST['page'] ), admin_url( 'admin.php' ) ) ) ?>" class="button-secondary"><?php _e( 'Back', 'yith-woocommerce-wishlist' )?></a>
</form>

<script>
	jQuery( document).ready( function($){
		$('#yith_wcwl_promotional_email_use_coupon').change(function(){
			var t = $(this);
			if( t.is( ':checked' ) ){
				t.parents( 'tr').next().show();
			}
			else{
				t.parents( 'tr').next().hide();
			}
		}).change();
	} );
</script>