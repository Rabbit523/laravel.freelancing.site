<?php
class cPass {
	function __construct($module = "", $id = 0) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}
	public function getPageContent() {
		$html = new MainTemplater(DIR_TMPL . "{$this->module}/{$this->module}.skd");
		$html = $html->compile();
		return $html;
	}
	public function cPassSubmit($data){
		extract($data);
	
		if ($password1 == $cPassword) {
			$cnt = $this->db->update("tbl_users",array('password'=>md5($password1),'token' => ''),array("id"=>$id))->affectedRows();
			if ($cnt > 0) {
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOUR_PASSWORD_HAS_BEEN_RESET_SUCCESSFULLY));
                redirectPage(SITE_URL);						
			}
			else
			{
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=> SOMETHING_WENT_WRONG));
                redirectPage(SITE_URL);
			}
		} else {
		
			$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=> CONFIRMED_PASSWORD_DOESNT_MATCH));
                redirectPage(SITE_URL);
		}
			
	}
}

?>
