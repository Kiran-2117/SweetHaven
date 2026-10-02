<?php

session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$current_page = "customers";   

function e($t) { return htmlspecialchars((string)$t); }


function time_ago($secs) {
    if ($secs === null) return 'Never';
    $secs = (int)$secs;
    if ($secs < 60)      return 'Just now';
    if ($secs < 3600)    return floor($secs / 60) . ' min ago';
    if ($secs < 86400)   return floor($secs / 3600) . ' hr ago';
    if ($secs < 2592000) { $d = floor($secs / 86400); return $d . ($d == 1 ? ' day' : ' days') . ' ago'; }
    return floor($secs / 2592000) . ' mo ago';
}

function dot_class($secs) {
    if ($secs === null) return 'grey';
    return ($secs <= 86400) ? 'green' : (($secs <= 604800) ? 'amber' : 'grey');
}


$hasLastLogin = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'last_login'")) > 0;
$loginCols = $hasLastLogin
    ? "u.last_login, TIMESTAMPDIFF(SECOND, u.last_login, NOW()) AS secs_ago"
    : "NULL AS last_login, NULL AS secs_ago";

$statSql = "SELECT COUNT(*) AS total, SUM(created_at >= NOW() - INTERVAL 30 DAY) AS newc"
         . ($hasLastLogin ? ", SUM(last_login >= NOW() - INTERVAL 7 DAY) AS activec" : "")
         . " FROM users";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $statSql));


$q   = trim($_GET['q'] ?? '');
$tab = $_GET['tab'] ?? 'all';
$where = []; $types = ''; $params = [];

if ($q !== '') {
    $where[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $like = '%' . $q . '%';
    $types .= 'sss';
    array_push($params, $like, $like, $like);
}
if ($tab === 'active' && $hasLastLogin) {
    $where[] = "u.last_login >= NOW() - INTERVAL 7 DAY";
} elseif ($tab === 'new') {
    $where[] = "u.created_at >= NOW() - INTERVAL 30 DAY";
} elseif ($tab === 'buyers') {
    $where[] = "EXISTS (SELECT 1 FROM orders o WHERE o.id = u.id)";
} else {
    $tab = 'all';
}

$sql = "SELECT u.id, u.full_name, u.email, u.phone, u.created_at, $loginCols,
               (SELECT COUNT(*) FROM orders o WHERE o.id = u.id) AS order_count,
               (SELECT COALESCE(SUM(o.total_amount), 0) FROM orders o
                 WHERE o.id = u.id AND o.status <> 'cancelled') AS spent
        FROM users u"
     . ($where ? " WHERE " . implode(" AND ", $where) : "")
     . ($hasLastLogin ? " ORDER BY u.last_login DESC, u.created_at DESC" : " ORDER BY u.created_at DESC");
$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$customers = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);


$cust = null; $history = [];
$view = (int)($_GET['view'] ?? 0);
if ($view > 0) {
    $s = mysqli_prepare($conn, "SELECT u.id, u.full_name, u.email, u.phone, u.gender,
                                       u.billing_address, u.shipping_address, u.created_at, $loginCols
                                FROM users u WHERE u.id = ?");
    mysqli_stmt_bind_param($s, "i", $view);
    mysqli_stmt_execute($s);
    $cust = mysqli_fetch_assoc(mysqli_stmt_get_result($s));

    if ($cust) {
        $s = mysqli_prepare($conn, "SELECT order_id, total_amount, status, created_at
                                    FROM orders WHERE id = ? ORDER BY order_id DESC");
        mysqli_stmt_bind_param($s, "i", $view);
        mysqli_stmt_execute($s);
        $history = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customers - Sweet Haven Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* colours/fonts the sidebar needs. If you already have a shared admin CSS with these, delete this block. */
:root{--wine:#5c1a2e;--wine-dark:#4a1424;--gold:#C9A05A;--cream:#FAF5EF;--bark:#3a2a22;
      --font-body:'Jost',sans-serif;--font-display:'Cormorant Garamond',serif}
.admin-main{padding:32px 40px;min-width:0}
h1{font-family:var(--font-display);color:var(--wine);font-size:2rem;margin-bottom:4px}
.sub{color:#997E67;margin-bottom:20px;font-size:.92rem}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:22px}
.stat{background:#fff;border:1px solid #eadfd5;border-radius:16px;padding:16px 18px}
.stat .n{font-family:var(--font-display);font-size:1.9rem;color:var(--wine)}
.stat .l{font-size:.82rem;color:#997E67}
.bar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin-bottom:14px}
.tabs a{display:inline-block;padding:6px 16px;border-radius:20px;border:1px solid var(--wine);color:var(--wine);font-size:.85rem;margin-right:6px}
.tabs a.on{background:var(--wine);color:#fff}
.search input{font:inherit;padding:8px 14px;border-radius:20px;border:1px solid #cdbfb0;width:240px}
.search button{font:inherit;padding:8px 16px;border-radius:20px;border:0;background:var(--wine);color:#fff;cursor:pointer}
.card{background:#fff;border:1px solid #eadfd5;border-radius:18px;padding:18px;margin-bottom:20px;overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:720px}
th{text-align:left;color:#997E67;font-weight:500;font-size:.85rem;padding:10px}
td{padding:12px 10px;border-top:1px solid #f0e6dc;font-size:.92rem}
tr.row:hover{background:#fbf3ea}
.who{display:flex;align-items:center;gap:12px}
.av{width:38px;height:38px;border-radius:50%;background:var(--wine);color:#fff;display:grid;place-items:center;font-weight:600;flex-shrink:0}
.lbl{color:#997E67;font-size:.82rem}
.dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:6px}
.green{background:#3a9d5d}.amber{background:#e0a030}.grey{background:#c9c0b8}
a.link{color:#B44446}
.b{padding:4px 12px;border-radius:14px;font-size:.78rem;font-weight:500}
.pending{background:#faecc8;color:#8a6a1f}.processing{background:#dbe8f7;color:#2b5a91}
.shipped{background:#e3dcf5;color:#5a3f96}.delivered{background:#d8f0dc;color:#2c7a3d}.cancelled{background:#f6d9d9;color:#a33}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.notice{background:#faecc8;color:#6b4f12;padding:12px 16px;border-radius:12px;margin-bottom:16px;font-size:.88rem}
@media(max-width:800px){.admin-layout{grid-template-columns:1fr}.admin-main{padding:20px}.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="admin-layout">
<?php include 'admin_sidebar.php'; ?>

<main class="admin-main">

<?php if (!$hasLastLogin): ?>
    <div class="notice">"Last login" is empty because the <b>users</b> table has no <code>last_login</code> column yet.
        Run: <code>ALTER TABLE users ADD COLUMN last_login DATETIME NULL DEFAULT NULL AFTER created_at;</code>
        and update login.php to save it.</div>
<?php endif; ?>

<?php if ($cust): ?>
  
    <a class="link" href="customers.php">&larr; Back to all customers</a>
    <h1 style="margin-top:10px"><?= e($cust['full_name']) ?></h1>
    <p class="sub"><?= e($cust['email']) ?></p>

    <div class="grid">
        <div class="card">
            <p class="lbl">Phone</p><p><?= e($cust['phone'] ?: '-') ?></p>
            <p class="lbl" style="margin-top:10px">Gender</p><p><?= e($cust['gender'] ?: '-') ?></p>
            <p class="lbl" style="margin-top:10px">Billing address</p><p><?= nl2br(e($cust['billing_address'] ?: '-')) ?></p>
            <p class="lbl" style="margin-top:10px">Shipping address</p><p><?= nl2br(e($cust['shipping_address'] ?: '-')) ?></p>
        </div>
        <div class="card">
            <p class="lbl">Member since</p><p><?= date('M d, Y', strtotime($cust['created_at'])) ?></p>
            <p class="lbl" style="margin-top:10px">Last login</p>
            <p><span class="dot <?= dot_class($cust['secs_ago']) ?>"></span><?= time_ago($cust['secs_ago']) ?></p>
            <p class="lbl" style="margin-top:10px">Total orders</p><p><?= count($history) ?></p>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom:8px">Order history</h3>
        <table>
            <tr><th>Order</th><th>Date</th><th>Amount</th><th>Status</th><th></th></tr>
            <?php if (!$history): ?><tr><td colspan="5">This customer hasn't placed any orders yet.</td></tr><?php endif; ?>
            <?php foreach ($history as $o): ?>
            <tr class="row">
                <td>#<?= str_pad($o['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td>Rs. <?= number_format($o['total_amount'], 2) ?></td>
                <td><span class="b <?= e($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                <td><a class="link" href="orders.php?view=<?= $o['order_id'] ?>">View order &rarr;</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

<?php else: ?>
  
    <h1>Customers</h1>
    <p class="sub">People who have created an account on Sweet Haven.</p>

    <div class="stats">
        <div class="stat"><div class="n"><?= (int)$stats['total'] ?></div><div class="l">Total customers</div></div>
        <div class="stat"><div class="n"><?= (int)($stats['activec'] ?? 0) ?></div><div class="l">Logged in last 7 days</div></div>
        <div class="stat"><div class="n"><?= (int)$stats['newc'] ?></div><div class="l">New in last 30 days</div></div>
    </div>

    <div class="bar">
        <div class="tabs">
            <?php foreach (['all' => 'All', 'active' => 'Recently active', 'new' => 'New', 'buyers' => 'Has ordered'] as $key => $label): ?>
                <a href="customers.php?tab=<?= $key ?>&q=<?= urlencode($q) ?>" class="<?= $tab === $key ? 'on' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
        <form class="search" method="GET">
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
            <input type="text" name="q" placeholder="Search name, email or phone" value="<?= e($q) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="card">
        <table>
            <tr><th>Customer</th><th>Phone</th><th>Joined</th><th>Last login</th><th>Orders</th><th>Total spent</th><th></th></tr>
            <?php if (!$customers): ?><tr><td colspan="7">No customers found.</td></tr><?php endif; ?>
            <?php foreach ($customers as $c): ?>
            <tr class="row">
                <td><div class="who">
                    <div class="av"><?= e(strtoupper(mb_substr($c['full_name'], 0, 1))) ?></div>
                    <div><?= e($c['full_name']) ?><br><span class="lbl"><?= e($c['email']) ?></span></div>
                </div></td>
                <td><?= e($c['phone'] ?: '-') ?></td>
                <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td><span class="dot <?= dot_class($c['secs_ago']) ?>"></span><?= time_ago($c['secs_ago']) ?></td>
                <td><?= (int)$c['order_count'] ?></td>
                <td>Rs. <?= number_format($c['spent'], 2) ?></td>
                <td><a class="link" href="customers.php?view=<?= $c['id'] ?>">View &rarr;</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

</main>
</div>
</body>
</html>