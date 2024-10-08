<script setup>
import {ref} from "vue";
import DateCarousel from "../components/DateCarousel.vue";
import {getSchedules} from "../services/ScheduleService.js";
import LoadingSpinner from "../components/LoadingSpinner.vue";

const isLoading = ref(false);
const schedules = ref([]);
const masters = ref([]);
const selectedService = ref({});
const selectedPrice = ref({});

const handleDateSelection = async (selectedDate) => {
  try {
    isLoading.value = true;
    const response = await getSchedules(selectedDate);
    if (response.status == 200) {
        const data = await response.data.data;
        masters.value = data;
    } else {
        console.error('Ошибка получения доступного расписания', error);
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

// Функция для обновления цены
const updatePrice = (masterId) => {
  const selected = selectedService.value[masterId];
  const master = masters.value.find((m) => m.master_id === masterId);
  const priceInfo = master.prices.find((price) => price.title === selected);
  selectedPrice.value[masterId] = priceInfo ? priceInfo.price : undefined;
};

// Функция для добавления в корзину
const addToCart = (masterId) => {
  console.log(
    `Добавлено в корзину: Мастер ID ${masterId}, Услуга ${selectedService.value[masterId]}`
  );
};
</script>

<template>
  <div>
    <DateCarousel @dateSelection="handleDateSelection" />
    <LoadingSpinner :isLoading="isLoading" />
    <div v-if="masters.length">
      <div v-for="master in masters" :key="master.master_id">
        <div
          v-for="schedule in master.schedules"
          :key="schedule.schedule_id"
          class="card"
        >
          <div class="card-container">
            <div class="card-header">
              <div class="master-info">
                <h2 class="first-name">{{ master.master_firstname }}</h2>
                <h3 class="last-name">{{ master.master_lastname }}</h3>
              </div>
              <div class="schedule">
                <span class="time">{{ schedule.date_time }}</span>
              </div>
            </div>
            <div class="service-selection">
              <label for="services">Выберите услугу:</label>
              <select
                v-model="selectedService[master.master_id]"
                @change="updatePrice(master.master_id)"
              >
                <option disabled value="">-- Выберите услугу --</option>
                <option
                  v-for="price in master.prices"
                  :key="price.price_id"
                  :value="price.title"
                >
                  {{ price.title }}
                </option>
              </select>
              <span v-if="selectedPrice[master.master_id] !== undefined">
                {{ selectedPrice[master.master_id] }} грн.
              </span>
              <button @click="addToCart(master.master_id)">
                Добавить в корзину
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else>
      <p>No schedules available for the selected date.</p>
    </div>
  </div>
</template>

<style scoped>
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
