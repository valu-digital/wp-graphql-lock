<?php
/**
 * Plugin Name: WPGraphQL Persisted Queries
 * Plugin URI: https://github.com/Quartz/wp-graphql-persisted-queries
 * Description: Provides persistence to queries allowing them to be queried by ID (hash)
 * Author: Chris Zarate, Quartz
 * Version: 1.0.0
 * Author URI: https://qz.com/
 *
 * @package wp-graphql-persisted-queries
 */

namespace WPGraphQL\Extensions\PersistedQueries;

require_once( __DIR__ . '/src/loader.php' );

add_action( 'graphql_init', array( new Loader(), 'init' ), 10, 0 );
