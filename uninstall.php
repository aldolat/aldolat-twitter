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
	// Delete options from the database.
	delete_option( 'widget_aldolat_twitter_widget' );

	// Delete transients.
	$transient = get_transient( 'aldolat-twitter-tweets' );
	if ( $transient ) {
		delete_transient( 'aldolat-twitter-tweets' );
	}
}

aldolat_twitter_uninstall();
