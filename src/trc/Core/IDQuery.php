<?php


class trc_Core_IDQuery implements trc_Core_QueryInterface {

	/**
	 * @var WP_Query
	 */
	protected $wp_query;

	/**
	 * @return trc_Core_IDQuery
	 */
	public static function instance() {
		return new self;
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function set( $key, $value ) {
		$this->wp_query->set( $key, $value );

		return $this;
	}

	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	public function set_args( array $args ) {
		$defaults = array(
			'fields'                 => 'ids',
			'suppress_filters'       => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'nopaging'               => true,
			'norestriction'          => true
		);
		$args     = array_merge( $args, $defaults );

		$this->wp_query = new WP_Query( $args );

		return $this;
	}

	/**
	 * @return array
	 */
	public function get_posts() {
		return $this->wp_query->get_posts();
	}
}