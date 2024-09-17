<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/frahim
 * @since             1.0.0
 * @package           Anber_wp_security

 * @wordpress-plugin
 * Plugin Name:       Anber Wp Security
 * Plugin URI:        https://github.com/frahim
 * Description:       A Security solution for Wordpress
 * Version:           1.0.0
 * Author:            Md Yeasir Arafat
 * Author URI:        https://github.com/frahim/
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       anber-wp-security
 * Domain Path:       /languages
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/* ---------------------------------------------------------------    */

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('ANBER_WP_SECURITY_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-anber-wp-security-activator.php
 */
function activate_anber_wp_security() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-anber-wp-security-activator.php';
    Anber_wp_security_activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-anber-wp-security-deactivator.php
 */
function deactivate_anber_wp_security() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-anber-wp-security-deactivator.php';
    Anber_wp_wsecurity_deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_anber_wp_security');
register_deactivation_hook(__FILE__, 'deactivate_anber_wp_security');



function anber_wp_security_admin_styles() {
    wp_enqueue_style('anber_wp_security_admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
}

add_action('admin_enqueue_scripts', 'anber_wp_security_admin_styles');

function anber_wp_security_admin_scripts() {
    wp_enqueue_script('anber_wp_security_admin_js', plugin_dir_url(__FILE__) . 'assets/js/admin-scripts.js', array('jquery'), null, true);
}

add_action('admin_enqueue_scripts', 'anber_wp_security_admin_scripts');


// Hook to add settings link beside Deactivate

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'anber_wp_security_settings_link' );


function anber_wp_security_settings_link($links) {
    $settings_link = '<a href="/wp-admin/options-general.php?page=anber-wp-security">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

/**
 * Includes files
 */
require_once plugin_dir_path(__FILE__) . 'includes/anber_wps_config.php';
require_once plugin_dir_path(__FILE__) . 'includes/anber_wps_htaccess.php';
require_once plugin_dir_path(__FILE__) . 'includes/anber_wps_setting.php';
require_once plugin_dir_path(__FILE__) . 'includes/anber_wps_login_attempts.php';
require_once plugin_dir_path(__FILE__) . 'includes/anber_wps_upload_restect.php';







/**
 * Deactivation Message
 */
// Enqueue deactivation script
add_action('admin_enqueue_scripts', 'anber_wp_security_enqueue_deactivation_script');
function anber_wp_security_enqueue_deactivation_script($hook_suffix) {
    // Load only on the plugins admin page
    if ($hook_suffix === 'plugins.php') {
        // Enqueue the Bootstrap CSS and JS (optional if not already included)
        wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js', array('jquery'), '4.3.1', true);

        // Enqueue the custom deactivation confirmation script
        wp_enqueue_script('anber-wp-security-deactivation-script', plugin_dir_url(__FILE__) . 'assets/js/deactivate-confirm.js', array('jquery'), '1.0', true);
    }
}


// Add modal HTML to the admin footer
add_action('admin_footer', 'anber_wp_security_add_deactivation_modal');
function anber_wp_security_add_deactivation_modal() {
    // Only display the modal on the plugins page
    $screen = get_current_screen();
    if ($screen->id === 'plugins') {
        ?>
        <!-- Modal HTML -->
        <div id="deactivationModal" class="modal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Confirm Deactivation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <p>Are you sure you want to deactivate this plugin? Please read this message carefully.</p>
                <p>Before deactivating this plugin, please ensure that all options are disabled. Otherwise, you may encounter access issues, and you will need to manually remove code from the .htaccess and wp-config files.</p>
              </div>
              <div class="modal-footer">
                <button type="button" id="confirmDeactivate" class="btn btn-danger">Deactivate</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              </div>
            </div>
          </div>
        </div>
        <?php
    }
}

