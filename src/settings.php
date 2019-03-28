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
				'option' => self::get_option_name( 'recording' )
			],
			[
				'label' => 'Lock queries',
				'option' => self::get_option_name( 'locked' )
			],
		];

	}

	public static function get_option_name( $name ) {
		return self::$namespace . '_' . $name;
	}

	public static function is_recording_enabled() {
		return (bool) get_option( self::get_option_name( 'recording') );
	}

	public static function is_locked() {
		if ( is_super_admin() ) {
			return false;
		}

		return (bool) get_option( self::get_option_name( 'locked') );
	}

	public function init() {
		add_action( 'admin_menu', [ $this, 'add_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
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
					$this->render_checkbox( $checkbox['option'] );
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

	public function render_checkbox( $option ) {
		// Here we are comparing stored value with 1. Stored value is 1 if user checks
		// the checkbox otherwise empty string.
		?>
		<input
			type="checkbox"
			name="<?php echo esc_attr( $option ); ?>"
			value="1"
			<?php checked( 1, get_option( $option ), true );
		?> />
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


