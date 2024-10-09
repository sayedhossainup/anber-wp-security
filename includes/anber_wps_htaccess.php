<?php

/**
 *
 * @link       https://github.com/frahim
 * @since      1.0.0
 *
 * @package    Anber_wp_security
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function update_htaccess_with_security_rules() {
    
     global $wp_filesystem;
    if (!$wp_filesystem) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        WP_Filesystem();
    }
    
    $htaccess_path = ABSPATH . '.htaccess';

    // Fetch the saved IPs and split them by commas, trimming any whitespace
    $whitelisted_ips = get_option('anber_wp_security_ip', '');
    $whitelisted_ips_array = array_map('trim', explode(',', $whitelisted_ips)); // Creates an array of IPs

    $ip_whitelisting_enabled = get_option('anber_wp_security_enable_ip') === '1';
    $xss_enabled = get_option('anber_wp_security_xss_protection') === '1';
    $csp_enabled = get_option('anber_wp_security_content_security_policy') === '1';
    $hsts_enabled = get_option('anber_wp_security_hsts') === '1';
    $clickjacking_enabled = get_option('anber_wp_security_prevent_clickjacking') === '1';
    $x_content_enabled = get_option('anber_wp_security_prevent_content_sniffing') === '1';
    $referrer_policy_enabled = get_option('anber_wp_security_referrer_policy') === '1';

    $security_rules = "# BEGIN Custom Security Rules\n";
    foreach ($whitelisted_ips_array as $ip) {
        if (!empty($ip)) {
            $security_rules .= "<Files wp-login.php>\nOrder deny,allow\nDeny from all\nAllow from $ip\n</Files>\n";
            $security_rules .= "<Files xmlrpc.php>\nOrder deny,allow\nDeny from all\nAllow from $ip\n</Files>\n";
        }
    }
    $security_rules .= "# END Custom Security Rules";

    // Add headers rules
    $xss_protection_rule = <<<EOT
# BEGIN XSS Protection
<IfModule mod_headers.c>
Header set X-XSS-Protection "1; mode=block"
</IfModule>
# END XSS Protection
EOT;

    $csp_protection_rule = <<<EOT
# BEGIN CSP Protection
<IfModule mod_headers.c>
Header set Content-Security-Policy-Report-Only: "default-src 'self'; script-src 'self' 'unsafe-inline'; connect-src 'self'; img-src 'self'; style-src 'self' 'unsafe-inline';base-uri 'self';form-action 'self';"
</IfModule>
# END CSP Protection
EOT;

    $hsts_protection_rule = <<<EOT
# BEGIN HSTS Protection
<IfModule mod_headers.c>
Header set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
</IfModule>
# END HSTS Protection
EOT;

    $clickjacking_protection_rule = <<<EOT
# BEGIN Clickjacking Protection
<IfModule mod_headers.c>
Header set X-Frame-Options "SAMEORIGIN"
</IfModule>
# END Clickjacking Protection
EOT;

    $x_content_protection_rule = <<<EOT
# BEGIN X-Content-Type-Options
<IfModule mod_headers.c>
Header set X-Content-Type-Options "nosniff"
</IfModule>
# END X-Content-Type-Options
EOT;

    $referrer_policy_rule = <<<EOT
# BEGIN Referrer-Policy
<IfModule mod_headers.c>
Header set Referrer-Policy "no-referrer-when-downgrade"
</IfModule>
# END Referrer-Policy
EOT;

   // Check if .htaccess is writable
if (!$wp_filesystem->is_writable($htaccess_path)) {
    error_log('.htaccess is not writable: ' . $htaccess_path);
    return;
}

    if (file_exists($htaccess_path)) {
        // Read the current .htaccess content
        $current_content = $wp_filesystem->get_contents($htaccess_path);
        if ($current_content === false) {
            error_log('Error reading .htaccess: ' . $htaccess_path);
            return;
        }

        // Apply IP whitelisting
        if ($ip_whitelisting_enabled && strpos($current_content, '# BEGIN Custom Security Rules') === false) {
            $current_content .= "\n" . $security_rules;
        } elseif (!$ip_whitelisting_enabled) {
            $current_content = preg_replace('/# BEGIN Custom Security Rules.*# END Custom Security Rules/s', '', $current_content);
        }

        // Apply other rules similarly (XSS, CSP, etc.)
        $rules_to_check = [
            'XSS' => [$xss_enabled, '# BEGIN XSS Protection', $xss_protection_rule],
            'CSP' => [$csp_enabled, '# BEGIN CSP Protection', $csp_protection_rule],
            'HSTS' => [$hsts_enabled, '# BEGIN HSTS Protection', $hsts_protection_rule],
            'Clickjacking' => [$clickjacking_enabled, '# BEGIN Clickjacking Protection', $clickjacking_protection_rule],
            'XContent' => [$x_content_enabled, '# BEGIN X-Content-Type-Options', $x_content_protection_rule],
            'ReferrerPolicy' => [$referrer_policy_enabled, '# BEGIN Referrer-Policy', $referrer_policy_rule]
        ];

        foreach ($rules_to_check as $rule) {
            [$enabled, $begin_marker, $rule_content] = $rule;
            if ($enabled && strpos($current_content, $begin_marker) === false) {
                $current_content .= "\n" . $rule_content;
            } elseif (!$enabled) {
                $current_content = preg_replace("/$begin_marker.*# END.*/s", '', $current_content);
            }
        }

        // Write the updated content to .htaccess
        if ($wp_filesystem->put_contents($htaccess_path, $current_content) === false) {
            error_log('Error writing to .htaccess: ' . $htaccess_path);
        }
    } else {
        // If .htaccess doesn't exist, create it with the rules
        $new_content = $security_rules;
        foreach ($rules_to_check as $rule) {
            if ($rule[0]) $new_content .= "\n" . $rule[2];
        }

        if ($wp_filesystem->put_contents($htaccess_path, $new_content) === false) {
            error_log('Error creating .htaccess: ' . $htaccess_path);
        }
    }
}

// Hook the function
add_action('admin_init', 'update_htaccess_with_security_rules');


