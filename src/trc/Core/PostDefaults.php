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
			$tax_object    = get_taxonomy( $tax );
			$post_types    = $tax_object->object_type;
			$slug_provider = $this->user_slug_providers[ $tax ];

			if ( empty( $slug_provider->get_default_post_terms() ) ) {
				continue;
			}

			foreach ( $post_types as $post_type ) {
				$unrestricted_posts = get_posts( array(
					'fields'           => 'ids',
					'nopaging'         => true,
					'suppress_filters' => true,
					'post_type'        => $post_type,
					'tax_query'        => array(
						array(
							'taxonomy' => $tax,
							'operator' => 'NOT EXISTS'
						)
					)
				) );

				if ( count( $unrestricted_posts ) ) {
					return true;
				}
			}
		}

		return false;
	}


}