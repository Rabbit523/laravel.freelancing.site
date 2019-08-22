<?php

$sqlSettings = $db->select("tbl_site_settings", array("constant", "value"))->results();
foreach ($sqlSettings as $conskey => $consval) {
    define($consval["constant"], $consval["value"]);
}

define("SALT_FOR_ENCRYPTION", "ts");

$host = $_SERVER['HTTP_HOST'];
$request_uri = $_SERVER['REQUEST_URI'];
$canonical_url = "http://" . $host . $request_uri;
define('CANONICAL_URL', $canonical_url);

define('YEAR', date("Y"));

define('MEND_SIGN', '<font color="#FF0000">*</font>');

define('AUTHOR', 'sukhadaam');
define('ADMIN_NM', 'Administrator');
define('REGARDS', SITE_NM);

define("SITE_CLASS", SITE_URL . "core-sd/");
define("SITE_VENDOR", SITE_URL . "vendor-sd/");
define("DIR_CLASS", DIR_URL . "core-sd/");
define("SITE_INC", SITE_URL . "requires-sd/");
if(!defined("DIR_INC")) {
define("DIR_INC", DIR_URL . "requires-sd/");	
}
define("SITE_MOD", SITE_URL . "units-sd/");
define("DIR_MOD", DIR_URL . "units-sd/");
 
define("SITE_APP_UPD", SITE_URL .'app-sd/'. "dspaces-sd/");
define("DIR_APP_UPD", DIR_URL .'app-sd/'. "dspaces-sd/");

define("DIR_VNDR", DIR_URL .'vender-sd/');

define("SITE_UPD", SITE_URL . "dspaces-sd/");
define("DIR_UPD", DIR_URL . "dspaces-sd/");


define('SITE_THEME', SITE_URL . 'frames-sd/');
define("DIR_THEME", DIR_URL . "frames-sd/");
define('SITE_CSS', SITE_THEME . 'css-sd/');
define("DIR_CSS", DIR_THEME . "css-sd/");
define('SITE_IMG', SITE_THEME . 'images-sd/');
if(!defined('DIR_IMG')) {
	define("DIR_IMG", DIR_THEME . "images-sd/");
}
define("DIR_FONT", DIR_INC . "fonts-sd/");

define('SITE_DETAILS_URL', SITE_URL . 'site_details/');


define('SITE_HYBRIDAUTH', DIR_INC . 'hybridauth-master/hybridauth/');
define("DIR_HYBRIDAUTH", DIR_INC . "hybridauth-master/hybridauth/");




//define("SITE_THEME_CSS", SITE_URL . "frames-sd/css-sd/");
define('SITE_THEME_FONTS', SITE_URL . 'fonts/');
define('SITE_THEME_IMG', SITE_URL . 'images/');
define('SITE_THEME_JS', SITE_URL . 'js/');


define('DIR_THEME_IMG', DIR_THEME . 'images-sd/');

define("SITE_JS", SITE_INC . "javascript-sd/");
if(!defined('DIR_JS')) {
	define("DIR_JS", DIR_INC . "javascript-sd/");
}
define("SITE_PLUGIN", SITE_JS . "plugins-sd/");

define('SITE_LOGO_URL', SITE_URL . 'theme-image/' . SITE_LOGO . '?w=161&h=37');

define("DIR_FUN", DIR_URL . "requires-sd/functions-sd/");
define("DIR_TMPL", DIR_URL . "skeletons-sd/");
define("SITE_TMPL", SITE_URL . "skeletons-sd/");
define("DIR_CACHE", DIR_UPD . "cache-sd/");

define('USER_DEFAULT_AVATAR', 'default_profile_pic.png');
define('PRODUCT_DEFAULT_IMAGE', SITE_THEME_IMG . 'product-default-image.jpg');

/* Start ADMIN SIDE */
define("SITE_ADMIN_URL", SITE_URL . "masters-sd/");
define("SITE_ADMIN_TMPL", SITE_ADMIN_URL . "skeletons-sd/");
define("SITE_ADM_CLASS", ADMIN_URL . "core-sd/");
define("SITE_ADM_CSS", ADMIN_URL . "frames-sd/css-sd/");
define("SITE_ADM_IMG", ADMIN_URL . "frames-sd/images-sd/");
define("SITE_ADM_INC", ADMIN_URL . "requires-sd/");
define("SITE_ADM_MOD", ADMIN_URL . "units-sd/");
define("SITE_ADM_JS", ADMIN_URL . "requires-sd/javascript-sd/");
define("SITE_ADM_UPD", ADMIN_URL . "dspaces-sd/");
define("SITE_JAVASCRIPT", SITE_URL . "requires-sd/javascript-sd/");
define("SITE_ADM_PLUGIN", ADMIN_URL . "requires-sd/plugins-sd/");
define("SITE_ADM_JAVA", SITE_ADMIN_URL . "requires-sd/javascript-sd/");
define("SITE_ADMIN_VENDOR",SITE_ADM_INC."vendors-sd/");

define("DIR_ADMIN_URL", DIR_URL . "masters-sd/");
define("DIR_ADMIN_CLASS", DIR_ADMIN_URL . "core-sd/");
define("DIR_ADMIN_THEME", DIR_ADMIN_URL . "frames-sd/");
define("DIR_ADMIN_TMPL", DIR_ADMIN_URL . "skeletons-sd/");
define("DIR_ADM_INC", DIR_ADMIN_URL . "requires-sd/");
define("DIR_ADM_MOD", DIR_ADMIN_URL . "units-sd/");
define("DIR_ADM_PLUGIN", DIR_ADM_INC . "plugins-sd/");
define("DIR_ADMIN_VENDOR",DIR_ADM_INC."vendors-sd/");
/* End ADMIN SIDE */

define("NMRF", '<div class="no-results">No more results found.</div>');
define("LOADER", '<img alt="Loading.." src=" ' . SITE_THEME_IMG . 'ajax-loader-transparent.gif" class="lazy-loader" />');

define("PHP_DATE_FORMAT", 'M d, Y');
define("PHP_DATE_FORMAT_MONTH", 'M Y');
define("PHP_DATE_FORMAT_MONTH_YEAR", 'M Y');
define("MYSQL_DATE_FORMAT", '%d-%m-%Y');
define("BOOTSTRAP_DATEPICKER_FORMAT", 'M d, yyyy');
define('CURRENCY_SYMBOL', '$');

/* Start Paypal Settings 
define('SANDBOX_MODE_ENABLED', true);
define("PAYPAL_EMAIL_ADD", PAYPAL_EMAIL);
define('PAYPAL_CURRENCY_CODE', 'USD');

define('PAYPAL_URL','https://www.sandbox.paypal.com/cgi-bin/webscr');

define('RETURN_URL', SITE_MOD . 'payments-sd.php');
define('CANCEL_RETURN_URL', SITE_URL . 'transaction_cancelled');
define('NOTIFY_URL', SITE_MOD.'payments-sd.php');
/* End Paypal Settings */

/* Start Paypal Settings */
define('PPL_MODE', 'sandbox');
define("PPL_API_USER", PAYPAL_USERNAME);
define("PPL_API_EMAIL", PAYPAL_EMAIL);
define("PPL_API_PASSWORD", PAYPAL_PASSWORD);
define("PPL_API_SIGNATURE", PAYPAL_SIGNATURE);

define('PPL_LANG', 'EN');
define('PPL_LOGO_IMG', 'http://www.domaintribes.com/frames-sd/images-sd/10538903871502186030.PNG');
define('PPL_RETURN_URL', SITE_MOD.'wallet-sd/index.php');
define('PPL_CANCEL_URL', SITE_MOD.'wallet-sd/cancel_url.php');
define('ADM_PPL_RETURN_URL', SITE_ADM_MOD.'redeem_request-sd/index.php');
define('ADM_PPL_CANCEL_URL', SITE_ADM_MOD.'redeem_request-sd/cancel_url.php');
define('PPL_CURRENCY_CODE', 'USD');
/* End Paypal Settings */ 

define("GOOGLE_MAPS_API_KEY", "AIzaSyDdUNwDsMUgonNscXdqmZAAWn4B1mFweDM");
define("SITE_THUMB", SITE_URL . "thumb/");

define('DATE_FORMAT', 'j<\s\up>S</\s\up> M Y');
define('DATE_FORMAT_ADMIN', 'd-m-Y');
define('FILE_SIZE', 5242880);//5 MB

define("SITE_CONTENT", SITE_UPD.'content-sd/');
define("DIR_CONTENT", DIR_UPD.'content-sd/');

/**** FOR LOCAL ****/
/*define('TWITTER_CONSUMER_KEY','VnxfovxX3uEEuPcAu6OGKStXR');
define('TWITTER_CONSUMER_SEC','oDrOfneksngk0DNJOqvcGZqkqHdrTltHQbZiagnTrspSgNvpKD');
define('TWITTER_TOCKEN','800887318240333825-FvD7IIVz8Z9K1mExUFJs7YRuq0R65XD');
define('TWITTER_SECRET','WPMm2b0pBwt26jxlYy46Hvaw0mtZbOIHiNtBQw6efWEaz');*/

/*** Nidhidave server ****/
define('TWITTER_CONSUMER_KEY','2WRk6nVWSmPhjVegnQMuOqYDh');
define('TWITTER_CONSUMER_SEC','c9r0CZHJjvtQEWkcrEOol3w6rj8ugeKu9ov2iuVq94f47cXcv4');
define('TWITTER_TOCKEN','800887318240333825-l6jR66dTRxzJLi2Q2ByiQOwJdIwsC2x');
define('TWITTER_SECRET','qadq7iUGf7LPvn98tcmAvfwtXvSXwda1My8qTIVdrCddA');	

define("WHOISKEY","c7cab1b59f8580e1mp2e5b74ced829e9c");

/*$GoogleAnalyticsClientId = "469327022516-7gpkovasainjiaunt8r1jmrel5olnf3q.apps.googleusercontent.com";
$GoogleAnalyticsClientSecret = "0_UU8-P_w6z1PcQyMfgv-U5L";
$GoogleAnalyticsDeveloperKey = "AIzaSyAFC89yXqb04C7KwzthhsBYlUW_MZeCj58";*/

$GoogleAnalyticsClientId = "395455385091-38mh39mcjk51mgedt50db1ulfkptbva7.apps.googleusercontent.com";
$GoogleAnalyticsClientSecret = "j9vtvWPvxOMoydm_9kiBWybT";
$GoogleAnalyticsDeveloperKey = "AIzaSyBzJVB5mo_t7P6H4ri3AQNQxgewED02Mck";

define("FB_ID",FB_APP_ID);

/* FOR TWILIO */
define('ACCOUNT_SID','AC86b7c1bd9ab84d9ddf749cb6eb1b6973');
define('AUTH_TOKEN','9d465292c8ca89201ef367b5c837d02d');
define('TWILIO_FROM_NUMBER','+18672924462');

define('SLIDER_HEADING','Startup Business plaza for Speculators');
define('SLIDER_TEXT_PLACEHOLDER','Type brand, name of your choice');
define('EXPERTS_CHOICE',"Expert's Choice");
define('VIEW_ALL','View All');
define('OPEN_COMMENT','Make an open comment');
define('KEEP_EYE_LISTING','Keep an eye on listing');
define('STAY_UPDATED','Stay updated with');
define('NEVER_MISS','and never miss a single opportunity. Quickly Add it to your watch list.');
define('CONTACT_SELLER_PLACEHOLDER',"This is the Public comment section so kindly post relevant comments only, per our Site Rules. Kindly use 'Contact Seller' to request further information.");
define('LIKE_TO_SELL','What would you like to sell?');

//$random = md5(time() . rand());  
$random = 'jk#osif8jiu4ars';
define('SHA1_KEY', $random);

define("NEED_VERITY_FOR_SELL",'yes');
if(!defined('CST_KEY')) {
	define('CST_KEY', '#&$sdfdfs789fs7d"');
}