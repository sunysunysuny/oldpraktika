<?php
ob_start();
require_once("/home/praktika/etc/gb_config.inc.php");

switch($_POST["act"]) {
    case "create":
        $result = $hDB->query("INSERT INTO prakt2.firmenordner SET firmenid = ".$_SESSION["s_firmenid"].", bereich = ".(int)$_POST["bereich"].", name = '".mysql_real_escape_string($_POST["name"])."', reihenfolge = 100", $praktdbmaster);
        break;
    case "rename":
        $result = $hDB->query("UPDATE prakt2.firmenordner SET name = '".mysql_real_escape_string($_POST["new"])."' WHERE id = ".(int)$_POST["dirID"]." AND firmenid = ".$_SESSION["s_firmenid"], $praktdbmaster);
        break;
    case "remove":
        $result = $hDB->query("DELETE FROM prakt2.firmenordner WHERE id = ".(int)$_POST["dirID"]." AND firmenid = ".$_SESSION["s_firmenid"], $praktdbmaster);

        switch($_POST["bereich"]) {
            case 1:
                $dbstring = 'UPDATE bewerbung SET fordner = 0 WHERE firmenid = '.intval($_SESSION['s_firmenid']).' AND fordner='.(int)$_POST["dirID"];
                break;
            case 2:
                $dbstring = 'UPDATE firmenkontakte SET ordner = 0 WHERE firmenid = '.intval($_SESSION['s_firmenid']).' AND ordner='.(int)$_POST["dirID"];
                break;
            case 3:
                $dbstring = 'UPDATE bewerberkontakte SET fordner = 0 WHERE firmenid = '.intval($_SESSION['s_firmenid']).' AND fordner='.(int)$_POST["dirID"];
                break;
            case 4:
                $dbstring = 'UPDATE firmenbookmark ordner = 0 WHERE firmenid = '.intval($_SESSION['s_firmenid']).' AND ordner='.(int)$_POST["dirID"];
                break;
        }
        $hDB->query($dbstring, $praktdbmaster);
        break;
}