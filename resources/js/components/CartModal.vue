<script setup>
import { ref, watch } from "vue";
import { defineProps } from "vue";
import { useRouter } from "vue-router";
import { getCart, remove } from "../services/CartService";

const props = defineProps({
    isCartModalOpen: Boolean,
});
const router = useRouter();
const cart = ref([]);
const hasErrorItems = ref(false);
const isLoading = ref(false);

const userId = localStorage.getItem("userId");

watch(
    () => props.isCartModalOpen,
    (newValue) => {
        if (newValue) {
        getCartItems();
        }
    }
);

const handleCheckoutClick = async () => {
    isLoading.value = true;
    props.isCartModalOpen = false;
    router.push("/cart/checkout");
    isLoading.value = false;
};

const getCartItems = async () => {
    try {
        isLoading.value = true;
        const response = await getCart(userId);
        cart.value = response.data.data;
        checkErrorItems();
    } catch (error) {
        console.error("Ошибка получения данных корзины:", error);
    }
    isLoading.value = false;
};

const removeItem = async (itemId) => {
    try {
        isLoading.value = true;
        const userId = localStorage.getItem("userId");
        await remove(userId, itemId);
        cart.value.items = cart.value.items.filter((item) => item.id !== itemId);
        checkErrorItems();
    } catch (error) {
        console.error("Ошибка удаления элемента:", error);
    }
    isLoading.value = false;
};

const checkErrorItems = () => {
    hasErrorItems.value = cart.value.items.some((item) => item.message);
};
</script>

<template>
  <div v-if="isCartModalOpen" class="cart-modal">
    <div class="cart-modal-content">
      <h2>Корзина</h2>
      <ul>
        <li v-for="item in cart.items" :key="item.id">
          <div class="cart-item-card" :class="{ 'error-item': item.message }">
            <h3>{{ item.title }}</h3>
            <p class="item-info">Дата и время: {{ item.date_time }}</p>
            <p class="item-info">
              Мастер: {{ item.master_firstname }} {{ item.master_lastname }}
            </p>
            <p class="item-info">Цена: {{ item.price }} ГРН</p>

            <button class="remove-button" @click="removeItem(item.id)">
              ✖
            </button>

            <div v-if="item.message" class="error-message">
              Это время больше недоступно! Удалите его, чтобы продолжить!
            </div>
          </div>
        </li>
      </ul>

      <div class="cart-summary">
        <p>Общее количество: {{ cart.totalCount }}</p>
        <p>Итоговая сумма: {{ cart.totalSum }} ГРН</p>
      </div>

      <button
        class="checkout-button"
        :disabled="hasErrorItems"
        @click="handleCheckoutClick"
      >
        Оформить заказ
      </button>
    </div>
  </div>
</template>

<style scoped>
.cart-modal {
  position: fixed;
  top: 0;
  right: 0;
  width: 50%;
  height: 100%;
  background-color: #fff;
  z-index: 1000;
  box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5);
  padding: 20px;
}

.cart-modal-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.cart-item-card {
  border: 1px solid #ccc;
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 5px;
  position: relative;
}

.error-item {
  border-color: red;
  background-color: #ffe6e6;
}

.item-info {
  font-size: 14px;
  margin-bottom: 5px;
}

.error-message {
  color: red;
  font-size: 12px;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  padding: 5px;
  background-color: rgba(255, 0, 0, 0.2);
  text-align: center;
}

.remove-button {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
}

.cart-summary {
  text-align: right;
  margin-top: 20px;
}

.checkout-button {
  background-color: #28a745;
  border: none;
  padding: 15px;
  color: #fff;
  font-size: 18px;
  cursor: pointer;
  width: 100%;
}

.checkout-button:disabled {
  background-color: grey;
}
</style>
