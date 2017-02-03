jQuery(document).ready(function($) {

    $('input[name="xoo_wishlist_color_icon"]').on('keyup', function() {
        var wishlist_color = $(this).val();
        if (wishlist_color == '') {
            $('.wishlist-icon').css('color', 'red');
        } else {
            $('.wishlist-icon').css('color', wishlist_color);
        }
    })

    function xoo_rating_check() {
        if ($('select[name="xoo_wishlist_position_icon"]').val() == 'woocommerce_after_shop_loop_item_title+6') {
            $('.xoo_wishlist_position_message').html('<b>Make sure product rating is on.</b>');
        } else {
            $('.xoo_wishlist_position_message').html('');
        }
    }
    xoo_rating_check();
    $('select[name="xoo_wishlist_position_icon"]').change(function() {
        xoo_rating_check()
    });

     $(".xoo-wishlist-pages-info").hover(function(){
        $('.xoo-pages-info').fadeIn('slow');
        }, function(){
        $('.xoo-pages-info').css("display", "none");
    });


})