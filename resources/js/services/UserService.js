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

// Смена пароля
export const changePassword = async (old_password, new_password) => {
    try {

        let data = {
            old_password: old_password,
            new_password: new_password
        };
        return await apiClient({
            url: urls.auth.changePassword.url,
            method: "POST",
            data: data,
        });
    } catch (error) {
        console.error("Ошибка смены пароля", error);
    }
}

// Логаут
export const logout = async () => {
    try {
        return await apiClient({
            url: urls.auth.logout.url,
            method: "DELETE",
        });
    } catch (error) {
        console.error('Ошибка логаута пользователя', error);
        throw error;
    }
}

// Полный логаут
export const logoutAll = async () => {
    try {
        return await apiClient({
            url: urls.auth.logoutAll.url,
            method: "DELETE",
        });
    } catch (error) {
        console.error('Ошибка полного логаута пользователя', error);
        throw error;
    }
}