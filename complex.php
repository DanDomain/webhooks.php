<?php

//get the incoming POST body as a string
$incomingJsonString = file_get_contents('php://input');

//decode the JSON string to an associative array.
$eventArray = json_decode($incomingJsonString, true);

$offset = 0;
foreach($eventArray as $key => $item) {

    $offset++;

    if($item['ObjectType'] == "Product") {
        //the event is for a product.

        if($item['NewValues'] === null && $item['OldValues'] !== null) {
            //product was deleted.
            $productNumber = $item['OldValues']['ObjectIdentifier'];

            write_to_file(
                get_event_data(
                    $item['PropertiesChanged'],
                    $item['OldValues']),
                "Product deleted with product number $productNumber",
                $offset);

        } else if($item['NewValues'] !== null && $item['OldValues'] === null) {
            //product was created.
            $productNumber = $item['NewValues']['ObjectIdentifier'];

            write_to_file(
                get_event_data(
                    $item['PropertiesChanged'],
                    $item['NewValues']),
                "Product created with product number $productNumber",
                $offset);

        } else if($item['NewValues'] !== null && $item['OldValues'] !== null) {
            //product was modified.

            $oldProductNumber = $item['OldValues']['ObjectIdentifier'];
            $newProductNumber = $item['NewValues']['ObjectIdentifier'];

            write_to_file(
                get_event_change_data($item),
                "Product modified with product numbers $oldProductNumber -> $newProductNumber",
                $offset);
            
        } else {
            //this should not happen, so throw an error.
            throw new Exception('Could not determine from the event what type of operation was performed on the object.');
        }
    }

}

function get_event_data(
    $propertiesChanged,
    $values) 
{
    $data = '';

    foreach($propertiesChanged as $key => $propertyChanged) {
        $value = $values[$propertyChanged];

        $data = $data . "$propertyChanged: \"$value\"\r\n";
        $data = $data . "\r\n";
    }

    return $data;
}

function get_event_change_data($item) {
    $data = '';
    
    $propertiesChanged = $item['PropertiesChanged'];
    foreach($propertiesChanged as $key => $propertyChanged) {
        $oldValue = $item['OldValues'][$propertyChanged];
        $newValue = $item['NewValues'][$propertyChanged];

        $data = $data . "$propertyChanged: \"$oldValue\" -> \"$newValue\"\r\n";
        $data = $data . "\r\n";
    }

    return $data;
}

function write_to_file(
    $content, 
    $title,
    $offset) 
{
    $file = fopen("event_$offset.txt", "w");
    if(!$file) {
        throw new Exception('Could not write to file.');
    }

    fwrite($file, $title);
    fwrite($file, "\r\n");
    fwrite($file, $content);
    fclose($file);
}

?>