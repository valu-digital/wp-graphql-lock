<?php
/**
 * Plugin Name: WPGraphQL Persisted Queries
 * Plugin URI: https://github.com/Quartz/wp-graphql-persisted-queries
 * Description: Provides persistence to GraphQL queries allowing them to be queried by ID or hash. Requires WPGraphQL 0.2.0 or newer.
 * Author: Chris Zarate, Quartz
 * Version: 1.0.0
 * Author URI: https://qz.com/
 *
 * @package wp-graphql-persisted-queries
 */

namespace WPGraphQL\Extensions\PersistedQueries;

require_once( __DIR__ . '/src/loader.php' );
require_once( __DIR__ . '/src/settings.php' );

add_action( 'graphql_init', array( new Loader(), 'init' ), 10, 0 );
 (new Settings())->init();
