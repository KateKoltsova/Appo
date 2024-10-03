<script setup>
import {onMounted, reactive, ref} from 'vue';
import {useRouter} from 'vue-router';
import {fetchUserById, updateUser} from "../services/UserService.js";
import {useAuthWatcher} from '../localstorage';
import LoadingSpinner from "../components/LoadingSpinner.vue";
import {UserModel} from "../models/UserModel.js";
import UserForm from "../components/UserForm.vue";

const user = ref({...UserModel});
const editedUser = reactive({...UserModel});
const isLoading = ref(false);
const router = useRouter();
useAuthWatcher();

onMounted(async () => {
    isLoading.value = true;
    const userId = localStorage.getItem('userId');
    const cachedUser = localStorage.getItem('user');

    if (cachedUser) {
        assignUserData(JSON.parse(cachedUser));
    } else {
        try {
            const response = await fetchUserById(userId);
            if (response.status === 200) {
                assignUserData(response.data.data);
            } else {
                console.error('Ошибка получения данных пользователя');
            }
        } catch (error) {
            console.error(error);
        }
    }
    isLoading.value = false;
});

const assignUserData = (data) => {
    Object.assign(user.value, data);
    Object.assign(editedUser, data);
    localStorage.setItem('user', JSON.stringify(data));
};

const editUser = async () => {
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
        const response = await updateUser(userId, updatedFields);
        if (response.status === 200) {
            assignUserData(editedUser);
        } else {
            console.error('Ошибка обновления данных пользователя');
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
    }
    isLoading.value = false;
};
</script>

<template>
    <div>
        <LoadingSpinner :isLoading="isLoading"/>
        <h2>Hello, user {{ editedUser?.firstname }} {{ editedUser?.lastname }}</h2>
        <UserForm :editedUser="editedUser" :isLoading="isLoading" @onSave="editUser"/>
    </div>
</template>

<style scoped>

</style>

