<?php

/**
 * Based roughly on wp-login.php
 */

global $wp_version, $Password_Protected, $error, $is_iphone;

/**
 * WP Shake JS
 */
if ( ! function_exists( 'wp_shake_js' ) ) {
	function wp_shake_js() {
		?>
		<script type="text/javascript">
			document.querySelector('form').classList.add('shake');
		</script>
		<?php
	}
}

/**
 * @since 3.7.0
 */
if ( ! function_exists( 'wp_login_viewport_meta' ) ) {
	function wp_login_viewport_meta() {
		?>
		<meta name="viewport" content="width=device-width" />
		<?php
	}
}

nocache_headers();
header( 'Content-Type: ' . get_bloginfo( 'html_type' ) . '; charset=' . get_bloginfo( 'charset' ) );

// Set a cookie now to see if they are supported by the browser.
setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
if ( SITECOOKIEPATH != COOKIEPATH ) {
	setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );
}

// If cookies are disabled we can't log in even with a valid password.
if ( isset( $_POST['password_protected_cookie_test'] ) && empty( $_COOKIE[ TEST_COOKIE ] ) ) {
	$Password_Protected->errors->add( 'test_cookie', __( "<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress.", 'password-protected' ) );
}

// Shake it!
$shake_error_codes = array( 'empty_password', 'incorrect_password' );
if ( $Password_Protected->errors->get_error_code() && in_array( $Password_Protected->errors->get_error_code(), $shake_error_codes ) ) {
	add_action( 'login_footer', 'wp_shake_js', 12 );
}

// Obey privacy setting
add_action( 'password_protected_login_head', 'wp_no_robots' );

add_action( 'password_protected_login_head', 'wp_login_viewport_meta' );

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>

<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<title><?php echo apply_filters( 'password_protected_wp_title', get_bloginfo( 'name' ) ); ?></title>

<?php

wp_admin_css( 'login', true );

do_action( 'login_enqueue_scripts' );
do_action( 'password_protected_login_head' );

$redirect_to = '';

$nonce = $Password_Protected->request( 'nonce', FILTER_SANITIZE_STRING );

if ( wp_verify_nonce( $nonce, $Password_Protected::ACTION ) ) {
	$redirect_to = $Password_Protected->request( 'redirect_to', FILTER_SANITIZE_STRING );
}

?>

</head>
<body class="login login-password-protected login-action-password-protected-login wp-core-ui">

<div id="login">
	<h1><a href="<?php echo esc_url( apply_filters( 'password_protected_login_headerurl', home_url( '/' ) ) ); ?>" title="<?php echo esc_attr( apply_filters( 'password_protected_login_headertitle', get_bloginfo( 'name' ) ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>

	<?php do_action( 'password_protected_login_messages' ); ?>
	<?php do_action( 'password_protected_before_login_form' ); ?>

	<form name="loginform" id="loginform" action="<?php echo esc_url( $Password_Protected->login_url() ); ?>" method="post">
		<p>
			<label for="password_protected_pass"><?php echo apply_filters( 'password_protected_login_password_title', __( 'Password', 'password-protected' ) ); ?><br />
			<input type="password" name="password_protected_pwd" id="password_protected_pass" class="input" value="" size="20" tabindex="20" /></label>
		</p>

		<?php if ( $Password_Protected->allow_remember_me() ) : ?>
			<p class="forgetmenot">
				<label for="password_protected_rememberme"><input name="password_protected_rememberme" type="checkbox" id="password_protected_rememberme" value="1" tabindex="90" /> <?php esc_attr_e( 'Remember Me' ); ?></label>
			</p>
		<?php endif; ?>

		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Log In' ); ?>" tabindex="100" />
			<input type="hidden" name="password_protected_cookie_test" value="1" />
			<input type="hidden" name="password-protected" value="login" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
			<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>" />
		</p>
	</form>

	<?php do_action( 'password_protected_after_login_form' ); ?>

</div>

<script>
try{document.getElementById('password_protected_pass').focus();}catch(e){}
if(typeof wpOnload=='function')wpOnload();
</script>

<?php do_action( 'login_footer' ); ?>

<div class="clear"></div>

</body>
</html>
