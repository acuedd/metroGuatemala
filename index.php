<?php
use kernel\Controller\global_controller;
use kernel\Controller\global_function;
use kernel\Controller\response_webservice;
use kernel\View\global_view;
use login\model\login_model;

date_default_timezone_set("America/Guatemala");
$strAction = basename(__FILE__);
//error_reporting(0);
require_once("kernel/global_controller.php");
require_once("modules/login/login_model.php");
$strTitle = config("title");
$objcontroller = new global_controller($strAction);
$objcontroller->setArrParam($_REQUEST);
$objcontroller->setStrTitle($strTitle);
$objcontroller->setStrAction($strAction);
if($objcontroller->getParam("act","") == "ajax"){
    $login = login_model::getInstance();
    if($login->check_login()) {
        $boolInclude = (isset($_GET["ijs"]))?true:false;
        if($boolInclude){
            $objcontroller->getObjViewScripts();
        }
        $objcontroller->getAjaxContent($objcontroller->getParam("page"),$objcontroller->getParam("mod"),"",$objcontroller->getParam("title"), ($boolInclude)?false:true );
    }
    else{
        global_function::getHeaders("json");
        response_webservice::printResponse(0, "Sesión caducada", false, false);
    }
    die();
}

if($objcontroller->getMethod() == "get"){
    if($objcontroller->getParam("sestimer",false)){

        $login = login_model::getInstance();
        global_function::getHeaders("json");
        //header("Content-Type: aplication/json");
        if($login->check_login()){
            response_webservice::printResponse(1,"ok");
        }
        else{
            response_webservice::printResponse(0,"fail");
        }
        die();
    }
}

$login = new login_model();
if($objcontroller->getParam("logout", false)){
    $login->LogOut();
    header("location: index.php");
    exit();
}

if( $objcontroller->getParam("login_name",false) && $objcontroller->getParam("login_passwd",false)){
    if(!$login->login($objcontroller->getParam("login_name"), $objcontroller->getParam("login_passwd"))){
        $arr = $objcontroller->getArrParams();
        echo "<div class='bg-danger' width='100%' align='center'>
                <h4>El usuario y password son incorrectos</h4>
             </div>";
    }
    else{
        $arr = $objcontroller->getArrParams();
    }
}

if($login->check_login()){
    include_once "kernel/sestimer.php";
    if($objcontroller->getParam("act","") == "lnk"){
        $objView = global_view::getInstance($strAction);
        $objView->getCabecera($objcontroller->getStrTitle());
        $objcontroller->getAjaxContent($objcontroller->getParam("page"),$objcontroller->getParam("mod"),"",$objcontroller->getParam("title"), false );
        $objView->getPiePagina();
    }
    else{
        $objcontroller->principal_struct();
    }
}
else{
    $option = $objcontroller->getParam("page",config("middle"));
    $settings = $objcontroller->getParam("settings");
    $objView = global_view::getInstance($strAction);
    if(!empty($settings)){
        $conf = array();
        $appset = explode("|", $settings);
        while($item = each($appset)){
            $sub = explode("-",$item["value"]);
            $conf[$sub[0]] = $sub[1];
        }

        if(!empty($conf["header"]) && $conf["header"] == "true"){
            $objView->getCabecera($strTitle,false,config("header"));
        }

        loadLayout($option);

        if(!empty($conf["footer"]) && $conf["footer"] == "true"){
            $objView->getPiePagina(config("footer"));
        }
    }
    else{
	    $objView->getCabecera($strTitle,false,config("header"));
    	if($option == "interno"){
		    loadLayout("");
	    }
	    else{
		    loadLayout($option);
	    }
        $objView->getPiePagina(config("footer"));
    }
}

function loadLayout($option){
    if(!empty($option)){
        $file = "views/layouts/{$option}.php";
        if(!file_exists($file)){
            include_once("404.shtml");
        }
        else{
            include_once($file);
        }
    }
    else{
        include_once("modules/login/login_view.php");
    }
}