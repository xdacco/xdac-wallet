<?php include_once( XDAC_ABSPATH.'/templates/header.php' ); ?>
<div class="wrapper-login-register">
    <div class="logo-block">
        <a href="<?php echo get_site_url(); ?>"><img src="<?php echo XDAC_PLUGIN_URL . 'assets/images/logo.png'; ?>"></a>
    </div>
    <div class="tabs-login-register">
        <ul class="nav nav-pills nav-justified">
            <li role="presentation" class="active xdac-register"><a href="javascript:void(0);"><?php _e('Register', 'xdac_wp_client'); ?></a></li>
            <li role="presentation" class="xdac-login"><a href="<?php echo home_url(XDAC_URL_LOGIN); ?>"><?php _e('Login', 'xdac_wp_client'); ?></a></li>
        </ul>

        <p class="login-register-description"><?php _e('Register to purchase XDAC Tokens', 'xdac_wp_client'); ?></p>
    </div>
    <div class="xdac-client-form">

        <div class="block-xdac-client-errors">
            <?php
            global $reg_errors;
            if ( is_wp_error( $reg_errors ) ) {
                foreach ( $reg_errors->get_error_messages() as $error ) {
                    echo '<p class="xdac-client-errors">' . $error . '</p>';
                }
            }
            ?>
        </div>

        <form class="" action="" method="post">
            <input type="hidden" name="xdac_client_form" value="register"/>
            <div>
                <input type="text" name="fname" value="<?php echo !empty($_POST['fname']) ? $_POST['fname'] : ''; ?>" placeholder="First Name"/>
            </div>

            <div>
                <input type="text" name="lname" value="<?php echo !empty($_POST['lname']) ? $_POST['lname'] : ''; ?>" placeholder="Last Name"/>
            </div>

            <div>
                <input type="email" name="email" value="<?php echo !empty($_POST['email']) ? $_POST['email'] : ''; ?>" placeholder="Email"/>
            </div>

            <div>
                <input type="password" name="password" value="" placeholder="Password"/>
            </div>

            <div>
                <input type="text" name="referral" value="<?php echo !empty($_POST['referral']) ? $_POST['referral'] : ''; ?>" placeholder="Referral Name or ID"/>
            </div>

            <input class="xdac-submit-form" type="submit" value="<?php _e('REGISTER', 'xdac_wp_client'); ?>"/>

            <p class="xdac-register-terms">
                <?php _e('By registering you agree to ', 'xdac_wp_client'); ?>
                <a href="javascript:void(0);"><?php _e('Website Terms of Use', 'xdac_wp_client'); ?></a>
                <?php _e(' and the ', 'xdac_wp_client'); ?>
                <a href="javascript:void(0);"><?php _e('Token Sale Terms and Conditions', 'xdac_wp_client'); ?></a>
                <?php _e(' as well as the ', 'xdac_wp_client'); ?>
                <a href="javascript:void(0);"><?php _e('Privacy Policy', 'xdac_wp_client'); ?></a>
            </p>

        </form>
    </div>
</div>
<?php include_once( XDAC_ABSPATH.'/templates/footer.php' ); ?>