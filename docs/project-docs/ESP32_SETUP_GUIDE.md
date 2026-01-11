# ESP32 Setup Guide

## Which File to Use?

**USE THIS FILE:** `esp32_mixed_security/esp32_mixed_security.ino`

There are 2 identical ESP32 files in the project:

1. ✅ **esp32_mixed_security/esp32_mixed_security.ino** ← **USE THIS ONE**
2. ❌ `esp32_mixed_security.ino` (root folder) ← Duplicate, ignore this

**Why?** The file inside the `esp32_mixed_security/` folder is the correct location for Arduino IDE to recognize and compile properly.

---

## How to Upload to ESP32

### Step 1: Open in Arduino IDE

```
File → Open → esp32_mixed_security/esp32_mixed_security.ino
```

### Step 2: Configure WiFi & MQTT

Edit these lines in the code:

```cpp
// WiFi Configuration
const char* ssid = "YOUR_WIFI_NAME";      // Change this
const char* password = "YOUR_WIFI_PASSWORD"; // Change this

// MQTT Broker IPs
const char* mqtt_server_secure = "192.168.100.10";   // Your PC IP
const char* mqtt_server_insecure = "192.168.100.10"; // Your PC IP

// MQTT Authentication (for secure broker)
const char* mqtt_user = "mqtt@example.com";  // Username
const char* mqtt_pass = "testpass";           // Password
```

### Step 3: Select Board

```
Tools → Board → ESP32 Dev Module
Tools → Port → COM3 (your port)
```

### Step 4: Upload

Click **Upload** button (→) in Arduino IDE

---

## What This Code Does

### Sensors Connected:

1. **DHT22** (Pin 4) - Temperature & Humidity
2. **LDR** (Pin 34) - Light sensor
3. **PIR** (Pin 5) - Motion sensor

### MQTT Publishing:

**Secure Broker (Port 8883 - TLS):**

-   `sensors/faris/dht_secure` → Temperature & Humidity
-   `sensors/faris/ldr_secure` → Light level

**Insecure Broker (Port 1883 - No TLS):**

-   `sensors/faris/pir_insecure` → Motion detection

### Publishing Interval:

-   Every 5 seconds

---

## Troubleshooting

**ESP32 not connecting to WiFi?**

-   Check SSID and password
-   Make sure WiFi is 2.4GHz (ESP32 doesn't support 5GHz)

**MQTT not connecting?**

-   Check broker IP address
-   Make sure MQTT brokers are running: `docker-compose up -d`
-   Verify credentials match broker configuration

**Sensors not reading?**

-   Check wiring connections
-   DHT22: Data → GPIO 4, VCC → 3.3V, GND → GND
-   LDR: Signal → GPIO 34 (through voltage divider)
-   PIR: Signal → GPIO 5, VCC → 5V, GND → GND

---

**Ready to use! Upload the code from `esp32_mixed_security/` folder!**
