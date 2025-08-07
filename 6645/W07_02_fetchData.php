<?php

require_once 'W07_01_connectDB.php';

$sql = "SELECT * FROM products";

$result = $conn->query($sql);

if ($result->rowCount() > 0) {
        // output data of each rowCount
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    //echo "<BR>";
    //echo "<PRE>";
    //print_r($data);
    //echo "</PRE>";
    // แสดงผลข้อมูลที่ดึงมา
        header('Content-Type: application/json'); // ระบุ Content-Type เป็น JSON
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); // แปลงข้อมูลใน $arr เป็น JSON และแสดงผล
    } else {
        echo "0 results";
        echo "<h2>ไม่พบ ข้อมูลในตาราง Product</h2>";

    // $data = $result->fetchAll(PDO::FETCH_NUM);
    // $data = $result->fetchAll(PDO::FETCH_ASSOC);
    // $data = $result->fetchAll(PDO::FETCH_BOTH);

    // print_r($data); // แสดงข้อมูลทั้งหมดแบบ array

    // echo "$data[0][0] <br>";

    //print_r($data); // แสดงข้อมูลทั้งหมด

    //echo "$data[0][0]<BR>";

    // ===================================================
    // ใช้ prepared statement เพื่อป้องกัน SQL injection
    // ใช้ execute() เพื่อสั่งประมวลผล SQL
    // ใช้ fetchAll() เพื่อดึงข้อมูลทั้งหมดในคร้ังเดียว
    // ใช้ print_r() เพื่อแสดงข้อมูลทั้งหมดในรูปแบบ array
    // ===================================================

    

    
}
?>

