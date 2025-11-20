import api from "./api";

export default {
    findAll: () => api.get("/roles"),
    findOne: (code) => api.get(`/roles/${code}`),
    create: (data) => api.post("/roles", data),
    update: (code, data) => api.put(`/roles/${code}`, data),
    delete: (code) => api.delete(`/roles/${code}`),
};
