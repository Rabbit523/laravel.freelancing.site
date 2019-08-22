<?php 
$page = new MainTemplater(DIR_TMPL."main-sd.skd");

$head = new MainTemplater(DIR_TMPL."head-sd.skd");

$header= new MainTemplater(DIR_TMPL."header-sd.skd");

$footer=new MainTemplater(DIR_TMPL."footer-sd.skd");


	/*$head->title = $winTitle;
	$head->metaTag = $metaTag;

  	/* for head  start*/
	/*$search = array('%METATAG%','%TITLE%');
	$replace = array($metaTag,$winTitle);
	$head_content=str_replace($search,$replace,$head->compile());
	$head_content = ($head_content);

	$search = array('%HEAD%','%SITE_HEADER%','%BODY%','%FOOTER%','%MESSAGE_TYPE%');
	$replace = array($head_content,$objHome->getHeaderContent(),$pageContent,$objHome->getFooterContent(),$msgType);
	$page_content=str_replace($search,$replace,$page->compile());
    echo ($page_content);
	exit;*/