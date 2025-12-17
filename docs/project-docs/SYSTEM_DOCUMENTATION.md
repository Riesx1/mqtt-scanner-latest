# MQTT Scanner System - Complete Analysis & Test Guide

## 📋 System Overview

Your project scans **ESP32 IoT devices** publishing sensor data to MQTT brokers with **mixed security profiles** (secure TLS + insecure plain text).

### Architecture

```
┌──────────────┐
│   ESP32      │  WiFi: ZurKun-TIME-2.4GHZ
│ 192.168.100.140 │  IP: 192.168.100.56 (MQTT server)
└──────┬───────┘
       │ Publishes every 3 seconds
       │
       ├─► Port 8883 (TLS) ──┐
       │   • DHT11 (temp/hum) │  Requires: testuser/testpass
       │   • LDR (light)      │  Encryption: TLS 1.2+
       │                      │
       └─► Port 1883 (Plain)──┤
           • PIR (motion)     │  No auth, No encryption
                              │
┌─────────────────────────────▼──────────────────────────────┐
│          MQTT Brokers (Docker)                              │
│  • mosq_secure (eclipse-mosquitto:2.0) - Port 8883         │
│  • mosq_insecure (eclipse-mosquitto:2.0) - Port 1883       │
└─────────────────────────────┬──────────────────────────────┘
                              │
                              │ Subscribes & Scans
                              │
┌─────────────────────────────▼──────────────────────────────┐
│          Flask MQTT Scanner (Python)                        │
│  • Port: 5000                                               │
│  • File: mqtt-scanner/app.py                                │
│  • Scanner: mqtt-scanner/scanner.py                         │
│  • API Key: my-very-secret-flask-key-CHANGEME               │
└─────────────────────────────┬──────────────────────────────┘
                              │
                              │ HTTP API (/api/scan, /api/results)
                              │
┌─────────────────────────────▼──────────────────────────────┐
│          Laravel Dashboard (PHP)                            │
│  • Port: 8000                                               │
│  • Controller: app/Http/Controllers/MqttScannerController   │
│  • View: resources/views/dashboard.blade.php                │
└─────────────────────────────────────────────────────────────┘
```

## 🔍 Sensor Specifications

### 1. DHT11 - Temperature & Humidity (SECURE)

-   **GPIO Pin**: 4
-   **Topic**: `sensors/faris/dht_secure`
-   **Port**: 8883 (TLS)
-   **Authentication**: Required (testuser/testpass)
-   **JSON Format**:
    ```json
    { "temp_c": 31.8, "hum_pct": 64.0 }
    ```
-   **ESP32 Output**:
    ```
    [SECURE DHT] ✓ Published: {"temp_c":31.8,"hum_pct":64.0}
    ```

### 2. LDR - Light Sensor (SECURE)

-   **GPIO Pin**: 34 (ADC)
-   **Topic**: `sensors/faris/ldr_secure`
-   **Port**: 8883 (TLS)
-   **Authentication**: Required (testuser/testpass)
-   **JSON Format**:
    ```json
    { "ldr_pct": 47.3, "ldr_raw": 1938 }
    ```
-   **ESP32 Output**:
    ```
    [SECURE LDR] ✓ Published: {"ldr_pct":47.3,"ldr_raw":1938}
    ```

### 3. PIR - Motion Sensor (INSECURE)

-   **GPIO Pin**: 27
-   **Topic**: `sensors/faris/pir_insecure`
-   **Port**: 1883 (Plain text)
-   **Authentication**: None (anonymous)
-   **JSON Format**:
    ```json
    { "pir": 1 }
    ```
    -   `1` = Motion detected
    -   `0` = No motion
-   **ESP32 Output**:
    ```
    [INSECURE PIR] ✓ Published: {"pir":1}
    ```

## 🚀 Quick Start Testing

### Step 1: Verify All Services Running

```powershell
# Check Docker containers
docker ps
# Expected: mosq_insecure (1883) and mosq_secure (8883)

# Check Flask (should show port 5000)
netstat -ano | findstr :5000

# Check Laravel (should show port 8000)
netstat -ano | findstr :8000
```

### Step 2: Test ESP32 Sensor Publishing

```powershell
# Run the test script
python test_esp32_sensors.py
```

**Expected output (every 3 seconds):**

```
[01:45:03] 🔒 SECURE - sensors/faris/dht_secure
  🌡️ Temp: 31.8°C, Humidity: 64.0%
  JSON: {"temp_c": 31.8, "hum_pct": 64.0}

[01:45:03] 🔒 SECURE - sensors/faris/ldr_secure
  💡 Light: 47.3% (Raw: 1938)
  JSON: {"ldr_pct": 47.3, "ldr_raw": 1938}

[01:45:03] ⚠️ INSECURE - sensors/faris/pir_insecure
  👁️ Motion: DETECTED ⚠️
  JSON: {"pir": 1}
```

If you see this output → ✅ **ESP32 is working correctly!**

### Step 3: Test Flask Scanner

```powershell
# Test the scan endpoint (scans 192.168.100.56)
Invoke-WebRequest -Uri "http://127.0.0.1:5000/api/scan" `
    -Method POST `
    -Headers @{
        "X-API-KEY" = "my-very-secret-flask-key-CHANGEME"
        "Content-Type" = "application/json"
    } `
    -Body (@{
        target = "192.168.100.56"
        creds = @{
            user = "testuser"
            pass = "testpass"
        }
        listen_duration = 5
        capture_all_topics = $true
    } | ConvertTo-Json)

# Get the results
$response = Invoke-WebRequest -Uri "http://127.0.0.1:5000/api/results" `
    -Method GET `
    -Headers @{"X-API-KEY" = "my-very-secret-flask-key-CHANGEME"}

$response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
```

### Step 4: Test Laravel Dashboard

1. Open browser: **http://127.0.0.1:8000**
2. Login (or register if first time)
3. Navigate to **Dashboard**
4. In the scan form:
    - **Target**: `192.168.100.56`
    - **Username**: `testuser`
    - **Password**: `testpass`
5. Click **Start Scan**
6. Wait 5-10 seconds

## ✅ Expected Dashboard Results

### Summary Cards

```
┌────────────────┐  ┌────────────────┐  ┌────────────────┐
│ Total Scanned  │  │ Open Brokers   │  │ Auth Failures  │
│       2        │  │       2        │  │       0        │
└────────────────┘  └────────────────┘  └────────────────┘
```

### Scan Results Table

| IP             | Port | Security        | Sensor Type | Publisher Topic            | Sensor Data                     | TLS Details                        |
| -------------- | ---- | --------------- | ----------- | -------------------------- | ------------------------------- | ---------------------------------- |
| 192.168.100.56 | 8883 | 🔒 Secure (TLS) | 🌡️ DHT      | sensors/faris/dht_secure   | Temp: 31.8°C<br>Humidity: 64.0% | TLS 1.2+<br>Self-signed cert       |
| 192.168.100.56 | 8883 | 🔒 Secure (TLS) | 💡 LDR      | sensors/faris/ldr_secure   | Light: 47.3%<br>Raw: 1938       | TLS 1.2+<br>Self-signed cert       |
| 192.168.100.56 | 1883 | ⚠️ Insecure     | 👁️ PIR      | sensors/faris/pir_insecure | Motion: DETECTED                | ⚠️ Unencrypted<br>Anonymous access |

**Note**: You might see 2 or 3 rows depending on how the scanner groups the results:

-   Option A: 2 rows (one for each port with multiple publishers)
-   Option B: 3 rows (one per sensor/topic)

## 🐛 Troubleshooting Guide

### Problem: No sensors detected in dashboard

#### Solution 1: Verify ESP32 is publishing

```powershell
python test_esp32_sensors.py
```

-   Should receive messages every 3 seconds
-   If no messages: Check ESP32 serial monitor, verify WiFi connection

#### Solution 2: Check Flask scanner

```powershell
# Check Flask logs
cd mqtt-scanner
# Look for errors in the terminal where app.py is running
```

#### Solution 3: Increase listen duration

-   In dashboard, edit the scan request to listen longer (try 10 seconds)

### Problem: "Connection refused" for port 8883

#### Solution: Check secure broker configuration

```powershell
docker exec mosq_secure cat /mosquitto/config/mosquitto.conf
# Should have: listener 8883, allow_anonymous false

docker exec mosq_secure cat /mosquitto/config/passwordfile
# Should show encrypted password for testuser

docker logs mosq_secure
# Check for certificate/TLS errors
```

### Problem: Port 1883 works but 8883 doesn't

#### Solution: TLS certificate issue

```powershell
# Check certificates exist
ls mqtt-brokers\secure\certs\
# Should have: server.crt, server.key

# Restart secure broker
docker restart mosq_secure
```

### Problem: Laravel shows "Authentication required"

#### Solution: Check API key

```powershell
# Check .env file
cat .env | Select-String "FLASK_API_KEY"
# Should be: FLASK_API_KEY=my-very-secret-flask-key-CHANGEME

# Clear Laravel config cache
php artisan config:clear
```

## 📊 Understanding the Scanner Logic

### How Flask Scanner Detects Sensors

1. **Port Scan**: Connects to ports 1883 and 8883
2. **MQTT Subscribe**: Subscribes to `#` (all topics)
3. **Listen Period**: Waits 5 seconds (configurable) for messages
4. **Message Capture**: Records all MQTT messages received
5. **Publisher Detection**: Identifies active publishers from:
    - Retained messages
    - Real-time publishes during listen period
    - Topic patterns (e.g., `sensors/faris/dht_secure`)

### Scanner Output Format

```python
{
  "ip": "192.168.100.56",
  "port": 8883,
  "classification": "open_or_auth_ok",
  "tls": True,
  "publishers": [
    {
      "topic": "sensors/faris/dht_secure",
      "payload_size": 35,
      "qos": 0,
      "retained": True
    }
  ],
  "topics_discovered": {
    "sensors/faris/dht_secure": {
      "message_count": 5,
      "first_seen": "2025-11-05T01:45:00"
    }
  },
  "tls_analysis": {
    "has_tls": True,
    "cert_valid": True,
    "security_score": 70,
    "security_issues": ["Self-signed certificate detected"]
  }
}
```

## 🔐 Security Analysis

### Secure Connection (Port 8883)

-   ✅ **Encryption**: TLS 1.2+
-   ✅ **Authentication**: Username/password required
-   ⚠️ **Certificate**: Self-signed (not CA-signed)
-   ✅ **Cipher**: Modern encryption (AES, ChaCha20)

**Security Score**: 70/100

### Insecure Connection (Port 1883)

-   ❌ **Encryption**: None (plain text)
-   ❌ **Authentication**: Anonymous allowed
-   ❌ **Eavesdropping**: Anyone can read messages
-   ❌ **Tampering**: Anyone can publish fake data

**Security Score**: 0/100 - **CRITICAL RISK**

## 📝 Files Reference

### ESP32 Code

-   `esp32_mixed_security.ino` - Main firmware

### Flask Scanner

-   `mqtt-scanner/app.py` - Flask API server
-   `mqtt-scanner/scanner.py` - MQTT scanning logic
-   `mqtt-scanner/requirements.txt` - Python dependencies

### Laravel Dashboard

-   `app/Http/Controllers/MqttScannerController.php` - API proxy
-   `resources/views/dashboard.blade.php` - UI
-   `routes/web.php` - Routing

### Docker Configuration

-   `mqtt-brokers/docker-compose.yml` - Container definitions
-   `mqtt-brokers/insecure/config/mosquitto.conf` - Port 1883 config
-   `mqtt-brokers/secure/config/mosquitto.conf` - Port 8883 config

### Test Scripts

-   `test_esp32_sensors.py` - Verify ESP32 publishing
-   `test_mqtt_scan.py` - Test scanner directly
-   `TEST_INSTRUCTIONS.md` - This document

## 🎯 Success Criteria Checklist

Run through this checklist after testing:

-   [ ] Docker containers running (both mosq_insecure and mosq_secure)
-   [ ] ESP32 connected to WiFi (check serial monitor)
-   [ ] ESP32 publishing to 3 topics every 3 seconds
-   [ ] test_esp32_sensors.py receives all 3 sensor types
-   [ ] Flask app running on port 5000
-   [ ] Laravel app running on port 8000
-   [ ] Dashboard scan completes in 5-10 seconds
-   [ ] Dashboard shows 3 sensors (DHT, LDR, PIR)
-   [ ] 2 sensors marked as "Secure" (port 8883)
-   [ ] 1 sensor marked as "Insecure" (port 1883)
-   [ ] Sensor data displays with actual values
-   [ ] TLS certificate details shown for port 8883
-   [ ] Security warnings shown for port 1883
-   [ ] CSV export works correctly

## 🔄 Auto-Refresh Considerations

Currently, auto-refresh is **disabled** in the dashboard. If you want real-time updates:

1. The ESP32 publishes every **3 seconds**
2. You can manually refresh by clicking **Start Scan** again
3. To enable auto-refresh, add this to dashboard.blade.php:
    ```javascript
    setInterval(startScan, 30000); // Refresh every 30 seconds
    ```

## 💡 Tips

1. **Retained Messages**: ESP32 uses `retained=true`, so the scanner will immediately see the last published value even if the ESP32 stopped publishing.

2. **Listen Duration**: If you miss sensors, increase from 5 to 10 seconds in the scan request.

3. **Multiple Scans**: You can run multiple scans in a row. Each scan creates a new CSV file.

4. **Port Configuration**: The system assumes:

    - Port 1883 = insecure
    - Port 8883 = secure/TLS

    Don't change these without updating the scanner logic.

## 📞 Support

If issues persist:

1. Check ALL logs: Docker, Flask, Laravel, ESP32 serial monitor
2. Verify network connectivity: `ping 192.168.100.56`
3. Test MQTT manually: Use `mosquitto_sub` or Python test scripts
4. Review this document's troubleshooting section

---

**Last Updated**: November 5, 2025
**Project**: IPB49906 - Final Year Project 2
**Author**: MQTT Security Scanner Team
