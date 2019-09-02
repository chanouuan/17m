<?php

class ConfigModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = Db::getInstance();
    }

    public function getConfig ($id = null, $where = '', $limit = '', $field = '*', $order = 'id desc')
    {
        if (isset($id)) {
            $id = intval($id);
            $rs = $this->db->table('~config~')->field('*')->where('id=' . $id)->find();
        } else {
            $rs = $this->db->table('~config~')->field($field)->where($where)->select();
        }
        return $rs;
    }
    public function getcitylist ($where, $limit = "")
    {
        if ($limit == "") {
            $rs = $this->db->table('~config~')->field('*')->where($where)->select();
        } else {
            $rs = $this->db->table('~config~')->field('*')->where($where)->order(" sort asc")->limit($limit)->select();
        }
        return $rs;
    }

    public function getconfiginfo ($where)
    {
        $rs = $this->db->table('~config~')->field('*')->where($where)->find();
        return $rs;
    }

    public function insertconfig ($cityarray)
    {
        return $this->db->insert("~config~", $cityarray);
    }

    public function updateconfig ($cityarray, $name)
    {
        return $this->db->update('~config~', $cityarray, "name ='{$name}'");
    }

    public function deleteconfig ($name)
    {
        return $this->db->delete('~config~', "name ='{$name}'");
    }

   
}