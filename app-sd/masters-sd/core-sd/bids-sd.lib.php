<?php

class Bids extends Home {

    public $page_name;
    public $page_title;
    public $meta_keyword;
    public $meta_desc;
    public $page_desc;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_bids';

        $this->type = $type;
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel=$this->db->pdoQuery("select tbl_listing.listingId,tbl_listing.tagline,tbl_listing.listingTypeId,tbl_listing.saleType,tbl_listing.listStatus,tbl_listing.createdDate,tbl_listing.listingUrl,tbl_listing.buyNowPrice,
                tbl_bids.bidId,tbl_bids.sellerId,
                tbl_listing_type.listingTypeName,
                tbl_listing_category.categoryName,
                tbl_monetize_type.monTypeName 
                from tbl_bids 
                left join tbl_listing on (tbl_listing.listingId=tbl_bids.listingId) 
                left join tbl_listing_type on (tbl_listing_type.listingTypeId=tbl_listing.listingTypeId) 
                left join tbl_listing_category on (tbl_listing_category.listingTypeId=tbl_listing_type.listingTypeId) 
                left join tbl_monetize_type on (tbl_listing.monTypeId=tbl_monetize_type.monTypeId)  
                where tbl_bids.bidId = $id");
            $qrySel=$qrySel->result();
            $fetchRes = $qrySel;
            $this->data['sellerId'] = $this->sellerId = $fetchRes['sellerId'];
            $this->data['tagline'] = $this->tagline = $fetchRes['tagline'];
            $this->data['listingTypeId'] = $this->listingTypeId = $fetchRes['listingTypeId'];
            $this->data['saleType'] = $this->saleType = ($fetchRes['saleType']=='a')?"Auction":"Classified";
            $this->data['listStatus'] = $this->listStatus = $fetchRes['listStatus'];
            $this->data['buyNowPrice'] = $this->buyNowPrice = $fetchRes['buyNowPrice'];
            $this->data['listingTypeName'] = $this->listingTypeName = $fetchRes['listingTypeName'];
            $this->data['categoryName'] = $this->categoryName = $fetchRes['categoryName'];
            $this->data['listingUrl'] = $this->listingUrl = $fetchRes['listingUrl'];
            $this->data['createdDate'] = $this->createdDate = $fetchRes['createdDate'];
            $this->data['listingId'] = $this->listingId = $fetchRes['listingId'];
        } 
        switch ($type) {
            case 'view' : {
                    $this->data['content'] = json_encode($this->bids_details());
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
        $content = 
                $this->displayBox(array("label" => "Buyer Name&nbsp;:", "value" => filtering(getUserName($this->sellerId)))).
                $this->displayBox(array("label" => "Project URL&nbsp;:", "value" => filtering($this->listingUrl))).
                $this->displayBox(array("label" => "Project Tagline&nbsp;:", "value" => filtering($this->tagline))) .
                $this->displayBox(array("label" => "Project Type&nbsp;:", "value" => filtering($this->listingTypeName))) .
                $this->displayBox(array("label" => "Listing Status&nbsp;:", "value" => filtering($this->listStatus))).
                $this->displayBox(array("label" => "Sale Type&nbsp;:", "value" => filtering($this->saleType))).
                $this->displayBox(array("label" => "Buy Now Price&nbsp;:", "value" => filtering(CURRENCY_SYMBOL.$this->buyNowPrice))).
                $this->displayBox(array("label" => "Inserted Date&nbsp;:", "value" => filtering(date(DATE_FORMAT_ADMIN,strtotime($this->createdDate)))));
        return $content;
    }

    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow =$whereCond= NULL;
        $whereCond="where 1=1";
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%',"'",'"'), array('\_', '\%',"\'",'\"'), $chr);
        if (isset($chr) && $chr != '') {
            $whereCond= " and  (tbl_users.userName LIKE '%" . $chr . "%' or tl.listingUrl LIKE '%".$chr."%' tl.appName LIKE '%".$chr."%' or  DATE_FORMAT(tl.createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%".$chr."%')";
        }
        if(!empty($filtering_type) && $filtering_type!=0) {
                $whereCond.=" and tbl_listing_type.listingTypeId=".$filtering_type;
            }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'tbl_bids.listingId DESC';        
        $qrySel=$this->db->pdoQuery("SELECT tbl_bids.listingId,tbl_bids.bidId,tbl_bids.sellerId,
            tl.tagline,tl.listingUrl,tl.createdDate,tl.saleType,tl.appName,tl.listingTypeId,
            tbl_listing_type.listingTypeName ,tl.isDeleted As delListing,
            tbl_users.userName,tbl_users.isDeleted as userDeleted
            from tbl_bids
            join tbl_listing as tl on (tl.listingId=tbl_bids.listingId ) 
            join tbl_listing_type on (tbl_listing_type.listingTypeId=tl.listingTypeId)
            join tbl_users on (tbl_users.id=tbl_bids.sellerId)  $whereCond group by tbl_bids.listingId  ORDER BY $sorting limit $offset , $rows ")->results();

            $totalRow = $this->db->pdoQuery("SELECT tbl_bids.listingId,tbl_bids.bidId,tbl_bids.sellerId,tl.appName,tl.listingTypeId,
            tl.tagline,tl.listingUrl,tl.createdDate,tl.saleType,tl.isDeleted As delListing,
            tbl_listing_type.listingTypeName ,
            tbl_users.userName,tbl_users.isDeleted as userDeleted
            from tbl_bids
            join tbl_listing as tl on (tl.listingId=tbl_bids.listingId ) 
            join tbl_listing_type on (tbl_listing_type.listingTypeId=tl.listingTypeId)
            join tbl_users on (tbl_users.id=tbl_bids.sellerId)  $whereCond group by tbl_bids.listingId")->affectedRows();
        foreach ($qrySel as $fetchRes) 
        {
            $operation = '';
            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['bidId'] . "", "class" => "btn default blue btnEdit","extraAtt" => "title = 'View'", "value" => '<i class="fa fa-laptop"></i>')) : '';

            $delete_status = ($fetchRes['delListing']=='y')?"<span class='label label-warning'>&nbsp;Listing deleted</span>":'';
            $user_status = ($fetchRes['userDeleted']=='y')?"<span class='label label-warning'>&nbsp;User deleted</span>":'';
            $final_array = array(
                filtering($fetchRes["bidId"]),
                filtering(ucfirst($fetchRes["userName"])),
                filtering(($fetchRes['listingTypeId']=='4')?$fetchRes['appName']:displaySiteUrl($fetchRes["listingUrl"])).'<br>'.$delete_status,
                filtering($fetchRes["listingTypeName"]),
                filtering(($fetchRes["saleType"]=='a')?"Auction":"Classified"),
                filtering(date(DATE_FORMAT_ADMIN,strtotime($fetchRes["createdDate"]))),
            );
            if (in_array('delete', $this->Permission) || in_array('view', $this->Permission)) {
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
    public function bids_details(){
        $content = '';
        $content.=$this->viewForm();
        $content.="<hr><hr><h2 align='center'>Bid Details</h2>";
        
        $qry=$this->db->pdoQuery("SELECT tbl_bids.*,bu.firstName,bu.lastName,bu.userName  from tbl_bids inner join tbl_listing on (tbl_listing.listingId=tbl_bids.listingId) inner join tbl_users on (tbl_users.id=tbl_bids.sellerId) inner join tbl_users as bu on (bu.id=tbl_bids.buyerId) where tbl_bids.listingId=".$this->listingId ." order by tbl_bids.biddedDate DESC");
        $query=$qry->results();
            $content.="<div class='table-responsive col-md-12' id='table_bid'><table class='table table-striped  table-bordered'><thead>
                        <th>Bidder Name</th>
                        <th>Amount</th>
                        <th>Bidded Date</th>
                        <th>Reserved</th>
                        </thead><tbody>
                        ";
             foreach ($query as $value) {
                $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-sd.skd");
                $main_content = $main_content->compile();
                if($value['isWon'] == 'y'){
                    $view_class="well";
                    $approvedDate="<b>(Approved Date : ".date(DATE_FORMAT_ADMIN,strtotime($value['approvedDate'])).")</b>"; 
                    $winner="<b>(Winner)</b>"; 
                    $style="background-color: #1abb9c;color:white;";            
                }else{
                    $view_class="";
                    $approvedDate="";
                    $winner="";  
                    $style="";        
                }
                $data = array(
                    "%MSG%" => CURRENCY_SYMBOL.$value['amount'], 
                    "%USER%" => $value['userName'],
                    "%DATE%" => date(DATE_FORMAT_ADMIN,strtotime($value['biddedDate'])),
                    "%APPROVED_DATE%" => $approvedDate,
                    "%CLASS%" => $view_class,
                    "%IS_RESERVED%" =>($value['isReserve']=='y')?"Yes":"No",
                    "%IS_WON%" => $winner,
                    "%WINNER%" => $style
                );
                $content .= str_replace(array_keys($data), array_values($data), $main_content);
            }
            $content.="</tbody><tfoot>
                    <th>Bidder Name</th>
                    <th>Amount</th>
                    <th>Bidded Date</th>
                    <th>Reserved</th>
                    </tfoot></table>";
        return sanitize_output($content);
    }
}
