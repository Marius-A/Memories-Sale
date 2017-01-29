/**
 * frontend.js
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Frequently Bought Together Premium
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    "use strict";

    var items       = $( '.yith-wfbt-items'),
        checkbox    = items.find( 'input'),
        total_wrap  = $( '.yith-wfbt-submit-block' ).find( '.total_price' ),
        total_html  = total_wrap.find( '.amount' ),
        total       = parseFloat( total_wrap.data('total')),
        table       = $('.yith-wfbt-images');

    // return total with currency
    var get_total = function( total ) {

        var html = '';

        if ( yith_wfbt.currency_pos === 'left' ) {
            html = yith_wfbt.currency_symbol + total.toFixed(2);
        }
        else if ( yith_wfbt.currency_pos === 'left_space' ) {
            html = yith_wfbt.currency_symbol + ' ' + total.toFixed(2);
        }
        else if ( yith_wfbt.currency_pos === 'right' ) {
            html = total.toFixed(2) + yith_wfbt.currency_symbol;
        }
        else if ( yith_wfbt.currency_pos === 'right_space' ) {
            html = total.toFixed(2) + ' ' + yith_wfbt.currency_symbol;
        }

        return html;
    };

    checkbox.on( 'change', function(){

        var t        = $(this),
            checked  = items.find('input:checked'),
            id       = $(this).attr( 'id' ),
            thumb    = $( '.yith-wfbt-images td.image-td[data-rel="' + id + '"]'),
            total    = 0,
            to_show  = [];

        t.parents('li').toggleClass( 'is_not_checked' );

        // show only necessary
        checked.each(function(i){
            to_show[ i ] = this.id;
            total += parseFloat( $(this).data('price') );
        });

        // show thumbnails
        thumb.fadeToggle();  // image

        // manage plus for first
        if ( to_show.length == 1 ) {
            $( 'td.image_plus').fadeOut();
        } else if ( to_show.length == $('td.image-td').length ) {
            $( 'td.image_plus').fadeIn();
        } else if ( to_show[0] == id || to_show[0] == thumb.next('td.image_plus').data('rel') ) {
            thumb.next('td.image_plus').fadeToggle();
        } else {
            thumb.prev('td.image_plus').fadeToggle();
        }

        // change total
        total_html.html( get_total( total ) );


        var num_active      = checked.length,
            block_submit    = $( '.yith-wfbt-submit-block' ),
            label           = '',
            label_total     = '';

        // change button label
        if( num_active == 0 ){
            block_submit.hide();
        }
        else {
            block_submit.show();

            if( num_active == 1 ){
                label       = yith_wfbt.label_single;
                label_total = yith_wfbt.total_single;
            }
            else if( num_active == 2 ) {
                label       = yith_wfbt.label_double;
                label_total = yith_wfbt.total_double;
            }
            else if( num_active == 3 ) {
                label       = yith_wfbt.label_three;
                label_total = yith_wfbt.total_three;
            }
            else {
                label       = yith_wfbt.label_multi;
                label_total = yith_wfbt.total_multi;
            }

            block_submit.find( 'span.total_price_label' ).html( label_total );
            block_submit.find( 'input').val( label );
        }

    });


    var single_variation = $( 'form.variations_form.cart' ).find( '.single_variation_wrap' ),
        form_bought      = $( 'form.yith-wfbt-form' );


    single_variation.on( 'show_variation', function( ev, data ){

        if( ! data.is_in_stock ){
            return;
        }

        var price           = data.display_price,
            price_html      = data.price_html,
            attributes      = data.attributes,
            product         = $( '.yith-wfbt-items' ).find( 'label[for="offeringID_0"]' ),
            product_input   = product.find( 'input[type="checkbox"]' );

        if( data.variation_id == product_input.val() ){
            return;
        }

        form_bought.block({
            message: null,
            overlayCSS: {
                background: '#fff url(' + yith_wfbt.loader + ') no-repeat center',
                opacity: 0.6
            }
        });

        $.ajax({
            type: 'POST',
            url: yith_wfbt.ajaxurl,
            data: {
                action      : 'yith_update_variation_product',
                product_id  : data.variation_id,
                context     : 'frontend'
            },
            dataType: 'json',
            success: function( res ){

                // change image
                $( '.yith-wfbt-images' ).find( 'td[data-rel="offeringID_0"]' ).html( res.image );

                // change price
                if( price_html != '' ) {

                    var old_price = parseFloat( product_input.data('price') );

                    product.find( 'span.price' ).replaceWith( price_html );

                    product_input.data( 'price', parseFloat(price) );

                    // update total
                    if( product_input.is( ':checked' ) ) {
                        total = ( total - old_price ) + price;
                        total_html.html( get_total( total ) );
                    }
                }

                // change attributes
                product.find( 'span.product-attributes').html( res.attributes );

                // change val of input
                product_input.val( data.variation_id );

                form_bought.unblock();
            }
        });

    });

    /********************
     * SLIDER SHORTCODE
     *******************/

    var slider = $(document).find( '.yith-wfbt-products-list' ),
        nav    = slider.next( '.yith-wfbt-slider-nav' );

    if( slider.length ) {

        slider.owlCarousel({
            items: yith_wfbt.visible_elem,
            loop: true,
            dots: false
        });

        if( nav.length ) {
            nav.find('.yith-wfbt-nav-prev').click(function () {
                slider.trigger('prev.owl.carousel');
            });

            nav.find('.yith-wfbt-nav-next').click(function () {
                slider.trigger('next.owl.carousel');
            })
        }
    }
});