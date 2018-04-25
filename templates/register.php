<?php
// Script calling for the current page
wp_enqueue_script( 'xdac-client-js' );
?>
<div class="wrapper-login-register">
    <div class="logo-block">
        <img src="<?php echo XDAC_PLUGIN_URL . 'assets/images/logo.png'; ?>">
    </div>
    <div class="tabs-login-register">
        <ul class="nav nav-pills nav-justified">
            <li role="presentation" class="active xdac-register"><a href="javascript:void(0);"><?php _e('Register', 'xdac_wp_client'); ?></a></li>
            <li role="presentation" class="xdac-login"><a href="javascript:void(0);"><?php _e('Login', 'xdac_wp_client'); ?></a></li>
        </ul>

        <p class="login-register-description"><?php _e('Register to purchase XDAC Tokens', 'xdac_wp_client'); ?></p>
    </div>
    <div class="xdac-client-form">
        <form class="" action="" method="post">

            <div>
                <input type="text" name="first" value="" placeholder="First Name"/>
            </div>

            <div>
                <input type="text" name="last" value="" placeholder="Last Name"/>
            </div>

            <div>
                <input type="email" name="email" value="" placeholder="Email"/>
            </div>

            <div>
                <input type="password" name="password" value="" placeholder="Password"/>
            </div>

            <div>
                <input type="text" name="referral" value="" placeholder="Referral Name or ID"/>
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