<?php
    //error_reporting(E_ALL);
    define("DB_HOST", "localhost");
    define("DB_USER", "root");
    define("DB_PASS", "");
    define("DB_NAME", "prd_pickgeeks");
    
    if(!defined("PROJECT_DIRECTORY_NAME")) { define("PROJECT_DIRECTORY_NAME", "pickgeeks_mabilis"); }
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    define('SITE_URL', $protocol . $_SERVER["SERVER_NAME"] . '/pickgeeks_mabilis/');
    define('ADMIN_URL', SITE_URL . 'masters-sd/');
    
    if(!defined("DIR_URL")) { define('DIR_URL', $_SERVER["DOCUMENT_ROOT"] . '/pickgeeks_mabilis/app-sd/'); }
    

