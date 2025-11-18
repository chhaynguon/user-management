import AuthService from "@/service/AuthService";
import { defineStore } from "pinia";

export const useAuthStore = defineStore("auth", {
    id: "auth",
    state: () => ({
        token: null,
        user: null,
    }),
    actions: {
        login({token, user}) {
            this.token = token;
            this.user = user;
        },
        async logout() {
            try {
                await AuthService.logout();
            } catch (e) {
                console.warn(
                    "Logout request failed, token might already be invalid."
                );
            }
            this.token = null;
            this.user = null;
        },
        hasRole(role) {
            return this.user?.roles?.includes(role);
        },
        hasPermission(permission) {
            return this.user?.permissions?.includes(permission);
        },
    },
    persist: {
        key: "auth",
        storage: sessionStorage,
    },
});
