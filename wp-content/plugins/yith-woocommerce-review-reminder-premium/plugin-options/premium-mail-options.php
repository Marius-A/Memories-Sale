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

$wcet_args = array(
    'page' => 'yith_wcet_panel',
);
$wcet_url  = esc_url( add_query_arg( $wcet_args, admin_url( 'admin.php' ) ) );

$email_templates_enable = ( defined( 'YITH_WCET_PREMIUM' ) && YITH_WCET_PREMIUM ) ? array(
    'name'    => __( 'Use YITH WooCommerce Email Templates', 'yith-woocommerce-review-reminder' ),
    'type'    => 'checkbox',
    'desc'    => sprintf( __( 'By selecting this option, you will need to assign a template from %s', 'yith-woocommerce-review-reminder' ), '<a href="' . $wcet_url . '" target="_blank">' . __( 'YITH WooCommerce Email Templates', 'yith-woocommerce-review-reminder' ) . '</a>' ),
    'id'      => 'ywrr_mail_template_enable',
    'default' => 'no',
) : '';

$query_args              = array(
    'page' => isset( $_GET['page'] ) ? $_GET['page'] : '',
    'tab'  => 'howto',
);
$howto_url               = esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) );
$placeholders_text       = __( 'Allowed placeholders:', 'yith-woocommerce-review-reminder' );
$ph_reference_link       = ' - <a href="' . $howto_url . '" target="_blank">' . __( 'More info', 'yith-woocommerce-review-reminder' ) . '</a>';
$ph_site_title           = ' <b>{site_title}</b>';
$ph_customer_name        = ' <b>{customer_name}</b>';
$ph_customer_email       = ' <b>{customer_email}</b>';
$ph_order_id             = ' <b>{order_id}</b>';
$ph_order_date           = ' <b>{order_date}</b>';
$ph_order_date_completed = ' <b>{order_date_completed}</b>';
$ph_order_list           = ' <b>{order_list}</b>';
$ph_days_ago             = ' <b>{days_ago}</b>';
$ph_unsubscribe_link             = ' <b>{unsubscribe_link}</b>';

return array(
    'premium-mail' => array(
        'review_reminder_general_title'         => array(
            'name' => __( 'General Settings', 'yith-woocommerce-review-reminder' ),
            'type' => 'title',
            'desc' => '',
        ),
        'review_reminder_general_enable_plugin' => array(
            'name'    => __( 'Enable YITH WooCommerce Review Reminder', 'yith-woocommerce-review-reminder' ),
            'type'    => 'checkbox',
            'desc'    => '',
            'id'      => 'ywrr_enable_plugin',
            'default' => 'yes',
        ),
        'review_reminder_general_show_column' => array(
            'name'    => __( 'Show in Orders page', 'yith-woocommerce-review-reminder' ),
            'type'    => 'checkbox',
            'desc'    => __( 'Show Review Reminder Column in Orders page', 'yith-woocommerce-review-reminder' ),
            'id'      => 'ywrr_schedule_order_column',
            'default' => 'yes',
        ),
        'review_reminder_general_end'           => array(
            'type' => 'sectionend',
        ),

        'review_reminder_mail_section_title'   => array(
            'name' => __( 'Mail Settings', 'yith-woocommerce-review-reminder' ),
            'type' => 'title',
            'desc' => '',
        ),
        'review_reminder_mail_type'            => array(
            'name'    => __( 'Email type', 'yith-woocommerce-review-reminder' ),
            'type'    => 'select',
            'desc'    => __( 'Choose which format of email to send.', 'yith-woocommerce-review-reminder' ),
            'options' => array(
                'html'  => __( 'HTML', 'yith-woocommerce-review-reminder' ),
                'plain' => __( 'Plain text', 'yith-woocommerce-review-reminder' )
            ),
            'default' => 'html',
            'id'      => 'ywrr_mail_type'
        ),
        'review_reminder_mail_template_enable' => $email_templates_enable,
        'review_reminder_mail_template'        => array(
            'name'    => __( 'Mail template', 'yith-woocommerce-review-reminder' ),
            'type'    => 'ywrr-mailskin',
            'desc'    => '',
            'options' => array(
                'base'      => __( 'Woocommerce Template', 'yith-woocommerce-review-reminder' ),
                'premium-1' => __( 'Template 1', 'yith-woocommerce-review-reminder' ),
                'premium-2' => __( 'Template 2', 'yith-woocommerce-review-reminder' ),
                'premium-3' => __( 'Template 3', 'yith-woocommerce-review-reminder' ),
            ),
            'default' => 'base',
            'id'      => 'ywrr_mail_template'
        ),
        'review_reminder_mail_subject'         => array(
            'name'              => __( 'Email subject', 'yith-woocommerce-review-reminder' ),
            'type'              => 'text',
            'desc'              => $placeholders_text . $ph_site_title . $ph_reference_link,
            'id'                => 'ywrr_mail_subject',
            'default'           => __( '[{site_title}] Review recently purchased products', 'yith-woocommerce-review-reminder' ),
            'css'               => 'width: 400px;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),
        'review_reminder_mail_body'            => array(
            'name'              => __( 'Email content', 'yith-woocommerce-review-reminder' ),
            'type'              => 'yith-wc-textarea',
            'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_email . $ph_order_id . $ph_order_date . $ph_order_date_completed . $ph_order_list . $ph_days_ago . $ph_unsubscribe_link. $ph_reference_link,
            'id'                => 'ywrr_mail_body',
            'default'           => __( 'Hello {customer_name},
Thank you for purchasing items from the {site_title} shop!
We would love if you could help us and other customers by reviewing the products you recently purchased.
It only takes a minute and it would really help others by giving them an idea of your experience.
Click the link below for each product and review the product under the \'Reviews\' tab.

{order_list}

Much appreciated,

{site_title}.


{unsubscribe_link}', 'yith-woocommerce-review-reminder' ),
            'class'             => 'ywrr-textarea',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),
        'review_reminder_mail_item_link'       => array(
            'name'    => __( 'Set Destination', 'yith-woocommerce-review-reminder' ),
            'type'    => 'select',
            'desc'    => __( 'Set the destination you want to show in the email', 'yith-woocommerce-review-reminder' ),
            'options' => array(
                'product' => __( 'Product page', 'yith-woocommerce-review-reminder' ),
                'review'  => __( 'Default WooCommerce Reviews Tab', 'yith-woocommerce-review-reminder' ),
                'custom'  => __( 'Custom Anchor', 'yith-woocommerce-review-reminder' ),
            ),
            'default' => 'product',
            'id'      => 'ywrr_mail_item_link'
        ),
        'review_reminder_mail_item_link_hash'  => array(
            'name' => __( 'Set Custom Anchor', 'yith-woocommerce-review-reminder' ),
            'type' => 'text',
            'desc' => '',
            'id'   => 'ywrr_mail_item_link_hash',
        ),

        'review_reminder_mail_enable_analytics' => array(
            'name'    => __( 'Add Google Analytics to email links', 'yith-woocommerce-review-reminder' ),
            'type'    => 'checkbox',
            'desc'    => '',
            'id'      => 'ywrr_enable_analytics',
            'default' => 'no',
        ),
        'review_reminder_mail_campaign_source'  => array(
            'name'              => __( 'Campaign Source', 'yith-woocommerce-review-reminder' ),
            'type'              => 'text',
            'desc'              => __( 'Referrer: google, citysearch, newsletter4', 'yith-woocommerce-review-reminder' ),
            'id'                => 'ywrr_campaign_source',
            'css'               => 'width: 400px;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),
        'review_reminder_mail_campaign_medium'  => array(
            'name'              => __( 'Campaign Medium', 'yith-woocommerce-review-reminder' ),
            'type'              => 'text',
            'desc'              => __( 'Marketing medium: cpc, banner, email', 'yith-woocommerce-review-reminder' ),
            'id'                => 'ywrr_campaign_medium',
            'css'               => 'width: 400px;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),
        'review_reminder_mail_campaign_term'    => array(
            'name'        => __( 'Campaign Term', 'yith-woocommerce-review-reminder' ),
            'type'        => 'yith-wc-custom-checklist',
            'desc'        => __( 'Identify the paid keywords. Enter values separated by commas, for example: term1, term2', 'yith-woocommerce-review-reminder' ),
            'id'          => 'ywrr_campaign_term',
            'css'         => 'width: 400px;',
            'placeholder' => __( 'Insert a term&hellip;', 'yith-woocommerce-review-reminder' ),

        ),
        'review_reminder_mail_campaign_content' => array(
            'name' => __( 'Campaign Content', 'yith-woocommerce-review-reminder' ),
            'type' => 'text',
            'desc' => __( 'Use to differentiate ads', 'yith-woocommerce-review-reminder' ),
            'id'   => 'ywrr_campaign_content',
            'css'  => 'width: 400px;',
        ),
        'review_reminder_mail_campaign_name'    => array(
            'name'              => __( 'Campaign Name', 'yith-woocommerce-review-reminder' ),
            'type'              => 'text',
            'desc'              => __( 'Product, promo code, or slogan', 'yith-woocommerce-review-reminder' ),
            'id'                => 'ywrr_campaign_name',
            'css'               => 'width: 400px;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),

        'review_reminder_mail_unsubscribe_text' => array(
            'name'              => __( 'Review unsubscription text', 'yith-woocommerce-review-reminder' ),
            'type'              => 'text',
            'desc'              => '',
            'id'                => 'ywrr_mail_unsubscribe_text',
            'default'           => __( 'Unsubscribe from review emails', 'yith-woocommerce-review-reminder' ),
            'css'               => 'width: 400px;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),

        'review_reminder_mail_test'        => array(
            'name'     => __( 'Test email', 'yith-woocommerce-review-reminder' ),
            'type'     => 'ywrr-send',
            'field_id' => 'ywrr_email_test',
        ),
        'review_reminder_mail_section_end' => array(
            'type' => 'sectionend',
        )
    )

);