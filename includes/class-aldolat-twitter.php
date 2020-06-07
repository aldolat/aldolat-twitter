<?php
/**
 * The plugin core class.
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
class Aldolat_Twitter {
	/**
	 * The property that contain an instance of TwitterOauth.
	 *
	 * @var object $connection
	 * @access private
	 * @since 0.0.1
	 */
	private $connection;

	/**
	 * The username on Twitter.
	 *
	 * @var string $screen_name
	 * @access private
	 * @since 0.0.1
	 */
	private $screen_name;

	/**
	 * The number of tweets to retrieve.
	 *
	 * @var integer $count
	 * @access private
	 * @since 0.0.1
	 */
	private $count;

	/**
	 * Whether to esclude replies.
	 *
	 * @var boolean $exclude_replies
	 * @access private
	 * @since 0.0.1
	 */
	private $exclude_replies;

	/**
	 * Whether to include retweets.
	 *
	 * @var boolean $include_rts
	 * @access private
	 * @since 0.0.2
	 */
	private $include_rts;

	/**
	 * Whether the links should be opened in a new tab.
	 *
	 * @var boolean $new_tab
	 * @access private
	 * @since 0.0.3
	 */
	private $new_tab;

	/**
	 * Constructon method.
	 *
	 * @since 0.0.1
	 */
	public function __construct( $args ) {
		$defaults = array(
			'screen_name'        => '',
			'count'              => 5,
			'exclude_replies'    => false,
			'include_rts'        => true,
			'new_tab'            => false,
			'consumer_key'       => '',
			'consumer_secret'    => '',
			'oauth_token'        => '',
			'oauth_token_secret' => '',
		);
		wp_parse_args( $args, $defaults );

		$settings = array(
			'consumer_key'       => $args['consumer_key'],
			'consumer_secret'    => $args['consumer_secret'],
			'oauth_token'        => $args['oauth_token'],
			'oauth_token_secret' => $args['oauth_token_secret'],
			'output_format'      => 'text',
		);

		$this->connection = new TwitterOAuth( $settings );

		$this->screen_name     = $args['screen_name'];
		$this->count           = $args['count'];
		$this->exclude_replies = $args['exclude_replies'];
		$this->include_rts     = $args['include_rts'];
		$this->new_tab         = $args['new_tab'];
	}

	/**
	 * Returns the difference in seconds between the tweet time and now.
	 *
	 * @param integer $t The formatted datetime of the tweet.
	 * @return integer The difference in seconds
	 * @since 0.0.1
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
	 */
	private function format( $tweet ) {
		$tweet_text     = $tweet->full_text;
		$tweet_entities = array();

		if ( $this->new_tab ) {
			$new_tab_text = 'rel="external noreferrer nofollow noopener" target="_blank" ';
		} else {
			$new_tab_text = '';
		}

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
	 * Fetch the tweets from Twitter.
	 *
	 * @return string $html The final HTML with tweets.
	 * @since 0.0.1
	 */
	public function fetch() {
		$params = array(
			'screen_name'     => $this->screen_name,
			'count'           => $this->count,
			'exclude_replies' => $this->exclude_replies,
			'include_rts'     => $this->include_rts,
			'tweet_mode'      => 'extended',
		);

		$html = '<div id="twitter-feed">';

		// Grab user timeline.
		$resp = $this->connection->get( 'statuses/user_timeline', $params );
		// Grab the favorite tweets.
		//$resp   = $this->connection->get( 'favorites/list', $params );
		$tweets = json_decode( $resp );

		if ( $this->new_tab ) {
			$new_tab_text = 'rel="external noreferrer nofollow noopener" target="_blank" ';
		} else {
			$new_tab_text = '';
		}

		foreach ( $tweets as $tweet ) {
			$html .= '<div class="tweet">';
			$html .= '<a ' . $new_tab_text . 'href="https://twitter.com/' . $this->screen_name . '/status/' . $tweet->id_str . '">';
			$html .= '<time class="tweet-date">' . $this->get_tweet_time( $tweet->created_at ) . '</time>';
			$html .= '</a>';
			$html .= '<span class="tweet-author">';
			$html .= ' ' . esc_html__( 'by', 'aldolat-twitter' ) . ' <a ' . $new_tab_text . 'href="https://twitter.com/' . $this->screen_name . '">' . $this->screen_name . '</a>';
			$html .= '</span>';
			$html .= '<div class="tweet-body">' . $this->format( $tweet ) . '</div>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
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
}
