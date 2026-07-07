<?php
$targetURL = "http://www.drudgereport.com/";
if(isset($_GET['url']) )
{
  $targetURL = $_GET['url'];
}
if(isset($_GET['urlfix']))
{
  $_SetURLsToProxy = 1;
}
$ch = curl_init($targetURL);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT,2);
$output = curl_exec($ch);
curl_close($ch);
error_reporting(0);
$dom = new DOMDocument();
//echo $output;
$dom->loadHTML($output);
$dom->preserveWhiteSpace = false;
$Dscript = $dom->getElementsByTagName('script');
$remove= [];
foreach($Dscript as $item)
{
  $remove[] = $item;
}
$Dnoscript = $dom->getElementsByTagName('noscript');
foreach($Dnoscript as $item)
{
  $remove[] = $item;
}
foreach( $remove as $item)
{
  $item->parentNode->removeChild($item);
}
//Edit any iframes.
$Diframe = $dom->getElementsByTagName('iframe');
foreach($Diframe as $item)
{
  /*
  foreach($item->attributes as $att)
  {
    echo $att->name . ";;" . $att->value . "\n";
  }
  */
  $item->parentNode->removeChild($item);
}
//Edit links if needed.
if( isset( $_SetURLsToProxy ) )
{
  $Da = $dom->getElementsByTagName('a');
  foreach($Da as $item)
  {
    $val = $item->attributes['href']->nodeValue;
    $ext = substr( $val, strrpos( $val, '.')+1);
    if( strlen( $ext ) < 5 )
    {
      $ext = strtolower($ext);
      if( $ext == 'gif' || $ext == 'jpg' || $ext == 'png' )
      { 
//        echo '$', $ext;
        continue;
      }
    }
    //var_dump( $item->attributes['href']->nodeValue );
    $item->attributes['href']->nodeValue ='http://rm.vg/t/d.php?urlfix=1~a~url=' . $val;
  }
}


$html = $dom->saveHTML();
$html = str_replace( '~a~', '&', $html);
echo $html;
?>
