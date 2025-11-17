import api from "./api";

export default {
    findAll: () => api.get("/fnctions"),
    findOne: (id) => api.get(`/fnctions/${id}`),
    create: (data) => api.post("/fnctions", data),
    update: (id, data) => api.put(`/fnctions/${id}`, data),
    delete: (id) => api.delete(`/fnctions/${id}`),
};
