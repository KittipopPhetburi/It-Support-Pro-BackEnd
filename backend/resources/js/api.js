import axios from 'axios';

// Configure base axios instance for the frontend to talk to the backend API.
// Adjust `baseURL` if your backend is hosted on a different origin or path.
const api = axios.create({
    baseURL: (import.meta.env.VITE_APP_API_BASE_URL) || '/api/v1',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true, // send cookies if backend uses session auth
});

// Response interceptor (optional): unwrap data
api.interceptors.response.use(
    response => response.data,
    error => Promise.reject(error)
);

export default api;

// Example convenience methods (exported for quick use in components/tests)
export const notifications = {
    list(params) {
        return api.get('/notifications', { params });
    },
    get(id) {
        return api.get(`/notifications/${id}`);
    },
    create(payload) {
        return api.post('/notifications', payload);
    },
    update(id, payload) {
        return api.put(`/notifications/${id}`, payload);
    },
    remove(id) {
        return api.delete(`/notifications/${id}`);
    },
};
