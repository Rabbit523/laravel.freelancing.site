<?php



$sqlSettings = $db->select("tbl_site_settings", array("constant", "value"))->results();
foreach ($sqlSettings as $conskey => $consval) {
    define($consval["constant"], $consval["value"]);
}


$conSettings = $db->select("tbl_language_constant", array("*"))->results();
foreach ($conSettings as $conskey => $consval)
{
	$consval[l_values("value")] = preg_replace_callback('/\{([A-Z_]+)\}/', function($matches) {
        	return (defined($matches[1]) ? constant($matches[1]) : $matches[0]);
	}, $consval[l_values("value")]);
    define($consval["constant"], $consval[l_values("value")]);
}

$price = $db->select("tbl_variables", array("constant_name", "value"))->results();
foreach ($price as $conskey => $consval) {
    define($consval["constant_name"], $consval["value"]);
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
if(!defined("DIR_INC")){define("DIR_INC", DIR_URL . "requires-sd/");}
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
define('ATTACHMENT_IMG', SITE_IMG . 'attachment/');
if(!defined("DIR_IMG")){define("DIR_IMG", DIR_THEME . "images-sd/");}
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
define("SITE_PLUGIN", SITE_JS . "plugins-sd/");

define('SITE_LOGO_URL', SITE_URL . 'theme-image/' . SITE_LOGO . '?w=161&h=37');

define("DIR_FUN", DIR_URL . "requires-sd/functions-sd/");
define("SITE_FUN", SITE_URL . "requires-sd/functions-sd/");
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

define("DIR_SLIDER_IMAGE", DIR_UPD . "slider-sd/");
define('SITE_SLIDER', SITE_UPD.'slider-sd/');
define("SITE_SLIDER_IMAGE", SITE_UPD . "slider-sd/");
define("DIR_CATEGORY_IMAGE", DIR_UPD . "category-sd/");
define("SITE_CATEGORY_IMAGE", SITE_UPD . "category-sd/");
define("DIR_SUB_CATEGORY_IMAGE", DIR_UPD . "sub_category-sd/");
define("SITE_SUB_CATEGORY_IMAGE", SITE_UPD . "sub_category-sd/");
define("DIR_CATEGORY_FILE", DIR_UPD . "category_file-sd/");
define("SITE_CATEGORY_FILE", SITE_UPD . "category_file-sd/");
define("DIR_SERVICES_FILE", DIR_UPD . "services_file-sd/");
define("SITE_SERVICES_FILE", SITE_UPD . "services_file-sd/");
define("SITE_USER_PROFILE", SITE_UPD . "profile/");
define("DIR_USER_PROFILE", DIR_UPD. "profile/");
define("DIR_WORKROOM", DIR_UPD . "workroom_files/");
define("SITE_WORKROOM", SITE_UPD . "workroom_files/");
define("DIR_PORTFOLIO_IMG", DIR_UPD. "portfolio_img/");
define("SITE_PORTFOLIO_IMG", SITE_UPD . "portfolio_img/");
define("DIR_JOB_FILES", DIR_UPD."job_files/");
define("SITE_JOB_FILES", SITE_UPD."job_files/");
define("DIR_WORK_FILES", DIR_UPD . "service_work_files/");
define("SITE_WORK_FILES", SITE_UPD . "service_work_files/");
define("DIR_MLS_FILES", DIR_UPD."milestone_work_files/");
define("SITE_MLS_FILES", SITE_UPD."milestone_work_files/");

define("DIR_CONSTANT_TMP", DIR_UPD."constant/");
define("SITE_CONSTANT_TMP", SITE_UPD."constant/");


define('SITE_HOWIT', SITE_UPD.'howit-sd/');
define('DIR_HOWIT', DIR_UPD.'howit-sd/');

define('SITE_WORK_JOB', SITE_UPD.'work_job-sd/');
define('DIR_WORK_JOB', DIR_UPD.'work_job-sd/');

define('SITE_WORK_SERVICE', SITE_UPD.'work_service-sd/');
define('DIR_WORK_SERVICE', DIR_UPD.'work_service-sd/');


/* Start Paypal Settings
define('SANDBOX_MODE_ENABLED', true);
define("PAYPAL_EMAIL", PAYPAL_EMAIL);
define('PAYPAL_CURRENCY_CODE', 'USD');

define('PAYPAL_URL','https://www.sandbox.paypal.com/cgi-bin/webscr');

define('RETURN_URL', SITE_MOD . 'payments-sd.php');
define('CANCEL_RETURN_URL', SITE_URL . 'transaction_cancelled');
define('NOTIFY_URL', SITE_MOD.'payments-sd.php');
/* End Paypal Settings */

/* Start Paypal Settings */
/*define('PPL_MODE', 'sandbox');*/
if(PPL_SANDBOX_MODE == 'y')
	define('PPL_MODE', 'sandbox');
else
	define('PPL_MODE', '');

define("PPL_API_USER", PAYPAL_USERNAME);
define("PPL_API_EMAIL", PAYPAL_EMAIL);
define("PPL_API_PASSWORD", PAYPAL_PASSWORD);
define("PPL_API_SIGNATURE", PAYPAL_SIGNATURE);

define('PPL_LANG', 'EN');
define('PPL_RETURN_URL', SITE_MOD.'freelancer_wallet-sd/index.php');
define('PPL_CANCEL_URL', SITE_MOD.'freelancer_wallet-sd/cancel.php');

define('PPL_RETURN_URL', SITE_MOD.'freelancer_financial_dashboard-sd/index.php');
define('PPL_CANCEL_URL', SITE_MOD.'freelancer_financial_dashboard-sd/cancel.php');

define('PPL_CURRENCY_CODE', 'USD');
define('PRODUCT_LOGO_IMG',SITE_IMG.SITE_LOGO);

define('PPL_CUST_RETURN_URL', SITE_MOD.'customer_wallet-sd/index.php');
define('PPL_CUST_CANCEL_URL', SITE_MOD.'customer_wallet-sd/cancel.php');

define('PPL_CUST_RETURN_URL', SITE_MOD.'customer_financial_dashboard-sd/index.php');
define('PPL_CUST_CANCEL_URL', SITE_MOD.'customer_financial_dashboard-sd/cancel.php');


/* End Paypal Settings */

define("GOOGLE_MAPS_API_KEY", "AIzaSyDdUNwDsMUgonNscXdqmZAAWn4B1mFweDM");
define("SITE_THUMB", SITE_URL . "thumb/");

/*define('DATE_FORMAT', 'j<\s\up>S</\s\up> M Y');*/
define('DATE_FORMAT_ADMIN', 'd-m-Y');
define('FILE_SIZE', 5242880);//5 MB

define("SITE_CONTENT", SITE_UPD.'content-sd/');
define("DIR_CONTENT", DIR_UPD.'content-sd/');

$GoogleAnalyticsClientId = "557605848914-ddqjcbjpiopj9200uhsbbmv6ndrvgah9.apps.googleusercontent.com";
$GoogleAnalyticsClientSecret = "E2F7ucSvWwFJoKebrDU8r6dL";
$GoogleAnalyticsDeveloperKey = "AIzaSyBTXMIgAKt7Dnjy7yAA4oDNLernTU9O0BE";

define("FB_ID",FB_APP_ID);

/* FOR TWILIO */
define('ACCOUNT_SID','ACf0e512b0276c9a628d690549797e888f');
define('AUTH_TOKEN','ecdf88697ad952f6958d52aeb7ba822d');

// define('SLIDER_HEADING','Startup Business plaza for Speculators');
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
$random = 'AJDFJF@#GIGKasb';
define('RD_VAL', $random);
/* For checking image is plain or not */
define('BLANK_IMAGE',SITE_UPD.'blank_image.png');


