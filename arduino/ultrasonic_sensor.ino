/*
 * SEGREBIN Ultrasonic Sensor Code
 * Compatible with Arduino Uno, Nano, ESP32, ESP8266
 * 
 * This code reads distance from an ultrasonic sensor (HC-SR04)
 * and sends the data to the SEGREBIN web server via HTTP POST
 * 
 * Hardware Requirements:
 * - Ultrasonic Sensor (HC-SR04)
 * - Arduino/ESP32/ESP8266
 * - WiFi module (for ESP boards) or Ethernet shield (for Arduino)
 * - Power supply
 * 
 * Pin Connections:
 * - VCC to 5V (or 3.3V for ESP32)
 * - GND to GND
 * - TRIG to digital pin 9 (configurable)
 * - ECHO to digital pin 10 (configurable)
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// WiFi Configuration
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// Server Configuration
const char* serverUrl = "http://segrebin.com/php/ultrasonic_sensor.php";
#define READING_INTERVAL 30000 // 30 seconds between readings

// Sensor Configuration
// Sensor 1
#define TRIG1 5
#define ECHO1 18

// Sensor 2
#define TRIG2 17
#define ECHO2 16

// Sensor 3
#define TRIG3 4
#define ECHO3 2

// Bin Configuration
#define BIN_HEIGHT 100.0  // Height of bin in cm
#define EMPTY_DISTANCE 5.08  // Distance from sensor to empty bin bottom in cm
#define TOLERANCE 0.5  // ±0.5 cm tolerance

// Bin IDs for each sensor
const char* BIN_ID_1 = "BIN001";
const char* BIN_ID_2 = "BIN002";
const char* BIN_ID_3 = "BIN003";

// Waste types for each bin
const char* WASTE_TYPE_1 = "plastic";
const char* WASTE_TYPE_2 = "paper";
const char* WASTE_TYPE_3 = "styrofoam";

void setup() {
  Serial.begin(115200);
  
  // Initialize sensor pins
  pinMode(TRIG1, OUTPUT);
  pinMode(ECHO1, INPUT);
  
  pinMode(TRIG2, OUTPUT);
  pinMode(ECHO2, INPUT);
  
  pinMode(TRIG3, OUTPUT);
  pinMode(ECHO3, INPUT);
  
  // Connect to WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println();
  Serial.println("WiFi connected!");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  
  Serial.println("Ultrasonic Sensor System Ready!");
  Serial.println("================================");
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    // Read distances from all sensors
    float dist1 = readDistance(TRIG1, ECHO1);
    float dist2 = readDistance(TRIG2, ECHO2);
    float dist3 = readDistance(TRIG3, ECHO3);
    
    // Display local readings
    Serial.println("=== Sensor Readings ===");
    Serial.printf("Sensor 1 (Bin %s): %.2f cm\n", BIN_ID_1, dist1);
    Serial.printf("Sensor 2 (Bin %s): %.2f cm\n", BIN_ID_2, dist2);
    Serial.printf("Sensor 3 (Bin %s): %.2f cm\n", BIN_ID_3, dist3);
    
    // Check if objects are detected at target distance
    if (dist1 >= (EMPTY_DISTANCE - TOLERANCE) && dist1 <= (EMPTY_DISTANCE + TOLERANCE)) {
      Serial.printf("✅ Sensor 1 detected object at ~%.2f cm!\n", EMPTY_DISTANCE);
    }
    
    if (dist2 >= (EMPTY_DISTANCE - TOLERANCE) && dist2 <= (EMPTY_DISTANCE + TOLERANCE)) {
      Serial.printf("✅ Sensor 2 detected object at ~%.2f cm!\n", EMPTY_DISTANCE);
    }
    
    if (dist3 >= (EMPTY_DISTANCE - TOLERANCE) && dist3 <= (EMPTY_DISTANCE + TOLERANCE)) {
      Serial.printf("✅ Sensor 3 detected object at ~%.2f cm!\n", EMPTY_DISTANCE);
    }
    
    // Send data to server for each bin
    bool success1 = sendDataToServer(BIN_ID_1, WASTE_TYPE_1, dist1);
    bool success2 = sendDataToServer(BIN_ID_2, WASTE_TYPE_2, dist2);
    bool success3 = sendDataToServer(BIN_ID_3, WASTE_TYPE_3, dist3);
    
    // Report transmission status
    Serial.println("=== Transmission Status ===");
    Serial.printf("Bin %s: %s\n", BIN_ID_1, success1 ? "✅ Sent" : "❌ Failed");
    Serial.printf("Bin %s: %s\n", BIN_ID_2, success2 ? "✅ Sent" : "❌ Failed");
    Serial.printf("Bin %s: %s\n", BIN_ID_3, success3 ? "✅ Sent" : "❌ Failed");
    
  } else {
    Serial.println("WiFi connection lost. Attempting to reconnect...");
    WiFi.reconnect();
  }
  
  Serial.println("================================");
  delay(READING_INTERVAL);
}

float readDistance(int trigPin, int echoPin) {
  // Send trigger pulse
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  
  // Read echo
  long duration = pulseIn(echoPin, HIGH);
  float distance = duration * 0.0343 / 2; // Convert to cm
  
  // Validate reading
  if (distance < 0 || distance > 400) {
    return -1; // Invalid reading
  }
  
  return distance;
}

bool sendDataToServer(const char* binId, const char* wasteType, float distance) {
  if (distance < 0) {
    Serial.printf("Invalid distance reading for %s: %.2f cm\n", binId, distance);
    return false;
  }
  
  HTTPClient http;
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload
  DynamicJsonDocument doc(512);
  doc["bin_id"] = binId;
  doc["waste_type"] = wasteType;
  doc["sensor_reading"] = distance;
  doc["bin_height"] = BIN_HEIGHT;
  doc["empty_distance"] = EMPTY_DISTANCE;
  doc["timestamp"] = millis(); // Simple timestamp
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.printf("Sending data for %s: %s\n", binId, jsonString.c_str());
  
  // Send POST request
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.printf("HTTP Response code: %d\n", httpResponseCode);
    Serial.printf("Response: %s\n", response.c_str());
    http.end();
    return true;
  } else {
    Serial.printf("Error code: %d\n", httpResponseCode);
    http.end();
    return false;
  }
}
