import apiClient from "../apiClient";
import {urls} from "../urls";

// Получение доступного расписания
export const getSchedules = async (selectedDate = null) => {
  try {
    let params = {};
    if (selectedDate) {
      const date = formatDate(selectedDate);
      params = {
        filter: {
          date: [date]
        }
      };
    }

    return await apiClient({
      url: urls.schedules.availableSchedules.url,
      method: "GET",
      params: params
    });
  } catch (error) {
      console.error('Ошибка получения доступного расписания', error);
      throw error;
  }
};

const formatDate = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};
