<?php
/**
 * Plugin Name: Feed Post Writer
 * Plugin URI: https://github.com/goodevilgenius/feed-post-writer
 * Description: Populate a post with the first entry from a feed
 * Version: 0.6.1
 * Author: Dan Jones
 * Author URI: http://goodevilgenius.org/
 * License: MIT
 */

// Add Admin Settings
require_once(plugin_dir_path(__FILE__) . 'fpw_admin.php');

// Add necessary functions
require_once(plugin_dir_path(__FILE__) . 'fpw_functions.php');

// Setup plugin
require_once(plugin_dir_path(__FILE__) . 'fpw_setup.php');
