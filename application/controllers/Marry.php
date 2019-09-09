<?php
class Marry_Action extends ActionPDO {

    /* 项目 */
    protected $projectId = 2;

    public function __init ()
    {
        $_notAuth = [];
        if (!in_array($this->_action, $_notAuth)) {
            // 登录验证
            if (empty($this->_G['user'])) {
                $this->error('用户校验失败');
            }
        }
    }

    /**
     * 门店选择
     */
    public function stores ()
    {
        if (!$cityInfo = (new CityModel())->getCity(intval(getgpc('citycode')), 'status = 1')) {
            $this->error('城市未开通', '?c=project&a=city', 0);
        }
        $stores = (new StoreModel())->getStore(null, 'status = 1 and project_id = ' . $this->projectId . ' and citycode = ' . $cityInfo['code'], null, 'id,name,address', 'sort asc');
        $this->show([
            'city_name' => $cityInfo['name'],
            'stores' => $stores
        ]);
    }

    /**
     * 产品选择
     */
    public function products ()
    {
        if (!$storeInfo = (new StoreModel())->getStore(intval(getgpc('store_id')), 'status = 1 and project_id = ' . $this->projectId)) {
            $this->error('该门店无效');
        }
        if (!$cityInfo = (new CityModel())->getCity($storeInfo['citycode'])) {
            $this->error('城市未开通');
        }
        $categories = (new CategoryModel())->getCategoryByStore($storeInfo['id']);
        $this->show([
            'city_name' => $cityInfo['name'],
            'storeInfo' => $storeInfo,
            'categories' => $categories
        ]);
    }

    /**
     * 产品详情
     */
    public function detail ()
    {
        $categoryModel = new CategoryModel();
        if (!$categoryInfo = $categoryModel->getCategory(intval(getgpc('category_id')), 'project_id = ' . $this->projectId)) {
            $this->error('该产品不存在');
        }
        if (!$relationCategory = $categoryModel->getRelationCategory($categoryInfo['id'])) {
            $this->error('该产品无效');
        }
        if (!$storeInfo = (new StoreModel())->getStore($relationCategory['store_id'], 'status = 1 and project_id = ' . $this->projectId)) {
            $this->error('门店不存在');
        }
        $categoryInfo['icon'] = httpurl($categoryInfo['icon']);
        $relationCategory['price'] = round_dollar($relationCategory['price']);
        $this->show([
            'categoryInfo' => $categoryInfo,
            'relationCategory' => $relationCategory,
            'storeInfo' => $storeInfo,
        ]);
    }

    /**
     * 排班日期
     */
    public function schedules ()
    {
        $categoryModel = new CategoryModel();
        if (!$categoryInfo = $categoryModel->getCategory(intval(getgpc('category_id')), 'project_id = ' . $this->projectId, null, 'id,name')) {
            $this->error('该产品不存在');
        }
        if (!$relationCategory = $categoryModel->getRelationCategory($categoryInfo['id'])) {
            $this->error('该产品无效');
        }
        if (!$storeInfo = (new StoreModel())->getStore($relationCategory['store_id'], 'status = 1 and project_id = ' . $this->projectId)) {
            $this->error('门店不存在');
        }
        if (!$cityInfo = (new CityModel())->getCity($storeInfo['citycode'])) {
            $this->error('城市未开通');
        }
        $schedules = (new PoolModel())->getScheduleDays($relationCategory['store_id']);
        $this->show([
            'city_name' => $cityInfo['name'],
            'categoryInfo' => $categoryInfo,
            'storeInfo' => $storeInfo,
            'schedules' => $schedules
        ]);
    }

    /**
     * 排班时间
     */
    public function times ()
    {
        $categoryModel = new CategoryModel();
        if (!$categoryInfo = $categoryModel->getCategory(intval(getgpc('category_id')), 'project_id = ' . $this->projectId, null, 'id,name,delay')) {
            $this->error('该产品不存在');
        }
        if (!$relationCategory = $categoryModel->getRelationCategory($categoryInfo['id'])) {
            $this->error('该产品无效');
        }
        if (!$storeInfo = (new StoreModel())->getStore($relationCategory['store_id'], 'status = 1 and project_id = ' . $this->projectId)) {
            $this->error('门店不存在');
        }
        if (!$cityInfo = (new CityModel())->getCity($storeInfo['citycode'])) {
            $this->error('城市未开通');
        }
        $result = (new PoolModel())->getScheduleTimes($relationCategory['store_id'], getgpc('date'));
        echo json_encode($result);
        exit(0);
    }

    /**
     * 确认支付
     */
    public function payment ()
    {
        $categoryModel = new CategoryModel();
        if (!$categoryInfo = $categoryModel->getCategory(intval(getgpc('category_id')), 'project_id = ' . $this->projectId, null, 'id,name,delay')) {
            $this->error('该产品不存在');
        }
        if (!$relationCategory = $categoryModel->getRelationCategory($categoryInfo['id'])) {
            $this->error('该产品无效');
        }
        if (!$storeInfo = (new StoreModel())->getStore($relationCategory['store_id'], 'status = 1 and project_id = ' . $this->projectId)) {
            $this->error('门店不存在');
        }
        if (!$cityInfo = (new CityModel())->getCity($storeInfo['citycode'])) {
            $this->error('城市未开通');
        }
        if (!$poolInfo = (new PoolModel())->get('maxcount > 0 and storeid = ' . $storeInfo['id'] . ' and id = ' . intval(getgpc('poolid')))) {
            $this->error('本次预约已满，请选择其他时段');
        }

        $categoryInfo['delay'] = round($categoryInfo['delay'] / 3600, 1);
        $poolInfo['ordertime'] = showWeekDate($poolInfo['starttime']);

        if ($this->_jssdk) {
            $jssdk = $this->_jssdk->GetSignPackage();
            if ($jssdk['errorcode'] !== 0) {
                $this->error($jssdk['data']);
            } else {
                $jssdk = $jssdk['data'];
            }
        }
        $this->show([
            'categoryInfo'    => $categoryInfo,
            'storeInfo'       => $storeInfo,
            'poolInfo'        => $poolInfo,
            'price'           => round_dollar($relationCategory['price']),
            'downpay_percent' => round_dollar(getConfig('downpay_percent')),
            'userInfo'        => $this->_G['user'],
            'refund_rule'     => json_decode(getConfig('refund_rule'), true),
            'jssdk'           => $jssdk
        ]);
    }

    public function coupon ()
    {
        $code = safe_subject(getgpc('code'));
        $storeid = intval(getgpc('storeid'));
        $coupon = new CouponModel();
        if (!$result = $coupon->getcouponinfofromcode($code, $storeid)) {
            echo json_encode([
                'isValid' => false,
                'error_msg' => '优惠码无效'
            ]);
            exit(0);
        }
        echo json_encode([
            'isValid' => true,
            'error_msg' => '优惠' . round_dollar($result['cost']) . '元'
        ]);
        exit(0);
    }

    protected function show (array $data = [])
    {
        $this->render(strtolower($this->_module) . ucfirst($this->_action) . '.html', $data);
    }

}
