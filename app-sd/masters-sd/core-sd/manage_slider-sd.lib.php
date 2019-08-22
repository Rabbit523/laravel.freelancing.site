<?php
class Slider extends Home {
	public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
		global $db;
		$this->db = $db;
		$this->id = $id;
		$this->module = $module;
		$this->table = 'tbl_slider';

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
		$static_a = ($this->isActive == 'y' ? 'checked' : '');
		$static_d = ($this->isActive != 'y' ? 'checked' : '');
		$languages = $this->db->pdoQuery("select * from tbl_language where isActive = 'y' ")->results();
		$l_content = '';
		foreach ($languages as $key => $value) {
			$l_content .= '<option value="'.$value['id'].'" > '.$value['language'].' </option> ';
		}
		$replace_array = array(
			"%IMAGE_FILE_CHECKED%" => (($this->type=="add") ? "checked='checked'" : (($this->type=="edit" && $this->slider_type=="i") ? "checked='checked'" : '')),
			"%VIDEO_FILE_CHECKED%" => (($this->type=="add") ? "" : (($this->type=="edit" && $this->slider_type=="v") ? "checked='checked'" : '')),
			"%IMAGE_DIV%" => (($this->type=="add") ? "block'" : (($this->type=="edit" && $this->slider_type=="i") ? "block'" : 'none')),
			"%IMAGE_SHOW_CLASS%" => (($this->type=="edit" && $this->slider_type=="i") ? "block" : "none"),
			"%VIDEO_DIV%" => (($this->type=="add") ? "none" : (($this->type=="edit" && $this->slider_type=="v") ? "block" : 'none')),
			"%STATIC_A%" => $static_a,
			"%STATIC_D%" => $static_d,
			"%TYPE%" => $this->type,
			"%ID%" => $this->id,
			"%TITLE%" => $this->type=="add" ? "" : $this->title,
			"%CONTENT%" => $this->type=="add" ? "" : $this->content,
			"%OLD_IMAGE%" => (($this->slider_type=="i") ? $this->file_name: ''),
			"%OLD_IMAGE_SRC%" => (($this->slider_type=="i") ? SITE_SLIDER_IMAGE.$this->file_name : ''),
			"%OLD_VIDEO%" => (($this->type=='edit') ? '<div style="display: '.(($this->slider_type=="v") ? "block" : 'none').'"><video width="320" height="240" controls><source src="'.SITE_SLIDER_IMAGE.$this->file_name.'" type="video/mp4">Your browser does not support the video tag.</video></div>' : ''),
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
			$status = ($fetchRes['isActive'] == "y") ? "checked" : "";

			$switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';

			$operation = '';
			$operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default  black btnEdit", "title" => "Edit","value" => '<i class="fa fa-edit"></i>')) : '';

			$operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "", "class" => "btn default red btn-delete", "title" =>"Delete","value" => '<i class="fa fa-trash-o"></i>')) : '';

			if($fetchRes['slider_type']=='i') {
				$slider_preview = '<img src="'.SITE_SLIDER.$fetchRes["file_name"].'" alt="No inage available"  style="width: 250px;height: auto;" />';
			} else {
				$slider_preview = '<video width="300" controls><source src="'.SITE_SLIDER_IMAGE.$fetchRes["file_name"].'" type="video/mp4">Your browser does not support the video tag.</video>';
			}

			$final_array = array(
				filtering($fetchRes["id"]),
				$slider_preview,
				filtering($fetchRes["title"]),
				filtering($fetchRes["content"]),
				
			);
			if (in_array('status', $this->Permission)) {
				$final_array = array_merge($final_array, array($switch));
			}
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
		$objPost['slider_type'] = $slider_type;
		$objPost['isActive'] = isset($is_active) ? $is_active : 'n';
		
		if($type=='add' && $objPost['slider_type']=='i' && empty($slider_image)) {
			$response['error'] = 'Please select image to upload'; $response['success'] = '';
			echo json_encode($response); exit;
		} else if(($type=='add') && $objPost['slider_type']=='v' && (empty($_FILES['slider_video']['name']) || !empty($_FILES['slider_video']['error']))) {
			$response['error'] = 'Please select video to upload'; $response['success'] = '';
			echo json_encode($response); exit;
		} else if($type=='edit' && $objPost['slider_type']=='v' && !empty($slider_image)) {
			$response['error'] = 'Please select video to upload'; $response['success'] = '';
			echo json_encode($response); exit;
		} else if(($type=='add' || $type=='edit') && $objPost['slider_type']=='i' && !empty($slider_image)) {
			$objPost['file_name'] = $slider_image;
		} else if($type=='add' && $objPost['slider_type']=='v' && !empty($_FILES['slider_video']['name']) && empty($_FILES['slider_video']['error'])) {
			$file_name = uploadFile($_FILES['slider_video'], DIR_SLIDER_IMAGE, SITE_SLIDER_IMAGE);
			$objPost['file_name'] = $file_name['file_name'];
		} else if($type=='edit' && $objPost['slider_type']=='v' && !empty($_FILES['slider_video']['name']) && empty($_FILES['slider_video']['error'])) {
			$file_name = uploadFile($_FILES['slider_video'], DIR_SLIDER_IMAGE, SITE_SLIDER_IMAGE, $this->table, 'file_name', 'id', $id);
			$objPost['file_name'] = $file_name['file_name'];
		}

		if($type == 'edit' && $id > 0) {
			if(in_array('edit', $Permission)) {
				$this->db->update($this->table, (array)$objPost, array("id" => $id));
				$activity_array = array("id"=>$id, "module"=>$this->module, "activity"=>'edit');
				add_admin_activity($activity_array);
				$response['status'] = true;
				$response['success'] = "Slider has been updated successfully";
			} else {
				$response['error'] = "You don't have permission.";
			}
		} else {
			if (in_array('add', $Permission)) {
				if(!empty($objPost['file_name'])) {

					if($slider_type == 'v'){
						$rowCount = $this->db->pdoQuery("Select id from tbl_slider where slider_type ='v' ")->affectedRows();
						if($rowCount >= 1){
							$response['error'] = "Sorry you cann't upload more than one video files.";
							echo json_encode($response); exit;
						}
					}
					$objPost['created_date'] = date("Y-m-d H:i:s");
					$id = $this->db->insert($this->table, (array)$objPost)->getLastInsertId();
					$activity_array = array("id"=>$id, "module"=>$this->module, "activity"=>'add');
					add_admin_activity($activity_array);
					$response['status'] = true;
				} else {
					$response['error'] = "Please select file to upload.";
				}
			} else {
				$response['error'] = "You don't have permission.";
			}
		}
		echo json_encode($response); exit;
	}
}