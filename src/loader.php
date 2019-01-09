<?php
/**
 * Loader
 *
 * @package wp-graphql-persisted-queries
 */

namespace WPGraphQL\Extensions\PersistedQueries;

/**
 * Load or save a persisted query from a custom post type. This allows users to
 * avoid sending the query over the wire, saving bandwidth. In particular, it
 * allows for moving to GET requests, which can be cached at the edge.
 *
 * @package WPGraphQL\Extensions\PersistedQueries
 */
class Loader {
	/**
	 * Whether query persistence is enabled.
	 *
	 * @var bool
	 */
	private $enabled = true;

	/**
	 * Post type for default query persistence. Unused if query persistence is
	 * disabled. Filter with graphql_persisted_query_post_type.
	 *
	 * @var string
	 */
	private $post_type = 'graphql_query';

	/**
	 * Filter configuration values and register the post type used to store
	 * persisted queries.
	 *
	 * @return void
	 */
	public function init() {
		/**
		 * Whether to enable persisted queries.
		 *
		 * @param bool $enabled Enable?
		 */
		$this->enabled = apply_filters( 'graphql_persisted_query_enabled', $this->enabled );

		/**
		 * Post type to use to persist queries. Unused if disabled.
		 *
		 * @param string $post_type
		 */
		$this->post_type = apply_filters( 'graphql_persisted_query_post_type', $this->post_type );

		// If not enabled or the post type doesn't look right, don't hook.
		if ( ! $this->enabled || empty( $this->post_type ) || post_type_exists( $this->post_type ) ) {
			return;
		}

		// Register the post type.
		$this->register_post_type();

		// Filter request data to load/save queries.
		add_filter( 'graphql_request_data', [ $this, 'process_request_data' ], 10, 1 );
	}

	/**
	 * Attempts to load a persisted query corresponding to a query ID (hash).
	 *
	 * @param  string $query_id Query ID
	 * @return string Query
	 * @throws RequestError
	 */
	public function load( $query_id ) {
		$post = get_page_by_path( $query_id, 'OBJECT', $this->post_type );

		return isset( $post->post_content ) ? $post->post_content : null;
	}

	/**
	 * Filter request data and load the query if request provides a query ID. We
	 * are following the Apollo draft spec for automatic persisted queries. See:
	 *
	 * https://github.com/apollographql/apollo-link-persisted-queries#automatic-persisted-queries
	 *
	 * @param  array $request_data Request data from WPHelper
	 * @return array
	 */
	public function process_request_data( $request_data ) {
		// Client sends *both* queryId and query = request to persist query.
		if ( ! empty( $request_data['queryId'] ) && ! empty( $request_data['query'] )  ) {
			$this->save( $request_data['queryId'], $request_data['query'], $request_data['operation'] );
		}

		// Client sends queryId but *not* query = optimistic request to use cached query.
		if ( ! empty( $request_data['queryId'] ) && empty( $request_data['query'] ) ) {
			$request_data['query'] = $this->load( $request_data['queryId'] );
		}

		// We've got this; call off any other persistence implementations.
		unset( $request_data['queryId'] );

		return $request_data;
	}

	/**
	 * Register the persisted query post type. Filter register_post_type_args to
	 * show persisted queries in GraphQL. 💅
	 *
	 * @return void
	 */
	private function register_post_type() {
		register_post_type(
			$this->post_type,
			[
				'label'               => 'Queries',
				'public'              => false,
				'query_var'           => true,
				'rewrite'             => false,
				'show_in_rest'        => false,
				'show_in_graphql'     => false,
				'graphql_single_name' => 'persistedQuery',
				'graphql_plural_name' => 'persistedQueries',
				'show_ui'             => true,
				'supports'            => [ 'title', 'editor' ],
			]
		);
	}

	/**
	 * Save (persist) a query.
	 *
	 * @param  string $query_id Query ID
	 * @param  string $query    GraphQL query
	 * @param  string $name     Operation name
	 * @return void
	 */
	public function save( $query_id, $query, $name = 'UnnamedQuery' ) {
		// Check to see if the query has already been persisted. If so, we're done.
		if ( ! empty( $this->load( $query_id ) ) ) {
			return;
		}

		// Persist the query.
		wp_insert_post( [
			'post_content' => $query,
			'post_name'    => $query_id,
			'post_title'   => $name,
			'post_status'  => 'draft',
			'post_type'    => $this->post_type,
		] );
	}
}
