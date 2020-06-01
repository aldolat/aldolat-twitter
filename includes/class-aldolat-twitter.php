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

	private $screen_name;
	private $count;
	private $exclude_replies;

	/**
	 * Constructon method.
	 *
	 * @since 0.0.1
	 */
	public function __construct( $args ) {
		$defaults = array(
			'consumer_key'       => '',
			'consumer_secret'    => '',
			'oauth_token'        => '',
			'oauth_token_secret' => '',
			'screen_name'        => '',
			'count'              => '',
			'exclude_replies'    => '',
		);
		wp_parse_args( $args, $defaults );

		$settings = array(
			'consumer_key'       => $args['consumer_key'],
			'consumer_secret'    => $args['consumer_secret'],
			'oauth_token'        => $args['oauth_token'],
			'oauth_token_secret' => $args['oauth_token_secret'],
			'output_format'      => 'text',
		);

		$this->connection      = new TwitterOAuth( $settings );
		$this->screen_name     = $args['screen_name'];
		$this->count           = $args['count'];
		$this->exclude_replies = $args['exclude_replies'];
	}

	private function relative_time( $t ) {
		$new_tweet_time = strtotime( $t );
		return human_time_diff( $new_tweet_time, time() );
	}

	private function format( $tweet ) {
		$tweet_text     = $tweet->text;
		$tweet_entities = array();

		foreach ( $tweet->entities->urls as $url ) {
			$tweet_entities[] = array(
				'type'    => 'url',
				'curText' => substr( $tweet_text, $url->indices[0], ( $url->indices[1] - $url->indices[0] ) ),
				'newText' => '<a href="' . $url->expanded_url . '">' . $url->display_url . '</a>',
			);
		}

		foreach ( $tweet->entities->user_mentions as $mention ) {
			$string = substr( $tweet_text, $mention->indices[0], ( $mention->indices[1] - $mention->indices[0] ) );
			$tweet_entities[] = array(
				'type'    => 'mention',
				'curText' => substr( $tweet_text, $mention->indices[0], ( $mention->indices[1] - $mention->indices[0] ) ),
				'newText' => '<a href="https://twitter.com/' . $mention->screen_name . '">' . $string . '</a>'
			);
		}

		foreach ( $tweet->entities->hashtags as $tag ) {
			$string = substr( $tweet_text, $tag->indices[0], ( $tag->indices[1] - $tag->indices[0] ) );
			$tweet_entities[] = array(
				'type'    => 'hashtag',
				'curText' => substr( $tweet_text, $tag->indices[0], ( $tag->indices[1] - $tag->indices[0] ) ),
				'newText' => '<a href="https://twitter.com/search?q=%23' . $tag->text . '&amp;src=hash">' . $string . '</a>'
			);
		}

		foreach ( $tweet_entities as $entity ) {
			$tweet_text = str_replace( $entity['curText'], $entity['newText'], $tweet_text );
		}

		return $tweet_text;
	}

	public function fetch() {
		$params = array(
			'screen_name'     => $this->screen_name,
			'count'           => $this->count,
			'exclude_replies' => $this->exclude_replies,
		);

		$html = '<div id="twitter-feed">';

		$resp   = $this->connection->get( 'statuses/user_timeline', $params );
		$tweets = json_decode( $resp );

		foreach ( $tweets as $tweet ) {
			$html .= '<div class="tweet">';
			$html .= '<time class="tweet-date">' . $this->relative_time( $tweet->created_at ) . ' ' . esc_html__( 'ago', 'aldolat-twitter' ) . '</time>';
			$html .= '<div class="tweet-body">' . $this->format( $tweet ) . '</div>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}
}
