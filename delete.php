<?php

require 'common.php';

if (! isset($_POST['id'])) {
    exit('id is not set');
}

$queue = loadJSON('queue.json');

$queue = array_values(array_filter($queue, function ($item) {
    return $item->id !== $_POST['id'];
}));

var_dump($queue);

saveJSON('queue.json', $queue);

header('Location: .');
