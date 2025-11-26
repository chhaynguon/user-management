import api from "./api";

export default {
    findAll: () => api.get("/permissions"),
    findOne: (code) => api.get(`/permissions/${code}`),
    create: (data) => api.post("/permissions", data),
    update: (code, data) => api.put(`/permissions/${code}`, data),
    delete: (code) => api.delete(`/permissions/${code}`),

    // New: fetch permissions grouped by functions
    findByFunction: (functionCode) => api.get(`/permissions/function/${functionCode}`),

    // New: fetch all permissions for a user (direct + role + group)
    findAllForUser: (userId) => api.get(`/users/${userId}/permissions`),
};
