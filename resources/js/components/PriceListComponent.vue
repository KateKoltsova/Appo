<script setup>
import { ref, onMounted } from "vue";
import { getPrices, addPrice, updatePrice, removePrice } from "../services/PriceService";
import { getAll } from "../services/ServiceService.js";
import LoadingSpinner from "./LoadingSpinner.vue";

const prices = ref([]);
const isLoading = ref(false);
const editingPriceId = ref(null);
const newPrice = ref(null);
const isCreateModalVisible = ref(false);
const apiServices = ref([]);
const serviceId = ref(null);
const price = ref(null);

const props = defineProps({
    userId: {
        type: [Number, String],
        required: true,
    }
});

onMounted(async () => {
    await fetchPrices(props.userId);
});

const getServices = async () => {
    try {
        isLoading.value = true;
        const response = await getAll();
        if (response.status === 200) {
            const data = response.data.data;
            apiServices.value = data.services;
        } else {
            console.error("Ошибка получения услуг", error);
        }
    } catch (error) {
        console.error("Ошибка получения услуг:", error);
    } finally {
        isLoading.value = false;
    }
};

const fetchPrices = async (userId) => {
    try {
        isLoading.value = true;
        const response = await getPrices(userId);
        if (response.status === 200) {
            prices.value = response.data.data;
        }
    } catch (error) {
        console.error("Ошибка получения цен:", error);
    } finally {
        isLoading.value = false;
    }
}

const createPrice = async (userId, serviceId, price) => {
    try {
        console.log(userId, serviceId, price);
        // if (price.value) {
            isLoading.value = true;
            const response = await addPrice(userId, serviceId, price);
            if (response.status === 200) {
                await fetchPrices(userId);
            }
        // }
    } catch (error) {
        console.error("Ошибка добавления цены:", error);
    } finally {
        closeCreateModal();
        isLoading.value = false;
    }
};

const setNewPrice = (priceId, currentPrice) => {
    editingPriceId.value = priceId;
    newPrice.value = currentPrice; // Установить текущую цену в поле редактирования
};

const editPrice = async (userId, priceId, price) => {
    try {
        isLoading.value = true;
        const response = await updatePrice(userId, priceId, price);
        if (response.status === 200) {
            await fetchPrices(userId);
        }
    } catch (error) {
        console.error("Ошибка изменения цены:", error);
    } finally {
        editingPriceId.value = null;
        newPrice.value = null;
        isLoading.value = false;
    }
}

const deletePrice = async (userId, priceId) => {
    try {
        isLoading.value = true;
        const response = await removePrice(userId, priceId);
        if (response.status === 200) {
            await fetchPrices(userId);
        }
    } catch (error) {
        console.error("Ошибка удаления цены:", error);
    } finally {
        isLoading.value = false;
    }
}

const openCreateModal = async () => {
    await getServices();
    isCreateModalVisible.value = true;
};

const closeCreateModal = () => {
    isCreateModalVisible.value = false;
};
</script>

<template>
    <LoadingSpinner :isLoading="isLoading" />
    <div>
        <button @click="openCreateModal()" class="add-price-btn">+</button>
        <h3>Цены</h3>
        <ul>
            <li v-for="price in prices" :key="price.price_id">
                <div class="price-card">
                    <p>Услуга: {{ price.title }}</p>
                    <p v-if="editingPriceId === price.price_id">
                        <input v-model="newPrice" type="number" />
                    </p>

                    <p v-else>
                        Цена: {{ price.price }} ГРН
                    </p>

                    <button v-if="editingPriceId === price.price_id"
                        @click="editPrice(props.userId, price.price_id, newPrice)">✔</button>
                    <button v-else @click="setNewPrice(price.price_id, price.price)">Изменить</button>

                    <!-- <button @click="editPrice(props.userId, price.price_id)">Изменить</button> -->
                    <button @click="deletePrice(props.userId, price.price_id)">Удалить</button>
                </div>
            </li>
        </ul>
    </div>

        <div v-if="isCreateModalVisible" class="modal-overlay">
    <div class="modal-content">
        <h3>Добавить цену</h3>
        <div>
            <label for="service">Услуга</label>
            <select id="service" v-model="serviceId">
                <option v-for="service in apiServices" :key="service.id" :value="service.id">
                    {{ service.title }}
                </option>
            </select>
        </div>
        <div>
            <label for="price">Цена (ГРН)</label>
            <input id="price" type="number" v-model="price" />
        </div>
        <button @click="createPrice(props.userId, serviceId, price)">Сохранить</button>
        <button @click="closeCreateModal()">Отмена</button>
    </div>
</div>
</template>

<style scoped>
.price-card {
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

.price-card button {
    margin-top: 10px;
    padding: 5px;
    border: none;
    background-color: #007bff;
    color: #fff;
    border-radius: 4px;
    cursor: pointer;
}

.price-card button:last-of-type {
    background-color: #dc3545;
    color: #fff;
}

.add-price-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #007bff;
    color: #fff;
    font-size: 24px;
    border: none;
    cursor: pointer;
    /* position: fixed; */
    bottom: 20px;
    right: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
}

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
    z-index: 1000;
}

.modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
    text-align: center;
}

.modal label {
    display: block;
    margin-bottom: 5px;
}

.modal input,
.modal select {
    width: 100%;
    margin-bottom: 15px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
</style>
