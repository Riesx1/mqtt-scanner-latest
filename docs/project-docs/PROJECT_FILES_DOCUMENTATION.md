# MQTT Scanner Project - File Documentation

## Project Overview
This is a comprehensive MQTT Security Scanner built with Laravel (backend/dashboard) and Flask (Python scanner). It scans MQTT brokers to detect security vulnerabilities, monitors ESP32 IoT sensors, and provides real-time security analysis.

---

## Core Application Files

### Laravel Application (PHP Backend)

#### **app/Http/Controllers/**
- **MqttScannerController.php**
  - Main controller for MQTT scanning functionality
  - Handles scan requests by communicating with Flask API
  - Functions:
    - `index()`: Displays the dashboard
    - `scan()`: Initiates MQTT scan via Flask backend
    - `results()`: Fetches scan results from Flask
  - Acts as a proxy between Laravel frontend and Flask scanner

- **SensorDataController.php**
  - Manages ESP32 sensor data display
  - Functions:
    - `index()`: Lists all sensors
    - `show()`: Shows detailed sensor information

- **ProfileController.php**
  - Handles user profile management
  - Functions for edit, update, and delete user profiles

#### **app/Models/**
- **User.php**
  - User authentication model
  - Handles user registration, login, and authentication

- **SensorReading.php**
  - Model for storing ESP32 sensor readings in database
  - Tracks temperature, humidity, light, and motion data

#### **app/Services/**
- **MqttSensorService.php**
  - Service class for MQTT sensor operations
  - Connects to MQTT brokers
  - Subscribes to sensor topics
  - Processes sensor data from ESP32 devices

- **MqttClientTracker.php**
  - Tracks MQTT client connections
  - Monitors active connections and disconnections

- **MqttSecurityAnalyzer.php**
  - Analyzes MQTT broker security configurations
  - Detects vulnerabilities and security issues

#### **routes/**
- **web.php**
  - Defines all web routes for the application
  - Key routes:
    - `/dashboard`: Main scanner dashboard (NO AUTH REQUIRED)
    - `/scan`: POST endpoint to start scanning
    - `/results`: GET endpoint to fetch scan results
    - `/sensors`: Sensor data routes

- **auth.php**
  - Authentication routes (login, register, password reset)

- **console.php**
  - Artisan console command routes

#### **resources/views/**
- **dashboard.blade.php**
  - Main dashboard UI for MQTT scanner
  - Features:
    - Network scanner interface
    - Real-time scan status
    - Security analysis display
    - TLS certificate information
    - Sensor data visualization
    - CSV and PDF export functionality
  - Uses Tailwind CSS for styling
  - Includes JavaScript for AJAX requests and PDF generation

#### **config/**
- **app.php**: Application configuration
- **database.php**: Database connection settings
- **queue.php**: Queue configuration
- **services.php**: Third-party service configurations

---

## Python Scanner (Flask Application)

#### **mqtt-scanner/app.py**
  - Flask web server for MQTT scanning operations
  - Functions:
    - `/api/scan`: API endpoint to trigger MQTT scans
    - `/api/results`: Returns scan results in JSON
    - `get_cert_info()`: Extracts TLS certificate information
    - `probe_broker_topics()`: Discovers MQTT topics and publishers
    - Rate limiting for API protection
    - API key authentication for security
  - Integrates with `scanner.py` for actual scanning

#### **mqtt-scanner/scanner.py**
  - Core MQTT scanning engine
  - Functions:
    - `run_scan()`: Scans IP ranges for MQTT brokers
    - Port detection (1883 insecure, 8883 secure)
    - Authentication testing
    - Publisher/subscriber detection
    - Topic discovery
  - Returns detailed scan results including:
    - IP and port information
    - Security classification
    - TLS status
    - Active publishers and topics

#### **mqtt-scanner/templates/**
  - **dashboard_pretty.html**: Flask UI template (not actively used, Laravel dashboard preferred)
  - **index.html**: Alternative scanner interface
  - **login.html**: Flask authentication page

#### **mqtt-scanner/requirements.txt**
  - Python dependencies:
    - Flask: Web framework
    - paho-mqtt: MQTT client library
    - flask-cors: Cross-origin resource sharing
    - Additional libraries for security scanning

---

## ESP32 IoT Device Code

#### **esp32_mixed_security/**
- **esp32_mixed_security.ino**
  - Arduino code for ESP32 microcontroller
  - Functions:
    - Connects to WiFi network
    - Reads DHT22 (temperature/humidity) sensor
    - Reads LDR (light) sensor
    - Reads PIR (motion) sensor
    - Publishes data to MQTT brokers:
      - Secure broker (Port 8883 with TLS): DHT and LDR
      - Insecure broker (Port 1883): PIR sensor
  - Demonstrates mixed security environment

---

## MQTT Broker Configuration

#### **mqtt-brokers/docker-compose.yml**
  - Docker configuration for running MQTT brokers
  - Sets up two Mosquitto brokers:
    - **Insecure Broker** (Port 1883): No encryption, anonymous access
    - **Secure Broker** (Port 8883): TLS encryption, requires authentication
  - Used for testing and demonstration

#### **mqtt-brokers/secure/certs/**
  - TLS certificates for secure MQTT broker
  - ca.crt: Certificate Authority
  - server.crt: Server certificate
  - server.key: Private key

#### **mqtt-brokers/secure/config/**
  - mosquitto.conf: Configuration for secure broker
    - Enables TLS
    - Requires username/password
    - Certificate paths

#### **mqtt-brokers/insecure/config/**
  - mosquitto.conf: Configuration for insecure broker
    - Anonymous access allowed
    - No encryption

---

## Database

#### **database/migrations/**
- **create_users_table.php**: Creates users table for authentication
- **create_sensor_readings_table.php**: Creates table for ESP32 sensor data
- **create_cache_table.php**: Creates cache table for performance
- **create_jobs_table.php**: Creates jobs table for queue processing

#### **database/database.sqlite**
  - SQLite database file
  - Stores user accounts and sensor readings

---

## Configuration Files

#### **composer.json**
  - PHP dependency manager for Laravel
  - Defines required packages and autoloading

#### **package.json**
  - Node.js dependencies
  - Frontend build tools (Vite, Tailwind CSS)

#### **.env.example**
  - Environment configuration template
  - Database settings
  - MQTT credentials
  - Flask API settings
  - Must be copied to `.env` and configured

#### **vite.config.js**
  - Frontend build configuration for Laravel

#### **tailwind.config.js**
  - Tailwind CSS configuration for styling

---

## Scripts and Testing Files

#### **scripts/python-tests/**
  - **check_sensors.py**: Verify ESP32 sensor connectivity
  - **test_esp32_sensors.py**: Test ESP32 sensor publishing
  - **test_mqtt_scan.py**: Test MQTT scanning functionality
  - **test_publisher.py**: Test MQTT publisher
  - **test_sensor_mqtt.py**: Test sensor MQTT communication
  - **verify_esp32_publishing.py**: Verify ESP32 is publishing data
  - **quick_test_mqtt.py**: Quick MQTT connection test

#### **scripts/batch-files/**
  - **start_all.bat**: Start all services (Laravel, Flask, MQTT brokers)
  - **start_flask.bat**: Start only Flask scanner

#### **scripts/**
  - **test_flask_connection.php**: Test Laravel to Flask connectivity
  - **cookies.txt**, **cookies2.txt**: Session/cookie test files

---

## Documentation Files (docs/project-docs/)

#### **README.md**
  - Main project documentation
  - Setup instructions
  - Usage guide

#### **QUICK_START.md**
  - Quick setup guide for getting started

#### **SYSTEM_DOCUMENTATION.md**
  - Detailed system architecture documentation

#### **REQUIREMENTS.md**
  - Software and hardware requirements

#### **TEST_INSTRUCTIONS.md**
  - How to run tests and verify functionality

#### **CHANGELOG.md**
  - Version history and changes

#### **FIXES_APPLIED.md**
  - Bug fixes and issues resolved

#### **DIAGNOSTIC.md**
  - Troubleshooting guide

---

## Key Technologies Used

### Backend
- **Laravel 11**: PHP framework for web application
- **Flask**: Python framework for MQTT scanning API
- **SQLite**: Lightweight database

### Frontend
- **Blade**: Laravel templating engine
- **Tailwind CSS**: Utility-first CSS framework
- **JavaScript**: Client-side functionality
- **jsPDF**: PDF generation library

### IoT & MQTT
- **Mosquitto**: MQTT broker
- **Paho MQTT**: MQTT client library
- **ESP32**: IoT microcontroller
- **DHT22, LDR, PIR**: Sensors

### DevOps
- **Docker**: Container platform for MQTT brokers
- **Composer**: PHP package manager
- **npm**: Node.js package manager

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     User Browser                             │
│              (Dashboard Interface)                           │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│               Laravel Application                            │
│         (Web Server - Port 8000)                            │
│   - Routes requests                                         │
│   - Renders dashboard                                       │
│   - Proxies to Flask API                                    │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│           Flask Scanner API                                  │
│         (Python - Port 5000)                                │
│   - Performs MQTT scans                                     │
│   - Analyzes security                                       │
│   - Detects publishers/topics                               │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│              MQTT Brokers                                    │
│   ┌─────────────────────┐  ┌─────────────────────┐         │
│   │  Insecure (1883)    │  │  Secure (8883)      │         │
│   │  - No encryption    │  │  - TLS encryption   │         │
│   │  - Anonymous        │  │  - Authentication   │         │
│   └─────────────────────┘  └─────────────────────┘         │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                ESP32 IoT Device                              │
│   - Reads sensors (DHT22, LDR, PIR)                        │
│   - Publishes to both brokers                               │
│   - Demonstrates mixed security                             │
└─────────────────────────────────────────────────────────────┘
```

---

## How to Run the Project

1. **Start MQTT Brokers** (Docker):
   ```bash
   cd mqtt-brokers
   docker-compose up -d
   ```

2. **Start Flask Scanner**:
   ```bash
   cd mqtt-scanner
   python app.py
   ```

3. **Start Laravel Application**:
   ```bash
   php artisan serve
   ```

4. **Upload ESP32 Code**:
   - Open esp32_mixed_security.ino in Arduino IDE
   - Configure WiFi credentials
   - Upload to ESP32

5. **Access Dashboard**:
   - Open browser to `http://localhost:8000/dashboard`
   - No login required
   - Enter target IP to scan

---

## Security Features

1. **TLS/SSL Detection**: Identifies encrypted connections
2. **Certificate Analysis**: Extracts and validates TLS certificates
3. **Authentication Testing**: Checks if authentication is required
4. **Anonymous Access Detection**: Identifies insecure brokers
5. **Topic Discovery**: Finds active MQTT topics
6. **Publisher Detection**: Identifies devices publishing data
7. **Security Scoring**: Rates broker security (0-100)

---

## Future Improvements

- Add more sensor types
- Implement automated remediation
- Add email notifications for security issues
- Enhanced reporting features
- Multi-language support
- Real-time monitoring dashboard

---

**Last Updated**: December 2, 2025
**Version**: 2.0
**Developed for**: Final Year Project (FYP)
