<?php

class MarryModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = & Db::getInstance();
    }



}
