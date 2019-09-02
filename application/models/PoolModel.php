<?php
/**
 * 用户模型
 */
class PoolModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = & Db::getInstance();
    }

    public function getPool ($code = '')
    {
        if ($code == "") {
            $rs = $this->db->table('~city~')->field('*')->select();
        
        } else {
            $rs = $this->db->table('~city~')->field('*')->where('code=' . $code)->find();
        }
        return $rs;
    }

    public function getPoollist ($sql)
    {
        $rs = $this->db->select($sql);
        return $rs;
    }

    public function getPoolinfo ($sql)
    {
        $rs = $this->db->find($sql);
        return $rs;
    }
    
    public function getPoolCount($where)
    {
        return $this->db->table('~pool~')->field('count(*)')->where($where)->find(null, true);
    }

    public function getPoollistwhere ($where, $limit = "")
    {
        if ($limit == "") {
            $rs = $this->db->table('~pool~')->field('*')->where($where)->select();
        } else {
            $rs = $this->db->table('~pool~')->field('*')->where($where)->order('today asc')->limit($limit)->select();
        }
        return $rs;
    }

    public function getPoolwhereinfo ($where)
    {
        $rs = $this->db->table('~pool~')->field('*')->where($where)->find();
        return $rs;
    }

    public function insertPool ($cityarray)
    {
        return $this->db->insert('~pool~', $cityarray);
    }

    public function replacePool ($cityarray)
    {
        if (!$this->db->insert('~pool~', $cityarray)) {
            $this->db->update('~pool~', array(
                    'maxcount' => $cityarray['maxcount']
            ), 'today = "' . $cityarray['today'] . '" and storeid = ' . $cityarray['storeid'] . ' and starttime = "' . $cityarray['starttime'] . '"');
        }
        return true;
    }
    
    public function getTodayPool($today, $storeid)
    {
        $storeid = intval($storeid);
        $rs = $this->db->table('~pool~')->field('id,starttime')->where('storeid = '.$storeid.' and today = "'.$today.'"')->select();
        $rs = $rs ? array_column($rs, 'starttime', 'id') : [];
        return $rs;
    }

    public function updatePool ($cityarray, $id)
    {
        return $this->db->update('~pool~', $cityarray, 'id = ' . $id);
    }

    public function deletePool ($id)
    {
        return $this->db->delete('~pool~', 'id in ('.$id.')');
    }

}