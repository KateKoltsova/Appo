import {onMounted, onBeforeUnmount} from 'vue';
import {useRouter} from 'vue-router';

export function useAuthWatcher() {
    const router = useRouter();

    // Получаем начальные значения userId и accessToken
    let lastUserId = localStorage.getItem('userId');
    let lastAccessToken = localStorage.getItem('accessToken');

    const checkAuth = () => {
        const userId = localStorage.getItem('userId');
        const accessToken = localStorage.getItem('accessToken');

        // Проверка на отсутствие значения или его изменение
        if (!userId || !accessToken || userId !== lastUserId || accessToken !== lastAccessToken) {
            localStorage.clear();
            router.push('/login');
        }
    };

    onMounted(() => {
        // Изначальная проверка
        checkAuth();

        // Отслеживание изменений в localStorage через событие 'storage' (для других вкладок)
        const handleStorageChange = () => {
            checkAuth();
        };

        window.addEventListener('storage', handleStorageChange);

        // Установка интервала для проверки изменений в текущей вкладке
        const intervalId = setInterval(() => {
            const currentUserId = localStorage.getItem('userId');
            const currentAccessToken = localStorage.getItem('accessToken');

            // Проверка на изменения
            if (currentUserId !== lastUserId || currentAccessToken !== lastAccessToken) {
                lastUserId = currentUserId; // Обновляем последнее значение
                lastAccessToken = currentAccessToken; // Обновляем последнее значение
                checkAuth(); // Выполняем проверку авторизации
            }
        }, 1000); // Проверка каждую секунду

        // Очищаем интервал и обработчик при размонтировании
        onBeforeUnmount(() => {
            clearInterval(intervalId);
            window.removeEventListener('storage', handleStorageChange);
        });
    });
}
