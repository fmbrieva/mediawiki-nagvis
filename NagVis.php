<?php
/**
 * NagVis - this extension add NagVis maps to mediawiki pages
 *
 * original by Felipe Muñoz Brieva 24.10.2008
 *
 * Installation:
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * require_once '$IP/extensions/NagVis/NagVis.php';
 *
 * @ingroup Extensions
 * @author Felipe Muñoz Brieva <felipe@gestiononline.es>
 * @version 1.08
 * @link http://www.mediawiki.org/wiki/Extension:NagVis Documentation
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
 * Usage:
 *  Use only one <NagVis>-tag per MediaWiki page.
 *
 *  <NagVis --arguments-- >Header Text</NagVis>
 *
 *  --arguments--:
 *
 *     showheader:    Shows a map header with links (NagVis/Nagios/Icinga) and a counter showing pending time to refresh NagVis maps
 *     urlnagvis:     NagVis website URL
 *     urlmonitor:    Nagios/Icinga website URL     (From NagVis 1.6)
 *     map:           NagVis map name
 *     mapBackground: NagVis map background         (From NagVis 1.7)
 *     nagvispath:    Web path to NagVis            (optional)
 *     cgibinpath:    cgi-bin path                  (From NagVis 1.7) 
 *     system:        Monitoring system             (optional) 
 *                          values:  nagios/icinga
 *                          default: icinga
 *
 * Note from NagVis 1.7:
 *      If map and background image have different names in NagVis you must specify the name of the background image 
 *      For example if the map is called "Wikimap" the background image asigned by NagVis extension is "WikiMap.png"
 *      If we use a background image with a different name, we must use "mapBackground" (e.g. mapBackground=BackgroundImage.jpg)
 *
 * IMPORTANT: Only can be used one <NagVis>-tag per mediawiki page
 *
 * Examples:
 *
 * NagVis version 1.6:
 *
 *  <NagVis map=MapName urlnagvis=http://www.nagvisserver.com/nagvis/frontend/nagvis-js/index.php urlmonitor=http://www.nagvisserver.com/icinga showheader=yes system=icinga>Title map (NagVis with Mediawiki)</NagVis>
 *
 * NagVis version 1.3 1.4:
 *
 *  <NagVis map=MapName showheader=yes system=nagios urlnagvis=http://www.nagviserver/nagios/nagvis/nagvis/index.php>Title (Map)</NagVis>
 *
 * ---------------------------------------------------------------------------------------------------------------------
 *
 * Version 1.00   (24/10/2008):
 *                              - Supported NagVis release: 1.3.2rc3 
 *               
 * Version 1.02   (30/12/2008):
 *                              - Supported NagVis release: 1.3.2, 1.3.2rc3
 *               
 * Version 1.04   (06/02/2009):
 *                              - Supported NagVis release 1.4
 *               
 * Version 1.04.1 (06/02/2009):
 *                              - Change error messages
 *               
 * Version 1.04.4 (21/05/2009):
 *                              - Add __toString() method: Not so magic before php 5.2.0
 *
 * Version 1.04.5 (22/05/2009):
 *                              - Final adjust for  __toString() methods
 *
 * Version 1.04.5-4 (15/06/2009):
 *                              - Add variable: nagiosFolder 
 *                              - Adjust frontendContext position
 *
 * Version 1.05 (20/01/2012):
 *                              - Add new monitoring system Icinga (param: system)
 *
 * Version 1.06 (26/01/2012):
 *                              - Supported NagVis relesase 1.6
 *                              - Add new param "urlmonitor" (Url of Nagios/Icinga website)
 *
 * Version 1.06.1 (27/01/2012):
 *                              - Add new param "nagvispath" (optional) Web path to NagVis 
 *
 * Version 1.08 (18/03/2015):
 *                              - Supported NagVis branch 1.7 and 1.8
 *                              - Add new params "mapBackground" "cgibinpath" (optional from NagVis 1.7)
 *
 */
 
if (!defined( 'MEDIAWIKI' ) ) {
	echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
	die( -1 );
}
 
$wgExtensionFunctions[] = "wfNagVisExtension";

# NagVis extension credits

$wgExtensionCredits['parserhook'][] = array(
        'name' => 'NagVis (version 1.08)',
	'version' => '1.08',
        'author' => 'Felipe Muñoz Brieva (email: felipe@delegacionprovincial.com)',
        'url' => 'http://www.delegacionprovincial.com/mediawiki/index.php?title=Gestion_Online:NagVisExtension',
        'description' => 'Add NagVis maps for Nagios/Icinga to mediawiki pages'
);

# NagVis extension 

function wfNagVisExtension() {
  global $wgParser;
 
  $wgParser->setHook( "NagVis", "renderNagVis" );
}
 
# NagVis parser

function renderNagVis( $input, array $args, Parser $parser ) {

  global $existsNagVisTag;
 
  if ($existsNagVisTag){
      return"<br><font color=red><em>---  NagVis tag ignored, only one tag per wiki page!   ---</em></font><br>";
     }

  $existsNagVisTag = true;

  // ###### INVALIDATE CACHE ######

  $parser->disableCache();
 
  // ###### LOAD NAGVIS ######

  $output="";
  
  $map="";
  $mapBackground="";
  $showHeader="yes";
  $urlNagVis="";
  $urlMonitor="";
  $nagvisPath="";
  $cgibinPath="";
  $mapTitle="";
  $monitorSystem="icinga";
 
  foreach( $args as $name => $value ){
    switch(strtolower(htmlspecialchars($name))){ 
      case 'map':
            $map=htmlspecialchars($value);
            break;
      case 'mapbackground':
            $mapBackground=htmlspecialchars($value);
            break;
      case 'showheader':
            $showHeader=htmlspecialchars($value);
            break;
      case 'urlnagvis':
            $urlNagVis=htmlspecialchars($value);
            break;
      case 'urlmonitor':
            $urlMonitor=htmlspecialchars($value);
            break;
      case 'nagvispath':
            $nagvisPath=htmlspecialchars($value);
            break;
      case 'cgibinpath':
            $cgibinPath=htmlspecialchars($value);
            break;
      case 'system':
            $monitorSystem=htmlspecialchars($value);
            break;
    }
  }

  if (trim($input)==""){
    $mapTitle=$map;
  }
  else {
    $mapTitle=htmlspecialchars($input);
  }

  // -------------------------------------------

  require_once('includes/simple_html_dom.php');

  $param["showHeader"]=$showHeader; 
  $param["monitorSystem"] = $monitorSystem; 
  $param["mapTitle"] = $mapTitle; 
  
  // Check: Exists NagVis URL? 

  if(!urlExists($urlNagVis)){
      $output.="<br><font color=red>";
      $output.="ERROR: (urlNagVis)<br>";
      $output.="Check your URL: $urlNagVis<br>";
      $output.="</font><br>";
      return $output;
  }

  // Load NagVis Map DOM 

  $html=new simple_html_dom();

  $html->load_file($urlNagVis);

  $title=$html->find('title',0);

  $NagVisVersion = substr($title->innertext,7);

  switch (substr($NagVisVersion,0,3)) {
      case "1.3": 
        $param["urlNagiosBase"] = 'http://'.str_replace('nagvis/nagvis/index.php','',str_replace('http://','',$urlNagVis));
        $param["urlNagVisBase"] = 'http://'.str_replace('index.php','',str_replace('http://','',$urlNagVis)); 
        $param["urlNagVisMap"] = $param["urlNagVisBase"]."index.php?map=".$map; 
        $nagiosFolder=explode("/",$param["urlNagiosBase"]);
        end($nagiosFolder);
        $param["nagiosFolder"] = prev($nagiosFolder);
        $html->load_file($param["urlNagVisMap"]);
        $output.= NagVis_v1_3($param,$html);
        break; 
      case "1.4": 
        $param["urlNagiosBase"] = 'http://'.str_replace('nagvis/nagvis/index.php','',str_replace('http://','',$urlNagVis)); 
        $param["urlNagVisBase"] = 'http://'.str_replace('index.php','',str_replace('http://','',$urlNagVis)); 
        $param["urlNagVisMap"] = $param["urlNagVisBase"]."index.php?map=".$map; 
        $nagiosFolder=explode("/",$param["urlNagiosBase"]);
        end($nagiosFolder);
        $param["nagiosFolder"] = prev($nagiosFolder);
        $html->load_file($param["urlNagVisMap"]);
        $output.= NagVis_v1_4($param,$html);
        break; 
      case "1.6": 
        $param["urlNagiosBase"] = 'http://'.str_replace('index.php','',str_replace('http://','',$urlMonitor)); 
        $param["urlNagVisBase"] = 'http://'.str_replace('/frontend/nagvis-js/index.php','',str_replace('http://','',$urlNagVis));
        $param["urlNagVisMap"] = $param["urlNagVisBase"]."/frontend/nagvis-js/index.php?mod=Map&act=view&show=".$map; 
        if (empty($nagvisPath))
          { $tmpNagVisPath=explode('/',$urlNagVis);
            if (in_array("frontend",$tmpNagVisPath))
              { $param["nagvisPath"] = "/".$tmpNagVisPath[(array_search("frontend",$tmpNagVisPath)-1)];} else 
              { $output.= "<br><font color=red>Are you running a supported version (1.3, 1.4, 1.6) ? </font><br>";}
          } else
          { $param["nagvisPath"]=$nagvisPath;} 
        $html->load_file($param["urlNagVisMap"]);
        $output.= NagVis_v1_6($param,$html);
        break; 
      case "1.7": 
      case "1.8": 
        $param["urlNagiosBase"] = 'http://'.str_replace('index.php','',str_replace('http://','',$urlMonitor)); 
        $param["urlNagVisBase"] = 'http://'.str_replace('/frontend/nagvis-js/index.php','',str_replace('http://','',$urlNagVis));
        $param["urlCgiBin"] = 'http://'.str_replace('/nagvis/frontend/nagvis-js/index.php','',str_replace('http://','',$urlNagVis)).$cgibinPath;
        $param["urlNagVisMap"] = $param["urlNagVisBase"]."/frontend/nagvis-js/index.php?mod=Map&act=view&show=".$map; 
        if (empty($nagvisPath))
          { $tmpNagVisPath=explode('/',$urlNagVis);
            if (in_array("frontend",$tmpNagVisPath))
              { $param["nagvisPath"] = "/".$tmpNagVisPath[(array_search("frontend",$tmpNagVisPath)-1)];} else 
              { $output.= "<br><font color=red>Are you running a supported version (1.3, 1.4, 1.6) ? </font><br>";}
          } else
          { $param["nagvisPath"]=$nagvisPath;} 
        if (empty($cgibinPath))
         {
            $param["cgibinPath"]=""; 
         } else {
            $param["cgibinPath"]=$cgibinPath;
         }

        if (empty($mapBackground))
         {
            $param["map"]=$map . '.png'; 
         } else {
            $param["map"]=$mapBackground;
         }
        $html->load_file($param["urlNagVisMap"]);
        $output.= NagVis_v1_8($param,$html);
        break; 
      default: 
        $output.="<br><font color=red>";
        $output.= 'Need to check: <br>';
        $output.= "   a) exists NagVis map (".$map.") in NagVis? <a href=".$urlNagVis.">Check it!</a><br>";
        $output.= "   b) is NagVis running correctly? Check nagvis.ini.php!!<br>";
        $output.= "   c) are you running a supported version (1.3, 1.4, 1.6, 1.7, 1.8) ? <br>";
        $output.="</font><br>";
  } 

  $html->clear();
  unset($html);

  return $output;

}

/* NagVis release 1.3 */

function NagVis_v1_3($param,$html) {

  global $wgOut, $wgScriptPath; 

  $output="";

  // Wrapper wiki offset

  $wiki_offsetx='2-(document.getElementById("content").offsetLeft)';
  $wiki_offsety='2-(document.getElementById("content").offsetTop)';

  // Start "div" in MediaWiki page for NagVis Maps
  
  $wgOut->addHTML('<div id="codeNagVis">');
  
  // Define refreshSwitch rotationSwitch

  $wgOut->addHTML('<link id="refreshSwitch" onclick="refreshSwitch(this,\'Start Refresh\',\'Stop Refresh\');" href="#" style="visibility: hidden;"></a>');

  $wgOut->addHTML('<link id="rotationSwitch" onclick="switchRotation(this,\'Start Rotation\',\'Stop Rotation\');" href="#" style="visibility: hidden;"></a>');

  // Styles ----------------------------------------------------------------------

  $styles=$html->find('style');

  foreach ($styles as $s)
  {
    $s=$s->__toString();
    $s=str_replace('/'.$param["nagiosFolder"].'/',$param["urlNagiosBase"],$s);
    $s=str_replace('./includes/',$param["urlNagVisBase"].'includes/',$s);
    $s=str_replace('); -->','',$s);
    $s=str_replace('</style>','',$s);
    $a=strpos($s,'@import');
    
    if ($a > 0){
        $csssheet=substr($s,$a+12); 
        $contentcsssheet=new simple_html_dom();
        $contentcsssheet->load_file($csssheet); 
        $contentcsssheet=$contentcsssheet->__toString();
        $valor=str_replace('body, table, th, td','not_used',$contentcsssheet);
        $wgOut->addHTML('<style type="text/css">');
        $wgOut->addHTML($valor);
        $wgOut->addHTML('</style>');
        //$contentcsssheet->clear();
        unset($contentcsssheet);
    }
  }

  // Scripts ---------------------------------------------------------------------

  // Scripts src -----------------------------------------------------------------

  $scriptssrc=$html->find('script[src]');

  foreach ($scriptssrc as $s)
  {
   $tmpscript=str_replace('/'.$param["nagiosFolder"].'/',$param["urlNagiosBase"],$s->src);
   $tmpscript=str_replace('./includes/',$param["urlNagVisBase"].'includes/',$tmpscript);

   $file1=file($tmpscript);

   $wgOut->addHTML('<script language="JavaScript" type="text/javascript">');

   foreach($file1 as $ss)
     {
       // Adjust for 1.3.2
       $ss=str_replace('getRequest(htmlBase+\'/nagvis/ajax_handler.php?','getRequest(\''.$wgScriptPath.'/extensions/NagVis/includes/ajax_handler_proxy.php?urlNagVisBase='.$param["urlNagVisBase"]."&",$ss);
       // Adjust for 1.3.2rc3
       $ss=str_replace('getRequest(\'./ajax_handler.php?','getRequest(\''.$wgScriptPath.'/extensions/NagVis/includes/ajax_handler_proxy.php?urlNagVisBase='.$param["urlNagVisBase"]."&",$ss);
       $ss=str_replace('o3_offsetx=ol_offsetx;',"o3_offsetx=$wiki_offsetx;",$ss);
       $ss=str_replace('o3_offsety=ol_offsety;',"o3_offsety=$wiki_offsety;",$ss);
       $wgOut->addHTML($ss);
     }

     $wgOut->addHTML('</script>');
    
}

  // Scripts language ------------------------------------------------------------

  $scriptslanguage=$html->find('script[language]');

  foreach ($scriptslanguage as $s)
  {
     $wgOut->addHTML($s->__toString());
  }

  $wgOut->addHTML('</div>');
 
  $output.=('<div id="nagvis">');

  // Header for NagVis map

  if ($param["showHeader"]=="yes"){
      $output.='<table class=header_table>';
      $output.='<tbody>';
      $output.='<td id="refreshCounterHead" style="width: 20px; text-align: center;">0</td>';
   
      $output.='<td style="width: 25px; text-align: center;">';
      $output.='<a href='.$param["urlNagVisMap"].'>';

      if ($param["monitorSystem"]=="nagios"){
          $output.='<img alt="Nagios" src='.$wgScriptPath.'/extensions/NagVis/images/nagios.png>';
      } else {
          $output.='<img alt="Icinga" src='.$wgScriptPath.'/extensions/NagVis/images/icinga.png>';
      }

      $output.='</a>';
      $output.='</td>';

      $output.='<td style="width: 25px; text-align: center;">';
      $output.='<a href='.$param["urlNagiosBase"].'>';
      $output.='<img alt="Nagios" src='.$wgScriptPath.'/extensions/NagVis/images/nagios.png>';
      $output.='</a>';
      $output.='</td>';
      $output.='<td style="text-align: center;">';
      $output.='<a style="color: black;">'.$param["mapTitle"].'</a>';
      $output.='</td>';

      $output.='</tbody>';
      $output.='</table>';
  }
 
  $output.=$html->find('div[id=overDiv]',0)->__toString();

  $s=$html->find('div.map',0)->__toString();
 
  $output.=str_replace('/'.$param["nagiosFolder"].'/',$param["urlNagiosBase"],$s);

  $output.=('</div>');

  return $output;
}

/* NagVis release 1.4 */

function NagVis_v1_4($param,$html) {

  global $wgOut,     $wgScriptPath;

  $output="";

  // Wrapper wiki offset

  $wiki_offsetx='2-(document.getElementById("content").offsetLeft)';
  $wiki_offsety='2-(document.getElementById("content").offsetTop)';

  // Start "div" in MediaWiki page for NagVis Maps
  
  $wgOut->addHTML('<div id="codeNagVis">');

  $wgOut->addHTML('<style type="text/css">@import url('.$param["urlNagiosBase"].'/nagvis/nagvis/templates/header/tmpl.default.css);</style>');

  // Define refreshSwitch rotationSwitch

  $wgOut->addHTML('<link id="refreshSwitch" onclick="switchRefresh(this,\'refreshStart\',\'refreshStop\');" href="#" style="visibility: hidden;"></a>');
  $wgOut->addHTML('<link id="rotationSwitch" onclick="switchRotation(this,\'rotationStart\',\'rotationStop\');" href="#" style="visibility: hidden;"></a>');
  
  // Scripts src -----------------------------------------------------------------

  $scriptssrc=$html->find('script[src]');

  foreach ($scriptssrc as $s)
  {
    $tmpscript=str_replace('/'.$param["nagiosFolder"].'/',$param["urlNagiosBase"],$s->src);
   
    $file1=file($tmpscript);

    $wgOut->addHTML('<script language="JavaScript" type="text/javascript">');

    foreach($file1 as $ss)
    {
       $ss=str_replace('oGeneralProperties.path_htmlbase+\'/nagvis/ajax_handler.php?','\''.$wgScriptPath.'/extensions/NagVis/includes/ajax_handler_proxy.php?urlNagVisBase='.$param["urlNagVisBase"]."&",$ss);
       $ss=str_replace('o3_offsetx=ol_offsetx;',"o3_offsetx=$wiki_offsetx;",$ss);
       $ss=str_replace('o3_offsety=ol_offsety;',"o3_offsety=$wiki_offsety;",$ss);
       $ss=str_replace('event.clientX','(event.clientX-document.getElementById("content").offsetLeft)',$ss);
       $ss=str_replace('event.clientY','(event.clientY-document.getElementById("backgroundImage").offsetTop)',$ss);
       $ss=str_replace('document.body','document.getElementById("nagvis")',$ss);
       $wgOut->addHTML($ss);
    }

    $wgOut->addHTML('</script>');

  }

  // Links language ------------------------------------------------------------

  $links=$html->find('link[type]');

  foreach ($links as $s)
  {
    $s->href=str_replace('\/',"/",$s->href);
    $s->href=str_replace('/'.$param["nagiosFolder"].'/',$param["urlNagiosBase"],$s->href);

    $contentcsssheet=new simple_html_dom();
    $contentcsssheet->load_file($s->href);
    $contentcsssheet=$contentcsssheet->__toString();
    $valor=str_replace('body, table, th, td','not_used',$contentcsssheet);
    $wgOut->addHTML('<style type="text/css">');
    $wgOut->addHTML($valor);
    $wgOut->addHTML('</style>');
    //$contentcsssheet->clear();
    unset($contentcsssheet);

  }

  $wgOut->addHTML('</div>');
 
  $output.=('<div id="nagvis">');

  // Header for NagVis map

  if ($param["showHeader"]=="yes"){
      $output.=('<table class=header_table>');
      $output.=('<tbody>');
      $output.=('<td style="width: 25px; text-align: center;">');
      $output.=('<a href='.$param["urlNagVisMap"].'>');
      $output.=('<img alt="Nagios" src='.$wgScriptPath.'/extensions/NagVis/images/nagvis.png>');
      $output.=('</a>');
      $output.=('</td>');

      $output.=('<td style="width: 25px; text-align: center;">');
      $output.=('<a href='.$param["urlNagiosBase"].'>');

      if ($param["monitorSystem"]=="nagios"){
          $output.='<img alt="Nagios" src='.$wgScriptPath.'/extensions/NagVis/images/nagios.png>';
      } else { 
          $output.='<img alt="Icinga" src='.$wgScriptPath.'/extensions/NagVis/images/icinga.png>';
      } 
 
      $output.=('</a>');
      $output.=('</td>');
      $output.=('<td style="text-align: center;">');
      $output.=('<a style="color: black;">'.$param["mapTitle"].'</a>');
      $output.=('</td>');

      $output.=('</tbody>');
      $output.=('</table>');
  }

  // Add NagVis Map

  $output.=$html->find('div.map',0);
  
  // Scripts language ------------------------------------------------------------

  $scriptslanguage=$html->find('script');

  foreach ($scriptslanguage as $s)
  {
    // 1.4rc1 script: attribute->  a) type + src
    //                             b) type + language
    // 1.4.rc2 script: attribute-> a) type + src
    //                             b) type

    if(!isset($s->src)) {
       $s=$s->__toString();
       $s=str_replace('\/',"/",$s);
       $s=str_replace('/'.$param["nagiosFolder"].'/',$param["urlNagiosBase"],$s);
       $s=str_replace('document.body','document.getElementById("nagvis")',$s);
       $s=str_replace('<!--', '', $s);
       $s=str_replace('//-->', '', $s);
       $output.=($s);
    }
  }

  // Add NagVis overDiv 

  $output.=$html->find('div[id=overDiv]',0)->__toString();

  $output.=('</div>');

  $output=preg_replace('/\x0A/', '', $output);
  $output=preg_replace('/\x0C/', '', $output);
  $output=preg_replace('/\x0D/', '', $output);
  
  return $output;

}

/* NagVis release 1.6 */

function NagVis_v1_6($param,$html) {

  global $wgOut,     $wgScriptPath;

  $output="";

  // Wrapper wiki offset

  $wiki_offsety='wiki_offsety = (document.getElementById(obj.conf.object_id+"-icondiv").offsetTop) + 20;';

  // Start "div" in MediaWiki page for NagVis Maps
  
  $wgOut->addHTML('<div id="codeNagVis">');

  // Scripts src -----------------------------------------------------------------

  $scriptssrc=$html->find('script[src]');

  foreach ($scriptssrc as $s)
  {
    $tmpscript=str_replace($param["nagvisPath"]."/",$param["urlNagVisBase"]."/",$s->src);
  
    $file1=file($tmpscript);

    $wgOut->addHTML('<script language="JavaScript" type="text/javascript">');

    foreach($file1 as $ss)
    {
       $ss=str_replace('oGeneralProperties.path_htmlbase+\'/nagvis/ajax_handler.php?','\''.$wgScriptPath.'/extensions/NagVis/includes/ajax_handler_proxy.php?urlNagVisBase='.$param["urlNagVisBase"]."&",$ss);
       $ss=str_replace('obj.hoverY = y;',"obj.hoverY = y;".$wiki_offsety,$ss);
       $ss=str_replace("hoverMenu.style.top = y + scrollTop + hoverSpacer - getHeaderHeight() + 'px';","hoverMenu.style.top = wiki_offsety + 'px';",$ss);
       $ss=str_replace('event.clientX','(event.clientX-document.getElementById("content").offsetLeft)',$ss);
       $ss=str_replace('event.clientY','(event.clientY-document.getElementById("backgroundImage").offsetTop)',$ss);
       $ss=str_replace('document.body','document.getElementById("nagvis")',$ss);
       $wgOut->addHTML($ss);
    }

    $wgOut->addHTML('</script>');

  }

  // Links language ------------------------------------------------------------

  $links=$html->find('link[type]');

  foreach ($links as $s)
  {
    $s->href=str_replace('\/',"/",$s->href);
    $s->href=str_replace($param["nagvisPath"].'/',$param["urlNagVisBase"]."/",$s->href);
    $contentcsssheet=new simple_html_dom();
    $contentcsssheet->load_file($s->href);
    $contentcsssheet=$contentcsssheet->__toString();
    $contentcsssheet=str_replace('body, table, th, td','not_used',$contentcsssheet);
    $valor=str_replace('#backgroundImage','backgroundImage',$contentcsssheet);
    $wgOut->addHTML('<style type="text/css">');
    $wgOut->addHTML($valor);
    $wgOut->addHTML('</style>');
    //$contentcsssheet->clear();
    unset($contentcsssheet);

  }

  $wgOut->addHTML('</div>');
 
  $output.=('<div id="nagvis">');

  // Header for NagVis map

  if ($param["showHeader"]=="yes"){
      $output.=('<table style="background-color: #F5F5F5; border-collapse: collapse; width: 100%;">');
      $output.=('<tbody>');
      $output.=('<td style="width: 25px; text-align: center; border: 1px #a4a4a4 solid; padding: 2px;">');
      $output.=('<a href='.$param["urlNagVisMap"].'>');
      $output.=('<img alt="Nagios" src='.$wgScriptPath.'/extensions/NagVis/images/nagvis.png>');
      $output.=('</a>');
      $output.=('</td>');

      $output.=('<td style="width: 25px; text-align: center; border: 1px #a4a4a4 solid; padding: 2px;">');
      $output.=('<a href='.$param["urlNagiosBase"].'>');

      if ($param["monitorSystem"]=="nagios"){
          $output.='<img alt="Nagios" src='.$wgScriptPath.'/extensions/NagVis/images/nagios.png>';
      } else {
          $output.='<img alt="Icinga" src='.$wgScriptPath.'/extensions/NagVis/images/icinga.png>';
      }

      $output.=('</a>');
      $output.=('</td>');
      $output.=('<td style="text-align: center; border: 1px #a4a4a4 solid; padding: 2px;font-size: 12px; color: #314455;">');
      $output.=('<a style="color: black;">'.$param["mapTitle"].'</a>');
      $output.=('</td>');

      $output.=('</tbody>');
      $output.=('</table>');
  }

  // Add NagVis Map

  $output.=('<div id="sidebar"></div>');
  $output.=('<div id="map" class="map"></div>');

  // Scripts language ------------------------------------------------------------

  $scriptslanguage=$html->find('script');

  foreach ($scriptslanguage as $s)
  {
    if(!isset($s->src)) {
       $wgOut->addHTML($s);
    }
  }

  $output.=('</div>');

  return $output;

}

/* NagVis release 1.8 */

function NagVis_v1_8($param,$html) {

	global $wgOut,     $wgScriptPath;
	$output="";
	$domain = 'http://'. parse_url($param["urlNagVisBase"])['host'];

	// Wrapper wiki offset for -icondiv -link1 -link2 ... 
	$wiki_offset= 'if (document.getElementById(obj.conf.object_id+"-icondiv") !== null){'.
                      '  wiki_offsety = (document.getElementById(obj.conf.object_id+"-icondiv").offsetTop) + 20;'.
                      '  wiki_offsetx = (document.getElementById(obj.conf.object_id+"-icondiv").offsetLeft) + 20;'.
                      '} else {'.
                      '  wiki_offsety = (document.elementFromPoint(x, y).offsetTop) + 20;'.
                      '  wiki_offsetx = (document.elementFromPoint(x, y).offsetLeft) + 20;'.
                      '  if (wiki_offsety < 25){'.
                      '     wiki_offsety = (document.elementFromPoint(x, y).parentNode.parentNode.offsetTop) + 20;'.
                      '     wiki_offsetx = (document.elementFromPoint(x, y).parentNode.parentNode.offsetLeft) + 20;'.
                      '  }'.
                      '}';
	
	// Start "div" in MediaWiki page for NagVis Maps
	$wgOut->addHTML('<div id="codeNagVis">');

	// Scripts src -----------------------------------------------------------------
	$scriptssrc=$html->find('script[src]');

	// Iterate through js scripts and replace url paths with remote NagVis site
	foreach ($scriptssrc as $s) {
		$tmpscript=str_replace($param["nagvisPath"]."/",$param["urlNagVisBase"]."/",$s->src);

		//$tmpscript=str_replace($param["cgibinPath"]."/",$param["urlCgiBin"]."/",$s->src);
		$file1=file($tmpscript);

		$wgOut->addHTML('<script language="JavaScript" type="text/javascript">');

		foreach($file1 as $ss) {
			$ss=str_replace('href="#"', 'href="' . $param["urlNagVisBase"] . '"',$ss);
			$ss=str_replace('oGeneralProperties.path_server', '"' . $param["urlNagVisBase"] . '/server/core/ajax_handler.php"',$ss);
			$ss=str_replace('oGeneralProperties.path_images', '"' . $param["urlNagVisBase"] . '/frontend/nagvis-js/images/"',$ss);
			$ss=str_replace('oGeneralProperties.path_iconsets', '"' . $param["urlNagVisBase"] . '/userfiles/images/iconsets/"',$ss);
			$ss=str_replace('oProperties.background_image', '"' . $param["urlNagVisBase"] . '/userfiles/images/maps/' . $param["map"].'"',$ss);
			$ss=str_replace("document.getElementById('map').appendChild(oImage);", "document.getElementById('map').appendChild(oImage);document.getElementById('backgroundImage').style.marginTop = '6px';",$ss);

                        $ss=str_replace('oGeneralProperties.path_cgi', '"' . $param["cgibinPath"] . '"',$ss);
			$ss=str_replace('this.conf.htmlcgi', '"' . $param["cgibinPath"] . '"',$ss);
			$ss=str_replace('oLink.href = ', 'oLink.href = "' . $domain . '" + ',$ss);

			//fix position of hover pop up window using the wiki offsets
			$ss=str_replace('obj.hoverY = y;',"obj.hoverY = y;".$wiki_offset,$ss);

			// Always hide sidebar in Mediawiki
                        $ss=str_replace("sidebar.style.display = 'inline';","sidebar.style.display = 'none';",$ss);
                        $ss=str_replace("content.style.left = '200px';","",$ss);

			$ss=str_replace("hoverMenu.style.top = (y + scrollTop + hoverSpacer - getHeaderHeight()) + 'px';","hoverMenu.style.top = wiki_offsety + 'px';",$ss);
		        $ss=str_replace("hoverMenu.style.left = (x + scrollLeft + hoverSpacer - getSidebarWidth()) + 'px';","hoverMenu.style.left = wiki_offsetx + 'px';",$ss);

			//fix position of right click menu
			$ss=str_replace("contextMenu.style.top = event.clientY + scrollTop - getHeaderHeight() + 'px';","contextMenu.style.top = wiki_offsety + 'px';",$ss);
		        $ss=str_replace("contextMenu.style.left = event.clientX + scrollLeft - getSidebarWidth() + 'px';","contextMenu.style.left = wiki_offsetx + 'px';",$ss);

			$ss=str_replace('document.body','document.getElementById("nagvis")',$ss);

			$wgOut->addHTML($ss);
		}
		$wgOut->addHTML('</script>');
	}

	// Links language ------------------------------------------------------------

	$links=$html->find('link[type]');

	// Iterate through link resources, replace url paths with remote NagVis site, and pull css stylesheets
	foreach ($links as $s) {
		$s->href=str_replace($param["nagvisPath"].'/',$param["urlNagVisBase"]."/",$s->href);
		$contentcsssheet=new simple_html_dom();
		$contentcsssheet->load_file($s->href);
		$contentcsssheet=$contentcsssheet->__toString();
		$contentcsssheet=str_replace('body, th, td','not_used',$contentcsssheet);
		$contentcsssheet=str_replace('../../frontend/nagvis-js/images/internal', 'imgPath+\'',$contentcsssheet);

                $contentcsssheet=str_replace('#backgroundImage { position: absolute; top: 0; left: 0; z-index: 0; }',
                                   '#backgroundImage { position: absolute; top: 20; left: 0; z-index: 0; }',
                                   $contentcsssheet);
		$valor=str_replace('#backgroundImage','backgroundImage',$contentcsssheet);

		$wgOut->addHTML('<style type="text/css">');
		$wgOut->addHTML($valor);
		$wgOut->addHTML('</style>');
		unset($contentcsssheet);
	}


	// Disable bottom-border around anchors
	$wgOut->addHTML('<style type="text/css">');
	$wgOut->addHTML('#nagvis a{ border-bottom: none; }');
	$wgOut->addHTML('</style>');

	// Closing div for id=codeNagVis
	$wgOut->addHTML('</div>');
 
	$output.=('<div id="nagvis">');

	// Header for NagVis map
	if ($param["showHeader"]=="yes"){
		$output.=('<div id="header" style="display:inline;">');
		$output.=('<div id="headerspacer" style="display:inherit;">');
		$output.=('<div id="headershow" style="border:0; display:inherit;">');
		$output.=('<table style="background-color: #F5F5F5; border-collapse: collapse; width: 100%;">');
		$output.=('<tbody>');
		$output.=('<td style="width: 25px; text-align: center; border: 1px #a4a4a4 solid; padding: 2px;">');
		$output.=('<a href='.$param["urlNagVisMap"].'>');
		$output.=('<img alt="Nagios" src='.$wgScriptPath.'/extensions/NagVis/images/nagvis.png>');
		$output.=('</a>');
		$output.=('</td>');
		$output.=('<td style="width: 25px; text-align: center; border: 1px #a4a4a4 solid; padding: 2px;">');
		$output.=('<a href='.$param["urlNagiosBase"].'>');

		if ($param["monitorSystem"]=="nagios"){
			$output.='<img alt="Nagios" src='.$wgScriptPath.'/extensions/NagVis/images/nagios.png>';
		} else {
			$output.='<img alt="Icinga" src='.$wgScriptPath.'/extensions/NagVis/images/icinga.png>';
		}

		$output.=('</a>');
		$output.=('</td>');
		$output.=('<td style="text-align: center; border: 1px #a4a4a4 solid; padding: 2px;font-size: 18px; color: #314455;">');
		$output.=('<a style="color: black;">'.$param["mapTitle"].'</a>');
		$output.=('</td>');
		$output.=('</tbody>');
		$output.=('</table>');
		$output.=('</div></div></div>');
	}

	// Add NagVis Map
        $output.=('<div id="sidebar"></div>');
	$output.=('<div id="map" class="map" ></div>');

	// Scripts language ------------------------------------------------------------
	$scriptslanguage=$html->find('script');

	foreach ($scriptslanguage as $s){
		if(!isset($s->src)) {
			$wgOut->addHTML($s);
		}
	}

	// Closing div for id=nagvis
	$output.=('</div>');

	return $output;

}
// Check : Exists URL ?

function urlExists($url) {

    $hdrs = @get_headers($url);
    return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false;
}

?>
