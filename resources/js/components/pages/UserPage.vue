<script setup>
import apiClient from "../../apiClient.js";
import {ref, onMounted} from "vue";
import {urls} from '../../urls.js';
import {UserModel} from "../../models/UserModel.js";
import {useRouter} from 'vue-router';

const user = ref({...UserModel});
const router = useRouter();

onMounted(async () => {
    const accessToken = localStorage.getItem('accessToken');
    const userId = localStorage.getItem('userId');
    if (!accessToken || !userId) {
        localStorage.clear();
        await router.push('/login');
        return;
    }

    const cachedUser = localStorage.getItem('user');
    if (cachedUser) {
        user.value = JSON.parse(cachedUser);
    } else {
        try {
            const response = await apiClient({
                url: urls.users.byId.url(userId),
                method: "GET",
            });
            if (response.status === 200) {
                Object.assign(user.value, response.data.data);
                localStorage.setItem('user', JSON.stringify(response.data.data));
            } else {
                console.error('Ошибка получения данных пользователя');
            }
        } catch (error) {
            console.error('Ошибка сети:', error);
        }
    }
});
</script>

<template>
    <div>
        <h2>Hello, user {{ user?.firstname }}</h2>
        {{ user }}
    </div>
</template>

<style scoped>

</style>
