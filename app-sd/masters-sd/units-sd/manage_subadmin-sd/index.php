<?php
	$reqAuth = true;
	require_once("../../../requires-sd/config-sd.php");
	include(DIR_ADMIN_CLASS."manage_subadmin-sd.lib.php");
	$module = "manage_subadmin-sd";
	$table = "tbl_admin";
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;

	$styles = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN),
    array("data-tables/fixedHeader.bootstrap.css
        ", SITE_ADM_PLUGIN),
    array("bootstrap-switch/css/bootstrap-switch.min.css", SITE_ADM_PLUGIN));

    $scripts = array("core/datatable.js",
    array("data-tables/jquery.dataTables.min.js", SITE_ADM_PLUGIN),
    array("data-tables/dataTables.fixedHeader.min.js", SITE_ADM_PLUGIN),
    array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN),
    array("bootstrap-switch/js/bootstrap-switch.min.js", SITE_ADM_PLUGIN));

	chkPermission($module);
	$Permission = chkModulePermission($module);


	$metaTag = getMetaTags(array("description" => "Admin Panel",
	    "keywords" => 'Admin Panel',
	    'author' => AUTHOR));
	$breadcrumb = array("Manage Subadmin");

	$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
	$type = (!empty($_REQUEST["type"]) ? trim($_REQUEST["type"]) : '');

	$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage').' Subadmin';
	$winTitle = $headTitle.' - '.SITE_NM;

	$objUser = new SubAdmin($id, array(), $type);

	if(isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") 
	{	
		$response['status'] = false;
		extract($_POST);
		$response = array('status'=>false, 'error'=>'Please fill all required values to submit from', 'success'=>'Subadmin has been added successfully.');

		$objPost = new stdClass();
		$objPost->uName = !empty($txt_uname) ? $txt_uname : '';
		$objPost->uEmail = !empty($txt_email) ? $txt_email : '';
		$objPost->uPass = !empty($txt_password) ? $txt_password : '';
		$objPost->isActive = !empty($isActive) ? (($isActive=='y') ? 'a' : 'd') : 'd';

		if(!empty($objPost->uName) && !empty($objPost->uEmail)) 
		{
			if($type == 'edit' && $id > 0) 
			{
				if(in_array('edit',$Permission))
				{
					$exits = $db->pdoQuery("SELECT COUNT(id) AS count FROM $table WHERE id <> ? AND (uName = ? OR uEmail = ?)", array($id, $objPost->uName, $objPost->uEmail))->result();
					if(empty($exits['count'])) 
					{
						$objPost->updated_date = date('Y-m-d H:i:s');
						$db->update("tbl_admin_permission", array("permission"=>""), array("admin_id"=>$id));
						if(empty($objPost->uPass)) { unset($objPost->uPass); } else { $objPost->uPass =  md5($objPost->uPass); }
						$db->update($table, (array)$objPost, array("id"=>$id));

						foreach($actions as $modules => $permission)
						{
							$objPost1 = new stdClass();
							$objPost1->admin_id = $id;
							$objPost1->page_id = getTableValue("tbl_adminrole", "id", array("pagenm"=>$modules));
							$objPost1->permission = implode(',', $permission);

							$exist = getTableValue('tbl_admin_permission', 'id', array('admin_id'=>$id, 'page_id'=>$objPost1->page_id));
							if(!empty($exist)) 
							{
								$db->update("tbl_admin_permission", (array)$objPost1, array('id'=>$exist));
							} 
							else 
							{
								$objPost1->created_date = date('Y-m-d H:i:s');
								$db->insert("tbl_admin_permission", (array)$objPost1);
							}
						}
						$activity_array = array("id" => $id, "module" => $module, "activity" => 'edit');
            			add_admin_activity($activity_array);
						$response['status']  = true;
						$response['success']  = 'Subadmin has been updated successfully.';
						echo json_encode($response);
                		exit;
					} 
					else 
					{	
						$response['status'] = false;
						$response['error'] = 'Admin already exist please use another email and username.';
						echo json_encode($response);
           				exit;
					}
				} 
				else 
				{
					$response['status'] = false;
					$response['error'] = 'You don\' have sufficient permission for edit record.';
					echo json_encode($response);
            		exit;
				}
			} 
			else 
			{
				if(in_array('add',$Permission))
				{
					$exits = $db->count($table, array('uName'=>$objPost->uName, 'OR uEmail='=>$objPost->uEmail));
					if(!empty($objPost->uPass)) 
					{
						if(empty($exits)) 
						{
							$mail_pass = $objPost->uPass;
							$objPost->uPass = md5($mail_pass);
							$objPost->adminType = 'g';
							$objPost->ipAddress = get_ip_address();
							$objPost->created_date = date('Y-m-d H:i:s');
							$last_id = $db->insert("tbl_admin", (array)$objPost)->getLastInsertId();

							foreach($actions as $modules => $permission)
							{
								$objPost1 = new stdClass();
								$objPost1->admin_id = $last_id;
								$objPost1->page_id = getTableValue("tbl_adminrole", "id", array("pagenm"=>$modules));
								$objPost1->permission = implode(',', $permission);
								$db->insert("tbl_admin_permission", (array)$objPost1);
							}

							$contArray = array(
								'greetings' => $objPost->uName,
								'USERNAME' => $objPost->uName,
								'EAMIL' => $objPost->uEmail,
								'PASSWORD' => $mail_pass,
								"LINK"=>SITE_ADM_MOD.'login-sd/'
							);
							//sendEmailAddress($objPost->uEmail, 'subadmin_signup', $contArray);
							$activity_array = array("id" => $id, "module" => $module, "activity" => 'add');
            				add_admin_activity($activity_array);
							$response['status']  = true;
							$response['success'] = 'Subadmin has been added successfully.';

						} 
						else 
						{
							$response['status'] = false;
							$response['error'] = 'Admin already exist please use another username and email.';
						}
					} 
					else 
					{
						$response['status'] = false;
						$response['error'] = 'Please fill all required values to submit form.';
					}
				}
				else
				{
					$respomse['status'] = false;
					$response['error'] = 'You don\' have sufficient permission for insert record.';
				}
			}
			echo json_encode($response);
			exit;
		} 
		else 
		{
			$response['error'] = 'Please fill all required values to submit form.';
		}
		echo json_encode($response);
		exit;
	}

	$pageContent = $objUser->getPageContent();
	require_once(DIR_ADMIN_TMPL . "compiler-sd.skd"); 