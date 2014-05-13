<?php

if ($_FILES["game"]["error"] > 0) {
    echo json_encode(array("error" => "Error: " . $_FILES["file"]["error"]));
} else {
    $contents = file_get_contents($_FILES["game"]["tmp_name"]);
    list($currentStates, $w, $h, $posx, $posy, $s, $payload) = explode("\n", $contents, 7);

    $objects = array();
    $objects[] = $currentStates;
    $objects[] = $w;
    $objects[] = $h;
    $objects[] = $posx;
    $objects[] = $posy;
    $objects[] = $s;

    if ($payload != md5(print_r($objects, true) . "8#$@#1")) {
        echo json_encode(array("error" => "Error: File error. Incorrect payload"));
    } else {
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