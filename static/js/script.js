document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.menu-item');
            const pages = {
                'dashboard': document.getElementById('dashboard-page'),
                'complaint': document.getElementById('complaint-page'),
                'physical-psychical': document.getElementById('physical-psychical-page'),
                'history': document.getElementById('history-page'),
                'profile': document.getElementById('profile-page'),
                'settings': document.getElementById('settings-page')
            };
            const pageTitle = document.getElementById('page-title');
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');
            
            // Data untuk jenis masalah berdasarkan tipe
            const issueTypes = {
                physical: [
                    "Cedera Olahraga",
                    "Kecelakaan di Sekolah",
                    "Penyakit Mendadak",
                    "Kekerasan Fisik",
                    "Lainnya"
                ],
                psychical: [
                    "Stres Akademik",
                    "Kecemasan",
                    "Depresi",
                    "Bullying/Perundungan",
                    "Masalah Keluarga",
                    "Lainnya"
                ]
            };
            
                
                // Tampilkan halaman yang dipilih
                if (pages[pageId]) {
                    pages[pageId].classList.add('active');
                    const activeMenuItem = document.querySelector(`.menu-item[data-page="${pageId}"] .menu-text`);
                    if (activeMenuItem) {
                        pageTitle.textContent = activeMenuItem.textContent;
                    }
                }
                
                // Perbarui menu aktif
                menuItems.forEach(item => {
                    item.classList.remove('active');
                    if (item.getAttribute('data-page') === pageId) {
                        item.classList.add('active');
                    }
                });
            }
            
            // Event listener untuk menu item
            menuItems.forEach(item => {
                const pageId = item.getAttribute('data-page');
                if (pageId) {
                    item.addEventListener('click', () => {
                        showPage(pageId);
                    });
                }
            });
            
            // Event listener untuk toggle menu di mobile
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
            
            // Fungsi untuk halaman pengaduan fisik/psikis
            const typeButtons = document.querySelectorAll('.type-btn');
            const issueTypeSelect = document.getElementById('issue-type');
            let selectedType = '';
            let selectedTeacherId = null;
            
            // Event listener untuk memilih tipe pengaduan
            typeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Hapus seleksi sebelumnya
                    typeButtons.forEach(btn => btn.classList.remove('selected'));
                    
                    // Tandai yang dipilih
                    button.classList.add('selected');
                    
                    // Simpan tipe yang dipilih
                    selectedType = button.getAttribute('data-type');
                    
                    // Isi opsi jenis masalah berdasarkan tipe
                    issueTypeSelect.innerHTML = '<option value="">Pilih jenis masalah</option>';
                    
                    if (selectedType && issueTypes[selectedType]) {
                        issueTypes[selectedType].forEach(issue => {
                            const option = document.createElement('option');
                            option.value = issue.toLowerCase().replace(/\s+/g, '-');
                            option.textContent = issue;
                            issueTypeSelect.appendChild(option);
                        });
                    }
                });
            });
            
            // Event listener untuk memilih guru
            const teacherCards = document.querySelectorAll('.teacher-card');
            teacherCards.forEach(card => {
                card.addEventListener('click', () => {
                    // Hapus seleksi sebelumnya
                    teacherCards.forEach(c => c.classList.remove('selected'));
                    
                    // Tandai yang dipilih
                    card.classList.add('selected');
                    
                    // Simpan ID guru yang dipilih
                    selectedTeacherId = card.getAttribute('data-teacher-id');
                });
            });
            
            // Event listener untuk tombol kirim pengaduan fisik/psikis
            const submitPpBtn = document.getElementById('submit-pp-btn');
            const confirmationModal = document.getElementById('confirmation-modal');
            const modalCloseBtn = document.getElementById('modal-close-btn');
            const modalDashboardBtn = document.getElementById('modal-dashboard-btn');
            const teacherNameConfirm = document.getElementById('teacher-name-confirm');
            
            submitPpBtn.addEventListener('click', () => {
                // Validasi form
                if (!selectedType) {
                    alert('Silakan pilih jenis pengaduan (Fisik atau Psikis)');
                    return;
                }
                
                if (!issueTypeSelect.value) {
                    alert('Silakan pilih jenis masalah');
                    return;
                }
                
                if (!selectedTeacherId) {
                    alert('Silakan pilih guru yang dituju');
                    return;
                }
                
                // Dapatkan nama guru yang dipilih
                const selectedTeacherCard = document.querySelector(`.teacher-card[data-teacher-id="${selectedTeacherId}"]`);
                const teacherName = selectedTeacherCard.querySelector('.teacher-name').textContent;
                
                // Tampilkan nama guru di modal konfirmasi
                teacherNameConfirm.textContent = teacherName;
                
                // Tampilkan modal konfirmasi
                confirmationModal.style.display = 'flex';
            });
            
            // Event listener untuk tombol di modal
            modalCloseBtn.addEventListener('click', () => {
                confirmationModal.style.display = 'none';
            });
            
            modalDashboardBtn.addEventListener('click', () => {
                confirmationModal.style.display = 'none';
                showPage('dashboard');
            });
            
            // Pengaturan Tab System
            const settingsTabs = document.querySelectorAll('.settings-tab');
            const settingsContents = document.querySelectorAll('.settings-content');
            
            settingsTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabId = tab.getAttribute('data-tab');
                    
                    // Hapus kelas aktif dari semua tab dan konten
                    settingsTabs.forEach(t => t.classList.remove('active'));
                    settingsContents.forEach(c => c.classList.remove('active'));
                    
                    // Tambahkan kelas aktif ke tab dan konten yang dipilih
                    tab.classList.add('active');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });
            
            // Password Strength Indicator
            const newPasswordInput = document.getElementById('new-password');
            const passwordStrengthBar = document.querySelector('.password-strength-bar');
            const passwordStrengthContainer = document.querySelector('.password-strength');
            
            newPasswordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Kriteria kekuatan password
                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;
                
                // Reset classes
                passwordStrengthContainer.classList.remove('password-weak', 'password-medium', 'password-strong');
                
                // Set classes based on strength
                if (password.length === 0) {
                    passwordStrengthBar.style.width = '0%';
                } else if (strength <= 2) {
                    passwordStrengthContainer.classList.add('password-weak');
                } else if (strength <= 4) {
                    passwordStrengthContainer.classList.add('password-medium');
                } else {
                    passwordStrengthContainer.classList.add('password-strong');
                }
            });
            
            // Logout Functionality
            const logoutBtn = document.getElementById('logout-btn');
            const logoutModal = document.getElementById('logout-modal');
            const logoutCancelBtn = document.getElementById('logout-cancel-btn');
            const logoutConfirmBtn = document.getElementById('logout-confirm-btn');
            const logoutUserName = document.getElementById('logout-user-name');
            
            // Set user name in logout modal
            const userName = document.querySelector('.user-name').textContent;
            logoutUserName.textContent = userName;
            
            // Show logout modal when logout button is clicked
            logoutBtn.addEventListener('click', () => {
                logoutModal.style.display = 'flex';
            });
            
            // Hide logout modal when cancel button is clicked
            logoutCancelBtn.addEventListener('click', () => {
                logoutModal.style.display = 'none';
            });
            
            // Redirect to index.php when logout is confirmed
            logoutConfirmBtn.addEventListener('click', () => {
                // Simulate logout process
                // In a real application, you would also clear session/cookie data
                window.location.href = '../index.php';
            });
            
            // Responsive check
            function checkWidth() {
                if (window.innerWidth <= 768) {
                    menuToggle.style.display = 'flex';
                    sidebar.classList.remove('active');
                } else {
                    menuToggle.style.display = 'none';
                    sidebar.classList.add('active');
                }
            }
            
            // Initial check
            checkWidth();
            
            // Listen for window resize
            window.addEventListener('resize', checkWidth);
        });
