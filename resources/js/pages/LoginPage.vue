<script setup>
import {onMounted, ref} from 'vue';
import {useRouter} from 'vue-router';
import {urls} from '../urls.js';
import apiClient from "../apiClient.js";
import LoadingSpinner from "../components/LoadingSpinner.vue";
import {forgotPassword} from "../services/UserService.js";

const email = ref('');
const password = ref('');
const router = useRouter();
const isLoading = ref(false);
const isForgotPassword = ref(false);
const resetMessage = ref('');

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
    } finally {
        isLoading.value = false;
    }
};

const forgotUserPassword = async () => {
    try {
        isLoading.value = true;
        const response = await forgotPassword(email.value);

        if (response.status === 200) {
            resetMessage.value = 'На указанный email отправлено письмо для смены пароля! Проверьте почту!';
        } else {
            console.error('Ошибка сброса пароля');
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
    } finally {
        isLoading.value = false;
    }
}

const toggleForgotPassword = () => {
    isForgotPassword.value = !isForgotPassword.value;
    resetMessage.value = '';
    email.value = '';
};
</script>

<template>
    <div>
        <LoadingSpinner :isLoading="isLoading"/>

        <h2 v-if="!isForgotPassword">Login</h2>
        <h2 v-else>Reset Password</h2>

        <form v-if="!isForgotPassword" @submit.prevent="login" :class="{ 'disabled': isLoading }">
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

        <form v-else-if="isForgotPassword && !resetMessage" @submit.prevent="forgotUserPassword" :class="{ 'disabled': isLoading }">
            <div>
                <label for="email">Введите ваш email:</label>
                <input type="email" v-model="email" required />
            </div>
            <button type="submit">Сбросить пароль</button>
        </form>

        <button v-if="!isForgotPassword" @click="toggleForgotPassword">Забыли пароль?</button>
        <p v-if="isForgotPassword && resetMessage">{{ resetMessage }}</p>
        <button v-if="isForgotPassword && resetMessage" @click="toggleForgotPassword">Вернуться к логину</button>
    </div>
</template>

<style scoped>

</style>
