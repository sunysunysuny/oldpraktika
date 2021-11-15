<?php
// this is the cv-specific configuration-file for the standard-cv

 // margin to leave on the left side of th ephoto
 $cvConf['photoLeftMargin'] = 5;
 // font name to use for date-strings, refers to font-configuration
 $cvConf['dateFontName'] = 'std';
 // alignment of date-strings in first column
 $cvConf['dateAlign'] = 'L';
 // column-layout of date-entry details
 $cvConf['cols-detail'] = array('firstStart' => 60, 'secStart' => 60, 'firstEnd' => 61, 'secEnd' =>0);
 // alignment of the description of the date-entry details
 $cvConf['detailDescAlign'] = 'L';
 // maps each header to a lang-table-key, which will be used for lookup of lang-specific string
 // default is used, if value is 0 
 $cvConf['headerLangKeys'] = array('cv' => 0, 'education' => 0, 'experience' => 0, 'skills' => 0, 'school' => 0, 'profEdu' => 0, 'acadEdu' => 0, 'praktika' =>0, 'professional' => 0, 'knowledge' => 0, 'language' => 0, 'hobbies' => 0, 'references' => 0);
 // maps each date-entry categorie to a lang-table-key, which will be used for lookup
 $cvConf['detailLangKeys'] = array('profEdu' => 0, 'acadEdu' => 0, 'praktika' => 0, 'professional' => 0);
 // font name to use for the first line of the date-entries within the experience-category, refers to font-configuration
 $cvConf['expFirstFontName'] = 'std';
 
 
 // base-conf
 $cvConf['cols-std'] = array('firstStart' => 0, 'secStart' => 60, 'firstEnd' => 50, 'secEnd' =>0);
 $cvConf['lineWidth'] = 0.2; 
 $cvConf['font-std'] = array('family' => 'Times', 'style' => '', 'size' => 10, 'lineHeight' => 0);
 $cvConf['font-h1'] = array('family' => 'Times', 'style' => 'B', 'size' => 12, 'lineHeight' => 0);
 $cvConf['header-std'] = array('spaceBefore' => 5, 'spaceAfter' => 5, 'fontName' => 'h1','cellWidth'=> 0, 'cellHeight' => 0, 'cellAlign' => 'L', 'cellFill' => 0, 'cellBorder' => 0, 'textAlign' => 'L', 'colorsName' => 'std', 'upperCase' => 1);
 $cvConf['line-std'] = array('start' => 0, 'end' => 0, 'width' => 0.2, 'spaceBefore' => 1, 'spaceAfter' => 1);
 $cvConf['colors-std'] = array('textGray' => 0, 'drawGray' => 0, 'fillGray' => 100); 
 $cvConf['photoWidth'] = 40; // with of applicants photo
 $cvConf['spaceBig'] = 2;
 $cvConf['spaceSmall'] = 1;
?>
