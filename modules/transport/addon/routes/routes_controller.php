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
		array_push($arrResponse, array( "h1"=>"Ruta principal",
										"h2"=> "Mixco terminal",
										"busfare"=>"2",
										"stars"=> "5"));
		array_push($arrResponse, array( "h1"=>"Ruta principal 2 ",
			"h2"=> "Mixco terminal",
			"busfare"=>"2",
			"stars"=> "5"));
		array_push($arrResponse, array( "h1"=>"Ruta principal 3 ",
			"h2"=> "Mixco terminal",
			"busfare"=>"2",
			"stars"=> "5"));

		return \kernel\Controller\response_webservice::response(1,"holi",array("detail"=>$arrResponse));
	}

}