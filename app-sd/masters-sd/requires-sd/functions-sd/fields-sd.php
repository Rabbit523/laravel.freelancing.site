<?php

class fields {

    function __construct() {
        
    }

    public function textBox($text) {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control ' . trim($text['class']) : 'form-control';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        if ($text["onlyField"] == true) {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'textbox_onlyfield.skd');
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'textbox.skd');
        }
        $main_content = $main_content->compile();
        $fields = array("%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%LABEL%");
        $fields_replace = array($text['class'], $text['name'], $text['name'], $text['value'], $text['extraAtt'], $text['label']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function hidden($text) {
        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'hidden.skd');
        $main_content = $main_content->compile();
        $fields = array("%NAME%", "%ID%", "%VALUE%", "%EXTRA%");
        $fields_replace = array($text['name'], $text['name'], $text['value'], $text['extraAtt']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function fileBox($text) {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control ' . trim($text['class']) : 'form-control';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $text["help"] = isset($text["help"]) ? $text["help"] : "";
        $text["helptext"] = ($text["help"] != "") ? '<p class="help-block">' . $text["help"] . '</p>' : "";

        if ($text["onlyField"] == true) {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'filebox_onlyfield.skd');
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'filebox.skd');
        }
        $main_content = $main_content->compile();
        $fields = array("%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%LABEL%", "%HELPTEXT%");
        $fields_replace = array($text['class'], $text['name'], $text['name'], $text['value'], $text['extraAtt'], $text['label'], $text["helptext"]);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function displayBox($text) {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'displaybox.skd');
        $main_content = $main_content->compile();
        $fields = array("%LABEL%", "%CLASS%", "%VALUE%");
        $fields_replace = array($text['label'], $text['class'], $text['value']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    # for use password and pass label,name,class,and value array

    public function password($text) {
        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control ' . trim($text['class']) : 'form-control';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        if ($text["onlyField"] == true) {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'password_onlyfield.skd');
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'password.skd');
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

        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'textarea_editor.skd');
        $main_content = $main_content->compile();
        $fields = array("%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%LABEL%");
        $fields_replace = array($text['class'], $text['name'], $text['name'], htmlentities($text['value']), $text['extraAtt'], $text['label']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    # for use textarea and pass label,name,class,and value array

    public function textArea($text) {
        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Password Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? "form-control " . $text['class'] : 'form-control';
        $text['extraAtt'] = isset($text['extraAtt']) ? ' ' . $text['extraAtt'] : '';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;

        if ($text["onlyField"] == true) {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'textarea_onlyfield.skd');
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'textarea.skd');
        }
        $main_content = $main_content->compile();
        $fields = array("%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%LABEL%");
        $fields_replace = array($text['class'], $text['name'], $text['name'], $text['value'], $text['extraAtt'], $text['label']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function checkBox($chk) {
        $checkBoxes = '';
        $chk['label'] = isset($chk['label']) ? $chk['label'] : ' ';
        $chk['value'] = isset($chk['value']) ? $chk['value'] : '';
        $chk['name'] = isset($chk['name']) ? $chk['name'] : array();
        $chk['class'] = isset($chk['class']) ? '' . $chk['class'] : '';
        $chk['extraAtt'] = isset($chk['extraAtt']) ? ' ' . $chk['extraAtt'] : '';
        $chk['onlyField'] = isset($chk['onlyField']) ? $chk['onlyField'] : false;
        $chk['text'] = isset($chk["text"]) ? $chk["text"] : "";
        $chk['noDiv'] = isset($chk['noDiv']) ? $chk['noDiv'] : true;

        $main_content_only_field = new Templater(DIR_ADMIN_FIELDS_HTML . 'checkbox_onlyfield.skd');
        $main_content_only_field = $main_content_only_field->compile();

        $fields = array("%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%DISPLAY_VALUE%", "%CHECKED%");

        foreach ($chk['values'] as $k => $v) {
            $check = ($k == $chk['value']) ? 'checked="checked"' : '';
            if (is_array($chk['value'])) {

                $check = (in_array($k, $chk['value'])) ? 'checked="checked"' : '';
            } else {
                $check = ($k == $chk['value']) ? 'checked="checked"' : '';
            }
            $fields_replace = array($chk['class'], $chk['name'], $chk['name'], $k, $chk['extraAtt'], $v, $check);
            $sub_final_result .= str_replace($fields, $fields_replace, $main_content_only_field);
        }
        if ($chk['onlyField'] == true) {
            return $sub_final_result;
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'checkbox.skd');
            $main_content = $main_content->compile();

            $fields = array("%CHECKBOX_LIST%", "%LABEL%");
            $fields_replace = array($sub_final_result, $chk['label']);
            return str_replace($fields, $fields_replace, $main_content);
        }
    }

    public function radio($chk) {
        $checkBoxes = '';
        $chk['label'] = isset($chk['label']) ? $chk['label'] : ' ';
        $chk['value'] = isset($chk['value']) ? $chk['value'] : '';
        $chk['name'] = isset($chk['name']) ? $chk['name'] : array();
        $chk['class'] = isset($chk['class']) ? $chk['class'] : '';
        $chk['extraAtt'] = isset($chk['extraAtt']) ? ' ' . $chk['extraAtt'] : '';
        $chk['onlyField'] = isset($chk['onlyField']) ? $chk['onlyField'] : false;
        $chk['text'] = isset($chk["text"]) ? $chk["text"] : "";
        $chk['noDiv'] = isset($chk['noDiv']) ? $chk['noDiv'] : true;
        $chk['label_class'] = isset($chk['label_class']) ? ' ' . $chk['label_class'] : '';

        $main_content_only_field = new Templater(DIR_ADMIN_FIELDS_HTML . 'radio_onlyfield.skd');
        $main_content_only_field = $main_content_only_field->compile();

        $fields = array("%LABEL_CLASS%", "%CLASS%", "%NAME%", "%ID%", "%VALUE%", "%EXTRA%", "%DISPLAY_VALUE%", "%CHECKED%");

        foreach ($chk['values'] as $k => $v) {
            $check = ($k == $chk['value']) ? 'checked="checked"' : '';
            $fields_replace = array($chk['label_class'], $chk['class'], $chk['name'], $k, $k, $chk['extraAtt'], $v, $check);
            $sub_final_result .= str_replace($fields, $fields_replace, $main_content_only_field);
        }
        if ($chk['onlyField'] == true) {
            return $sub_final_result;
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'radio.skd');
            $main_content = $main_content->compile();

            $fields = array("%RADIO_LIST%", "%LABEL%");
            $fields_replace = array($sub_final_result, $chk['label']);
            return str_replace($fields, $fields_replace, $main_content);
        }
    }

    public function radio1($radio) {
        $radio['label'] = isset($radio['label']) ? $radio['label'] : 'Select Any One: ';
        $radio['values'] = isset($radio['values']) ? $radio['values'] : array();
        $radio['value'] = isset($radio['value']) ? $radio['value'] : '';
        $radio['name'] = isset($radio['name']) ? $radio['name'] : '';
        $radio['class'] = isset($radio['class']) ? 'radio ' . $radio['class'] : 'radio';
        $radio['extraAtt'] = isset($radio['extraAtt']) ? ' ' . $radio['extraAtt'] : '';
        $radio['onlyField'] = isset($radio['onlyField']) ? $radio['onlyField'] : false;
        $radio['wrapper'] = isset($radio['wrapper']) ? $radio['wrapper'] : '';
        $radio['wrapperClass'] = isset($radio['wrapperClass']) ? $radio['wrapperClass'] : '';
        $check = '';
        $radios = '';
        foreach ($radio['values'] as $k => $v) {
            $check = ($k == $radio['value']) ? 'checked="checked"' : '';
            $radios.='<span class="radiobadge"><input class="' . $radio['class'] . '" id="' . $radio['name'] . '" name="' . $radio['name'] . '" type="radio" value="' . $k . '" ' . $check . ' ' . $radio['extraAtt'] . ' />&nbsp;' . ucwords($v) . "&nbsp;&nbsp;
			</span>";
        }
        if ($radio["onlyField"] == true) {
            return $radios;
        } else {
            return '<div class="form-group"><label>' . $radio["label"] . '</label>
            	' . ($radio['wrapper'] != '' ?
                            '<' . $radio['wrapper'] . ' class="' . ($radio['wrapperClass'] != '' ? $radio['wrapperClass'] : '') . '">' : '') . $radios . ($radio['wrapper'] != '' ? '</' . $radio['wrapper'] . '>' : '');
        }
    }

    function selectBox($field = array()) {
        global $db;
        $fields = '';
        $field['label'] = isset($field['label']) ? $field['label'] : array();
        $field['id'] = isset($field['id']) ? $field['id'] : $field['name'];
        $field['value'] = isset($field['value']) ? $field['value'] : '';
        $field['class'] = isset($field['class']) ? 'form-control ' . $field['class'] : 'form-control';
        $field['multiple'] = isset($field['multiple']) ? $field['multiple'] : false;
        $field['arr'] = isset($field['arr']) ? $field['arr'] : true;
        $field['defaultValue'] = isset($field['defaultValue']) ? $field['defaultValue'] : false;
        $field['allow_null'] = isset($field['allow_null']) ? $field['allow_null'] : false;
        $field['allow_null_value'] = isset($field['allow_null_value']) ? $field['allow_null_value'] : 0;
        $field['choices'] = isset($field['choices']) ? $field['choices'] : array();
        $field['optgroup'] = isset($field['optgroup']) ? $field['optgroup'] : false;
        $field['onlyField'] = isset($field['onlyField']) ? $field['onlyField'] : false;
        $field['intoDB'] = isset($field['intoDB']) ? $field['intoDB'] : array();

        $field['intoDB']["val"] = isset($field['intoDB']["val"]) ? $field['intoDB']["val"] : false;
        $field['intoDB']["groupBy"] = isset($field['intoDB']["groupBy"]) ? ' ' . $field['intoDB']["groupBy"] : '';
        $field['intoDB']["orderBy"] = isset($field['intoDB']["orderBy"]) ? ' ' . $field['intoDB']["orderBy"] : '';

        $field['extraAtt'] = isset($field['extraAtt']) ? ' ' . $field['extraAtt'] : '';
        $field['isArray'] = isset($field['isArray']) ? $field['isArray'] : '';
        if (empty($field['choices'])) {
            return false;
        }
        if ($field['intoDB']["val"] == true) {
            $field['choices'] = array();

            if ($field['intoDB']["custom"] == true) {
                $get1 = $db->pdoQuery("select " . $field['intoDB']["fields"] . " from " . $field['intoDB']["table"] . " where " . $field['intoDB']["where"] . " order by " . $field['intoDB']["orderBy"] . " ")->results();
            } else {
                $get1 = $db->select($field['intoDB']["table"], $field['intoDB']["fields"], $field['intoDB']["where"], "ORDER BY " . $field['intoDB']["orderBy"])->results();
            }
            foreach ($get1 as $checkVal) {
                $field['choices'][$checkVal[$field['intoDB']["valField"]]] = $checkVal[$field['intoDB']["dispField"]];
            }
        }

        $multiple = '';
        if ($field['multiple'] == 'true' || $field['multiple'] == true) {
            $multiple = ' multiple="multiple" size="5" ';
            if ($field['arr'] == 'true') {
                $field['name'] .= '[]';
            }
        }
        $id = ($field['isArray'] != 'true') ? $field['id'] : "";

        $main_content_only_field = new Templater(DIR_ADMIN_FIELDS_HTML . 'select_onlyfield.skd');
        $main_content_only_field = $main_content_only_field->compile();




        $fields.='<select name="' . $field['name'] . '" id="' . $id . '"  class="' . $field['class'] . '" ' . $multiple . ' ' . $field['extraAtt'] . '>';

        // null

        $main_content_options = new Templater(DIR_ADMIN_FIELDS_HTML . 'select_options.skd');
        $main_content_options = $main_content_options->compile();
        $content_fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");

        if ($field['allow_null'] == '1') {
            //$fields.= '<option value="'.$field['allow_null_value'].'">Please Select</option>';
            $content_fields_replace = array($field['allow_null_value'], '', 'Please Select');
            $final_result_options .= str_replace($content_fields, $content_fields_replace, $main_content_options);
        }


        // loop through values and add them as options
        foreach ($field['choices'] as $key => $value) {
            if ($field['optgroup']) {
                $main_content_optiongroup = new Templater(DIR_ADMIN_FIELDS_HTML . 'select_optiongroup.skd');
                $main_content_optiongroup = $main_content_optiongroup->compile();
                $content_fields_optiongroup = array("%VALUE%", "%OPTIONS%");

                // this select is grouped with optgroup

                if ($value) {
                    foreach ($value as $id => $label) {
                        $selected = '';
                        if (is_array($field['value']) && in_array($id, $field['value'])) {
                            $selected = 'selected="selected"';
                        } else {
                            // 3. this is not a multiple select, just check normaly
                            if ($id == $field['value']) {
                                $selected = 'selected="selected"';
                            }
                        }
                        $content_fields_replace = array($id, $selected, $label);
                        $final_result_suboptions .= str_replace($content_fields, $content_fields_replace, $main_content_options);
                    }
                }
                if ($key != '') {
                    $optiongroup_flag = true;
                    $content_fields_optiongroup_replace = array($key, '', $final_result_suboptions);
                    $final_result_optiongroup .= str_replace($content_fields_optiongroup, $content_fields_optiongroup_replace, $main_content_optiongroup);
                }
            } else {
                $selected = '';
                if (is_array($field['value']) && in_array($key, $field['value'])) {
                    // 2. If the value is an array (multiple select), loop through values and check if it is selected
                    $selected = 'selected="selected"';
                } else {
                    // 3. this is not a multiple select, just check normaly
                    if ($key == $field['value']) {
                        $selected = 'selected="selected"';
                    }
                }

                $content_fields_replace = array($key, $selected, ucfirst(stripslashes($value)));
                $final_result_options .= str_replace($content_fields, $content_fields_replace, $main_content_options);
            }
        }

        $final_result_options .=($optiongroup_flag == true) ? $final_result_optiongroup : $final_result_suboptions;

        $content_fields = array("%NAME%", "%ID%", "%CLASS%", "%MULTIPLE%", "%EXTRA%", "%OPTIONS%");
        $content_fields_replace = array($field['name'], $id, $field['class'], $multiple, $field['extraAtt'], $final_result_options);
        $final_result_only_field = str_replace($content_fields, $content_fields_replace, $main_content_only_field);

        if ($field["onlyField"] == true) {
            return $final_result_only_field;
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'select.skd');
            $main_content = $main_content->compile();
            $content_fields = array("%LABEL%", "%SELECT_BOX%");
            $content_fields_replace = array($field['label'], $final_result_only_field);
            $final_result = str_replace($content_fields, $content_fields_replace, $main_content);
            return $final_result;
        }
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

        $main_content_only_field = new Templater(DIR_ADMIN_FIELDS_HTML . 'button_onlyfield.skd');
        $main_content_only_field = $main_content_only_field->compile();
        $fields = array("%TYPE%", "%NAME%", "%CLASS%", "%ID%", "%SRC%", "%EXTRA%", "%VALUE%");
        $fields_replace = array($btn["type"], $btn["name"], $btn["class"], $btn["name"], $btn["src"], $btn['extraAtt'], $btn["value"]);
        $sub_final_result_only_field = str_replace($fields, $fields_replace, $main_content_only_field);

        if ($btn['onlyField'] == true) {
            return $sub_final_result_only_field;
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'button.skd');
            $main_content = $main_content->compile();
            $fields = array("%BUTTON%");
            $fields_replace = array($sub_final_result_only_field);
            return str_replace($fields, $fields_replace, $main_content);
        }
    }

    public function label($text) {
        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Label Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';

        $text['class'] = isset($text['class']) ? 'input_text ' . trim($text['class']) : 'input_text';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'label.skd');
        $main_content = $main_content->compile();
        $fields = array("%CLASS%", "%LABEL%");
        $fields_replace = array($text['class'], $text['label']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function div($text) {
        $text['class'] = isset($text['class']) ? trim($text['class']) : '';
        $text['id'] = isset($text['id']) ? trim($text['id']) : '';
        $text['innerhtml'] = isset($text['innerhtml']) ? trim($text['innerhtml']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'div.skd');
        $main_content = $main_content->compile();
        $fields = array("%CLASS%", "%ID%", "%INNERHTML%");
        $fields_replace = array($text['class'], $text['id'], $text['innerhtml']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function link($text) {
        $text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        //$text['class'] = isset($text['class']) ? 'input_text '.$text['class'] : 'input_text';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        //$text['extraAtt'] = isset($text['extraAtt']) ? ' '.$text['extraAtt'] : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        /* return '<span><a href="'.$text['href'].'" class="'.$text['class'].'" value="'.$text['value'].'" '.$text['extraAtt'].'>'.$text['value'].'</a></span>'; */
        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'link.skd');
        $main_content = $main_content->compile();
        $fields = array("%HREF%", "%CLASS%", "%VALUE%", "%EXTRA%");
        $fields_replace = array($text['href'], $text['class'], $text['value'], $text['extraAtt']);
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
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'img_onlyfield.skd');
            $main_content = $main_content->compile();
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'img.skd');
            $main_content = $main_content->compile();
        }
        $fields = array("%HREF%", "%SRC%", "%CLASS%", "%ID%", "%ALT%", "%WIDTH%", "%HEIGHT%", "%EXTRA%");
        $fields_replace = array($text['href'], $text['src'], $text['class'], $text['id'], $text['name'], $text['width'], $text['height'], $text['extraAtt']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function buttonpanel_start() {
        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'buttonpanel_start.skd');
        $main_content = $main_content->compile();

        return $main_content;
    }

    public function buttonpanel_end() {
        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'buttonpanel_end.skd');
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

        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'form_start.skd');
        $main_content = $main_content->compile();
        $fields = array("%ACTION%", "%METHOD%", "%NAME%", "%ID%", "%CLASS%", "%EXTRA%");
        $fields_replace = array($text['action'], $text['method'], $text['name'], $text['name'], $text['class'], $text['extraAtt']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function form_end() {
        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'form_end.skd');
        $main_content = $main_content->compile();
        return $main_content;
    }

    public function toggel_switch($text) {
        $text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
        $text['check'] = isset($text['check']) ? $text['check'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'switch.skd');
        $main_content = $main_content->compile();
        $fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%");
        $fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    function rattingBox($field = array()) {
        global $db;
        $fields = '';
        $field['label'] = isset($field['label']) ? $field['label'] : array();
        $field['id'] = isset($field['id']) ? $field['id'] : $field['name'];
        $field['value'] = isset($field['value']) ? $field['value'] : '';
        $field['class'] = isset($field['class']) ? 'form-control ' . $field['class'] : 'form-control';
        $field['multiple'] = isset($field['multiple']) ? $field['multiple'] : false;
        $field['arr'] = isset($field['arr']) ? $field['arr'] : true;
        $field['defaultValue'] = isset($field['defaultValue']) ? $field['defaultValue'] : false;
        $field['allow_null'] = isset($field['allow_null']) ? $field['allow_null'] : false;
        $field['allow_null_value'] = isset($field['allow_null_value']) ? $field['allow_null_value'] : 0;
        $field['choices'] = isset($field['choices']) ? $field['choices'] : array();
        $field['optgroup'] = isset($field['optgroup']) ? $field['optgroup'] : false;
        $field['onlyField'] = isset($field['onlyField']) ? $field['onlyField'] : false;
        $field['intoDB'] = isset($field['intoDB']) ? $field['intoDB'] : array();

        $field['intoDB']["val"] = isset($field['intoDB']["val"]) ? $field['intoDB']["val"] : false;
        $field['intoDB']["groupBy"] = isset($field['intoDB']["groupBy"]) ? ' ' . $field['intoDB']["groupBy"] : '';
        $field['intoDB']["orderBy"] = isset($field['intoDB']["orderBy"]) ? ' ' . $field['intoDB']["orderBy"] : '';

        $field['extraAtt'] = isset($field['extraAtt']) ? ' ' . $field['extraAtt'] : '';
        $field['isArray'] = isset($field['isArray']) ? $field['isArray'] : '';
        if (empty($field['choices'])) {
            return false;
        }
        if ($field['intoDB']["val"] == true) {
            $field['choices'] = array();

            if ($field['intoDB']["custom"] == true) {
                $get1 = $db->pdoQuery("select " . $field['intoDB']["fields"] . " from " . $field['intoDB']["table"] . " where " . $field['intoDB']["where"] . " order by " . $field['intoDB']["orderBy"] . " ")->results();
            } else {
                $get1 = $db->select($field['intoDB']["table"], $field['intoDB']["fields"], $field['intoDB']["where"], "ORDER BY " . $field['intoDB']["orderBy"])->results();
            }
            foreach ($get1 as $checkVal) {
                $field['choices'][$checkVal[$field['intoDB']["valField"]]] = $checkVal[$field['intoDB']["dispField"]];
            }
        }

        $multiple = '';
        if ($field['multiple'] == 'true' || $field['multiple'] == true) {
            $multiple = ' multiple="multiple" size="5" ';
            if ($field['arr'] == 'true') {
                $field['name'] .= '[]';
            }
        }
        $id = ($field['isArray'] != 'true') ? $field['id'] : "";

        $main_content_only_field = new Templater(DIR_ADMIN_FIELDS_HTML . 'ratting_onlyfield.skd');
        $main_content_only_field = $main_content_only_field->compile();




        $fields.='<select name="' . $field['name'] . '" id="' . $id . '"  class="' . $field['class'] . '" ' . $multiple . ' ' . $field['extraAtt'] . '>';

        // null

        $main_content_options = new Templater(DIR_ADMIN_FIELDS_HTML . 'ratting_select_options.skd');
        $main_content_options = $main_content_options->compile();
        $content_fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");

        if ($field['allow_null'] == '1') {
            //$fields.= '<option value="'.$field['allow_null_value'].'">Please Select</option>';
            $content_fields_replace = array($field['allow_null_value'], '', 'Please Select');
            $final_result_options .= str_replace($content_fields, $content_fields_replace, $main_content_options);
        }


        // loop through values and add them as options
        foreach ($field['choices'] as $key => $value) {
            $selected = '';
            if (is_array($field['value']) && in_array($key, $field['value'])) {
                // 2. If the value is an array (multiple select), loop through values and check if it is selected
                $selected = 'selected="selected"';
            } else {
                // 3. this is not a multiple select, just check normaly
                if ($key == $field['value']) {
                    $selected = 'selected="selected"';
                }
            }

            $content_fields_replace = array($key, $selected, ucfirst(stripslashes($value)));
            $final_result_options .= str_replace($content_fields, $content_fields_replace, $main_content_options);
        }

        $final_result_options .=($optiongroup_flag == true) ? $final_result_optiongroup : $final_result_suboptions;

        $content_fields = array("%NAME%", "%ID%", "%CLASS%", "%MULTIPLE%", "%EXTRA%", "%OPTIONS%");
        $content_fields_replace = array($field['name'], $id, $field['class'], $multiple, $field['extraAtt'], $final_result_options);
        $final_result_only_field = str_replace($content_fields, $content_fields_replace, $main_content_only_field);

        if ($field["onlyField"] == true) {
            return $final_result_only_field;
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'ratting.skd');
            $main_content = $main_content->compile();
            $content_fields = array("%LABEL%", "%SELECT_BOX%");
            $content_fields_replace = array($field['label'], $final_result_only_field);
            $final_result = str_replace($content_fields, $content_fields_replace, $main_content);
            return $final_result;
        }
    }

    public function dateRangeBox($text) {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value1'] = isset($text['value1']) ? $text['value1'] : '';
        $text['value2'] = isset($text['value2']) ? $text['value2'] : '';
        $text['name1'] = isset($text['name1']) ? $text['name1'] : '';
        $text['name2'] = isset($text['name2']) ? $text['name2'] : '';
        $text['class'] = isset($text['class']) ? 'form-control ' . trim($text['class']) : 'form-control';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt1'] = isset($text['extraAtt1']) ? $text['extraAtt1'] : '';
        $text['extraAtt2'] = isset($text['extraAtt2']) ? $text['extraAtt2'] : '';

        if ($text["onlyField"] == true) {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'daterangebox_onlyfield.skd');
        } else {
            $main_content = new Templater(DIR_ADMIN_FIELDS_HTML . 'daterangebox.skd');
        }
        $main_content = $main_content->compile();
        $fields = array("%CLASS%", "%NAME1%", "%NAME2%", "%ID1%", "%ID2%", "%VALUE1%", "%VALUE2%", "%EXTRA1%", "%EXTRA2%", "%LABEL%", "%START_DATE%");
        $fields_replace = array($text['class'], $text['name1'], $text['name2'], $text['name1'], $text['name2'], $text['value1'], $text['value2'], $text['extraAtt1'], $text['extraAtt2'], $text['label'], date('d-m-Y'));
        return str_replace($fields, $fields_replace, $main_content);
    }

}

?>