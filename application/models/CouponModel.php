<?php
/**
 * 用户模型
 */
class CouponModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = Db::getInstance();
    }

    public function getCoupon ($code = '')
    {
        if ($code == "") {
            $rs = $this->db->table('~coupon~')->field('*')->order(" sort asc")->select();
        } else {
            $rs = $this->db->table('~coupon~')->field('*')->where('code=' . $code)->find();
        }
        return $rs;
    }

    public function getCouponCount ($where)
    {
        return $this->db->table('~coupon~')->field('count(*)')->where($where)->find(null, true);
    }

    public function getCouponlist ($where, $limit = "")
    {
        if ($limit == "") {
            $rs = $this->db->table('~coupon~')->field('*')->where($where)->select();
        } else {
            $rs = $this->db->table('~coupon~')->field('*')->where($where)->order('id desc')->limit($limit)->select();
        }
        return $rs;
    }

    public function getCouponinfo ($where)
    {
        $rs = $this->db->table('~coupon~')->field('*')->where($where)->find();
        return $rs;
    }

    public function insertCoupon ($post)
    {
        $post['storeid'] = intval($post['storeid']);
        $post['type'] = intval($post['type']);
        $post['cost'] = intval($post['cost'] * 100);
        $post['count'] = intval($post['count']);
        $post['expire'] = date('Y-m-d 23:59:59', strtotime($post['expire']));
        if (!$post['storeid'] || $post['cost'] <= 0 || $post['count'] <= 0) {
            return false;
        }
        $params = [
                'type' => array_fill(0, $post['count'], $post['type']), 
                'storeid' => array_fill(0, $post['count'], $post['storeid']), 
                'cost' => array_fill(0, $post['count'], $post['cost']), 
                'partner' => array_map(function  () {
                    return $this->getRandomString(8);
                }, array_fill(0, $post['count'], null)), 
                'expire' => array_fill(0, $post['count'], $post['expire'])
        ];
        return $this->db->insert('~coupon~', $params);
    }

    public function getRandomString ($len, $chars = null)
    {
        if (is_null($chars)) {
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        // mt_srand(10000000 * (double) microtime());
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    public function updateCoupon ($cityarray, $code)
    {
        return $this->db->update('~coupon~', $cityarray, 'code = ' . $code);
    }

    public function deleteCoupon ($id)
    {
        return $this->db->delete('~coupon~', 'id = ' . $id);
    }

    public function getcouponinfofromcode ($code, $storeid)
    {
        return $this->db->table('~coupon~')->field('*')->where('partner="' . $code . '" and storeid="' . $storeid . '" and status<>1 and expire > "' . date('Y-m-d H:i:s', TIMESTAMP) . '"')->find();
    }

    /**
     * 获取评论成功派发的优惠卷
     */
    public function getCommentCoupon ($storeid)
    {
        if (!$comment_reward = intval(getConfig()['comment_reward'])) {
            return '';
        }
        if (!$coupon_info = $this->db->table('~coupon~')->field('id,partner')->where('storeid="' . $storeid . '" and type = 1 and status = 0 and expire > "' . date('Y-m-d H:i:s', TIMESTAMP) . '"')->find()) {
            return '';
        }
        if (!$this->db->update('~coupon~', [
                'status' => 2
        ], 'status = 0 and id = ' . $coupon_info['id'])) {
            return '';
        }
        return $coupon_info['partner'];
    }

}