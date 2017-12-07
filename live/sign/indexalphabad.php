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
*/

function textout($text, $xout, $yout, $bg = 0xFEFEFE, $fg = 0xFFFFFF)
{
  $font_size = $yout * .50;
$font_file = './T2000DOTHWYE.ttf';
$type_space = imagettfbbox( $font_size, 0, $font_file, $text);

$text_width = abs($type_space[4] - $type_space[0]);
$baseimg = imagecreatetruecolor ($text_width + $yout, $yout );
$almostblack = imagecolorallocate($baseimg, 254, 254, 254);
imagefill($baseimg, 0, 0, $almostblack);
//$black = imagecolorallocate($baseimg, 255, 255, 255);
$black = imagecolorallocate($baseimg, 255, 255, 255);

imagettftext($baseimg, $font_size, 0, $yout * .50, $yout * .75, $black, $font_file, $text);
imagecolortransparent($baseimg, $almostblack);
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
/*
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
}*/
function textoutdir($text, $xout, $yout, $bg = 0xFEFEFE, $fg = 0xFFFFFF, $ix = 1)
{
$text_1 = substr($text, 0, $ix);
$text_2 = substr($text, $ix);
$font_size_1 = $yout * .75;
$font_size_2 = $font_size_1 * .80;
$font_file = './T2000DOTHWYE.ttf';
$type_space_1 = imagettfbbox( $font_size_1, 0, $font_file, $text_1);
$type_space_2 = imagettfbbox( $font_size_2, 0, $font_file, $text_2);

$text_width_1 = abs($type_space_1[4] - $type_space_1[0]);
$text_width_2 = abs($type_space_2[4] - $type_space_2[0]);

$text_x = $yout * .1;
$text_y = $yout * .875;

$baseimg = imagecreatetruecolor ($text_width + $yout, $yout );
$almostblack = imagecolorallocate($baseimg, 254, 254, 254);
imagefill($baseimg, 0, 0, $almostblack);
$black = imagecolorallocate($baseimg, 255, 255, 255);

imagettftext($baseimg, $font_size_1, 0, $text_x, $text_y, $black, $font_file, $text_1);
imagettftext($baseimg, $font_size_2, 0, $text_x + $text_width_1 + $yout * .07, $text_y, $black, $font_file, $text_2);
imagecolortransparent($baseimg, $almostblack);
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
     //if there is a *# at the end, we override and set $sh = $yout * #.
    if( ($iasterisk = strrpos($strarr1[$i], '*')) !== FALSE && $iasterisk < strlen($strarr1[$i]) )
    {
       $numstr = substr($strarr1[$i], $iasterisk + 1);
	$strarr1[$i] = substr($strarr1[$i], 0, $iasterisk);
       if(is_numeric($numstr))
	{
	  $sh = $yout * floatval($numstr);
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
    else
    {
      //unknown, just put the text in color
      if(strlen($strarr1[$i]) > 0)
	 $lineimg = textout($strarr1[$i], $xout, $yout, 0x0000FF, $fg);
      else
        $lineimg = NULL;
    }
  }
  else
  {
    //text
    if(strlen($strarr1[$i]) > 0)
    {
      $lineimg = textout($strarr1[$i], $xout, $yout, $bg, $fg);
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
  imagesavealpha($baseimg, true);
  imagealphablending($baseimg, false);
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
  imagesavealpha($baseimg, true);
  imagealphablending($baseimg, false);

//  /* with padding*/ $baseimg = imagecreatetruecolor ($maxwidth + $yout * 2, $height + $cury);
//  imagefilledrectangle( $baseimg, 0, 0, imagesx($baseimg), imagesy($baseimg), imagecolorallocate( $baseimg, ($bg & 0xFF0000) >> 16, ($bg & 0x00FF00) >> 8, ($bg & 0x0000FF) ));
  imagefilledrectangle( $baseimg, 0, 0, imagesx($baseimg), imagesy($baseimg), imagecolorallocatealpha( $baseimg, 0, 0, 0, 0x255 ));
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

function state($state, $text, $county = 'Ocean County')
{

}
if(!isset($_REQUEST['debug']))
{
	header('Content-type: image/png');
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
if(isset($_REQUEST['num'])) $text = $_REQUEST['num'];
if(isset($_REQUEST['state'])) $state = $_REQUEST['state'];
if(isset($_REQUEST['title'])) $title = $_REQUEST['title'];
if(isset($_REQUEST['type'])) $type = $_REQUEST['type'];
if(isset($_REQUEST['h'])) $h = $_REQUEST['h'];
$w = (strlen($text) > 2)?(1.25 * $h):$h;

//select type.
if($type == 'i')
	$ret = ih($text, $w, $h, $title, $state);
else if($type == 'us')
	$ret = us($text, $w, $h);
else if($type == 'us48')
	$ret = us48($text, $w, $h, $state);
else if($type == 'text1')
	$ret = textout($text, $w, $h);
else if($type == 'textp')
	$ret = textoutparse($text, $w, $h);
else if($type == 'text')
	$ret = textoutm($text, $w, $h);
imagepng($ret);
imagedestroy($ret);
?>
