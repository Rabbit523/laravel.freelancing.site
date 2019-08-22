<?php
	$reqAuth = false;
	$module = 'content-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."content-sd.lib.php";
	$table = "tbl_content";

 	$slug = isset($_GET["slug"]) ? $_GET["slug"] : 0;

 	if(!empty($slug)) {
	 	$result = $db->select($table, array("*"), array("page_slug" => $slug, 'isActive'=>'y'));
		if ($result->affectedRows() == 0) {
			redirectPage(SITE_URL);
		} else {
			$result = $result->result();
		}
 	} else {
 		redirectPage(SITE_URL);
 	}

	$mainObj = new Content($module, $result['pId'], $result);

	$winTitle = $result['pageTitle'].' - ' . SITE_NM;
    $headTitle = $result['pageTitle'];
    $metaTitle = $result['metaTitle'];
    $metaDesc = $result['metaDesc'];
    $metaKeyword= $result['metaKeyword'];
    $metaTag = getMetaTitle(array("metaTitle"=>$metaTitle));
    $metaTag .= getMetaTags(array("description" => $metaDesc, "keywords" => $metaKeyword, "author" => AUTHOR));

	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";
?>