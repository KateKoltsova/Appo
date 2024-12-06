import {createRouter, createWebHistory} from 'vue-router';

const routes = [
    {
        path: "/",
        redirect: "/booking",
    },
    {
        path: "/register",
        component: () => import("../pages/RegisterPage.vue")
    },
    {
        path: "/login",
        component: () => import("../pages/LoginPage.vue"),
    },
    {
        path: "/reset-password/:token",
        component: () => import("../pages/ResetPasswordPage.vue"),
    },
    {
        path: "/profile",
        children: [
            {
                path: "",
                component: () => import("../pages/UserProfile.vue"),
            }
        ]
    },
    {
        path: "/cart",
        children: [
            {
                path: "checkout",
                component: () => import("../pages/CheckoutPage.vue")
            }
        ],
    },
    {
        path: "/booking",
        component: () => import("../pages/BookingPage.vue"),
    },
    // {
    //     path: '/:pathMatch(.*)*',
    //     component: NotFoundPage
    // }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
