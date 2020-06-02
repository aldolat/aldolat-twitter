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
 *    string  $username         The username on Twitter.
 *    string  $widget_id        The ID of the widget.
 * }
 *
 * @since 0.0.1
 * @return array $defaults The default options.
 */
function aldolat_twitter_get_defaults() {
	$defaults = array(
		'title'              => esc_html__( 'My latest tweets', 'aldolat-twitter' ),
		'intro_text'         => '',
		'screen_name'        => '',
		'count'              => 5,
		'exclude_replies'    => false,
		'include_rts'        => true,
		'cache_duration'     => 5, // In minutes.
		'new_tab'            => false,
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

	/*
	 * Remove any non-digit from the widget ID.
	 * For example: 'aldolat_twitter_widget-2' becomes '2'.
	 */
	$widget_id = preg_replace( '/\D/', '', $args['widget_id'] );

	$feed = get_transient( 'aldolat-twitter-tweets-' . $widget_id );

	if ( false === $feed ) {
		$twitter_getter = new Aldolat_Twitter( $args );
		$html           = $twitter_getter->fetch();
		set_transient( 'aldolat-twitter-tweets-' . $widget_id, $html, $args['cache_duration'] * MINUTE_IN_SECONDS );
	} else {
		$html = $feed;
	}

	return $html;
}

function aldolat_twitter_tweets( $args ) {
	echo aldolat_twitter_get_tweets( $args );
}
