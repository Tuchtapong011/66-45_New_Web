<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>odd Even Number</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">odd even Number</h1>
        <hr>
        <p class="text-center">กรุณากรอกตัวเลข</p>

        <form action="" method="post" class="text-center">
            <div class="form-group">
                <input type="number" name="number" id="number" class="form-control w-50 mx-auto" placeholder="Enter a number">
            </div>
            <button type="submit" class="btn btn-primary mt-3">Check</button>
        </form>

<!-- แสดงผลลัพธ์ -->


<!-- คำนวนหาเลขคู่เลขคึ่ -->

    </div>


    <?php
        $number = $_POST['number'] ?? null;
        if ($number % 2 == 0) {
            echo "<h3 class='text-success text-center'>the number $number This is an even number</h3>";
        } else {
            echo "<h3 class='text-success text-center'>the number $number This is an odd number</h3>";
        }
    ?>
    <hr>
    <a href="index.php">Home</a>

</body>
</html>