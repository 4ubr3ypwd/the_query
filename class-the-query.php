<?php

if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'The_Query' ) ) {
	/**
	 * Query Cache.
	 *
	 * In order to avoid assigning new WP_Query's to the global scope
	 * in variables within the theme, we can use `the_register_query( 'my-query', $args );`
	 * to create them and cache them here.
	 *
	 * We can then use `the_query( 'my-query' )` to access them.
	 */
	class The_Query {
		/**
		 * Class instance.
		 *
		 * @var null
		 */
		protected static $instance = null;

		/**
		 * Get instance.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Query Cache.
		 *
		 * @var array
		 */
		public $queries = array();

		/**
		 * Get a cached Query.
		 */
		function __construct() {
			// Doing nothing is better than being busy doing nothing. – Lao Tzu
		}

		/**
		 * Get a Query
		 * @param  string $query_name The assigned query name.
		 * @return object             WP_Query.
		 */
		public function get_query( $query_name ) {
			if ( isset( $this->queries[ $query_name ] ) ) {
				if ( is_object( $this->queries[ $query_name ] ) ) {
					// We've queried the args, so this is already a WP_Query.
					return $this->queries[ $query_name ];
				} else {
					// Query the args and cache the Query.
					$this->queries[ $query_name ] = new WP_Query( $this->queries[ $query_name ] );

					// Here's the query.
					return $this->queries[ $query_name ];
				}
			} else {
				return new WP_Error( 'bad_query_name', sprintf( __( 'Sorry, but the query %s does not exist.', 'clp' ), "<em>{$query_name}</em>" ) );
			}
		}

		/**
		 * Cache a Query.
		 *
		 * @param  string $query_name   Name of query.
		 * @param  array $args          WP_Query arguments.
		 */
		public function cache_query( $query_name, $args ) {
			$this->queries[ $query_name ] = $args;
		}
	} // The_Query (class).

	/**
	 * Access The_Query or Get a Cached Query.
	 *
	 * @return object WP_Query object if query name is supplied,
	 *                or the instance of The_Query.
	 */
	function the_query( $query_name = false ) {
		if ( $query_name ) {
			return The_Query::get_instance()->get_query( $query_name );
		} else {
			// We're creating first instance.
			return The_Query::get_instance();
		}
	}

	/**
	 * Register a Query.
	 *
	 * Creates a new WP_Query and caches it in our cache so
	 * you can access it later using the_query( 'my-query' ).
	 *
	 * @param  string $query_name The name of your query.
	 * @param  array $args WP_Query arguments.
	 */
	function the_register_query( $query_name, $args ) {
		the_query()->cache_query( $query_name, $args );
	}
} // if class exists.
