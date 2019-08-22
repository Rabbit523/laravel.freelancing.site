<?php
    $reqAuth = false;
    require_once("../../../requires-sd/config-sd.php");
    include(DIR_ADMIN_CLASS."login-sd.lib.php");
    $module = 'login-sd';
    $reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;

    if ($adminUserId > 0) { redirectPage(SITE_ADM_MOD . 'home-sd'); }
    $header_panel = false;
    $left_panel = false;
    $footer_panel = false;

    $winTitle = 'Login - ' . SITE_NM;
    $headTitle = 'Login';

    $metaTag = getMetaTags(array("description" => "Admin Panel",
        "keywords" => 'Admin Panel',
        'author' => AUTHOR)
    );

    $objCookie = new stdClass();
    if (isset($_COOKIE["remember"]) && $_COOKIE["remember"] == 'y') {
        // printr($_COOKIE, 1);
        $objCookie->uName = (!empty($_COOKIE["uName"]) ? base64_decode($_COOKIE["uName"]) : '');
        $objCookie->uPass = (!empty($_COOKIE["uPass"]) ? base64_decode($_COOKIE["uPass"]) : '');
        $objCookie->rememberme = 'y';
        // printr($objCookie, 1);
    }

    $objUser = new Login((array)$objCookie);

    if (isset($_POST["uEmail"])) {
        extract($_POST);
        $objPost->uEmail = isset($uEmail) ? filtering($uEmail, 'input') : '';
        if (!empty($objPost->uEmail)) {
            $loginReturn1 = $objUser->forgotProdedure();
            switch ($loginReturn1) {
                case 'succForgotPass' : {
                    $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Updated Password has been sent to your email, Please check your mail account'));
                    redirectPage(SITE_ADM_MOD . 'login-sd/');
                    break;
                }
                case 'wrongUsername' : {
                    $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'Invalid Email Address, Please try again'));
                }
            }
        }
    }

    if (isset($_POST["submitLogin"])) {
        extract($_POST);

        $objPost->uName = isset($uName) ? filtering($uName, 'input') : '';
        $objPost->uPass = isset($uPass) ? filtering($uPass, 'input') : '';
        $objPost->isRemember = isset($remember) ? $remember : '';

        if ($objPost->isRemember == 'remember') {
            setcookie('uName', base64_encode($objPost->uName), (time() + 3600 * 24 * 30), '/');
            setcookie('uPass', base64_encode($objPost->uPass), (time() + 3600 * 24 * 30), '/');
            setcookie('remember', 'y', (time() + 3600 * 24 * 30), '/');
        } else {
            setcookie('uName', '', (time() - 3600), '/');
            setcookie('uPass', '', (time() - 3600), '/');
            setcookie('remember', '', (time() - 3600), '/');
        }


        if (!empty($objPost->uName) && !empty($objPost->uPass)) {
            $loginReturn = $objUser->loginSubmit();

            switch ($loginReturn) {
                case 'invaildUsers' : $toastr_message = disMessage(array('type' => 'err', 'var' => 'User Name or Password is Invalid, Please try again'));
                break;
                case 'inactivatedUser' : $toastr_message = disMessage(array('type' => 'err', 'var' => 'Your account is not active, Please contact to '.SITE_NM.' support to get more information'));
                break;
                case 'invaildUsersAd' : $toastr_message = disMessage(array('type' => 'err', 'var' => 'Invalid credentials'));
                break;
            }
        }
    }

    if ($toastr_message == '' && !empty($_SESSION['req_uri_adm'])) {
        if (!isset($_SESSION['loginDisplayed_adm'])) {
            $toastr_message = array('type' => 'err', 'var' => 'Please Login to continue');
            $_SESSION['loginDisplayed_adm'] = 1;
        }
    }

    $pageContent = $objUser->getPageContent();
    require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");
?>