import axios from 'axios';
import {urls} from './urls.js';
import {refresh} from "./services/UserService.js";

const apiClient = axios.create({
    baseURL: `${window.location.origin}`,
    headers: {
        'Content-Type': 'application/json',
    },
});

apiClient.interceptors.request.use((config) => {
    const requestUrl = config.url;

    function extractParamsFromUrl(urlTemplate, url) {
        const templateParts = urlTemplate.split('/').filter(Boolean);
        const urlParts = url.split('/').filter(Boolean);

        const params = {};
        for (let i = 0; i < templateParts.length; i++) {
            if (templateParts[i].startsWith(':')) {
                const paramName = templateParts[i].substring(1);
                params[paramName] = urlParts[i];
            }
        }
        return params;
    }

    function matchUrlTemplate(urlTemplate, url) {
        const pattern = urlTemplate
            .replace(/:[^\s/]+/g, '([^/]+)')
            .replace(/\//g, '\\/')
            .replace(/\./g, '\\.');

        const regex = new RegExp(`^${pattern}`);
        return url.match(regex);
    }

    function getUrlConfig(requestUrl) {
        for (const group of Object.values(urls)) {
            for (const item of Object.values(group)) {
                if (typeof item === 'object' && item.url) {
                    const urlTemplate = typeof item.url === 'function' ? item.url(':id') : item.url;
                    const match = matchUrlTemplate(urlTemplate, requestUrl);
                    if (match) {
                        const params = extractParamsFromUrl(urlTemplate, requestUrl);
                        return {...item, params};
                    }
                }
            }
        }
        return null;
    }

    const urlConfig = getUrlConfig(requestUrl);
    if (urlConfig?.auth) {
        const accessToken = localStorage.getItem('accessToken');
        if (accessToken) {
            config.headers['Authorization'] = `Bearer ${accessToken}`;
        }
    }

    return config;
}, (error) => {
    return Promise.reject(error);
});

apiClient.interceptors.response.use((response) => {
    return response;
}, async (error) => {
    if (error.response && error.response.status === 401) {
        const refreshToken = localStorage.getItem('refreshToken');
        if (!refreshToken) {
            throw error;
        }
        try {
            const response = await refresh(refreshToken);
            if (response.status === 200) {
                const {access_token: accessToken, refresh_token: refreshToken, id: userId} = response.data.data;
                localStorage.setItem('accessToken', accessToken);
                localStorage.setItem('refreshToken', refreshToken);
                localStorage.setItem('userId', userId);
                localStorage.removeItem('user');
                error.config.headers['Authorization'] = `Bearer ${accessToken}`;
                return apiClient.request(error.config);
            } else {
                throw error;
            }
        } catch(error) {
            console.error('Unauthorized, redirecting to login page...');
            localStorage.clear();
            window.location.href = '/login';
        }
    }
    return Promise.reject(error);
});

export default apiClient;
