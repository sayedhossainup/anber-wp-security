<?php

/**
 *
 * @link       https://github.com/frahim
 * @since      1.0.0
 *
 * @package     
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include the necessary WordPress file
require_once(ABSPATH . 'wp-admin/includes/file.php');

function manage_htaccess_in_uploads() {
    // Get the checkbox option value
    $option = get_option('anber_wp_security_dwpu');
    $is_enabled_wpup = $option === '1';
    
    global $wp_filesystem;

    // Initialize the WP_Filesystem
    if (empty($wp_filesystem)) {
        WP_Filesystem();
    }

    // Set the path for the .htaccess file in the /wp-content/uploads/ directory
    $htaccess_path = WP_CONTENT_DIR . '/uploads/.htaccess';

    // Content for the .htaccess file
    $htaccess_content = "
    # Prevent direct access to PHP files in the uploads directory
    <FilesMatch \.php$>
    Order Deny,Allow
    Deny from all
    </FilesMatch>";

    // Check if the option is enabled
    if ($is_enabled_wpup) {
        // If the option is '1', create or update the .htaccess file
        if ($wp_filesystem->is_writable(WP_CONTENT_DIR . '/uploads')) {
            if (!$wp_filesystem->put_contents($htaccess_path, $htaccess_content, FS_CHMOD_FILE)) {
                error_log("Failed to create .htaccess file in the uploads directory.");
            } else {
                error_log(".htaccess file successfully created in the uploads directory.");
            }
        } else {
            error_log("Uploads directory is not writable.");
        }
    } else {
        // If the option is '0' or not '1', remove the .htaccess file
        if ($wp_filesystem->exists($htaccess_path)) {
            if (!$wp_filesystem->delete($htaccess_path)) {
                error_log("Failed to delete .htaccess file in the uploads directory.");
            } else {
                error_log(".htaccess file successfully deleted from the uploads directory.");
            }
        }
    }
}

// Hook to manage the .htaccess file based on the option value during WordPress initialization
add_action('init', 'manage_htaccess_in_uploads');

// Hook to manage the .htaccess file when the plugin is activated
register_activation_hook(__FILE__, 'manage_htaccess_in_uploads');

