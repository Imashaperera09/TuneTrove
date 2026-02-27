<?php
require_once 'includes/admin-header.php';

// Handle Delete
if (isset($_GET['remove_deal']) && in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    $pid = (int)$_GET['remove_deal'];
    $pdo->prepare("UPDATE products SET discount_percent=NULL, deal_start_date=NULL, deal_end_date=NULL, is_deal=0 WHERE id=?")->execute([$pid]);
    header("Location: deals.php?msg=Deal+removed"); exit();
}

// Handle Add/Update Deal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_deal'])) {
    $pid       = (int)$_POST['product_id'];
    $discount  = (float)$_POST['discount_percent'];
    $start     = $_POST['deal_start_date'] ?: null;
    $end       = $_POST['deal_end_date']   ?: null;
    $sale      = null;

    // Fetch product price to compute sale_price
    $p = $pdo->prepare("SELECT price FROM products WHERE id=?");
    $p->execute([$pid]);
    $row = $p->fetch();
    if ($row && $discount > 0) {
        $sale = round($row['price'] * (1 - $discount / 100), 2);
    }

    $pdo->prepare("UPDATE products SET discount_percent=?, deal_start_date=?, deal_end_date=?, sale_price=?, is_deal=1 WHERE id=?")
        ->execute([$discount, $start, $end, $sale, $pid]);
    header("Location: deals.php?msg=Deal+saved"); exit();
}

// Fetch all products with deals + all products for the form
$deals = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.is_deal=1 OR p.discount_percent > 0 ORDER BY p.id DESC")->fetchAll();
$all_products = $pdo->query("SELECT id, name, price, brand FROM products ORDER BY name")->fetchAll();
$today = date('Y-m-d');
?>

        <?php if (isset($_GET['msg'])): ?>
            <div style="background: rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.2); border-radius:0.5rem; padding:1rem; margin-bottom:1.5rem; color:#10b981; font-size:0.875rem;">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 2rem; align-items: flex-start; flex-wrap: wrap;">

            <!-- Add Deal Form -->
            <div class="content-card" style="flex: 0 0 360px; min-width: 300px;">
                <div class="card-header">
                    <h3 class="card-title">Add / Edit Deal</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <form method="POST" action="deals.php" style="display: grid; gap: 1rem;">
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Product</label>
                            <select name="product_id" required style="width:100%; padding:0.75rem; border:1px solid var(--admin-border); border-radius:0.5rem; font-size:0.9rem;">
                                <option value="">-- Select Product --</option>
                                <?php foreach ($all_products as $prod): ?>
                                    <option value="<?php echo $prod['id']; ?>">
                                        <?php echo htmlspecialchars($prod['name']); ?> — £<?php echo number_format($prod['price'],2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Discount %</label>
                            <input type="number" name="discount_percent" min="1" max="99" step="0.5" required placeholder="e.g. 20" style="width:100%; padding:0.75rem; border:1px solid var(--admin-border); border-radius:0.5rem;">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Start Date <small style="color:#888;">(optional)</small></label>
                            <input type="date" name="deal_start_date" style="width:100%; padding:0.75rem; border:1px solid var(--admin-border); border-radius:0.5rem;">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">End Date <small style="color:#888;">(optional)</small></label>
                            <input type="date" name="deal_end_date" style="width:100%; padding:0.75rem; border:1px solid var(--admin-border); border-radius:0.5rem;">
                        </div>
                        <button type="submit" name="save_deal" style="background:var(--admin-primary); color:#fff; border:none; padding:1rem; border-radius:0.5rem; font-weight:700; font-size:1rem; cursor:pointer;">Save Deal</button>
                    </form>
                </div>
            </div>

            <!-- Deals Table -->
            <div class="content-card" style="flex: 1; min-width: 400px;">
                <div class="card-header">
                    <h3 class="card-title">All Deals</h3>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Original</th>
                            <th>Discount</th>
                            <th>Sale Price</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <?php if (in_array($_SESSION['user_role'], ['admin', 'superadmin'])): ?>
                            <th>Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($deals)): ?>
                            <tr><td colspan="8" style="text-align:center; color:var(--admin-text-muted); padding:2rem;">No deals yet. Add one!</td></tr>
                        <?php else: ?>
                            <?php foreach ($deals as $d):
                                $start = $d['deal_start_date'];
                                $end   = $d['deal_end_date'];
                                $is_active = (!$start && !$end) ||
                                             (!$start && $today <= $end) ||
                                             (!$end   && $today >= $start) ||
                                             ($today >= $start && $today <= $end);
                                $status_color = $is_active ? '#10b981' : ($end && $today > $end ? '#ef4444' : '#f59e0b');
                                $status_label = $is_active ? 'Active' : ($end && $today > $end ? 'Expired' : 'Upcoming');
                            ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($d['name']); ?></div>
                                    <div style="font-size:0.75rem; color:var(--admin-text-muted);"><?php echo htmlspecialchars($d['brand']); ?></div>
                                </td>
                                <td>£<?php echo number_format($d['price'], 2); ?></td>
                                <td><span style="color:#f59e0b; font-weight:700;"><?php echo $d['discount_percent']; ?>%</span></td>
                                <td><span style="color:#10b981; font-weight:700;">£<?php echo number_format($d['sale_price'], 2); ?></span></td>
                                <td style="font-size:0.8rem; color:var(--admin-text-muted);"><?php echo $start ?: '—'; ?></td>
                                <td style="font-size:0.8rem; color:var(--admin-text-muted);"><?php echo $end   ?: '—'; ?></td>
                                <td>
                                    <span style="background:<?php echo $status_color; ?>22; color:<?php echo $status_color; ?>; border:1px solid <?php echo $status_color; ?>44; padding:0.25rem 0.6rem; border-radius:999px; font-size:0.7rem; font-weight:700; text-transform:uppercase;">
                                        <?php echo $status_label; ?>
                                    </span>
                                </td>
                                <?php if (in_array($_SESSION['user_role'], ['admin', 'superadmin'])): ?>
                                <td>
                                    <a href="deals.php?remove_deal=<?php echo $d['id']; ?>" onclick="return confirm('Remove this deal?')" style="color:#ef4444; text-decoration:none; font-weight:600; font-size:0.875rem;">Remove</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
