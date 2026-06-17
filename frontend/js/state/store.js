/**
 * ECO-PROTECT Reactive Global Store (Simple Pub/Sub)
 */

class Store {
    constructor() {
        // Trạng thái ban đầu
        this.state = {
            currentUser: null,
            cartItems: [],
            cartTotalPrice: 0,
            categories: [],
            activeCategory: null
        };
        
        this.listeners = [];
        
        // Khởi tạo thông tin từ localStorage nếu có
        try {
            const cachedUser = localStorage.getItem('current_user');
            if (cachedUser) {
                this.state.currentUser = JSON.parse(cachedUser);
            }
        } catch (e) {
            console.error('Lỗi khi đọc cache User:', e);
        }
    }

    // Đăng ký lắng nghe sự thay đổi của Store
    subscribe(listener) {
        this.listeners.push(listener);
        // Trả về hàm hủy đăng ký (unsubscribe)
        return () => {
            this.listeners = this.listeners.filter(l => l !== listener);
        };
    }

    // Phát thông báo thay đổi trạng thái
    publish() {
        for (const listener of this.listeners) {
            listener(this.state);
        }
    }

    // Cập nhật State một phần
    setState(newState) {
        this.state = { ...this.state, ...newState };
        this.publish();
    }

    // Các helper cập nhật chuyên biệt
    setCurrentUser(user) {
        this.setState({ currentUser: user });
    }

    setCart(cartItems, totalPrice) {
        this.setState({ 
            cartItems: cartItems || [], 
            cartTotalPrice: totalPrice || 0 
        });
    }

    setCategories(categories) {
        this.setState({ categories });
    }

    setActiveCategory(categoryId) {
        this.setState({ activeCategory: categoryId });
    }
}

export const store = new Store();
export default store;
