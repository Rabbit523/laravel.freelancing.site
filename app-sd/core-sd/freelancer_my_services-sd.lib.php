<?php

class FreelancerMyServices {
	function __construct($module = "", $id = 0, $token = "",$search_array= array()) {
		foreach ($GLOBALS as $key => $values)
        {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
        $this->search_array = $search_array;
	}

  	public function getPageContent()
  	{
  		$status = (array_key_exists('status',$this->search_array)) ? urldecode($this->search_array['status']) : '';
  		$approval_status = (array_key_exists('approval_status',$this->search_array)) ? urldecode($this->search_array['approval_status']) : '';
        $keyword = (array_key_exists('keyword',$this->search_array)) ? urldecode($this->search_array['keyword']) : '';
  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_my_services-sd.skd");
        $sub_content = $sub_content->compile();

        $where = "s.freelanserId='".$this->sessUserId."' and s.isDelete='n'";
        if($status!='')
        {
        	$where .= " AND s.isActive='".$status."' ";
        }
        if($approval_status!='')
        {
        	$where .= " AND s.isApproved='".$approval_status."' ";
        }
        if(isset($keyword))
        {
            $where .= " AND s.serviceTitle LIKE '%".$keyword."%' ";
        }

        $pending_status = ($approval_status == 'p' ? 'checked' : '');
        $approved_status = ($approval_status == 'a' ? 'checked' : '');
        $reject_status = ($approval_status == 'r' ? 'checked' : '');
        $active_status = ($status == 'y') ? 'checked' : '';
        $dactive_status = ($status == 'n') ? 'checked' : '';

        $total_data = $this->db->pdoQuery("select s.*,c.".l_values('category_name')." as category_name ,sub.".l_values('subcategory_name')." as subcategory_name from tbl_services As s
        	LEFT JOIN tbl_category As c ON c.id = s.servicesCategory
        	LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
        	where ".$where." order by s.id DESC")->affectedRows();

        $load_class = ($total_data>10) ? '' : 'hide';

        $array = array(
                "%SUB_HEADER_CONTENT%" => subHeaderContent("my-services"),
        	"%SERVICE_LOOP%" => $this->services_loop($this->search_array,1),
        	"%LOAD_CLASS" => $load_class,
        	"%PENDING_STATUS%" => $pending_status,
        	"%APPROVED_STATUS%" => $approved_status,
        	"%REJECT_STATUS%" => $reject_status,
        	"%ACTIVE_STATUS%" => $active_status,
        	"%DACTIVE_STATUS%" => $dactive_status,
            "%KEYWORD%" => $keyword
        	);

        return str_replace(array_keys($array), array_replace($array), $sub_content);
    }

    public function services_loop($search_array='',$pageNo='1')
    {

    	$num_rec_per_page = 10;
    	$start_from = load_more_pageNo($pageNo,10);

    	$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/services_loop-sd.skd");
        $sub_content = $sub_content->compile();

        $where = "s.freelanserId='".$this->sessUserId."' and s.isDelete='n' ";
        if(isset($search_array['status']) && $search_array['status']!='')
        {
        	$where .= " AND s.isActive='".$search_array['status']."' ";
        }
        if(isset($search_array['approval_status']) && $search_array['approval_status']!='')
        {
        	$where .= " AND s.isApproved='".$search_array['approval_status']."' ";
        }
        if(isset($search_array['keyword']) && $search_array['keyword']!='')
        {
            $where .= " AND s.serviceTitle LIKE '%".$search_array['keyword']."%' ";
        }

        $query = $this->db->pdoQuery("select s.*,c.".l_values('category_name')." as category_name ,sub.".l_values('subcategory_name')." as subcategory_name from tbl_services As s
        	LEFT JOIN tbl_category As c ON c.id = s.servicesCategory
        	LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
        	where ".$where." order by s.id DESC LIMIT ".$start_from.",".$num_rec_per_page)->results();

        $data = "";
        if(count($query)>0)
        {
            foreach ($query as $value)
            {

                if($value['featured'] == 'n' && $value['isApproved'] == 'a')
                {
                    $make_featured_Class = '';
                }
                else
                {
                    $make_featured_Class = 'hide';
                }
	        	$img = getserviceImages($value['id'],1);

                $delete_rights = (checkDelete($value['id'],$this->sessUserId) == 0) ? '' : 'hide';
                $addon_res =  $this->db->pdoQuery("SELECT COUNT(id) as total FROM tbl_services_addon WHERE services_id=".$value['id'])->result();
	        	$array = array(
	        		"%SERVICES_IMG%" => !empty($img[0])?$img[0]:SITE_APP_UPD.'/images.png',
	        		"%SERVICES_TITLE%" => filtering(ucfirst($value['serviceTitle'])),
	        		"%SERVICES_CATEGORY%" => filtering(ucfirst($value['category_name'])),
	        		"%SERVICES_SUBCATEGORY%" => filtering(ucfirst($value['subcategory_name'])),
	        		"%PRICE%" => filtering($value['servicesPrice'])."<span>".CURRENCY_SYMBOL."</span>",
	        		"%POSTED_DATE%" => date('dS, F Y',strtotime($value['servicesPostDate'])),
	        		"%STATUS%" => ($value['isActive']=='y') ? 'Active' : 'DeActive',
	        		"%DELIVERY_DAYS%" => filtering($value['noDayDelivery'])." Day(s)&lrm;",
	        		"%ADD_ON_DETAIL%" => $this->addon_detial($value['id']),
	        		"%ADD_ON_CLASS%" => ($this->addon_detial($value['id']) == '') ? 'hide' : '',
                    "%REJECTED_CLASS%" => $value['isApproved']=='r' ? '' : 'hide',
	        		"%FEATURED_CLASS%" => ($value['featured']=='y' && $value['featured_payment_status'] == 'c') ? '' : 'hide',
	        		"%SLUG%" => $value['servicesSlug'],
                    "%SERVICE_ID%" => $value['id'],
                    "%FEATURED_DIV_CLASS%" => ($value['featured']=='y' && $value['isApproved'] =='a' && $value['featured_payment_status']=='p') ? '' : 'hide',
                    "%FEATURED_FEES%" => SERVICE_FEATURED_FEES*$value['featured_days'],
                    "%EDIT_RIGHTS%" => ($value['isApproved']=='a') ? 'already_approve':'',
                    "%SHOW_EDIT%" => ($value["isActive"]=="y") ? '':'hide',
                    "%MAKE_FEATURED_CLASS%" => $make_featured_Class,
                    "%DELETE_RIGHTS%" => $delete_rights,
                    "%APPROVAL_STATUS%" => ($value['isApproved']=='a') ? 'Approved' : ( ($value['isApproved']=='r') ? "Rejected" : 'Pending'),
                    "%TOTAL_ADDONS%" => $addon_res["total"],
        		);
	        	$data .= str_replace(array_keys($array), array_replace($array), $sub_content);
	        }
        }
        else
        {
        	$data = "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_RECORDS_FOUND."</span>";
        }
        return $data;
    }

    public function addon_detial($servicesId)
    {
    	$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addOn_detail-sd.skd");
        $sub_content = $sub_content->compile();

        $query = $this->db->pdoQuery("select * from tbl_services_addon where services_id='".$servicesId."' ")->results();
        $data = "";
        if(count($query)>0)
        {
	        foreach ($query as $value)
	        {
	        	$array = array(
	        		"%ADDONTITLE%"=> filtering(ucfirst($value['addonTitle'])),
	        		"%ADDON_DESC%" => filtering($value['addonDesc']),
	        		"%ADDONDAYREQUIRED%"=> $value['addonDayRequired'],
	        		"%ADDON_PRICE%"=> CURRENCY_SYMBOL.$value['addonPrice']
	        		);
	        	$data .= str_replace(array_keys($array), array_replace($array), $sub_content);
	        }
        }
        else
        {
        	$data = '';
        }
        return $data;
    }



}
 ?>


