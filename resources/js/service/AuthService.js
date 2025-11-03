import api from "./api";

export default {
    login(email, password) {
        return api.post("/auth/login", { email, password });
    },
    register(payload) {
        return api.post("/auth/register", payload);
    },
    logout() {
        return api.post("/auth/logout");
    },
    me() {
        return api.get("/auth/me");
    },
};
