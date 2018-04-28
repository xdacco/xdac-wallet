<?php include_once( XDAC_ABSPATH.'/templates/header.php' ); ?>
<div class="wrapper-login-register">

    <div class="logo-block">
        <a href="<?php echo get_site_url(); ?>"><img src="<?php echo XDAC_PLUGIN_URL . 'assets/images/xDAC-logo.png'; ?>"></a>
    </div>
    <div class="tabs-login-register">
        <ul class="nav nav-pills nav-justified">
            <li role="presentation" class="xdac-register"><a href="<?php echo home_url(XDAC_URL_REGISTER); ?>"><?php _e('Register', 'xdac_wp_client'); ?></a></li>
            <li role="presentation" class="active xdac-login"><a href="javascript:void(0);"><?php _e('Login', 'xdac_wp_client'); ?></a></li>
        </ul>

        <p class="login-register-description"><?php _e('Log in to purchase XDAC Tokens', 'xdac_wp_client'); ?></p>
    </div>
    <div class="xdac-client-form">

        <div class="block-xdac-client-errors">
            <?php global $login_errors, $old_email; ?>

            <!-- Show errors if there are any -->
            <?php if ( count( $login_errors ) > 0 ) : ?>
                <?php foreach ( $login_errors as $error ) : ?>
                    <p class="xdac-client-errors">
                        <?php echo $error; ?>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form name="loginform" id="loginform" action="<?php echo home_url('/wp-login.php'); ?>" method="post">
            <input type="hidden" name="xdac_client_form" value="login"/>
            <div>
                <input type="email" name="log" value="<?php echo !empty($old_email) ? $old_email : ''; ?>" placeholder="Email"/>
            </div>

            <div>
                <input type="password" name="pwd" value="" placeholder="Password"/>
            </div>

            <div class="remember">
                <span class="xdac-remember-me">
                    <input type="checkbox" id="xdac-remember-cb" name="rememberme" value="forever" />
                    <label for="xdac-remember-cb" class="xdac-remember-check-box"></label>
                    <?php _e('Remember me', 'xdac_wp_client'); ?>
                </span>
                <span class="xdac-forgot"><a href="<?php echo home_url(XDAC_URL_RECOVER); ?>"><?php _e('Forgot Password?', 'xdac_wp_client'); ?></a></span>
            </div>

            <input class="xdac-submit-form" name="wp-submit" type="submit" value="<?php _e('LOG IN', 'xdac_wp_client'); ?>"/>
            <input type="hidden" name="redirect_to" value="<?php echo home_url('/account'); ?>">
            <input type="hidden" name="wppb_redirect_check" value="true">
        </form>
    </div>
</div>
<?php include_once( XDAC_ABSPATH.'/templates/footer.php' ); ?>