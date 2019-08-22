<?php

class user_payments extends Home {
    
    public $typeName;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_refund_payment';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("refundId" => $id))->result();

            $fetchRes = $qrySel;
            $this->data['userId'] = $this->userId = $fetchRes['userId'];
            $this->data['amount'] = $this->amount = $fetchRes['amount'];
            $this->data['listingId'] = $this->listingId = $fetchRes['listingId'];
            $this->data['isPaid'] = $this->isPaid = $fetchRes['isPaid'];
            $this->data['islistingDeleted'] = $this->islistingDeleted = $fetchRes['islistingDeleted'];
            $this->data['isBuyerPaid'] = $this->isBuyerPaid = $fetchRes['isBuyerPaid'];
            $this->data['isSellerTransfer'] = $this->isSellerTransfer = $fetchRes['isSellerTransfer'];
            $this->data['isBuyerAccept'] = $this->isBuyerAccept = $fetchRes['isBuyerAccept'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];

        } else {
            $this->data['userId'] = $this->userId = '';
            $this->data['amount'] = $this->amount = '';
            $this->data['listingId'] = $this->listingId = '';
            $this->data['isPaid'] = $this->isPaid = '';
            $this->data['islistingDeleted'] = $this->islistingDeleted = '';
            $this->data['isBuyerPaid'] = $this->isBuyerPaid = '';
            $this->data['isSellerTransfer'] = $this->isSellerTransfer = '';
            $this->data['isBuyerAccept'] = $this->isBuyerAccept = '';
            $this->data['createdDate'] = $this->createdDate = '';

        }
        switch ($type) {
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
            case 'edit' : 
            {
                $this->data['content'] =  (in_array('edit',$this->Permission))?$this->getForm():'';
                break;
            }
        }
    }


    public function viewForm() {
        $content = 
                $this->displayBox(array("label" => "User Name&nbsp;:", "value" => filtering(ucfirst(getUserDetails('userName',$this->userId)))));
        $content.=($this->listingId!=0)? $this->displayBox(array("label" => "Listing URL&nbsp;:", "value" => filtering(getlistingUrl_admin($this->listingId)))).$this->displayBox(array("label" => "Paid By Buyer?&nbsp;:", "value" => filtering(($this->isBuyerPaid=='y')?'Yes':'No'))).$this->displayBox(array("label" => "Seller Transfer Files?&nbsp;:", "value" => filtering(($this->isSellerTransfer=='y')?'Yes':'No'))).$this->displayBox(array("label" => "Buyer Accept Files?&nbsp;:", "value" => filtering(($this->isBuyerAccept=='y')?'Yes':'No'))):'';
        $content.=($this->islistingDeleted=='y')?$this->displayBox(array("label" => "Listing Deleted&nbsp;:", "value" => "Yes")):$this->displayBox(array("label" => "User Deleted&nbsp;:", "value" =>"Yes"));
        $content.=$this->displayBox(array("label" => "Amount&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$this->amount))).
        $this->displayBox(array("label" => "Paid By Admin?&nbsp;:", "value" => filtering(($this->isPaid=='y')?'Yes':'No'))).
                $this->displayBox(array("label" => "Inserted Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime(($this->createdDate))))));
        return $content;
    }
    public function getForm() {
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
        $main_content = $main_content->compile();
        $query=$this->db->pdoQuery('SELECT file_accept_status,listingId,userId,isBuyerPay from tbl_listing where listingId='.$this->listingId)->result();
        $content="<select class='form-control' name='file_status' id='file_status'><option value=''>Select File Status</option><option value='accepted'>Accept</option><option value='rejected'>Reject</option></select>";
        $fieldArr = array("%ID%","%TYPE%","%FILE_STATUS%","%SELLER_ID%","%BUYER_ID%");
        $replaceArr = array($query['listingId'],$this->type,$content,$query['userId'],$query['isBuyerPay']);
        $html = str_replace($fieldArr, $replaceArr, $main_content);     
        return $html;
    }
    
    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
         $whereCond = "where 1=1";
        if (isset($chr) && $chr != '') {
           $whereCond .= " and (userName LIKE '%" . $chr . "%' OR w.amount LIKE '%".$chr."%'  OR DATE_FORMAT(w.createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')";
        }
        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'w.refundId DESC';

        $totalRow = $this->db->pdoQuery("SELECT w.*,w.refundId As wid,w.createdDate As walletDate,u.*,u.id As uId,u.userName,tl.file_accept_status
            FROM tbl_refund_payment As w 
            LEFT JOIN tbl_users As u ON w.userId = u.id 
            LEFT JOIN tbl_listing AS tl on tl.listingId=w.listingId
            $whereCond")->affectedRows();
        
        $qrySel = $this->db->pdoQuery("SELECT w.*,w.refundId As wid,w.createdDate As walletDate,u.*,u.id As uId,u.userName,tl.file_accept_status 
            FROM tbl_refund_payment As w 
            LEFT JOIN tbl_users As u ON w.userId = u.id
            LEFT JOIN tbl_listing AS tl on tl.listingId=w.listingId 
            $whereCond ORDER BY $sorting LIMIT $offset , $rows")->results();
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";
            $operation = '';           

            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['wid'] . "", "class" => "btn default blue  btn-viewbtn","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';
            if($fetchRes['isPaid']=='n'){
                if($fetchRes['isSellerTransfer']=='y' && $fetchRes['islistingDeleted']=='y'){
                    $operation.=($fetchRes['file_accept_status']=="pending")?((in_array('edit',$this->Permission))?$this->operation(array("href"=>"ajax.".$this->module.".php?action=edit&id=".$fetchRes['wid']."", "extraAtt" =>"title = 'Edit'", "class"=>"btn default black btnEdit","value"=>'<i class="fa fa-edit"></i>')):''):(($fetchRes['file_accept_status']=='accepted')?"<button class='btn default btn-info btn-xs'>File Accepted</button>":"<button class='btn default btn-info btn-xs'>File Rejected</button>");
                }else{
                    $operation.=$this->operation(array("href" => "index.php?action=pay_refund&wid=" . $fetchRes['wid'] . "", "class" => "btn default btn-warning btn-xs","extraAtt" => "title = 'Pay'", "value" => CURRENCY_SYMBOL.$fetchRes['amount'].'&nbsp;Pay to User',"target"=>'_blank'));
                }
            }else{                
                $operation.="<button class='btn default btn-info btn-xs'>Payment Completed</button>";
            }
            $final_array = array(
                filtering($fetchRes["wid"]),
                filtering(ucfirst($fetchRes["userName"])),
                CURRENCY_SYMBOL.filtering($fetchRes["amount"]),
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes["walletDate"])))
            );            
            if (in_array('view', $this->Permission)) {
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

    

    public function operation($text) {

        $text['title'] = isset($text['title']) ? $text['title'] : '';
        $text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL .'/operation-sd.skd');
        $main_content = $main_content->compile();
        $fields = array("%HREF%", "%CLASS%", "%VALUE%", "%EXTRA%","%TITLE%");
        $fields_replace = array($text['href'], $text['class'], $text['value'], $text['extraAtt'],$text['title']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function displaybox($text) {

        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . '/displaybox.skd');
        $main_content = $main_content->compile();
        $fields = array("%LABEL%", "%CLASS%", "%VALUE%");
        $fields_replace = array($text['label'], $text['class'], $text['value']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".skd");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->compile();
        return $final_result;
    }
    
    public function contentSubmit($data,$Permission){
        $response = array();
        $response['status'] = false;
        if(!empty($data))
        {
            extract($data);
            $id=isset($id)?$id:0;
            $file_status=(isset($file_status))?$file_status:'pending';
            if ($type == 'edit' && $id > 0) {
                if (in_array('edit', $Permission)) {

                    $array=array(
                    "file_accept_status" => $file_status,
                    "file_transfer_date" => date('Y-m-d H:i:s')
                    );
                    $query=$this->db->update('tbl_listing',$array,array('listingId'=>$id))->affectedRows();
                    if($file_status=='accepted'){
                        $this->db->update("tbl_wallet",array('status'=>'inWallet'),array('projectId'=>$id,'earnFrom'=>'fromBuyer','userId'=>$buyerId));
                        $this->db->update("tbl_wallet",array('status'=>'inWallet'),array('projectId'=>$id,'earnFrom'=>'fromSeller','userId'=>$sellerId));
                        $this->db->update("tbl_wallet",array('status'=>'inWallet'),array('projectId'=>$id,'earnFrom'=>'listingCommission','userId'=>0));
                        $sellerWalletAmount = getTableValue("tbl_wallet","amount",array('projectId'=>$id,'earnFrom'=>'fromSeller','userId'=>$sellerId));
                        $sellerAmount = checkUserWalletAmount($sellerId);
                        $message="Admin Accept the files of listing ".getlistingFullUrl($id);
                        $sellerFinalAmount = $sellerAmount + $sellerWalletAmount;
                        $this->db->update("tbl_users",array('walletAmount'=>(string)$sellerFinalAmount),array('id'=>$sellerId));
                        $this->db->update("tbl_listing",array("listStatus"=>"sold"),array('listingId'=>$id));
                        $this->db->update("tbl_manage_order",array("paymentStatus"=>"completed"),array('listingId'=>$id,'listingOrderType'=>'c','buyerId'=>$buyerId,'sellerId' => $sellerId));
                        
                        $this->db->insert("tbl_notification",array("userId"=>$sellerId,"message"=>$message,"notificationType"=>"s","createdDate"=>date("Y-m-d H:i:s")));
                        $this->db->update("tbl_refund_payment",array('isBuyerAccept'=>'y'),array('listingId'=>$id,'islistingDeleted' => 'y','userId'=> $sellerId));
                    } 
                    else{
                        $buyerAmount=checkUserWalletAmount($buyerId);
                         $message="Admin Reject the files of listing ".getlistingFullUrl($id);
                        $buyerWalletAmount=getTableValue("tbl_wallet","amount",array('projectId'=>$id,'earnFrom'=>'fromBuyer','userId'=>$buyerId));

                        $buyerFinalAmount = $buyerAmount + $buyerWalletAmount;
                        $this->db->update("tbl_users",array('walletAmount'=>(string)$buyerFinalAmount),array('id'=>$buyerId));
                        $this->db->insert("tbl_notification",array("userId"=>$sellerId,"message"=>$message,"notificationType"=>"s","createdDate"=>date("Y-m-d H:i:s")));
                        $db->update('tbl_wallet',array('status' =>'refund'),array('projectId'=>$id,'earnFrom'=>'fromBuyer','userId'=>$buyerId));
                    }           
                    $response['status'] = true;
                    $response['success'] = "File accept status updated successfully";
                    echo json_encode($response);
                    exit;
                } else {
                    $response['error'] = "You don't have permission to edit File Accept Status";
                    echo json_encode($response);
                    exit;
                }
            }   
        }
    }

}
