<script setup>
import {ref, onMounted, computed} from "vue";
import {fetchDaySchedule, updateDaySchedule, removeDaySchedule, removeDayScheduleAppointment} from "../services/ScheduleService.js";
import LoadingSpinner from "./LoadingSpinner.vue";

const userSchedules = ref([]);
const isLoading = ref(false);
const selectedDaySchedules = ref([]);
const selectedDate = ref(new Date());
const today = new Date();

const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());

const isEditModalVisible = ref(false);
const scheduleToEdit = ref(null);
const newTime = ref(new Date());

const contextMenuVisible = ref(false);
const scheduleWithInfo = ref(null);

const props = defineProps({
    userId: {
        type: [Number, String],
        required: true,
    }
});

onMounted(async () => {
    selectedDate.value = today;
    await fetchUserSchedules(props.userId);
});

const fetchUserSchedules = async (userId, date = null) => {
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

const editSchedule = async () => {
    try {
        if (scheduleToEdit.value && newTime.value) {
            isLoading.value = true;
            const updatedDate = selectedDate;
            const [hours, minutes] = newTime.value.split(":").map(Number);
            updatedDate.value.setHours(hours, minutes, 0, 0);

            const response = await updateDaySchedule(props.userId, scheduleToEdit.value.schedule_id, updatedDate.value);
            if (response.status === 200) {
                await fetchUserSchedules(props.userId, selectedDate.value);
            }
        }
    } catch (error) {
        console.error("Ошибка редактирования графика", error);
    } finally {
        closeEditModal();
        isLoading.value = false;
    }
};

const deleteSchedule = async (userId, scheduleId) => {
    try {
        isLoading.value = true;
        const response = await removeDaySchedule(userId, scheduleId);
        if (response.status === 200) {
            await fetchUserSchedules(userId, selectedDate.value);
        }
    } catch (error) {
        console.error("Ошибка удаления графика", error);
    } finally {
        isLoading.value = false;
    }
};

const deleteAppointment = async (userId, scheduleId) => {
    try {
        isLoading.value = true;
        const response = await removeDayScheduleAppointment(userId, scheduleId);
        if (response.status === 200) {
            await fetchUserSchedules(props.userId, selectedDate.value);
            closeContextMenu();
        }
    } catch (error) {
        console.error("Ошибка удаления записи", error);
    } finally {
        isLoading.value = false;
    }
};

const openEditModal = (schedule) => {
    scheduleToEdit.value = schedule;
    newTime.value = schedule.date_time;
    isEditModalVisible.value = true;
};

const closeEditModal = () => {
    isEditModalVisible.value = false;
    scheduleToEdit.value = null;
};

const openContextMenu = (schedule) => {
    scheduleWithInfo.value = schedule;
    contextMenuVisible.value = true;
};

const closeContextMenu = () => {
    contextMenuVisible.value = false;
    scheduleWithInfo.value = null;
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
        await fetchUserSchedules(props.userId, day);
    }
};
</script>

<template>
    <LoadingSpinner :isLoading="isLoading" />
    <div class="calendar-container">
        <!-- Календарь -->
        <div class="calendar-grid">
            <div v-for="day in monthDays" :key="day" class="calendar-day"
                :class="{
                    selected: selectedDate && day && day.toDateString() === selectedDate.toDateString(),
                    disabled: day && day < today.setHours(0, 0, 0, 0),
                }"
                @click="day && selectDay(day)">
                <span>{{ day ? day.getDate() : '' }}</span>
            </div>
        </div>

        <!-- Карточка расписания для выбранного дня -->
        <div v-if="selectedDate" class="day-schedule-card">
            <h3>Расписание на {{ selectedDate.toLocaleDateString() }}</h3>
            <div v-for="schedule in selectedDaySchedules" :key="schedule.schedule_id" class="schedule-card"
                :class="{
                    available: schedule.status === 'available',
                    unavailable: schedule.status === 'unavailable',
                }">
                <p>Время: {{ schedule.date_time }}</p>
                <p>Статус: {{ schedule.status }}</p>
                <span v-if="schedule.status === 'unavailable'" @click="openContextMenu(schedule)" class="info-icon">ℹ️</span>
                <div v-if="contextMenuVisible && scheduleWithInfo?.schedule_id === schedule.schedule_id" class="context-menu">
                    <button class="close-btn" @click="closeContextMenu()">×</button>
                    <h3>Информация о записи</h3>
                    <p><strong>Клиент:</strong>{{ scheduleWithInfo.appointment.firstname }} {{ scheduleWithInfo.appointment.lastname }}</p>
                    <p><strong>Телефон:</strong>{{ scheduleWithInfo.appointment.phone_number }}</p>
                    <p><strong>Услуга:</strong>{{ scheduleWithInfo.appointment.title }}</p>
                    <p><strong>Сумма:</strong>{{ scheduleWithInfo.appointment.sum }} грн</p>
                    <p><strong>Оплачено:</strong>{{ scheduleWithInfo.appointment.paid_sum }} грн</p>
                    <button @click="deleteAppointment(props.userId, scheduleWithInfo.schedule_id)">Удалить</button>
                    <button @click="closeContextMenu()">Закрыть</button>
                </div>
                <button @click="openEditModal(schedule)">Редактировать</button>
                <!-- <button @click="deleteSchedule(props.userId, schedule.schedule_id)">Удалить</button> -->
            </div>
        </div>

        <div v-if="isEditModalVisible" class="modal">
            <div class="modal-content">
                <h3>Редактировать время для {{ new Date(scheduleToEdit.date_time).toLocaleDateString() }}</h3>
                <input v-model="newTime" type="time" placeholder="Введите новое время" />
                <button @click="editSchedule()">Сохранить</button>
                <button @click="closeEditModal()">Отмена</button>
            </div>
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

.schedule-card {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin: 10px 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.schedule-card.available {
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.schedule-card.unavailable {
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.schedule-card button {
    padding: 5px 10px;
    cursor: pointer;
    border: none;
    border-radius: 3px;
    margin-top: 5px;
    background-color: #007bff;
    color: #fff;
}

.schedule-card button:hover {
    background-color: #0056b3;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 400px;
    width: 100%;
}

.modal-content input {
    width: 100%;
    padding: 8px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.modal-content button {
    padding: 8px 12px;
    margin-top: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.modal-content button:first-of-type {
    background-color: #007bff;
    color: #fff;
}

.modal-content button:last-of-type {
    background-color: #ddd;
}

.info-icon {
    cursor: pointer;
    font-size: 1.2em;
    margin-left: 10px;
}

.context-menu {
    position: absolute;
    top: 100%;
    left: 0;
    padding: 10px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    max-width: 250px;
}

.context-menu p {
    margin: 5px 0;
}

.context-menu button {
    margin-top: 10px;
    padding: 5px;
    border: none;
    background-color: #007bff;
    color: #fff;
    border-radius: 4px;
    cursor: pointer;
}

.context-menu button:last-of-type {
    background-color: #ddd;
    color: #333;
}
</style>
