<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff') {
    header("Location: index.php");
    exit();
}
require_once 'includes/admin-header.php';
?>

        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">System Settings</h3>
            </div>
            <div style="padding: 2rem; color: var(--admin-text-muted);">
                <p>⚙️ General system configurations and preferences will appear here.</p>
                <div style="margin-top: 1.5rem; display: grid; gap: 1rem; max-width: 400px;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; color: var(--admin-text);">Shop Name</label>
                        <input type="text" value="TuneTrove" readonly style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem; background: #f1f5f9;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; color: var(--admin-text);">Admin Email</label>
                        <input type="text" value="admin@tunetrove.com" readonly style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem; background: #f1f5f9;">
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
