<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

return array(
    'mandrill' => array(
        'review_reminder_mandrill_section_title' => array(
            'name' => __( 'Mandrill Settings', 'yith-woocommerce-review-reminder' ),
            'type' => 'title',
            'desc' => '',
            'id'   => 'ywrr_mandrill_settings_title',
        ),
        'review_reminder_mandrill_enable'        => array(
            'name'    => __( 'Enable Mandrill', 'yith-woocommerce-review-reminder' ),
            'type'    => 'checkbox',
            'desc'    => __( 'Use Mandrill to send emails', 'yith-woocommerce-review-reminder' ),
            'id'      => 'ywrr_mandrill_enable',
            'default' => 'no',
        ),
        'review_reminder_mandrill_apikey'        => array(
            'name'    => __( 'Mandrill API Key', 'yith-woocommerce-review-reminder' ),
            'type'    => 'text',
            'desc'    => '',
            'id'      => 'ywrr_mandrill_apikey',
            'default' => '',
            'css'     => 'width: 400px;',
        ),
        'review_reminder_mandrill_section_end'   => array(
            'type' => 'sectionend',
            'id'   => 'ywrr_mandrill_settings_end'
        ),
    )
);