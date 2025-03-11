/**
 * Docker Events WebSocket Client
 * 
 * This module provides functionality to connect to Docker events via WebSockets
 * and handle real-time updates from the Docker engine.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Initialize the WebSocket connection
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

class DockerEventsClient {
    /**
     * Create a new Docker events client.
     *
     * @param {Object} options - Configuration options
     */
    constructor(options = {}) {
        this.options = Object.assign({
            defaultChannel: 'docker-events',
            onEvent: null,
            onContainerEvent: null,
            onImageEvent: null,
            onVolumeEvent: null,
            onNetworkEvent: null,
            onConnect: null,
            onDisconnect: null,
            debug: false,
        }, options);
        
        this.subscriptions = new Map();
        this.containerSubscriptions = new Map();
        this.connected = false;
    }
    
    /**
     * Start listening for Docker events.
     */
    connect() {
        this._log('Connecting to Docker events...');
        
        // Subscribe to the main Docker events channel
        const channel = window.Echo.channel(this.options.defaultChannel);
        
        channel.listen('.docker.event', (data) => {
            this._handleEvent(data);
        });
        
        this.connected = true;
        this.subscriptions.set(this.options.defaultChannel, channel);
        
        if (typeof this.options.onConnect === 'function') {
            this.options.onConnect();
        }
        
        return this;
    }
    
    /**
     * Subscribe to events for a specific event type.
     *
     * @param {string} type - Event type (container, image, volume, network)
     * @param {Function} callback - Callback function for events
     */
    subscribeToType(type, callback) {
        if (!this.connected) {
            this.connect();
        }
        
        const channelName = `docker-events.${type}`;
        
        if (!this.subscriptions.has(channelName)) {
            const channel = window.Echo.channel(channelName);
            
            channel.listen('.docker.event', (data) => {
                if (typeof callback === 'function') {
                    callback(data);
                }
                this._handleEvent(data);
            });
            
            this.subscriptions.set(channelName, channel);
            this._log(`Subscribed to ${type} events`);
        }
        
        return this;
    }
    
    /**
     * Subscribe to events for a specific container.
     *
     * @param {string} containerId - Container ID
     * @param {Function} callback - Callback function for container events
     */
    subscribeToContainer(containerId, callback) {
        if (!this.connected) {
            this.connect();
        }
        
        const channelName = `docker-container.${containerId}`;
        
        if (!this.containerSubscriptions.has(containerId)) {
            // This requires authentication for private channels
            const channel = window.Echo.private(channelName);
            
            channel.listen('.docker.event', (data) => {
                if (typeof callback === 'function') {
                    callback(data);
                }
                this._handleEvent(data);
            });
            
            this.containerSubscriptions.set(containerId, channel);
            this._log(`Subscribed to container ${containerId}`);
        }
        
        return this;
    }
    
    /**
     * Subscribe to container logs via server-sent events.
     *
     * @param {string} containerId - Container ID
     * @param {Function} callback - Callback function for log entries
     */
    subscribeToContainerLogs(containerId, callback) {
        const evtSource = new EventSource(`/api/docker/containers/${containerId}/logs/stream`);
        
        evtSource.onmessage = (event) => {
            if (typeof callback === 'function') {
                callback(event.data);
            }
        };
        
        evtSource.onerror = (error) => {
            this._log('EventSource error:', error);
            evtSource.close();
        };
        
        return evtSource;
    }
    
    /**
     * Stop listening for all Docker events.
     */
    disconnect() {
        // Leave all channels
        this.subscriptions.forEach((channel, name) => {
            window.Echo.leave(name);
        });
        
        this.containerSubscriptions.forEach((channel, containerId) => {
            window.Echo.leave(`docker-container.${containerId}`);
        });
        
        this.subscriptions.clear();
        this.containerSubscriptions.clear();
        this.connected = false;
        
        if (typeof this.options.onDisconnect === 'function') {
            this.options.onDisconnect();
        }
        
        this._log('Disconnected from Docker events');
        
        return this;
    }
    
    /**
     * Handle a Docker event.
     *
     * @param {Object} data - Event data
     * @private
     */
    _handleEvent(data) {
        this._log('Event received:', data);
        
        if (typeof this.options.onEvent === 'function') {
            this.options.onEvent(data);
        }
        
        // Call specific handlers based on event type
        if (data.event && data.event.Type) {
            const type = data.event.Type;
            const handlerName = `on${type.charAt(0).toUpperCase() + type.slice(1)}Event`;
            
            if (typeof this.options[handlerName] === 'function') {
                this.options[handlerName](data);
            }
        }
    }
    
    /**
     * Log a message if debugging is enabled.
     *
     * @param {...*} args - Arguments to log
     * @private
     */
    _log(...args) {
        if (this.options.debug) {
            console.log('[DockerEventsClient]', ...args);
        }
    }
}

export default DockerEventsClient; 