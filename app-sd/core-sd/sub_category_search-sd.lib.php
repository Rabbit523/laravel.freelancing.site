<?php

class subCategorySearch {
    function __construct($module = "", $cat_id=0) {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module = $module;
        $this->cat_id = $cat_id;
    }
    
    public function getPageContent() 
    {
        $keyword = (array_key_exists('keyword',$this->search_array)) ? urldecode($this->search_array['keyword']) : '';
        $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/sub_category_search-sd.skd");
        
        $sub_content = $sub_content->compile();
        $cat_id = $this->cat_id;
        $cat_res = $this->db->pdoQuery("SELECT * FROM tbl_category WHERE isActive='y' AND isDelete='n' AND id=".$cat_id)->result();
        $cat_name = $cat_res[l_values('category_name')];
        return str_replace(array("%CATEGORY_LIST%","%SUB_CAT_LIST%","%SELECTED_CAT_NAME%"), array($this->category_list($cat_id),$this->subcategoryData($cat_id,$cat_name),$cat_name), $sub_content);
    }
    public function category_list($cat_id){
        $data="";
        $category_res = $this->db->pdoQuery("SELECT * FROM tbl_category WHERE isActive='y' AND isDelete='n'")->results();
        if(!empty($category_res)){
            foreach ($category_res as $value) 
            {
                $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/categories-sd.skd");
                $sub_content = $sub_content->compile();
                $image = SITE_CATEGORY_IMAGE.$value['category_image'];
                $selected = ($value["id"]==$cat_id)?"active":"";
                $array = array(
                        "%SELECT_CLASS%" => $selected,
                        "%CAT_IMAGE%" => $image,
                        "%CAT_ID%" => filtering(ucfirst($value["id"])),
                        "%CAT_NAME%" => filtering(ucfirst($value[l_values('category_name')])),
                        "%URL%"=>SITE_URL."search/sub-category/".$value["id"],
                    );                
                $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
            }
        }
        return $data;
    }
    /*public function loop_data($keyword='')
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
    }*/
    public function subcategoryData($id,$cat_name)
    {
        $query = $this->db->pdoQuery("select * from tbl_subcategory where maincat_id = ? and isActive=? and isDelete=?",array($id,'y','n'))->results();
        $data = '';
        foreach ($query as $value) 
        {
            $sub_content = new MainTemplater(DIR_TMPL . $this->module . "/subcat_loop-sd.skd");
            $sub_content = $sub_content->compile();
            $image = !empty($value['sub_cat_image'])?(SITE_SUB_CATEGORY_IMAGE.$value['sub_cat_image']):SITE_UPD."default-image_450.png";
            $array = array(
                    "%SUB_CAT_NAME%" => filtering(ucfirst($value[l_values('subcategory_name')])),
                    "%URL%" => SITE_URL."search/freelancer/?category=".urlencode($cat_name)."&subcategory=".urlencode($value[l_values('subcategory_name')]),
                    "%SUB_CAT_IMAGE%"=>$image
                );
            $data .= str_replace(array_keys($array), array_replace($array), $sub_content);
        }
        return $data;
    }
   
  
}
 ?>


 