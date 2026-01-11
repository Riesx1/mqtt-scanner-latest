# 📘 MQTT Security Scanner - Complete Project Documentation

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [System Architecture](#2-system-architecture)
3. [Prerequisites & Installation](#3-prerequisites--installation)
4. [Hardware Setup (ESP32)](#4-hardware-setup-esp32)
5. [MQTT Broker Configuration](#5-mqtt-broker-configuration)
6. [Backend Setup (Flask Scanner)](#6-backend-setup-flask-scanner)
7. [Frontend Setup (Laravel Dashboard)](#7-frontend-setup-laravel-dashboard)
8. [System Features](#8-system-features)
9. [Security Implementation](#9-security-implementation)
10. [MQTT Vulnerabilities & Mitigations](#10-mqtt-vulnerabilities--mitigations)
11. [Testing & Validation](#11-testing--validation)
12. [Deployment Guide](#12-deployment-guide)
13. [Troubleshooting](#13-troubleshooting)
14. [API Documentation](#14-api-documentation)
15. [Performance & Scalability](#15-performance--scalability)
16. [Future Enhancements](#16-future-enhancements)

---

## 1. Project Overview

### 1.1 Introduction

The **MQTT Security Scanner** is a comprehensive network security tool designed to detect, analyze, and assess MQTT (Message Queuing Telemetry Transport) brokers and IoT devices on a network. The system provides real-time vulnerability assessment, authentication testing, and traffic monitoring capabilities.

### 1.2 Project Objectives

-   **Primary Goal:** Identify insecure MQTT brokers and IoT devices on a network
-   **Security Assessment:** Evaluate authentication mechanisms and encryption
-   **Real-time Monitoring:** Capture and analyze MQTT traffic and sensor data
-   **Vulnerability Detection:** Identify misconfigured brokers and weak security
-   **Reporting:** Generate comprehensive security reports in PDF and CSV formats

### 1.3 Problem Statement

IoT devices using MQTT protocol are increasingly deployed in critical infrastructure, smart homes, and industrial systems. However, many MQTT brokers are misconfigured with:

-   No authentication (anonymous access)
-   Unencrypted communication (plain text)
-   Default credentials
-   Publicly exposed ports

This creates significant security vulnerabilities that can be exploited by attackers.

### 1.4 Solution Approach

The MQTT Security Scanner provides:

1. **Automated Network Scanning:** Discovers MQTT brokers on specified IP ranges
2. **Authentication Testing:** Verifies security configurations
3. **Traffic Analysis:** Monitors published topics and messages
4. **Security Metrics:** Provides clear visualization of vulnerabilities
5. **Detailed Reporting:** Generates actionable security reports

### 1.5 Technology Stack

| Component        | Technology                    | Version |
| ---------------- | ----------------------------- | ------- |
| Backend API      | Python Flask                  | 3.0+    |
| MQTT Client      | Paho MQTT                     | 2.0+    |
| Frontend         | Laravel                       | 11.x    |
| Frontend UI      | Blade Templates, Tailwind CSS | 3.x     |
| Database         | SQLite                        | 3.x     |
| MQTT Brokers     | Eclipse Mosquitto             | 2.0     |
| Containerization | Docker & Docker Compose       | 24.x    |
| Hardware         | ESP32 DevKit                  | -       |
| Sensors          | DHT11, LDR, PIR               | -       |

---

## 2. System Architecture

### 2.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER INTERFACE                          │
│              (Web Browser - Laravel Dashboard)                  │
└───────────────────────────┬─────────────────────────────────────┘
                            │ HTTP/HTTPS
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL APPLICATION                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐        │
│  │ Controllers  │  │    Models    │  │    Views     │        │
│  │  - Scanner   │  │  - ScanLog   │  │  - Dashboard │        │
│  │  - Auth      │  │  - User      │  │  - Reports   │        │
│  └──────┬───────┘  └──────────────┘  └──────────────┘        │
│         │ API Key Auth                                          │
└─────────┼───────────────────────────────────────────────────────┘
          │ HTTP POST
          ▼
┌─────────────────────────────────────────────────────────────────┐
│                    FLASK SCANNER API                            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐        │
│  │   Scanner    │  │   Security   │  │   Analyzer   │        │
│  │   Module     │  │   Assessor   │  │   Module     │        │
│  └──────┬───────┘  └──────────────┘  └──────────────┘        │
│         │ MQTT Connect                                          │
└─────────┼───────────────────────────────────────────────────────┘
          │ Port 1883/8883
          ▼
┌─────────────────────────────────────────────────────────────────┐
│                    MQTT BROKERS (Docker)                        │
│  ┌────────────────────────┐  ┌────────────────────────┐       │
│  │  Insecure Broker       │  │  Secure Broker         │       │
│  │  - Port: 1883          │  │  - Port: 8883          │       │
│  │  - No Auth             │  │  - TLS Encryption      │       │
│  │  - Plain Text          │  │  - Authentication      │       │
│  └───────┬────────────────┘  └────────┬───────────────┘       │
└──────────┼──────────────────────────────┼─────────────────────┘
           │                              │
           │ MQTT Publish                 │ MQTT Publish (TLS)
           ▼                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    ESP32 + SENSORS                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐        │
│  │  DHT11       │  │     LDR      │  │     PIR      │        │
│  │  (Temp/Hum)  │  │  (Light)     │  │  (Motion)    │        │
│  │  → Secure    │  │  → Secure    │  │  → Insecure  │        │
│  └──────────────┘  └──────────────┘  └──────────────┘        │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Data Flow Diagram

```
┌──────────┐
│  User    │
└────┬─────┘
     │ 1. Initiates Scan (IP, credentials)
     ▼
┌────────────────┐
│ Laravel        │
│ Dashboard      │
└────┬───────────┘
     │ 2. POST /api/scan
     │    {target, creds}
     ▼
┌────────────────┐
│ Flask Scanner  │──┐
│ API            │  │ 3. For each IP in range
└────┬───────────┘  │    For each port (1883, 8883)
     │              │
     │◄─────────────┘
     │ 4. Connect to MQTT Broker
     ▼
┌────────────────┐
│ MQTT Broker    │
│ (Target)       │
└────┬───────────┘
     │ 5. Returns connection status
     │    - 0 (Success)
     │    - 5 (Not Authorized)
     │    - Timeout
     ▼
┌────────────────┐
│ Flask Scanner  │
│ - Classify     │
│ - Analyze TLS  │
│ - Capture Data │
└────┬───────────┘
     │ 6. Return results JSON
     │    {ip, port, classification,
     │     publishers, topics, etc.}
     ▼
┌────────────────┐
│ Laravel        │
│ - Store logs   │
│ - Generate CSV │
└────┬───────────┘
     │ 7. Display results
     ▼
┌────────────────┐
│ User Dashboard │
│ - Metrics      │
│ - Table        │
│ - Details      │
└────────────────┘
```

### 2.3 Component Interaction

**Laravel ↔ Flask Communication:**

-   Protocol: HTTP/HTTPS
-   Authentication: API Key in X-API-KEY header
-   Format: JSON
-   Timeout: 30 seconds for scans, 5 seconds for results

**Flask ↔ MQTT Brokers:**

-   Protocol: MQTT v3.1.1 / v5.0
-   Ports: 1883 (insecure), 8883 (secure/TLS)
-   QoS: 0, 1, 2 (configurable)
-   Keep-alive: 60 seconds

**ESP32 ↔ MQTT Brokers:**

-   Dual connection: Secure and Insecure
-   Publish interval: 3 seconds
-   Retain flag: True (persistent messages)

---

## 3. Prerequisites & Installation

### 3.1 System Requirements

**Hardware:**

-   **Processor:** Intel i5 or equivalent (2+ cores)
-   **RAM:** Minimum 4GB, Recommended 8GB
-   **Storage:** 5GB free space
-   **Network:** WiFi/Ethernet adapter

**Operating System:**

-   Windows 10/11 (64-bit)
-   macOS 10.15+ (Catalina or later)
-   Linux (Ubuntu 20.04+, Debian 11+)

### 3.2 Software Prerequisites

#### 3.2.1 Docker Desktop

**Purpose:** Run MQTT brokers in isolated containers

**Installation (Windows):**

```powershell
# Download installer
https://www.docker.com/products/docker-desktop

# Verify installation
docker --version
docker-compose --version
```

**Expected Output:**

```
Docker version 24.0.7
Docker Compose version 2.23.0
```

#### 3.2.2 PHP & Composer

**Purpose:** Run Laravel application

**Installation (Windows):**

```powershell
# Download PHP 8.2+
https://windows.php.net/download/

# Download Composer
https://getcomposer.org/download/

# Verify installation
php --version
composer --version
```

**Expected Output:**

```
PHP 8.2.x
Composer version 2.6.x
```

#### 3.2.3 Node.js & NPM

**Purpose:** Build frontend assets

**Installation:**

```powershell
# Download Node.js 18+
https://nodejs.org/

# Verify installation
node --version
npm --version
```

**Expected Output:**

```
v18.x.x
9.x.x
```

#### 3.2.4 Python 3.10+

**Purpose:** Run Flask scanner API

**Installation (Windows):**

```powershell
# Download Python 3.10+
https://www.python.org/downloads/

# Verify installation
python --version
pip --version
```

**Expected Output:**

```
Python 3.10.x
pip 23.x
```

#### 3.2.5 Arduino IDE

**Purpose:** Program ESP32 microcontroller

**Installation:**

```
1. Download from: https://www.arduino.cc/en/software
2. Install Arduino IDE
3. Add ESP32 board support:
   - File → Preferences
   - Add URL: https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
   - Tools → Board → Boards Manager
   - Search "ESP32" and install
```

### 3.3 Project Installation

#### Step 1: Clone Repository

```bash
git clone https://github.com/Riesx1/mqtt-scanner-latest.git
cd mqtt-scanner-latest
```

#### Step 2: Install Laravel Dependencies

```bash
# Install PHP packages
composer install

# Install Node.js packages
npm install

# Build frontend assets
npm run build
```

#### Step 3: Configure Environment

```bash
# Copy environment file
cp .env.example .env    # Mac/Linux
copy .env.example .env  # Windows

# Generate application key
php artisan key:generate

# Configure database
php artisan migrate
```

**.env Configuration:**

```env
APP_NAME="MQTT Security Scanner"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite

# Flask Scanner API
FLASK_BASE=http://127.0.0.1:5000
FLASK_API_KEY=my-very-secret-flask-key-CHANGEME
```

#### Step 4: Install Python Dependencies

```bash
cd mqtt-scanner
pip install -r requirements.txt
```

**requirements.txt includes:**

```txt
flask==3.0.0
flask-cors==4.0.0
paho-mqtt==2.0.0
python-dotenv==1.0.0
```

#### Step 5: Start MQTT Brokers

```bash
cd mqtt-brokers
docker-compose up -d
```

**Verify brokers are running:**

```bash
docker-compose ps
```

**Expected Output:**

```
NAME            IMAGE                  STATUS
mosq_insecure   eclipse-mosquitto:2.0  Up
mosq_secure     eclipse-mosquitto:2.0  Up
```

---

## 4. Hardware Setup (ESP32)

### 4.1 Components Required

| Component                | Quantity | Purpose                |
| ------------------------ | -------- | ---------------------- |
| ESP32 Development Board  | 1        | Microcontroller        |
| DHT11 Sensor             | 1        | Temperature & Humidity |
| LDR (Photo Resistor)     | 1        | Light Detection        |
| PIR Sensor (HC-SR501)    | 1        | Motion Detection       |
| 10kΩ Resistor            | 1        | LDR voltage divider    |
| Breadboard               | 1        | Circuit assembly       |
| Jumper Wires             | 15-20    | Connections            |
| USB Cable (Micro/Type-C) | 1        | Programming & Power    |

### 4.2 Circuit Diagram

```
ESP32 Development Board
┌─────────────────────────────┐
│                              │
│  3.3V ──┬──► DHT11 VCC      │
│         │                    │
│         ├──► LDR ──┐         │
│         │          │         │
│  GND ───┼──► DHT11 GND      │
│         │    LDR (via 10kΩ) │
│         └──► PIR GND         │
│                              │
│  GPIO 4  ──► DHT11 DATA     │
│  GPIO 34 ──► LDR Signal      │
│  GPIO 27 ──► PIR OUT         │
│                              │
│  5V  ────────► PIR VCC       │
└─────────────────────────────┘
```

### 4.3 Wiring Instructions

**DHT11 Sensor:**

```
DHT11 Pin 1 (VCC)  → ESP32 3.3V
DHT11 Pin 2 (DATA) → ESP32 GPIO 4
DHT11 Pin 3 (NC)   → Not connected
DHT11 Pin 4 (GND)  → ESP32 GND
```

**LDR Light Sensor (Voltage Divider):**

```
            3.3V
             │
             ├─── LDR ───┐
             │           │
        GPIO 34 ←────────┤
             │           │
             └── 10kΩ ───┤
                         │
                        GND
```

**PIR Motion Sensor:**

```
PIR VCC → ESP32 5V
PIR OUT → ESP32 GPIO 27
PIR GND → ESP32 GND
```

### 4.4 Arduino IDE Configuration

**Install Required Libraries:**

```
1. Open Arduino IDE
2. Sketch → Include Library → Manage Libraries
3. Install:
   - "DHT sensor library" by Adafruit
   - "Adafruit Unified Sensor" by Adafruit
   - "PubSubClient" by Nick O'Leary
```

### 4.5 ESP32 Code Configuration

**Open:** `esp32_mixed_security/esp32_mixed_security.ino`

**Configure WiFi & MQTT:**

```cpp
// WiFi Settings
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// MQTT Broker Settings
const char* mqtt_server = "192.168.100.56";  // Your PC IP

// Secure Connection Credentials
const char* mqtt_user = "faris02@gmail.com";
const char* mqtt_pass = "faris123";
```

**Upload Code:**

```
1. Connect ESP32 via USB
2. Tools → Board → ESP32 Dev Module
3. Tools → Port → (Select ESP32 port)
4. Click Upload (→) button
5. Wait for "Done uploading"
```

**Verify Operation:**

```
1. Tools → Serial Monitor
2. Set baud rate: 115200
3. Check for:
   - "WiFi connected!"
   - "✓ SECURE connection established!"
   - "✓ INSECURE connection established!"
   - "[SECURE DHT] ✓ Published: ..."
```

---

## 5. MQTT Broker Configuration

### 5.1 Docker Compose Structure

**File:** `mqtt-brokers/docker-compose.yml`

```yaml
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

### 5.2 Insecure Broker Configuration

**File:** `mqtt-brokers/insecure/config/mosquitto.conf`

```properties
listener 1883
protocol mqtt
allow_anonymous true

persistence true
persistence_location /mosquitto/data/
log_dest stdout
log_dest file /mosquitto/log/mosquitto.log
```

**Security Characteristics:**

-   ❌ No authentication required
-   ❌ No encryption
-   ❌ Plain text communication
-   ⚠️ **Used for demonstration purposes only**

### 5.3 Secure Broker Configuration

**File:** `mqtt-brokers/secure/config/mosquitto.conf`

```properties
listener 8883
allow_anonymous false
password_file /mosquitto/config/passwordfile
certfile /mosquitto/certs/server.crt
keyfile /mosquitto/certs/server.key
require_certificate false

persistence true
persistence_location /mosquitto/data/
log_dest file /mosquitto/log/mosquitto.log
```

**Security Characteristics:**

-   ✅ Authentication required
-   ✅ TLS/SSL encryption
-   ✅ Password-protected
-   ✅ Production-ready configuration

### 5.4 User Management

**Add new user:**

```bash
# Access secure broker container
docker exec -it mosq_secure sh

# Add user with password
mosquitto_passwd -b /mosquitto/config/passwordfile username password

# Exit container
exit

# Restart broker
docker-compose restart secure
```

**Password file format:**

```
username:$7$101$hashed_password_here...
```

### 5.5 Certificate Generation (TLS)

**Generate self-signed certificate:**

```bash
cd mqtt-brokers/secure/certs

# Generate CA key
openssl genrsa -out ca.key 2048

# Generate CA certificate
openssl req -new -x509 -days 365 -key ca.key -out ca.crt

# Generate server key
openssl genrsa -out server.key 2048

# Generate server certificate request
openssl req -new -key server.key -out server.csr

# Sign server certificate
openssl x509 -req -in server.csr -CA ca.crt -CAkey ca.key \
  -CAcreateserial -out server.crt -days 365
```

---

## 6. Backend Setup (Flask Scanner)

### 6.1 Flask Application Structure

```
mqtt-scanner/
├── app.py                      # Main Flask application
├── scanner.py                  # MQTT scanning logic
├── requirements.txt            # Python dependencies
├── test_auth_failures.py       # Authentication testing
├── test_broker_auth.py         # Broker credential testing
├── test_esp32_connection.py    # ESP32 connectivity test
├── clear_retained.py           # Clear retained messages
├── templates/
│   ├── dashboard_pretty.html   # Flask web interface
│   └── login.html              # Authentication page
└── storage/
    └── mqtt_scan_report.csv    # Scan results export
```

### 6.2 Core Scanner Module

**File:** `mqtt-scanner/scanner.py`

**Key Functions:**

```python
def try_mqtt_connect(host, port, use_tls=False, username=None, password=None):
    """
    Attempts to connect to MQTT broker and analyze security

    Returns:
        dict: {
            'ip': str,
            'port': int,
            'result': str,
            'classification': str,
            'publishers': list,
            'topics_discovered': dict,
            'tls_analysis': dict,
            'security_assessment': dict
        }
    """
```

**Classification Types:**

-   `open_or_auth_ok` - Successfully connected
-   `not_authorized` - Authentication failed
-   `unreachable_or_firewalled` - Cannot connect
-   `tls_or_ssl_error` - TLS/SSL issue

### 6.3 Flask API Endpoints

**Base URL:** `http://127.0.0.1:5000`

#### POST /api/scan

```json
Request:
{
  "target": "192.168.100.0/24",
  "creds": {
    "user": "username",
    "pass": "password"
  }
}

Response:
[
  {
    "ip": "192.168.100.56",
    "port": 1883,
    "result": "connected",
    "classification": "open_or_auth_ok",
    "publishers": [...],
    "topics_discovered": {...}
  }
]
```

#### GET /api/results

```json
Response:
{
  "results": [...],
  "count": 3,
  "timestamp": "2025-12-20T10:30:00"
}
```

### 6.4 Starting Flask Server

```bash
cd mqtt-scanner

# Set environment variables (optional)
export FLASK_ENV=development
export FLASK_SECRET_KEY=your-secret-key
export FLASK_API_KEY=my-api-key

# Start server
python app.py
```

**Expected Output:**

```
 * Running on http://127.0.0.1:5000
 * Debug mode: on
```

---

## 7. Frontend Setup (Laravel Dashboard)

### 7.1 Laravel Application Structure

```
laravel-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── MqttScannerController.php
│   ├── Models/
│   │   └── User.php
│   └── Services/
│       └── MqttClientTracker.php
├── resources/
│   ├── views/
│   │   ├── dashboard.blade.php
│   │   ├── welcome.blade.php
│   │   └── layouts/
│   │       ├── app.blade.php
│   │       └── navigation.blade.php
│   ├── css/
│   │   └── app.css
│   └── js/
│       └── app.js
├── routes/
│   ├── web.php
│   └── auth.php
├── database/
│   ├── migrations/
│   └── seeders/
└── public/
    ├── index.php
    └── build/
```

### 7.2 Controller Logic

**File:** `app/Http/Controllers/MqttScannerController.php`

```php
class MqttScannerController extends Controller
{
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|string|max:100',
            'creds' => 'nullable|array',
        ]);

        // Call Flask API
        $response = Http::timeout(30)
            ->withHeaders(['X-API-KEY' => env('FLASK_API_KEY')])
            ->post(env('FLASK_BASE') . '/api/scan', $validated);

        return response($response->body(), $response->status());
    }
}
```

### 7.3 Dashboard UI Components

**Key Features:**

1. **Network Scanner Form**

```html
<form id="scanForm">
    <input type="text" name="target" placeholder="192.168.100.0/24" />
    <input type="text" name="username" placeholder="Optional" />
    <input type="password" name="password" placeholder="Optional" />
    <button type="submit">Start Scan</button>
</form>
```

2. **Summary Cards**

```html
<div class="summary-cards">
    <div class="card purple">
        <p>Total Scanned</p>
        <h2 id="totalScanned">0</h2>
    </div>
    <div class="card red">
        <p>Open Brokers</p>
        <h2 id="openBrokers">0</h2>
    </div>
    <div class="card yellow">
        <p>Auth Failures</p>
        <h2 id="authFailures">0</h2>
    </div>
</div>
```

3. **Results Table**

```html
<table id="resultsTable">
    <thead>
        <tr>
            <th>IP:PORT</th>
            <th>SECURITY</th>
            <th>SENSOR</th>
            <th>SENSOR DATA</th>
            <th>TOPIC</th>
            <th>MESSAGES</th>
            <th>ACTION</th>
        </tr>
    </thead>
    <tbody id="resultsBody">
        <!-- Dynamic content -->
    </tbody>
</table>
```

### 7.4 JavaScript AJAX Implementation

```javascript
async function startScan() {
    const formData = {
        target: document.getElementById("targetIp").value,
        creds: {
            user: document.getElementById("username").value,
            pass: document.getElementById("password").value,
        },
    };

    const response = await fetch("/scan", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify(formData),
    });

    const results = await response.json();
    displayResults(results);
}
```

### 7.5 Starting Laravel Server

```bash
# Development server
php artisan serve

# Production (using Apache/Nginx)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Access Dashboard:**

```
http://127.0.0.1:8000
```

---

## 8. System Features

### 8.1 Network Scanning

**Capabilities:**

-   IP range scanning (CIDR notation)
-   Multiple port scanning (1883, 8883)
-   Concurrent connection testing
-   Timeout handling (2 seconds per connection)

**Supported IP Formats:**

```
Single IP:      192.168.100.56
CIDR Range:     192.168.100.0/24
Subnet Mask:    192.168.100.0/255.255.255.0
IP Range:       192.168.100.1-192.168.100.254
```

### 8.2 Authentication Testing

**Test Scenarios:**

1. **Anonymous Access Test**

    - Connect without credentials
    - Detect if anonymous access allowed
    - Security risk level: HIGH

2. **Wrong Credentials Test**

    - Connect with incorrect username/password
    - Verify authentication enforcement
    - Expected: Connection rejected (rc=5)

3. **Valid Credentials Test**
    - Connect with correct credentials
    - Verify successful authentication
    - Expected: Connection accepted (rc=0)

### 8.3 TLS/SSL Analysis

**Certificate Information Extracted:**

```json
{
    "subject": "CN=localhost, O=Organization",
    "issuer": "CN=localhost, O=Organization",
    "valid_from": "Dec 20 2024",
    "valid_to": "Dec 20 2025",
    "serial_number": "123456789",
    "self_signed": true,
    "fingerprint_sha256": "abc123...",
    "tls_version": "TLSv1.3",
    "cipher": ["TLS_AES_256_GCM_SHA384"],
    "security_score": 85
}
```

**Security Checks:**

-   ✅ Certificate validity period
-   ✅ Self-signed detection
-   ✅ Expiration warnings
-   ✅ Cipher strength assessment
-   ✅ TLS version verification

### 8.4 Topic Discovery & Monitoring

**Discovered Information:**

-   Published topics
-   Retained messages
-   Message frequency
-   Payload samples (truncated)
-   Publisher identification (limited by MQTT v3.x)

**Example Topics:**

```
sensors/faris/dht_secure     → Temperature & Humidity
sensors/faris/ldr_secure     → Light level
sensors/faris/pir_insecure   → Motion detection
$SYS/broker/clients/total    → System information
```

### 8.5 Security Metrics

**Dashboard Cards:**

1. **Total Scanned**

    - Count of all brokers/ports tested
    - Includes successful, failed, and timeout connections

2. **Open Brokers**

    - Successfully connected brokers
    - Classification: `open_or_auth_ok`

3. **Auth Failures**
    - Authentication rejection count
    - Classification: `not_authorized`

**Security Assessment:**

```python
{
  'risk_level': 'HIGH|MEDIUM|LOW',
  'anonymous_allowed': True/False,
  'requires_auth': True/False,
  'port_type': 'secure|insecure',
  'encryption': True/False
}
```

### 8.6 Reporting

**PDF Report Includes:**

-   Scan metadata (timestamp, target range, duration)
-   Executive summary
-   Detailed findings per broker
-   Security recommendations
-   Risk assessment

**CSV Export Includes:**

```csv
ip,port,result,classification,security,topics_count,publishers_count
192.168.100.56,1883,connected,open_or_auth_ok,Plain,1,1
192.168.100.56,8883,not_authorized,not_authorized,TLS,0,0
```

---

## 9. Security Implementation

### 9.1 Authentication Mechanisms

**Laravel Authentication (Breeze):**

```php
// Registration
Route::post('/register', [RegisteredUserController::class, 'store']);

// Login
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
```

**API Key Authentication:**

```php
// Flask API requires X-API-KEY header
$response = Http::withHeaders([
    'X-API-KEY' => env('FLASK_API_KEY')
])->post($url, $data);
```

### 9.2 Input Validation

**Laravel Validation Rules:**

```php
$validated = $request->validate([
    'target' => [
        'required',
        'string',
        'max:100',
        'regex:/^[0-9\.\/:a-zA-Z\-]+$/' // IP/CIDR only
    ],
    'creds.user' => 'nullable|string|max:255',
    'creds.pass' => 'nullable|string|max:255',
]);
```

**Prevents:**

-   SQL injection
-   Command injection
-   XSS attacks
-   Path traversal

### 9.3 Rate Limiting

**Implementation:**

```php
// 10 scans per minute per user
RateLimiter::for('mqtt_scan', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->id);
});
```

**Benefits:**

-   Prevents abuse
-   Protects against DoS
-   Ensures fair resource usage

### 9.4 CSRF Protection

**Laravel CSRF Token:**

```html
<form method="POST">
    @csrf
    <!-- form fields -->
</form>
```

**JavaScript:**

```javascript
fetch("/scan", {
    headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
            .content,
    },
});
```

### 9.5 TLS/SSL Configuration

**MQTT Secure Broker:**

```conf
listener 8883
allow_anonymous false
password_file /mosquitto/config/passwordfile

# TLS/SSL Configuration
certfile /mosquitto/certs/server.crt
keyfile /mosquitto/certs/server.key
require_certificate false
tls_version tlsv1.2
ciphers HIGH:!aNULL:!MD5

# Note: ACL disabled - authentication-only model
# acl_file /mosquitto/config/aclfile  (commented out)
```

**Python TLS Client:**

```python
client.tls_set(
    ca_certs=None,
    certfile=None,
    keyfile=None,
    cert_reqs=ssl.CERT_NONE,
    tls_version=ssl.PROTOCOL_TLS,
    ciphers=None
)
client.tls_insecure_set(True)  # Accept self-signed
```

---

## 10. MQTT Vulnerabilities & Mitigations

### 10.1 Overview

This section addresses the **five critical MQTT security vulnerabilities** identified in IoT deployments and provides implementation guidance for mitigation strategies in the MQTT Security Scanner project.

### 10.2 Vulnerability Assessment Matrix

| Vulnerability           | Risk Level  | Current Status          | Implementation Priority |
| ----------------------- | ----------- | ----------------------- | ----------------------- |
| Open Authentication     | 🔴 Critical | ✅ Detected & Mitigated | P0 - Implemented        |
| Plaintext Communication | 🔴 Critical | ✅ Detected & Mitigated | P0 - Implemented        |
| Topic Leakage (ACL)     | 🟡 High     | ❌ Not Implemented      | P2 - Out of Scope       |
| DoS via Flooding        | 🟡 High     | ⚠️ Partially Covered    | P1 - Documented         |
| Lack of Logging         | 🟢 Medium   | ✅ Implemented          | P0 - Implemented        |

---

### 10.3 Vulnerability 1: Open Authentication

#### 10.3.1 Description

**Threat:** MQTT brokers configured to allow anonymous login enable any client to connect without credentials, exposing all topics, messages, and system information to unauthorized access.

**Attack Scenarios:**

-   Unauthorized data extraction from sensor networks
-   Injection of malicious commands to IoT devices
-   Man-in-the-middle attacks
-   Industrial espionage

**Real-World Impact:**

```
Case Study: In 2019, researchers discovered 49,000+ publicly accessible
MQTT brokers with no authentication, exposing smart home devices,
industrial control systems, and medical equipment.
```

#### 10.3.2 Detection in Scanner

The MQTT Security Scanner automatically detects open authentication:

```python
# scanner.py - Authentication Detection
def try_mqtt_connect(host, port, use_tls=False, username=None, password=None):
    client = mqtt.Client(client_id=f"scanner_{random.randint(1000,9999)}")

    # Attempt anonymous connection
    if username is None:
        # No credentials provided
        result = client.connect(host, port, keepalive=60)
        if result == 0:  # Success
            classification = 'open_or_auth_ok'
            security_assessment['anonymous_allowed'] = True
            security_assessment['risk_level'] = 'HIGH'
```

**Dashboard Indication:**

-   **Open Brokers** card increments
-   Security column shows: 🔓 "Plain (No Auth)"
-   Risk assessment: **HIGH**

#### 10.3.3 Mitigation Implementation

**Step 1: Enforce Authentication in Mosquitto**

**File:** `mqtt-brokers/secure/config/mosquitto.conf`

```properties
# Disable anonymous access
allow_anonymous false

# Require password authentication
password_file /mosquitto/config/passwordfile

# Listener configuration
listener 8883
protocol mqtt
```

**Step 2: Create User Accounts**

```bash
# Access secure broker container
docker exec -it mosq_secure sh

# Add users with strong passwords
mosquitto_passwd -c /mosquitto/config/passwordfile admin
mosquitto_passwd -b /mosquitto/config/passwordfile faris02@gmail.com faris123
mosquitto_passwd -b /mosquitto/config/passwordfile sensor_device secure_device_pass

# Exit and restart broker
exit
docker-compose restart secure
```

**Step 3: Configure ESP32 with Credentials**

```cpp
// esp32_mixed_security.ino
const char* mqtt_user = "faris02@gmail.com";
const char* mqtt_pass = "faris123";

// Secure client setup
WiFiClientSecure espClientSecure;
PubSubClient clientSecure(espClientSecure);

// Connect with authentication
clientSecure.setServer(mqtt_server, mqtt_port_secure);
clientSecure.connect("ESP32_Secure", mqtt_user, mqtt_pass);
```

**Step 4: Verification**

```bash
# Test 1: Anonymous access (should fail)
mosquitto_sub -h 127.0.0.1 -p 8883 -t "#"
# Expected: Connection refused (5)

# Test 2: With credentials (should succeed)
mosquitto_sub -h 127.0.0.1 -p 8883 -t "#" \
  -u "faris02@gmail.com" -P "faris123"
# Expected: Connected and subscribed
```

**Implementation Status:** ✅ **Fully Implemented**

---

### 10.4 Vulnerability 2: Plaintext Communication

#### 10.4.1 Description

**Threat:** MQTT packets transmitted without encryption allow attackers to intercept sensitive data, credentials, and commands through network sniffing.

**Attack Scenarios:**

-   Packet sniffing with Wireshark/tcpdump
-   Credential harvesting
-   Sensor data interception
-   Command injection

**Packet Analysis Example:**

```bash
# Sniff unencrypted MQTT traffic
sudo tcpdump -i any -A 'tcp port 1883'

# Output shows plain text:
# CONNECT
# username: admin
# password: password123
# PUBLISH sensors/temperature: {"temp":25.5}
```

#### 10.4.2 Detection in Scanner

```python
# scanner.py - TLS Detection
def try_mqtt_connect(host, port, use_tls=False, ...):
    if port == 8883 or use_tls:
        # Attempt TLS connection
        client.tls_set(
            cert_reqs=ssl.CERT_NONE,
            tls_version=ssl.PROTOCOL_TLS
        )
        tls_info = extract_certificate_info(host, port)
        security_assessment['encryption'] = True
        security_assessment['risk_level'] = 'LOW'
    else:
        # Plain text connection
        security_assessment['encryption'] = False
        security_assessment['risk_level'] = 'HIGH'
```

#### 10.4.3 Mitigation Implementation

**Step 1: Generate TLS Certificates**

```bash
cd mqtt-brokers/secure/certs

# Generate CA key and certificate
openssl genrsa -out ca.key 2048
openssl req -new -x509 -days 3650 -key ca.key -out ca.crt \
  -subj "/C=MY/ST=Kuala Lumpur/L=KL/O=UniKL/CN=MQTT-CA"

# Generate server key
openssl genrsa -out server.key 2048

# Generate certificate signing request
openssl req -new -key server.key -out server.csr \
  -subj "/C=MY/ST=Kuala Lumpur/L=KL/O=UniKL/CN=192.168.100.56"

# Sign server certificate
openssl x509 -req -in server.csr -CA ca.crt -CAkey ca.key \
  -CAcreateserial -out server.crt -days 3650 -sha256

# Set permissions
chmod 644 ca.crt server.crt
chmod 600 ca.key server.key
```

**Step 2: Configure TLS in Mosquitto**

```properties
# mqtt-brokers/secure/config/mosquitto.conf

listener 8883
protocol mqtt

# TLS Configuration
cafile /mosquitto/certs/ca.crt
certfile /mosquitto/certs/server.crt
keyfile /mosquitto/certs/server.key

# Require TLS
require_certificate false  # Client cert not required

# TLS Protocol Version
tls_version tlsv1.2

# Strong Cipher Suites Only
ciphers ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256
```

**Step 3: ESP32 TLS Configuration**

```cpp
// esp32_mixed_security.ino

// Root CA Certificate (optional for testing)
const char* root_ca = R"EOF(
-----BEGIN CERTIFICATE-----
MIIDXTCCAkWgAwIBAgIJAKL...
-----END CERTIFICATE-----
)EOF";

void setup() {
  // Configure secure client
  espClientSecure.setCACert(root_ca);  // Optional
  espClientSecure.setInsecure();  // For self-signed (testing only)

  clientSecure.setServer(mqtt_server, 8883);
  clientSecure.connect("ESP32_Secure", mqtt_user, mqtt_pass);
}
```

**Step 4: Scanner TLS Analysis**

The scanner automatically extracts certificate information:

```python
def extract_certificate_info(host, port):
    context = ssl.create_default_context()
    context.check_hostname = False
    context.verify_mode = ssl.CERT_NONE

    with socket.create_connection((host, port), timeout=2) as sock:
        with context.wrap_socket(sock, server_hostname=host) as ssock:
            cert = ssock.getpeercert()

            return {
                'subject': dict(x[0] for x in cert['subject']),
                'issuer': dict(x[0] for x in cert['issuer']),
                'version': cert['version'],
                'serialNumber': cert['serialNumber'],
                'notBefore': cert['notBefore'],
                'notAfter': cert['notAfter'],
                'self_signed': is_self_signed(cert),
                'cipher': ssock.cipher(),
                'tls_version': ssock.version()
            }
```

**Implementation Status:** ✅ **Fully Implemented**

---

### 10.5 Vulnerability 3: Topic Leakage (ACL)

#### 10.5.1 Description

**Threat:** Without Access Control Lists (ACLs), authenticated users can subscribe to ALL topics, including sensitive system topics, administrative channels, and other users' data streams.

**Attack Scenarios:**

-   Lateral movement after credential compromise
-   Cross-tenant data leakage
-   Unauthorized access to $SYS topics
-   Privilege escalation

**Example Attack:**

```bash
# Attacker with valid sensor credentials
mosquitto_sub -h broker.com -p 8883 \
  -u "sensor01" -P "sensor_pass" \
  -t "#"  # Wildcard subscription

# Can now see:
# admin/commands
# users/alice/location
# $SYS/broker/clients/active
# critical/alarms
```

#### 10.5.2 Current Project Scope

**Why ACL is Not Implemented:**

This MQTT Security Scanner focuses on **network-level security assessment** and **authentication testing**. The project scope includes:

✅ Detecting open (anonymous) brokers
✅ Testing authentication requirements
✅ Analyzing TLS/SSL encryption
✅ Discovering published topics and sensor data

**ACL (Topic-level access control) is considered out of scope because:**

1. **No Administrative Interface:** The scanner is a read-only assessment tool, not a broker management system
2. **Complexity:** ACL requires multi-user management and permission systems beyond the scanning use case
3. **Project Focus:** Emphasis is on detecting **unauthenticated brokers** and **unencrypted communications** which are more critical vulnerabilities
4. **Real-world Analogy:** Similar to network scanners (Nmap, Nessus) that detect open ports and services but don't manage firewall rules

#### 10.5.3 Current Scanner Behavior

**Topic Discovery:**
The scanner discovers all accessible topics with valid credentials:

```python
# scanner.py
def on_message(client, userdata, msg):
    topic = msg.topic
    payload = msg.payload.decode('utf-8', errors='ignore')

    # Store all discovered topics
    if topic not in topics_discovered:
        topics_discovered[topic] = {
            'first_seen': datetime.now().isoformat(),
            'message_count': 0,
            'sample_payload': payload[:100]
        }
```

**Current Security Model:**

-   Secure broker requires username + password (authentication)
-   Once authenticated, users have access to all topics (no authorization/ACL)
-   Scanner reports what topics are accessible with given credentials

**Implementation Status:** ❌ **Not Implemented** (Out of project scope - Scanner is an assessment tool, not a broker management system)

---

### 10.6 Vulnerability 4: DoS via Flooding

#### 10.6.1 Description

**Threat:** High message publication rates, connection floods, or subscription storms can overwhelm MQTT brokers, causing service degradation or complete outage.

**Attack Scenarios:**

-   Message bombing (thousands of publishes per second)
-   Connection exhaustion (rapid connect/disconnect)
-   Subscription flooding (subscribing to massive wildcards)
-   Retained message spam

**Attack Example:**

```python
# Simple DoS attack script
import paho.mqtt.client as mqtt
import threading

def flood_attack():
    client = mqtt.Client()
    client.connect("target_broker", 1883)

    # Publish 10,000 messages
    for i in range(10000):
        client.publish("spam/topic", "x" * 1024, qos=2)

# Launch 100 threads
for _ in range(100):
    threading.Thread(target=flood_attack).start()
```

#### 10.6.2 Current Protection

**Laravel Rate Limiting:**

```php
// Already implemented for web API
RateLimiter::for('mqtt_scan', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->id);
});
```

**Limitation:** This only protects the Laravel API, NOT the MQTT brokers themselves.

#### 10.6.3 Mitigation Implementation

**Step 1: Connection Limits in Mosquitto**

**File:** `mqtt-brokers/secure/config/mosquitto.conf`

```properties
listener 8883

# Connection Limits
max_connections 100
max_connections_per_client 5

# Client Restrictions
max_inflight_messages 20
max_queued_messages 100

# Message Size Limits
message_size_limit 1048576  # 1MB max

# QoS Limits
max_qos 1  # Limit to QoS 0 and 1

# Keepalive
max_keepalive 300  # 5 minutes max

# Persistent Client Limits
persistent_client_expiration 1h
```

**Step 2: Docker Resource Limits**

**docker-compose.yml:**

```yaml
services:
    mosquitto_secure:
        image: eclipse-mosquitto:2.0
        container_name: mosq_secure

        # Resource limits
        deploy:
            resources:
                limits:
                    cpus: "1.0"
                    memory: 512M
                reservations:
                    cpus: "0.5"
                    memory: 256M

        # Restart policy
        restart: unless-stopped

        # Health check
        healthcheck:
            test: ["CMD", "mosquitto_sub", "-t", "$$SYS/#", "-C", "1"]
            interval: 30s
            timeout: 10s
            retries: 3
```

**Step 3: Monitoring Script**

**Script:** `monitor_broker_health.py`

```python
import paho.mqtt.client as mqtt
import time

def monitor_sys_topics():
    client = mqtt.Client()
    client.username_pw_set("admin", "adminpass")
    client.connect("127.0.0.1", 8883)

    metrics = {}

    def on_message(client, userdata, msg):
        topic = msg.topic
        value = msg.payload.decode()

        # Track key metrics
        if 'clients/active' in topic:
            metrics['active_clients'] = int(value)
        elif 'messages/received' in topic:
            metrics['msg_received'] = int(value)

        # Alert on anomalies
        if metrics.get('active_clients', 0) > 100:
            alert("High connection count!")

        if metrics.get('msg_received', 0) > 10000:
            alert("Potential DoS attack!")

    client.on_message = on_message
    client.subscribe("$SYS/#")
    client.loop_forever()

def alert(message):
    print(f"[ALERT] {message}")
```

**Implementation Status:** ⚠️ **Partially Implemented** (Laravel rate limiting exists, broker-level protection documented but not configured)

---

### 10.7 Vulnerability 5: Lack of Logging

#### 10.7.1 Description

**Threat:** Without comprehensive logging, security incidents cannot be detected, investigated, or proven for compliance/legal purposes.

**Missing Information Without Logging:**

-   Who connected when
-   What topics were accessed
-   Failed authentication attempts
-   Unusual activity patterns
-   Forensic evidence

#### 10.7.2 Current Implementation

**Mosquitto Logging:**

```properties
# mqtt-brokers/secure/config/mosquitto.conf
log_dest file /mosquitto/log/mosquitto.log
log_dest stdout

# Log types
log_type error
log_type warning
log_type notice
log_type information

# Connection logging
connection_messages true

# Timestamp format
log_timestamp true
log_timestamp_format %Y-%m-%dT%H:%M:%S
```

**Log Output Example:**

```
2025-12-21T10:30:15: New connection from 192.168.100.56:54321 on port 8883.
2025-12-21T10:30:15: Client ESP32_Secure connected (clean session=true)
2025-12-21T10:30:16: Client ESP32_Secure published to sensors/faris/dht_secure (QoS 0)
2025-12-21T10:30:45: Client ESP32_Secure disconnected.
```

**Implementation Status:** ✅ **Fully Implemented**

---

### 10.8 Vulnerability Summary & Recommendations

#### 10.8.1 Implementation Scorecard

| Vulnerability           | Detection | Mitigation Documented | Fully Implemented |
| ----------------------- | --------- | --------------------- | ----------------- |
| Open Authentication     | ✅ Yes    | ✅ Yes                | ✅ Yes            |
| Plaintext Communication | ✅ Yes    | ✅ Yes                | ✅ Yes            |
| Topic Leakage (ACL)     | ❌ No     | ✅ Yes                | ⚠️ Partial        |
| DoS Protection          | ❌ No     | ✅ Yes                | ⚠️ Partial        |
| Lack of Logging         | ✅ Yes    | ✅ Yes                | ✅ Yes            |

#### 10.8.2 Production Deployment Checklist

**Critical (Must Have):**

-   [x] Disable anonymous authentication
-   [x] Enable TLS/SSL encryption
-   [x] Use strong passwords (12+ characters)
-   [x] Enable comprehensive logging
-   [ ] Implement ACL for topic access control
-   [ ] Configure connection limits
-   [ ] Set message size limits

**Recommended (Should Have):**

-   [ ] Implement rate limiting
-   [ ] Set up monitoring and alerting
-   [ ] Regular log analysis
-   [ ] Network segmentation
-   [ ] Firewall rules
-   [ ] Regular security audits

**Best Practices (Nice to Have):**

-   [ ] Client certificate authentication
-   [ ] Message payload encryption
-   [ ] Intrusion detection system
-   [ ] Regular penetration testing
-   [ ] Automated compliance reporting

#### 10.8.3 Future Work

**Phase 1 (Short Term):**

1. Implement ACL detection in scanner
2. Add DoS testing capabilities
3. Create automated security reports

**Phase 2 (Medium Term):**

1. Mosquitto plugin for advanced rate limiting
2. Real-time threat detection with ML
3. Integration with SIEM systems

**Phase 3 (Long Term):**

1. Distributed broker scanning
2. Automated remediation
3. Compliance automation (ISO27001, NIST)

---

## 11. Testing & Validation

### 11.1 Unit Testing

**Test Files:**

```
mqtt-scanner/
├── test_auth_failures.py       # Auth detection
├── test_broker_auth.py         # Credential validation
├── test_esp32_connection.py    # ESP32 connectivity
└── clear_retained.py           # Message cleanup
```

**Run Tests:**

```bash
cd mqtt-scanner

# Test authentication failures
python test_auth_failures.py

# Test broker credentials
python test_broker_auth.py

# Test ESP32 connection
python test_esp32_connection.py
```

### 10.2 Integration Testing

**Test Scenario 1: End-to-End Scan**

```bash
# 1. Start all services
docker-compose up -d            # MQTT brokers
python app.py                   # Flask API
php artisan serve               # Laravel

# 2. Upload ESP32 code
# 3. Open http://127.0.0.1:8000
# 4. Scan network
# 5. Verify results
```

**Expected Results:**

-   ✅ 3 sensors detected (DHT11, LDR, PIR)
-   ✅ PIR on port 1883 (insecure)
-   ✅ DHT11, LDR on port 8883 (secure)

### 10.3 Security Testing

**Test Cases:**

1. **Anonymous Access Prevention**

```bash
# Try connecting without credentials to secure broker
mosquitto_sub -h 127.0.0.1 -p 8883 -t "#"
# Expected: Connection refused (5)
```

2. **Wrong Credentials Rejection**

```bash
mosquitto_sub -h 127.0.0.1 -p 8883 -t "#" \
  -u "wrong" -P "wrong"
# Expected: Not authorized
```

3. **TLS Enforcement**

```bash
# Try plain connection to secure port
mosquitto_sub -h 127.0.0.1 -p 8883 -t "#"
# Expected: Protocol error
```

### 10.4 Performance Testing

**Load Testing with Artillery:**

```yaml
config:
    target: "http://127.0.0.1:8000"
    phases:
        - duration: 60
          arrivalRate: 10

scenarios:
    - name: "Scan network"
      flow:
          - post:
                url: "/scan"
                json:
                    target: "192.168.100.0/24"
```

**Run Test:**

```bash
npm install -g artillery
artillery run load-test.yml
```

### 10.5 Validation Checklist

**Pre-Deployment:**

-   [ ] All Docker containers running
-   [ ] ESP32 publishing data
-   [ ] Flask API responding
-   [ ] Laravel dashboard accessible
-   [ ] Authentication working
-   [ ] Scans returning results
-   [ ] CSV export functioning
-   [ ] PDF generation working
-   [ ] No console errors
-   [ ] Mobile responsive

---

## 11. Deployment Guide

### 11.1 Production Environment Setup

**Server Requirements:**

-   Ubuntu 20.04 LTS or higher
-   4GB RAM minimum
-   20GB storage
-   Public IP address
-   Domain name (optional)

### 11.2 Docker Production Deployment

**docker-compose.prod.yml:**

```yaml
version: "3.8"
services:
    mosquitto_secure:
        image: eclipse-mosquitto:2.0
        container_name: mqtt_prod_secure
        restart: always
        volumes:
            - ./secure/config:/mosquitto/config
            - ./secure/data:/mosquitto/data
            - ./secure/log:/mosquitto/log
            - ./secure/certs:/mosquitto/certs
        ports:
            - "8883:8883"
        environment:
            - TZ=Asia/Kuala_Lumpur
```

### 11.3 Laravel Production Configuration

**.env.production:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mqtt_scanner
DB_USERNAME=mqtt_user
DB_PASSWORD=secure_password

FLASK_BASE=http://127.0.0.1:5000
FLASK_API_KEY=production-api-key-change-me
```

**Deploy Commands:**

```bash
# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 11.4 Nginx Configuration

**/etc/nginx/sites-available/mqtt-scanner:**

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/mqtt-scanner/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 11.5 SSL/TLS Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### 11.6 Process Management (Supervisor)

**/etc/supervisor/conf.d/flask-scanner.conf:**

```ini
[program:flask-scanner]
directory=/var/www/mqtt-scanner/mqtt-scanner
command=/usr/bin/python3 app.py
autostart=true
autorestart=true
stderr_logfile=/var/log/flask-scanner.err.log
stdout_logfile=/var/log/flask-scanner.out.log
user=www-data
```

**Start Supervisor:**

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start flask-scanner
```

---

## 12. Troubleshooting

### 12.1 Common Issues

#### Issue 1: ESP32 Not Connecting to WiFi

**Symptoms:**

-   Serial Monitor shows "Connecting..." repeatedly
-   No WiFi IP address assigned

**Solutions:**

```cpp
// Check WiFi credentials
const char* ssid = "CORRECT_SSID";
const char* password = "CORRECT_PASSWORD";

// Ensure 2.4GHz network (ESP32 doesn't support 5GHz)
// Check WiFi signal strength
// Restart ESP32
```

#### Issue 2: Docker Containers Not Starting

**Symptoms:**

```
Error: port already allocated
```

**Solutions:**

```bash
# Check if ports are in use
netstat -an | findstr "1883 8883"

# Stop conflicting services
docker-compose down

# Remove containers
docker rm -f mosq_insecure mosq_secure

# Restart
docker-compose up -d
```

#### Issue 3: Authentication Failures Not Detected

**Symptoms:**

-   Auth Failures card always shows 0
-   Scanner connects with wrong credentials

**Solutions:**

```bash
# Test authentication manually
cd mqtt-scanner
python test_auth_failures.py

# Check broker password file
docker exec mosq_secure cat /mosquitto/config/passwordfile

# Reset password
docker exec mosq_secure mosquitto_passwd -b \
  /mosquitto/config/passwordfile faris02@gmail.com faris123

# Restart broker
docker-compose restart secure
```

#### Issue 4: Laravel 500 Error

**Symptoms:**

-   White page with "Server Error"
-   500 Internal Server Error

**Solutions:**

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check permissions
chmod -R 755 storage bootstrap/cache

# Regenerate key
php artisan key:generate
```

#### Issue 5: Flask Scanner Timeout

**Symptoms:**

-   Scan takes too long
-   Timeout errors in dashboard

**Solutions:**

```python
# Increase timeout in scanner.py
TIMEOUT = 5  # Increase from 2 to 5 seconds

# Reduce listen duration
LISTEN_DURATION = 3  # Reduce from 5 to 3 seconds
```

```php
// Increase timeout in Laravel controller
$response = Http::timeout(60)  // Increase from 30 to 60
    ->post($url, $data);
```

### 12.2 Debugging Tools

**Check MQTT Connectivity:**

```bash
# Subscribe to all topics (insecure)
mosquitto_sub -h 127.0.0.1 -p 1883 -t "#" -v

# Subscribe with authentication (secure)
mosquitto_sub -h 127.0.0.1 -p 8883 -t "#" -v \
  -u faris02@gmail.com -P faris123 --cafile ca.crt
```

**Monitor Docker Logs:**

```bash
# All logs
docker-compose logs -f

# Specific service
docker-compose logs -f secure

# Last 50 lines
docker-compose logs --tail=50 insecure
```

**Laravel Debugging:**

```bash
# Enable debug mode
APP_DEBUG=true

# Check queue jobs
php artisan queue:work --verbose

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

**Flask Debugging:**

```python
# Enable debug mode in app.py
if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
```

### 12.3 Performance Optimization

**Laravel Optimizations:**

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

**Database Optimization:**

```bash
# Add indexes
php artisan migrate

# Optimize tables
php artisan db:optimize
```

**Frontend Optimization:**

```bash
# Minify assets
npm run build

# Enable compression in Nginx
gzip on;
gzip_types text/css application/javascript;
```

---

## 13. API Documentation

### 13.1 Laravel API Endpoints

#### POST /scan

**Description:** Initiate MQTT network scan

**Request:**

```http
POST /scan HTTP/1.1
Host: 127.0.0.1:8000
Content-Type: application/json
X-CSRF-TOKEN: csrf_token_here

{
  "target": "192.168.100.0/24",
  "creds": {
    "user": "faris02@gmail.com",
    "pass": "faris123"
  }
}
```

**Response:**

```json
{
    "results": [
        {
            "ip": "192.168.100.56",
            "port": 1883,
            "result": "connected",
            "classification": "open_or_auth_ok",
            "security_assessment": {
                "anonymous_allowed": true,
                "requires_auth": false
            }
        }
    ],
    "scan_duration": 5.2,
    "total_targets": 2
}
```

#### GET /results

**Description:** Retrieve last scan results

**Response:**

```json
{
  "results": [...],
  "count": 3,
  "timestamp": "2025-12-20T14:30:00Z"
}
```

### 13.2 Flask API Endpoints

#### POST /api/scan

**Description:** Execute MQTT broker scan

**Headers:**

```
X-API-KEY: my-very-secret-flask-key-CHANGEME
Content-Type: application/json
```

**Request:**

```json
{
    "target": "192.168.100.56",
    "creds": {
        "user": "testuser",
        "pass": "testpass"
    },
    "listen_duration": 5,
    "capture_all_topics": false
}
```

**Response:**

```json
[
    {
        "ip": "192.168.100.56",
        "port": 8883,
        "result": "connected",
        "classification": "open_or_auth_ok",
        "tls": true,
        "cert_info": {
            "subject": "CN=localhost",
            "issuer": "CN=localhost",
            "valid_from": "Dec 20 2024",
            "valid_to": "Dec 20 2025",
            "self_signed": true
        },
        "publishers": [
            {
                "topic": "sensors/faris/dht_secure",
                "payload": "{\"temp\":28.5,\"hum\":65.2}",
                "message_count": 3
            }
        ],
        "topics_discovered": {
            "sensors/faris/dht_secure": {
                "first_seen": "2025-12-20T14:30:00",
                "message_count": 3
            }
        },
        "security_assessment": {
            "anonymous_allowed": false,
            "requires_auth": true,
            "port_type": "secure"
        },
        "security_summary": {
            "risk_level": "LOW",
            "issues": [],
            "recommendations": ["Use strong passwords"]
        }
    }
]
```

#### GET /api/results

**Description:** Get cached scan results

**Response:**

```json
{
  "results": [...],
  "cached": true,
  "timestamp": "2025-12-20T14:30:00Z"
}
```

### 13.3 Error Responses

**400 Bad Request:**

```json
{
    "error": "Invalid target format",
    "message": "Target must be valid IP or CIDR range"
}
```

**401 Unauthorized:**

```json
{
    "error": "Invalid or missing API key"
}
```

**429 Too Many Requests:**

```json
{
    "error": "Rate limit exceeded",
    "retry_after": 45,
    "limit": "10 scans per 60 seconds"
}
```

**500 Internal Server Error:**

```json
{
    "error": "Failed to reach scanner",
    "message": "Connection timeout"
}
```

---

## 14. Performance & Scalability

### 14.1 Current Performance Metrics

**Scan Performance:**

-   Single broker scan: ~2-5 seconds
-   /24 subnet scan: ~30-60 seconds
-   Concurrent connections: 10 simultaneous

**Resource Usage:**

-   Laravel: ~50MB RAM
-   Flask: ~30MB RAM
-   Docker (2 containers): ~100MB RAM
-   ESP32: ~40KB RAM

### 14.2 Scalability Considerations

**Horizontal Scaling:**

```
Load Balancer
     │
     ├──► Laravel Instance 1
     ├──► Laravel Instance 2
     └──► Laravel Instance 3
              │
              ├──► Flask Scanner 1
              ├──► Flask Scanner 2
              └──► Flask Scanner 3
```

**Database Scaling:**

-   Switch from SQLite to PostgreSQL/MySQL
-   Implement connection pooling
-   Add read replicas

**Caching Strategy:**

```php
// Cache scan results
Cache::put("scan:{$target}", $results, 300); // 5 minutes

// Redis for session storage
SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 14.3 Optimization Techniques

**Scanner Optimizations:**

```python
# Concurrent scanning with ThreadPoolExecutor
from concurrent.futures import ThreadPoolExecutor

with ThreadPoolExecutor(max_workers=20) as executor:
    futures = [executor.submit(try_mqtt_connect, host, port)
               for host in hosts for port in ports]
    results = [f.result() for f in futures]
```

**Database Query Optimization:**

```php
// Eager loading
$scans = Scan::with('results')->get();

// Chunking large datasets
Scan::chunk(100, function ($scans) {
    foreach ($scans as $scan) {
        // Process
    }
});
```

---

## 15. Future Enhancements

### 15.1 Planned Features

**Phase 1 (Next 3 Months):**

-   [ ] WebSocket support for real-time updates
-   [ ] Advanced filtering in dashboard
-   [ ] Scan scheduling (cron jobs)
-   [ ] Email notifications for critical findings
-   [ ] Multi-tenant support

**Phase 2 (6 Months):**

-   [ ] Machine learning for anomaly detection
-   [ ] Historical trend analysis
-   [ ] Integration with SIEM systems
-   [ ] Mobile application (React Native)
-   [ ] REST API versioning

**Phase 3 (12 Months):**

-   [ ] Distributed scanning architecture
-   [ ] Cloud deployment (AWS/Azure)
-   [ ] Advanced threat intelligence
-   [ ] Compliance reporting (NIST, ISO27001)
-   [ ] Enterprise support features

### 15.2 Research Opportunities

-   MQTT v5.0 protocol support
-   CoAP protocol scanning
-   LoRaWAN device discovery
-   Blockchain for audit trails
-   AI-powered security recommendations

### 15.3 Community Contributions

**Contributing Guidelines:**

1. Fork repository
2. Create feature branch
3. Follow coding standards
4. Write tests
5. Submit pull request

**Areas for Contribution:**

-   Additional sensor drivers
-   New export formats
-   Internationalization (i18n)
-   Documentation improvements
-   Bug fixes

---

## Appendices

### Appendix A: Configuration Reference

**Complete .env Configuration:**

```env
# Application
APP_NAME="MQTT Security Scanner"
APP_ENV=local
APP_KEY=base64:xxxxxxxxxxxxx
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/database.sqlite

# Flask Scanner
FLASK_BASE=http://127.0.0.1:5000
FLASK_API_KEY=my-very-secret-flask-key-CHANGEME
FLASK_SECRET_KEY=flask-secret-key-change-me
FLASK_ADMIN_PASS=adminpass

# MQTT Broker
MQTT_HOST=127.0.0.1
MQTT_PORT=1883
MQTT_SECURE_PORT=8883

# Rate Limiting
RATE_LIMIT_WINDOW_SECS=60
MAX_SCANS_PER_WINDOW=10

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug

# Mail (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@mqtt-scanner.local"
MAIL_FROM_NAME="${APP_NAME}"
```

### Appendix B: Hardware Specifications

**ESP32 Technical Specifications:**

-   Processor: Xtensa dual-core 32-bit LX6 @ 240MHz
-   RAM: 520 KB SRAM
-   Flash: 4MB
-   WiFi: 802.11 b/g/n (2.4 GHz)
-   Bluetooth: v4.2 BR/EDR and BLE
-   GPIO: 34 programmable pins
-   ADC: 18 channels, 12-bit resolution
-   Operating Voltage: 3.3V
-   Input Voltage: 5V (USB) / 3.3V (Pin)

**Sensor Specifications:**

| Sensor               | Model    | Range                | Accuracy  | Interface |
| -------------------- | -------- | -------------------- | --------- | --------- |
| Temperature/Humidity | DHT11    | 0-50°C, 20-90% RH    | ±2°C, ±5% | Digital   |
| Light                | LDR      | 1-10000 Lux          | Variable  | Analog    |
| Motion               | HC-SR501 | 7m range, 120° angle | N/A       | Digital   |

### Appendix C: Network Diagram

```
Internet
    │
    ├── Router (192.168.100.1)
    │       │
    │       ├── PC/Laptop (192.168.100.56)
    │       │   ├── Docker (MQTT Brokers)
    │       │   ├── Laravel (Dashboard)
    │       │   └── Flask (Scanner API)
    │       │
    │       └── ESP32 (192.168.100.X)
    │           ├── DHT11 → MQTT Secure (8883)
    │           ├── LDR → MQTT Secure (8883)
    │           └── PIR → MQTT Insecure (1883)
    │
    └── External Devices (Optional)
```

### Appendix D: Security Checklist

**Production Security Checklist:**

-   [ ] Change all default passwords
-   [ ] Use strong API keys (32+ characters)
-   [ ] Enable HTTPS/TLS
-   [ ] Configure firewall rules
-   [ ] Implement rate limiting
-   [ ] Enable CSRF protection
-   [ ] Sanitize all inputs
-   [ ] Log security events
-   [ ] Regular security audits
-   [ ] Keep dependencies updated
-   [ ] Backup configuration files
-   [ ] Implement intrusion detection
-   [ ] Use environment variables for secrets
-   [ ] Enable two-factor authentication
-   [ ] Configure proper CORS headers

### Appendix E: Glossary

| Term             | Definition                                                                   |
| ---------------- | ---------------------------------------------------------------------------- |
| MQTT             | Message Queuing Telemetry Transport - lightweight publish/subscribe protocol |
| QoS              | Quality of Service - reliability level for message delivery                  |
| Retained Message | Message stored by broker and sent to new subscribers                         |
| Topic            | Named channel for publishing/subscribing messages                            |
| Payload          | Actual data content of MQTT message                                          |
| TLS/SSL          | Transport Layer Security - encryption protocol                               |
| CIDR             | Classless Inter-Domain Routing - IP address notation                         |
| rc               | Return Code - MQTT connection result                                         |
| CSP              | Content Security Policy - browser security feature                           |
| CSRF             | Cross-Site Request Forgery - security vulnerability                          |

### Appendix F: References

**Documentation:**

-   MQTT v3.1.1: http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html
-   MQTT v5.0: http://docs.oasis-open.org/mqtt/mqtt/v5.0/os/mqtt-v5.0-os.html
-   Paho MQTT: https://eclipse.dev/paho/
-   Laravel: https://laravel.com/docs
-   Flask: https://flask.palletsprojects.com/
-   ESP32: https://docs.espressif.com/

**Libraries:**

-   Paho MQTT Python: https://github.com/eclipse/paho.mqtt.python
-   Laravel Breeze: https://github.com/laravel/breeze
-   Mosquitto: https://mosquitto.org/documentation/

---

**Document Version:** 1.0  
**Last Updated:** December 20, 2025  
**Author:** Faris Shalim (Final Year Project)  
**Institution:** UniKL  
**Project Code:** IPB49906 - Final Year Project 2

---

## Document Change Log

| Version | Date       | Changes                             | Author       |
| ------- | ---------- | ----------------------------------- | ------------ |
| 1.0     | 2025-12-20 | Initial comprehensive documentation | Faris Shalim |

---

**END OF DOCUMENTATION**
