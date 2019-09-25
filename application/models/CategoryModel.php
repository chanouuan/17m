<?php
/**
 * 用户模型
 */
class CategoryModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = & Db::getInstance();
    }

    /**
     * 获取套餐分类
     */
    public function getCategoryType ($where = 'status = 1')
    {
        $list = $this->db->table('pro_category_type')->field('id,name')->where($where)->order('sort desc')->select();
        return array_column($list, null, 'id');
    }

    /**
     * 获取产品关联信息
     */
    public function getRelationCategory ($store_id, $categroy_id)
    {
        return $this->db->table('pro_category a inner join pro_story_category b on b.categroy_id = a.id')
            ->field('b.store_id,b.categroy_id as category_id,b.price')
            ->where('b.store_id = ' . $store_id . ' and b.categroy_id = ' . $categroy_id)
            ->find();
    }

    /**
     * 根据门店查询产品
     */
    public function getCategoryByStore ($store_id)
    {
        $list = $this->db->table('pro_story_category a inner join pro_category b on b.id = a.categroy_id inner join pro_store c on c.id = a.store_id')
            ->field('b.id,b.name,b.icon,b.type')
            ->where('c.id = ' . $store_id)
            ->order('b.sort')
            ->select();
        foreach ($list as $k => $v) {
            $list[$k]['icon'] = httpurl($v['icon']);
        }
        return $list;
    }

    public function getCategory ($id = null, $where = '', $limit = '', $field = '*', $order = 'sort asc')
    {
        if (isset($id)) {
            $id = intval($id);
            $rs= $this->db->table('~category~')->field($field)->where('id='.$id.($where?(' and ('.$where.')'):''))->find();
        } else {
            $rs = $this->db->table('~category~')->field($field)->where($where)->order($order)->limit($limit)->select();
        }
        return $rs;
    }
    public function getCategorylist ($sql)
    {
        $rs =  $this->db->select($sql);
        return $rs;
    }

    public function getCategoryInfo($sql)
    {
        $rs =  $this->db->find($sql);
        return $rs;
    }

    public function getStoreByCategoryId ($category_id)
    {
        $category_id = intval($category_id);
        return $this->db->field('a.price,b.name')->table('~story_category~ a left join ~store~ b on b.id = a.store_id')->where('a.categroy_id = '.$category_id)->order('b.sort')->select();
    }

    public function getCategoryNew($storeId,$categoryid){
        $sql="SELECT pro_category.type, pro_category.`name`, pro_category.`icon`, pro_category.`delay`, pro_category.`description`
	            , pro_category.id, pro_story_category.price
            FROM pro_category LEFT JOIN pro_story_category ON pro_category.id = pro_story_category.categroy_id
                        WHERE pro_story_category.store_id = '{$storeId}' and pro_story_category.categroy_id='{$categoryid}'";
        $rs =  $this->db->find($sql);
        return $rs;
    }
    public function getCategoryNew2($story_categoryid){
        $sql="SELECT pro_category.type, pro_category.`name`, pro_category.`icon`, pro_category.`delay`, pro_category.`description`
	            , pro_story_category.id, pro_story_category.price
            FROM pro_category LEFT JOIN pro_story_category ON pro_category.id = pro_story_category.categroy_id
                        WHERE pro_story_category.id = '{$story_categoryid}' ";
        $rs =  $this->db->find($sql);
        return $rs;
    }
    public function getCategorywhere($where, $limit = '', $field = '*', $order = 'sort asc'){
        return $this->db->table('~category~')->field($field)->where($where)->order($order)->limit($limit)->select();
    }
    public function getCategorywhereinfo($where, $field = '*'){
        $rs= $this->db->table('~category~')->field($field)->where($where)->find();
        return $rs;
    }
    public function  insertCategory($cityarray){
        return $this->db->insert("~category~", $cityarray);
    }

    public function  updateCategory($cityarray,$id){
        return  $this->db->update('~category~', $cityarray, 'id = ' . $id);
    }
    public function deleteCategory($id){
        return  $this->db->delete('~category~', 'id = ' . $id);
    }
    public function getpackage($id){
        $rs= $this->db->table('~story_category~')->field('*')->where(" id=".$id)->find();
        return $rs;
    }
}
