<?php

class categorySearch {
    function __construct($module = "", $type='jobs',$id = 0, $token = "",$search_array= array()) {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module = $module;
        $this->id = $id;
        $this->type = $type;
        $this->search_array = $search_array;
    }
    
    public function getPageContent() 
    {
        //printr($this->search_array,1);exit;
        $keyword = (array_key_exists('keyword',$this->search_array)) ? urldecode($this->search_array['keyword']) : '';
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/category_search-sd.skd");
        $sub_content = $sub_content->compile();
        return str_replace(array("%LOOP_DATA%"), $this->loop_data, $sub_content);
    }
    public function loop_data()
    {
        $query = $this->db->pdoQuery("select * from tbl_category where id IN (select maincat_id from tbl_subcategory)")->results();
        $data = '';
        foreach ($query as $value) 
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/maincat_loop-sd.skd");
            $sub_content = $sub_content->compile();
            $array = array(
                    "%CAT_TITLE%" => filtering($value[l_values('category_name')]),
                    "%CAT_LIST%" => $this->subcategoryData($value['id'],$categorylist)
                );
            $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
        return $data;
    }
   
  
}
 ?>


 