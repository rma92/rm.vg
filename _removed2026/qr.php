<?php
$useHeader = 1;
$useAnalytics = 1;
$db = 'sqlite:data/counter.db';
$c = new PDO($db);
$c->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$c->exec('CREATE TABLE IF NOT EXISTS cnt (ID integer primary key, URI text, TIME integer, IP text, USERAGENT text);');
$s1 = $c->prepare ('INSERT INTO cnt (URI, TIME, IP, USERAGENT) VALUES (:uri, strftime("%s","now"), :v, :u)');
$s1->bindValue(':uri', $_REQUEST['q']);
$s1->bindValue(':v', $_SERVER['REMOTE_ADDR'] );
$s1->bindValue(':u', $_SERVER['HTTP_USER_AGENT'] );
$s1->execute();
$c = null;
if( isset($_REQUEST['q']))
{
  $table = file_get_contents("qr.csv");
  $rows = explode("\n", $table);
  $uri = array();
  foreach($rows as $r)
  {
    $r2 = explode(',', $r);
    if(isset($r2[0]) && isset($r2[1]))
    {
      $uri[$r2[0]] = $r2[1];
    }
  }

  if(isset($uri[$_REQUEST['q']]) && strlen($uri[$_REQUEST['q']]) > 1)
  {
    if(isset($useHeader))
    {
      header('Location: ' . $uri[$_REQUEST['q']]);
    }
    //show a link if headers is off or there is an error
    echo '<a href="', $uri[$_REQUEST['q']], '">', $uri[$_REQUEST['q']],'</a>';
  }
}//is set $_REQUEST[q]
?>
404 Not Found.
