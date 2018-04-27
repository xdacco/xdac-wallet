<?php include_once( XDAC_ABSPATH.'/templates/header.php' ); ?>
<div class="wrapper-login-register">
    <div class="logo-block">
        <a href="<?php echo get_site_url(); ?>"><img src="<?php echo XDAC_PLUGIN_URL . 'assets/images/logo.png'; ?>"></a>
    </div>
    <div class="tabs-login-register">
        <ul class="nav nav-pills nav-justified">
            <li role="presentation" class="xdac-register"><a href="<?php echo home_url(XDAC_URL_REGISTER); ?>"><?php _e('Register', 'xdac_wp_client'); ?></a></li>
            <li role="presentation" class="xdac-login"><a href="<?php echo home_url(XDAC_URL_LOGIN); ?>"><?php _e('Login', 'xdac_wp_client'); ?></a></li>
        </ul>

        <p class="login-register-description"><?php _e('Recover password to your account', 'xdac_wp_client'); ?></p>
    </div>
    <div class="xdac-client-form">

        <div class="block-xdac-client-message">
            <?php
            global $recover_message;
            echo '<p class="xdac-client-message">'.$recover_message.'</p>';
            ?>
        </div>
        <div class="block-xdac-client-errors">
            <?php
            global $recover_errors;
            if ( is_wp_error( $recover_errors ) ) {
                foreach ( $recover_errors->get_error_messages() as $error ) {
                    echo '<p class="xdac-client-errors">' . $error . '</p>';
                }
            }
            ?>
        </div>

        <form class="" action="" method="post">
            <input type="hidden" name="xdac_client_form" value="recover"/>
            <div>
                <input type="email" name="email" value="<?php echo !empty($_POST['email']) ? $_POST['email'] : ''; ?>" placeholder="Email"/>
            </div>

            <input class="xdac-submit-form recover-submit" type="submit" value="<?php _e('RECOVER PASSWORD', 'xdac_wp_client'); ?>"/>

        </form>
    </div>
</div>
<?php include_once( XDAC_ABSPATH.'/templates/footer.php' ); ?>