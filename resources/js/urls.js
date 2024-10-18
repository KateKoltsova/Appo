const api = "api";
const v1 = "v1";

// Auth
const register = "register";
const login = "oauth/login";
const refresh = "oauth/refresh";
const logout = "oauth/logout";
const logoutAll = "oauth/logout/all";
const forgotPassword = "password/forgot";
const resetPassword = "password/reset";
const changePassword = "password/change";

// User profile
const users = "users";
const avatar = "avatar";
const gallery = "galleries";

// Client-Master data
const appointments = "appointments";
const schedules = "schedules";
const services = "services";
const prices = "prices";

// Order
const carts = "carts";
const checkout = "checkout";
const button = "button";

const urls = {
    auth: {
        register: {url: `${api}/${register}`, auth: false},
        login: {url: `${api}/${login}`, auth: false},
        refresh: {url: `${api}/${refresh}`, auth: false},
        logout: {url: `${api}/${logout}`, auth: false},
        logoutAll: {url: `${api}/${logoutAll}`, auth: false},
        forgotPassword: {url: `${api}/${forgotPassword}`, auth: false},
        resetPassword: {url: `${api}/${resetPassword}`, auth: false},
        changePassword: {url: `${api}/${changePassword}`, auth: false},
    },
    users: {
        all: {url: `${api}/${v1}/${users}`, auth: true},
        byId: {url: (id) => `${api}/${v1}/${users}/${id}`, auth: true}
    },
    // avatar: {
    //     byUserId: (userId) => `${api}/${v1}/${users}/${userId}/${avatar}`,
    // },
    // gallery: {
    //     all: (userId) => `${api}/${v1}/${users}/${userId}/${gallery}`,
    //     byId: (userId, galleryId) => `${api}/${v1}/${users}/${userId}/${gallery}/${galleryId}`,
    // },
    services: {
        all: {url: `${api}/${v1}/${services}`, auth: false},
        //     byId: (id) => `${api}/${v1}/${services}/${id}`,
    },
    // prices: {
    //     all: (userId) => `${api}/${v1}/${users}/${userId}/${prices}`,
    //     byId: (userId, priceId) => `${api}/${v1}/${users}/${userId}/${prices}/${priceId}`,
    // },
    schedules: {
        availableSchedules: {url: `${api}/${v1}/${schedules}`, auth: false}
        //     all: (userId) => `${api}/${v1}/${users}/${userId}/${schedules}`,
        //     byId: (userId, scheduleId) => `${api}/${v1}/${users}/${userId}/${schedules}/${scheduleId}`,
    },
    appointments: {
        all: {url: (userId) => `${api}/${v1}/${users}/${userId}/${appointments}`, auth: true},
        create: {url: (userId) => `${api}/${v1}/${users}/${userId}/${appointments}`, auth: true},
        // byId: (userId, appointmentId) => `${api}/${v1}/${users}/${userId}/${appointments}/${appointmentId}`,
    },
    carts: {
        add: {url: (id) => `${api}/${v1}/${users}/${id}/${carts}`, auth: true},
        all: {url: (userId) => `${api}/${v1}/${users}/${userId}/${carts}`, auth: true},
        delete: {url: ({userId, itemId}) => `${api}/${v1}/${users}/${userId}/${carts}/${itemId}`, auth: true},
    },
    checkout: {
        all: {url: (userId) => `${api}/${v1}/${users}/${userId}/${checkout}`, auth:true},
    },
    payButton: {
        all: {url: (userId) => `${api}/${v1}/${users}/${userId}/${button}`, auth:true},
    },
};

export {urls};
