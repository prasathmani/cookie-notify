<?php
/**
 * Plugin Name: Cookie Notify
 * Description: In simple way add personalized cookie info and link to wordpress privacy policy page.
 * Version: 1.3
 * Requires at least: 6.0.2
 * Requires PHP: 7.0
 * Author: CCP Programmers
 * Author URI: https://github.com/prasathmani
 * Text Domain: ccpprogrammers
 * Domain Path: /languages
 * License: GPLv3
 * 
 * Cookie Notify is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *   
 * Cookie Notify is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with Simple Cookie Notice. If not, see http://www.gnu.org/licenses/gpl.html.
 */

defined( 'ABSPATH' ) or die( 'hey, you don\'t have an access to read this site' );


// adding 'Settings' link to plugin links
function ccpp_add_plugin_settings_link( $links ) {
    $url = admin_url()."options-general.php?page=privacy-policy";
    $settings_link = '<a href="'.esc_url( $url ).'">'.esc_html( 'Settings' ).'</a>';
    $links[] = $settings_link;
    return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'ccpp_add_plugin_settings_link');


// adding styles and scripts
function ccpp_enqueue_scripts() {
    // load styles and script for plugin only if cookies are not accepted
    if ( !isset( $_COOKIE['cookie-accepted'] ) ) {
        wp_enqueue_style( 'styles', plugins_url( 'styles.css', __FILE__ ) );
        wp_enqueue_script( 'ccpp_script', plugins_url( 'public/js/ccpp_script.js', __FILE__ ), array( 'jquery' ), 1.0, true );
        wp_localize_script( 'ccpp_script', 'ccpp_script_ajax_object',
            array( 
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'ccpp_enqueue_scripts' );


// setting cookie - this function must be called before html code is displayed
function ccpp_set_cookie() {
    // make action when cookie accept button was clicked
    if ( isset( $_POST['cookie-accept-button'] ) ) {
        $domain = explode( 'https://', site_url() );
        if ( ! is_ssl() ) {
            $domain = explode( 'http://', site_url() );
        }
        $domain = explode('/', $domain[1], 2);
        $path = empty($domain[1]) ? "/" : "/".$domain[1]."/";
        setcookie( sanitize_key( 'cookie-accepted' ), 1, time()+3600*24*14, $path );
        $current_url = is_ssl() ? esc_url('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) : esc_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        wp_safe_redirect( $current_url );
        exit;
    }

    // make action when privacy policy button was clicked
    if ( isset( $_POST['cookie-privacy-policy'] ) ) {
        $privacy_policy = get_privacy_policy_url();
        if ( empty($privacy_policy) ) {
            $privacy_policy = get_home_url().'/privacy-policy';
        }
        wp_safe_redirect( $privacy_policy );
        exit;
    }
}
add_action('init', 'ccpp_set_cookie');

// setting cookie - ajax version
function ccpp_set_cookie_ajax() {
    // make action when cookie accept button was clicked - without page reloading
    $domain = explode( 'https://', site_url() );
    if ( ! is_ssl() ) {
        $domain = explode( 'http://', site_url() );
    }
    $domain = explode('/', $domain[1], 2);
    $path = empty($domain[1]) ? "/" : "/".$domain[1]."/";
    // setcookie( sanitize_key( 'cookie-accepted' ), 1, time()+3600*24*14, $path, $domain[0] );     // when add $domain[0], then cookies are setted to domain and all subdomains (in console domain is visible with dot - .domain)
    setcookie( sanitize_key( 'cookie-accepted' ), 1, time()+3600*24*14, $path );
    echo json_encode("cookies-added");
    die();
}
add_action( 'wp_ajax_set_cookie_ajax', 'ccpp_set_cookie_ajax' );
add_action( 'wp_ajax_nopriv_set_cookie_ajax', 'ccpp_set_cookie_ajax' );


// display cookie notice if cookie info is not set
function ccpp_display_cookie_notice() {    
    if ( !isset( $_COOKIE['cookie-accepted'] ) ) {
        add_action('wp_footer', 'ccpp_display_cookie_info');
    }
}
add_action( 'init', 'ccpp_display_cookie_notice');


// allowed html code in plugin message
function ccpp_allowed_html() {
    return array(
        'a' => array(
            'href' => array(),
            'title' => array(),
            'class' => array()
        ),
        'br' => array(),
        'em' => array(),
        'strong' => array(),
        'span' => array(
            'class' => array()
        ),
    );
}

// displaying cookie info on page
function ccpp_display_cookie_info() {
    $cookie_message = get_option( "ccpp-field1-cookie-message", 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies' );
    $cookie_info_button = get_option( "ccpp-field3-cookie-button-text", 'Accept Cookies' );
    $show_policy_privacy = get_option( "ccpp-field2-checkbox-privacy-policy", false );
    $background_color = get_option( "ccpp-field5-background-color", '#444546' );
    $text_color = get_option( "ccpp-field6-text-color", '#ffffff' );
    $button_background_color = get_option( "ccpp-field7-button-background-color", '#dcf1ff' );
    $button_text_color = get_option( "ccpp-field8-button-text-color", '#000000' );
    $cookie_info_placemet = get_option( "ccpp-field4-cookie-plugin-placement", 'bottom' );
    $allowed_html = ccpp_allowed_html();
?>
    <div class="ccpp-cookie-info-container" style="<?php echo 'background-color: '.esc_attr( $background_color ).'; '.esc_attr( $cookie_info_placemet ).': 0' ?>" id="ccpp-cookie-info-container">
       <!-- remove action method!!! -->
        <form method="post" id="cookie-form"> 
            <p class="ccpp-cookie-info" style="<?php echo 'color: '.esc_attr( $text_color ) ?>"><?php echo wp_kses( $cookie_message, $allowed_html ); ?></p>
            <div class="ccpp-buttons">
            <button type="submit" name="cookie-accept-button" class="ccpp-cookie-accept-button" id="cookie-accept-button" style="<?php echo 'background-color: '.esc_attr( $button_background_color ) ?>" ><span class="button-text" style="<?php echo 'color: '.esc_attr( $button_text_color ) ?>"><?php echo esc_html( $cookie_info_button ); ?></span></button>
            <?php if ( $show_policy_privacy ) { ?>
            <button type="submit" name="cookie-privacy-policy" class="ccpp-cookie-privacy-policy" id="cookie-privacy-policy" style="<?php echo 'background-color: '.esc_attr( $button_background_color ) ?>"><span class="button-text" style="<?php echo 'color: '.esc_attr( $button_text_color ) ?>"><?php esc_html_e( 'Privacy Policy', 'ccpp' ) ?></span></button>
            <?php } ?>
            </div>
        </form>
    </div>
<?php
}


// adding new page to admin menu
add_action( 'admin_menu', 'ccpp_add_new_page' );
function ccpp_add_new_page() {
    add_submenu_page(
        'options-general.php',                                  // $parent_slug
        'Cookie Notify',                                       // $page_title
        'Cookie Notify',                                       // $menu_title
        'manage_options',                                       // $capability
        'privacy-policy',                                       // $menu_slug
        'ccpp_page_html_content'                    // $function
    );
}


// adding settings and sections to page in admin menu
function ccpp_add_new_settings() {
    // register settings
    $configuration_settins_field1_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'ccpp_sanitize_textarea_field',
        'default' => 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies'
    );
    $configuration_settins_field2_arg = array(
        'type' => 'boolean',
        'sanitize_callback' => 'ccpp_sanitize_checkbox',
        'default' => false
    );
    $configuration_settins_field3_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'ccpp_sanitize_input_field',
        'default' => 'Accept Cookies'
    );
    $configuration_settins_field4_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'ccpp_sanitize_input_field',
        'default' => 'bottom'
    );
    $layout_settins_field1_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'ccpp_sanitize_color_input',
        'default' => '#444546'
    );
    $layout_settins_field2_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'ccpp_sanitize_color_input',
        'default' => '#ffffff'
    );
    $layout_settins_field3_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'ccpp_sanitize_color_input',
        'default' => '#dcf1ff'
    );
    $layout_settins_field4_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'ccpp_sanitize_color_input',
        'default' => '#000000'
    );
    register_setting( 'jl_options', 'ccpp-field1-cookie-message', $configuration_settins_field1_arg);     // option group, option name, args
    register_setting( 'jl_options', 'ccpp-field2-checkbox-privacy-policy', $configuration_settins_field2_arg);
    register_setting( 'jl_options', 'ccpp-field3-cookie-button-text', $configuration_settins_field3_arg);
    register_setting( 'jl_options', 'ccpp-field4-cookie-plugin-placement', $configuration_settins_field4_arg);
    register_setting( 'jl_options', 'ccpp-field5-background-color', $layout_settins_field1_arg);
    register_setting( 'jl_options', 'ccpp-field6-text-color', $layout_settins_field2_arg);
    register_setting( 'jl_options', 'ccpp-field7-button-background-color', $layout_settins_field3_arg);
    register_setting( 'jl_options', 'ccpp-field8-button-text-color', $layout_settins_field4_arg);

    // adding sections
    add_settings_section( 'ccpp_section_1_configuration', 'Configuration', null, 'jl-slug' );  // id (Slug-name to identify the section), title, callback, page slug
    add_settings_section( 'ccpp_section_2_layout', 'Layout', null, 'jl-slug-2' );

    // adding fields for section
    add_settings_field( 'field-1-cookie-message', 'Cookie Message', 'ccpp_field_1_callback', 'jl-slug', 'ccpp_section_1_configuration' );       // id (Slug-name to identify the field), title, callback, slug-name of the settings page on which to show the section, section, args (attr for field)
    add_settings_field( 'field-2-privacy-policy-button', 'Display Privacy Policy Button', 'ccpp_field_2_callback', 'jl-slug', 'ccpp_section_1_configuration' );
    add_settings_field( 'field-3-cookie-button-text', 'Cookie Button Text', 'ccpp_field_3_callback', 'jl-slug', 'ccpp_section_1_configuration' );
    add_settings_field( 'field-4-cookie-plugin-placement', 'Cookie info placement', 'ccpp_field_4_callback', 'jl-slug', 'ccpp_section_1_configuration' );
    add_settings_field( 'field-5-cookie-background-color', 'Background color', 'ccpp_field_5_callback', 'jl-slug-2', 'ccpp_section_2_layout' );
    add_settings_field( 'field-6-cookie-text-color', 'Text color', 'ccpp_field_6_callback', 'jl-slug-2', 'ccpp_section_2_layout' );
    add_settings_field( 'field-7-cookie-button-background-color', 'Button background color', 'ccpp_field_7_callback', 'jl-slug-2', 'ccpp_section_2_layout' );
    add_settings_field( 'field-8-cookie-button-text-color', 'Button text color', 'ccpp_field_8_callback', 'jl-slug-2', 'ccpp_section_2_layout' );
}
add_action( 'admin_init', 'ccpp_add_new_settings' );


// field 1 - cookie message
function ccpp_field_1_callback() {
    echo '<textarea type="text" cols="50" rows="4" name="ccpp-field1-cookie-message" >'.esc_textarea( get_option( "ccpp-field1-cookie-message", 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies' ) ).'</textarea>';
}

// field 2 - show privacy policy button
function ccpp_field_2_callback() {
    if ( get_option( "ccpp-field2-checkbox-privacy-policy", false ) ) {
        echo '<input type="checkbox" name="ccpp-field2-checkbox-privacy-policy" checked />';
        echo ' <a href="'.esc_url(admin_url()."options-privacy.php").'" style="margin-left: 20px">Set Privacy Policy Page</a>';
    } else {
        echo '<input type="checkbox" name="ccpp-field2-checkbox-privacy-policy" />';
    }
}

// field 3 - cookie button text
function ccpp_field_3_callback() {
    echo '<input type="text" name="ccpp-field3-cookie-button-text" value="'.esc_html( get_option( "ccpp-field3-cookie-button-text", 'Accept Cookies' ) ).'" />';
}

// field 4 - cookie info placement
function ccpp_field_4_callback() {
    $isChecked = get_option( "ccpp-field4-cookie-plugin-placement", 'bottom' );
    ?>
    <input type="radio" name="ccpp-field4-cookie-plugin-placement" value="top" <?php echo esc_html( $isChecked ) === 'top' ? "checked" : null ?> /> Top <br><br>
    <input type="radio" name="ccpp-field4-cookie-plugin-placement" value="bottom" <?php echo esc_html( $isChecked ) === 'bottom' ? "checked" : null ?> /> Bottom
    <?php
}

// field 5 - background color
function ccpp_field_5_callback() {
    echo '<input type="color" name="ccpp-field5-background-color" value="'.esc_html( get_option( "ccpp-field5-background-color", '#444546' ) ).'" />';
}

// field 6 - text color
function ccpp_field_6_callback() {
    echo '<input type="color" name="ccpp-field6-text-color" value="'.esc_html( get_option( "ccpp-field6-text-color", '#ffffff' ) ).'" />';
}

// field 7 - button background color
function ccpp_field_7_callback() {
    echo '<input type="color" name="ccpp-field7-button-background-color" value="'.esc_html( get_option( "ccpp-field7-button-background-color", '#dcf1ff' ) ).'" />';
}

// field 8 - button text color
function ccpp_field_8_callback() {
    echo '<input type="color" name="ccpp-field8-button-text-color" value="'.esc_html( get_option( "ccpp-field8-button-text-color", '#000000' ) ).'" />';
}

// sanitize textarea
function ccpp_sanitize_textarea_field( $input ) {
    if ( isset( $input ) ) {
        $allowed_html = ccpp_allowed_html();
        $input = wp_kses( $input, $allowed_html );
    }
    return $input;
}

// sanitize input
function ccpp_sanitize_input_field( $input ) {
    if ( isset( $input ) ) {
        $input = sanitize_text_field( $input );
    }
    return $input;
}

// sanitize checkbox
function ccpp_sanitize_checkbox( $checked ) {
    return ( ( isset( $checked ) && true == $checked ) ? true : false );
}

// sanitize color input
function ccpp_sanitize_color_input( $input ) {
    if ( isset( $input ) ) {
        $input = sanitize_hex_color( $input );
    }
    return $input;
}


// adding content to menu page
function ccpp_page_html_content() {
    if ( ! current_user_can( 'manage_options' ) ) {
        ?>
        <div style="font-size: 20px; margin-top: 20px"> <?php echo esc_html_e( "You don't have permission to manage this page", "ccpp" ); ?> </div>
        <?php
        return;
    }

    ?>
    <div class="wrap">
        <h2><?php echo esc_html( 'Cookie Notify') ?></h2>
        <form action="options.php" method="post">
            <?php
            // outpus settings fields (without this there is error after clicking save settings button)
            settings_fields( 'jl_options' );                        // A settings group name. This should match the group name used in register_setting()
            // output setting sections and their fields
            do_settings_sections( 'jl-slug' );                      // The slug name of settings sections you want to output.
            echo "<hr>";
            do_settings_sections( 'jl-slug-2' );                      // The slug name of settings sections you want to output.
            // output save settings button
            submit_button( 'Save Settings', 'primary', 'submit', true );     // Button text, button type, button id, wrap, any other attribute
            //echo 'val '.get_option( "ccpp-field4-cookie-plugin-placement" );
            ?>
        </form>
    </div>
    <?php
}



