<?php

class Fees extends Home {

    public $question;
    public $answer;
    public $priority;
    public $isActive;
    public $data = array();

    public function __construct($module, $feesId = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCatafeesId;
        $this->db = $db;
        $this->data['feesId'] = $this->feesId = $feesId;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_fees';

        $this->type = ($this->feesId > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->feesId > 0) {
            $qry = $this->db->pdoQuery("SELECT tbl_fees.*,tbl_listing_type.listingTypeName from tbl_fees INNER JOIN tbl_listing_type  on(tbl_listing_type.listingTypeId = tbl_fees.listingTypeId) where feesId = ".$feesId);
            $qrySel = $qry->result();
            $fetchRes = $qrySel;

            $this->data['feesId'] = $this->feesId = $fetchRes['feesId'];
            $this->data['listingTypeId'] = $this->listingTypeId = $fetchRes['listingTypeId'];
            $this->data['listingTypeName'] = $this->listingTypeName = $fetchRes['listingTypeName'];
            //$this->data['ansImage'] = $this->ansImage = $fetchRes['ansImage'];
            $this->data['price'] = $this->price = $fetchRes['price'];
            $this->data['feesType'] = $this->feesType = $fetchRes['feesType'];
            $this->data['isActive'] = $this->isActive = $fetchRes['isActive'];
            $this->data['classifiedOrauction'] = $this->classifiedOrauction = $fetchRes['classifiedOrauction'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
            $this->data['description'] = $this->description = $fetchRes['description'];
        } else {
            $this->data['feesId'] = $this->feesId = '';
            $this->data['listingTypeId'] = $this->listingTypeId = '';
            $this->data['listingTypeName'] = $this->listingTypeName = '';
            $this->data['price'] = $this->price = '';
            $this->data['feesType'] = $this->feesType = '';            
            $this->data['isActive'] = $this->isActive = 'y';
            $this->data['classifiedOrauction'] = $this->classifiedOrauction = 'n';
            $this->data['createdDate'] = $this->createdDate = '';
            $this->data['description'] = $this->description = "";
        }
        switch ($type) {
            case 'add' : {
                    $this->data['content'] = $this->getForm();
                    break;
                }
            case 'edit' : {
                    $this->data['content'] = $this->getForm();
                    break;
                }
            case 'view' : {
                    $this->data['content'] = $this->viewForm();
                    break;
                }
            case 'delete' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
            case 'datagrid' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
        }
    }

    public function viewForm() {
        if(filtering($this->classifiedOrauction=="c"))
            $classifiedOrauction = 'Classified';
        elseif(filtering($this->classifiedOrauction=="a"))
            $classifiedOrauction = 'Auction';
        else
            $classifiedOrauction = 'None';
        $content = 
                $this->displayBox(array("label" => "Category&nbsp;:", "value" => filtering($this->listingTypeName))) .
                $this->displayBox(array("label" => "Fees Type&nbsp;:", "value" => filtering($this->feesType))).
                $this->displayBox(array("label" => "Price&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$this->price))).
                $this->displayBox(array("label" => "Description&nbsp;:", "value" => filtering($this->description))).
                $this->displayBox(array("label" => "Classified/Auction&nbsp;:", "value" => $classifiedOrauction)).
                $this->displayBox(array("label" => "Inserted Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($this->createdDate)))));
        return $content;
    }

    public function getForm() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $listing='';

        $qrySel = $this->db->select("tbl_listing_type", "listingTypeId,listingTypeName",array('isActive'=>'y'))->results();
        foreach ($qrySel as $listing_type) {
            $select=($this->listingTypeId==$listing_type['listingTypeId']) ? "selected" : "";
            $listing.="<option value='".$listing_type['listingTypeId']."' ".$select.">".$listing_type['listingTypeName']."</option>";
        }
        $static_a = ($this->isActive == 'y' ? 'checked' : '');
        $static_d = ($this->isActive != 'y' ? 'checked' : '');
        $isAuctionPrice=($this->classifiedOrauction == 'a') ? 'checked' : '';
        $isClassifiedPrice=($this->classifiedOrauction == 'c') ? 'checked' : '';
        $isNone=($this->classifiedOrauction == 'n') ? 'checked' : '';

      
        $fields = array(
            "%LISTING_TYPE%" => $listing,
            "%FEES_TYPE%" => filtering($this->data['feesType']),
            "%PRICE%" => filtering($this->data['price']),
            "%DESCRIPTION%" =>filtering($this->data['description']),
            "%STATIC_A%" => filtering($static_a),
            "%STATIC_D%" => filtering($static_d),
            "%TYPE%" => filtering($this->type),
            "%AUCTION%" => filtering($isAuctionPrice),
            "%CLASSIFIED%" => filtering($isClassifiedPrice),
            "%NONE%" => filtering($isNone),
            "%FEESID%" => filtering($this->feesId, 'input', 'int')
        );

        $content = str_replace(array_keys($fields), array_values($fields), $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() 
    {
        $content = $operation = $whereCond = $totalRow =NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        if (isset($chr) && $chr != '') {
            $whereCond="where feesType LIKE '%".$chr."%' or listingTypeName LIKE '%".$chr."%' or price LIKE '%".$chr."%'";
        }
        if(!empty($filtering_type) && $filtering_type!=0) {
                $whereCond.=" and tbl_listing_type.listingTypeId=".$filtering_type;
            }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'feesId DESC';
       $qry = $this->db->pdoQuery("SELECT tbl_fees.*,tbl_listing_type.listingTypeName from tbl_fees INNER JOIN tbl_listing_type  on (tbl_listing_type.listingTypeId = tbl_fees.listingTypeId) ".$whereCond."  ORDER BY ".$sorting." LIMIT ".$offset." , ".$rows);
        $qrySel = $qry->results();
        $totalRow=count($qrySel);
        foreach ($qrySel as $fetchRes) 
        {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?feesId=" . $fetchRes['feesId'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&feesId=" . $fetchRes['feesId'] . "", "class" => "btn default  black btnEdit","extraAtt" => "title = 'Edit'", "value" => '<i class="fa fa-edit"></i>')) : '';
            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&feesId=" . $fetchRes['feesId'] . "", "extraAtt" => "title = 'View'","class" => "btn default blue  btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>')) : '';
            if($fetchRes['isDeleted'] == 'n')
            {
                $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&feesId=" . $fetchRes['feesId'] . "","extraAtt" => "title = 'Delete'", "class" => "btn default  red btn-delete", "value" => '<i class="fa fa-trash-o"></i>')) : '';
            }
            else
            {
                $operation .=(in_array('undo', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=undo&feesId=" . $fetchRes['feesId'] . "","extraAtt" => "title = 'Undo'", "class" => "btn default btn-info btn-undo", "value" => '<i class="fa fa-reply"></i>')) : '';
            }

            $final_array = array(
                filtering($fetchRes["feesId"]),
                filtering($fetchRes["feesType"]),
                filtering(CURRENCY_SYMBOL.$fetchRes["price"]),
                filtering($fetchRes["listingTypeName"])

            );
            if (in_array('status', $this->Permission)) {
                $final_array = array_merge($final_array, array($switch));
            }
            if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission)) {
                $final_array = array_merge($final_array, array($operation));
            }
            $row_data[] = $final_array;
        }
        $result["sEcho"] = $sEcho;
        $result["iTotalRecords"] = (int) $totalRow;
        $result["iTotalDisplayRecords"] = (int) $totalRow;
        $result["aaData"] = $row_data;
        return $result;
    }
    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }

}
