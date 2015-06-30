<?php


class trc_FilteringTaxQueryGenerator implements trc_FilteringTaxQueryGeneratorInterface {

	/**
	 * @var string
	 */
	protected $restricting_tax_name;

	/**
	 * @var trc_UserInterface
	 */
	protected $user;

	/**
	 * @param $restricting_tax_name
	 *
	 * @return array|WP_Error Either a tax query array or a WP_Error if the user is not set or the taxonomy is not a
	 *                        string.
	 */
	public function get_tax_query_for( $restricting_tax_name ) {
		if ( empty( $this->user ) ) {
			return new WP_Error( 'user_not_set', 'User is not set' );
		}

		if ( ! is_string( $restricting_tax_name ) ) {
			return new WP_Error( 'bad_taxonomy_argument', 'Taxonomy must be a string' );
		}

		return array(
			'taxonomy' => $restricting_tax_name,
			'field'    => 'slug',
			'terms'    => $this->user->get_user_slugs_for( $restricting_tax_name ),
			'operator' => 'IN'
		);
	}

	public static function instance() {
		$instance = new self;

		$instance->user = trc_Plugin::instance()->user;

		return $instance;
	}

	/**
	 * @param trc_UserInterface $user
	 */
	public function set_user( trc_UserInterface $user ) {
		$this->user = $user;
	}
}