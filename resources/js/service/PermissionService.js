import api from "./api";

export default {
    findAll: () => api.get("/permissions"),
    findOne: (id) => api.get(`/permissions/${id}`),
    create: (data) => api.post("/permissions", data),
    update: (id, data) => api.put(`/permissions/${id}`, data),
    delete: (id) => api.delete(`/permissions/${id}`),
};
