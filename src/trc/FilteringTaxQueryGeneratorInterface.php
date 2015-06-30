<?php


interface trc_FilteringTaxQueryGeneratorInterface {

	public function get_array_for( $restricting_tax_name );

	public static function instance();

	public function set_user( trc_User $user );
}