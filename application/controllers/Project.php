<?php
class Project_Action extends ActionPDO {

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
     * 开始页
     */
    public function book ()
    {
        if ($this->_jssdk) {
            $jssdk = $this->_jssdk->GetSignPackage();
            if ($jssdk['errorcode'] !== 0) {
                $jssdk = null;
            } else {
                $jssdk = $jssdk['data'];
            }
        }
        $this->show([
            'refund_rule' => json_decode(getConfig('refund_rule'), true),
            'jssdk' => $jssdk,
            'userinfo' => $this->_G['user']
        ]);
    }

    /**
     * 城市选择
     */
    public function city ()
    {
        $this->show([
            'list' => (new CityModel())->getcitylist()
        ]);
    }

    /**
     * 项目选择
     */
    public function select ()
    {
        if (!$cityInfo = (new CityModel())->getCity(intval(getgpc('citycode')), 'status = 1')) {
            $this->error('城市未开通', '?c=project&a=city', 0);
        }
        $projects = (new ProjectModel())->getProjects();
        $this->show([
            'city_name' => $cityInfo['name'],
            'projects' => $projects
        ]);
    }

    protected function show (array $data = [])
    {
        $this->render(strtolower($this->_module) . ucfirst($this->_action) . '.html', $data);
    }

}
