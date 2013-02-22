<?php

include_once("CONSTANTS.php");

$movie = new movie($_POST['contentId']);

foreach ($_POST as $key => $value) {
    if($key != "contentId") {
        if($key == "image" && $value != "") {
            $movie->hdImg = "roku/$value";
            $movie->sdImg = "roku/$value";
        } else {
            $movie->$key = htmlentities(str_replace('"',"",stripslashes($value)));
        }
    }
}

$fileElementName = "hdImg";

if (!empty($_FILES[$fileElementName]['error'])) {
    error_log("File upload error");
} elseif (empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none') {
    //error_log('No file was uploaded!');
} else {
    $movie->hdImg = "roku/images/".rawurlencode($_FILES[$fileElementName]['name']);
    $movie->sdImg = "roku/images/".rawurlencode($_FILES[$fileElementName]['name']);
    $target_path ='images/';
    $target_path = $target_path . basename( $_FILES[$fileElementName]['name']);
    move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $target_path);
    //for security reason, we force to remove all uploaded file
    @unlink($_FILES[$fileElementName]);
}

$movies[] = $movie;

$movieMeta = new videoMetaData();
$movieMeta->writeUpdate($movies);

header("location: /roku?page=".$_POST['page']."&playlistId=".$_POST['playlistId']);

?>
