<?
$i=99;$a=array(' bottle',' of beer',' on the wall');
while($i)
printf("%d%s%s%s%s, %d%s%s%s.\n%s, %d bottle%s of beer on the wall.\n\n",$i,$a[0],$x=($i^1)?'s':'',$a[1],$a[1],$i,$a[0],$x,$a[1],
($i^1)?"Take one down, pass it around":"Go to the store and buy some more",
(--$i>0)?$i:99,($i^1)?'s':'');
?>
