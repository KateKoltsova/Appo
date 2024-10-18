<script setup>
import {onMounted, reactive, ref} from 'vue';
import {useRouter} from 'vue-router';
import {fetchUserById, updateUser} from "../services/UserService.js";
import {useAuthWatcher} from '../localstorage';
import LoadingSpinner from "../components/LoadingSpinner.vue";
import {UserModel} from "../models/UserModel.js";
import UserForm from "../components/UserForm.vue";
import UserAppointments from "../components/UserAppointments.vue";

const activeTab = ref("profile");
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

const selectTab = (tab) => {
    activeTab.value = tab;
};

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
    <div class="profile-container">
        <LoadingSpinner :isLoading="isLoading" />
        <div class="profile-card">
            <nav class="side-tabs">
                <ul>
                    <li :class="{ active: activeTab === 'profile' }" @click="selectTab('profile')">
                        Профиль
                    </li>
                    <li :class="{ active: activeTab === 'appointments' }" @click="selectTab('appointments')">
                        Записи
                    </li>
                </ul>
            </nav>
            <div class="tab-content">
                <div v-if="activeTab === 'profile'">
                    <h2>Hello, user {{ user.id }} {{ editedUser?.firstname }} {{ editedUser?.lastname }}</h2>
                    <UserForm :editedUser="editedUser" :isLoading="isLoading" @onSave="editUser" />
                </div>

                <div v-if="activeTab === 'appointments'">
                    <h2>Ваши записи</h2>
                    <UserAppointments :userId="user?.id" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.profile-container {
    display: flex;
    justify-content: center;
    padding: 20px;
}

.profile-card {
    display: flex;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    background-color: white;
    width: 80%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.side-tabs {
    width: 200px;
    margin-right: 20px;
}

.side-tabs ul {
    list-style: none;
    padding: 0;
}

.side-tabs li {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #ddd;
}

.side-tabs li.active {
    background-color: #f5f5f5;
    font-weight: bold;
}

.tab-content {
    flex: 1;
}
</style>
