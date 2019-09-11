<?php
/**
 * 订单模型
 */
class OrderModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = Db::getInstance();
    }

    /**
     * 评价评分统计
     */
    public function commentScore ($area, $starttime, $endtime)
    {
        $where = [];
        if ($area > 0) {
            $where[] = 'storeid = ' . $area;
        }
        if ($starttime && $endtime) {
            $where[] = 'createtime between "' . date('Y-m-d 00:00:00', strtotime($_GET['search_starttime'])) . '" and "' . date('Y-m-d 23:59:59', strtotime($_GET['search_endtime'])) . '"';
        }
        $where = implode(' and ', $where);
        $score1 = $this->db->field('date_format(createtime,"%Y-%m-%d") as date,count(*) as count,storeid,score1')->table('~comment~')->where($where)->group('storeid,date,score1')->select();
        $score2 = $this->db->field('date_format(createtime,"%Y-%m-%d") as date,count(*) as count,storeid,score2')->table('~comment~')->where($where)->group('storeid,date,score2')->select();
        $score3 = $this->db->field('date_format(createtime,"%Y-%m-%d") as date,count(*) as count,storeid,score3')->table('~comment~')->where($where)->group('storeid,date,score3')->select();
        $score4 = $this->db->field('date_format(createtime,"%Y-%m-%d") as date,count(*) as count,storeid,score4')->table('~comment~')->where($where)->group('storeid,date,score4')->select();
        $data = [];
        foreach ($score1 as $k => $v) {
            $data[$v['storeid']][$v['date']]['score1'][$v['score1']] += $v['count'];
        }
        foreach ($score2 as $k => $v) {
            $data[$v['storeid']][$v['date']]['score2'][$v['score2']] += $v['count'];
        }
        foreach ($score3 as $k => $v) {
            $data[$v['storeid']][$v['date']]['score3'][$v['score3']] += $v['count'];
        }
        foreach ($score4 as $k => $v) {
            $data[$v['storeid']][$v['date']]['score4'][$v['score4']] += $v['count'];
        }
        $store_list = $this->db->table('~store~')->field('id,name')->where($area > 0 ? 'id = ' . $area : '')->select();
        $store_list = array_column($store_list, 'name', 'id');
        $result = [];
        $merge = [
                1 => '',
                2 => '',
                3 => '',
                4 => '',
                5 => ''
        ];
        foreach ($data as $k => $v) {
            foreach ($v as $kk => $vv) {
                $score1 = $vv['score1'] + $merge;
                ksort($score1);
                $score2 = $vv['score2'] + $merge;
                ksort($score2);
                $score3 = $vv['score3'] + $merge;
                ksort($score3);
                $score4 = $vv['score4'] + $merge;
                ksort($score4);
                $result['score'][] = [
                        'store' => $store_list[$k],
                        'date' => $kk,
                        'score1' => $score1,
                        'score2' => $score2,
                        'score3' => $score3,
                        'score4' => $score4
                ];
                $result['total'][$store_list[$k]]['date'] = date('Y年m月', strtotime($kk));
                $result['total'][$store_list[$k]]['score1'][1] += intval($score1[1]);
                $result['total'][$store_list[$k]]['score1'][2] += intval($score1[2]);
                $result['total'][$store_list[$k]]['score1'][3] += intval($score1[3]);
                $result['total'][$store_list[$k]]['score1'][4] += intval($score1[4]);
                $result['total'][$store_list[$k]]['score1'][5] += intval($score1[5]);
                $result['total'][$store_list[$k]]['score2'][1] += intval($score2[1]);
                $result['total'][$store_list[$k]]['score2'][2] += intval($score2[2]);
                $result['total'][$store_list[$k]]['score2'][3] += intval($score2[3]);
                $result['total'][$store_list[$k]]['score2'][4] += intval($score2[4]);
                $result['total'][$store_list[$k]]['score2'][5] += intval($score2[5]);
                $result['total'][$store_list[$k]]['score3'][1] += intval($score3[1]);
                $result['total'][$store_list[$k]]['score3'][2] += intval($score3[2]);
                $result['total'][$store_list[$k]]['score3'][3] += intval($score3[3]);
                $result['total'][$store_list[$k]]['score3'][4] += intval($score3[4]);
                $result['total'][$store_list[$k]]['score3'][5] += intval($score3[5]);
                $result['total'][$store_list[$k]]['score4'][1] += intval($score4[1]);
                $result['total'][$store_list[$k]]['score4'][2] += intval($score4[2]);
                $result['total'][$store_list[$k]]['score4'][3] += intval($score4[3]);
                $result['total'][$store_list[$k]]['score4'][4] += intval($score4[4]);
                $result['total'][$store_list[$k]]['score4'][5] += intval($score4[5]);
            }
        }
        return $result;
    }

    /**
     * 评论
     */
    public function postComment ($user, $post)
    {
        $post['id'] = intval($post['id']);
        $post['content'] = safe_subject(msubstr($post['content']));
        $post['score1'] = intval($post['score1']);
        $post['score2'] = intval($post['score2']);
        $post['score3'] = intval($post['score3']);
        $post['score4'] = intval($post['score4']);
        if (!$order_info = $this->getOrder($post['id'], 'status = 2 and uid = ' . $user['id'])) {
            return error('参数错误');
        }
        // 派发优惠卷
        $couponModel = new CouponModel();
        $coupon = $couponModel->getCommentCoupon($order_info['storeid']);
        // 增加评论
        if (!$this->db->insert('~comment~', [
                'orderid' => $post['id'],
                'storeid' => $order_info['storeid'],
                'raterid' => $order_info['uid'],
                'rater' => $order_info['buyer'],
                'coupon' => $coupon,
                'message' => $post['content'],
                'score1' => $post['score1'],
                'score2' => $post['score2'],
                'score3' => $post['score3'],
                'score4' => $post['score4'],
                'createtime' => date('Y-m-d H:i:s', TIMESTAMP)
        ])) {
            return error('操作失败');
        }
        return success('评价成功！' . ($coupon ? '获得优惠劵1张。' : ''));
    }

    /**
     * 获取评论详情
     */
    public function getCommentInfo ($id, $orderid = null)
    {
        $where = [];
        if ($id) {
            $where[] = 'id = ' . intval($id);
        }
        if ($orderid) {
            $where[] = 'orderid = ' . intval($orderid);
        }
        $where = implode(' and ', $where);
        if (!$comment_info = $this->db->table('~comment~')->field('*')->where($where)->find()) {
            return [];
        }
        if ($comment_info['coupon']) {
            $comment_info['coupon_info'] = $this->db->table('~coupon~')->field('*')->where('partner = "' . $comment_info['coupon'] . '"')->find();
        }
        $store_info = $this->db->table('~store~')->field('name')->where('id = ' . $comment_info['storeid'])->find();
        $comment_info['storename'] = $store_info['name'];
        $order_info = $this->db->table('~order~')->field('ordertime')->where('id = ' . $comment_info['orderid'])->find();
        $comment_info['ordertime'] = $order_info['ordertime'];
        return $comment_info;
    }

    /**
     * 获取评论列表
     */
    public function getCommentList ($field, $where, $limit = null)
    {
        if (!$rs = $this->db->table('~comment~')->field($field)->where($where)->limit($limit)->select()) {
            return [];
        }
        return $rs;
    }

    /**
     * 评论数量
     */
    public function getCommentCount ($where)
    {
        return $this->db->table('~comment~')->field('count(*)')->where($where)->find(null, true);
    }

    /**
     * 统计今日预约
     */
    public function getWeChatText ($openid)
    {
        $logininfo = $this->db->field('uid')->table('~loginbinding~')->where('type = "wx" and authcode = "' . $openid . '"')->find();
        if (!$logininfo) {
            return error('未知用戶');
        }
        $userinfo = $this->db->field('area')->table('~user~')->where('id = ' . $logininfo['uid'])->find();
        if ($userinfo['area'] < 0) {
            return error('没有权限查看');
        }
        $storelist = $this->db->field('id,name')->table('~store~')->select();
        $storelist = array_column($storelist, 'name', 'id');
        $where = [
                'status = 1',
                'ordertime between "' . (date('Y-m-d', TIMESTAMP) . ' 00:00:00') . '" and "' . (date('Y-m-d', TIMESTAMP) . ' 23:59:59') . '"'
        ];
        if ($userinfo['area'] > 0) {
            $where[] = 'storeid = ' . $userinfo['area'];
        }
        $where = implode(' and ', $where);
        $orderlist = $this->db->field('storeid,buyer,item,ordertime')->table('~order~')->where($where)->limit(100)->select();
        $contentStr = "【" . ($userinfo['area'] > 0 ? $storelist[$userinfo['area']] : '总店') . "今日统计】\r\n";
        if ($orderlist) {
            foreach ($orderlist as $k => $v) {
                $contentStr .= $v['ordertime'] . '　' . $storelist[$v['storeid']] . '　' . $v['buyer'] . '　' . $v['item'] . "\r\n";
            }
        } else {
            $contentStr .= '暂无预约';
        }
        return $contentStr;
    }

    /**
     * 获取个人底片列表
     */
    public function getPhotoList ($uid)
    {
        $rs = $this->db->table('~photo~')->field('*')->where('uid = ' . $uid)->order('id desc')->select();
        if ($rs) {
            foreach ($rs as $k => $v) {
                $rs[$k]['url'] = httpurl($v['url']);
                $rs[$k]['thumb'] = httpurl($v['thumb']);
                $rs[$k]['size'] = sizecount($v['size']);
            }
        }
        return $rs;
    }

    // 获取订单信息
    public function getOrder ($id = null, $where = '', $limit = '', $field = '*', $order = 'id desc')
    {
        if (isset($id)) {
            $id = intval($id);
            $rs = $this->db->table('~order~')->field('*')->where('id=' . $id . ($where ? ' and ' . $where : ''))->find();
        } else {
            $rs = $this->db->table('~order~')->field($field)->where($where)->order($order)->limit($limit)->select();
        }
        return $rs;
    }

    public function getOrderlist ($sql)
    {
        $rs = $this->db->select($sql);
        return $rs;
    }

    public function getOrderinfo ($sql)
    {
        $rs = $this->db->find($sql);
        return $rs;
    }

    public function getsmscode ($telephone, $code)
    {
        $rs = $this->db->table('~smscode~')->field('*')->where('tel = "' . $telephone . '" and code="' . $code . '"')->find();
        return $rs;
    }

    public function insertOrder ($orderarray)
    {
        $this->db->insert("pro_order", $orderarray);

    }

    public function getlastid ()
    {
        $rs = $this->db->getlastid();
        return $rs;
    }

    public function updatecoupon ($code, $storeid)
    {
        $this->db->update("pro_coupon", array(
                "status" => 1
        ), " partner='{$code}' and storeid=" . $storeid);
    }

    // 关闭订单
    public function closeOrder ($uid, $id)
    {
        if (!$cardinfo = $this->db->table('~cards~')->field('coupon')->where('status = 0 and orderid = ' . $id)->find()) {
            return error('该订单不能关闭');
        }
        if (!$this->db->query('delete a,b from ~order~ a inner join ~cards~ b on b.orderid = a.id where a.status = 0 and a.uid = ' . $uid . ' and a.id = ' . $id)) {
            return error('关闭订单失败');
        }
        if ($cardinfo['coupon']) {
            $this->db->update('~coupon~', array(
                    'status' => 2
            ), 'partner = "' . $cardinfo['coupon'] . '"');
        }
        return success('操作成功');
    }

    public function getOrderlistwhere ($where, $limit = "")
    {
        $sql = "SELECT pro_order.*, 
                   pro_cards.id AS cardid, 
                   ordercode, 
                       pro_store.`name`,pro_store.address
            FROM   pro_order 
                   LEFT JOIN pro_cards 
                          ON pro_order.id = pro_cards.orderid 
                   LEFT JOIN pro_store 
                          ON pro_order.storeid = pro_store.id ";
        if ($where) {
            $sql = $sql . "where " . $where;
        }
        $sql = $sql . " order by pro_order.ordertime desc";
        if ($limit != "") {
            $sql = $sql . $limit;
        }
        $rs = $this->db->select($sql);
        return $rs;
    }

    public function getOrderinfowhere ($where, $limit = "")
    {
        $sql = "SELECT pro_order.*, 
                   pro_cards.id AS cardid, 
                   ordercode, 
                       pro_store.`name`,pro_store.address
            FROM   pro_order 
                   LEFT JOIN pro_cards 
                          ON pro_order.id = pro_cards.orderid 
                   LEFT JOIN pro_store 
                          ON pro_order.storeid = pro_store.id ";
        if ($where) {
            $sql = $sql . "where " . $where;
        }

        if ($limit != "") {
            $sql = $sql . $limit;
        }
        $rs = $this->db->find($sql);
        return $rs;
    }

    public function getOrdersum ($storeid)
    {
        $sql = 'SELECT sum(downpay) sum, count(*) as count FROM pro_order where status > 0';
        if ($storeid) {
            $sql .= ' and storeid = ' . intval($storeid);
        }
        $rs = $this->db->find($sql);
        return $rs;
    }

    public function getOrderIn ($where)
    {
        $sql = "SELECT
	        createtime,
	        sum(downpay) count, count(*) as num
        FROM
	        (
		        SELECT
			        LEFT (createtime, 10) createtime,
			        downpay
		        FROM
			        `pro_order`
		        WHERE
			        1 = 1 and  $where
	        ) t
        GROUP BY
	        createtime";
        $rs = $this->db->select($sql);
        return $rs;
    }

    public function getOrdersumCount ($where)
    {
        $sql = "SELECT
			        count(*)count
		        FROM
			        `pro_order`
		        WHERE
			        1 = 1 and  $where ";
        $rs = $this->db->find($sql);
        return $rs;
    }

    public function insertphoto ($photoarray)
    {
        return $this->db->insert("~photo~", $photoarray);
    }

    public function getPhoto ($orderid)
    {
        $rs = $this->db->table('~photo~')->field('*')->where('orderid = ' . $orderid)->order('id desc')->select();
        return $rs;
    }

    public function getPhotosendmessage ($uid)
    {
        $rs = $this->db->table('~photo~')->field('*')->where(" uid = '{$uid}'  and  createtime>'" . date('Y-m-d H:i:s', time() - 3600) . "'")->order('id desc')->select();
        return $rs;
    }

    public function deletephoto ($id)
    {
        return $this->db->delete("~photo~", " id=" . $id);
    }

    public function updateorder ($orderarr, $id)
    {
        return $this->db->update('pro_order', $orderarr, ' id = ' . $id);
    }

}
