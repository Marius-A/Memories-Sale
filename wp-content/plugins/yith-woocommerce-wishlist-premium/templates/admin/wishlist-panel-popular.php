<h3><?php echo $title ?></h3>
<form id="popular-filter" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <input type="hidden" name="tab" value="<?php echo $current_tab ?>" />

	<?php if( isset( $product_id ) ): ?>
		<input type="hidden" name="action" value="show_users" />
		<input type="hidden" name="product_id" value="<?php echo $product_id ?>" />
	<?php endif; ?>

    <?php $table->search_box( $search_text, 'product' ) ?>
    <?php $table->display() ?>
</form>