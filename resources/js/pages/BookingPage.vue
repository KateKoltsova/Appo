<script setup>
import { ref } from "vue";
import DateCarousel from "../components/DateCarousel.vue";
import { getSchedules } from "../services/ScheduleService.js";
import LoadingSpinner from "../components/LoadingSpinner.vue";
import { AvailableScheduleModel } from "../models/AvailableScheduleModel.js";
import { add } from "../services/CartService.js";

const isLoading = ref(false);

const availableSchedules = ref([]);
const avSchedules = ref([]);

const selectedService = ref({});
const selectedPrice = ref({});

const handleDateSelection = async (selectedDate) => {
  try {
    isLoading.value = true;
    const response = await getSchedules(selectedDate);
    if (response.status == 200) {
      const data = await response.data.data;
      avSchedules.value = data.flatMap((master) => {
        return master.schedules.map((schedule) => {
          return Object.assign({}, AvailableScheduleModel, {
            schedule_id: schedule.schedule_id,
            date_time: schedule.date_time,
            status: schedule.status,
            master_id: master.master_id,
            master_firstname: master.master_firstname,
            master_lastname: master.master_lastname,
            prices: master.prices,
          });
        });
      });
      availableSchedules.value = avSchedules.value.sort((a, b) => {
        return new Date(a.date_time) - new Date(b.date_time);
      });
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
const showPrice = (scheduleId) => {
  const selected = selectedService.value[scheduleId];
  const schedule = availableSchedules.value.find((s) => s.schedule_id === scheduleId);
  const priceInfo = schedule.prices.find((price) => price.price_id === selected);
  selectedPrice.value[scheduleId] = priceInfo ? priceInfo : undefined;
};

// Функция для добавления в корзину
const addToCart = (scheduleId) => {
  const serviceId = selectedPrice.value[scheduleId].service_id;
  const priceId = selectedPrice.value[scheduleId].price;

  let item = {
    schedule_id: scheduleId,
    service_id: serviceId,
    price_id: priceId
  };
  const userId = localStorage.getItem('userId');
  if (userId != null) {
    add(userId);
  } else {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const isItemInCart = cart.some(cartItem => cartItem.schedule_id === item.schedule_id);
    if (!isItemInCart) {
      cart.push(item);
      localStorage.setItem('cart', JSON.stringify(cart));
    } else {
        console.log('Это время уже добавлено в корзину');
    }
  }

  console.log(`Добавлено в корзину: Расписание ID ${scheduleId}, Услуга ID ${serviceId}, Цена ID ${priceId}`
  );
};
</script>

<template>
  <div>
    <DateCarousel @dateSelection="handleDateSelection" />
    <LoadingSpinner :isLoading="isLoading" />
    <div v-if="availableSchedules.length">
      <div v-for="schedule in availableSchedules" :key="schedule.schedule_id" class="card">
        <div class="card-container">
          <div class="card-header">
            <div class="master-info">
              <h2 class="first-name">{{ schedule.master_firstname }}</h2>
              <h3 class="last-name">{{ schedule.master_lastname }}</h3>
            </div>
            <div class="schedule">
              <span class="time">{{ schedule.date_time }}</span>
            </div>
          </div>
          <div class="service-selection">
            <label for="services">Выберите услугу:</label>
            <select v-model="selectedService[schedule.schedule_id]" @change="showPrice(schedule.schedule_id)">
              <option disabled value="">-- Выберите услугу --</option>
              <option v-for="price in schedule.prices" :key="price.price_id" :value="price.price_id">
                {{ price.title }}
              </option>
            </select>
            <span v-if="selectedPrice[schedule.schedule_id] !== undefined">
              {{ selectedPrice[schedule.schedule_id].price }} грн.
            </span>
            <button @click="addToCart(schedule.schedule_id)">
              Добавить в корзину
            </button>
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
