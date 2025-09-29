<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'buyer') {
    echo "<script>redirectTo('login.php');</script>";
    exit;
}

if (!isset($_GET['quotation_id'])) {
    echo "<script>redirectTo('quotation.php');</script>";
    exit;
}

$quotation_id = $_GET['quotation_id'];
$stmt = $conn->prepare("
    SELECT q.*, p.name AS product_name, p.supplier_id, p.price 
    FROM quotations q 
    JOIN products p ON q.product_id = p.id 
    WHERE q.id = ? AND q.buyer_id = ? AND q.status = 'accepted'
");
$stmt->execute([$quotation_id, $_SESSION['user_id']]);
$quotation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quotation) {
    echo "<script>alert('Invalid or unaccepted quotation'); redirectTo('quotation.php');</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Dummy payment processing (marks as paid)
    $stmt = $conn->prepare("
        INSERT INTO orders (buyer_id, supplier_id, product_id, quantity, total_price, status, payment_status) 
        VALUES (?, ?, ?, ?, ?, 'pending', 'paid')
    ");
    $total_price = $quotation['offered_price'] * $quotation['quantity'];
    $stmt->execute([
        $_SESSION['user_id'],
        $quotation['supplier_id'],
        $quotation['product_id'],
        $quotation['quantity'],
        $total_price
    ]);
    echo "<script>alert('Order placed successfully!'); redirectTo('orders.php');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order</title>
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
            max-width: 600px; 
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
        p { 
            font-size: 16px; 
            color: #666; 
            margin: 10px 0; 
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
        form { 
            margin: 20px 0; 
        }
        @media (max-width: 768px) { 
            .container { padding: 15px; } 
            h2 { font-size: 24px; } 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Place Order</h2>
        <p><strong>Product:</strong> <?php echo htmlspecialchars($quotation['product_name']); ?></p>
        <p><strong>Quantity:</strong> <?php echo $quotation['quantity']; ?></p>
        <p><strong>Offered Price:</strong> $<?php echo number_format($quotation['offered_price'], 2); ?></p>
        <p><strong>Total Price:</strong> $<?php echo number_format($quotation['offered_price'] * $quotation['quantity'], 2); ?></p>
        <form method="POST">
            <button type="submit">Confirm Order (Dummy Payment)</button>
        </form>
        <button onclick="redirectTo('quotation.php')">Back to Quotations</button>
    </div>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
