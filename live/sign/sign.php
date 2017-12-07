<?
header('content-type: image/png');

$baseimg = imagecreatefrompng('iblank.png');
$basedim = getimagesize('iblank.png');
$basewidth = $basedim[0];
$baseheight = $basedim[1];
imageAlphaBlending($baseimg, true);
imageSaveAlpha($baseimg, true);
$smallwidth = 32;
$smallheight = 32;
$smallimg = imagecreatetruecolor($smallwidth, $smallheight);
imagecopyresampled( $smallimg, $baseimg, 0, 0, 0, 0, $smallwidth, $smallheight, $basewidth, $baseheight); 
imageAlphaBlending($smallimg, true);
imageSaveAlpha($smallimg, true);

imagepng($smallimg);
imagedestroy($smallimg);
/*
<div style="background-size:100% 100%;border: 2px solid black;background-image:url('iblank.svg');height:120px;">
95
</div>
*/

?>
