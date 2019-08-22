<?php

class FreelancerPostServices {
	function __construct($module = "", $id = 0, $token = "",$slug= array()) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
        $this->slug = $slug;
    }

    public function getPageContent()
    {   
        if($this->slug!='')
        {
            // echo "string";
            // exit();
            $service_detail = $this->db->pdoQuery("select * from tbl_services where servicesSlug=?",array($this->slug))->result();

            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_edit_post_services-sd.skd");
            $sub_content = $sub_content->compile();
            $featured = ($service_detail['featured'] == 'y') ? 'checked' : '';
            $array = array(
                "%SERVICES_IMG%" => $this->get_images($service_detail['id']),
                "%CATEGORY%" => getServiceCategory($service_detail['servicesCategory']),
                "%RANDOM%" => genrateRandom(),
                "%SUBCATEGORY%" => getSubcategory($service_detail['servicesCategory'],$service_detail['servicesSubCategory']),
                "%SERVICES_TITLE%"=> $service_detail['serviceTitle'],
                "%NO_DAYS%"=> $service_detail['noDayDelivery'],
                "%SERVICE_PRICE%"=> $service_detail['servicesPrice'],
                "%DESC%"=> $service_detail['description'],
                "%REQUIRED_DETAILS%"=> $service_detail['requiredDetails'],
                "%FEATURED%"=> $featured,
                "%FEATURED_DAYS%" => $service_detail['featured_days'],
                "%ADDON_DETAIL%" => $this->getAddOnDetail($service_detail['id']),
                "%ID%" => $service_detail['id'],
                "%DISPLAY_TYPE%" => ($service_detail['featured']=='y') ? 'block' : 'none' ,
                "%FETURED_DAYS%" => $service_detail['featured_days'],
                "%FEATURED_AMOUNT%" => $service_detail['featured_days'] * SERVICE_FEATURED_FEES,
                "%ADDON_HIDE_CLASS%" => ($this->getAddOnCount($service_detail['id']) != '0') ? '' : 'hide'
            );
        }
        else
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_post_services-sd.skd");
            $sub_content = $sub_content->compile();

            $array = array(
            	"%CATEGORY%" => getServiceCategory(),
                "%RANDOM%" => genrateRandom()
            );
        }
        return str_replace(array_keys($array), array_values($array), $sub_content);
    }
    public function get_images($id)
    {
        $images = $this->db->pdoQuery("select * from tbl_services_files where servicesId=?",array($id))->results();

        $data = '';
        if(count($images)>0)
        {
            $i=1;
            foreach ($images as $value) {

                $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/files-sd.skd");
                $sub_content = $sub_content->compile();

                $array = array(
                    "%ATT_SYMBOL%" => SITE_SERVICES_FILE.$value['fileName'],
                    "%FILE_NAME%" => "File".$i,
                    "%ID%" => $value['id']
                );
                $data .= str_replace(array_keys($array), array_values($array), $sub_content);
                $i++;
            }
        }
        else
        {
            $data = '';
        }
        return $data;
    }

    public function submitFiles2($token,$image)
    {
        //$extention;
        $objPost = new stdClass();
        $objPost->userId = $this->sessUserId;
        $objPost->token  = $token;
        $table = 'tbl_temp_files';

        $file_data = new MainTemplater(DIR_TMPL . $this->module . "/files-sd.skd");
        $file_data = $file_data->compile();

        $objPost->fileName = $image;
        $objPost->CreatedDate = date('Y-m-d H:i:s');

        $fileName = $image;
        $objPostArray = (array) $objPost;
        $id = $this->db->insert('tbl_temp_files',$objPostArray)->getLastInsertId();

        $attFileName = SITE_SERVICES_FILE. $fileName;

        $result = "";
        $data_array = array(
          "%ATT_SYMBOL%" => $attFileName,
          "%FILE_NAME%" => $fileName,
          "%ID%" => $id
        );
        return str_replace(array_keys($data_array),array_values($data_array),$file_data);
    }

    public function submitFiles($data,$token,$extention)
    {
        $extention;
        $objPost = new stdClass();
        $objPost->userId = $this->sessUserId;
        $objPost->token  = $token;
        $table = 'tbl_temp_files';

        $file_data = new MainTemplater(DIR_TMPL . $this->module . "/files-sd.skd");
        $file_data = $file_data->compile();

        $fileName = $_FILES['file']['name'];

        if(!empty($fileName))
        {
            $file_name = uploadFile($_FILES['file'], DIR_SERVICES_FILE,SITE_SERVICES_FILE);
            $objPost->fileName = $file_name['file_name'];
        }
        $objPost->CreatedDate = date('Y-m-d H:i:s');

        $objPostArray = (array) $objPost;
        $id = $this->db->insert('tbl_temp_files',$objPostArray)->getLastInsertId();

        $result = $ext_name = "";

        switch ($extention) {
            case 'png':
                $ext_name = 'png.png';
            break;
            case 'PNG':
                $ext_name = 'png.png';
            break;
            case 'jpg':
                $ext_name = 'jpg.png';
            break;
            case 'JPG':
                $ext_name = 'jpg.png';
            break;
            case 'jpeg':
                $ext_name = 'jpg.png';
            break;
            case 'JPEG':
                $ext_name = 'jpg.png';
            break;

        }

        $attFileName = SITE_SERVICES_FILE.$objPost->fileName;

        $data_array = array(
          "%ATT_SYMBOL%" => $attFileName,
          "%FILE_NAME%" => $fileName,
          "%ID%" => $id
        );
        $result = str_replace(array_keys($data_array),array_values($data_array),$file_data);
        echo json_encode($result);
        exit;
    }
    public function saveData($data)
    {
        $response = array();
        extract($data);
       
        $ServiceTitle =isset($serviceTitle)? filtering(ucfirst($serviceTitle)):'';
        $servicesSlug = Slug('servicesSlug',$ServiceTitle,'tbl_services');
        $Category =isset($category)? filtering($category):'';
        $Subcategory =isset($subcategory)? filtering($subcategory):'';
        $NoDayDelivery =isset($noDayDelivery)? filtering($noDayDelivery):'';
        $ServicesPrice =isset($servicesPrice)? filtering($servicesPrice):'';
        $Description =isset($description)? $description:'';
        $RequiredDetails = isset($requiredDetails)? filtering($requiredDetails):'';
        $no_of_days = isset($no_of_days) ? $no_of_days : '';
        $featured = isset($featured)? 'y':'n';

        if(SERVICE_APP_REQ=='yes')
        {
            $isApproved = 'p';
            $isActive = 'n';
        }
        else
        {
            $isApproved = 'a';
            $isActive = 'y';
        }


        if($id!='')
        {
            //echo "<pre>";
            //print_r($data);exit;
            $array = array(
                "serviceTitle"=> $ServiceTitle,
                "servicesCategory"=> $Category,
                "servicesSubCategory"=> $Subcategory,
                "description"=> $Description,
                "servicesPostDate"=> date('Y-m-d H:i:s'),
                "noDayDelivery"=> $NoDayDelivery,
                "servicesPrice"=> $ServicesPrice,
                "requiredDetails"=> $RequiredDetails,
                "featured"=> $featured,
                "featured_days" => $no_of_days,
                "isActive" => $isActive,
                "isApproved" => 'p'
            );
            $this->db->update("tbl_services",$array,array("id"=>$id));
            $id = $id;
            $msgUpdate = UPDATED_SERVICE;
        }
        else
        {
            if(SERVICE_APP_REQ=='yes'){
                $msgUpdate = WAITING_FOR_YOU_TO_APPROVE;
            }
            else{
                $msgUpdate = ADDED_A_NEW_SERVICE;
            }
            $array = array(
                "serviceTitle"=> $ServiceTitle,
                "servicesSlug"=> $servicesSlug,
                "servicesCategory"=> $Category,
                "servicesSubCategory"=> $Subcategory,
                "description"=> $Description,
                "servicesPostDate"=> date('Y-m-d H:i:s'),
                "noDayDelivery"=> $NoDayDelivery,
                "servicesPrice"=> $ServicesPrice,
                "requiredDetails"=> $RequiredDetails,
                "featured"=> $featured,
                "freelanserId"=> $this->sessUserId,
                "isApproved" => $isApproved,
                "featured_days" => $no_of_days,
                "isActive" => $isActive
            );
            $id = $this->db->insert("tbl_services",$array)->getLastInsertId();
        }


        /*services images insert*/
        if(isset($frmToken))
        {
            $token_detail = $this->db->pdoQuery("select * from tbl_temp_files where token=?",array($frmToken))->results();
            foreach ($token_detail as $value)
            {
                $this->db->insert("tbl_services_files",array("servicesId"=>$id,"fileName"=>filtering($value['fileName']),"CreatedDate"=>date('Y-m-d H:i:s')));
            }
            $this->db->delete("tbl_temp_files",array("token"=>$frmToken));
        }

        /*Add on insert*/

        if(!empty($addon_title))
        {
            if($action=='editData')
            {
                $this->db->delete("tbl_services_addon",array("services_id"=>$id));
            }
            foreach ($addon_title as $k => $value)
            {
                if((!empty($addon_title[$k])) && (!empty($addon_price[$k])) && (!empty($addon_days[$k])) && (!empty($addon_desc[$k])) )
                {
                    $title = (!empty($addon_title[$k])) ? filtering($addon_title[$k]) : '';
                    $dayRequired = (!empty($addon_days[$k])) ? filtering($addon_days[$k]) : '';
                    $addonPrice = (!empty($addon_price[$k])) ? filtering($addon_price[$k]) : '';
                    $addonDesc = (!empty($addon_desc[$k])) ? filtering($addon_desc[$k]): '';

                    $this->db->insert("tbl_services_addon",array("services_id"=>$id,"addonTitle"=>$title,"addonDayRequired"=>$dayRequired,"addonPrice"=>$addonPrice,"addonDesc"=>$addonDesc));
                }
            }
        }
        $freelanserDetail = getUser($this->sessUserId);
        $msg = filtering(ucfirst($freelanserDetail['firstName']))." ".filtering(ucfirst($freelanserDetail['lastName']))." has ".$msgUpdate;
        if($msgUpdate == "updated service."){
            $link = SITE_URL."app-sd/masters-sd/units-sd/manage_services-sd/";
        }else{
            $link = SITE_URL."app-sd/masters-sd/units-sd/manage_service_request-sd/";
        }
        $this->db->insert("tbl_notification",array("userId"=>'0',"message"=>$msg,"detail_link"=>$link,"isRead"=>'n',"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')));
        $adm_approval = '';
        if(SERVICE_APP_REQ=='yes'){
            $adm_approval = "Your Service would be there in Admin's approval";
        }
        if($action=='editData')
        {
            $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=> SERVICE_HAS_BEEN_UPDATED_SUCCESSFULLY.". ".$adm_approval));
        }
        else
        {
            $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>SERVICE_HAS_BEEN_ADDED_SUCCESSFULLY.". ".$adm_approval));
        }
        redirectPage(SITE_URL."my-services");
    }

    public function getAddOnCount($id)
    {
        $query = $this->db->pdoQuery("select * from tbl_services_addon where services_id=?",array($id))->affectedRows();
        return $query;
    }
    public function getAddOnDetail($id)
    {

        $query = $this->db->pdoQuery("select * from tbl_services_addon where services_id=?",array($id));
        $query_data = $query->results();
        $data = '';

        if($query->affectedRows()>0)
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addOn-sd.skd");
            $sub_content = $sub_content->compile();

            $i=1;
            foreach ($query_data as $value) {
                $array = array(
                    "%ADDON_TITLE%"=> filtering(ucfirst($value['addonTitle'])),
                    "%ADDON_PRICE%"=> filtering($value['addonPrice']),
                    "%ADDON_DAYS%"=> filtering($value['addonDayRequired']),
                    "%ADDON_DESC%"=> filtering($value['addonDesc']),
                    "%ID%" => $value['id'],
                    "%DATA_ID%" => $i
                );
                $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
                $i++;
            }
        }
        else
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/addOn-sd.skd");
            $sub_content = $sub_content->compile();
            $array = array(
                "%ADDON_TITLE%"=> '',
                "%ADDON_PRICE%"=> '',
                "%ADDON_DAYS%"=> '',
                "%ADDON_DESC%"=> '',
                "%ID%" => 1,
                "%DATA_ID%" => 1
            );
            $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
        return $data;
    }


}
?>


