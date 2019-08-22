<?php
class Home {
	public $last_page;
	function __construct($module = "", $id = 0, $token = "",$last_page="") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
		$this->last_page = $last_page;
	}
	public function getHeaderContent($last_page="") {
		$html = new MainTemplater(DIR_TMPL . "header-sd.skd");
		$html = $html->compile();
		$menu = NULL;
		$count = 0;

		$Hmenu_item = $menuStatic = "" ;
		$Habout_static = "";

		$languages = $this->db->pdoQuery("select * from tbl_language where isActive = 'y' ")->results();
		$l_content = '';

		foreach ($languages as $key => $value) {
			$cl = !empty($_SESSION['lang_key']) ? $_SESSION['lang_key'] : '';
			$select = $value['id'] == $cl ? 'selected="selected"' : ''; 
			$l_content .= '<option value="'.$value['id'].'" '.$select.' > '.$value['language'].' </option> ';
		}

		if(isset($this->sessUserId) && $this->sessUserType == 'Customer')
		{
			$notification_detail = $this->db->pdoQuery("select * from tbl_notification where notificationType=? and userId=? and isRead=?",array("c",$this->sessUserId,'n'))->affectedRows();
			$noti_bell = ($notification_detail>0) ? '' : 'hide';

			$menu = new MainTemplater(DIR_TMPL . "/" . "header_customer-sd.skd");
			$menu = $menu->compile();

			$userImage = getUserImage($this->sessUserId);
			$pmb_link = (pmbLink($this->sessUserId) > 0) ? '' : 'hide';
			return str_replace(array('%NOTIFICATION_LIST%',"%GET_PMB_MESSAGES%","%USERNAME%","%USER_IMG%","%NOTI_BELL%","%PMB_LINK%",'%L_CONTENT%'), array($this->getNotifications(),$this->getPmbMessages(),$this->sessUserName,$userImage,$noti_bell,$pmb_link,$l_content), $menu);
		}
		else if(isset($this->sessUserId) && $this->sessUserType == 'Freelancer')
		{
			$notification_detail = $this->db->pdoQuery("select * 	from tbl_notification where notificationType=? and userId=? and isRead=?",array("f",$this->sessUserId,'n'))->affectedRows();
			$noti_bell = ($notification_detail>0) ? '' : 'hide';

			$menu = new MainTemplater(DIR_TMPL . "/" . "header_freelancer-sd.skd");
			$menu = $menu->compile();

			$userImage = getUserImage($this->sessUserId);
			$pmb_link = (pmbLink($this->sessUserId) > 0) ? '' : 'hide';
			return str_replace(array('%NOTIFICATION_LIST%',"%GET_PMB_MESSAGES%","%USERNAME%","%USER_IMG%","%NOTI_BELL%","%PMB_LINK%",'%L_CONTENT%'), array($this->getNotifications(),$this->getPmbMessages(),$this->sessUserName,$userImage,$noti_bell,$pmb_link,$l_content), $menu);
		}
		else
		{
			$menu = new MainTemplater(DIR_TMPL . "/" . "header-sd.skd");
			$menu = $menu->compile();
			return str_replace(array("%POST_SERVICE%","",'%L_CONTENT%'), array("","",$l_content), $menu);
		}
		return $menu;

	}

	public function getNotifications(){
		$content = '';
		if(!empty($this->sessUserId)){
			$menu = new MainTemplater(DIR_TMPL . "/" . "notification_list-sd.skd");
			$menu = $menu->compile();
			$nlist = $this->db->pdoQuery("SELECT * FROM `tbl_notification` WHERE notificationType != 'a' and userId = ? order By createdDate desc limit 0, 5 ",[$this->sessUserId])->results();
			foreach ($nlist as $key => $value) {
				$arr = [
					'%ID%' => $value['id'],
					'%USERID%' => $value['userId'],
					'%AVTAR%' => getAvatar($value['message']),
					'%MESSAGE%' => $value['message'],
					'%UNREAD%' => $value['isRead'] == 'n' ? 'unread' : '',
					'%DETAIL_LINK%' => $value['detail_link'],
					'%ISREAD%' => $value['isRead'],
					'%NOTIFICATIONTYPE%' => $value['notificationType'],
					'%CREATEDDATE%' => date(DATE_FORMAT. " H:i a",strtotime( $value['createdDate']))
				];
				$content .= str_replace(array_keys($arr), array_values($arr), $menu);
			}
		}
		return $content;
	}
	public function getPmbMessages(){
		$content = '';
		if(!empty($this->sessUserId)){
			$menu = new MainTemplater(DIR_TMPL . "/" . "pmb_messages_list-sd.skd");
			$menu = $menu->compile();
			$message = $this->db->pdoQuery("select p.*,CONCAT(u.firstName,' ',u.lastName) as user_name,u.profileImg from tbl_pmb as p JOIN tbl_users as u ON(p.senderId=u.id) where p.ReceiverId=".$this->sessUserId." AND (NOT FIND_IN_SET(".$this->sessUserId.",p.deleteUser)) ORDER BY p.id DESC LIMIT 5")->results();
			if(!empty($message)){
				foreach ($message as $key => $value) {
					$user_img = ($value['profileImg']=='') ? SITE_UPD."default-image_450.png" : SITE_USER_PROFILE.$value['profileImg'];
					$arr = array(
						"%ID%" => 0,
						"%USER_IMAGE%" => $user_img,
						"%USER_NAME%" => $value['user_name'],
						"%MESSAGE%" => $value['message'],
						"%DATE%" =>  date(DATE_FORMAT,strtotime($value['createdDate'])),
						"%TIME%" =>  date(" H:i a",strtotime($value['createdDate'])),
					);
					$content .= str_replace(array_keys($arr), array_values($arr), $menu);
				}
			}
		}
		return $content;
	}


	public function getFooterContent() {
		$html = new MainTemplater(DIR_TMPL . "footer-sd.skd");
		$html = $html->compile();
		$menu = null;

		$menu_item = "";
		$about_static = "";

		$pages = $this->db->pdoQuery("select * from tbl_content where isActive = 'y' ")->results();
		$languages = $this->db->pdoQuery("select * from tbl_language where isActive = 'y' ")->results();
		$l_content = '';

		foreach ($languages as $key => $value) {
			$cl = !empty($_SESSION['lang_key']) ? $_SESSION['lang_key'] : '';
			$select = $value['id'] == $cl ? 'selected="selected"' : ''; 
			$l_content .= '<option value="'.$value['id'].'" '.$select.' > '.$value['language'].' </option> ';
		}
		$content = '';
		foreach ($pages as $page)
		{
			$sub_menu = new MainTemplater(DIR_TMPL . "footer_loop-sd.skd");
			$sub_menu = $sub_menu->compile();
			$fields = array('%PAGE_URL%','%PAGE_TITLE%');
			$menu_item = array(SITE_URL.'content/'.$page['page_slug'].'/',$page[l_values('pageTitle')]);
			$content .= str_replace($fields,$menu_item,$sub_menu);
		}
				
		$year = date('Y');
		$content_outer = str_replace(array("%FOOTER_LOOP%","%YEAR%",'%L_CONTENT%'), array($content,$year,$l_content), $html);
		return $content_outer;
	}

	public function categories(){

		$content = "";
		$lang_key = $_SESSION["lang_key"];
		$categories = $this->db->pdoQuery("select * from tbl_category where id>0 LIMIT 11")->results();
		$i=0;
		if(!empty($categories)){
			foreach ($categories as $value) {
				$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/categories-sd.skd");
				$sub_content = $sub_content->compile();
				
				$name = !empty($lang_key)?$value["category_name_".$lang_key]:$value["category_name"];
				$image = SITE_CATEGORY_IMAGE.$value['category_image'];
				$color_array = array("lgreen","orange","purple","green","pink","navyblue","blue");
				// $link = SITE_URL.'search/freelancer/?category='.urlencode($value[l_values('category_name')]);
				$link = 'search/sub-category/'.$value[l_values('id')];
				$arr = array(
					"%CAT_NAME%" => $name,
					"%CAT_IMAGE%" => $image,
					"%CAT_CLASS%" => $color_array[$i],
					"%CAT_LINK%" => $link,
				);
				$content .= str_replace(array_keys($arr), array_values($arr), $sub_content);
				$i++;
			}
			return $content;
		}

	}

	public function guidelines(){	
		$content = $data = "";
		$lang_key = $_SESSION["lang_key"];
		$slider_detail = $this->db->pdoQuery("select * from tbl_how_it_work where id>0")->results();
			
		if(!empty($slider_detail)){
			$i=1;
			foreach ($slider_detail as $value) {
				$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/guidelines-sd.skd");
				$sub_content = $sub_content->compile();
				$image = SITE_HOWIT.$value['icon'];
				$name = !empty($lang_key)?$value["title_".$lang_key]:$value["title"];
				$description = !empty($lang_key)?$value["content_".$lang_key]:$value["content"];	
				$data = '<div class="guidline-img">
							<img src="'.$image.'" alt="">
						</div>
						<div class="service-caption">
							<h4 class="service-heading">'.$name.'</h4>
							<div class="service-desc">'.$description.'</div>
						</div>';

				// $data = '<div class="">
				// 				<div class="guidline-img">
				// 					<img src="'.$image.'" alt="">
				// 				</div>
				// 			</div>
				// 			<div class="">
				// 				<div class="service-caption">
				// 					<h4 class="service-heading">'.$name.'</h4>
				// 					<div class="service-desc">'.$description.'</div>
				// 				</div>
				// 			</div>';


				// if($i%2!=0){
				// 	$data = '<div class="media-left">
				// 				<div class="hiw-icon">
				// 					<img src="'.$image.'" alt="">
				// 				</div>
				// 			</div>
				// 			<div class="media-body">
				// 				<div class="hiw-content">
				// 					<h4 class="hiw-title">'.$name.'</h4>
				// 					<p>'.$description.'</p>
				// 				</div>
				// 			</div>';
				// }else{	
				// 	$data =	'<div class="media-left mobile-view">
				// 				<div class="hiw-icon">
				// 					<img src="'.$image.'" alt="">
				// 				</div>
				// 			</div>
				// 			<div class="media-body">
				// 				<div class="hiw-content">
				// 					<h4 class="hiw-title">'.$name.'</h4>
				// 					<p>'.$description.'</p>
				// 				</div>
				// 			</div>
				// 			<div class="media-right desktop-view">
				// 				<div class="hiw-icon">
				// 					<img src="'.$image.'" alt="">
				// 				</div>
				// 			</div>';
				// }
				$arr = array(
					"%WORK_DATA%" => $data,
				);
				$content .= str_replace(array_keys($arr), array_values($arr), $sub_content);
				$i++;
			}
			return $content;
		}
	}
	public function way_to_work_on_job(){	

		$content = "";
		$lang_key = $_SESSION["lang_key"];
		$slider_detail = $this->db->pdoQuery("select * from tbl_way_to_work where id>0")->results();	
		if(!empty($slider_detail)){
			$i=1;
			foreach ($slider_detail as $value) {
				$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/way_to_work_on_job-sd.skd");
				$sub_content = $sub_content->compile();
				$image = SITE_WORK_JOB.$value['icon'];
				$name = !empty($lang_key)?$value["title_".$lang_key]:$value["title"];
				$description = !empty($lang_key)?$value["content_".$lang_key]:$value["content"];
				$arr = array(
					"%JOB_NAME%" => $name,
					"%JOB_DESC%" => $description,
					"%JOB_IMAGE%" => $image,
				);
				$i++;
				$content .= str_replace(array_keys($arr), array_values($arr), $sub_content);
			}
			return $content;
		}
	}
	public function way_to_work_on_service(){	

		$content = "";
		$lang_key = $_SESSION["lang_key"];
		$slider_detail = $this->db->pdoQuery("select * from tbl_way_to_work_service where id>0")->results();
		if(!empty($slider_detail)){
			$i=1;
			foreach ($slider_detail as $value) {
				$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/way_to_work_on_service-sd.skd");
				$sub_content = $sub_content->compile();
				$name = !empty($lang_key)?$value["content_".$lang_key]:$value["content"];
				$image = SITE_WORK_SERVICE.$value['icon'];
				$arr = array(
					"%COUNTER%" => $i,
					"%SERVICE_NAME%" => $name,
					"%SERVICE_ICON%" => $image,
				);
				$i++;
				$content .= str_replace(array_keys($arr), array_values($arr), $sub_content);
			}
			return $content;
		}
	}
	public function topSkills(){
		$content = $data = "";
		$lang_key = $_SESSION["lang_key"];
		$skill_detail = $this->db->pdoQuery("select * from tbl_skills where id>0")->results();
		
		$skill_name = !empty($lang_key)?"skill_name_".$lang_key:"skill_name";
		if(!empty($skill_detail)){
			foreach ($skill_detail as $value) {
			$skill_url = ($pageType == 'customerPage') ? SITE_URL."search/freelancer/?skills=".base64_encode($value['id']) : SITE_URL."search/jobs/?skills=".base64_encode($value['id']);
				$data .= '<li>
							<a href="'.$skill_url.'">'.$value[$skill_name].'</a>
						</li>';
			}
			$content = $data;
		}
		return $content;
	}
	public function getPageContent()
	{
		if(isset($this->sessUserId) && $this->sessUserType == 'Customer')
		{
			$result = $this->customerHomePage();
		}
		else if(isset($this->sessUserId) && $this->sessUserType == 'Freelancer')
		{
			$result = $this->freelancerHomePage();
		}
		else
		{
			$result = $this->beforeLoginHomePage();
		}
		
		return $result;
	}
	
	public function customerHomePage()
	{
		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/customer_home-sd.skd");
		$sub_content = $sub_content->compile();

		$content_detail = $this->db->pdoQuery("select ".l_values('pageDesc')." as pageDesc from tbl_content where page_slug='how-it-works' ")->result();

		$services_count =  $this->db->pdoQuery("select * from tbl_services where isActive='y' and isApproved='a' and featured='y' and featured_payment_status='c' group by servicesCategory")->affectedRows();


		$home_array = array(
			"%TOP_HOMEPAGE_SECTION%" => $this->top_home_section(),
			"%HOME_CATEGORY_LIST%" => $this->home_category_list("list",11),
			"%LOAD_MORE_CAT_CLASS%" => ($this->home_category_list("total_record","all") > 11) ? '':'hide',
			"%SKILL_LIST%" => $this->skill_list('','customerPage'),
			"%SKILL_LIST_CLASS%" => $this->skill_list('total'),
			"%FEATURED_SERVICES%" => $this->featured_services(),
			"%FEATURED_SERVICES_CLASS%" => ($this->featured_services('check')=='') ? 'hide' : '',
			"%SERVICES_CLASS%" => ($services_count>2) ? '' : 'hide'
		);
		$result = str_replace(array_keys($home_array),array_values($home_array),$sub_content);
		return $result;
	}
	
	public function freelancerHomePage()
	{
		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_home-sd.skd");
		$sub_content = $sub_content->compile();

		$job_details = $this->db->pdoQuery("select * from tbl_jobs As j
			LEFT JOIN tbl_category As c ON c.id = j.jobCategory
			where j.isActive='y' and j.isApproved='a' and j.featured='y' group by j.jobCategory")->affectedRows();

		$content_detail = $this->db->pdoQuery("select ".l_values('pageDesc')." as pageDesc  from tbl_content where page_slug='how-it-works' ")->result();
		$home_array = array(
			"%TOP_HOMEPAGE_SECTION%" => $this->top_home_section(),
			"%HOME_CATEGORY_LIST%" => $this->home_category_list(),
			"%HOW_IT_WORKS%" => $content_detail['pageDesc'],
			"%SKILL_LIST%" => $this->skill_list(),
			"%SKILL_LIST_CLASS%" => $this->skill_list('total'),
			"%FEATURED_JOB_LIST%" => $this->featured_job_list(),
			"%FEATURED_JOB_CLASS%" =>($this->featured_job_list('count')==0) ? 'hide' : '',
			"%JOB_CLASS%" => ($job_details>4) ? '' : 'hide'
		);
		$result = str_replace(array_keys($home_array),array_values($home_array),$sub_content);
		return $result;
	}
	
	public function beforeLoginHomePage()
	{
		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/home-sd.skd");
		$sub_content = $sub_content->compile();

		$content_detail = $this->db->pdoQuery("select ".l_values('pageDesc')."  as pageDesc from tbl_content where page_slug='how-it-works' ")->result();
		$search_res = $this->db->pdoQuery("select ".l_values('before_login_title')." as name, ".l_values('before_login_content')." as description from tbl_search_section WHERE id>0 LIMIT 1")->result();
		$hire_res = $this->db->pdoQuery("select ".l_values('title')." as name, ".l_values('content')." as description from tbl_hire_section WHERE id>0 LIMIT 2")->results();
		$app_res = $this->db->pdoQuery("select ".l_values('tag_line')." as tag, ".l_values('title')." as name, ".l_values('content')." as description from tbl_download_app WHERE id>0 LIMIT 1")->result();
		
		$home_array = array(
			"%TOP_HOMEPAGE_SECTION%" => $this->top_home_section(),
			"%HOME_CATEGORY_LIST%" => $this->home_category_list("list",11,'freelancer'),
			"%LOAD_MORE_CAT_CLASS%" => ($this->home_category_list("total_record","all") > 11) ? '':'hide',
			"%HOW_IT_WORKS%" => $content_detail['pageDesc'],
			"%HOW_IT_WORKS_CLASS%" => ($content_detail['pageDesc']=='') ? 'hide' : '',
			"%SKILL_LIST%" => $this->skill_list(),
			"%SKILL_LIST_CLASS%" => $this->skill_list('total'),
			"%SEARCH_TIITLE%" => $search_res["name"],
			"%SEARCH_DESCRIPTION%" => $search_res["description"],
			"%CATEGORIES%" => $this->categories(),
			"%GUIDELINES%" => $this->guidelines(),
			"%WORK_ON_JOB%" => $this->way_to_work_on_job(),
			"%WORK_ON_SERVICE%" => $this->way_to_work_on_service(),
			"%HIRE_FREELANCER%" => $hire_res[0]["name"],
			"%HIRE_FREELANCER_DESC%" => $hire_res[0]["description"],
			"%ONLINE_WORK%" => $hire_res[1]["name"],
			"%ONLINE_WORK_DESC%" => $hire_res[1]["description"],
			"%TAG_LINE%" => $app_res["tag"],
			"%DOWNLOAD_APP_TITLE%" => $app_res["name"],
			"%DOWNLOAD_APP_DESC%" => $app_res["description"],
			"%TOP_SKILLS%" => $this->topSkills(),
		);
		$result = str_replace(array_keys($home_array),array_values($home_array),$sub_content);
		return $result;
	}

	public function top_home_section()
	{
		global $db;
		$content = new MainTemplater(DIR_TMPL . $this->module . "/slider_images-sd.skd");
		$content = $content->compile();

		$res = $db->pdoQuery("SELECT * FROM tbl_search_section WHERE id>0")->result();

		$slider_content = $res["before_login_content"];
		$slider_heading = $res["before_login_title"];
		$input_lable = BEFORE_LOGIN_SEARCH_INPUT_LABEL;
		$slider_image = SITE_WORK_SERVICE.$res["file_name"];
		$first_data_url = "Jobs";
		if(!empty($_SESSION['pickgeeks_userId'])){
			if($_SESSION['pickgeeks_userType'] == 'Customer'){
				$slider_content = $res["customer_login_content"];
				$slider_heading = $res["customer_login_title"];
				$input_lable = CUSTOMER_SEARCH_INPUT_LABEL;
				$first_data_url	= "Services";
			}elseif($_SESSION['pickgeeks_userType'] == 'Freelancer'){
				$slider_content = $res["freelancer_login_content"];
				$slider_heading = $res["freelancer_login_title"];
				$input_lable = FREELANCER_SEARCH_INPUT_LABEL;
				$first_data_url	= "Jobs";			
			}
		}
		$array = array(
			"%USERTYPE%" => $_SESSION['pickgeeks_userType'] == 'Freelancer',
			"%SLIDER_IMAGE%" => $slider_image,
			"%SLIDER_CONTENT%" => $slider_content,
			"%SLIDER_HEADING%" => $slider_heading,
			"%INPUT_LABLE%" => $input_lable,
			"%FIRST_DATA_URL%" => $first_data_url,
			"%USER_TYPE%" => $_SESSION["pickgeeks_userType"]
		);
		$result = str_replace(array_keys($array), array_values($array), $content);
		return $result;

		// %SLIDER_IMAGE%

		/*$slider_detail = $this->db->pdoQuery("select * from tbl_slider where isActive='y' ")->affectedRows();

		if($slider_detail>0)
		{
			$top_section_detail = $this->db->pdoQuery("select * from tbl_slider where slider_type='v' and isActive='y' ");

			$top_video_detail = $top_section_detail->affectedRows();

			if($top_video_detail>0)
			{
				$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/home_video-sd.skd");
				$sub_content = $sub_content->compile();
				$top_section = $top_section_detail->result();
				$array = array(
					"%SRC%" => SITE_SLIDER_IMAGE.$top_section['file_name'],
					"%SEARCH_SECTION%" => $this->search_section()
				);
				$result = str_replace(array_keys($array), array_values($array), $sub_content);
			}
			else
			{

				$content = new MainTemplater(DIR_TMPL . $this->module . "/slider_images-sd.skd");
				$content = $content->compile();
				$i=1;$j=0;$loopData = $indicatorloopData = '';
				$image_qury = $this->db->pdoQuery("select * from tbl_slider where slider_type='i' and isActive='y' ");
				$image_slider = $image_qury->results();
				$image_data = $image_qury->affectedRows();
		       	//print_r($image_slider);
  				//die;

				foreach ($image_slider as $value)
				{
					$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/slider_loop-sd.skd");
					$sub_content = $sub_content->compile();
					$active_class = ($i==1) ? 'active' : '';
					$array = array(
						"%ACTIVE_CLASS%" => ($i==1) ? 'active' : '',
						"%NO%" => $i,
						"%IMAGE%" => SITE_SLIDER_IMAGE.$value['file_name'],
					);
					$loopData .= str_replace(array_keys($array), array_values($array), $sub_content);

					$sub_content1 = new MainTemplater(DIR_TMPL . $this->module . "/slider_indicator_loop-sd.skd");
					$sub_content1 = $sub_content1->compile();
					$active_class = ($i==1) ? 'active' : '';
					$array1 = array(
						"%NO%" => $j,
						"%CLASS%" => $active_class
					);
					$indicatorloopData .= str_replace(array_keys($array1), array_values($array1), $sub_content1);
					$i++;$j++;
				}
				$left_right_class = ($image_data>1) ? '' : 'hide';
				$result = str_replace(array("%SEARCH_SECTION%","%SLIDER_LOOP%","%LEFT_RIGHT_CLASS%","%INDICATOR_LOOP_DATA%"), array($this->search_section(),$loopData,$left_right_class,$indicatorloopData), $content);
			}
	       	//echo $result;
	       	//die;

		}
		else
		{
			$content = new MainTemplater(DIR_TMPL . $this->module . "/slider_images-sd.skd");
			$content = $content->compile();


			$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/slider_loop-sd.skd");
			$sub_content = $sub_content->compile();

			$array = array(
				"%ACTIVE_CLASS%" => 'active',
				"%NO%" => 1,
				"%IMAGE%" => SITE_UPD."Default_banner.jpeg",
			);
			$loopData = str_replace(array_keys($array), array_values($array), $sub_content);


			$result = str_replace(array("%SEARCH_SECTION%","%SLIDER_LOOP%","%LEFT_RIGHT_CLASS%","%INDICATOR_LOOP_DATA%"), array($this->search_section(),$loopData,'hide',''), $content);
		}*/

		

	}
	
	public function search_section()
	{
		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/search_section-sd.skd");
		$sub_content = $sub_content->compile();

		$slider_content = SLIDER_DESC;
		$slider_heading = SLIDER_HEADING;

		if(!empty($_SESSION['pickgeeks_userId'])){
			if($_SESSION['pickgeeks_userType'] == 'Customer'){
				$slider_content = CUSTOMER_SLIDER_DESC;
				$slider_heading = CUSTOMER_SLIDER_HEADING;
			}else{
				$slider_content = FREELANCER_SLIDER_DESC;
				$slider_heading = FREELANCER_SLIDER_HEADING;
			}
		}
		if(isset($this->sessUserId) && $this->sessUserId>0)
		{
			if($this->sessUserType=='Customer')
			{
				$array = array(
					"%SLIDER_CONTENT%" => $slider_content,
					"%SLIDER_HEADING%" => $slider_heading,
					"%JOB_CLASS%" => 'hide',
					"%FREELANCER_CLASS%" => '',
					"%SERVICE_CLASS%" => '',
					"%FIRST_LABEL%" => 'Freelancers',
					"%TEXT_PLACEHOLDER%" => 'Find Freelancers, Services (eg. Web Design)',
					"%FIRST_DATA_URL%" => 'Freelancers'
				);
			}
			else
			{
				$array = array(
					"%SLIDER_CONTENT%" => $slider_content,
					"%SLIDER_HEADING%" => $slider_heading,
					"%JOB_CLASS%" => '',
					"%FREELANCER_CLASS%" => 'hide',
					"%SERVICE_CLASS%" => 'hide',
					"%FIRST_LABEL%" => 'Jobs',
					"%TEXT_PLACEHOLDER%" => 'Find Jobs (eg. Web Design)',
					"%FIRST_DATA_URL%" => 'Jobs'
				);
			}
		}
		else
		{
			$array = array(
				"%SLIDER_CONTENT%" => $slider_content,
				"%SLIDER_HEADING%" => $slider_heading,
				"%JOB_CLASS%" => '',
				"%FREELANCER_CLASS%" => '',
				"%SERVICE_CLASS%" => '',
				"%FIRST_LABEL%" => 'Jobs',
				"%TEXT_PLACEHOLDER%" => 'Find Freelancers, Jobs or Services (eg. Web Design)',
				"%FIRST_DATA_URL%" => 'Jobs'
			);
		}
		$data = str_replace(array_keys($array), array_values($array), $sub_content);
		return $data;
	}
	
	public function featured_job_list($count='')
	{
		/*$query = $this->db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name from tbl_jobs As j
			LEFT JOIN tbl_category As c ON c.id = j.jobCategory
			LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
			where j.isActive='y' and j.isApproved='a' and j.featured='y' and j.featuredPayment='y' and j.jobStatus='p' and j.isDelete='n' ORDER BY j.id DESC limit ".HOME_FEATURE_JOB);*/
		$query = $this->db->pdoQuery("SELECT j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.profileImg, u.userSlug,u.userName,u.location
			FROM tbl_jobs As j
			LEFT JOIN tbl_category As c ON c.id = j.jobCategory
			LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
			JOIN tbl_users As u ON u.id = j.posterId
			where j.isActive='y' and j.isApproved='a' and j.featured='y' and j.featuredPayment='y' and j.jobStatus='p' and j.isDelete='n' ORDER BY j.id DESC limit ".HOME_FEATURE_JOB);
		if($count=='')
		{
			$content_detail = $query->results();
			$result = '';
			foreach ($content_detail as $value)
			{
				$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/featured_job_list-sd.skd");
				$sub_content = $sub_content->compile();
				$user_img = ($value['profileImg']=='') ? SITE_UPD."default-image_450.png" : SITE_USER_PROFILE.$value['profileImg'];
				$detail_link = SITE_URL."job/".$value['jobSlug'];
				$array = array(
					"%JOB_SLUG%" => $value['jobSlug'],
					"%JOB_TITLE%" => filtering($value['jobTitle']),
					"%BUDGET%" => $value['budget']." ",
					"%CUR_CODE%" => CURRENCY_CODE,
					"%DESCRIPTION%" => truncate_link(filtering($value['description']),80,$detail_link),
					"%SKILL_LIST%" => ($value['skills']!='') ? get_skill($value['skills']) : '',
					"%APPLICANTS%" => job_applicant($value['id']),
					"%POST_TIME%" => getTime($value['jobPostDate']),
					"%FEATURED_JOB%" => ($value['featured'] == 'y' && $value['featuredPayment'] == 'y') ? 'featured-tag' : 'hide',
					"%EXP_LVL%" => getJobExpLevel($value['expLevel']),
					"%EXP_LVL_TAG%" => ($value['expLevel'] == 'b') ? 'beginner-tag' : ($value['expLevel'] == 'p')?'pro-tag':'pro-tag',
					"%CATEGORY%" => $value['category_name'],
					"%SUBCATEGORY%" => $value['subcategory_name'],
					"%USER_SLUG%" => $value['userSlug'],
					"%SERVICE_SLUG%" => $value['servicesSlug'],
					"%SERVICE_IMG%" => $service_img[0],
					"%USER_SLUG%" => $value['userSlug'],
					"%USER_IMG%" => $user_img,
					"%USERNAME%" => $value['userName'],
					"%COUNTRY%" => $value['location'],
					"%RATING%" => ROUND(getAvgUserReview($value['posterId'],'cusomer'))
				);
				$result .= str_replace(array_keys($array), array_values($array), $sub_content);
			}
		}
		else
		{
			$result = $query->affectedRows();
		}
		return $result;
	}
	
	public function featured_services($check='')
	{
		$result = '';
		$lang_key = $_SESSION["lang_key"];
		$category_name = !empty($lang_key)?"category_name_".$lang_key:"category_name";
		$data = $this->db->pdoQuery("SELECT s.id As servicesId,s.serviceTitle, s.servicesSlug, s.featured, 
									s.featured_payment_status, s.servicesPrice, s.freelanserId,
									c.".$category_name." as category_name,
									u.profileImg, u.userSlug,u.userName,u.location
									FROM tbl_services As s
									JOIN tbl_category As c ON c.id = s.servicesCategory
									JOIN tbl_users As u ON u.id = s.freelanserId
									WHERE s.isActive='y' and s.isApproved='a' and s.isDelete='n' and s.featured='y' 
									and s.featured_payment_status='c'")->results();

		if(!empty($check)){
			return COUNT($data);
		}
		if(!empty($data)){
			foreach ($data as $value)
			{
				$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/customer_featured_service-sd.skd");
				$sub_content = $sub_content->compile();

				$service_img = getserviceImages($value['servicesId'],1);
				$user_img = ($value['profileImg']=='') ? SITE_UPD."default-image_450.png" : SITE_USER_PROFILE.$value['profileImg'];

				$sold_counter = $this->db->pdoQuery("select id from tbl_services_order where servicesId='".$value['servicesId']."' and paymentStatus='c' ")->affectedRows();
				$service_fav_list = $this->db->pdoQuery("select * from tbl_favorite_services where customerId=? and serviceId=?",array($this->sessUserId,$value['servicesId']))->affectedRows();

				$array = array(
					"%CATEGORY_NAME%" => $value['category_name'],
					"%SERVICE_ID%" => $value['servicesId'],
					"%FAV_CLASS%" => ($service_fav_list>0) ? 'fa fa-heart' : 'fa fa-heart-o',
					"%USER_SLUG%" => $value['userSlug'],
					"%SERVICE_SLUG%" => $value['servicesSlug'],
					"%SERVICE_IMG%" => $service_img[0],
					"%SERVICES_NAME%" => $value['serviceTitle'],
					"%FEATURED_SERVICE%" => ($value['featured']=='y' && $value['featured_payment_status']=='c')?'featured-tag':'hide',
					"%PRICE%" => CURRENCY_SYMBOL.$value['servicesPrice'],
					"%CURRENCY%" => CURRENCY_CODE,
					"%SOLD_COUNTER%" => $sold_counter,
					"%USER_IMG%" => $user_img,
					"%USERNAME%" => $value['userName'],
					"%COUNTRY%" => $value['location'],
					"%RATING%" => ROUND(getAvgUserReview($value['freelanserId'],'freelancer'))
				);
				$result .= str_replace(array_keys($array), array_values($array), $sub_content);				
			}
		}
		return $result;
	}
	
	public function skill_list($record='',$pageType='')
	{
		$query = $this->db->pdoQuery("select * from tbl_skills where isActive='y' and isDelete='n' ");

		if($record == 'total')
		{
			$skills  = $query->affectedRows();
		}
		else
		{
			$skill_list = $query->results();
			$skills = '';
			foreach ($skill_list as $value)
			{
				$content = new MainTemplater(DIR_TMPL . $this->module . "/home_skills-sd.skd");
				$content = $content->compile();
				$array = array(
					"%SKILL_NAME%" => $value[l_values('skill_name')],
					"%SKILL_ID%" => base64_encode($value['id']),
					"%SKILL_URL%" => ($pageType == 'customerPage') ? SITE_URL."search/freelancer/?skills=".base64_encode($value['id']) : SITE_URL."search/jobs/?skills=".base64_encode($value['id'])
				);
				$skills .= str_replace(array_keys($array),array_values($array),$content);
			}
		}

		return $skills;
	}

	public function home_category_list($type="total_record",$limit="all",$link='freelancer')
	{
		$limit = ($limit=='all') ? '' : 'limit '.$limit;
		$query = $this->db->pdoQuery("select * from tbl_category As c
			LEFT JOIN tbl_subcategory As s ON s.maincat_id = c.id
			where s.maincat_id IS NOT NULL and c.isHomepage=? and c.isActive=? and c.isDelete=? group by s.maincat_id ORDER by c.id DESC ".$limit,array('y','y','n'));
		$categories = '';
		if($type='list')
		{
			$category_list = $query->results();
			foreach ($category_list as $value) {
				$content = new MainTemplater(DIR_TMPL . $this->module . "/home_category-sd.skd");
				$content = $content->compile();
				// $link = 'search/freelancer/?category='.urlencode($value[l_values('category_name')]);
				$link = 'search/sub-category/'.$value[l_values('id')];
				$array = array(
					"%CATEGORY_NAME%" => $value[l_values('category_name')],
					"%IMG%" => SITE_CATEGORY_IMAGE.$value['category_image'],
					"%LINK%" => SITE_URL.$link
				);
				$categories .= str_replace(array_keys($array),array_values($array),$content);
			}
		}
		else
		{
			$categories = $query->affectedRows();
		}
		return $categories;
	}

	public function get_notifications() {
		$qrySel = $this->db->pdoQuery("
			SELECT COUNT(id) as count
			FROM tbl_notification AS n
			where n.isRead='n' AND userId = ".$_SESSION['pickgeeks_userId']." AND notificationType != 'a'
			ORDER BY n.createdDate DESC
			")->result();
		$count = $qrySel["count"];
		return $count;
	}

	public function subscriberContentSubmit($data)
	{

		extract($data);
		if(CheckRepeatEntry('tbl_newsletter_subscriber','subscribed_on','ipAddress','id')){

			$isAvailable = $this->db->select("tbl_newsletter_subscriber",array('*'),array("email"=>$email))->result();
			if(count($isAvailable) > 1)
			{
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>THIS_EMAIL_ADDRESS_IS_ALREADY_SUBSCRIBED));
				redirectPage(SITE_URL);
			}
			else
			{
				$subArry = array("email"=>$email,'ipAddress'=>get_ip_address(),"isActive"=>"y","subscribed_on"=>date('Y-m-d H:i:s'));

				$this->db->insert("tbl_newsletter_subscriber",$subArry)->affectedRows();
				$msgType = $_SESSION["msgType"] =disMessage(array('type'=>'suc','var'=> YOU_HAVE_SUCCESSFULLY_SUBSCRIBED_FOR_NEWSLETTER));
				redirectPage(SITE_URL);
			}
		}
		else{
			$msgType = $_SESSION["msgType"] =disMessage(array('type'=>'err','var'=>SOMETHING_WENT_WRONG));
			redirectPage(SITE_URL);
		}
	}

	public function userTypeSubmit($data)
	{
		extract($data);
		$this->db->update("tbl_users",array("userType"=>$userTypeS),array("id"=>$this->sessUserId));
		$_SESSION["pickgeeks_userType"] = ($userTypeS=="C") ? 'Customer':'Freelancer';
		redirectPage(SITE_URL);
	}


}
?>