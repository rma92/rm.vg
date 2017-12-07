<!DOCTYPE HTML>
<html>
<head>
<style>
.bgs
{
  border-radius: 10px;
  border: 2px solid white;
  background-color: #0FF;
  font-family: T2000DOTHWYD;
  color: #FFF;
  font-size: 32px;
  padding-left:14px;
  padding-right:14px;
  padding-top:4px;
  padding-right:4px;
}
.bgs img
{
}
.tab
{
width: 100px;
}
.dist
{
  font-size:20px;
}
</style>
</head>
<body bgcolor=red>
<div class="bgs tab">
Exit 2
</div>
<div class="bgs">
<center>
<?php
//$arr = array(2,4,5,8,10,12,15,16,17,19,20,22,24,25,26,27,29,30,35,37,39,40,43,44,45,49,55,57,59,64,65,66,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,93,94,95,96,97,99,'H1','H2','H3','H201' );
$arr = array(76, 676, 78, 278, 80, 280, 287, 95, 195, 295);
//for($i = 1; $i < 100; ++$i)
//{
  foreach($arr as $i)
//  echo '[I-', $i, ']';
//  echo '<img height=64px src=index.php?num=', $i, '&state=New%20Jersey>', "\n";
//}
?>
<img height=64px src=index.php?num=1-9&type=us>
<img src="index.php?type=text&back=0x00ffff&num=[To][I-76-NEW JERSEY][I-676][I-78][I-278][I-80][I-280][I-287][I-95][I-195][I-295]&h=48">
<br>
<table>
<tr>
<td valign=top style='font-size:20px'>
To
</td>
<td>
<img src='New_Jersey_Turnpike_Shield.svg' height=64px>
<!--<img src=index.php?type=textp&back=0x00ffff&num=[toll]_&h=64>-->
</td>
<td valign=top>
<span style="background-color:yellow;color:black;font-size:20px">TOLL</span>
</td>
<td valign=top>
<img src='GSParkway.svg' height=64px>
</td>
<td valign=top>
<span style="background-color:yellow;color:black;font-size:20px	">TOLL</span>
</td>
</tr>
</table>
<br/>
<img  src="index.php?type=text&back=0x00ffff&num=New Brunswick&h=48" alt="New Brunswick">
<br/>
<span class=dist><img  src="index.php?type=text&back=0x00ffff&num=Next Left&h=32" alt="Next Left"></span>
</center>
</div>
</body>
</html>