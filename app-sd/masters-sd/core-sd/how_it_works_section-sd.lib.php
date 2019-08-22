<?php
	class HowItWorks extends Home {
		public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
			global $db;
			$this->db = $db;
			$this->id = $id;
			$this->module = $module;
			$this->table = 'tbl_how_it_work';
			parent::__construct();

			$this->type = ($this->id > 0 ? 'edit' : 'add');
			$this->searchArray = $searchArray;
			parent::__construct();
			if ($this->id > 0) {
				$qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
				foreach ($qrySel as $key => $value) {
					$this->$key = $value;
				}
			} else {
				$this->file_name = '';
				$this->slider_type = 'i';
				$this->isActive = 'y';
			}
			switch ($type) {
				case 'add' : {
						$this->data['content'] = $this->getForm();
						break;
					}
				case 'edit' : {
						$this->data['content'] = $this->getForm();
						break;
					}
				case 'view' : {
						$this->data['content'] = $this->viewForm();
						break;
					}
				case 'delete' : {
						$this->data['content'] = json_encode($this->dataGrid());
						break;
					}
				case 'datagrid' : {
						$this->data['content'] = json_encode($this->dataGrid());
						break;
					}
			}
		}

		public function getPageContentTemp() {
			$final_content = NULL;
			$main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
			$main_content->breadcrumb = $this->getBreadcrumb();
			$main_content = $main_content->compile();

			$content_detail = $this->db->pdoQuery("select pageDesc,pId from tbl_content where page_slug='how-it-works' ")->result();

			$fields = array(
				"%CONTENT%" => $content_detail['pageDesc'],
				"%ID%" => $content_detail['pId']
				);
	
			$final_content = str_replace(array_keys($fields) ,array_values($fields), $main_content);
			return $final_content;
		}

		public function contentSubmitTemp($data)
		{
			extract($data);
			$this->db->update("tbl_content",array("pageDesc"=>$content),array("page_slug"=>'how-it-works'));

			$activity_array = array("id" => $id, "module" => $this->module, "activity" => 'edit');
            add_admin_activity($activity_array);

			$_SESSION["toastr_message"]= $toastr_message = disMessage(array('from' => 'admin', 'type' => 'suc', 'var' => 'Content has been updated successfully'));
            redirectPage(SITE_ADM_MOD . $this->module);
		}
		public function viewForm() {
		$content =
				$this->displayBox(array("label" => "Page Title&nbsp;:", "value" => filtering($this->pageTitle))) .
				$this->displayBox(array("label" => "Met Keyword&nbsp;:", "value" => filtering($this->metaKeyword))) .
				$this->displayBox(array("label" => "Meta Description&nbsp;:", "value" => filtering($this->metaDesc))) .
				$this->displayBox(array("label" => "Page Description&nbsp;:", "value" => filtering($this->pageDesc, 'output', 'text'))).
				$this->displayBox(array("label" => "Page Slug&nbsp;:", "value" => filtering($this->page_slug)));
		return $content;
	}

	public function getForm() {
		$content = '';
		$main_content = (new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd"))->compile();
		
		$languages = $this->db->pdoQuery("select * from tbl_language where isActive = 'y' ")->results();
		$l_content = '';
		foreach ($languages as $key => $value) {
			$l_content .= '<option value="'.$value['id'].'" > '.$value['language'].' </option> ';
		}

		$replace_array = array(
			"%TITLE%" => $this->title,
			"%CONTENT%" => $this->content,
			"%ID%" => $this->id,
			"%IMAGE_SHOW_CLASS%" => (($this->type=="edit") ? "block" : "none"),
			"%TYPE%" => $this->type,
			"%ID%" => $this->id,
			"%OLD_IMAGE%" => $this->icon,
			"%OLD_IMAGE_SRC%" => SITE_HOWIT.$this->icon,
			"%OLD_APP_IMAGE%" => $this->app_icon,
			"%APP_OLD_IMAGE_SRC%" => SITE_HOWIT.$this->app_icon,
			"%LANGUAGE%" => $l_content
		);

		$content = str_replace(array_keys($replace_array), array_values($replace_array), $main_content);
		return $content;
		return sanitize_output($content);
	}

	public function dataGrid() {
		$slider_preview = $content = $operation = $whereCond = $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		$chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
		$whereCond = array();
		if (!empty($chr)) {
			$whereCond = array("image_name LIKE" => "%$chr%");
		}

		if (isset($sort))
			$sorting = $sort . ' ' . $order;
		else
			$sorting = 'id DESC';

		$totalRow = $this->db->count($this->table, $whereCond);

		$qrySel = $this->db->select($this->table, "*", $whereCond, " ORDER BY $sorting limit $offset , $rows")->results();
		foreach ($qrySel as $fetchRes) {

			$operation = '';
			$operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit", "title" => "Edit","value" => '<i class="fa fa-edit"></i>')) : '';
			
			$iconCont = '<img src="'.SITE_HOWIT.$fetchRes["icon"].'" alt="No inage available"  style="width: 150px;height: auto;" />';
			$appIconCont = '<img src="'.SITE_HOWIT.$fetchRes["app_icon"].'" alt="No inage available"  style="width: 150px;height: auto;" />';
			$final_array = array(
				filtering($fetchRes["id"]),
				$iconCont,
				$appIconCont,
				$fetchRes['title'],
				$fetchRes['content'],
			);
			if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission)) {
				$final_array = array_merge($final_array, array($operation));
			}
			$row_data[] = $final_array;
		}
		$result["sEcho"] = $sEcho;
		$result["iTotalRecords"] = (int) $totalRow;
		$result["iTotalDisplayRecords"] = (int) $totalRow;
		$result["aaData"] = $row_data;
		return $result;
	}

	public function displaybox($text) {
		$text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
		$text['value'] = isset($text['value']) ? $text['value'] : '';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
		$text['class'] = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
		$text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
		$text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

		$main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/displaybox.skd');
		$main_content = $main_content->compile();
		$fields = array("%LABEL%", "%CLASS%", "%VALUE%");
		$fields_replace = array($text['label'], $text['class'], $text['value']);
		return str_replace($fields, $fields_replace, $main_content);
	}

	public function getPageContent() {
		$final_result = NULL;
		$main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
		$main_content->breadcrumb = $this->getBreadcrumb();
		$final_result = $main_content->compile();
		return $final_result;
	}

	public function contentSubmit($data, $files, $Permission) {

		$response = array('status'=>false, 'error'=>'Please fill all required values to submit form', 'success'=>'Slider has been added successfully');
		extract($data);
		

		$objPost = [];
		if (empty($language)) {
			$objPost['title'] = $title;
			$objPost['content'] = $content;
		} else {
			$titleFld = "title_".$language;
			$contentFld = "content_".$language;
			$objPost[$titleFld] = $title;
			$objPost[$contentFld] = $content;
		}	
		//print_r($objPost);exit;
		if($type=='edit') {
			if(!empty($_FILES['testiImage']['name']) && empty($_FILES['testiImage']['error'])){
				$file_name = uploadFile($_FILES['testiImage'], DIR_HOWIT, SITE_UPD);			
				$objPost['icon'] = $file_name['file_name'];
			}
			if(!empty($_FILES['appImage']['name']) && empty($_FILES['appImage']['error'])){
				$app_file_name = uploadFile($_FILES['appImage'], DIR_HOWIT, SITE_UPD);
				$objPost['app_icon'] = $app_file_name['file_name'];
			}
		}
		if($type == 'edit' && $id > 0) {
			if(in_array('edit', $Permission)) {
				$this->db->update($this->table, (array)$objPost, array("id" => $id));
				$activity_array = array("id"=>$id, "module"=>$this->module, "activity"=>'edit');
				add_admin_activity($activity_array);
				$response['status'] = true;
				$response['success'] = "Record updated successfully";
			} else {
				$response['error'] = "You don't have permission.";
			}
		}
		echo json_encode($response); exit;
	}
	}