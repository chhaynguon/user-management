import api from "./api";

export default {
    findAll: () => api.get("/fnctions"),
    findOne: (code) => api.get(`/fnctions/${code}`),
    create: (data) => api.post("/fnctions", data),
    update: (code, data) => api.put(`/fnctions/${code}`, data),
    delete: (code) => api.delete(`/fnctions/${code}`),
};
