/**
 * SEGREBIN Ultrasonic Sensor Real-time Updates
 * This script handles real-time bin level updates from ultrasonic sensors
 * and updates the dashboard accordingly
 */

class UltrasonicSensorManager {
    constructor() {
        this.updateInterval = 30000; // 30 seconds
        this.socket = null;
        this.binElements = new Map();
        this.alertSound = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        
        this.init();
    }

    init() {
        this.initializeBinElements();
        this.setupEventListeners();
        this.startRealTimeUpdates();
        this.loadInitialBinData();
        
        // Initialize alert sound
        this.initAlertSound();
        
        console.log('SEGREBIN Ultrasonic Sensor Manager initialized');
    }

    initializeBinElements() {
        // Map bin elements by their identifiers
        const binCircles = document.querySelectorAll('.bin-circle');
        binCircles.forEach(circle => {
            const binName = circle.querySelector('.bin-name')?.textContent;
            const location = circle.closest('.bin-levels')?.querySelector('h2')?.textContent;
            
            if (binName && location) {
                const binKey = `${location}_${binName}`;
                this.binElements.set(binKey, {
                    circle: circle.querySelector('.circle'),
                    percentage: circle.querySelector('.circle span'),
                    location: location,
                    wasteType: binName
                });
            }
        });

        console.log(`Initialized ${this.binElements.size} bin elements`);
    }

    setupEventListeners() {
        // Setup notification sound toggle
        const soundToggle = document.getElementById('soundToggle');
        if (soundToggle) {
            soundToggle.addEventListener('change', (e) => {
                this.toggleSound(e.target.checked);
            });
        }

        // Setup manual refresh button
        const refreshBtn = document.getElementById('refreshBinLevels');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadInitialBinData();
            });
        }

        // Setup bin level threshold settings
        this.setupThresholdSettings();
    }

    setupThresholdSettings() {
        const thresholdForm = document.getElementById('thresholdForm');
        if (thresholdForm) {
            thresholdForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveThresholdSettings();
            });
        }
    }

    startRealTimeUpdates() {
        // Start polling for updates
        setInterval(() => {
            this.fetchBinUpdates();
        }, this.updateInterval);

        // Also try to establish WebSocket connection if available
        this.attemptWebSocketConnection();
    }

    async loadInitialBinData() {
        try {
            const response = await fetch('php/ultrasonic_sensor.php');
            const data = await response.json();
            
            if (data.success) {
                this.updateBinDisplay(data.data);
                this.updateStatistics(data.data);
            } else {
                console.error('Failed to load bin data:', data.error);
            }
        } catch (error) {
            console.error('Error loading bin data:', error);
        }
    }

    async fetchBinUpdates() {
        try {
            const response = await fetch('php/ultrasonic_sensor.php');
            const data = await response.json();
            
            if (data.success) {
                this.processBinUpdates(data.data);
            }
        } catch (error) {
            console.error('Error fetching bin updates:', error);
        }
    }

    processBinUpdates(binData) {
        let hasChanges = false;
        let alerts = [];

        binData.forEach(bin => {
            const binKey = `${bin.location}_${bin.waste_type}`;
            const element = this.binElements.get(binKey);
            
            if (element) {
                const currentLevel = parseFloat(element.percentage.textContent);
                const newLevel = parseFloat(bin.current_fill_level || bin.fill_level);
                
                if (Math.abs(currentLevel - newLevel) > 1) { // Only update if change is significant
                    hasChanges = true;
                    this.updateBinElement(element, newLevel, bin);
                    
                    // Check for alerts
                    if (newLevel >= 90) {
                        alerts.push({
                            type: 'critical',
                            message: `${bin.location} - ${bin.waste_type} bin is ${newLevel}% full!`,
                            bin: bin
                        });
                    } else if (newLevel >= 70 && currentLevel < 70) {
                        alerts.push({
                            type: 'warning',
                            message: `${bin.location} - ${bin.waste_type} bin needs attention (${newLevel}%)`,
                            bin: bin
                        });
                    }
                }
            }
        });

        if (hasChanges) {
            this.updateDashboard();
        }

        if (alerts.length > 0) {
            this.showAlerts(alerts);
        }
    }

    updateBinElement(element, newLevel, binData) {
        // Update percentage display
        if (element.percentage) {
            element.percentage.textContent = `${newLevel}%`;
        }

        // Update circle class and styling
        if (element.circle) {
            // Remove old level classes
            element.circle.classList.remove('level-low', 'level-medium', 'level-high', 'level-full');
            
            // Add new level class
            let newClass = 'level-low';
            if (newLevel >= 90) {
                newClass = 'level-full';
            } else if (newLevel >= 70) {
                newClass = 'level-high';
            } else if (newLevel >= 40) {
                newClass = 'level-medium';
            }
            
            element.circle.classList.add(newClass);
            
            // Add update animation
            this.addUpdateAnimation(element.circle);
        }

        // Update last updated timestamp if available
        if (binData.last_updated) {
            this.updateLastUpdated(element, binData.last_updated);
        }
    }

    addUpdateAnimation(element) {
        element.style.transform = 'scale(1.1)';
        element.style.boxShadow = '0 0 20px rgba(59, 130, 246, 0.8)';
        
        setTimeout(() => {
            element.style.transform = 'scale(1)';
            element.style.boxShadow = '';
        }, 500);
    }

    updateLastUpdated(element, timestamp) {
        const lastUpdatedElement = element.closest('.bin-circle').querySelector('.last-updated');
        if (lastUpdatedElement) {
            lastUpdatedElement.textContent = `Updated: ${this.formatTimestamp(timestamp)}`;
        }
    }

    formatTimestamp(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        
        if (diffMins < 1) {
            return 'Just now';
        } else if (diffMins < 60) {
            return `${diffMins} min ago`;
        } else if (diffHours < 24) {
            return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        } else {
            return date.toLocaleDateString();
        }
    }

    updateDashboard() {
        // Update statistics
        this.updateStatistics();
        
        // Update charts if they exist
        this.updateCharts();
        
        // Update notification badge
        this.updateNotificationBadge();
    }

    updateStatistics() {
        // Count bins by status
        const statusCounts = { low: 0, medium: 0, high: 0, full: 0 };
        
        this.binElements.forEach((element, key) => {
            const percentage = parseFloat(element.percentage.textContent);
            if (percentage >= 90) statusCounts.full++;
            else if (percentage >= 70) statusCounts.high++;
            else if (percentage >= 40) statusCounts.medium++;
            else statusCounts.low++;
        });

        // Update dashboard stats
        this.updateStatCard('bins-low', statusCounts.low);
        this.updateStatCard('bins-medium', statusCounts.medium);
        this.updateStatCard('bins-high', statusCounts.high);
        this.updateStatCard('bins-full', statusCounts.full);
    }

    updateStatCard(statId, value) {
        const statElement = document.getElementById(statId);
        if (statElement) {
            const currentValue = parseInt(statElement.textContent);
            if (currentValue !== value) {
                statElement.textContent = value;
                statElement.style.color = '#10b981';
                setTimeout(() => {
                    statElement.style.color = '';
                }, 1000);
            }
        }
    }

    updateCharts() {
        // Update waste collection chart if it exists
        if (window.wasteChart) {
            // Add new data point
            const now = new Date();
            const newData = this.calculateTotalWaste();
            
            window.wasteChart.appendData([{
                data: [newData],
                timestamp: now.getTime()
            }]);
        }
    }

    calculateTotalWaste() {
        let total = 0;
        this.binElements.forEach((element) => {
            const percentage = parseFloat(element.percentage.textContent);
            // Estimate waste based on fill level (this is a simplified calculation)
            total += (percentage / 100) * 120; // Assuming 120L capacity per bin
        });
        return Math.round(total);
    }

    updateNotificationBadge() {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            const urgentBins = Array.from(this.binElements.values()).filter(element => {
                const percentage = parseFloat(element.percentage.textContent);
                return percentage >= 90;
            }).length;
            
            badge.textContent = urgentBins;
            badge.style.display = urgentBins > 0 ? 'block' : 'none';
        }
    }

    showAlerts(alerts) {
        alerts.forEach(alert => {
            // Play sound if enabled
            if (this.alertSound && this.isSoundEnabled) {
                this.alertSound.play().catch(e => console.log('Could not play alert sound'));
            }

            // Show visual notification
            this.showNotification(alert.message, alert.type);
            
            // Update map pins if they exist
            this.updateMapPin(alert.bin);
        });
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fa-solid ${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">&times;</button>
        `;

        // Add to notification container
        const container = document.querySelector('.notification-container') || document.body;
        container.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);

        // Close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => notification.remove());
        }
    }

    getNotificationIcon(type) {
        switch (type) {
            case 'critical': return 'fa-exclamation-triangle';
            case 'warning': return 'fa-exclamation-circle';
            case 'success': return 'fa-check-circle';
            default: return 'fa-info-circle';
        }
    }

    updateMapPin(bin) {
        // Find and update corresponding map pin
        const mapPin = document.querySelector(`[data-bin="${bin.waste_type}"][data-location="${bin.location}"]`);
        if (mapPin) {
            const icon = mapPin.querySelector('.map-pin-icon');
            if (icon) {
                icon.className = 'map-pin-icon';
                
                if (bin.fill_level >= 90) {
                    icon.classList.add('alert');
                } else if (bin.fill_level >= 70) {
                    icon.classList.add('warning');
                }
            }
        }
    }

    attemptWebSocketConnection() {
        // Try to establish WebSocket connection for real-time updates
        try {
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const wsUrl = `${protocol}//${window.location.host}/ws/ultrasonic`;
            
            this.socket = new WebSocket(wsUrl);
            
            this.socket.onopen = () => {
                console.log('WebSocket connected for real-time updates');
                this.isConnected = true;
                this.reconnectAttempts = 0;
            };
            
            this.socket.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    if (data.type === 'bin_update') {
                        this.processBinUpdates([data.bin]);
                    }
                } catch (error) {
                    console.error('Error parsing WebSocket message:', error);
                }
            };
            
            this.socket.onclose = () => {
                console.log('WebSocket disconnected');
                this.isConnected = false;
                this.attemptReconnection();
            };
            
            this.socket.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.isConnected = false;
            };
            
        } catch (error) {
            console.log('WebSocket not available, using polling fallback');
        }
    }

    attemptReconnection() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
            
            setTimeout(() => {
                this.attemptWebSocketConnection();
            }, 5000 * this.reconnectAttempts);
        } else {
            console.log('Max reconnection attempts reached, using polling fallback');
        }
    }

    initAlertSound() {
        try {
            this.alertSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
            this.isSoundEnabled = true;
        } catch (error) {
            console.log('Could not initialize alert sound');
            this.isSoundEnabled = false;
        }
    }

    toggleSound(enabled) {
        this.isSoundEnabled = enabled;
        localStorage.setItem('segrebin_sound_enabled', enabled);
    }

    saveThresholdSettings() {
        const form = document.getElementById('thresholdForm');
        const formData = new FormData(form);
        
        const settings = {
            low_threshold: formData.get('low_threshold'),
            medium_threshold: formData.get('medium_threshold'),
            high_threshold: formData.get('high_threshold'),
            critical_threshold: formData.get('critical_threshold')
        };
        
        localStorage.setItem('segrebin_thresholds', JSON.stringify(settings));
        
        // Show success message
        this.showNotification('Threshold settings saved successfully!', 'success');
    }

    // Public method to manually refresh bin levels
    refreshBinLevels() {
        this.loadInitialBinData();
    }

    // Public method to get current bin status
    getBinStatus() {
        const status = {};
        this.binElements.forEach((element, key) => {
            status[key] = {
                location: element.location,
                wasteType: element.wasteType,
                fillLevel: parseFloat(element.percentage.textContent),
                lastUpdated: element.lastUpdated
            };
        });
        return status;
    }
}

// Initialize the sensor manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a page with bin levels
    if (document.querySelector('.bin-levels')) {
        window.ultrasonicManager = new UltrasonicSensorManager();
        
        // Add global refresh function
        window.refreshBinLevels = () => {
            if (window.ultrasonicManager) {
                window.ultrasonicManager.refreshBinLevels();
            }
        };
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UltrasonicSensorManager;
}
