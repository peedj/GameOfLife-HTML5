<?php

// UPLOAD Life game files

if ($_FILES["game"]["error"] > 0) {
    echo json_encode(array("error" => "Error: " . $_FILES["file"]["error"]));
} else {
    // read file contents
    $contents = file_get_contents($_FILES["game"]["tmp_name"]);
    
    // parse data
    list($currentStates, $w, $h, $posx, $posy, $s, $payload) = explode("\n", $contents, 7);
    
    
    // build data object
    $objects = array();
    $objects[] = $currentStates;
    $objects[] = $w;
    $objects[] = $h;
    $objects[] = $posx;
    $objects[] = $posy;
    $objects[] = $s;
    
    // check for payload with salt
    if ($payload != md5(print_r($objects, true) . "8#$@#1")) {
        //  if error
        echo json_encode(array("error" => "Error: File error. Incorrect payload"));
    } else {
        // if data ok output it in JSON format
        echo json_encode(array(
            "currentStates" => $currentStates,
            "w" => $w,
            "h" => $h,
            "posx" => $posx,
            "posy" => $posy,
            "s" => $s,
        ));
    }
}