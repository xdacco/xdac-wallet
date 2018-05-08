<?php
/*
Plugin Name: xDAC Registration / Login Forms
Description: xDAC plugin Registration / Login Forms
Author: Dmytro Stepanenko
Version: 0.1
License: GPL
Text Domain: xdac_wp_client
*/
/*  Copyright 2018 Dmytro Stepanenko (email: dmytro.stepanenko.dev@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once 'classes/PushId.php';

use XDAC\PushId;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if( !class_exists('XdacClient') ):

    class XdacClient {

        const PAGE_REGISTER = 'register';
        const PAGE_LOGIN    = 'login';
        const PAGE_RECOVER  = 'recover';

        /**
         * Complete data transfer version.
         *
         * @var string
         */
        public $version = '0.1';

        /**
         * The single instance of the class.
         *
         * @var XdacClient
         * @since 0.1
         */
        protected static $_instance = null;

        /**
         * Notices (array)
         * @var array
         */
        public $notices = array();

        /**
         * Main XdacClient Instance.
         *
         * Ensures only one instance of XdacClient is loaded or can be loaded.
         *
         * @static
         * @see cdt()
         * @return XdacClient - Main instance.
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function __construct() {
            $this->define_constants();
            $this->init_hooks();

            do_action( 'xdac_client_loaded' );

            add_action( 'init', array( $this, 'process_post') );

        }

        public function process_post(){
            if(!empty($_POST['xdac_client_form'])){
                switch ($_POST['xdac_client_form']){
                    case 'register':
                        $this->registration();
                        break;
                    case 'login':
                        $this->login();
                        break;
                    case 'recover':
                        $this->recover();
                        break;
                    case 'reset':
                        $this->reset();
                        break;
                }
            }
        }

        public function registration(){

            $first_name =   sanitize_text_field( $_POST['fname'] );
            $last_name  =   sanitize_text_field( $_POST['lname'] );
            $email      =   sanitize_email( $_POST['email'] );
            $password   =   esc_attr( $_POST['password'] );
            $referral   =   sanitize_text_field( $_POST['referral'] );

            if($this->registration_validation($first_name, $last_name, $email, $password, $referral)){

                $user_login = sanitize_user($email);

                $userdata = array(
                    'user_login'    =>   $user_login,
                    'user_email'    =>   $email,
                    'user_pass'     =>   $password,
                    'first_name'    =>   $first_name,
                    'last_name'     =>   $last_name
                );
                $user = wp_insert_user( $userdata );

                if($user){

                    $this->xdac_registration_email($email, $first_name);

                    update_user_meta( $user, 'referral_id', $referral);

                    update_user_meta( $user, 'referral_program', PushId::generateRandomString());

                    if (!is_user_logged_in()) {
                        //login
                        wp_set_current_user($user, $user_login);
                        wp_set_auth_cookie($user);
                        do_action('wp_login', $user_login);
                    }

                    wp_redirect(home_url('/account'));
                    exit;
                }
            }
        }

        public function registration_validation($first_name, $last_name, $email, $password, $referral){

            global $reg_errors;
            $reg_errors = new WP_Error();


            if ( empty($first_name) ) {
                $reg_errors->add( 'first_name', __('First Name required', 'xdac_wp_client'));
            }

            if ( empty($last_name) ) {
                $reg_errors->add( 'last_name', __('Last Name required', 'xdac_wp_client'));
            }

            if ( strlen( $password ) < 6 ) {
                $reg_errors->add( 'password', __('Password length must be greater than 6', 'xdac_wp_client'));
            }

            if ( !is_email( $email ) ) {
                $reg_errors->add( 'email', __('Email is not valid', 'xdac_wp_client'));
            }elseif ( email_exists( $email ) ) {
                $reg_errors->add( 'email', __('Email already used', 'xdac_wp_client'));
            }

            if(!empty($referral) && $this->referral_check($referral) <= 0 ){
                $reg_errors->add( 'referral', __('Referral Code is not valid', 'xdac_wp_client'));
            }

            return count($reg_errors->get_error_messages()) == 0;
        }

        public function referral_check($referral){
            global $wpdb;
            $rowcount = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}usermeta WHERE meta_key = 'referral_program' AND meta_value = '$referral'");
            return $rowcount;
        }

        private function xdac_registration_email($email, $first_name){

            $message = __('
			<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
			   <head>
				  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				  <meta
					 name="viewport" content="width=device-width, initial-scale=1.0">
				  <title>xDAC: Registration</title>
				  <style type="text/css">/* Client-specific Styles */
					 #outlook a {padding:0;} /* Force Outlook to provide a "view in browser" menu link. */
					 body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0; background-color: #f5f7fb;}
					 /* Prevent Webkit and Windows Mobile platforms from changing default font sizes, while not breaking desktop design. */
					 .ExternalClass {width:100%;} /* Force Hotmail to display emails at full width */
					 .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing.  More on that: http://www.emailonacid.com/forum/viewthread/43/ */
					 #backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}
					 img {outline:none; text-decoration:none;border:none; -ms-interpolation-mode: bicubic;}
					 a img {border:none;}
					 .image_fix {display:block;}
					 p {margin: 0px 0px !important;}
					 table td {border-collapse: collapse;word-break: break-word;}
					 table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }
					 a {color: #1155cc;text-decoration: none;text-decoration:none!important;}
					 /*STYLES*/
					 table[class=full] { width: 100%; clear: both; }
					 /*################################################*/
					 /*IPAD STYLES*/
					 /*################################################*/
					 @media only screen and (max-width: 640px) {
					 a[href^="tel"], a[href^="sms"] {
					 text-decoration: none;
					 color: #ffffff; /* or whatever your want */
					 pointer-events: none;
					 cursor: default;
					 }
					 .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
					 text-decoration: default;
					 color: #ffffff !important;
					 pointer-events: auto;
					 cursor: default;
					 }
					 table[class=devicewidth] {width: 440px!important;text-align:center!important;}
					 table[class=devicewidthinner] {width: 420px!important;text-align:center!important;}
					 table[class="sthide"]{display: none!important;}
					 img[class="bigimage"]{width: 420px!important;height:219px!important;}
					 img[class="col2img"]{width: 420px!important;height:258px!important;}
					 img[class="image-banner"]{width: 440px!important;height:106px!important;}
					 td[class="menu"]{text-align:center !important; padding: 0 0 10px 0 !important;}
					 td[class="logo"]{padding:10px 0 5px 0!important;margin: 0 auto !important;}
					 img[class="logo"]{padding:0!important;margin: 0 auto !important;}
					 }
					 /*##############################################*/
					 /*IPHONE STYLES*/
					 /*##############################################*/
					 @media only screen and (max-width: 480px) {
					 a[href^="tel"], a[href^="sms"] {
					 text-decoration: none;
					 color: #ffffff; /* or whatever your want */
					 pointer-events: none;
					 cursor: default;
					 }
					 .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
					 text-decoration: default;
					 color: #ffffff !important;
					 pointer-events: auto;
					 cursor: default;
					 }
					 table[class=devicewidth] {width: 280px!important;text-align:center!important;}
					 table[class=devicewidthinner] {width: 260px!important;text-align:center!important;}
					 table[class="sthide"]{display: none!important;}
					 img[class="bigimage"]{width: 260px!important;height:136px!important;}
					 img[class="col2img"]{width: 260px!important;height:160px!important;}
					 img[class="image-banner"]{width: 280px!important;height:68px!important;}
					 }
				  </style>
			   </head>
			   <body style="background-color: #f5f7fb;">
				  <div
					 class="block">
					 <table
						width="100%" bgcolor="#f5f7fb" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="header">
						<tbody>
						   <tr>
							  <td>
								 <table
									width="960" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" hlitebg="edit" shadow="edit">
									<tbody>
									   <tr>
										  <td align="center" bgcolor="#292c3b" width="100%" height="20"><img src="https://www.xdac.co/wp-content/uploads/2018/03/xDAC-logo_800x300.png" alt="xDAC-logo_800x300" width="156" /></td>
									   </tr>
									   <tr>
										  <td>
											 <table
												width="450" cellpadding="0" cellspacing="0" border="0" align="right" class="devicewidth">
												<tbody>
												   <tr>
													  <td
														 width="450" valign="middle" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px; color: #ffffff;line-height: 24px; padding: 10px 0;" align="right" class="menu" st-content="menu"></td>
													  <td
														 width="20"></td>
												   </tr>
												</tbody>
											 </table>
										  </td>
									   </tr>
									</tbody>
								 </table>
							  </td>
						   </tr>
						</tbody>
					 </table>
				  </div>
				  <div
					 class="block">
					 <table
						width="100%" bgcolor="#f5f7fb" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="bigimage">
						<tbody>
						   <tr>
							  <td>
								 <table
									bgcolor="#ffffff" width="960" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidth" modulebg="edit">
									<tbody>
									   <tr>
										  <td>
											 <table
												width="920" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidthinner">
												<tbody>
												   <tr>
													  <td
														 style="font-family: Helvetica, arial, sans-serif; font-size: 13px; color: #95a5a6; text-align:left;line-height: 24px;" st-content="rightimage-paragraph">
														 <table
															border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#292c3b">
														 </table>
														 <h1 style="text-align: center;"><span
															style="color: #000000;"><strong>Welcome to xDAC</strong></span></h1>
														 <h2 style="text-align: center;"><span
															style="color: #000000;"><strong>'.$first_name.'</strong></span></h2>
														 <p
															style="text-align: center;">&nbsp;</p>
														 <p
															style="text-align: center;"><strong><img
															src="https://www.xdac.co/wp-content/uploads/2018/03/User-check.png" alt="" width="87" height="87" /></strong></p>
														 <p
															style="text-align: left;">&nbsp;</p>
														 <p
															style="text-align: left;">Dear<strong>&nbsp;'.$first_name.'</strong>,</p>
														 <p
															style="text-align: left;">&nbsp;</p>
														 <p
															style="text-align: left;">Thank you for your registration at xDAC.co.</p>
														 <p
															style="text-align: left;">Your username:&nbsp;'.$email.'</p>
														 <p
															style="text-align: left;">Login URL: <a
															href="https://www.xdac.co/login/">https://www.xdac.co/login</a></p>
														 <p
															style="text-align: left;">&nbsp;</p>
														 <p
															style="text-align: left;">If you have any questions you can contact us at <a
															href="mailto:support@xdac.co">support@xdac.co</a>.</p>
														 <p
															style="text-align: left;">Thank you and we look forward to helping you build your decentralized company.</p>
														 <p
															style="text-align: left;">The xDAC Team</p>
														 <p
															style="text-align: left;">&nbsp;</p>
														 <p
															style="text-align: left;">&nbsp;</p>
														 <p
															style="text-align: left;">&nbsp;</p>
														 <p
															style="text-align: center;"><a
															href="https://www.xdac.co/"><img
															src="https://www.xdac.co/wp-content/plugins/mailpoet/assets/img/newsletter_editor/social-icons/03-circles/Website.png?mailpoet_version=3.5.1" alt="website" width="32" height="32" /></a>&nbsp;<a
															href="mailto:support@xdac.co"><img
															src="https://www.xdac.co/wp-content/plugins/mailpoet/assets/img/newsletter_editor/social-icons/03-circles/Email.png?mailpoet_version=3.5.1" alt="email" width="32" height="32" /></a>&nbsp;<a
															href="https://t.me/xdacco"><img
															src="https://www.xdac.co/wp-content/uploads/2018/03/Telegram.png" alt="custom" width="32" height="32" /></a>&nbsp;<a
															href="https://twitter.com/xdacco"><img
															src="https://www.xdac.co/wp-content/plugins/mailpoet/assets/img/newsletter_editor/social-icons/03-circles/Twitter.png?mailpoet_version=3.5.1" alt="twitter" width="32" height="32" /></a>&nbsp;<a
															href="https://medium.com/xdac"><img
															src="https://www.xdac.co/wp-content/uploads/2018/03/Medium.png" alt="custom" width="32" height="32" /></a>&nbsp;<a
															href="https://www.reddit.com/user/xdacco"><img
															src="https://www.xdac.co/wp-content/uploads/2018/03/Reddit.png" alt="custom" width="32" height="32" /></a></p>
														 <hr
															/>
														 <p
															style="text-align: center;">Copyright &copy; 2018 xDAC, All rights reserved.</p>
														 <p
															style="text-align: center;"><a
															href="https://www.xdac.co/"><img
															src="https://www.xdac.co/wp-content/uploads/2018/03/xDAC-icon-mono-512x512.png" alt="xDAC-icon-mono-512x512" width="32" /></a></p>
													  </td>
												   </tr>
												   <tr>
													  <td
														 width="100%" height="20"></td>
												   </tr>
												</tbody>
											 </table>
										  </td>
									   </tr>
									</tbody>
								 </table>
							  </td>
						   </tr>
						</tbody>
					 </table>
				  </div>
				  <div
					 class="block">
					 <table
						width="100%" bgcolor="#f5f7fb" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="postfooter">
						<tbody>
						   <tr>
							  <td
								 width="100%">
								 <table
									width="960" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
									<tbody>
									   <tr>
										  <td
											 width="100%" height="5"></td>
									   </tr>
									   <tr>
										  <td
											 align="center" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 10px;color: #999999" st-content="preheader">
											 You are receiving this email because you have registered with xDAC.
										  </td>
									   </tr>
									   <tr>
										  <td
											 width="100%" height="5"></td>
									   </tr>
									</tbody>
								 </table>
							  </td>
						   </tr>
						</tbody>
					 </table>
				  </div>
			   </body>
			</html>
			', 'xdac_wp_client');

            $subject = __("Welcome to xDAC", 'xdac_wp_client');
            $headers = array();

            add_filter( 'wp_mail_content_type', function( $content_type ) {return 'text/html';});
            $headers[] = __('From: ', 'xdac_wp_client').get_bloginfo( 'name').' <info@xdac.co>'."\r\n";
            wp_mail( $email, $subject, $message, $headers);
            wp_mail( 'info@xdac.co', $subject, $message, $headers);
            // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
            remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
        }

        protected function login(){

        }

        protected function reset(){
            global $reset_errors;
            $reset_errors = new WP_Error();

            $reset_key = sanitize_text_field( $_POST['reset_key'] );
            $reset_login = sanitize_text_field( $_POST['reset_login'] );

            $password = esc_attr( $_POST['password'] );
            $repeat_password = esc_attr( $_POST['repeat_password'] );

            if ( strlen( $password ) < 6 ) {
                $reset_errors->add( 'password', __('Password length must be greater than 6', 'xdac_wp_client'));
            }

            if($password != $repeat_password){
                $reset_errors->add( 'password', __('Filled in passwords are different', 'xdac_wp_client'));
            }

            if(count($reset_errors->get_error_messages()) == 0){

                $response = check_password_reset_key($reset_key, $reset_login);

                if(is_wp_error($response)){

                    foreach ( $response->get_error_messages() as $key => $error ) {
                        $reset_errors->add( $key, $error);
                    }

                } else {
                    wp_set_password( $password, $response->ID);
                    if (!is_user_logged_in()) {
                        //login
                        wp_set_current_user($response->ID, $response->user_login);
                        wp_set_auth_cookie($response->ID);
                        do_action('wp_login', $response->user_login);
                    }
                    wp_redirect(home_url('/account'));
                    exit;
                }

            }
        }

        protected function recover(){

            global $recover_message;
            global $recover_errors;
            $recover_errors = new WP_Error();

            $email = sanitize_email($_POST['email']);

            if (!email_exists( $email ) ) {
                $recover_errors->add( "email", __("We don't have any users with that email address.", 'xdac_wp_client'));
            }

            if(count($recover_errors->get_error_messages()) == 0){
                $this->xdac_send_password_reset_email($email);
                $recover_message = __('Check your e-mail for the confirmation link', 'xdac_wp_client');
            }
        }

        private function xdac_send_password_reset_email($email){

            $user = get_user_by('email', $email);
            $firstname = $user->first_name;
            $user_login = $user->user_login;
            $reset_key = get_password_reset_key( $user );

            //$link = '<a href="' . home_url(self::PAGE_RECOVER) . '?key='.$reset_key.'&login='.rawurlencode($user_login).'>' . home_url(self::PAGE_RECOVER).'?key='.$reset_key.'&login='.rawurlencode($user_login).'</a>';
            $link = home_url(self::PAGE_RECOVER) . '?key='.$reset_key.'&login='.rawurlencode($user_login);

            if ($firstname == "") $firstname = "User";
            $message = __("Hi ", 'xdac_wp_client').$firstname.",<br><br>";
            $message .= __("To reset your password, visit the following address: <br>", 'xdac_wp_client');
            $message .= $link.'<br><br>';
            $message .= __("xDAC Support<br>", 'xdac_wp_client');
            $message .= __("www.xdac.co<br>", 'xdac_wp_client');

            $subject = __("Recover password on ".get_bloginfo( 'name'), 'xdac_wp_client');
            $headers = array();

            add_filter( 'wp_mail_content_type', function( $content_type ) {return 'text/html';});
            $headers[] = __('From: ', 'xdac_wp_client').get_bloginfo( 'name').' <info@xdac.co>'."\r\n";
            wp_mail( $email, $subject, $message, $headers);
            wp_mail( 'info@xdac.co', $subject, $message, $headers);

            // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
            remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
        }

        /**
         * Allow this class and other classes to add slug keyed notices (to avoid duplication)
         */
        public function add_admin_notice( $class, $message ) {
            $this->notices[] = array(
                'class'   => $class,
                'message' => $message,
            );
        }

        /**
         * Display any notices we've collected thus far (e.g. for connection, disconnection)
         */
        public function admin_notices() {
            foreach ($this->notices as $notice ) {
                echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
                echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
                echo '</p></div>';
            }
        }

        /**
         * Hook into actions and filters.
         * @since  1.0.0
         */
        private function init_hooks() {

            add_action('init', array( $this, 'xdac_session_start' ), 1);

            add_action('admin_notices', array( $this, 'admin_notices' ), 15 );
            add_action('plugins_loaded', array($this, 'init'), 1);

            add_filter( 'page_template', array($this, 'xdac_page_template'), 1);
            add_filter( 'login_redirect', array( $this, 'redirect_after_login' ), 10, 3 );

            add_filter( 'authenticate', array( $this, 'maybe_redirect_at_authenticate' ), 101, 3 );
        }

        public function xdac_session_start(){
            if(!session_id()) {
                session_start();
            }
        }

        /**
         * Returns the URL to which the user should be redirected after the (successful) login.
         *
         * @param string           $redirect_to           The redirect destination URL.
         * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
         * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
         *
         * @return string Redirect URL
         */
        public function redirect_after_login( $redirect_to, $requested_redirect_to, $user ) {
            $redirect_url = home_url();

            if ( ! isset( $user->ID ) ) {
                return $redirect_url;
            }

            //
            $referral = get_user_meta($user->ID, 'referal_id', true);
            if(!empty($referral)){
                update_user_meta( $user->ID, 'referral_id', $referral);
            }
            delete_user_meta( $user->ID, 'referal_id');

            //
            $referal_program = get_user_meta($user->ID, 'referral_program', true);
            if(empty($referal_program) || strlen($referal_program) != 10){
                update_user_meta( $user->ID, 'referral_program', PushId::generateRandomString());
            }

            if ( user_can( $user, 'manage_options' ) ) {
                // Use the redirect_to parameter if one is set, otherwise redirect to admin dashboard.
                if ( $requested_redirect_to == '' ) {
                    $redirect_url = admin_url();
                } else {
                    $redirect_url = $requested_redirect_to;
                }
            } else {
                // Non-admin users always go to their account page after login
                $redirect_url = home_url('/account');
            }

            return wp_validate_redirect( $redirect_url, home_url() );
        }

        /**
         * Redirect the user after authentication if there were any errors.
         *
         * @param Wp_User|Wp_Error  $user       The signed in user, or the errors that have occurred during login.
         * @param string            $username   The user name used to log in.
         * @param string            $password   The password used to log in.
         *
         * @return Wp_User|Wp_Error The logged in user, or error information if there were errors.
         */
        function maybe_redirect_at_authenticate( $user, $username, $password ) {
            // Check if the earlier authenticate filter (most likely,
            // the default WordPress authentication) functions have found errors
            if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
                if ( is_wp_error( $user ) ) {
                    $error_codes = join( ',', $user->get_error_codes() );

                    $login_url = home_url(self::PAGE_LOGIN);
                    $login_url = add_query_arg( 'login', $error_codes, $login_url );
                    $login_url = add_query_arg( 'email', rawurlencode($_POST['log']), $login_url );

                    wp_redirect( $login_url );
                    exit;
                }
            }

            return $user;
        }

        /**
         * Finds and returns a matching error message for the given error code.
         *
         * @param string $error_code    The error code to look up.
         *
         * @return string               An error message.
         */
        private function get_error_message( $error_code ) {
            switch ( $error_code ) {
                case 'empty_username':
                    return __( 'You do have an email address, right?', 'xdac_wp_client' );
                case 'empty_password':
                    return __( 'You need to enter a password to login.', 'xdac_wp_client' );
                case 'invalid_username':
                    return __(
                        "We don't have any users with that email address. <br/> Maybe you used a different one when signing up?",
                        'xdac_wp_client'
                    );
                case 'incorrect_password':
                    return __(
                        "The password you entered wasn't quite right.",
                        'xdac_wp_client'
                    );
                default:
                    break;
            }

            return __( 'An unknown error occurred. Please try again later.', 'xdac_wp_client' );
        }

        function xdac_page_template( $page_template )
        {
            /**
             * Save the referral code to the session in order to not miss the code,
             * if the user doesn't register at once
             */
            if(is_home() || is_front_page()){
                if(!empty($_GET['ref'])){
                    $_SESSION['ref'] = $_GET['ref'];
                }
            }

            if ( is_page( self::PAGE_REGISTER ) ) {
                if ( is_user_logged_in() ) {
                    wp_redirect(home_url('/account'));
                    exit;
                }
                $page_template = XDAC_ABSPATH.'/templates/register.php';
            }
            if ( is_page( self::PAGE_LOGIN ) ) {
                if ( is_user_logged_in() ) {
                    wp_redirect(home_url('/account'));
                    exit;
                }

                global $login_errors, $old_email;
                // Error messages
                $login_errors = array();

                if ( isset( $_REQUEST['email'] ) ) {
                    $old_email = $_REQUEST['email'];
                }

                if ( isset( $_REQUEST['login'] ) ) {
                    $error_codes = explode( ',', $_REQUEST['login'] );
                    foreach ( $error_codes as $code ) {
                        $login_errors []= $this->get_error_message( $code );
                    }
                }

                $page_template = XDAC_ABSPATH.'/templates/login.php';
            }
            if ( is_page( self::PAGE_RECOVER ) ) {
                if ( is_user_logged_in() ) {
                    wp_redirect(home_url('/account'));
                    exit;
                }

                if(!empty($_GET['key'])){
                    $page_template = XDAC_ABSPATH.'/templates/reset-password.php';
                } else {
                    $page_template = XDAC_ABSPATH.'/templates/recover.php';
                }
            }
            return $page_template;
        }

        /**
         * Define CDT Constants.
         */
        private function define_constants() {
            $this->define( 'XDAC_PLUGIN_FILE', __FILE__ );
            $this->define( 'XDAC_ABSPATH', dirname( __FILE__ ));
            $this->define( 'XDAC_PLUGIN_BASENAME', plugin_basename( __FILE__ ));
            $this->define( 'XDAC_PLUGIN_URL', plugin_dir_url( __FILE__ ));
            $this->define( 'XDAC_VERSION', $this->version );

            $this->define( 'XDAC_URL_LOGIN', self::PAGE_LOGIN );
            $this->define( 'XDAC_URL_REGISTER', self::PAGE_REGISTER );
            $this->define( 'XDAC_URL_RECOVER', self::PAGE_RECOVER );
        }

        /**
         * Init plugin
         */
        function init(){
            // admin only
            if( is_admin() ) {
            }
        }

        /**
         * Define constant if not already set.
         *
         * @param  string $name
         * @param  string|bool $value
         */
        private function define( $name, $value ) {
            if ( ! defined( $name ) ) {
                define( $name, $value );
            }
        }
    }

endif;

/**
 * Main instance of XdacClient.
 *
 * Returns the main instance of XdacClient to prevent the need to use globals.
 *
 * @since  1.0
 * @return XdacClient
 */
function XdacClient() {
    return XdacClient::instance();
}

// Global for backwards compatibility.
$GLOBALS['XdacClient'] = XdacClient();