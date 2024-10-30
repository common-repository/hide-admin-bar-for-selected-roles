<?php
/*
 * Plugin Name:       Hide Admin Bar for Selected Roles
 * Plugin URI:        
 * Description:       Hide the WordPress admin bar on the frontend for selected user roles, enhancing user experience by decluttering the interface.
 * Version:           1.1
 * Requires at least: 4.0
 * Requires PHP:      7.0
 * Author:            Mayursinh Vadher
 * Author URI:        https://www.geocities.ws/mayursinhvadher/
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hide-admin-bar-for-selected-roles
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Register settings with sanitization callback
function hasr_register_settings() {
    register_setting('hasr_options_group', 'hasr_roles_to_hide', 'hasr_sanitize_roles');
}
add_action('admin_init', 'hasr_register_settings');

// Sanitization callback function
function hasr_sanitize_roles($input) {
    // Sanitize input by trimming spaces and sanitizing text
    if (is_string($input)) {
        $roles = array_map('sanitize_text_field', array_map('trim', explode(',', $input)));
        return implode(',', $roles); // Return sanitized roles as a comma-separated string
    }
    return '';
}

// Add settings page
function hasr_add_admin_menu() {
    add_options_page('Hide Admin Bar Settings', 'Hide Admin Bar', 'manage_options', 'hide_admin_bar', 'hasr_options_page');
}
add_action('admin_menu', 'hasr_add_admin_menu');

// Create the settings page
function hasr_options_page() {
    ?>
    <div class="wrap">
        <h1>Hide Admin Bar Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('hasr_options_group'); ?>
            <?php $roles_to_hide = get_option('hasr_roles_to_hide', ''); ?>
            <label for="hasr_roles_to_hide">Roles to hide admin bar (comma separated):</label><br>
            <input type="text" id="hasr_roles_to_hide" name="hasr_roles_to_hide" value="<?php echo esc_attr($roles_to_hide); ?>" class="regular-text" /><br><br>
            <input type="submit" value="Save Changes" class="button button-primary">
        </form>
    </div>
    <?php
}

// Hide the admin bar for selected roles on the frontend
function hasr_hide_admin_bar_for_selected_roles() {
    // Get the roles to hide from the options
    $roles_to_hide = array_map('trim', explode(',', get_option('hasr_roles_to_hide', '')));

    // Check if the user is logged in and has one of the specified roles
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        foreach ($roles_to_hide as $role) {
            if (in_array($role, $user->roles)) {
                show_admin_bar(false);
                break; // Exit the loop once the admin bar is hidden
            }
        }
    }
}

// Hook into the 'after_setup_theme' action
add_action('after_setup_theme', 'hasr_hide_admin_bar_for_selected_roles');
