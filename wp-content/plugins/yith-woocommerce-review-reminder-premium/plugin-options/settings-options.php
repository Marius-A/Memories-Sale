<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

return array(
	'settings' => array(
		'review_reminder_request_section_title' => array(
			'name' => __( 'General Settings', 'yith-woocommerce-review-reminder' ),
			'type' => 'title',
			'desc' => '',
			'id'   => 'ywrr_request_settings_title',
		),
		'review_reminder_refuse_requests'       => array(
			'name'    => __( 'Don\'t email me checkbox', 'yith-woocommerce-review-reminder' ), //@since 1.2.6
			'type'    => 'checkbox',
			'desc'    => __( 'When they are in checkout page, users can refuse to receive review requests via email',
			'yith-woocommerce-review-reminder' ),
			//@since 1
			//.2.6
			'id'      => 'ywrr_refuse_requests',
			'default' => 'no',
		),
		'review_reminder_schedule_day'          => array(
			'name'              => __( 'Days to elapse', 'yith-woocommerce-review-reminder' ),
			'type'              => 'number',
			'desc'              => __( 'Type here the number of days that have to pass after the order has been set as "completed" before sending an email for reminding users to review the item(s)purchased. Defaults to 7 <br/> Note: Changing this WILL NOT re-schedule currently scheduled emails. If you would like to reschedule emails to this new date, make sure you check the \'Reschedule emails\' checkboxes below.', 'yith-woocommerce-review-reminder' ),
			'default'           => 7,
			'id'                => 'ywrr_mail_schedule_day',
			'custom_attributes' => array(
				'min'      => 1,
				'required' => 'required'
			)
		),
		'review_reminder_request_type'          => array(
			'name'    => __( 'Request a review for', 'yith-woocommerce-review-reminder' ),
			'type'    => 'select',
			'desc'    => __( 'Select the products you want to aks for a review', 'yith-woocommerce-review-reminder' ),
			'options' => array(
				'all'       => __( 'All products in order', 'yith-woocommerce-review-reminder' ),
				'selection' => __( 'Specific products', 'yith-woocommerce-review-reminder' )
			),
			'default' => 'all',
			'id'      => 'ywrr_request_type'
		),
		'review_reminder_request_number'        => array(
			'name'              => __( 'Number of products for review request', 'yith-woocommerce-review-reminder' ),
			'type'              => 'number',
			'desc'              => __( 'Set the number of products from the order to include in the review reminder email. Default: 1', 'yith-woocommerce-review-reminder' ),
			'default'           => 1,
			'id'                => 'ywrr_request_number',
			'custom_attributes' => array(
				'min'      => 1,
				'required' => 'required'
			)
		),
		'review_reminder_request_criteria'      => array(
			'name'    => __( 'Send review reminder for', 'yith-woocommerce-review-reminder' ),
			'type'    => 'select',
			'desc'    => '',
			'options' => array(
				'first'               => __( 'First products(s) bought', 'yith-woocommerce-review-reminder' ),
				'last'                => __( 'Last products(s) bought', 'yith-woocommerce-review-reminder' ),
				'highest_quantity'    => __( 'Products with highest number of items bought', 'yith-woocommerce-review-reminder' ),
				'lowest_quantity'     => __( 'Products with lowest number of items bought', 'yith-woocommerce-review-reminder' ),
				'most_reviewed'       => __( 'Products with highest number of reviews', 'yith-woocommerce-review-reminder' ),
				'least_reviewed'      => __( 'Products with lowest number of reviews', 'yith-woocommerce-review-reminder' ),
				'highest_priced'      => __( 'Products with highest price', 'yith-woocommerce-review-reminder' ),
				'lowest_priced'       => __( 'Products with lowest price', 'yith-woocommerce-review-reminder' ),
				'highest_total_value' => __( 'Products with highest total value', 'yith-woocommerce-review-reminder' ),
				'lowest_total_value'  => __( 'Products with lowest total value', 'yith-woocommerce-review-reminder' ),
				'random'              => __( 'Random', 'yith-woocommerce-review-reminder' ),
			),
			'default' => 'first',
			'id'      => 'ywrr_request_criteria'
		),
		'review_reminder_reschedule'            => array(
			'name'          => __( 'Reschedule emails', 'yith-woocommerce-review-reminder' ),
			'type'          => 'checkbox',
			'desc'          => __( 'Reschedule all currently scheduled emails to the new date defined above', 'yith-woocommerce-review-reminder' ),
			'id'            => 'ywrr_mail_reschedule',
			'default'       => 'no',
			'checkboxgroup' => 'start'
		),
		'review_reminder_send_rescheduled'      => array(
			'name'          => __( 'Reschedule emails', 'yith-woocommerce-review-reminder' ),
			'type'          => 'checkbox',
			'desc'          => __( 'Send emails if rescheduled date has already passed', 'yith-woocommerce-review-reminder' ),
			'id'            => 'ywrr_mail_send_rescheduled',
			'default'       => 'no',
			'checkboxgroup' => 'end'
		),
		'review_reminder_request_section_end'   => array(
			'type' => 'sectionend',
			'id'   => 'ywrr_request_settings_end'
		),

		'review_reminder_advanced_section_title' => array(
			'name' => __( 'Advanced Tools', 'yith-woocommerce-review-reminder' ),
			'type' => 'title',
		),
		'review_reminder_mass_schedule'          => array(
			'name' => '',
			'type' => 'ywrr-schedule',
		),
		'review_reminder_advanced_section_end'   => array(
			'type' => 'sectionend',
		),


	)
);