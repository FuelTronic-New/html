<?php

function DBQuery($Sql) {
    $dbhost = "picahoo.dedicated.co.za"; //your MySQL Server 
    $dbuser = "weboccult"; //your MySQL User Name 
    $dbpass = "Henz@rd011"; //your MySQL Password 
    $dbname = "SchoolSystem";

    $Connect = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if ($Connect->connect_errno) {
        echo $Connect->connect_error;
        exit;
    }
    $result = $Connect->query($Sql);
    if (!$result) {
        echo "Errormessage: %s\n", $Connect->error;
        exit;
    }
    return $result;
}
