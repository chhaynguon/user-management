import AppLayout from "@/layout/AppLayout.vue";
import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = createRouter({
    history: createWebHistory(),
    routes: [
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
                    meta: {  requiresAuth: true },
                },
                {
                    path: "pages/permissions",
                    name: "permission",
                    component: () =>
                        import("@/views/pages/admin/permission/index.vue"),
                    meta: {  requiresAuth: true },
                },
                {
                    path: "pages/roles",
                    name: "role",
                    component: () =>
                        import("@/views/pages/admin/role/index.vue"),
                    meta: {  requiresAuth: true },
                },
                {
                    path: "pages/fnctions",
                    name: "fnction",
                    component: () =>
                        import("@/views/pages/admin/fnction/index.vue"),
                    meta: {  requiresAuth: true },
                },
                {
                    path: "pages/groups",
                    name: "group",
                    component: () =>
                        import("@/views/pages/admin/group/index.vue"),
                    meta: {  requiresAuth: true },
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
        {
            path: "/auth/account",
            name: "account",
            component: () => import("@/views/pages/auth/Account.vue"),
        },
    ],
});
router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore();

    if ((to.name === "login" || to.name === "register") && auth.token) {
        return next({ name: "dashboard" });
    }

    if (to.meta.requiresAuth && !auth.token) {
        return next({ name: "login" });
    }

    if (to.meta.requiresAdmin) {
        try {
            if (!auth.user) await auth.fetchUser();

            if (auth.user.role !== "admin") {
                return next({ name: "dashboard" });
            }
        } catch (err) {
            await auth.logout();
            return next({ name: "login" });
        }
    }
    next();
});

export default router;
