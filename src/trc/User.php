<?php


class trc_User implements trc_UserInterface {

	/**
	 * @var WP_User
	 */
	protected $wp_user;

	/**
	 * @var trc_UserSlugProviderInterface[]
	 */
	protected $user_slug_providers = array();

	/**
	 * @var trc_RestrictingTaxonomiesInterface
	 */
	protected $taxonomies;

	public static function instance() {
		$instance = new self;

		$instance->set_user( get_user_by( 'id', get_current_user_id() ) );

		return $instance;
	}

	public function set_user( WP_User $user ) {
		$this->wp_user = $user;
	}

	public function get_content_access_slugs( $taxonomy ) {
		// @todo use user slug taxonomy provider class here
		$user_meta_key = trc_Plugin::instance()->user_content_access_slug_meta_key;
		$slugs         = get_user_meta( get_current_user_id(), $user_meta_key, true );

		$slugs = isset( $slugs[ $taxonomy ] ) ? $slugs[ $taxonomy ] : array();

		return apply_filters( 'trc_user_content_access_slugs', $slugs, get_current_user() );
	}

	public function can_access_query( WP_Query $query ) {

		return apply_filters( 'trc_user_can_access_query', true, $query, $this->wp_user );

	}

	/**
	 * @param int|WP_Post|null $post A post ID, a post object or null to use the current globally defined post.
	 *
	 * @return bool|WP_Error True if the user can access the post, false if the user cannot access the post, a WP_Error
	 *                       if the post parameter is not valid.
	 */
	public function can_access_post( $post = null ) {

		$post = empty( $post ) ? get_post() : get_post( $post );

		if ( empty( $post ) ) {
			return new WP_Error( 'invalid_post', 'The post parameter is not a valid post ID, post object or there is no globally defined post.' );
		}

		$taxonomies = $this->taxonomies->get_restricting_taxonomies( $post->post_type );

		if ( empty( $taxonomies ) ) {
			return true;
		}

		if ( empty( $this->user_slug_providers ) ) {
			return true;
		}

		$can_access = 1;
		foreach ( $taxonomies as $tax ) {
			$slugs = wp_get_object_terms( $post->ID, $tax, array( 'fields' => 'slug' ) );
			if ( empty( $slugs ) ) {
				$can_access *= 1;
				continue;
			}

			$user_slugs = $this->get_user_slugs_for( $tax );

			if ( empty( $user_slugs ) ) {
				$can_access = 0;
				break;
			}

			$can_access *= count( array_intersect( $slugs, $user_slugs ) );
		}

		return apply_filters( 'trc_user_can_access_post', (bool) $can_access, $post, $this->wp_user );
	}

	protected function get_user_slugs_for( $tax ) {
		if ( array_key_exists( $tax, $this->user_slug_providers ) ) {
			$providers = $this->user_slug_providers;

			return $providers[ $tax ]->get_user_slugs();
		}

		return array();
	}

	public function get_user_slug_providers() {
		return $this->user_slug_providers;
	}

	public function add_user_slug_provider( $taxonomy, trc_UserSlugProviderInterface $user_slug_provider ) {
		$this->user_slug_providers[ $taxonomy ] = $user_slug_provider;

		return $this;
	}

	public function remove_user_slug_provider( $taxonomy ) {
		$this->user_slug_providers = array_diff_key( $this->user_slug_providers, array( $taxonomy => 1 ) );

		return $this;
	}

	public function set_taxonomies( trc_RestrictingTaxonomiesInterface $taxonomies ) {
		$this->taxonomies = $taxonomies;
	}

}