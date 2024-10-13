import apiClient from "../apiClient";
import {urls} from "../urls";

export const add = async (userId, item) => {
    try {
        return await apiClient({
            url: urls.carts.add.url(userId),
            method: "POST",
            data: item
        });
    } catch (error) {
        console.error('Ошибка добавления в корзину', error);
        throw error;
    }
};

export const getCart = async (userId) => {
    try {
        return await apiClient({
            url: urls.carts.all.url(userId),
            method: "GET",
        });
    } catch (error) {
        console.error('Ошибка получения корзины', error);
        throw error;
    }
};

export const remove = async (userId, itemId) => {
    try {
        return await apiClient({
            url: urls.carts.delete.url({userId, itemId}),
            method: "DELETE",
        });
    } catch (error) {
        console.error('Ошибка удаления из корзины', error);
        throw error;
    }
};
