document.addEventListener('DOMContentLoaded', function() {
    // --- SCRIPT UNTUK NAVIGASI TAB/PAGE DI DASHBOARD ---
    const pageTitle = document.getElementById('page-title');

    function showPageFromHash() {
        if (!document.getElementById('dashboard-page')) return; // Hanya berjalan jika di dashboard

        const hash = window.location.hash.substring(1);
        const targetPage = hash || 'dashboard';
        const pageElement = document.getElementById(targetPage + '-page');

        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.menu-item[data-page]').forEach(m => m.classList.remove('active'));

        if (pageElement) {
            pageElement.classList.add('active');
            const menuItem = document.querySelector(`.menu-item[data-page="${targetPage}"]`);
            if (menuItem) {
                menuItem.classList.add('active');
                const menuText = menuItem.querySelector('.menu-text');
                if (pageTitle && menuText) {
                    pageTitle.textContent = menuText.textContent;
                }
            }
        } else {
            // Fallback jika hash tidak valid
            const dashboardPage = document.getElementById('dashboard-page');
            const dashboardMenuItem = document.querySelector('.menu-item[data-page="dashboard"]');
            if (dashboardPage) dashboardPage.classList.add('active');
            if (dashboardMenuItem) dashboardMenuItem.classList.add('active');
            if (pageTitle) pageTitle.textContent = 'Dashboard';
        }
    }

    // Jalankan fungsi navigasi hash jika elemen yang relevan ada
    if (pageTitle) {
        showPageFromHash();
        window.addEventListener('hashchange', showPageFromHash); // Tambahkan listener untuk perubahan hash

        document.querySelectorAll('.menu-item[data-page]').forEach(item => {
            item.addEventListener('click', (event) => {
                event.preventDefault();
                const pageId = item.getAttribute('data-page');
                window.location.hash = pageId;
            });
        });
    }


    // --- SCRIPT UNTUK MEMUAT PENGADUAN TERBARU DI DASHBOARD ---
    const complaintList = document.getElementById('complaint-list');
    if (complaintList) {
        fetch('get_complaints.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            complaintList.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
                complaintList.innerHTML = '<p style="color:#6c757d; text-align:center;">Belum ada pengaduan yang Anda ajukan.</p>';
                return;
            }
            data.forEach(c => {
                const a = document.createElement('a');
                a.href = 'view_complaint.php?id=' + c.id;
                a.className = 'complaint-item-link';
                a.innerHTML = `
                    <div class="complaint-item">
                        <div class="complaint-info">
                            <h3>${c.title || 'Tanpa Judul'}</h3>
                            <p>Diajukan pada: ${c.created_at || ''}</p>
                        </div>
                        <div class="status status-${c.status || ''}">${c.status ? c.status.charAt(0).toUpperCase() + c.status.slice(1) : ''}</div>
                    </div>`;
                complaintList.appendChild(a);
            });
        })
        .catch(error => {
            console.error('Error fetching complaints:', error);
            complaintList.innerHTML = '<p style="color:#dc3545; text-align:center;">Gagal memuat data pengaduan.</p>';
        });
    }

    
    // --- FUNGSI MODAL LOGOUT (INI YANG PENTING) ---
    const logoutBtn = document.getElementById('logout-btn');
    const logoutModal = document.getElementById('logout-modal');
    const logoutCancelBtn = document.getElementById('logout-cancel-btn');
    const logoutUserName = document.getElementById('logout-user-name');

    // Pastikan semua elemen modal ada sebelum menambahkan event listener
    if (logoutBtn && logoutModal && logoutCancelBtn) {
        // Tampilkan modal ketika tombol logout diklik
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Mencegah aksi default jika ada
            
            // Mengambil nama user dari header
            const userNameEl = document.querySelector('.user-name');
            if (userNameEl && logoutUserName) {
                logoutUserName.textContent = userNameEl.textContent;
            }
            logoutModal.style.display = 'flex';
        });

        // Sembunyikan modal ketika tombol batal diklik
        logoutCancelBtn.addEventListener('click', () => {
            logoutModal.style.display = 'none';
        });

        // Sembunyikan modal jika mengklik di luar konten modal
        window.addEventListener('click', (event) => {
            if (event.target === logoutModal) {
                logoutModal.style.display = 'none';
            }
        });
    }
});
