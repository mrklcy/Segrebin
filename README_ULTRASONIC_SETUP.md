# SEGREBIN Ultrasonic Sensor Integration

This document provides comprehensive setup instructions for integrating ultrasonic sensors with the SEGREBIN waste management system to enable real-time bin level monitoring.

## 🎯 Overview

The ultrasonic sensor system automatically monitors waste bin fill levels and provides real-time updates to the SEGREBIN dashboard. This enables:

- **Real-time monitoring** of bin fill levels
- **Automated alerts** when bins are full
- **Optimized collection routes** based on actual fill levels
- **Data analytics** for waste management optimization
- **Reduced operational costs** through efficient collection scheduling

## 🛠️ Hardware Requirements

### Required Components
- **Ultrasonic Sensor (HC-SR04)**: Distance measurement sensor
- **Microcontroller**: Arduino Uno/Nano, ESP32, or ESP8266
- **Power Supply**: 5V for Arduino, 3.3V for ESP boards
- **WiFi Module**: Built-in for ESP boards, external for Arduino
- **Mounting Hardware**: Sensor bracket, cables, connectors

### Recommended Setup
- **ESP32 Development Board**: Best balance of features and ease of use
- **HC-SR04 Ultrasonic Sensor**: Reliable and cost-effective
- **5V Power Supply**: Stable power for consistent readings
- **Waterproof Enclosure**: For outdoor bin installations

## 📋 Installation Steps

### 1. Hardware Assembly

#### Sensor Mounting
```
┌─────────────────┐
│   Bin Lid       │
├─────────────────┤
│  Ultrasonic     │
│    Sensor       │
├─────────────────┤
│   Waste Level   │
└─────────────────┘
```

**Mounting Guidelines:**
- Mount sensor at the top of the bin lid
- Ensure sensor faces downward toward waste
- Keep sensor 5-10cm above empty bin bottom
- Protect sensor from direct contact with waste

#### Wiring Diagram
```
ESP32/Arduino    HC-SR04
─────────────    ───────
VCC (5V/3.3V) → VCC
GND           → GND
GPIO9         → TRIG
GPIO10        → ECHO
```

### 2. Software Setup

#### Arduino/ESP32 Code
1. **Install Required Libraries:**
   ```bash
   # For ESP32/ESP8266
   Arduino IDE → Tools → Manage Libraries
   Search and install:
   - WiFi (built-in)
   - HTTPClient (built-in)
   - ArduinoJson
   ```

2. **Configure the Code:**
   - Open `arduino/ultrasonic_sensor.ino`
   - Update WiFi credentials
   - Set your server URL
   - Configure bin dimensions
   - Set unique bin ID

3. **Upload to Device:**
   - Connect device via USB
   - Select correct board and port
   - Upload code

#### Server Configuration
1. **Database Setup:**
   ```sql
   # Run the database schema
   mysql -u your_user -p your_database < database/sensor_schema.sql
   ```

2. **PHP API:**
   - Ensure `php/ultrasonic_sensor.php` is accessible
   - Verify database connection in `php/config.php`
   - Test API endpoint

3. **Web Dashboard:**
   - Include `js/ultrasonic_updates.js` in your HTML
   - Include `css/notifications.css` for notifications
   - Ensure proper file paths

### 3. Configuration

#### Bin Dimensions
```cpp
#define BIN_HEIGHT 100.0        // Height in cm
#define EMPTY_DISTANCE 5.0      // Distance from sensor to empty bottom
#define BIN_ID "BIN001"         // Unique identifier
```

#### Update Intervals
```cpp
#define READING_INTERVAL 30000  // 30 seconds (adjust as needed)
```

#### Alert Thresholds
```javascript
// Configure in dashboard
const thresholds = {
    low: 25,      // Below 25% - Normal
    medium: 40,   // 25-40% - Monitor
    high: 70,     // 40-70% - Attention
    critical: 90  // Above 90% - Immediate action
};
```

## 🔧 Testing & Calibration

### Initial Testing
1. **Power On Test:**
   - Verify sensor powers up
   - Check WiFi connection
   - Monitor serial output

2. **Distance Calibration:**
   - Test with known distances
   - Adjust `EMPTY_DISTANCE` value
   - Verify readings are consistent

3. **API Testing:**
   ```bash
   # Test sensor data endpoint
   curl -X POST http://your-domain.com/php/ultrasonic_sensor.php \
     -H "Content-Type: application/json" \
     -d '{
       "bin_id": "BIN001",
       "sensor_reading": 20.0,
       "bin_height": 100.0,
       "empty_distance": 5.0,
       "timestamp": "2024-01-15T10:30:00Z"
     }'
   ```

### Calibration Process
1. **Empty Bin Calibration:**
   - Ensure bin is completely empty
   - Measure distance from sensor to bottom
   - Update `EMPTY_DISTANCE` value

2. **Full Bin Calibration:**
   - Fill bin to known capacity
   - Verify sensor reading matches expected
   - Adjust calculations if necessary

3. **Environmental Factors:**
   - Test in different weather conditions
   - Account for temperature variations
   - Consider humidity effects

## 📊 Dashboard Integration

### Real-time Updates
The dashboard automatically:
- Updates bin level displays every 30 seconds
- Shows visual indicators for different fill levels
- Displays alerts for bins needing attention
- Updates statistics and charts

### Manual Controls
- **Refresh Button**: Manually update bin levels
- **Sound Toggle**: Enable/disable alert sounds
- **Threshold Settings**: Customize alert levels
- **Notification Management**: Control notification display

## 🚨 Troubleshooting

### Common Issues

#### Sensor Not Responding
- **Check power supply** - Ensure stable voltage
- **Verify connections** - Check TRIG/ECHO pins
- **Test with multimeter** - Verify signal integrity
- **Check sensor orientation** - Ensure proper mounting

#### Inaccurate Readings
- **Calibrate distances** - Verify empty/full measurements
- **Check for interference** - Avoid metal objects nearby
- **Clean sensor** - Remove dust/debris
- **Verify bin dimensions** - Update height values

#### WiFi Connection Issues
- **Check credentials** - Verify SSID/password
- **Signal strength** - Ensure adequate WiFi coverage
- **Power stability** - Unstable power can cause disconnects
- **Router settings** - Check for connection limits

#### API Errors
- **Database connection** - Verify credentials and connectivity
- **File permissions** - Ensure PHP files are readable
- **Server logs** - Check error logs for specific issues
- **CORS settings** - Verify cross-origin requests are allowed

### Debug Mode
Enable debug output in Arduino code:
```cpp
#define DEBUG_MODE true

if (DEBUG_MODE) {
    Serial.print("Distance: ");
    Serial.println(distance);
    Serial.print("Fill Level: ");
    Serial.println(fillLevel);
}
```

## 📈 Performance Optimization

### Reading Frequency
- **High-traffic areas**: 15-30 second intervals
- **Medium-traffic areas**: 1-2 minute intervals
- **Low-traffic areas**: 5-10 minute intervals

### Power Management
- **Deep sleep mode** for battery-powered installations
- **Wake on motion** for energy efficiency
- **Solar power** for remote locations

### Data Management
- **Local storage** for offline operation
- **Data compression** for bandwidth optimization
- **Batch updates** for multiple sensors

## 🔒 Security Considerations

### Network Security
- **WiFi encryption** (WPA2/WPA3)
- **Firewall rules** for sensor endpoints
- **VPN access** for remote management
- **Regular updates** for security patches

### Data Protection
- **HTTPS encryption** for data transmission
- **Authentication** for API access
- **Input validation** for sensor data
- **Audit logging** for system access

## 📱 Mobile Integration

### Mobile App Features
- **Real-time notifications** for urgent alerts
- **Collection route optimization** based on fill levels
- **Offline data sync** for field workers
- **Photo documentation** of collection activities

### Push Notifications
- **Critical alerts** for full bins
- **Maintenance reminders** for sensor health
- **Collection confirmations** for completed routes
- **System status updates** for administrators

## 🔄 Maintenance Schedule

### Daily
- Check sensor readings for anomalies
- Verify WiFi connectivity
- Monitor system alerts

### Weekly
- Clean sensor surfaces
- Review error logs
- Update firmware if needed

### Monthly
- Calibrate sensors
- Review performance metrics
- Update system configurations

### Quarterly
- Full system health check
- Performance optimization
- Security audit

## 📞 Support & Resources

### Documentation
- **API Reference**: Detailed endpoint documentation
- **Troubleshooting Guide**: Common issues and solutions
- **Video Tutorials**: Step-by-step setup guides
- **FAQ Section**: Frequently asked questions

### Community Support
- **Forum**: User community discussions
- **GitHub Issues**: Bug reports and feature requests
- **Email Support**: Direct technical assistance
- **Live Chat**: Real-time help during business hours

### Training Resources
- **Webinars**: Regular training sessions
- **Certification Program**: System administrator training
- **Best Practices Guide**: Industry recommendations
- **Case Studies**: Successful implementations

## 🚀 Future Enhancements

### Planned Features
- **AI-powered predictions** for fill level forecasting
- **IoT sensor integration** for additional metrics
- **Blockchain verification** for collection records
- **Machine learning** for route optimization

### Integration Possibilities
- **Smart city platforms** for municipal waste management
- **Logistics systems** for collection vehicle management
- **Environmental monitoring** for sustainability tracking
- **Financial systems** for cost analysis and billing

---

## 📝 Quick Start Checklist

- [ ] Hardware assembled and mounted
- [ ] Arduino/ESP32 code uploaded and configured
- [ ] Database schema created and populated
- [ ] PHP API tested and accessible
- [ ] Dashboard integration completed
- [ ] Initial calibration performed
- [ ] Alert thresholds configured
- [ ] Notification system tested
- [ ] Mobile access verified
- [ ] Documentation provided to users

## 🎉 Success Indicators

- **Real-time updates** working consistently
- **Alert system** responding to fill level changes
- **Dashboard** displaying accurate information
- **Mobile notifications** delivering timely alerts
- **Collection efficiency** improved
- **User satisfaction** with system performance

---

*For additional support, contact the SEGREBIN technical team at support@segrebin.com*

