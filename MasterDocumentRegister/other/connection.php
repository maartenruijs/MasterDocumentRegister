<?php

function performQuery($query) {
    $dbconn = pg_connect("host=localhost dbname=MasterDocumentRegister user=postgres password=1234")
        or die('Could not connect: ' . pg_last_error());
    $queryRecords = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());
    pg_close($dbconn);
    return $queryRecords;
}

?>