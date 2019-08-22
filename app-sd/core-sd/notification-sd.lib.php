 <?php
class Notification {
	function __construct($module = "",$id=0) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;}
		$this->module = $module;
		$this->id = $id;
	}
	public function getPageContent() {
		
		$htmlItems = new MainTemplater(DIR_TMPL . $this->module . "/notification-sd.skd");
		$htmlItems = $htmlItems->compile();

		$data = $this->db->pdoQuery('SELECT * FROM `tbl_notification` WHERE userid = '.$this->sessUserId.' AND (notificationtype = "c" OR notificationtype = "f") order by createdDate desc')->results();
		$notification_msg = "";
		if(count($data) > 0){
			foreach ($data as $value) 
			{
				$html = new MainTemplater(DIR_TMPL . $this->module . "/loop_data-sd.skd");
				$html = $html->compile();
				
				$notification_msg .= str_replace(array('%AVATAR%',"%DESC%","%TIME%","%ID%","%LINK%"), array(getAvatar($value['message']),$value['message'],date(DATE_FORMAT,strtotime($value['createdDate'])),$value['id'],$value['detail_link']),$html);
			}
		}else{
			$notification_msg="<div class='no-records'><i class='fa fa-exclamation-triangle'></i> No records found!</div>";
		}
		
		$content = str_replace(array("%NOTIFICATION%"), array($notification_msg),$htmlItems);
		
		return $content;

	}	
}


?>
