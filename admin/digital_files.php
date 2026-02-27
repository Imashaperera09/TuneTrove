<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is staff or admin
if (!is_logged_in() || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'staff')) {
    redirect('/TuneTrove/user/auth/login.php', 'Unauthorized access.', 'error');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$upload_dir = '../../user/assets/downloads/';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_file'])) {
        $product_id = (int)$_POST['product_id'];
        $download_limit = !empty($_POST['download_limit']) ? (int)$_POST['download_limit'] : null;

        if (isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['digital_file']['tmp_name'];
            $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "", basename($_FILES['digital_file']['name']));
            $file_path = $upload_dir . $file_name;
            $db_path = 'user/assets/downloads/' . $file_name;

            // Only allow PDF or specific extensions for security if needed
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if ($file_ext !== 'pdf' && $file_ext !== 'zip') {
                redirect('digital_files.php', 'Only PDF and ZIP files are allowed.', 'error');
            }

            if (move_uploaded_file($file_tmp, $file_path)) {
                // Remove existing if any
                $stmt = $pdo->prepare("SELECT file_path FROM digital_products WHERE product_id = ?");
                $stmt->execute([$product_id]);
                if ($existing = $stmt->fetch()) {
                    if (file_exists('../../' . $existing['file_path'])) {
                        unlink('../../' . $existing['file_path']);
                    }
                    $del = $pdo->prepare("DELETE FROM digital_products WHERE product_id = ?");
                    $del->execute([$product_id]);
                }

                // Insert new
                $stmt = $pdo->prepare("INSERT INTO digital_products (product_id, file_path, download_limit) VALUES (?, ?, ?)");
                $stmt->execute([$product_id, $db_path, $download_limit]);
                
                redirect('digital_files.php', 'Digital file uploaded successfully.', 'success');
            } else {
                redirect('digital_files.php', 'Failed to move uploaded file.', 'error');
            }
        } else {
            redirect('digital_files.php', 'No valid file uploaded.', 'error');
        }
    } elseif (isset($_POST['delete_file'])) {
        $product_id = (int)$_POST['product_id'];
        
        $stmt = $pdo->prepare("SELECT file_path FROM digital_products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        if ($existing = $stmt->fetch()) {
            if (file_exists('../../' . $existing['file_path'])) {
                unlink('../../' . $existing['file_path']);
            }
            $del = $pdo->prepare("DELETE FROM digital_products WHERE product_id = ?");
            $del->execute([$product_id]);
            redirect('digital_files.php', 'Digital file deleted.', 'success');
        }
        redirect('digital_files.php', 'File record not found.', 'error');
    }
}

// Fetch all digital products
$stmt = $pdo->query("
    SELECT p.id, p.name, p.brand, dp.file_path, dp.download_limit, dp.updated_at
    FROM products p
    LEFT JOIN digital_products dp ON p.id = dp.product_id
    WHERE p.is_digital = 1
    ORDER BY p.name ASC
");
$digital_items = $stmt->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="font-family: var(--font-heading); font-size: 2.5rem; color: #fff; margin: 0; letter-spacing: -0.02em;">Digital <span style="color: var(--primary);">Assets</span></h2>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="background: rgba(74, 222, 128, 0.1); border: 1px solid rgba(74, 222, 128, 0.2); color: #4ade80; padding: 1rem 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; font-weight: 700;">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 1rem 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; font-weight: 700;">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 1rem; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: rgba(0, 0, 0, 0.4); border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                    <th style="padding: 1.5rem; color: var(--accent); font-weight: 800; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.1em;">Product</th>
                    <th style="padding: 1.5rem; color: var(--accent); font-weight: 800; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.1em;">File Status</th>
                    <th style="padding: 1.5rem; color: var(--accent); font-weight: 800; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.1em;">Download Limit</th>
                    <th style="padding: 1.5rem; color: var(--accent); font-weight: 800; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.1em; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($digital_items as $item): ?>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.02); transition: background 0.2s;" onmouseover="this.style.background='rgba(255, 255, 255, 0.01)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1.5rem;">
                            <p style="font-weight: 800; color: #fff; margin: 0;"><?php echo htmlspecialchars($item['name']); ?></p>
                            <span style="font-size: 0.8rem; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em;"><?php echo htmlspecialchars($item['brand']); ?></span>
                        </td>
                        <td style="padding: 1.5rem;">
                            <?php if ($item['file_path']): ?>
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.8rem; background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.2); border-radius: 999px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">✅ File Uploaded</span>
                                <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem; word-break: break-all;"><?php echo basename($item['file_path']); ?></p>
                            <?php else: ?>
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.8rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 999px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">❌ Missing File</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1.5rem; color: #94a3b8; font-size: 0.9rem;">
                            <?php echo $item['download_limit'] ? $item['download_limit'] . ' limit' : 'Unlimited'; ?>
                        </td>
                        <td style="padding: 1.5rem; text-align: right;">
                            <button onclick="document.getElementById('upload-modal-<?php echo $item['id']; ?>').style.display='flex'" style="background: rgba(14, 165, 233, 0.1); color: var(--primary); border: 1px solid rgba(14, 165, 233, 0.2); padding: 0.5rem 1rem; border-radius: 0.4rem; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer; transition: all 0.2s; margin-right: 0.5rem;" onmouseover="this.style.background='var(--primary)'; this.style.color='#fff'" onmouseout="this.style.background='rgba(14, 165, 233, 0.1)'; this.style.color='var(--primary)'">
                                <?php echo $item['file_path'] ? 'Replace File' : 'Upload File'; ?>
                            </button>
                            
                            <?php if ($item['file_path']): ?>
                                <form action="digital_files.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this file? Users will no longer be able to download it.');">
                                    <input type="hidden" name="delete_file" value="1">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 0.5rem 1rem; border-radius: 0.4rem; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#ef4444'; this.style.color='#fff'" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='#ef4444'">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Upload Form Modal -->
                    <div id="upload-modal-<?php echo $item['id']; ?>" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; align-items: center; justify-content: center;">
                        <div style="background: var(--surface); padding: 3rem; border-radius: 1.5rem; width: 100%; max-width: 500px; border: 1px solid rgba(255, 255, 255, 0.05); position: relative;">
                            <button onclick="this.parentElement.parentElement.style.display='none'" style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">&times;</button>
                            
                            <h3 style="font-family: var(--font-heading); font-size: 1.75rem; color: #fff; margin-bottom: 2rem; letter-spacing: -0.02em;">Upload <span style="color: var(--primary);">Asset</span></h3>
                            <p style="color: #94a3b8; font-size: 1rem; margin-bottom: 2rem;">Attach a PDF or ZIP file for <strong><?php echo htmlspecialchars($item['name']); ?></strong>.</p>
                            
                            <form action="digital_files.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="upload_file" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                
                                <div style="margin-bottom: 1.5rem;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--accent); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Digital File (.pdf, .zip)</label>
                                    <input type="file" name="digital_file" required accept=".pdf,.zip" style="width: 100%; padding: 1rem; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 0.5rem; color: #fff;">
                                </div>

                                <div style="margin-bottom: 2.5rem;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--accent); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Download Limit (Optional)</label>
                                    <input type="number" name="download_limit" min="1" placeholder="Leave empty for unlimited" value="<?php echo $item['download_limit']; ?>" style="width: 100%; padding: 1rem; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 0.5rem; color: #fff;">
                                </div>

                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 0.5rem;">Upload File</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($digital_items)): ?>
            <div style="padding: 4rem; text-align: center;">
                <p style="color: #94a3b8; font-size: 1.1rem;">No digital products found in the catalog.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
