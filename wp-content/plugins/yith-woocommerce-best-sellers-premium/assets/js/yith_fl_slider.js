jQuery( function ( $, window, document ) {
    $.fn.yith_fl_slider = function ( options ) {

        return $( this ).each( function () {
            self.opts = {};

            var slider    = $( this ),
                container = null,
                items     = null,
                title     = null,
                right     = null,
                left      = null,
                number    = 0;

            var defaults = {
                title_class: 'yith-wcbsl-bestsellers-slider-title',
                container_class: 'yith-wcbsl-bestsellers-slider-container',
                item_class: 'yith-wcbsl-bestseller-in-slider-wrapper',
                right_class: 'yith-wcbsl-bestseller-slider-right',
                left_class: 'yith-wcbsl-bestseller-slider-left',
                items_number: 100
            };

            self.init = function () {
                self.opts = $.extend( {}, defaults, options );

                _set_elements();
                _set_buttons_actions();
                _start_animations();
            };

            var _set_elements        = function () {
                    container = slider.find( '.' + self.opts.container_class );
                    items     = slider.find( '.' + self.opts.item_class );
                    title     = slider.find( '.' + self.opts.title_class );
                    right     = slider.find( '.' + self.opts.right_class );
                    left      = slider.find( '.' + self.opts.left_class );

                    number = items.length;

                    var cont_w = items.first().width() * number + number * 20 + 10;
                    container.css( { width: cont_w + 'px' } );

                },
                _set_buttons_actions = function () {
                    right.on( 'mousedown', function () {
                        _move_items( 20 );
                    } ).on( 'mouseout mouseup', function () {
                        _move_items();
                    } );

                    left.on( 'mousedown', function () {
                        _move_items( -20 );
                    } ).on( 'mouseout mouseup', function () {
                        _move_items();
                    } );
                },
                _start_animations    = function () {
                    title.fadeOut( 3000 );
                    setTimeout( _move_items, 6000 );

                    //container.css( { left: '100%' } );
                    container.css( { left: '0' } );

                    container.on( 'mouseover', function () {
                        container.stop();
                    } );
                    container.on( 'mouseout', function () {
                        _move_items();
                    } );
                },
                _move_items          = function ( speed ) {
                    speed = speed || 1;

                    var w    = container.outerWidth(),
                        c    = container.position().left,
                        time = ((w + c) * 40),
                        complete_func = _show_title;

                    if ( speed < 0 ) {
                        time = (c * (-40));
                        w     = 0;
                        speed = speed * (-1);
                        complete_func = {};
                    }

                    container.stop();
                    container.animate( { left: '-' + w + 'px' }, {
                        duration: time / speed,
                        easing: 'linear',
                        complete: complete_func
                    } );
                }, _show_title       = function () {
                    title.fadeIn( 1000, _start_animations );
                };


            self.init();
        } );
    }
} );