<script setup>
import apiClient from "../../apiClient.js";
import {ref, onMounted, reactive} from "vue";
import {urls} from '../../urls.js';
import {UserModel} from "../../models/UserModel.js";
import {useRouter} from 'vue-router';
import {useAuthWatcher} from '../../localstorage.js';


const user = ref({...UserModel});
const editedUser = reactive({...UserModel});
const isLoading = ref(false);
const router = useRouter();
useAuthWatcher();

onMounted(async () => {
    const userId = localStorage.getItem('userId');
    const cachedUser = localStorage.getItem('user');
    let userData;

    if (cachedUser) {
        assignUserData(JSON.parse(cachedUser));
        // user.value = JSON.parse(cachedUser);
    } else {
        try {
            const response = await apiClient({
                url: urls.users.byId.url(userId),
                method: "GET",
            });
            if (response.status === 200) {
                // userData = response.data.data;
                assignUserData(userData);
                localStorage.setItem('user', JSON.stringify(userData));
            } else {
                console.error('Ошибка получения данных пользователя');
            }
        } catch (error) {
            console.error('Ошибка сети:', error);
        }
    }
    // Object.assign(user.value, userData);
    // Object.assign(editedUser, userData);
});

const assignUserData = (data) => {
    Object.assign(user.value, data);
    Object.assign(editedUser, data);
    localStorage.setItem('user', JSON.stringify(data));
};

const updateUser = async () => {
    isLoading.value = true;
    const userId = localStorage.getItem('userId');

    const updatedFields = {};
    for (const key in editedUser) {
        if (editedUser[key] !== user.value[key]) {
            updatedFields[key] = editedUser[key];
        }
    }

    if (Object.keys(updatedFields).length === 0) {
        console.log('Нет изменений для обновления');
        isLoading.value = false;
        return;
    }

    try {
        const response = await apiClient({
            url: urls.users.byId.url(userId),
            method: "PATCH",
            data: updatedFields,
        });
        if (response.status === 200) {
            console.log('Данные успешно обновлены');
            assignUserData(editedUser);
            // Object.assign(user.value, editedUser);
            localStorage.setItem('user', JSON.stringify(user.value));
        } else {
            console.error('Ошибка обновления данных');
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
        <h2>Hello, user {{ user?.firstname }} {{ user?.lastname }} </h2>
        <!--        {{ user }}-->
        <form @submit.prevent="updateUser" :class="{ 'disabled': isLoading }">
            <div>
                <label for="firstname">FirstName:</label>
                <input type="text" id="firstname" v-model="editedUser.firstname"/>
            </div>

            <div>
                <label for="lastname">Lastname:</label>
                <input type="text" id="lastname" v-model="editedUser.lastname"/>
            </div>

            <div>
                <label for="birthdate">Birthdate:</label>
                <input type="date" id="birthdate" v-model="editedUser.birthdate"/>
            </div>

            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" v-model="editedUser.email"/>
            </div>

            <div>
                <label for="phone_number">Phone number:</label>
                <input type="tel" id="phone_number" v-model="editedUser.phone_number"/>
            </div>

            <button type="submit">Save</button>
        </form>
        <!-- Overlay for loading state -->
        <div v-if="isLoading" class="loading-overlay">
            Loading...
        </div>
    </div>
</template>

<style scoped>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 2rem;
    z-index: 1000;
}

.disabled {
    opacity: 0.5;
    pointer-events: none;
}
</style>
