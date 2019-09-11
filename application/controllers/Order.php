<?php
class Order_Action extends ActionPDO {

    public function __init ()
    {
        $_notAuth = array(
                'help',
                'address'
        );
        if (!in_array($this->_action, $_notAuth)) {
            // 登录验证
            if (empty($this->_G['user'])) {
                $this->error('用户校验失败');
            }
        }
    }

    /**
     * 取消订单并退款
     */
    public function refundOrder ()
    {
        $id = $_GET['id'];
        $cardsmodel = new CardsModel();
        $ret = $cardsmodel->createRefund($this->_G['user']['id'], intval(getgpc('id')));
        echo json_unicode_encode($ret);
        return null;
    }

    /**
     * 定位城市
     */
    public function getLocation ()
    {
        $lon = round(floatval($_GET['lon']), 6);
        $lat = round(floatval($_GET['lat']), 6);
        $scan = 'http://restapi.amap.com/v3/geocode/regeo?key=93970d0444d2abd81cf00c2c59ae096e&location=' . $lon . ',' . $lat;
        try {
            $result = https_request($scan);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        if ($result['status'] != 1) {
            $this->error($result['info']);
        }
        $citymodel = new CityModel();
        $cityname = $result['regeocode']['addressComponent']['district'];
        if (!$citycode = $citymodel->getCityCode($cityname)){
            $cityname = $result['regeocode']['addressComponent']['city'];
            $citycode = $citymodel->getCityCode($cityname);
        }
        if (!$citycode) {
            $this->error('城市' . $cityname . '未开通');
        }
        $this->success($citycode);
        return null;
    }

    // 预约接口
    public function createorder ()
    {
        $step = $_GET["step"];
        $citycode = intval($_GET["citycode"]);
        $type = $_GET["type"];
        $categoryid = intval($_GET["categoryid"]);
        $citymodel = new CityModel();
        $categorymodel = new CategoryModel();
        $storemodel = new StoreModel();
        $this->db = & Db::getInstance();
        switch ($step) {
            // 选择城市
            case "city":
                $citylist = $citymodel->getcitylist(" status=1");
                $citylist1 = $citymodel->getcitylist(" status=0");

                $result = array(
                        'citylist' => $citylist,
                        'citylist1' => $citylist1,
                        'step' => $step
                );
                break;
            // 选择产品
            case "products":
                $citylist = $citymodel->getcitylist(" status=1");
                $citylist1 = $citymodel->getcitylist(" status=0");
                $cityname = $citymodel->getCity($citycode, 'status = 1');
                if (!$cityname) {
                    $this->error('城市未开通', '/?c=order&a=createorder&step=city', 0);
                }
                $productlistA = $categorymodel->getCategory(null, 'project_id = 1 and type=0');
                $productlist = $categorymodel->getCategory(null, 'project_id = 1 and type=1');
                $result = array(
                        'productlist' => $productlist,
                        'productlistA' => $productlistA,

                        'step' => $step,
                        'citylist' => $citylist,
                        'citylist1' => $citylist1,
                        'cityname' => $cityname,
                        "citycode" => $citycode
                );
                break;
            case 'openproduct':
                $category_info = $categorymodel->getCategory($categoryid);
                if (!$category_info) {
                    $this->error('参数错误', '?c=order&a=createorder');
                }
                $store_list = $categorymodel->getStoreByCategoryId($categoryid);
                $result = array(
                        'step' => 'openproduct',
                        'category' => $category_info,
                        'store' => $store_list
                );
                break;
            // 展现产品
            case "showproducts":
                $atLeastOneStoreOpen = false;
                $cityinfo = $citymodel->getCity($citycode);
                $store = $this->getstorelist($citycode, '', $categoryid);
                foreach ($store as $k => $v) {
                    if ($v['ordercount'] > 0) {
                        $atLeastOneStoreOpen = true;
                        $storeinfo = $categorymodel->getCategoryNew($v['id'], $categoryid);
                        $store[$k]['price'] = $storeinfo['price'];
                    }
                }
                $category = $categorymodel->getCategorywhereinfo('id = ' . $categoryid);
                $result = array(
                        'category' => $category,
                        'categoryid' => $categoryid,
                        'step' => $step,
                        'store' => $store,
                        'type' => $type,
                        "citycode" => $citycode,
                        "cityinfo" => $cityinfo,
                        'atLeastOneStoreOpen' => $atLeastOneStoreOpen
                );
                break;
            // 选择排班日期
            case "schedules":
                $cityinfo = $citymodel->getCity($citycode);
                $store = $this->getstorelist($citycode, '', $categoryid);
                $scheduleslist = $this->scheduleslist($citycode);
                $category = $categorymodel->getCategory($categoryid);
                $result = array(
                        'category' => $category,
                        'step' => $step,
                        'store' => $store,
                        'type' => $type,
                        'cityinfo' => $cityinfo,
                        'scheduleslist' => $scheduleslist
                );
                break;
            // 增加套餐
            case "package":
                $time = $_GET["time"];
                $week = $_GET["week"];
                $poolid = $_GET['poolid'];
                $datelist = $_GET["date"];
                $date = explode("-", $datelist);
                $date = $date[1] . '月' . $date[2] . '日';
                $storeId = $_GET["storeId"];
                $cityinfo = $citymodel->getCity($citycode);
                $store = $storemodel->getStore($storeId);
                $typelist = $this->db->find("select package from pro_story_category where categroy_id='{$categoryid}' and store_id='{$storeId}'");
                if ($typelist['package'] != null) {
                    $categorylist = $categorymodel->getCategorylist("select * from pro_category where id in ({$typelist['package']})");
                }
                if (!$categorylist) {
                    header("Location:?c=order&a=createorder&step=payment&poolid={$poolid}&type=1&categoryid={$categoryid}&citycode={$citycode}&week={$week}&storeId={$storeId}&date={$datelist}&time={$time}&combos=");
                    exit();
                }
                $category = $categorymodel->getCategory($categoryid);
                $result = array(
                        'category' => $category,
                        'categorylist' => $categorylist,
                        'step' => $step,
                        'store' => $store,
                        'type' => $type,
                        'cityinfo' => $cityinfo,
                        "datelist" => $datelist,
                        'time' => $time,
                        'week' => $week,
                        'date' => $date,
                        "categoryid" => $categoryid,
                        'poolid' => $poolid
                );
                break;
            // 微信支付
            case "payment":
                $poolid = $_GET['poolid'];
                $combos = $_GET["combos"];
                $combos = explode(",", $combos);
                $typelist = '';
                foreach ($combos as $k => $v) {
                    $v = explode("|", $v);
                    $typelist = $typelist . $v[0] . ',';
                }
                $typelist = trim($typelist, ",");
                $categoryid = $_GET["categoryid"];
                $week = $_GET["week"];
                $time = $_GET["time"];
                $datelist = $_GET["date"];
                $date = explode("-", $datelist);
                $date = $date[0] . '年' . $date[1] . '月' . $date[2] . '日';
                $storeId = $_GET["storeId"];
                $cityinfo = $citymodel->getCity($citycode);
                $config = new ConfigModel();
                $configinfo = $config->getconfiginfo(" name='downpay_percent'");
                $store = $storemodel->getStore($storeId);
                $category = $categorymodel->getCategoryNew($storeId, $categoryid);
                $delay = $category['delay'];
                $categoryname = $category['name'] . ":1";
                if ($typelist) {
                    $categorylist = $categorymodel->getCategorylist("select * from pro_category where id in ({$typelist})");

                    foreach ($categorylist as $v) {
                        $categoryname = $categoryname . ';' . $v['name'] . ":1";
                        $delay = $delay + $v['delay'];

                    }
                }

                $categoryname = trim($categoryname, ';');
                if ($this->_jssdk) {
                    $jssdk = $this->_jssdk->GetSignPackage();
                    if ($jssdk['errorcode'] !== 0) {
                        $this->error($jssdk['data']);
                    } else {
                        $jssdk = $jssdk['data'];
                    }
                }
                $result = array(
                        'refund_rule' => json_decode(getConfig()['refund_rule'], true),
                        'jssdk' => $jssdk,
                        'category' => $category,
                        'categorylist' => $categorylist,
                        'step' => $step,
                        'store' => $store,
                        'type' => $type,
                        'cityinfo' => $cityinfo,
                        'time' => $time,
                        'user' => $this->_G['user'],
                        'week' => $week,
                        'date' => $date,
                        'datelist' => $datelist,
                        'poolid' => $poolid,
                        'categoryname' => $categoryname,
                        'delay' => $delay,
                        'configinfo' => $configinfo
                );
                break;
            default:
                $userinfo = $this->_G['user'];
                if ($this->_jssdk) {
                    $jssdk = $this->_jssdk->GetSignPackage();
                    if ($jssdk['errorcode'] !== 0) {
                        $jssdk = null;
                    } else {
                        $jssdk = $jssdk['data'];
                    }
                }
                $result = array(
                        'refund_rule' => json_decode(getConfig()['refund_rule'], true),
                        'jssdk' => $jssdk,
                        'step' => $step,
                        'userinfo' => $userinfo
                );
                break;
        }
        return $result;
    }
    // 取门店的可用排号
    public function getstorelist ($citycode, $date = "", $categoryid)
    {
        global $atLeastOneStoreOpen;
        $storemodel = new StoreModel();
        $poolmodel = new PoolModel();
        $ordermodel = new OrderModel();
        $storelist = $storemodel->getStorelist("select pro_store.* from pro_store left join pro_story_category on pro_store.id=pro_story_category.store_id where citycode='{$citycode}' and pro_story_category.categroy_id='{$categoryid}' order by pro_store.sort");
        foreach ($storelist as $k => $v) {
            if ($date != "") {
                $poolstr = '';
                $poolinfowhere = " and today='{$date}'";
                $poollist = $poolmodel->getPoollist("select id from pro_pool where today='{$date}'and starttime>='" . date("Y-m-d H:i:s", time()) . "' and  storeid ='{$v['id']}'");
                foreach ($poollist as $vv) {
                    $poolstr = $poolstr . ',' . $vv['id'];
                }
                $poolstr = trim($poolstr, ",");
                if (strlen($poolstr) > 0) {
                    $orderinfowhere = " and  poolid in({$poolstr})";
                }
            }
            $poolinfo = $poolmodel->getPoolinfo("select if(SUM(maxcount)<SUM(ordercount),0,SUM(maxcount)-SUM(ordercount))   ordercount from  pro_pool    where storeid='{$v['id']}' and starttime>='" . date("Y-m-d H:i:s", time()) . "'  $poolinfowhere  ");
            // 取订单20分之前的数据
            $orderinfo = $ordermodel->getOrderinfo(" select count(*)ordercount from pro_order where storeid='{$v['id']}' and   createtime >'" . date("Y-m-d H:i:s", time() - 1200) . "' $orderinfowhere and status=0 group by storeid  ");
            $poolinfo['ordercount'] = $poolinfo['ordercount'] - $orderinfo['ordercount'];
            if (!$poolinfo || $poolinfo['ordercount'] == 0) {
                $storelist[$k]['ordercount'] = 0;
            } else {
                $storelist[$k]['ordercount'] = $poolinfo['ordercount'];
                $atLeastOneStoreOpen = "yes";
            }
        }
        return $storelist;
    }

    public function scheduleslist ($citycode)
    {
        $poolmodel = new PoolModel();
        $ordermodel = new OrderModel();

        $schedulessql = "select DAYNAME(today)name,today,if(SUM(maxcount)<SUM(ordercount),0,SUM(maxcount)-SUM(ordercount)) ordercount from pro_pool   where storeid in (SELECT id from pro_store where citycode='{$citycode}') and starttime>='" . date("Y-m-d H:i:s", time()) . "'  group by today  LIMIT 0,15";
        $scheduleslist = $poolmodel->getPoollist($schedulessql);
        foreach ($scheduleslist as $k => $v) {
            switch ($v['name']) {
                case "Wednesday":
                    $scheduleslist[$k]['name'] = "周三";
                    break;
                case "Thursday":
                    $scheduleslist[$k]['name'] = "周四";
                    break;
                case "Friday":
                    $scheduleslist[$k]['name'] = "周五";
                    break;
                case "Saturday":
                    $scheduleslist[$k]['name'] = "周六";
                    break;
                case "Sunday":
                    $scheduleslist[$k]['name'] = "周日";
                    break;
                case "Monday":
                    $scheduleslist[$k]['name'] = "周一";
                    break;
                default:
                    $scheduleslist[$k]['name'] = "周二";
                    break;
            }
            $today = explode("-", $v['today']);
            $scheduleslist[$k]['month'] = $today[1];
            if ($scheduleslist[$k]['month'] < 10) {
                $scheduleslist[$k]['month'] = trim($scheduleslist[$k]['month'], "0");
            }
            $poolstr = "";
            $sql = "select id from pro_pool where today='{$v['today']}' and  storeid in (SELECT id from pro_store where citycode='{$citycode}')";
            $poollist = $poolmodel->getPoollist($sql);
            foreach ($poollist as $v) {
                $poolstr = $poolstr . ',' . $v['id'];
            }
            $poolstr = trim($poolstr, ",");
            $ordersql = "select count(*)ordercount from pro_order where  storeid in (SELECT id from pro_store where citycode='{$citycode}') and  poolid in({$poolstr}) and   createtime >'" . date("Y-m-d H:i:s", time() - 1200) . "'   and status=0 group by storeid  ";
            $orderinfo = $ordermodel->getOrderinfo($ordersql);
            if ($orderinfo) {
                $scheduleslist[$k]['ordercount'] = $scheduleslist[$k]['ordercount'] - $orderinfo['ordercount'];
            }
            $scheduleslist[$k]['date'] = $today[2];
        }
        return $scheduleslist;
    }
    // 获取门店信息
    public function getstoreinfo ()
    {
        $storeid = $_GET["storeid"];
        $date = $_GET["date"];
        $storemodel = new StoreModel();
        $poolmodel = new PoolModel();
        $ordermodel = new OrderModel();
        $cudate = date("Y-m-d H:i:s", time());
        $store = $storemodel->getStore($storeid);
        $sql = "   SELECT * 
            FROM   (SELECT today, 
                           id, 
                           storeid, 
                           Substr(starttime, 12, 5)time, 
                          if(maxcount<ordercount,0,maxcount-ordercount) as count 
                    FROM   pro_pool 
                    WHERE  storeid ='{$storeid}'
                           AND today = '{$date}'
                           AND starttime > '{$cudate}' 
                UNION ALL
                    SELECT today, 
                           id, 
                           storeid, 
                           Substr(starttime, 12, 5)time, 
                           0                       count 
                    FROM   pro_pool 
                    WHERE  storeid ='{$storeid}'
                           AND today = '{$date}'
                           AND starttime < '{$cudate}' )t 
            ORDER  BY time ASC ";
        $poollist = $poolmodel->getPoollist($sql);
        foreach ($poollist as $k => $v) {
            // 排除预约但是没有缴费人员
            $orderinfo = $ordermodel->getOrderinfo("select  count(*) count from  pro_order  where storeid='{$storeid}'  and poolid='{$v['id']}' and status=0  and  createtime >'" . date("Y-m-d H:i:s", time() - 1200) . "' ");
            if ($orderinfo) {
                $poollist[$k]['count'] = $poollist[$k]['count'] - $orderinfo['count'];
            }
        }
        if ($store) {
            $success = 1;
        } else {
            $success = 0;
        }
        echo json_encode(array(
                "success" => $success,
                "msg" => $store,
                "poollist" => $poollist
        ));
        exit();
    }

    /**
     * 创建交易单
     */
    public function createcard ()
    {
        $categoryModel = new CategoryModel();
        if (!$relationCategory = $categoryModel->getRelationCategory(intval(getgpc('store_id')), intval(getgpc('category_id')))) {
            $this->error('该产品无效');
        }
        if (!$categoryInfo = $categoryModel->getCategory($relationCategory['category_id'], null, null, 'id,name,delay')) {
            $this->error('该产品不存在');
        }
        if (!$storeInfo = (new StoreModel())->getStore($relationCategory['store_id'], 'status = 1')) {
            $this->error('门店不存在');
        }
        if (!$cityInfo = (new CityModel())->getCity($storeInfo['citycode'])) {
            $this->error('城市未开通');
        }

        $_POST['citycode']      = $storeInfo['citycode'];
        $_POST['store_id']      = $storeInfo['id'];
        $_POST['category_name'] = $categoryInfo['name'];
        $_POST['delay']         = $categoryInfo['delay'];
        $_POST['price']         = $relationCategory['price'];
        $result = (new CardsModel())->createCard($this->_G['user'], $_POST);
        echo json_encode($result);
        return null;
    }

    /**
     * 支付确认
     */
    public function payquery ()
    {
        $cardsmodel = new CardsModel();
        $ret = $cardsmodel->payQuery($this->_G['user']['id'], getgpc('cardid'));
        echo json_unicode_encode($ret);
        return null;
    }

    /**
     * 检查订单超时
     */
    public function checkorderpay ()
    {
        $cardsmodel = new CardsModel();
        $ret = $cardsmodel->payQuery($this->_G['user']['id'], getgpc('cardid'));
        if ($ret['errorcode'] === 0) {
            $this->error($ret['data']);
        }
        if (!$cardsmodel->checkOrderPay($this->_G['user']['id'], getgpc('orderid'))) {
            $this->error('订单已关闭');
        }
        $this->success(getgpc('cardid'));
        return null;
    }

    public function getroundStr ($len)
    {
        $chars_array = array(
                "0",
                "1",
                "2",
                "3",
                "4",
                "5",
                "6",
                "7",
                "8",
                "9"
        );
        $charsLen = count($chars_array) - 1;

        $outputstr = "";
        for ($i = 0; $i < $len; $i++) {
            $outputstr .= $chars_array[mt_rand(0, $charsLen)];
        }
        return $outputstr;
    }
    // 根据城市编码和日期来生成可选择号段
    public function calscheduleforcityondate ()
    {
        $citycode = $_GET["citycode"];
        $categoryid = $_GET['categoryid'];
        $date = $_GET["date"];
        $this->db = & Db::getInstance();
        $atLeastOneStoreOpen = "no";
        $storelist = $this->getstorelist($citycode, $date, $categoryid);
        foreach ($storelist as $v) {
            if ($v['ordercount'] > 0) {
                $atLeastOneStoreOpen = "yes";
            }
        }
        if ($storelist) {
            $success = 1;
        } else {
            $success = 0;
        }
        echo json_encode(array(
                "success" => $success,
                "storelist" => $storelist,
                "atLeastOneStoreOpen" => $atLeastOneStoreOpen
        ));
        exit();
    }
    // 我的订单
    public function myorder ()
    {
        $ordermodel = new OrderModel();
        $userinfo = $this->_G['user'];
        $step = getgpc('step');

        switch ($step) {
            case 'comment':

                die(json_unicode_encode($ordermodel->postComment($userinfo, $_POST)));
                break;
            case 'detail':

                $id = intval(getgpc('id'));
                if (!$order = $ordermodel->getOrder($id, 'uid = ' . $userinfo['id'])) {
                    $this->error('该订单不存在或已删除');
                }
                // 获取交易单
                if (!$cardInfo = (new CardsModel())->getCardInfo('orderid = ' . $id, 'id,ordercode,coupon')) {
                    $this->error('该订单无效');
                }
                // 获取门店
                $storeInfo = ((new StoreModel())->getStore($order['storeid']));
                // 获取评价
                if ($order['status'] == 2) {
                    $comment = $ordermodel->getCommentInfo(null, $id);
                }
                $order['ordertime'] = showWeekDate($order['ordertime']);
                $order['item'] = str_replace(':1', '',  $order['item']);
                $order['delay'] = round($order['delay'] / 3600,1);
                if ($this->_jssdk) {
                    $jssdk = $this->_jssdk->GetSignPackage();
                    if ($jssdk['errorcode'] !== 0) {
                        $jssdk = null;
                    } else {
                        $jssdk = $jssdk['data'];
                    }
                }
                // 评价是否获得优惠劵
                $comment_reward = intval(getConfig('comment_reward'));
                $result = array(
                        'jssdk' => $jssdk,
                        'userinfo' => $userinfo,
                        'step' => $step,
                        'order' => $order,
                        'cardInfo' => $cardInfo,
                        'storeInfo' => $storeInfo,
                        'refund_money' => getRefundMoney($order['ordertime']),
                        'refund_rule' => json_decode(getConfig('refund_rule'), true),
                        'id' => $id,
                        'comment' => $comment,
                        'comment_reward' => $comment_reward
                );
                $this->render('orderDetail.html', $result);
                break;
            default:

                $order = $ordermodel->getOrder(null, 'uid = ' . $userinfo['id'], null, 'id,storeid,buyer,item,ordertime,createtime,status', 'id desc');
                if ($order) {
                    $stores = (new StoreModel())->getStore(null, 'id in (' . implode(',', array_column($order, 'storeid')) . ')', null, 'id,name');
                    $stores = array_column($stores, 'name', 'id');
                    $comment = $ordermodel->getCommentList('orderid', 'raterid = ' . $userinfo['id']);
                    $comment = array_column($comment, 'orderid', 'orderid');
                    foreach ($order as $k => $v) {
                        $order[$k]['store_name'] = $stores[$v['storeid']];
                        $order[$k]['comment_mark'] = ($v['status'] == 2 && !isset($comment[$v['id']])) ? 1 : 0;
                        $order[$k]['ordertime'] = showWeekDate($v['ordertime']);
                        $order[$k]['item'] = str_replace(':1', '',  $v['item']);
                    }
                }

                // 评价是否获得优惠劵
                $comment_reward = intval(getConfig('comment_reward'));
                $result = array(
                        'userinfo' => $userinfo,
                        'step' => $step,
                        'order' => $order,
                        'comment_reward' => $comment_reward
                );
                break;
        }
        return $result;
    }

    // 我的底片信息
    public function negative ()
    {

        $ordermodel = new OrderModel();
        $piclist = $ordermodel->getPhotoList($this->_G['user']['id']);
        return array(
                'piclist' => $piclist
        );
    }

    // 关闭订单
    public function closeOrder ()
    {
        $order = new OrderModel();
        $ret = $order->closeOrder($this->_G['user']['id'], intval(getgpc('id')));
        echo json_unicode_encode($ret);
        return null;
    }

    // 获取门店地址
    public function address ()
    {
        $step = $_GET["step"];
        $this->db = & Db::getInstance();
        $citycode = $_GET["citycode"];
        $type = $_GET["type"];
        $categoryid = $_GET["categoryid"];
        if ($citycode == "") {
            $citycode = "520300";
        }
        $citymodel = new CityModel();
        $categorymodel = new CategoryModel();
        $storemodel = new StoreModel();
        $cityname = $citymodel->getCity($citycode);
        $category = $categorymodel->getCategory($categoryid);
        $user = $this->_G['user'];
        $citylist = $citymodel->getcitylist(" status=1");
        $citylist1 = $citymodel->getcitylist(" status=0");
        $productlist = $categorymodel->getCategory(null, "type=1");
        $store = $storemodel->getStore(null, 'citycode=' . $citycode);
        $productlist1 = $categorymodel->getCategory(null, 'type=0');
        return array(
                'userinfo' => $user,
                'step' => $step,
                'citylist' => $citylist,
                'citylist1' => $citylist1,
                'cityname' => $cityname,
                'category' => $category,
                'productlist' => $productlist,
                'productlist1' => $productlist1,
                'citycode' => $citycode,
                'store' => $store,
                "type" => $type
        );
    }
    // 帮助信息
    public function help ()
    {
        return array();
    }

    public function personal ()
    {
        $user = $this->_G['user'];
        return array(
                "userinfo" => $user
        );
    }
    // 发送验证码
    public function sendcode ()
    {
        $phone = $_GET['tel'];
        $user = new UserModel();
        $msg = $user->sendSmsCode($phone);
        if ($msg['errorcode'] == 0) {
            $result = json_encode(array(
                    "success" => 1,
                    "msg" => $msg['data']
            ));
        } else {
            $result = json_encode(array(
                    "success" => 0,
                    "msg" => $msg['data']
            ));
        }
        echo $result;
        exit();
    }
    // 验证验证码是否正确
    public function validatecode ()
    {
        $telephone = $_GET['tel'];
        $code = $_GET['code'];
        $user = new UserModel();
        $ret = $user->checkSmsCode($telephone, $code, false);
        if ($ret) {
            echo json_unicode_encode(success(true));
        } else {
            echo json_unicode_encode(error(true));
        }
        return null;
    }

    public function coupon ()
    {
        $code = $_GET['code'];
        $storeid = $_GET['storeid'];
        $coupon = new CouponModel();
        $result_sms = $coupon->getcouponinfofromcode($code, $storeid);
        if ($result_sms) {
            $result = json_encode(array(
                    "success" => 1,
                    "msg" => $result_sms
            ));
        } else {
            $result = json_encode(array(
                    "success" => 0,
                    "msg" => $result_sms
            ));
        }
        echo $result;
        exit();
    }
    // 保存信息
    public function saveuserinfo ()
    {
        $user = new UserModel();
        $ret = $user->bindAccount($this->_G['user'], $_POST);
        echo json_unicode_encode($ret);
        return null;
    }

}
