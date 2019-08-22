<?php

class CustomerFavServices {
	function __construct($module = "", $id = 0, $token = "",$search_array= array()) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
    $this->search_array = $search_array;
	}

  	public function getPageContent()
  	{
        $content = "";
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/".$this->module.".skd");
        $sub_content = $sub_content->compile();

        $services = $this->db->pdoQuery("select * from tbl_favorite_services where customerId =".$this->sessUserId)->affectedRows();

        $no_jobs = ($services > 2) ? '' : 'hide';
        $content = array(
          "%SUB_HEADER_CONTENT%" => customerSubHeaderContent("favorite_sre"),
            "%SERVICE_DETAILS%" => $this->getServices($this->search_array,1),
            "%LOAD_CLASS%" => $no_jobs,
            "%REMOVE_ALL%" => ($services == '0') ? 'hide' : ''
        );
        return str_replace(array_keys($content),array_values($content), $sub_content);
    }

    public function getServices($search_array ='',$page_no = 1)
    {

        $services = $this->db->pdoQuery("select group_concat(serviceId) as SID,group_concat(id) as FID from tbl_favorite_services where customerId = ".$this->sessUserId)->result();

        $content = '';

        if($services['SID']!=''){
           // $services = $services->result();
           // printr($services,1);
           $myServices = explode(",",$services['SID']);
           $fevServiceId = explode(",",$services['FID']);

           foreach ($myServices as $key => $value) {
                $service_content = new MainTemplater(DIR_TMPL.$this->module."/service_desc-sd.skd");
                $service_content = $service_content->compile();

                $serviceData = $this->db->pdoQuery("
                    select s.*,c.".l_values('category_name')." as category_name ,sub.subcategory_name from tbl_services AS s
                    LEFT JOIN tbl_category AS c ON s.servicesCategory=c.id
                    LEFT JOIN tbl_subcategory AS sub ON s.servicesSubCategory = sub.id where s.id =".$value)->result();
                $userContent = getUser($serviceData['freelanserId']);

                $rating_detail = $this->db->pdoQuery("select AVG(startratings) As rating from tbl_reviews where freelancerId='".$serviceData['freelanserId']."' ")->result();
                $rating = ($rating_detail['rating']*20);

                $img = getserviceImages($value,1);

                $service_sold = $this->sold_service($serviceData['freelanserId']);
                $data_array = array(
                    "%FEATURED_LBL_CLASS%" => checkClass($serviceData['featured'],$serviceData['isDelete']),
                    "%FEATURED_LBL%" => (checkClass($serviceData['featured'],$serviceData['isDelete'])!='') ? ((checkClass($serviceData['featured'],$serviceData['isDelete'])=='deleted-class') ? 'Deleted' : 'Featured') : '' ,
                    "%ID%" => $fevServiceId[$key],
                    "%SERVICE_LINK%" => SITE_URL.'service/'.$serviceData['servicesSlug'],
                    "%SERVICE_NAME%" => $serviceData['serviceTitle'],
                    "%CAT_NAME%" => $serviceData[l_values('category_name')],
                    "%SUB_CAT_NAME%" => $serviceData['subcategory_name'],
                    "%FEATURED_HIDE%" => ($serviceData['featured'] == 'n') ? 'hide': '',
                    "%PRICE%" => $serviceData['servicesPrice']."<span>".CURRENCY_SYMBOL."</span>",
                    "%USER_NAME%" => $userContent['firstName'].' '.$userContent['lastName'],
                    "%USER_IMAGE%" => ($userContent['profileImg']=='') ? SITE_UPD."no_user_image.png" : SITE_USER_PROFILE.$userContent['profileImg'],
                    "%SOLD_SERVICES%" => $service_sold,
                    "%USER_RATINGS%" => $rating,
                    "%USER_LOCATION%" => $userContent['location'],
                    "%USER_IMG%" => getUserImage($serviceData['freelanserId']),
                    "%SERVICE_IMG%" =>$img[0],
                    "%DETAIL_LINK%" => ($serviceData['isDelete'] == 'y') ? 'javascript:void(0)' : SITE_URL.'service/'.$serviceData['servicesSlug']
                );
                $content .= str_replace(array_keys($data_array), array_values($data_array), $service_content);
          }
        } else {
          $content = "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_SERVICES_FOUND."</span>";
        }
        return $content;
    }

    public function submitContent($data){
        extract($data);
        $update = $this->db->update("tbl_jobs",array("budget"=>$budget),array('id'=>$jobId_edit));

        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => JOB_UPDATED_SUCCESSFULLY));
        redirectPage(SITE_URL.'C/my-jobs');
    }

    public function sold_service($id)
    {
      $data = $this->db->pdoQuery("select * from tbl_services_order where freelanserId='".$id."' and paymentStatus='c' and serviceStatus='c'")->results();
      $earned_amount = 0;
      foreach ($data as $value) {
        $earned_amount ++;
      }
      return $earned_amount;
    }

   public function responseTime($id)
   {
      $job_detail = $this->db->pdoQuery("select * from tbl_job_invitation where freelancerId='".$id."' and status!='p' ")->results();
      $d = "";
      $i=0;
      foreach ($job_detail as $value)
      {
        $now = date_create($value['createdDate']);
        $date = date_create($value['acceptRejectDate']);
        $interval = $date->diff($now);
        $days = $interval->format('%i');
        $d += $days;
        $i++;
      }
      $final = ceil($d/$i);

      if($final<1440)
      {
        if($final>60)
          $final_result = $final/60 ." minutes";
        else
          $final_result = $final." minutes";
      }
      else if($final>1440 && $final<43200)
      {
          $final_result = floor($final/1440)." days";
      }
      else if($final>43200)
      {
          $final_result = floor($final/1440)." Month";
      }
      return $final_result;
   }

}
 ?>


