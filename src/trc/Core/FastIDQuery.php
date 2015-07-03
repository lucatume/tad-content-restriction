<?php


class trc_Core_FastIDQuery extends WP_Query implements trc_Core_QueryInterface {

	public static function instance( $query ) {
		if ( is_array( $query ) ) {
			$defaults = array(
				'fields'                 => 'ids',
				'suppress_filters'       => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'nopaging'               => true,
				'norestriction'          => true
			);
			$query    = array_merge( $query, $defaults );
		}

		return new self( $query );
	}

}