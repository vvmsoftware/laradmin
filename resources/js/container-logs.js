/**
 * Docker Container Logs Viewer
 * 
 * This module provides functionality to stream and display container logs
 * in real-time using server-sent events.
 */

class ContainerLogsViewer {
    /**
     * Create a new container logs viewer.
     *
     * @param {Object} options - Configuration options
     */
    constructor(options = {}) {
        this.options = Object.assign({
            containerId: null,
            elementId: 'container-logs',
            maxEntries: 1000,
            autoScroll: true,
            timestampFormat: 'HH:mm:ss.SSS',
            showTimestamps: true,
            filterPattern: null,
            debug: false,
            onConnect: null,
            onDisconnect: null,
            onError: null,
            formatLogEntry: null,
        }, options);
        
        this.eventSource = null;
        this.logElement = null;
        this.entries = [];
        this.connected = false;
        
        if (!this.options.containerId) {
            throw new Error('Container ID is required');
        }
    }
    
    /**
     * Initialize the logs viewer.
     */
    init() {
        this.logElement = document.getElementById(this.options.elementId);
        
        if (!this.logElement) {
            throw new Error(`Element with ID "${this.options.elementId}" not found`);
        }
        
        // Add classes for styling
        this.logElement.classList.add('container-logs-viewer');
        
        // Create controls
        this.createControls();
        
        return this;
    }
    
    /**
     * Create control buttons for the logs viewer.
     */
    createControls() {
        const controlsContainer = document.createElement('div');
        controlsContainer.className = 'logs-controls';
        
        // Connect/disconnect button
        const toggleButton = document.createElement('button');
        toggleButton.textContent = 'Connect';
        toggleButton.className = 'btn btn-primary btn-sm';
        toggleButton.addEventListener('click', () => {
            if (this.connected) {
                this.disconnect();
                toggleButton.textContent = 'Connect';
                toggleButton.className = 'btn btn-primary btn-sm';
            } else {
                this.connect();
                toggleButton.textContent = 'Disconnect';
                toggleButton.className = 'btn btn-danger btn-sm';
            }
        });
        
        // Clear logs button
        const clearButton = document.createElement('button');
        clearButton.textContent = 'Clear';
        clearButton.className = 'btn btn-secondary btn-sm';
        clearButton.addEventListener('click', () => {
            this.clear();
        });
        
        // Auto-scroll toggle
        const autoScrollLabel = document.createElement('label');
        autoScrollLabel.className = 'auto-scroll-label';
        
        const autoScrollCheckbox = document.createElement('input');
        autoScrollCheckbox.type = 'checkbox';
        autoScrollCheckbox.checked = this.options.autoScroll;
        autoScrollCheckbox.addEventListener('change', (e) => {
            this.options.autoScroll = e.target.checked;
        });
        
        autoScrollLabel.appendChild(autoScrollCheckbox);
        autoScrollLabel.appendChild(document.createTextNode(' Auto-scroll'));
        
        // Filter input
        const filterLabel = document.createElement('label');
        filterLabel.className = 'filter-label';
        
        const filterInput = document.createElement('input');
        filterInput.type = 'text';
        filterInput.placeholder = 'Filter logs...';
        filterInput.className = 'form-control form-control-sm';
        filterInput.addEventListener('input', (e) => {
            this.options.filterPattern = e.target.value ? new RegExp(e.target.value, 'i') : null;
            this.renderLogs();
        });
        
        filterLabel.appendChild(document.createTextNode('Filter: '));
        filterLabel.appendChild(filterInput);
        
        // Add controls to container
        controlsContainer.appendChild(toggleButton);
        controlsContainer.appendChild(clearButton);
        controlsContainer.appendChild(autoScrollLabel);
        controlsContainer.appendChild(filterLabel);
        
        // Add controls before log content
        this.logElement.parentNode.insertBefore(controlsContainer, this.logElement);
        
        // Create logs container
        const logsContainer = document.createElement('div');
        logsContainer.className = 'logs-container';
        this.logsContainer = logsContainer;
        
        // Replace original element with logs container
        this.logElement.parentNode.replaceChild(logsContainer, this.logElement);
        
        // Add log content element back inside container
        this.logElement = document.createElement('pre');
        this.logElement.className = 'logs-content';
        this.logElement.id = this.options.elementId;
        logsContainer.appendChild(this.logElement);
    }
    
    /**
     * Connect to the container logs stream.
     */
    connect() {
        if (this.connected) {
            return this;
        }
        
        this._log('Connecting to container logs...');
        
        this.eventSource = new EventSource(`/api/docker/containers/${this.options.containerId}/logs/stream`);
        
        this.eventSource.onmessage = (event) => {
            this.addLogEntry(event.data);
        };
        
        this.eventSource.onerror = (error) => {
            this._log('EventSource error:', error);
            
            if (typeof this.options.onError === 'function') {
                this.options.onError(error);
            }
            
            this.disconnect();
        };
        
        this.connected = true;
        
        if (typeof this.options.onConnect === 'function') {
            this.options.onConnect();
        }
        
        return this;
    }
    
    /**
     * Disconnect from the container logs stream.
     */
    disconnect() {
        if (!this.connected) {
            return this;
        }
        
        this._log('Disconnecting from container logs...');
        
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
        
        this.connected = false;
        
        if (typeof this.options.onDisconnect === 'function') {
            this.options.onDisconnect();
        }
        
        return this;
    }
    
    /**
     * Add a log entry.
     *
     * @param {string} logData - Log data
     */
    addLogEntry(logData) {
        // Parse timestamp if the log has it
        let timestamp = new Date();
        let message = logData;
        
        // Docker logs often have timestamps at the beginning (2023-01-02T15:04:05.000000000Z)
        const timestampMatch = logData.match(/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+Z)/);
        
        if (timestampMatch) {
            timestamp = new Date(timestampMatch[1]);
            message = logData.substring(timestampMatch[0].length).trim();
        }
        
        const entry = {
            timestamp,
            message,
            raw: logData,
        };
        
        this.entries.push(entry);
        
        // Limit the number of entries
        if (this.entries.length > this.options.maxEntries) {
            this.entries = this.entries.slice(-this.options.maxEntries);
        }
        
        this.renderLogs();
        
        return this;
    }
    
    /**
     * Render the log entries.
     */
    renderLogs() {
        if (!this.logElement) {
            return this;
        }
        
        // Filter entries if a pattern is set
        const entriesToRender = this.options.filterPattern
            ? this.entries.filter(entry => this.options.filterPattern.test(entry.raw))
            : this.entries;
        
        // Format entries
        const formattedEntries = entriesToRender.map(entry => {
            if (typeof this.options.formatLogEntry === 'function') {
                return this.options.formatLogEntry(entry);
            }
            
            // Default formatting
            if (this.options.showTimestamps) {
                const formattedTime = this.formatTimestamp(entry.timestamp);
                return `[${formattedTime}] ${entry.message}`;
            }
            
            return entry.message;
        });
        
        // Update the log element
        this.logElement.textContent = formattedEntries.join('\n');
        
        // Auto-scroll to bottom if enabled
        if (this.options.autoScroll) {
            this.scrollToBottom();
        }
        
        return this;
    }
    
    /**
     * Format a timestamp.
     *
     * @param {Date} date - The timestamp to format
     * @returns {string} Formatted timestamp
     */
    formatTimestamp(date) {
        // Simple formatting, can be extended with a library like date-fns
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const seconds = date.getSeconds().toString().padStart(2, '0');
        const milliseconds = date.getMilliseconds().toString().padStart(3, '0');
        
        const format = this.options.timestampFormat.replace('HH', hours)
            .replace('mm', minutes)
            .replace('ss', seconds)
            .replace('SSS', milliseconds);
            
        return format;
    }
    
    /**
     * Scroll to the bottom of the logs.
     */
    scrollToBottom() {
        if (this.logsContainer) {
            this.logsContainer.scrollTop = this.logsContainer.scrollHeight;
        }
        
        return this;
    }
    
    /**
     * Clear all log entries.
     */
    clear() {
        this.entries = [];
        
        if (this.logElement) {
            this.logElement.textContent = '';
        }
        
        return this;
    }
    
    /**
     * Log a message if debugging is enabled.
     *
     * @param {...*} args - Arguments to log
     * @private
     */
    _log(...args) {
        if (this.options.debug) {
            console.log('[ContainerLogsViewer]', ...args);
        }
    }
}

export default ContainerLogsViewer; 