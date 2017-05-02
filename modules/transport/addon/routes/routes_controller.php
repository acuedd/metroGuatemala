<?php
include_once("modules/transport/transport_controller.php");
include_once("modules/transport/addon/routes/routes_model.php");
include_once("modules/transport/addon/routes/routes_view.php");

use kernel\Controller\controller;
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 02/05/17
 * Time: 15:16
 */
class routes_controller extends transport_controller implements controller
{

	public function __construct($strAction = ""){
		parent::__construct($strAction);
	}

	public function run()
	{
		// TODO: Implement run() method.
	}

	public function getOperation()
	{
		// TODO: Implement getOperation() method.
	}

	public function getRoutes(){
		$arrResponse = array();

		$arrparams = $this->arrParam;
		self::drawdebug($arrparams);

		$arr = array();
		$arr["h1"] = "Ruta principal";
		$arr["h2"] = "Mixco terminal";
		$arr["h3"] = "Mixco terminal";
		$arr["busmame"] = "2";
		$arr["busfare"] = "2";
		$arr["stars"] =  "5";
		array_push($arrResponse, $arr);

		$arr = array();
		$arr["h1"] = "Ruta principal 2";
		$arr["h2"] = "Mixco terminal";
		$arr["h3"] = "Mixco terminal";
		$arr["busmame"] = "2";
		$arr["busfare"] = "2";
		$arr["stars"] =  "5";
		array_push($arrResponse, $arr);

		$arr = array();
		$arr["h1"] = "Ruta principal 2";
		$arr["h2"] = "Mixco terminal";
		$arr["h3"] = "Mixco terminal";
		$arr["busmame"] = "2";
		$arr["busfare"] = "2";
		$arr["stars"] =  "5";
		array_push($arrResponse, $arr);


		return \kernel\Controller\response_webservice::response(1,"holi",array("detail"=>$arrResponse));
	}

}