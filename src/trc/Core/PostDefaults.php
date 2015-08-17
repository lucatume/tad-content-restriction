<?php


class trc_Core_PostDefaults {

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * @var trc_Public_UserSlugProviderInterface[]
	 */
	protected $user_slug_providers = array();

	public static function instance() {
		if ( empty( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function set_user_slug_provider_for( $taxonomy, trc_Public_UserSlugProviderInterface $user_slug_provider ) {
		$this->user_slug_providers[ $taxonomy ] = $user_slug_provider;
	}

	public function has_unrestricted_posts() {
		$taxonomies = array_keys( $this->user_slug_providers );
		foreach ( $taxonomies as $tax ) {
			$post_types = $this->get_restricted_post_types_for_taxonomy( $tax );

			foreach ( $post_types as $post_type ) {
				$unrestricted_posts = $this->get_unrestricted_for_taxonomy( $post_type, $tax );

				if ( count( $unrestricted_posts ) ) {
					return true;
				}
			}
		}

		return false;
	}

	public function get_unrestricted_posts( array $args = null ) {
		$args = wp_parse_args( $args, array( 'limit' => false, 'post_type' => false ) );

		if ( ! ( empty( $args['limit'] ) || is_numeric( $args['limit'] ) || is_bool( $args['limit'] ) ) ) {
			throw new InvalidArgumentException( 'Limit parameter must be an int, a bool or null.' );
		}

		if ( ! ( $args['post_type'] === false || is_string( $args['post_type'] ) ) ) {
			throw new InvalidArgumentException( 'Post type parameter must be a string or false.' );
		}

		$wanted_post_types = false;
		if ( $args['post_type'] ) {
			$wanted_post_types = is_array( $args['post_type'] ) ? $args['post_type'] : array( $args['post_type'] );
		}

		$found_by_post_type = array();
		$taxonomies         = array_keys( $this->user_slug_providers );
		$posts              = array();
		foreach ( $taxonomies as $tax ) {
			$post_types = $this->get_restricted_post_types_for_taxonomy( $tax );

			if ( $wanted_post_types ) {
				$post_types = array_intersect( $post_types, $wanted_post_types );
			}

			$found_posts = array();
			foreach ( $post_types as $post_type ) {
				$unrestricted_posts = $this->get_unrestricted_for_taxonomy( $post_type, $tax );

				if ( count( $unrestricted_posts ) ) {
					if ( is_string( $post_type ) ) {
						if ( isset( $found_by_post_type[ $post_type ] ) ) {
							$found_by_post_type[ $post_type ] = array_merge( $found_by_post_type[ $post_type ], $unrestricted_posts );
						} else {
							$found_by_post_type[ $post_type ] = $unrestricted_posts;
						}
					}
					$found_posts = array_merge( $found_posts, $unrestricted_posts );
				}
			}
			if ( $found_posts ) {
				$posts[ $tax ] = array_unique( $found_posts );
			}
		}

		if ( ! empty( $posts ) && $args['limit'] && intval( $args['limit'] ) > 0 ) {
			$_limit = intval( $args['limit'] );
			$_posts = [ ];
			foreach ( $posts as $taxonomy => $ids ) {
				$count = count( $ids );
				if ( $count <= $_limit ) {
					$_limit -= $count;
					$_posts[ $taxonomy ] = $ids;
				} else {
					$_ids                = array_values( array_splice( $ids, 0, $_limit ) );
					$_posts[ $taxonomy ] = $_ids;
					break;
				}
			}

			$posts = $_posts;
		}

		return $posts;
	}

	protected function get_unrestricted_for_taxonomy( $post_type, $taxonomy ) {
		$unrestricted_posts = get_posts( array(
			'fields'           => 'ids',
			'nopaging'         => true,
			'suppress_filters' => true,
			'post_type'        => $post_type,
			'tax_query'        => array(
				array(
					'taxonomy' => $taxonomy,
					'operator' => 'NOT EXISTS'
				)
			)
		) );

		return $unrestricted_posts;
	}

	/**
	 * @param $taxonomy
	 *
	 * @return array
	 */
	protected function get_restricted_post_types_for_taxonomy( $taxonomy ) {
		$tax_object = get_taxonomy( $taxonomy );
		$post_types = $tax_object->object_type;

		$slug_provider = $this->user_slug_providers[ $taxonomy ];
		if ( empty( $slug_provider->get_default_post_terms() ) ) {
			return [ ];
		}

		return is_array( $post_types ) ? $post_types : array( $post_types );
	}


}