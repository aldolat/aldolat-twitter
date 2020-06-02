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
 *    string  $title              The title of the widget.
 *    string  $intro_text         The introductory text for the widget.
 *    string  $screen_name        The username on Twitter.
 *    string  $count              The number of tweets to retrieve.
 *    boolean $exclude_replies    Whether to esclude replies.
 *    boolean $include_rts        Whether to include retweets.
 *    integer $cache_duration     The duration of the cache.
 *    boolean $new_tab            Whether the links should be opened in a new tab.
 *    string  $consumer_key       The Consumer Key of the Twitter app.
 *    string  $consumer_secret    The Consumer Secret of the Twitter app.
 *    string  $oauth_token        The Oauth Token of the Twitter app.
 *    string  $oauth_token_secret The Oauth Token Secret of the Twitter app.
 *    string  $widget_id          The ID of the widget.
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

/**
 * The main function that gets the tweets.
 *
 * @param array $args Various options to get tweets.
 *                    This function is fired by Aldolat_Twitter_Widget class.
 *
 * @return string $html The HTML containing the tweets.
 * @since 0.0.1
 */
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

/**
 * An helper function to echo the HTML containing the tweets.
 *
 * @uses aldolat_twitter_get_tweets().
 * @since 0.0.1
 */
function aldolat_twitter_tweets( $args ) {
	echo aldolat_twitter_get_tweets( $args );
}
