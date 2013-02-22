<?php
include("CONSTANTS.php");
header('Content-type: text/xml'); // define XML content type
$handle = fopen(PROGRESS_XML, 'r');
if (!$handle) {
    error_log("Progress could not open progress file");
} else {
    flock($handle, LOCK_SH);
    $response = @stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    $progress = simplexml_load_string($response);
    echo $progress->asXML();
}
?>