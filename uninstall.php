<?php
/**
 * Trigger this file when user uninstall plugin
 * 
 * @package MyPrivacyPolicy
 *
*/

// security check - prevent access fro outside of wordpress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

function jlplg_lovecoding_delete_settings() {
    delete_option( 'jlplg_prvpol-field1-cookie-message' );
    delete_option( 'jlplg_prvpol-field2-checkbox-privacy-policy' );
    delete_option( 'jlplg_prvpol-field3-cookie-button-text' );
    delete_option( 'jlplg_prvpol-field4-background-color' );
    delete_option( 'jlplg_prvpol-field5-text-color' );
    delete_option( 'jlplg_prvpol-field6-button-background-color' );
    delete_option( 'jlplg_prvpol-field7-button-text-color' );
}

jlplg_lovecoding_delete_settings();

function jlplg_lovecoding_delete_cookies() {
    unset( $_COOKIE['cookie-accepted'] );
    setcookie( sanitize_key( 'cookie-accepted' ), '', 1);
}

jlplg_lovecoding_delete_cookies();

