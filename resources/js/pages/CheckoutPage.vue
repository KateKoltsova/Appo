<script setup>
import {ref, onMounted, watch, nextTick} from "vue";
import {useRoute} from "vue-router";
import {checkout, payButton} from "../services/CartService";
import {paymentProcess} from "../services/AppointmentService";
import LoadingSpinner from "../components/LoadingSpinner.vue";

const isLoading = ref(false);
const orderData = ref({
    items: [],
    totalCount: 0,
    totalSum: { full: 0, prepayment: 0 }
});
const paymentType = ref('full');
const paymentButton = ref('');
const orderId = ref(null);
const userId = localStorage.getItem("userId");
const route = useRoute();

onMounted(async () => {
    await loadOrderData();
    await generatePaymentButton();
});

const loadOrderData = async () => {
    try {
        isLoading.value = true;
        const response = await checkout(userId);
        orderData.value = response.data.data;
    } catch (error) {
        console.error("Ошибка загрузки данных о заказе:", error);
    }
    isLoading.value = false;
};

const generatePaymentButton = async (paymentType) => {
    try {
        isLoading.value = true;
        const response = await payButton(userId, paymentType);
        paymentButton.value = response.data.data.html_button;
        orderId.value = response.data.data.order_id;

        await nextTick(() => {
            const formElement = document.querySelector('form[action="https://www.liqpay.ua/api/3/checkout"]');
            if (formElement) {
                formElement.removeEventListener('submit', handlePaymentSubmit);
                formElement.addEventListener('submit', handlePaymentSubmit);
            }
        });
    } catch (error) {
        console.error("Ошибка при генерации кнопки оплаты:", error);
    }
    isLoading.value = false;
};

watch(paymentType, (newPaymentType) => {
    generatePaymentButton(newPaymentType);
});

const handlePaymentSubmit = async (event) => {
    event.preventDefault();
    try {
        const response = await sendUserAppointmentsRequest();
        if (response.status === 200) {
            event.target.submit();
        }        
    } catch (error) {
        console.error('Ошибка при получении записей пользователя', error);
    }
};

const sendUserAppointmentsRequest = async () => {
    try {
        return await paymentProcess(userId, orderId.value);
    } catch (error) {
        console.error('Ошибка при получении записей пользователя', error);
    }
};
</script>

<template>
    <LoadingSpinner :isLoading="isLoading"/>
    <div class="checkout-page">
        <h2>Оформление заказа</h2>

        <ul v-if="orderData.items.length">
            <li v-for="item in orderData.items" :key="item.id">
                <h3>{{ item.title }}</h3>
                <p>Дата и время: {{ item.date_time }}</p>
                <p>Мастер: {{ item.master_firstname }} {{ item.master_lastname }}</p>
                <p>Цена: {{ item.price }} ГРН</p>
            </li>
        </ul>

        <div class="order-summary">
            <p>Общее количество: {{ orderData.totalCount }}</p>
            <p>Итоговая сумма: {{ orderData.totalSum.full }} ГРН</p>
        </div>

        <div class="payment-options">
            <label>
                <input type="radio" v-model="paymentType" value="full"/> Полная оплата
            </label>
            <label>
                <input type="radio" v-model="paymentType" value="prepayment"/>
                Предоплата ({{ orderData.totalSum.prepayment }} ГРН)
            </label>
        </div>

        <div v-html="paymentButton"></div>
    </div>
</template>

<style scoped>
/* Стили для оформления страницы */
.checkout-page {
    padding: 20px;
}

.order-summary,
.payment-options {
    margin-top: 20px;
}

.error-item {
    background-color: #f8d7da;
}

.error-message {
    color: red;
}
</style>