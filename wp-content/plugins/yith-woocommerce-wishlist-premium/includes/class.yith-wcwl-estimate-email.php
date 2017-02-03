<?php
/**
* Quote email class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 1.1.5
 */

if ( !defined( 'YITH_WCWL' ) ) {
    exit;
} // Exit if accessed directly

if( !class_exists( 'YITH_WCWL_Estimate_Email' ) ) {
    /**
     * WooCommerce Wishlist
     *
     * @since 1.0.0
     */
    class YITH_WCWL_Estimate_Email extends WC_Email {

        /**
         * True when the email notification is sent manually only.
         * @var bool
         */
        protected $manual = true;

        /**
         * Constructor method, used to return object of the class to WC
         *
         * @return \YITH_WCWL_Estimate_Email
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.com>
         */
        public function __construct() {
            $this->id 				   = 'estimate_mail';
            $this->title 			   = __( 'Wishlist "Ask for an estimate" email', 'yith-woocommerce-wishlist' );
            $this->description		   = __( 'This email is sent when a user click the "ask for an estimate" button, only if this feature has been enabled in the wishlist option panel', 'yith-woocommerce-wishlist' );

            $this->heading 			   = __( 'Estimate request', 'yith-woocommerce-wishlist' );
            $this->subject      	   = __( '[Estimate request]', 'yith-woocommerce-wishlist' );

            $this->template_html 	= 'emails/ask-estimate.php';
            $this->template_plain 	= 'emails/plain/ask-estimate.php';

            // Triggers for this email
            add_action( 'send_estimate_mail_notification', array( $this, 'trigger' ), 15, 2 );

            // Call parent constructor
            parent::__construct();

            // Other settings
            $this->recipient = $this->get_option( 'recipient' );

            if ( ! $this->recipient )
                $this->recipient = get_option( 'admin_email' );

            $this->enable_cc = $this->get_option( 'enable_cc' );
            $this->enable_cc = $this->enable_cc == 'yes';
        }

        /**
         * Method triggered to send email
         *
         * @param int $wishlist_id Id of wishlist
         *
         * @return void
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.com>
         */
        public function trigger( $wishlist_id, $additional_notes ) {
            if ( ! empty( $wishlist_id ) ) {
                $this->wishlist = YITH_WCWL()->get_wishlist_detail_by_token( $wishlist_id );
                $wishlist_token = $wishlist_id;
            }
            elseif( is_user_logged_in() ){
                $wishlists = YITH_WCWL()->get_wishlists( array( 'user_id' => get_current_user_id(), 'is_default' => true ) );

                if( empty( $wishlists ) ){
                    YITH_WCWL()->generate_default_wishlist( get_current_user_id() );
                    $wishlist_token = YITH_WCWL()->last_operation_token;
                    $this->wishlist = YITH_WCWL()->get_wishlist_detail( $wishlist_token );
                }
                else{
                    $this->wishlist = $wishlists[0];
                    $wishlist_token = $this->wishlist['wishlist_token'];
                }
            }
            else{
                return;
            }

            $user = get_user_by( 'id', $this->wishlist['user_id'] );

            if( isset( $user->first_name ) || isset( $user->first_name ) ){
                $this->wishlist['user_name'] = $user->first_name . " " . $user->last_name;
            }
            else{
                $this->wishlist['user_name'] = $user->user_login;
            }

            $this->wishlist['user_email'] = $user->user_email;
            $this->wishlist['wishlist_items'] = YITH_WCWL()->get_products( array( 'wishlist_token' => $wishlist_token ) );

	        $this->additional_notes = $additional_notes;

            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        /**
         * get_headers function.
         *
         * @access public
         * @return string
         */
        function get_headers() {
            $headers = "Reply-to: " . $this->wishlist['user_email'] . "\r\n";

            if( $this->enable_cc ){
                $headers .= "Cc: " . $this->wishlist['user_email'] . "\r\n";
            }

            $headers .= "Content-Type: " . $this->get_content_type() . "\r\n";

            return apply_filters( 'woocommerce_email_headers', $headers, $this->id, $this->object );
        }

        /**
         * Get HTML content for the mail
         *
         * @return string HTML content of the mail
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.com>
         */
        public function get_content_html(){
            ob_start();
            wc_get_template( $this->template_html, array(
                'email' => $this,
                'wishlist_data' => $this->wishlist,
	            'additional_notes' => $this->additional_notes,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text'    => false
            ) );
            return ob_get_clean();
        }

        /**
         * Get plain text content of the mail
         *
         * @return string Plain text content of the mail
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.com>
         */
        public function get_content_plain() {
            ob_start();
            wc_get_template( $this->template_plain, array(
                'email' => $this,
                'wishlist_data' => $this->wishlist,
                'additional_notes' => $this->additional_notes,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text'    => true
            ) );
            return ob_get_clean();
        }

        /**
         * Init form fields to display in WC admin pages
         *
         * @return void
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.com>
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'subject' => array(
                    'title' 		=> __( 'Subject', 'woocommerce' ),
                    'type' 			=> 'text',
                    'description' 	=> sprintf( __( 'This field lets you modify the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'yith-woocommerce-wishlist' ), $this->subject ),
                    'placeholder' 	=> '',
                    'default' 		=> ''
                ),
                'recipient' => array(
                    'title'         => __( 'Recipient(s)', 'yith-woocommerce-wishlist' ),
                    'type'          => 'text',
                    'description'   => sprintf ( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>', 'yith-woocommerce-wishlist' ), esc_attr( get_option('admin_email') ) ),
                    'placeholder' 	=> '',
                    'default' 		=> ''
                ),
                'enable_cc' => array(
                    'title'         => __( 'Send CC copy', 'yith-woocommerce-wishlist' ),
                    'type'          => 'checkbox',
                    'description'   => __( 'Send a carbon copy to the user', 'yith-woocommerce-wishlist' ),
                    'default'       => 'no'
                ),
                'heading' => array(
                    'title' 		=> __( 'Email Heading', 'woocommerce' ),
                    'type' 			=> 'text',
                    'description' 	=> sprintf( __( 'This field lets you modify the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'yith-woocommerce-wishlist' ), $this->heading ),
                    'placeholder' 	=> '',
                    'default' 		=> ''
                ),
                'email_type' => array(
                    'title' 		=> __( 'Email type', 'woocommerce' ),
                    'type' 			=> 'select',
                    'description' 	=> __( 'Choose which type of email to send.', 'woocommerce' ),
                    'default' 		=> 'html',
                    'class'			=> 'email_type',
                    'options'		=> array(
                        'plain'		 	=> __( 'Plain text', 'woocommerce' ),
                        'html' 			=> __( 'HTML', 'woocommerce' ),
                        'multipart' 	=> __( 'Multipart', 'woocommerce' ),
                    )
                )
            );
        }
    }
}


// returns instance of the mail on file include
return new YITH_WCWL_Estimate_Email();