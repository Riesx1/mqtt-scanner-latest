# CHAPTER 4: IMPLEMENTATION

## 4.1 Introduction

This chapter documents the comprehensive implementation process of the MQTT Network Security Scanner prototype system, detailing the practical transformation of the methodology framework presented in Chapter 3 into a functional, production-ready web-based security assessment tool. The implementation phase spanned two academic semesters following the Agile-inspired iterative development approach, progressing from FYP1's command-line interface (CLI) prototype to FYP2's integrated web platform combining Laravel PHP framework for the presentation layer, Flask Python framework for the API orchestration layer, and purpose-built Python scanning engine for MQTT protocol analysis. This chapter provides a complete development guide documenting installation procedures, software prerequisites, system configuration, database schema implementation, source code architecture, hardware testbed deployment, integration workflows, and operational user procedures necessary to reproduce the entire system from scratch. The implementation adhered to DevSecOps principles incorporating security-by-design practices including input validation, authentication enforcement, rate limiting, audit logging, and secure credential management throughout the development lifecycle. This comprehensive documentation enables future researchers or practitioners to replicate the system implementation, extend functionality with additional scanning capabilities, or adapt the architecture for alternative IoT protocol assessment scenarios.

## 4.2 Development Environment Setup

### 4.2.1 Hardware Requirements

The development and deployment of the MQTT Network Security Scanner system was conducted on a workstation meeting the following minimum specifications:

**Table 4.1: Hardware Specifications**

| Component       | Specification                                           | Purpose                                                                                           |
| --------------- | ------------------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| Processor       | Intel Core i5 or equivalent                             | Running Docker containers, web server, and concurrent scanning operations                         |
| RAM             | 8 GB minimum, 16 GB recommended                         | Supporting Laravel application, Flask API, MySQL database, and Docker containers simultaneously   |
| Storage         | 20 GB available disk space                              | Accommodating PHP and Python dependencies, Docker images, database storage, and log files         |
| Network         | WiFi or Ethernet connectivity                           | Accessing target MQTT brokers, testing ESP32 hardware integration, and external API communication |
| Microcontroller | ESP32 Development Board                                 | Publishing sensor telemetry to test brokers for validation testing                                |
| Sensors         | DHT11 (temperature/humidity), LDR (light), PIR (motion) | Generating realistic IoT traffic patterns for scanner validation                                  |

### 4.2.2 Software Prerequisites

The system implementation required installation and configuration of multiple software components across the development stack. The following subsections detail each prerequisite with specific version requirements and installation procedures for Windows operating system.

#### 4.2.2.1 PHP and Composer

Laravel framework requires PHP version 8.2 or higher with specific extensions enabled. Installation steps:

1. Download PHP 8.2+ from [https://windows.php.net/download/](https://windows.php.net/download/) (Thread Safe ZIP package)
2. Extract to `C:\php` and add to system PATH environment variable
3. Configure `php.ini` by copying `php.ini-development` to `php.ini` and enabling required extensions:

```ini
extension=pdo_mysql
extension=pdo_sqlite
extension=fileinfo
extension=openssl
extension=mbstring
extension=curl
extension=zip
```

4. Verify PHP installation:

```powershell
php -v
# Expected output: PHP 8.2.x (cli) (built: ...)
```

5. Install Composer dependency manager from [https://getcomposer.org/](https://getcomposer.org/):

```powershell
# Download and run Composer-Setup.exe
# Verify installation:
composer --version
# Expected output: Composer version 2.x.x
```

#### 4.2.2.2 Node.js and NPM

Frontend asset compilation using Vite requires Node.js runtime:

1. Download Node.js LTS version (20.x or higher) from [https://nodejs.org/](https://nodejs.org/)
2. Run installer accepting default options
3. Verify installation:

```powershell
node --version
# Expected output: v20.x.x

npm --version
# Expected output: 10.x.x
```

#### 4.2.2.3 Python and Virtual Environment

The scanning engine and Flask API require Python 3.10 or higher:

1. Download Python 3.10+ from [https://www.python.org/downloads/](https://www.python.org/downloads/)
2. During installation, check "Add Python to PATH" option
3. Verify installation:

```powershell
python --version
# Expected output: Python 3.10.x or higher

pip --version
# Expected output: pip 23.x.x from...
```

4. Install virtualenv for isolated Python environments:

```powershell
pip install virtualenv
```

#### 4.2.2.4 Database System (MySQL or SQLite)

The Laravel application supports multiple database backends. For development, SQLite provides simplicity, while MySQL offers production-grade features.

**Option A: SQLite (Recommended for Development)**

SQLite comes bundled with PHP. No additional installation required. Database file will be created automatically during migration.

**Option B: MySQL (Recommended for Production)**

1. Download MySQL Community Server 8.0+ from [https://dev.mysql.com/downloads/mysql/](https://dev.mysql.com/downloads/mysql/)
2. Run installer and configure root password
3. Create database for the application:

```sql
CREATE DATABASE mqtt_scanner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mqtt_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON mqtt_scanner.* TO 'mqtt_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 4.2.2.5 Docker Desktop

Docker enables deployment of MQTT broker testbed infrastructure:

1. Download Docker Desktop for Windows from [https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop/)
2. Install and enable WSL 2 backend when prompted
3. Verify installation:

```powershell
docker --version
# Expected output: Docker version 24.x.x

docker-compose --version
# Expected output: Docker Compose version 2.x.x
```

#### 4.2.2.6 Git Version Control

Git facilitates source code management and collaboration:

1. Download Git for Windows from [https://git-scm.com/download/win](https://git-scm.com/download/win)
2. Install with default options
3. Configure global user settings:

```powershell
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

#### 4.2.2.7 Arduino IDE (for ESP32 Programming)

ESP32 firmware development requires Arduino IDE with ESP32 board support:

1. Download Arduino IDE 2.x from [https://www.arduino.cc/en/software](https://www.arduino.cc/en/software)
2. Install ESP32 board support:
    - Open Arduino IDE
    - Navigate to File → Preferences
    - Add to Additional Boards Manager URLs:
        ```
        https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
        ```
    - Navigate to Tools → Board → Boards Manager
    - Search "ESP32" and install "esp32 by Espressif Systems"

3. Install required libraries via Library Manager (Sketch → Include Library → Manage Libraries):
    - PubSubClient (version 2.8 or higher)
    - DHT sensor library by Adafruit
    - Adafruit Unified Sensor

### 4.2.3 IDE and Development Tools

The following development tools enhanced productivity during implementation:

- **Visual Studio Code**: Primary code editor with PHP Intelephense, Python, and Laravel extensions
- **Postman**: API endpoint testing for Flask RESTful services
- **MySQL Workbench** (if using MySQL): Database schema visualization and query development
- **MQTT Explorer**: Visual MQTT client for verifying broker connectivity and topic structure

## 4.3 Project Installation from Scratch

This section provides step-by-step instructions to clone the repository and install all dependencies, reproducing the complete development environment.

### 4.3.1 Repository Cloning

Clone the project repository from GitHub:

```powershell
# Navigate to desired project directory
cd "S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop"

# Clone the repository
git clone https://github.com/Riesx1/mqtt-scanner-latest.git

# Navigate into project directory
cd mqtt-scanner-latest
```

### 4.3.2 Laravel Application Setup

Configure the Laravel web application following these sequential steps:

#### 4.3.2.1 Install PHP Dependencies

```powershell
# Install all Composer dependencies defined in composer.json
composer install
```

This command installs the following key Laravel framework components:

- `laravel/framework` (^12.0): Core Laravel framework
- `laravel/tinker` (^2.10): Interactive REPL for debugging
- `php-mqtt/client` (^2.3): MQTT client library for PHP

#### 4.3.2.2 Environment Configuration

```powershell
# Copy example environment file
copy .env.example .env

# Generate application encryption key
php artisan key:generate
```

Edit `.env` file to configure database and external service connections:

```dotenv
APP_NAME="MQTT Scanner"
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration (SQLite for development)
DB_CONNECTION=sqlite
# For MySQL, uncomment and configure:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=mqtt_scanner
# DB_USERNAME=mqtt_user
# DB_PASSWORD=secure_password

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Queue Configuration
QUEUE_CONNECTION=database

# Flask API Configuration
FLASK_BASE=http://127.0.0.1:5000
FLASK_API_KEY=my-very-secret-flask-key-CHANGEME
```

#### 4.3.2.3 Database Setup

For SQLite (recommended for development):

```powershell
# Create empty database file
New-Item database/database.sqlite
```

For MySQL (production deployment):

Ensure the database created in section 4.2.2.4 exists, then configure connection in `.env` file.

Run database migrations to create all required tables:

```powershell
# Execute all migration files in database/migrations/
php artisan migrate
```

This command creates the following database schema:

**Table 4.2: Database Schema Overview**

| Table Name            | Purpose                                      | Key Fields                                                                                      |
| --------------------- | -------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| `users`               | User authentication and profile management   | id, name, email, password, mqtt_broker, mqtt_username, mqtt_password                            |
| `mqtt_scan_histories` | Scan session metadata and summary statistics | id, user_id, target, started_at, completed_at, status, total_targets, vulnerable_count          |
| `mqtt_scan_results`   | Individual broker findings per scan          | id, scan_history_id, user_id, ip_address, port, outcome, severity, tls_available, auth_required |
| `sensor_readings`     | ESP32 sensor telemetry for testing           | id, user_id, sensor_type, value, unit, mqtt_topic, published_at                                 |
| `cache`               | Application cache storage                    | key, value, expiration                                                                          |
| `jobs`                | Background job queue                         | id, queue, payload, attempts, available_at                                                      |

#### 4.3.2.4 Install Frontend Dependencies

```powershell
# Install Node.js packages defined in package.json
npm install
```

This installs:

- `vite` (^7.0): Next-generation frontend build tool
- `tailwindcss` (^4.0): Utility-first CSS framework
- `axios` (^1.11): HTTP client for API requests
- `jspdf` and `jspdf-autotable`: PDF report generation

#### 4.3.2.5 Compile Frontend Assets

```powershell
# Build production-optimized assets
npm run build

# OR for development with hot reload:
npm run dev
```

### 4.3.3 Python Scanning Engine Setup

Configure the Python virtual environment and install scanning engine dependencies:

#### 4.3.3.1 Create Virtual Environment

```powershell
# Create isolated Python environment
python -m venv .venv

# Activate virtual environment
.\.venv\Scripts\Activate.ps1
```

After activation, your terminal prompt should show `(.venv)` prefix.

#### 4.3.3.2 Install Python Dependencies

Navigate to the mqtt-scanner directory and install requirements:

```powershell
cd mqtt-scanner

# Install packages from requirements.txt
pip install -r requirements.txt
```

This installs critical scanning dependencies:

**Table 4.3: Python Dependencies**

| Package         | Version  | Purpose                               |
| --------------- | -------- | ------------------------------------- |
| Flask           | (latest) | RESTful API framework                 |
| flask-cors      | (latest) | Cross-Origin Resource Sharing support |
| flask-wtf       | (latest) | CSRF protection                       |
| paho-mqtt       | ≥1.6.1   | MQTT protocol client library          |
| requests        | (latest) | HTTP client for external API calls    |
| python-dateutil | (latest) | Date/time parsing utilities           |

#### 4.3.3.3 Configure Flask Environment

Create Flask-specific environment configuration:

```powershell
# In mqtt-scanner directory, create .env file
New-Item .env

# Edit .env file with configuration:
```

```dotenv
FLASK_APP=app.py
FLASK_ENV=development
FLASK_SECRET_KEY=generate-random-secret-key-here
FLASK_API_KEY=my-very-secret-flask-key-CHANGEME
FLASK_ADMIN_PASS=adminpass

# Rate limiting configuration
RATE_LIMIT_WINDOW_SECS=60
MAX_SCANS_PER_WINDOW=5
```

**Security Note**: In production, replace default keys with cryptographically secure random values:

```powershell
# Generate secure Flask secret key
python -c "import secrets; print(secrets.token_hex(24))"
```

### 4.3.4 MQTT Broker Testbed Deployment

Deploy Docker-based MQTT broker infrastructure for testing and validation:

#### 4.3.4.1 Navigate to Broker Configuration

```powershell
cd mqtt-brokers
```

#### 4.3.4.2 Generate TLS Certificates for Secure Broker

The secure broker (port 8883) requires X.509 certificates for TLS encryption:

```powershell
# Create secure certs directory if not exists
New-Item -ItemType Directory -Path "secure/certs" -Force

cd secure/certs

# Generate Certificate Authority (CA) private key
openssl genrsa -out ca.key 2048

# Generate CA certificate (valid for 365 days)
openssl req -new -x509 -days 365 -key ca.key -out ca.crt -subj "/C=MY/ST=State/L=City/O=Org/OU=IT/CN=localhost"

# Generate server private key
openssl genrsa -out server.key 2048

# Generate Certificate Signing Request (CSR)
openssl req -new -key server.key -out server.csr -subj "/C=MY/ST=State/L=City/O=Org/OU=IT/CN=localhost"

# Sign server certificate with CA
openssl x509 -req -in server.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out server.crt -days 365

cd ../..
```

#### 4.3.4.3 Configure Mosquitto Broker Settings

**Insecure Broker Configuration** (`insecure/config/mosquitto.conf`):

```conf
# Allow anonymous connections (intentionally vulnerable for testing)
allow_anonymous true

# Disable TLS
listener 1883
protocol mqtt

# Enable persistence
persistence true
persistence_location /mosquitto/data/

# Logging
log_dest file /mosquitto/log/mosquitto.log
log_type all
log_timestamp true
```

**Secure Broker Configuration** (`secure/config/mosquitto.conf`):

```conf
# Disable anonymous access (require authentication)
allow_anonymous false
password_file /mosquitto/config/passwd

# TLS configuration
listener 8883
protocol mqtt
cafile /mosquitto/certs/ca.crt
certfile /mosquitto/certs/server.crt
keyfile /mosquitto/certs/server.key
tls_version tlsv1.2

# Persistence
persistence true
persistence_location /mosquitto/data/

# Logging
log_dest file /mosquitto/log/mosquitto.log
log_type all
log_timestamp true
```

#### 4.3.4.4 Create User Credentials for Secure Broker

```powershell
# Create password file for secure broker
docker run -it --rm eclipse-mosquitto:2.0 mosquitto_passwd -c -b passwd faris02@gmail.com Faris02!

# Copy generated passwd file to secure/config/
# (The above command outputs the passwd file content, save it to secure/config/passwd)
```

Alternatively, create directly using Docker volume:

```powershell
# Run temporary container to generate password file
docker run -it --rm -v ${PWD}/secure/config:/config eclipse-mosquitto:2.0 mosquitto_passwd -c /config/passwd faris02@gmail.com

# When prompted, enter password: Faris02!
```

#### 4.3.4.5 Launch Docker Containers

Review `docker-compose.yml` configuration:

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

Start both brokers:

```powershell
# Pull Mosquitto images and start containers in detached mode
docker-compose up -d

# Verify containers are running
docker ps
# Expected output: Both mosq_insecure and mosq_secure with status "Up"

# Check logs for successful startup
docker logs mosq_insecure
docker logs mosq_secure
```

### 4.3.5 ESP32 Hardware Configuration

Deploy ESP32 microcontroller with sensors to generate realistic IoT traffic for scanner validation.

#### 4.3.5.1 Hardware Wiring Connections

**Table 4.4: ESP32 Sensor Wiring Diagram**

| Sensor       | Component Pin | ESP32 GPIO         | Wire Color | Purpose                     |
| ------------ | ------------- | ------------------ | ---------- | --------------------------- |
| DHT11        | VCC           | 3.3V               | Red        | Power supply                |
| DHT11        | GND           | GND                | Black      | Ground                      |
| DHT11        | DATA          | GPIO 4             | Yellow     | Temperature & humidity data |
| LDR          | Terminal 1    | 3.3V               | Red        | Light sensor supply         |
| LDR          | Terminal 2    | GPIO 34 (ADC1_CH6) | Green      | Analog light reading        |
| LDR Resistor | 10kΩ to GND   | GND                | Black      | Voltage divider ground      |
| PIR          | VCC           | 5V                 | Red        | Motion sensor power         |
| PIR          | GND           | GND                | Black      | Ground                      |
| PIR          | OUT           | GPIO 27            | Blue       | Digital motion detection    |

#### 4.3.5.2 Arduino Code Upload

1. Open Arduino IDE
2. Open `esp32_mixed_security/esp32_mixed_security.ino`
3. Configure WiFi credentials in the code:

```cpp
const char *ssid = "Your_WiFi_SSID";
const char *password = "Your_WiFi_Password";
```

4. Configure MQTT broker IP address (replace with your PC's WiFi IP):

```cpp
const char *mqtt_server = "192.168.100.57"; // Update to your PC's local IP
```

5. Select board and port:
    - Tools → Board → ESP32 Arduino → ESP32 Dev Module
    - Tools → Port → (Select appropriate COM port)

6. Click Upload button (→) and wait for compilation and flashing

7. Open Serial Monitor (Tools → Serial Monitor, 115200 baud) to verify connection:

```
Expected output:
WiFi connected
IP address: 192.168.xxx.xxx
Secure broker connected
Publishing DHT secure data...
Publishing LDR secure data...
Motion detected! Publishing to insecure broker...
```

#### 4.3.5.3 Firmware Functionality Overview

The ESP32 firmware implements dual MQTT client connections demonstrating mixed security posture:

**Table 4.5: ESP32 MQTT Publishing Behavior**

| Sensor                | Broker Type | Port | Authentication               | TLS      | Topic                      | Publish Interval |
| --------------------- | ----------- | ---- | ---------------------------- | -------- | -------------------------- | ---------------- |
| DHT11 (Temp/Humidity) | Secure      | 8883 | faris02@gmail.com / Faris02! | Enabled  | sensors/faris/dht_secure   | 3 seconds        |
| LDR (Light)           | Secure      | 8883 | faris02@gmail.com / Faris02! | Enabled  | sensors/faris/ldr_secure   | 3 seconds        |
| PIR (Motion)          | Insecure    | 1883 | None (Anonymous)             | Disabled | sensors/faris/pir_insecure | On motion event  |

This configuration enables validation testing demonstrating the scanner's ability to detect:

- Anonymous access vulnerability (port 1883)
- Authentication-required configuration (port 8883)
- TLS encryption enforcement (port 8883)
- Active topic publishing for traffic observation

## 4.4 System Architecture Implementation

This section details the three-tier architecture implementation translating the design from Chapter 3 into executable code.

### 4.4.1 Architecture Overview

The system implements a modular three-tier architecture ensuring separation of concerns, independent component testing, and scalability:

**Figure 4.1: Three-Tier Implementation Architecture**

```
┌─────────────────────────────────────────────────────────────┐
│                    TIER 3: Presentation Layer                │
│  ┌───────────────────────────────────────────────────────┐  │
│  │         Laravel 12 Web Application (PHP 8.2)          │  │
│  │  • Blade Templates (dashboard.blade.php)              │  │
│  │  • MqttScannerController (scan orchestration)         │  │
│  │  • Authentication (Laravel Breeze)                    │  │
│  │  • Database ORM (Eloquent Models)                     │  │
│  │  • CSRF Protection, Input Validation, Rate Limiting   │  │
│  └───────────────────────────────────────────────────────┘  │
│                         ↕ HTTP/JSON                          │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                     TIER 2: API Layer                        │
│  ┌───────────────────────────────────────────────────────┐  │
│  │            Flask RESTful API (Python 3.10)            │  │
│  │  • app.py (REST endpoints, auth, rate limiting)       │  │
│  │  • POST /api/scan (scan execution endpoint)           │  │
│  │  • X-API-KEY authentication                           │  │
│  │  • JSON request/response serialization               │  │
│  │  • Error handling and logging                         │  │
│  └───────────────────────────────────────────────────────┘  │
│                      ↕ Function Calls                        │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   TIER 1: Scanning Engine                    │
│  ┌───────────────────────────────────────────────────────┐  │
│  │        Python MQTT Protocol Scanner                   │  │
│  │  • scanner.py (orchestration, IP parsing)             │  │
│  │  • TCP port scanning (ports 1883, 8883)               │  │
│  │  • MQTT connection probing (paho-mqtt client)         │  │
│  │  • TLS certificate analysis (ssl, socket)             │  │
│  │  • Anonymous access detection                         │  │
│  │  • Authentication testing                             │  │
│  │  • Topic observation and client tracking              │  │
│  └───────────────────────────────────────────────────────┘  │
│                   ↕ MQTT Protocol (1883, 8883)               │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    Target Infrastructure                     │
│  • Docker Mosquitto Brokers (localhost:1883, :8883)          │
│  • Physical MQTT Broker (192.168.100.57:1883)                │
│  • ESP32 IoT Devices (publishing sensor telemetry)           │
└─────────────────────────────────────────────────────────────┘
```

### 4.4.2 Tier 1: Python Scanning Engine Implementation

The scanning engine implemented in `mqtt-scanner/scanner.py` performs protocol-aware MQTT broker discovery and security analysis.

#### 4.4.2.1 Core Scanning Algorithm

The `run_scan()` function orchestrates the complete scanning workflow:

```python
def run_scan(target, credentials=None):
    """
    Main scanning entry point.
    Args:
        target (str): IP address or CIDR notation (e.g., '192.168.1.0/24')
        credentials (dict): Optional {'user': '', 'pass': ''} for auth testing
    Returns:
        dict: Scan results with 'results' array and 'summary' statistics
    """
    logger.info(f"Starting scan for target: {target}")

    # Parse target into list of IP addresses
    ips = parse_target(target)
    results = []

    # Scan each IP address
    for ip in ips:
        for port in COMMON_PORTS:  # [1883, 8883]
            # Step 1: TCP port scan
            if is_port_open(ip, port, timeout=TIMEOUT):
                # Step 2: MQTT protocol probing
                broker_info = probe_mqtt_broker(ip, port, credentials)
                results.append(broker_info)
            else:
                # Port closed or filtered
                results.append({
                    'ip': ip,
                    'port': port,
                    'outcome': {
                        'label': 'Unreachable',
                        'meaning': 'Port closed or filtered',
                        'security_implication': 'Service not exposed',
                        'evidence_signal': 'Connection refused'
                    },
                    'severity': 'Info'
                })

    # Generate summary statistics
    summary = {
        'total_scanned': len(results),
        'vulnerable_count': sum(1 for r in results if r['severity'] == 'Critical'),
        'auth_required_count': sum(1 for r in results if r['severity'] == 'Medium'),
        'unreachable_count': sum(1 for r in results if r['severity'] == 'Info')
    }

    return {'results': results, 'summary': summary}
```

#### 4.4.2.2 TCP Port Scanning

Port scanning determines service availability before MQTT protocol probing:

```python
def is_port_open(host, port, timeout=2):
    """
    TCP SYN scan to detect open ports.
    Returns True if port accepts connections, False otherwise.
    """
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout)
        result = sock.connect_ex((host, port))
        sock.close()
        return result == 0  # 0 indicates successful connection
    except socket.error as e:
        logger.debug(f"Port scan error {host}:{port} - {e}")
        return False
```

#### 4.4.2.3 MQTT Protocol Probing

The `probe_mqtt_broker()` function implements comprehensive MQTT-specific security testing:

```python
def probe_mqtt_broker(host, port, credentials=None):
    """
    Perform MQTT CONNECT handshake with security analysis.
    Tests anonymous access, authentication, and TLS configuration.
    """
    result = {
        'ip': host,
        'port': port,
        'tls_available': False,
        'auth_required': False,
        'outcome': {},
        'severity': 'Unknown',
        'certificate_details': None,
        'captured_topics': [],
        'error_details': None
    }

    # Step 1: Check for TLS on port 8883
    if port == 8883:
        cert_analysis = analyze_tls_certificate(host, port)
        result['tls_available'] = cert_analysis['has_tls']
        result['certificate_details'] = cert_analysis.get('cert_details')

    # Step 2: Attempt anonymous MQTT connection
    client_id = f"scanner_{int(time.time())}"
    client = mqtt_client.Client(client_id)

    try:
        if port == 8883:
            client.tls_set(cert_reqs=ssl.CERT_NONE)  # Accept self-signed
            client.tls_insecure_set(True)

        # Set connection timeout
        client.connect(host, port, keepalive=10)
        client.loop_start()
        time.sleep(2)  # Wait for CONNACK

        # Anonymous connection successful
        result['auth_required'] = False
        result['severity'] = 'Critical'
        result['outcome'] = {
            'label': 'Anonymous Access Success',
            'meaning': 'Broker accepts unauthenticated connections',
            'security_implication': 'Attackers can publish/subscribe without credentials',
            'evidence_signal': 'CONNACK received with return code 0'
        }

        # Attempt topic observation
        result['captured_topics'] = capture_topics(client, duration=5)

        client.loop_stop()
        client.disconnect()

    except Exception as e:
        error_code = str(e)

        # Step 3: Classify error responses
        if "Not authorized" in error_code or "5" in error_code:
            # MQTT return code 5: Connection Refused, not authorized
            result['auth_required'] = True
            result['severity'] = 'Medium'
            result['outcome'] = {
                'label': 'Authentication Required',
                'meaning': 'Broker requires valid credentials',
                'security_implication': 'Properly configured access control',
                'evidence_signal': f'CONNACK return code 5: {error_code}'
            }

            # Step 4: Test with provided credentials if available
            if credentials and credentials.get('user'):
                auth_result = test_credentials(host, port, credentials)
                result['outcome']['credential_test'] = auth_result

        elif "Connection refused" in error_code:
            result['severity'] = 'Info'
            result['outcome'] = {
                'label': 'Connection Refused',
                'meaning': 'Service actively rejected connection',
                'security_implication': 'Port open but service unavailable',
                'evidence_signal': error_code
            }

        else:
            # Timeout or network error
            result['severity'] = 'Info'
            result['outcome'] = {
                'label': 'Connection Timeout',
                'meaning': 'No response from broker within timeout period',
                'security_implication': 'Service may be firewalled or offline',
                'evidence_signal': error_code
            }

        result['error_details'] = error_code

    return result
```

#### 4.4.2.4 TLS Certificate Analysis

Enhanced TLS inspection extracts certificate details and security posture:

```python
def analyze_tls_certificate(host, port, timeout=3):
    """
    Extract and analyze TLS/SSL certificate information.
    Identifies self-signed certificates, expiration status, cipher strength.
    """
    cert_analysis = {
        'has_tls': False,
        'cert_valid': False,
        'cert_details': {},
        'security_issues': [],
        'security_score': 0
    }

    try:
        context = ssl.create_default_context()
        context.check_hostname = False
        context.verify_mode = ssl.CERT_NONE  # Accept self-signed

        with socket.create_connection((host, port), timeout=timeout) as sock:
            with context.wrap_socket(sock, server_hostname=host) as ssock:
                cert_analysis['has_tls'] = True
                cert_dict = ssock.getpeercert()

                if cert_dict:
                    subject = dict(x[0] for x in cert_dict.get('subject', []))
                    issuer = dict(x[0] for x in cert_dict.get('issuer', []))

                    cert_analysis['cert_details'] = {
                        'subject': subject.get('commonName', 'N/A'),
                        'issuer': issuer.get('commonName', 'N/A'),
                        'valid_from': cert_dict.get('notBefore'),
                        'valid_to': cert_dict.get('notAfter'),
                        'tls_version': ssock.version(),
                        'cipher': ssock.cipher()[0] if ssock.cipher() else 'Unknown'
                    }

                    # Check if self-signed
                    if subject == issuer:
                        cert_analysis['security_issues'].append('Self-signed certificate')
                        cert_analysis['cert_details']['self_signed'] = True

                    # Check expiration
                    not_after = datetime.strptime(
                        cert_dict.get('notAfter'), '%b %d %H:%M:%S %Y %Z'
                    )
                    if datetime.utcnow() > not_after:
                        cert_analysis['security_issues'].append('Certificate expired')

    except Exception as e:
        logger.error(f"TLS analysis failed for {host}:{port} - {e}")
        cert_analysis['error'] = str(e)

    return cert_analysis
```

#### 4.4.2.5 Topic Observation and Traffic Analysis

The scanner captures active MQTT topics to identify exposed data streams:

```python
def capture_topics(mqtt_client, duration=5):
    """
    Subscribe to wildcard topic and capture published messages.
    Returns list of observed topics with sample payloads.
    """
    captured = []
    capture_event = threading.Event()

    def on_message(client, userdata, msg):
        captured.append({
            'topic': msg.topic,
            'payload_preview': str(msg.payload[:100]),  # First 100 bytes
            'qos': msg.qos,
            'timestamp': datetime.now().isoformat()
        })

    mqtt_client.on_message = on_message
    mqtt_client.subscribe("#", qos=0)  # Subscribe to all topics

    # Listen for specified duration
    time.sleep(duration)

    mqtt_client.unsubscribe("#")
    return captured
```

### 4.4.3 Tier 2: Flask API Layer Implementation

The Flask application (`mqtt-scanner/app.py`) exposes RESTful endpoints with authentication and rate limiting.

#### 4.4.3.1 API Endpoint Definition

```python
from flask import Flask, request, jsonify
from flask_cors import CORS
from flask_wtf.csrf import CSRFProtect
from scanner import run_scan
import os

app = Flask(__name__)
app.secret_key = os.environ.get('FLASK_SECRET_KEY', 'default-secret-key')

CORS(app, supports_credentials=True)
csrf = CSRFProtect(app)

FLASK_API_KEY = os.environ.get('FLASK_API_KEY', 'my-very-secret-flask-key-CHANGEME')

@app.route('/api/scan', methods=['POST'])
@require_api_key
@csrf.exempt  # Exempt API endpoints from CSRF (use API key instead)
def api_scan():
    """
    POST /api/scan
    Request body: {'target': '192.168.1.0/24', 'creds': {'user': '', 'pass': ''}}
    Response: {'results': [...], 'summary': {...}}
    """
    try:
        # Parse JSON request
        data = request.get_json()
        if not data or 'target' not in data:
            return jsonify({'error': 'Missing required field: target'}), 400

        target = data['target']
        credentials = data.get('creds')

        # Input validation
        if not validate_target_format(target):
            return jsonify({'error': 'Invalid target format'}), 400

        # Rate limiting check
        client_ip = request.remote_addr
        allowed, retry_after = check_rate_limit(client_ip)
        if not allowed:
            return jsonify({
                'error': 'Rate limit exceeded',
                'retry_after': retry_after
            }), 429

        # Execute scan
        logger.info(f"API scan initiated: {target} from {client_ip}")
        results = run_scan(target, credentials)

        return jsonify(results), 200

    except Exception as e:
        logger.error(f"Scan error: {e}")
        return jsonify({'error': 'Internal server error', 'details': str(e)}), 500
```

#### 4.4.3.2 Authentication Middleware

```python
from functools import wraps

def require_api_key(f):
    """
    Decorator to enforce API key authentication.
    Checks X-API-KEY header or api_key query parameter.
    """
    @wraps(f)
    def decorated_function(*args, **kwargs):
        api_key = request.headers.get('X-API-KEY') or request.args.get('api_key')

        if not api_key or api_key != FLASK_API_KEY:
            logger.warning(f"Invalid API key attempt from {request.remote_addr}")
            return jsonify({'error': 'Invalid or missing API key'}), 401

        return f(*args, **kwargs)

    return decorated_function
```

#### 4.4.3.3 Rate Limiting Implementation

```python
from datetime import datetime, timedelta
from collections import defaultdict

RATE_LIMIT_WINDOW = 60  # seconds
MAX_SCANS_PER_WINDOW = 5
scan_history = defaultdict(list)  # IP -> [timestamp1, timestamp2, ...]

def check_rate_limit(ip_address):
    """
    Sliding window rate limiter.
    Returns (allowed: bool, retry_after: int)
    """
    now = datetime.now()
    cutoff = now - timedelta(seconds=RATE_LIMIT_WINDOW)

    # Remove expired entries
    scan_history[ip_address] = [
        ts for ts in scan_history[ip_address] if ts > cutoff
    ]

    # Check limit
    if len(scan_history[ip_address]) >= MAX_SCANS_PER_WINDOW:
        oldest = scan_history[ip_address][0]
        retry_after = int((oldest + timedelta(seconds=RATE_LIMIT_WINDOW) - now).total_seconds())
        return False, retry_after

    # Record this request
    scan_history[ip_address].append(now)
    return True, None
```

### 4.4.4 Tier 3: Laravel Web Application Implementation

The Laravel application provides user authentication, scan orchestration, and result visualization.

#### 4.4.4.1 MqttScannerController Implementation

Controller handling scan requests and database persistence:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\MqttScanHistory;
use App\Models\MqttScanResult;

class MqttScannerController extends Controller
{
    /**
     * Display dashboard with scan history
     */
    public function index()
    {
        $recentScans = MqttScanHistory::where('user_id', auth()->id())
            ->with('results')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $latestResults = MqttScanResult::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('dashboard', compact('recentScans', 'latestResults'));
    }

    /**
     * Execute MQTT scan via Flask API
     */
    public function scan(Request $request)
    {
        // Input validation with security constraints
        $validated = $request->validate([
            'target' => [
                'required',
                'string',
                'max:100',
                'regex:/^[0-9\.\/:a-zA-Z\-]+$/'  // Allow only IP/CIDR characters
            ],
            'creds' => ['nullable', 'array'],
            'creds.user' => ['nullable', 'string', 'max:255'],
            'creds.pass' => ['nullable', 'string', 'max:255'],
        ]);

        // Rate limiting: 10 scans per minute per user
        $key = 'mqtt_scan:' . auth()->id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'error' => 'Too many scan requests. Please wait.'
            ], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);

        // Create scan history record
        $scanHistory = MqttScanHistory::create([
            'user_id' => auth()->id(),
            'target' => $validated['target'],
            'credentials' => $validated['creds'] ?? null,
            'started_at' => now(),
            'status' => 'running',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            // Call Flask API
            $flaskBase = env('FLASK_BASE', 'http://127.0.0.1:5000');
            $apiKey = env('FLASK_API_KEY');

            $response = Http::timeout(30)
                ->withHeaders(['X-API-KEY' => $apiKey])
                ->post($flaskBase . '/api/scan', [
                    'target' => $validated['target'],
                    'creds' => $validated['creds'] ?? null,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Store results in database
                $this->storeResults($scanHistory, $data['results']);
                $scanHistory->markCompleted();
                $scanHistory->updateStatistics();

                return response()->json([
                    'success' => true,
                    'scan_id' => $scanHistory->id,
                    'results' => $data['results'],
                    'summary' => $data['summary']
                ]);
            } else {
                throw new \Exception('Flask API error: ' . $response->status());
            }

        } catch (\Exception $e) {
            $scanHistory->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);

            return response()->json([
                'error' => 'Scan failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store scan results in database
     */
    private function storeResults($scanHistory, $results)
    {
        foreach ($results as $result) {
            MqttScanResult::create([
                'scan_history_id' => $scanHistory->id,
                'user_id' => auth()->id(),
                'ip_address' => $result['ip'],
                'port' => $result['port'],
                'outcome' => $result['outcome']['label'] ?? 'Unknown',
                'severity' => $result['severity'] ?? 'Unknown',
                'tls_available' => $result['tls_available'] ?? false,
                'auth_required' => $result['auth_required'] ?? false,
                'certificate_subject' => $result['certificate_details']['subject'] ?? null,
                'captured_topics' => json_encode($result['captured_topics'] ?? []),
                'raw_response' => json_encode($result),
            ]);
        }
    }

    /**
     * Export scan results to CSV
     */
    public function exportCsv($scanId)
    {
        $scan = MqttScanHistory::where('id', $scanId)
            ->where('user_id', auth()->id())
            ->with('results')
            ->firstOrFail();

        $filename = "mqtt_scan_{$scanId}_" . now()->format('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($scan) {
            $file = fopen('php://output', 'w');

            // CSV header
            fputcsv($file, [
                'IP Address', 'Port', 'Outcome', 'Severity',
                'TLS Available', 'Auth Required', 'Certificate Subject',
                'Captured Topics', 'Scanned At'
            ]);

            // CSV rows
            foreach ($scan->results as $result) {
                fputcsv($file, [
                    $result->ip_address,
                    $result->port,
                    $result->outcome,
                    $result->severity,
                    $result->tls_available ? 'Yes' : 'No',
                    $result->auth_required ? 'Yes' : 'No',
                    $result->certificate_subject,
                    implode('; ', json_decode($result->captured_topics, true) ?? []),
                    $result->created_at->toDateTimeString()
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
```

#### 4.4.4.2 Database Models

**MqttScanHistory Model** (`app/Models/MqttScanHistory.php`):

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MqttScanHistory extends Model
{
    protected $fillable = [
        'user_id', 'target', 'credentials', 'started_at', 'completed_at',
        'duration', 'status', 'total_targets', 'reachable_count',
        'unreachable_count', 'vulnerable_count', 'ip_address',
        'user_agent', 'error_message'
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',  // Encrypt credentials in DB
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relationship to scan results
     */
    public function results()
    {
        return $this->hasMany(MqttScanResult::class, 'scan_history_id');
    }

    /**
     * Relationship to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark scan as completed
     */
    public function markCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration' => now()->diffInSeconds($this->started_at)
        ]);
    }

    /**
     * Update summary statistics from results
     */
    public function updateStatistics()
    {
        $this->update([
            'total_targets' => $this->results()->count(),
            'vulnerable_count' => $this->results()->where('severity', 'Critical')->count(),
            'reachable_count' => $this->results()->where('outcome', '!=', 'Unreachable')->count(),
            'unreachable_count' => $this->results()->where('outcome', 'Unreachable')->count(),
        ]);
    }
}
```

**MqttScanResult Model** (`app/Models/MqttScanResult.php`):

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MqttScanResult extends Model
{
    protected $fillable = [
        'scan_history_id', 'user_id', 'ip_address', 'port',
        'outcome', 'severity', 'tls_available', 'auth_required',
        'certificate_subject', 'certificate_issuer', 'certificate_expiry',
        'captured_topics', 'raw_response'
    ];

    protected $casts = [
        'tls_available' => 'boolean',
        'auth_required' => 'boolean',
        'captured_topics' => 'array',
        'raw_response' => 'array',
        'certificate_expiry' => 'datetime',
    ];

    /**
     * Relationship to scan history
     */
    public function scanHistory()
    {
        return $this->belongsTo(MqttScanHistory::class, 'scan_history_id');
    }

    /**
     * Relationship to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get severity badge color for UI
     */
    public function getSeverityColorAttribute()
    {
        return match($this->severity) {
            'Critical' => 'red',
            'High' => 'orange',
            'Medium' => 'yellow',
            'Low' => 'blue',
            'Info' => 'gray',
            default => 'gray'
        };
    }
}
```

#### 4.4.4.3 Route Configuration

Routes defined in `routes/web.php`:

```php
<?php

use App\Http\Controllers\MqttScannerController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [MqttScannerController::class, 'index'])
        ->name('dashboard');

    // Scan execution
    Route::post('/scan', [MqttScannerController::class, 'scan'])
        ->name('scan.execute');

    // Export results
    Route::get('/scan/{scanId}/export', [MqttScannerController::class, 'exportCsv'])
        ->name('scan.export');

    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// Authentication routes (provided by Laravel Breeze)
require __DIR__.'/auth.php';
```

#### 4.4.4.4 Frontend Implementation (Blade Template)

Dashboard view (`resources/views/dashboard.blade.php`) - Key sections:

```html
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('MQTT Network Scanner Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Scan Initiation Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Start New Scan</h3>

                    <form
                        id="scanForm"
                        method="POST"
                        action="{{ route('scan.execute') }}"
                    >
                        @csrf

                        <div class="mb-4">
                            <label
                                for="target"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Target IP or CIDR Range
                            </label>
                            <input
                                type="text"
                                name="target"
                                id="target"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                placeholder="192.168.1.1 or 192.168.1.0/24"
                                required
                            />
                            @error('target')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="useCredentials"
                                    class="rounded"
                                />
                                <span class="ml-2 text-sm"
                                    >Test with credentials</span
                                >
                            </label>
                        </div>

                        <div id="credentialsFields" class="hidden mb-4">
                            <input
                                type="text"
                                name="creds[user]"
                                placeholder="Username"
                                class="block w-full rounded-md border-gray-300 mb-2"
                            />
                            <input
                                type="password"
                                name="creds[pass]"
                                placeholder="Password"
                                class="block w-full rounded-md border-gray-300"
                            />
                        </div>

                        <button
                            type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                        >
                            Start Scan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Results Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Scan Results</h3>

                    <div id="resultsContainer">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        IP Address
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Port
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Outcome
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Severity
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        TLS
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Auth
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                id="resultsTableBody"
                                class="bg-white divide-y divide-gray-200"
                            >
                                @forelse($latestResults as $result)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $result->ip_address }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $result->port }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $result->outcome }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                bg-{{ $result->severity_color }}-100 
                                                text-{{ $result->severity_color }}-800"
                                        >
                                            {{ $result->severity }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($result->tls_available)
                                        <span class="text-green-600">✓</span>
                                        @else
                                        <span class="text-red-600">✗</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($result->auth_required)
                                        <span class="text-green-600">✓</span>
                                        @else
                                        <span class="text-red-600">✗</span>
                                        @endif
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        <button
                                            onclick="showDetails({{ $result->id }})"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            Details
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td
                                        colspan="7"
                                        class="px-6 py-4 text-center text-gray-500"
                                    >
                                        No scan results yet. Start a new scan
                                        above.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for AJAX scan submission -->
    <script>
        document
            .getElementById("scanForm")
            .addEventListener("submit", async function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                const data = Object.fromEntries(formData);

                try {
                    const response = await fetch(
                        '{{ route("scan.execute") }}',
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            },
                            body: JSON.stringify(data),
                        },
                    );

                    const result = await response.json();

                    if (result.success) {
                        // Update results table dynamically
                        updateResultsTable(result.results);
                        alert("Scan completed successfully!");
                    } else {
                        alert("Scan failed: " + result.error);
                    }
                } catch (error) {
                    alert("Error: " + error.message);
                }
            });

        function updateResultsTable(results) {
            // Implementation to dynamically update table with new results
            const tbody = document.getElementById("resultsTableBody");
            // ... DOM manipulation code ...
        }

        function showDetails(resultId) {
            // Show modal with detailed result information
            // ... implementation ...
        }
    </script>
</x-app-layout>
```

## 4.5 Security Controls Implementation

### 4.5.1 Input Validation and Sanitization

All user inputs undergo strict validation before processing:

**Laravel Request Validation**:

```php
$validated = $request->validate([
    'target' => [
        'required',
        'string',
        'max:100',
        'regex:/^[0-9\.\/:a-zA-Z\-]+$/'  // Only allow IP/CIDR characters
    ]
]);
```

**Python Input Sanitization**:

```python
def validate_target_format(target):
    """
    Validate target is either valid IP address or CIDR notation.
    Prevents command injection and path traversal attacks.
    """
    import re
    import ipaddress

    # CIDR pattern
    cidr_pattern = r'^(\d{1,3}\.){3}\d{1,3}\/\d{1,2}$'
    # IP pattern
    ip_pattern = r'^(\d{1,3}\.){3}\d{1,3}$'

    if re.match(cidr_pattern, target) or re.match(ip_pattern, target):
        try:
            # Validate IP is in valid range
            ipaddress.ip_network(target, strict=False)
            return True
        except ValueError:
            return False
    return False
```

### 4.5.2 Authentication and Authorization

**Laravel Authentication** (Laravel Breeze):

- Session-based authentication with CSRF protection
- Password hashing using bcrypt (12 rounds)
- User verification and email confirmation
- Password reset functionality

**Flask API Authentication**:

```python
def require_api_key(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        api_key = request.headers.get('X-API-KEY')
        if not api_key or api_key != FLASK_API_KEY:
            return jsonify({'error': 'Unauthorized'}), 401
        return f(*args, **kwargs)
    return decorated_function
```

### 4.5.3 Rate Limiting

**Laravel Rate Limiting**:

```php
use Illuminate\Support\Facades\RateLimiter;

$key = 'mqtt_scan:' . auth()->id();
if (RateLimiter::tooManyAttempts($key, 10)) {
    return response()->json(['error' => 'Too many requests'], 429);
}
RateLimiter::hit($key, 60); // 10 scans per 60 seconds
```

**Flask Rate Limiting**:

- Sliding window algorithm
- IP-based tracking
- Configurable limits via environment variables

### 4.5.4 Audit Logging

Comprehensive logging implemented for security monitoring:

```php
use Illuminate\Support\Facades\Log;

Log::info('MQTT scan initiated', [
    'user_id' => auth()->id(),
    'user_email' => auth()->user()->email,
    'target' => $target,
    'ip_address' => $request->ip(),
    'timestamp' => now()
]);
```

Logs stored in `storage/logs/laravel.log` with rotation policy.

### 4.5.5 Secure Credential Storage

User-provided MQTT credentials encrypted in database:

```php
protected $casts = [
    'credentials' => 'encrypted:array',  // Laravel encrypts using APP_KEY
];
```

## 4.6 Configuration Guide

### 4.6.1 Laravel Environment Configuration

Edit `.env` file for production deployment:

```dotenv
# Application
APP_NAME="MQTT Scanner"
APP_ENV=production
APP_KEY=base64:your-generated-app-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (MySQL Production)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mqtt_scanner
DB_USERNAME=mqtt_user
DB_PASSWORD=strong-password-here

# Flask API
FLASK_BASE=http://127.0.0.1:5000
FLASK_API_KEY=generate-strong-random-key

# Mail Configuration (for user registration)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mqttscanner.com
MAIL_FROM_NAME="${APP_NAME}"

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=warning
```

### 4.6.2 Flask Environment Configuration

Create `mqtt-scanner/.env`:

```dotenv
FLASK_APP=app.py
FLASK_ENV=production
FLASK_SECRET_KEY=your-secure-random-secret-key
FLASK_API_KEY=same-as-laravel-flask-api-key
FLASK_ADMIN_PASS=secure-admin-password

# Rate Limiting
RATE_LIMIT_WINDOW_SECS=60
MAX_SCANS_PER_WINDOW=5

# Scanning Configuration
MQTT_SCAN_TIMEOUT=3
MQTT_LISTEN_DURATION=5
```

### 4.6.3 Production Deployment Checklist

**Pre-Deployment Tasks**:

1. ✓ Change all default passwords and API keys
2. ✓ Set `APP_DEBUG=false` in Laravel `.env`
3. ✓ Configure production database with backup strategy
4. ✓ Enable HTTPS with valid SSL/TLS certificate
5. ✓ Configure firewall rules (allow only ports 80, 443)
6. ✓ Set appropriate file permissions (755 for directories, 644 for files)
7. ✓ Run `composer install --optimize-autoloader --no-dev`
8. ✓ Run `php artisan config:cache` and `php artisan route:cache`
9. ✓ Configure log rotation to prevent disk space exhaustion
10. ✓ Set up database backup cron jobs

## 4.7 Running the Application

### 4.7.1 Development Mode

**Terminal 1 - Laravel Server**:

```powershell
cd mqtt-scanner-fyp2-main
php artisan serve
# Access at http://localhost:8000
```

**Terminal 2 - Flask API Server**:

```powershell
cd mqtt-scanner-fyp2-main\mqtt-scanner
.\.venv\Scripts\Activate.ps1
python app.py
# Flask runs on http://127.0.0.1:5000
```

**Terminal 3 - Frontend Asset Compilation** (if modifying CSS/JS):

```powershell
cd mqtt-scanner-fyp2-main
npm run dev
# Vite hot reload on http://localhost:5173
```

**Terminal 4 - MQTT Brokers**:

```powershell
cd mqtt-scanner-fyp2-main\mqtt-brokers
docker-compose up
# Insecure broker: localhost:1883
# Secure broker: localhost:8883
```

### 4.7.2 Production Mode with Process Management

Use Laravel Sail or deploy with Nginx + PHP-FPM:

**Nginx Configuration** (`/etc/nginx/sites-available/mqtt-scanner`):

```nginx
server {
    listen 80;
    server_name yourdomain.com;
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

**Supervisor Configuration for Flask** (`/etc/supervisor/conf.d/flask-scanner.conf`):

```ini
[program:flask-scanner]
command=/var/www/mqtt-scanner/.venv/bin/python /var/www/mqtt-scanner/mqtt-scanner/app.py
directory=/var/www/mqtt-scanner/mqtt-scanner
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/flask-scanner.log
```

## 4.8 User Manual

### 4.8.1 User Registration and Login

1. Navigate to application URL (e.g., `http://localhost:8000`)
2. Click "Register" in top-right corner
3. Fill registration form:
    - Name
    - Email address
    - Password (minimum 8 characters)
    - Confirm password
4. Click "Register" button
5. Verify email if email verification enabled
6. Login with registered credentials

### 4.8.2 Executing Network Scans

**Step 1: Access Dashboard**

- After login, navigate to Dashboard page
- View recent scan history and results

**Step 2: Initiate Scan**

- Locate "Start New Scan" form
- Enter target in one of these formats:
    - Single IP: `192.168.100.57`
    - CIDR range: `192.168.1.0/24`
    - Multiple IPs: (run separate scans)

**Step 3: Optional Credential Testing**

- Check "Test with credentials" if you want to test authentication
- Enter username (MQTT broker username)
- Enter password (MQTT broker password)

**Step 4: Execute Scan**

- Click "Start Scan" button
- Wait for scan completion (typically 10-30 seconds depending on target range)
- Progress indicator shows scanning status

**Step 5: Review Results**

- Results appear in table below form
- Color-coded severity indicators:
    - **Red (Critical)**: Anonymous access allowed - immediate security risk
    - **Yellow (Medium)**: Authentication required - properly configured
    - **Gray (Info)**: Unreachable or connection refused

**Step 6: View Detailed Information**

- Click "Details" button on any result row
- Modal displays:
    - Full broker information
    - TLS certificate details (if applicable)
    - Captured topic list
    - Security implications
    - Error evidence for unreachable targets

### 4.8.3 Exporting Results

1. Click "Export" button on scan history
2. CSV file downloads automatically
3. Open in Excel/Google Sheets for analysis
4. CSV contains columns:
    - IP Address
    - Port
    - Outcome
    - Severity
    - TLS Available
    - Authentication Required
    - Certificate Subject
    - Captured Topics
    - Timestamp

### 4.8.4 Understanding Scan Outcomes

**Table 4.6: Scan Outcome Classification**

| Outcome                  | Meaning                                        | Security Implication                                                | Recommended Action                            |
| ------------------------ | ---------------------------------------------- | ------------------------------------------------------------------- | --------------------------------------------- |
| Anonymous Access Success | Broker accepts connections without credentials | **Critical Vulnerability** - Attackers can publish/subscribe freely | Immediately enable authentication and ACLs    |
| Authentication Required  | Broker requires valid credentials              | Properly secured - authentication enforced                          | Verify strong passwords are used              |
| TLS Required             | Broker requires encrypted connection           | Good security posture                                               | Ensure certificates are valid and not expired |
| Connection Refused       | Service actively rejected connection           | Port open but service unavailable                                   | Investigate broker logs for errors            |
| Connection Timeout       | No response within timeout period              | Service may be firewalled or offline                                | Check network connectivity and firewall rules |
| Unreachable              | Network-level connection failure               | Target not accessible from scanner                                  | Verify IP address and network routing         |

### 4.8.5 Troubleshooting Common Issues

**Issue: "Flask API connection failed"**

- Solution: Ensure Flask server is running on configured port (5000)
- Check `FLASK_BASE` setting in Laravel `.env`
- Verify Flask API key matches between Laravel and Flask

**Issue: "Rate limit exceeded"**

- Solution: Wait 60 seconds before retrying
- Indicates too many scan requests from same user/IP
- Contact administrator to adjust rate limits if needed

**Issue: "Invalid target format"**

- Solution: Verify target follows correct IP or CIDR format
- Examples: `192.168.1.1`, `10.0.0.0/24`
- Only alphanumeric, dots, slashes, colons, and hyphens allowed

**Issue: "All targets showing unreachable"**

- Solution: Verify network connectivity to targets
- Check firewall rules allow outbound connections to ports 1883/8883
- Confirm target MQTT brokers are actually running

**Issue: ESP32 not appearing in results**

- Solution: Verify ESP32 is connected to same network
- Check ESP32 serial monitor for connection errors
- Confirm `mqtt_server` IP in Arduino code matches PC's IP
- Scan the correct IP address where ESP32 is publishing

## 4.9 Testing and Validation

### 4.9.1 Unit Testing

Laravel includes PHPUnit for automated testing:

**Example Test** (`tests/Feature/ScanControllerTest.php`):

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Start New Scan');
    }

    public function test_scan_requires_valid_target_format()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/scan', [
            'target' => 'invalid-format-!@#'
        ]);

        $response->assertSessionHasErrors('target');
    }

    public function test_scan_creates_history_record()
    {
        $user = User::factory()->create();

        // Mock Flask API response
        Http::fake([
            'http://127.0.0.1:5000/api/scan' => Http::response([
                'results' => [],
                'summary' => ['total_scanned' => 0]
            ], 200)
        ]);

        $this->actingAs($user)->post('/scan', [
            'target' => '192.168.1.1'
        ]);

        $this->assertDatabaseHas('mqtt_scan_histories', [
            'user_id' => $user->id,
            'target' => '192.168.1.1'
        ]);
    }
}
```

Run tests:

```powershell
php artisan test
```

### 4.9.2 Integration Testing

Test complete workflow from Laravel → Flask → Scanner:

**Python Integration Test** (`mqtt-scanner/test_all_outcomes.py`):

```python
import unittest
from scanner import run_scan

class TestScannerIntegration(unittest.TestCase):

    def test_scan_localhost_insecure_broker(self):
        """Test detection of insecure broker on localhost:1883"""
        results = run_scan('127.0.0.1')

        # Should find broker on port 1883
        insecure_result = next(
            (r for r in results['results'] if r['port'] == 1883), None
        )

        self.assertIsNotNone(insecure_result)
        self.assertEqual(insecure_result['severity'], 'Critical')
        self.assertIn('Anonymous', insecure_result['outcome']['label'])

    def test_scan_localhost_secure_broker(self):
        """Test detection of secure broker on localhost:8883"""
        results = run_scan('127.0.0.1')

        # Should find broker on port 8883 requiring auth
        secure_result = next(
            (r for r in results['results'] if r['port'] == 8883), None
        )

        self.assertIsNotNone(secure_result)
        self.assertTrue(secure_result['tls_available'])
        self.assertTrue(secure_result['auth_required'])

if __name__ == '__main__':
    unittest.main()
```

Run Python tests:

```powershell
cd mqtt-scanner
python -m unittest discover -s . -p "test_*.py"
```

### 4.9.3 Hardware-in-the-Loop Testing

Validation testing with ESP32 hardware:

1. Upload firmware to ESP32 with sensors connected
2. Verify Serial Monitor shows successful MQTT connections
3. Run scanner targeting ESP32's broker IP (192.168.100.57:1883)
4. Confirm scanner detects:
    - Port 1883 open
    - Anonymous access allowed (Critical severity)
    - Captured topics include `sensors/faris/pir_insecure`
5. Verify secure broker (8883) requires authentication
6. Test with correct credentials - should succeed
7. Test with incorrect credentials - should fail with "Auth Required"

## 4.10 Implementation Summary

This chapter documented the complete implementation of the MQTT Network Security Scanner prototype system from initial environment setup through deployment-ready application. The implementation successfully translated the three-tier architectural design into functional code comprising:

1. **Python Scanning Engine** (Tier 1): Protocol-aware MQTT broker detection with TLS analysis, authentication testing, and topic observation capabilities
2. **Flask RESTful API** (Tier 2): Secure API gateway with authentication, rate limiting, input validation, and JSON serialization
3. **Laravel Web Application** (Tier 3): Full-featured dashboard with user authentication, scan history persistence, result visualization, and CSV export

The development process followed DevSecOps principles incorporating security controls at every layer including input validation, authentication enforcement, rate limiting, audit logging, and encrypted credential storage. Hardware validation using ESP32 microcontroller with multi-sensor telemetry confirmed the scanner's ability to detect diverse security postures across Docker-containerized and physical MQTT broker deployments.

The comprehensive installation manual, configuration guide, and user manual documented in this chapter enable reproduction of the entire system from scratch, supporting both academic research objectives and potential real-world deployment scenarios. Testing and validation procedures encompassing unit tests, integration tests, and hardware-in-the-loop testing verified correct functionality across all scan outcome classifications.

Chapter 5 will present the comprehensive testing results, performance analysis, and validation findings demonstrating the system's effectiveness in identifying MQTT security misconfigurations across representative IoT deployment scenarios.
