import api from "./api";

export default {
    findAll: () => api.get("/permissions"),
    findOne: (code) => api.get(`/permissions/${code}`),
    create: (data) => api.post("/permissions", data),
    update: (code, data) => api.put(`/permissions/${code}`, data),
    delete: (code) => api.delete(`/permissions/${code}`),
};
