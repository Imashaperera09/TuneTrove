<?php
require_once 'includes/admin-header.php';

// Handle Delete
if (isset($_GET['remove_deal']) && in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    $pid = (int)$_GET['remove_deal'];
    $pdo->prepare("UPDATE products SET discount_percent=NULL, deal_start_date=NULL, deal_end_date=NULL, sale_price=NULL, is_deal=0 WHERE id=?")->execute([$pid]);
    header("Location: deals.php?msg=Promotion+deleted+successfully"); exit();
}

// Handle Add/Update Deal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_deal'])) {
    $pid       = (int)$_POST['product_id'];
    $discount  = (float)$_POST['discount_percent'];
    $start     = $_POST['deal_start_date'] ?: null;
    $end       = $_POST['deal_end_date']   ?: null;
    $is_active = (int)$_POST['is_deal'];
    $sale      = null;

    // Fetch product price to compute sale_price
    $p = $pdo->prepare("SELECT price FROM products WHERE id=?");
    $p->execute([$pid]);
    $row = $p->fetch();
    if ($row && $discount > 0) {
        $sale = round((float)$row['price'] * (1 - $discount / 100), 2);
    }

    $pdo->prepare("UPDATE products SET discount_percent=?, deal_start_date=?, deal_end_date=?, sale_price=?, is_deal=? WHERE id=?")
        ->execute([$discount, $start, $end, $sale, $is_active, $pid]);
    header("Location: deals.php?msg=Promotion+updated"); exit();
}

// Fetch deal for editing if ID provided
$edit_deal = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $edit_deal = $stmt->fetch();
}

// Fetch all products with deals + all products for the form
$deals = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.discount_percent > 0 OR p.is_deal = 1 ORDER BY p.id DESC")->fetchAll();
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
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="card-title"><?php echo $edit_deal ? 'Edit Promotion' : 'Add Promotion'; ?></h3>
                    <?php if ($edit_deal): ?>
                        <a href="deals.php" style="font-size: 0.75rem; color: #888; text-decoration: none;">Cancel</a>
                    <?php endif; ?>
                </div>
                <div style="padding: 1.5rem;">
                    <form method="POST" action="deals.php" style="display: grid; gap: 1rem;">
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Product</label>
                            <select name="product_id" required style="width:100%; padding:0.75rem; border:1px solid var(--admin-border); border-radius:0.5rem; font-size:0.9rem; background: #fff;">
                                <option value="">-- Select Product --</option>
                                <?php foreach ($all_products as $prod): 
                                    $selected = ($edit_deal && $edit_deal['id'] == $prod['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $prod['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($prod['name']); ?> — £<?php echo number_format($prod['price'],2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Discount %</label>
                            <input type="number" name="discount_percent" min="1" max="99" step="0.5" required placeholder="e.g. 20" value="<?php echo $edit_deal ? $edit_deal['discount_percent'] : ''; ?>" style="width:100%; padding:0.75rem; border:1px solid var(--admin-border); border-radius:0.5rem;">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Promotion Status</label>
                            <select name="is_deal" required style="width:100%; padding:0.75rem; border:1px solid var(--admin-border); border-radius:0.5rem; font-size:0.9rem; background: #fff;">
                                <option value="1" <?php echo ($edit_deal && $edit_deal['is_deal'] == 1) || !$edit_deal ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($edit_deal && $edit_deal['is_deal'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Start Date <small style="color:#888;">(optional)</small></label>
                            <input type="date" name="deal_start_date" value="<?php echo $edit_deal ? $edit_deal['deal_start_date'] : ''; ?>" style="width:100%; padding:0.75rem; border:1px solid var(--admin-border); border-radius:0.5rem;">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">End Date <small style="color:#888;">(optional)</small></label>
                            <input type="date" name="deal_end_date" value="<?php echo $edit_deal ? $edit_deal['deal_end_date'] : ''; ?>" style="width:100%; padding:0.75rem; border:1px solid var(--admin-border); border-radius:0.5rem;">
                        </div>
                        <button type="submit" name="save_deal" style="background:var(--admin-primary); color:#fff; border:none; padding:1rem; border-radius:0.5rem; font-weight:700; font-size:1rem; cursor:pointer;">
                            <?php echo $edit_deal ? 'Update Promotion' : 'Save Promotion'; ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Deals Table -->
            <div class="content-card" style="flex: 1; min-width: 400px;">
                <div class="card-header">
                    <h3 class="card-title">All Promotions</h3>
                </div>
                <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Original</th>
                            <th>Discount</th>
                            <th>Sale Price</th>
                            <th>Range</th>
                            <th>Status</th>
                            <?php if (in_array($_SESSION['user_role'], ['admin', 'superadmin'])): ?>
                            <th style="text-align: right;">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($deals)): ?>
                            <tr><td colspan="8" style="text-align:center; color:var(--admin-text-muted); padding:2rem;">No promotions discovered yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($deals as $d):
                                $start = $d['deal_start_date'];
                                $end   = $d['deal_end_date'];
                                
                                // Logic for activity status
                                $is_active_range = (!$start && !$end) ||
                                             (!$start && $today <= $end) ||
                                             (!$end   && $today >= $start) ||
                                             ($today >= $start && $today <= $end);
                                
                                $is_manually_enabled = (bool)$d['is_deal'];
                                
                                if (!$is_manually_enabled) {
                                    $status_color = '#64748b';
                                    $status_label = 'Disabled';
                                } elseif ($end && $today > $end) {
                                    $status_color = '#ef4444';
                                    $status_label = 'Expired';
                                } elseif ($start && $today < $start) {
                                    $status_color = '#f59e0b';
                                    $status_label = 'Upcoming';
                                } else {
                                    $status_color = '#10b981';
                                    $status_label = 'Live';
                                }
                            ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600; color: #fff;"><?php echo htmlspecialchars($d['name']); ?></div>
                                    <div style="font-size:0.75rem; color:var(--admin-text-muted);"><?php echo htmlspecialchars($d['brand']); ?></div>
                                </td>
                                <td style="color: #94a3b8;">£<?php echo number_format($d['price'], 2); ?></td>
                                <td><span style="color:#f59e0b; font-weight:700;"><?php echo (float)$d['discount_percent']; ?>%</span></td>
                                <td><span style="color:#10b981; font-weight:700;">£<?php echo number_format($d['sale_price'], 2); ?></span></td>
                                <td style="font-size:0.75rem; color:var(--admin-text-muted);">
                                    <?php if ($start || $end): ?>
                                        <?php echo $start ? date('M j', strtotime($start)) : 'Anytime'; ?> — <?php echo $end ? date('M j', strtotime($end)) : 'Anytime'; ?>
                                    <?php else: ?>
                                        Indefinite
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="background:<?php echo $status_color; ?>15; color:<?php echo $status_color; ?>; border:1px solid <?php echo $status_color; ?>33; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing: 0.05em;">
                                        <?php echo $status_label; ?>
                                    </span>
                                </td>
                                <?php if (in_array($_SESSION['user_role'], ['admin', 'superadmin'])): ?>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                                        <a href="deals.php?edit_id=<?php echo $d['id']; ?>" style="color:var(--admin-primary); text-decoration:none; font-weight:700; font-size:0.75rem; text-transform: uppercase;">Edit</a>
                                        <a href="deals.php?remove_deal=<?php echo $d['id']; ?>" onclick="return confirm('Permanently remove this promotion data?')" style="color:#ef4444; text-decoration:none; font-weight:700; font-size:0.75rem; text-transform: uppercase;">Delete</a>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
