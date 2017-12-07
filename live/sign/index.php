<?
function resize_image_smart($orig, $dest_width=null, $dest_height=null)
{
  $orig_width = imagesx($orig);
  $orig_height = imagesy($orig);
  $vertical_offset = 0;
  $horizontal_offset = 0;
  if($dest_width == null)
  {
    if($dest_height == null)
    {
      die('$dest_width and $dest_height cant both be null!');
    }
    // height is locked
    $dest_width = $dest_height * $orig_width / $orig_height;
  } else {
    if($dest_height == null)
    {
      // width is locked
      $dest_height = $dest_width * $orig_height / $orig_width;
    } else {
      // both dimensions are locked
      $vertical_offset = $dest_height - ($orig_height * $dest_width) / $orig_width;
      $horizontal_offset = $dest_width - ($dest_height * $orig_width) / $orig_height;
      if($vertical_offset < 0) $vertical_offset = 0;
      if($horizontal_offset < 0) $horizontal_offset = 0;
    }
  }
  $img = imagecreatetruecolor($dest_width, $dest_height);
  imagesavealpha($img, true);
  imagealphablending($img, false);
  $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
  imagefill($img, 0, 0, $transparent);
  imagecopyresampled($img, $orig, round($horizontal_offset / 2),
                                  round($vertical_offset / 2),
                                  0,
                                  0,
                                  round($dest_width - $horizontal_offset),
                                  round($dest_height - $vertical_offset),
                                  $orig_width,
                                  $orig_height);
  return $img;
}

function imagettftextSp($image, $size, $angle, $x, $y, $color, $font, $text, $spacing = 0)
{        
    if ($spacing == 0)
    {
        imagettftext($image, $size, $angle, $x, $y, $color, $font, $text);
    }
    else
    {
        $temp_x = $x;
        for ($i = 0; $i < strlen($text); $i++)
        {
            $bbox = imagettftext($image, $size, $angle, $temp_x, $y, $color, $font, $text[$i]);
            $temp_x += $spacing + ($bbox[2] - $bbox[0]);
        }
    }
}

function ih($text, $xout, $yout, $title = 'INTERSTATE', $state = '')
{
if(strlen($text) > 2)
{
  $baseimg = imagecreatefrompng ('iblankw.png');
}
else
{
  $baseimg= imagecreatefrompng ('iblank.png');
}
$baseimg_x = imagesx( $baseimg );
//number
$font_size = 240;//should be 300?
if(strlen($text) > 3)
{
  $font_file = './T2000DOTHWYB.ttf';
}
else
{
  $font_file = './T2000DOTHWYD.ttf';
}
$text_color = imagecolorallocate($baseimg, 255, 255, 255);
$type_space = imagettfbbox( $font_size, 0, $font_file, $text);
$text_width = abs($type_space[4] - $type_space[0]);
$text_x = ( $baseimg_x - $text_width) /2 - 15 ;
if(strlen($state) > 0)
{
  $text_y = 440;
}
else
{
  $text_y = 440; //601 - 138?
}
imagettftextSp ($baseimg, $font_size, 0, $text_x, $text_y, $text_color, $font_file, $text);
//title
$font_size = 66;//should be 300?
$font_file = './T2000DOTHWYC.ttf';
$text_color = imagecolorallocate($baseimg, 255, 255, 255);
$type_space = imagettfbbox( $font_size, 0, $font_file, $title);
$text_width = abs($type_space[4] - $type_space[0]);
$text_x = ( $baseimg_x - $text_width) /2 - 6;
$text_y = 113; //601 - 138?
imagettftextSp ($baseimg, $font_size, 0, $text_x, $text_y, $text_color, $font_file, $title);

//state
$font_size = 38;//should be 300?
$font_file = './T2000DOTHWYC.ttf';
$text_color = imagecolorallocate($baseimg, 255, 255, 255);
$type_space = imagettfbbox( $font_size, 0, $font_file, $state);
$text_width = abs($type_space[4] - $type_space[0]);
$text_x = ( $baseimg_x - $text_width) /2 - 6;
$text_y = 187; //601 - 138?
imagettftextSp ($baseimg, $font_size, 0, $text_x, $text_y, $text_color, $font_file, $state);


$x = resize_image_smart($baseimg, $xout, $yout);//should be 300?
imagedestroy($baseimg);
return $x;
}

/*
  puts out a line of text with correct margins. 
  $text = the text of the line.
  $xout = ignored, since it will be calculated dynamically.
  $yout = the total height of the line.
  $bg = background color (default 0x0F0)
  $fg = foreground color (default white)
  $font_file = either specify the path to the font to use, or 'p', which will result in a switch at the beginning of the text being parsed 
	/b Use T2000 B.
       /t Use Times New Roman
*/
function textout($text, $xout, $yout, $bg = 0x00FF00, $fg = 0xFFFFFF, $font_file = './T2000DOTHWYEMOD.ttf')
{
//number
if($font_file == 'p')
{
  $font_file = './T2000DOTHWYEMOD.ttf'; //Default font is T2000DOTHWYEMOD. 
  if($text[0] == "\\" && strlen($text) > 2)
  {
    $swc = $text[1];
    $text = substr($text, 2);
    if($swc == 'b')
    {
      $font_file = './T2000DOTHWYB.ttf';
    }
    else if($swc == 'c')
    {
      $font_file = './T2000DOTHWYC.ttf';
    }
    else if($swc == 'd')
    {
      $font_file = './T2000DOTHWYD.ttf';
    }
    else if($swc == 'e')
    {
      $font_file = './T2000DOTHWYE.ttf';
    }
    else if($swc == 'm')
    {
      $font_file = './T2000DOTHWYEMOD.ttf';
    }
    else if($swc == '6')
    {
      $font_file = './clearviewhwy-6-w.ttf';
    }
    else if($swc == '5')
    {
      $font_file = './clearviewhwy-5-w.ttf';
    }
    else if($swc == '4')
    {
      $font_file = './clearviewhwy-4-w.ttf';
    }
    else if($swc == '3')
    {
      $font_file = './clearviewhwy-3-w.ttf';
    }
    else if($swc == '2')
    {
      $font_file = './clearviewhwy-2-w.ttf';
    }
    else if($swc == 't')
    {
      $font_file = './times.ttf';
    }
    else if($swc == 's')
    {
      $font_file = './comic.ttf';
    }
    else if($swc == 'h')
    {
      $font_file = './helv.ttf';
    }
  }
}

$font_size = $yout * .50;

$type_space = imagettfbbox( $font_size, 0, $font_file, $text);

$text_width = abs($type_space[4] - $type_space[0]);
//$baseimg = imagecreatetruecolor ($text_width + $yout, $yout );
$baseimg = imagecreatetruecolor ($text_width + $yout * .5, $yout );
imagefilledrectangle( $baseimg, 0, 0, imagesx($baseimg), imagesy($baseimg), imagecolorallocate( $baseimg, ($bg & 0xFF0000) >> 16, ($bg & 0x00FF00) >> 8, ($bg & 0x0000FF) ));
$text_color = imagecolorallocate($baseimg, ($fg & 0xFF0000) >> 16, ($fg & 0x00FF00) >> 8, ($fg & 0x0000FF) );
//$text_x = $yout * .50;
$text_x = $yout * .25;
$text_y = $yout * .75;
imagettftextSp ($baseimg, $font_size, 0, $text_x, $text_y, $text_color, $font_file, $text);
return $baseimg;
}

/*
  puts out a line of text for directions on guide signs (e.g. [US-1][North])
  $text = the text of the line.
  $xout = ignored, since it will be calculated dynamically.
  $yout = the total height of the line.
  $bg = background color (default 0x0F0)
  $fg = foreground color (default white)
  $ix = the index at which letters shall be smaller. This is 1 by default per 2012 standards.
*/
function textoutdir($text, $xout, $yout, $bg = 0x00FF00, $fg = 0xFFFFFF, $ix = 1)
{
//number
$text_1 = substr($text, 0, $ix);
$text_2 = substr($text, $ix);
$font_size_1 = $yout * .75;
$font_size_2 = $font_size_1 * .80;
$font_file = './T2000DOTHWYE.ttf';
$type_space_1 = imagettfbbox( $font_size_1, 0, $font_file, $text_1);
$type_space_2 = imagettfbbox( $font_size_2, 0, $font_file, $text_2);

$text_width_1 = abs($type_space_1[4] - $type_space_1[0]);
$text_width_2 = abs($type_space_2[4] - $type_space_2[0]);

$baseimg = imagecreatetruecolor ($text_width_1 + $text_width_2 + $yout * .4, $yout );
imagefilledrectangle( $baseimg, 0, 0, imagesx($baseimg), imagesy($baseimg), imagecolorallocate( $baseimg, ($bg & 0xFF0000) >> 16, ($bg & 0x00FF00) >> 8, ($bg & 0x0000FF) ));
$text_color = imagecolorallocate($baseimg, ($fg & 0xFF0000) >> 16, ($fg & 0x00FF00) >> 8, ($fg & 0x0000FF) );
$text_x = $yout * .1;
$text_y = $yout * .875;
imagettftextSp ($baseimg, $font_size_1, 0, $text_x, $text_y, $text_color, $font_file, $text_1);
imagettftextSp ($baseimg, $font_size_2, 0, $text_x + $text_width_1 + $yout * .07, $text_y, $text_color, $font_file, $text_2);
return $baseimg;
}

/*
  TextOutParse
  puts out a line of text with correct margins. 
  $text = the text of the line.
  $xout = ignored, since it will be calculated dynamically.
  $yout = the total height of the line.
  $bg = background color (default 0x0F0)
  $fg = foreground color (default white)
*/
function textoutparse($text, $xout, $yout, $bg = 0x00FF00, $fg = 0xFFFFFF)
{
//number
//parse the text.

$strarr1 = preg_split("/[\[\]]/", $text);
$imgarr = array();
$width = 1;
$maxheight = 1;
//odd indexes of strarr1 are inside of the brackets.
for($i = 0; $i < count($strarr1); ++$i)
{
  if($i % 2 == 1)
  { 
    //parse brackets
     //default size:
     $sh = $yout * 1.5;//height of shields
    //default rotation: (degrees)
     $srdeg = 0;
     //if there is a *# at the end, we override and set $sh = $yout * #.
    if( ($iasterisk = strrpos($strarr1[$i], '*')) !== FALSE && $iasterisk < strlen($strarr1[$i]) )
    {
       $numstr = substr($strarr1[$i], $iasterisk + 1);
	$strarr1[$i] = substr($strarr1[$i], 0, $iasterisk);
       if(strrpos($numstr, 'd'))
       {
         $numstr_e = explode('d', $numstr);
         $numstr = $numstr_e[0];
         $degstr = $numstr_e[1];
       }
       if(is_numeric($numstr))
	{
	  $sh = $yout * floatval($numstr);
       }
       if(is_numeric($degstr))
       {
         $srdeg = intval($degstr);
       }
    }


    //check if its a shield:
    if(strpos($strarr1[$i], '-') != FALSE)
    {
      	$route = explode('-', $strarr1[$i]);

	if(strcasecmp('us', $route[0]) == 0)
       {
         $lineimg = us($route[1], 0, $sh);
       }
	else if(strcasecmp('us48', $route[0]) == 0)
       {
         $lineimg = us48($route[1], 0, $sh, $route[2]);
       }
	else if(strcasecmp('sr', $route[0]) == 0
		|| strcasecmp('nj', $route[0]) == 0
		|| strcasecmp('ms', $route[0]) == 0
		|| strcasecmp('ia', $route[0]) == 0
		|| strcasecmp('ny', $route[0]) == 0
		|| strcasecmp('de', $route[0]) == 0
		|| strcasecmp('ct', $route[0]) == 0
		|| strcasecmp('ri', $route[0]) == 0
		|| strcasecmp('ma', $route[0]) == 0
		|| strcasecmp('me', $route[0]) == 0
		|| strcasecmp('yt', $route[0]) == 0
		)
       { //state($state, $text = 0, $xout = 0, $yout = 600, $county = 'Ocean County')
         $lineimg = state($route[0], $route[1], 0, $sh, $route[2]);
       }

	else if(strcasecmp('i', $route[0]) == 0 || strcasecmp('ih', $route[0]) == 0)
       {
         $flag = (strlen($route[3]))?$route[3]:'INTERSTATE';
         $lineimg = ih($route[1], 0, $sh, $flag, $route[2]);
       }
       else
       {
         $lineimg = textout($strarr1[$i], $xout, $yout, 0xFF00FF, $fg);
       }
    }
    else if(strcasecmp('toll', $strarr1[$i]) == 0)
    {
       $lineimg = textoutdir($strarr1[$i], 0, $sh * .42, 0xFFFF00, 0x000000, 1);
    }
    else if(strcasecmp('to', $strarr1[$i]) == 0)
    {
       $lineimg = textoutdir($strarr1[$i], 0, $sh * .42, $bg, $fg, 2);
    }
    else if(strcasecmp('west', $strarr1[$i]) == 0 || strcasecmp('east', $strarr1[$i]) == 0 || strcasecmp('south', $strarr1[$i]) == 0 || strcasecmp('north', $strarr1[$i]) == 0)
    {
       $lineimg = textoutdir($strarr1[$i], 0, $sh * .42, $bg, $fg, 1);
    }
    else if(strcasecmp('arrowdown', $strarr1[$i]) == 0)
    {
       //$arrowimg = imagecreatefrompng( 'mutcddown.png' );
       $arrowimg = imagecreatetruecolor(258, 165);
	imagefill($arrowimg, 0, 0, imagecolorallocatealpha($arrowimg, 0, 0, 0, 127));
       imagesavealpha($arrowimg, TRUE);
       $downarrowpoints = array(
	130, 162,
	14, 44,
	107, 65,
	107, 0,
	153, 0,
	153, 65,
	244, 44,
	130, 162);
       imagefilledpolygon($arrowimg, $downarrowpoints, count($downarrowpoints) /2, 
		imagecolorallocatealpha($arrowimg, ($fg & 0xFF0000) >> 16, ($fg & 0x00FF00) >> 8, ($fg & 0x0000FF), 0));
	$arrowimg2 = imagerotate($arrowimg, $srdeg, $bg);
       $lineimg = resize_image_smart($arrowimg2, $sh * .65, $sh * .42);
       imagedestroy($arrowimg);
       imagedestroy($arrowimg2);
	$srdeg = 0;
    }
    else if(strcasecmp('arrowup', $strarr1[$i]) == 0)
    {
       //$arrowimg = imagecreatefrompng( 'mutcddown.png' );
       $arrowimg = imagecreatetruecolor(164, 264);
	imagefill($arrowimg, 0, 0, imagecolorallocatealpha($arrowimg, 0, 0, 0, 127));
       imagesavealpha($arrowimg, TRUE);
       $downarrowpoints = array(
	58, 263,
	61, 108,
	1, 123,
	82, 1,
	163, 123,
	103, 108,
	106, 263,
	58, 263);
       imagefilledpolygon($arrowimg, $downarrowpoints, count($downarrowpoints) /2, 
		imagecolorallocatealpha($arrowimg, ($fg & 0xFF0000) >> 16, ($fg & 0x00FF00) >> 8, ($fg & 0x0000FF), 0));
	$arrowimg2 = imagerotate($arrowimg, $srdeg, $bg);
       $lineimg = resize_image_smart($arrowimg2, $sh * .26 * 1.4, $sh * .42 * 1.4);
       imagedestroy($arrowimg);
       imagedestroy($arrowimg2);
	$srdeg = 0;
    }
    else
    {
      //unknown, just put the text
      if(strlen($strarr1[$i]) > 0)
	 $lineimg = textout($strarr1[$i], $xout, $sh * .42, $bg, $fg, 'p');
      else
        $lineimg = NULL;
    }
    
    //rotate the image if the rotation is not 0.
    if($srdeg != 0)
    {
      $lineimg2 = imagerotate($lineimg, $srdeg, $bg);
      imagedestroy($lineimg);
      $lineimg = $lineimg2;
    }
  }
  else
  {
    //text
    if(strlen($strarr1[$i]) > 0)
    { 

      //check command flags.
	$font_file = 'p';
        $lineimg = textout($strarr1[$i], $xout, $yout, $bg, $fg, $font_file);
    }
    else
    {
      $lineimg = NULL;
    }
  }

  if($lineimg != NULL)
  {
    $width += imagesx($lineimg);
    if(imagesy($lineimg) > $maxheight) $maxheight = imagesy($lineimg);
    array_push($imgarr, $lineimg);
  }
}
$baseimg = imagecreatetruecolor ($width, $maxheight );
imagefilledrectangle( $baseimg, 0, 0, imagesx($baseimg), imagesy($baseimg), imagecolorallocate( $baseimg, ($bg & 0xFF0000) >> 16, ($bg & 0x00FF00) >> 8, ($bg & 0x0000FF) ));
$curx = 0;
foreach($imgarr as $lineimg)
  {
    imagecopy( $baseimg, $lineimg, $curx, 0, 0, 0, imagesx($lineimg), imagesy($lineimg) );
    $curx += imagesx($lineimg);	
}

return $baseimg;
}

/*
  Magic Text Out. Splits by lines (\n or %0A) and calls textoutparse to get an image for each line.
  $text = the text. Split lines by \n or %0A. See Textoutparse for more details.
  $xout = ignored. This is determined automatically.
  $yout = the height of a LINE of text. 
  $bg = background color (default 0x0F0)
  $fg = foreground color (default white)
*/
function textoutm($text, $xout, $yout, $bg = 0x00FF00, $fg = 0xFFFFFF)
{
  $textarr = explode("\n", $text);
  $imgarr = array();
  $maxwidth = 1;
  $cury = 2; //$yout * .681;
  $height = $cury;
  foreach($textarr as $textline)
  {
    $lineimg = textoutparse($textline, $xout, $yout, $bg, $fg);
    if(imagesx($lineimg) > $maxwidth)
	$maxwidth = imagesx($lineimg);
    $height += imagesy($lineimg);
    array_push( $imgarr, $lineimg );
  }
  $baseimg = imagecreatetruecolor ($maxwidth + 1, $height + $cury);
//  /* with padding*/ $baseimg = imagecreatetruecolor ($maxwidth + $yout * 2, $height + $cury);
  imagefilledrectangle( $baseimg, 0, 0, imagesx($baseimg), imagesy($baseimg), imagecolorallocate( $baseimg, ($bg & 0xFF0000) >> 16, ($bg & 0x00FF00) >> 8, ($bg & 0x0000FF) ));
  foreach($imgarr as $lineimg)
  {
    imagecopy( $baseimg, $lineimg, ($maxwidth - imagesx($lineimg)) / 2, $cury, 0, 0, imagesx($lineimg), imagesy($lineimg) );
///*with padding*/    imagecopy( $baseimg, $lineimg, $yout + ($maxwidth - imagesx($lineimg)) / 2, $cury, 0, 0, imagesx($lineimg), imagesy($lineimg) );
    $cury += imagesy($lineimg);	
  }
  return $baseimg;
} /* textoutm */

function us($text, $xout, $yout)
{
if(strlen($text) > 2)
{
  $baseimg = imagecreatefrompng ('usblankw.png');
}
else
{
  $baseimg= imagecreatefrompng ('usblank.png');
}
$baseimg_x = imagesx( $baseimg );
//number
$font_size = 250;//should be 300?
if(strlen($text) > 3)
{
  $font_file = './T2000DOTHWYB.ttf';
}
else
{
  $font_file = './T2000DOTHWYD.ttf';
}
$text_color = imagecolorallocate($baseimg, 0, 0, 0);
$type_space = imagettfbbox( $font_size, 0, $font_file, $text);
$text_width = abs($type_space[4] - $type_space[0]);
$text_x = ( $baseimg_x - $text_width) /2 - 5;
$text_y = 415;
imagettftextSp ($baseimg, $font_size, 0, $text_x, $text_y, $text_color, $font_file, $text);

if($xout == 0) $xout = (strlen($text) > 2)?(1.25 * $h):$h;
  $x = resize_image_smart($baseimg, $xout, $yout);//should be 300?
imagedestroy($baseimg);
return $x;
}

function us48($text, $xout, $yout, $state)
{
if(strlen($text) > 2)
{
  $baseimg = imagecreatefrompng ('usblankw.png');
}
else
{
  $baseimg= imagecreatefrompng ('usblank.png');
}
$baseimg_x = imagesx( $baseimg );
//number
$font_size = 200;//should be 300?
if(strlen($text) > 3)
{
  $font_file = './T2000DOTHWYB.ttf';
}
else
{
  $font_file = './T2000DOTHWYD.ttf';
}
$text_color = imagecolorallocate($baseimg, 0, 0, 0);
$type_space = imagettfbbox( $font_size, 0, $font_file, $text);
$text_width = abs($type_space[4] - $type_space[0]);
$text_x = ( $baseimg_x - $text_width) /2 - 10 ;
$text_y = 500;
imagettftextSp ($baseimg, $font_size, 0, $text_x, $text_y, $text_color, $font_file, $text);

//state
$font_file = './T2000DOTHWYB.ttf';
$font_size = 75;
$type_space = imagettfbbox( $font_size, 0, $font_file, $state);
$text_width = abs($type_space[4] - $type_space[0]);
$text_x = ( $baseimg_x - $text_width) /2;
$text_y = 160;
imagettftextSp ($baseimg, $font_size, 0, $text_x, $text_y, $text_color, $font_file, $state);


//title
$title = 'U S';
$font_file = './T2000DOTHWYE.ttf';
$font_size = 80;
$type_space = imagettfbbox( $font_size, 0, $font_file, $title);
$text_width = abs($type_space[4] - $type_space[0]);
$text_x = ( $baseimg_x - $text_width) /2 - 5;
$text_y = 290;
imagettftextSp ($baseimg, $font_size, 0, $text_x, $text_y, $text_color, $font_file, $title);


if(strlen($text) > 2)
	imageline( $baseimg, 42, 200, 708, 200, $text_color );
else
	imageline( $baseimg, 42, 200, 557, 200, $text_color );
if($xout == 0) $xout = (strlen($text) > 2)?(1.25 * $h):$h;
$x = resize_image_smart($baseimg, $xout, $yout);//should be 300?
imagedestroy($baseimg);
return $x;
}

/*
	State route marker.
	$text = the number.
	$county = county (if applicable)
*/
function state($state, $text = 0, $xout = 0, $yout = 600, $county = 'Ocean County')
{

	if(strcasecmp($state, 'ct') == 0)
	{
		if(strlen($text) > 2)
		{
		       $baseimg = imagecreatetruecolor(750, 600);
			imagefill($baseimg, 0, 0, imagecolorallocatealpha($baseimg, 0, 0, 0, 127));
			imagefilledrectangle( $baseimg, 5, 5, 750, 590, imagecolorallocatealpha($baseimg, 0,0,0,0));
			imagefilledrectangle( $baseimg, 20, 20, 740, 580, imagecolorallocatealpha($baseimg, 255,255,255,0));
		}
		else
		{
		       $baseimg = imagecreatetruecolor(600, 600);
			imagefill($baseimg, 0, 0, imagecolorallocatealpha($baseimg, 0, 0, 0, 127));
			imagefilledrectangle( $baseimg, 5, 5, 590, 590, imagecolorallocatealpha($baseimg, 0, 0, 0 ,0));
			imagefilledrectangle( $baseimg, 20, 20, 580, 580, imagecolorallocatealpha($baseimg, 255,255,255,0));
		}
	}
	else if(strcasecmp($state, 'ny') == 0)
	{
		/*if(strlen($text) > 2)
		{
		       $baseimg = imagecreatetruecolor(750, 600);
			imagefill($baseimg, 0, 0, imagecolorallocatealpha($baseimg, 0, 0, 0, 127));
			//imagefilledrectangle( $baseimg, 5, 5, 750, 590, imagecolorallocatealpha($baseimg, 0,0,0,0));
			//imagefilledrectangle( $baseimg, 20, 20, 740, 580, imagecolorallocatealpha($baseimg, 255,0,255,0));

		}
		else*/
		{
		       $baseimg = imagecreatetruecolor(600, 600);
			imagefill($baseimg, 0, 0, imagecolorallocatealpha($baseimg, 0, 0, 0, 127));
			//imagefilledrectangle( $baseimg, 5, 5, 590, 590, imagecolorallocatealpha($baseimg, 0, 0, 0 ,0));
			//imagefilledrectangle( $baseimg, 20, 20, 580, 580, imagecolorallocatealpha($baseimg, 255,0,255,0));
/*//hexagon
$downarrowpoints = array(
	32, 119, //top left

	300, 30, //top

	568, 119, //top right
	568, 470,
	300, 570,
	32, 470,
	32, 119);
*/
$downarrowpoints = array(
	32, 119, //top left
	60, 116,
	80, 115,
	100, 108,
	110, 106,
	120, 100,
	130, 96, 
	140, 89,
	150, 83,
	160, 78,
	170, 72,
	180, 64,
	190, 60,
	200, 52,
	300, 30, //top
	400, 52,
	410, 60,
	420, 64,
	430, 72,
	440, 78,
	450, 83,
	460, 89,
	470, 96,
	480, 100,
	490, 106,
	500, 108,
	520, 115,
	540, 116,
	568, 119, //top right
	568, 470,
	300, 570,
	32, 470,
	32, 119);
       imagefilledpolygon($baseimg, $downarrowpoints, count($downarrowpoints) /2, 
		imagecolorallocatealpha($baseimg, 255,255,255, 0));
		}
	}
	else
	{
		if(strlen($text) > 2)
		{
		       $baseimg = imagecreatetruecolor(750, 600);
			imagefill($baseimg, 0, 0, imagecolorallocatealpha($baseimg, 0, 0, 0, 127));
			imagefilledellipse( $baseimg, 375, 300, 748, 598, imagecolorallocatealpha($baseimg, 255,255,255,0));
		}
		else
		{
		       $baseimg = imagecreatetruecolor(600, 600);
			imagefill($baseimg, 0, 0, imagecolorallocatealpha($baseimg, 0, 0, 0, 127));
			imagefilledellipse( $baseimg, 300, 300, 598, 598, imagecolorallocatealpha($baseimg, 255,255,255,0));
		}
	}
imagesavealpha($baseimg, TRUE);
$baseimg_x = imagesx( $baseimg );
//number
$font_size = 250;//should be 300?
if(strlen($text) > 3)
{
  $font_file = './T2000DOTHWYB.ttf';
}
else
{
  $font_file = './T2000DOTHWYD.ttf';
}
$text_color = imagecolorallocate($baseimg, 0, 0, 0);
$type_space = imagettfbbox( $font_size, 0, $font_file, $text);
$text_width = abs($type_space[4] - $type_space[0]);
$text_x = ( $baseimg_x - $text_width) /2 - 9;
$text_y = 415;
imagettftextSp ($baseimg, $font_size, 0, $text_x, $text_y, $text_color, $font_file, $text);

if($xout == 0) $xout = (strlen($text) > 2)?(1.25 * $h):$h;
  $x = resize_image_smart($baseimg, $xout, $yout);//should be 300?
imagedestroy($baseimg);
return $x;
}

if(!isset($_REQUEST['debug']))
{
	header('Content-type: image/png');
	$expires = 60*60*24*1;
	header("Pragma: public");
	header("Cache-Control: maxage=".$expires);
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
}
//2 digit: 601 x 601
//3 digit: 751 x 601 ratio 1.249
//4 digit: uses 3 digit shield with B font.
//$ret = ih('H201', 205, 164, 'INTERSTATE', 'HAWAII');

$type = 'i';
$state = '';
$title = 'INTERSTATE';
$text = '395';
$h = 256;
$back = 0x007757;
$fore = 0xFFFFFF;
if(isset($_REQUEST['num'])) $text = $_REQUEST['num'];
if(isset($_REQUEST['state'])) $state = $_REQUEST['state'];
if(isset($_REQUEST['title'])) $title = $_REQUEST['title'];
if(isset($_REQUEST['type'])) $type = $_REQUEST['type'];
{
  if($type == 'text') $h = 64;
}
if(isset($_REQUEST['h'])) $h = $_REQUEST['h'];
if(isset($_REQUEST['fore']) && is_numeric($_REQUEST['fore'])) $fore = intval($_REQUEST['fore'], 0);
if(isset($_REQUEST['back']) && is_numeric($_REQUEST['back'])) $back  = intval($_REQUEST['back'], 0);
$w = (strlen($text) > 2)?(1.25 * $h):$h;

//select type.
if($type == 'i')
	$ret = ih($text, $w, $h, $title, $state);
else if($type == 'state')
{
	$ret = state($state, $text, $w, $h, 'Ocean County');
}
else if($type == 'us')
	$ret = us($text, $w, $h);
else if($type == 'us48')
	$ret = us48($text, $w, $h, $state);
else if($type == 'text1')
	$ret = textout($text, $w, $h, $back, $fore, 'p');
else if($type == 'textp')
	$ret = textoutparse($text, $w, $h, $back, $fore);
else if($type == 'text')
	$ret = textoutm($text, $w, $h, $back, $fore);
//imagecolortransparent($ret, imagecolorallocate($ret, 0, 255, 0));
imagepng($ret);
imagedestroy($ret);
?>
