<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

if (!is_logged_in()) {
    redirect('/TuneTrove/auth/login.php', 'Please login to access your account.', 'error');
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get order history
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<div class="account-patron-portal" style="min-height: 100vh; background: radial-gradient(circle at 0% 0%, rgba(37, 99, 235, 0.05), transparent), radial-gradient(circle at 100% 100%, rgba(168, 85, 247, 0.05), transparent); padding-top: 8rem; padding-bottom: 8rem;">
    <div class="container">
        <header style="margin-bottom: 5rem;">
            <h1 style="font-family: var(--font-heading); font-size: 3.5rem; letter-spacing: -0.05em; margin-bottom: 0.5rem;">The <span style="color: var(--primary);">Patron</span> Portal</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Manage your masterwork collection and identity.</p>
        </header>

        <div style="display: grid; grid-template-columns: 320px 1fr; gap: 4rem; align-items: flex-start;">
            <!-- Patron Sidebar -->
            <aside style="position: sticky; top: 120px;">
                <div class="glass-panel" style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 3rem; padding: 3rem; text-align: center; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary), #1e40af); color: #fff; border-radius: 2.5rem; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.75rem; color: #fff; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 3rem;">AUTHENTIC PATRON</p>
                    
                    <nav style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="#orders" class="patron-nav-link active">HISTORY OF ACQUISITIONS</a>
                        <a href="#profile" class="patron-nav-link">IDENTITY SETTINGS</a>
                        <a href="/TuneTrove/auth/logout.php" class="patron-nav-link" style="color: var(--error); border-color: rgba(239, 68, 68, 0.1);">EXECUTE LOGOUT</a>
                    </nav>
                </div>
            </aside>

            <!-- Patron Intelligence Content -->
            <div style="display: flex; flex-direction: column; gap: 4rem;">
                <!-- Acquisition History -->
                <div id="orders" class="glass-panel" style="background: rgba(30, 41, 59, 0.2); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 3rem; padding: 4rem;">
                    <h2 style="font-family: var(--font-heading); font-size: 2rem; color: #fff; margin-bottom: 3rem; display: flex; align-items: center; gap: 1rem;">
                        <span style="font-size: 1.5rem; color: var(--primary);">📋</span> Acquisition History
                    </h2>
                    
                    <?php if (empty($orders)): ?>
                        <div style="text-align: center; padding: 6rem; background: rgba(15, 23, 42, 0.3); border-radius: 2rem; border: 1px dashed rgba(255, 255, 255, 0.05);">
                            <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 2.5rem;">No acquisitions detected in your narrative.</p>
                            <a href="/TuneTrove/shop/" class="btn btn-primary" style="padding: 1rem 2.5rem; border-radius: 1.25rem; font-weight: 800;">ENTER SHOWROOM</a>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($orders as $order): ?>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="order-row-link">
                                    <div style="display: grid; grid-template-columns: 120px 1fr 140px 140px 40px; align-items: center; gap: 2rem; padding: 2rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 1.5rem; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
                                        <div>
                                            <div style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Reference</div>
                                            <div style="font-weight: 800; color: #fff; font-size: 0.9rem;">MM-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                        </div>
                                        <div>
                                            <div style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Timeline</div>
                                            <div style="color: rgba(255,255,255,0.7); font-size: 0.9rem; font-weight: 600;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                        </div>
                                        <div>
                                            <div style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Investment</div>
                                            <div style="font-weight: 800; color: var(--primary); font-size: 1.1rem;"><?php echo format_price($order['total_amount']); ?></div>
                                        </div>
                                        <div>
                                            <div style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Status</div>
                                            <span class="status-badge-compact <?php echo strtolower($order['status']); ?>">
                                                <?php echo strtoupper($order['status']); ?>
                                            </span>
                                        </div>
                                        <div style="text-align: right; opacity: 0.3;">→</div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Identity Settings -->
                <div id="profile" class="glass-panel" style="background: rgba(30, 41, 59, 0.2); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 3rem; padding: 4rem;">
                    <h2 style="font-family: var(--font-heading); font-size: 2rem; color: #fff; margin-bottom: 3rem; display: flex; align-items: center; gap: 1rem;">
                        <span style="font-size: 1.5rem; color: var(--primary);">🛡️</span> Identity Settings
                    </h2>
                    <form>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 2.5rem;">
                            <div>
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.75rem;">Full Governance Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" disabled style="width: 100%; padding: 1.25rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.25rem; color: rgba(255,255,255,0.5); font-family: inherit; font-weight: 600; cursor: not-allowed;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.75rem;">Patron Username</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="width: 100%; padding: 1.25rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.25rem; color: rgba(255,255,255,0.5); font-family: inherit; font-weight: 600; cursor: not-allowed;">
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.75rem;">Digital Correspondence</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="width: 100%; padding: 1.25rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.25rem; color: rgba(255,255,255,0.5); font-family: inherit; font-weight: 600; cursor: not-allowed;">
                        </div>
                        <div style="margin-top: 3rem; padding: 1.5rem; background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 1.25rem;">
                            <p style="font-size: 0.75rem; color: var(--primary); font-weight: 800; text-transform: uppercase; text-align: center;">Identity reconfiguration protocol is currently locked.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.patron-nav-link {
    display: block;
    padding: 1rem;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    border: 1px solid transparent;
    border-radius: 1rem;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.patron-nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.03);
    transform: translateX(5px);
}
.patron-nav-link.active {
    background: rgba(37, 99, 235, 0.1);
    border-color: rgba(37, 99, 235, 0.3);
    color: var(--primary);
}

.order-row-link {
    text-decoration: none;
    color: inherit;
    display: block;
}
.order-row-link:hover div[style*="background: rgba(15, 23, 42, 0.4)"] {
    background: rgba(15, 23, 42, 0.6) !important;
    border-color: var(--primary) !important;
    transform: translateY(-4px);
    box-shadow: 0 15px 30px -10px rgba(0,0,0,0.5);
}

.status-badge-compact {
    padding: 0.4rem 0.75rem;
    border-radius: 0.6rem;
    font-size: 0.6rem;
    font-weight: 900;
    letter-spacing: 0.05em;
}
.status-badge-compact.paid { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); }
.status-badge-compact.shipped { background: rgba(37, 99, 235, 0.1); color: var(--primary); border: 1px solid rgba(37, 99, 235, 0.3); }
.status-badge-compact.pending { background: rgba(245, 158, 11, 0.1); color: var(--accent); border: 1px solid rgba(245, 158, 11, 0.3); }

@media (max-width: 1024px) {
    div[style*="grid-template-columns: 320px 1fr"] {
        grid-template-columns: 1fr !important;
    }
    aside {
        position: static !important;
        margin-bottom: 4rem;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
