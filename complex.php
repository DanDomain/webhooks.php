<?php

//get the incoming POST body as a string
$incomingJsonString = file_get_contents('php://input');

//decode the JSON string to an associative array.
$eventArray = json_decode($incomingJsonString, true);

foreach($eventArray as $event) {

    if($event['ObjectType'] == "Product") {
        //the event is for a product.

        if($event['NewValues'] === null && $event['OldValues'] !== null) {
            //product was deleted.
            $productNumber = $event['OldValues']['ObjectIdentifier'];

            
        } else if($event['NewValues'] !== null && $event['OldValues'] === null) {
            //product was created.
            $productNumber = $event['NewValues']['ObjectIdentifier'];


        } else if($event['NewValues'] !== null && $event['OldValues'] !== null) {
            //product was modified.

            $oldProductNumber = $event['OldValues']['ObjectIdentifier'];
            $newProductNumber = $event['NewValues']['ObjectIdentifier'];

            
        } else {
            //this should not happen, so throw an error.
            throw new Exception('Could not determine from the event what type of operation was performed on the object.')
        }
    }

}

?>