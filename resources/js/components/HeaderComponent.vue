<script setup>
import {ref, watch} from 'vue';
import {useRouter} from 'vue-router';
import {isAuthenticated} from '../localstorage';
import LoadingSpinner from "./LoadingSpinner.vue";
import CartModal from "./CartModal.vue";

const token = ref('');
const userId = ref('');
const router = useRouter();
const iconClass = ref('fa-solid fa-right-to-bracket');

const isLoading = ref(false);
const isCartModalOpen = ref(false);

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

const handleCartClick = async () => {
    isCartModalOpen.value = !isCartModalOpen.value;

};
</script>

<template>
    <LoadingSpinner :isLoading="isLoading"/>
    <header class="site-header">
        <nav>
            <div class="logo">
                <img src="../../../storage/images/APPO_BEAUTY_logo.png" alt="Логотип" />
            </div>

            <div class="user-actions">
                <button @click="handleClick" class="icon-class">
                    <i :class="iconClass"></i>
                </button>
                <button @click="handleBookingClick" class="booking-button">
                    <i class="fa-solid fa-calendar-days"></i>
                </button>
                <button @click="handleCartClick" class="cart-button">
                    <i class="fa-solid fa-cart-shopping"></i>
                </button>
            </div>
        </nav>
    </header>
    <CartModal :isCartModalOpen="isCartModalOpen"/>
</template>

<style scoped>
.site-header {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    background-color: #02333e;
    border-bottom: 1px solid #ccc;
}

.site-header nav {
    display: flex;
    justify-content: space-between;
    width: 100%;
    align-items: center;
}

.logo img {
    height: 100px;
    width: auto;
}

.user-actions {
    display: flex;
    gap: 50px;
    align-items: center;
}

.user-actions button {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 44px;
    color: #ddd0d1;
}
</style>
