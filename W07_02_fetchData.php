<php

required_once 'w07_01_connectDB.php';

$sql = "SELECT * FROM products";

$result = $conn->query($sql);

if ($result->rowCount() > 0) {
    // output data of each rowCount
} else {
    echo "<h2>พบ ข้อมูลในตาราง Product</h2>";

    $data = $result->fetchall(PDO::FETCH_NUM);

    print_r($data); // แสดงข้อมูลทั้งหมด

    echo "$data[0][0]<BR>";
}
?>