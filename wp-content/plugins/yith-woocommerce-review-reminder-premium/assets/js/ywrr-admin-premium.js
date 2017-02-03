var array_unique_noempty, element_box;

jQuery(function ($) {

    $('body')
        .on('click', 'button.do-send-email', function () {
            if (window.confirm(ywrr_admin.do_send_email)) {

                var items_to_review = {},
                    container = $(this).parent().parent(),
                    title = container.find('.ywrr-send-title'),
                    date = container.find('.ywrr-send-date'),
                    result = container.find('.ywrr-send-result'),
                    order_id = (!ywrr_admin.is_order_page) ? ywrr_admin.post_id : $(this).parent().find('.ywrr-order-id').val(),
                    order_date = (!ywrr_admin.is_order_page) ? ywrr_admin.order_date : $(this).parent().find('.ywrr-order-date').val();


                result.show();
                result.removeClass('send-fail send-success');
                result.addClass('send-progress');

                result.html(ywrr_admin.please_wait);

                $('#order_line_items tr').each(function (index, item) {
                    if ($(this).find('input[type=checkbox]').attr('checked') || $(this).hasClass('selected')) {
                        items_to_review [$(this).data('order_item_id')] = $(this).data('order_item_id');
                    }
                });

                var data = {
                    action         : 'ywrr_send_request_mail',
                    order_id       : order_id,
                    order_date     : order_date,
                    items_to_review: JSON.stringify(items_to_review, null, '')
                };

                $.post(ywrr_admin.ajax_url, data, function (response) {

                    result.removeClass('send-progress');

                    if (response === true) {

                        result.addClass('send-success');
                        result.html(ywrr_admin.after_send_email);
                        date.html('');
                        title.hide();

                    } else {

                        result.addClass('send-fail');
                        result.html(response.error);

                    }

                    setTimeout(function () {
                        result.fadeOut('fast');
                    }, 1500);

                });

            }
        })
        .on('click', 'button.do-reschedule-email', function () {
            if (window.confirm(ywrr_admin.do_reschedule_email)) {

                var items_to_review = {},
                    container = $(this).parent().parent(),
                    result = container.find('.ywrr-send-result'),
                    date = container.find('.ywrr-send-date'),
                    title = container.find('.ywrr-send-title'),
                    order_id = (!ywrr_admin.is_order_page) ? ywrr_admin.post_id : $(this).parent().find('.ywrr-order-id').val();


                result.show();
                result.removeClass('send-fail send-success');
                result.addClass('send-progress');
                result.html(ywrr_admin.please_wait);


                $('#order_line_items tr').each(function (index, item) {
                    if ($(this).find('input[type=checkbox]').attr('checked') || $(this).hasClass('selected')) {
                        items_to_review [$(this).data('order_item_id')] = $(this).data('order_item_id');
                    }
                });


                var data = {
                    action         : 'ywrr_reschedule_mail',
                    order_id       : order_id,
                    items_to_review: JSON.stringify(items_to_review, null, '')
                };

                $.post(ywrr_admin.ajax_url, data, function (response) {

                    result.removeClass('send-progress');

                    if (response.success === true) {

                        result.addClass('send-success');
                        result.html(ywrr_admin.after_reschedule_email);
                        date.html(response.schedule);
                        title.show();

                    } else {

                        result.addClass('send-fail');
                        result.html(response.error);
                        date.html('');
                        title.hide();
                    }

                    setTimeout(function () {
                        result.fadeOut('fast');
                    }, 1500);

                });

            }
        })
        .on('click', 'button.do-cancel-email', function () {
            if (window.confirm(ywrr_admin.do_cancel_email)) {

                var container = $(this).parent().parent(),
                    result = container.find('.ywrr-send-result'),
                    date = container.find('.ywrr-send-date'),
                    title = container.find('.ywrr-send-title'),
                    order_id = (!ywrr_admin.is_order_page) ? ywrr_admin.post_id : $(this).parent().find('.ywrr-order-id').val();


                result.show();
                result.removeClass('send-fail send-success');
                result.addClass('send-progress');
                result.html(ywrr_admin.please_wait);

                var data = {
                    action  : 'ywrr_cancel_mail',
                    order_id: order_id
                };

                $.post(ywrr_admin.ajax_url, data, function (response) {

                    result.removeClass('send-progress');

                    if (response === true) {

                        result.addClass('send-success');
                        result.html(ywrr_admin.after_cancel_email);

                    } else if (response === 'notfound') {

                        result.addClass('send-fail');
                        result.html(ywrr_admin.not_found_cancel);

                    } else {

                        result.addClass('send-fail');
                        result.html(response.error);

                    }

                    date.html('');
                    title.hide();

                    setTimeout(function () {
                        result.fadeOut('fast');
                    }, 1500);

                });

            }
        })
        .on('click', 'button.ywrr-schedule-email', function () {

            var result = $(this).next();

            result.show();
            result.removeClass('send-progress send-fail send-success');

            var data = {
                action: 'ywrr_mass_schedule'
            };

            result.addClass('send-progress');
            result.html(ywrr_admin.please_wait);

            $.post(ywrr_admin.ajax_url, data, function (response) {

                result.removeClass('send-progress');

                if (response.success === true) {

                    result.addClass('send-success');
                    result.html(response.message);

                } else {

                    result.addClass('send-fail');
                    result.html(response.error);

                }

            });


        });

    $(document).ready(function ($) {

        $('select#ywrr_request_type').change(function () {

            var rows = $(this).parent().parent().nextAll('*:lt(2)'),
                option = $('option:selected', this).val();

            switch (option) {
                case 'selection':
                    rows.show();
                    break;
                default:
                    rows.hide();
            }

        }).change();

        $('select#ywrr_mail_item_link').change(function () {

            var option = $('option:selected', this).val(),
                ywrr_mail_item_link_hash = $('#ywrr_mail_item_link_hash').parent().parent();

            switch (option) {
                case 'custom':
                    ywrr_mail_item_link_hash.show();
                    break;
                default:
                    ywrr_mail_item_link_hash.hide();
            }

        }).change();

        $('#ywrr_mail_template_enable').change(function () {

            if ($(this).is(':checked')) {

                $('#ywrr_mail_template').val('base').prop("disabled", true);

            } else {

                $('#ywrr_mail_template').prop("disabled", false);

            }

        }).change();

        $('#ywrr_enable_analytics').change(function () {

            var rows = $(this).parent().parent().parent().parent().nextAll('*:lt(5)');

            if ($(this).is(':checked')) {

                rows.show();
                $('#ywrr_campaign_source').prop("required", true);
                $('#ywrr_campaign_medium').prop("required", true);
                $('#ywrr_campaign_name').prop("required", true);

            } else {

                rows.hide();
                $('#ywrr_campaign_source').prop("required", false);
                $('#ywrr_campaign_medium').prop("required", false);
                $('#ywrr_campaign_name').prop("required", false);

            }

        }).change();

        $('#ywrr_mail_reschedule').change(function () {

            var rows = $(this).parent().parent().nextAll('*:lt(1)');

            if ($(this).is(':checked')) {

                rows.show();

            } else {

                rows.hide();

            }

        }).change();

        element_box.init();

    });

    array_unique_noempty = function (array) {
        var out = [];

        $.each(array, function (key, val) {
            val = $.trim(val);

            if (val && $.inArray(val, out) === -1) {
                out.push(val);
            }
        });

        return out;
    };

    element_box = {
        clean: function (tags) {

            tags = tags.replace(/\s*,\s*/g, ',').replace(/,+/g, ',').replace(/[,\s]+$/, '').replace(/^[,\s]+/, '');

            return tags;
        },

        parseTags: function (el) {
            var id = el.id,
                num = id.split('-check-num-')[1],
                element_box = $(el).closest('.ywcc-checklist-div'),
                values = element_box.find('.ywcc-values'),
                current_values = values.val().split(','),
                new_elements = [];

            delete current_values[num];

            $.each(current_values, function (key, val) {
                val = $.trim(val);
                if (val) {
                    new_elements.push(val);
                }
            });

            values.val(this.clean(new_elements.join(',')));

            this.quickClicks(element_box);
            return false;
        },

        quickClicks: function (el) {

            var values = $('.ywcc-values', el),
                values_list = $('.ywcc-value-list ul', el),

                id = $(el).attr('id'),
                current_values;

            if (!values.length)
                return;

            current_values = values.val().split(',');
            values_list.empty();

            $.each(current_values, function (key, val) {

                var item, xbutton;

                val = $.trim(val);

                if (!val)
                    return;

                item = $('<li class="select2-search-choice" />');
                xbutton = $('<a id="' + id + '-check-num-' + key + '" class="select2-search-choice-close" tabindex="0"></a>');

                xbutton.on('click keypress', function (e) {

                    if (e.type === 'click' || e.keyCode === 13) {

                        if (e.keyCode === 13) {
                            $(this).closest('.ywcc-checklist-div').find('input.ywcc-insert').focus();
                        }

                        element_box.parseTags(this);
                    }

                });

                item.prepend('<div><div class="selected-option" data-id="' + val + '">' + val + '</div></div>').prepend(xbutton);

                values_list.append(item);

            });
        },

        flushTags: function (el, a, f) {
            var current_values,
                new_values,
                text,
                values = $('.ywcc-values', el),
                add_new = $('input.ywcc-insert', el);

            a = a || false;

            text = a ? $(a).text() : add_new.val();

            if ('undefined' == typeof( text )) {
                return false;
            }

            current_values = values.val();
            new_values = current_values ? current_values + ',' + text : text;
            new_values = this.clean(new_values);
            new_values = array_unique_noempty(new_values.split(',')).join(',');
            values.val(new_values);

            this.quickClicks(el);

            if (!a)
                add_new.val('');
            if ('undefined' == typeof( f ))
                add_new.focus();

            return false;

        },

        init: function () {
            var ajax_div = $('.ywcc-checklist-ajax');

            $('.ywcc-checklist-div').each(function () {
                element_box.quickClicks(this);
            });

            $('input.ywcc-insert', ajax_div).keyup(function (e) {
                if (13 == e.which) {
                    element_box.flushTags($(this).closest('.ywcc-checklist-div'));
                    return false;
                }
            }).keypress(function (e) {
                if (13 == e.which) {
                    e.preventDefault();
                    return false;
                }
            });


        }
    };

});

