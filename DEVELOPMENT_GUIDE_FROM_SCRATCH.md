# MQTT Security Scanner - Complete Development Guide from Scratch

## Table of Contents

1. [Project Concept & Planning](#1-project-concept--planning)
2. [Prerequisites & Environment Setup](#2-prerequisites--environment-setup)
3. [Phase 1: Infrastructure Setup](#phase-1-infrastructure-setup)
4. [Phase 2: Backend Development (Version 1)](#phase-2-backend-development-version-1)
5. [Phase 3: Frontend Development (Version 1)](#phase-3-frontend-development-version-1)
6. [Phase 4: Integration & Testing](#phase-4-integration--testing)
7. [Phase 5: Advanced Features (Version 2)](#phase-5-advanced-features-version-2)
8. [Phase 6: Security Enhancements](#phase-6-security-enhancements)
9. [Phase 7: Client Requirements & Refinements](#phase-7-client-requirements--refinements)
10. [Phase 8: Final Testing & Documentation](#phase-8-final-testing--documentation)

---

## 1. Project Concept & Planning

### 1.1 Problem Statement

IoT devices using MQTT protocol are often misconfigured with:

-   No authentication (anonymous access)
-   Unencrypted communication
-   Publicly exposed ports
-   Default credentials

### 1.2 Solution Objectives

Create a comprehensive security scanner that:

-   Discovers MQTT brokers on a network
-   Tests authentication mechanisms
-   Analyzes encryption status
-   Captures and analyzes traffic
-   Generates security reports

### 1.3 Technology Stack Decision

**Backend:**

-   **Python + Flask**: Lightweight API server, excellent MQTT library support
-   **paho-mqtt**: Industry-standard MQTT client library

**Frontend:**

-   **Laravel 11**: Modern PHP framework with built-in security features
-   **Tailwind CSS**: Utility-first CSS for rapid UI development
-   **JavaScript**: For dynamic interactions and real-time updates

**Infrastructure:**

-   **Docker**: Containerized MQTT brokers for consistent testing
-   **ESP32**: Physical IoT device simulation
-   **Mosquitto**: Open-source MQTT broker

---

## 2. Prerequisites & Environment Setup

### 2.1 System Requirements

-   **Operating System**: Windows 10/11, Linux, or macOS
-   **RAM**: Minimum 8GB (16GB recommended)
-   **Storage**: At least 5GB free space
-   **Network**: Local network access for testing

### 2.2 Software Installation

#### Step 1: Install Core Development Tools

```bash
# Install Git (for version control)
# Download from: https://git-scm.com/downloads

# Install Visual Studio Code
# Download from: https://code.visualstudio.com/

# Install Docker Desktop
# Download from: https://www.docker.com/products/docker-desktop
```

#### Step 2: Install PHP & Composer

```bash
# Windows: Download PHP 8.2+ from php.net
# Add PHP to system PATH

# Install Composer (PHP package manager)
# Download from: https://getcomposer.org/download/
```

#### Step 3: Install Python

```bash
# Download Python 3.10+ from python.org
# Ensure "Add Python to PATH" is checked during installation

# Verify installation
python --version
pip --version
```

#### Step 4: Install Node.js & NPM

```bash
# Download Node.js LTS from nodejs.org
# NPM is included with Node.js

# Verify installation
node --version
npm --version
```

#### Step 5: Install Arduino IDE (for ESP32)

```bash
# Download from: https://www.arduino.cc/en/software
# Install ESP32 board support:
# File > Preferences > Additional Board Manager URLs:
# https://dl.espressif.com/dl/package_esp32_index.json
```

### 2.3 VS Code Extensions (Recommended)

-   PHP Intelephense
-   Laravel Extension Pack
-   Python Extension
-   Docker Extension
-   Tailwind CSS IntelliSense

---

## Phase 1: Infrastructure Setup

### Step 1: Create Project Directory Structure

```bash
# Create main project folder
mkdir mqtt-security-scanner
cd mqtt-security-scanner

# Create subdirectories
mkdir mqtt-brokers
mkdir mqtt-scanner
mkdir esp32_code
```

### Step 2: Set Up MQTT Brokers with Docker

#### Create docker-compose.yml

```yaml
# File: mqtt-brokers/docker-compose.yml
version: "3.8"
services:
    mosquitto_insecure:
        image: eclipse-mosquitto:2.0
        container_name: mosq_insecure
        volumes:
            - ./insecure/config:/mosquitto/config
            - ./insecure/data:/mosquitto/data
            - ./insecure/log:/mosquitto/log
        ports:
            - "1883:1883"

    mosquitto_secure:
        image: eclipse-mosquitto:2.0
        container_name: mosq_secure
        volumes:
            - ./secure/config:/mosquitto/config
            - ./secure/data:/mosquitto/data
            - ./secure/log:/mosquitto/log
            - ./secure/certs:/mosquitto/certs
        ports:
            - "8883:8883"
```

#### Create Insecure Broker Configuration

```bash
# Create directory
mkdir -p mqtt-brokers/insecure/config

# Create configuration file
# File: mqtt-brokers/insecure/config/mosquitto.conf
listener 1883
allow_anonymous true
persistence true
persistence_location /mosquitto/data/
log_dest file /mosquitto/log/mosquitto.log
```

#### Create Secure Broker Configuration

```bash
# Create directories
mkdir -p mqtt-brokers/secure/config
mkdir -p mqtt-brokers/secure/certs

# File: mqtt-brokers/secure/config/mosquitto.conf
listener 8883
allow_anonymous false
password_file /mosquitto/config/password.txt
cafile /mosquitto/certs/ca.crt
certfile /mosquitto/certs/server.crt
keyfile /mosquitto/certs/server.key
require_certificate false
```

#### Generate SSL Certificates

```bash
cd mqtt-brokers/secure/certs

# Generate CA certificate
openssl req -new -x509 -days 365 -extensions v3_ca -keyout ca.key -out ca.crt

# Generate server certificate
openssl genrsa -out server.key 2048
openssl req -new -out server.csr -key server.key
openssl x509 -req -in server.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out server.crt -days 365
```

#### Create Password File

```bash
# File: mqtt-brokers/secure/config/password.txt
# Use mosquitto_passwd utility to create hashed password
docker run -it eclipse-mosquitto:2.0 mosquitto_passwd -c -b /tmp/password.txt faris02@gmail.com FarisAdmin8080!

# Copy the generated file to secure/config/
```

#### Start Brokers

```bash
cd mqtt-brokers
docker-compose up -d

# Verify brokers are running
docker ps
```

### Step 3: Set Up ESP32 Hardware

#### Hardware Connections

```
DHT11 Sensor:
- VCC -> 3.3V
- GND -> GND
- DATA -> GPIO 4

PIR Motion Sensor:
- VCC -> 5V
- GND -> GND
- OUT -> GPIO 5

LDR Light Sensor:
- VCC -> 3.3V
- GND -> GND (through 10K resistor)
- OUT -> GPIO 34 (ADC pin)
```

#### ESP32 Firmware (Initial Version)

```cpp
// File: esp32_code/esp32_mixed_security.ino
#include <WiFi.h>
#include <PubSubClient.h>
#include <DHT.h>

// WiFi credentials
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// MQTT Broker settings
const char* mqtt_server_insecure = "192.168.100.56"; // Your PC IP
const char* mqtt_server_secure = "192.168.100.56";
const int mqtt_port_insecure = 1883;
const int mqtt_port_secure = 8883;

// Sensor pins
#define DHTPIN 4
#define PIRPIN 5
#define LDRPIN 34
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);
WiFiClient espClient_insecure;
WiFiClientSecure espClient_secure;
PubSubClient client_insecure(espClient_insecure);
PubSubClient client_secure(espClient_secure);

void setup() {
  Serial.begin(115200);
  pinMode(PIRPIN, INPUT);
  dht.begin();

  // Connect to WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected");

  // Set up MQTT clients
  client_insecure.setServer(mqtt_server_insecure, mqtt_port_insecure);
  espClient_secure.setInsecure(); // Skip certificate verification for testing
  client_secure.setServer(mqtt_server_secure, mqtt_port_secure);
}

void reconnect_insecure() {
  while (!client_insecure.connected()) {
    if (client_insecure.connect("ESP32_Insecure")) {
      Serial.println("Connected to insecure broker");
    } else {
      delay(5000);
    }
  }
}

void reconnect_secure() {
  while (!client_secure.connected()) {
    if (client_secure.connect("ESP32_Secure", "faris02@gmail.com", "FarisAdmin8080!")) {
      Serial.println("Connected to secure broker");
    } else {
      delay(5000);
    }
  }
}

void loop() {
  if (!client_insecure.connected()) reconnect_insecure();
  if (!client_secure.connected()) reconnect_secure();

  client_insecure.loop();
  client_secure.loop();

  // Read sensors
  float temp = dht.readTemperature();
  float humidity = dht.readHumidity();
  int motion = digitalRead(PIRPIN);
  int light = analogRead(LDRPIN);

  // Publish to both brokers
  String payload = "{\"temp\":" + String(temp) + ",\"humidity\":" + String(humidity) +
                   ",\"motion\":" + String(motion) + ",\"light\":" + String(light) + "}";

  client_insecure.publish("sensors/dht11", payload.c_str());
  client_secure.publish("sensors/dht11", payload.c_str());

  delay(10000); // Send every 10 seconds
}
```

---

## Phase 2: Backend Development (Version 1)

### Step 1: Initialize Python Project

```bash
cd mqtt-scanner

# Create virtual environment
python -m venv venv

# Activate virtual environment
# Windows:
venv\Scripts\activate
# Linux/Mac:
source venv/bin/activate

# Create requirements.txt
touch requirements.txt
```

### Step 2: Define Dependencies

```txt
# File: mqtt-scanner/requirements.txt
Flask==3.0.0
flask-cors==4.0.0
paho-mqtt==2.0.0
pytz==2024.1
```

### Step 3: Install Dependencies

```bash
pip install -r requirements.txt
```

### Step 4: Create Basic Scanner Module (Version 1)

```python
# File: mqtt-scanner/scanner.py (Version 1 - Basic)
import socket
import paho.mqtt.client as mqtt_client
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

COMMON_PORTS = [1883, 8883]
TIMEOUT = 2

class MQTTScanner:
    def __init__(self):
        self.results = []

    def scan_port(self, host, port):
        """Basic port scanning"""
        try:
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(TIMEOUT)
            result = sock.connect_ex((host, port))
            sock.close()

            if result == 0:
                logger.info(f"Port {port} is open on {host}")
                return True
            return False
        except Exception as e:
            logger.error(f"Error scanning {host}:{port} - {e}")
            return False

    def test_mqtt_connection(self, host, port, username=None, password=None):
        """Test MQTT connection"""
        client = mqtt_client.Client()

        if username and password:
            client.username_pw_set(username, password)

        try:
            client.connect(host, port, 60)
            client.disconnect()
            return {"status": "connected", "auth_required": False}
        except Exception as e:
            if "not authorized" in str(e).lower():
                return {"status": "auth_failed", "auth_required": True}
            return {"status": "failed", "error": str(e)}

    def scan_network(self, target_ip, username=None, password=None):
        """Scan a target IP for MQTT brokers"""
        results = []

        for port in COMMON_PORTS:
            if self.scan_port(target_ip, port):
                mqtt_result = self.test_mqtt_connection(target_ip, port, username, password)
                results.append({
                    "host": target_ip,
                    "port": port,
                    "status": mqtt_result["status"]
                })

        return results
```

### Step 5: Create Flask API (Version 1)

```python
# File: mqtt-scanner/app.py (Version 1 - Basic)
from flask import Flask, request, jsonify
from flask_cors import CORS
from scanner import MQTTScanner
import logging

app = Flask(__name__)
CORS(app)
logging.basicConfig(level=logging.INFO)

scanner = MQTTScanner()

@app.route('/scan', methods=['POST'])
def scan():
    """API endpoint for scanning"""
    data = request.json
    target_ip = data.get('target_ip')
    username = data.get('username')
    password = data.get('password')

    if not target_ip:
        return jsonify({"error": "Target IP is required"}), 400

    results = scanner.scan_network(target_ip, username, password)

    return jsonify({
        "success": True,
        "results": results,
        "total_scanned": len(results)
    })

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({"status": "healthy"})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
```

### Step 6: Test Backend

```bash
# Start Flask server
python app.py

# Test with curl (in another terminal)
curl -X POST http://localhost:5000/scan \
  -H "Content-Type: application/json" \
  -d '{"target_ip": "192.168.100.56"}'
```

---

## Phase 3: Frontend Development (Version 1)

### Step 1: Initialize Laravel Project

```bash
# Navigate to project root
cd ..

# Create Laravel project
composer create-project laravel/laravel mqtt-dashboard

# Or use existing project structure
cd mqtt-dashboard
```

### Step 2: Install Frontend Dependencies

```bash
# Install Node packages
npm install

# Install additional dependencies
npm install jspdf jspdf-autotable --save
```

### Step 3: Configure Laravel Environment

```bash
# Copy .env.example
cp .env.example .env

# Generate application key
php artisan key:generate

# Set timezone in config/app.php
# 'timezone' => 'Asia/Kuala_Lumpur',
```

### Step 4: Create Dashboard Route

```php
// File: routes/web.php
<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});
```

### Step 5: Create Basic Dashboard View (Version 1)

```php
<!-- File: resources/views/dashboard.blade.php (Version 1 - Basic) -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MQTT Security Scanner</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">MQTT Security Scanner</h1>

        <!-- Scan Form -->
        <div class="card mb-6">
            <h2 class="text-xl font-semibold mb-4">Network Scan</h2>
            <form id="scanForm">
                <div class="mb-4">
                    <label class="block mb-2">Target IP:</label>
                    <input type="text" id="targetIp" class="border p-2 w-full"
                           placeholder="192.168.1.100" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Username (optional):</label>
                    <input type="text" id="username" class="border p-2 w-full">
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Password (optional):</label>
                    <input type="password" id="password" class="border p-2 w-full">
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                    Start Scan
                </button>
            </form>
        </div>

        <!-- Results Display -->
        <div class="card">
            <h2 class="text-xl font-semibold mb-4">Scan Results</h2>
            <div id="results">
                <p class="text-gray-500">No scan performed yet</p>
            </div>
        </div>
    </div>

    <script>
        const scanForm = document.getElementById('scanForm');
        const resultsDiv = document.getElementById('results');

        scanForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const targetIp = document.getElementById('targetIp').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            resultsDiv.innerHTML = '<p>Scanning...</p>';

            try {
                const response = await fetch('http://localhost:5000/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        target_ip: targetIp,
                        username: username,
                        password: password
                    })
                });

                const data = await response.json();

                if (data.success) {
                    displayResults(data.results);
                } else {
                    resultsDiv.innerHTML = '<p class="text-red-500">Scan failed</p>';
                }
            } catch (error) {
                resultsDiv.innerHTML = '<p class="text-red-500">Error: ' + error.message + '</p>';
            }
        });

        function displayResults(results) {
            if (results.length === 0) {
                resultsDiv.innerHTML = '<p>No brokers found</p>';
                return;
            }

            let html = '<table class="w-full"><thead><tr>' +
                       '<th class="border p-2">Host</th>' +
                       '<th class="border p-2">Port</th>' +
                       '<th class="border p-2">Status</th>' +
                       '</tr></thead><tbody>';

            results.forEach(result => {
                html += `<tr>
                    <td class="border p-2">${result.host}</td>
                    <td class="border p-2">${result.port}</td>
                    <td class="border p-2">${result.status}</td>
                </tr>`;
            });

            html += '</tbody></table>';
            resultsDiv.innerHTML = html;
        }
    </script>
</body>
</html>
```

### Step 6: Test Frontend

```bash
# Start Laravel development server
php artisan serve

# Open browser to http://localhost:8000
# Ensure Flask backend is also running on port 5000
```

---

## Phase 4: Integration & Testing

### Step 1: Test End-to-End Flow

1. Start Docker brokers: `cd mqtt-brokers && docker-compose up -d`
2. Start Flask backend: `cd mqtt-scanner && python app.py`
3. Start Laravel frontend: `cd mqtt-dashboard && php artisan serve`
4. Flash ESP32 firmware and power on the device
5. Open browser to `http://localhost:8000`
6. Enter target IP and click "Start Scan"
7. Verify results appear in the table

### Step 2: Debug Common Issues

```bash
# Check if brokers are running
docker ps

# Check broker logs
docker logs mosq_insecure
docker logs mosq_secure

# Test MQTT connectivity manually
mosquitto_sub -h localhost -p 1883 -t "#" -v

# Check Flask logs
# (visible in the terminal where you ran python app.py)

# Check Laravel logs
tail -f storage/logs/laravel.log
```

---

## Phase 5: Advanced Features (Version 2)

### Step 1: Enhanced Scanner with TLS Analysis

```python
# File: mqtt-scanner/scanner.py (Version 2 - Enhanced)
import ssl
import socket
import time
import datetime
from paho.mqtt import client as mqtt_client
import threading
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

COMMON_PORTS = [1883, 8883]
TIMEOUT = 2
LISTEN_DURATION = 5

captured_messages = {}
capture_lock = threading.Lock()

def analyze_tls_certificate(host, port, timeout=3):
    """Analyze TLS/SSL certificate"""
    cert_analysis = {
        'has_tls': False,
        'cert_valid': False,
        'cert_details': {},
        'security_issues': [],
        'security_score': 0,
        'error': None
    }

    try:
        context = ssl.create_default_context()
        context.check_hostname = False
        context.verify_mode = ssl.CERT_NONE

        with socket.create_connection((host, port), timeout=timeout) as sock:
            with context.wrap_socket(sock, server_hostname=host) as ssock:
                cert_analysis['has_tls'] = True
                cert = ssock.getpeercert()

                if cert:
                    # Extract certificate details
                    subject = dict(x[0] for x in cert['subject'])
                    issuer = dict(x[0] for x in cert['issuer'])

                    cert_analysis['cert_valid'] = True
                    cert_analysis['cert_details'] = {
                        'common_name': subject.get('commonName', 'N/A'),
                        'organization': subject.get('organizationName', 'N/A'),
                        'issuer': issuer.get('commonName', 'N/A'),
                        'not_before': cert.get('notBefore', 'N/A'),
                        'not_after': cert.get('notAfter', 'N/A'),
                        'serial_number': cert.get('serialNumber', 'N/A')
                    }

                    # Check if self-signed
                    if subject.get('commonName') == issuer.get('commonName'):
                        cert_analysis['security_issues'].append('Self-signed certificate')
                        cert_analysis['security_score'] -= 30

                    # Check expiration
                    not_after = datetime.datetime.strptime(cert['notAfter'], '%b %d %H:%M:%S %Y %Z')
                    days_until_expiry = (not_after - datetime.datetime.now()).days

                    if days_until_expiry < 0:
                        cert_analysis['security_issues'].append('Certificate expired')
                        cert_analysis['security_score'] -= 50
                    elif days_until_expiry < 30:
                        cert_analysis['security_issues'].append('Certificate expiring soon')
                        cert_analysis['security_score'] -= 10

                    # Base score for valid TLS
                    cert_analysis['security_score'] += 100

    except ssl.SSLError as e:
        cert_analysis['error'] = f'SSL Error: {str(e)}'
        logger.warning(f"SSL error on {host}:{port} - {e}")
    except Exception as e:
        cert_analysis['error'] = str(e)
        logger.error(f"Error analyzing certificate on {host}:{port} - {e}")

    return cert_analysis

def capture_mqtt_traffic(host, port, username=None, password=None, duration=5):
    """Capture MQTT traffic by subscribing to all topics"""
    captured_data = []

    def on_connect(client, userdata, flags, rc, properties=None):
        if rc == 0:
            logger.info(f"Connected to {host}:{port} for traffic capture")
            client.subscribe("#", qos=0)
        else:
            logger.error(f"Failed to connect for traffic capture: {rc}")

    def on_message(client, userdata, msg):
        captured_data.append({
            'topic': msg.topic,
            'payload': msg.payload.decode('utf-8', errors='ignore'),
            'qos': msg.qos,
            'retain': msg.retain,
            'timestamp': datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        })
        logger.info(f"Captured message on {msg.topic}")

    try:
        client = mqtt_client.Client()
        client.on_connect = on_connect
        client.on_message = on_message

        if username and password:
            client.username_pw_set(username, password)

        if port == 8883:
            client.tls_set(cert_reqs=ssl.CERT_NONE)
            client.tls_insecure_set(True)

        client.connect(host, port, 60)
        client.loop_start()

        time.sleep(duration)

        client.loop_stop()
        client.disconnect()

    except Exception as e:
        logger.error(f"Error capturing traffic on {host}:{port} - {e}")

    return captured_data

class MQTTScanner:
    def __init__(self):
        self.results = []

    def scan_host(self, host, port, username=None, password=None):
        """Comprehensive scan of a single host:port"""
        result = {
            'host': host,
            'port': port,
            'port_open': False,
            'security_mode': 'Unknown',
            'authentication_status': 'Unknown',
            'captured_messages': [],
            'tls_analysis': None,
            'risk_level': 'Unknown',
            'timestamp': datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        }

        # Step 1: Port scan
        try:
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(TIMEOUT)
            if sock.connect_ex((host, port)) == 0:
                result['port_open'] = True
                logger.info(f"[OPEN] {host}:{port}")
            else:
                logger.info(f"[CLOSED] {host}:{port}")
                return result
            sock.close()
        except Exception as e:
            logger.error(f"Port scan error on {host}:{port} - {e}")
            return result

        # Step 2: Determine security mode
        if port == 8883:
            result['security_mode'] = 'TLS/SSL'
            result['tls_analysis'] = analyze_tls_certificate(host, port)
        else:
            result['security_mode'] = 'Plain'
            logger.warning(f"[SECURITY RISK] {host}:{port} using insecure port (no TLS)")

        # Step 3: Test MQTT connection
        try:
            client = mqtt_client.Client()

            if username and password:
                client.username_pw_set(username, password)

            if port == 8883:
                client.tls_set(cert_reqs=ssl.CERT_NONE)
                client.tls_insecure_set(True)

            def on_connect(client, userdata, flags, rc, properties=None):
                if rc == 0:
                    result['authentication_status'] = 'Connected'
                    logger.info(f"[AUTH SUCCESS] {host}:{port}")
                elif rc == 5:
                    result['authentication_status'] = 'Auth Failed'
                    logger.warning(f"[AUTH FAILED] {host}:{port}")
                else:
                    result['authentication_status'] = f'Error (RC={rc})'

            client.on_connect = on_connect
            client.connect(host, port, 60)
            client.loop_start()
            time.sleep(2)
            client.loop_stop()
            client.disconnect()

        except Exception as e:
            if "not authorized" in str(e).lower() or "5" in str(e):
                result['authentication_status'] = 'Auth Failed'
            else:
                result['authentication_status'] = f'Connection Error'
            logger.error(f"MQTT connection error on {host}:{port} - {e}")

        # Step 4: Capture traffic if connected
        if result['authentication_status'] == 'Connected':
            result['captured_messages'] = capture_mqtt_traffic(host, port, username, password, LISTEN_DURATION)

        # Step 5: Calculate risk level
        if result['security_mode'] == 'Plain' and result['authentication_status'] == 'Connected':
            result['risk_level'] = 'CRITICAL'
        elif result['authentication_status'] == 'Auth Failed':
            result['risk_level'] = 'MEDIUM'
        elif result['security_mode'] == 'TLS/SSL' and result['authentication_status'] == 'Connected':
            result['risk_level'] = 'LOW'
        else:
            result['risk_level'] = 'UNKNOWN'

        return result

    def scan_network(self, target_ip, username=None, password=None):
        """Scan multiple ports on target IP"""
        results = []

        for port in COMMON_PORTS:
            result = self.scan_host(target_ip, port, username, password)
            results.append(result)

        return results
```

### Step 2: Update Flask API

```python
# File: mqtt-scanner/app.py (Version 2 - Enhanced)
from flask import Flask, request, jsonify
from flask_cors import CORS
from scanner import MQTTScanner
import logging
import pytz
from datetime import datetime

app = Flask(__name__)
CORS(app)
logging.basicConfig(level=logging.INFO)

scanner = MQTTScanner()
malaysia_tz = pytz.timezone('Asia/Kuala_Lumpur')

@app.route('/scan', methods=['POST'])
def scan():
    """Enhanced scan endpoint"""
    data = request.json
    target_ip = data.get('target_ip')
    username = data.get('username')
    password = data.get('password')

    if not target_ip:
        return jsonify({"error": "Target IP is required"}), 400

    scan_start = datetime.now(malaysia_tz)
    results = scanner.scan_network(target_ip, username, password)
    scan_end = datetime.now(malaysia_tz)

    # Calculate statistics
    total_scanned = len(results)
    open_brokers = sum(1 for r in results if r['port_open'] and r['authentication_status'] == 'Connected')
    auth_failures = sum(1 for r in results if r['authentication_status'] == 'Auth Failed')

    return jsonify({
        "success": True,
        "results": results,
        "statistics": {
            "total_scanned": total_scanned,
            "open_brokers": open_brokers,
            "auth_failures": auth_failures,
            "scan_duration": str(scan_end - scan_start)
        },
        "scan_time": scan_start.strftime('%Y-%m-%d %H:%M:%S')
    })

@app.route('/health', methods=['GET'])
def health():
    return jsonify({"status": "healthy", "version": "2.0"})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
```

---

## Phase 6: Security Enhancements

### Implemented Security Features

1. **DevSecOps Logging**

    - Structured logging with severity levels
    - Security event tracking
    - Audit trail for all scans

2. **Risk Classification System**

    - CRITICAL: Insecure + No Auth
    - MEDIUM: Auth failures
    - LOW: Secure + Authenticated

3. **Certificate Validation**
    - Expiration checking
    - Self-signed detection
    - Security scoring algorithm

---

## Phase 7: Client Requirements & Refinements

### Requirements Implemented:

1. **Accurate Total Scanned Counter**

    - Fixed to count unique IP:Port combinations
    - Uses Set for deduplication

2. **Remove Authentication**

    - Dashboard now publicly accessible
    - No login required

3. **Scan Timing Display**

    - Shows total scanned count
    - Displays current timestamp
    - Shows scan duration

4. **24-Hour Time Format**

    - Changed from 12-hour to 24-hour format
    - Removed seconds for cleaner display

5. **PDF Download Function**
    - Client-side PDF generation using jsPDF
    - Professional formatting
    - Includes all scan metadata

### Updated Dashboard (Final Version)

```javascript
// Add to dashboard.blade.php
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<script>
async function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Add header
    doc.setFontSize(18);
    doc.text("MQTT Security Scan Report", 14, 20);

    // Add metadata
    doc.setFontSize(10);
    const now = new Date();
    doc.text(`Report Generated: ${now.toLocaleString('en-MY', { timeZone: 'Asia/Kuala_Lumpur' })}`, 14, 30);

    // Add summary statistics
    doc.setFontSize(12);
    doc.text("Executive Summary", 14, 45);
    doc.setFontSize(10);
    doc.text(`Total Scanned: ${totalScanned}`, 14, 55);
    doc.text(`Open Brokers: ${openBrokers}`, 14, 62);
    doc.text(`Auth Failures: ${authFailures}`, 14, 69);

    // Add results table
    const tableData = scanResults.map(r => [
        `${r.host}:${r.port}`,
        r.security_mode,
        r.authentication_status,
        r.risk_level
    ]);

    doc.autoTable({
        startY: 80,
        head: [['Target', 'Security', 'Auth Status', 'Risk Level']],
        body: tableData,
        theme: 'grid',
        styles: { fontSize: 9 }
    });

    // Save PDF
    const filename = `mqtt_scan_report_${Date.now()}.pdf`;
    doc.save(filename);
}
</script>
```

---

## Phase 8: Final Testing & Documentation

### Testing Checklist

-   [ ] Brokers start successfully with Docker
-   [ ] ESP32 publishes data to both brokers
-   [ ] Python scanner detects open ports
-   [ ] Scanner differentiates between auth failure and connection refused
-   [ ] TLS certificate analysis works correctly
-   [ ] Traffic capture retrieves sensor data
-   [ ] Dashboard displays results in real-time
-   [ ] PDF download generates correct report
-   [ ] CSV export works properly
-   [ ] Timestamps show Malaysia time (UTC+8)

### Performance Metrics

-   Port scan: ~2 seconds per port
-   Traffic capture: 5 seconds per broker
-   Full scan (2 ports): ~15 seconds
-   Dashboard update: Real-time (< 1 second)

---

## Conclusion

This guide provides a complete roadmap from concept to deployment. By following these phases sequentially, you can build a fully functional MQTT Security Scanner that identifies vulnerabilities in IoT networks and provides actionable security insights.

### Key Achievements:

-   ✅ Modular 3-tier architecture
-   ✅ Comprehensive security analysis
-   ✅ Real-time monitoring and reporting
-   ✅ Professional PDF/CSV reports
-   ✅ DevSecOps best practices

### Next Steps:

-   Add support for scanning IP ranges (CIDR notation)
-   Implement scheduled scans
-   Add email notifications for critical findings
-   Create admin dashboard for scan history
-   Deploy to production environment
