<?php
use kernel\Model\global_model;

require_once("kernel/global_controller.php");
function webservice_getOperationInfo($strOperationUUID) {
    $model = global_model::getInstance();
	$strOperationUUID_E = $model->sql_real_escape_string($strOperationUUID);
	$strQuery = "SELECT modulo, descripcion, include_path, className, 
                        activo, publica, acceso
				 FROM webservices_operations
				 WHERE activo = 'Y' AND op_uuid = '{$strOperationUUID_E}'";
	$arrInfo = $model->sql_ejecutarKey($strQuery);
	if ($arrInfo === false) {
		return false;
	}
	else {
		return $arrInfo;
	}
}
/**
* Funcion que recorre un array tipo arbol y construye un XML analogo.  $arrArreglo se manda por referencia para ahorrar memoria.
*
* @param XMLobject $objXML Objeto XMLNode a poblar
* @param array $arrArreglo Array con los datos a insertar, puede ser de N niveles
* @param boolean $boolMayUseAtributes si puede usar atributos o no
*/
function webservice_arrayIntoXML(&$objXML, &$arrArreglo, $boolMayUseAtributes) {
	reset($arrArreglo);    
	while ($arrItem = each($arrArreglo)) {
		if (is_array($arrItem["value"])) {
			// Si es un array, lo pongo como hijo y hago recurrencia
			if (is_numeric($arrItem["key"])) {
				// Si el key es numerico, pongo el nodo como "item" y el key lo pongo como un atributo o como un nodo segun el caso
				$objItem = &$objXML->children[$objXML->addChild("item")];
				if ($boolMayUseAtributes) {
					$objItem->addAttribute("nodekey", $arrItem["key"]);
				}
				else {
					$objKey = &$objItem->children[$objItem->addChild("nodekey")];
					$objKey->setInternalText($arrItem["key"]);
				}
				webservice_arrayIntoXML($objItem, $arrArreglo[$arrItem["key"]], $boolMayUseAtributes);
			}
			else {
				$objItem = &$objXML->children[$objXML->addChild($arrItem["key"])];
				webservice_arrayIntoXML($objItem, $arrArreglo[$arrItem["key"]], $boolMayUseAtributes);
			}
		}
		else {
			// Si no es un array, lo pongo como atributo o texto segun $boolMayUseAtributes
			if ($boolMayUseAtributes) {
				$strKeyName = (is_numeric($arrItem["key"]))?"item_{$arrItem["key"]}":$arrItem["key"];

				$objXML->addAttribute($strKeyName, $arrItem["value"]);
			}
			else {
				if (is_numeric($arrItem["key"])) {
					// Si el key es numerico, pongo el nodo como "item"
					$objItem = &$objXML->children[$objXML->addChild("item")];
					$objKey = &$objItem->children[$objItem->addChild("nodekey")];
					$objKey->setInternalText($arrItem["key"]);

					$objValue = &$objItem->children[$objItem->addChild("value")];
					$objValue->setInternalText($arrItem["value"]);
				}
				else {
					$objItem = &$objXML->children[$objXML->addChild($arrItem["key"])];
					$objItem->setInternalText($arrItem["value"]);
				}
			}
		}        
	}    
	reset($arrArreglo);
}
/**
* Desactiva dispositivos no confirmados por el usuario en su interfaz grafica.
*
*/
function webservice_deactiveNotConfirmedDevices() {
    $strEngine = config();
    if($strEngine == "mysql"){
        webservice_deactiveNotConfirmedDevices_mysql();
    }
    else if($strEngine == "sqlsrv"){
        webservice_deactiveNotConfirmedDevices_sqlsrv();
    }
}

function webservice_deactiveNotConfirmedDevices_mysql(){
    $model = global_model::getInstance();
    // Esto es para que el clear solo corra una vez cada hora
    $strQuery = "SELECT COUNT(lastRun) AS conteo 
                FROM webservices_last_deactivate 
                WHERE lastRun > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    $intRuns = $model->sql_ejecutarKey($strQuery);
    if ($intRuns == 0) {
        $strQuery = "UPDATE webservices_devices WD 
                     SET WD.activo = 'N' 
                     WHERE WD.userid IS NULL";
        $model->sql_ejecutar($strQuery);

        $strQuery = "UPDATE webservices_devices
                     SET activo = 'N', fecha_baja = NOW()
                     WHERE confirmado = 'N' AND
                           fecha_alta < DATE_SUB(NOW(), INTERVAL 12 HOUR)";
        $model->sql_ejecutar($strQuery);

        $model->sql_ejecutar("TRUNCATE webservices_last_deactivate");
        $model->sql_ejecutar("INSERT INTO webservices_last_deactivate (lastRun) VALUES (NOW());");
    }
}

function webservice_deactiveNotConfirmedDevices_sqlsrv(){
    $model = global_model::getInstance();
    // Esto es para que el clear solo corra una vez cada hora
    $strQuery = "SELECT COUNT(lastRun) AS conteo 
                FROM webservices_last_deactivate 
                WHERE lastRun > DATEADD(HH, -1,GETDATE())";
    $intRuns = $model->sql_ejecutarKey($strQuery);

    if ($intRuns == 0) {
        $strQuery = "UPDATE webservices_devices 
                     SET activo = 'N' 
                     WHERE userid IS NULL";
        $model->sql_ejecutar($strQuery);

        $strQuery = "UPDATE webservices_devices
                     SET activo = 'N', fecha_baja = GETDATE()
                     WHERE confirmado = 'N' AND
                           fecha_alta < DATEADD(HH,12,GETDATE())";
        $model->sql_ejecutar($strQuery);

        $model->sql_ejecutar("TRUNCATE TABLE webservices_last_deactivate");
        $model->sql_ejecutar("INSERT INTO webservices_last_deactivate (lastRun) VALUES (GETDATE())");
    }
}

$intGlobalMRID = 0; // ID del log de respuestas

function getFile_contentType($strFileType = ""){
    $ContentType = "";

    if (!empty($strFileType)) {
        switch ($strFileType) {
            case ".asf":
                $ContentType = "video/x-ms-asf";
                break;
            case ".avi":
                $ContentType = "video/avi";
                break;
            case ".doc":
                $ContentType = "application/msword";
                break;
            case ".zip":
                $ContentType = "application/zip";
                break;
            case ".xls":
                $ContentType = "application/vnd.ms-excel";
                break;
            case ".gif":
                $ContentType = "image/gif";
                break;
            case ".jpg":
                $ContentType = "image/jpeg";
                break;
            case "jpeg":
                $ContentType = "image/jpeg";
                break;
            case ".wav":
                $ContentType = "audio/wav";
                break;
            case ".mp3":
                $ContentType = "audio/mpeg3";
                break;
            case ".mpg":
                $ContentType = "video/mpeg";
                break;
            case "mpeg":
                $ContentType = "video/mpeg";
                break;
            case ".rtf":
                $ContentType = "application/rtf";
                break;
            case ".htm":
                $ContentType = "text/html";
                break;
            case "html":
                $ContentType = "text/html";
                break;
            case ".xml":
                $ContentType = "text/xml";
                break;
            case ".xsl":
                $ContentType = "text/xsl";
                break;
            case ".css":
                $ContentType = "text/css";
                break;
            case ".csv":
                $ContentType = "text/csv";
                break;
            case ".txt":
                $ContentType = "text/txt";
                break;
            case ".php":
                $ContentType = "text/php";
                break;
            case ".asp":
                $ContentType = "text/asp";
                break;
            case ".pdf":
                $ContentType = "application/pdf";
                break;
            case "docx":
                $ContentType = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
                break;
            case "dotx":
                $ContentType = "application/vnd.openxmlformats-officedocument.wordprocessingml.template";
                break;
            case "pptx":
                $ContentType = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
                break;
            case "ppsx":
                $ContentType = "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
                break;
            case "potx":
                $ContentType = "application/vnd.openxmlformats-officedocument.presentationml.template";
                break;
            case "xlsx":
                $ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                break;
            case "xltx":
                $ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.template";
                break;
        }
    }
    else {
        $ContentType = "application/octet-stream";
    }
    return $ContentType;
}

/**
* Verifica si el AIM ya tiene respusta para el dispositivo.
* Si hay respuesta, la devuelve.
* Si no, inicializa el registro para guardar la respuesta del webservice a un dispositivo movil.  Tambien mantiene la tabla solo con las respuestas de los ultimos 3 días.
*
* @param integer $intDeviceID ID del dispositivo
* @param string $strDeviceAIM AIM enviado por el dispositivo
*/
function webservice_check_saved_response($intDeviceID, $strDeviceAIM, $strFormato) {
	global $intGlobalPageProcessedLogID, $intGlobalMRID;
    $intGlobalPageProcessedLogID = 1;
    $model = global_model::getInstance();
	//Housekeeping
	$intRows = $model->sql_ejecutarKey("SELECT COUNT(table_name) FROM catalogos_last_update WHERE table_name = 'webservices_mobile_responses' AND fecha = curdate()");
    if ($intRows == 0) {
        // Si NO hay corrido hoy... lo corro
        $model->sql_ejecutar("REPLACE INTO catalogos_last_update VALUES ('webservices_mobile_responses', curdate(), curtime())");

        $model->sql_ejecutar("DELETE FROM webservices_mobile_responses WHERE fecha < DATE_SUB(NOW(), INTERVAL 3 DAY)"); // No necesita optimize porque es InnoDB
    }

    $intDeviceID = intval($intDeviceID);
    $strDeviceAIM = $model->sql_real_escape_string($strDeviceAIM);

    // Busco si ya hay respuesta
    $arrResponse = $model->sql_ejecutarKey("SELECT id, status, respuesta FROM webservices_mobile_responses WHERE device_id = {$intDeviceID} AND device_aim = '{$strDeviceAIM}'");
    if ($arrResponse === false) {
		$model->sql_ejecutar("INSERT INTO webservices_mobile_responses
				  (userid, device_id, device_aim, process_log_id, status, fecha, hora, formato)
				  VALUES
				  ('{$_SESSION["motu"]["uid"]}', {$intDeviceID}, '{$strDeviceAIM}', {$intGlobalPageProcessedLogID}, 'en_proceso', curdate(), curtime(), '{$strFormato}')");
		$intGlobalMRID = $model->sql_lastID();

		return $intGlobalMRID;
    }
	else {
		if ($arrResponse["status"] == "terminada") {
			switch ($strFormato) {
				case "csv":
					$strContentType = getFile_contentType(".csv");
					break;
				case "xmlwa":
				case "xmlno":
				case "xmlc":
					$strContentType = getFile_contentType(".xml");
					break;
				case "json":
					$strContentType = "application/json";
                    //Esto se coloco porque en el aplicaciones moviles no reconocia la respuesta como un JSON, si no como STRING
                    $arrResponse["respuesta"] = json_decode($arrResponse["respuesta"]);
                    $arrResponse["respuesta"] = json_encode($arrResponse["respuesta"]);
					break;
				case "txt":
					$strContentType = getFile_contentType(".txt");
					break;
				case "html":
					$strContentType = getFile_contentType("html");
					break;
				case "bin":
					$strContentType = getFile_contentType();
					break;
			}

			header("Content-Type: {$strContentType}");
			print $arrResponse["respuesta"];

			$intGlobalMRID = 0;

			return "stop";
		}
		else {
			// Si no ha terminado... no devuelvo nada para que asuma que no hay respuesta y vuelva a intentar esperando a que termine el proceso anterior...
			$intGlobalMRID = 0;

			return "stop";
		}
	}
}

/**
* Guarda la respuesta a un dispositivo movil para referencia futura.
*
* @param string $strResponse Respuesta
*/
function webservice_save_response($strResponse) {
	global $intGlobalMRID;
    $model = global_model::getInstance();
	if ($intGlobalMRID == 0) return;

	$strResponse = $model->sql_real_escape_string($strResponse);
	$model->sql_ejecutar("UPDATE webservices_mobile_responses SET respuesta = '{$strResponse}', status = 'terminada' WHERE id = {$intGlobalMRID}");
}




