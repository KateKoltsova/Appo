<script setup>
import { ref, onMounted } from 'vue';
import { getSchedules } from '../services/ScheduleService.js';
import CalendarModal from './CalendarModal.vue';

const apiSchedules = ref([]);
const selectedDay = ref(new Date());
const calendarModalRef = ref(null);

const userId = localStorage.getItem('userId');

onMounted(async () => {
    await fetchSchedules();
});

const fetchSchedules = async () => {
    try {
        const response = await getSchedules(userId);
        if (response.status === 200) {
            apiSchedules.value = response.data;
        } else {
            console.error('Ошибка получения графика');
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
    }
};

const selectDay = (day) => {
    selectedDay.value = day;
};

const hasSchedule = (day) => {
    return apiSchedules.value.some(
        (schedule) =>
            new Date(schedule.date).toDateString() === new Date(day).toDateString() &&
            schedule.hasSchedule
    );
};

const hasAppointments = (day) => {
    return apiSchedules.value.some(
        (schedule) =>
            new Date(schedule.date).toDateString() === new Date(day).toDateString() &&
            schedule.hasAppointments
    );
};

const selectDateFromCalendar = (day) => {
    selectedDay.value = day;
    if (calendarModalRef.value) {
        calendarModalRef.value.closeModal();
    }
};
</script>

<template>
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
</template>

<style scoped>
.calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-day {
    padding: 10px;
    text-align: center;
    cursor: pointer;
}

.calendar-day .has-schedule {
    border: 2px solid blue; /* Стили для дней с расписанием */
}

.calendar-day .has-appointments {
    background-color: #ffcccc; /* Стили для дней с записями */
}

/* Стили для карточки календаря */
.calendar-card {
    margin-top: 20px; /* Отступ сверху */
    border: 1px solid #ccc; /* Граница карточки */
    border-radius: 8px; /* Закругление углов */
    padding: 20px; /* Отступ внутри карточки */
    background-color: #f9f9f9; /* Цвет фона карточки */
}
</style>
