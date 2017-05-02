var userAgent = navigator.userAgent.toLowerCase();
jQuery.browser = {
    version: (userAgent.match(/.+(?:rv|it|ra|ie|me)[\/: ]([\d.]+)/) || [])[1],
    chrome: /chrome/.test(userAgent),
    safari: /webkit/.test(userAgent) && !/chrome/.test(userAgent),
    opera: /opera/.test(userAgent),
    msie: /msie/.test(userAgent) && !/opera/.test(userAgent),
    mozilla: /mozilla/.test(userAgent) && !/(compatible|webkit)/.test(userAgent)
};

function getElement(strName, objDoc) {
    var p, i, x = false;

    if (!objDoc)
        objDoc = document;

    if (objDoc[strName]) {
        x = objDoc[strName];
        if (!x.tagName)
            x = false;
    }

    if (!x && objDoc.all)
        x = objDoc.all[strName];
    for (i = 0; !x && i < objDoc.forms.length; i++)
        x = objDoc.forms[i][strName];
    if (!x && objDoc.getElementById)
        x = objDoc.getElementById(strName);
    for (i = 0; !x && objDoc.layers && i < objDoc.layers.length; i++)
        x = getDocumentLayer(strName, objDoc.layers[i].document);

    return x;

}

function ajaxJsonData(url, data, strFunBef, strFunSuccess, strFunError) {
    if (!strFunBef)
        strFunBef = false;
    if (!strFunSuccess)
        strFunSuccess = false;
    if (!strFunError)
        strFunError = false;
    var _resp;
    $.ajax({
        type: "POST",
        dataType: "json",
        data: data,
        url: url,
        cache: false,
        async: false,
        beforeSend: function () {
            if (strFunBef)
                eval(strFunBef)
        },
        success: function (data) {
            _resp = data;
            $.each(data, function (i, item) {
                if (item.estado == "ok") {
                    if (!item.msg)
                        dialogModal("Su informacion ha sido ejecutada satisfactoriamente", "Mensaje del sistema", false, strFunSuccess);
                    else
                        dialogModal(item.msg, "Mensaje del sistema", false, strFunSuccess);
                }
                else {
                    if (!item.error) {
                        if (!item.msg)
                            dialogModal("Ha ocurrido un error, por favor intentelo de nuevo <br/> si el error persiste contacte a su administrador", "ERROR AL EJECUTAR", true, strFunError);
                        else
                            dialogModal(item.msg, "Mensaje del sistema", false, strFunError);
                    }
                    else {
                        if (!item.msg)
                            dialogModal(item.error, "Mensaje del sistema", false, strFunError);
                        else {
                            var tempo = item.msg + "<br>" + item.error;
                            dialogModal(tempo, "Mensaje del sistema", false, strFunError);
                        }
                    }
                }
            });
            return _resp;
        },
        error: function () {
            _resp = {
                "return": "0",
                "msg": "Ha ocurrido un error en la ejecucion"
            }
            dialogModal("Hemos perdido conexción con internet. <br/> Se recargara la ventana para que intente de nuevo.", "Mensaje del sistema", true);
            return _resp;
        }
    });
}

function ajaxSendData(link, data, objInterface, objXHR, boolLoading) {
    boolError = false;
    if (!objXHR)
        boolForceAbort = false;
    else
        boolForceAbort = true;
    if (!boolLoading)
        boolLoading = false;
    else
        boolError = true;
    objInterface.html("");
    if (boolForceAbort) {
        if (objXHR)
            objXHR.abort();
    }
    objXHR = $.ajax({
        type: "POST",
        data: data,
        url: link,
        beforeSend: function () {
            if (boolLoading)
                waitingDialog.show();
        },
        success: function (data) {
            if (boolLoading)
                waitingDialog.hide();
            if (objInterface.length)
                objInterface.html(data);

            xhr = null;
        },
        error: function () {
            if (boolLoading)
                waitingDialog.hide();
        }
    });
    return objXHR;
}

function checkLength(o, oa, n, min, max, boolError) {
    if (!boolError)
        boolError = false;
    var boolMax = (max > 0) ? true : false;
    o.removeClass("ui-state-error");
    if (boolMax) {
        if (max == 0 || (o.val().length > max || o.val().length < min) || boolError) {
            o.addClass("ui-state-error");
            updateTips(oa, " El tamaï¿½o del campo " + n + " tiene que ser entre  " +
                    min + " y " + max + ".");
            return false;
        } else {
            return true;
        }
    }
    else {
        if ((o.val().length < min) || boolError) {
            o.addClass("ui-state-error");
            updateTips(oa, n);
            return false;
        }
        else {
            return true;
        }
    }
}

function updateTips(oa, t) {
    oa.append(t).addClass("ui-state-highlight");
    setTimeout(function () {
        oa.removeClass("ui-state-highlight", 1500).addClass("ui-state-error");
    }, 500);
}

function clearTips() {
    $("#divglobal_load").hide().html("");
    $(".ui-state-error").removeClass("ui-state-error");
}

function checkRegexp(o, regexp, n) {
    if (!(regexp.test(o.val()))) {
        o.addClass("ui-state-error");
        updateTips(o, n);
        return false;
    } else {
        return true;
    }
}

function checkForm(arrCamposRequerido, boolError) {
    var bookExiste = true;
    var arrNotExist = new Array();
    if (!boolError)
        objError = false;
    else {
        objError = $("#divglobal_load");
        objError.html("").hide();
    }
    strError = "";
    var boolOK = true;
    for (var i in arrCamposRequerido) {
        if ($("input[name=" + i + "]").length) {
            $("input[name=" + i + "]").each(function () {
                if (!objError) {
                    if ($(this).val() == "" || $(this).val() == 0 || $(this).val().length == 0) {
                        strError += arrCamposRequerido[i];
                    }
                }
                else {
                    objError.show();
                    boolForceUpTip = false;
                    if ($(this).val() == "" || $(this).val() == 0 || $(this).val().length == 0) {
                        boolOK = false;
                        boolForceUpTip = true;
                    }
                    boolTMP = checkLength($(this), objError, arrCamposRequerido[i], 1, 0, boolForceUpTip);
                    boolOK = boolOK && boolTMP;
                }
            });
        }
        else if ($("select[name=" + i + "]").length) {
            $("select[name=" + i + "]").each(function () {
                if (!objError) {
                    if ($(this).val() == "" || $(this).val() == 0) {
                        strError += arrCamposRequerido[i];
                    }
                }
                else {
                    if ($(this).val() == "" || $(this).val() == 0) {
                        boolOK = false;
                    }
                }
            });
        }
        else {
            strError += arrCamposRequerido[i];
            boolOK = false;
            bookExiste = false;
            arrNotExist[i] = arrCamposRequerido[i];
        }
    }

    if (!objError) {
        if (strError == "") {
            return true;
        }
        else {
            dialogModal(strError);
            return false;
        }
    }
    else {
        if (boolOK)
            return true;
        else {
            if (!bookExiste) {
                var strNotExistmsg = "No existen algunos elementos que intenta validar.";
                var count = 0;
                for (var j in arrNotExist) {
                    strNotExistmsg += "\t\n " + j;
                    count++;
                }
                if (count > 0) {
                    dialogModal(strNotExistmsg);
                }
            }
            return false;
        }
    }
}

function ucWords(string) {
    if (string != null) {
        var arrayWords;
        var returnString = "";
        var len;
        arrayWords = string.split(" ");
        len = arrayWords.length;
        for (i = 0; i < len; i++) {
            if (i != (len - 1)) {
                returnString = returnString + ucFirst(arrayWords[i]) + " ";
            }
            else {
                returnString = returnString + ucFirst(arrayWords[i]);
            }
        }
        return returnString;
    }
}
function ucFirst(string) {
    return string.substr(0, 1).toUpperCase() + string.substr(1, string.length).toLowerCase();
}

function array_flip(trans) {
    var key, tmp_ar = {};
    if (trans && typeof trans === 'object' && trans.change_key_case) { // Duck-type check for our own array()-created PHPJS_Array
        return trans.flip();
    }
    for (key in trans) {
        if (!trans.hasOwnProperty(key)) {
            continue;
        }
        tmp_ar[trans[key]] = key;
    }
    return tmp_ar;
}

function debugJs($MyVar, $strName) {
    if (!$MyVar)
        var $MyVar;
    if (!$strName)
        var $strName = "VarType " + typeof $MyVar;
    else
        $strName = 'Var "' + $strName + '" ' + "Type " + typeof $MyVar;
    console.log($strName);
    console.log($MyVar);
}

function isLeapYear(intYear) {
    if (intYear % 4 == 0) {
        if (intYear % 100 == 0) {
            if (intYear % 400 == 0) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return true;
        }
    }
    else {
        return false;
    }
}

function boolCheckDate(intYear, intMonth, intDay) {
    boolReturn = true;
    if (!intYear)
        intYear = "";
    if (!intMonth)
        intMonth = "";
    if (!intDay)
        intDay = "";

    arrMeses = new Array();
    arrMeses[1] = 31;
    arrMeses[2] = (isLeapYear(intYear)) ? 29 : 28;
    arrMeses[3] = 31;
    arrMeses[4] = 30;
    arrMeses[5] = 31;
    arrMeses[6] = 30;
    arrMeses[7] = 31;
    arrMeses[8] = 31;
    arrMeses[9] = 30;
    arrMeses[10] = 31;
    arrMeses[11] = 30;
    arrMeses[12] = 31;

    intYear = validarEntero(intYear);
    intMonth = validarEntero(intMonth);
    intDay = validarEntero(intDay);

    if (intYear.length == 0)
        boolReturn = false;
    else if (intMonth.length == 0)
        boolReturn = false;
    else if (intDay.length == 0)
        boolReturn = false;

    if (intMonth > 12 || intMonth < 1) {
        boolReturn = false;
    }
    else {
        if (intDay > arrMeses[intMonth * 1] || intDay < 1) {
            boolReturn = false;
        }
    }

    return boolReturn;
}
/*Para el uso correcto de la function incluir el plugin de noticeAdd de jquery
 * o = Objeto de jquery eje: $("#tuobjeto")
 * min = minimo de caracteres
 * max = maximo de caracteres
 * n = algun titulo para el objeto
 * mostrar o no el error
 */
var checkLength = function (o, min, max, n, boolError) {
    if (!n)
        n = false;
    if (!boolError)
        boolError = false;
    var boolMax = (max > 0) ? true : false;
    var strTitle = "";
    if (n)
        var strTitle = n;
    if (boolMax) {
        if (max == 0 || (o.val().length > max || o.val().length < min)) {
            if (boolError) {
                jQuery.noticeAdd({text: "<b>El tamaï¿½o del campo " + strTitle + " tiene que ser entre " + min + " y " + max + ". !</b><br><br>", type: "warning", stay: false});
            }
            return false;
        }
        else {
            return true;
        }
    }
    else {
        if ((o.val().length < min)) {
            if (boolError) {
                jQuery.noticeAdd({text: "<b>El tamaï¿½o minimo del campo " + strTitle + " tiene que ser " + min + " !</b><br><br>", type: "warning", stay: false});
            }
            return false;
        }
        else {
            return true;
        }
    }
}

var outInts = function (number, boolAddComma) {
    if (number.length <= 3)
        return (number == '' ? '0' : number);
    else {
        var mod = number.length % 3;
        var output = (mod == 0 ? '' : (number.substring(0, mod)));
        for (i = 0; i < Math.floor(number.length / 3); i++) {
            if (((mod == 0) && (i == 0)) || !boolAddComma)
                output += number.substring(mod + 3 * i, mod + 3 * i + 3);
            else
                output += ',' + number.substring(mod + 3 * i, mod + 3 * i + 3);
        }
        return (output);
    }
}

var outCents = function (amount, intDec) {
    if (!intDec)
        intDec = 2;
    var intTenExp = Math.pow(10, intDec);
    amount = Math.round(((amount) - Math.floor(amount)) * intTenExp);
    var strZeros = "";
    for (i = 1; i <= intDec; i++) {
        if (amount < Math.pow(10, i - 1))
            strZeros += "0";
    }
    if (amount == 0)
        return "." + strZeros;
    else
        return "." + strZeros + amount;
}

var format_number = function (monto, decimales) {
    var comas = /,/ig;
    var strTotal = JavaScriptTextTrim(monto) + '';

    if (!decimales)
        decimales = 0;
    strTotal = strTotal.replace(comas, '');
    strTotal = strTotal * 1;

    var intTenExp = Math.pow(10, decimales);
    strTotal = Math.round(strTotal * intTenExp) / intTenExp;
    var addMinus = false;
    if (strTotal < 0) {
        strTotal = Math.abs(strTotal);
        addMinus = true;
    }
    return ((addMinus ? '-' : '') + (outInts(Math.floor(strTotal - 0) + '', true) + outCents(strTotal - 0, decimales)));
}

var format_number_scomas = function (monto, intDec) {
    var strTotal = "";
    var comas = /,/ig;
    if (!intDec)
        intDec = 2;
    var intTenExp = Math.pow(10, intDec);

    monto = JavaScriptTextTrim(monto);
    monto = monto.replace(comas, '');
    monto = Math.round(monto * intTenExp) / intTenExp;

    strTotal = monto + "";
    strTotal = strTotal.replace(comas, '');

    return outInts(Math.floor(strTotal - 0) + '', false) + outCents(strTotal - 0, intDec);
}

function JavaScriptTextTrim(str) {
    var whitespace = new String(" \t\n\r");
    var s = new String(str);

    if (whitespace.indexOf(s.charAt(0)) != -1) {
        var j = 0, i = s.length;
        while (j < i && whitespace.indexOf(s.charAt(j)) != - 1)
            j++;
        s = s.substring(j, i);
    }
    if (whitespace.indexOf(s.charAt(s.length - 1)) != -1) {
        var i = s.length - 1;
        while (i >= 0 && whitespace.indexOf(s.charAt(i)) != - 1)
            i--;
        s = s.substring(0, i + 1);
    }
    return s;
}

var validar_entero = function (intvalue) {
    if (!intvalue)
        intvalue = '';
    var RegExPattern = /^(?:\+|-)?\d+$/;
    if ((intvalue.match(RegExPattern)) && (intvalue != '')) {
        return intvalue;
    } else {
        return "";
    }
}

function validarEntero(intvalue) {
    var RegExPattern = /^(?:\+|-)?\d+$/;
    if ((intvalue.match(RegExPattern)) && (intvalue != '')) {
        return intvalue;
    } else {
        return "";
    }

}

/*
 * @description Envia un ajax y espera a que devuelva los datos, si ocurre un error o si el ajax devuelve la posiciï¿½n "status" diferente de "ok" entonces devuelve un false, de lo contrario devuelve la data del ajax.
 * @important Es necesario Jquery y la libreria JqueryUi para que funcione de lo contrario dara errores.
 * @returns object
 */
var MD5 = function (string) {
    /**
     *
     *  MD5 (Message-Digest Algorithm)
     *  http://www.webtoolkit.info/
     *
     **/
    function RotateLeft(lValue, iShiftBits) {
        return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));
    }

    function AddUnsigned(lX, lY) {
        var lX4, lY4, lX8, lY8, lResult;
        lX8 = (lX & 0x80000000);
        lY8 = (lY & 0x80000000);
        lX4 = (lX & 0x40000000);
        lY4 = (lY & 0x40000000);
        lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
        if (lX4 & lY4) {
            return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
        }
        if (lX4 | lY4) {
            if (lResult & 0x40000000) {
                return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
            } else {
                return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
            }
        } else {
            return (lResult ^ lX8 ^ lY8);
        }
    }

    function F(x, y, z) {
        return (x & y) | ((~x) & z);
    }
    function G(x, y, z) {
        return (x & z) | (y & (~z));
    }
    function H(x, y, z) {
        return (x ^ y ^ z);
    }
    function I(x, y, z) {
        return (y ^ (x | (~z)));
    }

    function FF(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    ;

    function GG(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    ;

    function HH(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    ;

    function II(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    ;

    function ConvertToWordArray(string) {
        var lWordCount;
        var lMessageLength = string.length;
        var lNumberOfWords_temp1 = lMessageLength + 8;
        var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
        var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
        var lWordArray = Array(lNumberOfWords - 1);
        var lBytePosition = 0;
        var lByteCount = 0;
        while (lByteCount < lMessageLength) {
            lWordCount = (lByteCount - (lByteCount % 4)) / 4;
            lBytePosition = (lByteCount % 4) * 8;
            lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount) << lBytePosition));
            lByteCount++;
        }
        lWordCount = (lByteCount - (lByteCount % 4)) / 4;
        lBytePosition = (lByteCount % 4) * 8;
        lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
        lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
        lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
        return lWordArray;
    }
    ;

    function WordToHex(lValue) {
        var WordToHexValue = "", WordToHexValue_temp = "", lByte, lCount;
        for (lCount = 0; lCount <= 3; lCount++) {
            lByte = (lValue >>> (lCount * 8)) & 255;
            WordToHexValue_temp = "0" + lByte.toString(16);
            WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length - 2, 2);
        }
        return WordToHexValue;
    }
    ;

    function Utf8Encode(string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    }
    ;

    var x = Array();
    var k, AA, BB, CC, DD, a, b, c, d;
    var S11 = 7, S12 = 12, S13 = 17, S14 = 22;
    var S21 = 5, S22 = 9, S23 = 14, S24 = 20;
    var S31 = 4, S32 = 11, S33 = 16, S34 = 23;
    var S41 = 6, S42 = 10, S43 = 15, S44 = 21;

    string = Utf8Encode(string);

    x = ConvertToWordArray(string);

    a = 0x67452301;
    b = 0xEFCDAB89;
    c = 0x98BADCFE;
    d = 0x10325476;

    for (k = 0; k < x.length; k += 16) {
        AA = a;
        BB = b;
        CC = c;
        DD = d;
        a = FF(a, b, c, d, x[k + 0], S11, 0xD76AA478);
        d = FF(d, a, b, c, x[k + 1], S12, 0xE8C7B756);
        c = FF(c, d, a, b, x[k + 2], S13, 0x242070DB);
        b = FF(b, c, d, a, x[k + 3], S14, 0xC1BDCEEE);
        a = FF(a, b, c, d, x[k + 4], S11, 0xF57C0FAF);
        d = FF(d, a, b, c, x[k + 5], S12, 0x4787C62A);
        c = FF(c, d, a, b, x[k + 6], S13, 0xA8304613);
        b = FF(b, c, d, a, x[k + 7], S14, 0xFD469501);
        a = FF(a, b, c, d, x[k + 8], S11, 0x698098D8);
        d = FF(d, a, b, c, x[k + 9], S12, 0x8B44F7AF);
        c = FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
        b = FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
        a = FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
        d = FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
        c = FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
        b = FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
        a = GG(a, b, c, d, x[k + 1], S21, 0xF61E2562);
        d = GG(d, a, b, c, x[k + 6], S22, 0xC040B340);
        c = GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
        b = GG(b, c, d, a, x[k + 0], S24, 0xE9B6C7AA);
        a = GG(a, b, c, d, x[k + 5], S21, 0xD62F105D);
        d = GG(d, a, b, c, x[k + 10], S22, 0x2441453);
        c = GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
        b = GG(b, c, d, a, x[k + 4], S24, 0xE7D3FBC8);
        a = GG(a, b, c, d, x[k + 9], S21, 0x21E1CDE6);
        d = GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
        c = GG(c, d, a, b, x[k + 3], S23, 0xF4D50D87);
        b = GG(b, c, d, a, x[k + 8], S24, 0x455A14ED);
        a = GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
        d = GG(d, a, b, c, x[k + 2], S22, 0xFCEFA3F8);
        c = GG(c, d, a, b, x[k + 7], S23, 0x676F02D9);
        b = GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
        a = HH(a, b, c, d, x[k + 5], S31, 0xFFFA3942);
        d = HH(d, a, b, c, x[k + 8], S32, 0x8771F681);
        c = HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
        b = HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
        a = HH(a, b, c, d, x[k + 1], S31, 0xA4BEEA44);
        d = HH(d, a, b, c, x[k + 4], S32, 0x4BDECFA9);
        c = HH(c, d, a, b, x[k + 7], S33, 0xF6BB4B60);
        b = HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
        a = HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
        d = HH(d, a, b, c, x[k + 0], S32, 0xEAA127FA);
        c = HH(c, d, a, b, x[k + 3], S33, 0xD4EF3085);
        b = HH(b, c, d, a, x[k + 6], S34, 0x4881D05);
        a = HH(a, b, c, d, x[k + 9], S31, 0xD9D4D039);
        d = HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
        c = HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
        b = HH(b, c, d, a, x[k + 2], S34, 0xC4AC5665);
        a = II(a, b, c, d, x[k + 0], S41, 0xF4292244);
        d = II(d, a, b, c, x[k + 7], S42, 0x432AFF97);
        c = II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
        b = II(b, c, d, a, x[k + 5], S44, 0xFC93A039);
        a = II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
        d = II(d, a, b, c, x[k + 3], S42, 0x8F0CCC92);
        c = II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
        b = II(b, c, d, a, x[k + 1], S44, 0x85845DD1);
        a = II(a, b, c, d, x[k + 8], S41, 0x6FA87E4F);
        d = II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
        c = II(c, d, a, b, x[k + 6], S43, 0xA3014314);
        b = II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
        a = II(a, b, c, d, x[k + 4], S41, 0xF7537E82);
        d = II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
        c = II(c, d, a, b, x[k + 2], S43, 0x2AD7D2BB);
        b = II(b, c, d, a, x[k + 9], S44, 0xEB86D391);
        a = AddUnsigned(a, AA);
        b = AddUnsigned(b, BB);
        c = AddUnsigned(c, CC);
        d = AddUnsigned(d, DD);
    }

    var temp = WordToHex(a) + WordToHex(b) + WordToHex(c) + WordToHex(d);

    return temp.toLowerCase();
}

jQuery.fn.jNumber = function (intDecimal) {
    if (!intDecimal)
        intDecimal = 2;
    $(this).change(function () {
        var value = format_number($(this).val(), intDecimal);
        value = value.replace(",", "");
        var arrVal = value.split(".");
        var boolOk = true;
        for (var i in arrVal) {
            var intvalue = (parseInt(arrVal[i] * 1));
            if (isNaN(intvalue)) {
                boolOk = false;
            }
        }
        if (boolOk) {
            if (value < 0)
                $(this).val(value).css({"color": "red"});
            else
                $(this).val(value).css({"color": "black"});
        }
        else {
            $(this).val("");
            $(this).focus();
            $(this).attr("palceholder", "Ingrese un valor numerico");
        }
    });
}

function serializeObj(ObjTmp, strKey) {
    var _RETURN = "";
    if (!strKey)
        strKey = "";

    if (!ObjTmp)
        ObjTmp = {};

    var NewObj = jQuery.extend({}, ObjTmp);
    $.each(NewObj, function (key, value) {
        if (typeof (value) == "object") {
            var strTMP = (strKey) ? strKey + "[" + key + "]" : key;
            var strObjVal = serializeObj(value, strTMP, _RETURN);
            _RETURN += strObjVal;
        }
        else {
            var strTMP = strKey + "[" + key + "]";
            _RETURN += (strKey) ? "&" + strTMP + "=" + value : "&" + key + "=" + value;
        }
    });
    return _RETURN;
}
function objectSize(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key))
            size++;
    }
    return size;
}
;

/**
 * Module for displaying "Waiting for..." dialog using Bootstrap
 *
 * @author Eugene Maslovich <ehpc@em42.ru>
 */

var waitingDialog = waitingDialog || (function ($) {
    'use strict';

    // Creating modal dialog's DOM
    var $dialog = $(
            '<div class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style="padding-top:15%; overflow-y:visible;">' +
            '<div class="modal-dialog modal-m">' +
            '<div class="modal-content">' +
            '<div class="modal-header"><h3 style="margin:0;"></h3></div>' +
            '<div class="modal-body" id="loading-body">' +
            '<div class="progress progress-striped active" style="margin-bottom:0;"><div class="progress-bar" style="width: 100%"></div></div>' +
            '</div>' +
            '</div></div></div>');

    $dialog.on('hide.bs.modal', function (e) {
        $dialog.remove();
    });

    return {
        /**
         * Opens our dialog
         * @param message Custom message
         * @param options Custom options:
         * 				  options.dialogSize - bootstrap postfix for dialog size, e.g. "sm", "m";
         * 				  options.progressType - bootstrap postfix for progress bar type, e.g. "success", "warning".
         */
        show: function (message, options) {
            // Assigning defaults
            if (typeof options === 'undefined') {
                options = {};
            }
            if (typeof message === 'undefined') {
                message = 'Loading';
            }
            var settings = $.extend({
                dialogSize: 'm',
                progressType: '',
                onHide: null // This callback runs after the dialog was hidden
            }, options);

            // Configuring dialog
            $dialog.find('.modal-dialog').attr('class', 'modal-dialog').addClass('modal-' + settings.dialogSize);
            $dialog.find('.progress-bar').attr('class', 'progress-bar');
            if (settings.progressType) {
                $dialog.find('.progress-bar').addClass('progress-bar-' + settings.progressType);
            }
            $dialog.find('h3').text(message);
            // Adding callbacks
            if (typeof settings.onHide === 'function') {
                $dialog.off('hidden.bs.modal').on('hidden.bs.modal', function (e) {
                    settings.onHide.call($dialog);
                });
            }
            // Opening dialog
            $dialog.modal();
        },
        /**
         * Closes dialog
         */
        hide: function () {
            $dialog.modal('hide');
        }
    };

})(jQuery);

var dialogModal = function (message, tittle, BoolRecarga, strFun) {
    if (!strFun)
        strFun = false;
    if (!BoolRecarga)
        BoolRecarga = false;

    if (!tittle)
        tittle = "Mensaje del sistema";
    $div = $('<div class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style="padding-top:15%; overflow-y:visible;"></div>');
    $divM = $('<div class="modal-dialog modal-m"></div>');
    $div.append($divM);
    $divC = $('<div class="modal-content"></div>');
    $divM.append($divC);
    $divHeader = $('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h3 style="margin:0;">' + tittle + '</h3></div>');
    $divC.append($divHeader);
    $divBody = $('<div class="modal-body"></div>').html(message);
    $divC.append($divBody);
    $divFooter = $('<div class="modal-footer"></div>');
    $divC.append($divFooter);

    if (strFun !== false) {
        var evaluarFun = function () {
            if (strFun)
                eval(strFun);

        };
        $buttonFun = $('<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="' + evaluarFun() + '">OK</button>');
        $divFooter.append($buttonFun);
    }
    $("body").append($div);
    $div.modal();

    $div.on('hide.bs.modal', function (e) {
        $div.remove();
        if (BoolRecarga)
            location.reload();
    });

    return $div;
};

function isValidDate(day, month, year)
{
    var dteDate;

    // En javascript, el mes empieza en la posicion 0 y termina en la 11 
    //   siendo 0 el mes de enero
    // Por esta razon, tenemos que restar 1 al mes
    month = month - 1;
    // Establecemos un objeto Data con los valore recibidos
    // Los parametros son: aÃ±o, mes, dia, hora, minuto y segundos
    // getDate(); devuelve el dia como un entero entre 1 y 31
    // getDay(); devuelve un num del 0 al 6 indicando siel dia es lunes,
    //   martes, miercoles ...
    // getHours(); Devuelve la hora
    // getMinutes(); Devuelve los minutos
    // getMonth(); devuelve el mes como un numero de 0 a 11
    // getTime(); Devuelve el tiempo transcurrido en milisegundos desde el 1
    //   de enero de 1970 hasta el momento definido en el objeto date
    // setTime(); Establece una fecha pasandole en milisegundos el valor de esta.
    // getYear(); devuelve el aÃ±o
    // getFullYear(); devuelve el aÃ±o
    dteDate = new Date(year, month, day);

    //Devuelva true o false...
    return ((day == dteDate.getDate()) && (month == dteDate.getMonth()) && (year == dteDate.getFullYear()));
}

/**
 * Funcion para validar una fecha
 * Tiene que recibir:
 *  La fecha en formato ingles yyyy-mm-dd
 * Devuelve:
 *  true-Fecha correcta
 *  false-Fecha Incorrecta
 */
function validate_fecha(fecha)
{
    var patron = new RegExp("^(19|20)+([0-9]{2})([-])([0-9]{1,2})([-])([0-9]{1,2})$");

    if (fecha.search(patron) == 0)
    {
        var values = fecha.split("-");
        if (isValidDate(values[2], values[1], values[0]))
        {
            return true;
        }
    }
    return false;
}

/**
 * Esta función calcula la edad de una persona y los meses
 * La fecha la tiene que tener el formato yyyy-mm-dd que es
 * metodo que por defecto lo devuelve el <input type="date">
 */
function calcularEdad(fecha) {
    if (validate_fecha(fecha) == true) {
        // Si la fecha es correcta, calculamos la edad
        var values = fecha.split("-");
        var dia = values[2];
        var mes = values[1];
        var ano = values[0];

        // cogemos los valores actuales
        var fecha_hoy = new Date();
        var ahora_ano = fecha_hoy.getYear();
        var ahora_mes = fecha_hoy.getMonth() + 1;
        var ahora_dia = fecha_hoy.getDate();

        // realizamos el calculo
        var edad = (ahora_ano + 1900) - ano;
        if (ahora_mes < mes) {
            edad--;
        }
        if ((mes == ahora_mes) && (ahora_dia < dia)) {
            edad--;
        }
        if (edad > 1900) {
            edad -= 1900;
        }

        // calculamos los meses
        var meses = 0;
        if (ahora_mes > mes)
            meses = ahora_mes - mes;
        if (ahora_mes < mes)
            meses = 12 - (mes - ahora_mes);
        if (ahora_mes == mes && dia > ahora_dia)
            meses = 11;

        // calculamos los dias
        var dias = 0;
        if (ahora_dia > dia)
            dias = ahora_dia - dia;
        if (ahora_dia < dia) {
            ultimoDiaMes = new Date(ahora_ano, ahora_mes, 0);
            dias = ultimoDiaMes.getDate() - (dia - ahora_dia);
        }

        return edad + " años " + meses + " meses " + dias + " dias";
    }
    else {
        return "La fecha " + fecha + " es incorrecta";
    }
}

function validarEmail(email) {
    expr = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if (!expr.test(email))
        return false;
    else
        return true;
}

var STR_PAD_LEFT = 1;
var STR_PAD_RIGHT = 2;
var STR_PAD_BOTH = 3;

function pad(str, len, pad, dir) {

    if (typeof (len) == "undefined") {
        var len = 0;
    }
    if (typeof (pad) == "undefined") {
        var pad = ' ';
    }
    if (typeof (dir) == "undefined") {
        var dir = STR_PAD_RIGHT;
    }

    if (len + 1 >= str.length) {
        switch (dir) {

            case STR_PAD_LEFT:
                str = Array(len + 1 - str.length).join(pad) + str;
                break;

            case STR_PAD_BOTH:
                var right = Math.ceil((padlen = len - str.length) / 2);
                var left = padlen - right;
                str = Array(left + 1).join(pad) + str + Array(right + 1).join(pad);
                break;

            default:
                str = str + Array(len + 1 - str.length).join(pad);
                break;

        } // switch

    }
    return str;

}

/**
 * @autor Magdiel Canahuí
 * @description Envia un ajax y espera a que devuelva los datos, si ocurre un error o si el ajax devuelve la posiciï¿½n "status" diferente de "ok" entonces devuelve un false, de lo contrario devuelve la data del ajax.
 * @important Es necesario Jquery y la libreria JqueryUi para que funcione de lo contrario dara errores.
 * @returns object
 */
var fntSendData = function (ObjSettings,$fntBeforeSave,$fntSuccesSave){

    /*
      Ejemplo de ejecuciï¿½n 1:
      var getData = new fntSendData();
          getData.strParams = "post1=123&post2=321";
          getData.strUrl = "MyFile.php?get1=001";
          getData.strDataTypeAjax = "json";
          getData.boolMsjReturn = false;

      var rGetData = getData.fntRunSave();
      rGetData -> devuelve un objeto tipo json o un false

      Ejemplo de ejecuciï¿½n 2:
      var getData = new fntSendData({strParams : "post1=123&post2=321",
                                     strUrl = "MyFile.php?get1=001",
                                     strDataTypeAjax = "json",
                                     strStatus = "ok", // esta variable es importante, cuando se envia el ajax a la hora de retornar el json, este json debe tener la posicion "status" con el valor de esta variable.
                                     boolMsjReturn = false});

      getData -> devuelve un objeto, si devuelve la posiciï¿½n "status" en fail es porque no se ejecutï¿½ correctamente.

    */

    //Definiciï¿½n de variables privadas.

    if(!ObjSettings) ObjSettings = false;
    if(!$fntBeforeSave) $fntBeforeSave = false;
    if(!$fntSuccesSave) $fntSuccesSave = false;

    var self = this;
    this._RETURN = false;

    var jQueryAjax = false;
    var boolSuccesAjax = false;

    var boolSaved = false;
    var boolProccesSaved = false;

    var boolNewIntent = false;

    //Definiciï¿½n de variables publicas.

    this.strParams = (ObjSettings.strParams) ?ObjSettings.strParams : "";
    this.strUrl = (ObjSettings.strUrl) ?ObjSettings.strUrl: "";
    this.strStatus = (ObjSettings.strStatus) ?ObjSettings.strStatus: "ok";

    this.strTypeAjax = (ObjSettings.strTypeAjax) ?ObjSettings.strTypeAjax: "POST";
    this.strDataTypeAjax = (ObjSettings.strDataTypeAjax) ?ObjSettings.strDataTypeAjax: "";//xml, json, script, or html
    this.ObjDataAjax = false;

    //Seccion de mensajes
    this.boolMsjReturn = (ObjSettings.boolMsjReturn || ObjSettings.boolMsjReturn === false) ?ObjSettings.boolMsjReturn: true;
    this.strMsjReturn = (ObjSettings.strMsjReturn) ? ObjSettings.strMsjReturn : "<b>Error Inesperado!</b><br>Los datos no fueron enviados.";
    this.strTitleReturn = (ObjSettings.strTitleReturn) ?ObjSettings.strTitleReturn: "Mensaje del sistema";
    this.strMsjNewIntent = (ObjSettings.strMsjNewIntent) ?ObjSettings.strMsjNewIntent : "ï¿½Desea intentarlo de nuevo?";
    this.boolFailData = (ObjSettings.boolFailData)?ObjSettings.boolFailData:false;
    this.boolDisplayLoad = (ObjSettings.boolDisplayLoad)?ObjSettings.boolDisplayLoad:true;
    this.boolDisplayLoadModal = (ObjSettings.boolDisplayLoadModal)?ObjSettings.boolDisplayLoadModal:false;

    //Seccion de intentos de envio de la funcion
    this.fntNewIntent = (ObjSettings.fntNewIntent) ?ObjSettings.fntNewIntent:  function (){return true;};
    this.fntWaitNewIntent = (ObjSettings.fntWaitNewIntent) ?ObjSettings.fntWaitNewIntent:false;
    this.intMaxIntents = (ObjSettings.intMaxIntents) ?ObjSettings.intMaxIntents:3;
    this.intCountIntents = (ObjSettings.intCountIntents) ?ObjSettings.intCountIntents:0;

    //Definiciï¿½n de funciones privadas

    //Funciï¿½n que envia el ajax y espera los datos antes de dar un return
    var fntSendAjax = function () {
        if(!boolSuccesAjax){
            if(!jQueryAjax){
                jQueryAjax = $.ajax({
                    type: self.strTypeAjax,
                    dataType :  self.strDataTypeAjax,
                    data :  self.strParams,
                    url :   self.strUrl,
                    cache: false,
                    async : false,
                    beforeSend: function(){
                        if(self.boolDisplayLoad){
                            //waitingDialog.show();                            
                        }
                    },
                    success:function(data) {
                        if(typeof data != "undefined"){
                            if(self.strDataTypeAjax === 'html' || typeof(data) != 'object'){
                                self.ObjDataAjax = data;
                                boolNewIntent = false;
                                boolSaved = true;
                            }
                            else{
                                if(data["status"] == self.strStatus){
                                    self.ObjDataAjax = data;
                                    self.strMsjReturn = data['msj'];

                                    if(data['boolMsjReturn'])
                                        self.boolMsjReturn = (data['boolMsjReturn'] == "true");

                                    boolNewIntent = false;
                                    boolSaved = true;
                                }
                                else{
                                    if(self.boolFailData){
                                        self.ObjDataAjax = data;
                                        boolSaved = true;
                                    }

                                    if(data['msj'])
                                        self.strMsjReturn = data['msj'];

                                    if(data['boolMsjReturn'])
                                        self.boolMsjReturn = (data['boolMsjReturn'] == "true");

                                    if(data['boolNewIntent'])
                                        boolNewIntent=(data['boolNewIntent'] === "true") ? true : false;
                                }
                            }
                        }
                        else{
                            self.ObjDataAjax = "error";
                            boolNewIntent = false;
                            boolSaved = true;
                        }

                        if(self.boolDisplayLoad)
                            //waitingDialog.hide();

                        boolSuccesAjax=true;
                    },
                    error:function (){
                        if(self.boolDisplayLoad)
                            //waitingDialog.hide();

                        boolSuccesAjax=true;
                    }
                });
            }
            return fntSendAjax();
        }
        else
            return true;
    }



    //Funciï¿½n que manda a llamar a fntSendAjax y retorna si logro enviarlo
    var fntSave = function (){
        if(fntSendAjax()){
            if(!boolSaved){
                if(boolNewIntent){
                    jQueryAjax = false;
                    boolSuccesAjax = false;
                    self.fntNewIntent = function () {
                        boolProccesSaved = false;
                        dialogModal(self.strMsjNewIntent, self.strTitleReturn, fntSave);
                        self.intCountIntents++;
                    };
                    if (self.intCountIntents >= self.intMaxIntents)
                        self.fntNewIntent = function () {return true;}
                }
            }

            if(!boolProccesSaved && self.boolMsjReturn){
                dialogModal(self.strMsjNewIntent, self.strTitleReturn, self.fntNewInten);
                //self.ObjWidgets.alertDialog(self.strMsjReturn,self.strTitleReturn,false,self.fntNewIntent,self.fntWaitNewIntent);
            }
            boolProccesSaved = true;
            return boolSaved;
        }
    }
    //Funciï¿½n que se ejecuta antes de enviar el ajax
    var fntBeforeSave = function (fntBeforeSave){
        if(!fntBeforeSave)
            fntBeforeSave = function () { return true; }

        if(fntBeforeSave())
            return fntSave();
    }

    //Funciï¿½n que se ejecuta despues de enviar el ajax es necesaio que el ajax devuelve la variable boolSaved en true.
    var fntSuccesSave = function (fntSuccesSave){
        if(!fntSuccesSave)
            fntSuccesSave = function () { return self.ObjDataAjax;};
        return fntSuccesSave(self.ObjDataAjax);
    }

    //Definiciï¿½n de funciones publicas

    //Funciï¿½n publica, esta funciï¿½n viene siendo como el constructor de mi clase.
    this.fntRunSave = function ($fntBeforeSave,$fntSuccesSave){
        if(fntBeforeSave($fntBeforeSave)){
            return fntSuccesSave($fntSuccesSave);
        }
        else
            return false;
    }

    if(ObjSettings){
        var MyRetrun = self.fntRunSave($fntBeforeSave,$fntSuccesSave);
        self._RETURN = MyRetrun;
        if(!MyRetrun)
            return {status:"fail",msj:"<b>Error Inesperado!</b><br>Los datos no fueron enviados."};
        else
            return MyRetrun;
    }
}

function validateFormBootstrap(frm,lvl){
    if(!frm)return false;
    if(!lvl) lvl = 2;
    var boolOk = true;

    $("#" + frm + " .validate").each(function () {
        var obj = false;
        for(var i = 1;i<=lvl;i++){
            obj = $(this).parent();
        }
        if(obj){
            obj.removeClass("has-error");
        }
        if ($(this).attr("type") == "email") {
            if (!validarEmail($(this).val())) {
                var obj = false;
                for(var i = 1;i<=lvl;i++){
                    obj = $(this).parent();
                }
                if(obj){
                    obj.addClass("has-error");
                }
                boolOk = false;
            }
        }
        else {
            if($(this).val() == null){
                var obj = false;
                for(var i = 1;i<=lvl;i++){
                    obj = $(this).parent();
                }
                if(obj){
                    obj.addClass("has-error");
                }
                boolOk = false;
            }
            if(typeof $(this).attr("multiple") === "undefined"){
                if ($(this).val().trim() === "" || $(this).val().trim() === "0") {
                    var obj = false;
                    for(var i = 1;i<=lvl;i++){
                        obj = $(this).parent();
                    }
                    if(obj){
                        obj.addClass("has-error");
                    }
                    boolOk = false;
                }
            }
        }
    });
    return boolOk;
}


var Telefonos = function (strNameObj, limit){
    if(!limit) limit = 0;
    var objParent = $("#"+strNameObj);
    var i = 0;
    var self = this;
    var disabled = "";

    this.setDisabled = function(boolDisabled){
        if(boolDisabled)disabled = "disabled";
        else disabled = "";
    };

    this.drawTrigger = function(){
        var span = $("<label>Agregar teléfono</label>&nbsp;&nbsp;<i class='fa fa-plus fa-2x' aria-hidden='true'></i>").click(function(){
            if(disabled === ""){
                self.addPanel();
            }
        });
        span.css({"cursor":"pointer"});
        objParent.append(span)
            .append("<br>");
    };

    this.addPanel = function(arrCustom){
        i++;
        if(i <= limit || limit == 0){
            var defaults = {
                tag:"movil",
                phone:""
            };
            arrCustom || ( arrCustom = {} );
            var arrItem = $.extend({}, defaults, arrCustom);

            var content = $("<div class='row'></div>");
            var divTag = $("<div class='col-lg-4'></div>");content.append(divTag);
            var divInput = $("<div class='col-lg-6'></div>");content.append(divInput);
            var divDelete = $("<div class='col-lg-2'></div>");content.append(divDelete);

            var input = $("<input type='text' class='form-control' name='txt_phone_number[]' "+disabled+" >")
                .val(arrItem.phone);divInput.append(input);

            var input = $("<select class='form-control' name='sel_phone_tag[]' "+disabled+" ></select>")
                .append("<option value='movil'>Movil</option>")
                .append("<option value='casa'>Casa</option>")
                .append("<option value='trabajo'>Trabajo</option>")
                .append("<option value='principal'>Principal</option>")
                .val(arrItem.tag);
            divTag.append(input);

            var deleteD = $("<i class='fa fa-trash-o fa-2x text-danger' style='cursor:pointer;'></i>").click(function(){
                i--;
                content.remove();
            }); divDelete.append(deleteD);

            objParent.append(content);
        }
        else i--;
    };

    this.cleanPanel = function(){
        objParent.find(".row").remove();
    };
}

var Direcciones = function(strNameObj, limit){
    if(!limit) limit = 0;
    var objParent = $("#"+strNameObj);
    var i = 0;
    var self = this;
    var disabled = "";

    this.setDisabled = function(boolDisabled){
        if(boolDisabled)disabled = "disabled";
        else disabled = "";
    };

    this.drawTrigger = function(){
        var span = $("<label>Agregar direccion</label>&nbsp;&nbsp;<i class='fa fa-plus fa-2x' aria-hidden='true'></i>").click(function(){
            if(disabled == ""){
                self.addPanel();
            }
        }).css({"cursor":"pointer"});
        objParent.append(span);
    };

    this.addPanel = function(arrCustom){
        i++;
        if(i <= limit || limit == 0){
            var defaults = {
                direccion: "",
                zona: "",
                municipio: "",
                departamento: ""
            };
            arrCustom || ( arrCustom = {} );
            var arrItem = $.extend({}, defaults, arrCustom);

            var content = $("<div class='row'></div>").css("border-bottom","1px solid #F8F8F8");
            var divDelete = $("<div class='col-lg-12 text-right'></div>");content.append(divDelete);

            var deleteD = $("<i class='fa fa-trash-o fa-2x text-danger' style='cursor:pointer;'></i>").click(function(){
                if(disabled == ""){
                    i--;
                    content.remove();
                }
            }); divDelete.append(deleteD);

            var lbl = $("<label class='col-sm-4 control-label' >Direccion</label>").css("text-align","left"); content.append(lbl);
            var div = $("<div class='col-sm-8'></div>");content.append(div);
            var input = $("<input type='text' class='form-control col-sm-6' name='txt_address_direccion[]' "+disabled+">")
                .val(arrItem.direccion);div.append(input);

            var lbl = $("<label for='txt_address_zona_"+i+"' class='col-sm-4 control-label' >Zona</label>").css("text-align","left"); content.append(lbl);
            var div = $("<div class='col-sm-8'></div>");content.append(div);
            var input = $("<input type='text' class='form-control col-sm-6' name='txt_address_zona[]' "+disabled+">")
                .val(arrItem.zona);div.append(input);

            var lbl = $("<label for='txt_address_municipio_"+i+"' class='col-sm-4 control-label' >Municipio</label>").css("text-align","left"); content.append(lbl);
            var div = $("<div class='col-sm-8'></div>");content.append(div);
            var input = $("<input type='text' class='form-control col-sm-6' name='txt_address_municipio[]' "+disabled+">")
                .val(arrItem.municipio);div.append(input);

            var lbl = $("<label for='txt_address_departamento_"+i+"' class='col-sm-4 control-label' >Departamento</label>").css("text-align","left"); content.append(lbl);
            var div = $("<div class='col-sm-8'></div>");content.append(div);
            var input = $("<input type='text' class='form-control col-sm-6' name='txt_address_departamento[]' "+disabled+">")
                .val(arrItem.departamento);div.append(input);

            objParent.append(content);
        }
        else i--;
    };

    this.cleanPanel = function(){
        objParent.find(".row").remove();
    }
};

var addInput = function (strContainer) {

    var objParent = $("#"+strContainer);
    var objRow = false;
    var disabled = "";

    this.setDisabled = function(boolDisabled){
        if(boolDisabled)disabled = "disabled";
        else disabled = "";
    };

    this.drawTrigger = function(label,addTrigger){
        if(!label) label = "";
        if(!addTrigger) addTrigger = function(){return false;};
        var span = $("<label>"+label+"</label>&nbsp;&nbsp;<i class='fa fa-plus fa-2x' aria-hidden='true'></i>").click(function(){
            if(disabled == ""){
                addTrigger();
            }
        }).css({"cursor":"pointer"});
        objParent.append(span);
    };

    this.addRow = function(){
        var content = $("<div class='row'></div>").css({
            "border-bottom":"3px solid #F8F8F8",
            "padding-bottom":"5px"
        });
        var divDelete = $("<div class='col-lg-12 text-right'></div>");content.append(divDelete);

        var deleteD = $("<i class='fa fa-trash-o fa-2x text-danger' style='cursor:pointer;'></i>").click(function(){
            if(disabled == ""){
                content.remove();
            }
        }); divDelete.append(deleteD);
        objRow = $(content);
        objParent.append(content);
    };

    this.addInput = function(arrCustom){
        var defaults = {
            name: "",
            type: "text",
            label: "",
            placeholder: "",
            value: ""
        };
        arrCustom || ( arrCustom = {} );
        var arrItem = $.extend({}, defaults, arrCustom);

        if(arrItem.type == "hidden"){
            var input = $("<input type='hidden' class='form-control col-sm-6' name='"+arrItem.name+"' "+disabled+">")
                .val(arrItem.value);objRow.append(input);
        }
        else if(arrItem.type == "checkbox"){
            /*
             <div class="slideThree" dt-active="Si" dt-not-active="No">
             <input type="checkbox" class="ios-chk" value="Y" id="chkTerms" name="chkTerms" disabled />
             <label for="chkTerms"></label>
             </div>
             */
            var lbl = $("<label class='col-sm-4 control-label' >"+arrItem.label+"</label>").css("text-align","left"); objRow.append(lbl);
            var div = $("<div class='col-sm-8'></div>");objRow.append(div);
            var slide = $("<div></div>").attr({
                "class":"slideThree",
                "dt-active":"Si",
                "dt-not-active":"No"
            });
            div.append(slide);
            var input = $("<input type='checkbox' class='ios-chk' value='Y' id='"+arrItem.name+"' name='"+arrItem.name+"' "+disabled+">" );slide.append(input);
            var label = $("<label for='"+arrItem.name+"'></label>");slide.append(label);
            if(arrItem.value == "Y")input.prop("checked",true);
            else input.prop("checked",false);

        }
        else{
            var lbl = $("<label class='col-sm-4 control-label' >"+arrItem.label+"</label>").css("text-align","left"); objRow.append(lbl);
            var div = $("<div class='col-sm-8'></div>");objRow.append(div);
            var input = $("<input type='text' class='form-control col-sm-6' name='"+arrItem.name+"' placeholder='"+arrItem.placeholder+"' "+disabled+">")
                .val(arrItem.value);div.append(input);
        }
    };

    this.cleanPanel = function(){
        objParent.find(".row").remove();
    }
};