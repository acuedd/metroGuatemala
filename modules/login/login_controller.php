<?php
/**
 * Created by PhpStorm.
 * User: acuedd
 * Date: 30/08/2016
 * Time: 1:51 PM
 */
require_once("kernel/global_controller.php");
include_once("modules/login/login_view.php");

class login_controller extends global_controller{

    function __construct($strAction) {
        parent::__construct($strAction);
    }
}