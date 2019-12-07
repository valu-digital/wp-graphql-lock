# 🔒 WP GraphQL Lock

This plugin enables query locking for [WPGraphQL][] by implementing persisted
GraphQL queries.

Persisted GraphQL queries allow a GraphQL client to optimistically send a hash
of the query instead of the full query; if the server has seen the query
before, it can satisfy the request.

Once the server knowns all the possible queries the plugin can lock it down
disallowing any unwanted queries that are possibly malicious. This can
greatly improve the server security and can even protect against unpatched
vulnerabilities in some cases.

Alternatively you can pre-generate the query IDs from your client source code
with the [GraphQL Code Generator plugin][codegen] and load the IDs with the
`graphql_lock_load_query` filter.

In addition to enabling query locking this saves network overhead and makes
it possible to move to `GET` requests instead of `POST`. The primary benefit
of `GET` requests is that they can be easily cached at the edge (e.g., with
Varnish, nginx etc.).

This plugin requires WPGraphQL 0.2.0 or newer.

[codegen]: https://github.com/valu-digital/graphql-codegen-persisted-query-ids
[wpgraphql]: https://github.com/wp-graphql/wp-graphql

## Compatibility

Apollo Client provides an easy implementation of persisted queries:

https://github.com/apollographql/apollo-link-persisted-queries#automatic-persisted-queries

This plugin aims to be compatible with that implementation, but will work with
any client that sends a `queryId` alongside the `query`. Make sure your client
also sends `operationName` with the optimistic request.

## Implementation

When the client provides a query hash or ID, that query will be persisted in a
custom post type. By default, this post type will be visible in the dashboard
only to admins.

Query IDs are case-insensitive (i.e., `MyQuery` and `myquery` are equivalent).

## Installation

If you use composer you can install it from packagist

    composer require valu/wp-graphql-lock

Otherwise you can clone it from Github to your plugins using the stable branch

    cd wp-content/plugins
    git clone --branch stable https://github.com/valu-digital/wp-graphql-lock.git

## Filters

### `graphql_lock_load_query`

- Load the queries from a custom location
- The query ID is passed as the second parameter

Example:

```php
add_filter( 'graphql_lock_load_query', function( string $query, string $query_id ) {
    $queries = json_decode( file_get_contents( __DIR__ . '/.persisted-query-ids/server.json' ), true );
    return $queries[ $query_id ] ?? null;
}, 10, 2 );
```

Note: You should prefer using hidden directories / files to avoid exposing the lock file via your webserver.

### `graphql_lock_post_type`

- Default: `'graphql_query'`
- The custom post type used to persist queries. If empty, queries will not be
  persisted.

### `graphql_lock_show_in_graphql`

- Default: `false`
- Whether the custom post type will itself be exposed via GraphQL. Enabling
  allows insight into which queries are persisted.

```graphql
query PersistedQueryQuery {
  persistedQueries {
    nodes {
      id
      title
      content(format: RAW)
    }
  }
}
```

If you'd like to further customize the custom post type, filter
`register_post_type_args`.

[wp-graphql]: https://github.com/wp-graphql/wp-graphql

## Lock mode

When it's active no new queries can be saved and only the saved ones can be
used. This can greatly improve security as attackers cannot send arbitrary
queries to the endpoint.

Lock mode can be activated by setting `graphql_lock_locked` option to true:

```php
update_option( 'graphql_lock_locked', true );
```

```php
add_filter( 'option_graphql_lock_locked', function() {
    return 'production' === WP_ENV;
}, 10 , 1 );
```

## Settings

There's a settings screen for managing the option

![settings](https://user-images.githubusercontent.com/225712/55174721-a360ac00-5186-11e9-91de-bd1c45ffad11.png)

## Acknowledgements

This plugin is based on the [Quartz persisted Queries][original] plugin.

[original]: https://github.com/Quartz/wp-graphql-persisted-queries

## Contributing

Read [CONTRIBUTING.md](/CONTRIBUTING.md)
