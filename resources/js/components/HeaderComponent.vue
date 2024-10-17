<script setup>
import {ref, watch} from 'vue';
import {useRouter} from 'vue-router';
import {useAuthWatcher, isAuthenticated} from '../localstorage';
import LoadingSpinner from "./LoadingSpinner.vue";
import CartModal from "./CartModal.vue";

const token = ref('');
const userId = ref('');
const router = useRouter();
const iconClass = ref('fa-solid fa-right-to-bracket');

const isLoading = ref(false);
const isCartModalOpen = ref(false);

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
    isCartModalOpen.value = false;
    if (isAuthenticated.value) {
        router.push('/profile');
    } else {
        router.push('/login');
    }
};

const handleBookingClick = () => {
    isCartModalOpen.value = false;
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

};
</script>

<template>
    <LoadingSpinner :isLoading="isLoading" />
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
    <CartModal :isCartModalOpen="isCartModalOpen" />
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
</style>
