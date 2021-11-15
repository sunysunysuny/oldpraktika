<?php

$l_curr_year = date("Y");
$lst_month_arr = array("1","2","3","4","5","6","7","8","9","10","11","12");
/*
--------------------------------
OLD
--------------------------------
*/
$prj_path="event/";                     // project reletive root directory
$frm_options_reload=0;                  // make refresh in the form options, if 0 - hide button/reload OnChange event; 1 - show button/reload on button click
$db_save_ev_cl=0;                       // save events in DB on Click(clicks to show events details)
$year_deapth=1;                         // deapth of years in the select event options form
$look_backwards=0;                      // if 1-look backwards, if 0 - don't look backwards
$show_calendar=1;                       // if show calender, then - 1, else - 0
//********************************* bd constans


$lst_tbls=array(1 => "veranstaltungskalender", 2 => "kategorie",
3 => "land", 4 => "bundesland");        // tables's list used   ($arr_dbtbls_lst)
//********************************* END bd constans
//********************************* html tablse
$bg_main_tbl="<table border='0' width='90%' cellspacing='0' cellpadding='5'>";
$bg_opt_tbl="<table border='0' width='300' cellspacing='0' cellpadding='3'>";
$bg_list_tbl="<table border='0' width='200px' cellspacing='0' cellpadding='5'>";
$bg_col_tr_frmsel="#FFFFE0";
$bg_col_tr_year="#E0E0E0";
$bg_col_tr_month="#E0E0E0";
$bg_col_tr_ev="#FFFFF5";
$td_width_arr=array(1 => "80", 2 => "160", 3 => "180", 4 => "160", 5 => "40");
?>
