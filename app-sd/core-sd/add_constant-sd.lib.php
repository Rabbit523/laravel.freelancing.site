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
		echo "string";exit();
		$html = new MainTemplater(DIR_TMPL . "{$this->module}/{$this->module}.skd");
		$selectPageId=$this->db->pdoQuery("SELECT id from tbl_adminrole where sectionid=10");
		print_r($selectPageId);exit();
		$html = $html->compile();
		return $html;
	}
	public function cPassSubmit($data){
		
		extract($data);
		
				if ($password1 == $cPassword) {
					$cnt = $this->db->update("tbl_users",array('password'=>md5($password1),'token' => ''),array("id"=>$id))->affectedRows();
					if ($cnt > 0) {
						$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>"Congratulations! Your password has been reset successfully"));
                        redirectPage(SITE_URL);						
					}
					else
					{
						$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>"Something went wrong, please try again!"));
                        redirectPage(SITE_URL);
					}
				} else {
				
					$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>"Confirmed password doesn't match"));
                        redirectPage(SITE_URL);
				}
			
	}
}

?>
