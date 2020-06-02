<?php
/**
 * Aldolat Twitter Uninstall
 *
 * @since 1.0.0
 * @package AldolatTwitter
 */

// Check for the 'WP_UNINSTALL_PLUGIN' constant, before executing.
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

function aldolat_twitter_uninstall() {
	global $wpdb;

	// Get transient created by this plugin from the database.
	$transients = $wpdb->get_col(
		"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '%aldolat-twitter-tweets%';"
	);

	// If there are no transient, stop the function.
	if ( ! $transients ) {
		return;
	}

	/*
	 * There could be more than one transients created by this plugin,
	 * one for every widget.
	 *
	 * $transients is always an array, returned by $wpdb->get_col().
	 *
	 * For each plugin, WordPress creates two transients:
	 * '_transient_aldolat-twitter-tweets-NUMBER' containing the object with information;
	 * '_transient_timeout_aldolat-twitter-tweets-NUMBER' containing the expiry time of the transient.
	 *
	 * When we delete a transient '_transient_aldolat-twitter-tweets-NUMBER',
	 * WordPress automatically deletes the '_transient_timeout_aldolat-twitter-tweets-NUMBER' also.
	 *
	 * So, for each transient we get in the following cycle,
	 * we do not consider the transient with 'timeout' in its name.
	 */
	foreach ( $transients as $transient ) {
		if ( ! strpos( $transient, 'timeout' ) ) {
			$transient = str_replace( '_transient_', '', $transient );
			delete_transient( $transient );
		}
	}

	// Delete options from the database.
	delete_option( 'widget_aldolat_twitter_widget' );
}

aldolat_twitter_uninstall();

/*
 * "So long, and thanks for all the fish."
 * (Douglas Adams)
 */
