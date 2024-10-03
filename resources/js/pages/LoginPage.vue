<script setup>
import {onMounted, ref} from 'vue';
import {useRouter} from 'vue-router';
import {urls} from '../urls.js';
import apiClient from "../apiClient.js";
import LoadingSpinner from "../components/LoadingSpinner.vue";

const email = ref('');
const password = ref('');
const router = useRouter();
const isLoading = ref(false);

onMounted(async () => {
    const accessToken = localStorage.getItem('accessToken');
    const userId = localStorage.getItem('userId');

    if (accessToken && userId) {
        await router.push('/profile');
    }
});
const login = async () => {
    try {
        isLoading.value = true;
        const response = await apiClient({
            url: urls.auth.login.url,
            method: 'POST',
            data: {
                email: email.value,
                password: password.value,
            },
        });

        if (response.status === 200) {
            const {access_token: accessToken, id: userId} = response.data.data;
            localStorage.setItem('accessToken', accessToken);
            localStorage.setItem('userId', userId);

            await router.push('/profile');
        } else {
            console.error('Ошибка авторизации');
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
    }
    isLoading.value = false;
};
</script>

<template>
    <div>
        <h2>Login</h2>
        <form @submit.prevent="login" :class="{ 'disabled': isLoading }">
            <div>
                <label for="email">Email:</label>
                <input type="email" v-model="email" required/>
            </div>
            <div>
                <label for="password">Пароль:</label>
                <input type="password" v-model="password" required/>
            </div>
            <button type="submit">Войти</button>
        </form>
        <LoadingSpinner :isLoading="isLoading" />
    </div>
</template>

<style scoped>

</style>
