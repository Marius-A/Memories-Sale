<?php
/**
 * Login Form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php wc_print_notices(); ?>

<style>
/*	#site-top-bar,
	#masthead,*/
	.entry-header .entry-title,
	.entry-header .page-title,
	/*#site-footer,*/
	.page-title:after,
	form .page-title
	{
		display: none !important;
	}
	
/*	.st-content,
	.st-container
	{
		height: 100%;
	}
	
	.st-content
	{
		overflow-y: auto;
	}
	
	body
	{
		overflow-y: auto;	
	}
	
	@media only screen and (min-width: 1025px) {	
		.content-area
		{
			margin-top: 0 !important;	
		}	
	}
*/	
</style>

<div class="row">
	<div class="medium-10 medium-centered large-6 large-centered columns">

		<ul class="account-tab-list">
		 
			<li class="account-tab-item">
			    <a class="account-tab-link <?php echo ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' )  ? 'current':'registration_disabled' ?>" href="#login"><?php _e( 'Login', 'woocommerce' ); ?></a>
			</li>
			 
			<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>
				<li class="sep">/</li>
				<li class="account-tab-item last">
					<a class="account-tab-link" href="#register"><?php _e( 'Register', 'woocommerce' ); ?></a>
				</li>
			<?php endif; ?>
	 
		</ul>
		
		<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

		<div class="login-register-container">
				
					<div class="account-forms-container">
						
						
						<div class="account-forms">
							<form id="login" method="post" class="login-form">
					
								<h1 class="page-title">Login</h1>
					
								<?php do_action( 'woocommerce_login_form_start' ); ?>
					
								<p class="form-row form-row-wide">
									<input type="text" class="input-text" name="username" id="username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" placeholder="<?php _e( 'Username or email address *', 'woocommerce' ); ?>"/>
								</p>
								<p class="form-row form-row-wide">
									<input class="input-text" type="password" name="password" id="password" placeholder="<?php _e( 'Password *', 'woocommerce' ); ?>" />
								</p>
					
								<?php do_action( 'woocommerce_login_form' ); ?>
					
								<p class="form-row">
									<input type="submit" class="button" name="login" value="<?php _e( 'Login', 'woocommerce' ); ?>" /> 
								</p>
					
								<p class="form-row without-padding">
									<?php wp_nonce_field( 'woocommerce-login' ); ?>
								</p>
								<p class="form-row remember-me-row">
									<a class="lost-pass-link" href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
										<?php _e( 'Lost your password?', 'woocommerce' ); ?>
									</a>
									<input name="rememberme" class="check_box" type="checkbox" id="rememberme" value="forever" /> 
									<label for="rememberme" class="remember-me check_label"><?php _e( 'Remember me', 'woocommerce' ); ?></label>
								</p>
								
					
								<?php do_action( 'woocommerce_login_form_end' ); ?>
							
							<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>	
								<div class="login_footer_separator"><div class="login_footer_separator_title">or</div></div>
								<a class="account-tab-link-mobile account-tab-link-register" href="#register"><?php _e( 'Register', 'woocommerce' ); ?></a>
							<?php endif; ?>
							
							</form>
							
							
						<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>
								
							<form id="register" method="post" class="register-form">
								
								<h1 class="page-title">Register</h1>
					
								<?php do_action( 'woocommerce_register_form_start' ); ?>
					
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
					
									<p class="form-row form-row-wide">
										<input type="text" class="input-text" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) esc_attr_e( $_POST['username'] ); ?>" placeholder="<?php _e( 'Username *', 'woocommerce' ); ?>" />
									</p>
					
								<?php endif; ?>
					
								<p class="form-row form-row-wide">
									<input type="email" class="input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) esc_attr_e( $_POST['email'] ); ?>" placeholder="<?php _e( 'Email *', 'woocommerce' ); ?>"/>
								</p>
					
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
	
								<p class="form-row form-row-wide">
									<input type="password" class="input-text" name="password" id="reg_password" placeholder="<?php _e( 'Password *', 'woocommerce' ); ?>"/>
								</p>
					
			<?php endif; ?>

								<!-- Spam Trap -->
			<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="trap"><?php _e( 'Anti-spam', 'woocommerce' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" /></div>
					
								<?php do_action( 'woocommerce_register_form' ); ?>
								<?php do_action( 'register_form' ); ?>
					
								<p class="form-row">
				<?php wp_nonce_field( 'woocommerce-register' ); ?>
									<input type="submit" class="button" name="register" value="<?php _e( 'Register', 'woocommerce' ); ?>" />
								</p>
					
								<?php do_action( 'woocommerce_register_form_end' ); ?>
								
							<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>
								
								<div class="login_footer_separator"><div class="login_footer_separator_title">or</div></div>
								<a class="account-tab-link-mobile account-tab-link-login" href="#register"><?php _e( 'Login', 'woocommerce' ); ?></a>
							
							<?php endif; ?>
							
							</form><!-- .register-->
					
								
						<?php endif; ?>	
						</div><!-- .account-forms-->
						
					</div><!-- .account-forms-container-->
		</div><!-- .login-register-container-->
		
		<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

	</div><!-- .large-6-->
</div><!-- .rows-->

<!-- <div class="login_footer">
	
	<ul class="account-tab-list">
		 
		 <li class="account-tab-item">
			 <a class="account-tab-link <?php echo ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' )  ? 'current':'registration_disabled' ?>" href="#login"><?php _e( 'Login', 'woocommerce' ); ?></a>
		 </li>
		 
		 <?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>
			 <li class="account-tab-item last">
				<a class="account-tab-link" href="#register"><?php _e( 'Register', 'woocommerce' ); ?></a>
			 </li>
		 <?php endif; ?>
	 
	 </ul>
	<a class="go_home" href="<?php echo home_url(); ?>" title="<?php bloginfo('name'); ?>"><?php bloginfo('name'); ?></a>
</div> -->

