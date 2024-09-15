<script setup>
import {onMounted, onUnmounted, ref, watchEffect} from 'vue';
import { useRouter } from 'vue-router';

const token = ref('');
const userId = ref('');
const router = useRouter();
const iconClass = ref('fa-solid fa-right-to-bracket');

const checkUser = () => {
    token.value = localStorage.getItem('accessToken');
    userId.value = localStorage.getItem('userId');
};

const handleClick = () => {
    if (token.value && userId.value) {
        router.push('/profile');
    } else {
        router.push('/login');
    }
};

const handleStorageChange = () => {
    checkUser();
    if (!token.value || !userId.value) {
        window.location.reload();
    }
};

watchEffect(() => {
    iconClass.value = token.value && userId.value
        ? 'fa-regular fa-circle-user'
        : 'fa-solid fa-right-to-bracket';
});

onMounted(() => {
    checkUser();
    window.addEventListener('storage', handleStorageChange);
});

onUnmounted(() => {
    window.removeEventListener('storage', handleStorageChange);
});
</script>

<template>
    <header class="site-header">
        <nav>
            <div class="user-actions">
                <button @click="handleClick">
                    <i :class="iconClass"></i>
                </button>
            </div>
        </nav>
    </header>
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
</style>
