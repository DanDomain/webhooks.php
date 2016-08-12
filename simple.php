<?php

//get the incoming POST body as a string
$incomingJsonString = file_get_contents('php://input');

//write the body into a file which you can then view
$file = fopen("last_incoming_json.txt", "w");
if(!$file) {
    throw new Exception('Could not write to file.');
}
fwrite($file, $incomingJsonString);
fclose($file);

?>