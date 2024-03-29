<?php
/**
 * The widget
 *
 * @package AldolatTwitter
 * @since 0.0.1
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
 * Create the widget and display it.
 *
 * @since 0.0.1
 */
class Aldolat_Twitter_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$widget_ops  = array(
			'classname'   => 'aldolat_twitter_widget',
			'description' => esc_html__( 'Publish your tweets in your blog', 'aldolat-twitter' ),
		);
		$control_ops = array(
			'width'   => 350,
			'id_base' => 'aldolat_twitter_widget',
		);

		parent::__construct(
			'aldolat_twitter_widget',
			esc_html__( 'Aldolat Twitter', 'aldolat-twitter' ),
			$widget_ops,
			$control_ops
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 *                    $args contains:
	 *                        $args['name'];
	 *                        $args['id'];
	 *                        $args['description'];
	 *                        $args['class'];
	 *                        $args['before_widget'];
	 *                        $args['after_widget'];
	 *                        $args['before_title'];
	 *                        $args['after_title'];
	 *                        $args['widget_id'];
	 *                        $args['widget_name'].
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$defaults = aldolat_twitter_get_defaults();
		$instance = wp_parse_args( $instance, $defaults );

		echo "\n" . '<!-- Start Aldolat Twitter - ' . $args['widget_id'] . ' -->' . "\n";

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
		}

		// The introductory text.
		if ( $instance['intro_text'] ) {
			echo '<p class="aldolat-twitter-intro-text">' . wp_kses_post( $instance['intro_text'] ) . '</p>';
		}

		/*
		 * The $instance variable contains:
		 * title
		 * intro_text
		 * screen_name
		 * type_of_tweets
		 * count
		 * exclude_replies
		 * include_rts
		 * cache_duration
		 * new_tab
		 * consumer_key
		 * consumer_secret
		 * oauth_token
		 * oauth_token_secret
		 * widget_id
		 */
		$aldolat_tweets = new Aldolat_Twitter_Core( $instance );
		$aldolat_tweets->the_tweets();

		echo $args['after_widget'];

		echo "\n" . '<!-- End Aldolat Twitter - ' . $args['widget_id'] . ' -->' . "\n\n";
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = (array) $old_instance;

		$instance['title']       = sanitize_text_field( $new_instance['title'] );
		$instance['intro_text']  = wp_kses_post( $new_instance['intro_text'] );
		$instance['screen_name'] = preg_replace( '([^a-zA-Z0-9\-_])', '', sanitize_text_field( $new_instance['screen_name'] ) );

		$instance['type_of_tweets'] = $new_instance['type_of_tweets'];

		$instance['count'] = absint( sanitize_text_field( $new_instance['count'] ) );
		if ( 0 === $instance['count'] || '' === $instance['count'] || ! is_numeric( $instance['count'] ) ) {
			$instance['count'] = 3;
		}
		if ( 200 < $instance['count'] ) {
			$instance['count'] = 200;
		}

		$instance['exclude_replies'] = isset( $new_instance['exclude_replies'] ) ? true : false;
		$instance['include_rts']     = isset( $new_instance['include_rts'] ) ? true : false;
		$instance['display_avatar']  = isset( $new_instance['display_avatar'] ) ? true : false;

		$instance['cache_duration'] = absint( sanitize_text_field( $new_instance['cache_duration'] ) );
		if ( 0 === $instance['cache_duration'] || '' === $instance['cache_duration'] || ! is_numeric( $instance['cache_duration'] ) ) {
			$instance['cache_duration'] = 5;
		}
		if ( 5 > $instance['cache_duration'] ) {
			$instance['cache_duration'] = 5;
		}

		$instance['new_tab']            = isset( $new_instance['new_tab'] ) ? true : false;
		$instance['consumer_key']       = sanitize_text_field( $new_instance['consumer_key'] );
		$instance['consumer_secret']    = sanitize_text_field( $new_instance['consumer_secret'] );
		$instance['oauth_token']        = sanitize_text_field( $new_instance['oauth_token'] );
		$instance['oauth_token_secret'] = sanitize_text_field( $new_instance['oauth_token_secret'] );

		// This option is stored only for debug purposes.
		$instance['widget_id'] = $this->id;

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$defaults = aldolat_twitter_get_defaults();
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<div class="aldolat-twitter-widget-content">

			<h4><?php esc_html_e( 'Title of the widget', 'aldolat-twitter' ); ?></h4>

			<?php
			// Title.
			aldolat_twitter_form_input_text(
				esc_html__( 'Title:', 'aldolat-twitter' ),
				$this->get_field_id( 'title' ),
				$this->get_field_name( 'title' ),
				esc_attr( $instance['title'] ),
				esc_html__( 'My latest tweets', 'aldolat-twitter' )
			);
			?>

			<h4><?php esc_html_e( 'Introductory text', 'aldolat-twitter' ); ?></h4>

			<?php
			// Introductory text.
			aldolat_twitter_form_textarea(
				esc_html__( 'Place this text after the title:', 'aldolat-twitter' ),
				$this->get_field_id( 'intro_text' ),
				$this->get_field_name( 'intro_text' ),
				$instance['intro_text'],
				esc_html__( 'These are my latest tweets. Follow me on Twitter!', 'aldolat-twitter' ),
				esc_html__( 'You can use some HTML, as you would do when writing a post.', 'aldolat-twitter' ),
				$style = 'resize: vertical; height: 80px;'
			);
			?>

			<h4><?php esc_html_e( 'Setup', 'aldolat-twitter' ); ?></h4>

			<?php
			// Username.
			aldolat_twitter_form_input_text(
				esc_html__( 'Username on Twitter:', 'aldolat-twitter' ),
				$this->get_field_id( 'screen_name' ),
				$this->get_field_name( 'screen_name' ),
				esc_attr( $instance['screen_name'] ),
				esc_html__( 'username', 'aldolat-twitter' ),
				esc_html__( 'This is the only mandatory option.', 'aldolat-twitter' )
			);

			// Type of tweets.
			$options = array(
				'timeline'  => array(
					'value' => 'timeline',
					'desc'  => esc_html__( 'Timeline', 'aldolat-twitter' ),
				),
				'favorites' => array(
					'value' => 'favorites',
					'desc'  => esc_html__( 'Favorites', 'aldolat-twitter' ),
				),
			);
			aldolat_twitter_form_select(
				esc_html__( 'Type of tweets', 'aldolat-twitter' ),
				$this->get_field_id( 'type_of_tweets' ),
				$this->get_field_name( 'type_of_tweets' ),
				$options,
				$instance['type_of_tweets']
			);

			// Number of items.
			aldolat_twitter_form_input_text(
				esc_html__( 'Number of items:', 'aldolat-twitter' ),
				$this->get_field_id( 'count' ),
				$this->get_field_name( 'count' ),
				esc_attr( $instance['count'] ),
				'3'
			);

			// Exclude replies.
			aldolat_twitter_form_checkbox(
				esc_html__( 'Exclude replies', 'aldolat-twitter' ),
				$this->get_field_id( 'exclude_replies' ),
				$this->get_field_name( 'exclude_replies' ),
				$instance['exclude_replies']
			);

			// Include retweets.
			aldolat_twitter_form_checkbox(
				esc_html__( 'Include retweets', 'aldolat-twitter' ),
				$this->get_field_id( 'include_rts' ),
				$this->get_field_name( 'include_rts' ),
				$instance['include_rts']
			);

			// Display avatar.
			aldolat_twitter_form_checkbox(
				esc_html__( 'Display user profile picture', 'aldolat-twitter' ),
				$this->get_field_id( 'display_avatar' ),
				$this->get_field_name( 'display_avatar' ),
				$instance['display_avatar']
			);

			// Cache.
			aldolat_twitter_form_input_text(
				esc_html__( 'Cache duration:', 'aldolat-twitter' ),
				$this->get_field_id( 'cache_duration' ),
				$this->get_field_name( 'cache_duration' ),
				esc_attr( $instance['cache_duration'] ),
				'5',
				esc_html__( 'In minutes. The minimum accepted value is 5.', 'aldolat-twitter' )
			);

			// New tab for links.
			aldolat_twitter_form_checkbox(
				esc_html__( 'Open links in a new browser tab', 'aldolat-twitter' ),
				$this->get_field_id( 'new_tab' ),
				$this->get_field_name( 'new_tab' ),
				$instance['new_tab']
			);
			?>

			<h4><?php esc_html_e( 'Twitter authentication', 'aldolat-twitter' ); ?></h4>

			<?php
			// Consumer key.
			aldolat_twitter_form_input_text(
				esc_html__( 'Consumer Key:', 'aldolat-twitter' ),
				$this->get_field_id( 'consumer_key' ),
				$this->get_field_name( 'consumer_key' ),
				esc_attr( $instance['consumer_key'] ),
				'',
				__( 'Insert Consumer Key', 'aldolat-twitter' ),
				'',
				'',
				'password'
			);
			// Consumer secret.
			aldolat_twitter_form_input_text(
				esc_html__( 'Consumer secret:', 'aldolat-twitter' ),
				$this->get_field_id( 'consumer_secret' ),
				$this->get_field_name( 'consumer_secret' ),
				esc_attr( $instance['consumer_secret'] ),
				'',
				__( 'Insert Consumer Secret', 'aldolat-twitter' ),
				'',
				'',
				'password'
			);
			// Oauth token.
			aldolat_twitter_form_input_text(
				esc_html__( 'Oauth token:', 'aldolat-twitter' ),
				$this->get_field_id( 'oauth_token' ),
				$this->get_field_name( 'oauth_token' ),
				esc_attr( $instance['oauth_token'] ),
				'',
				__( 'Insert Oauth Token', 'aldolat-twitter' ),
				'',
				'',
				'password'
			);
			// Oauth token secret.
			aldolat_twitter_form_input_text(
				esc_html__( 'Oauth token secret:', 'aldolat-twitter' ),
				$this->get_field_id( 'oauth_token_secret' ),
				$this->get_field_name( 'oauth_token_secret' ),
				esc_attr( $instance['oauth_token_secret'] ),
				'',
				__( 'Insert Oauth Token Secret', 'aldolat-twitter' ),
				'',
				'',
				'password'
			);
			?>
		</div>
		<?php
	}
}
