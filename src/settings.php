<?php

namespace WPGraphQL\Extensions\PersistedQueries;

class Settings {

	/**
	 * Namespace for WP filters.
	 *
	 * @var string
	 */
	static $namespace = 'graphql_persisted_queries';

	private $section = 'graphql_persisted_queries_settings_section';

	private $page = 'graphql-persisted-queries-settings';

	private $options = null;

	public function __construct() {
		$this->checkboxes = [
			[
				'label' => 'Record queries',
				'option' => self::get_option_name( 'recording' ),
				'description' => 'Record queries when they are sent with a queryId param.',
			],
			[
				'label' => 'Lock queries',
				'option' => self::get_option_name( 'locked' ),
				'description' => 'Only respond to queries that have a matching persisted query.',
			],
			[
				'label' => 'Generate Query IDs',
				'option' => self::get_option_name( 'generate_ids' ),
				'description' => 'Generate query IDs for queries that don\'t itself provide one. This allows query locking without client side generated query IDs.',
			],
		];

	}

	public static function get_option_name( $name ) {
		return self::$namespace . '_' . $name;
	}

	public static function is_recording_enabled() {
		return (bool) get_option( self::get_option_name( 'recording' ) );
	}

	public static function is_generate_ids_enabled() {
		return (bool) get_option( self::get_option_name( 'generate_ids' ) );
	}

	public static function is_internal_graphql_request() {
		return $_SERVER["REQUEST_URI"] !== '/graphql'; // There must be a better way?
	}

	public static function is_locked( $deny_admin = false) {

		if ( Settings::is_internal_graphql_request() ) {
			return false;
		}

		if ( is_super_admin() && ! $deny_admin ) {
			return false;
		}

		return (bool) get_option( self::get_option_name( 'locked') );
	}

	public function init() {
		add_action( 'admin_menu', [ $this, 'add_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_notices', [ $this, 'render_admin_notices' ], 0 );
	}

	public function render_admin_notices() {
		// Skip notices on the settings page itself
		if ( $_GET['page'] === $this->page ) {
			return;
		}

		if ( Settings::is_locked( true ) ) {
			return;
		}

		$this->render_notice(
			'The API is open. Anyone can send any query to it!',
			'warning'
		);

		if ( Settings::is_recording_enabled() ) {
			$this->render_notice(
				'Query recording is enabled'
			);
		}
	}

	private function render_notice( $message, $type = 'success' ) {
		$class = 'notice notice-' . $type;
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( 'WP GraphQL: ' . $message ) );
	}

	public function register_settings() {
		add_settings_section(
			$this->section,
			'General',
			null,
			$this->page
		);

		foreach ( $this->checkboxes as $checkbox ) {
			add_settings_field(
				$checkbox['option'],
				$checkbox['label'],
				function () use ( $checkbox ) {
					$this->render_checkbox( $checkbox['option'], $checkbox['description'] );
				},
				$this->page,
				"{$this->section}"
			);

			register_setting(
				"{$this->section}",
				$checkbox['option']
			);

		}


	}

	public function add_page() {
		// This page will be under "Settings"
		add_options_page(
			'GraphQL Persited Queries',
			'GraphQL Persited Queries',
			'manage_network',
			$this->page,
			[ $this, 'render_settings' ]
		);

	}

	public function render_checkbox( $option, $description = '' ) {
		// Here we are comparing stored value with 1. Stored value is 1 if user checks
		// the checkbox otherwise empty string.
		?>
		<input
			type="checkbox"
			name="<?php echo esc_attr( $option ); ?>"
			value="1"
			<?php checked( 1, get_option( $option ), true );
		?> />
		<p class="description"><?php echo esc_html( $description ) ?></p>
		<?php
	}

	public function render_settings() {
		?>
		<div class="wrap">
			<h1>WP GraphQL Persisted Queries Settings</h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( "{$this->section}" );
				do_settings_sections( $this->page );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

}


