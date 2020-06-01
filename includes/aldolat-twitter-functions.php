<?php
/**
 * Aldolat Twitter general functions.
 *
 * @since 0.0.1
 * @package AldolatTwitter
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
 * Returns the default options.
 *
 * $defaults contains the default parameters:
 *    string  $title            The title of the widget.
 *    string  $intro_text       The introductory text for the widget.
 *    string  $username         The username on Pinboard.
 *    string  $widget_id        The ID of the widget.
 * }
 *
 * @since 0.0.1
 * @return array $defaults The default options.
 */
function aldolat_twitter_get_defaults() {
	$defaults = array(
		'title'              => esc_html__( 'My tweets', 'pinboard-bookmarks' ),
		'intro_text'         => '',
		'screen_name'        => '',
		'count'              => 3,
		'exclude_replies'    => true,
		'consumer_key'       => '',
		'consumer_secret'    => '',
		'oauth_token'        => '',
		'oauth_token_secret' => '',
		'widget_id'          => '',
	);

	return $defaults;
}

/**
 * Register the widget.
 *
 * @since 0.0.1
 */
function aldolat_twitter_load_widget() {
	register_widget( 'Aldolat_Twitter_Widget' );
}

function aldolat_twitter_get_tweets( $args ) {
	$html = '';

	$feed = get_transient( 'aldolat-twitter-tweets' );

	if ( false === $feed ) {
		$twitter_getter = new Aldolat_Twitter( $args );
		$html           = $twitter_getter->fetch();
		set_transient( 'aldolat-twitter-tweets', $html, 5 * MINUTE_IN_SECONDS );
	} else {
		$html = $feed;
	}

	return $html;
}

function aldolat_twitter_tweets( $args ) {
	echo aldolat_twitter_get_tweets( $args );
}
