<?php
/**
 * Plugin Name: WP Github
 * Plugin URI: https://github.com/seinoxygen/wp-github
 * Description: Display users Github public repositories, commits, issues and gists.
 * Author: Pablo Cornehl
 * Author URI: http://www.seinoxygen.com
 * Version: 1.2.4
 *
 * Licensed under the MIT License
 */
require dirname(__FILE__) . '/lib/wpGithubCache.php';
require(dirname(__FILE__) . '/lib/github.php');
require(dirname(__FILE__) . '/inc/shortcodes.php');
require(dirname(__FILE__) . '/inc/widgets.php');

/*
 * Init FRONT END General Style
 * */
add_action('wp_enqueue_scripts', 'wpgithub_style', 20);
function wpgithub_style() {
  wp_enqueue_style('wp-github', plugin_dir_url(__FILE__) . 'css/wp-github.css');

  // If custom stylesheet exists load it.
  $custom = plugin_dir_path(__FILE__) . 'custom.css';
  if (file_exists($custom)) {
    wp_enqueue_style('wp-github-custom', plugin_dir_url(__FILE__) . 'custom.css');
  }
}

/*
 * ADMIN SECTION
 * Add a plugin settings page
 * */
add_action('admin_menu', 'wpgithub_plugin_menu');
add_action('admin_init', 'wpgithub_register_settings');

/**
 * Add option page
 */
function wpgithub_plugin_menu() {
  add_options_page('WP Github Options', 'WP Github', 'manage_options', 'wp-github', 'wpgithub_plugin_options');
}

/*
 * Register Option page Settings
 * */
function wpgithub_register_settings() {
  //register our settings
  register_setting('wp-github', 'wpgithub_cache_time', 'wpgithub_validate_int');
  register_setting('wp-github', 'wpgithub_clear_cache', 'wpgithub_clearcache');
  register_setting('wp-github', 'wpgithub_addPrismJs', 'wpgithub_addPrismJs');
  register_setting('wp-github', 'wpgithub_defaultuser', 'wpgithub_sanitizeUserName');
  register_setting('wp-github', 'wpgithub_defaultrepo', 'wpgithub_sanitizeUserName');
  //Authentification
  register_setting('wp-github', 'wpgithub_clientID', 'wpgithub_sanitizeUserName');
  register_setting('wp-github', 'wpgithub_clientSecret', 'wpgithub_sanitizeUserName');
}

/*
 * Add Admin page
 * */
function wpgithub_plugin_options() {
  include('admin/options.php');
}


/**
 * Clear cache on request
 * @param $input
 */
function wpgithub_clearcache($input) {
  if ($input == 1) {
    foreach (glob(plugin_dir_path(__FILE__) . "cache/*.json") as $file) {
      unlink($file);
    }
    add_settings_error('wpgithub_clear_cache', esc_attr('settings_updated'), 'Cache has been cleared.', 'updated');
  }
}

/**
 * include files for syntax highlighting
 * @param $input
 * @return string
 */
function wpgithub_addPrismJs($input) {
  if (!empty($input)) {
    $input = 'checked';
  }
  else {
    $input = 'unchecked';
  }
  return $input;
}

/**
 * loadCodeHighLightAssets
 * Load js && css assets for highlighting
 */
function loadCodeHighLightAssets() {
  // enqueue scripts
  wp_enqueue_script('highlight', plugin_dir_url(__FILE__) . '/js/prism.js', array('jquery'), '1.0', TRUE);
  wp_enqueue_style('style-hightlight', plugin_dir_url(__FILE__) . 'css/prism.css');
}

/*
 * initCodeHighLightJs
 * init Js code if PrismJs is activated
 * */
function initCodeHighLightJs() {
  $initPrism = "<script>jQuery(function(){
		var form = jQuery('form'),
			code = jQuery('code', form),
			highlightCode = function() { Prism.highlightElement(code); };
		});
	</script>";
  echo $initPrism;
}

/*
 * Check if user wants to have a syntax highlighter
 * */
if (get_option('wpgithub_addPrismJs', '') == 'checked') {
  add_action('wp_enqueue_scripts', 'loadCodeHighLightAssets');
  add_action('wp_footer', 'initCodeHighLightJs', 2000);
}

/**
 * wpgithub_validate_int
 * Sanitize functions for admin options
 * @param $input
 * @return int
 */
function wpgithub_validate_int($input) {
  return intval($input); // return validated input
}

/**
 * wpgithub_sanitizeUserName
 * Sanitize functions for admin options
 * @param $input
 * @return int
 */
function wpgithub_sanitizeUserName($input) {
  $newstr = filter_var($input, FILTER_SANITIZE_STRING);
  return $newstr;
}

