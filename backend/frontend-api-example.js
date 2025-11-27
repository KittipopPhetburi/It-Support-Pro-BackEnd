// API Configuration for Frontend
// Backend API URL: http://localhost:80/api

const API_BASE_URL = 'http://localhost:80/api';

// Example API calls for frontend integration

// ==================== AUTH ====================

// Register
async function register(userData) {
    const response = await fetch(`${API_BASE_URL}/register`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(userData),
    });
    return response.json();
}

// Login
async function login(credentials) {
    const response = await fetch(`${API_BASE_URL}/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(credentials),
    });
    return response.json();
}

// Logout (requires auth token)
async function logout(token) {
    const response = await fetch(`${API_BASE_URL}/logout`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
    });
    return response.json();
}

// Get current user
async function getCurrentUser(token) {
    const response = await fetch(`${API_BASE_URL}/user`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
    });
    return response.json();
}

// ==================== INCIDENTS ====================

async function getIncidents(token) {
    const response = await fetch(`${API_BASE_URL}/incidents`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
    });
    return response.json();
}

async function createIncident(token, incidentData) {
    const response = await fetch(`${API_BASE_URL}/incidents`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(incidentData),
    });
    return response.json();
}

// ==================== ASSETS ====================

async function getAssets(token) {
    const response = await fetch(`${API_BASE_URL}/assets`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
    });
    return response.json();
}

async function assignAsset(token, assetId, assignmentData) {
    const response = await fetch(`${API_BASE_URL}/assets/${assetId}/assign`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(assignmentData),
    });
    return response.json();
}

// ==================== KB ARTICLES ====================

async function getKbArticles(token) {
    const response = await fetch(`${API_BASE_URL}/kb-articles`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
    });
    return response.json();
}

// ==================== PROBLEMS ====================

async function getProblems(token) {
    const response = await fetch(`${API_BASE_URL}/problems`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
    });
    return response.json();
}

// ==================== REQUESTS ====================

async function createAssetRequest(token, requestData) {
    const response = await fetch(`${API_BASE_URL}/asset-requests`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(requestData),
    });
    return response.json();
}

async function createOtherRequest(token, requestData) {
    const response = await fetch(`${API_BASE_URL}/other-requests`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(requestData),
    });
    return response.json();
}

// Export for module usage
export {
    API_BASE_URL,
    register,
    login,
    logout,
    getCurrentUser,
    getIncidents,
    createIncident,
    getAssets,
    assignAsset,
    getKbArticles,
    getProblems,
    createAssetRequest,
    createOtherRequest,
};
