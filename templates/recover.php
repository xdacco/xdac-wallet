<?php include_once( XDAC_ABSPATH.'/templates/header.php' ); ?>
<div class="wrapper-login-register">
    <div class="logo-block">
        <a href="<?php echo get_site_url(); ?>"><img src="<?php echo XDAC_PLUGIN_URL . 'assets/images/logo.png'; ?>"></a>
    </div>
    <div class="tabs-login-register">
        <ul class="nav nav-pills nav-justified">
            <li role="presentation" class="xdac-register"><a href="javascript:void(0);"><?php _e('Register', 'xdac_wp_client'); ?></a></li>
            <li role="presentation" class="xdac-login"><a href="javascript:void(0);"><?php _e('Login', 'xdac_wp_client'); ?></a></li>
        </ul>

        <p class="login-register-description"><?php _e('Recover password to your account', 'xdac_wp_client'); ?></p>
    </div>
    <div class="xdac-client-form">
        <form class="" action="" method="post">
            <input type="hidden" name="xdac_client_form" value="recover"/>
            <div>
                <input type="email" name="email" value="" placeholder="Email"/>
            </div>

            <input class="xdac-submit-form recover-submit" type="submit" value="<?php _e('RECOVER PASSWORD', 'xdac_wp_client'); ?>"/>

        </form>
    </div>
</div>
<?php include_once( XDAC_ABSPATH.'/templates/footer.php' ); ?>