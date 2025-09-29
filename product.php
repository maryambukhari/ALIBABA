<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    echo "<script>redirectTo('index.php');</script>";
    exit;
}
$product_id = $_GET['id'];
$stmt = $conn->prepare("
    SELECT p.*, u.username AS supplier, u.id AS supplier_id, c.name AS category 
    FROM products p 
    JOIN users u ON p.supplier_id = u.id 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<script>redirectTo('index.php');</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['type'] == 'buyer') {
    $quantity = $_POST['quantity'];
    $offered_price = $_POST['offered_price'];
    $buyer_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        INSERT INTO quotations (buyer_id, product_id, quantity, offered_price, status) 
        VALUES (?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$buyer_id, $product_id, $quantity, $offered_price]);
    echo "<script>alert('Quotation requested successfully!'); redirectTo('quotation.php');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - <?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Roboto', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); 
            color: #333; 
            line-height: 1.6; 
        }
        .container { 
            max-width: 800px; 
            margin: 30px auto; 
            padding: 20px; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 6px 20px rgba(0,0,0,0.1); 
            animation: fadeIn 1s ease; 
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        h2 { 
            color: #ff6200; 
            font-family: 'Poppins', sans-serif; 
            font-size: 28px; 
            margin-bottom: 20px; 
        }
        img { 
            width: 100%; 
            max-height: 400px; 
            object-fit: cover; 
            border-radius: 10px; 
            border: 2px solid #ff6200; 
            margin-bottom: 20px; 
            display: block; 
        }
        p { 
            font-size: 16px; 
            color: #666; 
            margin: 10px 0; 
        }
        form { 
            margin: 20px 0; 
            display: flex; 
            flex-direction: column; 
            gap: 15px; 
        }
        input { 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-size: 16px; 
            outline: none; 
        }
        button { 
            background: #ff6200; 
            color: white; 
            border: none; 
            padding: 12px 24px; 
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
        }
        @media (max-width: 768px) { 
            .container { padding: 15px; } 
            img { max-height: 300px; } 
            h2 { font-size: 24px; } 
            input, button { font-size: 14px; } 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <img src="<?php echo htmlspecialchars($product['image'] ?? 'https://via.placeholder.com/400'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?> | <strong>MOQ:</strong> <?php echo $product['moq']; ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
        <p><strong>Supplier:</strong> <?php echo htmlspecialchars($product['supplier']); ?></p>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['type'] == 'buyer'): ?>
            <form method="POST">
                <input type="number" name="quantity" placeholder="Quantity" min="1" required>
                <input type="number" name="offered_price" placeholder="Offered Price" step="0.01" min="0" required>
                <button type="submit">Request Quotation</button>
            </form>
            <div class="button-group">
                <button onclick="redirectTo('message.php?to=<?php echo $product['supplier_id']; ?>')">Message Supplier</button>
                <button onclick="redirectTo('search.php')">Back to Search</button>
            </div>
        <?php else: ?>
            <div class="button-group">
                <button onclick="redirectTo('login.php')">Login to Request Quotation</button>
                <button onclick="redirectTo('search.php')">Back to Search</button>
            </div>
        <?php endif; ?>
    </div>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
