<?php

function performQuery($query) {
    $dbconn = pg_connect("host=localhost dbname=MasterDocumentRegister user=postgres password=1234")
        or die('Could not connect: ' . pg_last_error());
    $queryRecords = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());
    $data = pg_fetch_all($queryRecords);
    pg_close($dbconn);
    return $data;
}

function performQueryCOUNT($query) {
    $dbconn = pg_connect("host=localhost dbname=MasterDocumentRegister user=postgres password=1234")
        or die('Could not connect: ' . pg_last_error());
    $queryRecords = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());;
    pg_close($dbconn);
    return $queryRecords;
}

function performQueryINSERT($query) {
    $dbconn = pg_connect("host=localhost dbname=MasterDocumentRegister user=postgres password=1234")
        or die('Could not connect: ' . pg_last_error());
    $queryRecords = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());
    // waarom werkt onderstaande niet???
    if(!$queryRecords) {
        echo "<script type='text/javascript'>alert('".pg_last_error($dbconn)."')</script>";
    } else {
        echo "<script type='text/javascript'>alert('Records created successfully\n')</script>";
    };
    pg_close($dbconn);
}

?>