import apiClient from "../apiClient";
import {urls} from "../urls";

export const getUserAppointments = async (userId) => {
    try {
        return await apiClient({
            url: urls.appointments.all.url(userId),
            method: "GET",
        });
    } catch (error) {
        console.error('Ошибка получения записей', error);
        throw error;
    }
};

export const paymentProcess = async (userId, orderId) => {
    try {
        let data = {
            order_id: orderId
        }
        return await apiClient({
            url: urls.appointments.create.url(userId),
            method: "POST",
            data: data
        });
    } catch (error) {
        console.error('Ошибка оформления заказа', error);
        throw error;
    }
};
