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

// Get the checkbox option value
$option = get_option('anber_wp_security_lla_option');
$limit_login_attempts = $option === '1';

if ($limit_login_attempts) {

    // Set the maximum login attempts and lockout duration
    define('MAX_LOGIN_ATTEMPTS', 3);  // Maximum allowed failed attempts
    define('LOCKOUT_TIME', 900);  // Lockout time in seconds (900 = 15 minutes)

    // Track failed login attempts
    add_action('wp_login_failed', 'track_failed_login', 10, 1);

    function track_failed_login($username) {
        // Check if REMOTE_ADDR is set
        if (isset($_SERVER['REMOTE_ADDR'])) {
            // Unslash and sanitize the IP address
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
            $attempts = get_transient('failed_login_attempts_' . $ip);

            if ($attempts) {
                $attempts++;
            } else {
                $attempts = 1;
            }

            set_transient('failed_login_attempts_' . $ip, $attempts, LOCKOUT_TIME);

            // If attempts exceed the maximum allowed, log it and maybe email the admin
            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                error_log("User locked out after $attempts failed attempts. IP: $ip");
            }
        }
    }

    // Check login attempts before authenticating
    add_filter('authenticate', 'check_login_attempts', 30, 3);

    function check_login_attempts($user, $username, $password) {
        // Check if REMOTE_ADDR is set
        if (isset($_SERVER['REMOTE_ADDR'])) {
            // Unslash and sanitize the IP address
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
            $attempts = get_transient('failed_login_attempts_' . $ip);

            if ($attempts && $attempts >= MAX_LOGIN_ATTEMPTS) {
                return new WP_Error('too_many_attempts', __('You have exceeded the maximum number of login attempts. Please try again later.'));
            }
        }

        return $user;
    }

    // Clear failed login attempts on successful login
    add_action('wp_login', 'clear_failed_login_attempts', 10, 2);

    function clear_failed_login_attempts($user_login, $user) {
        // Check if REMOTE_ADDR is set
        if (isset($_SERVER['REMOTE_ADDR'])) {
            // Unslash and sanitize the IP address
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
            delete_transient('failed_login_attempts_' . $ip);
        }
    }

}
