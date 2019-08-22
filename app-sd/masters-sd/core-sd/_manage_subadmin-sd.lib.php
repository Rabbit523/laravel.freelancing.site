<?php
class SubAdmin extends Home 
{
	public $data = array();
	public function __construct($id=0, $searchArray=array(), $type='') 
	{
		$this->data['id'] = $this->id = $id;
		$this->table = 'tbl_admin';
		$this->type = ($this->id > 0 ? 'edit' : 'add');
		$this->searchArray = $searchArray;
		parent::__construct();
		if($this->id > 0) 
		{
			$qrySel = $this->db->pdoQuery("
				SELECT id, uName, uEmail, uPass, ipAddress, adminType, isActive,created_date
				FROM tbl_admin
				WHERE id = ?
			", array($this->id))->result();
			$fetchRes = $qrySel;
			$this->data['uName'] = $this->uName = $fetchRes['uName'];
			$this->data['uEmail'] = $this->uEmail = $fetchRes['uEmail'];
			$this->data['uPass'] = $this->uPass = '';
			$this->data['ipAddress'] = $this->ipAddress = $fetchRes['ipAddress'];
			$this->data['adminType'] = $this->adminType = $fetchRes['adminType'];
			$this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
			$this->data['created_date'] = $this->created_date = $fetchRes['created_date'];


			$qrySel_permission = $this->db->pdoQuery("
				SELECT ap.permission,ar.pagenm
				FROM tbl_admin_permission AS ap
				LEFT JOIN tbl_adminrole AS ar ON (ar.id=ap.page_id)
				WHERE admin_id = ?
			", array($this->id))->results();
			foreach($qrySel_permission as $fetchRes_permission)
			{
				if($fetchRes_permission['pagenm'] != "")
				{
					$this->data[$fetchRes_permission['pagenm']] = $this->{$fetchRes_permission['pagenm']} = explode(',',$fetchRes_permission['permission']);
				}
				
			}
		} 
		else 
		{
			$this->data['uName'] = $this->uName = '';
			$this->data['uEmail'] = $this->uEmail = '';
			$this->data['uPass'] = $this->uPass = '';
			$this->data['ipAddress'] = $this->ipAddress = '';
			$this->data['adminType'] = $this->adminType = '';
			$this->data['isActive'] = $this->isActive = 'a';
		}

		switch($type) 
		{
			case 'add' :
			{
				$this->data['content'] =  (in_array('add',$this->Permission))?$this->getForm():'';
				break;
			}
			case 'edit' : 
			{
				$this->data['content'] =  (in_array('edit',$this->Permission))?$this->getForm():'';
				break;
			}
			case 'view' : 
			{
				$this->data['content'] = (in_array('view',$this->Permission))?$this->viewForm():'';
				break;
			}
			case 'view_activity' : 
			{
				$this->data['content'] =  (in_array('view',$this->Permission))?$this->viewActivityForm():'';
				break;
			}
			case 'activity_datagrid' : 
			{
				$this->data['content'] =  (in_array('view',$this->Permission))?json_encode($this->activity_datagrid()):'';
				break;
			}
			case 'delete' : 
			{
				$this->data['content'] =  (in_array('delete',$this->Permission))?json_encode($this->dataGrid()):'';
				break;
			}
			case 'datagrid': 
			{
				$this->data['content'] =  (in_array('module',$this->Permission))?json_encode($this->dataGrid()):'';
			}
		}
	}

	public function viewForm()
	{
		$content = $this->displayBox(array("label"=>"User Name&nbsp;:","value"=>$this->uName)).
		$this->displayBox(array("label"=>"Email&nbsp;:","value"=>$this->uEmail)).
		$this->displayBox(array("label" => "Status&nbsp;:", "value" => $this->isActive == 'a' ? 'Active' : 'Deactive'));
		$qryRes = $this->adminPagePermission($this->id);
		foreach($qryRes as $key => $fetchRes) {
			$tmp = implode(', ', $fetchRes['page_action_id']);
			$content .=	$this->displayBox(array("label"=>$fetchRes['title'].": ", "value"=>$tmp));
		}
		$content.=$this->displaybox(array("label" =>"Created Date&nbsp;:","value" => date(DATE_FORMAT_ADMIN,strtotime($this->created_date)))).
		$this->displaybox(array("label" =>"IP Address&nbsp;:","value" => $this->ipAddress));
		return $content;
	}

	public function viewActivityForm()
	{
		return $main_content = (new MainTemplater(DIR_ADMIN_TMPL.$this->module."/view_activity_datatable-sd.skd"))->compile();
	}

	public function checkBox($chk) 
	{
		$sub_final_result = NULL;
		$chk['label'] = isset($chk['label']) ? $chk['label'] : ' ';
		$chk['value'] = isset($chk['value']) ? $chk['value'] : '';
		$chk['name'] = isset($chk['name']) ? $chk['name'] : array();
		$chk['class'] = isset($chk['class']) ? ''.$chk['class'] : '';
		$chk['extraAtt'] = isset($chk['extraAtt']) ? ' '.$chk['extraAtt'] : '';
		$chk['onlyField'] = isset($chk['onlyField']) ? $chk['onlyField'] : false;
		$chk['text'] = isset($chk["text"])?$chk["text"]:"";
		$chk['noDiv']=isset($chk['noDiv'])?$chk['noDiv']:true;

		$main_content_only_field = (new MainTemplater(DIR_ADMIN_TMPL.$this->module.'/checkbox_onlyfield-sd.skd'))->compile();

		foreach($chk['values'] as $k => $v)
		{
			if(is_array($chk['value'])) 
			{
				$check = (in_array($k, $chk['value'])) ? 'checked="checked"' : '';
			} 
			else 
			{
				$check = ($k == $chk['value']) ? 'checked="checked"' : '';
			}

			$fields_replace = array(
				"%CLASS%" => $chk['class'],
				"%NAME%" => $chk['name'],
				"%ID%" => $chk['name'],
				"%VALUE%" => $k,
				"%EXTRA%" => $chk['extraAtt'],
				"%DISPLAY_VALUE%" => $v,
				"%CHECKED%" => $check
			);

			$sub_final_result .= str_replace(array_keys($fields_replace), array_values($fields_replace), $main_content_only_field);
		}

		if($chk['onlyField'] == true) {
		   return $sub_final_result;
		} else {
			$main_content = (new MainTemplater(DIR_ADMIN_TMPL.$this->module.'/checkbox-sd.skd'))->compile();
			$fields_replace = array(
				"%CHECKBOX_LIST%" => $sub_final_result,
				"%LABEL%" => $chk['label']
			);
			$main_content = str_replace(array_keys($fields_replace), array_values($fields_replace), $main_content);
			return $main_content;
		}
	}

	public function getForm() 
	{
		$content = $main_content = NULL;
		$main_content = (new MainTemplater(DIR_ADMIN_TMPL.$this->module."/form-sd.skd"))->compile();
		$status_a = (($this->isActive=="a") ? 'checked="checked"' : '');
		$status_d = (($this->isActive!="a") ? 'checked="checked"' : '');
		$fetchAction=$this->SubadminAction();
		$qryRes= $this->adminPageList();

		// echo "<pre>";
		// print_r($qryRes);
		// echo "<pre>";
		//exit();


		$edit_frm = ($this->type=='edit' ) ? 'none' : 'block';
        $field_type = ($this->type=='edit' ) ? 'hidden' : 'password';
		foreach($qryRes as $key => $fetchRes) 
		{
			$content .=	$this->checkBox(
				array("label"=>$fetchRes['title']." : ",
					"name"=>'actions['.$fetchRes['pagenm'].'][]',
					"class"=>"radioBtn-bg chk_".$fetchRes['pagenm']."_".$key." chk_group",
					"value"=>(isset($this->{$fetchRes['pagenm']}) ? $this->{$fetchRes['pagenm']} : ''),
					"values"=>array_intersect($fetchAction,$fetchRes['page_action_id']),
					"extraAtt"=>"data-page='".$fetchRes['pagenm']."' data-page_id='".$key."' "
				)
			);
		}

		$replace_array = array(
			"%USERNAME%" => $this->uName,
			"%EMAIL%" => $this->uEmail,
			"%MODULES_LIST%" => $content,
			"%STATIC_A%" => $status_a,
			"%STATIC_D%" => $status_d,
			"%EDIT_PASS%"=> $edit_frm,
			"%FIELD%" => $field_type,
			"%TYPE%" => $this->type,
			"%ID%" => $this->id,
			"%NOTE%" => (($this->type=='edit') ? '<p style="color: #a94442;">*Please left this filed blank if you don\'t wants to change password</p>' : '')
		);

		$main_content = str_replace(array_keys($replace_array), array_values($replace_array), $main_content);
		return $main_content;
	}

	public function dataGrid() 
	{
		$sWhere = $content = $operation = $whereCond = $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		$chr = str_replace(array('_', '%'), array('\_', '\%'),$chr );


		$aWhere = array('s', $this->adminUserId, 't');
		if(!empty($chr)) 
		{
			$sWhere = " AND (uName LIKE ? OR LOWER(uEmail) LIKE ?)";
			$aWhere[] = "%$chr%"; $aWhere[] = "%".strtolower($chr)."%";
		}

		if(isset($sort))
			$sorting = $sort.' '. $order;
		else
			$sorting = 'id DESC';

		$totalRow = $this->db->pdoQuery("SELECT COUNT(id) AS totalRow FROM $this->table WHERE adminType <> ? AND id <> ? AND isActive <> ? $sWhere", $aWhere)->result();
		$totalRow = $totalRow['totalRow'];

		$qrySel = $this->db->pdoQuery("
			SELECT * FROM $this->table WHERE adminType <> ? AND id <> ? AND isActive <> ? $sWhere ORDER BY $sorting LIMIT $offset, $rows
		", $aWhere)->results();

		foreach($qrySel as $fetchRes) 
		{
			$status = ($fetchRes['isActive']=="a") ? "checked" : "";
			$switch  =(in_array('status',$this->Permission))?$this->toggel_switch(array("action"=>"ajax.".$this->module.".php?id=".$fetchRes['id']."","check"=>$status)):'';

			$operation ='';
			$operation .= (in_array('edit',$this->Permission))?$this->operation(array("href"=>"ajax.".$this->module.".php?action=edit&id=".$fetchRes['id']."", "extraAtt" =>"title = 'Edit'", "class"=>"btn default black btnEdit","value"=>'<i class="fa fa-edit"></i>')):'';

			$operation .=(in_array('view',$this->Permission))?$this->operation(array("href"=>"ajax.".$this->module.".php?action=view&id=".$fetchRes['id']."","class"=>"btn btn-success btn-viewbtn", "extraAtt" =>"title= 'View'", "value"=>'<i class="fa fa-laptop"></i>')):'';
			$operation .=(in_array('view',$this->Permission))?$this->operation(array("href"=>"ajax.".$this->module.".php?action=view_activity&id=".$fetchRes['id']."","class"=>"btn btn-warning btn-viewbtn", "extraAtt" =>"title = 'View Subadmin Activities'", "value"=>'<i class="fa fa-eye"></i>')):'';
			$operation .=(in_array('delete',$this->Permission))?$this->operation(array("href"=>"ajax.".$this->module.".php?action=delete&id=".$fetchRes['id']."","class"=>"btn default  red btn-delete", "extraAtt" =>"title= 'Delete'", "value"=>'<i class="fa fa-trash-o"></i>')):'';



			$final_array =  array($fetchRes["id"], 
							$fetchRes["uName"], 
							$fetchRes["uEmail"], 
							date(DATE_FORMAT_ADMIN, strtotime($fetchRes["created_date"])));

			if(in_array('status',$this->Permission)) 
			{
				$final_array =  array_merge($final_array, array($switch));
			}
			if(in_array('edit',$this->Permission) || in_array('delete',$this->Permission) || in_array('view',$this->Permission)) 
			{
				$final_array =  array_merge($final_array, array($operation));
			}
			$row_data[] = $final_array;
		}
		$result["sEcho"] = $sEcho;
		$result["iTotalRecords"] = (int)$totalRow;
		$result["iTotalDisplayRecords"] = (int)$totalRow;
		$result["aaData"] = $row_data;
		return $result;
	}

	public function activity_datagrid() 
	{
		$content = $operation = $whereCond = $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		if(isset($sort))
			$sorting = $sort.' '. $order;
		else
			$sorting = 'a.id DESC';
		$totalRow = $this->db->pdoQuery("
			SELECT a.id
			FROM tbl_admin_activity AS a
			LEFT JOIN tbl_subadmin_action AS sa ON (sa.id=a.activity_type)
			LEFT JOIN tbl_adminrole AS ar ON (ar.id=a.page_id)
			LEFT JOIN tbl_admin AS a1 ON (a1.id=a.admin_id)
			WHERE a.admin_id='".$this->id."'
			")->affectedRows();

		$qryRes = $this->db->pdoQuery("
			SELECT a1.uName, sa.title, sa.constant, ar.title AS page_title, ar.pagenm, ar.table_name, ar.table_field, ar.table_primary_field, a.created_date, a.entity_id, a.entity_action,a.id
			FROM tbl_admin_activity AS a
			LEFT JOIN tbl_subadmin_action AS sa ON (sa.id=a.activity_type)
			LEFT JOIN tbl_adminrole AS ar ON (ar.id=a.page_id)
			LEFT JOIN tbl_admin AS a1 ON (a1.id=a.admin_id)
			WHERE a.admin_id='".$this->id."'
			ORDER BY $sorting
			LIMIT $offset, $rows
		")->results();

		foreach($qryRes as $key => $fetchRes) 
		{
			$message = 'Record ';
			if(!empty($fetchRes['table_name']) && !empty($fetchRes['table_field']) && $fetchRes['pagenm']!='sitesetting-sd')
			{
				$primary_key = '';
				if(!empty($primary_key)) 
				{ 
					$id = $primary_key; 
				}
				else
				{ 
					$id = $fetchRes['table_primary_field']; 
				}
				if ($fetchRes['pagenm']=='users-sd' && $fetchRes['constant'] == 'export') 
				{
					$message = 'Records ';
				} 
				else 
				{
					$message = 'Record '.getTableValue($fetchRes['table_name'], $fetchRes['table_field'], array($id=>$fetchRes['entity_id']))." ";
				}
			}

			$constant = $fetchRes['constant'];
			$fetchRes['constant'] = ($fetchRes['constant']=='status')?(($fetchRes['entity_action']=='a')?'activate':'deactivate'):$fetchRes['constant'];
			$message .= $fetchRes['constant'].'ed '.(($fetchRes['constant']=='delete' || $fetchRes['constant']=='view' || $constant=='status')?'from':'to').' ';
			$message .= $fetchRes['page_title'].' module';
			$message = str_replace(array('deleteed','activateed','deactivateed'), array('deleted','activated','deactivated'), $message);
			$row_data[] = array($fetchRes['id'],$message,date('d-m-Y H:i:s' ,strtotime($fetchRes['created_date'])));
		}
		$result["sEcho"] = $sEcho;
		$result["iTotalRecords"] = (int)$totalRow;
		$result["iTotalDisplayRecords"] = (int)$totalRow;
		$result["aaData"] = $row_data;
		return $result;
	}

	public function getPageContent()
	{
		$final_result = NULL;
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".$this->module.".skd");
		$main_content->breadcrumb = $this->getBreadcrumb();
		$main_content->getForm = $this->getForm();
		$final_result = $main_content->compile();
		return $final_result;
	}

	public function toggel_switch($text) 
	{
        $text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
        $text['check'] = isset($text['check']) ? $text['check'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . 'switch-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%");
        $fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function operation($text) 
    {

        $text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['title'] = isset($text['title']) ? $text['title'] :'';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . '/operation-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%HREF%", "%CLASS%", "%VALUE%", "%EXTRA%","%TITLE%");
        $fields_replace = array($text['href'], $text['class'], $text['value'], $text['extraAtt'], $text['title']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function displaybox($text) 
    {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . '/displaybox.skd');
        $main_content = $main_content->compile();
        $fields = array("%LABEL%", "%CLASS%", "%VALUE%");
        $fields_replace = array($text['label'], $text['class'], $text['value']);
        return str_replace($fields, $fields_replace, $main_content);
    }
}