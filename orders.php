<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>redirectTo('login.php');</script>";
    exit;
}

if ($_SESSION['type'] == 'buyer') {
    $stmt = $conn->prepare("
        SELECT o.*, p.name AS product_name, u.username AS supplier 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        JOIN users u ON o.supplier_id = u.id 
        WHERE o.buyer_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $conn->prepare("
        SELECT o.*, p.name AS product_name, u.username AS buyer 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        JOIN users u ON o.buyer_id = u.id 
        WHERE o.supplier_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
}
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['type'] == 'seller') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND supplier_id = ?");
    $stmt->execute([$status, $id, $_SESSION['user_id']]);
    echo "<script>alert('Order status updated!'); redirectTo('orders.php');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
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
        h2 { 
            color: #ff6200; 
            font-family: 'Poppins', sans-serif; 
            font-size: 28px; 
            margin-bottom: 20px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
            font-size: 14px; 
        }
        th { 
            background: #ff6200; 
            color: white; 
            font-family: 'Poppins', sans-serif; 
        }
        select, button { 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-size: 14px; 
        }
        button { 
            background: #ff6200; 
            color: white; 
            border: none; 
            cursor: pointer; 
            transition: background 0.3s, transform 0.2s; 
        }
        button:hover { 
            background: #e55a00; 
            transform: scale(1.05); 
        }
        .back-button { 
            display: inline-block; 
            margin-top: 20px; 
        }
        @media (max-width: 768px) { 
            .container { padding: 15px; } 
            th, td { font-size: 12px; padding: 8px; } 
            h2 { font-size: 24px; } 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Orders</h2>
        <?php if (empty($orders)): ?>
            <p>No orders found.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Product</th>
                    <th><?php echo $_SESSION['type'] == 'buyer' ? 'Supplier' : 'Buyer'; ?></th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <?php if ($_SESSION['type'] == 'seller'): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($_SESSION['type'] == 'buyer' ? $order['supplier'] : $order['buyer']); ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td><?php echo htmlspecialchars($order['payment_status']); ?></td>
                        <?php if ($_SESSION['type'] == 'seller'): ?>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                    <select name="status">
                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        <button class="back-button" onclick="redirectTo('index.php')">Back to Home</button>
    </div>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
