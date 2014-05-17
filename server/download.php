<?php
// FILE DOWNLOAD


if($_POST && isset($_POST['currentStates'])) {
    $objects = array();
    
    // create object
    $objects[] = $_POST['currentStates'];
    $objects[] = $_POST['w'];
    $objects[] = $_POST['h'];
    $objects[] = $_POST['posx'];
    $objects[] = $_POST['posy'];
    $objects[] = $_POST['s'];
    
    // use salt for protection
    $objects[] = md5(print_r($objects, true) . "8#$@#1");
    $filecontents = implode("\n", $objects);
    
    
    // start output
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=LifeGameSave.life');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filecontents));
    
    echo $filecontents;
}