<?php
require_once 'includes/admin-header.php';

// Handle Delete Request
if (isset($_POST['delete_review']) && isset($_POST['review_id'])) {
    // Both admin and staff can access this page, but if you want only admins to delete, add a role check
    /*
    if ($_SESSION['user_role'] === 'staff') {
        die("Unauthorized to delete reviews.");
    }
    */
    
    $review_id = (int)$_POST['review_id'];
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$review_id]);
    
    // Refresh to show message
    header("Location: reviews.php?msg=deleted");
    exit();
}

// Fetch Reviews with User and Product Info
$stmt = $pdo->query("SELECT r.*, u.username, u.email, p.name AS product_name 
                      FROM reviews r 
                      JOIN users u ON r.user_id = u.id 
                      JOIN products p ON r.product_id = p.id 
                      ORDER BY r.created_at DESC");
$reviews = $stmt->fetchAll();
?>

        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Customer Reviews</h3>
            </div>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div style="background: rgba(74, 222, 128, 0.1); border: 1px solid rgba(74, 222, 128, 0.2); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: #4ade80; font-size: 0.875rem;">
                    Review deleted successfully.
                </div>
            <?php endif; ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 150px;">Date</th>
                        <th style="width: 150px;">Customer</th>
                        <th style="width: 250px;">Product</th>
                        <th style="width: 100px;">Rating</th>
                        <th>Comment</th>
                        <th style="width: 80px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colSpan="7" style="text-align: center; color: var(--admin-text-muted); padding: 2rem;">No reviews found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $r): ?>
                            <tr>
                                <td><?php echo $r['id']; ?></td>
                                <td style="color: var(--admin-text-muted); font-size: 0.875rem;"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($r['username']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($r['email']); ?></div>
                                </td>
                                <td>
                                    <a href="/TuneTrove/user/shop/product.php?id=<?php echo $r['product_id']; ?>" target="_blank" style="color: var(--admin-primary); text-decoration: none; font-weight: 500;">
                                        <?php echo htmlspecialchars($r['product_name']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div style="color: #fbbf24; font-size: 0.875rem;">
                                        <?php echo str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-height: 3rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; font-size: 0.875rem; line-height: 1.5; color: var(--admin-text);">
                                        <?php echo htmlspecialchars($r['comment']); ?>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <form method="POST" action="reviews.php" onsubmit="return confirm('Are you sure you want to delete this review?');" style="display: inline;">
                                        <input type="hidden" name="review_id" value="<?php echo $r['id']; ?>">
                                        <button type="submit" name="delete_review" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0.5rem; border-radius: 0.25rem;" title="Delete Review" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'" onmouseout="this.style.background='none'">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
