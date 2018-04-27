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

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if( !class_exists('XdacClient') ):

    class XdacClient {

        const PAGE_REGISTER = 'test-plugin-register';
        const PAGE_LOGIN    = 'test-plugin-login';
        const PAGE_RECOVER  = 'test-plugin-recover';

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

            if($this->registration_validation($email, $password)){

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
                    update_user_meta( $user, 'referal_id', $referral);

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

        public function registration_validation($email, $password){

            global $reg_errors;
            $reg_errors = new WP_Error();

            if ( strlen( $password ) < 6 ) {
                $reg_errors->add( 'password', __('Password length must be greater than 6', 'xdac_wp_client'));
            }

            if ( !is_email( $email ) ) {
                $reg_errors->add( 'email_invalid', __('Email is not valid', 'xdac_wp_client'));
            }

            if ( email_exists( $email ) ) {
                $reg_errors->add( 'email', __('Email Already in use', 'xdac_wp_client'));
            }

            return count($reg_errors->get_error_messages()) == 0;
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
                $this->xdac_send_password_reset_mail($email);
                $recover_message = __('Check your e-mail for the confirmation link', 'xdac_wp_client');
            }
        }

        private function xdac_send_password_reset_mail($email){

            $user = get_user_by('email', $email);
            $firstname = $user->first_name;
            $user_login = $user->user_login;
            $reset_key = get_password_reset_key( $user );

            $link = '<a href="' . home_url(self::PAGE_RECOVER) . '?key='.$reset_key.'&login='.rawurlencode($user_login).'>' . home_url(self::PAGE_RECOVER).'?key='.$reset_key.'&login='.rawurlencode($user_login).'</a>';

            if ($firstname == "") $firstname = "User";
            $message = __("Hi ", 'xdac_wp_client').$firstname.",<br>";
            $message .= __("To reset your password, visit the following address: <br>", 'xdac_wp_client');
            $message .= $link.'<br>';

            $subject = __("Recover password on ".get_bloginfo( 'name'), 'xdac_wp_client');
            $headers = array();

            add_filter( 'wp_mail_content_type', function( $content_type ) {return 'text/html';});
            $headers[] = __('From: ', 'xdac_wp_client').get_bloginfo( 'name').' <do-not-reply@xdac.co>'."\r\n";
            wp_mail( $email, $subject, $message, $headers);
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
            add_action('admin_notices', array( $this, 'admin_notices' ), 15 );
            add_action('plugins_loaded', array($this, 'init'), 1);

            add_filter( 'page_template', array($this, 'xdac_page_template'), 1);
            add_filter( 'login_redirect', array( $this, 'redirect_after_login' ), 10, 3 );

            add_filter( 'authenticate', array( $this, 'maybe_redirect_at_authenticate' ), 101, 3 );
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