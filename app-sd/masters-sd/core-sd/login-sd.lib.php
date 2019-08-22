<?php
    class Login extends Home {
        function __construct($cookie=array()) {
            parent::__construct();
            $this->cookie = $cookie;
        }

        public function loginSubmit() {
            $uName = $this->objPost->uName;
            $uPass = $this->objPost->uPass;

            $qrysel = $this->db->pdoQuery("SELECT id,uPass,isActive FROM tbl_admin WHERE uName=? OR uEmail=?",array($uName,$uName))->result();
            if (!empty($qrysel) > 0 && ($qrysel['isActive'] != 'd' && $qrysel['isActive'] != 't')) {
                $fetchUser = $qrysel;
                $adm_id = $fetchUser['id'];
                if ($fetchUser["uPass"] == md5($uPass)) {
                    $_SESSION['pickgeeks_adminUserId'] = (int) $fetchUser["id"];
                    $_SESSION['pickgeeks_uName'] = $uName;
                    $sess_id = session_id();

                    if (isset($_SESSION['req_uri_adm']) && $_SESSION['req_uri_adm'] != '') {
                        $url = $_SESSION['req_uri_adm'];
                        unset($_SESSION['req_uri_adm']);
                        unset($_SESSION['loginDisplayed_adm']);
                        redirectPage($url);
                    } else {
                        redirectPage(SITE_ADM_MOD . 'home-sd/');
                    }
                } else {
                    return 'invaildUsers';
                }
            } else if ($qrysel['isActive'] == 'd') {
                $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'Your account is not active, Please contact to '.SITE_NM.' support to get more information'));
                redirectPage(SITE_ADM_MOD . 'login-sd/');
            } else {
                $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'User Name or Password is Invalid, Please try agian'));
                redirectPage(SITE_ADM_MOD . 'login-sd/');
            }
        }

        public function forgotProdedure() {
            $uEmail = isset($this->objPost->uEmail) ? $this->objPost->uEmail : '';
            $uName = isset($this->objPost->uName) ? $this->objPost->uName : '';
            $value = new stdClass();

            $fetchUser = $this->db->select("tbl_admin", array("id", "uName", "uEmail", "adminType"), array("uEmail" => $uEmail))->result();
            if (!empty($fetchUser)) {
                $pass = genrateRandom();

                $value->uPass = md5($pass);
                $this->db->update("tbl_admin", (array)$value, array("id" => $fetchUser['id']));
                $arrayCont = array(
                    "greetings" => $fetchUser["uName"],
                    "PASSWORDLINK" => $pass
                );
                $array = generateEmailTemplate('forgot_password',$arrayCont);
                sendEmailAddress($fetchUser["uEmail"], $array['subject'], $array['message']);  
                return 'succForgotPass';
            } else {
                return 'wrongUsername';
            }
        }

        public function getPageContent() {
            $final_result = NULL;
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
            $main_content->breadcrumb = $this->getBreadcrumb();
            $main_content->uName = ((!empty($this->cookie['uName']) && $this->cookie['rememberme']=='y') ? $this->cookie['uName'] : '');
            $main_content->uPass = ((!empty($this->cookie['uPass']) && $this->cookie['rememberme']=='y') ? $this->cookie['uPass'] : '');
            $main_content->rememberme_checked = ((!empty($this->cookie['rememberme']) && $this->cookie['rememberme']=='y') ? 'checked="checked"' : '');
            $final_result = $main_content->compile();
            return $final_result;
        }
    }
?>