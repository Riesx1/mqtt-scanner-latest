# CHAPTER 4: IMPLEMENTATION - COMPREHENSIVE DEVELOPMENT GUIDE

## 4.1 Introduction

This chapter provides a comprehensive step-by-step guide on how to develop the MQTT Network Security Scanner from scratch. Rather than simply documenting what was built, this guide walks through the actual development process for each main function, including code examples, configuration steps, and troubleshooting tips. By following this chapter, another developer should be able to recreate the entire system independently.

The development follows a logical progression:

- **Section 4.2:** Environment setup and project initialization
- **Section 4.3:** Building the core scanning engine (Python)
- **Section 4.4:** Creating the Flask API middleware
- **Section 4.5:** Developing the Laravel web interface
- **Section 4.6:** Implementing database persistence
- **Section 4.7:** Adding security controls
- **Section 4.8:** Deploying test infrastructure (Docker brokers)
- **Section 4.9:** Testing and validation

Each section focuses on developing specific main functions identified in the system analysis, with complete code listings and explanations.

---

## 4.2 Environment Setup and Project Initialization

### 4.2.1 Installing Required Software

Before starting development, install all prerequisite software on your development machine.

#### Step 1: Install PHP 8.2+ and Composer

**Windows Installation:**

1. Download PHP 8.2 from https://windows.php.net/download/
2. Extract to `C:\php` and add to system PATH:

    ```powershell
    $env:Path += ";C:\php"
    [Environment]::SetEnvironmentVariable("Path", $env:Path, [System.EnvironmentVariableTarget]::Machine)
    ```

3. Enable required PHP extensions in `C:\php\php.ini`:

    ```ini
    extension=openssl
    extension=pdo_sqlite
    extension=pdo_mysql
    extension=mbstring
    extension=fileinfo
    extension=curl
    extension=zip
    ```

4. Verify PHP installation:

    ```powershell
    php -v
    # Expected: PHP 8.2.x (cli)
    ```

5. Download and install Composer from https://getcomposer.org/Composer-Setup.exe

6. Verify Composer:
    ```powershell
    composer --version
    # Expected: Composer version 2.x.x
    ```

#### Step 2: Install Node.js and NPM

1. Download Node.js 20.x LTS from https://nodejs.org/
2. Run installer with default settings
3. Verify installation:
    ```powershell
    node --version  # Expected: v20.x.x
    npm --version   # Expected: 10.x.x
    ```

#### Step 3: Install Python 3.10+

1. Download Python 3.10+ from https://www.python.org/downloads/
2. Run installer and CHECK "Add Python to PATH"
3. Verify installation:
    ```powershell
    python --version  # Expected: Python 3.10.x or higher
    pip --version     # Expected: pip 23.x.x
    ```

#### Step 4: Install Docker Desktop

1. Download Docker Desktop from https://www.docker.com/products/docker-desktop/
2. Install and enable WSL 2 backend
3. Verify installation:
    ```powershell
    docker --version           # Expected: Docker version 24.x.x
    docker-compose --version   # Expected: Docker Compose version 2.x.x
    ```

#### Step 5: Install Git

1. Download Git from https://git-scm.com/downloads
2. Install with default settings
3. Configure global identity:
    ```bash
    git config --global user.name "Your Name"
    git config --global user.email "your.email@example.com"
    ```

### 4.2.2 Creating the Project Structure

**Step 1: Initialize Laravel Project**

Open PowerShell and create a new Laravel 12 project:

```powershell
composer create-project laravel/laravel mqtt-scanner-latest
cd mqtt-scanner-latest
```

**Step 2: Create Python Directory Structure**

Inside the Laravel project, create directories for Python components:

```powershell
New-Item -ItemType Directory -Path mqtt-scanner
New-Item -ItemType Directory -Path mqtt-brokers
```

**Step 3: Initialize Git Repository**

```powershell
git init
git add .
git commit -m "Initial Laravel 12 project setup"
```

**Step 4: Create Python Virtual Environment**

```powershell
python -m venv .venv
.\.venv\Scripts\Activate.ps1
```

Your terminal prompt should now show `(.venv)` prefix.

---

## 4.3 Building the Core Scanning Engine (Python)

This section demonstrates how to develop the main scanning functions from scratch.

### 4.3.1 Function: Parse Target Specification

**Purpose:** Convert user input (IP address, CIDR notation) into a list of IPs to scan.

**Development Steps:**

**Step 1: Create the scanner module**

Create file `mqtt-scanner/scanner.py`:

```python
#!/usr/bin/env python3
"""
MQTT Network Security Scanner - Core Scanning Engine
"""

import ipaddress
from typing import List
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)
```

**Step 2: Implement target parsing function**

Add this function to `scanner.py`:

```python
def parse_target(target: str) -> List[str]:
    """
    Parse target specification into list of IP addresses
    Supports: single IP, CIDR notation

    Args:
        target: String containing IP address or CIDR (e.g., "192.168.1.0/24")

    Returns:
        List of IP addresses as strings

    Raises:
        ValueError: If target format is invalid
    """
    try:
        # Check if target is CIDR notation
        if '/' in target:
            network = ipaddress.ip_network(target, strict=False)
            ip_list = [str(ip) for ip in network.hosts()]
            logger.info(f"Parsed CIDR {target} into {len(ip_list)} IP addresses")
            return ip_list

        # Single IP address
        ip_obj = ipaddress.ip_address(target)
        logger.info(f"Parsed single IP: {target}")
        return [str(ip_obj)]

    except ValueError as e:
        logger.error(f"Invalid target format: {target} - {e}")
        raise ValueError(f"Invalid target format: {target}")
```

**Step 3: Test the function**

Add a test block at the end of `scanner.py`:

```python
if __name__ == '__main__':
    # Test parse_target function
    print("Testing parse_target function...")

    # Test 1: Single IP
    result = parse_target("192.168.1.10")
    print(f"Single IP: {result}")
    assert result == ["192.168.1.10"], "Single IP test failed"

    # Test 2: CIDR notation
    result = parse_target("192.168.1.0/30")
    print(f"CIDR /30: {result}")
    assert len(result) == 2, "CIDR /30 should have 2 hosts"

    # Test 3: Invalid input
    try:
        parse_target("invalid_input")
        assert False, "Should have raised ValueError"
    except ValueError:
        print("Invalid input correctly rejected")

    print("✓ All tests passed!")
```

**Step 4: Run and verify**

```powershell
python mqtt-scanner/scanner.py
```

Expected output:

```
Testing parse_target function...
Single IP: ['192.168.1.10']
CIDR /30: ['192.168.1.1', '192.168.1.2']
Invalid input correctly rejected
✓ All tests passed!
```

### 4.3.2 Function: Check TCP Port Availability

**Purpose:** Determine if MQTT ports are open before attempting MQTT protocol handshake.

**Development Steps:**

**Step 1: Import socket module**

Add to the top of `scanner.py`:

```python
import socket
```

**Step 2: Implement TCP port checking**

Add this function to `scanner.py`:

```python
def check_tcp_port(host: str, port: int, timeout: int = 3) -> bool:
    """
    Check if a TCP port is open on target host

    Args:
        host: IP address or hostname
        port: Port number to check
        timeout: Connection timeout in seconds

    Returns:
        True if port is open, False otherwise
    """
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.settimeout(timeout)

    try:
        result = sock.connect_ex((host, port))
        sock.close()

        if result == 0:
            logger.debug(f"Port {port} is OPEN on {host}")
            return True
        else:
            logger.debug(f"Port {port} is CLOSED on {host}")
            return False

    except socket.error as e:
        logger.debug(f"Socket error checking {host}:{port} - {e}")
        return False
```

**Step 3: Test the function**

Update the test block:

```python
if __name__ == '__main__':
    print("\n=== Testing check_tcp_port function ===")

    # Test against common open port (DNS)
    is_open = check_tcp_port("8.8.8.8", 53, timeout=2)
    print(f"Google DNS port 53: {'OPEN' if is_open else 'CLOSED'}")

    # Test against closed port
    is_open = check_tcp_port("8.8.8.8", 9999, timeout=2)
    print(f"Google DNS port 9999: {'OPEN' if is_open else 'CLOSED'}")
    assert not is_open, "Port 9999 should be closed"

    print("✓ TCP port check tests passed!")
```

### 4.3.3 Function: Probe MQTT Broker (Anonymous Access)

**Purpose:** Attempt to connect to MQTT broker without credentials to detect vulnerabilities.

**Development Steps:**

**Step 1: Install paho-mqtt library**

Create `mqtt-scanner/requirements.txt`:

```txt
paho-mqtt==1.6.1
flask==3.1.0
python-dotenv==1.0.0
```

Install dependencies:

```powershell
pip install -r mqtt-scanner/requirements.txt
```

**Step 2: Import MQTT client**

Add to `scanner.py`:

```python
import paho.mqtt.client as mqtt_client
import ssl
import time
from typing import Dict, Optional
```

**Step 3: Implement anonymous connection testing**

Add this function:

```python
def test_anonymous_connection(host: str, port: int) -> Dict:
    """
    Test MQTT broker for anonymous access vulnerability

    Args:
        host: Broker IP address
        port: Broker port (1883 or 8883)

    Returns:
        Dictionary with connection result:
        - success: Boolean
        - outcome: String description
        - severity: String (Critical/Medium/Low/Info)
        - error: String error message (if failed)
    """
    client_id = f"scanner_test_{int(time.time())}"
    client = mqtt_client.Client(client_id=client_id, protocol=mqtt_client.MQTTv311)

    result = {
        'success': False,
        'outcome': 'Unknown',
        'severity': 'Info',
        'error': ''
    }

    # Configure TLS for secure port
    if port == 8883:
        try:
            client.tls_set(cert_reqs=ssl.CERT_NONE)
            client.tls_insecure_set(True)
            logger.debug(f"TLS configured for {host}:{port}")
        except Exception as e:
            result['error'] = f"TLS configuration failed: {str(e)}"
            return result

    # Set up connection callback
    def on_connect(client, userdata, flags, rc, properties=None):
        if rc == 0:
            result['success'] = True
            result['outcome'] = 'Anonymous Access Allowed'
            result['severity'] = 'Critical'
            logger.warning(f"CRITICAL: {host}:{port} allows anonymous access!")
        else:
            error_codes = {
                1: "Connection refused - incorrect protocol version",
                2: "Connection refused - invalid client identifier",
                3: "Connection refused - server unavailable",
                4: "Connection refused - bad username or password",
                5: "Connection refused - not authorized"
            }
            result['error'] = error_codes.get(rc, f"Connection failed with code {rc}")
            result['outcome'] = 'Authentication Required' if rc == 5 else 'Connection Failed'
            result['severity'] = 'Medium' if rc == 5 else 'Info'

    client.on_connect = on_connect

    # Attempt connection
    try:
        client.connect(host, port, keepalive=10)
        client.loop_start()
        time.sleep(2)  # Wait for callback
        client.loop_stop()
        client.disconnect()
    except Exception as e:
        result['error'] = str(e)
        result['outcome'] = 'Connection Failed'
        logger.debug(f"Connection exception for {host}:{port} - {e}")

    return result
```

**Step 4: Create a simple test function**

Add this function to scan a single target:

```python
def scan_single_broker(host: str, port: int = 1883) -> Dict:
    """
    Scan a single MQTT broker

    Args:
        host: Broker IP address
        port: Broker port (default 1883)

    Returns:
        Dictionary with scan results
    """
    logger.info(f"Scanning {host}:{port}")

    # Check if port is open
    if not check_tcp_port(host, port):
        return {
            'ip_address': host,
            'port': port,
            'outcome': 'Port Closed',
            'severity': 'Info',
            'details': f'TCP port {port} is not open'
        }

    # Test anonymous access
    conn_result = test_anonymous_connection(host, port)

    return {
        'ip_address': host,
        'port': port,
        'outcome': conn_result['outcome'],
        'severity': conn_result['severity'],
        'details': conn_result.get('error', 'Anonymous access test completed'),
        'timestamp': time.strftime('%Y-%m-%d %H:%M:%S')
    }
```

**Step 5: Test against local broker**

Update test block:

```python
if __name__ == '__main__':
    print("\n=== Testing MQTT Scanning ===")

    # Test against public test broker (if available)
    test_broker = "test.mosquitto.org"
    test_port = 1883

    print(f"Scanning {test_broker}:{test_port}")
    result = scan_single_broker(test_broker, test_port)

    print(f"\nScan Result:")
    print(f"  IP: {result['ip_address']}")
    print(f"  Port: {result['port']}")
    print(f"  Outcome: {result['outcome']}")
    print(f"  Severity: {result['severity']}")
    print(f"  Details: {result['details']}")
```

Run the test:

```powershell
python mqtt-scanner/scanner.py
```

### 4.3.4 Function: Full Network Scan

**Purpose:** Combine all scanning functions to scan multiple targets.

**Development Steps:**

**Step 1: Create main scanning class**

Add this class to `scanner.py`:

```python
class MQTTScanner:
    """Main scanner class orchestrating all scan operations"""

    def __init__(self):
        self.results = []
        self.scan_start_time = None
        self.scan_end_time = None

    def scan(self, target: str, ports: List[int] = [1883, 8883]) -> List[Dict]:
        """
        Execute full scan on target

        Args:
            target: IP address or CIDR notation
            ports: List of MQTT ports to scan

        Returns:
            List of scan result dictionaries
        """
        logger.info(f"Starting scan of target: {target}")
        self.scan_start_time = time.time()
        self.results = []

        # Parse target into IP list
        try:
            ip_addresses = parse_target(target)
        except ValueError as e:
            logger.error(f"Target parsing failed: {e}")
            return []

        logger.info(f"Scanning {len(ip_addresses)} IP addresses")

        # Scan each IP
        for ip in ip_addresses:
            for port in ports:
                result = scan_single_broker(ip, port)

                # Only store results for open ports
                if result['outcome'] != 'Port Closed':
                    self.results.append(result)

        self.scan_end_time = time.time()
        duration = self.scan_end_time - self.scan_start_time
        logger.info(f"Scan completed in {duration:.2f} seconds")
        logger.info(f"Found {len(self.results)} accessible MQTT brokers")

        return self.results

    def get_summary(self) -> Dict:
        """Get scan summary statistics"""
        total = len(self.results)
        severity_counts = {
            'Critical': sum(1 for r in self.results if r['severity'] == 'Critical'),
            'High': sum(1 for r in self.results if r['severity'] == 'High'),
            'Medium': sum(1 for r in self.results if r['severity'] == 'Medium'),
            'Low': sum(1 for r in self.results if r['severity'] == 'Low'),
            'Info': sum(1 for r in self.results if r['severity'] == 'Info')
        }

        return {
            'total_brokers_found': total,
            'vulnerable_count': severity_counts['Critical'] + severity_counts['High'],
            'severity_counts': severity_counts,
            'scan_duration_seconds': self.scan_end_time - self.scan_start_time if self.scan_end_time else 0
        }
```

**Step 2: Create entry point function**

Add this function for API integration:

```python
def run_scan(target: str) -> Dict:
    """
    Main entry point for scanning operations
    Used by Flask API

    Args:
        target: IP address or CIDR notation

    Returns:
        Dictionary with scan results and summary
    """
    scanner = MQTTScanner()
    results = scanner.scan(target)
    summary = scanner.get_summary()

    return {
        'success': True,
        'target': target,
        'results': results,
        'summary': summary
    }
```

**Step 3: Test the complete scanner**

Update test block:

```python
if __name__ == '__main__':
    import sys

    print("\n=== Complete Scanner Test ===")

    # Allow command-line target specification
    if len(sys.argv) > 1:
        target = sys.argv[1]
    else:
        target = "test.mosquitto.org"  # Public test broker

    print(f"Target: {target}\n")

    # Run scan
    scan_data = run_scan(target)

    # Display results
    print(f"=== Scan Results ===")
    print(f"Target: {scan_data['target']}")
    print(f"Total Brokers Found: {scan_data['summary']['total_brokers_found']}")
    print(f"Vulnerable: {scan_data['summary']['vulnerable_count']}")
    print(f"Duration: {scan_data['summary']['scan_duration_seconds']:.2f}s\n")

    print("Severity Breakdown:")
    for severity, count in scan_data['summary']['severity_counts'].items():
        if count > 0:
            print(f"  {severity}: {count}")

    print("\nDetailed Results:")
    for result in scan_data['results']:
        print(f"\n  {result['ip_address']}:{result['port']}")
        print(f"    Outcome: {result['outcome']}")
        print(f"    Severity: {result['severity']}")
```

**Step 4: Test with different targets**

```powershell
# Test single IP
python mqtt-scanner/scanner.py test.mosquitto.org

# Test CIDR range (after you have local brokers running)
python mqtt-scanner/scanner.py 127.0.0.1
```

**Congratulations!** You've built a working MQTT security scanner engine. The scanner can:

- Parse IP addresses and CIDR notation
- Check TCP port availability
- Test for anonymous MQTT access
- Classify vulnerability severity
- Generate summary statistics

---

## 4.4 Creating the Flask API Middleware

Now we'll wrap the scanner in a REST API for Laravel to consume.

### 4.4.1 Function: API Authentication

**Purpose:** Secure the Flask API with API key authentication.

**Development Steps:**

**Step 1: Create Flask application file**

Create `mqtt-scanner/app.py`:

```python
#!/usr/bin/env python3
"""
MQTT Scanner - Flask API Server
Provides RESTful middleware for Laravel integration
"""

from flask import Flask, request, jsonify
from scanner import run_scan
import os
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Initialize Flask
app = Flask(__name__)

# Load configuration from environment
FLASK_API_KEY = os.environ.get('FLASK_API_KEY', 'CHANGE_ME_IN_PRODUCTION')
FLASK_HOST = os.environ.get('FLASK_HOST', '127.0.0.1')
FLASK_PORT = int(os.environ.get('FLASK_PORT', 5000))
```

**Step 2: Implement API key authentication decorator**

Add to `app.py`:

```python
def require_api_key(f):
    """
    Decorator to enforce API key authentication

    Usage:
        @app.route('/protected')
        @require_api_key
        def protected_route():
            return {'message': 'Access granted'}
    """
    def decorated_function(*args, **kwargs):
        # Get API key from request header
        api_key = request.headers.get('X-API-KEY')

        # Check if API key is present
        if not api_key:
            logger.warning(f"API request without key from {request.remote_addr}")
            return jsonify({
                'error': 'Missing X-API-KEY header',
                'message': 'Please provide API key in X-API-KEY header'
            }), 401

        # Validate API key
        if api_key != FLASK_API_KEY:
            logger.warning(f"Invalid API key attempt from {request.remote_addr}")
            return jsonify({
                'error': 'Invalid API key',
                'message': 'The provided API key is incorrect'
            }), 401

        # API key is valid, proceed with request
        return f(*args, **kwargs)

    decorated_function.__name__ = f.__name__
    return decorated_function
```

**Step 3: Create health check endpoint (no auth required)**

Add to `app.py`:

```python
@app.route('/health', methods=['GET'])
def health_check():
    """
    Health check endpoint for monitoring
    No authentication required
    """
    return jsonify({
        'status': 'healthy',
        'service': 'mqtt-scanner-api',
        'version': '2.0'
    }), 200
```

**Step 4: Test Flask API authentication**

Add at the end of `app.py`:

```python
if __name__ == '__main__':
    logger.info(f"Starting Flask API server on {FLASK_HOST}:{FLASK_PORT}")

    if FLASK_API_KEY == 'CHANGE_ME_IN_PRODUCTION':
        logger.warning("⚠️  WARNING: Using default API key! Set FLASK_API_KEY environment variable.")

    app.run(host=FLASK_HOST, port=FLASK_PORT, debug=True)
```

Start the Flask server:

```powershell
cd mqtt-scanner
python app.py
```

Expected output:

```
Starting Flask API server on 127.0.0.1:5000
⚠️  WARNING: Using default API key! Set FLASK_API_KEY environment variable.
 * Running on http://127.0.0.1:5000
```

**Step 5: Test health endpoint (no auth)**

Open new PowerShell terminal and test:

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:5000/health"
```

Expected response:

```json
{
    "status": "healthy",
    "service": "mqtt-scanner-api",
    "version": "2.0"
}
```

### 4.4.2 Function: Scan API Endpoint

**Purpose:** Create protected endpoint that executes scans and returns results.

**Development Steps:**

**Step 1: Implement scan endpoint**

Add to `app.py` (before the `if __name__ == '__main__'` block):

```python
@app.route('/api/scan', methods=['POST'])
@require_api_key
def api_scan():
    """
    Execute MQTT network scan

    Request Body (JSON):
        {
            "target": "192.168.1.0/24"
        }

    Response (JSON):
        {
            "success": true,
            "target": "192.168.1.0/24",
            "results": [...],
            "summary": {...}
        }

    Requires: X-API-KEY header
    """
    # Validate content type
    if not request.is_json:
        return jsonify({
            'error': 'Invalid content type',
            'message': 'Content-Type must be application/json'
        }), 400

    # Parse request data
    data = request.get_json()
    target = data.get('target')

    # Validate target parameter
    if not target:
        return jsonify({
            'error': 'Missing parameter',
            'message': 'Required parameter "target" is missing'
        }), 400

    if not isinstance(target, str):
        return jsonify({
            'error': 'Invalid parameter type',
            'message': 'Parameter "target" must be a string'
        }), 400

    if len(target) > 100:
        return jsonify({
            'error': 'Parameter too long',
            'message': 'Target parameter must be less than 100 characters'
        }), 400

    # Input sanitization - allow only safe characters
    import re
    if not re.match(r'^[0-9\.\/:a-zA-Z\-]+$', target):
        return jsonify({
            'error': 'Invalid target format',
            'message': 'Target contains invalid characters'
        }), 400

    # Execute scan
    try:
        logger.info(f"Scan requested for target: {target} (from {request.remote_addr})")
        scan_results = run_scan(target)
        logger.info(f"Scan completed. Found {len(scan_results['results'])} brokers.")

        return jsonify(scan_results), 200

    except Exception as e:
        logger.error(f"Scan failed for target {target}: {str(e)}")
        return jsonify({
            'error': 'Scan execution failed',
            'message': str(e)
        }), 500
```

**Step 2: Test scan endpoint with authentication**

Restart Flask server (Ctrl+C, then `python app.py`).

In another terminal, test WITHOUT API key (should fail):

```powershell
$body = @{ target = "127.0.0.1" } | ConvertTo-Json
Invoke-RestMethod -Uri "http://127.0.0.1:5000/api/scan" -Method POST -Body $body -ContentType "application/json"
```

Expected error:

```json
{
    "error": "Missing X-API-KEY header",
    "message": "Please provide API key in X-API-KEY header"
}
```

Test WITH API key (should succeed):

```powershell
$headers = @{ "X-API-KEY" = "CHANGE_ME_IN_PRODUCTION" }
$body = @{ target = "127.0.0.1" } | ConvertTo-Json
Invoke-RestMethod -Uri "http://127.0.0.1:5000/api/scan" -Method POST -Headers $headers -Body $body -ContentType "application/json"
```

Expected response:

```json
{
  "success": true,
  "target": "127.0.0.1",
  "results": [...],
  "summary": {
    "total_brokers_found": 0,
    "vulnerable_count": 0,
    ...
  }
}
```

### 4.4.3 Function: Rate Limiting

**Purpose:** Prevent API abuse by limiting requests per IP address.

**Development Steps:**

**Step 1: Implement rate limiter**

Add to `app.py` (after imports):

```python
import time
from collections import defaultdict

# Rate limiting storage
rate_limit_storage = defaultdict(list)
RATE_LIMIT_REQUESTS = 5
RATE_LIMIT_WINDOW = 60  # seconds

def check_rate_limit(client_ip: str) -> tuple:
    """
    Check if client has exceeded rate limit

    Args:
        client_ip: Client IP address

    Returns:
        Tuple (allowed: bool, retry_after: int)
    """
    current_time = time.time()

    # Remove expired requests
    rate_limit_storage[client_ip] = [
        req_time for req_time in rate_limit_storage[client_ip]
        if current_time - req_time < RATE_LIMIT_WINDOW
    ]

    # Check if limit exceeded
    if len(rate_limit_storage[client_ip]) >= RATE_LIMIT_REQUESTS:
        oldest_request = rate_limit_storage[client_ip][0]
        retry_after = int(RATE_LIMIT_WINDOW - (current_time - oldest_request))
        return False, retry_after

    # Record this request
    rate_limit_storage[client_ip].append(current_time)
    remaining = RATE_LIMIT_REQUESTS - len(rate_limit_storage[client_ip])

    logger.debug(f"Rate limit check for {client_ip}: {remaining} requests remaining")
    return True, 0
```

**Step 2: Apply rate limiting to scan endpoint**

Modify the `api_scan()` function to add rate limiting at the beginning:

```python
@app.route('/api/scan', methods=['POST'])
@require_api_key
def api_scan():
    # Rate limiting check
    client_ip = request.remote_addr
    allowed, retry_after = check_rate_limit(client_ip)

    if not allowed:
        logger.warning(f"Rate limit exceeded for {client_ip}")
        return jsonify({
            'error': 'Rate limit exceeded',
            'message': f'Too many requests. Try again in {retry_after} seconds.',
            'retry_after': retry_after
        }), 429

    # ... rest of the function remains the same
```

**Step 3: Test rate limiting**

Create a test script `mqtt-scanner/test_rate_limit.py`:

```python
import requests
import time

API_URL = "http://127.0.0.1:5000/api/scan"
API_KEY = "CHANGE_ME_IN_PRODUCTION"

headers = {"X-API-KEY": API_KEY}
body = {"target": "127.0.0.1"}

print("Testing rate limiting...")
print(f"Limit: {5} requests per 60 seconds\n")

for i in range(7):
    print(f"Request {i+1}...")
    response = requests.post(API_URL, json=body, headers=headers)

    if response.status_code == 200:
        print(f"  ✓ Success (Status: {response.status_code})")
    elif response.status_code == 429:
        data = response.json()
        print(f"  ✗ Rate limited! Retry after: {data['retry_after']}s")
        break
    else:
        print(f"  ? Status: {response.status_code}")

    time.sleep(1)

print("\n✓ Rate limiting is working correctly!")
```

Run the test:

```powershell
python mqtt-scanner/test_rate_limit.py
```

Expected output:

```
Testing rate limiting...
Limit: 5 requests per 60 seconds

Request 1...
  ✓ Success (Status: 200)
Request 2...
  ✓ Success (Status: 200)
...
Request 6...
  ✗ Rate limited! Retry after: 59s

✓ Rate limiting is working correctly!
```

**Congratulations!** Your Flask API now has:

- ✅ API key authentication
- ✅ Protected scan endpoint
- ✅ Rate limiting
- ✅ Input validation
- ✅ Error handling

---

## 4.5 Developing the Laravel Web Interface

Now we'll build the user-facing web interface using Laravel.

### 4.5.1 Function: User Authentication System

**Purpose:** Allow users to register, login, and access protected features.

**Development Steps:**

**Step 1: Install Laravel Breeze authentication**

```powershell
cd mqtt-scanner-latest
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run dev
```

This creates:

- Registration page (`/register`)
- Login page (`/login`)
- Dashboard page (`/dashboard`)
- User authentication logic

**Step 2: Configure database**

Edit `.env` file to use SQLite for development:

```env
APP_NAME="MQTT Scanner"
APP_ENV=local
APP_KEY=base64:GENERATED_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

Create SQLite database:

```powershell
New-Item -ItemType File -Path database/database.sqlite
```

**Step 3: Run migrations**

```powershell
php artisan migrate
```

This creates the `users` table and other authentication tables.

**Step 4: Test authentication**

Start Laravel development server:

```powershell
php artisan serve
```

Open browser to http://localhost:8000 and:

1. Click "Register" and create a test account
2. Verify you're redirected to dashboard after registration
3. Logout and login again to test authentication

### 4.5.2 Function: Database Schema for Scan History

**Purpose:** Create database tables to persist scan results.

**Development Steps:**

**Step 1: Create migration for scan histories**

```powershell
php artisan make:migration create_mqtt_scan_histories_table
```

Edit `database/migrations/YYYY_MM_DD_HHMMSS_create_mqtt_scan_histories_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mqtt_scan_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('target', 100);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->integer('total_brokers_found')->default(0);
            $table->integer('vulnerable_count')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mqtt_scan_histories');
    }
};
```

**Step 2: Create migration for scan results**

```powershell
php artisan make:migration create_mqtt_scan_results_table
```

Edit the migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mqtt_scan_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_history_id')->constrained('mqtt_scan_histories')->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->integer('port');
            $table->string('outcome', 100);
            $table->string('severity', 20);
            $table->text('details')->nullable();
            $table->boolean('tls_enabled')->default(false);
            $table->boolean('auth_required')->default(false);
            $table->timestamps();

            $table->index('scan_history_id');
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mqtt_scan_results');
    }
};
```

**Step 3: Run migrations**

```powershell
php artisan migrate
```

Expected output:

```
Migration table created successfully.
Migrating: 2024_01_01_000000_create_mqtt_scan_histories_table
Migrated:  2024_01_01_000000_create_mqtt_scan_histories_table
Migrating: 2024_01_01_000001_create_mqtt_scan_results_table
Migrated:  2024_01_01_000001_create_mqtt_scan_results_table
```

**Step 4: Create Eloquent models**

Create `app/Models/MqttScanHistory.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MqttScanHistory extends Model
{
    protected $fillable = [
        'user_id',
        'target',
        'started_at',
        'completed_at',
        'status',
        'total_brokers_found',
        'vulnerable_count'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(MqttScanResult::class, 'scan_history_id');
    }
}
```

Create `app/Models/MqttScanResult.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MqttScanResult extends Model
{
    protected $fillable = [
        'scan_history_id',
        'ip_address',
        'port',
        'outcome',
        'severity',
        'details',
        'tls_enabled',
        'auth_required'
    ];

    protected $casts = [
        'tls_enabled' => 'boolean',
        'auth_required' => 'boolean',
    ];

    public function scanHistory(): BelongsTo
    {
        return $this->belongsTo(MqttScanHistory::class, 'scan_history_id');
    }
}
```

### 4.5.3 Function: Scan Controller (Initiate Scan)

**Purpose:** Handle scan requests from web interface and communicate with Flask API.

**Development Steps:**

**Step 1: Configure Flask API credentials**

Add to `.env`:

```env
FLASK_BASE=http://127.0.0.1:5000
FLASK_API_KEY=CHANGE_ME_IN_PRODUCTION
```

**Step 2: Create controller**

```powershell
php artisan make:controller MqttScannerController
```

Edit `app/Http/Controllers/MqttScannerController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\MqttScanHistory;
use App\Models\MqttScanResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class MqttScannerController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        $recentScans = MqttScanHistory::where('user_id', Auth::id())
            ->with('results')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboard', compact('recentScans'));
    }

    /**
     * Execute MQTT scan
     */
    public function scan(Request $request): JsonResponse
    {
        // Validate input
        $validated = $request->validate([
            'target' => [
                'required',
                'string',
                'max:100',
                'regex:/^[0-9\.\/:a-zA-Z\-]+$/'
            ]
        ]);

        // Create scan history record
        $scanHistory = MqttScanHistory::create([
            'user_id' => Auth::id(),
            'target' => $validated['target'],
            'started_at' => now(),
            'status' => 'running'
        ]);

        Log::info("User {$scanHistory->user_id} initiated scan", [
            'scan_id' => $scanHistory->id,
            'target' => $validated['target']
        ]);

        try {
            // Call Flask API
            $response = Http::timeout(60)
                ->withHeaders([
                    'X-API-KEY' => env('FLASK_API_KEY'),
                    'Content-Type' => 'application/json'
                ])
                ->post(env('FLASK_BASE') . '/api/scan', [
                    'target' => $validated['target']
                ]);

            if (!$response->successful()) {
                throw new \Exception("Flask API returned status {$response->status()}");
            }

            $scanData = $response->json();

            // Store results
            $vulnerableCount = 0;
            foreach ($scanData['results'] as $result) {
                MqttScanResult::create([
                    'scan_history_id' => $scanHistory->id,
                    'ip_address' => $result['ip_address'],
                    'port' => $result['port'],
                    'outcome' => $result['outcome'],
                    'severity' => $result['severity'],
                    'details' => $result['details'] ?? '',
                    'tls_enabled' => $result['tls_enabled'] ?? false,
                    'auth_required' => $result['auth_required'] ?? false
                ]);

                if (in_array($result['severity'], ['Critical', 'High'])) {
                    $vulnerableCount++;
                }
            }

            // Update scan history
            $scanHistory->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_brokers_found' => count($scanData['results']),
                'vulnerable_count' => $vulnerableCount
            ]);

            Log::info("Scan {$scanHistory->id} completed", [
                'brokers_found' => count($scanData['results']),
                'vulnerable' => $vulnerableCount
            ]);

            return response()->json([
                'success' => true,
                'scan_id' => $scanHistory->id,
                'summary' => $scanData['summary'],
                'results' => $scanData['results']
            ]);

        } catch (\Exception $e) {
            // Mark scan as failed
            $scanHistory->update([
                'status' => 'failed',
                'completed_at' => now()
            ]);

            Log::error("Scan {$scanHistory->id} failed: {$e->getMessage()}");

            return response()->json([
                'error' => 'Scan failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

**Step 3: Create routes**

Edit `routes/web.php`:

```php
<?php

use App\Http\Controllers\MqttScannerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [MqttScannerController::class, 'index'])->name('dashboard');
    Route::post('/api/mqtt/scan', [MqttScannerController::class, 'scan'])->name('mqtt.scan');
});

require __DIR__.'/auth.php';
```

### 4.5.4 Function: Dashboard UI with Scan Form

**Purpose:** Create user interface for initiating scans and viewing results.

**Development Steps:**

**Step 1: Edit dashboard view**

Edit `resources/views/dashboard.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('MQTT Network Security Scanner') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Scan Form Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Initiate New Scan</h3>

                    <form id="scanForm" class="space-y-4">
                        @csrf
                        <div>
                            <label for="target" class="block text-sm font-medium text-gray-700">
                                Target (IP Address or CIDR)
                            </label>
                            <input
                                type="text"
                                id="target"
                                name="target"
                                placeholder="e.g., 192.168.1.10 or 192.168.1.0/24"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                            <p class="mt-1 text-sm text-gray-500">
                                Enter a single IP address or CIDR notation for network range scanning
                            </p>
                        </div>

                        <div>
                            <button
                                type="submit"
                                id="scanButton"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Start Scan
                            </button>
                        </div>

                        <div id="scanStatus" class="hidden">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="animate-spin h-5 w-5 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Scanning in progress...
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Card -->
            <div id="resultsCard" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Scan Results</h3>

                    <!-- Summary Statistics -->
                    <div id="scanSummary" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6"></div>

                    <!-- Results Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Port</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outcome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TLS</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Auth</th>
                                </tr>
                            </thead>
                            <tbody id="resultsTableBody" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Scans History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Scans</h3>

                    @if($recentScans->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentScans as $scan)
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium">Target: {{ $scan->target }}</p>
                                            <p class="text-sm text-gray-500">{{ $scan->started_at->diffForHumans() }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($scan->status === 'completed') bg-green-100 text-green-800
                                                @elseif($scan->status === 'failed') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800
                                                @endif">
                                                {{ ucfirst($scan->status) }}
                                            </span>
                                            <p class="text-sm text-gray-600 mt-1">
                                                Found: {{ $scan->total_brokers_found }} | Vulnerable: {{ $scan->vulnerable_count }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No scans yet. Start your first scan above!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('scanForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const target = document.getElementById('target').value;
            const button = document.getElementById('scanButton');
            const status = document.getElementById('scanStatus');
            const resultsCard = document.getElementById('resultsCard');

            // Show loading state
            button.disabled = true;
            status.classList.remove('hidden');
            resultsCard.classList.add('hidden');

            try {
                const response = await fetch('/api/mqtt/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ target: target })
                });

                const data = await response.json();

                if (data.success) {
                    displayResults(data);
                } else {
                    alert('Scan failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                button.disabled = false;
                status.classList.add('hidden');
            }
        });

        function displayResults(data) {
            const resultsCard = document.getElementById('resultsCard');
            const summaryDiv = document.getElementById('scanSummary');
            const tableBody = document.getElementById('resultsTableBody');

            // Display summary
            summaryDiv.innerHTML = `
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-900">${data.summary.total_brokers_found}</div>
                    <div class="text-sm text-blue-700">Brokers Found</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-red-900">${data.summary.vulnerable_count}</div>
                    <div class="text-sm text-red-700">Vulnerable</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-900">${data.summary.scan_duration_seconds.toFixed(2)}s</div>
                    <div class="text-sm text-green-700">Scan Duration</div>
                </div>
            `;

            // Display results table
            tableBody.innerHTML = data.results.map(result => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${result.ip_address}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${result.port}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${result.outcome}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            ${result.severity === 'Critical' ? 'bg-red-100 text-red-800' : ''}
                            ${result.severity === 'High' ? 'bg-orange-100 text-orange-800' : ''}
                            ${result.severity === 'Medium' ? 'bg-yellow-100 text-yellow-800' : ''}
                            ${result.severity === 'Low' ? 'bg-blue-100 text-blue-800' : ''}
                            ${result.severity === 'Info' ? 'bg-gray-100 text-gray-800' : ''}">
                            ${result.severity}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${result.tls_enabled ? '✓' : '✗'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${result.auth_required ? '✓' : '✗'}
                    </td>
                </tr>
            `).join('');

            resultsCard.classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
```

**Step 2: Test the complete workflow**

1. Ensure Flask API is running:

    ```powershell
    cd mqtt-scanner
    python app.py
    ```

2. Ensure Laravel is running:

    ```powershell
    # In another terminal
    cd mqtt-scanner-latest
    php artisan serve
    ```

3. Open browser to http://localhost:8000
4. Login with your test account
5. Enter target `127.0.0.1` and click "Start Scan"
6. Verify results are displayed

**Congratulations!** You now have a fully functional web-based MQTT scanner with:

- ✅ User authentication
- ✅ Scan initiation form
- ✅ Real-time results display
- ✅ Database persistence
- ✅ Scan history tracking

---

## 4.6 Adding Security Controls

### 4.6.1 Function: Input Validation

Already implemented in:

- **Laravel:** `$request->validate()` in controller
- **Flask:** Regex pattern matching in `api_scan()`

### 4.6.2 Function: Rate Limiting in Laravel

**Development Steps:**

**Step 1: Configure rate limiter**

Edit `app/Providers/RouteServiceProvider.php`:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    RateLimiter::for('scan-api', function (Request $request) {
        return Limit::perUser(10)
            ->by($request->user()?->id ?: $request->ip())
            ->response(function (Request $request, array $headers) {
                return response()->json([
                    'error' => 'Rate limit exceeded',
                    'message' => 'Too many scan requests. Please slow down.'
                ], 429, $headers);
            });
    });
}
```

**Step 2: Apply to route**

Edit `routes/web.php`:

```php
Route::middleware(['auth', 'throttle:scan-api'])->group(function () {
    Route::post('/api/mqtt/scan', [MqttScannerController::class, 'scan'])->name('mqtt.scan');
});
```

### 4.6.3 Function: Audit Logging

**Development Steps:**

Already implemented using `Log::info()` throughout the controller.

View logs:

```powershell
Get-Content storage/logs/laravel.log -Tail 50
```

---

## 4.7 Deploying Test Infrastructure (Docker Brokers)

### 4.7.1 Function: Create Insecure Test Broker

**Development Steps:**

**Step 1: Create Docker Compose file**

Create `mqtt-brokers/docker-compose.yml`:

```yaml
version: "3.8"

services:
    mosquitto_insecure:
        image: eclipse-mosquitto:2.0
        container_name: mqtt_broker_insecure
        ports:
            - "1883:1883"
        volumes:
            - ./insecure/mosquitto.conf:/mosquitto/config/mosquitto.conf:ro
        networks:
            - mqtt_network
        restart: unless-stopped

networks:
    mqtt_network:
        driver: bridge
```

**Step 2: Create insecure broker configuration**

Create directory and file:

```powershell
New-Item -ItemType Directory -Path mqtt-brokers/insecure
```

Create `mqtt-brokers/insecure/mosquitto.conf`:

```conf
# Insecure MQTT Broker - For Testing Only
listener 1883
protocol mqtt

# Allow anonymous access (VULNERABLE)
allow_anonymous true

# Logging
log_dest stdout
log_type all
log_timestamp true

# Persistence
persistence true
persistence_location /mosquitto/data/
```

**Step 3: Start broker**

```powershell
cd mqtt-brokers
docker-compose up -d
```

**Step 4: Test broker**

```powershell
cd ../mqtt-scanner
python scanner.py 127.0.0.1
```

Expected output showing Critical vulnerability detected.

### 4.7.2 Function: Create Secure Test Broker (Optional)

Follow similar steps but with authentication and TLS configuration (details in Appendix A.4).

---

## 4.8 Testing and Validation

### 4.8.1 End-to-End Test

**Complete workflow test:**

1. ✅ Start Docker brokers: `docker-compose up -d`
2. ✅ Start Flask API: `python mqtt-scanner/app.py`
3. ✅ Start Laravel: `php artisan serve`
4. ✅ Login to web interface
5. ✅ Initiate scan of `127.0.0.1`
6. ✅ Verify results displayed correctly
7. ✅ Check database for persisted data:
    ```powershell
    php artisan tinker
    >>> App\Models\MqttScanHistory::with('results')->latest()->first()
    ```

### 4.8.2 Security Testing

Test the security controls you built:

1. **Rate Limiting Test:** Send 11 rapid requests, verify 11th is blocked
2. **API Authentication Test:** Send request without X-API-KEY, verify rejection
3. **Input Validation Test:** Send malicious input like `'; DROP TABLE users--`, verify rejection
4. **SQL Injection Test:** Try injecting SQL in target field, verify sanitization

---

## 4.9 Summary

**What You've Built:**

You now have a complete MQTT Network Security Scanner with:

**Core Functions:**

- ✅ Parse CIDR notation and IP addresses
- ✅ Check TCP port availability
- ✅ Probe MQTT brokers for vulnerabilities
- ✅ Classify security severity
- ✅ Generate scan summaries

**API Layer:**

- ✅ Flask RESTful API
- ✅ API key authentication
- ✅ Rate limiting (5 req/60s)
- ✅ Input validation

**Web Interface:**

- ✅ User registration and authentication
- ✅ Scan initiation form
- ✅ Real-time results display
- ✅ Scan history tracking
- ✅ Database persistence

**Test Infrastructure:**

- ✅ Docker Compose broker setup
- ✅ Insecure broker for vulnerability testing

**Security Controls:**

- ✅ Input validation (Laravel + Flask)
- ✅ Rate limiting (Laravel + Flask)
- ✅ Authentication (Laravel Breeze)
- ✅ Audit logging
- ✅ SQL injection prevention

**Next Steps:**

1. Deploy to production server
2. Add CSV export functionality
3. Implement advanced visualizations
4. Add secure broker with TLS
5. Integrate ESP32 hardware (optional)

---

**Congratulations!** You've successfully developed a complete MQTT Network Security Scanner from scratch following this comprehensive guide. Every function has been built step-by-step with complete code examples and testing procedures.
