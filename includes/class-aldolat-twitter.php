<?php
/**
 * The plugin class for startup.
 *
 * @since 0.0.4
 * @package AldolatTwitter
 */

class Aldolat_Twitter {
	public $plugin_version;

	/**
	 * The path to the plugin root dir.
	 *
	 * @var string $plugin_dir_path
	 * @access private
	 * @example /path/to/wp/dir/wp-content/plugins/aldolat-twitter/
	 * @since 0.0.4
	 */
	private $plugin_dir_path;

	/**
	 * The name of the directory that contains the plugin.
	 *
	 * @var string $plugin_dirname
	 * @access private
	 * @example aldolat-twitter/
	 * @since 0.0.4
	 */
	private $plugin_dirname;

	/**
	 * Set up some properties.
	 *
	 * @access public
	 * @since 0.0.4
	 */
	public function __construct() {
		$this->plugin_version  = '0.1.0';
		$this->plugin_dir_path = trailingslashit( dirname( plugin_dir_path( __FILE__ ) ) );
		$this->plugin_dirname  = trailingslashit( dirname( plugin_basename( __FILE__ ), 2 ) );
	}

	public function init() {
		add_action( 'plugins_loaded', array( $this, 'load_translations' ) );

		$this->load_required_files();

		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}

	public function load_translations() {
		load_plugin_textdomain( 'aldolat-twitter', false, $this->plugin_dirname . 'languages' );
	}

	private function load_required_files() {
		// Load the TwitterOAuth files.
		require_once $this->plugin_dir_path . 'TwitterOAuth/TwitterOAuth.php';
		require_once $this->plugin_dir_path . 'TwitterOAuth/Exception/TwitterException.php';
		// Load the init functions.
		require_once $this->plugin_dir_path . 'includes/aldolat-twitter-functions.php';
		// Load the class for Twitter.
		require_once $this->plugin_dir_path . 'includes/class-aldolat-twitter-core.php';
		// Load the widget's form functions.
		require_once $this->plugin_dir_path . 'includes/aldolat-twitter-widget-form-functions.php';
		// Load the widget's PHP file.
		require_once $this->plugin_dir_path . 'includes/class-aldolat-twitter-widget.php';
	}

	public function register_widget() {
		register_widget( 'Aldolat_Twitter_Widget' );
	}
}
