<?php
/**
 * Created by PhpStorm.
 * User: acuedd
 * Date: 30/08/2016
 * Time: 10:44 AM
 */
namespace kernel\Controller {
    use kernel\Model\global_model;
    use kernel\View\global_view;

    $intGlobalPageProcessedLogID = 0;
    global $intUid;
    include_once("kernel/global_view.php");
    include_once("kernel/global_model.php");
    session_start();

    /**
     * global controller
     * Clases para la gestion del view
     */
    class global_controller
    {

        private static $_instance;

        /* Variable para usar el action en cada controler
         * @param string
         * @access private
         */
        private $strAction;
        private $strTitle;
        private $strNamePage;
        protected $arrParam;
        private $method;
        private $modulo = "";
        private $addon = "";

        //Constructor de la clase
        function __construct($strAction = "")
        {
            //inicializacion de procesos o variables, si se deseara
            $this->setStrAction($strAction);
        }

        /* Evitamos el clonaje del objeto. Patrón Singleton */

        private function __clone()
        {

        }

        /* Función encargada de crear, si es necesario, el objeto. Esta es la función que debemos llamar desde fuera de la clase para instanciar el objeto, y así, poder utilizar sus métodos */

        public static function getInstance($strAction = "")
        {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self($strAction);
            }
            return self::$_instance;
        }

        /**
         * @return mixed
         */
        public function getMethod()
        {
            return $this->method;
        }

        /**
         * @param mixed $method
         */
        public function setMethod($method)
        {
            $this->method = $method;
        }

        public function setArrParam($arrP)
        {
            $this->arrParam = $arrP;
            $this->method = (isset($_SERVER['REQUEST_METHOD'])) ? strtolower($_SERVER['REQUEST_METHOD']) : "";
        }

        public function getArrParams()
        {
            return $this->arrParam;
        }

        public function setModulo($modulo)
        {
            $this->modulo = $modulo;
        }

        public function setAddon($addon)
        {
            $this->addon = $addon;
        }

        public function getModulo()
        {
            return $this->modulo;
        }

        public function getAddon()
        {
            return $this->addon;
        }

        public function getParam($strTerm, $default = "", $boolUTF8 = false)
        {
            return global_function::getParam($this->arrParam, $strTerm, $default, $boolUTF8);
        }

        public function getObjViewScripts()
        {
            $view = global_view::getInstance($this->getStrAction());
            $view->global_scripts();
        }

        //Funciones de variables
        public function setStrAction($string)
        {
            $this->strAction = $string;
        }

        public function getStrAction()
        {
            return $this->strAction;
        }

        public function setStrTitle($string)
        {
            $this->strTitle = $string;
        }

        public function getStrTitle()
        {
            return $this->strTitle;
        }

        /**
         * @return mixed
         */
        public function getStrNamePage()
        {
            return $this->strNamePage;
        }

        /**
         * @param mixed $strNamePage
         */
        public function setStrNamePage($strNamePage)
        {
            $this->strNamePage = $strNamePage;
        }

        /**
         * Metodo imprime el html en la intercase de usuario
         *
         * @param $html codigo html
         */
        private function print_html($html)
        {
            print $html;
        }

        function getAjaxContent($strClass, $strModule = "", $strPageId = "", $strTitle = "", $boolincludeHead = true)
        {
            if ($boolincludeHead)
                global_function::getHeaders("html");
            //header("Content-Type: text/html; charset=UTF-8");
            $strClass = strtolower($strClass);
            $classname = "{$strClass}_controller";
            $strTarget = "modules/{$strModule}/addon/{$strClass}/{$classname}.php";

            $varTMP = $this->validateClass($strTarget, $classname, $strClass, $strModule, $strTitle);
            if (!$varTMP) {
                $strTarget = "modules/{$strModule}/{$classname}.php";
                $varTMP = $this->validateClass($strTarget, $classname, $strClass, $strModule, $strTitle);
                if (!$varTMP) {
                    $objView = global_view::getInstance($this->getStrAction());
                    $objView->fntWindowUnable();
                }
            }
        }

        function validateClass($strTarget, $classname, $strClass, $strModule, $strTitle)
        {
            $boolReturn = false;
            if (file_exists($strTarget)) {
                include_once($strTarget);
                $this->setArrParam($_REQUEST);
                if (!class_exists($classname)) {
                    $boolReturn = false;
                } else {
                    $strAction = "index.php?act=ajax&page={$strClass}&mod={$strModule}"; //basename(__FILE__);
                    $var = new $classname($strAction);
                    $var->setModulo($strModule);
                    $var->setAddon($strClass);
                    $var->arrParam = $this->arrParam;
                    if (isset($this->arrParam["op"]) && method_exists($var, "getOperation")) {
                        global_function::getHeaders("json");
                        $arrReturn = $var->getOperation();
                        print json_encode($arrReturn);
                        die();
                    } else {
                        if (method_exists($var, "run")) {
                            $var->setStrNamePage($this->getParam("name"));
                            $var->run($strTitle);
                            return true;
                        }
                    }
                }
            }
            return $boolReturn;
        }

        /**
         * Elimina los slashes de un user input segun la configuracion de magic_quotes_gpc.  DEBE ser utilizada en TODOS los inputs.
         *
         * @param string $strInput
         * @param boolean $boolUTF8Decode
         * @return string
         */
        public static function user_magic_quotes($strInput, $boolUTF8Decode = false)
        {
            //htmlspecialchars_decode
            //html_entity_decode
            $strInput = trim($strInput);
            if (get_magic_quotes_gpc()) {
                $strInput = stripslashes($strInput);
            }
            //Esto arruina los gets... pero sirve con los posts de ajax...
            if ($boolUTF8Decode && mb_detect_encoding($strInput) == "UTF-8") {
                $strInput = utf8_decode($strInput);
            }
            return $strInput;
        }

        /* Funcion para debuguear
         * @param $ThisVar = variable a debuguear
         * @param $VariableName = etiqueta para el debug
         * @parama $ShowWhat
         */

        public static function drawdebug($ThisVar, $VariableName = "", $ShowWhat = 0, $boolForceShow = false)
        {
            echo "<div style='text-align:left'>";
            $strType = gettype($ThisVar);
            $strPreOpen = "";
            $strPreClose = "";
            if (!is_string($ThisVar)) {
                $strPreOpen = "<pre>";
                $strPreClose = "</pre>";
            }

            echo "\n<hr>";
            if (!empty($VariableName))
                echo "<b><i> $VariableName</b></i> ";
            echo "Var  Type of var = <b>" . $strType . "</b><br><br>\n{$strPreOpen}";
            if ($ShowWhat == 0) {
                if (is_bool($ThisVar))
                    print_r(($ThisVar) ? "true" : "false");
                else
                    print_r($ThisVar);
            } else if ($ShowWhat == 1) {
                print_r(array_values($ThisVar));
            } else if ($ShowWhat == 2) {
                print_r(array_keys($ThisVar));
            }
            print_r("<hr>{$strPreClose}\n");
            echo "</div>";
        }

        public function principal_struct($strTarget = "")
        {
            $view = global_view::getInstance($this->getStrAction());
            $view->drawTema($this->getStrTitle());
        }

        public function get_arrayMenu()
        {
            $model = global_model::getInstance();
            $arrMenu = $model->getArrayMenuForUser();
            return $arrMenu;
        }

        public static function clearTerm($strTMP, $boolUTFDecode = false)
        {
            return global_model::clearTerm($strTMP, $boolUTFDecode);
        }

        public function formatDate($strDate)
        {
            $arrDate = explode("-", $strDate);
            $arrMeses[1] = "enero";
            $arrMeses[2] = "febrero";
            $arrMeses[3] = "marzo";
            $arrMeses[4] = "abril";
            $arrMeses[5] = "mayo";
            $arrMeses[6] = "junio";
            $arrMeses[7] = "julio";
            $arrMeses[8] = "agosto";
            $arrMeses[9] = "septiembre";
            $arrMeses[10] = "octubre";
            $arrMeses[11] = "noviembre";
            $arrMeses[12] = "diciembre";

            $arrDate[1] = intval($arrDate[1]);

            return "{$arrDate[2]} de {$arrMeses[$arrDate[1]]} del {$arrDate[0]}";
        }

        public static function getNotificacion()
        {
            $varM = global_model::getInstance();
            return $varM->getNotification();
        }

    }

    class global_function
    {

        static $MINUTOS_X_HORA = 60;
        static $SEGUNDOS_X_MINUTO = 60;
        static $HORAS_X_DIA = 24;

        public static function myTruncate($string, $limit, $break = ".", $pad = "?")
        {
            // return with no change if string is shorter than $limit
            if (strlen($string) <= $limit)
                return $string;
            // is $break present between $limit and the end of the string?
            if (false !== ($breakpoint = strpos($string, $break, $limit))) {
                if ($breakpoint < strlen($string) - 1) {
                    $string = substr($string, 0, $breakpoint) . $pad;
                }
            }
            return $string;
        }

        public static function formatDate($strDate, $boolIncludeTime = false)
        {
            $arrDate = explode("-", $strDate);
            $arrMeses[1] = "enero";
            $arrMeses[2] = "febrero";
            $arrMeses[3] = "marzo";
            $arrMeses[4] = "abril";
            $arrMeses[5] = "mayo";
            $arrMeses[6] = "junio";
            $arrMeses[7] = "julio";
            $arrMeses[8] = "agosto";
            $arrMeses[9] = "septiembre";
            $arrMeses[10] = "octubre";
            $arrMeses[11] = "noviembre";
            $arrMeses[12] = "diciembre";

            $arrDate[1] = intval($arrDate[1]);

            $arrDateHour = explode(" ", $arrDate[2]);
            $strReturn = "{$arrDateHour[0]} de {$arrMeses[$arrDate[1]]} del {$arrDate[0]}";
            if ($boolIncludeTime) {
                $strReturn .= ", a las {$arrDateHour[1]}.";
            }
            return $strReturn;
        }

        public function getHorarios($sinMin = 0, $sinMax = 0, $boolFormatAMPM = false)
        {
            $sinMax = ($sinMax == 0) ? self::$HORAS_X_DIA : $sinMax;
            $sinMin = str_replace(":", ".", $sinMin);
            $sinMax = str_replace(":", ".", $sinMax);

            $horas = 0;
            $horasMasc = 0;
            $minutos = 0;
            $strMasc = "AM";
            $arrReturn = array();
            if ($sinMin == 0) {
                if ($boolFormatAMPM)
                    $arrReturn["00:00"] = "00:00 {$strMasc}";
                else
                    $arrReturn["00:00"] = "00:00";
            }

            $horasConstante = $sinMax;
            $MinutosConstante = 30;
            $intMinutosXHora = self::$MINUTOS_X_HORA;

            $i = 0;
            $boolContinue = true;
            $boolFormat12 = $boolFormatAMPM;
            while (($boolContinue)) {
                $i++;
                $minutos += $MinutosConstante;
                if ($minutos >= $intMinutosXHora) {
                    $horas++;
                    $horasMasc++;
                    $minutos -= $intMinutosXHora;
                    if ($horas > 12 && ($boolFormat12)) {
                        $boolFormat12 = false;
                        $horasMasc = 1;
                        $strMasc = "PM";
                    }
                    if (($horas >= $horasConstante))
                        break;
                } else {
                    if (($horas >= $horasConstante))
                        break;
                }
                $horasMasc = str_pad($horasMasc, 2, "0", STR_PAD_LEFT);
                $horas = str_pad($horas, 2, "0", STR_PAD_LEFT);
                $minutos = str_pad($minutos, 2, "0", STR_PAD_LEFT);

                if (floatval("{$horas}.{$minutos}") >= floatval($sinMin)) {
                    if ($boolFormatAMPM)
                        $arrReturn["{$horas}:{$minutos}"] = "{$horasMasc}:{$minutos} {$strMasc}";
                    else
                        $arrReturn["{$horas}:{$minutos}"] = "{$horas}:{$minutos}";
                }
            }

            return $arrReturn;
        }

        public static function multiplicaTime($strTime, $intExponte = 1, $boolIncludeSec = true)
        {
            $arrTime = explode(":", $strTime);

            $intHoras = (!empty($arrTime[0])) ? $arrTime[0] : 0;
            $intMinutos = (!empty($arrTime[1])) ? $arrTime[1] : 0;
            $intSegundos = (!empty($arrTime[2])) ? $arrTime[2] : 0;

            $intHoras = ($intHoras * $intExponte);
            $intMinutos = ($intMinutos * $intExponte);
            $intSegundos = ($intSegundos * $intExponte);

            if ($intSegundos >= self::$SEGUNDOS_X_MINUTO) {
                $boolOK = true;
                while ($boolOK) {
                    if ($intSegundos < self::$SEGUNDOS_X_MINUTO)
                        break;
                    $intMinutos++;
                    $intSegundos -= self::$SEGUNDOS_X_MINUTO;
                }
            }
            if ($intMinutos >= self::$MINUTOS_X_HORA) {
                $boolOK = true;
                while ($boolOK) {
                    if ($intMinutos < self::$MINUTOS_X_HORA)
                        break;
                    $intHoras++;
                    $intMinutos -= self::$MINUTOS_X_HORA;
                }
            }
            $intHoras = str_pad($intHoras, 2, "0", STR_PAD_LEFT);
            $intMinutos = str_pad($intMinutos, 2, "0", STR_PAD_LEFT);
            $intSegundos = str_pad($intSegundos, 2, "0", STR_PAD_LEFT);
            $strReturn = "{$intHoras}:{$intMinutos}";
            if ($boolIncludeSec)
                $strReturn .= ":{$intSegundos}";

            return $strReturn;
        }

        public static function calculo_dif_fechas($dia1, $dia2, $mes1, $mes2, $ano1, $ano2)
        {
            $timestamp1 = mktime(0, 0, 0, $mes1, $dia1, $ano1);
            $timestamp2 = mktime(4, 12, 0, $mes2, $dia2, $ano2);

            $segundos_diferencia = $timestamp1 - $timestamp2;
            $dias_diferencia = $segundos_diferencia / (60 * 60 * 24);
            $dias_diferencia = $dias_diferencia;
            $dias_diferencia = floor($dias_diferencia) + 1;
            return $dias_diferencia;
        }

        public static function calcular_tiempo_trasnc($hora1, $hora2)
        {
            $separar[1] = explode(':', $hora1);
            $separar[2] = explode(':', $hora2);

            $total_minutos_trasncurridos[1] = ($separar[1][0] * 60) + $separar[1][1];
            $total_minutos_trasncurridos[2] = ($separar[2][0] * 60) + $separar[2][1];
            $total_minutos_trasncurridos = $total_minutos_trasncurridos[1] - $total_minutos_trasncurridos[2];
            if ($total_minutos_trasncurridos <= 59)
                return ($total_minutos_trasncurridos . ' Minutos');
            elseif ($total_minutos_trasncurridos > 59) {
                $HORA_TRANSCURRIDA = round($total_minutos_trasncurridos / 60);
                if ($HORA_TRANSCURRIDA <= 9)
                    $HORA_TRANSCURRIDA = '0' . $HORA_TRANSCURRIDA;
                $MINUITOS_TRANSCURRIDOS = $total_minutos_trasncurridos % 60;
                if ($MINUITOS_TRANSCURRIDOS <= 9)
                    $MINUITOS_TRANSCURRIDOS = '0' . $MINUITOS_TRANSCURRIDOS;
                return ($HORA_TRANSCURRIDA . ':' . $MINUITOS_TRANSCURRIDOS);
            }
        }

        /**
         * Elimina los slashes de un user input segun la configuracion de magic_quotes_gpc.  DEBE ser utilizada en TODOS los inputs.
         *
         * @param string $strInput
         * @param boolean $boolUTF8Decode
         * @return string
         */
        public static function user_magic_quotes($strInput, $boolUTF8Decode = false)
        {

            $strInput = trim($strInput);
            if (get_magic_quotes_gpc()) {
                $strInput = stripslashes($strInput);
            }
            /*Esto arruina los gets... pero sirve con los posts de ajax...*/
            if ($boolUTF8Decode && mb_detect_encoding($strInput) == "UTF-8") {
                $strInput = utf8_decode($strInput);
            }
            return $strInput;
        }

        /**
         * Encodea con utf8 los strings dentro de un array, ojo que es recursivo.
         *
         * @param array $arrToEncode
         * @return boolean
         */
        public static function utf8_encode_array(&$arrToEncode)
        {
            reset($arrToEncode);
            while ($arrItem = each($arrToEncode)) {
                if (is_array($arrItem["value"]) || is_object($arrItem["value"])) {
                    $arrItem["value"] = false; //Para liberar memoria
                    if (is_object($arrToEncode))
                        self::utf8_encode_array($arrToEncode->$arrItem["key"]);
                    else
                        self::utf8_encode_array($arrToEncode[$arrItem["key"]]);
                } else if (is_string($arrItem["value"])) {
                    if (is_object($arrToEncode))
                        $arrToEncode->$arrItem["key"] = utf8_encode($arrItem["value"]);
                    else
                        $arrToEncode[$arrItem["key"]] = utf8_encode($arrItem["value"]);
                    $arrItem["value"] = false; //Para liberar memoria
                } else {
                    /* No hago nada porque voy a devolver el mismo array para ahorrar memoria */
                }
            }
            reset($arrToEncode);
            return true;
        }

        /**
         * Para des encodear de utf8 los strings dentro de un array, ojo que es recursiva
         *
         * @param array $arrToDecode
         */
        public static function utf8_decode_array(&$arrToDecode)
        {
            reset($arrToDecode);
            while ($arrItem = each($arrToDecode)) {
                if (is_array($arrItem["value"]) || is_object($arrItem["value"])) {
                    $arrItem["value"] = false; //Para liberar memoria
                    if (is_object($arrToDecode))
                        utf8_decode_array($arrToDecode->$arrItem["key"]);
                    else
                        utf8_decode_array($arrToDecode[$arrItem["key"]]);
                } else if (is_string($arrItem["value"])) {
                    if (is_object($arrToDecode))
                        $arrToDecode->$arrItem["key"] = utf8_decode($arrItem["value"]);
                    else
                        $arrToDecode[$arrItem["key"]] = utf8_decode($arrItem["value"]);
                    $arrItem["value"] = false; //Para liberar memoria
                } else {
                    /*No hago nada porque voy a devolver el mismo array para ahorrar memoria
                    */
                }
            }
            reset($arrToDecode);
            return true;
        }

        public static function CSV_prepararLinea($arrCampos, $boolAgregarNewLine = true)
        {
            // El delimitador es ","
            // Si el texto tiene "," el string va delimitado por "
            // Las comillas van dobles "" => "
            $arrLinea = array();
            while ($arrItem = each($arrCampos)) {
                $strTMP = $arrItem["value"];
                // Le doy escape a las "
                $strTMP = str_replace("\"", "\"\"", $strTMP);
                if (strstr($strTMP, ",") !== false || strstr($strTMP, "\"") !== false) {
                    // Si tiene coma o comillas, lo meto entre comillas...
                    $strTMP = "\"{$strTMP}\"";
                }
                $arrLinea[] = $strTMP;
            }

            $strTMP = implode(";", $arrLinea);

            //Algunos estandares dicen que debiera terminar con CRLF (\r\n)... pendiente de confirmar pues no hay un estandar formal
            if ($boolAgregarNewLine)
                $strTMP .= "\n";
            return $strTMP;
        }

        function getCacheHeaders($intHoras, $intLastModified, $strContentType, $strPragma = "private")
        {
            header("Pragma: {$strPragma}");

            $expires = floor(60 * 60 * $intHoras); // El tiempo de expiracion en segundos

            if ($intLastModified > 0) {
                $arrApacheHeaders = apache_request_headers();
                if (isset($arrApacheHeaders["If-Modified-Since"])) {
                    // Si esta el parametro If-Modified-Since es porque estoy validando una fecha
                    $intIfModifiedSince = strtotime($arrApacheHeaders["If-Modified-Since"]); //Combierto esta fecha a timestamp
                    if ($intIfModifiedSince >= $intLastModified) {
                        // Si el cache tiene un archivo que no ha cambiado segun el parametro de la fecha de modificacion, devuelvo un 304 not modified
                        $strProtocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                        header("Cache-Control: maxage=" . $expires);
                        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expires) . " GMT");
                        header($strProtocol . " 304 Not Modified");
                        die();
                    }
                }
                // Si los archivos cambiaron desde la ultima descarga, los bajo de nuevo
                $strLastModified = gmdate("D, d M Y H:i:s", $intLastModified);
            }

            header("Cache-Control: maxage=" . $expires);
            if ($intLastModified > 0) header("Last-Modified: " . $strLastModified . " GMT");
            header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expires) . " GMT");
            header("Content-Type: {$strContentType}");
        }

        public static function core_validateEmailAddress($strEMail)
        {
            $strEMail = trim($strEMail);

            if (empty($strEMail))
                return false;

            return (preg_match("/^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/i", $strEMail) == 1);
        }

        /**
         * Imprime en pantalla un string pero con sus caracteres convertidos a HTML para que no haya problemas ni errores.
         *
         * @param unknown_type $strString
         */
        public static function safeprint($strString, $boolPrint = true)
        {
            if ($boolPrint) {
                print htmlspecialchars($strString);
            } else {
                return htmlspecialchars($strString);
            }
        }

        public static function getParam(&$arrParam, $strTerm, $default = "", $boolUTF8 = false)
        {
            $model = global_model::getInstance();
            if (!empty($arrParam[$strTerm])) {
                if (is_int($arrParam[$strTerm]))
                    $arrParam[$strTerm] = intval($arrParam[$strTerm]);
                else if (is_float($arrParam[$strTerm]))
                    $arrParam[$strTerm] = floatval($arrParam[$strTerm]);
                else if (is_string($arrParam[$strTerm]))
                    $arrParam[$strTerm] = $model->sql_real_escape_string(global_function::user_magic_quotes($arrParam[$strTerm], $boolUTF8));
                else
                    $arrParam[$strTerm] = $model->sql_real_escape_string(global_function::user_magic_quotes($arrParam[$strTerm], $boolUTF8));

                return $arrParam[$strTerm];
            }
            return $default;
        }

        public static function getHeaders($strType = "json", $strPragma = "private")
        {
            $strContentType = "";
            if ($strType == "json") {
                $strContentType = "aplication/json";
            } else if ($strType == "xml") {
                $strContentType = "text/xml";
            } else if ($strType == "csv") {
                $strContentType = "text/csv";
            } else if ($strType == "html") {
                $strContentType = "text/html; charset=iso-8859-1";
            } else {
                $strContentType = "text/html; charset=iso-8859-1";
            }
            header("Pragma: {$strPragma}");
            header("Content-Type: {$strContentType}");
        }
    }

    /**
     * This class conteins the logical to handling points of debug
     * @author Edward Acu <acued89@gmail.com>
     * @version 0.2
     */
    class debug
    {

        static $_instance;
        private $DEBUG_STR = "";
        private $CLASSREF;

        function __construct($objClass = false)
        {
            $this->CLASSREF = $objClass;
            if (!$this->CLASSREF)
                $this->CLASSREF = $this;
        }

        public static function getInstance($objClass = false)
        {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self($objClass);
            }
            return self::$_instance;
        }

        static function drawdebug($ThisVar, $VariableName = "", $ShowWhat = 0, $boolForceShow = false)
        {
            $strType = gettype($ThisVar);
            $strPreOpen = "";
            $strPreClose = "";
            if (!is_string($ThisVar)) {
                $strPreOpen = "<pre>";
                $strPreClose = "</pre>";
            }

            echo "\n<hr>";
            echo "\n<div style='padding: 10px'>";
            if (!empty($VariableName))
                echo "<b><i> $VariableName</b></i> ";
            echo "Var  Type of var = <b>" . $strType . "</b><br><br>\n{$strPreOpen}";
            if ($ShowWhat == 0) {
                if (is_bool($ThisVar))
                    print_r(($ThisVar) ? "true" : "false");
                else
                    print_r($ThisVar);
            } else if ($ShowWhat == 1) {
                print_r(array_values($ThisVar));
            } else if ($ShowWhat == 2) {
                print_r(array_keys($ThisVar));
            }
            print_r("<hr>{$strPreClose}\n");
            echo "\n</div>";
        }

        /*Function that append point to debug*/
        function addDebug($strDebugString)
        {
            $this->DEBUG_STR .= (empty($this->DEBUG_STR)) ? "" : ", ";
            $this->DEBUG_STR .= $this->getmicrotime() . ' ' . get_class($this->CLASSREF) . ": {$strDebugString}\n<br>";
        }

        /*#Function that clear the debug's variable*/
        function clearDebug()
        {
            return $this->DEBUG_STR;
        }

        /*#Fucntion that return all debugs in string*/
        function getDebug()
        {
            return $this->DEBUG_STR;
        }

        /*#for times*/
        function getmicrotime()
        {
            list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
        }

    }

#handling errors
    /**
     * This class contains the logical to handling error
     * @author Edward Acu <acued89@gmail.com>
     * @version 0.3
     */
    class error
    {

        /**
         * Array that conteins all errors descriptions
         *
         * @var array -> array that conteins all errors
         * @access protected
         */
        private $arrErrorMsgs = false;

        /**
         * Method thad added an errer into array's error
         *
         * @param string $strMsg
         * @access protected
         */
        public function addError($strMsg, $strKey = "")
        {
            if (!empty($strKey)) {
                $this->arrErrorMsgs[$strKey][] = $strMsg;
            } else
                $this->arrErrorMsgs[] = $strMsg;
        }

        /**
         * Method that ordering the error's array
         */
        public function sortErrorsByText()
        {
            if ($this->hasError()) {
                sort($this->arrErrorMsgs);
            }
        }

        /**
         * Method that indicating if it has errors
         *
         * @access public
         * @return boolean
         */
        public function hasError($strKey = "")
        {
            if (!empty($strKey)) {
                return (isset($this->arrErrorMsgs[$strKey]) && is_array($this->arrErrorMsgs[$strKey]) && (count($this->arrErrorMsgs[$strKey]) > 0));
            } else
                return (is_array($this->arrErrorMsgs) && (count($this->arrErrorMsgs) > 0));
        }

        /**
         * Method that return the error's array, support array view or string view
         *
         * @param string $strMode modes that return message array|string
         * @param mixed $varModeHelper indicates which is the glue if a string
         * @return mixed
         */
        public function getErrors($strMode = "array", $varModeHelper = false, $strKey = "")
        {
            if (!empty($strKey)) {
                if (!$this->hasError($strKey))
                    return false;
                if ($strMode == "string") {
                    if ($varModeHelper == false)
                        $varModeHelper = ", ";
                    return implode($varModeHelper, $this->arrErrorMsgs[$strKey]);
                } else {
                    return $this->arrErrorMsgs[$strKey];
                }
            } else {
                if (!$this->hasError())
                    return false;
                if ($strMode == "string") {
                    if ($varModeHelper == false)
                        $varModeHelper = ", ";

                    $strVar = "";
                    foreach ($this->arrErrorMsgs as $element) {
                        if (is_array($element) && (count($element) > 0)) {
                            foreach ($element as $element2) {
                                $strVar .= (empty($strVar)) ? $element2 : $varModeHelper . $element2;
                            }
                        } else {
                            $strVar .= (empty($strVar)) ? $element : $varModeHelper . $element;
                        }
                    }
                    return $strVar;
                } else {
                    return $this->arrErrorMsgs;
                }
            }
        }

        public function getErrorCodes()
        {
            $strReturn = "";
            if (is_array($this->arrErrorMsgs)) {
                foreach ($this->arrErrorMsgs AS $key => $value) {
                    $strReturn .= (empty($strReturn)) ? "{$key}" : ",{$key}";
                    unset($key);
                    unset($value);
                }
            }
            return $strReturn;
        }

    }

    class password
    {

        static $_instance;

        function __construct()
        {

        }

        /* Evitamos el clonaje del objeto. */

        private function __clone()
        {

        }

        /* Funcion encargada de crear, si es necesario, el objeto. Esta es la funcion que debemos llamar desde fuera de la clase para instanciar el objeto, y asi, poder utilizar sus metodos
         *  instanciar todos los objetos con este metodo ya que por medio de él podemos acceder indiscriminadamente a funciones estaticas y no estaticas
         */

        public static function getInstance()
        {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        private $arrWords = array();

        private static function setWords(&$arrWords)
        {
            $arrWords[1] = 'Air';
            $arrWords[2] = 'Pen';
            $arrWords[3] = 'Sol';
            $arrWords[4] = 'Sun';
            $arrWords[5] = 'Agua';
            $arrWords[6] = 'Aire';
            $arrWords[7] = 'Ball';
            $arrWords[8] = 'Book';
            $arrWords[9] = 'Desk';
            $arrWords[10] = 'Door';
            $arrWords[11] = 'Fire';
            $arrWords[12] = 'Hoja';
            $arrWords[13] = 'Leer';
            $arrWords[14] = 'Luna';
            $arrWords[15] = 'Moon';
            $arrWords[16] = 'Nota';
            $arrWords[17] = 'Note';
            $arrWords[18] = 'Hijo';
            $arrWords[19] = 'Rain';
            $arrWords[20] = 'Read';
            $arrWords[21] = 'Room';
            $arrWords[22] = 'Shoe';
            $arrWords[23] = 'Work';
            $arrWords[24] = 'Carro';
            $arrWords[25] = 'Child';
            $arrWords[26] = 'Cielo';
            $arrWords[27] = 'Class';
            $arrWords[28] = 'Compu';
            $arrWords[29] = 'Fuego';
            $arrWords[30] = 'Lapiz';
            $arrWords[31] = 'Libro';
            $arrWords[32] = 'Pants';
            $arrWords[33] = 'Paper';
            $arrWords[34] = 'Pluma';
            $arrWords[35] = 'Shirt';
            $arrWords[36] = 'Silla';
            $arrWords[37] = 'Techo';
            $arrWords[38] = 'Water';
            $arrWords[39] = 'Write';
            $arrWords[40] = 'Heaven';
            $arrWords[41] = 'Lluvia';
            $arrWords[42] = 'Mother';
            $arrWords[43] = 'Pagina';
            $arrWords[44] = 'Parent';
            $arrWords[45] = 'Pelota';
            $arrWords[46] = 'Pencil';
            $arrWords[47] = 'Puerta';
            $arrWords[48] = 'Puerta';
            $arrWords[49] = 'Tierra';
            $arrWords[50] = 'Window';
        }

        /**
         * Retorna un password generada mas user-friendly
         *
         * @return string
         */
        public static function generate_humanpass()
        {
            $arrWords = array();
            self::setWords($arrWords);
            $strTMP = "";
            $strPassword = "";
            $k = count($arrWords);

            while ((strlen($strPassword) < 8)) {
                $intTMP = rand(1, $k);
                $strTMP = (!empty($arrWords[$intTMP])) ? $arrWords[$intTMP] : "";

                $strConcat = $strPassword . $strTMP;
                while ((strlen($strConcat) > 10)) {
                    $intTMP = rand(1, $k);
                    $strTMP = (!empty($arrWords[$intTMP])) ? $arrWords[$intTMP] : "";
                    $strConcat = $strPassword . $strTMP;
                }
                $strPassword = $strPassword . $strTMP;
            }
//Ahora agrego los numeros como prefijo y sufijo

            $intTMP = rand(10, 99);
            $strPassword = $intTMP . $strPassword;
            $intTMP = rand(10, 99);
            $strPassword .= $intTMP;

            return $strPassword;
        }

        /**
         * Retorna un password generado en base a la longitud ingresada
         *
         * @param string $longitudPass = 10 Longitud del password
         * @return string
         */
        public static function generate_frikipass($longitudPass = 10)
        {
//Se define la longitud de la contraseña, por default usamos 10 pero podemos setearlo
//Se define una cadena de caractares. Te recomiendo que uses esta.
            $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
//Obtenemos la longitud de la cadena de caracteres
            $longitudCadena = strlen($cadena);

//Se define la variable que va a contener la contraseña
            $pass = "";

//Creamos la contraseña
            for ($i = 1; $i <= $longitudPass; $i++) {
//Definimos numero aleatorio entre 0 y la longitud de la cadena de caracteres -1
                $pos = rand(0, $longitudCadena - 1);
//ahora formando la contraseña en cada iteracción del ciclo, añadimos a la cadena $pass la letra correspondiente a la posición $pos en la cadena de caracteres definida
                $pass .= substr($cadena, $pos, 1);
            }
            return $pass;
        }

    }

    class response_webservice
    {

        /**
         * @var singleton $instance
         * @desc Singleton var
         */
        private static $instance;

        /**
         * Singleton pattern. Returns the same instance to all callers
         *
         * @return Socket
         */
        private $Response = false;
        private $strMessage = "";
        private $valido = 0;
        private $arrDetail = array();
        private $boolPrintJson = true;

        public static function singleton($valido = 0, $strMensaje = "", $arrDetail = array())
        {
            if (self::$instance == null || !self::$instance instanceof response_webservice) {
                self::$instance = new response_webservice($valido, $strMensaje, $arrDetail);
            }
            return self::$instance;
        }

        public function __construct($valido = 0, $strMensaje = "", $arrDetail = array())
        {
            $this->setValido($valido);
            $this->setStrMessage($strMensaje);
            $this->setArrDetail($arrDetail);
        }

        function setStrMessage($strMessage)
        {
            $this->strMessage = $strMessage;
        }

        function setValido($valido)
        {
            $this->valido = $valido;
        }

        function setArrDetail($arrDetail)
        {
            $this->arrDetail = $arrDetail;
        }

        function setBoolPrintJson($boolPrintJson)
        {
            $this->boolPrintJson = $boolPrintJson;
        }

        public function setResponse($valido, $strMensaje = "", $arrDetail = array(), $boolUseUTF8 = true)
        {
            $this->setValido($valido);
            $this->setStrMessage($strMensaje);
            $this->setArrDetail($arrDetail);

            $this->Response = $this->arrDetail;
            $this->Response["valido"] = $this->valido;
            $this->Response["status"] = ($this->valido <= 0) ? "fail" : "ok";
            $this->Response["estado"] = ($this->valido <= 0) ? "fail" : "ok";
            $this->Response["razon"] = $this->strMessage;
            $this->Response["msj"] = $this->strMessage;
            $this->Response["msg"] = $this->strMessage;
            if ($boolUseUTF8)
                global_function::utf8_encode_array($this->Response);
        }

        public function getResponse($valido = 0, $strMensaje = "", $arrDetail = array(), $boolUseUTF8 = true)
        {
            if (($valido !== false)) {
                $this->setResponse($valido, $strMensaje, $arrDetail, $boolUseUTF8);
            }
            if ($this->boolPrintJson) {
                print json_encode($this->Response);
            } else {
                return $this->Response;
            }
        }

        public static function response($valido = 0, $strMensaje = "", $arrDetail = array(), $boolUseUTF8 = true)
        {
            $response = array();
            $valido = intval($valido);
            $response = $arrDetail;
            $response["valido"] = $valido;
            $response["status"] = ($valido <= 0) ? "fail" : "ok";
            $response["estado"] = ($valido <= 0) ? "fail" : "ok";
            $response["razon"] = utf8_encode($strMensaje);
            $response["msj"] = utf8_encode($strMensaje);
            $response["msg"] = utf8_encode($strMensaje);
            if ($boolUseUTF8)
                global_function::utf8_encode_array($response);
            return $response;
        }

        public static function printResponse($valido = 0, $strMensaje = "", $arrDetail = array())
        {
            $response = array();
            $valido = intval($valido);
            $response = $arrDetail;
            $response["valido"] = $valido;
            $response["status"] = ($valido <= 0) ? "fail" : "ok";
            $response["estado"] = ($valido <= 0) ? "fail" : "ok";
            $response["razon"] = $strMensaje;
            $response["msj"] = $strMensaje;
            $response["msg"] = $strMensaje;

            global_function::utf8_encode_array($response);
            print json_encode($response);
        }

    }

    interface controller
    {
        public function run();

        public function getOperation();
    }
}
