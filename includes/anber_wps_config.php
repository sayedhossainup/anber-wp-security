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

function modify_wp_config_based_on_checkbox() {
    global $wp_filesystem;
    if (!$wp_filesystem) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        WP_Filesystem();
    }
    //$wp_filesystem->put_contents('/path/to/file.txt', 'Hello, World!', FS_CHMOD_FILE);

    $wp_config_path = ABSPATH . 'wp-config.php';

    // Check if the file exists
    if (file_exists($wp_config_path)) {
        $config_file = $wp_filesystem->get_contents($wp_config_path);

        // Get the checkbox option value
        $option = get_option('anber_wp_security_option');
        $is_enabled = $option === '1';

        $line_to_add = "define('DISALLOW_FILE_EDIT', true);\ndefine('DISALLOW_FILE_MODS', true);\n";
        $line_comment = "/* That's all, stop editing! Happy publishing. */";

        if ($is_enabled) {
            // Check if the lines are already present
            if (strpos($config_file, "define('DISALLOW_FILE_EDIT', true);") === false) {
                // Insert lines before the comment line
                $insert_position = strpos($config_file, $line_comment);
                if ($insert_position !== false) {
                    $config_file = substr_replace($config_file, $line_to_add, $insert_position, 0);
                    $wp_filesystem->put_contents($wp_config_path, $config_file);
                }
            }
        } else {
            // Remove the lines if checkbox is unchecked
            $config_file = preg_replace('/define\(\'DISALLOW_FILE_EDIT\', true\);\ndefine\(\'DISALLOW_FILE_MODS\', true\);\n/', '', $config_file);
            $wp_filesystem->put_contents($wp_config_path, $config_file);
        }
    }
}

// Hook into admin_init to run the function
add_action('admin_init', 'modify_wp_config_based_on_checkbox');
