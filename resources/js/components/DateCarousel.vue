<script setup>
import {ref, onMounted, computed} from "vue";
import {defineEmits, defineComponent} from "vue";
import {useCalendar} from "../services/Calendar.js";
import CalendarModal from "./CalendarModal.vue";

const emit = defineEmits(["dateSelection"]);
const {selectedDate, displayedDates, getDateWithOffset} = useCalendar();
const today = new Date();
const isModalOpen = ref(false);

onMounted(() => {
  selectDate(today, 0);
});

// Выбор даты
const selectDate = (date, offset) => {
  date.value = new Date(date);
  const { newDate, days } = getDateWithOffset(date.value, offset);
  selectedDate.value = newDate;
  displayedDates.value = days;
  closeCalendarModal();
  emit("dateSelection", selectedDate.value);
};

const isPastDate = (date) => {
  date.value = new Date(date);
  return date.getTime() < today.setHours(0, 0, 0, 0);
};

const openCalendarModal = () => {
  isModalOpen.value = true;
};

const closeCalendarModal = () => {
  isModalOpen.value = false;
};

const monthAndYear = computed(() => {
  return selectedDate.value.toLocaleDateString("ru-RU", {
    month: "long",
    year: "numeric",
  });
});
</script>

<template>
  <button class="open-calendar-button" @click="openCalendarModal">
    {{ monthAndYear }}
  </button>
  <CalendarModal
    :isOpen="isModalOpen"
    :selectedDate="selectedDate"
    @close="closeCalendarModal"
    @dateSelected="selectDate"
  />

  <div class="date-carousel">
    <button @click="selectDate(selectedDate, -1)">&lt;</button>

    <div class="dates">
      <div
        v-for="date in displayedDates"
        :key="date"
        class="date-card"
        :class="{
          active: date.getTime() === selectedDate.getTime(),
          disabled: isPastDate(date),
        }"
        @click="selectDate(date)"
      >
        <div class="day-name">
          {{ date.toLocaleDateString("ru-RU", { weekday: "short" }) }}
        </div>
        <div class="day-number">{{ date.getDate() }}</div>
        <div class="month-name">
          {{ date.toLocaleDateString("ru-RU", { month: "short" }) }}
        </div>
      </div>
    </div>

    <button @click="selectDate(selectedDate, 1)">&gt;</button>
  </div>
</template>

<style scoped>
.open-calendar-button {
  margin-bottom: 20px;
  padding: 10px;
  font-size: 16px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  width: 100%;
}

.date-selector {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.month-button {
  margin-bottom: 20px;
  padding: 10px;
  font-size: 16px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.date-carousel {
  display: flex;
  align-items: center;
}

.dates {
  display: flex;
  justify-content: center;
}

.date-card {
  margin: 0 10px;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  text-align: center;
  cursor: pointer;
}

.date-card.active {
  background-color: #007bff;
  color: white;
}

.date-card.disabled {
  pointer-events: none;
  opacity: 0.5;
}
</style>
