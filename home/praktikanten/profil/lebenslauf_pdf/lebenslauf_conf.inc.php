<?php
// this is the base-configuration
// configuration-keys apply to all cvs
// values may be overridden in cv-specific configuration-file
// if not stated otherwise, all measures are in mm

	// page margins
 $cvConf['marginTop'] = 20;
 $cvConf['marginLeft'] = 25;
 $cvConf['marginRight'] = 20;
 $cvConf['marginBottom'] = 25;
 // placeholder, which will be replaces by the number of pages after creation
 $cvConf['aliasNbPages'] = '{%pages%}';
 // standard line-width for lines and borders etc. 
 $cvConf['lineWidth'] = 0.2; 
 // font-configuration, the part after the '-' is the name of the font, which can be refered to in other
 // configuration settings and wich will be usd for setFont($name) in CVBase
 // family - font-family to use (Arial | Times | Courier | Symbol | Zapfdingsbats)
 // style - 'B' for bold, 'I' for italic , 'U' for underline or '' for regular
 // size - font size in pixel 1px = 0.35 mmm
 // lineHeight - in mm
 $cvConf['font-std'] = array('family' => 'Times', 'style' => '', 'size' => 10, 'lineHeight' => 0);
 $cvConf['font-h1'] = array('family' => 'Times', 'style' => 'B', 'size' => 12, 'lineHeight' => 0);
 // header-configuration, the part after the '-' is the name of the header-style, used printHeader($txt, $name) of CVBase
 // spaceBefore - space before header, if not start of page
 // spaceAfter - space after header
 // fontName - name of font to use for header - corresponds to a matching font-configuration
 // cellWidth - width of cell, in wich header is printed 
 // cellHeight - height of cell
 // cellAlign - alignment of header-cell on page (L | R | C)
 // cellBorder - border around header-cell, 0 for no border, 1 for borders around or a combination of 'L','T','R','B' for left, top, right and bottom
 // cellFill - 1, if cell should be filled with current fill-color
 // textAlign - alignment of text in cell (L | R | C)
 // colorsName - name of color-settings to use for borders and fill-color
 // upperCase - 1, if header-text should be upper-cased
 $cvConf['header-std'] = array('spaceBefore' => 5, 'spaceAfter' => 5, 'fontName' => 'h1','cellWidth'=> 0, 'cellHeight' => 0, 'cellAlign' => 'L', 'cellFill' => 0, 'cellBorder' => 0, 'textAlign' => 'L', 'colorsName' => 'std', 'upperCase' => 0);
 // line-configuration, the part after the '-' is the name of the line-stye, used in drawLine($name) of CVBase
 // start - x-start position
 // end - x-end position
 // width - line-width
 // spaceBefore - space before line
 // spaceAfter - space after line
 $cvConf['line-std'] = array('start' => 0, 'end' => 0, 'width' => 0.2, 'spaceBefore' => 1, 'spaceAfter' => 1);
 // column-layout configuration, the part after the '-' is the name of the cloumn-layout used in setCols($name) of CVBase
 // firstStart - x-start position of first column
 // firstEnd - x-end position of first column
 // secStart - x-start position of second column
 // secEnd - x-end position of second column
 $cvConf['cols-std'] = array('firstStart' => 0, 'secStart' => 60, 'firstEnd' => 50, 'secEnd' =>0);
 // color-configuration, the part after the '-' is the name used in setColors($name) of CVBase
 // text*, draw* and fill* - you can specify gray-value, or RGB i.e. 'textR' => 50, 'textG' => 120, 'textB' => 10
 $cvConf['colors-std'] = array('textGray' => 0, 'drawGray' => 0, 'fillGray' => 100);
 // width of applicants-photo 
 $cvConf['photoWidth'] = 40;
 // space between different date-entries  
 $cvConf['spaceBig'] = 2;
 // space between description and details of date-entry
 $cvConf['spaceSmall'] = 1;
?>
