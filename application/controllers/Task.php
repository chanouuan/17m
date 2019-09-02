<?php
class Task_Action extends ActionPDO {

    /**
     * 订单短信提醒
     */
    public function orderalert ()
    {
        set_time_limit(3600);
        $cardmodel = new CardsModel();
        $cardmodel->orderalert();
        return null;
    }

}