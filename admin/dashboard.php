<?php

require_once "config.php";
require_admin_login(); 

$admin_name = $_SESSION["admin_name"] ?? "Kiran";

// TOTAL REVENUE- ALL ORDERS
$total_revenue = 0;
$result = mysqli_query($conn, "SELECT SUM(total_amount) AS total FROM orders WHERE status != 'cancelled'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_revenue = $row["total"] ? $row["total"] : 0;
}

// TOTAL ORDER- ORDER IN ROWS
$total_orders = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_orders = $row["total"];
}

// TOTAL PRODUCTS
$total_products = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_products = $row["total"];
}

// TOTAL CUSTOMERS
$total_customers = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_customers = $row["total"];
}

// AVG RATING
$avg_rating = 0;
$result = mysqli_query($conn, "SELECT AVG(rating) AS avg_rating FROM reviews");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $avg_rating = $row["avg_rating"] ? round($row["avg_rating"], 1) : 0;
}

// REVENUE FOR LAST 7 DAYS
$trend_labels = [];
$trend_values = [];

for ($i = 6; $i >= 0; $i--) {

    $day_date  = date("Y-m-d", strtotime("-$i day")); // e.g. 2026-08-07
    $day_label = date("D", strtotime("-$i day"));      // e.g. Fri

    $sql = "SELECT SUM(total_amount) AS total FROM orders
            WHERE DATE(created_at) = '$day_date' AND status != 'cancelled'";
    $result = mysqli_query($conn, $sql);
    $day_total = 0;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $day_total = $row["total"] ? $row["total"] : 0;
    }

    $trend_labels[] = $day_label;
    $trend_values[] = $day_total;
}

// PRODUCTS PER CATEGORY
$cat_labels = [];
$cat_values = [];

$sql = "SELECT c.name AS category_name, COUNT(p.product_id) AS product_count
        FROM categories c
        LEFT JOIN products p ON p.categories_id = c.categories_id
        GROUP BY c.categories_id
        HAVING product_count > 0
        ORDER BY product_count DESC
        LIMIT 6";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cat_labels[] = $row["category_name"];
        $cat_values[] = $row["product_count"];
    }
}

// RECENT ORDER
$recent_orders = [];
$sql = "SELECT o.order_id, u.full_name, o.total_amount, o.status, o.created_at
        FROM orders o
        JOIN users u ON u.id = o.id
        ORDER BY o.created_at DESC
        LIMIT 6";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recent_orders[] = $row;
    }
}
// LOW STOCK PRODUCTS
$low_stock = [];
$sql = "SELECT name AS product_name, stock AS stock_quantity
        FROM products
        WHERE stock <= 5
        ORDER BY stock ASC
        LIMIT 5";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $low_stock[] = $row;
    }
}

// RECENT REVIEW
$recent_reviews = [];
$sql = "SELECT r.rating, r.review_text, r.created_at, u.full_name, p.name AS product_name
        FROM reviews r
        JOIN users u ON u.id = r.user_id
        JOIN products p ON p.product_id = r.product_id
        ORDER BY r.created_at DESC
        LIMIT 4";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recent_reviews[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Sweet Haven Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="admin-layout">

    <?php
    $current_page = "dashboard"; 
    include "admin_sidebar.php";
    ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle sidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="topbar-right">
                <button class="icon-btn" aria-label="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    <span class="badge">3</span>
                </button>
                <div class="admin-chip">
                    <span class="avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></span>
                    <span><?php echo htmlspecialchars($admin_name); ?></span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>
        </header>

        <div class="page-head">
            <h1>Welcome back, <?php echo htmlspecialchars($admin_name); ?></h1>
            <p class="breadcrumb">Here's what's happening at Sweet Haven today.</p>
        </div>

        <!-- ============ KPI CARDS ============ -->
        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon kpi-wine"><i class="fa-solid fa-sack-dollar"></i></div>
                <div>
                    <div class="kpi-label">Total Revenue</div>
                    <div class="kpi-value">Rs. <?php echo number_format($total_revenue, 2); ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-clay"><i class="fa-solid fa-cart-shopping"></i></div>
                <div>
                    <div class="kpi-label">Total Orders</div>
                    <div class="kpi-value"><?php echo number_format($total_orders); ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-gold"><i class="fa-solid fa-box"></i></div>
                <div>
                    <div class="kpi-label">Total Products</div>
                    <div class="kpi-value"><?php echo number_format($total_products); ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-bark"><i class="fa-solid fa-users"></i></div>
                <div>
                    <div class="kpi-label">Customers</div>
                    <div class="kpi-value"><?php echo number_format($total_customers); ?></div>
                </div>
            </div>
        </section>

        <!-- ============ CHARTS ============ -->
        <section class="chart-grid">
            <div class="chart-card">
                <div class="chart-card-head"><h3>Revenue — Last 7 Days</h3></div>
                <canvas id="revenueChart" height="110"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-card-head"><h3>Products by Category</h3></div>
                <canvas id="categoryChart" height="110"></canvas>
            </div>
        </section>

        <!-- ============ ORDERS + SIDE WIDGETS ============ -->
        <section class="bottom-grid">

            <div class="table-card">
                <div class="chart-card-head">
                    <h3>Recent Orders</h3>
                    <a href="orders.php" class="view-all">View all <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <?php if (count($recent_orders) === 0): ?>
                    <p class="empty-state">No orders yet.</p>
                <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo str_pad($order["order_id"], 4, "0", STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($order["full_name"]); ?></td>
                            <td>Rs. <?php echo number_format($order["total_amount"], 2); ?></td>
                            <td><span class="status-badge status-<?php echo $order["status"]; ?>"><?php echo ucfirst($order["status"]); ?></span></td>
                            <td><?php echo date("d M", strtotime($order["created_at"])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <aside class="side-widgets">

                <div class="side-card">
                    <h3>Low Stock Alert</h3>
                    <?php if (count($low_stock) === 0): ?>
                        <p class="empty-state">All products are well stocked.</p>
                    <?php else: ?>
                        <?php foreach ($low_stock as $item): ?>
                        <div class="low-stock-row">
                            <span class="stock-dot"></span>
                            <span class="stock-name"><?php echo htmlspecialchars($item["product_name"]); ?></span>
                            <span class="stock-count"><?php echo $item["stock_quantity"]; ?> left</span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="side-card">
                    <h3>Recent Reviews <span class="avg-rating"><i class="fa-solid fa-star"></i> <?php echo $avg_rating ? $avg_rating : "—"; ?></span></h3>
                    <?php if (count($recent_reviews) === 0): ?>
                        <p class="empty-state">No reviews yet.</p>
                    <?php else: ?>
                        <?php foreach ($recent_reviews as $review): ?>
                        <div class="review-row">
                            <div class="review-top">
                                <span class="review-stars">
                                    <?php for ($star = 1; $star <= 5; $star++): ?>
                                        <?php if ($star <= $review["rating"]): ?>
                                            <i class="fa-solid fa-star star-on"></i>
                                        <?php else: ?>
                                            <i class="fa-solid fa-star star-off"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </span>
                                <span class="review-date"><?php echo date("d M", strtotime($review["created_at"])); ?></span>
                            </div>
                            <p class="review-text"><?php echo htmlspecialchars(substr($review["review_text"] ?? "", 0, 80)); ?></p>
                            <p class="review-meta"><?php echo htmlspecialchars($review["full_name"]); ?> on <?php echo htmlspecialchars($review["product_name"]); ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </aside>

        </section>

    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script src="add_product.js"></script>
<script>
    
    const trendLabels = <?php echo json_encode($trend_labels); ?>;
    const trendValues = <?php echo json_encode($trend_values); ?>;
    const catLabels    = <?php echo json_encode($cat_labels); ?>;
    const catValues    = <?php echo json_encode($cat_values); ?>;
</script>
<script src="dashboard.js"></script>
</body>
</html>