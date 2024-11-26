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
const isDropdownOpen = ref(false);
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
        isDropdownOpen.value = !isDropdownOpen.value;
    } else {
        router.push('/login');
    }
};

const handleProfileClick = () => {
    isDropdownOpen.value = false;
    router.push('/profile');
};

const handleLogoutClick = () => {
    isDropdownOpen.value = false;
    localStorage.clear();
    router.push('/login');
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
                <button @click="handleBookingClick" class="logo-button">
                    <img src="../../../storage/images/APPO_BEAUTY_logo.png" alt="Логотип" />
                </button>
            </div>

            <div class="user-actions">
                <div class="user-menu-wrapper">
                    <button @click="handleClick" class="icon-class">
                        <i :class="iconClass"></i>
                    </button>
                    <ul v-if="isDropdownOpen" class="dropdown-menu">
                        <li @click="handleProfileClick">Профиль</li>
                        <li @click="handleLogoutClick">Выйти</li>
                    </ul>
                </div>

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
    height: 130px;
    display: flex;
    align-items: center;
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
    margin: -10px;
    height: 120px;
    width: auto;
}

.logo-button {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
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

.user-menu-wrapper {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    display: inherit;
    top: 100%;
    right: 0;
    background-color: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    list-style: none;
    padding: 10px 0;
    margin: 0;
    border-radius: 4px;
    z-index: 10;
    text-align: right;
    width: 150px;
}

.dropdown-menu li {
    padding: 10px 15px;
    cursor: pointer;
    font-size: 20px;
    color: #333;
    transition: background-color 0.2s;
}

.dropdown-menu li:hover {
    background-color: #f5f5f5;
}
</style>
