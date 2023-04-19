//FUNCTION USED FOR PRINTING THE PAGE


function PrintThisPage(obj) {
    var sPath = window.location.pathname;
    var sPage = sPath.substring(sPath.lastIndexOf('/') + 1);
     var sOption="toolbar=yes,location=no,directories=yes,menubar=yes,";
     sOption += "scrollbars=yes,align=center,width=1005px,height=700px";
    var sWinHTML = document.getElementById('content-area').innerHTML;
    var winprint = window.open("", "", sOption);
	var pathToTheme = location.protocol +"//"+ location.host + Drupal.settings.basePath + "/sites/all/themes/" + Drupal.settings.ajaxPageState.theme;
    winprint.document.open();
	winprint.document.write('<html><head><link href="'+pathToTheme+'/css/print_sub.css"  rel="stylesheet" media="all" type="text/css" />');
	winprint.document.write('</head>');
    winprint.document.write('<body class="front" style="font-family:Arial, Helvetica, sans-serif;color: #4a4b4f;font-size: 11px;font-weight: normal;line-height: 16px; vertical-align:top" onload="window.print();">');
    winprint.document.write('<span style="margin-right:5px" class="img1">');
	winprint.document.write('<center><IMG width="998px" height="82px" SRC="'+pathToTheme+'/images/header_printbg.jpg" /></center>');
	winprint.document.write('</span>');
    winprint.document.write('<div style="font-family:Arial, Helvetica, sans-serif; color: #4a4b4f;font-size: 12px;font-weight: normal;line-height: 16px; margin:0; padding:0">' + sWinHTML + '</div>\r\n');
    //winprint.document.write('<div class="input_but" width:900px;><a href="javascript:void(0)" onClick="javascript:window.close();"><IMG  width="44px" height="18px" border="0" SRC="images/close_bt.gif" style="margin-left:90px"  /></a></div></div>');
    winprint.document.write('</body></html>');
    winprint.document.close();
    winprint.focus();
}
