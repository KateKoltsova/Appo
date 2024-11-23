<script setup>
import {onMounted, reactive, ref} from 'vue';
import {useRouter} from 'vue-router';
import {
    fetchUserById,
    updateUser,
    removeUser,
    logout,
    logoutAll,
    uploadAvatar,
    removeAvatar,
    fetchGallery,
    fetchGalleryById,
    uploadGallery,
    removeGallery
} from "../services/UserService.js";
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
const gallery = ref([]);
const showGalleryModal = ref(false);
const showImageModal = ref(false);
const selectedImageIndex = ref(0);
const galleryImages = ref([]);
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
    await fetchUserGallery();
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

        const {x, y, width, height} = cropperData;

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
                console.error('Ошибка при получении данных пользователя');
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

const deleteUser = async () => {
    try {
        const confirmed = confirm('Вы уверены, что хотите удалить свой профиль? Это действие нельзя отменить!');
        if (!confirmed) return;
        isLoading.value = true;
        const response = await removeUser(userId);
        if (response.status === 200) {
            localStorage.clear();
        } else {
            console.error('Ошибка удаления пользователя');
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
    } finally {
        isLoading.value = false;
    }
}

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

const fetchUserGallery = async () => {
    isLoading.value = true;
    try {
        const response = await fetchGallery(userId);
        if (response.status === 200) {
            gallery.value = response.data.data;
        } else {
            console.error("Ошибка загрузки галереи");
        }
    } catch (error) {
        console.error("Ошибка сети:", error);
    } finally {
        isLoading.value = false;
    }
};

const openGalleryModal = () => {
    showGalleryModal.value = true;
};

const closeGalleryModal = () => {
    showGalleryModal.value = false;
};

const onGalleryFileChange = (event) => {
    galleryImages.value = Array.from(event.target.files);
};

const saveGalleryImage = async () => {
    if (!galleryImages.value) return;
    try {
        isLoading.value = true;
        const response = await uploadGallery(userId, galleryImages.value);
        if (response.status === 200) {
            console.log("Изображение добавлено в галерею");
            await fetchUserGallery();
        } else {
            console.error("Ошибка загрузки изображения");
        }
    } catch (error) {
        console.error("Ошибка сети:", error);
    } finally {
        galleryImages.value = [];
        isLoading.value = false;
        closeGalleryModal();
    }
};

const deleteGalleryImage = async (imageId) => {
    try {
        isLoading.value = true;
        const response = await removeGallery(userId, imageId);
        if (response.status === 200) {
            console.log("Изображение удалено из галереи");
            await fetchUserGallery();
        } else {
            console.error("Ошибка загрузки изображения");
        }
    } catch (error) {
        console.error('Ошибка при удалении изображения:', error);
        alert('Не удалось удалить изображение.');
    } finally {
        isLoading.value = false;
        closeGalleryModal();
    }
}

const openImageModal = (index) => {
    selectedImageIndex.value = index;
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
};

const nextImage = () => {
    selectedImageIndex.value =
        (selectedImageIndex.value + 1) % gallery.value.length;
};

const prevImage = () => {
    selectedImageIndex.value =
        (selectedImageIndex.value - 1 + gallery.value.length) % gallery.value.length;
};
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
                    <template v-if="user.role === 'master'">
                    <li :class="{ active: activeTab === 'schedules' }"
                        @click="selectTab('schedules')">
                        Расписание
                    </li>
                    <li :class="{ active: activeTab === 'prices' }"
                        @click="selectTab('prices')">
                        Цены
                    </li>
                    <li :class="{ active: activeTab === 'gallery' }"
                        @click="selectTab('gallery')">
                        Галерея
                    </li>
                    </template>
                </ul>
            </nav>
            <div class="tab-content">
                <div v-if="activeTab === 'profile'">
                    <h2>Hello, user {{ user.id }} {{ editedUser?.firstname }} {{ editedUser?.lastname }}</h2>
                    <button @click="deleteUser()">Удалить профиль</button>
                    <button @click="userLogout()">Выйти</button>
                    <button @click="userLogoutAll()">Выйти со всех устройств</button>

                    <div class="avatar-container" @click="openModal">
                        <img v-if="user.image_url" :src="user.image_url" alt="User Avatar" class="avatar"/>
                        <div v-else class="avatar-placeholder">+</div>
                    </div>

                    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
                        <div class="modal-content">
                            <h3>Редактирование аватарки</h3>
                            <input type="file" accept="image/*" @change="onFileChange"/>
                            <Cropper v-if="avatar" :src="avatar"
                                     :stencil-props="{ aspectRatio: 1, movable: true, scalable: true }"
                                     @change="onCropChange"/>

                            <div class="modal-actions">
                                <button @click="saveCroppedImage">Сохранить</button>
                                <button @click="closeModal">Отмена</button>
                            </div>
                        </div>
                    </div>

                    <UserForm :editedUser="editedUser" :isLoading="isLoading" @onSave="editUser"/>
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
                <div v-if="activeTab === 'gallery'">
                    <div class="gallery-header">
                        <button @click="openGalleryModal">Загрузить изображение</button>
                    </div>
                    <div class="gallery-grid">
                        <div v-for="(image, index) in gallery" :key="image.id" class="gallery-item"
                             @click="openImageModal(index)">
                            <img :src="image.image_url" alt="Gallery Image"/>
                            <button class="delete-button" @click.stop="deleteGalleryImage(image.id)">×</button>
                        </div>
                    </div>

                    <div v-if="showGalleryModal" class="modal-overlay" @click.self="closeGalleryModal">
                        <div class="modal-content">
                            <h3>Загрузить изображение</h3>
                            <input type="file" multiple accept="image/*" @change="onGalleryFileChange"/>
                            <button @click="saveGalleryImage">Сохранить</button>
                            <button @click="closeGalleryModal">Отмена</button>
                        </div>
                    </div>

                    <div v-if="showImageModal" class="modal-overlay" @click.self="closeImageModal">
                        <div class="modal-content">
                            <img :src="gallery[selectedImageIndex]?.image_url" alt="Gallery Image"/>
                            <button class="prev-button" @click="prevImage">←</button>
                            <button class="next-button" @click="nextImage">→</button>
                        </div>
                    </div>
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

.gallery-header {
    margin-bottom: 20px;
    text-align: right;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
}

.gallery-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    cursor: pointer;
}

.modal-content img {
    max-width: 100%;
    max-height: 80vh;
}

.prev-button,
.next-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 2rem;
    cursor: pointer;
}

.prev-button {
    left: 10px;
}

.next-button {
    right: 10px;
}

.gallery-item {
    position: relative;
}

.delete-button {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: red;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 16px;
    cursor: pointer;
}

.delete-button:hover {
    background-color: darkred;
}
</style>
