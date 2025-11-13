import api from "./api";

export default {
    findAll: () => api.get("/functions"),
    findOne: (id) => api.get(`/functions/${id}`),
    create: (data) => api.post("/functions", data),
    update: (id, data) => api.put(`/functions/${id}`, data),
    delete: (id) => api.delete(`/functions/${id}`),
};
