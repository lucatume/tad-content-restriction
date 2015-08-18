<?php


class trc_Core_AbstractUserSlugProviderClient {

	/**
	 * @var trc_Public_UserSlugProviderInterface[]
	 */
	protected $user_slug_providers = array();

	public function set_user_slug_provider_for( $taxonomy, trc_Public_UserSlugProviderInterface $user_slug_provider ) {
		$this->user_slug_providers[ $taxonomy ] = $user_slug_provider;
	}
}