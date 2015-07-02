<?php


class trc_Core_FilteringTaxQueryGenerator implements trc_Core_FilteringTaxQueryGeneratorInterface {

	/**
	 * @var string
	 */
	protected $restricting_tax_name;

	/**
	 * @var trc_Core_UserInterface
	 */
	protected $user;

	/**
	 * @param      $restricting_tax_name
	 *
	 * @param bool $include Whether the `IN` or the `NOT IN` operators should be used.
	 *
	 * @return array|WP_Error Either a tax query array or a WP_Error if the user is not set or the taxonomy is not a
	 *                        string.
	 */
	public function get_tax_query_for( $restricting_tax_name, $include = true ) {
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
			'operator' => $include ? 'IN' : 'NOT IN'
		);
	}

	public static function instance() {
		$instance = new self;

		$instance->user = trc_Core_Plugin::instance()->user;

		return $instance;
	}

	/**
	 * @param trc_Core_UserInterface $user
	 */
	public function set_user( trc_Core_UserInterface $user ) {
		$this->user = $user;
	}
}