import axios from 'axios';
window.axios = axios;

// default header
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configure baseURL for API calls. When using Vite, you can set
// VITE_APP_API_BASE_URL in your `.env` (e.g. VITE_APP_API_BASE_URL="http://localhost:8000/api/v1").
if (import.meta && import.meta.env) {
	const base = import.meta.env.VITE_APP_API_BASE_URL || '/api/v1';
	window.axios.defaults.baseURL = base;
}

// CSRF: if your backend uses Laravel session + CSRF cookie, attach token
const tokenMeta = document.querySelector('meta[name="csrf-token"]');
if (tokenMeta) {
	window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
