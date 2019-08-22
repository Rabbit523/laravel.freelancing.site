<?php
    $reqAuth = true;
    require_once("../../../requires-sd/config-sd.php");
    include(DIR_ADMIN_CLASS."newsletter-sd.lib.php");
    $module = "newsletter-sd";
    $reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
    $table = "tbl_newsletters";

    $styles = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN),
        array("bootstrap-switch/css/bootstrap-switch.min.css", SITE_ADM_PLUGIN),
        array("bootstrap-select.min.css", SITE_CSS)
    );

    $scripts = array("core/datatable.js",
        array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
        array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN),
        array("bootstrap-switch/js/bootstrap-switch.min.js", SITE_ADM_PLUGIN),
        array("bootstrap-select.min.js", SITE_JS)
    );

    chkPermission($module);
    $Permission = chkModulePermission($module);

    $metaTag = getMetaTags(array("description" => "Admin Panel",
        "keywords" => 'Admin Panel',
        'author' => AUTHOR)
    );

    $id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
    $postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
    $type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

    $headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Newsletters';
    $winTitle = $headTitle . ' - ' . SITE_NM;
    $breadcrumb = array($headTitle);

    if (!empty($_POST["action"])  && $_POST["action"]=="submitAddForm"  && $_SERVER["REQUEST_METHOD"] == "POST") {
        extract($_POST);
        $responce = array('status' => false, 'error' => 'Please fill all required value', 'success' => 'Newsletter added successfully');

        $objPost->newsletter_name = (!empty($newsletter_name) ? filtering($newsletter_name, 'input') : '');
        $objPost->newsletter_subject = (!empty($newsletter_subject) ? filtering($newsletter_subject,"input") : '');
        $objPost->newsletter_content = (!empty($newsletter_content) ? $newsletter_content: '');
        $objPost->status = (!empty($status) ? $status : 'd');

        if(!empty($objPost->newsletter_name) && !empty($objPost->newsletter_subject) && !empty($objPost->newsletter_content)) {
            if($type == 'edit' && $id > 0) {
                if(in_array('edit', $Permission)) {
                    $objPost->updated_on=date('Y-m-d H:i:s');
                    $db->update($table, (array)$objPost, array('id'=>$id));

                    $activity_array = array("id"=>$id, "module"=>$module, "activity"=>'edit');
                    add_admin_activity($activity_array);
                    $responce['status'] = true;
                    $responce['success'] = 'Newsletter updated successfully';
                } else {
                    $responce['error'] = "You don't have permission to edit Newsletter";
                }
            } else {
                if(in_array('add', $Permission)) {
                    $objPost->added_on=date('Y-m-d H:i:s');
                    $id = $db->insert($table, (array)$objPost)->getLastInsertId();
                    
                    $activity_array = array("id"=>$id, "module"=>$module, "activity"=>'add');
                    add_admin_activity($activity_array);
                    $responce['status'] = true;
                } else {
                    $responce['error'] = "You don't have permission to add Newsletter";
                }
            }
        }
        echo json_encode($responce);
        exit;
    } 
    else if(!empty($_POST['type']) && $_POST['type']=='send_mail' && !empty($_POST['emails'])) 
    {
        extract($_POST);
        $newsletter = $db->select($table, array('newsletter_content', 'newsletter_subject'), array('id'=>$id))->result();
        if(count($emails)>0){
            foreach ($emails as $value) {
                
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) 
                {

                    $name = getTablevalue('tbl_users', 'firstName', array('email'=>$value));
                    $name = (!empty($name) ? $name : 'There');
                    $arrayCont = array('greetings' => $name,'newsletter_content'=>$newsletter['newsletter_content'],'newsletter_subject'=>filtering($newsletter['newsletter_subject'],"output"),"output");

                    $activity_array = array("id" => $id, "module" => $module, "activity" => 'send');
                    add_admin_activity($activity_array);

                    $array = generateEmailTemplate('newsletter',$arrayCont);
                    sendEmailAddress($value,filtering($array['subject'],"output"),$array['message']);
               // sendEmailAddress($value, $id, $arrayCont, true);
                
                }
            
            }
            // $activity_array = array("id" => $id, "module" => $this->module, "activity" => $type);
            //     add_admin_activity($activity_array);
            $responce['status'] = true;
            $responce['success'] = 'Newsletter sent successfully'; 
        }else{
            $responce['status'] = false;
                    $responce['success'] = 'There seems to be an issue to sending NewsLetter,Please try again';
        }
        echo json_encode($responce);
        exit; 
    }
$objTemplate = new NewsLetter($module);
$pageContent = $objTemplate->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");
