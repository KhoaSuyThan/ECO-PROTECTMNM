/**
 * ECO-PROTECT API Service Client
 */

const API_BASE_URL = '../api';

// Lưu trữ token vào localStorage
function getAccessToken() {
    return localStorage.getItem('access_token');
}

function getRefreshToken() {
    return localStorage.getItem('refresh_token');
}

function setTokens(accessToken, refreshToken) {
    if (accessToken) localStorage.setItem('access_token', accessToken);
    if (refreshToken) localStorage.setItem('refresh_token', refreshToken);
}

function clearTokens() {
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('current_user');
}

// Hàm fetch wrapper chung hỗ trợ xử lý token & refresh token tự động
async function request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    
    // Đảm bảo có headers
    options.headers = options.headers || {};
    
    // Đính kèm JWT Access Token nếu có
    const token = getAccessToken();
    if (token && !options.headers['Authorization']) {
        options.headers['Authorization'] = `Bearer ${token}`;
    }

    // Nếu body là object thông thường (không phải FormData) thì JSON.stringify
    if (options.body && !(options.body instanceof FormData) && typeof options.body === 'object') {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(options.body);
    }

    try {
        let response = await fetch(url, options);

        // Nếu Token hết hạn (401) và có Refresh Token -> Thử lấy Access Token mới
        if (response.status === 401 && getRefreshToken()) {
            const refreshed = await tryRefreshToken();
            if (refreshed) {
                // Thử gửi lại yêu cầu ban đầu với Token mới
                options.headers['Authorization'] = `Bearer ${getAccessToken()}`;
                response = await fetch(url, options);
            } else {
                // Refresh thất bại -> Đăng xuất người dùng
                clearTokens();
                window.location.hash = '#login';
                throw new Error('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
            }
        }

        if (!response.ok) {
            const errData = await response.json().catch(() => ({}));
            throw new Error(errData.message || `Lỗi hệ thống: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error(`API Request Error [${endpoint}]:`, error);
        throw error;
    }
}

// Gọi API lấy Access Token mới bằng Refresh Token
async function tryRefreshToken() {
    const refresh = getRefreshToken();
    if (!refresh) return false;

    try {
        const url = `${API_BASE_URL}/auth/refresh`;
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ refresh_token: refresh })
        });

        if (res.ok) {
            const data = await res.json();
            setTokens(data.access_token);
            return true;
        }
    } catch (e) {
        console.error('Lỗi khi làm mới token:', e);
    }
    return false;
}

export const api = {
    // AUTHENTICATION APIs
    auth: {
        async register(username, email, password, confirmPassword) {
            return request('/auth/register', {
                method: 'POST',
                body: { username, email, password, confirm_password: confirmPassword }
            });
        },
        
        async login(username, password) {
            const data = await request('/auth/login', {
                method: 'POST',
                body: { username, password }
            });
            setTokens(data.access_token, data.refresh_token);
            localStorage.setItem('current_user', JSON.stringify(data.user));
            return data.user;
        },

        logout() {
            clearTokens();
            // Trả về promise giả lập
            return Promise.resolve(true);
        },

        async getMe() {
            if (!getAccessToken()) return null;
            try {
                const user = await request('/auth/me');
                localStorage.setItem('current_user', JSON.stringify(user));
                return user;
            } catch (e) {
                clearTokens();
                return null;
            }
        },

        async updateProfile(formData) {
            // Nhận vào FormData (để upload ảnh)
            const data = await request('/auth/profile', {
                method: 'POST', // Backend hỗ trợ POST upload profile
                body: formData
                // Lưu ý: Không đặt Content-Type header để trình duyệt tự thiết lập boundary của FormData
            });
            localStorage.setItem('current_user', JSON.stringify(data.user));
            return data.user;
        },

        async changePassword(current_password, new_password, confirm_password) {
            return request('/auth/change-password', {
                method: 'PUT',
                body: { current_password, new_password, confirm_password }
            });
        },

        async forgotPassword(email) {
            return request('/auth/forgot-password', {
                method: 'POST',
                body: { email }
            });
        },

        async listUsers() {
            return request('/auth/users');
        },

        async toggleUserStatus(userId) {
            return request('/auth/users', {
                method: 'PUT',
                body: { id: userId }
            });
        }
    },

    // PRODUCT APIs
    products: {
        async getProducts(params = {}) {
            const queryParams = new URLSearchParams();
            for (const [key, val] of Object.entries(params)) {
                if (val !== undefined && val !== null && val !== '') {
                    queryParams.append(key, val);
                }
            }
            const queryStr = queryParams.toString();
            return request(`/product${queryStr ? '?' + queryStr : ''}`);
        },

        async getProduct(id) {
            return request(`/product/${id}`);
        },

        async addProduct(formData) {
            return request('/product', {
                method: 'POST',
                body: formData
            });
        },

        async updateProduct(id, formData) {
            // Với API PUT multipart/form-data trong PHP, ta gửi POST kèm giả lập hoặc dùng POST trực tiếp
            // Vì ProductApiController dùng $_POST/$_FILES cho hàm update($id)
            return request(`/product/${id}`, {
                method: 'POST', // Gửi dạng POST vì PHP không hỗ trợ sẵn PUT file upload qua form-data
                body: formData
            });
        },

        async deleteProduct(id) {
            return request(`/product/${id}`, {
                method: 'DELETE'
            });
        }
    },

    // CATEGORY APIs
    categories: {
        async getCategories() {
            return request('/category');
        },
        async addCategory(name) {
            return request('/category', {
                method: 'POST',
                body: { name }
            });
        },
        async updateCategory(id, name) {
            return request(`/category/${id}`, {
                method: 'PUT',
                body: { name }
            });
        },
        async deleteCategory(id) {
            return request(`/category/${id}`, {
                method: 'DELETE'
            });
        }
    },

    // CART APIs (Đồng bộ trực tiếp với database thông qua API)
    cart: {
        async getCart() {
            if (!getAccessToken()) {
                // Khách vãng lai dùng giỏ hàng ảo trong LocalStorage
                const localCart = JSON.parse(localStorage.getItem('guest_cart') || '[]');
                const totalPrice = localCart.reduce((total, item) => total + (item.price * item.quantity), 0);
                return { cart: localCart, total_price: totalPrice };
            }
            return request('/cart');
        },

        async addToCart(productId, quantity = 1) {
            if (!getAccessToken()) {
                // Xử lý giỏ hàng Guest
                throw new Error('Vui lòng đăng nhập để thêm vào giỏ hàng!');
            }
            return request('/cart', {
                method: 'POST',
                body: { product_id: productId, quantity }
            });
        },

        async updateQuantity(productId, quantity) {
            if (!getAccessToken()) return;
            return request(`/cart/${productId}`, {
                method: 'PUT',
                body: { quantity }
            });
        },

        async removeFromCart(productId) {
            if (!getAccessToken()) return;
            return request(`/cart/${productId}`, {
                method: 'DELETE'
            });
        },

        async clearCart() {
            if (!getAccessToken()) return;
            return request('/cart', {
                method: 'DELETE'
            });
        }
    },

    // ORDER APIs
    orders: {
        async getOrders() {
            return request('/order');
        },
        
        async getOrder(id) {
            return request(`/order/${id}`);
        },

        async createOrder(name, phone, address, paymentMethod = 'COD') {
            return request('/order', {
                method: 'POST',
                body: { name, phone, address, payment_method: paymentMethod }
            });
        },

        async cancelOrder(id) {
            return request(`/order/${id}/cancel`, {
                method: 'PUT'
            });
        },

        async updateOrderStatus(id, status) {
            return request(`/order/${id}/status`, {
                method: 'PUT',
                body: { status }
            });
        }
    },

    // PAYMENT APIs
    payment: {
        async pay(orderId, amount) {
            return request('/payment', {
                method: 'POST',
                body: { order_id: orderId, amount }
            });
        }
    }
};
