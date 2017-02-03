jQuery(document).ready(function($) {
    $('.xoo-dialog-close').on('click', function() {
        $('.xoo-wishlist-dialog').slideUp('slow');
    })

    function xoo_ajax_add_wishlist(product_id){
        $.ajax({
                url: xoo_data.adminurl,
                type: 'POST',
                data: {
                    action: 'wishlist_cookie',
                    xoo_product_id: product_id
                },
            })
    }
    function xoo_ajax_remove_wishlist(product_id){
         $.ajax({
                url: xoo_data.adminurl,
                type: 'POST',
                data: {
                    action: 'wishlist_cookie',
                    xoo_remove_id: product_id
                },
            })
    }

    $("#xoo-wishlist-" + xoo_data.button_id).addClass("active");

    $('.xoo_wishlist_add').on('click', function() {
        var toggle = $(this).hasClass('active');
        var product_id = $(this).attr('post-id');
        if (toggle == false) {
            $(this).addClass('active');
            xoo_ajax_add_wishlist(product_id);
            $('.xoo-wishlist-dialog').slideDown();
            toggle = true;
        }
         else if (toggle == true) {
            $(this).removeClass('active');
           xoo_ajax_remove_wishlist(product_id);
            toggle = false;
        }
    })

    $('.xoo_wishlist_remove').on('click', function() {
        var product_id = $(this).attr('remove-id');
        var parent_height = $(this).parent().height();
        if ($(this).hasClass('xoo-wishlist-undo')) {
            $(this).siblings().fadeIn();
            xoo_ajax_add_wishlist(product_id);
            $(this).removeClass('xoo-wishlist-undo');

            $(this).children().remove();
            $(this).addClass('icon-cross xoo-wishlist-remove-cross');
             
        } else {
            $(this).siblings().fadeOut('fast');
           
            $(this).parent().css('height',parent_height);
            $(this).addClass('xoo-wishlist-undo');
            $('.xoo-wishlist-undo').css('top',parent_height/3);
            $(this).removeClass('icon-cross xoo-wishlist-remove-cross');
            $(this).html('<div class ="xoo-wishlist-undo-icon icon-undo"><br>Undo?</div>');
            xoo_ajax_remove_wishlist(product_id);
        }
    })
})