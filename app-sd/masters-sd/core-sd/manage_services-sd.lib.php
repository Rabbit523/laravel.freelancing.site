<?php
class Services extends Home 
{

	public $page_name;
	public $page_title;
	public $meta_keyword;
	public $meta_desc;
	public $page_desc;
	public $isActive;
	public $data = array();

	public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') 
	{
		global $db, $fields, $sessCataId;
		$this->db = $db;
		$this->data['id'] = $this->id = $id;
		$this->fields = $fields;
		$this->module = $module;
		$this->table = 'tbl_services';

		$this->type = ($this->id > 0 ? 'edit' : 'add');
		$this->searchArray = $searchArray;
		parent::__construct();
		if ($this->id > 0) 
		{
			$qrySel = $this->db->pdoQuery("Select sa.addonTitle,sa.addonDayRequired,sa.addonPrice,sa.addonDesc,s.*,sc.category_name,ssc.subcategory_name,CONCAT(u.firstName,' ',u.lastName) As username from tbl_services As s LEFT JOIN tbl_category As sc ON s.servicesCategory = sc.id
				LEFT JOIN tbl_subcategory As ssc ON s.servicesSubCategory = ssc.id
				LEFT JOIN tbl_users As u ON s.freelanserId = u.id
				LEFT JOIN tbl_services_addon As sa ON sa.services_id = s.id
				where s.id='".$id."'
				")->result();
			$fetchRes = $qrySel;
			$this->data['serviceTitle'] = $this->serviceTitle = $fetchRes['serviceTitle'];
			$this->data['servicesCategory'] = $this->servicesCategory = $fetchRes['servicesCategory'];
			$this->data['servicesSubCategory'] = $this->servicesSubCategory = $fetchRes['servicesSubCategory'];
			$this->data['description'] = $this->description = $fetchRes['description'];
			$this->data['servicesPostDate'] = $this->servicesPostDate = $fetchRes['servicesPostDate'];
			$this->data['services_image'] = $this->services_image = $fetchRes['services_image'];
			$this->data['noDayDelivery'] = $this->noDayDelivery = $fetchRes['noDayDelivery'];
			$this->data['servicesPrice'] = $this->servicesPrice = $fetchRes['servicesPrice'];            
			$this->data['serviceAdonTitle'] = $this->serviceAdonTitle = $fetchRes['addonTitle'];
			$this->data['serviceAdondayRequired'] = $this->serviceAdondayRequired = $fetchRes['addonDayRequired'];
			$this->data['serviceAdonPrice'] = $this->serviceAdonPrice = $fetchRes['addonPrice'];
			$this->data['serviceAdonDesc'] = $this->serviceAdonDesc = $fetchRes['addonDesc'];
			$this->data['requiredDetails'] = $this->requiredDetails = $fetchRes['requiredDetails'];
			$this->data['featured'] = $this->featured = $fetchRes['featured'];
			$this->data['freelanserId'] = $this->freelanserId = $fetchRes['freelanserId'];
			$this->data['isApproved'] = $this->isApproved = $fetchRes['isApproved'];
			$this->data['reportStatus'] = $this->reportStatus = $fetchRes['reportStatus'];
			$this->data['category_name'] = $this->category_name = $fetchRes['category_name'];
			$this->data['subcategory_name'] = $this->subcategory_name = $fetchRes['subcategory_name'];
			$this->data['username'] = $this->username = $fetchRes['username'];
			$this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
		} 
		else 
		{
			$this->data['serviceTitle'] = $this->serviceTitle = '';
			$this->data['servicesCategory'] = $this->servicesCategory = '';
			$this->data['servicesSubCategory'] = $this->servicesSubCategory = '';
			$this->data['description'] = $this->description = '';
			$this->data['servicesPostDate'] = $this->servicesPostDate = '';
			$this->data['services_image'] = $this->services_image = '';
			$this->data['noDayDelivery'] = $this->noDayDelivery = '';
			$this->data['servicesPrice'] = $this->servicesPrice = '';
			$this->data['serviceAdonTitle'] = $this->serviceAdonTitle = '';
			$this->data['serviceAdondayRequired'] = $this->serviceAdondayRequired = '';
			$this->data['serviceAdonPrice'] = $this->serviceAdonPrice = '';
			$this->data['serviceAdonDesc'] = $this->serviceAdonDesc = '';
			$this->data['requiredDetails'] = $this->requiredDetails = '';
			$this->data['featured'] = $this->featured = '';
			$this->data['freelanserId'] = $this->freelanserId = '';
			$this->data['isApproved'] = $this->isApproved = '';
			$this->data['reportStatus'] = $this->reportStatus = '';
			$this->data['category_name'] = $this->category_name = '';
			$this->data['subcategory_name'] = $this->subcategory_name = '';
			$this->data['username'] = $this->username = '';
			$this->data['isActive'] = $this->isActive = 'y';
		}
		switch ($type) 
		{
			case 'add' : 
			{
				$this->data['content'] = $this->getForm();
				break;
			}
			case 'edit' : 
			{
				$this->data['content'] = $this->getForm();
				break;
			}
			case 'view' : 
			{
				$this->data['content'] = $this->viewForm();
				break;
			}
			case 'undo' : {
					$this->data['content'] = json_encode($this->dataGrid());
					break;
			}
			case 'delete' : 
			{
					
				$this->data['content'] = json_encode($this->dataGrid());
				break;
			}
			case 'datagrid' : 
			{
				$this->data['content'] = json_encode($this->dataGrid());
				break;
			}
		}
	}

	public function viewForm() 
	{
	  

		if($this->freelanserId != 0)
		{
			$user_detail = $this->db->pdoQuery("Select * from tbl_users where id='".$this->freelanserId."' ")->result();
			$freelanser_img = "<img src='".SITE_USER_PROFILE.$user_detail['profileImg']."' width='50' height='50'>";
			$review = $this->db->pdoQuery("Select * from tbl_reviews where id='".$this->freelanserId."' ")->result();

			$user_data = 
						$this->displayBox(array("label" => "Freelancer Name &nbsp;:", "value" => $user_detail['userName'] )).
						$this->displayBox(array("label" => "Freelancer Profile&nbsp;:", "value" => $freelanser_img )).
						$this->displayBox(array("label" => "Star Rating &nbsp;:", "value" => $review['startratings'] )).
						$this->displayBox(array("label" => "Freelancer Location&nbsp;:", "value" => $user_detail['location'] ));
		}
		else
		{
			$user_data = $this->displayBox(array("label" => "Freelancer Name &nbsp;:", "value" => 'Admin' ));
		}

		$addon_query = $this->db->pdoQuery("select * from tbl_services_addon where services_id='".$this->id."' ");
		$addon_detail = $addon_query->results();
		if($addon_query->affectedRows()>0)
		{
			$addon_data = "";
			$addon_data .= "<div class='well'><center><h2><strong>Services Addon Detail</strong></h2></center>";
			foreach ($addon_detail as $value) 
			{
				$addon_data .=   

					$this->displayBox(array("label" => "Addon Title &nbsp;:", "value" => $value['addonTitle'] )).
					$this->displayBox(array("label" => "Addon Day Required &nbsp;:", "value" => $value['addonDayRequired'] )).
					$this->displayBox(array("label" => "Addon Price &nbsp;:", "value" => CURRENCY_SYMBOL.$value['addonPrice'] )).
					$this->displayBox(array("label" => "Addon Description &nbsp;:", "value" => $value['addonDesc']  ));
			}
			$addon_data .="</div>";
		}
		else
		{
			$addon_data = '';
		}
			$img = $this->db->pdoQuery("Select * from tbl_services_files where id='".$this->servicesId."' ")->result();
			$services_img =  "<img src='".$img['fileName']."' width='100' height='100'>";

		$content = 
			$this->displayBox(array("label" => "Services Title &nbsp;:", "value" => filtering($this->serviceTitle))).
			$this->displayBox(array("label" => "Services Image &nbsp;:", "value" => $services_img)).
			$this->displayBox(array("label" => "Services Required Detail  &nbsp;:", "value" => filtering($this->requiredDetails))).
			$this->displayBox(array("label" => "Services Category &nbsp;:", "value" => filtering($this->category_name))).
			$this->displayBox(array("label" => "Services Subcategory &nbsp;:", "value" => filtering($this->subcategory_name))).
			$this->displayBox(array("label" => "Services Description &nbsp;:", "value" => filtering($this->description))).
			$this->displayBox(array("label" => "Services Posted date &nbsp;:", "value" => filtering(date('d-m-Y H:i:s',strtotime($this->servicesPostDate))))).
			$this->displayBox(array("label" => "No of Day of Delivery &nbsp;:", "value" => filtering($this->noDayDelivery))).
			$this->displayBox(array("label" => "Services Price &nbsp;:", "value" => CURRENCY_SYMBOL.$this->servicesPrice)).
			"<div class='well'><h2><center><strong>Freelancer Detail</strong></h2></center>".
			$user_data. "</div>".           
			$addon_data.                      
			$this->displayBox(array("label" => "Featured &nbsp;:", "value" => ($this->featured == 'y') ? 'Yes':'No'))
		   
		;                   
		return $content;
	}

	public function getForm() 
	{


		$main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
		$main_content = $main_content->compile();
		/*$hideFrmSearch_y = ($this->hideFrmSearch == 'y' ? 'checked' : '');
		$hideFrmSearch_n = ($this->hideFrmSearch != 'y' ? 'checked' : '');*/

		$featured_y = ($this->featured == 'y' ? 'checked' : '');
		$featured_n = ($this->featured == 'n' ? 'checked' : '');

	 	$static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');


		$category = $subcategory = $skills = '';

		/*category list*/
		$category_detail = $this->db->pdoQuery("select * from tbl_category where (category_type='j' OR category_type='b') and isActive='y' and isDelete='n'")->results();
		foreach ($category_detail as $value) {
			$cat_sel = ($this->servicesCategory == $value['id']) ? 'selected' : '';
			$category .="<option value='".$value['id']."' ".$cat_sel.">".$value['category_name']."</option>";
		}
		/*subcategory list*/
		$subcategory_detail = $this->db->pdoQuery("select * from tbl_subcategory where maincat_id='".$this->servicesCategory."' and isActive='y' and isDelete='n' ")->results();
		foreach ($subcategory_detail as $value1) 
		{
			$sub_cat_sel = ($this->servicesSubCategory == $value1['id']) ? 'selected' : '';
			$subcategory .="<option value='".$value1['id']."' ".$sub_cat_sel.">".$value1['subcategory_name']."</option>";
		}

		$add_on_section = ($this->type == 'edit') ? $this->addOnSection() : '';

		$image = SITE_SERVICES_FILE.$this->services_image;
		$services_img = ($this->services_image!='') ? "<img src='".$image."' width='100' height='100'>" : '';

		/*skills list*/

		$question_list = '';
		$fields = array(
			"%BYDEFAULT%" => ($this->type == 'add') ? 'checked': '',
			"%SERVICES_IMG%" => $services_img,
			"%OLD_IMG%" => ($this->services_image != '') ? $this->services_image : '',
			"%SERVICES_TITLE%" => filtering($this->serviceTitle),
			"%CATEGORY%" => $category,
			"%SUB_CATEGORY%" => $subcategory,
			"%DESC%" => $this->description,
			"%NO_DELIVERY%" => $this->noDayDelivery,
			"%SERVICES_PRICE%" => $this->servicesPrice,
			"%SERVICE_ADON_TITLE%" => $this->serviceAdonTitle,
			"%SERVICE_ADON_REQUIRED%" => $this->serviceAdondayRequired,
			"%SERVICE_ADON_PRICE%" => $this->serviceAdonPrice,
			"%SERVICE_ADON_DESC%" => $this->serviceAdonDesc,
			"%REQUIRED_DETAILS%" => $this->requiredDetails,
			"%ADDON_CONTENT%" => $add_on_section,
			"%FEATURED_Y%" => $featured_y,
			"%FEATURED_N%" => $featured_n,
			"%TYPE%" => filtering($this->type),
			"%ID%" => filtering($this->id, 'input', 'int'),
			"%IMG_LIST%" => $this->get_img_list($this->id),
			"%SERVICE_ADD_ON_CLASS%" => !empty($add_on_section) ? 'hide' : '',
			"%STATIC_A%"=> filtering($static_a),
            "%STATIC_D%"=> filtering($static_d),	

		);

		$content = str_replace(array_keys($fields), array_values($fields), $main_content);
		return sanitize_output($content);
	}

	public function get_img_list($id)
	{
		$img = "";
		$data = $this->db->pdoQuery("select * from tbl_services_files where servicesId=?",array($id))->results();
		foreach ($data as $value) 
		{
			$image = SITE_SERVICES_FILE.$value['fileName'];
			$img .= "<div class='col-md-3'><img src='".$image."' width='50' height='50'><a href='javascript:void(0)' class='delete_image' data-id='".$value['id']."'>Close</a></div>";
		}
		return $img;
	}
	public function addOnSection()
	{
		$data = $this->db->pdoQuery("Select * from tbl_services_addon where services_id='".$this->id."' ")->results();
		$loop_data = '';
		foreach ($data as $value) 
		{
			$main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/addOn_div-sd.skd");
			$main_content = $main_content->compile();

			$array = array(
				"%ADDONTITLE%" => $value['addonTitle'],
				"%ADDONDAYREQUIRED%" => $value['addonDayRequired'],
				"%ADDONPRICE%" => $value['addonPrice'],
				"%ADDONDESC%" => $value['addonDesc'],
				"%ADDONIDS%" => $value['id']
				);
			$loop_data .= str_replace(array_keys($array), array_replace($array), $main_content);
		}

		return $loop_data;
	}
	

	public function dataGrid() 
	{
		$content = $operation = $whereCond = $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		$chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
	   $whereCond = "1 = ?";
	   $wArray = array('1');
		if (!empty($chr)) 
		{
				$whereCond .= " AND (s.serviceTitle LIKE ?) ";
				$wArray[] = "%$chr%";
		}
		if (isset($sort))
			$sorting = $sort . ' ' . $order;
		else
			$sorting = 's.isApproved DESC,s.servicesPostDate DESC';
		if (isset($filterCategory) && $filterCategory != '' && $filterSubCategory == ''){
			if ($whereCond) {
				$whereCond .= " AND ";
			} else {
				$whereCond .= " WHERE ";
			}
			$whereCond .= " s.servicesCategory = '$filterCategory'";
		} elseif (isset($filterSubCategory) && $filterSubCategory != '' && $filterCategory != ''){
			if ($whereCond) {
				$whereCond .= " AND ";
			} else {
				$whereCond .= " WHERE ";
			}
			$whereCond .= " s.servicesSubCategory = '$filterSubCategory' AND s.servicesCategory = '$filterCategory'";
		} elseif (isset($ApprovalStatus) && $ApprovalStatus != ''){
			if ($whereCond) {
				$whereCond .= " AND ";
			} else {
				$whereCond .= " WHERE ";
			}
			$whereCond .= " s.isApproved = '$ApprovalStatus'";
		}
		else if (isset($filterSubCategory) && $filterSubCategory != ''){
			if ($whereCond) {
				$whereCond .= " AND ";
			} else {
				$whereCond .= " WHERE ";
			}
			$whereCond .= " s.servicesSubCategory = '$filterSubCategory'";
		}
		$qrySel = $this->db->pdoQuery("SELECT s.id,s.freelanserId,sc.category_name,s.isApproved,s.isDelete,s.isActive,s.serviceTitle,sc.category_name,s.noDayDelivery,CONCAT(u.firstName,' ',u.lastName) As username,s.id As serviceID FROM tbl_services As s
			LEFT JOIN tbl_category As sc ON sc.id = s.servicesCategory
			LEFT JOIN tbl_users As u ON u.id = s.freelanserId
			WHERE $whereCond ORDER BY $sorting LIMIT $offset , $rows",$wArray)->results();
		$totalRow = $this->db->pdoQuery("SELECT s.id,s.freelanserId,sc.category_name,s.isApproved,s.isDelete,s.isActive,s.serviceTitle,sc.category_name,s.noDayDelivery,CONCAT(u.firstName,' ',u.lastName) As username,s.id As serviceID FROM tbl_services As s
			LEFT JOIN tbl_category As sc ON sc.id = s.servicesCategory
			LEFT JOIN tbl_users As u ON u.id = s.freelanserId
			WHERE $whereCond ORDER BY $sorting ",$wArray)->affectedRows();
		 
		foreach ($qrySel as $fetchRes) 
		{
			$operation = "";
			$status = ($fetchRes['isActive'] == "y") ? "checked" : "";            
			$switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['serviceID'] . "", "check" => $status)) : '';

			$sStatus = checkDelete($fetchRes['serviceID'],$fetchRes['freelanserId']);
			// $delete_status = ($fetchRes['isDelete']=='y') ? "<span class='label  label-warning  label-large'>&nbsp;Job deleted</span>" : '';
			if($fetchRes['isApproved'] == 'p'){
				$operation = (in_array('status', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=approveStatus&id=" . $fetchRes['serviceID'] . "",  "extraAtt" => "title = 'Approve'","class" => "btn default black btn-approve", "value" => '<i class="fa fa-check"></i>', "title"=>"Approve")) : '' ;
				$operation .="<button type='button' class='btn default red reject' data-id='".$fetchRes['serviceID']."'><i class='fa fa-times' aria-hidden='true'></i></button>";
                                $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['serviceID'] . "", "extraAtt" => "title = 'Edit'", "class" => "btn default black btnEdit", "value" => '<i class="fa fa-edit"></i>')) : '';
				$operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['serviceID'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';

				$switch= "<label class='label label-info'>Pending</label>";
			}else if($fetchRes['isApproved'] == 'a'){
				$status = ($fetchRes['isActive'] == "y") ? "checked" : "";
				$switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['serviceID'] . "", "check" => $status)) : '';
				if($fetchRes['isDelete'] != 'y'){

					$operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" =>  "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['serviceID'] . "", "extraAtt" => "title = 'Delete' data-id='".$fetchRes['serviceID']."'", "class" => "btn default red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
					$operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['serviceID'] . "", "extraAtt" => "title = 'Edit'", "class" => "btn default black btnEdit", "value" => '<i class="fa fa-edit"></i>')) : '';
					$operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['serviceID'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';

				}

			}else if($fetchRes['isApproved'] == 'r') {
				if($fetchRes['isDelete'] != 'y'){
				  
				 $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" =>  "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['serviceID'] . "", "extraAtt" => "title = 'Delete' data-id='".$fetchRes['serviceID']."'", "class" => "btn default red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
				}
				$switch= "<label class='label label-danger'>Rejected</label>";
			}
			// if($fetchRes['isDelete'] == 'y'){
			// 	 // $operation .=(in_array('undo', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['serviceID'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-reply"></i>')) : '';
			// 	 $operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" =>  "ajax." . $this->module . ".php?action=perDelete&id=" . $fetchRes['serviceID'] . "", "extraAtt" => "title = 'Permanent Delete' data-id='".$fetchRes['serviceID']."'", "class" => "btn default red btn-perdelete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
			// 	// echo "<br>".$cnt;
			// }
		   /* if($jdelete == '1' && $fetchRes['isDelete']=='n'){
				$operation .=(in_array('delete', $this->Permission)) ?$this->operation(array("href" => "javascript:void(0);", "extraAtt" => "title = 'Delete' data-id='".$fetchRes['serviceID']."'", "class" => "btn default red jb_delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
			}*/

			// $operation .=(in_array('view', $this->Permission)) ?$this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['serviceID'] . "", "extraAtt" => "title = 'View'", "class" => "btn default blue btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';
			/*$operation .= (in_array('edit', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['serviceID'] . "", "extraAtt" => "title = 'Edit'", "class" => "btn default black btnEdit", "value" => '<i class="fa fa-edit"></i>')) : '';     */           
			
			$freelanserName = ($fetchRes['username'] == '') ? 'Admin' : $fetchRes['username'];           
			$final_array = array(
				$fetchRes['id'],
				$fetchRes['serviceTitle'].'<br>'.$delete_status,
				filtering($fetchRes["category_name"]),
				ucfirst($freelanserName)
			);
			if (in_array('status', $this->Permission)) 
			{
				$final_array = array_merge($final_array, array($switch));
			}
			if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission)) 
			{
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

	public function toggel_switch($text) 
	{
		$disabledSwitch=NULL;
		$text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
		$text['check'] = isset($text['check']) ? $text['check'] : '';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
		$text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
		$text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
		if(!empty($text['deletecat']))
		{
			 if(empty($text['check']) && $text['deletecat']=='y')
			 {
								$disabledSwitch='disabled';
			 }
		}
		if(!empty($text['homecat']))
		{
			 if(empty($text['check']) && $text['homecat']=='y')
			 {
								$disabledSwitch='disabled';
			 }
		}

		$main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/switch-sd.skd');
		$main_content = $main_content->compile();
		$fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%","%DISABLECAT%");
		$fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check'],$disabledSwitch);
		return str_replace($fields, $fields_replace, $main_content);
	}

	public function operation($text) 
	{

		$text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
		$text['value'] = isset($text['value']) ? $text['value'] : '';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
		$text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
		$text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
		$main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/operation-sd.skd');
		$main_content = $main_content->compile();
		$fields = array("%HREF%", "%CLASS%", "%VALUE%", "%EXTRA%");
		$fields_replace = array($text['href'], $text['class'], $text['value'], $text['extraAtt']);
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
		$fields = array(
				"%CATEGORY_OPTIONS%" => $this->getCategory(),
				"%SUB_CAT%" => $this->getSubCategory()
		);
		$final_result = str_replace(array_keys($fields), array_values($fields), $final_result);
		return $final_result;
	}
	public function getCategory()
	{
		$query = $this->db->pdoQuery("select * from tbl_category where isActive='y' and isDelete='n' ")->results();
		$category = "";
		foreach ($query as $value) 
		{
			$category.="<option value='".$value['id']."'>".$value['category_name']."</option>";
		}
		return $category;
	}
	public function getSubCategory()
	{
		$query = $this->db->pdoQuery("select * from tbl_subcategory where isActive='y' and isDelete='n' ")->results();
		$category = "";
		foreach ($query as $value) 
		{
			$category.="<option value='".$value['id']."'>".$value['subcategory_name']."</option>";
		}
		return $category;
	}
	public function contentSubmit($data,$Permission){
		//printr($_FILES);exit;
		
		

		$response = array();
		extract($data);
		$objPost = new stdClass();
		
		$objPost->serviceTitle = isset($data['servicesTitle']) ? $data['servicesTitle'] : "";
		$objPost->servicesCategory = isset($data['servicesCategory']) ? $data['servicesCategory'] : "";
		$objPost->servicesSubCategory = isset($data['servicesSubCategory']) ? $data['servicesSubCategory'] : "";
		$objPost->description = isset($data['description']) ? $data['description'] : "";
		$objPost->noDayDelivery = isset($data['noDayDelivery']) ? $data['noDayDelivery'] : "";
		$objPost->servicesPrice = isset($data['servicesPrice']) ? $data['servicesPrice'] : "";
		$objPost->requiredDetails = isset($data['requiredDetails']) ? $data['requiredDetails'] : "";
		$objPost->featured = isset($data['featured']) ? $data['featured'] : "";
		$objPost->requiredDetails = isset($data['requiredDetails']) ? $data['requiredDetails'] : "";
		$objPost->requiredDetails = isset($data['requiredDetails']) ? $data['requiredDetails'] : "";
		$objPost->isActive = isset($isActive) ? $isActive : 'n';

		
		$this->db->update($this->table, (array)$objPost, array("id" => $id));
		if (!empty($serviceAdonTitle)) {
			foreach ($serviceAdonTitle as $key => $value) {
				$addonTitle = $value;
				$addonDayRequired = isset($serviceAdondayRequired[$key]) ? $serviceAdondayRequired[$key] : 0;
				$addonPrice = isset($serviceAdonPrice[$key]) ? $serviceAdonPrice[$key] : 0;
				$addonDesc = isset($serviceAdonDesc[$key]) ? $serviceAdonDesc[$key] : 0;
				
				$insertAddOnArr = [
					'addonTitle' => $addonTitle,
					'addonDayRequired' => $addonDayRequired,
					'addonPrice' => $addonPrice,
					'addonDesc' => $addonDesc,
					'services_id' => $id,

				];
				if (!empty($serviceAdonIds[$key]) ) {
					$addonServiceId = $serviceAdonIds[$key];
					$this->db->update('tbl_services_addon', $insertAddOnArr, array("id" => $addonServiceId));
				} else {
					$this->db->insert("tbl_services_addon",$insertAddOnArr);
				}
		//printr($insertAddOnArr,1);exit;
			}
		}
		if (isset($_FILES['services_image']['name'][0]) && !empty($_FILES['services_image']['name'][0]) && !empty($_FILES)) {
			$reArrayFiles = $this->reArrayFiles($_FILES['services_image'], $id);
		}
		

		$_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Service Updated successfully'));
		

	}
	public function reArrayFiles($file_post, $servicesId) {
		$file_ary = array();
		$file_count = count($file_post['name']);
		$file_keys = array_keys($file_post);

		for ($i=0; $i<$file_count; $i++) {
			foreach ($file_keys as $key) {
				$file_ary[$i][$key] = $file_post[$key][$i];
			}

			$file_name = uploadFile($file_ary[$i], DIR_SERVICES_FILE, SITE_SERVICES_FILE, 'tbl_services_files', 'fileName', 'id', 0);
			$this->db->insert("tbl_services_files",["fileName" => $file_name['file_name'], "servicesId" => $servicesId, "CreatedDate" => date('Y-m-d')]);

			//print_r($file_name);exit;
		}

		return $file_ary;
	}
}
