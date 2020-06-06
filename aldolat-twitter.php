<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    https://dev.aldolat.it/projects/aldolat-twitter/
 * @since   0.0.1
 * @package AldolatTwitter
 * @license GPLv3 or later
 *
 * @wordpress-plugin
 * Plugin Name: Aldolat Twitter
 * Description:  Display your Tweets in a widget.
 * Plugin URI: https://dev.aldolat.it/projects/aldolat-twitter/
 * Author: Aldo Latino
 * Author URI: https://www.aldolat.it/
 * Version: 0.0.3
 * License: GPLv3 or later
 * Text Domain: aldolat-twitter
 * Domain Path: /languages/
 */

/*
 * Copyright (C) 2020  Aldo Latino  (email : aldolat@gmail.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * TODO: Add option to get user timeline or favorites (see lines 188 and 190 in `class-aldolat-twitter.php` ).
 * TODO: Add option for getting tweets older or newer than a certain tweet.
 *       See: https://developer.twitter.com/en/docs/tweets/timelines/api-reference/get-statuses-user_timeline
 * TODO: Add option for displaying date and time.
 */

/**
 * Prevent direct access to this file.
 *
 * @since 0.0.1
 */
if ( ! defined( 'WPINC' ) ) {
	exit( 'No script kiddies please!' );
}

/**
 * Launch Aldolat Twitter.
 *
 * @since 0.0.1
 */
add_action( 'plugins_loaded', 'aldolat_twitter_setup' );

/**
 * Setup the plugin and fire the necessary files.
 *
 * @since 0.0.1
 */
function aldolat_twitter_setup() {
	/*
	 * Define the version of the plugin.
	 */
	define( 'ALDOLAT_TWITTER_PLUGIN_VERSION', '0.0.3' );

	/*
	 * Load the translation.
	 *
	 * @since 0.0.1
	 */
	load_plugin_textdomain( 'aldolat-twitter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/*
	 * Include all necessary PHP files.
	 *
	 * @since 0.0.1
	 */
	// Load the TwitterOAuth library.
	require_once plugin_dir_path( __FILE__ ) . 'TwitterOAuth/TwitterOAuth.php';
	require_once plugin_dir_path( __FILE__ ) . 'TwitterOAuth/Exception/TwitterException.php';
	// Load the class for Twitter.
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aldolat-twitter.php';
	// Load the init functions.
	require_once plugin_dir_path( __FILE__ ) . 'includes/aldolat-twitter-functions.php';
	// Load the widget's form functions.
	require_once plugin_dir_path( __FILE__ ) . 'includes/aldolat-twitter-widget-form-functions.php';
	// Load the widget's PHP file.
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aldolat-twitter-widget.php';

	/*
	 * Load Aldolat Twitter's widgets.
	 *
	 * @since 0.0.1
	 */
	add_action( 'widgets_init', 'aldolat_twitter_load_widget' );
}

/*
 * CODE IS POETRY
 */
