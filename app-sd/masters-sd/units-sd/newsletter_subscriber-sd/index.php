<?php

$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
include(DIR_ADMIN_CLASS."newsletter_subscriber-sd.lib.php");
$module = "newsletter_subscriber-sd";
$table = "tbl_newsletter_subscriber";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;

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
    'author' => AUTHOR));

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Newsletter Subscribers';
$breadcrumb = array($headTitle);
$winTitle = $headTitle . ' - ' . SITE_NM;

$objContent = new NewsletterSubscriber($module,$Permission);
if(!empty($_POST['type']) && $_POST['type']=='send_mail' && !empty($_POST['newsletter_name'])) 
{
     $objContent->contentSubmit($_POST,$Permission);  
 /*   extract($_POST);
    $newsletter = $db->pdoQuery("SELECT email FROM tbl_newsletter_subscriber WHERE id= $id")->result();

    if(count($newsletter['email'])>0)
    {
        foreach ($newsletter_name as $value) {
          
            $newsletterTemplate = $db->select('tbl_newsletters', array('newsletter_content', 'newsletter_subject'), array('newsletter_name'=>$value))->result();
            $name = getTablevalue('tbl_users', 'firstName', array('email'=>$newsletter['email']));
            $name = (!empty($name) ? $name : 'There');
            $arrayCont = array('greetings' => $name,'newsletter_content'=>$newsletterTemplate['newsletter_content'],'newsletter_subject'=>filtering($newsletterTemplate['newsletter_subject'],"output"));
            $array = generateEmailTemplate('newsletter',$arrayCont);
            
            sendEmailAddress($newsletter['email'],filtering($array['subject'],"output"),$array['message']);

        }
            $activity_array = array("id" => $id, "module" => $module, "activity" => 'send');
            add_admin_activity($activity_array);
            $responce['status'] = true;
        $responce['success'] = 'Newsletter sent successfully'; 
    }else{
        $responce['status'] = false;
                $responce['success'] = 'There seems to be an issue to sending NewsLetter, Please try again';
    }
    echo json_encode($responce);
    exit; */
}

$pageContent = $objContent->getPageContent();

$module = "newsletter_subscriber-sd";
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");
