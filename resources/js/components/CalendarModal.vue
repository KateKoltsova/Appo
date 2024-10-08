<script setup>
import {ref, computed, watch} from "vue";
import {useCalendar} from "../services/Calendar.js";

const props = defineProps({isOpen: Boolean, selectedDate: Date});
const emit = defineEmits(["close", "dateSelected"]);

const {selectedDate, getDateWithOffset} = useCalendar();
const today = new Date();
const currentMonth = ref(props.selectedDate.getMonth());
const currentYear = ref(props.selectedDate.getFullYear());
const isYearPickerOpen = ref(false); 
const years = ref([]);
const startYear = ref(currentYear.value);

// Массив дней для отображения в календаре
const monthDays = computed(() => {
  const firstDayOfMonth = new Date(currentYear.value, currentMonth.value, 1);
  const lastDayOfMonth = new Date(currentYear.value, currentMonth.value + 1, 0);

  // Добавляем дни в начале месяца
  const missingDays = (firstDayOfMonth.getDay() + 6) % 7;

  const days = [];

  for (let i = 0; i < missingDays; i++) {
    days.push(
      new Date(
        firstDayOfMonth.getFullYear(),
        firstDayOfMonth.getMonth(),
        1 - missingDays + i
      )
    );
  }

  for (
    let day = new Date(firstDayOfMonth);
    day <= lastDayOfMonth;
    day.setDate(day.getDate() + 1)
  ) {
    days.push(new Date(day));
  }

  // Добавляем дни в конце месяца
  const totalDaysInWeek = 7;
  const totalDaysDisplayed = days.length;
  const daysToAdd = totalDaysInWeek - (totalDaysDisplayed % totalDaysInWeek);

  if (daysToAdd < totalDaysInWeek) {
    for (let i = 1; i <= daysToAdd; i++) {
      days.push(
        new Date(
          lastDayOfMonth.getFullYear(),
          lastDayOfMonth.getMonth(),
          lastDayOfMonth.getDate() + i
        )
      );
    }
  }

  return days;
});

const monthName = computed(() => {
  return new Date(currentYear.value, currentMonth.value).toLocaleDateString(
    "ru-RU",
    { month: "long" }
  );
});

const yearName = computed(() => {
  return currentYear.value;
});

const createYearArray = () => {
  years.value = [];
  for (let i = startYear.value; i <= startYear.value + 3; i++) {
    years.value.push(i);
  }
};

const selectYear = (year) => {
  currentYear.value = year;
  createYearArray();
  isYearPickerOpen.value = false;
};

watch(
  () => props.selectedDate,
  (newDate) => {
    currentMonth.value = newDate.getMonth();
    currentYear.value = newDate.getFullYear();
  },
  createYearArray()
);

const selectDay = (day) => {
  if (day >= today) {
    // props.selectedDate.value = new Date(day.value);
    emit("dateSelected", day);
    emit("close");
  }
};

const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
  createYearArray();
};

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
  createYearArray();
};

const closeModal = () => {
  emit("close");
};
</script>

<template>
  <div v-if="props.isOpen" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="calendar-header">
        <button class="month-button" @click="previousMonth">&lt;</button>
        <h2>{{ monthName }}</h2>
        <div class="year-picker" @click.stop>
          <span @click="isYearPickerOpen = !isYearPickerOpen">{{
            yearName
          }}</span>
          <div v-if="isYearPickerOpen" class="year-scroll">
            <div class="year-options">
              <div v-for="year in years" :key="year" @click="selectYear(year)">
                {{ year }}
              </div>
            </div>
          </div>
        </div>
        <button class="month-button" @click="nextMonth">&gt;</button>
      </div>

      <div class="calendar-grid">
        <div
          v-for="day in monthDays"
          :key="day"
          class="calendar-day"
          :class="{
            selected:
              day.getDate() === props.selectedDate.getDate() &&
              day.getMonth() === currentMonth &&
              day.getFullYear() === currentYear,
            disabled:
              day.getTime() < today.setHours(0, 0, 0, 0) ||
              day.getMonth() !== currentMonth,
          }"
          @click="selectDay(day)"
        >
          {{ day.getDate() }}
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 10px;
  width: 300px;
  text-align: center;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.month-button {
  padding: 10px;
  font-size: 16px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
  margin-bottom: 20px;
}

.calendar-day {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  cursor: pointer;
}

.calendar-day.selected {
  background-color: #007bff;
  color: white;
}

.calendar-day.disabled {
  pointer-events: none;
  opacity: 0.5;
}

.close-button {
  margin-top: 10px;
  padding: 10px;
  font-size: 16px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.year-picker {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
}

.year-scroll {
  position: absolute;
  background: white;
  border: 1px solid #ccc;
  border-radius: 5px;
  overflow-y: auto;
  max-height: 150px;
  z-index: 10;
  width: 100%;
  margin-top: 5px;
}

.year-options {
  display: flex;
  flex-direction: column;
}

.year-options div {
  padding: 5px;
  cursor: pointer;
}

.year-options div:hover {
  background-color: #f0f0f0;
}
</style>
