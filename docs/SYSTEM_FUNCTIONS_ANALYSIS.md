# SYSTEM FUNCTIONS ANALYSIS

## MQTT Network Security Scanner - Bachelor FYP

**Document Purpose:** Extract and document all essential functions required to build the MQTT Network Security Scanner system end-to-end.

**Analysis Date:** January 21, 2026

---

## PART A: USER-FACING MAIN FUNCTIONS

These are functions directly accessible and visible to end-users (security analysts, network administrators) through the web interface.

### A.1 Register New User Account

**Purpose:** Allow new users to create an account to access the scanning platform.

**Input:**

- Full name (string, max 255 characters)
- Email address (string, valid email format)
- Password (string, minimum 8 characters)
- Password confirmation (string, must match password)

**Output:**

- Success: User account created, redirect to login page
- Failure: Validation error messages displayed

**Where It Happens:**

- **Laravel UI:** Registration form (`resources/views/auth/register.blade.php`)
- **Laravel Backend:** User creation logic (`app/Http/Controllers/Auth/RegisteredUserController.php`)
- **Database:** User record stored in `users` table

**Priority:** ESSENTIAL

---

### A.2 Login to System

**Purpose:** Authenticate existing users and establish secure session to access protected features.

**Input:**

- Email address (string)
- Password (string)
- "Remember Me" option (boolean, optional)

**Output:**

- Success: Authenticated session created, redirect to dashboard
- Failure: "Invalid credentials" error message

**Where It Happens:**

- **Laravel UI:** Login form (`resources/views/auth/login.blade.php`)
- **Laravel Backend:** Authentication logic (`app/Http/Controllers/Auth/AuthenticatedSessionController.php`)
- **Session Storage:** User session data stored server-side

**Priority:** ESSENTIAL

---

### A.3 Initiate MQTT Network Scan

**Purpose:** Start a new security scan targeting specified MQTT brokers or network ranges.

**Input:**

- Target specification (string):
    - Single IP address (e.g., `192.168.1.10`)
    - CIDR notation (e.g., `192.168.1.0/24`)
    - Hostname (e.g., `mqtt.example.com`)

**Output:**

- Success: Scan initiated, scan ID returned, real-time results displayed
- Failure: Error message (invalid target format, rate limit exceeded, API unavailable)

**Where It Happens:**

- **Laravel UI:** Scan form on dashboard (`resources/views/dashboard.blade.php`)
- **Laravel Backend:** Scan controller (`app/Http/Controllers/MqttScannerController.php` - `scan()` method)
- **Flask API:** Receives scan request (`mqtt-scanner/app.py` - `/api/scan` endpoint)
- **Python Scanner:** Executes scanning logic (`mqtt-scanner/scanner.py`)
- **Database:** Scan history created in `mqtt_scan_histories` table with status "running"

**Priority:** ESSENTIAL - Core function of the system

---

### A.4 View Real-Time Scan Results

**Purpose:** Display scan results as they are discovered during active scan execution.

**Input:**

- Scan ID (integer, from initiated scan)

**Output:**

- Real-time table showing discovered brokers:
    - IP address
    - Port number
    - Connection outcome (e.g., "Anonymous Access Allowed", "Authentication Required")
    - Severity level (Critical, High, Medium, Low, Info)
    - TLS status
    - Authentication requirement
- Summary statistics (total brokers found, vulnerability count)

**Where It Happens:**

- **Laravel UI:** Results table component (`resources/views/dashboard.blade.php`)
- **Laravel Backend:** JSON response from scan controller
- **Frontend JavaScript:** AJAX polling or real-time updates (`resources/js/app.js`)

**Priority:** ESSENTIAL

---

### A.5 View Scan History

**Purpose:** Allow users to review past scans they have conducted, including metadata and summary statistics.

**Input:**

- User authentication (implicit - current logged-in user)
- Optional pagination parameters (page number)

**Output:**

- Paginated list of historical scans showing:
    - Scan ID
    - Target specification
    - Execution timestamp
    - Status (completed, failed, running)
    - Total brokers found
    - Vulnerable broker count
    - Link to detailed results

**Where It Happens:**

- **Laravel UI:** History section on dashboard (`resources/views/dashboard.blade.php`)
- **Laravel Backend:** History retrieval (`app/Http/Controllers/MqttScannerController.php` - `history()` method)
- **Database:** Query `mqtt_scan_histories` table filtered by `user_id`

**Priority:** ESSENTIAL - Required for audit trail and compliance

---

### A.6 View Detailed Scan Results

**Purpose:** Display comprehensive details for a specific past scan including all discovered brokers and their security configurations.

**Input:**

- Scan ID (integer)

**Output:**

- Complete scan details:
    - Scan metadata (target, timestamp, duration)
    - Full list of discovered brokers with all assessment details
    - TLS certificate information (if applicable)
    - Connection error details
    - Severity distribution chart/table

**Where It Happens:**

- **Laravel UI:** Scan details page or modal
- **Laravel Backend:** Single scan retrieval (`app/Http/Controllers/MqttScannerController.php` - `show()` method)
- **Database:** Query `mqtt_scan_histories` with eager-loaded `mqtt_scan_results` relationship

**Priority:** ESSENTIAL

---

### A.7 Export Scan Results to CSV

**Purpose:** Generate downloadable CSV report of scan results for external analysis, reporting, or compliance documentation.

**Input:**

- Scan ID (integer)

**Output:**

- CSV file download containing:
    - Header row: IP Address, Port, Outcome, Severity, TLS Enabled, Auth Required, Details, Timestamp
    - Data rows: One row per discovered broker
- Filename format: `mqtt_scan_{scan_id}_{date}_{time}.csv`

**Where It Happens:**

- **Laravel UI:** Export button on results page
- **Laravel Backend:** CSV generation (`app/Http/Controllers/MqttScannerController.php` - `export()` method)
- **Browser:** File download initiated

**Priority:** ESSENTIAL - Required for reporting and documentation

---

### A.8 Logout from System

**Purpose:** Terminate user session and revoke authentication to protect account security.

**Input:**

- None (implicit - current user session)

**Output:**

- Session destroyed, redirect to login page
- Success message displayed

**Where It Happens:**

- **Laravel UI:** Logout link/button in navigation
- **Laravel Backend:** Session destruction (`app/Http/Controllers/Auth/AuthenticatedSessionController.php`)

**Priority:** ESSENTIAL - Security requirement

---

## PART B: SYSTEM CORE FUNCTIONS

These are technical functions required for the system to operate correctly, though not directly visible to users.

### B.1 Parse Target Specification

**Purpose:** Convert user-provided target input (IP address, CIDR notation, hostname) into a list of individual IP addresses to scan.

**Input:**

- Target string (e.g., `192.168.1.0/24`, `192.168.1.10`, `mqtt.example.com`)

**Output:**

- List of IP addresses (array of strings)
- Error if target format is invalid

**Where It Happens:**

- **Python Scanner:** `scanner.py` - `parse_target()` function
- **Library Used:** Python `ipaddress` module for CIDR expansion

**Priority:** ESSENTIAL - Required for scan execution

---

### B.2 Check TCP Port Availability

**Purpose:** Determine if standard MQTT ports (1883, 8883) are open and accepting connections on target IP addresses before attempting MQTT protocol handshake.

**Input:**

- IP address (string)
- Port number (integer: 1883 or 8883)
- Timeout (integer: 3 seconds default)

**Output:**

- Boolean: `True` if port is open, `False` if closed/filtered/timeout

**Where It Happens:**

- **Python Scanner:** `scanner.py` - `check_tcp_port()` function
- **Library Used:** Python `socket` module

**Priority:** ESSENTIAL - Optimization to avoid unnecessary MQTT connection attempts

---

### B.3 Probe MQTT Broker (Anonymous Access Test)

**Purpose:** Attempt to connect to discovered MQTT broker without authentication credentials to detect anonymous access vulnerabilities.

**Input:**

- IP address (string)
- Port number (integer)
- TLS configuration (boolean: enable for port 8883)

**Output:**

- Connection result dictionary:
    - `success`: Boolean (connection succeeded)
    - `outcome`: String (e.g., "Anonymous Access Allowed", "Connection Refused")
    - `severity`: String (Critical, High, Medium, Low, Info)
    - `error_code`: Integer (MQTT return code)
    - `error_message`: String (detailed error description)

**Where It Happens:**

- **Python Scanner:** `scanner.py` - `_test_connection()` with `anonymous=True`
- **Library Used:** `paho-mqtt` client library

**Priority:** ESSENTIAL - Primary vulnerability detection mechanism

---

### B.4 Probe MQTT Broker (Authentication Test)

**Purpose:** Attempt to connect to MQTT broker with test credentials to determine if authentication is enforced.

**Input:**

- IP address (string)
- Port number (integer)
- Username (string: test credential)
- Password (string: test credential)
- TLS configuration (boolean)

**Output:**

- Connection result dictionary (same structure as B.3)
- Authentication requirement status (boolean)

**Where It Happens:**

- **Python Scanner:** `scanner.py` - `_test_connection()` with `anonymous=False`
- **Library Used:** `paho-mqtt` client library

**Priority:** ESSENTIAL - Determines broker security posture

---

### B.5 Inspect TLS Certificate

**Purpose:** Extract and analyze TLS certificate details for secure MQTT connections (port 8883) to assess encryption quality.

**Input:**

- IP address (string)
- Port number (integer: 8883)

**Output:**

- TLS information dictionary:
    - `tls_version`: String (e.g., "TLSv1.2", "TLSv1.3")
    - `cert_issuer`: Dictionary (certificate authority details)
    - `cert_subject`: Dictionary (certificate owner details)
    - `cert_valid_from`: String (validity start date)
    - `cert_valid_to`: String (validity end date)
    - Error details if certificate invalid/expired

**Where It Happens:**

- **Python Scanner:** `scanner.py` - `_check_tls_certificate()` function
- **Library Used:** Python `ssl` module

**Priority:** ESSENTIAL - TLS security assessment

---

### B.6 Classify Vulnerability Severity

**Purpose:** Assign severity rating (Critical/High/Medium/Low/Info) to discovered broker based on security configuration analysis.

**Input:**

- Connection outcome (string)
- Authentication status (boolean)
- TLS status (boolean)
- Error codes (integer)

**Output:**

- Severity classification (string: Critical, High, Medium, Low, Info)
- Classification logic:
    - **Critical:** Anonymous access allowed (no authentication)
    - **High:** Weak authentication or TLS issues
    - **Medium:** Authentication required but other concerns
    - **Low:** Secured but minor issues detected
    - **Info:** Connection failed or broker unreachable

**Where It Happens:**

- **Python Scanner:** `scanner.py` - `probe_mqtt_broker()` function
- Embedded within scanning logic

**Priority:** ESSENTIAL - Risk assessment output

---

### B.7 Store Scan History Metadata

**Purpose:** Persist scan session information to database for audit trail, history tracking, and result association.

**Input:**

- User ID (integer - who initiated scan)
- Target specification (string)
- Scan start timestamp (datetime)
- Scan status (enum: running, completed, failed)
- Total brokers found (integer)
- Vulnerable broker count (integer)
- Scan completion timestamp (datetime)

**Output:**

- Scan history record created in database
- Scan ID (integer, primary key) returned for result association

**Where It Happens:**

- **Laravel Backend:** `app/Http/Controllers/MqttScannerController.php` - `scan()` method
- **Database:** `mqtt_scan_histories` table
- **ORM Model:** `app/Models/MqttScanHistory.php`

**Priority:** ESSENTIAL - Data persistence requirement

---

### B.8 Store Scan Results (Individual Brokers)

**Purpose:** Persist detailed findings for each discovered MQTT broker to database for historical analysis and reporting.

**Input:**

- Scan history ID (integer - foreign key)
- IP address (string)
- Port number (integer)
- Connection outcome (string)
- Severity (string)
- Details (text)
- TLS enabled status (boolean)
- Authentication required status (boolean)
- Timestamp (datetime)

**Output:**

- Scan result record created in database
- Result ID (integer, primary key)

**Where It Happens:**

- **Laravel Backend:** `app/Http/Controllers/MqttScannerController.php` - `storeResults()` helper method
- **Database:** `mqtt_scan_results` table
- **ORM Model:** `app/Models/MqttScanResult.php`

**Priority:** ESSENTIAL - Result persistence

---

### B.9 Authenticate Flask API Requests

**Purpose:** Verify that requests to Flask API originate from authorized Laravel application using API key authentication to prevent unauthorized access.

**Input:**

- HTTP request with `X-API-KEY` header
- Configured API key from environment variable `FLASK_API_KEY`

**Output:**

- Authorization status:
    - Authorized: Proceed with request processing
    - Unauthorized: Return 401 error with message

**Where It Happens:**

- **Flask API:** `mqtt-scanner/app.py` - `@require_api_key` decorator
- **Laravel Backend:** Sends API key in HTTP request headers

**Priority:** ESSENTIAL - Security requirement to protect API

---

### B.10 Rate Limit Scan Requests

**Purpose:** Prevent abuse and resource exhaustion by limiting number of scan requests per user/IP address within time window.

**Input:**

- User ID (for authenticated requests)
- IP address (for API requests)
- Time window (60 seconds)
- Request limit (10 requests for Laravel, 5 requests for Flask)

**Output:**

- Rate limit status:
    - Allowed: Process request normally
    - Exceeded: Return 429 error with retry-after time

**Where It Happens:**

- **Laravel Backend:** `RateLimiter` facade with custom key (`app/Providers/RouteServiceProvider.php`)
- **Flask API:** `mqtt-scanner/app.py` - `check_rate_limit()` function using sliding window algorithm

**Priority:** ESSENTIAL - System protection and fair usage

---

### B.11 Log Security Audit Events

**Purpose:** Record security-relevant events (scan initiation, completion, failures, authentication) to audit logs for compliance and incident investigation.

**Input:**

- Event type (string: scan_initiated, scan_completed, scan_failed, login_attempt, etc.)
- User ID (integer)
- IP address (string)
- Timestamp (datetime)
- Event data (JSON: target, scan_id, error details, etc.)

**Output:**

- Log entry written to file system
- Log files: `storage/logs/laravel.log`, `storage/logs/audit.log`

**Where It Happens:**

- **Laravel Backend:** `Log` facade with custom channels
- **Log Configuration:** `config/logging.php`
- **Usage:** Throughout controllers and authentication flows

**Priority:** ESSENTIAL - Security compliance and forensics

---

### B.12 Validate User Input

**Purpose:** Sanitize and validate all user inputs to prevent injection attacks (SQL injection, command injection, XSS) and ensure data integrity.

**Input:**

- Raw user input from forms/requests (target, email, password, etc.)
- Validation rules (format, length, allowed characters)

**Output:**

- Validated data (sanitized and type-checked)
- Validation errors (array of error messages if validation fails)

**Where It Happens:**

- **Laravel Backend:** `$request->validate()` method with validation rules
- **Flask API:** Regex pattern matching and type checking
- **Validation Rules Examples:**
    - Target: `/^[0-9\.\/:a-zA-Z\-]+$/` (alphanumeric, dots, slashes, colons, hyphens only)
    - Email: Standard email format validation
    - Password: Minimum 8 characters, complexity requirements

**Priority:** ESSENTIAL - Security requirement to prevent attacks

---

### B.13 Manage User Sessions

**Purpose:** Maintain secure session state for authenticated users, including session creation, validation, and destruction.

**Input:**

- User credentials (for session creation)
- Session ID cookie (for validation)
- Session timeout configuration (default 120 minutes)

**Output:**

- Active session with user authentication status
- Session data stored securely (encrypted)
- Automatic session expiration

**Where It Happens:**

- **Laravel Backend:** Session middleware and session driver
- **Configuration:** `config/session.php`
- **Session Storage:** File system (`storage/framework/sessions/`) or database

**Priority:** ESSENTIAL - User authentication and authorization

---

### B.14 Execute HTTP Communication (Laravel to Flask)

**Purpose:** Enable Laravel backend to communicate with Flask API for scan execution, transmitting scan parameters and receiving results.

**Input:**

- HTTP request parameters:
    - URL: `http://127.0.0.1:5000/api/scan`
    - Method: POST
    - Headers: `X-API-KEY`, `Content-Type: application/json`
    - Body: JSON with `target` parameter
    - Timeout: 60 seconds

**Output:**

- HTTP response:
    - Success (200): JSON with scan results array and summary
    - Error (4xx/5xx): Error message and status code

**Where It Happens:**

- **Laravel Backend:** `Http::post()` facade with timeout and headers
- **Flask API:** Receives and processes request at `/api/scan` endpoint
- **Network:** HTTP communication over localhost

**Priority:** ESSENTIAL - Inter-tier communication

---

## OPTIONAL FUNCTIONS

These functions enhance the system but are not strictly required for basic operation.

### O.1 Deploy Docker Test Brokers

**Purpose:** Provide isolated MQTT broker instances (insecure and secure) for testing scanner functionality without affecting production brokers.

**Input:**

- Docker Compose configuration (`mqtt-brokers/docker-compose.yml`)
- Broker configuration files (Mosquitto configs)
- TLS certificates (for secure broker)

**Output:**

- Running Docker containers:
    - `mosquitto_insecure` on port 1883 (anonymous access)
    - `mosquitto_secure` on port 8883 (authenticated + TLS)

**Where It Happens:**

- **Docker Infrastructure:** `mqtt-brokers/` directory
- **Container Orchestration:** Docker Compose
- **Broker Software:** Eclipse Mosquitto 2.0

**Priority:** OPTIONAL - Recommended for development/testing

---

### O.2 Publish ESP32 Sensor Telemetry

**Purpose:** Generate realistic IoT MQTT traffic using physical hardware sensors to validate scanner against live deployments.

**Input:**

- ESP32 firmware (`esp32_mixed_security.ino`)
- WiFi credentials configuration
- MQTT broker IP address
- Sensor readings (DHT11 temperature/humidity, LDR light, PIR motion)

**Output:**

- MQTT messages published to test brokers:
    - Secure channel: `esp32/secure/sensors` with DHT11 + LDR data
    - Insecure channel: `esp32/insecure/motion` with PIR data

**Where It Happens:**

- **ESP32 Hardware:** Microcontroller board with sensors
- **Firmware:** Arduino C++ code uploaded to ESP32
- **MQTT Brokers:** Receives published telemetry

**Priority:** OPTIONAL - Hardware validation (not required for core functionality)

---

### O.3 Display Real-Time Dashboards/Charts

**Purpose:** Visualize scan statistics and trends through graphical charts (pie charts for severity distribution, line graphs for historical trends).

**Input:**

- Scan results data (severity counts, timestamps)
- Charting library (Chart.js, ApexCharts)

**Output:**

- Interactive charts displaying:
    - Severity distribution (Critical/High/Medium/Low/Info)
    - Scan frequency over time
    - Broker discovery trends

**Where It Happens:**

- **Laravel UI:** Dashboard page with chart components
- **Frontend JavaScript:** Chart rendering library
- **Data Source:** API endpoints providing aggregated statistics

**Priority:** OPTIONAL - Enhanced user experience

---

## SYSTEM EXECUTION WORKFLOW

**End-to-End Scan Process:**

1. **User Action:** User logs into Laravel UI, navigates to dashboard, enters target (e.g., `192.168.1.0/24`), clicks "Start Scan"

2. **Laravel Processing:**
    - Validates target input (B.12)
    - Checks rate limit (B.10)
    - Creates scan history record with status "running" (B.7)
    - Logs audit event "scan_initiated" (B.11)

3. **API Communication:**
    - Laravel sends HTTP POST to Flask API (B.14)
    - Request includes X-API-KEY header and target parameter
    - Flask validates API key (B.9)
    - Flask checks rate limit (B.10)

4. **Scan Execution:**
    - Python scanner parses target to IP list (B.1)
    - For each IP:
        - Check TCP ports 1883, 8883 (B.2)
        - If port open:
            - Attempt anonymous connection (B.3)
            - If failed, attempt authenticated connection (B.4)
            - If port 8883, inspect TLS certificate (B.5)
            - Classify vulnerability severity (B.6)

5. **Result Storage:**
    - Flask returns JSON results to Laravel (B.14)
    - Laravel stores each broker result (B.8)
    - Laravel updates scan history with "completed" status, totals (B.7)
    - Laravel logs audit event "scan_completed" (B.11)

6. **User Display:**
    - Laravel returns JSON response to frontend
    - UI displays results table with severity badges (A.4)
    - User can export to CSV (A.7) or view in history (A.5)

---

## TECHNOLOGY MAPPING

| Function Category         | Primary Technology                 | Secondary Technologies         |
| ------------------------- | ---------------------------------- | ------------------------------ |
| User Interface (Part A)   | Laravel Blade Templates            | Tailwind CSS, JavaScript       |
| Authentication & Sessions | Laravel Auth                       | Bcrypt password hashing        |
| Database Operations       | Laravel Eloquent ORM               | SQLite/MySQL                   |
| Input Validation          | Laravel Validation                 | Regex patterns                 |
| API Middleware            | Flask Framework                    | Werkzeug WSGI                  |
| Scanning Engine           | Python (paho-mqtt)                 | socket, ssl, ipaddress modules |
| Rate Limiting             | Laravel RateLimiter + Flask custom | In-memory storage              |
| Logging                   | Laravel Log facade                 | Monolog                        |
| Containerization          | Docker Compose                     | Eclipse Mosquitto              |
| Hardware (Optional)       | ESP32 + Arduino IDE                | PubSubClient, DHT libraries    |

---

## FUNCTIONAL DEPENDENCIES

**Critical Path (Must Work Together):**

```
User Login (A.2)
    ↓
Initiate Scan (A.3)
    ↓
    ├─→ Validate Input (B.12)
    ├─→ Check Rate Limit (B.10)
    ├─→ Create Scan History (B.7)
    ├─→ Authenticate API (B.9)
    └─→ Parse Target (B.1)
            ↓
        Check TCP Ports (B.2)
            ↓
        Probe MQTT (Anonymous) (B.3)
            ↓
        Probe MQTT (Authenticated) (B.4)
            ↓
        Inspect TLS (B.5) [if port 8883]
            ↓
        Classify Severity (B.6)
            ↓
        Store Results (B.8)
            ↓
View Results (A.4) + Export (A.7)
```

**Supporting Functions:**

- Audit Logging (B.11) - Runs parallel to all operations
- Session Management (B.13) - Active throughout user session
- HTTP Communication (B.14) - Connects Laravel ↔ Flask

---

## CONCLUSION

**Total Essential Functions:** 22 (8 user-facing + 14 system core)  
**Total Optional Functions:** 3 (Docker brokers, ESP32 hardware, advanced visualizations)

**Minimum Viable System:** All functions in Part A and Part B are required for a working end-to-end MQTT security scanner. The optional functions enhance testing capabilities and user experience but are not blocking for core functionality.

**Function Completeness:** All extracted functions directly correspond to implemented code in the project repository with no invented features.
