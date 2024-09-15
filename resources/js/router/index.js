// import {
//     AfterPaymentPage,
//     AppointmentPage,
//     AvailableSchedulesDetailsPage,
//     AvailableSchedulesPage,
//     CartPage,
//     CheckoutPage,
//     CreateMastersPage,
//     CreateServicesPage,
//     ForgotPasswordPage,
//     GalleryPageForMaster,
//     LoginPage,
//     NotFoundPage,
//     PayCartPage,
//     PersonalAccount,
//     PricePage,
//     RegisterPage,
//     ResetPasswordPage,
//     ScheduleAddPage,
//     SchedulePages,
//     SchedulesPageDetails,
//     ServicesPage,
//     UserInfoPage,
//     UserListPage,
// } from "./pages";
import {createRouter, createWebHistory} from 'vue-router';

const routes = [
    {
        path: "/login",
        component: () => import("../components/pages/LoginPage.vue"),
    },
    // {
    //     path: "/register",
    //     component: RegisterPage,
    // },
    // {
    //     path: "/forgot-password",
    //     component: ForgotPasswordPage,
    // },
    // {
    //     path: "/reset-password",
    //     component: ResetPasswordPage,
    // },
    {
        path: "/profile",
        // component: PersonalAccount,
        children: [
            {
                path: "",
                component: () => import("../components/pages/UserPage.vue"),
            },
    //         {
    //             path: "appointments",
    //             component: AppointmentsPage,
    //         },
    //         {
    //             path: "schedules",
    //             component: SchedulesPage,
    //             children: [
    //                 {
    //                     path: ":id",
    //                     component: ScheduleDetailsPage,
    //                 }
    //             ],
    //         },
    //         {
    //             path: "prices",
    //             component: PricesPage,
    //         },
    //         {
    //             path: "gallery",
    //             component: GalleryPage,
    //             children: [
    //                 {
    //                     path: ":id",
    //                     component: GalleryPage,
    //                 },
    //             ],
    //         }
        ]
    },
    // {
    //     path: "/cart",
    //     component: CartPage,
    //     children: [
    //         {
    //             path: "checkout",
    //             component: CheckoutPage,
    //         }
    //     ],
    // },
    // {
    //     path: "/services",
    //     component: ServicesPage,
    // },
    // {
    //     path: "/availableSchedules",
    //     component: AvailableSchedulesPage,
    // },
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
