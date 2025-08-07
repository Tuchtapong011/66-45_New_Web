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
        $products = [
        ['id' => 1001, 'name' => 'apple', 'price' => 60, 'quantity' => 50],
        ['id' => 1002, 'name' => 'banana', 'price' => 30, 'quantity' => 100],
        ['id' => 1003, 'name' => 'orange', 'price' => 45, 'quantity' => 70],
        ['id' => 1004, 'name' => 'grape', 'price' => 80, 'quantity' => 40],
        ['id' => 1005, 'name' => 'mango', 'price' => 90, 'quantity' => 30],
        ['id' => 1006, 'name' => 'pineapple', 'price' => 55, 'quantity' => 25],
        ['id' => 1007, 'name' => 'papaya', 'price' => 40, 'quantity' => 60],
        ['id' => 1008, 'name' => 'watermelon', 'price' => 70, 'quantity' => 20],
        ['id' => 1009, 'name' => 'kiwi', 'price' => 65, 'quantity' => 35],
        ['id' => 1010, 'name' => 'pear', 'price' => 50, 'quantity' => 45],
        ['id' => 1011, 'name' => 'peach', 'price' => 75, 'quantity' => 38],
        ['id' => 1012, 'name' => 'plum', 'price' => 60, 'quantity' => 32],
        ['id' => 1013, 'name' => 'strawberry', 'price' => 85, 'quantity' => 28],
        ['id' => 1014, 'name' => 'blueberry', 'price' => 95, 'quantity' => 22],
        ['id' => 1015, 'name' => 'cherry', 'price' => 100, 'quantity' => 18],
    ];

    
        $filteredProducts = $products;
        if (isset($_POST['price'])) {
        $filterPrice = $_POST['price'];
        $filteredProducts = array_filter($products, function($product) use ($filterPrice) {
            return $product['price'] <= $filterPrice;
        });
        }else{
            // สินค้า array ใหม่ ต้องรี index are 0 
        $filteredProducts = array_values($filteredProducts);
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
            <th>Price</th>
            <th>Quantity</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($filteredProducts as $index => $product): ?>
            <tr>
            <td><?= $index + 1 ?></td>
            <td><?= $product['id'] ?></td>
            <td><?= $product['name'] ?></td>
            <td><?= $product['price'] ?></td>
            <td><?= $product['quantity'] ?></td>
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
