jQuery(document).ready(function ($) {

    if (window.location.hash) {

        // Vars
        var tab = window.location.hash.replace('#', '');
        var tab_content = tab.replace('tab-', '');

        // Tabs
        $('li.description_tab').removeClass('active');
        $('li.' + tab_content + '_tab').addClass('active');

        // Tabs content
        $('#tab-description').hide();
        $('#' + tab).show();
    }

});