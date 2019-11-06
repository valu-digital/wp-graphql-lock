<?php
/**
 * Plugin Name: WP GraphQL Lock
 * Plugin URI: https://github.com/valu-digital/wp-graphql-lock
 * Description: This plugin enables query locking for WPGraphQL by implementing persisted GraphQL queries.
 * Author: Esa-Matti Suuronen, Valu Digital Oy
 * Version: 0.1.1
 * Author URI: https://valu.fi/
 *
 * @package wp-graphql-lock
 */

namespace WPGraphQL\Extensions\Lock;

require_once( __DIR__ . '/src/loader.php' );
require_once( __DIR__ . '/src/settings.php' );

add_action( 'graphql_init', array( new Loader(), 'init' ), 10, 0 );
 (new Settings())->init();
