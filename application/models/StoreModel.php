<?php
/**
 * 用户模型
 */
class StoreModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = & Db::getInstance();
    }

    public function getStore ($id = null, $where = '', $limit = '', $field = '*', $order = 'id desc')
    {
        if (isset($id)) {
            $id = intval($id);
            $rs= $this->db->table('~store~')->field($field)->where('id='.$id.($where?(' and ('.$where.')'):''))->find();
        } else {
            $rs = $this->db->table('~store~')->field($field)->where($where)->order($order)->limit($limit)->select();
        }
        return $rs;
    }
    public function getStorelist ($sql)
    {
        $rs = $this->db->select($sql);
        return $rs;
    }
    public function getstorewhere($where ,$limit=""){
        if($limit==""){
            $rs= $this->db->table('~store~')->field('*')->where($where)->select();
        }
        else{
            $rs = $this->db->table('~store~')->field('*')->where($where)->order(" sort asc")->limit($limit)->select();
        }
        return $rs;
    }
    public function getstoreinfo($where){
        $rs= $this->db->table('~store~')->field('*')->where($where)->find();
        return $rs;
    }
    public function  insertstore($cityarray){
        return $this->db->insert("pro_store", $cityarray);
    }
    public function getlastid(){
        $id= $this->db->getlastid();
        return $id;
    }
    public function  updatestore($cityarray,$id){
        return  $this->db->update('pro_store', $cityarray, 'id = ' . $id);
    }
    public function deletestore($id){
        return  $this->db->delete('pro_store', 'id = ' . $id);
    }
    public function getcityname($code){
        return  $this->db->table('~city~')->field('*')->where('code='.$code)->find();

    }
    public function getcategorylist($id=null){
        if (isset($id)) {
            $id = intval($id);
            $rs=  $this->db->select("select * from pro_category where id in(SELECT categroy_id from pro_story_category where store_id= '{$id}' )");
        }
        else{
            $rs=$this->db->table('~category~')->field('*')->select();
        }
        return $rs;
    }
    public function insertcategory($id, $package){
        $category_ids = $this->db->table('~story_category~')->field('categroy_id')->where('store_id = ' . $id)->select();
        $category_ids = $category_ids ? array_column($category_ids, 'categroy_id') : [];
        $package = $package ? array_values($package) : [];
        // 新增
        $insert = array_diff($package, $category_ids);
        // 删除
        $delete = array_diff($category_ids, $package);
        if($insert){
            $data = [];
            foreach ($insert as $k=>$v){
                $data['categroy_id'][] = $v;
                $data['store_id'][] = $id;
            }
            $this->db->insert('~story_category~', $data);
        }
        if($delete){
            $this->db->delete('~story_category~', 'store_id = ' . $id . ' and categroy_id in ('.implode(',', $delete).')');
        }
    }
    public function  updatestory_category($cityarray,$id){
        return  $this->db->update('pro_story_category', $cityarray, 'id = ' . $id);
    }
}
