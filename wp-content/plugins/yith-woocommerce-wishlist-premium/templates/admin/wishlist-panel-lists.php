<h3><?php _e( 'Users\' wishlists', 'yith-woocommerce-wishlist' ) ?></h3>

<?php $wishlist_table->views() ?>

<form id="wishlist-filter" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
	<input type="hidden" name="tab" value="<?php echo $current_tab ?>" />
    <?php $wishlist_table->search_box( 'Search by user', 'wishlist_user' ) ?>
    <?php $wishlist_table->display() ?>
</form>