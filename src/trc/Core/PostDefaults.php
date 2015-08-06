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

	public function hook() {

		/**
		 * Fires once a post has been saved.
		 *
		 * @since 2.0.0
		 *
		 * @param int     $post_ID Post ID.
		 * @param WP_Post $post    Post object.
		 * @param bool    $update  Whether this is an existing post being updated or not.
		 */
		add_action( 'wp_insert_post', array(
			$this,
			'apply_default_terms'
		), 99, 3 );
	}

	public function set_user_slug_provider_for( $taxonomy, trc_Public_UserSlugProviderInterface $user_slug_provider ) {
		$this->user_slug_providers[ $taxonomy ] = $user_slug_provider;
	}

	public function apply_default_terms( $post_id, WP_Post $post, $update = false ) {
		if ( $update ) {
			// if editing an existing posts defaults are either in place or the user manually set them
			return;
		}
		$this->apply_default_restrictions( $post_id, $post );
	}

	public function apply_default_restrictions( $post_id, WP_Post $post = null ) {
		$post = empty( $post ) ? get_post( $post_id ) : $post;
		foreach ( $this->user_slug_providers as $taxonomy => $slug_provider ) {
			wp_set_object_terms( $post_id, $slug_provider->get_default_post_terms( $post ), $taxonomy, true );
		}
	}

	public function fetch_posts_with_no_default_restriction( $post_type = 'post', $taxonomy = null ) {
		$single_return = false;
		if ( is_array( $taxonomy ) ) {
			$taxonomies = $taxonomy;
		} else if ( empty( $taxonomy ) ) {
			$taxonomies = get_object_taxonomies( $post_type );
		} else {
			$single_return = true;
			$taxonomies    = array( $taxonomy );
		}

		$transient    = "all_{$post_type}_posts";
		$all_post_ids = get_transient( $transient );
		if ( ! $all_post_ids ) {
			$all_post_ids = get_posts( array(
				'post_type'       => $post_type,
				'post_status'     => 'any',
				'fields'          => 'ids',
				'supress_filters' => true,
				'nopaging'        => true,
			) );
			set_transient( $transient, $all_post_ids );
		}

		foreach ( $taxonomies as $tax ) {
			if ( empty( $this->user_slug_providers[ $tax ] ) ) {
				$unrestricted[ $tax ] = array();
				continue;
			}
			$slug_provider = $this->user_slug_providers[ $tax ];
			if ( empty( $slug_provider->get_default_post_terms( $post_type ) ) ) {
				$unrestricted[ $tax ] = array();
				continue;
			}
			$restricted_ids       = get_posts( array(
				'post_type'       => $post_type,
				'post_status'     => 'any',
				'fields'          => 'ids',
				'supress_filters' => true,
				'nopaging'        => true,
				'tax_query'       => [
					[
						'taxonomy' => $tax,
						'operator' => 'EXISTS'
					]
				]
			) );
			$unrestricted[ $tax ] = array_diff( $all_post_ids, $restricted_ids );
		}

		return $single_return ? array_pop( $unrestricted ) : $unrestricted;
	}

}