<?php
session_start();
include 'db.php';

// Fetch trending products (only the 3 specified sample products with provided image URLs)
$stmt = $conn->prepare("
    SELECT p.*, u.username AS supplier, c.name AS category 
    FROM products p 
    JOIN users u ON p.supplier_id = u.id 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.name IN ('4K LED Smart TV 55-Inch', 'Men’s Cotton T-Shirts (Bulk)', 'Women’s Denim Jeans')
    ORDER BY p.views DESC 
    LIMIT 3
");
$stmt->execute();
$trending_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch featured suppliers dynamically (no sample data, ordered by rating)
$stmt = $conn->prepare("
    SELECT u.*, COUNT(p.id) AS product_count 
    FROM users u 
    LEFT JOIN products p ON u.id = p.supplier_id 
    WHERE u.type = 'seller' 
    GROUP BY u.id 
    ORDER BY u.rating DESC 
    LIMIT 4
");
$stmt->execute();
$featured_suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B Wholesale Marketplace - Alibaba Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Roboto', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); 
            color: #333; 
            line-height: 1.6; 
        }
        header { 
            background: linear-gradient(to right, #ff6200, #ff8c00); 
            color: white; 
            padding: 20px 0; 
            text-align: center; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.2); 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            animation: slideDown 0.5s ease; 
        }
        @keyframes slideDown { from { transform: translateY(-100%); } to { transform: translateY(0); } }
        .container { 
            max-width: 1200px; 
            margin: 30px auto; 
            padding: 0 20px; 
        }
        .section { 
            margin-bottom: 50px; 
            background: white; 
            border-radius: 12px; 
            padding: 30px; 
            box-shadow: 0 6px 20px rgba(0,0,0,0.1); 
            animation: fadeIn 1s ease; 
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        h1, h2 { 
            color: #ff6200; 
            font-family: 'Poppins', sans-serif; 
            margin-bottom: 20px; 
        }
        h1 { font-size: 36px; }
        h2 { font-size: 28px; }
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 20px; 
        }
        .product-card { 
            background: #fff; 
            border-radius: 10px; 
            overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
            transition: transform 0.3s, box-shadow 0.3s; 
        }
        .product-card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 8px 25px rgba(0,0,0,0.2); 
        }
        .product-card img { 
            width: 100%; 
            height: 220px; 
            object-fit: cover; 
            border-bottom: 3px solid #ff6200; 
            display: block; 
        }
        .product-card-content { 
            padding: 15px; 
        }
        .product-card h3 { 
            font-size: 20px; 
            color: #333; 
            margin-bottom: 10px; 
            font-family: 'Poppins', sans-serif; 
        }
        .product-card p { 
            font-size: 14px; 
            color: #666; 
            margin: 5px 0; 
        }
        .supplier-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px; 
        }
        .supplier-card { 
            background: #fff; 
            border-radius: 8px; 
            padding: 15px; 
            text-align: center; 
            border: 1px solid #ddd; 
            transition: background 0.3s, transform 0.3s, color 0.3s; 
        }
        .supplier-card:hover { 
            background: #ff6200; 
            color: white; 
            transform: scale(1.05); 
        }
        .supplier-card h3 { 
            font-size: 18px; 
            margin-bottom: 10px; 
            font-family: 'Poppins', sans-serif; 
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
        .auth-buttons { 
            margin-top: 15px; 
        }
        .auth-buttons button { 
            margin: 0 10px; 
        }
        .search-bar { 
            margin: 20px 0; 
            text-align: center; 
        }
        .search-bar input { 
            padding: 12px; 
            width: 320px; 
            border: 1px solid #ddd; 
            border-radius: 5px 0 0 5px; 
            font-size: 16px; 
            outline: none; 
        }
        .search-bar button { 
            border-radius: 0 5px 5px 0; 
            padding: 12px 24px; 
        }
        @media (max-width: 768px) { 
            .product-grid { grid-template-columns: 1fr; } 
            .supplier-grid { grid-template-columns: 1fr; } 
            .search-bar input { width: 100%; } 
            .container { padding: 0 10px; } 
            h1 { font-size: 28px; } 
            h2 { font-size: 24px; } 
            .product-card img { height: 180px; } 
        }
    </style>
</head>
<body>
    <header>
        <h1>B2B Wholesale Marketplace</h1>
        <p>Your Global Source for Bulk Products</p>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search products..." onkeypress="if(event.key === 'Enter') searchProducts()">
            <button onclick="searchProducts()">Search</button>
        </div>
        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Logged in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (<?php echo htmlspecialchars($_SESSION['type']); ?>)</span>
                <button onclick="redirectTo('profile.php')">Profile</button>
                <button onclick="redirectTo('logout.php')">Logout</button>
            <?php else: ?>
                <button onclick="redirectTo('login.php')">Login</button>
                <button onclick="redirectTo('signup.php')">Signup</button>
            <?php endif; ?>
        </div>
    </header>
    <div class="container">
        <div class="section">
            <h2>Trending Products</h2>
            <div class="product-grid">
                <?php foreach ($trending_products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image'] ?? 'https://via.placeholder.com/300'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-card-content">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p>Price: $<?php echo number_format($product['price'], 2); ?> | MOQ: <?php echo $product['moq']; ?></p>
                            <p>Category: <?php echo htmlspecialchars($product['category']); ?></p>
                            <p>Supplier: <?php echo htmlspecialchars($product['supplier']); ?></p>
                            <button onclick="redirectTo('product.php?id=<?php echo $product['id']; ?>')">View Details</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="section">
            <h2>Featured Suppliers</h2>
            <?php if (empty($featured_suppliers)): ?>
                <p>No suppliers available.</p>
            <?php else: ?>
                <div class="supplier-grid">
                    <?php foreach ($featured_suppliers as $supplier): ?>
                        <div class="supplier-card">
                            <h3><?php echo htmlspecialchars($supplier['username']); ?></h3>
                            <p>Company: <?php echo htmlspecialchars($supplier['company_name'] ?? 'N/A'); ?></p>
                            <p>Rating: <?php echo number_format($supplier['rating'], 1); ?>/5</p>
                            <p>Products: <?php echo $supplier['product_count']; ?></p>
                            <button onclick="redirectTo('supplier.php?id=<?php echo $supplier['id']; ?>')">Contact Supplier</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }

        function searchProducts() {
            const query = document.getElementById('searchInput').value.trim();
            if (query) {
                redirectTo(`search.php?search=${encodeURIComponent(query)}`);
            }
        }
    </script>
</body>
</html>
