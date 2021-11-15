<?php
if(!isset($_GET['quality']) || !is_numeric($_GET['quality']))
{
	$_GET['quality'] = 0;
}

if (!is_int($_GET['quality']) && is_numeric($_GET['quality']))
{
	$_GET['quality'] = (int) round($_GET['quality']);
}

if (is_int($_GET['quality']) && $_GET['quality'] > 100)
{
	$_GET['quality'] = 100;
}

/**
 * Konfiguration
 */
#$fontfile = '/home/praktika/public_html/fonts/verdanab.ttf';


/**
 * �ffne Rohling
 */
$image = imagecreatetruecolor(140,14);
$bar   = imagecreatefrompng('/home/praktika/public_html/styles/images/regenbogen.png');

if ($image)
{
	imagealphablending($image, true);
	imagesavealpha($image, true);

        $trans_colour = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $trans_colour);

	$percent = ($_GET['quality'] / 100);

	/**
	 * Textfarbe bestimmen
	 */
	# $color = imagecolorat($image, (int) (imagesx($bar)-1)*$percent, 0);
	# $index = imagecolorsforindex($bar, $color);
	# $color = imagecolorallocate($image, $index['red'], $index['green'], $index['blue']);
	
	/**
	 * Prozentzahl erzeugen
	 */
	# imagettftext($image, 9, 0, 100, 14, $color, $fontfile, $_GET['quality'].'%');
	
	/**
	 * Balken erzeugen
	 */

	imagecopymerge($image, $bar, 0, 0, 0, 0, (int) imagesx($bar) * $percent, 14, 100);
	
	//imagefilledrectangle($bar, (imagesx($bar)-1)*$percent, 0, (imagesx($bar)-1)*$percent, 0, imagecolorallocate($bar, 0, 0, 0));
	
	/**
	 * Ausgabe
	 */
	if (function_exists("imagepng")) 
	{
		header("Content-type: image/png");
		//imagepng($bar);
		imagepng($image);
	}
	elseif (function_exists("imagegif")) 
	{
    	header("Content-type: image/gif");
    	//imagegif($bar);
    	ImageGif($bar);
	}

	//imagedestroy($bar);
	imagedestroy($image);
}

die();

?>