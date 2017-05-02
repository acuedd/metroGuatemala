<?php
/**
 * Created by PhpStorm.
 * User: Alexander Flores
 * Date: 01-sep-16
 * Time: 4:07 p.m.
 */
include_once("modules/settings/settings_controller.php");
include_once("modules/settings/addon/webservices/webservices_model.php");
include_once("modules/settings/addon/webservices/webservices_view.php");


use kernel\Controller\controller;
use kernel\Controller\response_webservice;
use settings\webservices\Model\webservices_model;
use settings\webservices\View\webservices_view;
class webservices_controller extends settings_controller implements controller {

    private $strOpCod = "";
    private $strPathMaster = "webservices/webservices_core/webservice_master.php";
    private $strNameClassMaster = "webservice_master";
    private $response = false;

    public $objClass = false;
    private $arrInfoClass = array();

    public function __construct($strAction = ""){
        parent::__construct($strAction);
    }

    public function run(){
        $objModel = webservices_model::getInstance();
        $objView = webservices_view::getInstance($this->getStrAction());
        $objView->setStrNamePage($this->getStrNamePage());
        $this->getHtml();
        $objView->setStrReport($objModel->getRptWebservices());
        $objView->drawPage();
    }

    public function getOperation(){
        if($this->getParam("op",false)){
            if($this->arrParam["op"] == "save"){
                return $this->create_services();
            }
        }
    }

    public function setStrOpCod($strOpCod){
        $this->strOpCod = $strOpCod;
        $this->setClass();
    }

    public function getHtml(){
        if($this->getParam("frm")){
            $objModel = webservices_model::getInstance();
            $arrData = array();
            if($this->getParam("uuid",false)){
                $strUuid = $this->getParam("uuid");
                $arrData["webservice"] = $objModel->getWebservice($strUuid);
                $arrData["params"] = $objModel->getWebserviceParams($strUuid);
                $arrData["function"] = $objModel->getWebserviceFunction($strUuid);
            }
            $objView = webservices_view::getInstance($this->getStrAction());
            $objView->drawContent($arrData);
            die;
        }
        if($this->getParam("add")){
            $objView = webservices_view::getInstance($this->getStrAction());
            $objView->draw_line($this->getParam("opt",""),$this->getParam("count",0));
            die;
        }
    }

    public function create_services(){
        if($this->validate_fields_service()){
            $this->setOperationCode();
            if($this->save_service()){
                $this->save_fields_service();
                $this->save_functions_services();
                $arrTMP = array();
                $arrTMP["op"] = $this->strOpCod;
                $this->response = response_webservice::response(1,"Servicio guardado corretamente",$arrTMP);
            }
            else{
                $this->response = response_webservice::response(0,"No se pudo crear el servicio");
            }
        }
        return $this->response;
    }

    private function setOperationCode(){
        $objModel = webservices_model::getInstance();
        if(empty($this->arrParam["txtOp"])){
            if($objModel->getEngine() == "mysql"){
                $this->strOpCod = $objModel->sql_ejecutarKey("SELECT UUID()");
            }
            elseif($objModel->getEngine() == "sqlsrv"){
                $this->strOpCod = $objModel->sql_ejecutarKey("SELECT NEWID()");
            }
        }
        else{
            $this->strOpCod = trim($this->arrParam["txtOp"]);
        }
    }

    private function validate_fields_service(){
        $arrError = array();
        if(empty($this->arrParam["module"])){
            $arrError[] = "Módulo no puede estar vacío sssss";
        }
        if(empty($this->arrParam["descripcion"])){
            $arrError[] = "descripcion no puede estar vacío";
        }
        if(empty($this->arrParam["path_class"])){
            $arrError[] = "path_class no puede estar vacío";
        }
        elseif(!file_exists($this->arrParam["path_class"])){
            $arrError[] = "path_class no existe";
        }
        if(empty($this->arrParam["name_class"])){
            $arrError[] = "name_class no puede estar vacío";
        }
        if(empty($this->arrParam["allowed"])){
            $arrError[] = "allowed no puede estar vacío";
        }
        if(empty($this->arrParam["format_response"])){
            $arrError[] = "format_response no puede estar vacío";
        }
        if(empty($this->arrParam["method_response"])){
            $arrError[] = "method_response no puede estar vacío";
        }
        foreach($this->arrParam AS $key => $val){
            $arrKey = explode("_", $key);
            if(isset($arrKey[1]) && ($arrKey[0] == "desc" || $arrKey[0] == "key")){
                $intCampo = $arrKey[1] + 1;
                if($arrKey[0] == "desc" && empty($val)){
                    $arrError[] = "La descripción del parámetro No. {$intCampo} no puedes estar vacio";
                }
                if($arrKey[0] == "key" && empty($val)){
                    $arrError[] = "El key del parámetro No. {$intCampo} no puedes estar vacio";
                }
            }
            unset($key);
            unset($val);
        }
        if(count($arrError)){
            $strMsj = implode("<br>",$arrError);
            $this->response = response_webservice::response(0,$strMsj,false,false);
            return false;
        }
        $this->validate_fields_extra();
        return true;
    }

    private function validate_fields_extra(){
        $this->arrParam["access"] = isset($this->arrParam["access"])?$this->arrParam["access"]:"freeAccess";
        $this->arrParam["active"] = isset($this->arrParam["active"])?"Y":"N";
        $this->arrParam["public"] = isset($this->arrParam["public"])?"Y":"N";
        $this->arrParam["check_config"] = isset($this->arrParam["check_config"])?"Y":"N";
    }

    private function save_service(){
        $arrKey = array();
        $arrKey["op_uuid"] = $this->strOpCod;
        $arrFields = array();
        $arrFields["modulo"] = $this->getParam("module");
        $arrFields["descripcion"] = $this->getParam("descripcion");
        $arrFields["include_path"] = $this->strPathMaster;
        $arrFields["className"] = $this->strNameClassMaster;
        $arrFields["publica"] = $this->getParam("public");
        $arrFields["acceso"] = $this->getParam("access");
        $arrFields["activo"] = $this->getParam("active");
        $arrFields["isNewMod"] = "Y";
        $arrFields["path_mainClass"] = $this->getParam("path_class");
        $arrFields["class_mainClass"] = $this->getParam("name_class");
        $arrFields["allowed_format"] = implode(",", $this->arrParam["allowed"]);
        $arrFields["format_response"] = implode(",", $this->arrParam["format_response"]);
        $arrFields["method_response"] = $this->getParam("method_response");
        $arrFields["check_config_device"] = $this->getParam("check_config");

        $objModel = webservices_model::getInstance();
        return $objModel->sql_TableUpdate("webservices_operations",$arrKey,$arrFields);
    }

    private function save_fields_service(){
        $objModel = webservices_model::getInstance();

        $arrTMP = array();
        $arrTMP["webservices_operations_extra_data"] = array();
        $arrTMP["webservices_operations_extra_data"]["op"] = $this->strOpCod;
        $objModel->sql_TableDelete($arrTMP);
        foreach($this->arrParam AS $key => $val){
            $arrKey = explode("_", $key);
            if(isset($arrKey[1]) && ($arrKey[0] == "desc")){
                $intCampo = $arrKey[1];
                $arrKey = array();
                $arrKey["id"] = 0;
                $arrFields = array();
                $arrFields["op"] = $this->strOpCod;
                $arrFields["required"] = isset($this->arrParam["required_{$intCampo}"])?"Y":"N";
                $arrFields["parameter_description"] = $this->getParam("desc_{$intCampo}");
                $arrFields["method_validation"] = $this->getParam("validate_{$intCampo}");
                $arrFields["key_parameter"] = $this->getParam("key_{$intCampo}");
                $arrFields["error_response"] = $this->getParam("error_{$intCampo}");
                $arrFields["transform_key"] = $this->getParam("trans_{$intCampo}");

                $objModel->sql_TableUpdate("webservices_operations_extra_data", $arrKey, $arrFields);
            }
            unset($key);
            unset($val);
        }
    }

    private function save_functions_services(){
        $objModel = webservices_model::getInstance();

        $arrTMP = array();
        $arrTMP["webservices_operations_extra_function"] = array();
        $arrTMP["webservices_operations_extra_function"]["op"] = $this->strOpCod;
        $objModel->sql_TableDelete($arrTMP);
        foreach($this->arrParam AS $key => $val){
            $arrKey = explode("_", $key);
            if(isset($arrKey[1]) && ($arrKey[0] == "function")){
                $intCampo = $arrKey[1];
                if(empty($this->arrParam["function_{$intCampo}"]))continue;
                $arrKey = array();
                $arrKey["id"] = 0;
                $arrFields = array();
                $arrFields["op"] = $this->strOpCod;
                $arrFields["webservices_baseClass"] = isset($this->arrParam["derived_{$intCampo}"])?"Y":"N";
                $arrFields["str_function"] = $this->getParam("function_{$intCampo}");

                $objModel->sql_TableUpdate("webservices_operations_extra_function", $arrKey, $arrFields);
            }
            unset($key);
            unset($val);
        }
    }

    /**
     * Trae toda la información del servicio e instancia la clase principal
     */
    private function setClass(){
        $objModel = webservices_model::getInstance();
        $this->arrInfoClass = $objModel->getWebservice($this->strOpCod);
        if(file_exists($this->arrInfoClass["path_mainClass"])){
            include_once($this->arrInfoClass["path_mainClass"]);
            if(class_exists($this->arrInfoClass["class_mainClass"])){
                $this->objClass = new $this->arrInfoClass["class_mainClass"]();
            }
        }
    }

    public function get_extra_function(){
        $objModel = webservices_model::getInstance();
        return $objModel->getWebserviceFunction($this->strOpCod);
    }

    public function check_params_class(&$arrParams){
        $objModel = webservices_model::getInstance();
        $this->response = response_webservice::response(1,"Parámetros correctos");
        if($this->objClass){
            $arrParametros = $objModel->getWebserviceParams($this->strOpCod);
            if(count($arrParametros)){
                foreach($arrParametros AS $val){
                    //Reviso los parametros requeridos
                    if($val["required"] == "Y"){
                        if(empty($arrParams[$val["key_parameter"]])){
                            $error = (!empty($val["error_response"]))?$val["error_response"]:"Falta o viene vacío parámetro '{$val["key_parameter"]} - {$val["parameter_description"]}'";
                            $this->response = response_webservice::response(0,$error,false,false);
                            break;
                        }
                    }
                    if(isset($arrParams[$val["key_parameter"]])){
                        $objModel->sql_real_escape_string($arrParams[$val["key_parameter"]]);
                        //Si tiene metodo de validacion lo valido con el metodo
                        if(!empty($val["method_validation"])){
                            $strMethod = $val["method_validation"];
                            if(method_exists($this->objClass, $strMethod)){
                                $boolTMP = $this->objClass->$strMethod($arrParams[$val["key_parameter"]]);
                                if(!empty($boolTMP)){
                                    $error = (!empty($val["error_response"]))?$val["error_response"]:"'{$val["key_parameter"]} - {$val["parameter_description"]}' es incorrecto";
                                    $this->response = response_webservice::response(0,$error,false,false);
                                    break;
                                }
                            }
                            else{
                                $this->response = response_webservice::response(0,"No existe método de validación para el parámetro '{$val["key_parameter"]} - {$val["parameter_description"]}'",false,false);
                                break;
                            }
                        }

                        //Transformo el key si asi lo pidiera
                        if(!empty($val["transform_key"])){
                            $arrParams[$val["transform_key"]] = $arrParams[$val["key_parameter"]];
                            unset($arrParams[$val["key_parameter"]]);
                        }
                    }
                    else{
                        $arrParams[$val["key_parameter"]] = "";
                        //Transformo el key si asi lo pidiera
                        if(!empty($val["transform_key"])){
                            $arrParams[$val["transform_key"]] = "";
                            unset($arrParams[$val["key_parameter"]]);
                        }
                    }
                    unset($val);
                }
            }
            else{
                $this->response = response_webservice::response(0,$this->lang["WEBSERVICES_ERROR021"],false,false);
            }
        }
        else{
            $this->response = response_webservice::response(0,$this->lang["WEBSERVICES_ERROR022"],false,false);
        }
        return $this->response;
    }

    public function response_class($arrParams){
        $strMethodResponse = $this->arrInfoClass["method_response"];
        if(!empty($strMethodResponse)){
            if(method_exists($this->objClass, $strMethodResponse)){
                $this->response = $this->objClass->$strMethodResponse($arrParams);
            }
            else{
                $this->response = response_webservice::response(0,"Método de respuesta no existe");
            }
        }
        else{
            $this->response = response_webservice::response(0,"Método de respuesta no existe");
        }
        return $this->get_return();
    }

    public function getModosPermitidos(){
        $strModes = $this->arrInfoClass["allowed_format"];
        $arr = explode(",", $strModes);
        return $arr;
    }

    public function getFormatResponse(){
        $strFormats = $this->arrInfoClass["format_response"];
        $arr = explode(",", $strFormats);
        return $arr;
    }

    public function get_info_class(){
        return $this->arrInfoClass;
    }

    public function execute_function($strFunction){
        $this->response = response_webservice::response(0,"método de validación no exite");
        if($this->objClass){
            if(method_exists($this->objClass, $strFunction)){
                return $this->objClass->$strFunction();
            }
        }
        return $this->get_return();
    }
	private function get_return(){
		/*
		if(is_array($this->response))
			utf8_encode_array($this->response);
		*/
		return $this->response;
	}
}