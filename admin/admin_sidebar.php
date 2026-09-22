<?php

if (!isset($current_page)) {
    $current_page = "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
    margin: 0;
    font-family: var(--font-body);
    background: var(--cream);
    color: var(--bark);
}

a { text-decoration: none; color: inherit; }

.admin-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
}

/* ---------------- SIDEBAR ---------------- */
.admin-sidebar {
    background: linear-gradient(180deg, var(--wine) 0%, var(--wine-dark) 100%);
    color: var(--blush);
    padding: 28px 20px;
    display: flex;
    flex-direction: column;
    gap: 28px;
    position: relative;
    overflow: hidden;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
}
.brand-icon { color: var(--gold); font-size: 22px; }
.brand-name {
    font-family: var(--font-display);
    font-size: 19px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #fff;
}
.brand-sub {
    font-size: 10px;
    letter-spacing: 3px;
    color: var(--gold);
    margin-top: 2px;
}

.side-nav {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}
.side-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 10px;
    font-size: 14.5px;
    color: #eaddd7;
    transition: background .15s ease, color .15s ease;
}
.side-nav a i { width: 18px; text-align: center; opacity: .9; }
.side-nav a:hover { background: rgba(255,255,255,0.08); color: #fff; }
.side-nav a.active {
    background: var(--gold);
    color: var(--wine-dark);
    font-weight: 600;
}

.logout-link {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #eaddd7;
    padding: 10px 14px;
}
.back-store-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 10px;
    padding: 10px;
    font-size: 13px;
    color: #fff;
}
.back-store-btn:hover { background: rgba(255,255,255,.08); }
    </style>
</head>
<body>
    

<aside class="admin-sidebar" id="adminSidebar">

    <div class="brand">
        <i class="fa-solid fa-leaf brand-icon"></i>
        <div>
            <div class="brand-name">SWEET HAVEN</div>
            <div class="brand-sub">ADMIN PANEL</div>
        </div>
    </div>

    <nav class="side-nav">
        <a href="dashboard.php"    class="<?php echo ($current_page === "dashboard")   ? "active" : ""; ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="add_products.php"  class="<?php echo ($current_page === "add_product") ? "active" : ""; ?>"><i class="fa-solid fa-circle-plus"></i> Add Product</a>
        <a href="products.php"     class="<?php echo ($current_page === "products")    ? "active" : ""; ?>"><i class="fa-solid fa-box"></i> Products</a>
        <a href="categories.php"   class="<?php echo ($current_page === "categories")  ? "active" : ""; ?>"><i class="fa-solid fa-table-cells"></i> Categories</a>
        <a href="collections.php"  class="<?php echo ($current_page === "collections") ? "active" : ""; ?>"><i class="fa-solid fa-layer-group"></i> Collections</a>
        <a href="orders.php"       class="<?php echo ($current_page === "orders")      ? "active" : ""; ?>"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
        <a href="customers.php"    class="<?php echo ($current_page === "customers")   ? "active" : ""; ?>"><i class="fa-solid fa-users"></i> Customers</a>
        <a href="reviews.php"      class="<?php echo ($current_page === "reviews")     ? "active" : ""; ?>"><i class="fa-regular fa-star"></i> Reviews</a>
        <a href="messages.php"     class="<?php echo ($current_page === "messages")    ? "active" : ""; ?>"><i class="fa-regular fa-envelope"></i> Messages</a>
        <a href="settings.php"     class="<?php echo ($current_page === "settings")    ? "active" : ""; ?>"><i class="fa-solid fa-gear"></i> Settings</a>
    </nav>

    <a href="admin_logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    <a href="../home.php" class="back-store-btn"><i class="fa-solid fa-arrow-left"></i> Back to Store</a>

</aside>
</body>
</html>