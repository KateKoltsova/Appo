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