<?php
/**
 * Created by PhpStorm.
 * User: Alexander Flores
 * Date: 01-sep-16
 * Time: 4:25 p.m.
 */

namespace settings\webservices\View{

    include_once("modules/settings/settings_view.php");

    use kernel\Controller\debug;
    use kernel\View\view;

    class webservices_view extends \settings_view implements view {

        private static $_instance;
        private $strReport = "";

        public function __construct($strAction){
            parent::__construct($strAction);
        }

        public static function getInstance($strAction = "") {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self($strAction);
            }
            return self::$_instance;
        }

        public function getArrButtons(){
            $arrButtons = array();
            $arrButtons[] = array("title"=>"Nuevo","name"=>"btnAdd","onclick"=>"getWebservices()");
            $arrButtons[] = array("title"=>"Guardar","name"=>"btnSave","onclick"=>"saveService()");
            $arrButtons[] = array("title"=>"Cancelar","name"=>"btnCancel","onclick"=>"cancelar()");
            return $arrButtons;
        }

        public function setStrReport($strReport){
            $this->strReport = $strReport;
        }

        public function scripts(){
            ?>
            <script type="text/javascript">
                $(document).ready(function(){
                    $("#btnSave").hide();
                    $("#btnCancel").hide();
                });

                function cancelar(){
                    $("#btnAdd").show();
                    $("#btnSave").hide();
                    $("#btnCancel").hide();

                    $(".div-reporte").removeClass("hide");
                    $(".div-formulario").addClass("hide");
                    $(".div-formulario").html("");

                }

                function getWebservices(id){
                    $("#btnAdd").hide();
                    $("#btnSave").show();
                    $("#btnCancel").show();

                    var filter = "";
                    if(id) filter = "&uuid="+id;

                    $.ajax({
                        type: "GET",
                        url: "<?php print $this->getStrAction() ?>&frm=true"+filter,
                        dataType: "HTML",
                        beforeSend: function(){
                            waitingDialog.show();
                        },
                        success: function(data){
                            waitingDialog.hide();

                            $(".div-reporte").addClass("hide");
                            $(".div-formulario").removeClass("hide");
                            $(".div-formulario").html(data);
                        },
                        error: function(){
                            waitingDialog.hide();
                        }
                    });
                }

                function delete_line(obj){
                    if(!obj)return false;
                    if($(obj).parent().parent().parent().find("tr").length < 2){
                        dialogModal("No se puede eliminar esta fila");
                        return false;
                    }
                    $(obj).parent().parent().remove();
                }

                function addLine(id){
                    if(!id)return false;
                    var intCount = 0;
                    if(id === "tblParams"){
                        intCount = $("#countParams").val();
                    }
                    else{
                        intCount = $("#countFuntions").val();
                    }
                    $.ajax({
                        type:"GET",
                        url:"<?php print $this->getStrAction(); ?>&add=true&count="+intCount+"&opt="+id,
                        dataType: "html",
                        success: function(data){
                            $("#"+id+" tbody").append(data);
                            if(id === "tblParams"){
                                intCount = (intCount * (1)) + 1;
                                $("#countParams").val(intCount);
                            }
                            else{
                                intCount = (intCount * (1)) + 1;
                                $("#countFuntions").val(intCount);
                            }
                        }
                    });
                }
                function saveService(){
                    $.ajax({
                        type:"POST",
                        url:"<?php print $this->getStrAction(); ?>&op=save",
                        data: $("#frmWebservice").serialize(),
                        dataType: "JSON",
                        beforeSend: function(){
                            waitingDialog.show();
                        },
                        success: function(data){
                            waitingDialog.hide();
                            if(data.status === "ok"){
                                $("#mdlProfiles").modal("hide");
                                setTimeout(function(){ fntGetPage('page=webservices&mod=settings',"Administración de webservices"); }, 500);
                            }
                            else{
                                dialogModal(data.msj)
                            }

                        },
                        error: function(){
                            waitingDialog.hide();
                        }
                    });
                }
            </script>
            <?php
        }

        public function drawPage(){
            $this->scripts();
            $this->draw_headlines("btnWebservicesActions");
            ?>
            <div class="Content container bg-white">
                <div class="row">
                    <div class="col-lg-12 div-reporte"><?php print $this->strReport; ?></div>
                    <div class="col-lg-12 hide div-formulario"></div>
                </div>
            </div>
            <?php
        }

        public function drawContent($arrInfo = false){
            $intCountParams = (isset($arrInfo["params"]))?count($arrInfo["params"]):1;
            $intCountFunctions = (isset($arrInfo["function"]))?count($arrInfo["params"]):1;
            $strOp = "";
            $strNameClass = "";
            $strPathClass = "";
            $strResponseClass = "";
            $strModuleClass = "";
            $strAccessClass = "freeAccess";
            $strAllowedClass = "";
            $strFormatClass = "";
            $strDescripcionClass = "";
            $strActiveClass = "";
            $strPublicClass = "";
            $strCheckConfig = "";
            if(isset($arrInfo["webservice"])){
                $strOp = $arrInfo["webservice"]["op_uuid"];
                $strNameClass = $arrInfo["webservice"]["class_mainClass"];
                $strPathClass = $arrInfo["webservice"]["path_mainClass"];
                $strResponseClass = $arrInfo["webservice"]["method_response"];
                $strModuleClass = $arrInfo["webservice"]["modulo"];
                $strAccessClass = $arrInfo["webservice"]["acceso"];
                $strAllowedClass = $arrInfo["webservice"]["allowed_format"];
                $strFormatClass = $arrInfo["webservice"]["format_response"];
                $strDescripcionClass = $arrInfo["webservice"]["descripcion"];
                $strActiveClass = $arrInfo["webservice"]["activo"];
                $strPublicClass = $arrInfo["webservice"]["publica"];
                $strCheckConfig = $arrInfo["webservice"]["check_config_device"];
            }
            ?>
            <form id="frmWebservice">
                <input type="hidden" id="countParams" value="<?php print $intCountParams; ?>">
                <input type="hidden" id="countFuntions" value="<?php print $intCountFunctions; ?>">
                <fieldset>
                    <legend>Configuración de webservices</legend>
                    <div class="row">
                        <div class="col-lg-5">
                            <?php $this->draw_input("Código de operación", "txtOp", "text",$strOp); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3"><?php $this->draw_input("Nombre de la clase", "name_class", "text",$strNameClass) ?></div>
                        <div class="col-lg-3"><?php $this->draw_input("Path", "path_class", "text",$strPathClass) ?></div>
                        <div class="col-lg-3"><?php $this->draw_input("Método de respuesta", "method_response", "text",$strResponseClass) ?></div>
                        <div class="col-lg-3"><?php $this->draw_input("Módulo", "module", "text",$strModuleClass) ?></div>
                        <div class="col-lg-3"><?php $this->draw_input("Access", "access", "text",$strAccessClass) ?></div>

                        <div class="col-lg-3"><?php $this->draw_input("Modos permitidos", "allowed", "multiselect",$strAllowedClass, array("option"=>array("w"=>"w","am"=>"am","wm"=>"wm"))) ?></div>
                        <div class="col-lg-3"><?php $this->draw_input("Formatos de respuesta", "format_response", "multiselect", $strFormatClass, array("option"=>array("json"=>"json","html"=>"html","xmlno"=>"xmlno"))) ?></div>
                        <div class="col-lg-3"><?php $this->draw_input("Descripción", "descripcion", "text",$strDescripcionClass) ?></div>
                        <div class="col-lg-3"><?php $this->draw_input("Activo", "active", "check",$strActiveClass) ?></div>
                        <div class="col-lg-3"><?php $this->draw_input("Público", "public", "check",$strPublicClass) ?></div>

                        <div class="col-lg-3"><?php $this->draw_input("Check config device", "check_config", "check",$strCheckConfig) ?></div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Parámetros</legend>
                    <div class="col-lg-12">
                        <div  class="table-responsive" style="height:auto; width:97%;">
                            <table class="table table-bordered" cellspacing="0" cellpadding="0" id="tblParams" data-toggle="tooltip" title="Recuerda que el método de valicación tiene que estar en tu clase.">
                                <thead>
                                <tr>
                                    <th align="center">Obligatorio</th>
                                    <th align="center">Descripción</th>
                                    <th align="center">Método de validación</th>
                                    <th align="center">Key del parámetro</th>
                                    <th align="center">Error devuelto</th>
                                    <th align="center">Key transaformado</th>
                                    <th align="center">Eliminar</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    //Si existen
                                    $intCount = 0;
                                    if(isset($arrInfo["params"])){
                                        if(count($arrInfo["params"])){
                                            foreach($arrInfo["params"] AS $val){
                                                ?>
                                                <tr>
                                                    <td align="center"><?php $this->draw_input("", "required_{$intCount}", "check-ios",$val["required"]) ?></td>
                                                    <td align="center"><?php $this->draw_input("", "desc_{$intCount}", "text",$val["parameter_description"]) ?></td>
                                                    <td align="center"><?php $this->draw_input("", "validate_{$intCount}", "text",$val["method_validation"]) ?></td>
                                                    <td align="center"><?php $this->draw_input("", "key_{$intCount}", "text",$val["key_parameter"]) ?></td>
                                                    <td align="center"><?php $this->draw_input("", "error_{$intCount}", "text",$val["error_response"]) ?></td>
                                                    <td align="center"><?php $this->draw_input("", "trans_{$intCount}", "text",$val["transform_key"]) ?></td>
                                                    <td align="center"><i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i></td>
                                                </tr>
                                                <?php
                                                unset($val);
                                                $intCount++;
                                            }
                                        }
                                        else{
                                            $this->draw_line("tblParams",$intCount);
                                        }
                                    }
                                    else{
                                        $this->draw_line("tblParams",$intCount);
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <button type="button" class="btn btn-default" onclick="addLine('tblParams')">Agregar párametro</button>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Funciones a validar(se valida en setParams)</legend>
                    <div class="col-lg-12">
                        <div  class="table-responsive" style="height:auto; width:60%;">
                            <table class="table table-bordered" cellspacing="0" cellpadding="0" id="tblFunctions" data-toggle="tooltip" title="Si el checkbox no está activo, se buscara la función en la clase creada.">
                                <thead>
                                <tr>
                                    <th align="center">Nombre de la función</th>
                                    <th align="center">Derivada de "webservices_baseClass"</th>
                                    <th align="center">Eliminar</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $intCountFunction = 0;
                                if(isset($arrInfo["function"])){
                                    if(count($arrInfo["function"])){
                                        foreach($arrInfo["function"] AS $val){
                                            ?>
                                            <tr>
                                                <td align="center"><?php $this->draw_input("", "function_{$intCountFunction}", "text",$val["str_function"]) ?></td>
                                                <td align="center">
                                                    <?php $this->draw_input("", "derived_{$intCountFunction}", "check-ios",$val["webservices_baseClass"]) ?>
                                                </td>
                                                <td align="center">
                                                    <i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i>
                                                </td>
                                            </tr>
                                            <?php
                                            unset($val);
                                            $intCountFunction++;
                                        }
                                    }
                                    else{
                                        $this->draw_line("tblFunctions",$intCountFunction);
                                    }
                                }
                                else{
                                    $this->draw_line("tblFunctions",$intCountFunction);
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <button type="button" class="btn btn-default" onclick="addLine('tblFunctions')">Agregar función</button>
                    </div>
                </fieldset>

            </form>
            <?php
        }

        public function draw_input($strName,$strNameField,$strType,$value = "",$extra = array()){
            if($strType == "text"){
                if(!empty($strName)){
                    ?><b><?php print $strName; ?></b><br><?php
                }
                ?>
                <input type="text" class="field_textbox" name="<?php print $strNameField; ?>" id="<?php print $strNameField; ?>"
                       value="<?php print $value; ?>" placeholder="<?php print isset($extra["hint"])?$extra["hint"]:""; ?>">
                <?php
            }
            else if($strType == "check"){
                ?>
                &nbsp;<br>
                <div class="divCheckbox">
                    <input type="checkbox" name="<?php print $strNameField; ?>" id="<?php print $strNameField; ?>" class="chk" <?php print ($value == "Y")?"checked":""; ?>/>
                    <label class="labelRadio" for="<?php print $strNameField; ?>"><?php print $strName; ?></label>
                </div>
                <?php
            }
            elseif($strType == "check-ios"){
                ?>
                <div class="slideThree" dt-active="Si" dt-not-active="No">
                    <input type="checkbox" class="ios-chk" id="<?php print $strNameField; ?>" name="<?php print $strNameField; ?>" <?php print ($value == "Y")?"checked":""; ?> />
                    <label for="<?php print $strNameField; ?>"></label>
                </div>
                <?php
            }
            else if($strType == "multiselect"){
                if(!isset($extra["option"]))return false;
                $arrValue = explode(",", $value);
                ?>
                <b><?php print $strName; ?></b><br>
                <select class="field_listbox" name="<?php print $strNameField; ?>[]" multiple class="chosen-select" id="<?php print $strNameField; ?>" data-placeholder="Seleccione opciones" style="width: 75%;">
                    <?php
                    foreach($extra["option"] AS $key => $val){
                        ?>
                        <option value="<?php print $key; ?>" <?php print (in_array($key, $arrValue))?"selected":""; ?> ><?php print $val; ?></option>
                        <?php
                        unset($key);
                        unset($val);
                    }
                    ?>
                </select>
                <script>
                    $(document).ready(function(){
                        $("#<?php print $strNameField; ?>").chosen({
                            no_results_text: "No se encontraron resultados para"
                        });
                    });
                </script>
                <?php
            }
        }

        public function draw_line($opt,$intCount){
            if($opt == "tblParams"){
                ?>
                <tr>
                    <td align="center"><?php $this->draw_input("", "required_{$intCount}", "check-ios") ?></td>
                    <td align="center"><?php $this->draw_input("", "desc_{$intCount}", "text") ?></td>
                    <td align="center"><?php $this->draw_input("", "validate_{$intCount}", "text") ?></td>
                    <td align="center"><?php $this->draw_input("", "key_{$intCount}", "text") ?></td>
                    <td align="center"><?php $this->draw_input("", "error_{$intCount}", "text") ?></td>
                    <td align="center"><?php $this->draw_input("", "trans_{$intCount}", "text") ?></td>
                    <td align="center"><i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i></td>
                </tr>
                <?php
            }
            else{
                ?>
                <tr>
                    <td align="center"><?php $this->draw_input("", "function_{$intCount}", "text") ?></td>
                    <td align="center">
                        <?php $this->draw_input("", "derived_{$intCount}", "check-ios") ?>
                    </td>
                    <td align="center">
                        <i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i>
                    </td>
                </tr>
                <?php
            }
        }
    }
}