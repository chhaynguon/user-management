import AppLayout from "@/layout/AppLayout.vue";
import AuthService from "@/service/AuthService";
import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/",
            redirect: () => {
                const token = localStorage.getItem("token");
                return token ? { name: "dashboard" } : { name: "login" };
            },
        },
        {
            path: "/",
            component: AppLayout,
            children: [
                {
                    path: "",
                    name: "dashboard",
                    component: () => import("@/views/Dashboard.vue"),
                    meta: { requiresAuth: true },
                },
                {
                    path: "uikit/formlayout",
                    name: "formlayout",
                    component: () => import("@/views/uikit/FormLayout.vue"),
                },
                {
                    path: "uikit/input",
                    name: "input",
                    component: () => import("@/views/uikit/InputDoc.vue"),
                },
                {
                    path: "uikit/button",
                    name: "button",
                    component: () => import("@/views/uikit/ButtonDoc.vue"),
                },
                {
                    path: "uikit/table",
                    name: "table",
                    component: () => import("@/views/uikit/TableDoc.vue"),
                },
                {
                    path: "uikit/list",
                    name: "list",
                    component: () => import("@/views/uikit/ListDoc.vue"),
                },
                {
                    path: "uikit/tree",
                    name: "tree",
                    component: () => import("@/views/uikit/TreeDoc.vue"),
                },
                {
                    path: "uikit/panel",
                    name: "panel",
                    component: () => import("@/views/uikit/PanelsDoc.vue"),
                },

                {
                    path: "uikit/overlay",
                    name: "overlay",
                    component: () => import("@/views/uikit/OverlayDoc.vue"),
                },
                {
                    path: "uikit/media",
                    name: "media",
                    component: () => import("@/views/uikit/MediaDoc.vue"),
                },
                {
                    path: "uikit/message",
                    name: "message",
                    component: () => import("@/views/uikit/MessagesDoc.vue"),
                },
                {
                    path: "uikit/file",
                    name: "file",
                    component: () => import("@/views/uikit/FileDoc.vue"),
                },
                {
                    path: "uikit/menu",
                    name: "menu",
                    component: () => import("@/views/uikit/MenuDoc.vue"),
                },
                {
                    path: "uikit/charts",
                    name: "charts",
                    component: () => import("@/views/uikit/ChartDoc.vue"),
                },
                {
                    path: "uikit/misc",
                    name: "misc",
                    component: () => import("@/views/uikit/MiscDoc.vue"),
                },
                {
                    path: "uikit/timeline",
                    name: "timeline",
                    component: () => import("@/views/uikit/TimelineDoc.vue"),
                },
                {
                    path: "pages/empty",
                    name: "empty",
                    component: () => import("@/views/pages/Empty.vue"),
                },
                {
                    path: "pages/crud",
                    name: "crud",
                    component: () => import("@/views/pages/Crud.vue"),
                },
                {
                    path: "pages/users",
                    name: "user",
                    component: () =>
                        import("@/views/pages/admin/user/index.vue"),
                    meta: { requiresAdmin: true },
                },
                {
                    path: "documentation",
                    name: "documentation",
                    component: () => import("@/views/pages/Documentation.vue"),
                },
            ],
        },
        {
            path: "/auth/login",
            name: "login",
            component: () => import("@/views/pages/auth/Login.vue"),
        },
        {
            path: "/auth/register",
            name: "register",
            component: () => import("@/views/pages/auth/Register.vue"),
        },
        {
            path: "/auth/access",
            name: "accessDenied",
            component: () => import("@/views/pages/auth/Access.vue"),
        },
        {
            path: "/auth/error",
            name: "error",
            component: () => import("@/views/pages/auth/Error.vue"),
        },
        {
            path: "/:pathMatch(.*)*",
            name: "notfound",
            component: () => import("@/views/pages/NotFound.vue"),
        },
    ],
});
router.beforeEach(async (to, from, next) => {
    const token = localStorage.getItem("token");

    const isAuthenticated = !!localStorage.getItem("token"); // example
    if (to.meta.requiresAuth && !isAuthenticated)
        return next({ name: "login" });

    if (to.meta.requiresAuth) {
        if (!token) return next({ name: "login" });

        // optional: ensure user is loaded into store or check role quickly
        try {
            // fetch current user once per protected route navigation
            const res = AuthService.me(token);
            const user = res.data;
            //store globally
            window.currentUser = user;
            localStorage.setItem("user", JSON.stringify(user));

            // admin check
            if (to.meta.requiresAdmin && user.role !== "admin") {
                return next({ name: "dashboard" }); // or access denied page
            }
            return next();
        } catch (err) {
            localStorage.removeItem("token");
            return next({ name: "login" });
        }
    } else {
        return next();
    }
});

export default router;
