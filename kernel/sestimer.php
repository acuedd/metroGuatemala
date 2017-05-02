<?php
/**
 * Created by PhpStorm.
 * User: acuedd
 * Date: 30/08/2016
 * Time: 10:44 AM
 */
?>
<script type="application/javascript">
    var intSesExpTimer = 0;
    var intSesExpTotalTime = 5*60000;
    var intSesExpFailCounter = 0;
    var objSesExpMiniWindow = false;
    intSesExpTimer = setTimeout(SesCheckExpiration,intSesExpTotalTime);
    function SesCheckExpiration(){
        clearTimeout(intSesExpTimer);
        $.ajax({
            type:"GET",
            url: "index.php?sestimer=true",
            success: function(data){
            if(data.valido == 1){
                var objFunction = function() { SesCheckExpiration(); };
                intSesExpTimer = setTimeout(objFunction, intSesExpTotalTime);
            }
            else{
                showSesExpirationMiniWindow();
            }
        },
            error: function(){

        }
        });
    }

    function showSesExpirationMiniWindow(){
        dialogModal("Ha caducado su sesión, ingrese nuevamente a la pagina","AUTENTICACIÓN", true);
    }
</script>
