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
	 * It contains:
	 * array {
	 *     'title'              => string '',
	 *     'intro_text'         => string '',
	 *     'screen_name'        => string '',
	 *     'count'              => int INT,
	 *     'exclude_replies'    => boolean,
	 *     'include_rts'        => boolean,
	 *     'cache_duration'     => int INT,
	 *     'new_tab'            => boolean,
	 *     'consumer_key'       => string '',
	 *     'consumer_secret'    => string '',
	 *     'oauth_token'        => string '',
	 *     'oauth_token_secret' => string '',
	 *     'widget_id'          => string '',
	 * }
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
		$args     = wp_parse_args( $args, $defaults );

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
	 * Get the tweets.
	 *
	 * @since 0.1.0
	 * @access public
	 */
	public function the_tweets() {
		$tweets       = $this->fetch();
		$new_tab_text = $this->new_tab( $this->plugin_settings['new_tab'] );

		if ( false === $tweets ) {
			esc_html_e( 'No response from Twitter', 'aldolat-twitter' );
		} else {
			$this->the_html_tweets( $tweets, $new_tab_text );
		}
	}

	/**
	 * Fetch the tweets from Twitter.
	 *
	 * @return array $tweets The array with with tweets.
	 * @since 0.0.1
	 * @access private
	 */
	private function fetch() {
		$the_widget_id = preg_replace( '/\D/', '', $this->plugin_settings['widget_id'] );

		$transient = get_transient( 'aldolat-twitter-tweets-' . $the_widget_id );

		if ( $transient ) {
			$tweets = $transient;
		} else {
			$response = $this->get_response();
			if ( $response ) {
				$tweets = json_decode( $response );
				set_transient( 'aldolat-twitter-tweets-' . $the_widget_id, $tweets, $this->plugin_settings['cache_duration'] * MINUTE_IN_SECONDS );
			} else {
				$tweets = false;
			}
		}

		return $tweets;
	}

	/**
	 * Get the response from Twitter based on type of tweets.
	 *
	 * @return string $response The response from Twitter with tweets.
	 * @since 0.2.0
	 * @access private
	 */
	private function get_response() {
		$params = array(
			'screen_name'     => $this->plugin_settings['screen_name'],
			'count'           => $this->plugin_settings['count'],
			'exclude_replies' => $this->plugin_settings['exclude_replies'],
			'include_rts'     => $this->plugin_settings['include_rts'],
			'tweet_mode'      => 'extended',
		);

		$response = '';

		switch ( $this->plugin_settings['type_of_tweets'] ) {
			case 'timeline':
				$response = $this->connection->get( 'statuses/user_timeline', $params );
				break;
			case 'favorites':
				$response = $this->connection->get( 'favorites/list', $params );
				break;
			default:
				$response = $this->connection->get( 'statuses/user_timeline', $params );
				break;
		}

		return $response;
	}


	/**
	 * Print the HTML with tweets.
	 *
	 * @param $tweets The array containing the tweets.
	 * @param $new_tab_text The string with the text for HTML new tab.
	 * @since 0.4.0
	 */
	private function the_html_tweets( $tweets, $new_tab_text ) {
		?>

		<div id="twitter-feed">
			<?php
			foreach ( $tweets as $tweet ) {
				?>
				<div class="tweet">
					<?php
					if ( isset( $tweet->retweeted_status ) ) {
						$tweet_screen_name = $tweet->retweeted_status->user->screen_name;
						$tweet_user_image  = $tweet->retweeted_status->user->profile_image_url_https;
					} else {
						$tweet_screen_name = $tweet->user->screen_name;
						$tweet_user_image  = $tweet->user->profile_image_url_https;
					}
					?>
					<p class="tweet-user-image">
						<a <?php echo $new_tab_text; ?>href="https://twitter.com/<?php echo esc_html( $tweet_screen_name ); ?>">
							<img src="<?php echo esc_html( $tweet_user_image ); ?>" alt="profile picture" width="32" height="32" />
						</a>
					</p>
					<p class="tweet-body">
						<?php echo $this->format( $tweet ); ?>
					</p>
					<?php
					if ( $tweet->in_reply_to_status_id ) {
						?>
						<p class="tweet-in-reply-to">
							<?php
							printf(
								// translators: The original tweet author name and link.
								esc_html__( 'In reply to %s', 'aldolat-twitter' ),
								'<a href="https://twitter.com/' . esc_html( $tweet->in_reply_to_screen_name ) . '/status/' . esc_html( $tweet->in_reply_to_status_id ) . '">@' . esc_html( $tweet->in_reply_to_screen_name ) . '</a>'
							);
							?>
						</p>
						<?php
					}
					?>
					<p class="tweet-date-author">
						<?php
						if ( isset( $tweet->retweeted_status ) ) {
							$tweet_user = $tweet->retweeted_status->user->screen_name;
							$tweet_name = $tweet->retweeted_status->user->name;
							$tweet_id   = $tweet->retweeted_status->id_str;
							$tweet_time = $tweet->retweeted_status->created_at;
						} else {
							$tweet_user = $tweet->user->screen_name;
							$tweet_name = $tweet->user->name;
							$tweet_id   = $tweet->id_str;
							$tweet_time = $tweet->created_at;
						}
						?>
						<span class="tweet-date">
							<a <?php echo $new_tab_text; ?>href="https://twitter.com/<?php echo esc_html( $tweet_user ); ?>/status/<?php echo esc_html( $tweet_id ); ?>"><time><?php echo esc_html( $this->get_tweet_time( $tweet_time ) ); ?></time></a>
						</span>
						<span class="tweet-author">
							<?php esc_html_e( 'by', 'aldolat-twitter' ); ?>
							<a <?php echo $new_tab_text; ?>href="https://twitter.com/<?php echo esc_html( $tweet_user ); ?>"><?php echo esc_html( $tweet_name ); ?></a>
						</span>
						<?php
						if ( isset( $tweet->retweeted_status ) ) {
							printf(
								// translators: date and tweet author name
								' ' . esc_html__( '(RT on %1$s by %2$s)', 'aldolat-twitter' ),
								'<a ' . $new_tab_text . 'href="https://twitter.com/' . esc_html( $tweet->user->screen_name ) . '/status/' . esc_html( $tweet->id_str ) . '">' . esc_html( $this->get_tweet_time( $tweet->created_at ) ) . '</a>',
								'<a ' . $new_tab_text . 'href="https://twitter.com/' . esc_html( $tweet->user->screen_name ) . '">' . esc_html( $tweet->user->name ) . '</a>'
							);
						}
						?>
					</p>
				</div>
				<?php
			}
			?>
		</div>
		<?php
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
		// Get the local date/time formats.
		$datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		// Convert tweet time into UNIX timestamp.
		$unix_tweet_time = strtotime( $tweet_time );

		// The tweet date/time is returned in the "... ago" form if the tweet is up to a day old.
		if ( DAY_IN_SECONDS < ( time() - $unix_tweet_time ) ) {
			$time = wp_date( $datetime_format, $unix_tweet_time );
		} else {
			$time = $this->relative_time( $tweet_time );
		}

		return $time;
	}

	/**
	 * Returns the difference in seconds between the tweet time and now,
	 * including the '... ago' string.
	 *
	 * @param integer $t The formatted datetime of the tweet.
	 * @return integer The difference in seconds
	 * @since 0.0.1
	 * @access private
	 */
	private function relative_time( $t ) {
		$tweet_time = strtotime( $t );
		return human_time_diff( $tweet_time, time() ) . ' ' . esc_html__( 'ago', 'aldolat-twitter' );
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
