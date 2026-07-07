<?php
$db = 'sqlite:data/counter.db';
$c = new PDO($db);
if( isset($_REQUEST['slow']) )
{
  $s = $c->prepare('SELECT * FROM cnt');
}
else
{
  $s = $c->prepare('SELECT count(*),URI FROM cnt group by URI;');
}
$s->execute();
?>
<table>
<?php
while(($r = $s->fetch(PDO::FETCH_ASSOC)) !== false)
{
  $ak = array_keys( $r );
  if(!isset($header_done))
  {
    $header_done = 1;
    foreach($ak as $a)
    {
      echo '<th>', $a, '</th>';
    }
  }
  echo '<tr>';
  foreach( $ak as $a )
  {
    echo '<td>', $r[$a], '</td>';
  }
  echo "</tr>\n";
}
?>
</table>
