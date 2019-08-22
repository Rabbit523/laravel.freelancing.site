<?php

class SiteSetting extends Home {

    function __construct() {
        parent::__construct();
        $this->table = 'tbl_site_settings';
    }

    public function _index() {
        $content = '';

        $sqlSetting = $this->db->select($this->table, "*")->results();
        foreach ($sqlSetting as $k => $setrow) {
            $required = '';
            $mend_sign = '';

            if ($setrow["type"] == "filebox" && $setrow["value"] == "") {
                $required = "required ";
                $mend_sign = MEND_SIGN;
            }
            if ($setrow["type"] == "filebox" && !empty($setrow["value"])) {
                $setrow["value"] = $this->img(array("onlyField" => true, "src" => "" . SITE_IMG . $setrow["value"] . "", "width" => "" . (($setrow["constant"] == "SITE_FAVICON") ? "20px" : "200px") . ""));
            }
            if ($setrow["type"] == "radio") {
                $mend_sign = MEND_SIGN;
                //$content.= $this->$setrow["type"](array("label"=>$setrow["label"].":","class"=>"radioBtn-bg","name"=>$setrow["id"],"value"=>$setrow["value"],"values"=>array("y"=>"Enable","n"=>"Disable")));
            } else if ($setrow["type"] == "selectBox") {
                $mend_sign = MEND_SIGN;
                $content.= $this->selectBox(array("label" => $mend_sign . $setrow["label"] . ":", "onlyField" => false, "allow_null" => true, "allow_null_value" => "", "class" => "required", "name" => $setrow["id"], "choices" => array(0 => "Select Location"), "value" => $setrow["value"], "defaultValue" => true, "multiple" => false, "optgroup" => false, "intoDB" => array("val" => true,
                        "table" => "tbl_locations",
                        "fields" => "*",
                        "where" => "status='1'",
                        "orderBy" => "location_name",
                        "valField" => "id",
                        "dispField" => "location_name")));
            } else {

                if ($setrow["required"] == 1) {
                    $required = "required ";
                    $mend_sign = MEND_SIGN;
                }

                $content.=$this->{$setrow["type"]}(array("label" => $mend_sign . $setrow["label"] . ":", "value" => $setrow["value"], "class" => $required . $setrow["class"], "name" => $setrow["id"]));
            }
            
            if (!empty($setrow['hint'])) {
                //$content.=$this->displayBox(array("label"=>"&nbsp;","value"=>$setrow['hint'],"class"=>"hint"));				
            }
        }
        $content.=$this->buttonpanel_start() .
                $this->button(array("onlyField" => true, "name" => "submitSetForm", "type" => "submit", "class" => "btn btn-success", "value" => "Submit", "extraAtt" => "")) .
                $this->button(array("onlyField" => true, "name" => "cn", "type" => "button", "class" => "btn btn-primary", "value" => "Cancel", "extraAtt" => "onclick=\"location.href='" . SITE_ADM_MOD . "home-sd/'\""));

        $content.=$this->buttonpanel_end();
        return $content;
    }

    public function button($btn) {
        $btn['value'] = isset($btn['value']) ? $btn['value'] : '';
        $btn['name'] = isset($btn['name']) ? $btn['name'] : '';
        $btn['class'] = isset($btn['class']) ? 'btn ' . $btn['class'] : 'btn';
        $btn['type'] = isset($btn['type']) ? $btn['type'] : '';
        $btn['src'] = isset($btn['src']) ? $btn['src'] : '';
        $btn['extraAtt'] = isset($btn['extraAtt']) ? ' ' . $btn['extraAtt'] : '';
        $btn['onlyField'] = isset($btn['onlyField']) ? $btn['onlyField'] : false;
        $btn["src"] = ($btn["type"] == "image" && $btn["src"] != '') ? $btn["src"] : '';

        $main_content_only_field = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/button_onlyfield.skd");
        $main_content_only_field = $main_content_only_field->compile();
        $fields = array("%TYPE%", "%NAME%", "%CLASS%", "%ID%", "%SRC%", "%EXTRA%", "%VALUE%");
        $fields_replace = array($btn["type"], $btn["name"], $btn["class"], $btn["name"], $btn["src"], $btn['extraAtt'], $btn["value"]);
        $sub_final_result_only_field = str_replace($fields, $fields_replace, $main_content_only_field);

        if ($btn['onlyField'] == true) {
            return $sub_final_result_only_field;
        } else {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/button.skd");
            $main_content = $main_content->compile();
            $fields = array("%BUTTON%");
            $fields_replace = array($sub_final_result_only_field);
            return str_replace($fields, $fields_replace, $main_content);
        }
    }

    public function checkbox($check) {
        $check['label'] = isset($check['label']) ? $check['label'] : 'Enter Text Here: ';
        $check['value'] = isset($check['value']) ? $check['value'] : '';        
        $check['name'] = isset($check['name']) ? $check['name'] : '';
        //$check['class'] = isset($check['class']) ? '' . $check['class'] : '';
        $check['type'] = isset($check['type']) ? $check['type'] : '';
        $check['src'] = isset($check['src']) ? $check['src'] : '';
        $check['extraAtt'] = isset($check['extraAtt']) ? ' ' . $check['extraAtt'] : '';
        if($check['value'] == 'on'){$ch = "checked";}else{$ch="";}
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/checkbox.skd");
        $main_content = $main_content->compile();
        $fields = array("%LABEL%","%TYPE%", "%NAME%", "%ID%", "%EXTRA%", "%VALUE%" ,"%CHECKED%");
        $fields_replace = array($check['label'],$check["type"], $check["name"],  $check["name"], $check['extraAtt'], $check['value'],$ch);
        return str_replace($fields, $fields_replace, $main_content);
        
    }



    public function img($text) {
        $text['href'] = isset($text['href']) ? $text['href'] : '';
        $text['src'] = isset($text['src']) ? $text['src'] : 'Enter Image Path Here: ';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['id'] = isset($text['id']) ? $text['id'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['height'] = isset($text['height']) ? '' . trim($text['height']) : '';
        $text['width'] = isset($text['width']) ? '' . trim($text['width']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : '';

        if ($text['onlyField'] == true) {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/img_onlyfield.skd");
            $main_content = $main_content->compile();
        } else {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/img.skd");
            $main_content = $main_content->compile();
        }
        $fields = array("%HREF%", "%SRC%", "%CLASS%", "%ID%", "%ALT%", "%WIDTH%", "%HEIGHT%", "%EXTRA%");
        $fields_replace = array($text['href'], $text['src'], $text['class'], $text['id'], $text['name'], $text['width'], $text['height'], $text['extraAtt']);
        return str_replace($fields,$fields_replace,$main_content);
    }

    public function buttonpanel_start() {
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/buttonpanel_start.skd");
        $main_content = $main_content->compile();

        return $main_content;
    }

    public function buttonpanel_end() {
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/buttonpanel_end.skd");
        $main_content = $main_content->compile();
        return $main_content;
    }

    public function form_start($text) {
        $text['action'] = isset($text['action']) ? $text['action'] : '';
        $text['method'] = isset($text['method']) ? $text['method'] : 'post';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['id'] = isset($text['id']) ? $text['id'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : 'form-horizontal';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form_start.skd");
        $main_content = $main_content->compile();
        $fields = array("%ACTION%", "%METHOD%", "%NAME%", "%ID%", "%CLASS%", "%EXTRA%");
        $fields_replace = array($text['action'], $text['method'], $text['name'], $text['name'], $text['class'], $text['extraAtt']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function form_end() {
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form_end.skd");
        $main_content = $main_content->compile();
        return $main_content;
    }

    public function displayBox($text) {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/displaybox.skd");
        $main_content = $main_content->compile();
        $fields = array("%LABEL%", "%CLASS%", "%VALUE%");
        $fields_replace = array($text['label'], $text['class'], $text['value']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function textbox($text) {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control ' . trim($text['class']) : 'form-control';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $content = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/textbox-sd.skd");
        $main_content = $main_content->compile();

        $fields = array("%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%LABEL%");
        $fields_replace = array($text['class'], $text['name'], $text['name'], $text['value'], $text['extraAtt'], $text['label']);
        $content = str_replace($fields, $fields_replace, $main_content);
        return $content;
    }
    
    public function password($text) {
        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control ' . trim($text['class']) : 'form-control';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        if ($text["onlyField"] == true) {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/password_onlyfield.skd');
        } else {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/password.skd');
        }
        $main_content = $main_content->compile();
        $fields = array("%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%LABEL%");
        $fields_replace = array($text['class'], $text['name'], $text['name'], $text['value'], $text['extraAtt'], $text['label']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function filebox($text) {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control ' . trim($text['class']) : 'form-control';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $text["help"] = isset($text["help"]) ? $text["help"] : "";
        $text["helptext"] = ($text["help"] != "") ? '<p class="help-block">' . $text["help"] . '</p>' : "";

        $content = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/filebox-sd.skd");
        $main_content = $main_content->compile();

        $fields = array("%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%LABEL%", "%HELPTEXT%");
        $fields_replace = array($text['class'], $text['name'], $text['name'], $text['value'], $text['extraAtt'], $text['label'], $text["helptext"]);

        $content = str_replace($fields, $fields_replace, $main_content);
        return $content;
    }

    public function textArea($text) {
        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Password Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? "form-control " . $text['class'] : 'form-control';
        $text['extraAtt'] = isset($text['extraAtt']) ? ' ' . $text['extraAtt'] : '';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;

        if ($text["onlyField"] == true) {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/textarea_onlyfield.skd');
        } else {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/textarea.skd');
        }
        $main_content = $main_content->compile();
        $fields = array("%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%LABEL%");
        $fields_replace = array($text['class'], $text['name'], $text['name'], $text['value'], $text['extraAtt'], $text['label']);
        return str_replace($fields, $fields_replace, $main_content);
    }
    
    public function textAreaEditor($text) {
        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Password Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'ckeditor form-control ' . $text['class'] : 'ckeditor form-control';
        $text['extraAtt'] = isset($text['extraAtt']) ? ' ' . $text['extraAtt'] : '';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/textarea_editor.skd');
        $main_content = $main_content->compile();
        $fields = array("%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%LABEL%");
        $fields_replace = array($text['class'], $text['name'], $text['name'], htmlentities($text['value']), $text['extraAtt'], $text['label']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function getPageContent() {
        $final_result = NULL;

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $main_content->getForm = $this->_index();

        $final_result = $main_content->compile();
        return $final_result;
    }

    public function settingSubmit($data){
        extract($data);
        // printr($data,1);

        foreach ($data as $k => $v) {
            if ((int) $k) {
                $v = closetags($v);
                if($k == 36)
                    $sData = array("value" => $v);
                else
                    $sData = array("value" => filtering($v));
                //echo "<pre>";print_r($sData);exit;
                $sWhere = array("id" => $k);
                $this->db->update("tbl_site_settings", $sData, $sWhere);
                if ($k == 2) {
                    $data = array("uEmail" => $v);
                    $where = array("id" => "1", "adminType" => "s");
                    $this->db->update("tbl_admin", $data, $where);
                    $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Site Settings updated successfully'));
                }                
               
            }
        }
        redirectPage(SITE_ADM_MOD . $this->module);
    }
    public function fileSubmit($data){
        foreach ($_FILES as $a => $b) {

        $selField = array('type');
        $selWhere = array('id' => $a);
        
        $type1Sql = $this->db->select("tbl_site_settings", $selField, $selWhere)->results();
        
        foreach ($type1Sql as $c => $b) {
            $type1 = $b["type"];
            $constant = $b["constant"];
        }

        if ($type1 == "filebox") {
            
            $type = $_FILES[$a]["type"];
            $fileName = $_FILES[$a]["name"];
            $TmpName = $_FILES[$a]["tmp_name"];

            if ($type == "image/jpeg" || $type == "image/png" || $type == "image/gif" || $type == "image/x-png" || $type == "image/jpg" || $type == "image/x-png" || $type == "image/x-jpeg" || $type == "image/pjpeg" || $type == "image/x-icon" || $type == "image/vnd.microsoft.icon") {
                //echo 'Inside';exit;
                
                if($constant == "SITE_FAVICON") {
                    $height_width_array = array('height' => 20, 'width' => 20);
                } else {
                    $height_width_array = array('height' => 130, 'width' => 110);
                }

                $fileName = GenerateThumbnail($fileName, DIR_IMG, $TmpName, array($height_width_array));
                $dataArr = array("value" => $fileName);
                $dataWhere = array("id" => $a);
                $this->db->update('tbl_site_settings', $dataArr, $dataWhere);
            }
        }
    }
    
    }

}

?>