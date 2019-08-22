<?php

class cPass extends Home {

    function __construct($module) {
        parent::__construct();
        $this->module=$module;
    }

    public function getForm() {
        $content = NULL;
        /*$opasswd = isset($this->objPost->opasswd) ? $this->objPost->opasswd : '';
        $passwd = isset($this->objPost->passwd) ? $this->objPost->passwd : '';
        $cpasswd = isset($this->objPost->cpasswd) ? $this->objPost->cpasswd : '';*/


       /* $qrySel = $this->db->select("tbl_admin", array("uPass"), array("id =" => $this->adminUserId))->result();
        $fetchUser = $qrySel;*/
        $selectPageId=$this->db->pdoQuery("SELECT id,title from tbl_adminrole")->results();
        $PageIds="";
        if(!empty($selectPageId)){
            foreach ($selectPageId as $key => $value) {
                $PageIds.="<option value=".$value['id'].">".$value['title']."</option>";
            }
        }
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();

        $fields = array("%PAGE_ID%");
        $fields_replace = array($PageIds);
        $content = str_replace($fields, $fields_replace, $main_content);
        return $content;
    }

    public function submitProcedure() {
        /*echo "<pre>";
        print_r($this->module);exit();*/
        extract($_POST);
        
        $objPost = new stdClass();

        $objPost->page_id = isset($page_id) ? $page_id : '';
        $objPost->tab_constant = isset($tab_constant) ? strtoupper($tab_constant) : '';
        $objPost->tab_name = isset($tab_name) ? $tab_name : '';


        $countArrayValue=count($_POST['field_constant']);

      /*  $selectAllConstant=$this->db->pdoQuery("SELECT * from tbl_language_constant")->results();
        $i=$flag=0;
        foreach ($selectAllConstant as $key => $value) {
            if($value['constant']==$field_value[$i]){
                $flag=1;
            }
            $i++;
        }
        
        if($flag!=1){*/
            for($i=0;$i<$countArrayValue;$i++){
                $objPost->constant=strtoupper($field_constant[$i]);
                $objPost->value=$field_value[$i];
                $this->db->insert("tbl_language_constant", (array)$objPost);
                    
            }
            return "success";
   /*     }*/
        $msgType = $_SESSION["msgType"] =disMessage(array('type'=>'suc','var'=>"Dear user, You are successfully login"));
        redirectPage(SITE_ADM_MOD . $module);

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
