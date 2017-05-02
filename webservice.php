<?php
use kernel\Controller\global_controller;
use kernel\Controller\global_function;

require_once("kernel/global_controller.php");
require_once 'librarys/php/xml/xmlfunctions.php';
require_once 'webservices/webservices_library.php';

function local_invalid_operation($strDebugMessage = "") {
	$boolGlobalIsLocalDev = true;
	if ($boolGlobalIsLocalDev) {
		die("{$strDebugMessage}, en produccion dara un error 404 - Not Found");
	}
	else {        
		$strProtocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		header($strProtocol . " 400 Bad request");
		die();
	}
}

$strCodigoOperacion = (isset($_REQUEST["o"]))?global_controller::user_magic_quotes($_REQUEST["o"]):false; // codigo de operacion - Obligatorio
$strFormatoRespuesta = (isset($_REQUEST["f"]))?global_controller::user_magic_quotes($_REQUEST["f"]):"json"; //formato de salida - Default es json
$strModoOperacion = (isset($_REQUEST["m"]))?global_controller::user_magic_quotes($_REQUEST["m"]):false; // modo de operacion - Obligatorio
$strSecurityToken = (isset($_REQUEST["t"]) && !empty($_REQUEST["t"]))?global_controller::user_magic_quotes($_REQUEST["t"]):session_id(); // token de seguridad - Obligatorio solo para modo am ya que un session id jamas sera un UDID
$strQry = (isset($_REQUEST["qry"]))?global_controller::user_magic_quotes($_REQUEST["qry"]):false; // Para ver si se quiere hacer un query de los formatos o modos de operacion validos

if ($strCodigoOperacion === false || $strFormatoRespuesta === false || $strModoOperacion === false || $strSecurityToken === false) {
    local_invalid_operation("Faltan parametros");
}
else{
    // Recibo los parametros de operacion en un array de GET o POST  (mejor si son en POST para que funcione con friendly URL)
	$arrParametros = $_REQUEST;
    
    // Quito los parametros de la operacion en si
	if (isset($arrParametros["o"])) unset($arrParametros["o"]);
	if (isset($arrParametros["f"])) unset($arrParametros["f"]);
	if (isset($arrParametros["m"])) unset($arrParametros["m"]);
	if (isset($arrParametros["t"])) unset($arrParametros["t"]);
	if (isset($arrParametros["qry"])) unset($arrParametros["qry"]);
    
    global_function::utf8_decode_array($arrParametros);
    
    $arrInfo = webservice_getOperationInfo($strCodigoOperacion);    
    if($arrInfo === false){
        local_invalid_operation("Operacion invalida");
    }
    else{        
        //TODO check module
        if (!file_exists($arrInfo["include_path"])) local_invalid_operation("Path no encontrado");  
        
        // Estas variables se leen de mi base de datos... ¿Valdra la pena validar más por seguridad?
        include_once($arrInfo["include_path"]);
		$strClassName = $arrInfo["className"];
        $arrInfo["page"] = (!empty($arrParametros["page"]))?$arrParametros["page"]:"";
		$objWebservice = new $strClassName($strCodigoOperacion, $arrInfo);
        if (!$objWebservice->setFormatoRespuesta($strFormatoRespuesta)) {
			die($objWebservice->getError());
		}
        if (!$objWebservice->setModoOperacion($strModoOperacion)) {
			$objWebservice->darRespuestaInvalido();
		}
        else{
            if ($arrInfo["publica"] == "N" && (!$objWebservice->boolValidarCodigo($strSecurityToken) || !$objWebservice->boolValidarAcceso())) {
				$objWebservice->darRespuestaInvalido();
			}
            else{
                if ($strQry) {
					// Puedo solicitar a la operacion que me indique los formatos disponibles y modos de operacion válidos.
					if ($strFormatoRespuesta == "txt" || $strFormatoRespuesta == "html" || $strFormatoRespuesta == "bin") {
						die("Formato invalido para qry");
					}
					else {
						if ($strQry == "formatos_validos") {
							$objWebservice->darRespuestaFormatosValidos();
						}
						else if ($strQry == "modos_validos") {
							$objWebservice->darRespuestaModosValidos();
						}
						else {
							local_invalid_operation("Qry invalido de webservice");
						}
					}
				}
                else {
					$varReturn = "not am";
					if ($strModoOperacion == "am" && isset($arrParametros["aim"])) {
						$varReturn = webservice_check_saved_response($objWebservice->getDeviceID(), $arrParametros["aim"], $strFormatoRespuesta);
					}
					if ($varReturn !== "stop") {
						if ($objWebservice->setArrParam($arrParametros)) {
							$objWebservice->darRespuesta();
						}
						else {
							$objWebservice->darRespuestaInvalido();
						}
					}
				}
            }            
        }
    }    
}

