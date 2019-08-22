<?php
require_once "../../requires-sd/config-sd.php";

/*$query_data = $db->pdoQuery("SELECT * FROM `tbl_email_templates`")->results();
foreach ($query_data as $key => $value) {
	$contecnt = str_replace('Team Pickgeeks', 'Team ###SITE_NM###', $value['templates']);
	$db->pdoQuery("UPDATE `tbl_email_templates` SET `templates` = ? WHERE `tbl_email_templates`.`id` = ? ",[$contecnt,$value['id']]);
}
exit();*/

$reqAuth = false;
$module = 'faq-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
require_once DIR_CLASS."faq-sd.lib.php";

extract($_REQUEST);

$winTitle = 'Faq - ' . SITE_NM;

$headTitle = 'Faq - '. SITE_NM;
$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

$id = isset($_GET["id"]) ? $_GET["id"] : 0;

$obj = new Faq($module,$id);

$pageContent = $obj->getPageContent();

require_once DIR_TMPL . "compiler-sd.skd";
?>