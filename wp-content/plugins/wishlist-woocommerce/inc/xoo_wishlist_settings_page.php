 <?php if (!defined('ABSPATH')) {
	return;
}
?>

<h1>Wishlist WooCommerce Settings</h1>
<hr>
<h3> Use shortcode [xoo_wishlist] to display products wishlist.</h3>
<form method="post" action="options.php">
<?php settings_fields('xoo-wishlist-group'); ?>
<?php do_settings_sections('xoo_wishlist'); ?>
<?php submit_button(); ?>
</form>