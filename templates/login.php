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
            <li role="presentation" class="xdac-register"><a href="javascript:void(0);"><?php _e('Register', 'xdac_wp_client'); ?></a></li>
            <li role="presentation" class="active xdac-login"><a href="javascript:void(0);"><?php _e('Login', 'xdac_wp_client'); ?></a></li>
        </ul>

        <p class="login-register-description"><?php _e('Log in to purchase XDAC Tokens', 'xdac_wp_client'); ?></p>
    </div>
    <div class="xdac-client-form">
        <form class="" action="" method="post">

            <div>
                <input type="email" name="email" value="" placeholder="Email"/>
            </div>

            <div>
                <input type="password" name="password" value="" placeholder="Password"/>
            </div>

            <div class="remember">
                <span class="xdac-remember-me">
                    <input type="checkbox" id="xdac-remember-cb" name="remember" />
                    <label for="xdac-remember-cb" class="xdac-remember-check-box"></label>
                    <?php _e('Remember me', 'xdac_wp_client'); ?>
                </span>
                <span class="xdac-forgot"><a href="javascript:void(0);"><?php _e('Forgot Password?', 'xdac_wp_client'); ?></a></span>
            </div>

            <input class="xdac-submit-form" type="submit" value="<?php _e('LOG IN', 'xdac_wp_client'); ?>"/>

        </form>
    </div>
</div>