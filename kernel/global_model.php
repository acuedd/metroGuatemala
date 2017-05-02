<?php
/**
 * Created by PhpStorm.
 * User: acuedd
 * Date: 30/08/2016
 * Time: 10:44 AM
 */
namespace kernel\Model{
    
    use kernel\Controller\global_controller;
    use kernel\Controller\global_function;
    use kernel\db\db;

    include_once ("kernel/drivers/db.php");
    include_once("kernel/global_controller.php");

    class global_model extends db  {
        protected $arrParam;
        private static $_instance;
        private $modulo;
        private $addon;
        //Constructor
        function __construct(){
            parent::__construct();
        }

        /* Evitamos el clonaje del objeto. Patrón Singleton */
        private function __clone() {

        }

        /* Función encargada de crear, si es necesario, el objeto. Esta es la función que debemos llamar desde fuera de la clase para instanciar el objeto, y así, poder utilizar sus métodos */
        public static function getInstance() {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }
        public function setArrParam($arrP) {
            $this->arrParam = $arrP;
        }
        public function getArrParams() {
            return $this->arrParam;
        }
        public function getModulo(){
            return $this->modulo;
        }
        public function setModulo($modulo){
            $this->modulo = $modulo;
        }
        public function getAddon(){
            return $this->addon;
        }
        public function setAddon($addon){
            $this->addon = $addon;
        }
        public function getParam($strTerm, $default = "", $boolUTF8 = false) {
            return global_function::getParam($this->arrParam, $strTerm,$default,$boolUTF8);
        }
        public static function clearTerm($strTMP, $boolUTFDecode = false){
            if(!empty($strTMP)){
                return mysql_real_escape_string( global_controller::user_magic_quotes($strTMP, $boolUTFDecode) );
            }
            else return "";
        }
        /* Funcion para eliminar datos de las tablas
        * @param array() $arrTables["nombreTabla"]["key_tabla"] = valor
        */
        public function sql_TableDelete($arrTables){
            if(is_array($arrTables) && count($arrTables) > 0){
                while($arrEachT = each($arrTables)){
                    $strWhere = "";
                    if(is_array($arrEachT["value"]) && count($arrEachT["value"])){
                        while($arrTMP2 = each($arrEachT["value"])){
                            $strWhere .= (empty($strWhere))?"{$arrTMP2["key"]}='{$arrTMP2["value"]}'": " AND {$arrTMP2["key"]}='{$arrTMP2["value"]}'";
                        }
                        $strQuery = "DELETE FROM {$arrEachT["key"]} WHERE {$strWhere}";
                        $this->sql_ejecutar($strQuery);
                    }
                }
            }

        }
        /*Funcion para debuguear un query*/
        public static function sql_queryDebug($sql, $boolShowQueryString = true, $arrFilter = false, $boolExplain = false, $objConnection = false){
            $t = global_model::getInstance();
            $boolFilter = is_array($arrFilter);
            if ($boolExplain)
                $sql = "EXPLAIN\n" . $sql;
            $qTMP = $t->sql_ejecutar($sql);
            ?>
            <div  style="position:relative; z-index:20; background-color:white; color:black;">
                <?php
                if ($boolShowQueryString)
                    print_r("<hr>" . nl2br($sql) . "<br><br>");
                ?>
                <table border="1" cellspacing="0" cellpadding="2" align="center">
                    <?php
                    $boolFirstRow = true;
                    $listFields = $t->sql_get_fields($qTMP);
                    if ($rTMP = $t->sql_fetch_assoc($qTMP)) {
                        do {
                            if ($boolFirstRow) {
                                $strRow = "<tr>";
                                reset($listFields);
                                foreach ($listFields as $key => $entry) {
                                    $strRow.="<th>{$key}</th>";
                                }
                                $strRow.= "</tr>\n";
                                echo $strRow;
                                $boolFirstRow = false;
                                reset($rTMP);
                            }
                            if ($boolFilter) {
                                $boolOK = true;
                                while ($arrFItem = each($arrFilter)) {
                                    if ($rTMP[$arrFItem["key"]] != $arrFItem["value"])
                                        $boolOK = false;
                                }
                                reset($arrFilter);
                                if (!$boolOK)
                                    continue;
                            }
                            $strRow = "<tr>";
                            reset($listFields);
                            foreach ($listFields as $key => $entry) {
                                $strValue = $rTMP[$key];
                                if (strlen($rTMP[$key]) == 0) {
                                    $strValue = "&nbsp;";
                                }
                                $strRow.="<td>{$strValue}</td>";
                            }
                            $strRow.= "</tr>\n";
                            echo $strRow;
                        }
                        while($rTMP=$t->sql_fetch_assoc($qTMP));
                    }
                    ?>
                </table><br><?php print $t->sql_num_rows($qTMP); ?> rows<hr>
            </div>
            <?php
            $t->sql_free_result($qTMP);
        }
        public function getVentanas($strFilter = ""){
            $arrRetun = false;
            $sql = "SELECT * FROM menu WHERE 1 {$strFilter}";
            $stmp = $this->sql_ejecutar($sql);
            while($rtmp = $this->sql_fetch_assoc($stmp)){
                $arrRetun[$rtmp["modulo"]][$rtmp["menu_id"]] = $rtmp;
            }
            return $arrRetun;
        }
        public function getArrayMenuForUser(){
            $arrRet = array();

            $sql = "SELECT  menu.menu_id, menu.page, menu.nombre, menu.image, menu.modulo,
                            menu_categoria.id AS categoria, menu_categoria.nombre AS categoriaNombre,
                            menu_categoria.imagen AS imagenCatego
                    FROM menu AS menu
                        LEFT JOIN menu_categoria menu_categoria
                            ON menu_categoria.id = menu.categoria_id";
            $qtmp = $this->sql_ejecutar($sql);
            if($this->sql_num_rows($qtmp) >0){
                while($rtmp = $this->sql_fetch_assoc($qtmp)){
                    if($this->check_user_access($rtmp["modulo"]."/".$rtmp["page"],true)){
                        if(!isset($arrRet[$rtmp["categoria"]]["modulo"])){
                            $arrRet[$rtmp["categoria"]]["modulo"] = $rtmp["categoriaNombre"];
                            $arrRet[$rtmp["categoria"]]["img"] = $rtmp["imagenCatego"];
                            $arrRet[$rtmp["categoria"]]["detalle"] = array();
                        }

                        $arrRet[$rtmp["categoria"]]["detalle"][$rtmp["menu_id"]]["name"] = $rtmp["nombre"];
                        $arrRet[$rtmp["categoria"]]["detalle"][$rtmp["menu_id"]]["img"] = $rtmp["image"];
                        $arrRet[$rtmp["categoria"]]["detalle"][$rtmp["menu_id"]]["link"] = "page={$rtmp["page"]}&mod={$rtmp["modulo"]}";
                    }
                }
            }
            return $arrRet;
        }
        public function sql_getArray($strSQL, $inArray = false, $strKeyName="", $strValueName=""){
            $return = false;
            $qList = $this->sql_ejecutar($strSQL);
            $listFields = $this->sql_get_fields($qList);
            $strFieldName = ($strValueName!="")?$strValueName:0;
            $strKeyName = ($strKeyName != "")?$strKeyName:0;
            if($this->sql_num_rows($qList) == 0){
                $return = false;
            }
            elseif($this->sql_num_rows($qList) == 1){
                $rList = $this->sql_fetch_array($qList);
                if ($inArray)
                    if ($strKeyName != "") {
                        $return = array($rList[$strKeyName] => $rList[$strFieldName]);
                    }
                    else {
                        $return = array($rList[$strFieldName]);
                    }
                else {
                    $return = $rList[$strFieldName];
                }
            }
            else{
                $rList = $this->sql_fetch_array($qList);
                $return = array();
                $boolFirst = true;
                do{
                    if($strKeyName != ""){
                        $return[$rList[$strKeyName]] = $rList[$strFieldName];
                    }
                    else{
                        $return[] = $rList[$strFieldName];
                    }
                }
                while($rList = $this->sql_fetch_array($qList));

            }
            $this->sql_free_result($qList);
            return $return;
        }

        public function getNotification(){
            return false;
            $sql = "SELECT * FROM notificacion WHERE userid_to = '{$_SESSION["motu"]["uid"]}'";
            return $this->sql_ejecutarKey($sql);
        }
        public function check_user_access($strAccess, $boolMenu = false){
            //Si el usuario es tipo Admin devuelvo true
            if((!empty($_SESSION["motu"]["class"])) && $_SESSION["motu"]["class"] == "webmaster")return true;
            //Valido que no venga vacio el acceso
            if(empty($strAccess))return false;
            //Valido que si este logeado(si es distinto de 0 quiere decir que si)
            if(empty($_SESSION["motu"]["uid"]))return false;
            //Reviso en la tabla si tiene acceso

            $strFilter = " = '{$strAccess}'";
            if($boolMenu)$strFilter = "LIKE '%{$strAccess}%'";
            $strQuery = "SELECT * FROM user_access WHERE user = '{$_SESSION["motu"]["uid"]}' AND access {$strFilter}";
            $arrInfo = $this->sql_ejecutarKey($strQuery);
            if($arrInfo)return true;
            else return false;
        }
        protected function sendCredentials($arrParams){
            /*Asunto------------------------------*/
            $strAsunto = "Credenciales";

            /*Cabeceras---------------------------*/
            $strCabeceras  = "MIME-Version: 1.0" . "\r\n";
            $strCabeceras .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
            $strCabeceras .= "From: noreply@visanet\r\n";

            /*Mensaje-----------------------------*/
            $strMensaje = $this->message($arrParams);
            @mail($arrParams["nickname"],$strAsunto,$strMensaje,$strCabeceras);
        }
        protected function message($arrData){
            $strMsj = "";
            $strMsj .= <<<EOD
                <table>
                    <tr>
                        <td>Usuario:</td>
                        <td>{$arrData["nickname"]}</td>
                    </tr>
                    <tr>
                        <td>Contraseña:</td>
                        <td>{$arrData["pass"]}</td>
                    </tr>
                </table>

EOD;
            return $strMsj;
        }
        protected function arrAccess(){
            $arrReturn = array();

            //Modulo de users
            $arrReturn["users"] = array();
            $arrReturn["users"]["name"] = "Usuarios";
            $arrReturn["users"]["page"] = array();
            $arrReturn["users"]["page"]["profiles"] = array();
            $arrReturn["users"]["page"]["profiles"]["name"] = "Perfil de accesos";
            $arrReturn["users"]["page"]["register"] = array();
            $arrReturn["users"]["page"]["register"]["name"] = "Registrar";

            //Modulo delivery
            $arrReturn["delivery"] = array();
            $arrReturn["delivery"]["name"] = "Delivery";
            $arrReturn["delivery"]["page"] = array();
            $arrReturn["delivery"]["page"]["business"] = array();
            $arrReturn["delivery"]["page"]["business"]["name"] = "Comercios";

            //Modulo settings
            $arrReturn["settings"] = array();
            $arrReturn["settings"]["name"] = "Configuracion";
            $arrReturn["settings"]["page"] = array();
            $arrReturn["settings"]["page"]["merchant"] = array();
            $arrReturn["settings"]["page"]["merchant"]["name"] = "Datos de empresa";

            //Modulo settings
            $arrReturn["stocktaking"] = array();
            $arrReturn["stocktaking"]["name"] = "Productos";
            $arrReturn["stocktaking"]["page"] = array();
            $arrReturn["stocktaking"]["page"]["category"] = array();
            $arrReturn["stocktaking"]["page"]["category"]["name"] = "Categorías y subcategorias";
            $arrReturn["stocktaking"]["page"]["spect_purchase"] = array();
            $arrReturn["stocktaking"]["page"]["spect_purchase"]["name"] = "Aspectos de la compra";

            //Modulo settings
            $arrReturn["settings"] = array();
            $arrReturn["settings"]["name"] = "Configuracion";
            $arrReturn["settings"]["page"] = array();
            $arrReturn["settings"]["page"]["merchant"] = array();
            $arrReturn["settings"]["page"]["merchant"]["name"] = "Datos de empresa";

            return $arrReturn;
        }
        public function getAccess($modulo,$addon, $extraacces = ""){
            $arrReturn = array();
            if($this->check_user_access("{$modulo}/{$addon}/consultar"))
                $arrReturn["{$modulo}/{$addon}/consultar"] = 1;
            if($this->check_user_access("{$modulo}/{$addon}/crear"))
                $arrReturn["{$modulo}/{$addon}/crear"] = 1;
            if($this->check_user_access("{$modulo}/{$addon}/modificar"))
                $arrReturn["{$modulo}/{$addon}/modificar"] = 1;
            if($this->check_user_access("{$modulo}/{$addon}/eliminar"))
                $arrReturn["{$modulo}/{$addon}/eliminar"] = 1;

            if(!empty($extraacces)){
                if($this->check_user_access("{$modulo}/{$addon}/{$extraacces}"))
                    $arrReturn["{$modulo}/{$addon}/{$extraacces}"] = 1;
            }

            return $arrReturn;
        }

    }
    interface model {

    }
}