<?php

/* * ********************************* */
/* * *  File Name : Function File   ** */
/* * *  Date		: 13/04/2015	  ** */
/* * ********************************* */
/* error_reporting(0); */

/* Redirect page */

if(!function_exists("redirectPage")) {
function redirectPage($url) {
    header('Location:' . $url);
    exit;
}
}

function redirectErrorPage($error) {
    echo $error;
    //redirectPage(SITE_URL.'modules/error?u='.base64_encode($error));
}

/* Sanitize Output */

if(!function_exists("sanitize_output")) {
    function sanitize_output($buffer) {

        $search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s');
        $replace = array('>', '<', '\\1', '');
        $buffer = preg_replace($search, $replace, $buffer);
        return $buffer;
    }
}


function removeFromString($str, $item)
{
    $parts = explode(',', $str);
    //echo $parts;
    while(($i = array_search($item, $parts)) !== false)
    {
        unset($parts[$i]);
    }
    return implode(',', $parts);
}
/* Use to remove whitespace,Spaces and make string to lower case. Add '-' where Space. */

/*function Slug($string,$table = 'tbl_content',$args = 'page_slug') {
    $slug = strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    $slug_exists = slug_exist($slug,$table,$args);

    if($slug_exists) {
        $i = 1; $baseSlug = $slug;
        while(slug_exist($slug,$table,$args)){
            $slug = $baseSlug . "-" . $i++;
        }
    }

    return $slug;
}*/
function Slug($field,$string,$table) {
    $slug = strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    $slug_exists = slug_exist($field, $slug, $table);

    if($slug_exists) {
        $i = 1; $baseSlug = $slug;
        while(slug_exist($field,$slug,$table)){
            $slug = $baseSlug . "-" . $i++;
        }
    }

    return $slug;
}
function slug_exist($field,$slug,$table) {
    global $db;

    $sql = "SELECT ".$field." FROM ".$table." WHERE ".$field." = '".$slug."' ";


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

/* Get IP Address of current system. */

if(!function_exists("get_ip_address")) {
    function get_ip_address() {
        foreach (array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ) as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }
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
/* Generate Random String */

if(!function_exists("generateRandString")) {
    function generateRandString($totalString = 10, $type = 'alphanum') {
        if ($type == 'alphanum')
            $alphanum = "AaBbC0cDdEe1FfGgH2hIiJj3KkLlM4mNnOo5PpQqR6rSsTt7UuVvW8wXxYy9Zz";
        else if ($type == 'num')
            $alphanum = "098765432101234567890098765432101234567890098765432101234567890";
        return substr(str_shuffle($alphanum), 0, $totalString);
    }
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

/* Check Authentication */

if(!function_exists("Authentication")) {
    function Authentication($reqAuth = false, $redirect = true, $allowedUserType = 'a',$last_page = "") {
        //echo $last_page;exit;
        $todays_date = date("Y-m-d");
        global $adminUserId, $sessUserId, $db;

        //exit();
        $whichSide = domain_details('dir');
        if ($reqAuth == true) {
            if ($whichSide == 'masters-sd') {

                if ($adminUserId == 0) {
                    $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => 'Please Sign in to continue'));
                    $_SESSION['req_uri_adm'] = $_SERVER['REQUEST_URI'];

                    if ($redirect) {
                        redirectPage(SITE_ADMIN_URL);
                    } else {
                        return false;
                    }
                } else {
                    return true;
                }
            } else {

        //echo $last_page;exit;
                if ($sessUserId <= 0) {

                    $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => 'Please Sign in to continue'));
                    $_SESSION['req_uri'] = $_SERVsR['REQUEST_URI'];
                    if ($redirect) {
                        redirectPage(SITE_URL.'SignIn/'.$last_page);
                    } else {
                        return false;
                    }
                }
                return true;
            }
        }
    }
}

if(!function_exists("getTableValue")) {
    function getTableValue($table, $field, $wherecon = array())
    {
        global $db;
        $qrySel = $db->select($table, array($field), $wherecon);
        $qrysel1 = $qrySel->result();
        $totalRow = $qrySel->affectedRows();
        $fetchRes = $qrysel1;

        if ($totalRow > 0) {
            return $fetchRes[$field];
        } else {
            return "";
        }
    }
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
function sendMail($to, $type, $arrayCont, $is_newsletter=false) {
    global $db;
    require_once("class.phpmailer.php");

    if(!is_array($type)) {
        $table = (($is_newsletter) ? 'tbl_newsletters' : 'tbl_email_templates');
        $field_array = (($is_newsletter) ? array('newsletter_subject', 'newsletter_content') : array("subject", "templates"));
        $wArray = (($is_newsletter) ? array("id" => $type) : array("constant" => $type));
        $subject_field  = (($is_newsletter) ? 'newsletter_subject' : 'subject');
        $template_field = (($is_newsletter) ? 'newsletter_content' : 'templates');

        $email_templates = $db->select($table, $field_array, $wArray)->result();
        $subject = trim(stripslashes($email_templates[$subject_field]));
        $subject = str_replace("###SITE_NM###", SITE_NM, $subject);

        $message = trim(stripslashes($email_templates[$template_field]));
        $message = str_replace("###SITE_LOGO_URL###", SITE_IMG.SITE_LOGO, $message);
        $message = str_replace("###SITE_URL###", SITE_URL, $message);
        $message = str_replace("###SITE_NM###", SITE_NM, $message);
        $message = str_replace("###YEAR###", date('Y'), $message);

        $array_keys = (array_keys($arrayCont));
        for ($i = 0; $i < count($array_keys); $i++) {
            $message = str_replace("###".$array_keys[$i]."###", "".$arrayCont[$array_keys[$i]] . "",$message);
            $subject = str_replace("###" . $array_keys[$i] . "###", "" . $arrayCont[$array_keys[$i]] . "", $subject);
        }
    } else {
        $subject = $arrayCont['subject'];
        $message = $arrayCont['message'];
    }

    $mail = new PHPMailer(); // create a new object
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled

    //mail via gmail
    $mail->SMTPSecure = 'TLS'; // secure transfer enabled REQUIRED for GMail

    //mail via hosting server
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT; // or 587

    $mail->IsHTML(true);
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SetFrom(FROM_EMAIL, FROM_NM);

    $mail->AddReplyTo(FROM_EMAIL, FROM_NM);
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->AddAddress($to);
    /*echo "1";
    printr($mail, 1);*/
    $result = true;
    if (!$mail->Send()) {
        //echo "Mailer Error: " . $mail->ErrorInfo;
        $result = false;
    }
    return $result;
}

function sendEmailAddress($to, $subject, $message) {

    require_once("class.phpmailer.php");
    $mail = new PHPMailer(); // create a new object
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    $mail->SMTPKeepAlive    = true;                  // SMTP connection will not close after each email sent
    //mail via gmail
    $mail->SMTPSecure = 'TLS'; // secure transfer enabled REQUIRED for GMail

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
    // pre_print($mail);
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
if(!function_exists("domain_details")) {
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
                if($_SERVER['SERVER_NAME'] == "www.sukhadaam.com")
                {               
                    /*  for sub directory : (i.e.  : demo/<pro_name>) */
                    if($arrScriptName[3]!="" && $arrScriptName[4]!="masters-sd"){
                    return $arrScriptName[3];
                    }else if($arrScriptName[4]=='masters-sd'){
                        return $arrScriptName[4];
                    }else{
                        return '';
                    }
                }
                else
                {
                    /*  for main directory : (i.e.  : <pro_name>) */
                    if($arrScriptName[2]!="" && $arrScriptName[3]!="masters-sd"){
                        return $arrScriptName[2];
                    }else if($arrScriptName[3]=='masters-sd'){
                        return $arrScriptName[3];
                    }else{
                        return '';
                    }
                }

            }else if ($returnWhat == 'file'){
                return ($arrScriptName[5] != "" ? $arrScriptName[5] : '');
            }
            else if ($returnWhat == 'file-module'){
                return ($arrScriptName[3] != "" ? $arrScriptName[3] : '');
            }
        
    }
}

function truncate($text,$chars) {
    /*    $text = $text." ";
        $text = substr($text,0,$chars);
        $text = substr($text,0,strrpos($text,' '));*/
     //   echo strlen($text)." ".$chars;exit;
        if(strlen($text)<$chars)
        {
            $text = $text;
        }
        else
        {
            $text = substr($text,0,$chars)."...";
        }

        return $text;
    }

    function truncate_link($text,$chars,$link) {
    /*    $text = $text." ";
        $text = substr($text,0,$chars);
        $text = substr($text,0,strrpos($text,' '));*/
     //   echo strlen($text)." ".$chars;exit;
        $dot = "....";
        if(strlen($text)<$chars)
        {
            $text = closetags($text);
        }
        else
        {
            $string = substr($text,0,$chars).$dot;
            $text = closetags($string)."<a href='".$link."'>See More</a>";
        }

        return $text;
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

function getUser($userId,$cols = '*')
{
    global $db;
    $qSel = "SELECT ".$cols." from tbl_users WHERE id = '".$userId."'";
    $qrysel = $db->pdoQuery($qSel);
    $userDetails = $qrysel->result();
    return  $userDetails;
}
function getJobTitle($id){
    global $db;
    $qSel = "SELECT * from tbl_jobs WHERE id = ".$id;
    $qrysel0 = $db->pdoQuery($qSel);
    $userDetails = $qrysel0->result();
    $title = $userDetails['jobTitle'];
    return $title;
}
function getServiceTitle($id){
    global $db;
    $qSel = "SELECT * from tbl_services WHERE id = '".$id."'";
    $qrysel0 = $db->pdoQuery($qSel);
    $userDetails = $qrysel0->result();
    $title = $userDetails['serviceTitle'];
    return $title;
}



function getUserImage($userId)
{
    global $db;
    $qSel = "SELECT profileImg from tbl_users WHERE id = '".$userId."'";
    $qrysel0 = $db->pdoQuery($qSel);
    $userDetails = $qrysel0->result();
    $userData = '';
    if(!empty($userDetails))

        $userData = ($userDetails['profileImg']=='') ? SITE_UPD."th2_no_user_image.png" : SITE_USER_PROFILE.$userDetails['profileImg'];
    return $userData;
}

function getserviceImages($serviceId,$limit)
{
    global $db;
    if($serviceId!='')
    {
        $array = array();
        $qSel = "SELECT * from tbl_services_files WHERE servicesId = '".$serviceId."' limit ".$limit;
        $qrysel0 = $db->pdoQuery($qSel);
        $userDetails = $qrysel0->results();
        $userData = '';
        

        if(!empty($userDetails))
        {
            if(count($userDetails)>1)
            {
                foreach ($userDetails as $value) {
                    if(file_exists(DIR_SERVICES_FILE.$value['fileName'])){
                        array_push($array,SITE_SERVICES_FILE.$value['fileName']);
                    }
                    else{
                        array_push($array,SITE_UPD.'default-image_450.png');

                    }
                }
            }
            else
            {
                if(file_exists(DIR_SERVICES_FILE.$userDetails[0]['fileName'])){
                    array_push($array,SITE_SERVICES_FILE.$userDetails[0]['fileName']);
                }
                else{
                    array_push($array,SITE_UPD.'default-image_450.png');

                }   
            }
        }
        else{
             array_push($array,SITE_UPD.'default-image_450.png');
 
        }

        return $array;
    }
    else
    {
        return '';
    }
}

function job_applicant($jobId)
{
    global $db;
    $result = $db->pdoQuery("select * from tbl_job_bids where jobid='".$jobId."' ")->affectedRows();
    return $result;
}

function get_time_diff($timestamp)
{
    $daysleft = '0';
    $now = date_create(date('Y-m-d H:i:s'));

    $future_date = date_create($timestamp);
    $interval = $future_date->diff($now);
    $days = $interval->format('%d');
    $years = $interval->format('%y');
    $months = $interval->format('%m');
    $hours = $interval->format('%h');
    $minut = $interval->format('%i');
    $seconds = $interval->format('%s');

    $time = 'Ending in';
    if(strtotime(date('Y-m-d H:i:s')) < strtotime($timestamp))
    {
        if($years != '0'){
            $time .= " ".$years.' year(s)';
        }
        if($months != '0')
        {
            $time .= " ".$months.' month(s)';
        }
        if($days!='0'){
            $time .= " ".$days.' day(s)';
        }else{
            if($hours!=0){
                $time .= " ".$hours.' hour(s)';
            }
            if($minut!=0){
                $time .= " ".$minut.' minute(s)';
            }
        }
        
    }
    else
    {
        $time = "Expired";
    }

    return $time;
}

function get_skill($skill='all')
{
    global $db;

    if($skill!='all')
        $query = $db->pdoQuery("select * from tbl_skills where id IN(".$skill.") and isActive='y' and isApproved='y' and isDelete='n' ")->results();
    else
        $query = $db->pdoQuery("select * from tbl_skills where isActive='y' and isApproved='y' and isDelete='n' ")->results();

    $skill_list = "";
    foreach ($query as $value) {
        $skill_list .= $value['skill_name'].",";
    }
    return trim($skill_list,",");
}
function skill_list($skills='')
{
    global $db;
    $query = $db->pdoQuery("select * from tbl_skills where isActive='y' and isApproved='y' and isDelete='n' ")->results();
    $data = '<option value="">--Select skills--</option>';
    foreach ($query as $value) {
        $select = ($skills!='') ? (($skills == $value['id']) ? 'selected' : '') : '';
        $data .= "<option value='".$value['id']."' ".$select.">".$value['skill_name']."</option>";
    }
    return $data;
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

function service_status($status)
{
    $data = array();
    if($status == 'no')
    {
        $status = "New Order";
        $class = "badge-primary";
    }
    else if($status == 'ip')
    {
        $status = "In Progress";
        $class = "badge-warning";
    }
    else if($status == 'ar')
    {
        $status = "Ask For Refund";
        $class = "badge-danger";
    }
    else if($status == 'c')
    {
        $status = "Completed";
        $class = "badge-success";
    }
    else if($status == 'p')
    {
        $status = "Payment Pending";
        $class = "badge-danger";
    }
    else if($status == 'ud')
    {
        $status = "Under Dispute";
        $class = "badge-danger";
    }
    else if($status == 'ds')
    {
        $status = "Dispute Solved";
        $class = "badge-success";
    }
    else if($status == 'dsc')
    {
        $status = "Dispute Solved and Closed";
        $class = "badge-success";
    }
    $data['status']=$status;
    $data['class']=$class;
    return $data;
}
function service_status_webservice($status)
{
    $data = array();
    if($status == 'no')
    {
        $status = "New Order";
        $class = "#29a7df";
    }
    else if($status == 'ip')
    {
        $status = "In Progress";
        $class = "#e4952f";
    }
    else if($status == 'ar')
    {
        $status = "Ask For Refund";
        $class = "#dc3545";
    }
    else if($status == 'c')
    {
        $status = "Completed";
        $class = "#28a745";
    }
    else if($status == 'p')
    {
        $status = "Payment Pending";
        $class = "#dc3545";
    }
    else if($status == 'ud')
    {
        $status = "Under Dispute";
        $class = "#dc3545";
    }
    else if($status == 'ds')
    {
        $status = "Dispute Solved";
        $class = "#28a745";
    }
    else if($status == 'dsc')
    {
        $status = "Dispute Solved and Closed";
        $class = "#28a745";
    }
    $data['status']=$status;
    $data['class']=$class;
    return $data;
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
            "listingRatingDesc" => nl2br(filtering($comment)),
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

    // $id is seller id
    $subscribe = $db->select('tbl_users',array("subscribe_email","email"),array('id'=> $id))->result();

    if((isset($notification_pref) && $notification_pref=='y')){
        /* Sending mail to seller */
        $email_array = generateEmailTemplate($template_name,$data['email_content']);

        sendEmailAddress($subscribe['email'],$email_array['subject'],$email_array['message']);
        /* End mail */
    }
    /* Sending email to Admin */
    $data['email_content']['greetings']=FROM_NM;
    $array = generateEmailTemplate($template_name_admin,$data['email_content']);
    sendEmailAddress(trim(ADMIN_EMAIL),$array['subject'],$array['message']);
}
function filtering($value = '', $type = 'output', $valType = 'string', $funcArray = '') {
    global $abuse_array, $abuse_array_value;
    if(count($abuse_array) > 0){
        if ($valType != 'int' && $type == 'output') {
            $value = str_ireplace($abuse_array, $abuse_array_value, $value);
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
function export_to_excel($data, $name='export_to_excel', $heading_array=array()) {
    $name = $name . ".xls";
    /*header("Content-Disposition: attachment; filename=\"$name\"");
    header("Content-Type: application/vnd.ms-excel");*/
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$name.'"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
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
        ((empty($heading_array)) ? @array_walk($row, 'filterData') : '');
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
    $objWriter->save('php://output');
    exit;
}


/*Export CSV*/
function convert_to_csv($input_array, $output_file_name, $delimiter){
    $temp_memory = fopen('php://memory', 'w');
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
    return ($usersData['walletAmount'] == '') ? '0' : $usersData['walletAmount'];
}

function userReemAmount($userId)
{
    global $db;
    $redeemAmount = $db->pdoQuery("select SUM(amount) As redeemAmount from tbl_redeem_request where userId=? and paymentStatus=?",array($userId,'p'))->result();
    return ($redeemAmount['redeemAmount']=='') ? '0' : $redeemAmount['redeemAmount'];
}

function finalWalletAmount($userId)
{
    $wallet_amount = checkUserWalletAmount($userId);
    $redeem_amount = userReemAmount($userId);

    $final_amount = $wallet_amount - $redeem_amount;
    return $final_amount;
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

/*function CheckRepeatEntry($table_name,$date_field,$ipField,$checkRepeatMinute=3)
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
}*/
function CheckRepeatEntry($table_name, $date_field, $ipField, $order_by_field="id", $block_minutes_diff = 30) {
    global $db;
    $ip_address = get_ip_address();

    $fetchData = $db->pdoQuery("SELECT ".$date_field." from ".$table_name." where ".$ipField."  = '".$ip_address."' ORDER BY ".$date_field." DESC LIMIT 1")->result();

    if(!empty($fetchData[$date_field])){
        $db_date = $fetchData[$date_field];

        if((strtotime(date('Y-m-d H:i:s')) - strtotime($db_date)) <= $block_minutes_diff)
        {
            $exist_in_block = $db->select('tbl_black_list', 'id', array("ipAddress"=>get_ip_address()))->affectedRows();
            $exist_block = $db->select('tbl_black_list', 'id', array("ipAddress"=>get_ip_address()))->result();
            if($exist_in_block > 0)
            {
                $counter = $exist_block['counter'] + 1;
                $db->update("tbl_black_list",array("counter"=>$counter),array("ipAddress"=>get_ip_address()));
                return false;
            } else {
                $db->insert("tbl_black_list", array("ipAddress"=>get_ip_address(), 'counter'=>'1',"createdDate"=>date('Y-m-d H:i:s')))->result();
                return false;
            }
        }
    }
    return true;
}
function printr($data, $exit=false) {
    print '<pre>';
    print_r($data);
    print '</pre>';
    ($exit ? exit() : '');
}

function checkLenghtLimit($string){
    $limit=50;
    if(strlen($string) > $limit){
        return true;
    }
    return false;
}

// function removes script tag from the content
function filterscriptTags($content) {
    $content = str_replace(array('&lt;script&gt;', '&lt;/script&gt;','<script>','</script>'),array('','','',''),$content);
    return $content;
}

function check_url($url){
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    $result = curl_exec($curl);
    if(SITE_CHK_URL == 'n'){
        if ($result !== false)
        {
          $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
          if ($statusCode == 404)
          {
            return 0;
        }
        else
        {
           return 1;
       }
   }
   else
   {
      return 0;
  }
} else {
    return 1;
}

}
/* checking image is proper or not */
function check_image($img){
    $img1 = BLANK_IMAGE;
    $img2 = $img;

    $first_img = base64_encode(file_get_contents($img1));
    $second_img = base64_encode(file_get_contents($img2));

    if($first_img == $second_img){
        return 1;
    } else {
        return 0;
    }
}


function slug_avail_check($table,$field,$field_val)
{
    global $db;
    /*echo "select $field from $table where $field= $field_val";
    die;*/
    $avail = $db->pdoQuery("select $field from $table where $field=? ",array($field_val))->affectedRows();
    if($avail>0)
    {
        return true;
    }
    else
    {
        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => 'Something went wrong'));
        redirectPage(SITE_URL);
    }
}

function get_create_time()
{
    $start  = date_create('2017-11-23');
    $end    = date_create(); // Current time and date
    $diff   = date_diff( $start, $end );

    /*echo 'The difference is ';
    echo  $diff->y . ' years, ';
    echo  $diff->m . ' months, ';
    echo  $diff->d . ' days, ';
    echo  $diff->h . ' hours, ';
    echo  $diff->i . ' minutes, ';
    echo  $diff->s . ' seconds';*/

    return $diff->s;
}

// function display_rating($rate, $div=true) {
//     $per = ((!empty($rate) && $rate=="5") ? 100 : ((!empty($rate) && $rate=="4") ? 80 : ((!empty($rate) && $rate=="3") ? 60 : ((!empty($rate) && $rate=="2") ? 40 : ((!empty($rate) && $rate=="1") ? 20 : 0 )))));
//     $dispRate = $rate . " Star" . (($rate > 1) ? "s" : "");
//     $return_content = (($div==true) ? '<div class="rating" title="'.$dispRate.'">' : '');
//     $return_content .= '
//         <div class="star-ratings-sprite" title="'.$dispRate.'">
//             <span style="width:'.$per.'%" class="star-ratings-sprite-rating"></span>
//         </div>
//     ';
//     $return_content .= (($div==true) ? '</div>' : '');
//     return $return_content;
// }

function customerSpentAmount($custId,$isSpan='y'){
    global $db;
    $totalAmount = $db->pdoQuery("select sum(amount) as amt from tbl_wallet where transactionType = 'payToFreelancer' AND entity_type = 'ml' and userType = ? and userid = ?",array('c',$custId))->result();
    if($totalAmount['amt'] !=''){
        if($isSpan=='n'){
            return $totalAmount['amt'].CURRENCY_SYMBOL;
        }else{
            return $totalAmount['amt']."<span>".CURRENCY_SYMBOL."</span>";
        }
    } else {
        return '0';
    }
}


function load_more_pageNo($pageNo,$limit)
{
    $num_rec_per_page=$limit;
    $start_from = ($pageNo-1) * $num_rec_per_page;
    return $start_from;
}
function load_more_data($total_data,$limit,$limit_data,$pageNo)
{
    $data = array();

    $data['page'] = ceil($total_data/$limit);
    if($limit_data<$limit || ($data['page']==$pageNo))
    {
        $data['btn'] = "hide";
    }
    else
    {
        $data['btn'] = "";
    }
    return $data;
}

function getServiceCategory($cat='',$l_id=''){
    global $db;
    // for english and arabic tab we need to pass langauge Id 
    if(!empty($l_id)){
        // For second tab categories
        $category = $db->pdoQuery("select c.id As catId,c.category_name_$l_id as category_name from tbl_category As c
        LEFT JOIN tbl_subcategory As s ON s.maincat_id = c.id
        where c.isActive=? and c.isDelete=? and (c.category_type = ? OR c.category_type = ?) and s.isActive = ? and s.isDelete = ? and s.maincat_id IS NOT NULL group by s.maincat_id",array('y','n','b','s','y','n'))->results();    
    }else{
        $category = $db->pdoQuery("select c.id As catId,c.".l_values('category_name')." as category_name from tbl_category As c
            LEFT JOIN tbl_subcategory As s ON s.maincat_id = c.id
            where c.isActive=? and c.isDelete=? and (c.category_type = ? OR c.category_type = ?) and s.isActive = ? and s.isDelete = ? and s.maincat_id IS NOT NULL group by s.maincat_id",array('y','n','b','s','y','n'))->results();
    }    
    $default_cons = lang_lables("SELECT_CATEGORY",$l_id); 
    $category_content = '<option value="">'.$default_cons.'</option>';

    foreach ($category as $key => $value) {
        $select = ($cat == $value['catId']) ? 'selected' : '';
        $category_content .=  "<option value='".$value['catId']."' ".$select.">".$value['category_name']."</option>";
    }
    return $category_content;
}

function getCategory($cat=''){
    global $db;
    $category = $db->pdoQuery("select c.id As catId,c.".l_values('category_name')." as category_name from tbl_category As c
        LEFT JOIN tbl_subcategory As s ON s.maincat_id = c.id
        where c.isActive=? and c.isDelete=? and (c.category_type = ? OR c.category_type = ?) and s.isActive = ? and s.isDelete = ? and s.maincat_id IS NOT NULL group by s.maincat_id",array('y','n','b','j','y','n'))->results();

    //printr($category,1);
    $category_content = '<option value="">--Select Category--</option>';
    foreach ($category as $key => $value) {
        $select = ($cat == $value['catId']) ? 'selected' : '';
        $category_content .=  "<option value='".$value['catId']."' ".$select.">".$value['category_name']."</option>";
    }
    return $category_content;
}

function getSubcategory($main_cat,$sub_cat='',$lid='')
{
    global $db;
    if(!empty($lid)){
        $category = $db->pdoQuery("SELECT id,subcategory_name_1 as subcategory_name FROM tbl_subcategory WHERE isActive='y' AND maincat_id=? AND isDelete='n'",array($main_cat))->results();
    }else{
        $category = $db->pdoQuery("SELECT id,".l_values('subcategory_name')." as subcategory_name FROM tbl_subcategory WHERE isActive='y' AND maincat_id=? AND isDelete='n'",array($main_cat))->results();
    }
    $default_cons = lang_lables("SELECT_SUB_CATEGORY",$lid); 
    $category_content = '<option value="">'.$default_cons.'</option>';

    foreach ($category as $key => $value) {
        $select = ($sub_cat == $value['id']) ? 'selected' : '';
        $category_content .=  "<option value='".$value['id']."' ".$select." >".$value['subcategory_name']."</option>";
    }
    return $category_content;
}

function getServiceId($slug)
{
    global $db;
    $service_detail = $db->pdoQuery("select id from tbl_services where servicesSlug=?",array($slug))->result();
    return $service_detail['id'];
}

function getAvgUserReview($id,$type='freelancer'){
    global $db;
    if($type == 'freelancer')
    {
        $result = $db->pdoQuery("select AVG(startratings) As rating from tbl_reviews where freelancerId='".$id."' and workClarification!='' and punctality!='' and expertise!='' and communication!='' and workQuality!='' ")->result();
    }
    else
    {
        $result = $db->pdoQuery("select AVG(customerStarRating) As rating from tbl_reviews where customerId='".$id."' and reqClarification!='' and onTimePayment!='' and onTimeResponse!='' and custComm!='' ")->result();
    }
    return ($result['rating']=='') ? '0' : $result['rating'];
}

function getJobStatus($status){
    if($status == 'p')
    {
        $data = "Pending";
    }
    else if($status == 'c')
    {
        $data = "Closed";
    }
    else if($status == 'h')
    {
        $data = "Hired";
    }
    else if($status == 'ip')
    {
        $data = "In Progress";
    }
    else if($status == 'ud')
    {
        $data = "Under Dispute";
    }
    else if($status == 'dsp')
    {
        $data = "dispute solved in progress";
    }
    else if($status == 'dsc')
    {
        $data = "dispute solved and closed";
    }
    else if($status == 'dsCo')
    {
        $data = "dispute solved and completed";
    }
    else if($status == 'co')
    {
        $data = "Completed";
    }
    return $data;
}


function get_country_list($country='all')
{
    global $db;
    if($country!="" && $country!='all'){
        $query = $db->pdoQuery("select * from tbl_country where id IN(".$country.") ")->results();
        $country_list = "";
        foreach ($query as $value) {
            $country_list .= $value['country_name'].",";
        }
        return trim($country_list,",");
    }
    else{
        return "All Countries";
    }
}
function get_country_list_for_view_jobs($country)
{
    global $db;

    if($country!=""){
        $query = $db->pdoQuery("select * from tbl_country where id IN(".$country.") ")->results();
    }
    else{
        $query = "All Countries";
    }

    $country_list = "";
    if(is_array($query)){
        foreach ($query as $value) {
            $country_list .= $value['country_name'].",";
        }
        return trim($country_list,",");
    }
    else{
        $country_list=$query;
        return trim($country_list);
    }

}

function user_language_list($id)
{
    global $db;
    $lang = $db->pdoQuery("select GROUP_CONCAT(languageId) As userlang from tbl_user_language where userId =?",array($id))->result();
    return $lang['userlang'];
}

function getCredits($id)
{
    global $db;
    $plan = $db->pdoQuery("select * from tbl_user_plan where userId =".$id." and isCurrent ='y' ");
    if($plan->affectedRows() > 0){
        $plan = $plan->result();
        $credit = $plan['no_credit'] - $plan['used_credit'];
        $data['credit'] = $credit;
        $data['planId'] = $plan['id'];
    } else {
        $data['credit'] = $data['planId'] = '0';
    }
    return $data;
}
function pg_enc($string) {
    return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(RD_VAL), $string, MCRYPT_MODE_CBC, md5(md5(RD_VAL))));
}

/*****/

function pg_dec($string) {
    return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(RD_VAL), base64_decode($string), MCRYPT_MODE_CBC, md5(md5(RD_VAL))), "\0");
}
/*function getCommision($budget,$type){
    global $db;
    $data = $db->pdoQuery("SELECT specificAmount FROM tbl_commision WHERE  commisionType ='".$type."' and ".$budget." BETWEEN minPrice AND maxPrice")->result();

    if(count($data) > 0) {
        return $data['specificAmount'];
    } else {
        return DEFAULT_SERVICE_COMM;
    }
}*/
function getCommision($budget,$type){
    global $db;
    $commision = 0;
    if(!empty($budget) && !empty($type)){
        $data = $db->pdoQuery("SELECT * FROM tbl_commision WHERE  commisionType ='".$type."' and ".$budget." BETWEEN minPrice AND maxPrice")->result();
        if(!empty($data)) {
            if($data["commision_by"]=="percentage"){
                $commision = ($budget*$data['percentage']/100);
            }else{
                $commision = $data['specificAmount'];
            }            
        } else if($type=="E") {
            $commision = ($budget*DEFAULT_ESCROW_COMM/100);
        }else if($type=="S"){
            $commision = ($budget*DEFAULT_SERVICE_COMM/100);
        }
    }
    return $commision;
}
function getDateDiff($date) {
    /* Getting Date difference in days */
    $start = date_create(date('Y-m-d H:i:s'));
    $end = date_create($date);
    $diff = date_diff($end,$start);

    if($diff->invert == 0){
        if($diff->h > 0){
            return  $diff->h;
        }
        return $diff->d;
    } else {
        return -1;
    }
}
function noMilestone($jobId)
{
    global $db;
    $data = $db->pdoQuery("select count(id) As totalMilestone from tbl_milestones where jobId=? ",array($jobId))->result();
    return $data['totalMilestone'];
}
function earnedAmountFreelancer($userId,$isSpan='y')
{
    global $db;
    $user_service = $db->pdoQuery("select group_concat(id) As orderId from tbl_services_order where freelanserId=? and orderStatus=? ",array($userId,'c'))->result();

    if($user_service['orderId']!='')
    {
        $service_detail = $db->pdoQuery("SELECT SUM(amount) As totalEarned FROM `tbl_wallet` WHERE entity_id IN(".$user_service['orderId'].") and `entity_type`='s' and `status`='completed' and transactionType IS NULL")->result();
        $serviceFees = $service_detail['totalEarned'];
    }
    else
    {
        $serviceFees = 0;
    }
    $user_jobs = $db->pdoQuery("select group_concat(jobid) As jobList from tbl_job_bids where userId=? and isHired=?",array($userId,'y'))->result();
    if($user_jobs['jobList']!='')
    {
        $job_detail = $db->pdoQuery("SELECT SUM(CASE WHEN m.paymentstatus='c' THEN w.amount ELSE 0 END) AS totalamount FROM `tbl_milestones` AS m LEFT JOIN tbl_wallet AS w ON w.entity_id = m.id LEFT JOIN tbl_users AS u ON u.id = m.ownerid LEFT JOIN tbl_jobs AS j ON j.id = m.jobid WHERE m.jobid IN(".$user_jobs['jobList'].") AND (CASE WHEN (m.paymentstatus='c') THEN w.entity_type='ml' ELSE 1 END)")->result();
        $jobFees = $job_detail['totalamount'];
    }
    else
    {
        $jobFees = 0;
    }

    $totalFees = $serviceFees + $jobFees;
    return $isSpan=='y'?($totalFees."<span>".CURRENCY_SYMBOL."</span>"):$totalFees;
}
function serviceSlugCheck($slug)
{
    global $db;
    $query = $db->pdoQuery("select * from tbl_services where servicesSlug=?",array($slug))->affectedRows();
    return $query;
}

function freelancerSlugCheck($slug)
{
    global $db;
    $query = $db->pdoQuery("select * from tbl_users where userSlug=?",array($slug))->affectedRows();
    return $query;
}

function jobSlugCheck($slug)
{
    global $db;
    $query = $db->pdoQuery("select * from tbl_jobs where jobSlug=?",array($slug))->affectedRows();
    return $query;
}
function notifyCheck($field,$id)
{
    global $db;
    $query = $db->pdoQuery("select $field from tbl_users where id=?",array($id))->result();
    if($query[$field]=='y')
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

function pmbLink($id)
{
    global $db;
    $query = $db->pdoQuery("select * from tbl_pmb where senderId=? OR ReceiverId=?",array($id,$id))->affectedRows();
    return $query;
}

function checkDelete($id,$userId)
{
    global $db;
    $query = $db->pdoQuery("select * from tbl_services_order where freelanserId=? and servicesId=? and (serviceStatus!='c' OR serviceStatus!='cl') ",array($userId,$id))->affectedRows();
    return $query;
}

function checkJobDelete($id)
{
    global $db;
    $query = $db->pdoQuery("select * from tbl_jobs where id = ?",array($id))->result();
    if($query['jobStatus'] == 'c' || $query['jobStatus'] == 'dsc' || $query['jobStatus'] == 'co' || $query['jobStatus'] == 'dsCo')
    {
        return '1';
    }
    return '0';
}
function checkUserFreePlan($id)
{
    global $db;
    $freeplandetail = $db->pdoQuery("Select * from tbl_credit_package where price=0")->result();
    if($freeplandetail['id']!='')
    {
        $query = $db->pdoQuery("select * from tbl_user_plan where userId=? and planId=?",array($id,$freeplandetail['id']))->affectedRows();
        return $query;
    }
}

function checkFeaturedExpiry($payment_date,$f_duration){
    $ex_class = '';

    $init_date = date_create($payment_date);
    $curr_date = date_create(date("Y-m-d H:i:s"));
    $f_interval = $curr_date->diff($init_date);

    $int_mins = (($f_interval->d)*24*60) + 1;
    $dur_mins = $f_duration*24*60;
    $diff_r = $int_mins-$dur_mins;
    if($diff_r>0){
        $ex_class = 'expired';
    }
    return $ex_class;
}

function checkClass($featured,$deleted,$expiry="")
{
    $class = 'hide';
    if($featured=='n' && $deleted=='n')
    {
        $class = 'hide';
    }
    else if($deleted=='y')
    {
        $class = 'deleted-class';
    }
    else if($featured =='y')
    {
        $class = 'featured';
    }
    return $class;
}





class CropAvatar {
    private $src;
    private $data;
    private $dst;
    private $type;
    private $extension;
    private $d_file_width;
    private $d_file_height;
    private $msg;

    function __construct($src, $data, $file,$file_size = []) {
            //echo $src;
            /*echo $src;
            echo $src;*/
            $this->d_file_width = !empty($file_size['width']) ? $file_size['width'] : 220;
            $this->d_file_height = !empty($file_size['height']) ? $file_size['height'] : 220;
            $this->filesrc = $src;
            $this->setSrc($src,$file);
            $this->setData($data);
            $this->setFile($file);
            $this->crop($this->src, $this->dst, $this->data);
        }

        private function setSrc($src,$file) {
            if (!empty($src)) {
                //$type = exif_imagetype($src);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $type = $ext == 'gif' ? 1 : ($ext == 'jpeg' || $ext == 'jpg' ? 2 : 3);
                if ($type) {
                    $this->src = $src;
                    $this->type = $type;
                    $this->extension = image_type_to_extension($type);
                    $this->setDst();
                }
            }
        }

        private function setData($data) {
            if (!empty($data)) {
                $this->data = json_decode(stripslashes($data));
            }
        }

        private function setFile($file) {
            $errorCode = $file['error'];

            if ($errorCode === UPLOAD_ERR_OK) {
                //$type = exif_imagetype($file['tmp_name']);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $type = $ext == 'gif' ? 1 : ($ext == 'jpeg' || $ext == 'jpg' ? 2 : 3);
                if ($type) {
                    $extension = image_type_to_extension($type);
                    $src = $this->filesrc . md5(mt_rand()) . $extension;

                    if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG) {

                        if (file_exists($src)) {
                            unlink($src);
                        }

                        $result = @move_uploaded_file($file['tmp_name'], $src);

                        if ($result) {
                            $this->src = $src;
                            $this->type = $type;
                            $this->extension = $extension;
                            $this->setDst();
                        } else {
                            $this->msg = 'Failed to save file';
                        }
                    } else {
                        $this->msg = 'Please upload image with the following types: JPG, PNG, GIF';
                    }
                } else {
                    $this->msg = 'Please upload image file';
                }
            } else {
                $this->msg = $this->codeToMessage($errorCode);
            }
        }

        private function setDst() {
            $this->dst = $this->src;
        }

        private function crop($src, $dst, $data) {
            //echo "called";
            if (!empty($src) && !empty($dst) && !empty($data)) {
                switch ($this->type) {
                    case IMAGETYPE_GIF:
                    $src_img = imagecreatefromgif($src);
                    break;

                    case IMAGETYPE_JPEG:
                    $src_img = imagecreatefromjpeg($src);
                    break;

                    case IMAGETYPE_PNG:
                    $src_img = imagecreatefrompng($src);
                    break;
                }

                if (!$src_img) {
                    $this->msg = "Failed to read the image file";
                    return;
                }

                $size = getimagesize($src);
      $size_w = $size[0]; // natural width
      $size_h = $size[1]; // natural height

      $src_img_w = $size_w;
      $src_img_h = $size_h;

      $degrees = $data->rotate;

      // Rotate the source image
      if (is_numeric($degrees) && $degrees != 0) {
        // PHP's degrees is opposite to CSS's degrees
        $new_img = imagerotate( $src_img, -$degrees, imagecolorallocatealpha($src_img, 0, 0, 0, 127) );

        imagedestroy($src_img);
        $src_img = $new_img;

        $deg = abs($degrees) % 180;
        $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

        $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);
        $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);

        // Fix rotated image miss 1px issue when degrees < 0
        $src_img_w -= 1;
        $src_img_h -= 1;
    }

    $tmp_img_w = $data->width;
    $tmp_img_h = $data->height;
    $dst_img_w = $this->d_file_width;
    $dst_img_h = $this->d_file_height;

    $src_x = $data->x;
    $src_y = $data->y;

    if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
        $src_x = $src_w = $dst_x = $dst_w = 0;
    } else if ($src_x <= 0) {
        $dst_x = -$src_x;
        $src_x = 0;
        $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
    } else if ($src_x <= $src_img_w) {
        $dst_x = 0;
        $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
    }

    if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
        $src_y = $src_h = $dst_y = $dst_h = 0;
    } else if ($src_y <= 0) {
        $dst_y = -$src_y;
        $src_y = 0;
        $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
    } else if ($src_y <= $src_img_h) {
        $dst_y = 0;
        $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
    }

      // Scale to destination position and size
    $ratio = $tmp_img_w / $dst_img_w;
    $dst_x /= $ratio;
    $dst_y /= $ratio;
    $dst_w /= $ratio;
    $dst_h /= $ratio;

    $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);
      // Add transparent background to destination image
    imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
    imagesavealpha($dst_img, true);

    $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

    if ($result) {
        if (!imagepng($dst_img, $dst)) {
            $this->msg = "Failed to save the cropped image file";
        }
    } else {
        $this->msg = "Failed to crop the image file";
    }

    imagedestroy($src_img);
    imagedestroy($dst_img);
}
}

private function codeToMessage($code) {
    $errors = array(
        UPLOAD_ERR_INI_SIZE =>'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE =>'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL =>'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE =>'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR =>'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE =>'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION =>'File upload stopped by extension',
    );

    if (array_key_exists($code, $errors)) {
        return $errors[$code];
    }

    return 'Unknown upload error';
}

public function getResult() {
    return !empty($this->data) ? $this->dst : $this->src;
}

public function getMsg() {
    return $this->msg;
}
}


function is_home(){
    $data = sprintf(
        "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME'],
        $_SERVER['REQUEST_URI']
    );

    $data = explode('?',$data);

    return trim($data[0],'/') == trim(SITE_URL,'/') ? true : false;
}


function add_home_class(){

    
    if(!empty($_SESSION['userId'])){
        if($_SESSION['pickgeeks_userType'] == 'Customer'){
            if(is_home()){
                return 'user_customer customer_home';    
            }else{
                return "user_customer";
            }
        }else if($_SESSION['pickgeeks_userType'] == 'Freelancer'){
            if(is_home()){
                return 'user_freelancer freelancer_home';    
            }else{
                return "user_freelancer";
            }
        }else{
            if(is_home()){
                return 'homepage_class';    
            }else{
                return "";
            }
        }
    }else{
        if(is_home()){
            return 'homepage_class';    
        }else{
            return "";
        }
    }
}



function notify($type,$user_id,$message,$link=''){
    global $db;
    if(!empty($type) && isset($user_id) && !empty($message)){
        $arr = [
            'userId' => $user_id,
            'message' => $message,
            'detail_link' => $link,
            'isRead' => 'n',
            'notificationType' => $type,
            'createdDate' => date('Y-m-d H:i:s'),
        ];

        $id = $db->insert("tbl_notification",$arr)->getLastInsertId();
        if($id != 0){
            return true;
        }
    }
    return false;
}
function getUserExpLevel($explevel){
    $exp_level='';
    if($explevel=='f'){
        $exp_level = "Beginner";
    }
    else if($explevel=='i'){
        $exp_level = "Intermediate";
    }
    else {
        $exp_level = "PRO";
    }
    return $exp_level;
}

function getJobExpLevel($explevel){
    $exp_level='';
    if($explevel=='b'){
        $exp_level = "Beginner";
    }
    else if($explevel=='i'){
        $exp_level = "Intermediate";
    }
    else {
        $exp_level = "PRO";
    }
    return $exp_level;
}

function getAvatar($msg){
    if (strpos($msg, 'Your service request has been accepted') !== false) {
        return 'S';
    }
    if (strpos($msg, 'Your redeem request has been declined') !== false) {
        return 'R';
    }
    if (strpos($msg, 'You have received new message') !== false) {
        return 'M';
    }
    return 'P';
}
function getBidStatus($hiredStatus){
    $bid_status = '';
    if($hiredStatus=='n'){
        $bid_status='Pending';
    }
    if($hiredStatus=='y'){
        $bid_status='Hired';
    }
    if($hiredStatus=='r'){
        $bid_status='Rejected';
    }
    if($hiredStatus=='a'){
        $bid_status='Accepted';
    }
    return $bid_status;
}


function getEmailUser($id){
    global $db;
    $udata = getUser($id);
    if(!empty($udata)){
        return $db->pdoQuery('SELECT id FROM `tbl_users` WHERE email = ?',[$udata['email']])->results();
    }
    return [];
}


function updateWallet($user_id,$amount,$type='p'){
    global $db;
    $emails = getEmailUser($user_id);
    $user = getUser($user_id);    
    if(!empty($emails) && !empty($amount)){
        $walletamount = !empty($user['walletAmount']) ? $user['walletAmount'] : 0;
        $walletamount = $type == 'p' ? $walletamount + $amount : $walletamount - $amount; 
        if($walletamount < 0){
            return false;
        }
        foreach($emails as $value){
            $db->update("tbl_users",array("walletAmount"=>number_format($walletamount,2)),array("id"=>$value['id']));
        }
        return true;
    }
    return false;
}

function release_job_payment($milestone_id){
    global $db;
    $mdata = $db->pdoQuery('SELECT * FROM `tbl_milestones` WHERE id = ? ',[$milestone_id])->result();
    $allmdata = $db->pdoQuery( "SELECT m.*,m.amount as m_amount,dsp.entityId as dId FROM `tbl_milestones` as m 
        LEFT JOIN tbl_dispute as dsp on m.id = dsp.entityId AND dsp.type = 'ML' 
        WHERE  m.jobId = ? AND m.paymentStatus != 'c'
        GROUP BY m.id",[$mdata['jobId']])->results();
    $jdata = $db->pdoQuery('SELECT * FROM `tbl_jobs` WHERE id = ?',[$mdata['jobId']])->result();
    $payable_amount = 0;

    foreach ($allmdata as $key => $value) {
        if(empty($value['dId'])){
            $payable_amount = $payable_amount + $value['m_amount'];
        }
    }


    if(!empty($payable_amount) && !empty($jdata['posterId'])){
        return updateWallet($jdata['posterId'],$payable_amount);
    }
    return false;
}



function getFileImage($filename){
    $arr = [
       'jpg' => SITE_IMG."attachment/jpg.png",
       'pdf' => SITE_IMG."attachment/pdf.png",
       'ppt' => SITE_IMG."attachment/ppt.png",
       'xls' => SITE_IMG."attachment/xls.png",
       'csv' => SITE_IMG."attachment/csv.png",
       'audio' => SITE_IMG."attachment/audio.png",
       'doc' => SITE_IMG."attachment/doc.png",
       'png' => SITE_IMG."attachment/png.png",
       'txt' => SITE_IMG."attachment/txt.png",
       'video' => SITE_IMG."attachment/video.png",
       'mp4' => SITE_IMG."attachment/video.png",
       'avi' => SITE_IMG."attachment/video.png",
       'mpeg' => SITE_IMG."attachment/video.png",
       'unknown' => SITE_IMG."attachment/unknown.png"
   ];


   $ext =  !empty($filename) ? explode('.',$filename)  : '';
   $ext = !empty($ext[1]) ? strtolower($ext[1]) : '';

   return !empty($arr[$ext]) ? $arr[$ext] : $arr['unknown'];

}

function getLang($type = ''){
    global $db;
    $where = $type == '' ? 'isActive ="y" ' : ' 1 = 1 ';
    return $db->pdoQuery("SELECT * FROM `tbl_language` where ".$where)->results();
}

function getLangarray($name,$title){
    $arr[] = ['id' => 0,'f_name' => $name,'f_title' => $title.' (Default)'];
    foreach (getLang() as $key => $value) {
        $arr[] = ['id' => 0,'f_name' => $name.'_'.$value['id'],'f_title' => $title.' ('.$value['language'].')'];
    }
    return $arr;
}

function getLangValues($table,$id,$name,$title,$where = ''){
    global $db;
    $final_content = '';
    $arr = getLangarray($name,$title);
    $wherecond = !empty($where) ? $where : ' id = '.$id;
    $data = $db->pdoQuery("SELECT * FROM `".$table."` where ".$wherecond)->result();
    foreach ($arr as $key => $value) {
        $arr[$key]['f_value'] = !empty($data[$value['f_name']]) ? $data[$value['f_name']] : '';
    }
    return $arr;
}

function getLangForm($table,$id,$name,$title,$content,$where = ''){
    global $db;
    $final_content = '';
    $arr = getLangarray($name,$title);
    $id = !empty($id) ? $id : 0;
    $wherecond = !empty($where) ? $where : ' id = '.$id;
    $data = $db->pdoQuery("SELECT * FROM `".$table."` where ".$wherecond)->result();
    foreach ($arr as $key => $value) {
        $value['f_value'] = !empty($data[$value['f_name']]) ? $data[$value['f_name']] : '';
        $v_values = array_values($value);
        $v_keys = array_keys($value);
        foreach ($v_keys as $key2 => $value2) {
            $v_keys[$key2] = '%'.strtoupper($value2).'%';
        }
        $final_content .= str_replace($v_keys, $v_values, $content);
    }
    return $final_content;
}

function setfeilds($obj,$name,$default = ''){
    foreach (getLangarray($name,'') as $key => $value) {
        $v = $value['f_name'];
        $obj->$v = !empty($_POST[$v]) ? $_POST[$v] : $default;
    }
    return $obj;
}
function pre_print($data,$is_exit=true) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    if($is_exit)exit;
}


function custom_encoder($string) {
    return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(CST_KEY), $string, MCRYPT_MODE_CBC, md5(md5(CST_KEY))));
}

function custom_decoder($string) {
    return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(CST_KEY), base64_decode($string), MCRYPT_MODE_CBC, md5(md5(CST_KEY))), "\0");
}

/*
    Prevent Repeated Entry
    $block_minutes_diff - Different in seconds
    Query => SELECT createddate FROM `tbl_users` WHERE ipaddress = "127.0.0.1" ORDER BY id DESC LIMIT 0, 1;
*/

function getLangVal($langId, $constant) {
    global $db;
    $value = !empty($langId)?"value_".$langId:"value";
    $lang_val = $db->select('tbl_language_constant', array($value), array("constant" => $constant))->result();
    return $lang_val[$value];
}

function sendPushtoAndroid($registrationIds, $msg) {
    $notification = array(
        "body" => $msg['notificationText'],
        "sound" => "default",
        "click_action" => "FCM_PLUGIN_ACTIVITY",
        "icon" => "fcm_trans.png",
        "large_icon" => "fcm_trans.png",
        "show_in_foreground" => true
    );
    $fields = array('registration_ids' => $registrationIds, 'data' => $msg, 'notification' => $notification, "priority" => "high");
    $headers = array('Authorization: key='.API_ACCESS_KEY, 'Content-Type: application/json');
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);
}

function CheckAuthToken() {
    global $db;
    $allowed = false;
    $header_data = getallheaders();
    $userId = (!empty($header_data['Content-Id']) ? $header_data['Content-Id'] : 0);
    $header_authToken = (!empty($header_data['Content-Header']) ? $header_data['Content-Header'] : 0);

    if(!empty($userId) && !empty($header_authToken)) {
        $current_auth_exist = getTablevalue('tbl_users', 'authToken', array('id' => $userId));
        if(!empty($current_auth_exist) && ($header_authToken == $current_auth_exist)) {
            $allowed = true;
        }
    }
    return $allowed;
}

function job_status($jobStatus)
{
    $result=array();
    if($jobStatus=='p')
    {
        $job_status = PENDING_LABEL;
        $job_class = "badge-warning";
    }
    else if($jobStatus=='c')
    {
        $job_status = C_SO_CLOSED_LBL;
        $job_class = "badge-success";
    }
    else if($jobStatus=='h')
    {
        $job_status = HIRED_LABEL;
        $job_class = "badge-secondary";
    }
    else if($jobStatus=='ip')
    {
        $job_status = C_SO_IN_PROGRESS_LBL;
        $job_class = "badge-warning";
    }
    else if($jobStatus=='ud')
    {
        $job_status = C_SO_UNDER_DISPUTE_LBL;
        $job_class = "badge-danger";
    }
    else if($jobStatus=='dsp')
    {
        $job_status = DISPUTE_SOLVED_AND_WORK_IN_PROGRESS;
        $job_class = "badge-warning";
    }
    else if($jobStatus=='dsc')
    {
        $job_status = DISPUTE_SOLVED_AND_JOB_CLOSED;
        $job_class = "badge-success";
    }
    else if($jobStatus=='dsCo')
    {
        $job_status = DISPPUTE_SOLVED_AND_JOB_COMPLETED;
        $job_class = "badge-primary";
    }
    else if($jobStatus=="co")
    {
        $job_status = C_SO_COMPLETED_LBL;           
        $job_class = "badge-primary";
    }
    $result["job_status"]=$job_status;
    $result["job_class"]=$job_class;
    return $result;
}
function job_status_webservice($jobStatus)
{
    $result=array();
    if($jobStatus=='p')
    {
        $job_status = PENDING_LABEL;
        $job_class = "#e4952f";
    }
    else if($jobStatus=='c')
    {
        $job_status = C_SO_CLOSED_LBL;
        $job_class = "#28a745";
    }
    else if($jobStatus=='h')
    {
        $job_status = HIRED_LABEL;
        $job_class = "#858585";
    }
    else if($jobStatus=='ip')
    {
        $job_status = C_SO_IN_PROGRESS_LBL;
        $job_class = "#e4952f";
    }
    else if($jobStatus=='ud')
    {
        $job_status = C_SO_UNDER_DISPUTE_LBL;
        $job_class = "#dc3545";
    }
    else if($jobStatus=='dsp')
    {
        $job_status = DISPUTE_SOLVED_AND_WORK_IN_PROGRESS;
        $job_class = "#e4952f";
    }
    else if($jobStatus=='dsc')
    {
        $job_status = DISPUTE_SOLVED_AND_JOB_CLOSED;
        $job_class = "#28a745";
    }
    else if($jobStatus=='dsCo')
    {
        $job_status = DISPPUTE_SOLVED_AND_JOB_COMPLETED;
        $job_class = "#29a7df";
    }
    else if($jobStatus=="co")
    {
        $job_status = C_SO_COMPLETED_LBL;           
        $job_class = "#29a7df";
    }
    $result["job_status"]=$job_status;
    $result["job_class"]=$job_class;
    return $result;
}
function addOrdinalNumberSuffix($num) {
  if (!in_array(($num % 100),array(11,12,13))){
    switch ($num % 10) {
      // Handle 1st, 2nd, 3rd
      case 1:  return $num.'st';
      case 2:  return $num.'nd';
      case 3:  return $num.'rd';
    }
  }
  return $num.'th';
}
// For get constant lable using language
function lang_lables($constant,$lang_id=0){
    global $db;
    $value = !empty($lang_id) ? "value_".$lang_id:"value";
    $res = $db->pdoQuery("SELECT * FROM tbl_language_constant WHERE constant=?",array($constant))->result();
    return !empty($res[$value])?$res[$value]:""; 
}
function subHeaderContent($page_name){
    $sub_content = new MainTemplater(DIR_TMPL . "/sub_header_content-sd.skd");
    $sub_content = $sub_content->compile();
    $array = array( 
        "%PROFILE_ACTIVE%" => $page_name=="profile"?"active":"",
        "%JOB_ACTIVE%" => $page_name=="my-jobs"?"active":"",
        "%SERVICE_ACTIVE%" => $page_name=="f/services-order"?"active":"",
        "%POSTED_SERVICE_ACTIVE%" => $page_name=="my-services"?"active":"",
        "%REVIEW_ACTIVE%" => $page_name=="review"?"active":"",
        "%INVITATION_ACTIVE%" => $page_name=="invitation"?"active":"",
        "%FINANCIAL_ACTIVE%" => $page_name=="financial"?"active":"",
        "%CREDIT_ACTIVE%" => $page_name=="credit"?"active":"",
    );
    return str_replace(array_keys($array), array_values($array), $sub_content);
}
function customerSubHeaderContent($page_name){
    $sub_content = new MainTemplater(DIR_TMPL . "/customer_sub_header_content-sd.skd");
    $sub_content = $sub_content->compile();
    $array = array( 
        "%PROFILE_ACTIVE%" => $page_name=="profile"?"active":"",
        "%MYJOBS_ACTIVE%" => $page_name=="myjobs"?"active":"",
        "%REVIEW_ACTIVE%" => $page_name=="review"?"active":"",
        "%FINANCIAL_ACTIVE%" => $page_name=="financial"?"active":"",
        "%SER_ORDER_ACTIVE%" => $page_name=="ser_order"?"active":"",
        "%SAVED_FREELANCER_ACTIVE%" => $page_name=="saved_freelancer"?"active":"",
        "%FAVORITE_SRE_ACTIVE%" => $page_name=="favorite_sre"?"active":"",
    );
    return str_replace(array_keys($array), array_values($array), $sub_content);
}


function push_notification_android($device_id,$message){

    //API URL of FCM
    $url = 'https://fcm.googleapis.com/fcm/send';

    // api_key available in:
    // Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
    $api_key = 'AAAAiRv8NOA:APA91bHFilKUzM_CIINw-bjcbv_ciHEsc1Iajt3zMRj6IW6HIngrUFjbtuSPKEOfReM9K3DQLEoD49NvY-NtK5NvBvFeXJKB-vjiMLUFsAQCkF1Ccmib0T04zPQoI4rP6XMUjlqMHB7Z';
                
    $fields = array (
        'registration_ids' => array (
                $device_id
        ),
        'data' => array (
                "message" => $message
        )
    );

    //header includes Content type and api key
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key='.$api_key
    );
                
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    pre_print($result);
    if ($result === FALSE) {
        die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}