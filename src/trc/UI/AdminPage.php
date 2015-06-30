<?php


class trc_UI_AdminPage {

	public static function instance() {
		return new self;
	}

	public function init() {
		return $this;
	}
}