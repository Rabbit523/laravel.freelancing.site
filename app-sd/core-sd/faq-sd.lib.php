<?php
class Faq {
	function __construct($module = "",$id=0) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;

		}
		$this->module = $module;
		$this->id = $id;
		
	}

	public function getPageContent() {

		$html = new MainTemplater(DIR_TMPL . $this->module . "/" . $this->module . ".skd");
		$html = $html->compile();

		$query_data = $this->db->select("tbl_faq_category","*",array("isActive"=>'y'))->result();	
		$faq_id = ($query_data['id']=='') ? '0': $query_data['id'];

		$field_array = array("%QUESTION%","%FAQ_CATEGORY%");
		$replace_array = array($this->faq_load($faq_id),$this->faq_category());
		$fields_replace = str_replace($field_array,$replace_array,$html);
		return $fields_replace;

	}

	public function faq_load($categoryName,$keyword='')
	{
		if($categoryName == '0')
		{
			$form_data = NO_FAQ_FOUND;
		}
		else
		{
			if($keyword=='')
			{
				$query = $this->db->pdoQuery("select * from tbl_faq where isActive='y' and faqCategoryId='".$categoryName."' ");
			}
			else
			{
				$query = $this->db->pdoQuery("select * from tbl_faq where isActive='y' and faqCategoryId='".$categoryName."' and (question LIKE '%".$keyword."%' OR ansDesc LIKE '%".$keyword."%') ");
			}
			

			$query_data = $query->results();
			$count_data = $query->affectedRows();
			if($count_data == '0'){
				$form_data = "";
				$form_data .= '<div class="panel-body"> '.NO_FAQ_FOUND.'.</div>';
			}
			else{			
				$form_data = "";
				$i = 1;
				foreach ($query_data as $value) 
				{

					$html_faq = new MainTemplater(DIR_TMPL . $this->module . "/loop_data-sd.skd");
					$html_faq = $html_faq->compile();

					$faq_data = array(
						"%IN%" => ($i==1) ? "in" : "",
						"%collapsed%" => ($i==1) ? "" : "collapsed",
						"%ID%" => $value['id'],
						"%QUESTION%" => $value['question'],
						"%DESCRIPTION%" => $value['ansDesc']
						);

					$form_data .= str_replace(array_keys($faq_data), array_values($faq_data), $html_faq);
					$i++;
				}
			}
		}

		return $form_data;
	}
	public function faq_category()
	{
		$query_data = $this->db->select("tbl_faq_category","*",array("isActive"=>'y'))->results();

		$form_data = '';
		$i=1;
		foreach ($query_data as $value) 
		{

			$html_faq = new MainTemplater(DIR_TMPL . $this->module . "/category-sd.skd");
			$html_faq = $html_faq->compile();

			$faq_data = array(
				"%CATEGORY_ID%" => $value['id'],
				"%CATEGORY_NAME%" => $value['categoryName'],
				"%ACTIVE_CLASS%" => ($i=='1') ? 'active' : ''
				);

			$form_data .= str_replace(array_keys($faq_data), array_values($faq_data), $html_faq);
			$i++;
		}

		return $form_data;
	}
	public function search_faq($keyword,$cat='')
	{
		$keyword = str_replace(array('_', '%'), array('\_', '\%'), $keyword);
		$wArray = array();
		if (!empty($keyword)) 
		{
            $where = "isActive='y' and ( question LIKE '%".$keyword."%' OR ansDesc LIKE '%".$keyword."%')";
            $where .= ($cat!='') ? " and faqCategoryId ='".$cat."' " : "";
        }

		$query = $this->db->pdoQuery("SELECT * from tbl_faq where $where")->results();

		$form_data = "";
		
		if(count($query) == 0)
		{
			$form_data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RESULT_FOUND."</span>";
		}
		else
		{
			$i = 1;
			foreach ($query as $value) {

				$html_faq = new MainTemplater(DIR_TMPL . $this->module . "/loop_data-sd.skd");
				$html_faq = $html_faq->compile();

				$faq_data = array(
					"%QUESTION%" => $value['question'],
					"%DESCRIPTION%" => $value['ansDesc'],
					"%ID%" => $value['id'],
					"%IN%" => ($i==1) ? "in" : "",
					"%collapsed%" => ($i==1) ? "" : "collapsed",
					);

				$form_data .= str_replace(array_keys($faq_data), array_values($faq_data), $html_faq);
				$i++;
			}
		}
		return $form_data;
	}
	
	
	
	
}

?>
