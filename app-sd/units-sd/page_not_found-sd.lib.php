<?php
class Pagenotfound extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		parent::__construct();
	}

	public function getPageContent() {
	
		$html = (new MainTemplater(DIR_TMPL . "page_not_found-sd/page_not_found-sd.skd"))->compile();
		return $html;
	}
}
?>