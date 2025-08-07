<?php
    require_once 'W07_01_connectDB.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Loop For Show Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"/>
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" rel="stylesheet"/>
    <style>
        .container {
        max-width: 800px;
        }
    </style>
    </head>
    <body>

    <?php
        $sql = "SELECT * FROM products";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
        
        if (isset($_POST['price'])) {
        $filterPrice = $_POST['price'];
        $filteredProducts = array_filter($products, function($product) use ($filterPrice) {
            return $product['price'] <= $filterPrice;
        });
        $filteredProducts = array_values($filteredProducts);
        }else{
            // สินค้า array ใหม่ ต้องรี index are 0 
        $filteredProducts = $data;
        }
        
    ?>

    <div class="container mt-5">
        <h1>Product List</h1>

    <form action="" method="post" class="mb-3">
        <div class="input-group">
        <input type="number" name="price" placeholder="Enter price" class="form-control" required>
        <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <table id="producttable" class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>Name</th>
            <th>category</th>
            <th>Price</th>
            <th>Quantity</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($filteredProducts as $index => $product): ?>
            <tr>
            <td><?= $index + 1 ?></td>
            <td><?= $product['product_id'] ?></td>
            <td><?= $product['product_name'] ?></td>
            <td><?= $product['category'] ?></td>
            <td><?= $product['price'] ?></td>
            <td><?= $product['stock_quantity'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
        $('#producttable').DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100]
        });
    });
    </script>

</body>
</html>
