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
 * Return the default options.
 *
 * $defaults contains the default parameters:
 *    string  $title              The title of the widget.
 *    string  $intro_text         The introductory text for the widget.
 *    string  $screen_name        The username on Twitter.
 *    string  $type_of_tweets     The type of tweets to display.
 *    string  $count              The number of tweets to retrieve.
 *    boolean $exclude_replies    Whether to esclude replies.
 *    boolean $include_rts        Whether to include retweets.
 *    boolean $display_avatar     Whether to display user avatar.
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
		'type_of_tweets'     => 'timeline',
		'count'              => 5,
		'exclude_replies'    => false,
		'include_rts'        => true,
		'display_avatar'     => true,
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
