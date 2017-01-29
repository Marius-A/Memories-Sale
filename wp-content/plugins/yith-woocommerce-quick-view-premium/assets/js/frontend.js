/**
 * frontend.js
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Quick View
 * @version 1.0.0
 */

jQuery(document).ready(function($){
    "use strict";

    var buttons     = '',
        qv_modal    = '',
        qv_content  = '',
        qv_close    = '',
        qv_nav      = '',
        products_ids = [],
        center_modal = function() {

            var t = $(document).find( '.yith-quick-view .yith-wcqv-wrapper' ),
                window_w = $(window).width(),
                window_h = $(window).height(),
                width    = ( ( window_w - 60 ) > yith_qv.popup_size_width ) ? yith_qv.popup_size_width : ( window_w - 60 ),
                height   = ( ( window_h - 120 ) > yith_qv.popup_size_height ) ? yith_qv.popup_size_height : ( window_h - 120 );

            t.css({
                'left' : (( window_w/2 ) - ( width/2 )),
                'top' : (( window_h/2 ) - ( height/2 )),
                'width'     : width + 'px',
                'height'    : height + 'px'
            });
        };

    if( typeof yith_qv === 'undefined' ) {
        return;
    }

    /*==================
     * INIT PLUGIN
     ==================*/

    $.fn.yith_wcqv_init = function() {

        buttons     = $(document).find( '.yith-wcqv-button' );
        qv_modal    = yith_qv.type != 'yith-inline' ? $(document).find( '.yith-quick-view' ) : $(document).find( '.yith-quick-view' ).clone();
        qv_content  = qv_modal.find( '.yith-quick-view-content' );
        qv_close    = qv_modal.find( '.yith-quick-view-close' );
        qv_nav      = qv_modal.find( '.yith-quick-view-nav' );


        // build products id array
        $.each( buttons, function(){
            var product_id = $(this).data('product_id');
            if( $.inArray( product_id, products_ids ) == -1 ) {
                products_ids.push( product_id );
            }
        } );

        // nav event
        if( qv_nav.length ) {
            nav_ajax_call(qv_nav);
        }
        // close event
        close_modal_qv( qv_close );

        // responsive
        if( yith_qv.type != 'yith-inline' ) {
            center_modal();
            $( window ).on( 'resize', center_modal );
        }

        // off old event ( prevent multiple open )
        buttons.off( 'click' );

        // calculate position
        if( buttons.hasClass( 'inside-thumb' ) ) {
            imagesLoaded( qv_content, function(){
                button_position( buttons );
            });
        }

        $(document).on( 'click', '.yith-wcqv-button', function(e){

            var t           = $(this),
                data_type   = t.data('type'),
                product_id  = t.data( 'product_id' );

            if( ! product_id ) {
                return;
            }

            e.preventDefault();

            if( ! yith_qv.enable_loading ) {
                qv_loader( t );
            }

            // if is inline move modal
            if ( yith_qv.type == 'yith-inline' ) {

                var elem        = t.parents( yith_qv.main_product ),
                    last_elem   = ( elem.hasClass( 'last' ) ) ? elem : elem.nextUntil( '.first', '.last' );

                if( ! last_elem.length ){
                    last_elem = t.closest( '.products' ).find( yith_qv.main_product ).last();
                }


                if( last_elem.next( '.yith-quick-view' ).length ) {
                    // if in same row of li call qv_loader
                    qv_loader( qv_content );
                }
                else if ( qv_modal.hasClass('open') ) {
                    // if in another row close it and move
                    qv_modal.removeClass('open').removeClass('loading');

                    qv_modal.slideUp( 'slow', function(){
                        last_elem.after( qv_modal );

                        // ajax call
                        ajax_call( t, product_id );
                    });

                    return;
                }
                else {
                    // and move it
                    last_elem.after( qv_modal );
                }
            }
            else {
                // add loading effect
                $(document).trigger( 'qv_loading' );
            }

            ajax_call( t, product_id );

        });
    };

    /*=====================
    * MAIN BUTTON POSITION
     =======================*/

    var button_position = function( buttons ){

        var img_height = $(document).find( 'img.attachment-shop_catalog' ).height(),
            trigger_h = buttons.height();

        // add position
        buttons.css({
            'top'     : ( img_height - trigger_h ) / 2 + 'px'
        });
    };


    /*=================
    * LOADER FUNCTION
    ==================*/

    var qv_loader = function(t) {

        if ( typeof yith_qv.loader !== 'undefined'  ) {

            t.find('span, img').block({
                message   : null,
                overlayCSS: {
                    background: '#fff url(' + yith_qv.loader + ') no-repeat center',
                    opacity   : 0.5,
                    cursor    : 'none'
                }
            });

            $(document).on( 'qv_loader_stop', function(){
                t.find('span, img').unblock();
            });
        }
    };

    /*==============
     * NAVIGATION
     ==============*/

    var nav_ajax_call = function( nav ) {

        var a = nav.find( '> a' );

        // prevent multiple
        a.off( 'click' );

        a.on( 'click', function (e) {
            e.preventDefault();

            var t = $(this),
                product_id = t.data('product_id');

                qv_loader( qv_content );

            ajax_call( t, product_id )
        });
    };


    /*================
     * MAIN AJAX CALL
     ================*/

    var ajax_call = function( t, product_id ) {

        var current_index = $.inArray( product_id, products_ids ),
            prev_id      = products_ids[ current_index - 1 ],
            next_id      = products_ids[ current_index + 1 ],
            // create data for post
            data = {
                action: 'yith_load_product_quick_view',
                product_id: product_id,
                prev_product_id: prev_id,
                next_product_id: next_id,
                context: 'frontend'
            };

        $.ajax({
            url: yith_qv.ajaxurl,
            data: data,
            dataType: 'json',
            type: 'POST',
            success: function( data ) {

                qv_content.html( data.html );

                // Init images slider
                if( data.images_type == 'slider' ){
                    imagesLoaded( qv_content, function(){
                        qv_images_slider();
                    });
                }
                else {
                    thumb_change();
                }

                //scroll
                qv_content.find( 'div.summary' ).perfectScrollbar({
                    suppressScrollX : true
                });

                // quantity fields for WC 2.2
                if( yith_qv.is2_2 || yith_qv.increment_plugin ) {
                    qv_content.find('div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)').addClass('buttons_added').append('<input type="button" value="+" class="plus" />').prepend('<input type="button" value="-" class="minus" />');
                }

                // Variation Form
                var form_variation = qv_content.find( '.variations_form' );

                form_variation.wc_variation_form();
                form_variation.trigger( 'check_variations' );
                form_variation.trigger( 'reset_image' );

                if( typeof $.fn.yith_wccl !== 'undefined' ) {
                    // color e label free
                    form_variation.yith_wccl();
                }
                else if( typeof $.yith_wccl != 'undefined' && data.prod_attr ) {
                    // color e label premium
                    $.yith_wccl( data.prod_attr );
                }

                // Request a Quote Integration
                if( typeof $.fn.yith_ywraq_variations !== 'undefined' ) {
                    $.fn.yith_ywraq_variations();
                    qv_content.find( '[name|="variation_id"]').trigger('change');
                }

                // One click checkout integration
                var wocc_wrapper = qv_content.find( '.yith-wocc-wrapper' );
                if( wocc_wrapper.length && typeof $.fn.yith_wocc_init != 'undefined' ) {
                    wocc_wrapper.yith_wocc_init();
                }

                // Init prettyPhoto
                if( typeof $.fn.prettyPhoto !== 'undefined' ) {
                    qv_content.find("a[data-rel^='prettyPhoto']").prettyPhoto({
                        hook: 'data-rel',
                        social_tools: false,
                        theme: 'pp_woocommerce',
                        horizontal_padding: 20,
                        opacity: 0.8,
                        deeplinking: false
                    });
                }

                // add to cart
                if( yith_qv.ajaxcart ) {
                    add_to_cart_ajax( data.product_link, qv_content );
                }

                // change thumb with variation
                var single_variation = form_variation.find( '.single_variation_wrap' );
                change_variation_thumb( single_variation );

                // Contact Form 7 Integration
                if( typeof $.fn.ajaxForm !== 'undefined' && typeof $.fn.wpcf7InitForm !== 'undefined' ) {
                    $.fn.ajaxForm({
                        delegation: true,
                        target    : '#output'
                    });

                    qv_content.find('div.wpcf7 > form').wpcf7InitForm();
                }

                if( qv_nav.length ) {

                    var next = qv_nav.find( '.yith-wcqv-next' ),
                        prev = qv_nav.find( '.yith-wcqv-prev' );

                    if ( data.prev_product ) {
                        //add title and thumb
                        prev.find( 'div' ).html( data.prev_product_preview );
                        //show prev
                        prev.data( 'product_id', data.prev_product ).css({ 'display' : 'block' });
                    }
                    else {
                        prev.css({ 'display' : 'none' });
                    }
                    if ( data.next_product ) {
                        //add title and thumb
                        next.find( 'div' ).html( data.next_product_preview );
                        //show prev
                        next.data( 'product_id', data.next_product ).css({ 'display' : 'block' });
                    }
                    else {
                        next.css({ 'display' : 'none' });
                    }
                }

                if( ! qv_modal.hasClass( 'open' ) ) {
                    qv_modal.removeClass('loading').addClass('open');
                    if( yith_qv.type == 'yith-inline' ) {
                        qv_modal.slideDown('slow');
                    }
                    else {
                        $( 'html' ).addClass( 'yith-quick-view-is-open' );
                    }
                }

                // stop loader
                $(document).trigger( 'qv_loader_stop' );

            }
        });
    };


    /*======================
    * SLIDER IN QUICK VIEW
    =======================*/

    var qv_images_slider = function(){

        var slider_wrapper = qv_content.find( '.yith-quick-view-images-slider'),
            slides         = slider_wrapper.find( '.yith-quick-view-slides' );

        slides.owlCarousel({
            items   : 1,
            dots    : false
        });

        slider_wrapper.on('click', '.es-nav-next', function(){
            slides.trigger( 'next.owl.carousel' );
        });
        slider_wrapper.on('click', '.es-nav-prev', function(){
            slides.trigger( 'prev.owl.carousel' );
        });

    };

    /*=============================
    * CLASSIC STYLE. THUMB CHANGE
     ==============================*/

    var thumb_change = function(){

        $( '.yith-quick-view-single-thumb' ).on('click',  function(){

            $(this).siblings().removeClass('active');

            var main = $(this).parents( '.images' ),
                link = main.find( '.woocommerce-main-image' ),
                big = main.find( 'img.wp-post-image' ),
                attachment = $(this).data('img'),
                attachment_href = $(this).data('href');

            if( ! big.length ){
                big = main.find( 'img.attachment-quick_view_image_size' );
                link = big.closest('a');
            }

            big.attr( 'src', attachment )
                .attr('srcset', attachment)
                .attr('src-orig', attachment);

            link.attr( 'href', attachment_href );
            $(this).addClass('active');

            $(document).trigger('yith_wcqv_change_thumb')
        });
    };

    /*===================
     * CLOSE QUICK VIEW
     ===================*/

    var close_modal_qv = function( close ) {

        // prevent multiple
        close.off('click');

        // close box by click close button
        close.on('click', function (e) {
            e.preventDefault();
            close_qv();
        });


        if ( yith_qv.type != 'yith-inline' ) {
            // close box with esc key
            $(document).keyup(function (e) {
                if (e.keyCode === 27)
                    close_qv();
            });
            // close box by click overlay
            $( '.yith-quick-view-overlay' ).on( 'click', function(e){
                if( ! qv_modal.hasClass('loading') )
                    close_qv();
            });
        }

        var close_qv = function() {
            qv_modal.removeClass('open').removeClass('loading');

            if ( yith_qv.type != 'yith-inline' ) {
                $( 'html' ).removeClass( 'yith-quick-view-is-open' );
                setTimeout(function () {
                    empty_qv();
                }, 1000);
            }
            else {
                qv_modal.slideUp( 'slow', function(){
                    empty_qv();
                });
            }
        };

        var empty_qv = function(){
            qv_content.html('');
            $(document).trigger('qv_is_closed');
        }
    };

    /*===============================================
      * INFINITE SCROLLING AND AJAX NAV COMPATIBILITY
    ================================================*/

    $( document ).on( 'yith_infs_adding_elem yith-wcan-ajax-filtered', function(){
        // RESTART
        $.fn.yith_wcqv_init();
    });

    /*===================
    * ADD TO CART IN AJAX
    ====================*/

    var add_to_cart_ajax = function( product_url, cont ) {

        cont.find( 'form.cart' ).on('submit', function (e) {
            e.preventDefault();

            var form    = $(this),
                button  = form.find( 'button' );

            if( typeof yith_qv.loader !== 'undefined' ){

                button.block({
                    message   : null,
                    overlayCSS: {
                        background: '#fff url(' + yith_qv.loader + ') no-repeat center',
                        opacity   : 0.5,
                        cursor    : 'none'
                    }
                });
            }

            $.post( product_url, form.serialize() + '&_wp_http_referer=' + product_url, function (result) {

                if( yith_qv.redirect_checkout ){
                    window.location.href = yith_qv.checkout_url;
                        return;
                }

                var message         = $( result ).find( 'div.woocommerce-message' ), // get standard message
                    cart_dropdown   = $( result ).find( '#header .yit_cart_widget'),
                    summary         = cont.find( 'div.summary');

                if( ! message.length ) {
                    // if same gone wrong get error
                    message = $( result ).find( 'ul.woocommerce-error' );
                }

                // update dropdown cart
                $( '#header' ).find( '.yit_cart_widget' ).replaceWith( cart_dropdown );

                // update fragments
                if ( typeof wc_cart_fragments_params !== 'undefined' ) {

                    /* Storage Handling */
                    var $supports_html5_storage;
                    try {
                        $supports_html5_storage = ( 'sessionStorage' in window && window.sessionStorage !== null );

                        window.sessionStorage.setItem( 'wc', 'test' );
                        window.sessionStorage.removeItem( 'wc' );
                    } catch( err ) {
                        $supports_html5_storage = false;
                    }

                    $.ajax({ url: wc_cart_fragments_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'get_refreshed_fragments' ),
                        type: 'POST',
                        success: function( data ) {
                            if ( data && data.fragments ) {
                                $.each( data.fragments, function( key, value ) {
                                    $( key ).replaceWith( value );
                                });

                                if ( $supports_html5_storage ) {
                                    sessionStorage.setItem( wc_cart_fragments_params.fragment_name, JSON.stringify( data.fragments ) );
                                    sessionStorage.setItem( 'wc_cart_hash', data.cart_hash );
                                }

                                $( document.body ).trigger( 'wc_fragments_refreshed' );
                            }
                        }
                    });
                }

                summary.find( '.woocommerce-message, .woocommerce-error').remove();
                summary.prepend( message );

                if( typeof yith_qv.loader !== 'undefined' ){
                    button.unblock();
                }

                // Trigger event so themes can refresh other areas
                $('body').trigger( 'added_to_cart' );
            });
        });
    };

    /*********************************
     * ON VARIATION SELECT CHANGE THUMB
     * @param variation
     ***********************************/

    var change_variation_thumb = function( variation ){

        variation.on( 'show_variation', function( ev, data ){

            if( typeof data.attachment_id == 'undefined' ) {
                return;
            }

            // classic thumb
            var thumbs  = $( '.yith-quick-view-thumbs' ),
                single  = thumbs.find( '.yith-quick-view-single-thumb' ),
                current = thumbs.find( '.yith-quick-view-single-thumb[data-attachment_id="' + data.attachment_id + '"]' );

            single.removeClass( 'active' );

            if( current.length ) {
                current.addClass( 'active' );
            }

        });
    };

    /*************************************
     * ADD LOADING OVERLAY
     ************************************/

    $(document).on( 'qv_loading', function(){

        if( ! yith_qv.enable_loading ) {
            return false;
        }

        var qv_modal    = $(document).find( '.yith-quick-view' ),
            qv_overlay  = qv_modal.find( '.yith-quick-view-overlay');

        if( ! qv_modal.hasClass( 'loading' ) ) {
            qv_modal.addClass('loading');
        }

        if ( ! qv_overlay.find('p').length ) {
            var p = $('<p />').text( yith_qv.loading_text );
            qv_overlay.append( p );
        }
    });

    /***************************************
     * ZOOM MAGNIFIER
     **************************************/


    if( yith_qv.enable_zoom && typeof $.fn.yith_magnifier != 'undefined' ) {

        $(document).on('qv_loader_stop', function (ev) {

            if( typeof yith_magnifier_options == 'undefined' ){
                return false;
            }

            var yith_wcmg               = $('.yith-quick-view-content .images' ),
                yith_wcmg_zoom          = yith_wcmg.find('.yith_magnifier_zoom' ),
                yith_wcmg_image         = yith_wcmg.find('.yith_magnifier_zoom img.attachment-quick_view_image_size' ),
                yith_wcmg_default_zoom  = yith_wcmg.find('.yith_magnifier_zoom').attr('href'),
                yith_wcmg_default_image = yith_wcmg.find('.yith_magnifier_zoom img').attr('src');

            yith_wcmg_zoom.attr('href', yith_wcmg_default_zoom);
            yith_wcmg_image.attr('src', yith_wcmg_default_image);
            yith_wcmg_image.attr('srcset', yith_wcmg_default_image);
            yith_wcmg_image.attr('src-orig', yith_wcmg_default_image);

            if ( yith_wcmg.data('yith_magnifier') ) {
                yith_wcmg.yith_magnifier('destroy');
            }

            var $opts = {
                enableSlider: false,
                position: 'inside',
                elements: {
                    zoom: yith_wcmg_zoom,
                    zoomImage: yith_wcmg_image
                }
            };

            $opts = $.extend(true, {}, yith_magnifier_options, $opts );

            // reset prettyPhoto init
            $(document).off('click', 'a.pp_expand' );
            yith_wcmg.yith_magnifier($opts);

            $(document).off('yith_wcqv_change_thumb').on('yith_wcqv_change_thumb', function () {
                yith_wcmg.yith_magnifier('destroy');
                yith_wcmg.yith_magnifier($opts);
            });
        });
    }

    $(document).on('qv_loader_stop', function () {
        if ( yith_qv.type == 'yith-inline' ) {
            $('html, body').animate({
                scrollTop: $(".yith-quick-view").offset().top - 100
            }, 1000);
        }
    });

    /********************
     * DOUBLE TAP MOBILE
     ********************/
    $.fn.YitdoubleTapToGo = function () {
        this.each(function () {
            var t = false,
                p = $(this).closest('.product');

            $(document).on('qv_loader_stop', function(){
                p.removeClass('hover_mobile');
            });

            $(this).on( "touchstart", function (e) {
                // RESET ALL
                p.siblings().removeClass('hover_mobile');

                if( ! t || ! p.hasClass( 'hover_mobile' ) ) {
                    e.preventDefault();
                    p.addClass('hover_mobile');
                    t = true;
                }
            });
        });

        return this
    };
    // Double tap init
    if( yith_qv.ismobile ) {
        $( yith_qv.main_product_link ).YitdoubleTapToGo();
    }

    // START
    $.fn.yith_wcqv_init();
    
    // re init on WC Ajax Filters Update
    $(document).on('yith_wcqv_wcajaxnav_update', $.fn.yith_wcqv_init );
});