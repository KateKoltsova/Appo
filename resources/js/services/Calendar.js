import {ref} from "vue";

export function useCalendar() {
    const selectedDate = ref(new Date()); // Изначально выбранная дата
    const displayedDates = ref([]); // Массив отображаемых дат

    // Метод для получения новой даты и массива отображаемых дней
    const getDateWithOffset = (currentDate = new Date(), offset = 0) => {
        const newDate = new Date(currentDate);
        newDate.setDate(newDate.getDate() + offset);

        // Генерируем массив из 3 дней до и 3 дней после
        const days = [];
        for (let i = -3; i <= 3; i++) {
            const date = new Date(newDate);
            date.setDate(newDate.getDate() + i);
            days.push(date);
        }

        return {
            newDate,
            days,
        };
    };

    return {
        selectedDate,
        displayedDates,
        getDateWithOffset,
    };
}
