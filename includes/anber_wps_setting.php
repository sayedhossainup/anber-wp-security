<?php
/**
 *
 * @link              https://github.com/frahim
 * @since             1.0.0
 * @package           Anber_wp_security

 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

add_action('admin_menu', 'anber_wp_security_menu');

function anber_wp_security_menu() {
    add_options_page(
            'Anber WP Security Settings',
            'Anber WP Security',
            'manage_options',
            'anber-wp-security',
            'anber_wp_security_settings_page'
    );
}

function anber_wp_security_settings_page() {
    ?>

<section id="wrapper">

       <h2 class="pagetitle mb-30"><?php echo esc_html(get_admin_page_title()); ?></h2>
      <!-- Tab links -->
      <div class="tabs">
         <button class="tablinks active" data-country="general-settings"><p data-title="General">General</p></button>
         <button class="tablinks" data-country="style-settings"><p data-title="Security Headers">Security Headers</p></button>         
      </div>
      
       <!-- Tab content -->
      <div class="wrapper_tabcontent">
         <div id="general-settings" class="tabcontent active">
           <form method="post" action="options.php">
                <?php
                settings_fields('anber_wp_security_options_group');
                do_settings_sections('anber-wp-security');
                submit_button();
                ?>
            </form>
         </div>

         <div id="style-settings" class="tabcontent">
           <form method="post" action="options.php">
                <?php
                settings_fields('anber_wp_security_header_group');
                do_settings_sections('anber_wp_security_header');
                submit_button();
                ?>
            </form>
         </div>

         
      </div>

</section>


    <?php
}

add_action('admin_init', 'anber_wp_security_settings');

function anber_wp_security_settings() {
    register_setting('anber_wp_security_options_group', 'anber_wp_security_option');
    register_setting('anber_wp_security_options_group', 'anber_wp_security_lla_option');
    register_setting('anber_wp_security_options_group', 'anber_wp_security_enable_ip');
    register_setting('anber_wp_security_options_group', 'anber_wp_security_ip');
    register_setting('anber_wp_security_header_group', 'anber_wp_security_xss_protection');
    register_setting('anber_wp_security_header_group', 'anber_wp_security_content_security_policy');
    register_setting('anber_wp_security_header_group', 'anber_wp_security_hsts');
    register_setting('anber_wp_security_header_group', 'anber_wp_security_prevent_clickjacking');
    register_setting('anber_wp_security_header_group', 'anber_wp_security_prevent_content_sniffing');
    register_setting('anber_wp_security_header_group', 'anber_wp_security_referrer_policy');

    add_settings_section(
            'anber_wp_security_main_section',
            '',
            'anber_wp_security_section_text',
            'anber-wp-security'
    );

    add_settings_field(
            'anber_wp_security_option',
            'Disable File Editing in WordPress',
            'anber_wp_security_checkbox_callback',
            'anber-wp-security',
            'anber_wp_security_main_section'
    );
    
     add_settings_field(
            'anber_wp_security_lla_option',
            'Enable limit login attempts',
            'anber_wp_security_lla_callback',
            'anber-wp-security',
            'anber_wp_security_main_section'
    );

    add_settings_field(
            'anber_wp_security_enable_ip',
            'Enable IP Whitelisting',
            'anber_wp_security_ip_checkbox_callback',
            'anber-wp-security',
            'anber_wp_security_main_section'
    );

    add_settings_field(
            'anber_wp_security_ip',
            'Your IPv4 IP address',
            'anber_wp_security_ip_callback',
            'anber-wp-security',
            'anber_wp_security_main_section'
    );

    add_settings_section(
            'anber_wp_security_header_section',
            'Add Security Headers',
            'anber_wp_security_header_text',
            'anber_wp_security_header'
    );
    add_settings_field(
            'anber_wp_security_xss_protection',
            'Enable XSS Protection',
            'anber_wp_security_xss_protection_callback',
            'anber_wp_security_header',
            'anber_wp_security_header_section'
    );
    add_settings_field(
            'anber_wp_security_content_security_policy',
            'Enable Content Security Policy',
            'anber_wp_security_content_security_policy_callback',
            'anber_wp_security_header',
            'anber_wp_security_header_section'
    );
    add_settings_field(
            'anber_wp_security_hsts',
            'Enable HSTS (HTTP Strict Transport Security)',
            'anber_wp_security_hsts_callback',
            'anber_wp_security_header',
            'anber_wp_security_header_section'
    );
    add_settings_field(
            'anber_wp_security_prevent_clickjacking',
            'Enable X-Frame-Options (Clickjacking Protection)',
            'anber_wp_security_prevent_clickjacking_callback',
            'anber_wp_security_header',
            'anber_wp_security_header_section'
    );
    add_settings_field(
            'anber_wp_security_prevent_content_sniffing',
            'Enable X-Content-Type-Options (Prevent MIME Sniffing)',
            'anber_wp_security_prevent_content_sniffing_callback',
            'anber_wp_security_header',
            'anber_wp_security_header_section'
    );
    add_settings_field(
            'anber_wp_security_referrer_policy',
            'Enable Referrer-Policy',
            'anber_wp_security_referrer_policy_callback',
            'anber_wp_security_header',
            'anber_wp_security_header_section'
    );
}

function anber_wp_security_section_text() {
    echo '';
}

function anber_wp_security_checkbox_callback() {
    // Get the saved value of the option
    $option = get_option('anber_wp_security_option');
    
    // Check if the option is set, and check the box if the value is '1'
    $checked = isset($option) && $option === '1' ? 'checked' : '';
    
    // Output the switch-styled checkbox
    echo '
    <label class="switch" for="anber_wp_security_option">
        <input type="checkbox" id="anber_wp_security_option" name="anber_wp_security_option" value="1" ' . $checked . ' />
        <div class="slider round"></div>
    </label>';
    
  
}

function anber_wp_security_ip_checkbox_callback() {
    $option = get_option('anber_wp_security_enable_ip');
    $checked = isset($option) && $option === '1' ? 'checked' : '';
    echo '<label class="switch" for="anber_wp_security_enable_ip"><input type="checkbox" id="anber_wp_security_enable_ip" name="anber_wp_security_enable_ip" value="1" ' . $checked . ' /><div class="slider round"></div></label>';
}

function anber_wp_security_ip_callback() {
    $ip = get_option('anber_wp_security_ip', ''); // Fetch the saved IPs

    // Output a textarea instead of a single-line input to handle multiple IPs
    echo '<textarea id="anber_wp_security_ip" name="anber_wp_security_ip" rows="5" cols="30">' . esc_textarea($ip) . '</textarea>';
    echo '<p>Enter multiple IPs separated by commas.</p>';
    echo "<p>If you're unsure of your IP address, please <a target='_blank' href='https://whatismyipaddress.com/'>check here</a>.</p>";
}



function anber_wp_security_header_text() {
    echo 'Add security headers to your website for added protection:';
}

function anber_wp_security_xss_protection_callback() {
    $option = get_option('anber_wp_security_xss_protection');
    $checked = isset($option) && $option === '1' ? 'checked' : '';
    echo '<label class="switch" for="anber_wp_security_xss_protection"><input type="checkbox" id="anber_wp_security_xss_protection" name="anber_wp_security_xss_protection" value="1" ' . $checked . ' /><div class="slider round"></div></label>';
}
function anber_wp_security_lla_callback() {
    $option = get_option('anber_wp_security_lla_option');
    $checked = isset($option) && $option === '1' ? 'checked' : '';
    echo '<label class="switch" for="anber_wp_security_lla_option"><input type="checkbox" id="anber_wp_security_lla_option" name="anber_wp_security_lla_option" value="1" ' . $checked . ' /><div class="slider round"></div></label>';
}

function anber_wp_security_content_security_policy_callback() {
    $option = get_option('anber_wp_security_content_security_policy');
    $checked = isset($option) && $option === '1' ? 'checked' : '';
    echo '<label class="switch" for="anber_wp_security_content_security_policy"><input type="checkbox" id="anber_wp_security_content_security_policy" name="anber_wp_security_content_security_policy" value="1" ' . $checked . ' /><div class="slider round"></div></label>';
}

function anber_wp_security_hsts_callback() {
    $option = get_option('anber_wp_security_hsts');
    $checked = isset($option) && $option === '1' ? 'checked' : '';    
    echo '<label class="switch" for="anber_wp_security_hsts"><input type="checkbox" id="anber_wp_security_hsts" name="anber_wp_security_hsts" value="1" ' . $checked . ' /><div class="slider round"></div></label>';
}

function anber_wp_security_prevent_clickjacking_callback() {
    $option = get_option('anber_wp_security_prevent_clickjacking');
    $checked = isset($option) && $option === '1' ? 'checked' : '';    
    echo '<label class="switch" for="anber_wp_security_prevent_clickjacking"><input type="checkbox" id="anber_wp_security_prevent_clickjacking" name="anber_wp_security_prevent_clickjacking" value="1" ' . $checked . ' /><div class="slider round"></div></label>';
}

function anber_wp_security_prevent_content_sniffing_callback() {
    $option = get_option('anber_wp_security_prevent_content_sniffing');
    $checked = isset($option) && $option === '1' ? 'checked' : '';    
    echo '<label class="switch" for="anber_wp_security_prevent_content_sniffing"><input type="checkbox" id="anber_wp_security_prevent_content_sniffing" name="anber_wp_security_prevent_content_sniffing" value="1" ' . $checked . ' /><div class="slider round"></div></label>';
}

function anber_wp_security_referrer_policy_callback() {
    $option = get_option('anber_wp_security_referrer_policy');
    $checked = isset($option) && $option === '1' ? 'checked' : '';    
    echo '<label class="switch" for="anber_wp_security_referrer_policy"><input type="checkbox" id="anber_wp_security_referrer_policy" name="anber_wp_security_referrer_policy" value="1" ' . $checked . ' /><div class="slider round"></div></label>';
}
