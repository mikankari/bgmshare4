<?php

function loadJSON($filename) {
    // file_get_contents はファイルロックしてくれないのでここで実装する
    $lock = fopen(__DIR__ . '/' . $filename, 'r');
    flock($lock, LOCK_SH);
    $contents = file_get_contents(__DIR__ . '/' . $filename); // fread より速い
    flock($lock, LOCK_UN);
    fclose($lock);

    return json_decode($contents);
}

function saveJSON($filename, $contents) {
    return file_put_contents(__DIR__ . '/' . $filename, json_encode($contents), LOCK_EX);
}
