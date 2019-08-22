<?php
class Maintenance {
	function __construct($module = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		
	}

	public function getPageContent() {
		$main_content = (new MainTemplater(DIR_TMPL . $this->module . "/" . $this->module . ".skd"))->compile();
		return $main_content;
	}
	
}

?>
