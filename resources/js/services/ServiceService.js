import apiClient from "../apiClient";
import { urls } from "../urls";

export const getAll = async (selectedCategories = null) => {
    try {
        let params = {
            filter: {
                category: [],
            },
        };
        if (selectedCategories) {
            params.filter.category.push(...selectedCategories);
        }
        return await apiClient({
            url: urls.services.all.url,
            method: "GET",
            params: params,
        });
    } catch (error) {
        console.error("Ошибка получения услуг", error);
        throw error;
    }
};
