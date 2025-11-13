import api from "./api";

export default {
    findAll: () => api.get("/roles"),
    findOne: (id) => api.get(`/roles/${id}`),
    create: (data) => api.post("/roles", data),
    update: (id, data) => api.put(`/roles/${id}`, data),
    delete: (id) => api.delete(`/roles/${id}`),
};
