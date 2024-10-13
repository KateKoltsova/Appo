<script setup>
import {ref, watch} from 'vue';
import {useRouter} from 'vue-router';
import {useAuthWatcher, isAuthenticated} from '../localstorage';
import {getCart, remove} from '../services/CartService';
import LoadingSpinner from "./LoadingSpinner.vue";

const token = ref('');
const userId = ref('');
const router = useRouter();
const iconClass = ref('fa-solid fa-right-to-bracket');

const isLoading = ref(false);
const cart = ref([]);
const isCartModalOpen = ref(false);
const hasErrorItems = ref(false);

useAuthWatcher();

watch(isAuthenticated, (newVal) => {
    iconClass.value = newVal
        ? 'fa-regular fa-circle-user'
        : 'fa-solid fa-right-to-bracket';
});

const checkUser = () => {
    token.value = localStorage.getItem('accessToken');
    userId.value = localStorage.getItem('userId');
};

const handleClick = () => {
    if (isAuthenticated.value) {
        router.push('/profile');
    } else {
        router.push('/login');
    }
};

const handleBookingClick = () => {
    router.push('/booking');
};

const handleStorageChange = () => {
    checkUser();
    if (!token.value || !userId.value) {
        window.location.reload();
    }
};

const handleCartClick = async () => {
    isCartModalOpen.value = !isCartModalOpen.value;
    if (isCartModalOpen.value) {
        await getCartItems();
    }
};

const getCartItems = async () => {
    try {
        isLoading.value = true;
        const userId = localStorage.getItem("userId");
        const response = await getCart(userId);
        cart.value = response.data.data;
        checkErrorItems();
    } catch (error) {
        console.error('Ошибка получения данных корзины:', error);
    }
    isLoading.value = false;
};

const removeItem = async (itemId) => {
    try {
        const userId = localStorage.getItem("userId");
        await remove(userId, itemId);
        cart.value.items = cart.value.items.filter(item => item.id !== itemId);
        checkErrorItems();
    } catch (error) {
        console.error('Ошибка удаления элемента:', error);
    }
};

const checkErrorItems = () => {
    hasErrorItems.value = cart.value.items.some(item => item.message);
};
</script>

<template>
    <header class="site-header">
        <nav>
            <div class="user-actions">
                <button @click="handleClick">
                    <i :class="iconClass"></i>
                </button>
                <button @click="handleBookingClick" class="booking-button">
                    Booking
                </button>
                <button @click="handleCartClick" class="cart-button">
                    <i class="fa-solid fa-cart-shopping"></i>
                </button>
            </div>
        </nav>
    </header>
    <div v-if="isCartModalOpen" class="cart-modal">
        <div class="cart-modal-content">
            <h2>Корзина</h2>
            <LoadingSpinner :isLoading="isLoading"/>

            <ul>
                <li v-for="item in cart.items" :key="item.id">
                    <div class="cart-item-card" :class="{'error-item': item.message}">
                        <h3>{{ item.title }}</h3>
                        <p class="item-info">Дата и время: {{ item.date_time }}</p>
                        <p class="item-info">Мастер: {{ item.master_firstname }} {{ item.master_lastname }}</p>
                        <p class="item-info">Цена: {{ item.price }} ГРН</p>

                        <button class="remove-button" @click="removeItem(item.id)">✖</button>

                        <div v-if="item.message" class="error-message">
                            Это время больше недоступно! Удалите его, чтобы продолжить!
                        </div>
                    </div>
                </li>
            </ul>

            <div class="cart-summary">
                <p>Общее количество: {{ cart.totalCount }}</p>
                <p>Итоговая сумма: {{ cart.totalSum }} ГРН</p>
            </div>

            <button class="checkout-button" :disabled="hasErrorItems">
                Оформить заказ
            </button>
        </div>
    </div>
</template>

<style scoped>
.site-header {
    display: flex;
    justify-content: space-between;
    padding: 10px 20px;
    background-color: #6c757d;
    color: #fff;
}

.nav-links {
    list-style: none;
    display: flex;
}

.nav-links li {
    margin-right: 20px;
}

.user-actions button {
    background: none;
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
}

.user-actions button i {
    font-size: 70px;
}

.user-actions button:hover {
    color: #ddd;
}

.booking-button {
    margin-left: 20px;
    padding: 10px 20px;
    background-color: #28a745;
    border-radius: 5px;
    color: #fff;
    cursor: pointer;
    font-size: 18px;
}

.booking-button:hover {
    background-color: #218838;
}

.cart-modal {
    position: fixed;
    top: 0;
    right: 0;
    width: 50%;
    height: 100%;
    background-color: #fff;
    z-index: 1000;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5);
    padding: 20px;
}

.cart-modal-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.cart-item-card {
    border: 1px solid #ccc;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    position: relative;
}

.error-item {
    border-color: red;
    background-color: #ffe6e6;
}

.item-info {
    font-size: 14px;
    margin-bottom: 5px;
}

.error-message {
    color: red;
    font-size: 12px;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    padding: 5px;
    background-color: rgba(255, 0, 0, 0.2);
    text-align: center;
}

.remove-button {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
}

.cart-summary {
    text-align: right;
    margin-top: 20px;
}

.checkout-button {
    background-color: #28a745;
    border: none;
    padding: 15px;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
    width: 100%;
}

.checkout-button:disabled {
    background-color: grey;
}
</style>
