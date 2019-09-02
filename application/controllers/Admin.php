<?php
class Admin_Action extends ActionPDO {

    function __init ()
    {
        $_notAuth = array(
                'login', 
                'checkcode', 
                'loginout'
        );
        if (!in_array($this->_action, $_notAuth)) {
            // 登录验证
            if (empty($this->_G['user'])) {
                $this->error('用户校验失败', '?c=admin&a=login&referer=' . urlencode($_SERVER['REQUEST_URI']));
            }
            // 校验权限
            if (!Auth::check($this->_action, $this->_G['user']['id'])) {
                $this->error('权限校验失败', '?c=admin&a=login&referer=' . urlencode($_SERVER['REQUEST_URI']));
            }
        }
    }

    function index ()
    {
        return true;
    }

    function login ()
    {
        if (submitcheck(null, false)) {
            $this->checkcode(getgpc('code')) || $this->error('验证码错误');
            $user = new UserModel();
            $ret = $user->login(getgpc('name'), getgpc('password'));
            $ret['errorcode'] !== 0 && $this->error($ret['data']);
            $this->success('登录成功');
        }
        return true;
    }

    function loginout ()
    {
        $user = new UserModel();
        $ret = $user->loginout($this->_G['user']['id'], $this->_G['user']['clienttype']);
        echo json_unicode_encode($ret);
    }

    function checkcode ($code = null)
    {
        @session_start();
        if (isset($code)) {
            return $_SESSION['code'] === strtolower($code);
        }
        $checkcode = new Checkcode();
        $checkcode->doimage();
        $_SESSION['code'] = $checkcode->get_code();
    }

    function render ($tplName, $params = array(), $style = null)
    {
        $params['user_info'] = $this->_G['user'];
        $usermodel = new UserModel();
        $params['user_access'] = $usermodel->getUserAccessDesc($this->_G['user']['id'], $this->_G['user']['area']);
        parent::render('admin/' . $tplName, $params, 'default');
    }
    // 订单页面
    function order ()
    {
        $order = new OrderModel();
        $status = getgpc('status');
        $action = getgpc('action');
        $id = intval(getgpc('id'));
        $storeid = intval(getgpc('storeid'));
        if ($status == null) {
            $status = "全部";
        }
        $search_starttime = getgpc("search_starttime");
        $search_endtime = getgpc("search_endtime");
        if ($search_starttime == null) {
            $search_starttime = date("Y-m-d", time());
        }
        if ($search_endtime == null) {
            $search_endtime = date("Y-m-d", time());
        }
        if (strlen($search_starttime) <= 10 && $search_starttime) {
            $search_starttime = $search_starttime . " 00:00:00";
        }
        
        if (strlen($search_endtime) <= 10 && $search_endtime) {
            $search_endtime = $search_endtime . " 23:59:00";
        }
        if ($action == "from" && $id) {
            $storeinfo = $order->getOrderinfowhere(" pro_order.id=" . $id);
            $Photolist = $order->getPhoto($id);
            $weekarray = array(
                    "日", 
                    "一", 
                    "二", 
                    "三", 
                    "四", 
                    "五", 
                    "六"
            );
            $storeinfo['week'] = "周" . $weekarray[date("w", strtotime($storeinfo['ordertime']))];
            $ordertime = explode(" ", $storeinfo['ordertime']);
            $storeinfo['time'] = substr($ordertime[1], 0, 5);
            $storeinfo['itemlist'] = explode(";", $storeinfo['item']);
            return array(
                    "storeinfo" => $storeinfo, 
                    "action" => $action, 
                    'Photolist' => $Photolist
            );
        } else if ($action == "editbuyer" && $id) {
            $buyer = getgpc("buyer");
            $order->updateorder(array(
                    "buyer" => $buyer
            ), $id);
            $this->success('操作成功', '?' . burl('action='), 0);
        
        } else if ($action == "editinfo" && $id) {
            $storeinfo = $order->getOrderinfowhere(" pro_order.id=" . $id);
            $Photolist = $order->getPhoto($id);
            $weekarray = array(
                    "日", 
                    "一", 
                    "二", 
                    "三", 
                    "四", 
                    "五", 
                    "六"
            );
            $storeinfo['week'] = "周" . $weekarray[date("w", strtotime($storeinfo['ordertime']))];
            $ordertime = explode(" ", $storeinfo['ordertime']);
            $storeinfo['time'] = substr($ordertime[1], 0, 5);
            $storeinfo['itemlist'] = explode(";", $storeinfo['item']);
            return array(
                    "storeinfo" => $storeinfo, 
                    "action" => $action, 
                    'Photolist' => $Photolist
            );
        } 

        else if ($action == "savefile") {
            // 图片上传
            $orderid = getgpc("orderid");
            $storeinfo = $order->getOrderinfowhere(" pro_order.id=" . $orderid);
            $file = uploadfile($_FILES['upload'], '', 300);
            $str = strstr($file, '-');
            if ($str) {
                $output = array(
                        "success" => "0", 
                        "msg" => $str
                );
                echo json_encode($output);
                exit();
            }
            $issend = $order->getPhotosendmessage($storeinfo['uid']);
            if (!$issend) {
                $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
                $post_data = "account=cf_17pcy&password=6ba91fe2c9daf43cfa8ee0b7d100d846&mobile=" . $storeinfo['buyerphone'] . "&content=" . rawurlencode("您在" . $storeinfo['name'] . "拍摄的照片已完成了后期处理，在线下载请访问:17平微信公众号 ->我的底片");
                $gets = $this->xml_to_array($this->Post($post_data, $target));
                // 发送成功
                if ($gets['SubmitResult']['code'] != 2) {
                    $output = array(
                            "success" => "0", 
                            "msg" => $gets['SubmitResult']['msg']
                    );
                    echo json_encode($output);
                    exit();
                }
            }
            $photoarray = array(
                    "uid" => $storeinfo['uid'], 
                    "orderid" => $storeinfo['id'], 
                    'name' => $file['name'], 
                    'url' => $file['url'], 
                    'thumb' => $file['thumburl'], 
                    'size' => $file['size'], 
                    'createtime' => date('Y-m-d H:i:s', time())
            );
            $result = $order->insertphoto($photoarray);
            if ($result) {
                $order->updateorder(array(
                        'status' => 2
                ), $orderid);
            }
        
        } else if ($action == "delete") {
            $result = $order->deletephoto($id);
            if ($result) {
                $output = array(
                        "success" => "1", 
                        "msg" => "删除成功!"
                );
            
            }
            echo json_encode($output);
            exit();
        }
        $page = getgpc("page");
        $action = getgpc("action");
        $city = new CityModel();
        $citylist = $city->getCity();
        
        $page_count = 20;
        $page = $page < 1 ? 1 : $page;
        if ($page == "1") {
            $pagenum = 0;
        } else {
            $pagenum = ($page - 1) * $page_count;
        }
        $where = 'pro_order.status != 0 ';
        $buyerphone = getgpc("buyerphone");
        if ($buyerphone) {
            $where .= " and buyerphone = '{$buyerphone}'";
        }
        if ($search_starttime) {
            $where = $where . " and pro_order.ordertime>='{$search_starttime}'";
        }
        if ($search_endtime) {
            $where = $where . " and pro_order.ordertime<='{$search_endtime}'";
        }
        if ($status != "全部") {
            $where = $where . " and pro_order.status='{$status}'";
        }
        if ($storeid > 0) {
            $where .= " and pro_order.storeid='{$storeid}'";
        }
        $userinfo = $this->_G['user'];
        if ($userinfo['area'] > 0) {
            $where = $where . " and pro_order.storeid='{$userinfo['area']}'";
        }
        $Orderlistsum = $order->getOrderlistwhere($where);
        if ($Orderlistsum) {
            $totalcount = count($Orderlistsum);
        } else {
            $totalcount = 0;
        }
        $totalpage = ($totalcount % $page_count) > 0 ? (intval($totalcount / $page_count) + 1) : intval($totalcount / $page_count);
        $pagestr = auto_page_arr($page, $totalpage);
        $limit = " limit $pagenum,$page_count ";
        $Orderlist = $order->getOrderlistwhere($where, $limit);
        if ($userinfo['area'] < 1) {
            $store = new StoreModel();
            $storelist = $store->getstorewhere('');
        }
        $result = array(
                'storelist' => $storelist, 
                'userinfo' => $userinfo, 
                'Orderlist' => $Orderlist, 
                "pagestr" => $pagestr, 
                "totalcount" => $totalcount, 
                "page_count" => $page_count, 
                "buyerphone" => $buyerphone, 
                'page' => $page, 
                'action' => $action, 
                'citylist' => $citylist, 
                'totalpage' => $totalpage, 
                'search_starttime' => $search_starttime, 
                'search_endtime' => $search_endtime, 
                'status' => $status, 
                'storeid' => $storeid
        );
        $result = array_merge($result, $this->gettimearray());
        return $result;
    
    }
    // 邀请码
    function coupon ()
    {
        $userinfo = $this->_G['user'];
        $coupon = new CouponModel();
        $store = new StoreModel();
        $storelist = $store->getstorewhere($userinfo['area'] > 0 ? "pro_store.id='{$userinfo['area']}'" : '');
        $status = getgpc("status");
        $type = getgpc("type");
        $storeid = intval(getgpc("storeid"));
        $partner = getgpc('partner');
        $page = getgpc("page");
        $action = getgpc("action");
        $id = intval(getgpc("id"));
        if (empty($action)) {
            $page_count = 20;
            $page = $page < 1 ? 1 : $page;
            if ($page == "1") {
                $pagenum = 0;
            } else {
                $pagenum = ($page - 1) * $page_count;
            }
            $where = " 1=1 ";
            if ($partner) {
                $where = $where . ' and pro_coupon.partner="' . strtoupper($partner) . '"';
            }
            if ($storeid) {
                $where = $where . " and pro_coupon.storeid='{$storeid}'";
            }
            if (isset($type) && $type !== '') {
                $where = $where . " and pro_coupon.type='{$type}'";
            }
            if (isset($status) && $status !== '') {
                $where = $where . " and pro_coupon.status='{$status}'";
            }
            if ($userinfo['area'] > 0) {
                $where = $where . " and pro_coupon.storeid='{$userinfo['area']}'";
            }
            $totalcount = $coupon->getCouponCount($where);
            $totalpage = ($totalcount % $page_count) > 0 ? (intval($totalcount / $page_count) + 1) : intval($totalcount / $page_count);
            $pagestr = auto_page_arr($page, $totalpage);
            $limit = " $pagenum,$page_count ";
            $couponlist = $coupon->getCouponlist($where, $limit);
            foreach ($couponlist as $k => $v) {
                foreach ($storelist as $vv) {
                    if ($vv['id'] == $v['storeid']) {
                        $couponlist[$k]['storename'] = $vv['name'];
                    }
                }
            }
            $result = array(
                    'couponlist' => $couponlist, 
                    'search_starttime' => $search_starttime, 
                    'search_endtime' => $search_endtime, 
                    "pagestr" => $pagestr, 
                    "totalcount" => $totalcount, 
                    "page_count" => $page_count, 
                    "name" => $name, 
                    'page' => $page, 
                    'action' => $action, 
                    'totalpage' => $totalpage, 
                    'storelist' => $storelist, 
                    'storeid' => $storeid, 
                    'status' => $status
            );
            return $result;
        } elseif ($action == "from") {
            return array(
                    'storelist' => $storelist, 
                    'action' => $action
            );
        } elseif ($action == "save") {
            if (!$coupon->insertCoupon($_POST)) {
                $this->error('保存失败');
            }
            $this->success('保存成功', '?c=admin&a=coupon');
        } else if ($action == "delete") {
            $coupon->deleteCoupon($id);
            $output = array(
                    "success" => "1", 
                    "msg" => "删除成功!"
            );
            echo json_encode($output);
            exit();
        }
    }
    // 系统配置
    function config ()
    {
        $config = new ConfigModel();
        $configlist = $config->getConfig();
        $page = getgpc("page");
        $action = getgpc("action");
        $name = getgpc("name");
        if ($action == "from" && $name) {
            $configinfo = $config->getconfiginfo("name='{$name}'");
            return array(
                    "configinfo" => $configinfo, 
                    "action" => $action
            );
        }
        if ($action == "save") {
            $name = getgpc("name");
            $value = getgpc("value");
            $description = getgpc("description");
            $type = getgpc("type");
            if ($type == 'textarea') {
                $value = htmlspecialchars_decode($value, ENT_QUOTES);
            }
            if ($name) {
                $cityarray = array(
                        "name" => $name, 
                        "value" => $value, 
                        'description' => $description, 
                        'type' => $type
                );
                $config->updateconfig($cityarray, $name);
            } else {
                $cityarray = array(
                        "name" => $name, 
                        "value" => $value, 
                        'description' => $description, 
                        'type' => $type
                );
                $config->insertconfig($cityarray);
            }
            F('config', null);
            header('Location:?c=admin&a=config');
            exit();
        } else if ($action == "delete") {
            $config->deleteconfig($id);
            $output = array(
                    "success" => "1", 
                    "msg" => "删除成功!"
            );
            echo json_encode($output);
            exit();
        }
        $page_count = 20;
        $page = $page < 1 ? 1 : $page;
        if ($page == "1") {
            $pagenum = 0;
        } else {
            $pagenum = ($page - 1) * $page_count;
        }
        $name = getgpc("name");
        $where = " 1=1 and name like '%{$name}%'";
        $citylistsum = $config->getcitylist($where);
        $totalcount = count($citylistsum);
        $totalpage = ($totalcount % $page_count) > 0 ? (intval($totalcount / $page_count) + 1) : intval($totalcount / $page_count);
        $pagestr = auto_page_arr($page, $totalpage);
        $limit = " $pagenum,$page_count ";
        $configlist = $config->getcitylist($where, $limit);
        $result = array(
                'configlist' => $configlist, 
                "pagestr" => $pagestr, 
                "totalcount" => $totalcount, 
                "page_count" => $page_count, 
                "name" => $name, 
                'page' => $page, 
                'action' => $action, 
                'totalpage' => $totalpage
        );
        $result = array_merge($result, $this->gettimearray());
        return $result;
    }

    function gettimearray ()
    {
        // 昨天 今天
        $year_begin = date('Y-m-d 00:00:00', mktime(0, 0, 0, 1, 1, date('Y', time())));
        $year_end = date('Y-m-d 23:59:59', mktime(0, 0, 0, 12, 31, date('Y', time())));
        $yesteryear_begin = date('Y-m-d 00:00:00', mktime(0, 0, 0, 1, 1, date('Y', strtotime('-1 year', time()))));
        $yesteryear_end = date('Y-m-d 23:59:59', mktime(0, 0, 0, 12, 31, date('Y', strtotime('-1 year', time()))));
        
        $tomorrow_start = date("Y-m-d 00:00:00", strtotime("+1 day"));
        $tomorrow_end = date("Y-m-d 23:59:00", strtotime("+1 day"));
        $yesterday_start = date("Y-m-d 00:00:00", strtotime("-1 day"));
        $yesterday_end = date("Y-m-d 23:59:00", strtotime("-1 day"));
        $today_start = date("Y-m-d 00:00:00");
        $today_end = date("Y-m-d 23:59:00");
        $lweek_starttime = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1 - 7, date("Y")));
        $lweek_endtime = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - date("w") + 7 - 7, date("Y")));
        // 本周起始时间
        $week_starttime = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y")));
        $week_endtime = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y")));
        // 上月起始时间
        $lmonth_starttime = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
        $lmonth_endtime = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), 0, date("Y")));
        // 本月起始时间
        $month_starttime = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $month_endtime = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("t"), date("Y")));
        // 去年起始时间
        $lyear_starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date("Y") - 1));
        $lyear_endtime = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date("Y")) - 1);
        // 本年起始时间
        $year_starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date("Y")));
        $year_endtime = date('Y-m-d H:i:s', mktime(23, 59, 59, 1, 1, date("Y") + 1) - 24 * 3600);
        $timearr = array(
                'tomorrow_start' => $tomorrow_start, 
                'tomorrow_end' => $tomorrow_end, 
                'year_begin' => $year_begin, 
                'year_end' => $year_end, 
                'yesteryear_begin' => $yesteryear_begin, 
                'yesteryear_end' => $yesteryear_end, 
                'yesterday_start' => $yesterday_start, 
                'yesterday_end' => $yesterday_end, 
                'today_start' => $today_start, 
                'today_end' => $today_end, 
                'lweek_starttime' => $lweek_starttime, 
                'lweek_endtime' => $lweek_endtime, 
                'week_starttime' => $week_starttime, 
                'week_endtime' => $week_endtime, 
                'lmonth_starttime' => $lmonth_starttime, 
                'lmonth_endtime' => $lmonth_endtime, 
                'month_starttime' => $month_starttime, 
                "month_endtime" => $month_endtime, 
                'lyear_starttime' => $lyear_starttime, 
                'lyear_endtime' => $lyear_endtime, 
                'year_starttime' => $year_starttime, 
                'year_endtime' => $year_endtime
        );
        return $timearr;
    }
    // 门店信息
    function store ()
    {
        $store = new StoreModel();
        $city = new CityModel();
        $category = new CategoryModel();
        $page = getgpc("page");
        $action = getgpc("action");
        $step = getgpc("step");
        $citylist = $city->getCity();
        $id = getgpc("id");
        $story_categoryid = getgpc("story_categoryid");
        $store_id = getgpc("store_id");
        $categorylist = $category->getCategory();
        if ($step == "package") {
            $sql = "SELECT
	            pro_category.*, pro_story_category.id story_categoryid,pro_story_category.price
            FROM
	            pro_category
            LEFT JOIN pro_story_category ON pro_category.id = pro_story_category.categroy_id
            WHERE
	            store_id = '{$store_id}'";
            $storeinfo = $store->getStore($store_id);
            $categorylist = $category->getCategorylist($sql);
            return array(
                    "categorylist" => $categorylist, 
                    "action" => $action, 
                    'citylist' => $citylist, 
                    'step' => $step, 
                    'store_id' => $store_id, 
                    'storeinfo' => $storeinfo
            );
        } else if ($step == "story_category") {
            
            $story_categoryid = getgpc("story_categoryid");
            $sql = "SELECT
	            pro_category.*, pro_story_category.id story_categoryid,pro_story_category.price
            FROM
	            pro_category
            LEFT JOIN pro_story_category ON pro_category.id = pro_story_category.categroy_id
            WHERE
	            pro_story_category.id = '{$story_categoryid}'";
            $categoryinfo = $category->getCategoryInfo($sql);
            return array(
                    "categoryinfo" => $categoryinfo, 
                    "action" => $action, 
                    'citylist' => $citylist, 
                    'step' => $step, 
                    'store_id' => $store_id
            );
        }
        if ($action == "savestory_category") {
            $price = intval(getgpc("price")) * 100;
            $store->updatestory_category(array(
                    "price" => $price
            ), $id);
            
            header('Location:?c=admin&a=store&step=package&store_id=' . $store_id);
            exit();
        }
        if ($action == "from" && $id) {
            $storeinfo = $store->getstoreinfo("id=" . $id);
            
            $sql = "select * from pro_category where id in (select categroy_id from pro_story_category where store_id='{$id}')";
            $Categorystore = $category->getCategorylist($sql);
            $package = [];
            foreach ($Categorystore as $v) {
                array_push($package, $v['id']);
            }
            return array(
                    "storeinfo" => $storeinfo, 
                    "action" => $action, 
                    'citylist' => $citylist, 
                    'categorylist' => $categorylist, 
                    'package' => $package
            );
        }
        
        if ($action == "save") {
            // 保存门店信息
            $citycode = getgpc("citycode");
            $name = getgpc("name");
            $sort = getgpc("sort");
            $tel = getgpc("tel");
            $address = getgpc("address");
            $email = getgpc("email");
            $transit = getgpc("transit");
            $package = getgpc("package");
            if ($id) {
                $storearray = array(
                        "citycode" => $citycode, 
                        "name" => $name, 
                        'sort' => $sort, 
                        'tel' => $tel, 
                        'address' => $address, 
                        'email' => $email, 
                        'transit' => $transit
                );
                $store->updatestore($storearray, $id);
            } else {
                $storearray = array(
                        "citycode" => $citycode, 
                        "name" => $name, 
                        'sort' => $sort, 
                        'tel' => $tel, 
                        'address' => $address, 
                        'email' => $email, 
                        'transit' => $transit
                );
                $store->insertstore($storearray);
                $id = $store->getlastid();
            }
            $store->insertcategory($id, $package);
            
            header('Location:?c=admin&a=store');
            exit();
        } else if ($action == "delete") {
            // 删除门店信息
            $store->deletestore($id);
            $output = array(
                    "success" => "1", 
                    "msg" => "删除成功!"
            );
            echo json_encode($output);
            exit();
        }
        $page_count = 20;
        $page = $page < 1 ? 1 : $page;
        if ($page == "1") {
            $pagenum = 0;
        } else {
            $pagenum = ($page - 1) * $page_count;
        }
        $name = getgpc("name");
        $where = " 1=1 and name like '%{$name}%'";
        $userinfo = $this->_G['user'];
        if ($userinfo['area'] > 0) {
            $where = $where . " and pro_store.id='{$userinfo['area']}'";
        }
        $storelistsum = $store->getstorewhere($where);
        if ($storelistsum) {
            $totalcount = count($storelistsum);
        } else {
            $totalcount = 0;
        }
        $totalpage = ($totalcount % $page_count) > 0 ? (intval($totalcount / $page_count) + 1) : intval($totalcount / $page_count);
        $pagestr = auto_page_arr($page, $totalpage);
        $limit = " $pagenum,$page_count ";
        $storelist = $store->getstorewhere($where, $limit);
        foreach ($storelist as $k => $v) {
            $storeinfo = $store->getcityname($v['citycode']);
            $storelist[$k]['cityname'] = $storeinfo['name'];
        }
        if ($story_categoryid != null) {
            $story_category = $category->getpackage($story_categoryid);
        
        }
        $package = explode(",", $story_category['package']);
        return array(
                'storelist' => $storelist, 
                "pagestr" => $pagestr, 
                "totalcount" => $totalcount, 
                "page_count" => $page_count, 
                "name" => $name, 
                'page' => $page, 
                'action' => $action, 
                'citylist' => $citylist, 
                'totalpage' => $totalpage, 
                'categorylist' => $categorylist, 
                'story_categoryid' => $story_categoryid, 
                'package' => $package, 
                'step' => $step
        );
    }

    function negative ()
    {
        $search_starttime = getgpc("search_starttime");
        $status = getgpc("status");
        $search_endtime = getgpc("search_endtime");
        if (strlen($search_starttime) <= 10 && $search_starttime) {
            $search_starttime = $search_starttime . " 00:00:00";
        }
        if (strlen($search_endtime) <= 10 && $search_endtime) {
            $search_endtime = $search_endtime . " 23:59:00";
        }
        $order = new OrderModel();
        $page = getgpc("page");
        $action = getgpc("action");
        $id = getgpc("id");
        if ($action == "from" && $id) {
            $storeinfo = $order->getOrderinfowhere(" pro_order.id=" . $id);
            $weekarray = array(
                    "日", 
                    "一", 
                    "二", 
                    "三", 
                    "四", 
                    "五", 
                    "六"
            );
            $storeinfo['week'] = "周" . $weekarray[date("w", strtotime($storeinfo['ordertime']))];
            $ordertime = explode(" ", $storeinfo['ordertime']);
            $storeinfo['time'] = substr($ordertime[1], 0, 5);
            $storeinfo['itemlist'] = explode(";", $storeinfo['item']);
            return array(
                    "storeinfo" => $storeinfo, 
                    "action" => $action, 
                    'package' => $package
            );
        }
        $city = new CityModel();
        $citylist = $city->getCity();
        $page_count = 20;
        $page = $page < 1 ? 1 : $page;
        if ($page == "1") {
            $pagenum = 0;
        } else {
            $pagenum = ($page - 1) * $page_count;
        }
        $where = '1 ';
        $buyerphone = getgpc("buyerphone");
        if ($buyerphone) {
            $where .= " and buyerphone = '{$buyerphone}'";
        }
        if ($search_starttime) {
            $where = $where . " and pro_order.createtime>='{$search_starttime}'";
        }
        if ($search_endtime) {
            $where = $where . " and pro_order.createtime<='{$search_endtime}'";
        }
        $where = $where . " and pro_order.status='1'";
        $userinfo = $this->_G['user'];
        if ($userinfo['area'] > 0) {
            $where = $where . " and pro_order.storeid='{$userinfo['area']}'";
        }
        $Orderlistsum = $order->getOrderlistwhere($where);
        if ($Orderlistsum) {
            $totalcount = count($Orderlistsum);
        } else {
            $totalcount = 0;
        }
        $totalpage = ($totalcount % $page_count) > 0 ? (intval($totalcount / $page_count) + 1) : intval($totalcount / $page_count);
        $pagestr = auto_page_arr($page, $totalpage);
        $limit = " limit $pagenum,$page_count ";
        $Orderlist = $order->getOrderlistwhere($where, $limit);
        
        $result = array(
                'Orderlist' => $Orderlist, 
                "pagestr" => $pagestr, 
                "totalcount" => $totalcount, 
                "page_count" => $page_count, 
                "buyerphone" => $buyerphone, 
                'page' => $page, 
                'action' => $action, 
                'citylist' => $citylist, 
                'totalpage' => $totalpage, 
                'search_starttime' => $search_starttime, 
                'search_endtime' => $search_endtime, 
                'status' => $status
        );
        $result = array_merge($result, $this->gettimearray());
        return $result;
    
    }
    // 统计分析
    function statistic ()
    {
        $search_date = $this->gettimearray();
        $search_starttime = getgpc("search_starttime");
        $search_endtime = getgpc("search_endtime");
        $storeid = getgpc("storeid");
        if (!isset($search_starttime)) {
            $search_starttime = $search_date['month_starttime'];
        }
        if (!isset($search_endtime)) {
            $search_endtime = $search_date['month_endtime'];
        }
        $search_starttime = date('Y-m-d 00:00:00', strtotime($search_starttime));
        $search_endtime = date('Y-m-d 23:59:59', strtotime($search_endtime));
        
        $order = new OrderModel();
        $page = getgpc("page");
        $action = getgpc("action");
        $city = new CityModel();
        $citylist = $city->getCity();
        $page_count = 20;
        $page = $page < 1 ? 1 : $page;
        if ($page == "1") {
            $pagenum = 0;
        } else {
            $pagenum = ($page - 1) * $page_count;
        }
        
        $where = " pro_order.status>0 ";
        if ($search_starttime) {
            $where = $where . " and pro_order.createtime>='{$search_starttime}'";
        }
        if ($search_endtime) {
            $where = $where . " and pro_order.createtime<='{$search_endtime}'";
        }
        $userinfo = $this->_G['user'];
        if ($userinfo['area'] > 0) {
            $where = $where . " and pro_order.storeid='{$userinfo['area']}'";
        }
        
        if ($storeid > 0) {
            $where = $where . " and  pro_order.storeid='{$storeid}'";
        }
        $store = new StoreModel();
        $storelist = $store->getstorewhere('1=1');
        $sumcount = $order->getOrdersum($storeid);
        $orderin = $order->getOrderIn($where);
        $data3 = [];
        $x_axis = [];
        $data4 = [];
        foreach ($orderin as $k => $v) {
            array_push($data3, round_dollar($v['count']));
            array_push($x_axis, date('m-d', strtotime($v['createtime'])));
            array_push($data4, $v['num']);
        }
        $result = array(
                'current_sum' => floatval(array_sum($data3)), 
                'current_count' => intval(array_sum($data4)), 
                'Orderlist' => $Orderlist, 
                "pagestr" => $pagestr, 
                'storeid' => $storeid, 
                'userinfo' => $userinfo, 
                'storelist' => $storelist, 
                "totalcount" => $totalcount, 
                "page_count" => $page_count, 
                'page' => $page, 
                'action' => $action, 
                'citylist' => $citylist, 
                'totalpage' => $totalpage, 
                'search_starttime' => $search_starttime, 
                'search_endtime' => $search_endtime, 
                'sumcount' => $sumcount, 
                'orderin' => $orderin, 
                'data3' => $data3, 
                'x_axis' => $x_axis, 
                'orderCount' => $orderCount, 
                'data4' => $data4, 
                'sumCount1' => $sumCount1
        );
        $result = array_merge($result, $search_date);
        return $result;
    
    }

    function package ()
    {
        $store = new StoreModel();
        $store_id = getgpc("store_id");
        $categorylist = $store->getcategorylist($store_id);
        return array(
                "categorylist" => $categorylist
        );
    }
    // 排班信息
    function pool ()
    {
        $poolmodel = new PoolModel();
        $page = getgpc("page");
        $store = new StoreModel();
        $userinfo = $this->_G['user'];
        if ($userinfo['area'] > 0) {
            $where = "  pro_store.id='{$userinfo['area']}'";
        }
        $storelist = $store->getstorewhere($where);
        $action = getgpc("action");
        $id = getgpc("id");
        $hour = explode(",", "10,10:30,11,11:30,12,12:30,13,13:30,14,14:30,15,15:30,16,16:30,17,17:30");
        
        if ($action == "edit" && $id) {
            
            $poolinfo = $poolmodel->getPoolwhereinfo("id=" . $id);
            return array(
                    "poolinfo" => $poolinfo, 
                    "action" => $action, 
                    'hour' => $hour, 
                    'storelist' => $storelist
            );
        }
        if ($action == "save") {
            $starttime = getgpc("starttime");
            $endtime = getgpc("endtime");
            $hour = getgpc("hour");
            $start = new DateTime($starttime);
            $end = new DateTime($endtime);
            
            $maxcount = getgpc("maxcount");
            $storeid = getgpc("storeid");
            if ($id) {
                // 修改排班信息
                $storeid = getgpc("storeid");
                $today = getgpc("today");
                $cityarray = array(
                        "starttime" => $starttime, 
                        "endtime" => $endtime, 
                        'today' => $today, 
                        'storeid' => $storeid, 
                        'maxcount' => $maxcount
                );
                $poolmodel->updatePool($cityarray, $id);
            } else {
                // 新增排班信息
                set_time_limit(3600);
                $savemode = intval(getgpc('savemode'));
                for ($start; $start <= $end; $start->modify('+1 day')) {
                    $today = $start->format('Y-m-d');
                    // 获取已有的排班
                    $exists_date = $poolmodel->getTodayPool($today, $storeid);
                    // 提交的排班
                    $post_date = [];
                    foreach ($hour as $k => $v) {
                        $date_str = strtotime($today . ' ' . $v);
                        if ($date_str) {
                            $post_date[] = date('Y-m-d H:i:s', $date_str);
                        }
                    }
                    // 比较差异
                    $add_date = array_diff($post_date, $exists_date);
                    $delete_date = array_diff($exists_date, $post_date);
                    $update_date = array_intersect($exists_date, $post_date);
                    if ($add_date) {
                        $arr = [];
                        foreach ($add_date as $k => $v) {
                            $arr['today'][] = $today;
                            $arr['starttime'][] = $v;
                            $arr['endtime'][] = date('Y-m-d H:i:s', strtotime($v) + 1800);
                            ;
                            $arr['maxcount'][] = $maxcount;
                            $arr['storeid'][] = $storeid;
                        }
                        if (!$poolmodel->insertPool($arr)) {
                            $this->error('操作失败(add)');
                        }
                    }
                    if ($delete_date && $savemode) {
                        if (!$poolmodel->deletePool(implode(',', array_keys($delete_date)))) {
                            $this->error('操作失败(delete)');
                        }
                    }
                    if ($update_date && $savemode) {
                        foreach ($update_date as $k => $v) {
                            if (false === $poolmodel->updatePool([
                                    'maxcount' => $maxcount
                            ], $k)) {
                                $this->error('操作失败(update)');
                            }
                        }
                    }
                }
            }
            header('Location:?c=admin&a=Pool');
            exit();
        } else if ($action == "delete") {
            // 删除排班信息
            $poolmodel->deletePool($id);
            $output = array(
                    "success" => "1", 
                    "msg" => "删除成功!"
            );
            echo json_encode($output);
            exit();
        }
        $page_count = 20;
        $page = $page < 1 ? 1 : $page;
        if ($page == "1") {
            $pagenum = 0;
        } else {
            $pagenum = ($page - 1) * $page_count;
        }
        $storeid = getgpc("storeid");
        $search_starttime = getgpc("search_starttime");
        $search_endtime = getgpc("search_endtime");
        if (strlen($search_starttime) <= 10 && $search_starttime) {
            $search_starttime = $search_starttime . " 00:00:00";
        }
        if (strlen($search_endtime) <= 10 && $search_endtime) {
            $search_endtime = $search_endtime . " 23:59:00";
        }
        $where = "1=1 ";
        if (!$storeid == "") {
            $where = $where . "  and storeid ='{$storeid}'";
        }
        if ($search_starttime) {
            $where = $where . " and today >='{$search_starttime}'";
        }
        if ($search_endtime) {
            $where = $where . "  and today <='{$search_endtime}'";
        }
        $userinfo = $this->_G['user'];
        if ($userinfo['area'] > 0) {
            $where = $where . " and pro_pool.storeid='{$userinfo['area']}'";
        }
        $name = getgpc("name");
        $totalcount = $poolmodel->getPoolCount($where);
        $totalpage = ($totalcount % $page_count) > 0 ? (intval($totalcount / $page_count) + 1) : intval($totalcount / $page_count);
        $pagestr = auto_page_arr($page, $totalpage);
        $limit = " $pagenum,$page_count ";
        $Poollist = $poolmodel->getPoollistwhere($where, $limit);
        foreach ($Poollist as $k => $v) {
            foreach ($storelist as $vv) {
                if ($vv['id'] == $v['storeid']) {
                    $Poollist[$k]['storename'] = $vv['name'];
                }
            }
        }
        $result = array(
                'Poollist' => $Poollist, 
                "pagestr" => $pagestr, 
                "totalcount" => $totalcount, 
                "page_count" => $page_count, 
                "name" => $name, 
                'page' => $page, 
                'action' => $action, 
                'storelist' => $storelist, 
                'storeid' => $storeid, 
                'search_starttime' => $search_starttime, 
                'search_endtime' => $search_endtime, 
                'hour' => $hour, 
                'totalpage' => $totalpage
        );
        $result = array_merge($result, $this->gettimearray());
        return $result;
    }
    // 导出word
    function exportword ()
    {
        $id = intval(getgpc("id"));
        $order = new OrderModel();
        $user = $this->_G['user'];
        $date = date('Y年m月d日', time());
        $storeinfo = $order->getOrderinfowhere(" pro_order.id=" . $id);
        return array(
                'storeinfo' => $storeinfo, 
                'date' => $date, 
                'user' => $user
        );
    }
    // 套餐信息
    function category ()
    {
        
        $category = new CategoryModel();
        $categorylist = $category->getCategory();
        $page = getgpc("page");
        
        $action = getgpc("action");
        $id = getgpc("id");
        if ($action == "from" && $id) {
            $categoryinfo = $category->getCategorywhereinfo("id=" . $id);
            return array(
                    "categoryinfo" => $categoryinfo, 
                    "action" => $action
            );
        }
        if ($action == "save") {
            // 保存套餐信息
            $file = uploadfile($_FILES['doc-form-file']);
            $icon = getgpc("icon");
            if (count($file) > 1) {
                $icon = $file['url'];
            }
            $name = getgpc("name");
            $type = getgpc("type");
            $sort = getgpc("sort");
            $delay = getgpc("delay") * 3600;
            if ($id) {
                $cityarray = array(
                        "icon" => $icon, 
                        "delay" => $delay, 
                        "name" => $name, 
                        'sort' => $sort, 
                        'type' => $type
                );
                $category->updateCategory($cityarray, $id);
            } else {
                $cityarray = array(
                        "icon" => $icon, 
                        "delay" => $delay, 
                        "name" => $name, 
                        'sort' => $sort, 
                        'type' => $type
                );
                $category->insertCategory($cityarray);
            }
            header('Location:?c=admin&a=category');
            exit();
        } else if ($action == "delete") {
            // 删除套餐信息
            $category->deleteCategory($id);
            $output = array(
                    "success" => "1", 
                    "msg" => "删除成功!"
            );
            echo json_encode($output);
            exit();
        }
        $page_count = 20;
        $page = $page < 1 ? 1 : $page;
        if ($page == "1") {
            $pagenum = 0;
        } else {
            $pagenum = ($page - 1) * $page_count;
        }
        $name = getgpc("name");
        $where = " 1=1 and name like '%{$name}%'";
        $categorylistsum = $category->getCategorywhere($where);
        if ($categorylistsum) {
            $totalcount = count($categorylistsum);
        
        } else {
            $totalcount = 0;
        }
        $totalpage = ($totalcount % $page_count) > 0 ? (intval($totalcount / $page_count) + 1) : intval($totalcount / $page_count);
        $pagestr = auto_page_arr($page, $totalpage);
        $limit = " $pagenum,$page_count ";
        $categorylist = $category->getCategorywhere($where, $limit);
        return array(
                'categorylist' => $categorylist, 
                "pagestr" => $pagestr, 
                "totalcount" => $totalcount, 
                "page_count" => $page_count, 
                "name" => $name, 
                'page' => $page, 
                'action' => $action
        );
    }
    // 城市信息
    function city ()
    {
        $city = new CityModel();
        $page = getgpc("page");
        $action = getgpc("action");
        $code = getgpc("code");
        if ($action == "from" && $code) {
            $cityinfo = $city->getcityinfo("code=" . $code);
            return array(
                    "cityinfo" => $cityinfo, 
                    "action" => $action
            );
        }
        if ($action == "save") {
            $id = getgpc("id");
            $code = getgpc("code");
            $name = getgpc("name");
            $status = getgpc("status");
            
            $sort = getgpc("sort");
            if ($sort == "") {
                $sort = 0;
            }
            $phone = getgpc("phone");
            if ($id) {
                // 更新城市信息
                $cityarray = array(
                        "code" => $code, 
                        "name" => $name, 
                        'sort' => $sort, 
                        'phone' => $phone, 
                        'status' => $status
                );
                $city->updateCity($cityarray, $id);
            } else {
                // 新增城市信息
                $cityarray = array(
                        "code" => $code, 
                        "name" => $name, 
                        'sort' => $sort, 
                        'phone' => $phone, 
                        'status' => $status
                );
                $city->insertCity($cityarray);
            }
            header('Location:?c=admin&a=city');
            exit();
        } else if ($action == "delete") {
            $city->deletecity($code);
            $output = array(
                    "success" => "1", 
                    "msg" => "删除成功!"
            );
            echo json_encode($output);
            exit();
        }
        $page_count = 20;
        $page = $page < 1 ? 1 : $page;
        if ($page == "1") {
            $pagenum = 0;
        } else {
            $pagenum = ($page - 1) * $page_count;
        }
        $name = getgpc("name");
        $where = " 1=1 and name like '%{$name}%'";
        $citylistsum = $city->getcitylist($where);
        if ($citylistsum) {
            $totalcount = count($citylistsum);
        } else {
            $totalcount = 0;
        }
        $totalpage = ($totalcount % $page_count) > 0 ? (intval($totalcount / $page_count) + 1) : intval($totalcount / $page_count);
        $pagestr = auto_page_arr($page, $totalpage);
        $limit = " $pagenum,$page_count ";
        $citylist = $city->getcitylist($where, $limit);
        return array(
                'citylist' => $citylist, 
                "pagestr" => $pagestr, 
                "totalcount" => $totalcount, 
                "page_count" => $page_count, 
                "name" => $name, 
                'page' => $page, 
                'action' => $action
        );
    }
    
    // 用户信息
    function user ()
    {
        $user = new UserModel();
        $store = new StoreModel();
        $storelist = $store->getStore();
        $storelist = array_column($storelist, null, 'id');
        $storeid = getgpc("storeid");
        $page = getgpc("page");
        $action = getgpc("action");
        $id = getgpc("id");
        if ($action == "from" && $id) {
            $userinfo = $user->getUser($id);
            return array(
                    "userinfo" => $userinfo, 
                    "action" => $action, 
                    'storelist' => $storelist
            );
        }
        if ($action == "edit") {
            // 编辑用户信息
            if ($id) {
                $userinfo = $user->getUser($id);
            } else {
                $userinfo = null;
            }
            return array(
                    "userinfo" => $userinfo, 
                    "action" => $action, 
                    'storelist' => $storelist
            );
        }
        if ($action == "save") {
            Auth::check('user/auth', $this->_G['user']['id']) || $this->error('权限校验失败');
            if ($id) {
                $area = getgpc("area");
                if (!$user->updateAuth($area, $id)) {
                    $this->error('操作失败');
                }
            }
            header('Location:?c=admin&a=user');
            exit();
        }
        
        if ($action == "editpass") {
            // 修改密码
            $telephone = getgpc("telephone");
            $password = getgpc("password");
            if ($id) {
                $useinfo = $user->getUser($id);
                if ($useinfo['password'] != $user->derange_password($password)) {
                    if (!$user->updatepass($telephone, $password, $id)) {}
                }
            } else {
                $useinfo = $user->getUser(null, " telephone=" . $telephone);
                if (!$useinfo) {
                    $user->insertpass($telephone, $password);
                } else {
                    $this->error('该手机号已存在');
                }
            }
            header('Location:?c=admin&a=user');
            exit();
        }
        $page_count = 20;
        $page = $page < 1 ? 1 : $page;
        if ($page == "1") {
            $pagenum = 0;
        } else {
            $pagenum = ($page - 1) * $page_count;
        }
        $where = '1 ';
        $telephone = getgpc("telephone");
        if ($telephone) {
            $where .= " and telephone = '{$telephone}'";
        }
        if (isset($storeid) && $storeid !== "") {
            $where .= "  and area ='{$storeid}'";
        }
        $totalcount = $user->getUserCount($where);
        $totalpage = ($totalcount % $page_count) > 0 ? (intval($totalcount / $page_count) + 1) : intval($totalcount / $page_count);
        $pagestr = auto_page_arr($page, $totalpage);
        $limit = " $pagenum,$page_count ";
        $userlist = $user->getUser(null, $where, $limit);
        foreach ($userlist as $k => $v) {
            if ($v['area'] > 0) {
                $userlist[$k]['store'] = $storelist[$v['area']]['name'];
            } else if ($v['area'] == 0) {
                $userlist[$k]['store'] = '总店';
            } else {
                $userlist[$k]['store'] = '';
            }
        }
        $search_starttime = getgpc("search_starttime");
        $search_endtime = getgpc("search_endtime");
        if (strlen($search_starttime) <= 10 && $search_starttime) {
            $search_starttime = $search_starttime . " 00:00:00";
        }
        if (strlen($search_endtime) <= 10 && $search_endtime) {
            $search_endtime = $search_endtime . " 23:59:00";
        }
        $result = array(
                'userlist' => $userlist, 
                'storelist' => $storelist, 
                'storeid' => $storeid, 
                "search_starttime" => $search_starttime, 
                "search_endtime" => $search_endtime, 
                "telephone" => $telephone, 
                "pagestr" => $pagestr, 
                "totalcount" => $totalcount, 
                "page_count" => $page_count, 
                "name" => $name, 
                'totalpage' => $totalpage, 
                'page' => $page, 
                'action' => $action
        );
        $result = array_merge($result, $this->gettimearray());
        return $result;
    }

    /**
     * 发送短信验证码
     */
    public function Post ($curlPost, $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }
    
    // 将 xml数据转换为数组格式。
    public function xml_to_array ($xml)
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = $this->xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

    /**
     * 评价管理
     */
    public function comment ()
    {
        if (empty($_GET['action'])) {
            
            $where = [];
            if ($this->_G['user']['area'] > 0) {
                $where[] = 'storeid = ' . $this->_G['user']['area'];
            }
            if ($_GET['search_orderid']) {
                $where[] = 'orderid = ' . intval($_GET['search_orderid']);
            }
            if ($_GET['search_storeid']) {
                $where[] = 'storeid = ' . intval($_GET['search_storeid']);
            }
            if ($_GET['search_starttime'] && $_GET['search_endtime']) {
                $where[] = 'createtime between "' . date('Y-m-d 00:00:00', strtotime($_GET['search_starttime'])) . '" and "' . date('Y-m-d 23:59:59', strtotime($_GET['search_endtime'])) . '"';
            }
            if ($_GET['search_server'] && $_GET['search_score']) {
                $where[] = $_GET['search_server'] . ' = ' . intval($_GET['search_score']);
            }
            $where = implode(' and ', $where);
            $orderModel = new OrderModel();
            $totalcount = $orderModel->getCommentCount($where);
            $pagesize = getPageParams($_GET['page'], $totalcount);
            $list = $orderModel->getCommentList('*', $where, $pagesize['limitstr']);
            $storeModel = new StoreModel();
            $storelist = $storeModel->getstorewhere($this->_G['user']['area'] > 0 ? 'id = ' . $this->_G['user']['area'] : '');
            $storelist = array_column($storelist, null, 'id');
            return array(
                    'pagesize' => $pagesize, 
                    'list' => $list, 
                    'storelist' => $storelist, 
                    'search_date' => $this->gettimearray()
            );
        
        } elseif ($_GET['action'] == 'view') {
            
            $orderModel = new OrderModel();
            $info = $orderModel->getCommentInfo(intval($_GET['id']));
            return [
                    'info' => $info
            ];
        
        } elseif ($_GET['action'] == 'chart') {
            
            $orderModel = new OrderModel();
            $score_list = $orderModel->commentScore($this->_G['user']['area'], $_GET['search_starttime'], $_GET['search_endtime']);
            header("Cache-control: public");
            header("Pragma: public");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment;filename=" . date('YmdHis') . '.xls');
            include APPLICATION_PATH . '/application/views/default/admin/comment_xls.html';
            exit();
        
        }
    
    }

}