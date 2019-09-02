<?php
/**
 * 用户模型
 */
class CityModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = & Db::getInstance();
    }

    public function getCityCode ($cityname)
    {
        $cityname = msubstr(safe_subject(trim($cityname)), 0, 2);
        return $this->db->table('~city~')->field('code')->where('status = 1 and name like "' . $cityname . '%"')->find(null, true);
    }

    public function getCity ($code = '', $where = '')
    {
        if ($code == "") {
            $rs = $this->db->table('~city~')->field('*')->order(" sort asc")->select();
        
        } else {
            $rs = $this->db->table('~city~')->field('*')->where('code=' . $code . ($where ? ' and ' . $where : ''))->find();
        }
        return $rs;
    }

    public function getcitylist ($where, $limit = "")
    {
        if ($limit == "") {
            $rs = $this->db->table('~city~')->field('*')->where($where)->order(" sort asc")->select();
        } else {
            $rs = $this->db->table('~city~')->field('*')->where($where)->order(" sort asc")->limit($limit)->select();
        }
        return $rs;
    }

    public function getcityinfo ($where)
    {
        $rs = $this->db->table('~city~')->field('*')->where($where)->find();
        return $rs;
    }

    public function insertCity ($cityarray)
    {
        return $this->db->insert("pro_city", $cityarray);
    }

    public function updateCity ($cityarray, $code)
    {
        return $this->db->update('pro_city', $cityarray,"code = '{$code}'");
    }

    public function deletecity ($code)
    {
        return $this->db->delete('pro_city', "code = '{$code}'" );
    }

}