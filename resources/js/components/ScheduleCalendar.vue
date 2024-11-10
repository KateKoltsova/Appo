<script setup>
import {ref, onMounted, computed} from 'vue';
import {fetchDaySchedule} from "../services/ScheduleService.js";
import LoadingSpinner from "./LoadingSpinner.vue";

const userSchedules = ref([]);
const isLoading = ref(false);
const selectedDaySchedules = ref([]);
const selectedDate = ref(new Date());
const today = new Date();

const props = defineProps({
    userId: {
        type: [Number, String],
        required: true,
    }
});
const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());

onMounted(async () => {
    selectedDate.value = today;
    await fetchUserAppointments(props.userId);
});

const fetchUserAppointments = async (userId, date = null) => {
    try {
        isLoading.value = true;
        if (date != null) {
            selectedDate.value = new Date(date);
        }
        const response = await fetchDaySchedule(userId, selectedDate.value);
        if (response.status === 200) {
            userSchedules.value = response.data.data;
            selectedDaySchedules.value = date ? response.data.data : [];
        }
    } catch (error) {
        console.error("Ошибка получения графика", error);
    } finally {
        isLoading.value = false;
    }
};

// Массив дней месяца для отображения в календаре
const monthDays = computed(() => {
    const days = [];
    const firstDayOfMonth = new Date(currentYear.value, currentMonth.value, 1);
    const lastDayOfMonth = new Date(currentYear.value, currentMonth.value + 1, 0);

    // Добавляем пустые дни для выравнивания начала недели
    const startDay = (firstDayOfMonth.getDay() + 6) % 7;
    for (let i = 0; i < startDay; i++) {
        days.push(null);
    }

    // Добавляем все дни месяца
    for (let day = 1; day <= lastDayOfMonth.getDate(); day++) {
        days.push(new Date(currentYear.value, currentMonth.value, day));
    }
    return days;
});

// Обработка выбора дня в календаре
const selectDay = async (day) => {
    if (day && day >= today) {
        selectedDate.value = day;
        await fetchUserAppointments(props.userId, day);
    }
};
</script>

<template>

    <LoadingSpinner :isLoading="isLoading"/>
    <div class="calendar-container">
        <!-- Календарь -->
        <div class="calendar-grid">
            <div v-for="day in monthDays" :key="day" class="calendar-day"
                 :class="{
                    selected: selectedDate && day && day.toDateString() === selectedDate.toDateString(),
                    disabled: day && day < today
                 }"
                 @click="day && selectDay(day)">
                <span>{{ day ? day.getDate() : '' }}</span>
            </div>
        </div>

        <!-- Карточка расписания для выбранного дня -->
        <div v-if="selectedDate" class="day-schedule-card">
            <h3>Расписание на {{ selectedDate.toLocaleDateString() }}</h3>
            <ul>
                <li v-for="schedule in selectedDaySchedules" :key="schedule.id">
                    <p>Время: {{ schedule.date_time }}</p>
                    <p>Статус: {{ schedule.status }}</p>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.calendar-container {
    display: flex;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
    width: 60%;
}

.calendar-day {
    padding: 10px;
    text-align: center;
    cursor: pointer;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.calendar-day.selected {
    background-color: #007bff;
    color: #fff;
}

.calendar-day.disabled {
    opacity: 0.5;
    cursor: default;
}

.day-schedule-card {
    width: 40%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-left: 20px;
}
</style>
