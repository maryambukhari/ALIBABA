<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    echo "<script>redirectTo('index.php');</script>";
    exit;
}
$supplier_id = $_GET['id'];
$stmt = $conn->prepare("
    SELECT u.*, COUNT(p.id) AS product_count 
    FROM users u 
    LEFT JOIN products p ON u.id = p.supplier_id 
    WHERE u.id = ? AND u.type = 'seller'
");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$supplier) {
    echo "<script>redirectTo('index.php');</script>";
    exit;
}

$stmt = $conn->prepare("
    SELECT p.*, c.name AS category 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.supplier_id = ?
");
$stmt->execute([$supplier_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Profile - <?php echo htmlspecialchars($supplier['username']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <style>
        /* Internal CSS: Consistent with Alibaba clone theme */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Roboto', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); 
            color: #333; 
            line-height: 1.6; 
        }
        .container { 
            max-width: 1000px; 
            margin: 30px auto; 
            padding: 20px; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 6px 20px rgba(0,0,0,0.1); 
            animation: fadeIn 1s ease; 
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        h2, h3 { 
            color: #ff6200; 
            font-family: 'Poppins', sans-serif; 
        }
        h2 { font-size: 28px; margin-bottom: 20px; }
        h3 { font-size: 22px; margin: 20px 0 10px; }
        p { 
            font-size: 16px; 
            color: #666; 
            margin: 10px 0; 
        }
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 15px; 
        }
        .product-card { 
            background: #f9f9f9; 
            border-radius: 8px; 
            padding: 15px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            transition: transform 0.3s; 
        }
        .product-card:hover { 
            transform: translateY(-5px); 
        }
        .product-card img { 
            width: 100%; 
            height: 150px; 
            object-fit: cover; 
            border-radius: 8px; 
            margin-bottom: 10px; 
        }
        button { 
            background: #ff6200; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 5px; 
            cursor: pointer; 
            font-family: 'Poppins', sans-serif; 
            font-size: 14px; 
            transition: background 0.3s, transform 0.2s; 
        }
        button:hover { 
            background: #e55a00; 
            transform: scale(1.05); 
        }
        .button-group { 
            display: flex; 
            gap: 10px; 
            flex-wrap: wrap; 
            margin-top: 20px; 
        }
        @media (max-width: 768px) { 
            .container { padding: 15px; } 
            h2 { font-size: 24px; } 
            h3 { font-size: 20px; } 
            .product-grid { grid-template-columns: 1fr; } 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($supplier['username']); ?></h2>
        <p><strong>Company:</strong> <?php echo htmlspecialchars($supplier['company_name'] ?? 'N/A'); ?></p>
        <p><strong>Rating:</strong> <?php echo number_format($supplier['rating'], 1); ?>/5</p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($supplier['address'] ?? 'N/A'); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($supplier['phone'] ?? 'N/A'); ?></p>
        <p><strong>Products:</strong> <?php echo $supplier['product_count']; ?></p>
        <h3>Products</h3>
        <?php if (empty($products)): ?>
            <p>No products available.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image'] ?? 'https://via.placeholder.com/150'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p>Price: $<?php echo number_format($product['price'], 2); ?> | MOQ: <?php echo $product['moq']; ?></p>
                        <p>Category: <?php echo htmlspecialchars($product['category']); ?></p>
                        <button onclick="redirectTo('product.php?id=<?php echo $product['id']; ?>')">View Product</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="button-group">
            <?php if (isset($_SESSION['user_id']) && $_SESSION['type'] == 'buyer'): ?>
                <button onclick="redirectTo('message.php?to=<?php echo $supplier['id']; ?>')">Contact Supplier</button>
            <?php endif; ?>
            <button onclick="redirectTo('index.php')">Back to Home</button>
        </div>
    </div>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
