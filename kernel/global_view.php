<?php
/**
 * Created by PhpStorm.
 * User: acuedd
 * Date: 30/08/2016
 * Time: 10:44 AM
 */
namespace kernel\View{

	use kernel\Controller\debug;
	use kernel\Controller\global_controller;
    use kernel\Controller\global_function;

    include_once("kernel/global_controller.php");
    class global_view{
        /*es para saber de donde viene la pagina
    * @access private
    */
        private $strAction;
        protected $arrParam = array();
        protected $arrAccess = array();
        /*Contine el nombre de la pagina
        * @access private
        */
        private $strNamePage;
        function getArrParam() {
            return $this->arrParam;
        }

        function setArrParam($arrParam) {
            $this->arrParam = $arrParam;
        }

        public function getParam($strTerm, $default = "", $boolUTF8 = false) {
            return global_function::getParam($this->arrParam, $strTerm, $default, $boolUTF8);
        }

        private static $_instance;

        function __construct($strAction){
            $this->setStrAction($strAction);
        }

        /*Evitamos el clonaje del objeto. Patr?n Singleton*/
        private function __clone(){ }

        /*Funci?n encargada de crear, si es necesario, el objeto. Esta es la funci?n que debemos llamar desde fuera de la clase para instanciar el objeto, y as?, poder utilizar sus m?todos*/
        public static function getInstance($strAction){
            if (!(self::$_instance instanceof self)){
                self::$_instance=new self($strAction);
            }
            return self::$_instance;
        }

        public function setArrAccess($arrAccess){
            $this->arrAccess = $arrAccess;
        }

        /*Getter y setter*/
        public function getStrAction(){
            if(empty($this->strAction)) $this->strAction = "";
            return $this->strAction;
        }
        public function setStrAction($strTMP){
            $this->strAction = $strTMP;
        }
        public function getStrNamePage(){
            if(empty($this->strNamePage)) $this->strNamePage = "";
            return $this->strNamePage;
        }
        public function setStrNamePage($strName){
            $this->strNamePage = $strName;
        }

    public function getCabecera($strTittle = "",$includeMenu = true, $strHeader = false){
        $this->setStrNamePage($strTittle);
        $strSRC = "images/user_Male.jpg";
        $boolLogged = (!empty($_SESSION["motu"]["logged"]));
        ?>
        <!DOCTYPE>
        <html lang="es">
        <head>
            <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
            <!--<meta http-equiv="content-type" content="text/html; charset=UTF-8;" />-->
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="Club rotario Guatemala">
            <meta name="author" content="Homeland">
            <title><?php print $this->getStrNamePage(); ?></title>
            <meta http-equiv="content-type" content="text/html; charset=iso-8859-1;">
            <meta name="robots" content="index, follow">
            <?php
            $this->global_scripts();
            ?>
        </head>
        <script type="text/javascript">
            <?php
            if($boolLogged){
            ?>
            <?php
            }
            ?>
        </script>

        <body id="PageBody" onLoad="">

        <?php
        if(!empty($strHeader)){
	        include_once($strHeader);
        }
        else{
            ?>
            <header>
                <!--<div id="bodyT">-->
		        <?php
		        if($includeMenu){
			        $objCont = new global_controller();
			        $arrMenus = $objCont->get_arrayMenu();
			        $varMenu = new menu($arrMenus,$boolLogged);
			        $varMenu->admin_navigation($strTittle);
		        }
		        ?>
                <!-- Modal -->
                <div class="modal fade" id="MyglobalModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Mensaje del sistema</h4>
                            </div>
                            <div id="myModalContent" class="modal-body">

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="divglobal_load"></div>
            </header>
            <?php
        }
        ?>
        <section>
            <div id="contentPage" class="view-page-container">

                <?php

                }

                public function getPiePagina($strFooter = ""){
                ?>
            </div>
        </section>
        <?php
        if(file_exists($strFooter)){
            include_once($strFooter);
        }
        else{
            ?>
            <!--<footer class="bottomT">
                <div style="float: left;">

                </div>
                <div style="width: 10%;margin-left: 32%;float: left;">

                </div>
                <div style="float: right;">

                </div>
            </footer>-->
            <?php
        }
        ?>
        </body>
        </html>
        <?php
    }

        public function getLeft(){

        }
        public function getRight(){

        }
        public function global_scripts(){
            ?>

            <link type="text/css" rel="stylesheet" href="views/fonts/font-awesome/css/font-awesome.min.css"/>
            <script type="text/javascript" src="librarys/js/jquery.js"></script>
            <!--<script type="text/javascript" src="librarys/js/spritely/jq.spritely.js"></script>-->
            <!--<script type="text/javascript" src="librarys/js/ui/jquery.ui.js"></script>-->
            <!--<link rel="stylesheet" type="text/css" href="librarys/js/ui/jquery.ui.css">-->
            <!--        <script type="text/javascript" src="librarys/js/notice/jquery.notice.js"></script>
                    <link rel="stylesheet" type="text/css" href="librarys/js/notice/jquery.notice.css">-->
            <script type="text/javascript" src="librarys/js/bootstrap/js/bootstrap.min.js"></script>
            <link rel="stylesheet" type="text/css" href="librarys/js/bootstrap/css/bootstrap.min.css">

            <?php //para el menu ?>
            <!--<script type="text/javascript" src="librarys/js/mb/_inc/jquery.hoverIntent.min.js" ></script>
            <script type="text/javascript" src="librarys/js/mb/_inc/jquery.metadata.js" ></script>
            <script type="text/javascript" src="librarys/js/mb/menu/jq.mb.menu.js" ></script>
            <link rel="stylesheet" type="text/css" href="librarys/js/mb/menu/jq.mb.menu.css" >-->
            <link rel="stylesheet" type="text/css" href="librarys/js/wysiwyg-editor/editor.css" >
            <script type="text/javascript" src="librarys/js/wysiwyg-editor/editor.js"></script>

            <script type="text/javascript" src="librarys/js/moment/moment.js"></script>
            <link rel="stylesheet" type="text/css" href="librarys/js/datetimerpicker/datetimerpicker.css" >
            <script type="text/javascript" src="librarys/js/datetimerpicker/datetimerpicker.js"></script>

            <link rel="stylesheet" type="text/css" href="librarys/js/chosen/jquery.chosen.css" >
            <script type="text/javascript" src="librarys/js/chosen/jquery.chosen.min.js"></script>

            <link rel="stylesheet" type="text/css" href="librarys/js/timepicker/bootstrap-timepicker.css" >
            <script type="text/javascript" src="librarys/js/timepicker/bootstrap-timepicker.js"></script>

            <script type="text/javascript" src="librarys/js/library.js"></script>

            <script type="text/javascript" src="views/js/new-age.js"></script>

            <!--<link type="text/css" rel="stylesheet" href="views/css/estilos.css" />-->
            <link type="text/css" rel="stylesheet" href="views/css/new-age.css" />
            <link type="text/css" rel="stylesheet" href="views/css/site_bootstrap.css" />
            <link type="text/css" rel="stylesheet" href="views/css/my_style.css" />
            <link type="text/css" rel="stylesheet" href="views/css/style.css" />


            <!-- Custom Fonts -->
            <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css?family=Catamaran:100,200,300,400,500,600,700,800,900" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css?family=Muli" rel="stylesheet">

            <script type="text/javascript">

                function fntGetPage(strLink, strName, $boollink){
                    var params = "";
                    if(!$boollink) $boollink = false;
                    if(!(strName)) strName = "";
                    else params = "&name="+strName;

                    if($boollink){
                        var objForm = $("<form ></form>",{
                            "action":'<?php print $this->getStrAction(); ?>',
                            "method":'post',
                            "name":"formlnk"
                        });
                        strLink += params+"&act=lnk";
                        var arrlinds = strLink.split("&");
                        $.each(arrlinds, function (i, val){
                            var attr = val.split("=");
                            var input = $("<input />",{ "type":"hidden", "name": attr[0], "value":attr[1] });
                            objForm.append(input);
                        });

                        $("#PageBody").append(objForm);
                        objForm.submit();
                    }
                    else{
                        $.ajax({
                            type:   "GET",
                            url :   '<?php print $this->getStrAction(); ?>?act=ajax&' +strLink+params,
                            beforeSend: function(){
                                waitingDialog.show();
                                if($(".ui-dialog").length) $(".ui-dialog").remove();

                            },
                            success: function (data){
                                waitingDialog.hide();
                                if(typeof data.valido != "undefined"){
                                    dialogModal(data.msg, (data.valido == 1)? "MENSAJE DEL SISTEMA":"ALERTA",true);
                                }
                                else{
                                    $("#contentPage").html(data);

                                    //Limpio estas insersiones que no se donde se generan
                                    $("#InsertLink").remove();
                                    $("#InsertImage").remove();
                                    $("#InsertTable").remove();
                                }
                            },
                            error: function(){
                                waitingDialog.hide();
                            }
                        });
                    }
                }

                <?php
                if(isset($_GET["print"])){
                ?>
                window.print();
                <?php
                }
                ?>
            </script>
            <?php
        }

        public function drawTema($strTittle = ""){
            $this->getCabecera($strTittle);
            $this->getPiePagina();
        }

        public function fntWindowUnable(){
            ?>
            <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
                <tr>
                    <td align="center" valign="center">
                        <div class="ui-state-error ui-corner-all" style="padding: 0 .7em; min-height: 40px; vertical-align: middle;">
                            <p style="vertical-align: middle;">
                                <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
                                <strong>Alerta: </strong>
                                Ventana no definida!!!
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
            <?php
        }

        public function fntAlerta($strTitle, $strTexto = ""){
            ?>
            <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
                <tr>
                    <td align="center" valign="center">
                        <div class="ui-state-error ui-corner-all" style="padding: 0 .7em; min-height: 40px; vertical-align: middle;">
                            <p style="vertical-align: middle;">
                                <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
                                <strong><?php print $strTitle; ?> </strong>
                                <?php print $strTexto; ?>
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
            <?php
        }

    public function initForm($strName, $boolTargetBlank = false, $strAction = ""){
        if(empty($strAction)){
            $strAction = $this->getStrAction();
        }
        ?>
        <form action="<?php print $strAction; ?>" name="<?php print $strName?>" id="<?php print $strName?>" method="post" enctype="multipar/form-data" <?php print ($boolTargetBlank)?"target='_blank'":""; ?> >
            <input type="hidden" name="<?php print $strName; ?>_hddn" id="<?php print $strName; ?>_hddn" value="1">
            <?php
            }

            public function finForm(){
            ?>
        </form>
        <?php
    }

        public function getButtons($strName, $arrButtons, $intMax = 5,$boolDragable = true){
            ?>
            <script type="text/javascript">
                var intWidth = 100;
                $(function(){
                    if($.browser.msie){
                        $("#<?php  print $strName; ?>").addClass("IEMenuButton");
                    }
                    else{

                    }
                    $("#<?php print $strName; ?>").addClass("MenuButton");
                });
            </script>
            <div id="<?php print $strName; ?>">
                <p >
                    <?php
                    $count = 0;
                    $intNum = 0;
                    $boolShow = false;
                    if(is_array($arrButtons) && count($arrButtons)){
                        while($arrT = each($arrButtons)){
                            $count++;
                            $boolShow = true;

                            if($intNum >= $intMax){
                                $intNum = 0;
                                print "</p>";
                                print "<p>";
                            }
                            $strClass = (empty($arrT["value"]["class"]))?"btn-warning":$arrT["value"]["class"];
                            ?>
                            <button type="button" class="btn <?php print $strClass; ?> btn-md butimg<?php print $arrT["key"]; ?>" name="<?php print $arrT["value"]["name"]; ?>"
                                    id="<?php print $arrT["value"]["name"]; ?>" onclick="<?php print $arrT["value"]["onclick"]; ?>"
                                <?php print (isset($arrT["value"]["tags"]))?$arrT["value"]["tags"]:""; ?> >
                                <?php print $arrT["value"]["title"]; ?>
                            </button>
                            <?php
                            $intNum++;

                            $arrT = false;
                        }
                    }

                    ?>
                </p>
            </div>
            <?php
        }

        public function draw_headlines($nameSpace = ""){
            ?>
            <div class="row-header">
                <div class="col-lg-5">
                    <h3><?php print $this->getStrNamePage(); ?></h3>
                </div>
                <div class="col-lg-7 pull-right margin-btn">
                    <?php $this->getButtons($nameSpace,$this->getArrButtons()); ?>
                </div>
            </div>
            <?php
        }
    }

    class menu{
        private $boolLogged = false;
        private $arrLinks = array();
        private $strIdMenu = "menu";
        function __construct($arrMenus, $boollogged) {
            $this->arrLinks = $arrMenus;
            $this->boolLogged = $boollogged;
        }

        function admin_navigation($strTittle = ""){
            ?>
            <style>
                .navbar{
                    border-radius: 0;
                }
                /* navbar */
                .navbar-default {
                    background-color: #273a99;
                    border-color: #273a99;
                }
                /* title */
                .navbar-default .navbar-brand {
                    color: #ffffff;
                }
                .navbar-default .navbar-brand:hover,
                .navbar-default .navbar-brand:focus {
                    color: #5E5E5E;
                }
                /* link */
                .navbar-default .navbar-nav > li > a {
                    color: #ffffff;
                    font-size: 14px;
                }
                .navbar-default .navbar-nav > li > a:hover,
                .navbar-default .navbar-nav > li > a:focus {
                    color: #F7901A;
                }
                .navbar-default .navbar-nav > .active > a,
                .navbar-default .navbar-nav > .active > a:hover,
                .navbar-default .navbar-nav > .active > a:focus {
                    color: #555;
                    background-color: #E7E7E7;
                }

                .dropdown-menu > .active > a,
                .dropdown-menu > .active > a:hover,
                .dropdown-menu > .active > a:focus {
                    background-color: #056874;
                }

                .dropdown-menu > li > a{
                    border-bottom: 1px solid #0080C1;
                    font-weight: bold;
                    color:#153477;
                }

                .dropdown-menu > li > a:hover,
                .dropdown-menu > li > a:focus{
                    background-color: transparent;
                    background-image: none;
                    color: #F7901A;
                    border-color: #F7901A;
                }

                .navbar-default .navbar-nav > .open > a,
                .navbar-default .navbar-nav > .open > a:hover,
                .navbar-default .navbar-nav > .open > a:focus {
                    color: #F7901A;
                    background-color: transparent;
                }
                /* caret */
                .navbar-default .navbar-nav > .dropdown > a .caret {
                    border-top-color: #FFFFFF;
                    border-bottom-color: #FFFFFF;
                }
                .navbar-default .navbar-nav > .dropdown > a:hover .caret,
                .navbar-default .navbar-nav > .dropdown > a:focus .caret {
                    border-top-color: #F7901A;
                    border-bottom-color: #F7901A;
                }
                .navbar-default .navbar-nav > .open > a .caret,
                .navbar-default .navbar-nav > .open > a:hover .caret,
                .navbar-default .navbar-nav > .open > a:focus .caret {
                    border-top-color: #F7901A;
                    border-bottom-color: #F7901A;
                }
                /* mobile version */
                .navbar-default .navbar-toggle {
                    border-color: #DDD;
                }
                .navbar-default .navbar-toggle:hover,
                .navbar-default .navbar-toggle:focus {
                    background-color: #DDD;
                }
                .navbar-default .navbar-toggle .icon-bar {
                    background-color: #CCC;
                }
                @media (max-width: 767px) {
                    .navbar-default .navbar-nav .open .dropdown-menu > li > a {
                        color: #fff;
                    }
                    .navbar-default .navbar-nav .open .dropdown-menu > li > a:hover,
                    .navbar-default .navbar-nav .open .dropdown-menu > li > a:focus {
                        color: #F7901A;
                    }
                }
                .navbar-login{
                    margin-top: 15px;
                    float: right;
                    padding-bottom: 10px;
                }
                .navbar-login > .img-circle{
                    background: #FEA01E;
                    width: 50px;
                    height: 50px;
                    text-align: center;
                    float: left;
                }
                .navbar-login > .img-circle > i{
                    color: white;margin-top: 5px;
                }
                .navbar-login > .user{
                    color: white;
                    margin-top: 15px;
                    float: left;
                    margin-left: 5px;
                    font-weight: bold;
                    font-size: 14px;
                    cursor: pointer;
                }
                .navbar-login > .user > i{
                    color: white;
                    margin-left: 7px;
                    margin-top: -17px;
                }
                .navbar-login-panel{
                    position: absolute;
                    top: 73px;
                    right: 0;
                    width: 200px;
                    background: #FFFFFF;
                    padding: 10px 5px;
                    font-size: 14px;
                    box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
                    visibility: hidden;
                    opacity: 0;
                    -webkit-transition: opacity 0.5s ease; /* For Safari 3.1 to 6.0 */
                    transition: opacity 0.5s ease;
                    z-index: 11 !important;
                }
                .navbar-login-panel-show{
                    visibility: visible;
                    opacity: 1;
                }
                .navbar-login-panel:before{
                    content: "";
                    width: 0;
                    height: 0;
                    position: absolute;
                    right: 4px;
                    top: -10px;
                    border-left: 10px solid transparent;
                    border-right: 10px solid transparent;
                    border-bottom: 10px solid #FFFFFF;
                }
                .navbar-login-panel > div{
                    width: 100%;
                    cursor: pointer;
                    padding: 7px 5px;
                }
                .navbar-login-panel > div:hover{
                    background-color: #F5F5F5;
                }
                .navbar-login-panel i{
                    margin-right: 7px;
                }
            </style>
            <script type="text/javascript">
                $(document).ready(function(){
                    $(".navbar-login .user").on("click",function(){
                        $(".navbar-login-panel").addClass("navbar-login-panel-show");
                    });
                    $(".navbar-login").on("mouseleave",function(){
                        $(".navbar-login-panel").removeClass("navbar-login-panel-show");
                    });
                });
            </script>
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" >
                            <img src="<?php print "views/img/logo.png"; ?>" class="img-responsive img-rounded" width="132" height="40" title="Tigo Pos" alt="Brand" onclick="document.location.href='index.php'" style="cursor: pointer;">
                        </a>
                    </div>
                    <div aria-expanded="false" id="navbar" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav">
                            <?php
                            if($this->boolLogged){
                                //debug::drawdebug($this->arrLinks);
                                foreach($this->arrLinks AS $value){
                                    ?>
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle text-center" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa <?php print $value["img"];  ?> fa-2x" aria-hidden="true"></i> <br>
                                            <?php print $value["modulo"];  ?> <b class="caret"></b>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <?php
                                            foreach($value["detalle"] AS $link){
                                                ?>
                                                <li><a style="cursor:pointer;" onclick="fntGetPage('<?php print $link["link"] ?>', '<?php print $link["name"]; ?>')"><?php print $link["name"]; ?></a></li>
                                                <?php
                                            }
                                            ?>
                                        </ul>
                                    </li>

                                    <?php
                                    unset($value);
                                }
                            }
                            ?>
                        </ul>

                        <?php
                        if($this->boolLogged){
                            $strSRC = "images/user_Male.jpg";

                            ?>
                            <ul class="nav navbar-nav navbar-right">
                                <?php
                                $intN = global_controller::getNotificacion();
                                if($intN){
                                    ?>
                                    <li onclick="fntGetPage('page=mensaje&mod=usuario');" style="cursor:pointer">
                                        <i class="fa fa-bell fa-2x text-primary" style="padding-top: 10px;"></i>
                                        <span class='badge' style="background-color:red;"><?php print $intN["notification"]; ?></span>
                                    </li>
                                    <?php
                                }
                                ?>
                                <li>
                                    <div class="navbar-login" style="z-index:3000;">
                                        <div class="img-circle">
                                            <i class="fa fa-user fa-3x"></i>
                                        </div>
                                        <div class="user">
                                            <?php print (isset($_SESSION["motu"]["UserName"]))?$_SESSION["motu"]["UserName"]:""; ?>
                                            <i class="fa fa-sort-desc"></i>
                                        </div>
                                        <div class="navbar-login-panel">
                                            <div onclick="fntGetPage('page=register&mod=users&opt=go','Mi cuenta', true);"><i class="fa fa-user"></i>Mi cuenta</div>
                                            <div onclick="document.location.href='index.php?logout=true'"><i class="fa fa-sign-out"></i>Cerrar sesión</div>
                                        </div>
                                    </div>
                                </li>
                            </ul >
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </nav>
            <?php
        }
    }

    interface view{
        public function getArrButtons();
        public function scripts();
        public function drawPage();
        public function drawContent();
    }
}