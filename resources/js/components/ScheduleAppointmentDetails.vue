<template>
    <div class="appointment-details">
        <p>Клиент: {{ appointment.firstname }} {{ appointment.lastname }}</p>
        <p>Услуга: {{ appointment.title }}</p>
        <p>Стоимость: {{ appointment.sum }}</p>
        <p>Оплачено: {{ appointment.paid_sum }}</p>
    </div>
</template>

<script>
export default {
    props: ['appointment'],
};
</script>

<style scoped>
.appointment-details {
    background-color: #f9f9f9;
    padding: 10px;
    margin-top: 10px;
    border-radius: 5px;
}
</style>
