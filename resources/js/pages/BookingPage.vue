<script setup>
import {ref, watch, onMounted} from "vue";
import DateCarousel from "../components/DateCarousel.vue";
import {getAvailableSchedules} from "../services/ScheduleService.js";
import {getAll} from "../services/ServiceService.js";
import LoadingSpinner from "../components/LoadingSpinner.vue";
import {AvailableScheduleModel} from "../models/AvailableScheduleModel.js";
import {add} from "../services/CartService.js";
import ProfileModal from "../components/ProfileModal.vue";

const isLoading = ref(false);

const apiCategories = ref([]);
const apiServices = ref([]);

const apiSchedules = ref([]);
const availableSchedules = ref([]);

const selectedDate = ref(null);
const selectedCategories = ref([]);
const selectedService = ref(null);

const isModalOpen = ref(false);
const selectedMaster = ref(null);

onMounted(async () => {
    getServices();
});

const getServices = async () => {
    try {
        isLoading.value = true;
        const response = await getAll(selectedCategories.value);
        if (response.status === 200) {
            const data = response.data.data;
            apiCategories.value = data.categories;
            apiServices.value = data.services;
        } else {
            console.error("Ошибка получения услуг", error);
        }
    } catch (error) {
        console.error("Ошибка получения услуг:", error);
    }
    isLoading.value = false;
};

const removeCategory = (category) => {
    selectedCategories.value = selectedCategories.value.filter(
        (c) => c !== category
    );
};

watch(selectedCategories, (newValue, oldValue) => {
    if (newValue !== oldValue && selectedDate.value) {
        getServices();
        handleDateSelection(selectedDate.value);
    }
});

watch(selectedService, (newValue, oldValue) => {
    if (newValue !== oldValue && selectedDate.value) {
        handleDateSelection(selectedDate.value);
    }
});

const handleDateSelection = async (date) => {
    selectedDate.value = date;
    try {
        isLoading.value = true;
        const response = await getAvailableSchedules(
            selectedDate.value,
            selectedCategories.value,
            selectedService.value
        );
        if (response.status === 200) {
            const data = await response.data.data;
            apiSchedules.value = data.flatMap((master) => {
                return master.schedules.map((schedule) => {
                    return Object.assign({}, AvailableScheduleModel, {
                        schedule_id: schedule.schedule_id,
                        date_time: schedule.date_time,
                        status: schedule.status,
                        master_id: master.master_id,
                        master_firstname: master.master_firstname,
                        master_lastname: master.master_lastname,
                        master_image: master.master_image,
                        prices: master.prices[0],
                    });
                });
            });
            availableSchedules.value = apiSchedules.value.sort((a, b) => {
                return new Date(a.date_time) - new Date(b.date_time);
            });
        } else {
            console.error("Ошибка получения доступного расписания", error);
        }
    } catch (error) {
        console.error("Ошибка получения расписаний:", error);
    }
    isLoading.value = false;
};

// Функция для форматирования времени
const formatTime = (dateTime) => {
    return new Date(dateTime).toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Функция для добавления в корзину
const addToCart = async (schedule) => {
    let item = {
        schedule_id: schedule.schedule_id,
        service_id: schedule.prices.service_id,
        price_id: schedule.prices.price_id,
    };
    const userId = localStorage.getItem("userId");
    if (userId != null) {
        const response = await add(userId, JSON.stringify(item));
        if (response.status !== 200) {
            addToStorage(item);
        }
    } else {
        addToStorage(item);
    }

    console.log(
        `Добавлено в корзину: Расписание ID ${item.schedule_id}, Услуга ID ${item.service_id}, Цена ID ${item.price_id}`
    );
};

const addToStorage = (item) => {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const isItemInCart = cart.some(
        (cartItem) => cartItem.schedule_id === item.schedule_id
    );
    if (!isItemInCart) {
        cart.push(item);
        localStorage.setItem("cart", JSON.stringify(cart));
    } else {
        console.log("Это время уже добавлено в корзину");
    }
}

const handleAvatarClick = (masterId) => {
    selectedMaster.value = masterId;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedMaster.value = null;
};
</script>

<template>
    <div>
        <DateCarousel @dateSelection="handleDateSelection"/>

        <div class="selected-categories">
      <span
          v-for="category in selectedCategories"
          :key="category"
          class="category-card"
      >
        {{ category }}
        <button @click="removeCategory(category)" class="remove-button">
          ✖
        </button>
      </span>
        </div>

        <div class="category-list">
            <h3>Выберите категории услуг:</h3>
            <div class="checkbox-list">
                <label
                    v-for="category in apiCategories"
                    :key="category"
                    class="checkbox-label"
                >
                    <input
                        type="checkbox"
                        v-model="selectedCategories"
                        :value="category"
                        @change=""
                    />
                    {{ category }}
                </label>
            </div>
        </div>

        <div class="service-list">
            <h3>Выберите услугу:</h3>
            <div class="radio-list">
                <label
                    v-for="service in apiServices"
                    :key="service.id"
                    class="radio-label"
                >
                    <input type="radio" v-model="selectedService" :value="service.id"/>
                    {{ service.title }}
                </label>
            </div>
        </div>

        <LoadingSpinner :isLoading="isLoading"/>
        <div v-if="availableSchedules.length">
            <div
                v-for="schedule in availableSchedules"
                :key="schedule.schedule_id"
                class="card"
            >
                <div class="card-container">
                    <div class="card-header">
                        <div
                            class="user-icon-wrapper"
                            @click="handleAvatarClick(schedule.master_id)"
                        >
                            <img
                                v-if="schedule.master_image"
                                :src="schedule.master_image"
                                alt="Avatar"
                                class="user-icon"
                            />
                            <i v-else class="fa-regular fa-circle-user user-icon"></i>
                        </div>
                        <div class="master-info">
                            <h2 class="first-name">{{ schedule.master_firstname }}</h2>
                            <h3 class="last-name">{{ schedule.master_lastname }}</h3>
                        </div>
                        <div class="schedule">
                            <span class="time">{{ schedule.date_time }}</span>
                        </div>
                    </div>
                    <span v-if="selectedService"> {{ schedule.prices.price }} грн. </span>
                    <button @click="addToCart(schedule)">Добавить в корзину</button>
                    <ProfileModal
                        v-if="isModalOpen"
                        :masterId="selectedMaster"
                        @close="closeModal"
                    />
                </div>
            </div>
        </div>

        <div v-else>
            <p>No schedules available for the selected date.</p>
        </div>
    </div>
</template>

<style scoped>
.selected-categories {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 10px 0;
}

.category-card {
    background-color: #4caf50;
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    display: flex;
    align-items: center;
}

.remove-button {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    margin-left: 5px;
}

.category-list {
    margin-top: 10px;
}

.checkbox-list,
.radio-list {
    max-height: 20px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px;
    background-color: white;
}

.checkbox-label,
.radio-label {
    display: block;
    padding: 10px;
    cursor: pointer;
}

.checkbox-label:hover,
.radio-label:hover {
    background-color: #f1f1f1;
}

.card-container {
    display: flex;
    align-items: center;
    gap: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 20px;
    width: 100%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.card-header {
    display: flex;
    align-items: center;
}

.card-header i {
    font-size: 50px;
}

.user-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 10px;
}

.master-info {
    flex-grow: 1;
}

.first-name {
    font-size: 1.2em;
    margin: 0;
}

.last-name {
    font-size: 1em;
    margin: 0;
}

.schedule {
    font-size: 1.5em;
    margin-left: 10px;
}

.service-selection {
    margin-top: 15px;
}
</style>
