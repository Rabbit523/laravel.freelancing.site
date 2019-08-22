<?php
class Content extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {

		$html = (new MainTemplater(DIR_TMPL . "content-sd/content-sd.skd"))->compile();

		$fields = array(
			"%PAGE_TITLE%" => (!empty($this->result[l_values("pageTitle")]) ? $this->result[l_values("pageTitle")] : ''),
			"%CONTENT%" => (!empty($this->result[l_values("pageDesc")]) ? $this->result[l_values("pageDesc")] : '')
		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
}
?>