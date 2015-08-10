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

	public function get_unrestricted_posts( $limit = false ) {
		if ( ! ( empty( $limit ) || is_numeric( $limit ) || is_bool( $limit ) ) ) {
			throw new InvalidArgumentException( 'Limit parameter must be an int, a bool or null.' );
		}

		$taxonomies = array_keys( $this->user_slug_providers );
		$posts      = array();
		foreach ( $taxonomies as $tax ) {
			$post_types = $this->get_restricted_post_types_for_taxonomy( $tax );

			foreach ( $post_types as $post_type ) {
				$unrestricted_posts = $this->get_unrestricted_for_taxonomy( $post_type, $tax );

				if ( count( $unrestricted_posts ) ) {
					$posts[ $tax ] = $unrestricted_posts;
				}
			}
		}

		if ( ! empty( $posts ) && $limit && intval( $limit ) > 0 ) {
			$_limit = intval( $limit );
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

		return array( $post_types );
	}


}