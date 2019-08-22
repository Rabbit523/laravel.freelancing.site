<?php
    class Maintanancemode extends Home {
        public function __construct($module) {
            global $db;
            $this->db = $db;
            $this->module = $module;
            $this->table = 'tbl_variable';
            parent::__construct();
        }
        public function getPageContent() {
            $final_result = NULL;
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
            $main_content->breadcrumb = $this->getBreadcrumb();

            $status = ((MAINTENANCE_MODE=='y') ? 'checked' : '');

            $fetchRes['id'] = 0;
            $action = "ajax." . $this->module . ".php?action=maintanance_mode";
            $main_content->maintenance_switch = (in_array('status', $this->Permission)) ? $this->toggle_switch_new(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status)) : '';
        
            $final_result = $main_content->compile();
            return $final_result;
        }

        public function contentSubmit($data, $Permission){
            extract($data);

            $objPost = new stdClass();
            $objPost->maintanance_mode = (!empty($maintanance_sd) ? $maintanance_sd : MAINTENANCE_MODE);
            if ($type == 'edit' && $id > 0) {
                if (in_array('edit', $Permission)) {
                    $this->db->update($this->table, (array)$objPost, array("pId" => $id));
                    $response['status'] = true;
                    $response['success'] = "Maintanance Mode has been updated successfully";
                    echo json_encode($response);
                    exit;
                } else {
                    $response['error'] = "You don't have permission.";
                    echo json_encode($response);
                    exit;
                }
            }
        }
        public function toggle_switch_new($text)
        {
            $text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
            $text['check'] = isset($text['check']) ? $text['check'] : '';
            $text['name'] = isset($text['name']) ? $text['name'] : '';
            $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
            $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
            $text['data-id'] = isset($text['data-id']) ? $text['data-id'] : '';

            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module.'/switch-sd.skd');
            $main_content = $main_content->compile();
            $fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%","%DATAID%");
            $fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check'], $text['data-id']);
            return str_replace($fields, $fields_replace, $main_content);
        }

        
    }