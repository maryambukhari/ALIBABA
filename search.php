<?php
session_start();
include 'db.php';

// Search and filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? PHP_INT_MAX;
$moq = $_GET['moq'] ?? PHP_INT_MAX;

$query = "SELECT p.*, u.username AS supplier FROM products p JOIN users u ON p.supplier_id = u.id WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND MATCH(name, description) AGAINST (?)"; // Advanced full-text
    $params[] = $search;
}
if ($category) {
    $query .= " AND category_id = ?";
    $params[] = $category;
}
$query .= " AND price >= ? AND price <= ? AND moq <= ?";
$params = array_merge($params, [$min_price, $max_price, $moq]);

$stmt = $conn->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categories for filter
$stmt = $conn->prepare("SELECT * FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(to bottom, #f0f4f8, #ffffff); padding: 20px; }
        form { margin-bottom: 20px; }
        .container { max-width: 1200px; margin: auto; }
        .product-card { display: inline-block; width: 30%; margin: 1.5%; background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.3s; }
        .product-card:hover { transform: scale(1.05); }
        .product-card img { width: 100%; border-radius: 10px 10px 0 0; height: 200px; object-fit: cover; }
        input, select { padding: 10px; margin: 5px; }
        button { background: #ff6200; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        @media (max-width: 768px) { .product-card { width: 100%; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Search & Filter Products</h2>
        <form method="GET">
            <input type="text" name="search" placeholder="Search..." value="<?php echo $search; ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php if ($category == $cat['id']) echo 'selected'; ?>><?php echo $cat['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="min_price" placeholder="Min Price" value="<?php echo $min_price; ?>">
            <input type="number" name="max_price" placeholder="Max Price" value="<?php echo $max_price; ?>">
            <input type="number" name="moq" placeholder="Max MOQ" value="<?php echo $moq; ?>">
            <button type="submit">Filter</button>
        </form>
        <div>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo $product['image'] ?? 'placeholder.jpg'; ?>" alt="Product">
                    <h3><?php echo $product['name']; ?></h3>
                    <p>Price: $<?php echo $product['price']; ?> | MOQ: <?php echo $product['moq']; ?></p>
                    <p>Supplier: <?php echo $product['supplier']; ?></p>
                    <button onclick="redirectTo('product.php?id=<?php echo $product['id']; ?>')">View</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
