<?php
/*
Plugin Name: Strict Plugin Install Blocker
Description: Completely blocks other plugins/themes from installing or activating unless user explicitly confirms. Updates don't require confirmation.
Version: 3.1
Author: Manish Pandey
*/

// Install and Activate Confirmation JavaScript
function strict_install_blocker_enqueue_script() {
    wp_enqueue_script('strict_install_blocker_js', plugins_url('install-blocker.js', __FILE__), array('jquery'), '3.1', true);
}
add_action('admin_enqueue_scripts', 'strict_install_blocker_enqueue_script');

// Plugin Installation Block
function strict_install_blocker_prevent_install($response, $hook_extra) {
    // Plugin ki installation tab block karein jab yeh apna plugin nahi ho
    if (!isset($_GET['confirm_install']) || $_GET['confirm_install'] !== 'yes') {
        if (isset($hook_extra['type']) && $hook_extra['type'] === 'plugin' && $hook_extra['plugin'] !== plugin_basename(__FILE__)) {
            wp_die(
                'Installation blocked. Please confirm installation to proceed.',
                'Installation Blocked',
                array('back_link' => true)
            );
        }
    }
    return $response;
}
add_filter('upgrader_pre_install', 'strict_install_blocker_prevent_install', 10, 2);

// Plugin Activation Block
function strict_install_blocker_prevent_activation($plugin, $network_wide) {
    // Activation block karein jab yeh apna plugin nahi ho
    if (!isset($_GET['confirm_install']) || $_GET['confirm_install'] !== 'yes') {
        if ($plugin !== plugin_basename(__FILE__)) {
            wp_die(
                'Activation blocked. Please confirm activation to proceed.',
                'Activation Blocked',
                array('back_link' => true)
            );
        }
    }
}
add_action('activate_plugin', 'strict_install_blocker_prevent_activation', 10, 2);

// JavaScript for Install and Activate Confirmation
function strict_install_blocker_js_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Install confirmation
            $(document).on('click', '.install-now', function(e) {
                e.preventDefault();
                var userConfirm = confirm('Are you sure you want to install this plugin?');
                if (userConfirm) {
                    var installUrl = $(this).attr('href') + '&confirm_install=yes';
                    window.location.href = installUrl;
                } else {
                    alert('Installation cancelled.');
                }
            });

            // Activate confirmation
            $(document).on('click', '.activate-now', function(e) {
                e.preventDefault();
                var userConfirm = confirm('Are you sure you want to activate this plugin?');
                if (userConfirm) {
                    var activateUrl = $(this).attr('href') + '&confirm_install=yes';
                    window.location.href = activateUrl;
                } else {
                    alert('Activation cancelled.');
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'strict_install_blocker_js_script');
