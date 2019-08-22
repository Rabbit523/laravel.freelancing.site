<?php
class serviceSearch {
    function __construct($module = "", $type='jobs',$id = 0, $token = "",$search_array= array()) {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module = $module;
        $this->id = $id;
        $this->type = $type;
        $this->search_array = $search_array;
    }

    public function getPageContent()
    {
//        $selQuery = $this->db->pdoQuery("select * from tbl_users where (email = ? or userName = ?) AND password = ? and is_default_usertype = 'y'",array($email,$email,md5($password)))->result();
//        if(!empty($selQuery)){
//            extract($selQuery);

//        $last_page = $_SESSION['last_page'];
//       if(isset($email) && isset($password)){
        $start_amount = (array_key_exists('start_amount',$this->search_array)) ? urldecode($this->search_array['start_amount']) : '';
        $end_amount = (array_key_exists('end_amount',$this->search_array)) ? urldecode($this->search_array['end_amount']) : '';

        $Rstart_amount = (array_key_exists('Rstart_amount',$this->search_array)) ? urldecode($this->search_array['Rstart_amount']) : '';
        $Rend_amount = (array_key_exists('Rend_amount',$this->search_array)) ? urldecode($this->search_array['Rend_amount']) : '';
        $keyword = (array_key_exists('keyword',$this->search_array)) ? urldecode($this->search_array['keyword']) : '';
        $category = (array_key_exists('category',$this->search_array)) ? urldecode($this->search_array['category']) : ((array_key_exists('Rcategory',$this->search_array)) ? urldecode($this->search_array['Rcategory']) : '');
        $subcategory = (array_key_exists('subcategory',$this->search_array)) ? urldecode($this->search_array['subcategory']) : ((array_key_exists('Rsubcategory',$this->search_array)) ? urldecode($this->search_array['Rsubcategory']) : '');
        $sort = (array_key_exists('sorting',$this->search_array)) ? urldecode($this->search_array['sorting']) : '';
        $exp_lvl = (array_key_exists('exp_lvl',$this->search_array)) ? urldecode($this->search_array['exp_lvl']) : ((array_key_exists('Rexp_lvl',$this->search_array) ? urldecode($this->search_array['Rexp_lvl']) : 'checked'));
        $deliveryTime = (array_key_exists('deliveryTime',$this->search_array)) ? urldecode($this->search_array['deliveryTime']) : '';
        $serviceType = (array_key_exists('serviceType',$this->search_array)) ? urldecode($this->search_array['serviceType']) : ((array_key_exists('RserviceType',$this->search_array)) ? urldecode($this->search_array['RserviceType']) : '');
        $location = $this->search_array['location'] = (array_key_exists('location',$this->search_array)) ? urldecode($this->search_array['location']) : '';
        if(!empty($category)){
            $main_cat_id = $this->db->pdoQuery("select id from tbl_category where ".l_values('category_name')." LIKE '%".$category."%' ")->result();
        }
        else{
            $main_cat_id['id'] = 0;
        }

        $where = $sorting = '';

        if(isset($sorting))
        {
            $sorting .= $this->dataSort($sorting);
        }
        if($this->search_array!='')
        {
            $where .= $this->conditionWhere($this->search_array);
        }


        $query = $this->db->pdoQuery("SELECT (select count(id) from tbl_services_order where tbl_services_order.servicesId = s.id and paymentStatus='c' and serviceStatus='c' group by tbl_services_order.servicesId) As soldServices,s.*,AVG(startratings) AS freelancerrate,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,f.firstName,f.lastName,f.location FROM tbl_services AS s
            LEFT JOIN tbl_category AS c ON c.id = s.servicesCategory
            LEFT JOIN tbl_subcategory AS sub ON sub.id = s.servicesSubCategory
            LEFT JOIN tbl_reviews AS r ON r.freelancerid = s.freelanserid
            LEFT JOIN tbl_users AS f ON f.id = s.freelanserid
            where s.isActive ='y' and s.isApproved='a' and f.isDeleted='n' and f.isActive='y' and s.isDelete = 'n'
            $where
            group by s.id ".$sorting)->affectedRows();

        /*echo "SELECT (select count(id) from tbl_services_order where tbl_services_order.servicesId = s.id and paymentStatus='c' and serviceStatus='c' group by tbl_services_order.servicesId) As soldServices,s.*,AVG(startratings) AS freelancerrate,c.category_name,sub.".l_values('subcategory_name')." as subcategory_name,f.firstName,f.lastName,f.location FROM tbl_services AS s
            LEFT JOIN tbl_category AS c ON c.id = s.servicesCategory
            LEFT JOIN tbl_subcategory AS sub ON sub.id = s.servicesSubCategory
            LEFT JOIN tbl_reviews AS r ON r.freelancerid = s.freelanserid
            LEFT JOIN tbl_users AS f ON f.id = s.freelanserid
            where s.isActive ='y' and s.isApproved='a' and s.isDelete = 'n'
            $where
            group by s.id ".$sorting*/;

        $load_class = ($query>10) ? '' : 'hide';

        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/service_search-sd.skd");
        $sub_content = $sub_content->compile();

        $array = array
        (
            "%KEYWORD%" => $keyword,
            "%MAX_PRICE%" => $this->maxPrice(),
            "%CATEGORY%" => $this->getCategory($category),
            "%SUBCATEGORY%" => $this->getSubcategory($main_cat_id['id'],$subcategory),
            "%SERVICES_DATA%" => $this->serviceList($this->search_array),
            "%LOAD_CLASS%" => $load_class,
            "%BEGINNER_CLASS%" => ($exp_lvl!='' && $exp_lvl=='beginner') ? 'checked' : '',
            "%PRO_CLASS%" => ($exp_lvl!='' && $exp_lvl=='pro') ? 'checked' : '',
            "%IM_CLASS%" => ($exp_lvl!='' && $exp_lvl=='intermediate') ? 'checked' : '',
            "%DELIVERY_TIME%" => $deliveryTime,
            "%START_AMOUNT%" => $start_amount,
            "%END_AMOUNT%" => $end_amount,
            "%RSTART_AMOUNT%" => $Rstart_amount,
            "%REND_AMOUNT%" => $Rend_amount,
            "%SERVICETYPE_FEATURED%" => ($serviceType == 'featured') ? 'selected' : '',
            "%SERVICETYPE_NEW%" => ($serviceType == 'new') ? 'selected' : '',
            "%LOCATION%" => $location,
            "%NEWEST%" => ($sort=="Newest") ? 'selected' : '',
            "%LTOHSOLD%" => ($sort=="lTohSold") ? 'selected' : '',
            "%HTOLSOLD%" => ($sort=="hTolSold") ? 'selected' : '',
            "%LTOHPRICE%" => ($sort=="lTohPrice") ? 'selected' : '',
            "%HTOLPRICE%" => ($sort=="hTolPrice") ? 'selected' : '',
            "%LTOHRATE%" => ($sort=="lTohRate") ? 'selected' : '',
            "%HTOLRATE%" => ($sort=="hTolRate") ? 'selected' : '',
            "%SEARCH_SECTION%" => $this->search_label($keyword,$location)
        );
        $data = str_replace(array_keys($array), array_replace($array), $sub_content);
        return $data;
//       }else{
//           $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => PLEASE_SIGN_IN_FOR_SERVICE));
//           redirectPage(SITE_URL."SignIn");
//       }
    }

    public function search_label($keyword='',$location='')
    {
        $sub_content = new MainTemplater(DIR_TMPL . "/search_section-sd.skd");
        $sub_content = $sub_content->compile();
        if(isset($this->sessUserId) && $this->sessUserId>0)
        {
            if($this->sessUserType=='Customer')
            {
                $array = array(
                    "%JOB_CLASS%" => 'hide',
                    "%FREELANCER_CLASS%" => '',
                    "%SERVICE_CLASS%" => '',
                    "%FIRST_LABEL%" => 'Services',
                    "%TEXT_PLACEHOLDER%" => 'Find Services',
                    "%FIRST_DATA_URL%" => 'Services',
                    "%KEYWORD%" => $keyword
                );
            }
            else
            {
                $array = array(
                    "%JOB_CLASS%" => '',
                    "%FREELANCER_CLASS%" => 'hide',
                    "%SERVICE_CLASS%" => 'hide',
                    "%FIRST_LABEL%" => 'Services',
                    "%TEXT_PLACEHOLDER%" => 'Find Services',
                    "%FIRST_DATA_URL%" => 'Services',
                    "%KEYWORD%" => $keyword
                );
            }
        }
        else
        {
            $array = array(
                "%JOB_CLASS%" => '',
                "%FREELANCER_CLASS%" => '',
                "%SERVICE_CLASS%" => '',
                "%FIRST_LABEL%" => 'Services',
                "%TEXT_PLACEHOLDER%" => 'Find Services',
                "%FIRST_DATA_URL%" => 'Services',
                "%KEYWORD%" => $keyword
            );
        }
        $option = "";
        $option .= $array["%FREELANCER_CLASS%"]==""?'<option value="'.SLIDER_LIST_I.'" %I_SELECTED%>'.SLIDER_LIST_I.'</option>':"";
        $option .= $array["%JOB_CLASS%"]==""?'<option value="'.SLIDER_LIST_II.'" %II_SELECTED%>'.SLIDER_LIST_II.'</option>':"";
        $option .= $array["%SERVICE_CLASS%"]==""?'<option value="'.SLIDER_LIST_III.'" %III_SELECTED%>'.SLIDER_LIST_III.'</option>':"";
        $array["%TYPE%"]=$option;
        $array["%LOCATION%"]=$location;
        $array["%I_SELECTED%"]="";
        $array["%II_SELECTED%"]="";
        $array["%III_SELECTED%"]="selected";
        $data = str_replace(array_keys($array), array_values($array), $sub_content);
        return $data;
    }

    public function maxPrice()
    {
        $price = $this->db->pdoQuery("select MAX(servicesPrice) As maxServicePrice from tbl_services where isActive='y' and isApproved='a'  ")->result();
        return $price['maxServicePrice'];
    }

    public function serviceList($search_array='',$pageNo=1)
    {
        $num_rec_per_page=10;
        $start_from = ($pageNo-1) * $num_rec_per_page;
        $where = '';
        if($this->type=='feed')
        {
            $where .= $this->jobFeedCondition();
        }
        $sorting = '';
        if(isset($search_array['sorting']))
        {
            $sorting .= $this->dataSort($search_array['sorting']);
        }
        else
        {
            $sorting .= " ORDER BY s.id DESC";
        }
        if($search_array!='')
        {
            $where .= $this->conditionWhere($search_array);
        }

        $query = $this->db->pdoQuery("SELECT (select count(id) from tbl_services_order where tbl_services_order.servicesId = s.id and paymentStatus='c' and serviceStatus='c' group by tbl_services_order.servicesId) As soldServices,s.*,AVG(startratings) AS freelancerrate,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,f.firstName,f.lastName,f.location,f.userSlug,f.freelancerLvl FROM tbl_services AS s
            LEFT JOIN tbl_category AS c ON c.id = s.servicesCategory
            LEFT JOIN tbl_subcategory AS sub ON sub.id = s.servicesSubCategory
            LEFT JOIN tbl_reviews AS r ON r.freelancerid = s.freelanserid
            LEFT JOIN tbl_users AS f ON f.id = s.freelanserid
            where s.isActive ='y' and s.isApproved='a' and s.isDelete = 'n'
            $where and f.isDeleted='n' and f.isActive = 'y'
            group by s.id ".$sorting." LIMIT ".$start_from.",".$num_rec_per_page)->results();

        $data = '';
        if(count($query)>0)
        {

            foreach ($query as $value)
            {
                $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/service_loop-sd.skd");
                $sub_content = $sub_content->compile();

                if(isset($this->sessUserId) && $this->sessUserId>0)
                    $fav = $this->db->pdoQuery("select * from tbl_favorite_services where customerId=? and serviceId=?",array($this->sessUserId,$value['id']))->affectedRows();
                else
                    $fav = 0;
                $img = getserviceImages($value['id'],1);
                $array = array(
                    "%SERVICE_ID%" => $value['id'],
                    "%FEATURED_CLASS%" => ($value['featured'] == 'y' && $value['featured_payment_status'] == 'c') ? '' : 'hide',
                    "%SERVICE_IMG%" => $img[0],
                    "%SLUG%" => $value['servicesSlug'],
                    "%SERVICE_TITLE%" =>  filtering(ucfirst($value['serviceTitle'])),
                    "%CATEGORY%" => filtering(ucfirst($value['category_name'])),
                    "%SUBCATEGORY%" => filtering(ucfirst($value['subcategory_name'])),
                    "%PRICE%" => CURRENCY_SYMBOL.$value['servicesPrice'],
                    "%SOLD_SERVICE%" => ($value['soldServices']=='') ? '0' : $value['soldServices'] ,
                    "%FREELANCER_IMG%" => getUserImage($value['freelanserId']),
                    "%FREELANCER_NAME%" => filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
                    "%FREELANCER_EXP_LVL%" => getUserExpLevel($value['freelancerLvl']),
                    "%FREELANCER_RATE%" => ROUND($value['freelancerrate']),
                    "%LOCATION%" => filtering(ucfirst($value['location'])),
                    "%SAVE_CLASS%" => ($fav>0) ? 'fa fa-heart' : 'fa fa-heart-o',
                    "%SAVE_RIGHTS%" => (isset($this->sessUserId) && $this->sessUserId>0)  ? (($this->sessUserType=='Customer') ? '' : 'hide') : '',
                    "%USER_SLUG%" => $value['userSlug']
                );
                $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
            }
        }
        else
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/no_record-sd.skd");
            $sub_content = $sub_content->compile();
            $keyword = !empty($this->search_array['keyword']) ? $this->search_array['keyword'] : '';

            $array = [
                '%REMOVE_DESC%' => $keyword == '' ? 'style="display: none;"' : '',
                '%KEYWORD_DESC%' => $keyword == '' ? 'Search Services'   : "Search Services for : '".$keyword."' ",
                '%REDIRECT_URL%' => SITE_URL.'search/freelancer/'.(empty($keyword) ? ''  : '?keyword='.$keyword)
            ];
            $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
        return $data;
    }

    public function conditionWhere($search_array)
    {
        $where = '';

        if(!empty($_SESSION['pickgeeks_email'])){
            $where .= "and f.email !='".$_SESSION['pickgeeks_email']."'";
        }

        if(isset($search_array['category']) && $search_array['category']!='')
        {
            $where .= " and c.".l_values('category_name')." LIKE '%".urldecode($search_array['category'])."%' ";
        }
        if(isset($search_array['subcategory']) && $search_array['subcategory']!='')
        {
            $where .= " and sub.".l_values('subcategory_name')." LIKE '%".$search_array['subcategory']."%' ";
        }
        if(isset($search_array['exp_lvl']) && $search_array['exp_lvl']!='')
        {
            $lvl = '';
            if($search_array['exp_lvl']=='beginner'){
                $lvl='f';
            }
            else if($search_array['exp_lvl']=='pro'){
                $lvl='e';
            }
            else{
                $lvl='i';
            }
            $where .= " and f.freelancerLvl = '".$lvl."' ";
        }
        if(isset($search_array['deliveryTime']) && $search_array['deliveryTime']!='')
        {
            $where .= " and s.noDayDelivery = '".$search_array['deliveryTime']."' ";
        }
        if(isset($search_array['start_amount']) && $search_array['start_amount']!='')
        {
            $where .= " and (s.servicesPrice between '".$search_array['start_amount']."' and  '".$search_array['end_amount']."') ";
        }
        if(isset($search_array['Rstart_amount']) && $search_array['Rstart_amount']!='')
        {
            $where .= " and (s.servicesPrice between '".$search_array['Rstart_amount']."' and  '".$search_array['Rend_amount']."') ";
        }
        if(isset($search_array['serviceType']) && $search_array['serviceType']!='')
        {
            if($search_array['serviceType'] == "featured")
            {
                $where .= " and (s.featured = 'y') ";
            }
            else
            {
                $where .= " and (DATEDIFF('".date('Y-m-d H:i:s')."',s.servicesPostDate) <15) ";
            }
        }
        if(isset($search_array['location']) && $search_array['location']!='')
        {
            $where .= " and f.location LIKE '%".$search_array['location']."%' ";
        }
        if(isset($search_array['keyword']) && $search_array['keyword']!='')
        {
            $where .= " and s.serviceTitle LIKE '%".urldecode($search_array['keyword'])."%' ";
        }
        return $where;
    }
    public function dataSort($order='Newest')
    {
        $sorting = '';
        if($order == "Newest")
        {
            $sorting .= " ORDER BY s.id DESC";
        }
        if($order == "lTohSold")
        {
            $sorting .= " ORDER BY (select count(id) from tbl_services_order where tbl_services_order.servicesId = s.id and paymentStatus='c' and serviceStatus='c' group by tbl_services_order.servicesId) ASC";
        }
        if($order == "hTolSold")
        {
            $sorting .= " ORDER BY (select count(id) from tbl_services_order where tbl_services_order.servicesId = s.id and paymentStatus='c' and serviceStatus='c' group by tbl_services_order.servicesId) DESC";
        }
        if($order == "lTohPrice")
        {
            $sorting .= " ORDER BY s.servicesPrice ASC";
        }
        if($order == "hTolPrice")
        {
            $sorting .= " ORDER BY s.servicesPrice DESC";
        }
        if($order == "lTohRate")
        {
            $sorting .= " ORDER BY AVG(startratings) ASC";
        }
        if($order == "hTolRate")
        {
            $sorting .= " ORDER BY AVG(startratings) DESC";
        }

        return $sorting;
    }

    public function getCategory($cat='')
    {
        $where = '';
        $category = $this->db->pdoQuery("select c.id As catId,c.".l_values('category_name')." as category_name from tbl_category As c
            LEFT JOIN tbl_subcategory As s ON s.maincat_id = c.id
            where c.isActive=? $where and c.isDelete=? and s.maincat_id IS NOT NULL group by s.maincat_id",array('y','n'))->results();
        $category_content = '<option value="">--'.SELECT_CATEGORY.'--</option>';

        foreach ($category as $key => $value) {
            $select = ($cat == $value['category_name']) ? 'selected' : '';
            $category_content .=  "<option value='".$value['category_name']."' ".$select.">".$value['category_name']."</option>";
        }
        return $category_content;
    }

    public function getSubcategory($main_cat,$sub_cat='')
    {
        $category = $this->db->select('tbl_subcategory',array('id',l_values('subcategory_name').' as subcategory_name'),array('isActive'=>'y','maincat_id'=>$main_cat,'isDelete'=>'n'))->results();
        $category_content = '<option value="">--'.SELECT_SUBCATEGORY.'--</option>';

        foreach ($category as $key => $value) {
            $select = ($sub_cat == $value['subcategory_name']) ? 'selected' : '';
            $category_content .=  "<option value='".$value['subcategory_name']."' ".$select." >".$value['subcategory_name']."</option>";
        }
        return $category_content;
    }

}
?>


