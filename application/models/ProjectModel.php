<?php

class ProjectModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = & Db::getInstance();
    }

    /**
     * 获取项目
     */
    public function getProjects ()
    {
        $list = $this->db->table('~project~')->field('id,name,icon,url')->where('status = 1')->order('sort desc')->select();
        foreach ($list as $k => $v) {
            $list[$k]['icon'] = httpurl($v['icon']);
        }
        return $list;
    }

}
