<?php
    
    //// connect database by MySQLI

//    $conn = new mysqli($host, $username, $password, $database);

//    if($conn->connect_error){
//        die("connection failed: " , $connect_error);
//    } else {
//        echo "kuy"
//    }
    //// connect database by PDO
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "db68s_product";

    $dns = "mysql:host=$host;dbname=$database";
    try {
    ////        $conn = new PDO("mysqli:host=$host;dbname=$database" , $$username, $$password);
        $conn = new PDO($dns, $username, $password);
        // set this PDO error mode to exception
        $conn->setattribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e){
        echo "connection failed: " . $e->getmessage();
    }

?>

