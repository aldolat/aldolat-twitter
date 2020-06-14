<?php
/**
 * The plugin class for managing tweets.
 *
 * @since 0.0.1
 * @package AldolatTwitter
 */

use TwitterOAuth\TwitterOAuth;

/**
 * Main Aldolat Twitter class.
 *
 * @link https://gabrieleromanato.com/2018/06/wordpress-creare-un-plugin-per-reperire-i-dati-da-twitter
 * @since 0.0.1
 */
class Aldolat_Twitter_Core {
	/**
	 * The property that contains an instance of TwitterOauth.
	 *
	 * @var object $connection
	 * @access private
	 * @since 0.0.1
	 */
	private $connection;

	/**
	 * The array with all plugin settings.
	 *
	 * @var array $plugin_settings
	 * @access private
	 * @since 0.0.4
	 */
	private $plugin_settings;

	/**
	 * Constructon method.
	 *
	 * @param array $args The default parameters.
	 * @since 0.0.1
	 * @access public
	 */
	public function __construct( $args ) {
		$defaults = aldolat_twitter_get_defaults();
		wp_parse_args( $args, $defaults );

		$this->plugin_settings = $args;

		$this->connection = new TwitterOAuth(
			array(
				'consumer_key'       => $this->plugin_settings['consumer_key'],
				'consumer_secret'    => $this->plugin_settings['consumer_secret'],
				'oauth_token'        => $this->plugin_settings['oauth_token'],
				'oauth_token_secret' => $this->plugin_settings['oauth_token_secret'],
				'output_format'      => 'text',
			)
		);
	}

	/**
	 * Fetch the tweets from Twitter.
	 *
	 * @return array $tweets The array with with tweets.
	 * @since 0.0.1
	 * @access public
	 */
	public function fetch() {
		$params = array(
			'screen_name'     => $this->plugin_settings['screen_name'],
			'count'           => $this->plugin_settings['count'],
			'exclude_replies' => $this->plugin_settings['exclude_replies'],
			'include_rts'     => $this->plugin_settings['include_rts'],
			'tweet_mode'      => 'extended',
		);

		$widget_id = preg_replace( '/\D/', '', $this->plugin_settings['widget_id'] );

		$feed = get_transient( 'aldolat-twitter-tweets-' . $widget_id );

		if ( false === $feed ) {
			// Grab user timeline.
			$resp = $this->connection->get( 'statuses/user_timeline', $params );
			// Grab favorite tweets.
			//$resp   = $this->connection->get( 'favorites/list', $params );
			$tweets = json_decode( $resp );
			set_transient( 'aldolat-twitter-tweets-' . $widget_id, $tweets, $this->plugin_settings['cache_duration'] * MINUTE_IN_SECONDS );
		} else {
			$tweets = $feed;
		}

		return $tweets;
	}

	/**
	 * Get the tweets.
	 *
	 * @return string $output The final HTML with tweets.
	 * @since 0.1.0
	 * @access public
	 */
	public function get_tweets() {
		$tweets = $this->fetch();

		$output = '<div id="twitter-feed">';

		$new_tab_text = $this->new_tab( $this->plugin_settings['new_tab'] );

		$twitter_link = 'href="https://twitter.com/' . $this->plugin_settings['screen_name'];

		foreach ( $tweets as $tweet ) {
			$output .= '<div class="tweet">';
			$output .= '<a ' . $new_tab_text . $twitter_link . '/status/' . $tweet->id_str . '">';
			$output .= '<time class="tweet-date">' . $this->get_tweet_time( $tweet->created_at ) . '</time>';
			$output .= '</a> ';
			$output .= '<span class="tweet-author">';
			$output .= esc_html__( 'by', 'aldolat-twitter' ) . ' ';
			$output .= '<a ' . $new_tab_text . $twitter_link . '">';
			$output .= $this->plugin_settings['screen_name'];
			$output .= '</a>';
			$output .= '</span>';
			$output .= '<div class="tweet-body">' . $this->format( $tweet ) . '</div>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Returns the difference in seconds between the tweet time and now.
	 *
	 * @param integer $t The formatted datetime of the tweet.
	 * @return integer The difference in seconds
	 * @since 0.0.1
	 * @access private
	 */
	private function relative_time( $t ) {
		$new_tweet_time = strtotime( $t );
		return human_time_diff( $new_tweet_time, time() );
	}

	/**
	 * Format the tweet adding HTML links to URL, mentions, and hashtags.
	 *
	 * @param object $tweet The object containing the tweet.
	 * @return string $tweet_text The resulting tweet with HTML.
	 * @since 0.0.1
	 * @access private
	 */
	private function format( $tweet ) {
		$tweet_text     = $tweet->full_text;
		$tweet_entities = array();

		$new_tab_text = $this->new_tab( $this->plugin_settings['new_tab'] );

		foreach ( $tweet->entities->urls as $url ) {
			$tweet_entities[] = array(
				'type'    => 'url',
				'curText' => mb_substr( $tweet_text, $url->indices[0], ( $url->indices[1] - $url->indices[0] ) ),
				'newText' => '<a ' . $new_tab_text . 'href="' . $url->expanded_url . '">' . $url->display_url . '</a>',
			);
		}

		foreach ( $tweet->entities->user_mentions as $mention ) {
			$string           = mb_substr( $tweet_text, $mention->indices[0], ( $mention->indices[1] - $mention->indices[0] ) );
			$tweet_entities[] = array(
				'type'    => 'mention',
				'curText' => mb_substr( $tweet_text, $mention->indices[0], ( $mention->indices[1] - $mention->indices[0] ) ),
				'newText' => '<a ' . $new_tab_text . 'href="https://twitter.com/' . $mention->screen_name . '">' . $string . '</a>',
			);
		}

		foreach ( $tweet->entities->hashtags as $tag ) {
			$string           = mb_substr( $tweet_text, $tag->indices[0], ( $tag->indices[1] - $tag->indices[0] ) );
			$tweet_entities[] = array(
				'type'    => 'hashtag',
				'curText' => mb_substr( $tweet_text, $tag->indices[0], ( $tag->indices[1] - $tag->indices[0] ) ),
				'newText' => '<a ' . $new_tab_text . 'href="https://twitter.com/hashtag/' . $tag->text . '">' . $string . '</a>',
			);
		}

		foreach ( $tweet_entities as $entity ) {
			$tweet_text = str_replace( $entity['curText'], $entity['newText'], $tweet_text );
		}

		return $tweet_text;
	}

	/**
	 * Returns the datetime of the tweet using '... ago' form if the tweet is
	 * not older than a day.
	 *
	 * The function respects the local offset time and the option defined by the
	 * user about the formatting of date and time in the WordPress dashboard.
	 *
	 * @param string $tweet_time The formatted time of the tweet.
	 * @return string $time The datetime of the tweet or the '... ago' form.
	 * @since 0.0.1
	 * @access private
	 */
	private function get_tweet_time( $tweet_time ) {
		// Get the local GMT offset and date/time formats.
		$local_offset    = (int) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		$datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		// Convert tweet time into UNIX timestamp and add local offset.
		$unix_tweet_time = strtotime( $tweet_time ) + $local_offset;

		// The tweet date/time is returned in the "... ago" form if the tweet is up to a day old.
		if ( DAY_IN_SECONDS < ( time() - $unix_tweet_time ) ) {
			$time = gmdate( $datetime_format, $unix_tweet_time );
		} else {
			$time = $this->relative_time( $tweet_time ) . ' ' . esc_html__( 'ago', 'aldolat-twitter' );
		}

		return $time;
	}

	/**
	 * Get the HTML rel attribute for links.
	 *
	 * @param bool $new_tab Whether the browser should open links in a new tab.
	 * @return string $text The rel and target attributes for links.
	 * @since 0.1.0
	 * @access private
	 */
	private function new_tab( $new_tab ) {
		if ( $new_tab ) {
			$text = 'rel="external noreferrer nofollow noopener" target="_blank" ';
		} else {
			$text = '';
		}

		return $text;
	}
}
