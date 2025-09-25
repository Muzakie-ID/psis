<?php
// templates/footer.php

$jsPath = file_exists(__DIR__ . '/../static/js/script.js') ? 'static/js/script.js' : '../static/js/script.js';
?>
    <div class="logout-modal" id="logout-modal">
        <div class="logout-modal-content">
             <div class="logout-modal-header"><i class="fas fa-sign-out-alt"></i><h2>Konfirmasi Keluar</h2></div>
             <p>Apakah Anda yakin ingin keluar, <span id="logout-user-name"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></span>?</p>
             <div class="logout-modal-buttons">
                <button class="btn btn-secondary" id="logout-cancel-btn">Batal</button>
                <a href="logout.php" class="btn btn-danger">Keluar</a>
             </div>
        </div>
    </div>

    <script src="<?= htmlspecialchars($jsPath) ?>"></script>
</body>
</html>
