<?php


class trc_Core_User implements trc_Core_UserInterface {

	/**
	 * @var WP_User
	 */
	protected $wp_user;

	/**
	 * @var trc_Public_UserSlugProviderInterface[]
	 */
	protected $user_slug_providers = array();

	/**
	 * @var trc_Core_RestrictingTaxonomiesInterface
	 */
	protected $taxonomies;

	public static function instance() {
		$instance = new self;

		$instance->user       = get_user_by( 'id', get_current_user_id() );
		$instance->taxonomies = trc_Core_Plugin::instance()->taxonomies;

		return $instance;
	}

	/**
	 * @param WP_User $user
	 */
	public function set_user( WP_User $user ) {
		$this->wp_user = $user;
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

		$taxonomies = $this->taxonomies->get_restricting_taxonomies_for( $post->post_type );

		if ( empty( $taxonomies ) ) {
			return true;
		}

		if ( empty( $this->user_slug_providers ) ) {
			return true;
		}

		$can_access = 1;
		foreach ( $taxonomies as $tax ) {
			$slugs = wp_get_object_terms( $post->ID, $tax, array( 'fields' => 'slugs' ) );
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

	/**
	 * @param string $tax
	 *
	 * @return array|string[]
	 */
	public function get_user_slugs_for( $tax ) {
		$slugs = array();

		if ( array_key_exists( $tax, $this->user_slug_providers ) ) {
			$providers = $this->user_slug_providers;

			$slugs = $providers[ $tax ]->get_user_slugs();
		}

		return apply_filters( 'trc_user_slugs_for', $slugs, $tax, $this );
	}

	/**
	 * @return trc_Public_UserSlugProviderInterface[]
	 */
	public function get_user_slug_providers() {
		return $this->user_slug_providers;
	}

	/**
	 * @param string                               $taxonomy
	 * @param trc_Public_UserSlugProviderInterface $user_slug_provider
	 *
	 * @return $this
	 */
	public function add_user_slug_provider( $taxonomy, trc_Public_UserSlugProviderInterface $user_slug_provider ) {
		$this->user_slug_providers[ $taxonomy ] = $user_slug_provider;

		return $this;
	}

	/**
	 * @param $taxonomy
	 *
	 * @return $this
	 */
	public function remove_user_slug_provider( $taxonomy ) {
		$this->user_slug_providers = array_diff_key( $this->user_slug_providers, array( $taxonomy => 1 ) );

		return $this;
	}

	/**
	 * @param trc_Core_RestrictingTaxonomiesInterface $taxonomies
	 */
	public function set_taxonomies( trc_Core_RestrictingTaxonomiesInterface $taxonomies ) {
		$this->taxonomies = $taxonomies;
	}

}