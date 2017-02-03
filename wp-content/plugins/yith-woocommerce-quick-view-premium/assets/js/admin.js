/**
 * admin.js
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Quick View
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    "use strict";

    var select          = $( document).find( '.yith-wcqv-chosen' ),
        icon_cont       = $( '#yith-wcqv-button-icon-container' ).parents( 'tr' ),
        label_cont      = $( '#yith-wcqv-button-label' ).parents( 'tr' );

    select.each( function() {
        $(this).select2({
            minimumResultsForSearch: Infinity
        })
    });


    $( '#yith-wcqv-button-type' ).on( 'change', function(){

        var selected = $(this).find( 'option:selected' ).val();

        if ( selected === 'icon' ) {
            icon_cont.show();
            label_cont.hide();
        }
        else if ( selected === 'button' ) {
            icon_cont.hide();
            label_cont.show();
        }
    }).trigger('change');

    var input = $(document).find('select, input');

    input.each( function(){
        var option = $(this).closest('tr'),
            parent,
            deps = $(this).data('deps'),
            deps_value = $(this).data('deps_value');

        if( typeof deps_value == 'undefined' || typeof deps == 'undefined' ){
            return;
        }

        parent = $(document).find( '#' + deps );

        parent.on('change', function(){

            if( deps_value == $(this).val() || ( deps_value == 'yes' && $(this).is(':checked') ) ) {
                option.show();
            }
            else{
                option.hide();
            }
        }).trigger('change');

    })

});