<?php
//op:84c9b355-550c-11e6-8ea0-0025904f3ae4
use kernel\Controller\debug;
use kernel\Controller\response_webservice;
use kernel\Model\global_model;

require_once("webservices/webservices_library.php");
require_once("webservices/webservices_baseclass.php");

class webservice_validate_udid extends webservices_baseClass implements webservices{
    
    function __construct($strCodigoOperacion, $arrInfoOperacion) {
        parent::__construct($this, $strCodigoOperacion, $arrInfoOperacion);
        $this->setModosPermitidos(array("am")); //Solo para aplicaciones moviles
		$this->setFormatosPermitidos(array("xmlwa", "xmlno", "json")); //Esta operacion debiera pedir la data en un formato corto estructurado
    }
    /**
	* Override para definir los parametros
	*
	* @param mixed $arrParametros
	*
	* Espero que los parametros sean:
	* $this->arrParam["user"] = "xxxx";
	* $this->arrParam["password"] = "xxxx";
	* $this->arrParam["udid"] = "xxxx";
	*/
	public function setArrParam($arrParametros) {
		$this->arrParam = $arrParametros;

		if (!isset($this->arrParam["udid"])) {
			$this->appendError("Faltan parámetros");
			return false;
		}
		else {
			return true;
		}
	}
    /**
     * Valido si el udid que me envían es valido o no 
     */
    public function darRespuesta(){
        $model = global_model::getInstance();
        webservice_deactiveNotConfirmedDevices();
        
        $strCodeSecurity = $this->getParam("udid");        
        $strCodeSecurity_E = $model->sql_real_escape_string($strCodeSecurity);
        $strQuery = "SELECT userid
                    FROM    webservices_devices
                    WHERE   activo = 'Y' AND device_udid = '{$strCodeSecurity_E}'";
        $intUserID = $model->sql_ejecutarKey($strQuery);
        if($intUserID !== false){
            $strQuery = "SELECT userid FROM main_user WHERE userid = {$intUserID} AND active = 'Y' ";
            $intUserID = $model->sql_ejecutarKey($strQuery);
        }
        if($intUserID === false){
            parent::darRespuestaInvalido(); 
        }
        else{
            $this->arrDataOutput = response_webservice::response(1);
            parent::darRespuesta();
        }                
    }

}

