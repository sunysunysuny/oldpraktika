<?php
class DATE_TIME {
    function set_locals($lngv){ // local settings, argument - "en", "ge" etc.
        return setlocale (LC_ALL, $lngv);
    }
    function d_unixtime_mysql($time){ //1055434956
        return date("Y-m-d", $time);  //2003-06-12
    }
    function d_timestamp_mysql($date_stamp_txt){ //20030604123530
        $date_stamp="";
        $date_stamp.=substr($date_stamp_txt,0,4)."-";
        $date_stamp.=substr($date_stamp_txt,4,2)."-";
        $date_stamp.=substr($date_stamp_txt,6,2);
        return $date_stamp; //2003-06-04
    }
    function d_txt_timestamp($txt_date){ //20,05,2003
        $date_string=substr($txt_date,6)."";
        $date_string.=substr($txt_date,3,2)."";
        $date_string.=substr($txt_date,0,2)."000000";
        return $date_string;//20030520000000
    }

    function d_unixtime_timestamp($time){ //1055434956
        $date=date("Ymd", $time);
        return $date."000000";  //20030612000000
    }

    function d_txt_unixtime($txt_date){ //20030501
        $date_string=mktime(0,0,0,substr($txt_date,4,2),substr($txt_date,6,2),substr($txt_date,0,4));
        return $date_string;    //unix time
    }
    function d_txt_string($txt_date){ //20,05,2003
        $date_string=substr($txt_date,6)."";
        $date_string.=substr($txt_date,3,2)."";
        $date_string.=substr($txt_date,0,2);
        return $date_string;//20030520
    }
    function d_timestamp_txt($date_stamp_txt){ //20030604123530
        $date_stamp="";
        $date_stamp.=substr($date_stamp_txt,0,4)."";
        $date_stamp.=substr($date_stamp_txt,4,2)."";
        $date_stamp.=substr($date_stamp_txt,6,2);
        return $date_stamp; //20030604
    }
    function d_unixtime_txt($time){ //1055434956
        return date("Ymd", $time);  //20030612
    }
    function d_unixtime_timestamp_minus($time,$minus_days){ //1055434956
        $year=date("Y", $time);
        $month=date("m", $time);
        $day=date("d", $time);
        settype($minus_days,"integer");
        settype($year,"integer");
        settype($month,"integer");
        settype($day,"integer");
        $time=mktime(0,0,0,$month,$day-$minus_days,$year);
        $date=date("Ymd", $time);
        return $date."000000";  //20030612000000
    }
}
?>
