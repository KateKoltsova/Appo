<script setup>
import {onMounted, reactive, ref} from 'vue';
import {useRouter} from 'vue-router';
import {fetchUserById, uploadAvatar, updateUser, logout, logoutAll} from "../services/UserService.js";
import {useAuthWatcher} from '../localstorage';
import LoadingSpinner from "../components/LoadingSpinner.vue";
import {UserModel} from "../models/UserModel.js";
import UserForm from "../components/UserForm.vue";
import UserAppointments from "../components/UserAppointments.vue";
import ScheduleCalendar from "../components/ScheduleCalendar.vue";
import DayScheduleCard from "../components/DayScheduleCard.vue";
import PriceListComponent from "../components/PriceListComponent.vue";
import {Cropper} from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

// const selectedDate = ref(null);
const activeTab = ref("profile");
const user = ref({...UserModel});
const userId = localStorage.getItem('userId');
const avatar = ref(null);
const showModal = ref(false);
const cropperData = reactive({
    x: 0,
    y: 0,
    width: 100,
    height: 100,
});
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

const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const onFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            avatar.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const onCropChange = (coordinates) => {
    cropperData.x = coordinates.coordinates.left;
    cropperData.y = coordinates.coordinates.top;
    cropperData.width = coordinates.coordinates.width;
    cropperData.height = coordinates.coordinates.height;
};

const saveCroppedImage = async () => {
    try {
        isLoading.value = true;
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        const image = new Image();
        image.src = avatar.value;

        await new Promise((resolve, reject) => {
            image.onload = resolve;
            image.onerror = reject;
        });

        const { x, y, width, height } = cropperData;

        canvas.width = width;
        canvas.height = height;
        context.drawImage(image, x, y, width, height, 0, 0, width, height);

        const blob = await new Promise((resolve) => {
            canvas.toBlob(resolve, 'image/jpeg', 1);
        });

        const response = await uploadAvatar(userId, blob);
        if (response.status === 200) {
            console.log('Аватар успешно обновлен');
            const userResponse = await fetchUserById(userId);
            if (userResponse.status === 200) {
                assignUserData(userResponse.data.data);
            } else {
                throw new Error('Ошибка при получении данных пользователя');
            }
        }
    } catch (error) {
        console.error('Ошибка при обрезке изображения:', error);
    } finally {
        isLoading.value = false;
        closeModal();
    }
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

const userLogout = async () => {
    try {
        isLoading.value = true;
        const response = await logout();
        if (response.status === 200) {
            localStorage.clear();
        } else {
            console.error('Ошибка логаута пользователя');
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
    } finally {
        isLoading.value = false;
    }
}

const userLogoutAll = async () => {
    try {
        isLoading.value = true;
        const response = await logoutAll();
        if (response.status === 200) {
            localStorage.clear();
        } else {
            console.error('Ошибка полного логаута пользователя');
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
    } finally {
        isLoading.value = false;
    }
}
</script>

<template>
    <div class="profile-container">
        <LoadingSpinner :isLoading="isLoading"/>
        <div class="profile-card">
            <nav class="side-tabs">
                <ul>
                    <li :class="{ active: activeTab === 'profile' }" @click="selectTab('profile')">
                        Профиль
                    </li>
                    <li :class="{ active: activeTab === 'appointments' }" @click="selectTab('appointments')">
                        Записи
                    </li>
                    <li v-if="user.role === 'master'" :class="{ active: activeTab === 'schedules' }"
                        @click="selectTab('schedules')">
                        Расписание
                    </li>
                    <li v-if="user.role === 'master'" :class="{ active: activeTab === 'prices' }"
                        @click="selectTab('prices')">
                        Цены
                    </li>
                </ul>
            </nav>
            <div class="tab-content">
                <div v-if="activeTab === 'profile'">
                    <h2>Hello, user {{ user.id }} {{ editedUser?.firstname }} {{ editedUser?.lastname }}</h2>
                    <button @click="userLogout()">Выйти</button>
                    <button @click="userLogoutAll()">Выйти со всех устройств</button>
                    
                    <div class="avatar-container" @click="openModal">
                        <img v-if="user.image_url" :src="user.image_url" alt="User Avatar" class="avatar" />
                        <div v-else class="avatar-placeholder">+</div>
                    </div>
                    
                    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
                        <div class="modal-content">
                            <h3>Редактирование аватарки</h3>
                            <input type="file" accept="image/*" @change="onFileChange" />
                            <Cropper v-if="avatar" :src="avatar"
                                :stencil-props="{ aspectRatio: 1, movable: true, scalable: true }"
                                @change="onCropChange" />

                            <div class="modal-actions">
                                <button @click="saveCroppedImage">Сохранить</button>
                                <button @click="closeModal">Отмена</button>
                            </div>
                        </div>
                    </div>

                    <UserForm :editedUser="editedUser" :isLoading="isLoading" @onSave="editUser" />
                </div>

                <div v-if="activeTab === 'appointments'">
                    <h2>Ваши записи</h2>
                    <UserAppointments :userId="user?.id"/>
                </div>
                <div v-if="activeTab === 'schedules'">
                    <h2>Ваше расписание</h2>
                    <ScheduleCalendar :userId="user?.id"/>
                </div>
                <div v-if="activeTab === 'prices'">
                    <h2>Ваши цены</h2>
                    <PriceListComponent :userId="user?.id"/>
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
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
}

.modal-actions button {
    margin: 10px;
}
.avatar-container {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid #ddd;
}
.avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
