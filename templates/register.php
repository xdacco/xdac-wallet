<?php include_once( XDAC_ABSPATH.'/templates/header.php' ); ?>
    <div class="wrapper-login-register">
        <div class="logo-block">
            <a href="<?php echo get_site_url(); ?>"><img src="<?php echo XDAC_PLUGIN_URL . 'assets/images/xDAC-logo.png'; ?>"></a>
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

                /**
                 * If there is a referral link - it should be added
                 */
                $get_referral = !empty($_SESSION['ref']) ? $_SESSION['ref'] : '';
                $referral = !empty($_POST['referral']) ? $_POST['referral'] : $get_referral;

                ?>
            </div>

            <form class="" action="" method="post">
                <input type="hidden" name="xdac_client_form" value="register"/>
                <div <?php if( !empty($reg_errors->errors['first_name']) ) echo 'class="error"'; ?>>
                    <?php if ( !empty($reg_errors->errors['first_name']) )  echo '<p class="xdac-client-errors">' . $reg_errors->errors['first_name'][0] . '</p>'; ?>
                    <input type="text" name="fname" value="<?php echo !empty($_POST['fname']) ? $_POST['fname'] : ''; ?>" placeholder="First Name *"  />
                </div>

                <div <?php if( !empty($reg_errors->errors['last_name']) ) echo 'class="error"'; ?>>
                    <?php if ( !empty($reg_errors->errors['last_name']) )  echo '<p class="xdac-client-errors">' . $reg_errors->errors['last_name'][0] . '</p>'; ?>
                    <input type="text" name="lname" value="<?php echo !empty($_POST['lname']) ? $_POST['lname'] : ''; ?>" placeholder="Last Name *"  />
                </div>

                <div <?php if( !empty($reg_errors->errors['email']) ) echo 'class="error"'; ?>>
                    <?php if ( !empty($reg_errors->errors['email']) ) echo '<p class="xdac-client-errors">' . $reg_errors->errors['email'][0] . '</p>'; ?>
                    <input type="email" name="email" value="<?php echo !empty($_POST['email']) ? $_POST['email'] : ''; ?>" placeholder="Email *" required />
                </div>

                <div <?php if( !empty($reg_errors->errors['password']) ) echo 'class="error"'; ?>>
                    <?php if ( !empty($reg_errors->errors['password']) ) echo '<p class="xdac-client-errors">' . $reg_errors->errors['password'][0] . '</p>'; ?>
                    <input type="password" name="password" value="" placeholder="Password *" required />
                </div>

                <div <?php if( !empty($reg_errors->errors['referral']) ) echo 'class="error"'; ?>>
                    <?php if ( !empty($reg_errors->errors['referral']) ) echo '<p class="xdac-client-errors">' . $reg_errors->errors['referral'][0] . '</p>'; ?>
                    <input  type="text"
                            name="referral"
                            value="<?php echo $referral; ?>"
                            placeholder="Referral Name or ID"
                            <?php if(!empty($get_referral)) echo 'disabled'; ?>
                            maxlength="10"
                            pattern="^([a-zA-Z])[a-zA-Z_-]*[\w_-]*[\S]$|^([a-zA-Z])[0-9_-]*[\S]$|^[a-zA-Z]*[\S]$"
                            title="Referral Name or ID should contain only numbers and letters. e.g. XExaMple18"/>
                </div>

                <input class="xdac-submit-form" type="submit" value="<?php _e('REGISTER', 'xdac_wp_client'); ?>"/>

                <p class="xdac-register-terms">
                    <?php _e('By registering you agree to ', 'xdac_wp_client'); ?>
                    <a href="https://www.xdac.co/terms/" target="_blank"><?php _e('Website Terms of Use', 'xdac_wp_client'); ?></a>
                    <?php _e(' and the ', 'xdac_wp_client'); ?>
                    <a href="https://xdac.co/docs/xDAC-Token-Sale-Terms.pdf" target="_blank"><?php _e('Token Sale Terms and Conditions', 'xdac_wp_client'); ?></a>
                    <?php _e(' as well as the ', 'xdac_wp_client'); ?>
                    <a href="https://www.xdac.co/privacy-policy/"  target="_blank"><?php _e('Privacy Policy', 'xdac_wp_client'); ?></a>
                </p>

            </form>
        </div>
    </div>
<?php include_once( XDAC_ABSPATH.'/templates/footer.php' ); ?>