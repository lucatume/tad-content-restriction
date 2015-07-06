<?php


interface trc_Core_QueryMarshalInterface {

	public function set_query( WP_Query $query );

	public function set_query_scrutinizer( trc_Core_QueryScrutinizerInterface $query_scrutinizer );

	public function set_excluded_posts();

	public function set_filtering_tax_query();
}