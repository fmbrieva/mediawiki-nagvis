<?
/*******************************************************************************
Program: ajax_handler_bridge.php
Version: 1.04
Website: http://www.mediawiki.org/wiki/Extension:NagVis 
Author:  Felipe Muñoz Brieva <felipe@gestiononline.net> 
Licensed under The GPL License
Redistributions of files must retain the above copyright notice.
*******************************************************************************/

$urlNagVisBase=$_GET['urlNagVisBase'];

$valor="urlNagVisBase=".$urlNagVisBase."&".$_SERVER['QUERY_STRING'];

$file=file
("$urlNagVisBase/ajax_handler.php?".substr($valor,stripos($valor,"&")) );

foreach($file as $s){
   /* 1.3 */
   $s=str_replace('/nagios/nagvis/nagvis/',$urlNagVisBase,$s);
   /* 1.4 */
   $s=str_replace('\/nagios\/nagvis\/nagvis\/',$urlNagVisBase,$s);
   print($s);
}

?>
