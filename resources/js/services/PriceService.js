import apiClient from "../apiClient";
import {urls} from "../urls";

// Получение цен
export const getPrices = async (userId) => {
    try {
        return await apiClient({
            url: urls.prices.all.url(userId),
            method: "GET",
        });
    } catch (error) {
        console.error('Ошибка получения цен', error);
        throw error;
    }
};

// Добавление цены
export const addPrice = async (userId, serviceId, price) => {
    try {
        let data = {
            service_id: serviceId,
            price: price
        }
        return await apiClient({
            url: urls.prices.create.url(userId),
            method: "POST",
            data: data
        });
    } catch (error) {
        console.error('Ошибка добавления цены', error);
        throw error;
    }
};

// Обновление цены
export const updatePrice = async (userId, priceId, price) => {
    try {
        let data = {
            price: price
        }
        return await apiClient({
            url: urls.prices.edit.url(userId, priceId),
            method: "PATCH",
            data: data
        });
    } catch (error) {
        console.error('Ошибка изменения цены', error);
        throw error;
    }
};

// Удаление цены
export const removePrice = async (userId, priceId) => {
    try {
        return await apiClient({
            url: urls.prices.delete.url(userId, priceId),
            method: "DELETE",
        });
    } catch (error) {
        console.error('Ошибка удаления цены', error);
        throw error;
    }
};