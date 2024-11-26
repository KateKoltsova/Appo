<script setup>
import {ref} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import LoadingSpinner from "../components/LoadingSpinner.vue";
import {resetPassword} from "../services/UserService.js";

const router = useRouter();
const route = useRoute();
const token = route.params.token;
const email = route.query.email;
const newPassword = ref('');
const confirmPassword = ref('');
const isLoading = ref(false);

const resetUserPassword = async () => {
    console.log(token);
    if (newPassword.value !== confirmPassword.value) {
        alert('Пароли не совпадают!');
        return;
    }

    try {
        isLoading.value = true;
        const response = await resetPassword(email, token, newPassword.value);

        if (response.status === 200) {
            alert('Ваш пароль успешно изменён!');
            await router.push('/login');
        } else {
            console.error('Ошибка смены пароля');
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div>
        <h2>Reset Password</h2>
        <form @submit.prevent="resetUserPassword" :class="{ 'disabled': isLoading }">
            <div>
                <label for="new-password">Новый пароль:</label>
                <input id="new-password" v-model="newPassword" type="password" required/>
            </div>
            <div>
                <label for="confirm-password">Повторите новый пароль:</label>
                <input id="confirm-password" v-model="confirmPassword" type="password" required/>
            </div>
            <button type="submit">Сохранить</button>
        </form>
        <LoadingSpinner :isLoading="isLoading"/>
    </div>
</template>

<style scoped>

</style>
