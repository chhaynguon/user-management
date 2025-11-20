import api from "./api";

export default {
    findAll: () => api.get("/groups"),
    findOne: (code) => api.get(`/groups/${code}`),
    create: (data) => api.post("/groups", data),
    update: (code, data) => api.put(`/groups/${code}`, data),
    delete: (code) => api.delete(`/groups/${code}`),
};
