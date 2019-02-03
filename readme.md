# WPGraphQL Persisted Queries

Persisted GraphQL queries allow a GraphQL client to optimistically send a hash
of the query instead of the full query; if the server has seen the query
before, it can satisfy the request. This saves network overhead and makes it
possible to move to `GET` requests instead of `POST`. The primary benefit of
`GET` requests is that they can be easily cached at the edge (e.g., with
Varnish).

This plugin requires [WPGraphQL][wp-graphql] 0.2.0 or newer.

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

## Filters

### `graphql_persisted_queries_post_type`

- Default: `'graphql_query'`
- The custom post type used to persist queries. If empty, queries will not be
  persisted.

### `graphql_persisted_queries_show_in_graphql`

- Default: `false`
- Whether the custom post type will itself be exposed via GraphQL. Enabling
  allows insight into which queries are persisted.

  ```
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
