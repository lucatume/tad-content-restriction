<?php


class trc_FilteringTaxQueryGenerator implements trc_FilteringTaxQueryGeneratorInterface {

	/**
	 * @var string
	 */
	protected $restricting_tax_name;

	/**
	 * @var trc_User
	 */
	protected $user;

	public function get_array_for( $restricting_tax_name ) {
		return array(
			array(
				'taxonomy' => $restricting_tax_name,
				'field'    => 'slug',
				'terms'    => $this->user->get_content_access_slugs(),
				'operator' => 'IN'
			)
		);
	}

	public static function instance() {
		$instance = new self;

		$instance->set_user( trc_User::instance() );

		return $instance;
	}

	public function set_user( trc_User $user ) {
		$this->user = $user;
	}
}