<?php

class FreelancerPaymentHistory {
	function __construct($module = "", $id = 0, $token = "",$search_array= array()) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
        $this->search_array = $search_array;
	}
	
  	public function getPageContent() 
  	{
  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/freelancer_payment_history-sd.skd");
        $sub_content = $sub_content->compile();
        return str_replace(array("%LOOP_DATA%",'%CREDIT_PACKAGE_DATA%','%SERVICE_DATA%'), array($this->loop_Data(),$this->credit_package_data(),$this->service_data()), $sub_content);
    }

    public function loop_Data()
    {
        $query = $this->db->pdoQuery("select s.serviceTitle,w.* from tbl_wallet As w
            LEFT JOIN tbl_services AS s ON s.id = w.entity_id
            where userId=? and transactionType=? and entity_type=?",array($this->sessUserId,'featuredFees',"s"))->results();
        $data = '';
        foreach ($query as $value) 
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/loop_data-sd.skd");
            $sub_content = $sub_content->compile();

            $array = array(
                "%PAYMENT_DATE%" => date('dS F,Y',strtotime($value['createdDate'])),
                "%PAID_AMOUNT%" => CURRENCY_SYMBOL.$value['amount'],
                "%SERVICE_TITLE%" => filtering(ucfirst($value['serviceTitle'])),
                "%PAYMENT_STATUS%" => ($value['paymentStatus']=='p') ? PENDING_LABEL : C_SO_COMPLETED_LBL
                );

            $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }

        if(empty($data)){
            $data = '<tr> <td colspan="4" style="text-align:center" > '.NO_RECORDS_FOUND.'</td> </tr>';
        }

        return $data;
    }

    public function credit_package_data()
    {
        $query = $this->db->pdoQuery("SELECT w.*,c.title FROM `tbl_wallet` as w LEFT JOIN tbl_credit_package as c on w.entity_id = c.id WHERE transactionType = 'creaditPurchase' AND w.userId = ? order by createdDate desc  ",array($this->sessUserId))->results();
        $data = '';

        foreach ($query as $value) 
        {
            $value['title'] = !empty($value['title']) ? $value['title'] : 'N/A';
            $data .= '<tr>';
            $data .= '<td>'.filtering(ucfirst($value['title'])).' </td>';
            $data .= '<td>'.CURRENCY_SYMBOL.$value['amount'].' </td>';
            $data .= '<td>'.date('dS F,Y',strtotime($value['createdDate'])).' </td>';
            $data .= '<td>'.(($value['paymentStatus']=='p') ? PENDING_LABEL : C_SO_COMPLETED_LBL).' </td>';
            $data .= '</tr>';
        }

        if(empty($data)){
            $data = '<tr> <td colspan="4" style="text-align:center" > '.NO_RECORDS_FOUND.'</td> </tr>';
        }

        return $data;
    }

    public function service_data()
    {
        $query = $this->db->pdoQuery("SELECT w.*,s.serviceTitle FROM `tbl_wallet` as w LEFT JOIN tbl_services as s on w.entity_id = s.id WHERE transactionType = 'featuredFees' AND w.userId = ? and w.entity_type = 's' order by createdDate desc  ",array($this->sessUserId))->results();
        $data = '';

        foreach ($query as $value) 
        {
            $value['serviceTitle'] = !empty($value['serviceTitle']) ? $value['serviceTitle'] : 'N/A';
            $data .= '<tr>';
            $data .= '<td>'.filtering(ucfirst($value['serviceTitle'])).' </td>';
            $data .= '<td>'.CURRENCY_SYMBOL.$value['amount'].' </td>';
            $data .= '<td>'.date('dS F,Y',strtotime($value['createdDate'])).' </td>';
            $data .= '<td>'.(($value['paymentStatus']=='p') ? PENDING_LABEL : C_SO_COMPLETED_LBL).' </td>';
            $data .= '</tr>';
        }

        if(empty($data)){
            $data = '<tr> <td colspan="4" style="text-align:center" > '.NO_RECORDS_FOUND.'</td> </tr>';
        }

        return $data;
    }

   
	     
}
 ?>


