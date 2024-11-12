import apiClient from "../apiClient";
import {urls} from "../urls";

// Получение доступного расписания
export const getAvailableSchedules = async (
    selectedDate = null,
    selectedCategories = null,
    selectedService = null
) => {
    try {
        let params = {
            filter: {
                date: [],
                category: [],
                service_id: [],
            },
        };
        if (selectedDate) {
            const date = formatDate(selectedDate);
            params.filter.date.push(date);
        }

        if (selectedCategories) {
            params.filter.category.push(...selectedCategories);
        }

        if (selectedService) {
            params.filter.service_id.push(selectedService);
        }

        return await apiClient({
            url: urls.schedules.availableSchedules.url,
            method: "GET",
            params: params,
        });
    } catch (error) {
        console.error("Ошибка получения доступного расписания", error);
        throw error;
    }
};

const formatDate = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

const formatTime = (date) => {
    const hours = date.getHours();
    const minutes = date.getMinutes();
    return `${hours}:${minutes}:00`;
};

export const fetchDaySchedule = async (userId, selectedDate = null) => {
    try {
        let params = {
            filter: {
                date: [],
            },
        };
        if (selectedDate) {
            const date = formatDate(selectedDate);
            params.filter.date.push(date);
        }
        return await apiClient({
            url: urls.schedules.all.url(userId),
            method: "GET",
            params: params,
        });
    } catch (error) {
        console.error("Ошибка получения графика", error);
    }
}

export const addDaySchedule = async (userId, dateTime) => {
    try {
        const parameter = formatDate(dateTime).concat(" ", formatTime(dateTime));

        let data = {
            date_time: parameter,
        };
        return await apiClient({
            url: urls.schedules.create.url(userId),
            method: "POST",
            data: data,
        });
    } catch (error) {
        console.error("Ошибка создания графика", error);
    }
}

export const updateDaySchedule = async (userId, scheduleId, dateTime) => {
    try {
        const parameter = formatDate(dateTime).concat(" ", formatTime(dateTime));

        let data = {
            date_time: parameter,
        };
        return await apiClient({
            url: urls.schedules.edit.url(userId, scheduleId),
            method: "PATCH",
            data: data,
        });
    } catch (error) {
        console.error("Ошибка редактирования графика", error);
    }
}

export const removeDaySchedule = async (userId, scheduleId) => {
    try {
        return await apiClient({
            url: urls.schedules.delete.url(userId, scheduleId),
            method: "DELETE",
        });
    } catch (error) {
        console.error("Ошибка удаления графика", error);
    }
}

export const removeDayScheduleAppointment = async (userId, scheduleId) => {
    try {
        return await apiClient({
            url: urls.schedules.cancelAppointment.url(userId, scheduleId),
            method: "DELETE",
        });
    } catch (error) {
        console.error("Ошибка отмены записи", error);
    }
};
