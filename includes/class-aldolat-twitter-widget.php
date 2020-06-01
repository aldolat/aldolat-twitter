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
 * Creates the widget and display it.
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
		$instance = wp_parse_args( $instance, aldolat_twitter_get_defaults() );

		echo "\n" . '<!-- Start Aldolat Twitter - ' . $args['widget_id'] . ' -->' . "\n";

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
		}

		$params = array(
			'consumer_key'       => $instance['consumer_key'],
			'consumer_secret'    => $instance['consumer_secret'],
			'oauth_token'        => $instance['oauth_token'],
			'oauth_token_secret' => $instance['oauth_token_secret'],
			'screen_name'        => $instance['screen_name'],
			'count'              => $instance['count'],
			'exclude_replies'    => $instance['exclude_replies'],
		);
		aldolat_twitter_tweets( $params );

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

		$instance['count'] = absint( sanitize_text_field( $new_instance['count'] ) );
		if ( 0 === $instance['count'] || '' === $instance['count'] || ! is_numeric( $instance['count'] ) ) {
			$instance['count'] = 3;
		}

		$instance['exclude_replies']    = isset( $new_instance['exclude_replies'] ) ? true : false;
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
		$instance = wp_parse_args( (array) $instance, aldolat_twitter_get_defaults() );
		?>

		<div class="aldolat-twitter-widget-content">

			<h4><?php esc_html_e( 'Introduction', 'aldolat-twitter' ); ?></h4>

			<p>
				<?php
				esc_html_e(
					'This widget allows you to publish your tweets in your sidebar.',
					'aldolat-twitter'
				);
				?>
			</p>

			<h4><?php esc_html_e( 'Title of the widget', 'aldolat-twitter' ); ?></h4>

			<?php
			// Title.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Title:', 'aldolat-twitter' ),
				$this->get_field_id( 'title' ),
				$this->get_field_name( 'title' ),
				esc_attr( $instance['title'] ),
				esc_html__( 'My bookmarks on Pinboard', 'aldolat-twitter' )
			);
			?>

			<h4><?php esc_html_e( 'Introductory text', 'aldolat-twitter' ); ?></h4>

			<?php
			// Introductory text.
			pinboard_bookmarks_form_textarea(
				esc_html__( 'Place this text after the title', 'aldolat-twitter' ),
				$this->get_field_id( 'intro_text' ),
				$this->get_field_name( 'intro_text' ),
				$instance['intro_text'],
				esc_html__( 'These are my bookmarks on Pinboard about Italian recipes.', 'aldolat-twitter' ),
				esc_html__( 'You can use some HTML, as you would do when writing a post.', 'aldolat-twitter' ),
				$style = 'resize: vertical; height: 80px;'
			);
			?>

			<h4><?php esc_html_e( 'Basic Setup', 'aldolat-twitter' ); ?></h4>

			<?php
			// Username.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Username on Twitter:', 'aldolat-twitter' ),
				$this->get_field_id( 'screen_name' ),
				$this->get_field_name( 'screen_name' ),
				esc_attr( $instance['screen_name'] ),
				esc_html__( 'username', 'aldolat-twitter' ),
				esc_html__( 'This is the only mandatory option.', 'aldolat-twitter' )
			);

			// Number of items.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Number of items:', 'aldolat-twitter' ),
				$this->get_field_id( 'count' ),
				$this->get_field_name( 'count' ),
				esc_attr( $instance['count'] ),
				'3'
			);

			// Random order.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Esclude replies', 'aldolat-twitter' ),
				$this->get_field_id( 'exclude_replies' ),
				$this->get_field_name( 'exclude_replies' ),
				$instance['exclude_replies']
			);
			?>

			<h4><?php esc_html_e( 'Twitter authentication', 'aldolat-twitter' ); ?></h4>

			<?php
			// Consumer key.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Consumer Key:', 'aldolat-twitter' ),
				$this->get_field_id( 'consumer_key' ),
				$this->get_field_name( 'consumer_key' ),
				esc_attr( $instance['consumer_key'] )
			);
			// Consumer secret.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Consumer secret:', 'aldolat-twitter' ),
				$this->get_field_id( 'consumer_secret' ),
				$this->get_field_name( 'consumer_secret' ),
				esc_attr( $instance['consumer_secret'] )
			);
			// Oauth token.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Oauth token:', 'aldolat-twitter' ),
				$this->get_field_id( 'oauth_token' ),
				$this->get_field_name( 'oauth_token' ),
				esc_attr( $instance['oauth_token'] )
			);
			// Oauth token secret.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Oauth token secret:', 'aldolat-twitter' ),
				$this->get_field_id( 'oauth_token_secret' ),
				$this->get_field_name( 'oauth_token_secret' ),
				esc_attr( $instance['oauth_token_secret'] )
			);
			?>
		</div>
		<?php
	}
}
