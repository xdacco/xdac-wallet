<?php include_once( XDAC_ABSPATH.'/templates/header.php' ); ?>
    <div class="wrapper-login-register">
        <div class="logo-block">
            <a href="<?php echo get_site_url(); ?>"><img src="<?php echo XDAC_PLUGIN_URL . 'assets/images/xDAC-logo.png'; ?>"></a>
        </div>
        <div class="tabs-login-register">
            <ul class="nav nav-pills nav-justified">
                <li role="presentation" class="xdac-register"><a href="<?php echo home_url(XDAC_URL_REGISTER); ?>"><?php _e('Register', 'xdac_wp_client'); ?></a></li>
                <li role="presentation" class="xdac-login"><a href="<?php echo home_url(XDAC_URL_LOGIN); ?>"><?php _e('Login', 'xdac_wp_client'); ?></a></li>
            </ul>

            <p class="login-register-description"><?php _e('Reset password to your account', 'xdac_wp_client'); ?></p>
        </div>
        <div class="xdac-client-form">

            <div class="block-xdac-client-errors">
                <?php
                global $reset_errors;
                if ( is_wp_error( $reset_errors ) ) {
                    foreach ( $reset_errors->get_error_messages() as $error ) {
                        echo '<p class="xdac-client-errors">' . $error . '</p>';
                    }
                }
                ?>
            </div>

            <form class="" action="" method="post">
                <input type="hidden" name="reset_key" value="<?php echo $_GET['key']; ?>"/>
                <input type="hidden" name="reset_login" value="<?php echo $_GET['login']; ?>"/>
                <input type="hidden" name="xdac_client_form" value="reset"/>
                <div>
                    <input type="password" name="password" value="" placeholder="Password"/>
                </div>
                <div>
                    <input type="password" name="repeat_password" value="" placeholder="Password is the same"/>
                </div>

                <input class="xdac-submit-form recover-submit" type="submit" value="<?php _e('RESET PASSWORD', 'xdac_wp_client'); ?>"/>

            </form>
        </div>
    </div>
<?php include_once( XDAC_ABSPATH.'/templates/footer.php' ); ?>