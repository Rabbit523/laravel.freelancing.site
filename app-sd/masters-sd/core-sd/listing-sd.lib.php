<?php
class Listing extends Home {
    public $page_name;
    public $page_title;
    public $meta_keyword;
    public $meta_desc;
    public $page_desc;
    public $isActive;
    public $data = array();
    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '',$listingTypeId = 0) {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->data['listingTypeId'] = $this->listingTypeId = $listingTypeId;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_listing';
        $this->tab = (isset($_REQUEST['tab']))?$_REQUEST['tab']:'';
        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        switch ($type) {
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
            case 'undo' : {
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
        $content='';
        $totalbids=$this->db->pdoQuery("select count(bidId) as totalbids from tbl_bids where listingId=".$this->id)->result();
        $fetchResPCats = $this->db->pdoQuery("SELECT tl.*,
                tbl_listing_type.listingTypeName,
                tbl_listing_category.categoryName,
                tbl_listing_niche.nicheName,
                tbl_listing_platform.platformName,
                GROUP_CONCAT(DISTINCT(tbl_monetize_type.monTypeName))  as monTypeName,
                GROUP_CONCAT(DISTINCT(sc.categoryName)) as subCatName,
                GROUP_CONCAT(DISTINCT(sn.nicheName)) as subNicheName,
                tbl_fees.price,
                GROUP_CONCAT(DISTINCT(ef.feesType)) as extra_fees_listing,
                
                tbl_users.userName
                from tbl_listing as tl
                join tbl_listing_type on (tbl_listing_type.listingTypeId=tl.listingTypeId) 
                left join tbl_listing_category on (tbl_listing_category.id=tl.catId)
                left join tbl_listing_niche on (tbl_listing_niche.id=tl.nicheId)  
                left join tbl_listing_platform on (tbl_listing_platform.id=tl.listingPlatform)  
                left join tbl_monetize_type on FIND_IN_SET(tbl_monetize_type.monTypeId, tl.monTypeId ) > 0  
                left join tbl_listing_category as sc on FIND_IN_SET(sc.id, tl.subCatId) > 0 
                left join tbl_listing_niche as sn on FIND_IN_SET(sn.id, tl.subNicheId) > 0 
                left join tbl_fees as ef on FIND_IN_SET(ef.feesId, tl.extra_fees) > 0 
                left join tbl_fees on (tl.saleType=tbl_fees.classifiedOrauction AND tl.listingTypeId=tbl_fees.listingTypeId) 
                left join tbl_users on (tbl_users.id = tl.userId)
                where tl.listingId =".$this->id." group by tl.listingId")->result();
        extract($fetchResPCats);
        $grossRevenue=($grossRevenue!='')?(array)json_decode($grossRevenue):'';
        $netRevenue=($netRevenue!='')?(array)json_decode($netRevenue):'';
        $attachments=($attachments!='')?explode(",", $attachments):'';
        $keys_gross=$keys_net=array();
        $grossRevenue_content=$netRevenue_content=$attachment_data='';

        if($grossRevenue!=''){
            $keys_gross=array_keys($grossRevenue);
            $values=array_values($grossRevenue);
            for($i=0;$i<count($keys_gross);$i++){
                $keys_gross[$i]=date('M Y',strtotime($keys_gross[$i]));
                if($values[$i]){
                    $grossRevenue_content.= $keys_gross[$i]." : ".CURRENCY_SYMBOL.$values[$i]."&lt;br&gt;";
                }
            }
        }

        if($netRevenue!=''){
            $keys_net=array_keys($netRevenue);
            $values=array_values($netRevenue);
            for($i=0;$i<count($keys_net);$i++){
                $keys_net[$i]=date('M Y',strtotime($keys_net[$i]));
                if($values[$i]){
                    $netRevenue_content.= $keys_net[$i].":".CURRENCY_SYMBOL.$values[$i]."&lt;br&gt;";
                }
            }
        }
        if($attachments!=''){
            for($i=0;$i<count($attachments);$i++){
                //$extension= pathinfo($data['attachments'][$i], PATHINFO_EXTENSION);
                $attachment_data.='&lt;a href="' . SITE_UPD.'product/' . $attachments[$i] . '"&gt;'.split("--",$attachments[$i])[1].'&lt;/a&gt;&lt;br/&gt;';
            }
        }
        $content.=$this->displayBox(array("label" => "User Name&nbsp;:", "value" => filtering($userName)));
        $content.=$this->displayBox(array("label" => "Project URL&nbsp;:", "value" => filtering($listingUrl)));
        $content.=$this->displayBox(array("label" => "Project Tagline&nbsp;:", "value" => filtering($tagline)));        
        $content.=($description!='')?$this->displayBox(array("label" => "Project Description&nbsp;:", "value" => trim($description))):'';
        $content.=$this->displayBox(array("label" => "Project Type&nbsp;:", "value" => filtering($listingTypeName)));
        $content.=$this->displayBox(array("label" => "Sale Type&nbsp;:", "value" => filtering(($saleType=='a')?'Auction':'Classified')));
        $content.=($saleType=="c")?(($minimumOffer==1)?'':($this->displayBox(array("label" => "Minimum Offer Price&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$minimumOffer))))):(($reservePrice==0 || $reservePrice=='')?'':$this->displayBox(array("label" => "Reserved Price&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$reservePrice))));
        $content.=(($buyNowPrice==0) || ($buyNowPrice==''))?'':($this->displayBox(array("label" => "Buy Now Price&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$buyNowPrice))));
        
        $content.=($totalbids['totalbids']!=0)?(($saleType=="c")?$this->displayBox(array("label" => "Total Bids&nbsp;:", "value" => filtering($totalbids['totalbids']))):$this->displayBox(array("label" => "Total Offers&nbsp;:", "value" => filtering($totalbids['totalbids'])))):'';
        $content.=$this->displayBox(array("label" => "Duration Days&nbsp;:", "value" => filtering($listDurationDate)));
        $content.=$this->displayBox(array("label" => "Project Slug&nbsp;:", "value" => filtering($listingSlug)));


        if($listingTypeId==1 || $listingTypeId==2){
            $content.=($summary!='')?$this->displayBox(array("label" => "Project Summary&nbsp;:", "value" => filtering($summary))):'';
            $content.=($categoryName!='')?$this->displayBox(array("label" => "Project Category&nbsp;:", "value" => filtering($categoryName))):'';
            $content.=($subCatName!='')?$this->displayBox(array("label" => "Project Sub Category&nbsp;:", "value" => filtering($subCatName))):'';
            $content.=($nicheName!='')?$this->displayBox(array("label" => "Project Niche&nbsp;:", "value" => filtering($nicheName))):'';
            $content.=($subNicheName!='')?$this->displayBox(array("label" => "Project Sub Niches&nbsp;:", "value" => filtering($subNicheName))):'';
            $content.=($listingPlatform=='' || $listingPlatform==0)?'':$this->displayBox(array("label" => "Project Platform&nbsp;:", "value" => filtering($platformName)));            
            $content.=($monTypeName!='' || $monOther!='')?($this->displayBox(array("label" => "Monetization Methods&nbsp;:", "value" => filtering(($monTypeName!='')?$monTypeName:$monOther)))):'';            
            $content.=$this->displayBox(array("label" => "Is Revenue Generate&nbsp;:", "value" => filtering(($isRevenueGenerate=='y')?'Yes':'No')));            
            $content.=($grossRevenue_content!='')?$this->displayBox(array("label" => "Gross Revenue&nbsp;:", "value" => filtering($grossRevenue_content))):'';
             $content.=($netRevenue_content!='')?$this->displayBox(array("label" => "Net Revenue&nbsp;:", "value" => filtering($netRevenue_content))):'';
        $content.=($domainCode!='')?$this->displayBox(array("label" => "Domain Code&nbsp;:", "value" => filtering($domainCode))):'';
        if($listingTypeId==1){
            $content.=$this->displayBox(array("label" => "Is Traffic Received&nbsp;:", "value" => filtering($isTrafficRecieved=='y'?'Yes':'No')));
            if($isTrafficRecieved=='y'){
                $content.=($isGoogleVerified!='')? $this->displayBox(array("label" => "Is Verified By Google&nbsp;:", "value" => filtering($isGoogleVerified))):'';                
                $content.=($googleVerifyCode!='')?$this->displayBox(array("label" => "Google Verification Code&nbsp;:", "value" => filtering($googleVerifyCode))):'';
            }
        }
        $content.=$this->displayBox(array("label" => "Live Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($liveDate)))));
        }
        if($listingTypeId==4){
            $content.= $this->displayBox(array("label" => "Application Name&nbsp;:", "value" => filtering($appName))).
            $this->displayBox(array("label" => "First Publish&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($liveDate))))).
            $this->displayBox(array("label" => "iOS App&nbsp;:", "value" => filtering(($isIosApp=='y')?'Yes':'No'))).
            $this->displayBox(array("label" => "Android App&nbsp;:", "value" => filtering(($isAndroidApp=='y')?'Yes':'No'))).
            $this->displayBox(array("label" => "Average Revenue&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$averageRevenue))).
            $this->displayBox(array("label" => "Is Revenue Generate&nbsp;:", "value" => filtering(($isRevenueGenerate=='y')?'Yes':'No')));
            $content.=($grossRevenue_content!='')?$this->displayBox(array("label" => "Gross Revenue&nbsp;:", "value" => filtering($grossRevenue_content))):'';
            $content.=($netRevenue_content!='')?$this->displayBox(array("label" => "Net Revenue&nbsp;:", "value" => filtering($netRevenue_content))):'';
            $content.=$this->displayBox(array("label" => "Live Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($liveDate)))));
        }
        $content.=$this->displayBox(array("label" => "Is Listing Verified&nbsp;:", "value" => filtering($isListingVerified=='y'?'Yes':'No')));
        $content.=$this->displayBox(array("label" => "File Accept Status&nbsp;:", "value" => filtering($file_accept_status)));
        $content.=$this->displayBox(array("label" => "File Transfer Status&nbsp;:", "value" => filtering($file_transfer_status)));
        $content.=($disclosureAgreement!='')?$this->displayBox(array("label" => "Nondisclosure Agreements&nbsp;:", "value" => filtering($disclosureAgreement))):'';
        $content.=($attachment_data!='')?$this->displayBox(array("label" => "Attachments&nbsp;:", "value" => filtering($attachment_data))):'';
        $content.=$this->displayBox(array("label" => "Seller Amount&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$sellerAmount)));
        $content.=$this->displayBox(array("label" => "Listing Status&nbsp;:", "value" => filtering($listStatus)));
        $content.=($extra_fees_listing!='')?$this->displayBox(array("label" => "Extra Fees For&nbsp;:", "value" => filtering($extra_fees_listing))):'';
        $content.=$this->displayBox(array("label" => "Status&nbsp;:", "value" => filtering(($isActive=='y')?'Active':'Deactive')));
        $content.=$this->displayBox(array("label" => EXPERTS_CHOICE."&nbsp;:", "value" => filtering($editor_choice=='y'?'Yes':'No')));
        $content.=$this->displayBox(array("label" => "Inserted Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($createdDate)))));
        return $content;
    }
    public function getForm() {

        $content = $publish_month = $publish_year = $content_fees=$month_option=$month_expense_option='';
        $total_price=0;
        $listingTypeId = $this->listingTypeId;
        if($listingTypeId == 1 || $listingTypeId == 2)
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        elseif($listingTypeId == 3)
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form_domain-sd.skd");
        elseif($listingTypeId == 4)
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form_app-sd.skd");
        $main_content = $main_content->compile();
        $fetchResPCats = $this->db->select("tbl_listing",array("*"),array("listingId"=>$this->id))->result();
            $saleTypeId = $this->saleTypeId = $fetchResPCats['listingTypeId'];
            $this->website_url = $fetchResPCats['listingUrl'];
            $this->saleType = $fetchResPCats['saleType'];
            $this->domainCode = $fetchResPCats['domainCode'];
            $salePriceArr = $this->db->select("tbl_fees",array("*"),array('listingTypeId'=>$saleTypeId,'classifiedOrauction'=> 'a'))->result();
            $this->salePrice = $salePriceArr['price'];

        // printr($fetchResPCats,1);    
        //get all the listing category for website
        $website_url = ($this->saleTypeId=='4')?displayAppUrl($this->website_url):displaySiteUrl($this->website_url);
        $hide_website_live = $hideFromAuction = $hide_from_starter = '';
        if($this->saleType == 'c')
        {
            $hide_website_live = 'hide';
            $sale_type = 'Classified';
        }
        else
        {
            $hideFromAuction = 'hide';
            $sale_type = 'Auction';
        }

        $next_traffic_link = '#traffic';
        $next_traffic_id = 'traffic';
        if($this->saleTypeId == 2)
        {
            $hide_from_starter = 'hide';
            $next_traffic_link = '#pitch';
            $next_traffic_id = 'pitch';
        }
        //code for edit listing -start
        $action=(isset($_REQUEST['action']) && $_GET['action']!='')?$_GET['action'] : '';
        $data=array();
        $qrySel=$this->db->pdoQuery("SELECT * from tbl_listing where listingId = $this->id")->result();
        extract($qrySel);
        $grossRevenue = (!empty($grossRevenue))?(array)(json_decode($grossRevenue)):'';
        $netRevenue = (!empty($netRevenue))?(array)(json_decode($netRevenue)):'';
        $total=(($sellerAmount!='') && ($sellerAmount > 0))?$sellerAmount:$this->salePrice;
        $disable_verify=($isListingVerified=='y')?"disabled":'';
        $attachments=($attachments!='')?explode(",",$attachments):'';
        $htmlPCat = $monthYearhtml =$publish_month = $publish_year= $htmlPNiche= $htmlPlatform =$htmlMon =$htmlFees =$attachment_data='';
        //$image_extension=array('JPG','JPEG','PNG','GIF','PJPEG');
        if($attachments!=''){
            for($i=0;$i<count($attachments);$i++){
                //$extension= pathinfo($data['attachments'][$i], PATHINFO_EXTENSION);
                $attachment_data .= '<tr id="row_'.$i.'">';
                $attachment_data.='<td><a href="' . SITE_UPD.'product/' . $attachments[$i] . '">'.split("--",$attachments[$i])[1].'</a></td><td align="center"><button class="btn  btn-circle btn-danger attachment_class" id="'.$i.'" type="button" data-name="'.$attachments[$i].'"><i class="fa fa-times"></i></button></td>';
                $attachment_data .= '</tr>';
            }
        }
        //get all the listing category for website
        $fetchResPCats = $this->db->select("tbl_listing_category",array("*"),array("isactive"=>'y','parent_id'=>0))->results();
        foreach($fetchResPCats as $fetchResPCat)
        {
            $htmlPCat .= '<div class="col-md-6 selling_website_type">';
            $html_par_cat = new MainTemplater(DIR_ADMIN_TMPL . "{$this->module}/parent_category-sd.skd");
            $html_par_cat = $html_par_cat->compile();           
            $condition=($catId == $fetchResPCat['id'])?"checked":"";
            $isDeleted = ($fetchResPCat['isDeleted'] == 'y')?'disabled':'';            
            $fieldArrCat = array("%PARENT_CAT_NAME%","%PARENT_CAT_ID%","%SELECTED%","%ID_DISABLED%");
            $replaceArrCat = array($fetchResPCat['categoryName'],$fetchResPCat['id'],$condition,$isDeleted);
            $htmlPCat .= str_replace($fieldArrCat, $replaceArrCat, $html_par_cat);          
            $htmlPCat .= '</div>';      
        }

        //get  all the subcategory of listing for website

        $get_sub_cat = $this->db->pdoQuery("SELECT * FROM tbl_listing_category WHERE isActive = 'y' AND parent_id =".$catId)->results();

         $all_sub_cat = "";
         $sub_cat_array=($subCatId!='')?explode(",",$subCatId):array(0);
        foreach ($get_sub_cat as $value) 
        {
            $isDeleted = ($value['isDeleted'] == 'y')?'disabled':'';
            $condition=(in_array($value['id'], $sub_cat_array))?"selected":"";
            $all_sub_cat .= "<option value='".$value['id']."' ".$condition." ".$isDeleted.">".$value['categoryName']."</option>";           
        }

        //get all listing niches for website
        $fetchResPNiches = ($this->listingTypeId=='4')?$this->db->select("tbl_listing_niche",array("*"),array("isactive"=>'y','parent_id'=>0,'listType'=>'app'))->results():$this->db->select("tbl_listing_niche",array("*"),array("isactive"=>'y','parent_id'=>0,'listType'=>'website'))->results();
        foreach($fetchResPNiches as $fetchResPNiche)
        {
            $htmlPNiche .= '<div class="col-md-6">';
            $html_par_niche = new MainTemplater(DIR_ADMIN_TMPL . "{$this->module}/parent_niche-sd.skd");
            $html_par_niche = $html_par_niche->compile();           
            $condition=($nicheId == $fetchResPNiche['id'])?"selected":"";
            $isDeleted = ($fetchResPNiche['isDeleted'] == 'y')?'disabled':'';
            $fieldArrNiche = array("%PARENT_CAT_NAME%","%PARENT_CAT_ID%","%SELECTED%","%IS_DISABLED%");
            $replaceArrNiche = array($fetchResPNiche['nicheName'],$fetchResPNiche['id'],$condition,$isDeleted);
            $htmlPNiche .= str_replace($fieldArrNiche, $replaceArrNiche, $html_par_niche);          
            $htmlPNiche .= '</div>';
        }

        //get  all the sub niche of listing for website

        $fetchResCNiches = ($this->listingTypeId=='4')?$this->db->select("tbl_listing_niche",array("*"),array("isactive"=>'y','parent_id'=>$nicheId,"listType" => 'app'))->results():$this->db->select("tbl_listing_niche",array("*"),array("isactive"=>'y','parent_id'=>$nicheId,"listType" => 'website'))->results();

         $all_sub_niche = "";
         $sub_niche_array=($subNicheId!='')?explode(",",$subNicheId):array(0);
        foreach ($fetchResCNiches as $value) 
        {
            $isDeleted = ($value['isDeleted'] == 'y')?'disabled':'';
            $condition=(in_array($value['id'], $sub_niche_array))?"selected":"";
            $all_sub_niche .= "<option value='".$value['id']."' ".$condition." ".$isDeleted.">".$value['nicheName']."</option>";           
        }

        //get platforms for website
        $fetchResPlatforms = $this->db->select("tbl_listing_platform",array("*"),array("isactive"=>'y'))->results();
        foreach($fetchResPlatforms as $fetchResPlatform)
        {       
            $htmlPlatform .= '<div class="col-md-6">';
            $html_par_plat = new MainTemplater(DIR_ADMIN_TMPL . "{$this->module}/listing_platform-sd.skd");
            $html_par_plat = $html_par_plat->compile();         
            $condition=($listingPlatform == $fetchResPlatform['id'])?"selected":"";
            $isDeleted = ($fetchResPlatform['isDeleted'] == 'y')?'disabled':'';
            $fieldArrPlatform = array("%PLATFORM_NAME%","%PLATFORM_ID%","%SELECTED%","%IS_DISABLED%");
            $replaceArrPlatform = array($fetchResPlatform['platformName'],$fetchResPlatform['id'],$condition,$isDeleted);
            $htmlPlatform .= str_replace($fieldArrPlatform, $replaceArrPlatform, $html_par_plat);           
            $htmlPlatform .= '</div>';
        }

        //publish year and publish month
        $publish_get_month=(($liveDate!='0000-00-00') && ($liveDate!=''))?(int)(date('n', strtotime($liveDate))):0;
        $publish_get_year=(($liveDate!='0000-00-00') && ($liveDate!=''))?(int)date('Y',strtotime($liveDate)):0;
        $month=(int)(date('n'));
        $year=(int)(date('Y'));
        for ($m=1; $m<=12; $m++) {
            $checked=($publish_get_month==0)?(($m ==$month)?'selected':''):(($publish_get_month==$m)?"selected" :'');
            $publish_month .= '  <option value="' . $m . '" '.$checked.'>' . date('F', mktime(0,0,0,$m))  . '</option >' . PHP_EOL;
        }

        // Publised year
        $cutoff = 1990;        
        $now = (int)date('Y');
        for ($y=$cutoff; $y<=$now; $y++) {
           $checked=($publish_get_year==0)?(($now==$y)?'selected':''):(($publish_get_year==$y)?"selected" : '');
           $publish_year .=  '  <option value="' . $y . '" '.$checked.'>' . $y . '</option>' . PHP_EOL;
        }
        //month and year
        $array_keys_gross=($grossRevenue!='')?array_keys($grossRevenue):'';
        $array_values_gross=($grossRevenue!='')?array_values($grossRevenue):'';
        $array_values_net=($netRevenue!='')?array_values($netRevenue):'';
        $limit=($array_keys_gross=='')?12:count($array_keys_gross);
        for($k=1,$i=0; $k<=$limit; $k++,$i++)
        {
            $monthYear = ($array_keys_gross=='')?date('M Y :', strtotime('-'.$k.' month')):date('M Y',strtotime($array_keys_gross[$i]));
            $monthYearKey = ($array_keys_gross=='')?date('m/d/Y', strtotime('-'.$k.' month')):date('m/d/Y',strtotime($array_keys_gross[$i])); // previous month
            $value_gross=($array_values_gross=='')?'':$array_values_gross[$i];
            $value_net=($array_values_net=='')?'':$array_values_net[$i];
            $monthYearhtml.='<label class="col-md-2 col-sm-6" >'.$monthYear.'</label><div class="col-md-5 col-sm-12"><div class=" input-group col-md-9"><div class="input-group-addon">$</div><input type="text" name="grossRevenue['.$monthYearKey.']" class="form-control grossRevenueCls" value="'.$value_gross.'" id="gross_'.($i+1).'"></div></div><div class="col-md-5 col-sm-12"><div class=" input-group col-md-9"><div class="input-group-addon">$</div><input type="text" name="netRevenue['.$monthYearKey.']" class="form-control netRevenueCls"  value="'.$value_net.'" id="net_'.($i+1).'"></div></div><div class="col-md-10 col-md-offset-2"><span id="grossError_'.($i+1).'" class="red"></span></div>';
        }
        //get all monetization method for website
        $fetchResMons = $this->db->select("tbl_monetize_type",array("*"),array("isactive"=>'y'))->results();
        $mon=($monTypeId!='')?explode(",",$monTypeId):array(0);
        foreach($fetchResMons as $fetchResMon)
        {
            $htmlMon .= '<div class="col-md-6">';
            $checked=(in_array($fetchResMon['monTypeId'], $mon))?'checked': "";
            $htmlMon .= '<div class="checkbox"><label><input type="checkbox" name="monetizeType[]" class="monetizeType" data_value="'.$fetchResMon['monTypeName'].'" value="'.$fetchResMon['monTypeId'].'"'. $checked.'>&nbsp;'.$fetchResMon['monTypeName'].'</label></div>';           
            $htmlMon .= '</div>';
        }

        //get fees for website
        $fetchResFees = $this->db->select("tbl_fees",array("*"),array("isActive"=>'y','listingTypeId'=>$this->listingTypeId,'classifiedOrauction'=>'n'))->results();
        $fees_extra=($extra_fees!='')?explode(",",$extra_fees):array(0);
        $checked_disabled=($action=='edit')?'disabled':'';
        foreach($fetchResFees as $fetchResFee)
        {
            $fees_check=(in_array($fetchResFee['feesId'], $fees_extra))?"checked":"";
            $htmlFees .= '<div class="media"><div class="pull-right">$'.$fetchResFee["price"].'</div>';
            $htmlFees .= '<div class="pull-left"><input type="checkbox" data-ng-model="rememberMe" class="pull-right mycheckbox feesCheckbox" price-id="'.$fetchResFee["price"].'" value="'.$fetchResFee["feesId"].'" name="extra_fees[]" '.$fees_check."  ".$checked_disabled. ' id="'.$fetchResFee["feesId"].'"></div>';
            $htmlFees .= '<div class="media-body"><label for="'.$fetchResFee["feesId"].'">'.$fetchResFee["feesType"].'</label></div>';
            $htmlFees .= '</div>';      
        } 

        $revenue_y=($isRevenueGenerate=='y')?'checked':'';
        $revenue_n=($isRevenueGenerate=='n')?'checked':'';
        $traffic_y=($isTrafficRecieved=='y')?'':'checked';
        $domain_include_y=($domain_include=='y')?'checked':'';
        $domain_include_n=($domain_include=='n')?'checked':'';
        $static_a = ($isActive == 'y' ? 'checked' : '');
        $static_d = ($isActive != 'y' ? 'checked' : '');
        $editor_check = ($editor_choice == 'y' ? 'checked' : '');
        $revenue_table = ($isRevenueGenerate == 'y' ? '' : 'display:none;');
        $monetization_div = ($isRevenueGenerate == 'y' ? '' : 'display:none;');
        $disable_google_analytic=($isTrafficRecieved=="y")?"":"disabled";
        $display_sub_cat=($catId==0 || $all_sub_cat=='')? "display:none;":"display:block;";
        $display_sub_niche=($nicheId==0 || $all_sub_niche=='')? "display:none;":"display:block;";
        $display_upload=($attachments!='')? "":"display:none;";
        $checkout_button=($action=='edit')?'Update':'Checkout';
        $activeTrafficTab = $activeWebsiteTab = '';
        $disabled_fields=($action=='edit')?'disabled':'';
        $hide_android=(strpos($listingUrl, 'google') !== false)?'hide':'';
        $hide_ios=(strpos($listingUrl, 'google') == false)?'hide':'';
        $androidcheck=($isAndroidApp=='y')?'checked':'';
        $ioscheck=($isIosApp=='y')?'checked':'';
        if($this->tab == 'traffic')
            $activeTrafficTab= 'active';
        else
            $activeWebsiteTab = 'active';
        //code for edit listing -end

        if($reservePrice == 0)
            $reservePrice = '';

        if($buyNowPrice == 0)
            $buyNowPrice = '';
        
        $fieldArr = array(
            "%SALETYPEID%"=>$this->saleTypeId,
            "%WEBSITEURL%"=>$website_url,
            "%PARENT_CATEGORY%"=>$htmlPCat,
            "%PREVIOUS_MONTH_YEAR%"=>$monthYearhtml,
            "%MONETIZE_TYPES%"=>$htmlMon,
            "%EXTRA_FEES%"=>$htmlFees,
            "%TOTAL_PRICE%"=>$total,
            '%LAST_INSERTED_ID%'=>$this->id,
            "%PARENT_NICHE%"=>$htmlPNiche,
            '%hideFromClasified%'=>$hide_website_live,
            '%hideFromAuction%'=>$hideFromAuction,
            '%HIDE_FORM_STARTER%'=>$hide_from_starter,
            '%LISTING_PLATFORM%'=>$htmlPlatform,
            '%META_TAG_CODE%'=>$this->domainCode,
            '%NEXT_TRAFFIC_LINK%'=>$next_traffic_link,
            '%NEXT_TRAFFIC_ID%'=>$next_traffic_id,
            '%SELL_TYPE%'=>$sale_type,
            '%TRAFFIC_Y%'=>$traffic_y,
            '%DISABLE_GOOGLE_ANALYTICS%'=>$disable_google_analytic,
            '%PUBLISH_MONTH%'=>$publish_month,
            '%PUBLISH_YEAR%'=>$publish_year,
            '%REVENUE_Y%'=>$revenue_y,
            '%REVENUE_N%'=>$revenue_n,
            '%TAGLINE%'=>trim($tagline),
            '%SUMMARY%'=>trim($summary),
            "%DESCRIPTION%"=>filterscriptTags($description),
            "%DURATION_TIME%"=>$listDurationDate,
            '%MINIMUM_PRICE%'=>$minimumOffer,
            '%RESERVE_PRICE%'=>$reservePrice,
            '%BUYNOW_PRICE%'=>$buyNowPrice,
            '%DISABLE%'=>$disable_verify,
            '%ALL_SUB_CATEGORY%'=>$all_sub_cat,
            "%ACTION%"=>$action,
            "%DISPLAY_CHILD_CATEGORY%"=>$display_sub_cat,
            '%DISPLAY_CHILD_NICHE%'=>$display_sub_niche,
            "%ALL_SUB_NICHE%"=>$all_sub_niche,
            "%REVENUE_TABLE%"=>$revenue_table,
            "%MONETIZATION_DIV%"=>$monetization_div,
            "%DOMAIN_Y%"=>$domain_include_y,
            "%DOMAIN_N%"=>$domain_include_n,
            "%UPLOADED_IMAGES%"=>$attachment_data,
            "%UPLOAD_DISPLAY%"=>$display_upload,
            "%TYPE%"=>$this->type,
            "%EDITOR_Y%"=>$editor_check,
            '%ACTIVE_TRAFFIC_TAB%'=>$activeTrafficTab,
            '%ACTIVE_WEBSITE_TAB%'=>$activeWebsiteTab,
            "%DISABLE_FIELDS%"=>$disabled_fields,
            "%DISCLAUSER%"=> filterscriptTags($disclosureAgreement),
            "%HIDE_IOS%"=>$hide_ios,
            "%HIDE_ANDROID%"=>$hide_android,
            "%IOS_CHECK%"=>$ioscheck,
            "%ANDROID_CHECK%"=>$androidcheck,
            "%AVERAGE_DOWNLOAD%" => $averageDownload
            );
        // printr($fieldArr,1);
        $replaceArr = array();        
        $html = str_replace(array_keys($fieldArr), array_values($fieldArr), $main_content);     
        return $html;
    }
    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        if (isset($chr) && $chr != '') {
            $whereCond.=" and (tl.listingUrl LIKE '%".$chr."%' OR tl.appName LIKE '%".$chr."%' OR  tbl_users.userName LIKE '%" . $chr . "%' or tbl_listing_type.listingTypeName LIKE '%".$chr."%')";
        }
        if(isset($filtering_type) && $filtering_type != 0){
            $whereCond.=" and tbl_listing_type.listingTypeId=".$filtering_type;
        }
        if(isset($status_type) && $status_type != ''){
            $whereCond.=($status_type=='b')?" and tl.buyNowPrice > 0":" and tl.saleType='".$status_type."'";
        }
        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'listingId DESC';

        $qrySel=$this->db->pdoQuery("SELECT tl.listingId,tl.editor_choice,tl.listingUrl,tl.isActive,tbl_listing_type.listingTypeName,tl.isActive,tl.listingTypeId,tl.userId,tbl_users.firstName,tbl_users.lastName,tbl_users.userName,tl.isDeleted,tl.appName,tbl_users.isDeleted As userDeleted from tbl_listing as tl join tbl_listing_type on (tbl_listing_type.listingTypeId=tl.listingTypeId) join tbl_users on (tbl_users.id=tl.userId) where tl.isActive!='d' and tl.isAdminApproved='approved' and tl.isPaid='y' $whereCond  ORDER BY $sorting limit $offset , $rows")->results();

        $totalRow=$this->db->pdoQuery("SELECT tl.listingId,tl.listingUrl,tl.isActive,tbl_listing_type.listingTypeName,tl.isActive,tl.listingTypeId,tl.userId,tbl_users.firstName,tbl_users.lastName,tbl_users.userName,tl.isDeleted,tl.appName,tbl_users.isDeleted As userDeleted from tbl_listing as tl join tbl_listing_type on (tbl_listing_type.listingTypeId=tl.listingTypeId) join tbl_users on (tbl_users.id=tl.userId) where tl.isActive!='d' and tl.isAdminApproved='approved' and tl.isPaid='y' $whereCond")->affectedRows();

        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
            $exp_choice = ($fetchRes['editor_choice'] == "y") ? "checked" : "";
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['listingId'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&listingTypeId=".$fetchRes['listingTypeId']."&id=" . $fetchRes['listingId'] . "","extraAtt" => "title = 'Edit'", "class" => "btn default black btnEdit", "value" => '<i class="fa fa-edit"></i>')) : '';
            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['listingId'] . "", "class" => "btn default blue btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';

            if($fetchRes['isDeleted'] == 'n')
            {
                $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['listingId'] . "", "class" => "btn default  red btn-delete","extraAtt" => "title = 'Delete'", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            }
            else
            {
                $operation .=(in_array('undo', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&id=" . $fetchRes['listingId'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-reply"></i>')) : '';
            }
            /* Expert Choice */
            $expert_choice = $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?choice_id=" . $fetchRes['listingId'] . "", "check" => $exp_choice));
           
            /* End Expert Choice */    

            $delete_status = ($fetchRes['isDeleted']=='y')?"<span class='label label-warning'>&nbsp;Listing deleted</span>":'';
            $user_status = ($fetchRes['userDeleted']=='y')?"<span class='label label-warning'>&nbsp;User deleted</span>":'';
            $final_array = array(
                filtering($fetchRes["listingId"]),
                filtering(ucfirst($fetchRes["userName"])).'<br>'.$user_status,
                filtering(($fetchRes['listingTypeId']=='4')?$fetchRes['appName']:displaySiteUrl($fetchRes["listingUrl"])).'<br>'.$delete_status,
                filtering($fetchRes["listingTypeName"]),
                $expert_choice
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
    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }
    public function contentSubmit($data,$Permission){        
        $response = array();
        $response['status'] = false;
        if(!empty($data))
        { 
            extract($data);
            $editor_choice=(isset($editor_choice) && $editor_choice=='y')?'y':'n';
            if($saleTypeId==1 || $saleTypeId==2){
                $subNicheIdVal  = $subCatIdVal = $grossRevenueJson = $netRevenueJson = $extra_fees_val = $monetizeTypeVal = '';
                $subCatIdVal=(!empty($childCat))?implode(",",$childCat):'';
                $subNicheIdVal=(!empty($childNiche))?implode(",",$childNiche):'';
                $liveDate = $websiteLiveYear.'-'.$websiteLiveMonth."-01";
                $isTrafficRecieved = (isset($isTrafficRecieved))?'n':'y';
                $isRevenueGenerate = (isset($isRevenueGenerate))?$isRevenueGenerate:'n';
                $domain_include = (isset($domain_include))?'y':'n';
                $grossRevenueJson=(!empty($grossRevenue) && $isRevenueGenerate=='y')?stripslashes(json_encode($grossRevenue)):'';
                $netRevenueJson=(!empty($netRevenue))?stripslashes(json_encode($netRevenue)):'';
                $monetizeTypeVal=(!empty($monetizeType))?implode(",",$monetizeType):'';
                $setValue = array(  
                                'catId'=>isset($parentCat)?$parentCat:0,
                                'subCatId'=>$subCatIdVal,
                                'nicheId'=>$parentNiche,
                                'subNicheId'=>$subNicheIdVal,
                                "liveDate"=>$liveDate,
                                'isTrafficRecieved'=>$isTrafficRecieved,
                                'isRevenueGenerate'=>$isRevenueGenerate,
                                'grossRevenue'=>$grossRevenueJson,
                                'netRevenue'=>$netRevenueJson,
                                'monTypeId'=>$monetizeTypeVal,
                                'monOther'=>$monOther,
                                'tagline'=>trim($tagline),
                                'summary'=>trim($summary),
                                'description'=>filterscriptTags($description),
                                'listDurationDate'=>$listDurationDate,
                                'disclosureAgreement'=>filterscriptTags($disclosureAgreement),
                                'sellerAmount'=>$finalPrice,
                                'listingPlatform'=>$listingPlatform,
                                'domain_include'=>$domain_include,
                                'editor_choice' => $editor_choice
                            ); 
            }else if($saleTypeId==3){
                $setValue = array(  
                            'tagline'=>trim($tagline),
                            'description'=>filterscriptTags($description),
                            'listDurationDate'=>$listDurationDate,
                            'disclosureAgreement'=>filterscriptTags($disclosureAgreement),
                            'sellerAmount'=>$finalPrice,
                            'editor_choice' => $editor_choice
                        ); 

            }
            else if($saleTypeId==4){
                $subNicheIdVal  = $grossRevenueJson = $netRevenueJson = $extra_fees_val ='';
                $subNicheIdVal=(!empty($childNiche))?implode(",",$childNiche):'';
                $liveDate = $websiteLiveYear.'-'.$websiteLiveMonth."-01";                
                $isRevenueGenerate = (isset($isRevenueGenerate))?$isRevenueGenerate:'n';
                $grossRevenueJson=(!empty($grossRevenue) && $isRevenueGenerate=='y')?stripslashes(json_encode($grossRevenue)):'';
                $netRevenueJson=(!empty($netRevenue))?stripslashes(json_encode($netRevenue)):'';
                $isIosApp=((isset($isIos) && $isIos=='ios') || (strpos($website_url, 'google') == false))?'y':'n';
                $isAndroidApp=((isset($isAndroid) && $isAndroid=='android') || (strpos($website_url, 'google') !== false))?'y':'n';
                $setValue = array(
                                'nicheId'=>$parentNiche,
                                'subNicheId'=>$subNicheIdVal,
                                "liveDate"=>$liveDate,
                                'isRevenueGenerate'=>$isRevenueGenerate,
                                'grossRevenue'=>$grossRevenueJson,
                                'netRevenue'=>$netRevenueJson,
                                'tagline'=>trim($tagline),
                                'description'=>filterscriptTags($description),
                                'listDurationDate'=>$listDurationDate,
                                'disclosureAgreement'=> filterscriptTags($disclosureAgreement),
                                'sellerAmount'=>$finalPrice,
                                'editor_choice' => $editor_choice,
                                'isIosApp' => $isIosApp,
                                'isAndroidApp' => $isAndroidApp
                            ); 
            }
            if ($type == 'edit' && $siteId > 0) {
                if (in_array('edit', $Permission)) {
                    $this->db->update($this->table, $setValue, array("listingId" => $siteId));

                    $activity_array = array("id" => $this->id, "module" => $this->module, "activity" => $type);
                    add_admin_activity($activity_array);

                    $response['status'] = true;
                    $response['success'] = "Listing updated successfully";
                    echo json_encode($response);
                    exit;
                } else {
                    $response['error'] = "You don't have permission to edit Listing";
                    echo json_encode($response);
                    exit;
                }
            } 
        }
    }
}
