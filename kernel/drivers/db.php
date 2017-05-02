<?php
/**
 * Created by PhpStorm.
 * User: acuedd
 * Date: 30/08/2016
 * Time: 3:02 PM
 */
namespace kernel\db{
    include_once 'kernel/drivers/Database.php';
    use PHPtricks\Database\Database;

    class db extends Database{
        private $engine = "";
        private $link;
        private $stmt;
        private $array;
        private $info = false;
        private static $_instance;
        static $count = 0;

        protected function __construct() {
            db::$count++;
            //call_user_func_array([$this, \config()], [null]);
            $this->engine = config();
            $this->conectar();
        }
        private function __clone() {

        }
        public static function getInstance() {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function getEngine(){
            return $this->engine;
        }

        protected function conectar(){
            if($this->engine == "mysql"){
                $this->mysql_conectar();
            }
            else if($this->engine == "mssql"){
                $this->sqlsrv_conectar();
            }
            else if($this->engine == "sqlsrv"){
                $this->sqlsrv_conectar();
            }
        }
        public function conectar_db_remota($db){
            if($this->engine == "mysql"){
                return $this->mysql_conectar_db_remota($db);
            }
            else if($this->engine == "mssql"){
                return $this->sqlsrv_conectar_db_remota($db);
            }
            else if($this->engine == "sqlsrv"){
                return $this->sqlsrv_conectar_db_remota($db);
            }
        }
        private function validate_conection($objConection = false){
            return ($objConection === false)?$this->link:$objConection;
        }
        public function desconectar($objConection){
            if($this->engine == "mysql"){
                $this->mysql_desconectar($objConection);
            }
            else if($this->engine == "mssql"){
                $this->sqlsrv_desconectar($objConection);
            }
            else if($this->engine == "sqlsrv"){
                $this->sqlsrv_desconectar($objConection);
            }
        }
        public function sql_ejecutar($sql,$objConection = false,$params = array(),$options = array("Scrollable"=>"")){
            if($this->engine == "mysql"){
                return $this->mysql_sql_ejecutar($sql, $objConection);
            }elseif ($this->engine == "mssql"){
                return $this->sqlsrv_sql_ejecutar($sql, $objConection, $params, $options);
            }elseif ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_ejecutar($sql, $objConection, $params, $options);
            }
        }
        public function sql_fetch_array($stmt, $fila = 0){
            if($this->engine == "mysql"){
                return $this->mysql_sql_fetch_array($stmt, $fila);
            }elseif ($this->engine == "mssql"){
                return $this->sqlsrv_sql_fetch_array($stmt, $fila);
            }elseif ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_fetch_array($stmt, $fila);
            }
        }
        public function sql_fetch_assoc($stmt){
            if($this->engine == "mysql"){
                return $this->mysql_sql_fetch_assoc($stmt);
            }elseif ($this->engine == "mssql"){
                return $this->sqlsrv_sql_fetch_assoc($stmt);
            }elseif ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_fetch_assoc($stmt);
            }
        }
        public function sql_lastID($objConection = false){
            if($this->engine == "mysql"){
                return $this->mysql_sql_lastID($objConection);
            }elseif ($this->engine == "mssql"){
                return $this->sqlsrv_sql_lastID($objConection);
            }elseif ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_lastID($objConection);
            }
        }
        public function sql_num_rows($stmt){
            if($this->engine == "mysql"){
                return $this->mysql_sql_num_rows($stmt);
            }elseif ($this->engine == "mssql"){
                return $this->sqlsrv_sql_num_rows($stmt);
            }elseif ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_num_rows($stmt);
            }
        }
        public function sql_escape($strTMP){
            return $this->sql_real_escape_string($strTMP);
        }
        public function sql_real_escape_string($strTMP){
            if($this->engine == "mysql"){
                return $this->mysql_sql_real_escape_string($strTMP);
            }elseif ($this->engine == "mssql"){
                return $this->sqlsrv_sql_real_escape_string($strTMP);
            }elseif ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_real_escape_string($strTMP);
            }
        }
        public function sql_free_result($stmt){
            if($this->engine == "mysql"){
                return $this->mysql_sql_free_result($stmt);
            }elseif ($this->engine == "mssql"){
                return $this->sqlsrv_sql_free_result($stmt);
            }elseif ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_free_result($stmt);
            }
        }
        public function sql_get_fields($argIndex){
            if($this->engine == "mysql"){
                return $this->mysql_sql_get_fields($argIndex);
            }elseif ($this->engine == "mssql"){
                return $this->sqlsrv_sql_get_fields($argIndex);
            }elseif ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_get_fields($argIndex);
            }
        }
        public function sql_num_fields($argIndex){
            if($this->engine == "mysql"){
                return $this->mysql_sql_num_fields($argIndex);
            }elseif ($this->engine == "mssql"){
                return $this->sqlsrv_sql_num_fields($argIndex);
            }elseif ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_num_fields($argIndex);
            }
        }
        public function sql_ejecutarKey($strSQL, $boolFalseOnEmpty = false, $boolForceArray = false){
            if($this->engine == "mysql"){
                return $this->mysql_sql_ejecutarKey($strSQL, $boolFalseOnEmpty, $boolForceArray);
            }
            else if ($this->engine == "mssql"){
                return $this->sqlsrv_sql_ejecutarKey($strSQL, $boolFalseOnEmpty, $boolForceArray);
            }
            else if ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_ejecutarKey($strSQL, $boolFalseOnEmpty, $boolForceArray);
            }
        }
        public function sql_TableUpdate($strTable, $arrKey, $arrFields, $arrExtraInsertFields = false, $boolForceReplace = false){
            if($this->engine == "mysql"){
                return $this->mysql_sql_TableUpdate($strTable, $arrKey, $arrFields, $arrExtraInsertFields , $boolForceReplace );
            }
            else if ($this->engine == "mssql"){
                return $this->sqlsrv_sql_TableUpdate($strTable, $arrKey, $arrFields, $arrExtraInsertFields , $boolForceReplace );
            }
            else if ($this->engine == "sqlsrv"){
                return $this->sqlsrv_sql_TableUpdate($strTable, $arrKey, $arrFields, $arrExtraInsertFields , $boolForceReplace );
            }
        }

        /*Libs mysql*/
        private function mysql_conectar( )  {
            if (!isset($this->link)) {
                $this->link = (mysql_connect(config("host_name"), config("db_user"), config("db_password"))) or die(mysql_error());
                mysql_select_db(config("db_name"), $this->link) or die(print "MySQL Error:". mysql_error());
            }
            //@mysql_query("SET NAMES 'utf8'");
        }
        private function mysql_conectar_db_remota($db){
            $objConection = (mysql_connect(config("host_name"), config("db_user"), config("db_password"))) or die(mysql_error());
            mysql_select_db($db, $objConection) or die(print "MySQL Error:". mysql_error());
            if($objConection === false)return false;
            return $objConection;
        }
        private function mysql_desconectar($objConection = false) {
            mysql_close($this->validate_conection($objConection));
        }
        private function mysql_sql_ejecutar($sql, $objConection = false) {
            $this->stmt = mysql_query($sql, $this->validate_conection($objConection));
            if (!$this->stmt) {
                print "MySQL Error: " . mysql_error();
                print global_controller::drawdebug($sql, "error mysql");
                exit;
            }
            return $this->stmt;
        }
        private function mysql_sql_fetch_array($stmt, $fila = 0) {
            if ($fila == 0) {
                $this->array = mysql_fetch_array($stmt);
            } else {
                mysql_data_seek($stmt, $fila);
                $this->array = mysql_fetch_array($stmt);
            }
            return $this->array;
        }
        private function mysql_sql_fetch_assoc($stmt) {
            if (!is_resource($stmt))
                return false;
            $this->array = mysql_fetch_assoc($stmt);
            return $this->array;
        }
        private function mysql_sql_lastID($objConection = false) {
            return mysql_insert_id($this->validate_conection($objConection));
        }
        private function mysql_sql_num_rows($stmt) {
            if (!is_resource($stmt))
                return false;
            return mysql_num_rows($stmt);
        }
        private function mysql_sql_real_escape_string($strTMP) {
            return mysql_real_escape_string($strTMP);
        }
        private function mysql_sql_free_result($stmt) {
            return mysql_free_result($stmt);
        }
        private function mysql_sql_get_fields($argIndex) {
            if ($field = mysql_fetch_field($argIndex)) {
                do {
                    $fields[$field->name]['name'] = $field->name;
                    $fields[$field->name]['table'] = $field->table;
                    $fields[$field->name]['max_length'] = $field->max_length;
                    $fields[$field->name]['not_null'] = $field->not_null;
                } while ($field = mysql_fetch_field($argIndex));
            }
            return $fields;
        }
        private function mysql_sql_num_fields($argIndex) {
            return mysql_num_fields($argIndex);
        }
        private function mysql_sql_ejecutarKey($strSQL, $boolFalseOnEmpty = false, $boolForceArray = false){
            $return = false;
            $qList = $this->sql_ejecutar($strSQL . " LIMIT 0,1 ");
            $listFields = $this->sql_get_fields($qList);
            if ($rList = $this->sql_fetch_array($qList)) {
                if ($this->sql_num_fields($qList) == 1 && !$boolForceArray) {
                    $return = $rList[0];
                    if($boolFalseOnEmpty){
                        $strTMP = html_entity_decode($return);
                        $strTMP = strip_tags($strTMP);
                        $strTMP = str_replace(" ", "", $strTMP);
                        $strTMP = trim($strTMP);
                        $strTMP = str_replace(" ", "", $strTMP);
                        $strTMP = trim($strTMP);

                        if (empty($return) || empty($strTMP)) $return = false;
                    }
                }
                else{
                    $return = array();
                    foreach ($listFields as $field) {
                        $return[$field['name']] = $rList[$field['name']];
                    }
                }
            }
            $this->sql_free_result($qList);
            return $return;
        }
        /**
         * Funcion que construye y ejecuta los queries para actualizar o insertar data.
         *
         * @param string $strTable Nombre de la tabla a afectar
         * @param array $arrKey Array con las llaves de la tabla campo=>value
         * @param array $arrFields Array con los datos a actualizar campo=>value
         * @param mixed $arrExtraInsertFields Array con campos extras a agregar si es un insert (dateregistered, por ejemplo)
         * @param boolean $boolForceReplace obliga a hacer un replace into
         */
        public function mysql_sql_TableUpdate($strTable, $arrKey, $arrFields, $arrExtraInsertFields = false, $boolForceReplace = false) {
            if(!$boolForceReplace){
                $strWhere = "1";
                while ($arrField = each($arrKey)) {
                    $strValue =  $this->sql_real_escape_string($arrField["value"]);
                    $strWhere .= " AND {$arrField["key"]} = '{$strValue}'";
                }
                // Primero veo si el dato ya existe
                $strQuery = "SELECT COUNT(*) AS conteo
                             FROM {$strTable}
                             WHERE {$strWhere}";
                $intNumRows = $this->sql_ejecutarKey($strQuery);
            }
            else {
                $intNumRows = 0;
            }

            if ($intNumRows <= 0) {
                // Insert
                $arrAllFields = array_merge($arrKey, $arrFields);
                if (is_array($arrExtraInsertFields)) {
                    $arrAllFields = array_merge($arrAllFields, $arrExtraInsertFields);
                }
                $strFields = "";
                $strValues = "";
                while ($arrField = each($arrAllFields)) {
                    $strValue = $this->sql_real_escape_string($arrField["value"]);
                    $strFields .= ", {$arrField["key"]}";
                    if($strValue == "NULL" || ((preg_match("/^[A-Z]+[\(\)]/",$strValue)) === 1)){
                        $strValues .= ", {$strValue}";
                    }
                    else{
                        $strValues .= ", '{$strValue}'";
                    }
                }
                $strFields = substr($strFields, 2);
                $strValues = substr($strValues, 2);
                $strCommand = ($boolForceReplace)?"REPLACE":"INSERT";
                $strQuery = "{$strCommand} INTO {$strTable}
                             ({$strFields})
                             VALUES
                             ({$strValues})";
                $this->sql_ejecutar($strQuery);
                return true;
            }
            else if ($intNumRows == 1) {
                // Update
                $strSet = "";
                while ($arrField = each($arrFields)) {
                    $strValue = $this->sql_real_escape_string($arrField["value"]);
                    if($strValue == "NULL" || ((preg_match("/^[A-Z]+[\(\)]/",$strValue)) === 1)){
                        $strSet .= ", {$arrField["key"]} = {$strValue}";
                    }
                    else{
                        $strSet .= ", {$arrField["key"]} = '{$strValue}'";
                    }
                }
                $strSet = substr($strSet, 2);

                $strQuery = "UPDATE {$strTable}
                             SET {$strSet}
                             WHERE {$strWhere}";
                $this->sql_ejecutar($strQuery);
                return true;
            }
        }

        /*Sqlsrv*/
        private function sqlsrv_conectar(){
            if(!$this->link){
                $this->info = array('Database'=>config("db_name"), 'UID'=>config("db_user"), 'PWD'=>config("db_password"));
                $this->link = sqlsrv_connect(config("host_name"),$this->info) or $this->sqlsrv_get_last_message();
            }
        }
        private function sqlsrv_conectar_db_remota($db){
            $arrInfo = array('Database'=>$db, 'UID'=>config("db_user"), 'PWD'=>config("db_password"));
            $objConection = sqlsrv_connect(config("host_name"),$arrInfo);
            if($objConection === false)return false;
            return $objConection;
        }
        private function sqlsrv_desconectar($objConection = false){
            sqlsrv_close($this->validate_conection($objConection));
        }
        private function sqlsrv_sql_ejecutar($sql,$objConection = false,$params = array(),$options = array("Scrollable"=>SQLSRV_CURSOR_KEYSET)){
            $stmt = sqlsrv_query($this->validate_conection($objConection),$sql,$params,$options);
            if(!$stmt)$this->sqlsrv_get_last_message(true,$sql);
            return $stmt;
        }
        private function sqlsrv_sql_fetch_array($stmt,$fila = 0) {
            if (!is_resource($stmt))
                return false;
            if ($fila == 0) {
                $arr = sqlsrv_fetch_array($stmt);
            } else {
                sqlsrv_fetch($stmt, $fila);
                $arr = sqlsrv_fetch_array($stmt);
            }
            return $arr;
        }
        private function sqlsrv_sql_fetch_assoc($stmt) {
            if (!is_resource($stmt))
                return false;
            return sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
        }
        private function sqlsrv_sql_lastID($objConection = false) {
            $id = 0;
            $res = $this->sql_ejecutar("SELECT @@identity AS id",$objConection);
            if ($row = $this->sql_fetch_assoc($res)) {
                $id = $row["id"];
            }
            return $id;
        }
        private function sqlsrv_sql_num_rows($stmt) {
            if (!is_resource($stmt))
                return false;
            return sqlsrv_num_rows($stmt);
        }
        private function sqlsrv_sql_real_escape_string($strTMP) {
            if(get_magic_quotes_gpc()){
                $strTMP = stripslashes($strTMP);
            }
            return str_replace("'", "''", $strTMP);
        }
        private function sqlsrv_sql_free_result($stmt) {
            return sqlsrv_free_stmt($stmt);
        }
        private function sqlsrv_sql_get_fields($argIndex) {
            $fields = false;
            if ($field = sqlsrv_field_metadata($argIndex)) {
                foreach($field AS $val){
                    $fields[$val["Name"]]['name'] = $val["Name"];
                    //$fields[$field["name"]]['table'] = $field->column_source;
                    $fields[$val["Name"]]['max_length'] = $val["Size"];
                    $fields[$val["Name"]]['type'] = $val["Type"];
                    unset($val);
                }

            }
            return $fields;
        }
        private function sqlsrv_sql_num_fields($argIndex) {
            return sqlsrv_num_fields($argIndex);
        }
        private function sqlsrv_get_last_message($boolDie = true,$sql = ""){
            print "<pre>";
            print_r(sqlsrv_errors());
            print "</pre>";
            print "<pre>";
            print_r($sql);
            print "</pre>";
            if($boolDie)die;
        }
        private function sqlsrv_sql_ejecutarKey($strSQL, $boolFalseOnEmpty = false, $boolForceArray = false){
            $return = false;

            $sql = "";
            if(substr($strSQL,0,6) == "SELECT"){
                $sql = "SELECT TOP 1";
                $sql .= substr($strSQL,6);

                $qList = $this->sql_ejecutar($sql);
                $listFields = $this->sql_get_fields($qList);
                if ($rList = $this->sql_fetch_array($qList)) {
                    if ($this->sql_num_fields($qList) == 1 && !$boolForceArray) {
                        $return = $rList[0];
                        if($boolFalseOnEmpty){
                            $strTMP = html_entity_decode($return);
                            $strTMP = strip_tags($strTMP);
                            $strTMP = str_replace(" ", "", $strTMP);
                            $strTMP = trim($strTMP);
                            $strTMP = str_replace(" ", "", $strTMP);
                            $strTMP = trim($strTMP);

                            if (empty($return) || empty($strTMP)) $return = false;
                        }
                    }
                    else{
                        $return = array();
                        foreach ($listFields as $field) {
                            $return[$field['name']] = $rList[$field['name']];
                        }
                    }
                }
                $this->sql_free_result($qList);
            }
            return $return;
        }
        /**
         * Funcion que construye y ejecuta los queries para actualizar o insertar data.
         *
         * @param string $strTable Nombre de la tabla a afectar
         * @param array $arrKey Array con las llaves de la tabla campo=>value
         * @param array $arrFields Array con los datos a actualizar campo=>value
         * @param mixed $arrExtraInsertFields Array con campos extras a agregar si es un insert (dateregistered, por ejemplo)
         * @param boolean $boolForceReplace obliga a hacer un replace into
         */
        public function sqlsrv_sql_TableUpdate($strTable, $arrKey, $arrFields, $arrExtraInsertFields = false, $boolForceReplace = false) {
            if(!$boolForceReplace){
                $strWhere = "";
                while ($arrField = each($arrKey)) {
                    $strValue =  $this->sql_real_escape_string($arrField["value"]);
                    $strWhere .= (empty($strWhere))?"{$arrField["key"]} = '{$strValue}'":" AND {$arrField["key"]} = '{$strValue}'";
                }
                // Primero veo si el dato ya existe
                $strQuery = "SELECT COUNT(*) AS conteo
                         FROM {$strTable} ";
                $strQuery .= (!empty($strWhere))?"WHERE {$strWhere}":"";
                $intNumRows = $this->sql_ejecutarKey($strQuery);
            }
            else {
                $intNumRows = 0;
            }

            if ($intNumRows <= 0) {
                // Insert
                $arrAllFields = array_merge($arrKey, $arrFields);
                if (is_array($arrExtraInsertFields)) {
                    $arrAllFields = array_merge($arrAllFields, $arrExtraInsertFields);
                }
                $strFields = "";
                $strValues = "";
                while ($arrField = each($arrAllFields)) {
                    $strValue = $this->sql_real_escape_string($arrField["value"]);
                    if(!empty($strValue)){
                        $strFields .= ", {$arrField["key"]}";
                        if($strValue == "NULL" || ((preg_match("/^[A-Z]+[\(\)]/",$strValue)) === 1)){
                            $strValues .= ", {$strValue}";
                        }
                        else{
                            $strValues .= ", '{$strValue}'";
                        }
                    }
                }
                $strFields = substr($strFields, 2);
                $strValues = substr($strValues, 2);
                $strCommand = ($boolForceReplace)?"REPLACE":"INSERT";
                $strQuery = "{$strCommand} INTO {$strTable}
                         ({$strFields})
                         VALUES
                         ({$strValues})";
                $this->sql_ejecutar($strQuery);
                return true;
            }
            else if ($intNumRows == 1) {
                // Update
                $strSet = "";
                while ($arrField = each($arrFields)) {
                    $strValue = $this->sql_real_escape_string($arrField["value"]);
                    if($strValue == "NULL" || ((preg_match("/^[A-Z]+[\(\)]/",$strValue)) === 1)){
                        $strSet .= ", {$arrField["key"]} = {$strValue}";
                    }
                    else{
                        $strSet .= ", {$arrField["key"]} = '{$strValue}'";
                    }
                }
                $strSet = substr($strSet, 2);

                $strQuery = "UPDATE {$strTable}
                         SET {$strSet}
                         WHERE {$strWhere}";
                $this->sql_ejecutar($strQuery);
                return true;
            }
        }




    }
}