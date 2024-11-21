<script setup>
import {ref, onMounted} from "vue";
import {getUserAppointments, cancelAppointment} from "../services/AppointmentService";
import LoadingSpinner from "./LoadingSpinner.vue";

const userAppointments = ref([]);
const isLoading = ref(false);

const props = defineProps({
    userId: {
        type: [Number, String],
        required: true,
    }
});

onMounted(async () => {
    await fetchUserAppointments(props.userId);
});

const fetchUserAppointments = async (userId) => {
    try {
        isLoading.value = true;
        const response = await getUserAppointments(userId);
        if (response.status === 200) {
            userAppointments.value = response.data.data;
        }
    } catch (error) {
        console.error("Ошибка получения записей:", error);
    } finally {
        isLoading.value = false;
    }
}

const deleteUserAppointment = async (userId, appointmentId) => {
    try {
        isLoading.value = true;
        const response = await cancelAppointment(userId, appointmentId);
        if (response.status === 200) {
            await fetchUserAppointments(userId);
        }
    } catch (error) {
        console.error("Ошибка отмены записи:", error);
    } finally {
        isLoading.value = false;
    }
}
</script>

<template>
    <LoadingSpinner :isLoading="isLoading"/>
    <div>
        <ul>
            <li v-for="appointment in userAppointments" :key="appointment.id">
                <div class="appointment-card">
                    <p>Дата: {{ appointment.date_time }}</p>
                    <p>Мастер: {{ appointment.master_firstname }} {{ appointment.master_lastname }}</p>
                    <p>Услуга: {{ appointment.title }}</p>
                    <p>Оплачено: {{ appointment.paid_sum }} ГРН</p>
                    <p>Стоимость: {{ appointment.sum }} ГРН</p>
                    <button @click="deleteUserAppointment(props.userId, appointment.id)">Удалить</button>
                </div>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.appointment-card {
    border: 1px solid #ddd;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    background-color: #f9f9f9;
}

ul {
    list-style: none;
    padding: 0;
}

li {
    margin-bottom: 15px;
}

.appointment-card button {
    margin-top: 10px;
    padding: 5px;
    border: none;
    background-color: #007bff;
    color: #fff;
    border-radius: 4px;
    cursor: pointer;
}

.appointment-card button:last-of-type {
    background-color: #ddd;
    color: #333;
}
</style>
