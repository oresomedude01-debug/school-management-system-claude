// Simple React-like Framework in Vanilla JavaScript
// No npm, no build tools - pure JavaScript!

class Component {
    constructor(props = {}) {
        this.props = props;
        this.state = {};
        this.element = null;
    }

    setState(newState) {
        this.state = { ...this.state, ...newState };
        this.update();
    }

    update() {
        if (this.element && this.element.parentNode) {
            const newElement = this.render();
            this.element.parentNode.replaceChild(newElement, this.element);
            this.element = newElement;
        }
    }

    render() {
        return createElement('div', {}, 'Component');
    }

    mount(container) {
        this.element = this.render();
        if (typeof container === 'string') {
            container = document.querySelector(container);
        }
        if (container) {
            container.innerHTML = '';
            container.appendChild(this.element);
        }
        return this.element;
    }
}

// Helper function to create elements
function createElement(tag, attrs = {}, ...children) {
    const element = document.createElement(tag);

    // Set attributes
    Object.keys(attrs).forEach(key => {
        if (key === 'className') {
            element.className = attrs[key];
        } else if (key === 'onClick' || key.startsWith('on')) {
            const eventName = key.substring(2).toLowerCase();
            element.addEventListener(eventName, attrs[key]);
        } else if (key === 'style' && typeof attrs[key] === 'object') {
            Object.assign(element.style, attrs[key]);
        } else {
            element.setAttribute(key, attrs[key]);
        }
    });

    // Append children
    children.flat().forEach(child => {
        if (child instanceof Node) {
            element.appendChild(child);
        } else if (child !== null && child !== undefined && child !== false) {
            element.appendChild(document.createTextNode(String(child)));
        }
    });

    return element;
}

// Router class for SPA routing
class Router {
    constructor() {
        this.routes = {};
        this.currentRoute = null;

        window.addEventListener('popstate', () => this.handleRoute());
        window.addEventListener('DOMContentLoaded', () => this.handleRoute());
    }

    register(path, component) {
        this.routes[path] = component;
    }

    navigate(path) {
        window.history.pushState({}, path, window.location.origin + '/#' + path);
        this.handleRoute();
    }

    handleRoute() {
        const hash = window.location.hash.slice(1) || '/';
        const route = this.routes[hash] || this.routes['/'];

        if (route) {
            this.currentRoute = route;
            if (typeof route === 'function') {
                const component = new route();
                component.mount('#app');
            } else {
                route.mount('#app');
            }
        }
    }
}

// API helper
class API {
    constructor() {
        this.baseURL = '/api';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    async request(endpoint, options = {}) {
        const url = endpoint.startsWith('http') ? endpoint : this.baseURL + endpoint;

        const config = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            ...options
        };

        if (options.body && typeof options.body === 'object') {
            config.body = JSON.stringify(options.body);
        }

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    }

    post(endpoint, body) {
        return this.request(endpoint, { method: 'POST', body });
    }

    put(endpoint, body) {
        return this.request(endpoint, { method: 'PUT', body });
    }

    delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
}

// Global instances
const router = new Router();
const api = new API();

// Store for global state management
class Store {
    constructor() {
        this.state = {
            user: null,
            isAuthenticated: false,
        };
        this.listeners = [];
    }

    getState() {
        return this.state;
    }

    setState(newState) {
        this.state = { ...this.state, ...newState };
        this.notify();
    }

    subscribe(listener) {
        this.listeners.push(listener);
        return () => {
            this.listeners = this.listeners.filter(l => l !== listener);
        };
    }

    notify() {
        this.listeners.forEach(listener => listener(this.state));
    }
}

const store = new Store();
