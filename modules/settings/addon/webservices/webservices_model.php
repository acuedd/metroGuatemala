<?php
/**
 * Created by PhpStorm.
 * User: Alexander Flores
 * Date: 01-sep-16
 * Time: 4:19 p.m.
 */

namespace settings\webservices\Model{

    use kernel\Model\model;

    include_once("modules/settings/settings_model.php");

    class webservices_model extends \settings_model implements model {

        private static $_instance;
        private $strOpCod = "";

        public function __construct(){
            parent::__construct();
        }

        public static function getInstance() {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function getRptWebservices(){
            if($this->check_user_access("settings/merchant/consultar")){
                include_once("librarys/php/report/hml_report.php");
                $strQuery = "SELECT op_uuid AS Codigo,descripcion AS Descripcion,activo AS Activo
                             FROM 	webservices_operations
                             WHERE	activo = 'Y' ¿f? ¿o?";

                $arrEncabezado = array();
                $arrParametros = array();

                $arrEncabezado["filter"]["Codigo"] = "Codigo";
                $arrEncabezado["filter"]["Descripcion"] = "Descripcion";
                $arrEncabezado["filter"]["Activo"] = "Activo";
                $arrEncabezado["hidden"]["RowNum"] = "RowNum";
                $arrEncabezado["onclick"]["all_row"]["function"] = "getWebservices";
                $arrEncabezado["onclick"]["all_row"]["params"][] = "Codigo";

                $arrParametros["tipo"] = "paginador";
                $arrParametros["btnExportar"] = false;
                $arrParametros["porPagina"] = "15";

                $objReport = new \hml_report($strQuery,$arrEncabezado,$arrParametros);
                return $objReport->dibujarHML_RPT();
            }
            return "";
        }

        public function getWebservice($uuid){
            return $this->sql_ejecutarKey("SELECT * FROM webservices_operations WHERE op_uuid = '{$uuid}'");
        }

        public function getWebserviceParams($uuid){
            $arrReturn = array();
            $strQuery = "SELECT * FROM webservices_operations_extra_data WHERE op = '{$uuid}'";
            $qTMP = $this->sql_ejecutar($strQuery);
            if($this->sql_num_rows($qTMP)){
                while($rTMP = $this->sql_fetch_array($qTMP)){
                    $arrReturn[] = $rTMP;
                    unset($rTMP);
                }
                $this->sql_free_result($qTMP);
            }
            return $arrReturn;
        }

        public function getWebserviceFunction($uuid){
            $arrReturn = array();
            $strQuery = "SELECT * FROM webservices_operations_extra_function WHERE op = '{$uuid}'";
            $qTMP = $this->sql_ejecutar($strQuery);
            if($this->sql_num_rows($qTMP)){
                while($rTMP = $this->sql_fetch_array($qTMP)){
                    $arrReturn[] = $rTMP;
                    unset($rTMP);
                }
                $this->sql_free_result($qTMP);
            }
            return $arrReturn;
        }



    }
}