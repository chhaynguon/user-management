import api from "./api"

export default {
    findAll:() => api.get('/users'),
    findOne:(id) => api.get(`/users/${id}`),
    create:(data) => api.post('/users', data),
    update:(id, data) => api.put(`/users/${id}`, data),
    delete:(id) => api.delete(`/users/${id}`)

}