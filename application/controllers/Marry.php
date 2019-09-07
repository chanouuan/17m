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
        if (!$categoryInfo = $categoryModel->getCategory(intval(getgpc('category_id')), 'project_id = ' . $this->projectId)) {
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
            'storeInfo' => $storeInfo,
            'schedules' => $schedules
        ]);
    }

    protected function show (array $data = [])
    {
        $this->render(strtolower($this->_module) . ucfirst($this->_action) . '.html', $data);
    }

}
