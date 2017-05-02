<?php
//op_uuid: bdef04ac-cadb-11e1-8235-df5303155f9f
use kernel\Controller\debug;
use kernel\Controller\global_function;
use kernel\Controller\response_webservice;
use kernel\Model\global_model;
use login\model\login_model;

require_once("webservices/webservices_library.php");
require_once("webservices/webservices_baseclass.php");


class webservice_record_udid extends webservices_baseClass {

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

		if (!isset($this->arrParam["user"]) || !isset($this->arrParam["password"])) {
			$this->appendError("Faltan parámetros");
			return false;
		}
		else {
			return true;
		}
	}
    /**
	* Valido si el udid (codigo del dispositivo) que me envian es valido o no.
	*
	*/
    public function darRespuesta() {
        $model = global_model::getInstance();
        $this->arrDataOutput = array();

        // Primero verifico el usuario y contraseña y que este activo
		$strUserName = $this->getParam("user");
		$strPassWord = $this->getParam("password");
        
        //Parametros opcionales
		$strTipo = $this->getParam("tipo");
		$strMarca = $this->getParam("marca");
		$strModelo = $this->getParam("modelo");
        $strOSversion = $this->getParam("OSversion");
        $strAppversion = $this->getParam("appversion");
        $strDispositivoID = $this->getParam("dispositivo_id");
        $strApiVersion = $this->getParam("apiversion");
        $strOS = $this->getParam("OS");
        $strAppName = $this->getParam("appname");
        $boolOK = true;
        
        //$model->sql_ejecutar("INSERT INTO z_debug (content) VALUES ('prueba register');");
        $sql = $this->getQueryUser($strUserName,$strPassWord);
        $arrUserInfo = $model->sql_ejecutarKey($sql);
        if (!$arrUserInfo) {
            $this->appendError("Usuario invalido");
	        $arr = array();
	        $arr["userData"] = "";
            $this->darRespuestaInvalido($arr);
        }
        else{
            $booContinue = true;
            webservice_deactiveNotConfirmedDevices();
            $strUDID = "";
            $intUserID = $arrUserInfo["userid"];
            $arrEmails = array();
            if (!empty($arrUserInfo["email"]) && global_function::core_validateEmailAddress($arrUserInfo["email"])) $arrEmails[] = $arrUserInfo["email"];
            if(empty($strApiVersion)){
                $this->appendError("Existe una nueva version de la aplicación, dirígete a la tienda!");
                $booContinue = false;
            }
            else{
                $sql = "SELECT 	U.userid,
                                        WDA.id_deviceauth, WDA.id_credencial, WDA.userid, WDA.activo, 
                                        WD.id AS device_id, WD.device_udid, WD.activo AS device_activo, WD.confirmado AS device_confirmado
                        FROM (
                                        (
                                            main_user U
                                                LEFT JOIN webservices_devices_auth WDA ON WDA.userid = U.userid
                                        )
                                        LEFT JOIN webservices_devices WD ON WD.code_device = '{$strDispositivoID}' AND WD.userid = U.userid
                                    )
                        WHERE U.userid = '{$intUserID}'";                
                $arrInfo = $model->sql_ejecutarKey($sql);
                if($arrInfo){
                    //Reviso si existe ya el dispositivo registrado                                   
                    if(!empty($arrInfo["device_udid"])){
                        if($arrInfo["device_activo"] =="N"){
                            $model->sql_ejecutar("UPDATE webservices_devices SET activo = 'N' WHERE userid = '{$intUserID}'");
                            $model->sql_ejecutar("UPDATE webservices_devices SET activo = 'Y' WHERE id = '{$arrInfo["device_id"]}'");
                            $strUDID = $arrInfo["device_udid"];
                            $booContinue = true;
                            //$this->appendError("El dispositivo se encuentra inactivo, debe confirmarlo de su sitio online.");
                        }
                        else{
                            $model->sql_ejecutar("UPDATE webservices_devices SET activo = 'N' WHERE userid = '{$intUserID}'");
                            $model->sql_ejecutar("UPDATE webservices_devices SET activo = 'Y' WHERE id = '{$arrInfo["device_id"]}'");
                            $strUDID = $arrInfo["device_udid"];
                            $model->sql_ejecutar("UPDATE webservices_devices SET 
                                            marca = '{$strMarca}', 
                                            modelo = '{$strModelo}', 
                                            osversion = '{$strOSversion}', 
                                            appversion = '{$strAppversion}', 
                                            code_device = '{$strDispositivoID}', 
                                            apiversion = '{$strApiVersion}',
                                            OS = '{$strOS}',
                                            appname = '{$strAppName}',
                                            modified_config = 'N'
                                            WHERE id = '{$arrInfo["device_id"]}'");
                        }
                    }
                    else{
                        //voy a revisar si tiene mas dispositivos asociados, si tiene mas entonces no dejo relacionarlo hasta desactivar el dispositivo anterior
                        $strQuery = "SELECT COUNT(*) as cuantos FROM webservices_devices WHERE userid = '{$intUserID}' AND activo = 'Y'";
                        $intCount = $model->sql_ejecutarKey($strQuery);
                        //Do not applied limit of devices                        
                        if($intCount>0 && false){
                            $booContinue = false;
                            $this->appendError("Ya hay un dispositivo asociado a su usuario, para ingresar desasocie su anterior dispositivo.");                            
                        }
                        else{
                            if($this->strEngine == "mysql"){
                                $strQuery = "INSERT INTO webservices_devices
                                        (userid, id_deviceauth, device_udid, activo, fecha_alta, tipo, marca, modelo, osversion, appversion, code_device, apiversion,confirmado,OS,appname)
                                        VALUES
                                        ('{$intUserID}', '{$arrInfo["id_deviceauth"]}' , UUID(), 'Y', NOW(), '{$strTipo}', '{$strMarca}', '{$strModelo}', '{$strOSversion}', '{$strAppversion}', '{$strDispositivoID}', '{$strApiVersion}','Y','{$strOS}','{$strAppName}')";
                            }
                            else if($this->strEngine == "sqlsrv"){
                                $strQuery = "INSERT INTO webservices_devices
                                        (userid, id_deviceauth, device_udid, activo, fecha_alta, tipo, marca, modelo, osversion, appversion, code_device, apiversion,confirmado,OS,appname)
                                        VALUES
                                        ('{$intUserID}', '{$arrInfo["id_deviceauth"]}' , NEWID(), 'Y', GETDATE(), '{$strTipo}', '{$strMarca}', '{$strModelo}', '{$strOSversion}', '{$strAppversion}', '{$strDispositivoID}', '{$strApiVersion}','Y','{$strOS}','{$strAppName}')";
                            }
                                        
                            $model->sql_ejecutar($strQuery);
                            $intPrimaryKey = $model->sql_lastID();
                            $strUDID = $model->sql_getArray("SELECT device_udid FROM webservices_devices WHERE id = {$intPrimaryKey}");
                        }
                    }
                }
                else{
                    $booContinue = false;
                    $this->appendError("Su usuario no tiene permitido poder relacionar este dispositivo");
                }
            }
            
            if($booContinue && (!empty($strUDID))){
                $strDestination = "";
                if (count($arrEmails)) $strDestination = implode(", ", $arrEmails);
                $sqlPhone = "SELECT phone_number FROM phones WHERE table_from = 'main_user' AND idtable = {$intUserID}";
                $sqlAddress = "SELECT CONCAT(D.address, ' ', D.district, ' ', D.town, ' ', D.state) as address
                                FROM  addresses AS D 
                                WHERE table_from = 'main_user' AND idtable = '{$intUserID}'";
                $login = login_model::getInstance();
                $login->llenar_session($intUserID);
                $this->arrDataOutput["udid"] = $strUDID;
                $this->arrDataOutput["institucion"] = "";
                $this->arrDataOutput["userData"] = $arrUserInfo;
                $stmt = $model->sql_ejecutar($sqlPhone);
                $i = 0;
                while($rtmp = $model->sql_fetch_assoc($stmt)){
                    $i++;
                    $this->arrDataOutput["userData"]["phone{$i}"] = ($rtmp["phone_number"])?$rtmp["phone_number"]:"";
                }
                $strAddress = $model->sql_ejecutarKey($sqlAddress);
                $this->arrDataOutput["userData"]["address"] = ($strAddress)?$strAddress:"";
                $this->arrDataOutput = response_webservice::response(1,"Dispositivo registrado satisfactoriamente.",$this->arrDataOutput);
                parent::darRespuesta();
            }
            else{
	            $arr = array();
	            $arr["userData"] = "";
                parent::darRespuestaInvalido($arr);
            }                         
        }                
    }

    public function getQueryUser($strUserName,$strPassWord){
        $strQuery = "";
        if($this->strEngine == "sqlsrv"){
            $strPassWord = md5($strPassWord);
            $strQuery = "SELECT  CONVERT(VARCHAR(10),U.birth_date,110) as birth,
                                U.userid, U.nickname, U.type AS 'tipo', U.first_name AS 'nombres', U.last_name AS 'apellidos', U.fullname AS 'nombreCompleto', 
                                U.email
                        FROM    main_user U
                        WHERE   [nickname] = '{$strUserName}' AND
                                [password] = '{$strPassWord}' AND
                                active = 'Y'";
        }
        elseif($this->strEngine = "mysql"){
            $strQuery = "SELECT  DATE_FORMAT(U.birth_date,'%d/%m/%Y') as birth,
                        DATE_FORMAT(U.fecha_ingresoclub, '%d/%m/%Y') as club_enter, 
                        DATE_FORMAT(U.conyugue_fecha_nacimiento, '%d/%m/%Y') as bith_couple,
                        U.userid, U.nickname, U.type AS 'tipo', U.first_name AS 'nombres', U.last_name AS 'apellidos', U.fullname AS 'nombreCompleto', 
                        U.email, U.avatar, U.degree AS 'titulo', U.codigo, U.conyugue_nombre, UP.label AS profesion
                FROM    main_user U
                          LEFT JOIN user_merge_profession UP ON UP.userid = U.userid

                WHERE   nickname = '{$strUserName}' AND
                        password = md5('{$strPassWord}') AND
                        active = 'Y'
                GROUP BY U.userid";
        }

        return $strQuery;
    }
}