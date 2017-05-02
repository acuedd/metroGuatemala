<?php
use kernel\Controller\debug;
use kernel\Controller\error;
use kernel\Controller\global_controller;
use kernel\Controller\response_webservice;
use kernel\Model\global_model;
use login\model\login_model;

require_once("kernel/global_controller.php");
include_once("modules/login/login_model.php");

abstract class webservices_baseClass extends global_controller{
    //***********************************************************************
	//***** Variables para errores y debug y sus funciones
	//***********************************************************************
   
    protected $error;
    protected $debug;
    /**
	 * the debug level for this instance
	 *
	 * @var    integer
	 * @access private
	 */
	private $debugLevel = 0;

    protected $strEngine = "";
       
    public function appendError($str){
        $this->error->addError($str);
    }
    public function getError($strMode = "string"){
        return $this->error->getErrors($strMode);
    }    
    /**
	* Define el nivel de debug
	*
	* @param    int    $intLevel    Debug level 0-9, where 0 turns off
	* @access    public
	*/
	public function setDebugLevel($intLevel) {
		$this->debugLevel = $intLevel;
	}
	/**
	* gets the debug level for this instance
	*
	* @return    int    Debug level 0-9, where 0 turns off
	* @access    public
	*/
	public function getDebugLevel() {
		return $this->debugLevel;
	}
    /**
	* Agrega una linea de Debug
	*
	* @param    string $strDebugString debug data
	* @access   protected
	*/
	protected function appendDebug($strDebugString){
        if ($this->debugLevel > 0) {
            $this->debug->addDebug($strDebugString);
        }
    }
    /**
	* Limpia la info de debug
	*
	* @access   public
	*/
	public function clearDebug() {
        $this->debug->clearDebug();
    }
    /**
	* gets the current debug data for this instance
	*
	* @return   string debug data
	* @access   public
	*/
	public function getDebug() {
        $this->debug->getDebug();
    }
    //***********************************************************************
	//***** Variables para operacion y sus funciones
	//***********************************************************************
    /**
	* Usuario que solicita el servicio, se registra ya sea con el device id o segun la sesion que ya debiera estar activa
	*
	* @var integer
	*/
	protected $intUserID = 0;

	/**
	* Dispositivo que solicita el servicio, se registra con el device id
	*
	* @var integer
	*/
	protected $intDeviceID = 0;

	/**
	* Devuelve el deviceID
	*
	* @return integer device id
	* @access public
	*
	*/
	public function getDeviceID() {
		return $this->intDeviceID;
	}
    /**
    * Devuelve el UserID
    * @return integer useriID
    * @access public
    */
    public function getUserID(){
        return $this->intUserID;
    } 

	/**
	* Para guardar el UUID de la operacion a realizar con esta instancia, solo por referencia
	*
	* @var string
	* @access private
	*/
	private $strOperacion_Codigo = false;
    /**
	* Setea el código de la operacion
	*
	* @param string $strCodigo
	* @access protected
	*/
	protected function setCodigoOperacion($strCodigo) {
		$this->strOperacion_Codigo = $strCodigo;
	}
	/**
	* Devuelve el código de la operacion
	*
	* @return string código de operacion
	* @access public
	*
	*/
	public function getCodigoOperacion() {
		return $this->strOperacion_Codigo;
	}
    /**
	* Para guardar la descripcion de la operacion a realizar, algo que sea entendible por un humano para no tener
	* que saber el código de la operacion
	*
	* @var string
	* @access private
	*/
	private $strOperacion_Descripcion = "";

	/**
	* Setea la descripcion de la operacion
	*
	* @param string $strDescripcion
	* @access protected
	*/
	protected function setDescripcionOperacion($strDescripcion) {
		$this->strOperacion_Descripcion = $strDescripcion;
	}
	/**
	* Devuelve la descripcion de la operacion
	*
	* @return string descripcion de operacion
	* @access public
	*
	*/
	public function getDescripcionOperacion() {
		return $this->strOperacion_Descripcion;
	}
    /**
	* Para guardar la descripcion de la operacion a realizar, algo que sea entendible por un humano para no tener
	* que saber el código de la operacion
	*
	* @var string
	* @access private
	*/
	private $strOperacion_Acceso = "";

	/**
	* Setea el acceso de la operacion
	*
	* @param string $strAcceso
	* @access protected
	*/
	protected function setAccesoOperacion($strAcceso) {
		$this->strOperacion_Acceso = $strAcceso;
	}
	/**
	* Devuelve el acceso de la operacion
	*
	* @return string acceso de operacion
	* @access public
	*
	*/
	public function getAccesoOperacion() {
		return $this->strOperacion_Acceso;
	}
    /**
	* Formatos permitidos, la baseclass tiene todos pero cada subclase debe limitar sus formatos
	* "csv", "xmlwa", "xmlno", "json", "txt", "html", "bin", "xmlc"
	*
	* @var array
	*/
	private $arrFormatosPermitidos = array();

	/**
	* Setea los formatos permitidos para la operacion.  Los modos validos son:  csv, xmlwa, xmlno, json, txt, html, bin, xmlc
	*
	* @param array $arrFormatos
	*/
	protected function setFormatosPermitidos($arrFormatos) {
		$arrValid = array("csv", "xmlwa", "xmlno", "json", "txt", "html", "bin", "xmlc");
		$this->arrFormatosPermitidos = array_intersect($arrValid, $arrFormatos);
	}
	/**
	* Devuelve los formatos de respuesta permitidos
	*
	*/
	public function getFormatosPermitidos() {
		return $this->arrFormatosPermitidos;
	}

	/**
	* Formato para la salida de informacion (respuesta), puede ser:
	* csv: csv
	* xmlwa: XML con atributos
	* xmlno: XML solo con nodos
	* json: JSon
	* txt: Texto
	* html: Html
	* bin: Binario
	* xmlc: Contenido textual, html o binario ordenado en nodos de XML
	*
	* @var string
	* @access private
	*/
	private $strFormatoRespuesta = false;

	/**
	* Define el formato para la salida de informacion (respuesta), puede ser:
	* csv: csv
	* xmlwa: XML con atributos
	* xmlno: XML solo con nodos
	* json: JSon
	* txt: Texto
	* html: Html
	* bin: Binario
	* xmlc: Contenido textual, html o binario ordenado en nodos de XML
	*
	* @param string $strFormat csv, xmlwa, xmlno, json, txt, html, bin, xmlc
	* @access public
	* @return boolean Formato valido
	*/
	public function setFormatoRespuesta($strFormato) {
		$arrFormatosTmp = $this->getFormatosPermitidos();
		if (in_array($strFormato, $arrFormatosTmp)) {
			$this->strFormatoRespuesta = $strFormato;
			return true;
		}
		else {
			$this->strFormatoRespuesta = false;
			$this->appendError("Formato de respuesta inválido.");
			$this->appendDebug("Formato de respuesta inválido, solo se soportan: ". implode(", ", $arrFormatosTmp));
			return false;
		}
	}

	/**
	* Devuelve el formato de respuesta segun se configuro
	*
	*/
	public function getFormatoRespuesta() {
		return $this->strFormatoRespuesta;
	}

	/**
	* Modos permitidos, la baseclass tiene todos pero cada subclase debe limitar sus modos.
	* El baseclass lo trae vacio para obligar al desarrollador a definir los suyos en cada operacion.
	* "am", "wm", "w"
	*
	* @var array
	*/
	private $arrModosPermitidos = array();

	/**
	* Setea los modos permitidos para la operacion.  Los modos validos son:  am, wm y w.
	*
	* @param array $arrModos
	*/
	protected function setModosPermitidos($arrModos) {
		$arrValid = array("am", "wm", "w");
		$this->arrModosPermitidos = array_intersect($arrValid, $arrModos);
	}

	/**
	* Devuelve los modos de operacion permitidos
	*
	*/
	public function getModosPermitidos() {
		return $this->arrModosPermitidos;
	}

	/**
	* Modo de operación, am = aplicacion movil, wm = website movil, w = website.
	*
	* @var string
	* @access private
	*/
	private $strModoOperacion = false;

	/**
	* Setea el modo de operacion (am = aplicacion movil, wm = website movil, w = website)
	* Esto tiene que ver con el código de seguridad, si es am es el UUID del dispositivo,
	* si es wm o w, sera el sessionID, asumiendo que ya hizo login antes...
	*
	* @param string $strModo
	* @access public
	* @return boolean Modo valido o invalido
	*/
	public function setModoOperacion($strModo) {		
        $arrTMPModes = $this->getModosPermitidos();
        if (in_array($strModo, $arrTMPModes)) {
            $this->strModoOperacion = $strModo;
            return true;
        }
        else {
            $this->strModoOperacion = false;
            $this->appendError("Modo de operacion inválido.");
            $this->appendDebug("Modo de operacion inválido, solo se soportan: " . implode(", ", $arrTMPModes));
            return false;
        }
	}
	/**
	* Devuelve el modo de operacion
	*
	* @return string modo de operacion
	* @access public
	*
	*/
	public function getModoOperacion() {
		return $this->strModoOperacion;
	}

	/**
	* String para validar seguridad.  Si es am es el UUID del dispositivo, si es wm o w, sera el sessionID, asumiendo que ya hizo login antes...
	*
	* @var string
	* @access = private
	*/
	private $strCodigoSeguridad = "";

	/**
	* Setea el código de seguridad, depende del modo de operacion.
	* Si es am es el UUID del dispositivo, si es wm o w, sera el sessionID,
	* asumiendo que ya hizo login antes...
	*
	* @param string $strCodigo
	* @access public
	*/
	public function setCodigoSeguridad($strCodigo) {
		$this->strCodigoSeguridad = $strCodigo;
	}
	/**
	* Devuelve el código de seguridad
	*
	* @return string código de seguridad
	* @access public
	*
	*/
	public function getCodigoSeguridad() {
		return $this->strCodigoSeguridad;
	}
    // Cache
	/**
	* Indica si se usa cache de contenido o no, para evitar trafico por gusto
	*
	* @var boolean
	* @access = private
	*/
	private $boolUseContentCache = false;
	/**
	* Para setear la variable $boolUseContentCache
	*
	* @param bool $boolUse
	* @access protected
	*/
	public function setUseContentCache($boolUse) {
		$this->boolUseContentCache = $boolUse;
	}
	/**
	* Devuelve la variable $boolUseContentCache
	*
	* @return bool Se usa cache o no
	* @access protected
	*
	*/
	public function getUseContentCache() {
		return $this->boolUseContentCache;
	}
	/**
	* Indica la cantidad de horas que el contenido se mantiene en cache, solo valido si boolUseContentCache es true
	*
	* @var integer
	* @access = private
	*/
	private $intHorasEnCache = 0;

	/**
	* Para setear la variable $intHorasEnCache
	*
	* @param integer $intHorasEnCache
	* @access protected
	*/
	public function setHorasEnCache($intHorasEnCache) {
		$this->intHorasEnCache = $intHorasEnCache;
	}
	/**
	* Devuelve la variable $intHorasEnCache
	*
	* @return integer
	* @access protected
	*
	*/
	public function getHorasEnCache() {
		return $this->intHorasEnCache;
	}

	/**
	* Indica la cantidad de horas que el contenido se mantiene en cache, solo valido si boolUseContentCache es true
	*
	* @var integer
	* @access = private
	*/
	private $intLastModified = 0;

	/**
	* Para setear la variable $intLastModified
	*
	* @param integer $intLastModified
	* @access protected
	*/
	public function setLastModified($intLastModified) {
		$this->intLastModified = $intLastModified;
	}
	/**
	* Devuelve la variable $intLastModified
	*
	* @return integer
	* @access protected
	*
	*/
	public function getLastModified() {
		return $this->intLastModified;
	}

	/**
	* String definir el tipo de cache a usar
	*
	* @var string
	* @access = private
	*/
	private $strCachePragma = "private";

	/**
	* Para setear la variable $strCachePragma
	*
	* @param string $strCachePragma
	* @access public
	*/
	public function setCachePragma($strCachePragma) {
		$this->strCachePragma = $strCachePragma;
	}
	/**
	* Devuelve la variable $strCachePragma
	*
	* @return string código de seguridad
	* @access public
	*
	*/
	public function getCachePragma() {
		return $this->strCachePragma;
	}

	/**
	* Arreglo para almacenar los datos ordenados que se devuelven al cliente.
	* Sirve para los formatos csv, xmlwa, xmlno, json.
	*
	* NO tiene funcion SET ni GET
	*
	* @var array
	* @access protected
	*/
	protected $arrDataOutput = array();

	/**
	* Arreglo para almacenar el string a devolver
	* Sirve para los formatos txt, html, bin
	*
	* NO tiene funcion SET ni GET
	*
	* @var string
	* @access protected
	*/
	protected $strContentOutput = "";

	/**
	* Arreglo para almacenar el XML con contenido a devolver
	* Sirve para los formatos xmlc
	*
	* NO tiene funcion SET ni GET
	*
	* @var objeto XML - hacer include de libreria XML en sub clase
	* @access protected
	*/
	protected $objXMLOutput = false;
    
    //***********************************************************************
	//***** Funciones de la clase en general, no se repiten en las subclases
	//***********************************************************************
    
    /**
	* Constructor - OJO, este va a correr cuando YA se que operacion voy a ejecutar porque ya elegi de una u otra forma
	* la clase a incluir, por eso el código ya debe venir validado desde afuera
	*
	* @param mixed $strCodigoOperacion
	* @param mixed $arrInfoOperacion array con informacion de la operacion que se obtiene de webservice_getOperationInfo
	*/
    public function __construct($classRef, $strCodigoOperacion, $arrInfoOperacion) {
        $this->error = new error();
        $this->debug = new debug($classRef);
        $this->setCodigoOperacion($strCodigoOperacion);
		$this->setDescripcionOperacion($arrInfoOperacion["descripcion"]);
		$this->setAccesoOperacion($arrInfoOperacion["acceso"]);
        $this->strEngine = config();
    }
    function __destruct() {
        $login = login_model::getInstance();
		if ($this->getModoOperacion() == "am") {
			$login->limpiar_session();
		}
	}

    //***********************************************************************
	//***** Funciones para hacer overrides en las sub-clases
	//***********************************************************************
    
    /**
	* Valida el modo de operacion y el código de seguridad...
	*
	* @param string $strCodigoSeguridad
	*/
	public function boolValidarCodigo($strCodigoSeguridad) {
		$model = global_model::getInstance();
        $login = login_model::getInstance();
        $this->setCodigoSeguridad($strCodigoSeguridad);

		/*
		si es am es el UUID del dispositivo,
		si es wm o w, sera el sessionID, asumiendo que ya hizo login antes...
		*/;
		if ($this->getModoOperacion() == "am") {
				webservice_deactiveNotConfirmedDevices();

				$strCodigoSeguridad_E = $model->sql_real_escape_string($strCodigoSeguridad);                
				$strQuery = "SELECT id, userid
							 FROM webservices_devices
							 WHERE activo = 'Y' AND device_udid = '{$strCodigoSeguridad_E}'";              
				$arrDeviceInfo = $model->sql_ejecutarKey($strQuery);
				$intUserID = false;
				if ($arrDeviceInfo !== false) {
					$intUserID = $arrDeviceInfo["userid"];
                    $strQuery = "SELECT userid FROM main_user  
                                WHERE userid = {$intUserID} 
                                        AND active = 'Y'";
					$intUserID = $model->sql_ejecutarKey($strQuery);
				}

				if ($intUserID === false) {
					$this->appendError("Código de seguridad inválido o dispositivo desactivado");
					return false;
				}
				else {
					$this->intUserID = $intUserID;
					$this->intDeviceID = $arrDeviceInfo["id"];

					$login->llenar_session($intUserID);                    
                    /*Fin override de settings de usuario*/                                                                      
					$model->sql_ejecutar("UPDATE webservices_devices SET last_use = NOW(), uses = uses + 1 WHERE id = {$arrDeviceInfo["id"]}");
					return true;
				}			
		}
		else {
			// si es wm o w, tendria que estar ya logineado...
			// Esta parte confia en mini_main.php
			$intUserID = (isset($_SESSION["motu"]["logged"]) && $_SESSION["motu"]["logged"])?$_SESSION["motu"]["uid"]:0;

			if (!$intUserID || $strCodigoSeguridad != session_id()) {
				$this->appendError("Usuario no registrado");
				return false;
			}
			else {
				$this->intUserID = $intUserID;
				return true;
			}
		}
	}

    /**
	* Valida que el usuario tenga acceso a la operacion
	*
	*/
	public function boolValidarAcceso() {
		//aca no hay validación de accesos aun
        return true;
	}
	/**
	* Guarda los parametros en la variable interna.
	* El override debiera llamar a esta y adicionalmente recorrer los parametros para setearlos en variables
	* internas de la sub clase.
	*
	* @param mixed $arrParametros
	*/
	public function setParametros($arrParametros) {
		$this->appendError("DEV - Falta definir funcion setParametros para validar que se envien los parametros en la subclase.");
		$this->appendDebug("DEV - Falta definir funcion setParametros para validar que se envien los parametros en la subclase.");

		return false;
	}

    /**
	* Esta funcion devuelve el mensaje de Modo de operación inválido, esta funcion prepara la data para enviarla ya sea desde array o desde contenido.  Si la pedian como xmlc lo tiro como xmlno
	*
	*/
	public function darRespuestaInvalido($arr = array()) {
		// Pongo los datos en el array de datos
		$this->arrDataOutput = array();
        $arr["error"] = $this->error->getErrorCodes();
        $this->arrDataOutput = response_webservice::response(0,$this->error->getErrors("string"),$arr,false);
        
		// Pongo los datos en el string de contenido
		$this->strContentOutput = $this->error->getErrors("string");

		if ($this->getFormatoRespuesta() == "xmlc") $this->setFormatoRespuesta("xmlno");

		self::darRespuesta();
	}
    
	/**
	* Funcion que devuelve los formatos validos en una consulta al webservice como tal. Respeta los formatos.
	*
	*/
	public function darRespuestaFormatosValidos() {
		$this->arrDataOutput = $this->getFormatosPermitidos();
		self::darRespuesta();
	}
    	/**
	* Funcion que devuelve los modos permitidos en una consulta al webservice como tal. Respeta los formatos.
	*
	*/
	public function darRespuestaModosValidos() {
		$this->arrDataOutput = $this->getModosPermitidos();
		self::darRespuesta();
	}
	/**
	* Devuelve la respuesta al cliente
	*
	*/
	public function darRespuesta() {
		switch ($this->getFormatoRespuesta()) {
			case "csv":
				$this->darRespuesta_csv();
				break;
			case "xmlwa":
			case "xmlno":
				$this->darRespuesta_xml();
				break;
			case "xmlc":
				$this->darRespuesta_xmlc();
				break;
			case "json":
				$this->darRespuesta_json();
				break;
			case "txt":
			case "html":
			case "bin":
				$this->darRespuesta_content();
				break;
		}
	}
    /**
	* Devuelve la respuesta en CSV.  Esto asume:
	* - Que la primera dimension del array arrDataOutput son las FILAS del archivo y que la segunda dimension con los textos de las posiciones.
	* - Que todas las posiciones vienen aunque sea con empty.
	*
	*/
	private function darRespuesta_csv() {
		if (is_array($this->arrDataOutput) && count($this->arrDataOutput)) {
			$strContentType = getFile_contentType(".csv");
			if ($this->getUseContentCache()) {
				global_function::getCacheHeaders($this->getHorasEnCache(), $this->getLastModified(), $strContentType, $this->getCachePragma());
			}
			else {
				header("Content-Type: {$strContentType}");
			}

			reset($this->arrDataOutput);
			while ($arrRow = each($this->arrDataOutput)) {
				if (is_array($arrRow["value"])) {
					print global_function::CSV_prepararLinea($arrRow["value"]);
				}
				else {
					print $arrRow["value"]."\n";
				}
			}
			reset($this->arrDataOutput);
		}
		else {
			$this->appendError("No están listos los datos de respuesta para el formato seleccionado.");
			$this->darRespuestaInvalido();
		}
	}
    /**
	* Devuelve la respuesta en XML con atributos, segun el formato lo manda con o sin atributos.  Esto asume:
	* - Que el contenido de los atributos o los nodos siempre sera un texto o numero corto, no puede ser un objeto.
	* - Puede ser un array multidimensional
	*/
	private function darRespuesta_xml() {
        require_once 'librarys/php/xml/xmlfunctions.php';        
        
		if (is_array($this->arrDataOutput) && count($this->arrDataOutput)) {
			$strContentType = getFile_contentType(".xml");
			if ($this->getUseContentCache()) {
				global_function::getCacheHeaders($this->getHorasEnCache(), $this->getLastModified(), $strContentType, $this->getCachePragma());
			}
			else {
				header("Content-Type: {$strContentType}");
			}

			print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";

			$objXML = new XMLNode("return");            
			webservice_arrayIntoXML($objXML, $this->arrDataOutput, ($this->getFormatoRespuesta() == "xmlwa"));
			print $objXML->toString(true);
		}
		else {
			$this->appendError("No están listos los datos de respuesta para el formato seleccionado.");
			$this->darRespuestaInvalido();
		}
	}
    /**
	* Devuelve el contenido del objeto objXMLOutput, asume que ya tiene datos listos.
	*
	*/
	private function darRespuesta_xmlc() {
		require_once 'librarys/php/xml/xmlfunctions.php';

		if (is_object($this->objXMLOutput) && get_class($this->objXMLOutput) == "XMLNode") {
			// Si la variable objXMLOutput es un objeto de la clase XMLNode, devuelvo el XML
			$strContentType = getFile_contentType(".xml");

			if ($this->getUseContentCache()) {
				global_function::getCacheHeaders($this->getHorasEnCache(), $this->getLastModified(), $strContentType, $this->getCachePragma());
			}
			else {
				header("Content-Type: {$strContentType}");
			}

			print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
			print $this->objXMLOutput->toString(true);
		}
		else {
			// Si no, devuelvo "formato invalido"
			$this->appendError("No están listos los datos de respuesta para el formato seleccionado.");
			$this->appendDebug("La variable objXMLOutput no es un objeto válido");
			$this->darRespuestaInvalido();
		}
	}
    /**
	* Devuelve la respuesta en formato json
	*
	*/
	private function darRespuesta_json() {
		if (is_array($this->arrDataOutput) && count($this->arrDataOutput)) {
			if ($this->getUseContentCache()) {
				global_function::getCacheHeaders($this->getHorasEnCache(), $this->getLastModified(), "application/json", $this->getCachePragma());
			}
			else {
				header('Content-type: application/json');
			}

			$arrResult = $this->arrDataOutput;
			//utf8_encode_array($arrResult); Se comentarea debido a que en la funcion estandar de response webservice se añade esta propiedad
			print json_encode($arrResult);
			$arrResult = false; //Para liberar memoria
		}
		else {
			$this->appendError("No están listos los datos de respuesta para el formato seleccionado.");
			$this->darRespuestaInvalido();
		}
	}
    /**
	* Da respueta tipo contenido, ya sea para txt, html o bin
	*
	*/
	private function darRespuesta_content() {
		switch ($this->getFormatoRespuesta()) {
			case "txt":
				$strContentType = getFile_contentType(".txt");
				$this->strContentOutput = strip_tags($this->strContentOutput);
				break;
			case "html":
				$strContentType = getFile_contentType("html");
				$strContentType .= "; charset=iso-8859-1";
				break;
			case "bin":
				//Este falta definirlo, por el momento lo tiro como application/octet-stream
				$strContentType = getFile_contentType();
				break;
		}

		if (!empty($this->strContentOutput)) {
			if ($this->getUseContentCache()) {
				global_function::getCacheHeaders($this->getHorasEnCache(), $this->getLastModified(), $strContentType, $this->getCachePragma());
			}
			else {
				header("Content-Type: {$strContentType}");
			}
			print $this->strContentOutput;
		}
		else {
			$this->appendError("No están listos los datos de respuesta para el formato seleccionado.");
			$this->darRespuestaInvalido();
		}
	}
    public function check_config_device(){
        $model = global_model::getInstance();
        $boolReturn = true;
        $strQuery = "SELECT modified_config FROM webservices_devices WHERE id = {$this -> getDeviceID()}";
        $boolModified =  $model->sql_ejecutarKey($strQuery);
        if($boolModified == "Y"){
            $this -> appendError("Los parametros han cambiado, por favor cierre sesión e ingrese de nuevo para actualizar los parametros");
            $boolReturn = false;
        }
        return $boolReturn;
    }
}

interface webservices{
    public function darRespuesta();
    public function setArrParam($arrParam);
}
