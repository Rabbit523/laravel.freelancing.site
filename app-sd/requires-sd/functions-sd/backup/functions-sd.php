<?php

/* * ********************************* */
/* * *  File Name : Function File   ** */
/* * *  Date		: 13/04/2015	  ** */
/* * ********************************* */
/* error_reporting(0); */

/* Redirect page */

function redirectErrorPage($error) {
    echo $error;
    //redirectPage(SITE_URL.'modules/error?u='.base64_encode($error));
}

function slug_exist($slug,$table = 'tbl_content',$args = 'page_slug') {
    global $db;
    $sql = "SELECT ".$args." FROM ".$table." WHERE ".$args." = '".$slug."' ";
    $content_page = $db->pdoQuery($sql)->result();

    if ($content_page) {
        return true;
    }
}

/* Comment Remaining */

function requiredLoginId() {
    global $sessUserType, $sesspUserId, $memberId;
    if (isset($sessUserType) && $sessUserType == 's')
        return $sesspUserId;
    else
        return $memberId;
}
function closetags($html) {
    #put all opened tags into an array
    preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);

    $openedtags = $result[1];   #put all closed tags into an array
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    # all tags are closed
    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    # close tags
    for ($i = 0; $i < $len_opened; $i++) {

        if (!in_array($openedtags[$i], $closedtags)) {

            $html .= '</' . $openedtags[$i] . '>';
        } else {

            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    } return $html;
}

/* Get Domain name from url */

function GetDomainName($url) {
    $now1 = ereg_replace('www\.', '', $url);
    $now2 = ereg_replace('\.com', '', $now1);
    $domain = parse_url($now2);
    if (!empty($domain["host"])) {
        return $domain["host"];
    } else {
        return $domain["path"];
    }
}

/* Generate Random String as type alpha,nume,alphanumeric,hexidec */

function genrateRandom($length = 8, $seeds = 'alphanum') {
    // Possible seeds
    $seedings['alpha'] = 'abcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $seedings['numeric'] = '0123456789';
    $seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $seedings['hexidec'] = '0123456789abcdef';
    // Choose seed
    if (isset($seedings[$seeds])) {
        $seeds = $seedings[$seeds];
    }
    // Seed generator
    list($usec, $sec) = explode(' ', microtime());
    $seed = (float) $sec + ((float) $usec * 100000);
    mt_srand($seed);
    // Generate
    $str = '';
    $seeds_count = strlen($seeds);
    for ($i = 0; $length > $i; $i++) {
        $str .= $seeds{mt_rand(0, $seeds_count - 1)};
    }
    return $str;
}

/* Sub admin Check Permission */

function checkPermission($usertype, $pagenm, $permission) {
    if ($usertype == 'a') {
        $flag = 0;
        $sadm_page = array('subadmin');
        if (in_array($pagenm, $sadm_page)) {
            $flag = 1;
        } else {
            $getval = getValFromTbl('id', 'adminrole', 'id IN (' . $permission . ') AND pagenm="' . $pagenm . '"');
            if ($getval == 0)
                $flag = 1;
        }
        if ($flag == 1) {

            $_SESSION['notice'] = NOTPER;
            redirectPage(SITE_URL . get_language_url() . 'admin/dashboard');
            exit;
        }
    }
}

/* Load Css Set directory and give filenname as array */

function load_css($filename = array()) {
    $returnStyle = '';
    $filePath = array();
    if (!empty($filename)) {
        if (domain_details('dir') == 'masters-sd') {
            foreach ($filename as $k => $v) {
                if (is_array($v)) {
                    if (isset($v[1]) && $v[1] != "") {
                        $filePath[] = $v[1] . $v[0];
                    } else {
                        $filePath[] = SITE_ADM_CSS . $v[0];
                    }
                } else {
                    $filePath[] = SITE_ADM_CSS . $v;
                }
            }
        } else {
            foreach ($filename as $k => $v) {
                if (is_array($v)) {
                    if (isset($v[1]) && $v[1] != "") {
                        $filePath[] = $v[1] . $v[0];
                    } else {
                        $filePath[] = SITE_CSS . $v[0];
                    }
                } else {
                    $filePath[] = SITE_CSS . $v;
                }
            }
        }
    }
    foreach ($filePath as $style) {
        $returnStyle .= '<link rel="stylesheet" type="text/css" href="' . $style . '">';
    }
    return $returnStyle;
}

/* Load JS Set directory and give filename as array */

function load_js($filename = array()) {
    $returnStyle = '';
    $filePath = array();
    if (!empty($filename)) {
        if (domain_details('dir') == 'masters-sd') {
            foreach ($filename as $k => $v) {
                if (is_array($v)) {
                    if (isset($v[1]) && $v[1] != "") {
                        $filePath[] = $v[1] . $v[0];
                    } else {
                        $filePath[] = SITE_ADM_JS . $v[0];
                    }
                } else {
                    $filePath[] = SITE_ADM_JS . $v;
                }
            }
        } else {
            foreach ($filename as $k => $v) {
                if (is_array($v)) {
                    if (isset($v[1]) && $v[1] != "") {
                        $filePath[] = $v[1] . $v[0];
                    } else {
                        $filePath[] = SITE_JS . $v[0];
                    }
                } else {
                    $filePath[] = SITE_JS . $v;
                }
            }
        }
    }
    foreach ($filePath as $scripts) {
        $returnStyle .= '<script type="text/javascript" src="' . $scripts . '"></script>';
    }
    return $returnStyle;
}

/* Diplay message function */

function disMessage($msgArray, $script = true) {
    if(domain_details('dir') == 'masters-sd'){
        $script = false;
    }
    $message = '';
    $content = '';
    $type = isset($msgArray["type"]) ? $msgArray["type"] : NULL;
    $message = isset($msgArray["var"]) ? $msgArray["var"] : NULL;
    
    $type1 = ($type == 'suc' ? 'success' : ($type == 'inf' ? 'info' : ($type == 'war' ? 'warning' : 'error')));
    if ($script) {
        $content = '<script type="text/javascript"> toastr["' . $type1 . '"]("' . $message . '");</script>';
    } else {
        $content = 'toastr["' . $type1 . '"]("' . $message . '");';
    }

    return $content;
}

function getExt($file) {
    $path_parts = pathinfo($file);
    $ext = $path_parts['extension'];
    return $ext;
}

function GenerateThumbnail($varPhoto, $uploadDir, $tmp_name, $th_arr = array(), $file_nm = '', $addExt = true, $crop_coords = array()) {
    //$ext=strtoupper(substr($varPhoto,strlen($varPhoto)-4));die;
    $ext = '.' . strtoupper(getExt($varPhoto));
    $tot_th = count($th_arr);


    if (($ext == ".JPG" || $ext == ".GIF" || $ext == ".PNG" || $ext == ".BMP" || $ext == ".JPEG" || $ext == ".ICO")) {
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777);
        }

        if ($file_nm == '')
            $imagename = rand() . time();
        else
            $imagename = $file_nm;

        if ($addExt || $file_nm == '')
            $imagename = $imagename . $ext;

        $pathToImages = $uploadDir . $imagename;
        $Photo_Source = copy($tmp_name, $pathToImages);

        if ($Photo_Source) {
            for ($i = 0; $i < $tot_th; $i++) {
                resizeImage($uploadDir . $imagename, $uploadDir . 'th' . ($i + 1) . '_' . $imagename, $th_arr[$i]['width'], $th_arr[$i]['height'], false, $crop_coords);
            }

            return $imagename;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function uploadFile($file, $dir_path, $site_path, $tbl="", $col="", $id_col=0, $id=0) {
    global $db; $result= NULL;
    if (!$file['name'])
        return false;
    if(!empty($tbl) && !empty($col) && !empty($id_col) && !empty($id)) {
        $result = getTableValue($tbl, $col, array($id_col => $id)); //get old image name
    }

    $file_title = $file['name'];
    $folder = $dir_path;
    $path_folder = $site_path;
    $file_name = strtolower(pathinfo($file['name'], PATHINFO_FILENAME));
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $uniqer = md5(uniqid(rand(), 1));
    $file_name = $uniqer . '.' . $ext;
    if ($folder && !is_dir($folder)) {
        mkdir($folder, 0777);
    }
    $uploadfile = $folder . $file_name;

    if(copy($file['tmp_name'], $uploadfile)) {
        $copied = true;
    }
    
    if (!empty($result)) { // remove old image after new image is uploaded successfully
        $filepath = $folder;
        if (file_exists($filepath . $result)) {
            unlink($filepath . $result);
        }
    }
    
    return array("file_path" => $path_folder, "file_name" => $file_name, 'actual_file_name'=>$file['name'], 'copied'=>$copied, 'dir_path'=>$uploadfile);
}
function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale){
    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
    $imageType = image_type_to_mime_type($imageType);
    
    $newImageWidth = ceil($width * $scale);
    $newImageHeight = ceil($height * $scale);
    $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
    switch($imageType) {
        case "image/gif":
            $source=imagecreatefromgif($image); 
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            $source=imagecreatefromjpeg($image); 
            break;
        case "image/png":
        case "image/x-png":
            $source=imagecreatefrompng($image); 
            break;
    }
    imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
    switch($imageType) {
        case "image/gif":
            imagegif($newImage,$thumb_image_name); 
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            imagejpeg($newImage,$thumb_image_name,100); 
            break;
        case "image/png":
        case "image/x-png":
            imagepng($newImage,$thumb_image_name);  
            break;
    }
    chmod($thumb_image_name, 0777);
    return $thumb_image_name;
}

function resizeImage($filename, $newfilename = "", $max_width, $max_height = '', $withSampling = true, $crop_coords = array()) {

    if ($newfilename == "") {
        $newfilename = $filename;
    }

    $fileExtension = strtolower(getExt($filename));
    if ($fileExtension == "jpg" || $fileExtension == "jpeg") {
        $img = imagecreatefromjpeg($filename);
    } else if ($fileExtension == "png") {
        $img = imagecreatefrompng($filename);
    } else if ($fileExtension == "gif") {
        $img = imagecreatefromgif($filename);
    } else {
        $img = imagecreatefromjpeg($filename);
    }

    $width = imageSX($img);
    $height = imageSY($img);

    // Build the thumbnail
    $target_width = $max_width;
    $target_height = $max_height;
    $target_ratio = $target_width / $target_height;
    $img_ratio = $width / $height;

    if (empty($crop_coords)) {

        if ($target_ratio > $img_ratio) {
            $new_height = $target_height;
            $new_width = $img_ratio * $target_height;
        } else {
            $new_height = $target_width / $img_ratio;
            $new_width = $target_width;
        }

        if ($new_height > $target_height) {
            $new_height = $target_height;
        }
        if ($new_width > $target_width) {
            $new_height = $target_width;
        }
        $new_img = imagecreatetruecolor($target_width, $target_height);

        $white = imagecolorallocate($new_img, 255, 255, 255);
        imagecolortransparent($new_img);
        @imagefilledrectangle($new_img, 0, 0, $target_width - 1, $target_height - 1, $white);
        @imagecopyresampled($new_img, $img, ($target_width - $new_width) / 2, ($target_height - $new_height) / 2, 0, 0, $new_width, $new_height, $width, $height);

        //$new_img = imagecreatetruecolor($new_width, $new_height);
        //@imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    } else {
        $new_img = imagecreatetruecolor($target_width, $target_height);
        $white = imagecolorallocate($new_img, 255, 255, 255);
        @imagefilledrectangle($new_img, 0, 0, $target_width - 1, $target_height - 1, $white);
        @imagecopyresampled($new_img, $img, 0, 0, $crop_coords['x1'], $crop_coords['y1'], $target_width, $target_height, $crop_coords['x2'], $crop_coords['y2']);
    }

    if ($fileExtension == "jpg" || $fileExtension == "jpeg") {
        $createImageSave = imagejpeg($new_img, $newfilename, 90);
    } else if ($fileExtension == 'png') {
        $createImageSave = imagepng($new_img, $newfilename, 9);
    } else if ($fileExtension == "gif") {
        $createImageSave = imagegif($new_img, $newfilename, 90);
    } else {
        $createImageSave = imagejpeg($new_img, $newfilename, 90);
    }

}

if (!function_exists('dump')) {
    function dump($var, $label = 'Dump', $exit = false, $echo = TRUE) {
        // Store dump in variable
        ob_start();
        var_dump($var);
        $output = ob_get_clean();

        // Add formatting
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        $output = '<pre style="background: rgba(244, 25, 113, 1);
    color: #FFFFFF;
    border: 1px dotted #000;
    padding: 10px;
    margin: 10px 0;
    text-align: left;
    font-size: 14px;">' . $label . ' => ' . $output . '</pre>';

        // Output
        if ($echo == TRUE) {
            echo $output;
        } else {
            return $output;
        }
        if ($exit) {
            die();
        }

    }
}
function getMetaTitle($metaArray) {
    $content = NULL;
    $content = '<meta name="title" content="' . $metaArray["metaTitle"] . '" />';

    return sanitize_output($content);
}
function getMetaTags($metaArray) {
    $content = NULL;
    $content = '<meta name="keywords" content="' . $metaArray["keywords"] . '" /><meta name="description" content="' . $metaArray["description"] . ', ' . $metaArray["keywords"] . ', ' . SITE_NM . ', ' . REGARDS . '" /><meta name="author" content="' . $metaArray["author"] . '" />';

    if (isset($metaArray["nocache"]) && $metaArray["nocache"] == true) {
        $content .= '<meta HTTP-EQUIV="CACHE-CONTROL" content="NO-CACHE" />
        ';
    }

    return sanitize_output($content);
}
function issetor(&$var, $default = false) {
    return isset($var) ? $var : $default;
}

function getOGMetaTags($metaArray){
    $content = NULL;
    if(!empty($metaArray)) {
        foreach ($metaArray as $key => $value) {
            $content .= "<meta property='$key' content='$value'/>";
        }
    }
    return sanitize_output($content);
}

/* Send SMTP Mail */
function generateEmailTemplate($type, $arrayCont) {
    global $sessUserId;
    global $db;
    
    $query = $db->select('tbl_email_templates', array("subject", "templates"), array("constant" => $type))->result();
    $q = $query;

    $subject = trim(stripslashes($q["subject"]));
    $subject = str_replace("###SITE_NM###", SITE_NM, $subject);

    $site_logo = !empty(SITE_LOGO)? SITE_IMG.SITE_LOGO:"";
    $message = trim(stripslashes($q["templates"]));
    $message = str_replace("###SITE_LOGO_URL###", $site_logo, $message);
    $message = str_replace("###SITE_URL###", SITE_URL, $message);
    $message = str_replace("###SITE_NM###", SITE_NM, $message);
    $message = str_replace("###YEAR###", date('Y'), $message);

    $array_keys = (array_keys($arrayCont));

    for ($i = 0; $i < count($array_keys); $i++) {
        $message = str_replace("###".$array_keys[$i]."###", "".$arrayCont[$array_keys[$i]] . "",$message);
        $subject = str_replace("###" . $array_keys[$i] . "###", "" . $arrayCont[$array_keys[$i]] . "", $subject);
    }

    $data['message'] = $message;
    $data['subject'] = $subject;
    return $data;
}

function sendEmailAddress($to, $subject, $message) {

    require_once("class.phpmailer.php");
    $mail = new PHPMailer(); // create a new object
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    //mail via gmail
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail

    //For LOCAL ONLY
    /*$mail->Host = "smtp.gmail.com";
    $mail->Port = 465; // or 587*/
    
    //mail via hosting server ( FOR LIVE ONLY)	
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;

    $mail->IsHTML(true);
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    //$mail->SetFrom(SMTP_USERNAME);
    $mail->SetFrom(FROM_EMAIL, FROM_NM);

    $mail->AddReplyTo(FROM_EMAIL, FROM_NM);
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->AddAddress($to);
    $result = true;
    if (!$mail->Send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        $result = false;
    }
    return $result;
}


/*Admin Functions*/
function convertDate($date, $time = false, $what = 'default') {
    if ($what == 'wherecond') {
        return date('Y-m-d', strtotime($date));
    } else if ($what == 'display') {
        return date('M d, Y h:i A', strtotime($date));
    } else if ($what == 'onlyDate') {
        return date('M d, Y', strtotime($date));
    } else if ($what == 'gmail') {
        return date('D, M d, Y - h:i A', strtotime($date));
        //Tue, Jul 16, 2013 at 12:14 PM
    } else if ($what == 'default') {
        if (trim($date) != '' && $date != '0000-00-00' && $date != '1970-01-01') {
            if (!$time) {
                $retDt = date('d-m-Y', strtotime($date));
                return $retDt == '01-01-1970' ? '' : $retDt;
            } else {
                '1970-01-01 01:00:00';
                '01-01-1970 01:00 AM';
                $retDt = date('d-m-Y h:i A', strtotime($date));
                return $retDt == '01-01-1970 01:00 AM' ? '' : $retDt;
            }
        } else {
            return '';
        }

    } else if ($what == 'db') {
        if (trim($date) != '' && $date != '0000-00-00' && $date != '1970-01-01') {
            if (!$time) {
                $retDt = date('Y-m-d', strtotime($date));
                return $retDt == '1970-01-01' ? '' : $retDt;
            } else {
                $retDt = date('Y-m-d H:i:s', strtotime($date));
                return $retDt == '1970-01-01 01:00:00' ? '' : $retDt;
            }
        } else {
            return '';
        }

    }
}
function curPageURL() {
    $pageURL = 'http';

    if (isset($_SERVER["HTTPS"])) {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }

    define('CURRENT_PAGE_URL', $pageURL);
}

function curPageName() {
    $pageName = substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);
    define('CURRENT_PAGE_NAME', $pageName);
}
function checkIfIsActive() {
    global $db;

    if (isset($_SESSION['pickgeeks_user_id']) && '' != $_SESSION['pickgeeks_user_id']) {
        $user_details = $db->select("tbl_users", "*", array(
                    "id" => $_SESSION['pickgeeks_user_id']
                ))->result();
        if ($user_details) {
            if ('n' == $user_details['isActive']) {
                unset($_SESSION['pickgeeks_user_id']);
                unset($_SESSION['pickgeeks_first_name']);
                unset($_SESSION['pickgeeks_last_name']);

                $_SESSION['toastr_message'] = disMessage(array('type' => 'err', 'var' => "You have not verified the email address that is registered with us. Please verify your Email Address"));
                redirectPage(SITE_URL);
                return false;
            } else if ('d' == $user_details['status']) {
                unset($_SESSION['pickgeeks_user_id']);
                unset($_SESSION['pickgeeks_first_name']);
                unset($_SESSION['pickgeeks_last_name']);

                $_SESSION['toastr_message'] = disMessage(array('type' => 'err', 'var' => "Your account has been deactivated by Admin.Please contact Site Admin to activate your account"));
                redirectPage(SITE_URL);
                return false;
            } else {
                return true;
            }
        } else {
            unset($_SESSION['pickgeeks_user_id']);
            unset($_SESSION['pickgeeks_first_name']);
            unset($_SESSION['pickgeeks_last_name']);

            $_SESSION['toastr_message'] = disMessage(array('type' => 'err', 'var' => "There seems to be an issue. Please Sign in again"));
            redirectPage(SITE_URL);
            return false;
        }
    } else {
        return true;
    }
}


/* get domain details, pass module, dir, file or file-module whichever required. */
if(!function_exists('domain_details')){
    function domain_details($returnWhat) {
        $arrScriptName = explode('/', $_SERVER['SCRIPT_NAME']);

        if (PROJECT_DIRECTORY_NAME != '' && in_array(PROJECT_DIRECTORY_NAME, $arrScriptName) == true) {
            $arrKey = array_search(PROJECT_DIRECTORY_NAME, $arrScriptName);
            unset($arrScriptName[$arrKey]);
        }

        $arrScriptName = array_values($arrScriptName);

        if ($returnWhat == 'module'){
            return ($arrScriptName[4] != "" ? $arrScriptName[4] : '');
        }
        else if ($returnWhat == 'dir'){
            if($arrScriptName[2]!="" && $arrScriptName[3]!="masters-sd"){
                return $arrScriptName[2];
            }else if($arrScriptName[3]=='masters-sd'){
                return $arrScriptName[3];
            }else{
                return '';
            }
            
        }else if ($returnWhat == 'file'){
            return ($arrScriptName[5] != "" ? $arrScriptName[5] : '');
        }
        else if ($returnWhat == 'file-module'){
            return ($arrScriptName[3] != "" ? $arrScriptName[3] : '');
        }
    }
}
/*new structure html function*/

function generatePassword($length = 8) {
    // start with a blank password
    $password = "";
    // define possible characters - any character in this string can be
    // picked for use in the password, so if you want to put vowels back in
    // or add special characters such as exclamation marks, this is where
    // you should do it
    $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
    // we refer to the length of $possible a few times, so let's grab it now
    $maxlength = strlen($possible);
    // check for length overflow and truncate if necessary
    if ($length > $maxlength) {
        $length = $maxlength;
    }
    // set up a counter for how many characters are in the password so far
    $i = 0;
    // add random characters to $password until $length is reached
    while ($i < $length) {

        // pick a random character from the possible ones
        $char = substr($possible, mt_rand(0, $maxlength - 1), 1);

        // have we already used this character in $password?
        if (!strstr($password, $char)) {
            // no, so it's OK to add it onto the end of whatever we've already got...
            $password .= $char;
            // ... and increase the counter by one
            $i++;
        }
    }
    return $password;
}

function closePopup() {
    $content = '<script type="text/javascript">window.close();</script>';
    return $content;
}
function humanTiming($time) {

    $time = time() - $time; // to get the time since that moment

    $tokens = array(
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second',
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) {
            continue;
        }

        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
    }

}
function getTime($date) {
    $time = humanTiming(strtotime($date));
    if ($time == "") {
        $time = "Just Now";
    } else {
        $time .= " ago";
    }

    return $time;
}
function get_listing($limit = 3, $bunchNo = 0){ 
    global $db,$sessUserId;
    $start = $limit * $bunchNo;
    $limit++;
    ob_start();
    $pQuery = $db->pdoQuery("Select p.*,c.categoryName,s.subcategoryName from tbl_product p LEFT JOIN tbl_category c on(p.categoryID = c.id) LEFT JOIN tbl_subcategory s on(p.subcategoryID = s.id) where p.isActive = 'y' ORDER BY p.createdDate limit $start,$limit");
    
    $limit--;
    $totP = $pQuery->affectedRows();
    if($totP>0){
        $products = $pQuery->results();
       foreach ($products as $key => $value) {
            if ($key == $limit) {
                    break;
            }
            extract($value);
            $pImage = SITE_UPD.'product/th1_'.$image;
            $html = html(DIR_TMPL."listing-sd/listing-row-sd.skd");
                echo str_replace(array("%ID%","%PRODUCT%","%CATEGORY%","%SUBCATEGORY%","%IMG%"),array($id,$productName,$categoryName,$subcategoryName,$pImage), $html); 
        }
    }else{
            html_r(DIR_TMPL . "load_more-msg-sd.skd", "%MSG%", "No any product found", true);
    }

    if ($totP <= $limit) {
            
    } else {
            html_r(DIR_TMPL . "load_more-sd.skd", array("%START%", "%LIMIT%","%CLASS%"), array($bunchNo + 1, $limit,'product'), true);
    }  
 return ob_get_clean();
}     
        
function get_search($fromTable = '',$tableArray = array(),$fieldList = '',$whereCond = '',$limit = 3, $bunchNo = 0){ 
    global $db,$sessUserId;
    $start = $limit * $bunchNo;
    $limit++;
    ob_start();
    if(count($tableArray) > 0){
        $leftJoin  = '';
        foreach ($tableArray as $tableName => $leftJoinCond) {
            $leftJoin  .= ' LEFT JOIN '.$tableName.'  ON ( '.$leftJoinCond.' ) ';
        }
    }
    $q = ("SELECT $fieldList FROM $fromTable $leftJoin $whereCond LIMIT $start,$limit");
    $pQuery = $db->pdoQuery($q);
    
    $limit--;
    $totP = $pQuery->affectedRows();
    if($totP>0){
        $products = $pQuery->results();
       foreach ($products as $key => $value) {
            if ($key == $limit) {
                    break;
            }
            extract($value);
            $pImage = SITE_UPD.'product/th1_'.$image;
            $html = html(DIR_TMPL."listing-sd/listing-row-sd.skd");
                echo str_replace(array("%ID%","%PRODUCT%","%CATEGORY%","%SUBCATEGORY%","%IMG%"),array($id,$productName,$categoryName,$subcategoryName,$pImage), $html); 
        }
    }else{
            html_r(DIR_TMPL . "load_more-msg-sd.skd", "%MSG%", "No any product found", true);
    }

    if ($totP <= $limit) {
            
    } else {
            html_r(DIR_TMPL . "load_more-sd.skd", array("%START%", "%LIMIT%","%CLASS%"), array($bunchNo + 1, $limit,'search-product'), true);
    }  
    return ob_get_clean();
}

/*for check existing record in table*/     
function getTotalRows($tableName, $condition = '', $countField = '*') {

    global $db;
    $db->select($tableName, $countField, $condition);

    $qSel = "SELECT * from " . $tableName . " WHERE " . $condition;

    $qrysel0 = $db->pdoQuery($qSel);
    $totlaRows = $qrysel0->affectedRows();
    return $totlaRows;
}        
function getUserName($userId) 
{
    global $db;  
    $qSel = "SELECT * from tbl_users WHERE id = '".$userId."'";
    $qrysel0 = $db->pdoQuery($qSel);
    $userDetails = $qrysel0->result();        
    //$userName = $userDetails['firstName'].' '.$userDetails['lastName'];
    $userName = $userDetails['userName'];
    return $userName; 
} 
function getlistingUrl($listingId) 
{
    global $db;  
    $qSel = "SELECT listingUrl from tbl_listing WHERE listingId = '".$listingId."'";
    $qrysel = $db->select('tbl_listing',array('listingUrl'),array('listingId'=>$listingId))->result();
    $listingDisplayUrl =(checkHiddenUrl($listingId) == 'true')?'URL HIDDEN':displaySiteUrl($qrysel['listingUrl']);
    return $listingDisplayUrl; 
}   
function getlistingFullUrl($listingId)
{
    global $db;  
    $qSel = "SELECT listingUrl from tbl_listing WHERE listingId = '".$listingId."'";
    $qrysel = $db->select('tbl_listing',array('listingSlug,listingUrl,isAdminApproved,listingTypeId,appName'),array('listingId'=>$listingId))->result();
    $Url = SITE_URL."site_details/".$qrysel['listingSlug'];
    $listingDisplayUrl =(checkHiddenUrl($listingId) == 'true')?'URL HIDDEN':($qrysel['listingTypeId'] == 4)?$qrysel['appName']:displaySiteUrl($qrysel['listingUrl']);
    //$listingDisplayUrl =(checkHiddenUrl($listingId) == 'true')?'URL HIDDEN':displaySiteUrl($Url);
    if($qrysel['isAdminApproved']=='rejected' || $qrysel['isAdminApproved']=='pending')
    {
        $finalUrl = "<a href='#'>".$listingDisplayUrl."</a>";    
    }
    else
    {
        $finalUrl = "<a href='".$Url."'>".$listingDisplayUrl."</a>";    
    }
    
    return $finalUrl; 
}

function getUserDetails($fieldKey,$userId) 
{
    global $db;  
    $qSel = "SELECT ".$fieldKey." from tbl_users WHERE id = '".$userId."'";
    $qrysel0 = $db->pdoQuery($qSel);
    $userDetails = $qrysel0->result();    
    $userData = '';
    if(!empty($userDetails))
        $userData = $userDetails[$fieldKey];
    return $userData; 
}

function getListingType($listTypeId) 
{
    global $db;  
    $qSel = "SELECT * from tbl_listing_type WHERE listingTypeId = '".$listTypeId."'";
    $qrysel0 = $db->pdoQuery($qSel);
    $userDetails = $qrysel0->result();        
    $userName = $userDetails['listingTypeName'];
    return $userName; 
}

function getHighPrice($listingId){
    global $db;
    $qSel = "SELECT MAX(amount) as amount from tbl_bids WHERE listingId = '".$listingId."'";
    $qrysel0 = $db->pdoQuery($qSel);
    $daysDetails = $qrysel0->result();        
    $day = ($daysDetails['amount']!='')?$daysDetails['amount']:'1';
    return $day;
}      
function getRemainingDays($listDurationDate,$createdDate,$remainDays = ''){
    $daysleft = '0';
    $strCurrent_date = strtotime(date('Y-m-d H:i:s'));

    if($remainDays == 'remainDays')
        $timestamp = date('Y-m-d H:i:s',strtotime($listDurationDate));
    else
    {
        $timestamp = date('Y-m-d H:i:s',strtotime("+".$listDurationDate." days", strtotime($createdDate)));
        $futureDateTS = strtotime($timestamp);
        $createdDateTS = $strCurrent_date; //Future date.    
        $timeleft = $futureDateTS-$createdDateTS;
        $daysleft = round_up(((($timeleft/24)/60)/60),0);
    }
    
    $now = new DateTime("now");
    $future_date = new DateTime($timestamp);
    $interval = $future_date->diff($now);

    $days = $interval->format('%d');
    $years = $interval->format('%y');
    $months = $interval->format('%m');
    $hours = $interval->format('%h');
    $minut = $interval->format('%i');
    $seconds = $interval->format('%s');
    if($strCurrent_date < strtotime($timestamp))
    { 
        if($years != '0')
            return $years.' years left';
        else if($months != '0')
        {
            //return $months.' months left';
            return $daysleft.' days left';
        }
        else if($days!='0'){
            return $daysleft.' days left';
        }else if($hours!=0){
            return $hours.' hours left';
        }else if($minut!=0){
            return $minut.' minutes left';
        }else if($seconds!=0){
            return $seconds.' seconds left';
        }else{
            return '';
        }
        /*if($days == 0)
            return $hours.' hours left';
        elseif($hours == 0)
            return $minut.' minutes left';
        elseif($minut == 0)
            return $seconds.' seconds left';
        else
            return $days.' days left';*/
    }
    else{
        $isEnded = 'Ended';
        if($remainDays == 'remainDays')
            $isEnded = '';

        if($years != '0')
        {
            if($months != '0')
                return $isEnded.' '.$years.' years '.$months.' months ago';
            else
                return $isEnded.' '.$years.' years ago';
        }
        else if($months != '0')
        {
            return $isEnded.' '.$months.' months ago';
        }
        else if($days != 0)
            return $isEnded.' '.$days.' days ago';
        elseif($hours != 0)
            return $isEnded.' '.$hours.' hours ago';
        elseif($minut != 0)
            return $isEnded.' '.$minut.' minutes ago';
        elseif($seconds != 0)
            return $isEnded.' '.$seconds.' seconds ago';
        else
            return $isEnded.' '.$days.' days ago';

    }
}
function round_up($number, $precision = 2)
{
    $fig = (int) str_pad('1', $precision, '0');
    return (ceil($number * $fig) / $fig);
}
function getTotalBids($listingId){
    global $db;
    $daysDetails = $db->pdoQuery("SELECT bidId from tbl_bids WHERE  isBuyNow='n' AND listingId = '".$listingId."'")->affectedRows();
    return $daysDetails;
}   

function getBuyNowBids($listingId){
    global $db;
    $daysDetails = $db->pdoQuery("SELECT bidId from tbl_bids WHERE  isBuyNow='y' AND listingId = '".$listingId."'")->affectedRows();
    return $daysDetails;
}   

function getListingOldYear($date)
{
    $then_ts = strtotime($date);
    $then_year = date('Y', $then_ts);
    $age = date('Y') - $then_year;
    if(strtotime('+' . $age . ' years', $then_ts) > time()) $age--;
    if($age == 0)
        $age = 'New';
    else
        $age = $age.' y/o';
    return $age;
}
function getCategoryName($catId){
    global $db;
    $qSel = "SELECT categoryName from tbl_listing_category WHERE id = '".$catId."' AND isActive = 'y'";
    $qrysel0 = $db->pdoQuery($qSel);
    $daysDetails = $qrysel0->result();        
    $categoryName = $daysDetails['categoryName'];
    return $categoryName;
}
function getMonetizeType($monTypeId)
{
    global $db;
    if($monTypeId!='')
    {
        $qSel = "SELECT * FROM `tbl_monetize_type` WHERE `monTypeId` IN ('".$monTypeId."')";
        $qrysel0 = $db->pdoQuery($qSel);
        $monTypes = $qrysel0->results();        
        $monatize = '';
        foreach($monTypes as $monType)
        {
            if($monatize == '')
                $monatize = $monType['monTypeName']; 
            else
                $monatize = ' & '.$monType['monTypeName']; 
        }
        return $monatize;
    }
}

function auctionDetailById($listId)
{

    $table = 'tbl_listing';

    $fields = '*';

    $id = $listId;

    $key = 'listingId';

    $limit=1;

    $fetchAuction = get_record_by_ID($table,$key,$id,$fields,$limit);    
    return $fetchAuction;

}

function get_record_by_ID($table, $keyColumnName, $id, $fields = "*",$limit=10){
 global $db;
        $i=0;

        ///echo "hello";

        if(is_array($keyColumnName))

        {

            foreach($keyColumnName as $key=>$value) {

                $i++;

                if(count($keyColumnName)>$i)

                {

                    $sets[] = $key.'='.$value.' AND';

                }

                else

                {

                    $sets[] = $key.'='.$value;

                }

            }

            $sets = implode(' ', $sets);

            $sql = "SELECT $fields FROM $table WHERE ".$sets." LIMIT $limit";

        }

        else

        {

            $sets = $keyColumnName;         

            $sql = "SELECT $fields FROM $table WHERE $sets = '$id' LIMIT $limit"; 

        }       

        $returnArray = $db->pdoQuery($sql)->result();

        return $returnArray;
    }

function chkBidPrice($listingId,$amount){
    global $db;
    $query=$db->select('tbl_bids',array('MAX(amount) as amount'),array('listingId' => $listingId))->result(); 
    $bid_amount_status=($amount >= (($query['amount']) + 5)) ? "y":"n";
    return $bid_amount_status;
}
function reserveMet($listingId,$amount){
    global $db;
    $query=$db->select('tbl_listing',array("reservePrice"),array('listingId' => $listingId))->result();
    $reserve=(($amount >= $query["reservePrice"] && $query["reservePrice"] != 0))?'y':'n';
    return $reserve; 
}
function minimumOffer($listingId,$amount){
    global $db;
    $query=$db->select('tbl_listing',array("minimumOffer"),array('listingId' => $listingId))->result();
    $reserve=(($amount >=$query["minimumOffer"]))?'y':'n';
    return $reserve; 
}
function getMonthsBeforeNow($before) 
{
    //return date('Y-m-d',strtotime(-$before." months,-1 days"));
    return date("Y-m-d", strtotime( -$before." months" ) );
}
function getMonthsBeforeNowDay($before) 
{
    //return date('Y-m-d',strtotime(-$before." months,-1 days"));
    return date("Y-m-d", strtotime( -$before." months,-1 days" ) );
}

function getNow() 
{
    return date('Y-m-d');    
}
function check_txnid($tnxid){
    global $link;
    return true;
    $valid_txnid = true;
    //get result set
    $sql = mysql_query("SELECT * FROM `tbl_payment_history` WHERE transactionId = '$tnxid'", $link);
    if ($row = mysql_fetch_array($sql)) {
        $valid_txnid = false;
    }
    return $valid_txnid;
}

function check_price($price, $id){
    $valid_price = false;
    //you could use the below to check whether the correct price has been paid for the product
    
    /*
    $sql = mysql_query("SELECT amount FROM `products` WHERE id = '$id'");
    if (mysql_num_rows($sql) != 0) {
        while ($row = mysql_fetch_array($sql)) {
            $num = (float)$row['amount'];
            if($num == $price){
                $valid_price = true;
            }
        }
    }
    return $valid_price;
    */
    return true;
}

function updatePayments($data){
    global $link;
    
    if (is_array($data)) {
        $sql = mysql_query("INSERT INTO `payments` (txnid, payment_amount, payment_status, itemid, createdtime) VALUES (
                '".$data['txn_id']."' ,
                '".$data['payment_amount']."' ,
                '".$data['payment_status']."' ,
                '".$data['item_number']."' ,
                '".date("Y-m-d H:i:s")."'
                )", $link);
        return mysql_insert_id($link);
    }
}
 

 /*for send message to seller*/
function sendMessageSeller($data){
    global $db,$sessUserId;
    extract($data);
    if($message!=''){
        $array=array(
        "ownerId" => $sessUserId,
        "senderId" => $sessUserId,
        "receiverId" => $receiverId,
        "messageDesc" => $message,
        "delete_user" =>'',
        "createdDate" => date('Y-m-d H:i:s')
        );
        $isUsreContact = $db->pdoQuery("SELECT * FROM tbl_user_contacts WHERE (userId = '".$_SESSION['pickgeeks_userId']."' AND contactuserId = '".$receiverId."') OR (contactuserId = '".$_SESSION['pickgeeks_userId']."' AND userId= '".$receiverId."')")->results();        

        if(count($isUsreContact) <= 0)
                $db->insert("tbl_user_contacts",array("userId"=>$sessUserId,"contactuserId"=>$receiverId,"createdDate"=>date('Y-m-d H:i:s')));
        $query=$db->insert('tbl_messages',$array)->getLastInsertId();
        $mail_data['id']=$data['receiverId'];
        $mail_data['template_name']=$mail_data['template_name_admin']='message_add';
        $mail_data['email_content']=array('greetings'=>getUserName($data['receiverId']),'name'=>getUserName($sessUserId),'message' => $data['message'],'messagedate' => date(DATE_FORMAT,strtotime(date('Y-m-d H:i:s'))));
        ($query>0)?sendmail_updates($mail_data):'';
        $msgType = $_SESSION["msgType"] = ($query>0)?disMessage(array('type'=>'suc','var'=>'Your message sent successfully')):disMessage(array('type'=>'err','var'=>"There seems to be an issue while sending message"));

        redirectPage($_SERVER['HTTP_REFERER']);
    }
    
}
function displaySiteUrl($siteUrl){
    $website_url='';
    if($siteUrl != '')
    {
        $patterns = array();
        $patterns[0] = '/http:\/\//';
        $patterns[1] = '/www./';
        $patterns[2] = '/https:\/\//';
        $replacements = '';
        
        $website_url = preg_replace($patterns, $replacements, $siteUrl);  
    }
    return $website_url;
}

/*Image Thumbnail generate function*/
function src($url, $h = 100, $w = 100, $zc = 1, $q = 90) {
    return SITE_THUMB . "?src=" . ($url) . "&w={$w}&h={$h}&zc={$zc}&q={$q}";
}
function websiteAge($date){
    if(!empty($date)){
        $birthdate = new DateTime($date);
        $today   = new DateTime('today');
        $age = $birthdate->diff($today)->y;
        if($age == 00 || $age == 0)
        {
            $age = $birthdate->diff($today)->m;
            if($age == 1)
                return $age.' month';
            else
                return $age.' months';
        }
        else
        {
            if($age == 1)
                return $age.' year';
            else
                return $age.' years';
        }
        
    }else{
        return 0;
    }
}
function IsActionEnd($listDurationDate,$createdDate){
    $timestamp = date('Y-m-d H:i:s',strtotime("+".$listDurationDate." days", strtotime($createdDate)));
    $strCurrent_date = strtotime(date('Y-m-d H:i:s'));
    $now = new DateTime();
    $future_date = new DateTime($timestamp);
    $interval = $future_date->diff($now);
    $days = $interval->format('%d');
    $hours = $interval->format('%h');
    $minut = $interval->format('%i');
    $seconds = $interval->format('%s');

    $val =  'continue';
    if($strCurrent_date > strtotime($timestamp))
    { 
        $val = 'ended';        
    }
    return $val;
}

function checkHiddenUrl($listingId){
    
    global $db;
    $query=$db->pdoQuery("SELECT feesType FROM tbl_fees JOIN tbl_listing ON (FIND_IN_SET(tbl_fees.feesId,tbl_listing.extra_fees) > 0 AND tbl_listing.listingTypeId=tbl_fees.listingTypeId) WHERE tbl_fees.feesType LIKE '%hidden%' AND listingId=".$listingId)->result();
    return ($query!='')?((count($query) > 0)?'true':'false'):'false';
}
function listingRating($data){
    global $db,$sessUserId;
    extract($data);
    
    if($comment == "" && $status == "")
    {
        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>'Please enter all values'));
    }
    else if($comment == "")
    {
        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>'Please enter Comment'));
    }
    else if($status == "")
    {
        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>'Please select Feedback Type'));
    }
    else
    {
        $score=($score=='')?0:$score;
        $array=array(
            "userId" => $sessUserId,
            "listingId" => $listingId,
            "listingRatingDesc" => $comment,
            "listingRating" => $score,
            "isPossitive" => ($status=='p')?'p':'n',
            "createdDate" => date('Y-m-d H:i:s')
            );
        $query=$db->insert('tbl_listing_rating',$array)->getLastInsertId();
        $msgType = $_SESSION["msgType"] = ($query>0)?disMessage(array('type'=>'suc','var'=>'Rating & Review added successfully')):disMessage(array('type'=>'err','var'=>"There seems to be an issue while adding Rating & Review"));
    }
    
    redirectPage($_SERVER['HTTP_REFERER']);
}
function sendmail_updates($data){
    global $db;
    extract($data);
    $subscribe=$db->select('tbl_users',array("subscribe_email","email"),array('id'=> $id))->result();
    if((isset($notification_pref) && $notification_pref=='y') || $subscribe['subscribe_email']=='y'){
        $array = generateEmailTemplate($template_name,$data['email_content']);
        sendEmailAddress($subscribe['email'],$array['subject'],$array['message']);
    }
    $data['email_content']['greetings']=FROM_NM;
    $array = generateEmailTemplate($template_name_admin,$data['email_content']);
    sendEmailAddress(trim(ADMIN_EMAIL),$array['subject'],$array['message']);   
}

function filtering($value = '', $type = 'output', $valType = 'string', $funcArray = '',$notFilterAbuse = '') {
    global $abuse_array, $abuse_array_value;

    if($notFilterAbuse == ''){
        if (domain_details('dir') != 'masters-sd') {
            if ($valType != 'int' && $type == 'output') {
                $value = str_ireplace($abuse_array, $abuse_array_value, $value);
            }
        }
    }
    if ($type == 'input' && $valType == 'string') {
        $value = str_replace('<', '< ', $value);
    }

    $content = $filterValues = '';
    if ($valType == 'int')
        $filterValues = (isset($value) ? (int) strip_tags(trim($value)) : 0);
    if ($valType == 'float')
        $filterValues = (isset($value) ? (float) strip_tags(trim($value)) : 0);
    else if ($valType == 'string')
        $filterValues = (isset($value) ? (string) strip_tags(trim($value)) : NULL);
    else if ($valType == 'text')
        $filterValues = (isset($value) ? (string) trim($value) : NULL);
    else
        $filterValues = (isset($value) ? trim($value) : NULL);

    if ($type == 'input') {
        //$content = mysql_real_escape_string($filterValues);
        //$content = $filterValues;
        //$value = str_replace('<', '< ', $filterValues);
        $content = addslashes($filterValues);
    } else if ($type == 'output') {
        if ($valType == 'string')
            $filterValues = html_entity_decode($filterValues);

        $value = str_replace(array('\r', '\n', ''), array('', '', ''), $filterValues);
        $content = stripslashes($value);
    }
    else {
        $content = $filterValues;
    }

    if ($funcArray != '') {
        $funcArray = explode(',', $funcArray);
        foreach ($funcArray as $functions) {
            if ($functions != '' && $functions != ' ') {
                if (function_exists($functions)) {
                    $content = $functions($content);
                }
            }
        }
    }

    return $content;
}
/*export excel file*/
function export_to_excel($data, $name='export_to_excel', $heading_array=array()) {
    $name = $name . ".xlsx";
    header("Content-Disposition: attachment; filename=\"$name\"");
    header("Content-Type: application/vnd.ms-excel");
    $flag = false;
    foreach($data as $row) {
        if(!$flag) {
            if(!empty($heading_array)) {
                // display separate array headings
                
                foreach ($heading_array as $key => $value) {

                    echo implode("\t", $value) . "\n";
                }
            } else {
                // display column names as first row
                echo implode("\t", array_keys($row)) . "\n";
            }
            $flag = true;
        }

        // filter data
        ((empty($heading_array)) ? array_walk($row, 'filterData') : '');
        echo implode("\t", array_values($row)) . "\n";
    }
    exit;
}
/*filtering data*/
function filterData(&$str) {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
}
function convert_to_excel($input_array, $output_file_name, $output_file_sheet_name="Language Constants", $sticky=true){

    require_once DIR_INC . 'excel/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $column_count = count($input_array[0]);

    foreach($input_array as $key=>$value){
        foreach($value as $k=>$v){
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValueByColumnAndRow($k, $key+1, $v)
                    ->setCellValueByColumnAndRow($k, $key+1, $v)
                    ->setCellValueByColumnAndRow($k, $key+1, $v);
        }
    }
    $i=0;
    foreach(range('A','Z') as $columnID) {
        if($column_count>$i){
        $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
            ->setAutoSize(true);
        $i++;
        }
    }
    $objPHPExcel->getActiveSheet()->setTitle($output_file_sheet_name);
    if($sticky==true){
        error_reporting(0);
        $objPHPExcel->getActiveSheet()->freezePane('A2');
        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    }
    $styleArray = array(
        'font'  => array(
            'color' => array('rgb' => '236bbf')
    ));
    $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($styleArray);
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$output_file_name.'"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    exit;
    $objWriter->save('php://output');
    exit;
}


/*Export CSV*/
function convert_to_csv($input_array, $output_file_name, $delimiter,$columnanme){
    $temp_memory = fopen('php://memory', 'w');
    fputcsv($temp_memory,$columnanme);
    foreach ($input_array as $line) {
        fputcsv($temp_memory, $line, $delimiter);
    }
    fseek($temp_memory, 0);
    header('Content-Type: application/csv');
    header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
    fpassthru($temp_memory);
    exit;
}

function checkUserWalletAmount($userId)
{
    global $sesspUserId,$db;
    $usersData = $db->select('tbl_users',array('walletAmount'),array('id'=>$userId))->result(); 
    return $usersData['walletAmount'];
}

function removeSpecialChar($string) {
    // Removes special chars.
    return str_replace(array("\'",'_', '%',"'",'"'), array('','\_', '\%',"\'",'\"'), $string);
}
function displayAppUrl($siteUrl){
    $website_url_temp=array();
    if($siteUrl != '')
    {  
        $website_url_temp=(strpos($siteUrl, 'google') == false)?explode("/", $siteUrl):explode("?", $siteUrl);
        $website_url=(strpos($siteUrl, 'google') == false)?$website_url_temp[5]:str_replace(array("id=","&hl=en"),array("",""), $website_url_temp[1]);
    }
    return $website_url;
}
function number_format_short( $n ) {
    if ($n > 0 && $n < 1000) {
        // 1 - 999
        $n_format = floor($n);
        $suffix = '';
    } else if ($n >= 1000 && $n < 1000000) {
        // 1k-999k
        $n_format = floor($n / 1000);
        $suffix = 'K+';
    } else if ($n >= 1000000 && $n < 1000000000) {
        // 1m-999m
        $n_format = floor($n / 1000000);
        $suffix = 'M+';
    } else if ($n >= 1000000000 && $n < 1000000000000) {
        // 1b-999b
        $n_format = floor($n / 1000000000);
        $suffix = 'B+';
    } else if ($n >= 1000000000000) {
        // 1t+
        $n_format = floor($n / 1000000000000);
        $suffix = 'T+';
    }

    return !empty($n_format . $suffix) ? $n_format . $suffix : 0;
}

function checkGooglePlayApp($url)
{
    $curlOptions = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_URL => $url
    );

    $ch = curl_init();
    curl_setopt_array($ch, $curlOptions);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $http_code == '200';
}

function generateToken($formName)  {
    if ( !session_id() ) { session_start(); }
    $sessionId = session_id();
    return sha1( $formName.$sessionId.SHA1_KEY );
}

function checkToken( $token, $formName )  {
    return $token === generateToken($formName);
}

function CheckRepeatEntry($table_name,$date_field,$ipField,$checkRepeatMinute=3)
{
    global $db;
    
    $ip_address = get_ip_address();

    $getData = $db->pdoQuery("SELECT ".$date_field." from ".$table_name." where ".$ipField."  = '".$ip_address."' ORDER BY ".$date_field." DESC LIMIT 1");
    $totData = $getData->affectedRows();

    if($totData > 0)
    {
        $fetchData = $getData->result();
        $db_date = $fetchData[$date_field];
        if(strtotime($db_date) >= strtotime(date('Y-m-d H:i:s', strtotime('-'.$checkRepeatMinute.' minutes'))))
        {
            $totBlack = $db->select("tbl_black_list" ,'', array("ipAddress"=>get_ip_address()));
            $total_record = $totBlack->affectedRows();
            $record_detail = $totBlack->result();
            if($total_record > 0)
            {
                $record = $record_detail['counter']+1;
                $db->update("tbl_black_list",array("counter"=>$record),array("ipAddress"=>get_ip_address()));
                return false;
            }else{
                $db->insert("tbl_black_list",array("ipAddress"=>get_ip_address(),"counter"=>'1',"createdDate"=>date('Y-m-d H:i:s')));
                return true;
            }
        }
    } 
    return true;   
}
