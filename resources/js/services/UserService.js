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
            url: urls.users.edit.url(userId),
            method: "PATCH",
            data: updatedFields,
        });
    } catch (error) {
        console.error('Ошибка обновления данных пользователя', error);
        throw error;
    }
};

// Удаление данных пользователя
export const removeUser = async (userId) => {
    try {
        return await apiClient({
            url: urls.users.delete.url(userId),
            method: "DELETE",
        });
    } catch (error) {
        console.error('Ошибка удаления пользователя', error);
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
        throw new Error(error.response.data.message);
    }
}

// Генерация ссылки для сброса пароля
export const forgotPassword = async (email) => {
    try {
        let data = {
            email: email,
            url: window.location.origin + "/reset-password"
        };
        return await apiClient({
            url: urls.auth.forgotPassword.url,
            method: "POST",
            data: data,
        });
    } catch (error) {
        throw new Error(error.response.data.message);
    }
}

// Сброс пароля
export const resetPassword = async (email, token, password) => {
    try {
        let data = {
            email: email,
            token: token,
            password: password
        };
        return await apiClient({
            url: urls.auth.resetPassword.url,
            method: "POST",
            data: data,
        });
    } catch (error) {
        throw new Error(error.response.data.message);
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

// Загрузка аватарки
export const uploadAvatar = async (userId, image) => {
    try {
        let data = {
            image: image,
        }
        return await apiClient({
            url: urls.users.uploadAvatar.url(userId),
            method: "POST",
            data: data,
            headers: {'Content-Type': 'multipart/form-data'}
        });
    } catch (error) {
        console.error('Ошибка загрузки аватарки', error);
        throw error;
    }
};

// Удаление аватарки
export const removeAvatar = async (userId) => {
    try {
        return await apiClient({
            url: urls.users.deleteAvatar.url(userId),
            method: "DELETE",
        });
    } catch (error) {
        console.error('Ошибка удаления аватарки', error);
        throw error;
    }
};

// Получение галереи
export const fetchGallery = async (userId) => {
    try {
        return await apiClient({
            url: urls.users.galleryList.url(userId),
            method: "GET",
        });
    } catch (error) {
        console.error('Ошибка получения галереи', error);
        throw error;
    }
};

// Получение галереи по ID
export const fetchGalleryById = async (userId, galleryId) => {
    try {
        return await apiClient({
            url: urls.users.galleryById.url(userId, galleryId),
            method: "GET",
        });
    } catch (error) {
        console.error('Ошибка получения галереи', error);
        throw error;
    }
};

// Добавление картинки галереи
export const uploadGallery = async (userId, images) => {
    try {
        let data = {
            images: {},
        }
        data.images = Array.from(images);
        return await apiClient({
            url: urls.users.galleryAdd.url(userId),
            method: "POST",
            data: data,
            headers: {'Content-Type': 'multipart/form-data'}
        });
    } catch (error) {
        console.error('Ошибка добавления картинки галереи', error);
        throw error;
    }
};

// Удаление картинки галереи
export const removeGallery = async (userId, galleryId) => {
    try {
        return await apiClient({
            url: urls.users.galleryDelete.url(userId, galleryId),
            method: "DELETE",
        });
    } catch (error) {
        console.error('Ошибка удаления картинки галереи', error);
        throw error;
    }
};
