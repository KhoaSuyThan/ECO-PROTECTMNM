import { api } from '../api/api.js';
import { store } from '../state/store.js';
import { showAlert } from '../components/Alert.js';

export function renderProfilePage() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;

    if (!store.state.currentUser) {
        window.location.hash = '#login';
        return;
    }

    const { currentUser } = store.state;
    const avatarUrl = currentUser.avatar ? `../${currentUser.avatar}` : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80';

    mainContent.innerHTML = `
        <h1 style="margin-bottom: 2rem; font-size: 2.2rem; color: var(--primary);">Hồ Sơ Cá Nhân</h1>
        
        <div class="profile-grid">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar-wrapper">
                    <img class="profile-avatar" id="profile-avatar-img" src="${avatarUrl}" alt="Avatar" onError="this.src='https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80'">
                </div>
                <h3 style="margin-bottom: 0.5rem;">${currentUser.fullname || currentUser.username}</h3>
                <span class="badge badge-primary">${currentUser.role === 'admin' ? 'Quản trị viên' : 'Khách hàng'}</span>
                
                <div style="margin-top: 2rem; text-align: left; font-size: 0.9rem; color: var(--gray-600);">
                    <p style="margin-bottom: 0.5rem;"><i class="fa-regular fa-envelope"></i> ${currentUser.email}</p>
                    <p style="margin-bottom: 0.5rem;"><i class="fa-solid fa-phone"></i> ${currentUser.phone || 'Chưa cập nhật SĐT'}</p>
                </div>
            </div>

            <!-- Main forms -->
            <div class="profile-main">
                <h2 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem; color: var(--gray-800);">Cập nhật thông tin</h2>
                
                <form id="profile-update-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="prof-fullname">Họ và tên</label>
                        <input type="text" id="prof-fullname" class="form-control" placeholder="Nhập họ và tên..." value="${currentUser.fullname || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label for="prof-email">Email</label>
                        <input type="email" id="prof-email" class="form-control" placeholder="Nhập địa chỉ email..." value="${currentUser.email || ''}" required>
                    </div>

                    <div class="form-group">
                        <label for="prof-phone">Số điện thoại</label>
                        <input type="tel" id="prof-phone" class="form-control" placeholder="Nhập số điện thoại..." value="${currentUser.phone || ''}">
                    </div>

                    <div class="form-group">
                        <label for="prof-address">Địa chỉ</label>
                        <input type="text" id="prof-address" class="form-control" placeholder="Nhập địa chỉ..." value="${currentUser.address || ''}">
                    </div>

                    <div class="form-group">
                        <label for="prof-avatar-file">Ảnh đại diện mới</label>
                        <input type="file" id="prof-avatar-file" class="form-control" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-primary" id="profile-save-btn">
                        <i class="fa-regular fa-floppy-disk"></i> Lưu thay đổi
                    </button>
                </form>

                <h2 style="margin-top: 3rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem; color: var(--gray-800);">Đổi mật khẩu</h2>
                
                <form id="profile-pwd-form">
                    <div class="form-group">
                        <label for="pwd-current">Mật khẩu hiện tại <span style="color: var(--danger);">*</span></label>
                        <input type="password" id="pwd-current" class="form-control" placeholder="Nhập mật khẩu hiện tại..." required>
                    </div>
                    <div class="form-group">
                        <label for="pwd-new">Mật khẩu mới <span style="color: var(--danger);">*</span></label>
                        <input type="password" id="pwd-new" class="form-control" placeholder="Nhập mật khẩu mới..." required>
                    </div>
                    <div class="form-group">
                        <label for="pwd-confirm">Xác nhận mật khẩu mới <span style="color: var(--danger);">*</span></label>
                        <input type="password" id="pwd-confirm" class="form-control" placeholder="Xác nhận mật khẩu mới..." required>
                    </div>

                    <button type="submit" class="btn btn-outline" id="pwd-save-btn">
                        <i class="fa-solid fa-key"></i> Đổi mật khẩu
                    </button>
                </form>
            </div>
        </div>
    `;

    // Cập nhật thông tin cá nhân
    const profileForm = document.getElementById('profile-update-form');
    const saveBtn = document.getElementById('profile-save-btn');

    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const fullname = document.getElementById('prof-fullname').value.trim();
        const email = document.getElementById('prof-email').value.trim();
        const phone = document.getElementById('prof-phone').value.trim();
        const address = document.getElementById('prof-address').value.trim();
        const avatarFile = document.getElementById('prof-avatar-file').files[0];

        // Tạo FormData để upload file
        const formData = new FormData();
        formData.append('fullname', fullname);
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('address', address);
        if (avatarFile) {
            formData.append('avatar', avatarFile);
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';

        try {
            const updatedUser = await api.auth.updateProfile(formData);
            store.setCurrentUser(updatedUser);
            showAlert('Cập nhật hồ sơ cá nhân thành công!', 'success');
            
            // Cập nhật lại giao diện avatar và tên
            document.getElementById('profile-avatar-img').src = updatedUser.avatar ? `../${updatedUser.avatar}` : avatarUrl;
            document.querySelector('.profile-sidebar h3').textContent = updatedUser.fullname || updatedUser.username;
        } catch (error) {
            showAlert(error.message || 'Lỗi khi cập nhật hồ sơ.', 'danger');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-regular fa-floppy-disk"></i> Lưu thay đổi';
        }
    });

    // Đổi mật khẩu
    const pwdForm = document.getElementById('profile-pwd-form');
    const pwdBtn = document.getElementById('pwd-save-btn');

    pwdForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const currentPwd = document.getElementById('pwd-current').value;
        const newPwd = document.getElementById('pwd-new').value;
        const confirmPwd = document.getElementById('pwd-confirm').value;

        if (newPwd.length < 6) {
            showAlert('Mật khẩu mới phải dài từ 6 ký tự!', 'warning');
            return;
        }

        if (newPwd !== confirmPwd) {
            showAlert('Mật khẩu xác nhận không khớp!', 'warning');
            return;
        }

        pwdBtn.disabled = true;
        pwdBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang đổi...';

        try {
            await api.auth.changePassword(currentPwd, newPwd, confirmPwd);
            showAlert('Đổi mật khẩu thành công!', 'success');
            pwdForm.reset();
        } catch (error) {
            showAlert(error.message || 'Mật khẩu hiện tại không chính xác.', 'danger');
        } finally {
            pwdBtn.disabled = false;
            pwdBtn.innerHTML = '<i class="fa-solid fa-key"></i> Đổi mật khẩu';
        }
    });
}
