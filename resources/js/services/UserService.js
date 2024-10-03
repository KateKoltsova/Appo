import apiClient from "../apiClient";
import {urls} from "../urls";

// Получение данных пользователя
export const fetchUserById = async (userId) => {
    try {
        return await apiClient({
            url: urls.users.byId.url(userId),
            method: "GET",
        });
    } catch (error) {
        console.error('Ошибка получения данных пользователя', error);
        throw error;
    }
};

// Обновление данных пользователя
export const updateUser = async (userId, updatedFields) => {
    try {
        return await apiClient({
            url: urls.users.byId.url(userId),
            method: "PATCH",
            data: updatedFields,
        });
    } catch (error) {
        console.error('Ошибка обновления данных пользователя', error);
        throw error;
    }
};
