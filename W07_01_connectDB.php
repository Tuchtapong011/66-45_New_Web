<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <php
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
    $password = "product_db";

    $dns = "mysqli:host=$host;dbname=$database";
    try (
////        $conn = new PDO("mysqli:host=$host;dbname=$database" , $$username, $$password);
        $conn = new PDO("$dns, $$username, $$password);
        // set this PDO error mode to exception
        $conn->setattribyte(PDO::ATTR_ERRORMODE, PDO::ERROR_EXEPTION);
    ) catch(PDOException $e){
        echo "connection failed: " . $e->getmessage();
    }

?>
</body>
</html>
