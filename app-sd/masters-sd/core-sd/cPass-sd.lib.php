<?php

class cPass extends Home {

    function __construct() {
        parent::__construct();
    }

    public function getForm() {
        $content = NULL;
        $opasswd = isset($this->objPost->opasswd) ? $this->objPost->opasswd : '';
        $passwd = isset($this->objPost->passwd) ? $this->objPost->passwd : '';
        $cpasswd = isset($this->objPost->cpasswd) ? $this->objPost->cpasswd : '';

        $qrySel = $this->db->select("tbl_admin", array("uPass"), array("id =" => $this->adminUserId))->result();
        $fetchUser = $qrySel;

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();

        $fields = array("%OLD_PASSWORD%", "%NEW_PASSWORD%", "%CONFIRM_PASSWORD%", "%PASS_VALUE%");
        $fields_replace = array($opasswd, $passwd, $cpasswd, $fetchUser['uPass']);
        $content = str_replace($fields, $fields_replace, $main_content);
        return $content;
    }

    public function submitProcedure() {
        $opasswd = isset($this->objPost->opasswd) ? $this->objPost->opasswd : '';
        $passwd = isset($this->objPost->passwd) ? $this->objPost->passwd : '';
        $cpasswd = isset($this->objPost->cpasswd) ? $this->objPost->cpasswd : '';

        $qrySel = $this->db->select("tbl_admin", array("uPass"), array("id =" => $this->adminUserId))->result();
        $fetchUser = $qrySel;
        
        if ($fetchUser["uPass"] != md5($opasswd)) {
            return 'wrongPass';
        } else if ($passwd != $cpasswd) {
            return 'passNotmatch';
        } else {
            $value = new stdClass();
            $value->uPass = md5($cpasswd);
            $value->ipAddress = $_SERVER["REMOTE_ADDR"];

            $valArray = array("uPass" => $value->uPass, "ipAddress" => $value->ipAddress);
            $whereArray = array("id " => $this->adminUserId);
            $this->db->update("tbl_admin", $valArray, $whereArray);
            return 'succChangePass';
        }
    }

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $main_content->getForm = $this->getForm();

        $final_result = $main_content->compile();
        return $final_result;
    }

}
