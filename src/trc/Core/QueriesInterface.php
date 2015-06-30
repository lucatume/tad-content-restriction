<?php


interface trc_Core_QueriesInterface {

	public static function instance();

	public function should_restrict_queries();

	public function should_restrict_query( WP_Query $query );
}