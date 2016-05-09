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

		function __construct() {
			// Doing nothing is better than being busy doing nothing. – Lao Tzu
		}

		public function get_query( $query_name_or_args, $query_name = false ) {

				/*
				 * We asked for the query by name, but nothing is set.
				 *
				 *     the_query( 'query_name' );
				 *
				 */
				if ( is_string( $query_name_or_args ) && ! isset( $this->queries[ $query_name_or_args ] ) ) {

					// Return an error.
					return new WP_Error( 'the_query_bad_query_name', __( 'Sorry, but this query does not exists', 'the-query' ) );

				/*
				 * We passed arguments for a new query, and a name, so let's store those arguments since they don't exists.
				 *
				 *     the_query( array(), 'query_name' );
				 *
				 */
				} else if ( is_array( $query_name_or_args ) && is_string( $query_name ) && ! isset( $this->queries[ $query_name_or_args ] ) ) {

					// Cache the arguments.
					$this->cache_query( $query_name, $query_name_or_args );

				/*
				 * We just passed a name, and the query or the args already exist in the cache.
				 *
				 *     the_query( 'query_name' );
				 *
				 */
				} else if ( is_string( $query_name_or_args ) && isset( $this->queries[ $query_name_or_args ] ) ) {

					// Looks like this was already converted to a query.
					if ( is_object( $this->queries[ $query_name_or_args ] ) ) {
						return $this->queries[ $query_name_or_args ];

					// They are just args at the moment, let's make it a query.
					} else if ( is_array( $this->queries[ $query_name_or_args ] ) ) {

						// Set the cache to the actual query and also return it to the user.
						return $this->cache_query( $query_name, new WP_Query( $this->queries[ $query_name_or_args ] ) );
					}

				/*
				 * We passed only arguments, and no name, let's try and find the query or make a new one and give it a name.
				 *
				 *     the_query( array() );
				 *
				 */
				} else if ( is_array( $query_name_or_args ) && ! $query_name ) {
					foreach ( $this->queries as $query_args_or_wp_query ) {

						// The arguments match the one's in the cache, so let's convert that to a WP_Query and return it.
						if ( is_array( $query_args_or_wp_query ) && $query_args_or_wp_query == $query_name_or_args ) {
							return $this->cache_query( $query_name, new WP_Query( $this->queries[ $query_name_or_args ] ) );

						// This one is already and WP_Query, so we have to create WP_Query to compare.
						} else if ( is_object( $query_args_or_wp_query ) ) {
							$wp_query = new WP_Query( $query_name_or_args );

							// The query is the same, so return a new query.
							if ( $wp_query === $query_args_or_wp_query ) {
								return $wp_query.
							}
						}
					}
				}
		}

		public function cache_query( $query_name, $args_or_wp_query ) {
			return $this->queries[ $query_name ] = $args_or_wp_query;
		}
	} // The_Query (class).

	function the_query( $query_name = false ) {
		if ( $query_name ) {
			return The_Query::get_instance()->get_query( $query_name );
		} else {
			// We're creating first instance.
			return The_Query::get_instance();
		}
	}

	function the_register_query( $query_name, $args ) {
		return the_query()->cache_query( $query_name, $args );
	}
} // if class exists.
