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
        $keyword = (array_key_exists('keyword',$this->search_array)) ? urldecode($this->search_array['keyword']) : '';
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/category_search-sd.skd");
        $sub_content = $sub_content->compile();
        return str_replace(array("%LOOP_DATA%","%KEYWORD%"), array($this->loop_data($keyword),$keyword), $sub_content);
    }
    public function loop_data($keyword='')
    {
        $where = ($keyword!='') ? " and (c.".l_values('category_name')." LIKE '%".filtering($keyword)."%'  OR ".l_values('subcategory_name')." LIKE '%".$keyword."%')" : '';
        $query = $this->db->pdoQuery("select c.* from tbl_category As c 
            LEFT JOIN tbl_subcategory As s ON s.maincat_id = c.id
            where c.id IN (select maincat_id from tbl_subcategory) $where group by s.maincat_id")->results();
        $data = '';
        foreach ($query as $value) 
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/maincat_loop-sd.skd");
            $sub_content = $sub_content->compile();
            $array = array(
                    "%MAIN_CAT_TITLE%" => filtering(ucfirst($value[l_values('category_name')])),
                    "%SUB_CAT_LIST%" => $this->subcategoryData($value['id'],$keyword,$value['category_name'])
                );
            $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
        return $data;
    }
    public function subcategoryData($id,$keyword='',$maincatName)
    {
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/subcat_loop-sd.skd");
        $sub_content = $sub_content->compile();
        
        $query = $this->db->pdoQuery("select * from tbl_subcategory where maincat_id = ? and isActive=? and isDelete=?",array($id,'y','n'))->results();
        $data = '';
        foreach ($query as $value) 
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/subcat_loop-sd.skd");
            $sub_content = $sub_content->compile();
            $array = array(
                    "%CAT_NAME%" => filtering(ucfirst($value[l_values('subcategory_name')])),
                    "%URL%" => SITE_URL."search/freelancer/?category=".urlencode($maincatName)."&subcategory=".urlencode($value[l_values('subcategory_name')])
                );
            $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
        return $data;
    }
   
  
}
 ?>


 