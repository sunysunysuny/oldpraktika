<?php
if(!ob_start("ob_gzhandler")) ob_start();

$cacheFile = "/home/praktika/cache/cacheheader/statics/css/page_".$_GET["page_key"]."";

if(!file_exists($cacheFile)) exit();
header("Content-type: text/css");
header('Expires: Mon, 01 Jul 2020 05:00:00 GMT');

$hash = md5($_GET["page_key"].filemtime($cacheFile));
header ("Etag: \"" . $hash . "\"");

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"')
{
        // Return visit and no modifications, so do not send anything
        header ("HTTP/1.0 304 Not Modified");
        header ('Content-Length: 0');
        exit();
}

$data = file_get_contents($cacheFile);
$array = unserialize($data);

if(empty($data)) die("NotFound");

echo $array["cssContent"];
