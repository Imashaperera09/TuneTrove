<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

if (!is_logged_in()) {
    redirect('/TuneTrove/user/auth/login.php', 'Please login to access your account.', 'error');
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

<div style="background: var(--background); min-height: 100vh; padding-top: 2rem; padding-bottom: 8rem;">
    <div class="container">
        <!-- Header -->
        <div style="margin-bottom: 2rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 1.5rem;">
            <p style="text-transform: uppercase; font-size: 0.75rem; font-weight: 800; color: var(--accent); letter-spacing: 0.25em; margin-bottom: 0.5rem;">Customer Presence</p>
            <h1 style="font-family: var(--font-heading); font-size: 3.25rem; letter-spacing: -0.04em; color: #fff; margin: 0;">My <span style="color: var(--primary);">Account</span></h1>
            <p style="color: #64748b; font-size: 1.05rem; margin-top: 0.5rem;">Manage your order history and historical acquisitions.</p>
        </div>

        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 3rem; align-items: flex-start;">
            <!-- Sidebar -->
            <aside style="position: sticky; top: 120px;">
                <div style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem; padding: 3rem; text-align: center; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; margin: 0 auto 1.5rem; box-shadow: 0 15px 30px rgba(14, 165, 233, 0.3); border: 4px solid rgba(255, 255, 255, 0.05);">
                        <?php echo strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1)); ?>
                    </div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.35rem; font-weight: 800; color: #fff; margin-bottom: 0.25rem; letter-spacing: -0.01em;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p style="color: var(--accent); font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 2.5rem; opacity: 0.8;">Verified Member</p>
                    
                    <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="#orders" style="padding: 1rem 1.25rem; text-decoration: none; color: #fff; font-weight: 700; font-size: 0.95rem; background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 0.5rem; transition: all 0.2s;">Order History</a>
                        <a href="/TuneTrove/user/auth/logout.php" style="padding: 1rem 1.25rem; text-decoration: none; color: #ef4444; font-weight: 700; font-size: 0.95rem; border-radius: 0.5rem; margin-top: 1rem; border: 1px solid rgba(239, 68, 68, 0.1); transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.05)'" onmouseout="this.style.background='transparent'">Logout</a>
                    </nav>
                </div>
            </aside>

            <!-- Main Content -->
            <div style="display: flex; flex-direction: column; gap: 4rem;">
                
                <!-- Orders -->
                <section id="orders">
                    <h2 style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; color: #fff; margin-bottom: 2.5rem; letter-spacing: -0.03em;">Order History</h2>
                    
                    <?php if (empty($orders)): ?>
                        <div style="text-align: center; padding: 6rem; background: rgba(255, 255, 255, 0.01); border: 2px dashed rgba(255, 255, 255, 0.05); border-radius: 1rem;">
                            <p style="color: #64748b; margin-bottom: 2.5rem; font-size: 1.1rem;">You haven't placed any orders yet.</p>
                            <a href="/TuneTrove/user/shop/" class="btn btn-primary" style="padding: 1.25rem 3rem; font-weight: 800; text-transform: uppercase; font-size: 0.9rem; border-radius: 0.5rem; letter-spacing: 0.1em;">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($orders as $order): ?>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; align-items: center; gap: 3rem; padding: 2.5rem; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 1rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(14, 165, 233, 0.4)'; this.style.background='rgba(14, 165, 233, 0.02)'" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.05)'; this.style.background='rgba(255, 255, 255, 0.02)'">
                                        <div>
                                            <p style="font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Identifier</p>
                                            <p style="font-weight: 800; color: #fff; font-size: 1.1rem; letter-spacing: -0.01em;">TT-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                        </div>
                                        <div>
                                            <p style="font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Date</p>
                                            <p style="color: #94a3b8; font-weight: 600;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                        </div>
                                        <div>
                                            <p style="font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Total</p>
                                            <p style="font-weight: 800; color: var(--primary); font-size: 1.1rem;">$<?php echo number_format($order['total_amount'], 2); ?></p>
                                        </div>
                                        <div>
                                            <p style="font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Status</p>
                                            <span style="display: inline-block; padding: 0.4rem 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 4px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #fff; border: 1px solid rgba(255, 255, 255, 0.1); letter-spacing: 0.1em;">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </div>
                                        <div style="color: var(--primary); font-size: 1.5rem; font-weight: 800;">→</div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
