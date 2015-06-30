<?php


interface trc_Core_FilteringTaxQueryGeneratorInterface {

	public function get_tax_query_for( $restricting_tax_name );

	public static function instance();

	public function set_user( trc_Core_UserInterface $user );
}