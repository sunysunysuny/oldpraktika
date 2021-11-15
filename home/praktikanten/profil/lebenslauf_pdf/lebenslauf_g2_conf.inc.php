<?php 
 // 1, if the cv-header should be printed above everything
 $cvConf['printCVHeader'] = 1; 
 // conf for big-category headers
 $cvConf['header-big'] = array('spaceBefore' => 10, 'spaceAfter' => 5, 'fontName' => 'h2', 'cellAlign' => 'L', 'upperCase' => 1);
 // conf for the cv-header
 $cvConf['header-cv'] = array('spaceBefore' => 0, 'spaceAfter' => 0, 'fontName' => 'h2', 'cellAlign' => 'C', 'upperCase' => 1);
 // font-conf used by header-big and header-cv 
 $cvConf['font-h2'] = array('family' => 'Arial', 'style' => 'B', 'size' => 12, 'lineHeight' => 0);
 // font-conf used by dateFontName and expFirstFontName
 $cvConf['font-bold'] = array('family' => 'Arial', 'style' => 'B', 'size' => 9, 'lineHeight' => 0);
 // column-layout for personal-data
 $cvConf['cols-personal'] = array('firstStart' => 0, 'secStart' => 60, 'firstEnd' => 50, 'secEnd' =>120);

 // g1-inherited-conf
 $cvConf['dateAlign'] = 'L';
 $cvConf['cols-detail'] = array('firstStart' => 60, 'secStart' => 90, 'firstEnd' => 85, 'secEnd' =>0); 
 $cvConf['dateFontName'] = 'bold';
 $cvConf['detailDescAlign'] = 'L';
 $cvConf['headerLangKeys'] = array('cv' => 0, 'education' => 0, 'experience' => 0, 'skills' => 'knowledge', 'school' => 0, 'profEdu' => 0, 'acadEdu' => 0, 'praktika' =>0, 'professional' => 0, 'knowledge' => 'skills', 'language' => 0, 'hobbies' => 0, 'references' => 0);
 $cvConf['detailLangKeys'] = array('profEdu' => 'content', 'acadEdu' => 'content', 'praktika' => 'description', 'professional' => 'description');
 $cvConf['expFirstFontName'] = 'bold';
 
 // base -conf
 $cvConf['cols-std'] = array('firstStart' => 0, 'secStart' => 60, 'firstEnd' => 50, 'secEnd' =>0);
 $cvConf['lineWidth'] = 0.2;
 $cvConf['font-std'] = array('family' => 'Arial', 'style' => '', 'size' => 10, 'lineHeight' => 0);;
 $cvConf['font-h1'] = array('family' => 'Arial', 'style' => 'B', 'size' => 10, 'lineHeight' => 0);
 $cvConf['header-std'] = array('spaceBefore' => 5, 'spaceAfter' => 5, 'fontName' => 'h1','cellWidth'=> 0, 'cellHeight' => 0, 'cellAlign' => 'L', 'cellFill' => 0, 'cellBorder' => 0, 'textAlign' => 'L', 'colorsName' => 'std', 'upperCase' => 1);
 $cvConf['line-std'] = array('start' => 0, 'end' => 0, 'width' => 0.2, 'spaceBefore' => 1, 'spaceAfter' => 1);
 $cvConf['colors-std'] = array('textGray' => 0, 'drawGray' => 0, 'fillGray' => 100); 
 $cvConf['photoWidth'] = 40; // with of applicants photo
 $cvConf['spaceBig'] = 2;
 $cvConf['spaceSmall'] = 1;

 //$cvConf[''] = '';
?>
