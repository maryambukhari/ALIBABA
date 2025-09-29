<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>redirectTo('login.php');</script>";
    exit;
}
include 'db.php';

// Fetch quotations based on user type
if ($_SESSION['type'] == 'buyer') {
    $stmt = $conn->prepare("SELECT q.*, p.name AS product FROM quotations q JOIN products p ON q.product_id = p.id WHERE q.buyer_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $conn->prepare("SELECT q.*, p.name AS product, u.username AS buyer FROM quotations q JOIN products p ON q.product_id = p.id JOIN users u ON q.buyer_id = u.id WHERE p.supplier_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}
$quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle negotiation/update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['type'] == 'seller') {
    $id = $_POST['id'];
    $offered_price = $_POST['offered_price'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE quotations SET offered_price = ?, status = ? WHERE id = ?");
    $stmt->execute([$offered_price, $status, $id]);
    echo "<script>alert('Quotation updated!'); redirectTo('quotation.php');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotations</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(to bottom, #f0f4f8, #ffffff); padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #ff6200; color: white; }
        form { display: inline; }
        button { background: #ff6200; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        @media (max-width: 768px) { table { font-size: 12px; } }
    </style>
</head>
<body>
    <h2>Your Quotations</h2>
    <table>
        <tr><th>Product</th><th>Quantity</th><th>Offered Price</th><th>Status</th><?php if ($_SESSION['type'] == 'seller'): ?><th>Action</th><?php endif; ?></tr>
        <?php foreach ($quotations as $q): ?>
            <tr>
                <td><?php echo $q['product']; ?></td>
                <td><?php echo $q['quantity']; ?></td>
                <td>$<?php echo $q['offered_price']; ?></td>
                <td><?php echo $q['status']; ?></td>
                <?php if ($_SESSION['type'] == 'seller'): ?>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                            <input type="number" name="offered_price" placeholder="New Price" step="0.01">
                            <select name="status">
                                <option value="negotiating">Negotiate</option>
                                <option value="accepted">Accept</option>
                                <option value="rejected">Reject</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
    <button onclick="redirectTo('index.php')">Back</button>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
