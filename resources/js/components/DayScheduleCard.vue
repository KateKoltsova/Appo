<script setup>
import { ref, onMounted } from 'vue';
import { fetchDaySchedule, addDaySchedule, removeDaySchedule } from '../services/ScheduleService.js';
import ScheduleAppointmentDetails from './ScheduleAppointmentDetails.vue';

const props = defineProps(['day', 'userId']);
const schedules = ref([]);

const fetchSchedules = async () => {
    const response = await fetchDaySchedule(props.userId, props.day.date);
    schedules.value = response.data.schedules;
};

const addSchedule = async () => {
    await addDaySchedule(props.userId, props.day.date);
    fetchSchedules();
};

const removeSchedule = async (scheduleId) => {
    await removeDaySchedule(scheduleId);
    fetchSchedules();
};

const toggleAppointmentDetails = (schedule) => {
    schedule.showDetails = !schedule.showDetails;
};

onMounted(fetchSchedules);
</script>

<template>
    <div class="day-schedule">
        <div class="day-header">
            <h3>Расписание на {{ new Date(day.date).toLocaleDateString() }}</h3>
            <button @click="addSchedule">Добавить расписание</button>
        </div>

        <div class="schedule-list">
            <div class="schedule-item" v-for="schedule in schedules" :key="schedule.id">
                <span>{{ new Date(schedule.time).toLocaleTimeString() }}</span>
                <span>{{ schedule.isFree ? 'Свободно' : 'Занято' }}</span>
                <span v-if="!schedule.isFree" @click="toggleAppointmentDetails(schedule)">
                    Показать клиента
                </span>
                <button @click="removeSchedule(schedule.id)">❌</button>

                <!-- Подробности записи клиента -->
                <ScheduleAppointmentDetails v-if="schedule.showDetails" :appointment="schedule.appointment" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.day-schedule {
    border: 1px solid #ddd;
    padding: 20px;
    border-radius: 8px;
}

.day-header {
    display: flex;
    justify-content: space-between;
}

.schedule-list {
    margin-top: 20px;
}

.schedule-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}
</style>
