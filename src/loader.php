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
 */
class Loader {
	/**
	 * Namespace for WP filters.
	 *
	 * @var string
	 */
	private $namespace = 'graphql_persisted_queries';

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
		 * Post type to use to persist queries. Unused if disabled.
		 *
		 * @param string $post_type
		 * @since 1.0.0
		 */
		$this->post_type = apply_filters( "{$this->namespace}_post_type", $this->post_type );

		// If the post type doesn't look right, don't hook.
		if ( empty( $this->post_type ) || post_type_exists( $this->post_type ) ) {
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
		// Client sends *both* queryId and query == request to persist query.
		if ( ! empty( $request_data['queryId'] ) && ! empty( $request_data['query'] )  ) {
			$this->save( $request_data['queryId'], $request_data['query'], $request_data['operation'] );
		}

		// Client sends queryId but *not* query == optimistic request to use cached query.
		if ( ! empty( $request_data['queryId'] ) && empty( $request_data['query'] ) ) {
			$request_data['query'] = $this->load( $request_data['queryId'] );
		}

		// We've got this. Call off any other persistence implementations.
		unset( $request_data['queryId'] );

		return $request_data;
	}

	/**
	 * Register the persisted query post type. We could filter a lot of individual
	 * values here, but we won't. If further customization is wanted, filter
	 * register_post_type_args.
	 *
	 * @return void
	 */
	private function register_post_type() {
		/**
		 * Whether persisted queries can be themselves queried via GraphQL. 💅
		 *
		 * @param bool $show_in_graphql Show in GraphQL?
		 * @since 1.0.0
		 */
		$show_in_graphql = apply_filters( "{$this->namespace}_show_in_graphql", false );

		register_post_type(
			$this->post_type,
			[
				'label'               => 'Queries',
				'public'              => false,
				'query_var'           => false,
				'rewrite'             => false,
				'show_in_rest'        => false,
				'show_in_graphql'     => $show_in_graphql,
				'graphql_single_name' => 'persistedQuery',
				'graphql_plural_name' => 'persistedQueries',
				'show_ui'             => is_admin(),
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
