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

    /**
     * 获取排班时间
     * @param $store_id 门店
     * @param $date 日期 xxxx-xx-xx
     * @return array
     */
    public function getScheduleTimes ($store_id, $date)
    {
        $time = strtotime($date);
        if ($time < TIMESTAMP - 86400) {
            return [];
        }

        $condition = [
            'storeid = ' . $store_id,
            'today = "' . date('Y-m-d', $time) . '"',
            'starttime > "' . date('Y-m-d H:i:s', TIMESTAMP) . '"'
        ];

        $condition = implode(' and ', $condition);
        // 获取排班
        if (!$poolList = $this->db->table('pro_pool')->field('id,right(starttime,8) as time,maxcount')->where($condition)->order('starttime asc')->select()) {
            return [];
        }

        // 获取未过期占号
        $condition = [
            'storeid = ' . $store_id,
            'poolid in (' . implode(',', array_column($poolList, 'id')) . ')',
            'status = 0',
            'createtime > "' . date('Y-m-d H:i:s', TIMESTAMP - 1200) . '"'
        ];
        $condition = implode(' and ', $condition);
        $orderPool = $this->db->table('pro_order')->field('poolid,count(*) as count')->where($condition)->group('poolid')->select();
        if ($orderPool) {
            $orderPool = array_column($orderPool, 'count', 'poolid');
            foreach ($poolList as $k => $v) {
                // 减去占号数
                if (isset($orderPool[$v['id']])) {
                    $poolList[$k]['maxcount'] = min(0, $v['maxcount'] - $orderPool[$v['id']]);
                }
            }
        }

        // 格式化输出
        foreach ($poolList as $k => $v) {
            $poolList[$k]['time'] = substr($v['time'], 0, 5);
            $poolList[$k]['leFT'] = $v['maxcount'];
        }

        return $poolList;
    }

    /**
     * 获取预约排班
     */
    public function getScheduleDays ($store_id, $schedule_days = 15)
    {
        $date = [];
        for ($i = 0; $i < $schedule_days; $i++) {
            $date[] = date('Y-m-d', TIMESTAMP + 86400 * $i);
        }

        $condition = [
            'storeid = ' . $store_id,
            'today in (' . ('"' . implode('","', $date) . '"') . ')',
            'starttime > "' . date('Y-m-d H:i:s', TIMESTAMP) . '"'
        ];

        $condition = implode(' and ', $condition);
        // 获取排班剩号数
        $poolList = $this->db->table('pro_pool')->field('today,sum(maxcount) as maxcount')->where($condition)->group('today')->select();

        if ($poolList) {
            $poolList = array_column($poolList, 'maxcount', 'today');
            // 获取未过期占号
            $condition = [
                'storeid = ' . $store_id,
                'status = 0',
                'createtime > "' . date('Y-m-d H:i:s', TIMESTAMP - 1200) . '"'
            ];
            $condition = implode(' and ', $condition);
            $orderPool = $this->db->table('pro_order')->field('left(ordertime,10) as today,count(*) as count')->where($condition)->group('today')->select();
            if ($orderPool) {
                $orderPool = array_column($orderPool, 'count', 'today');
                foreach ($poolList as $k => $v) {
                    // 减去占号数
                    if (isset($orderPool[$k])) {
                        $poolList[$k] = min(0, $v - $orderPool[$k]);
                    }
                }
            }
        }

        $week = [
            0 => '周日', 1 => '周一', 2 => '周二', 3 => '周三', 4 => '周四', 5 => '周五', 6 => '周六'
        ];
        // 格式化
        foreach ($date as $k => $v) {
            $time = strtotime($v);
            $date[$k] = [
                'date'   => $v,
                'week'   => $week[date('w', $time)],
                'month'  => date('n', $time),
                'day'    => date('j', $time),
                'amount' => intval($poolList[$v])
            ];
        }

        return $date;
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
