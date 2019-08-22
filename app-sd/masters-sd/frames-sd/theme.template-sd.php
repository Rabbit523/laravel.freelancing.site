<?php 

$page = new MainTemplater(DIR_ADMIN_TMPL."main-sd.skd");

$head = new MainTemplater(DIR_ADMIN_TMPL."head-sd.skd");

$site_header= new MainTemplater(DIR_ADMIN_TMPL."header-sd.skd");

$footer=new MainTemplater(DIR_ADMIN_TMPL."footer-sd.skd");

$page->body= '';
$page->right='';
$page->head='';
$page->header='';
$page->footer='';

