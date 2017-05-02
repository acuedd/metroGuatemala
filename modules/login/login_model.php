<?php
/**
 * Created by PhpStorm.
 * User: acuedd
 * Date: 30/08/2016
 * Time: 2:17 PM
 */
namespace login\model{
    use kernel\Model\global_model;

    require_once("kernel/global_model.php");
    require_once("kernel/global_controller.php");

    class login_model extends global_model{
        static $_instance;
        public static function getInstance() {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        function __construct() {
            parent::__construct();
        }

        function limpiar_session(){
            $strSesionid = session_id();
            $_SESSION["motu"] = array();
            $_SESSION["motu"]["uid"] = 0;
            $_SESSION["motu"]["CountryName"] = "";
            $_SESSION["motu"]["IdCountry"] = "";
            $_SESSION["motu"]["IdRole"] = "";
            $_SESSION["motu"]["RoleName"] = "";
            $_SESSION["motu"]["UserName"] = "";
            $_SESSION["motu"]["Password"] = "";
            $_SESSION["motu"]["tipo"] = "";
            $_SESSION["motu"]["tipo"] = "*PUBLIC*";
        }

        function llenar_session($intUsuario){
            $boolReturn = false;
            $intUsuario = intval($intUsuario);
            $strQuery = "SELECT userid, nickname, password, type, class
                            FROM main_user
                            WHERE userid= {$intUsuario} ";
            $UserInfo = $this->sql_ejecutarKey($strQuery);

            if($UserInfo){
                $_SESSION["motu"]["uid"] = $intUsuario;
                $_SESSION["motu"]["UserName"] = $UserInfo['nickname'];
                $_SESSION["motu"]["Password"] = $UserInfo['password'];
                $_SESSION["motu"]["tipo"] = $UserInfo["type"];
                $_SESSION["motu"]["class"] = $UserInfo["class"];
                $_SESSION["motu"]["logged"] = true;
                $boolReturn = true;
            }

            return $boolReturn;

        }

        function login($strUser, $strPassword, $intUid = false){
            $strSesionid = session_id();

            $strUser = strtolower(trim($strUser));
            if (!preg_match("/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/",$strUser)) {
                // if(!preg_match("/^[a-z]+$/",$strUser)){
                return false;
            }
            $boolSetSession = false;
            if($intUid === false){
                $strPassword = md5($strPassword);
                $strQuery = "SELECT * FROM main_user 
                            WHERE   nickname = '{$strUser}' AND 
                                    password = '{$strPassword}' AND 
                                    active = 'Y'";
                $sql = $this->sql_ejecutarKey($strQuery);
                $boolSetSession = true;
            }
            else{
                if(!preg_match("/^[0-9]+$/",$intUid)){
                    $intUid = 0;
                }
                $strPassword = $this->sql_escape($strPassword);
                $sql = $this->sql_ejecutarKey("SELECT * FROM main_user Auser
                                                WHERE   nickname = '{$strUser}' AND 
                                                        password = '{$strPassword}' AND 
                                                        active = 'Y'");
            }
            if($sql){
                if($boolSetSession){
                    $this->llenar_session($sql["userid"]);
                }
                return true;
            }
            $this->LogOut();
            return FALSE;
        }

        function LogOut(){
            session_destroy();
            unset($_SESSION["motu"]);
            return true;
        }

        function check_login(){
            if(isset($_SESSION["motu"])){
                if ($this->login($_SESSION["motu"]["UserName"],$_SESSION["motu"]["Password"],$_SESSION["motu"]["uid"])) {
                    return true;
                }
            }
            return false;
        }
    }
}