<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class WC_Review_Reminder extends WC_Email {

	public function __construct() {

		$this->id = 'wc_advanced_review';

		$this->title = 'Advanced Review Reminder';

		$this->description = 'Reminders to review purchases can be sent out automatically.';

		$this->heading = __('We would love your feedback','wc-review-reminder');
		$this->subject = __('Please help us by reviewing','wc-review-reminder');
		parent::__construct();
	}


	/**
	 * get_content_html function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		woocommerce_get_template( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading()
			) );
		return ob_get_clean();
	}


	/**
	 * Custom process admin options - sends test email if inputfield is not empty
	 * @author larsk
	 * @return void
	 */
	public function process_admin_options() {
		parent::process_admin_options();
		// Continue processing.
		// Check to see if we should send a test email
		if ( (isset($_POST['arr_email_recipient'])) && (is_email( $_POST['arr_email_recipient'])) ) {
			global $advanced_review_rem,$wpdb;

			// try to detect earliest set day interval
			$intervals=explode(',',$_POST['woocommerce_wc_advanced_review_interval']);
			if (isset($intervals[0])) { // pick up the first
				$testinterval=intval($intervals[0]);	
			}
			else {
				$testinterval=7; // set default to 7 days after if not properly detected
			}

			$advanced_review_rem->send_email_reminder(0,$testinterval,$_POST['arr_email_recipient']);
			unset($_POST['arr_email_recipient']); // unset so test email only gets sent once.
		}

	}


	/**
	 * Adds custom HTML to settings page.
	 * @author larsk
	 * @return void
	 */
	public function admin_options() {
		?>
		<h2><?php _e('WooCommerce Advanced Review Reminder','wc-review-reminder'); ?></h2>
		<p><a href="<?php echo plugin_dir_url(dirname(__FILE__)).'documentation/index.html'; ?>" target="_blank"><?php _e('Click here to read the documentation','wc-net-promoter'); ?></a></p>
		<?php
		global $advanced_review_rem;
	//	$advanced_review_rem->_activate_routines();
		?>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>

		<h3><?php _e('Send Test Email','wc-review-reminder'); ?></h3>


		<?php wp_nonce_field( 'arr_nonce' ); ?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="arr_email_recipient"><?php _e('Email Recipient','wc-review-reminder'); ?></label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span><?php _e('Email Recipient','wc-review-reminder'); ?></span></legend>
							<input class="input-text regular-input" type="text" name="arr_email_recipient" id="arr_email_recipient"  value="">
							<p class="description"><?php _e('Enter a valid email to send a test email.','wc-review-reminder'); ?><br />
							</p>
						</fieldset>
					</td>
				</tr>

			</tbody>
		</table>	
		<?php

		global $wpdb;
		$logtable= $wpdb->prefix."woocommerce_arr_log";
		$query = "SELECT * FROM $logtable order by `time` DESC LIMIT 50;";

		$logs = $wpdb->get_results($query, ARRAY_A);

		$time= date('Y-m-d H:i:s ',time());
		if ($logs) {
			?>
			<h3><?php _e('Logs','wc-review-reminder'); ?></h3>
			<table class="wp-list-table widefat logtable">
				<thead>
					<tr>
						<th scope="shortcol" class="shortcol"><?php _e('Time','wc-review-reminder'); ?></th>
						<th scope="col"><?php _e('Event','wc-review-reminder'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($logs as $log) {
						echo "<tr><td class='shortcol prio-".$log['prio']."'>".$log['time']."</td><td class='prio-".$log['prio']."'>".stripslashes($log['note'])."</td></tr>";
					}
					?>

				</tbody>
				<tfoot>
					<tr>
						<th scope="shortcol" class="shortcol"><?php _e('Time','wc-review-reminder'); ?></th>
						<th scope="col"><?php _e('Event','wc-review-reminder'); ?></th>
					</tr>
				</tfoot>
			</table>
			<?php
} // if ($logs)


}

	/**
	 * get_content_plain function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		woocommerce_get_template( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading()
			) );
		return ob_get_clean();
	}


	/**
	 * Initialize Settings Form Fields
	 *
	 * @since 2.0
	 */
	public function init_form_fields() {

		$macrolisttable='<table>';

		$macrolisttable .= '<tr><td><code>{customer_name}</code></td><td>'.__('Replaced with the customers name.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '<tr><td><code>{customer_firstname}</code></td><td>'.__('Replaced with the customers name.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '<tr><td><code>{customer_lastname}</code></td><td>'.__('Replaced with the customers name.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '<tr><td><code>{customer_email}</code></td><td>'.__('Replaced with the customer email.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '<tr><td><code>{site_title}</code></td><td>'.__('Replaced with the site title.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '<tr><td><code>{order_id}</code></td><td>'.__('Replaced with the order id.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '<tr><td><code>{order_date}</code></td><td>'.__('Replaced with the date and time of the order.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '<tr><td><code>{order_date_completed}</code></td><td>'.__('Replaced with the date the order was marked completed.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '<tr><td><code>{stop_emails_link}</code></td><td>'.__('Replaced with a link to stop recieving email review reminders.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '<tr><td><code>{order_list}</code></td><td>'.__('Replaced with a list of products purchased but not reviewed.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '<tr><td><code>{days_ago}</code></td><td>'.__('Replaced with the number of days ago the order was made.','wc-review-reminder').'</td></tr>';

		$macrolisttable .= '</table>';


		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __('Enable/Disable','wc-review-reminder'),
				'type'    => 'checkbox',
				'label'   => __('Enable this email notification','wc-review-reminder'),
				'default' => 'yes'
				),
			'interval'  => array(
				'title'       => __('Day(s) after order','wc-review-reminder'),
				'type'        => 'text',
				'description' => __( 'You can choose how many days after a order has been completed before a reminder email is sent.','wc-review-reminder' ),
				'placeholder' => '',
				'default'     => '7,14'
				),
			'subject'    => array(
				'title'       => __('Email subject','wc-review-reminder'),
				'type'        => 'text',
				'description' => __('The email subject line.','wc-review-reminder'),
				'placeholder' => '',
				'default'     => __('[{site_title}] Review recently purchased products','wc-review-reminder')
				),
			'email'    => array(
				'title'       => __('Email Content','wc-review-reminder'),
				'type'        => 'textarea',
				'description' =>  __( 'This is the email template','wc-review-reminder' ).$macrolisttable,
				'placeholder' => '',
				'default'     => __("Hello {customer_name},\r\nThank you for purchasing items from the {site_title} shop!\r\nWe would love if you could help us and other customers by reviewing the products you recently purchased.  It only takes a minute and it would really help others by giving them an idea of your experience.  Click the link below for each product and review the product under the 'Reviews' tab.\r\n{order_list}\r\nMuch appreciated,\r\n{site_title}.\r\n{stop_emails_link}",'wc-review-reminder')
				),

			'unsubscribesection'    => array(
				'title'       => ' ',
				'type'        => 'title',
				'description' => '<h3>'.__('Unsubscribe Options','wc-review-reminder').'</h3><hr>'
				),

			'stoptext'    => array(
				'title'       => __('Stop Receiving Emails Text','wc-review-reminder'),
				'type'        => 'text',
				'description' => __( 'This text will be made in to a clickable link you can used with the <code>{stop_emails_link}</code> macro.','wc-review-reminder' ),
				'placeholder' => '',
				'default'     => __('Unsubscribe from review emails','wc-review-reminder')
				),


			'unsubscribesubjectline'    => array(
				'title'       => __('Stop Receiving Emails Text','wc-review-reminder'),
				'type'        => 'text',
				'description' => __( 'This will be the subject line in the unsubscribe confirmation email. Macros can not be used in this subject line.','wc-review-reminder' ),
				'placeholder' => '',
				'default'     => __('You are now unsubscribed','wc-review-reminder')
				),

			'unsubscribetext'    => array(
				'title'       => __('Unsubscribe message','wc-review-reminder'),
				'type'        => 'textarea',
				'description' =>  __( 'This is the email template that will be sent to users who unsubscribe. Macros can not be used in this email.','wc-review-reminder' ),
				'placeholder' => '',
				'default'     => __("Hello\r\nYou are now unsubscribed from further emails requesting product reviews.",'wc-review-reminder')
				),
			'blocklist'    => array(
				'title'       => __('Email blocklist','wc-review-reminder'),
				'type'        => 'textarea',
				'description' =>  __( 'Comma separated list of emails that have asked not to recieve any more review reminders.','wc-review-reminder' ),
				'placeholder' => '',
				'default'     => ''
				),			

			);
}


} // end WC_Review_Reminder class
