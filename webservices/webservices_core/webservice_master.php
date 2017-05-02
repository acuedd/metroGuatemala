<?php
/**
 * Created by PhpStorm.
 * User: Alexander FLORES
 * Date: 01-sep-16
 * Time: 12:22 p.m.
 */
require_once("webservices/webservices_baseclass.php");
require_once("modules/settings/addon/webservices/webservices_controller.php");
class webservice_master extends webservices_baseClass{

    private $objAdmin = false;
    function __construct($strCodigoOperacion, $arrInfoOperacion) {
        parent::__construct($this,$strCodigoOperacion, $arrInfoOperacion);
        //obtengo formatos permitidos
        $this->objAdmin = new webservices_controller();
        $this->objAdmin->setStrOpCod($strCodigoOperacion);
        $this->setModosPermitidos($this->objAdmin->getModosPermitidos());
        $this->setFormatosPermitidos($this->objAdmin->getFormatResponse());
    }

    public function setParametros($arrParametros) {
        $this->arrParams = $arrParametros;
        //Valido si se quiere ver que la configuracion del dispositivo ha cambiado
        $arrTMP = $this->objAdmin->get_info_class();
        if($arrTMP["check_config_device"] == "Y"){
            if(!$this->check_config_device())return false;
        }

        /**
         * Si se desean tener las bondades o funciones del webservices, entonces se envia la clase como tal a la clase del proceso
         * Esto es ya para un uso avanzado de los webservices (o para quien se anime y no se pierda en código XD)
         */
        if(method_exists($this->objAdmin->objClass, "setObjWebservices")){
            $this->objAdmin->objClass->setObjWebservices($this);
        }

        //reviso si existen funciones para validar
        $arrTMP = $this->objAdmin->get_extra_function();
        if(count($arrTMP)){
            $_return = NULL;
            foreach($arrTMP AS $val){
                $strFunction = $val["nombre"];
                if($val["local"] == "Y"){
                    if(method_exists($this, $strFunction)){
                        $_return = $this->$strFunction();
                    }
                }
                else{
                    $_return = $this->objAdmin->execute_function($strFunction);
                }

                if(is_array($_return)){
                    if($_return["status"] == "fail"){
                        $this ->appendError($_return["msj"]);
                        return false;
                    }
                }
                elseif(is_bool($_return)){
                    if(!$_return){
                        return false;
                    }
                }
                else{
                    $this ->appendError("Respuesta del método \"{$strFunction}\" es incorrecto");
                    return false;
                }
                unset($val);
            }
        }

        $arrReturn = $this->objAdmin->check_params_class($this->arrParams);
        /**
         * Si la clase viene derivada de un global_config entonces se válida para setear los parámetros
         */
        if(method_exists($this->objAdmin->objClass,"setArrParam") ){
            $this->objAdmin->objClass->setArrParam($this->arrParams);
        }
        if($arrReturn["status"] == "fail"){
            $this->appendError($arrReturn["msj"]);
            return false;
        }
        return true;
    }

    public function darRespuesta() {
        $response = $this->objAdmin->response_class($this->arrParams);
        if(is_array($response)){
            $this->arrDataOutput = $response;
            parent::darRespuesta();
        }
        elseif(is_string($response)){
            $this->strContentOutput = $response;
            parent::darRespuesta();
        }
        else{
            $this->appendError("WEBSERVICES_DATA_NOT_READY");
            $this->darRespuestaInvalido();
        }
    }
}