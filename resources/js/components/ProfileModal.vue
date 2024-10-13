<script setup>
import {ref, watch} from "vue";
import {fetchUserById} from "../services/UserService.js";

const props = defineProps({
    masterId: {
        type: Number,
        required: true,
    },
});

const masterDetails = ref(null);

watch(
    () => props.masterId,
    async (newMasterId) => {
        console.log("Обновленный masterId:", newMasterId);
        if (newMasterId) {
            await getMasterInfo(newMasterId);
        }
    },
);

const getMasterInfo = async (masterId) => {
    try {
        console.log("Запрос на мастер с ID:", masterId);
        const response = await fetchUserById(masterId);
        if (response.status === 200) {
            console.log("Данные мастера получены:", response.data);
            masterDetails.value = response.data.data;
        }
    } catch (error) {
        console.error("Ошибка получения данных о мастере:", error);
    }
}
</script>

<template>
    <div class="modal">
        <div class="modal-content">
            <span class="close" @click="$emit('close')">&times;</span>
            <div v-if="masterDetails">
                <h2>{{ masterDetails.firstname }} {{ masterDetails.lastname }}</h2>
                <img :src="masterDetails.image_url" alt="Master Profile"/>
                 <p>{{ masterDetails }}</p>
<!--                 <div class="gallery">-->
<!--                  <img-->
<!--                    v-for="image in masterDetails.gallery"-->
<!--                    :src="image"-->
<!--                    :key="image"-->
<!--                  />-->
<!--                </div>-->
            </div>
            <div v-else>
                <p>Загрузка данных...</p>
            </div>
        </div>
    </div>
</template>

<style>
/* Стили для модального окна */
.modal {
    display: block; /* Show the modal */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.1);
}

.modal-content {
    position: absolute;
    background-color: white;
    padding: 20px;
    margin: auto;
    top: 20%;
    left: 50%;
    transform: translate(-50%, -20%);
    width: 60%;
}

.modal-content img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 10px;
}

.close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 28px;
    cursor: pointer;
}
</style>
