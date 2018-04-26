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
                $userdata = array(
                    'user_login'    =>   sanitize_user($email),
                    'user_email'    =>   $email,
                    'user_pass'     =>   $password,
                    'first_name'    =>   $first_name,
                    'last_name'     =>   $last_name
                );
                $user = wp_insert_user( $userdata );
                if($user){
                    update_user_meta( $user, 'referal_id', $referral);
                    wp_redirect(get_site_url(null, 'test-plugin-login'));
                }
            }
        }

        public function registration_validation($email, $password){

            global $reg_errors;
            $reg_errors = new WP_Error();

            if ( strlen( $password ) < 6 ) {
                $reg_errors->add( 'password', 'Password length must be greater than 6' );
            }

            if ( !is_email( $email ) ) {
                $reg_errors->add( 'email_invalid', 'Email is not valid' );
            }

            if ( email_exists( $email ) ) {
                $reg_errors->add( 'email', 'Email Already in use' );
            }

            return count($reg_errors->get_error_messages()) == 0;
        }

        protected function login(){

        }

        protected function recover(){

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
        }


        function xdac_page_template( $page_template )
        {
            if ( is_page( 'test-plugin-register' ) ) {
                $page_template = XDAC_ABSPATH.'/templates/register.php';
            }
            if ( is_page( 'test-plugin-login' ) ) {
                $page_template = XDAC_ABSPATH.'/templates/login.php';
            }
            if ( is_page( 'test-plugin-recover' ) ) {
                $page_template = XDAC_ABSPATH.'/templates/recover.php';
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
        }

        /**
         * Init plugin
         */
        function init(){
            // admin only
            if( is_admin() ) {
            }

            //add_action("wp_enqueue_scripts", array($this, 'register_scripts'));

            add_shortcode( 'xdac-client-register', array($this, 'xdac_client_register'));
            add_shortcode( 'xdac-client-login', array($this, 'xdac_client_login'));
            add_shortcode( 'xdac-client-recover', array($this, 'xdac_client_recover'));
        }

        public function register_scripts() {
            wp_enqueue_style('xdac-client', XDAC_PLUGIN_URL.'assets/css/xdac-client.css', array('appai-blog', 'bootstrap-css'), $this->version);
            wp_register_script( 'xdac-client-js', XDAC_PLUGIN_URL . 'assets/js/xdac-client.js' , '', $this->version, true );
        }

        public function xdac_client_register(){
            ob_start();
            include_once( XDAC_ABSPATH.'/templates/register.php' );
            return ob_get_clean();
        }

        public function xdac_client_login(){
            ob_start();
            include_once( XDAC_ABSPATH.'/templates/login.php' );
            return ob_get_clean();
        }

        public function xdac_client_recover(){
            ob_start();
            include_once( XDAC_ABSPATH.'/templates/recover.php' );
            return ob_get_clean();
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