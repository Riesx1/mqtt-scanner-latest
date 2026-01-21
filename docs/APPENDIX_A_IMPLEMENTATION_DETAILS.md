# APPENDIX A: IMPLEMENTATION DETAILS

This appendix provides comprehensive technical documentation supporting Chapter 4 Implementation, including detailed installation procedures, complete source code listings, configuration file templates, and extended technical specifications.

---

## A.1 Detailed Software Installation Procedures

### A.1.1 PHP and Composer Installation

**Windows Installation Steps:**

1. Download PHP 8.2+ from https://windows.php.net/download/
2. Extract to `C:\php` and add to system PATH
3. Verify installation:

```bash
php -v
# Expected output: PHP 8.2.x (cli)
```

4. Enable required extensions in `php.ini`:

```ini
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=mbstring
extension=fileinfo
extension=curl
extension=zip
```

5. Download Composer installer from https://getcomposer.org/
6. Run installer and verify:

```bash
composer --version
# Expected output: Composer version 2.x.x
```

### A.1.2 Node.js and NPM Installation

**Installation Steps:**

1. Download Node.js 20.x LTS from https://nodejs.org/
2. Run installer with default settings
3. Verify installation:

```bash
node --version
# Expected output: v20.x.x

npm --version
# Expected output: 10.x.x
```

4. Configure npm global prefix (optional):

```bash
npm config set prefix "C:\npm-global"
```

### A.1.3 Python Virtual Environment Setup

**Windows PowerShell Steps:**

1. Download Python 3.10+ from https://www.python.org/downloads/
2. Install with "Add Python to PATH" option enabled
3. Verify installation:

```powershell
python --version
# Expected output: Python 3.10.x or higher
```

4. Create project virtual environment:

```powershell
cd mqtt-scanner-latest
python -m venv .venv
.\.venv\Scripts\Activate.ps1
```

5. Install Python dependencies:

```powershell
pip install --upgrade pip
pip install -r mqtt-scanner/requirements.txt
```

### A.1.4 MySQL Installation and Configuration

**MySQL Server Setup:**

1. Download MySQL Community Server 8.0+ from https://dev.mysql.com/downloads/
2. Run installer and configure root password
3. Create application database:

```sql
CREATE DATABASE mqtt_scanner_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mqtt_user'@'localhost' IDENTIFIED BY 'SecurePassword123!';
GRANT ALL PRIVILEGES ON mqtt_scanner_db.* TO 'mqtt_user'@'localhost';
FLUSH PRIVILEGES;
```

4. Update Laravel `.env` configuration:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mqtt_scanner_db
DB_USERNAME=mqtt_user
DB_PASSWORD=SecurePassword123!
```

**SQLite Alternative (Development):**

For development environments, SQLite requires no installation:

```dotenv
DB_CONNECTION=sqlite
# DB_DATABASE will default to database/database.sqlite
```

Create SQLite database file:

```bash
touch database/database.sqlite
```

### A.1.5 Docker Desktop Installation

**Windows Installation with WSL 2:**

1. Download Docker Desktop from https://www.docker.com/products/docker-desktop/
2. Install with WSL 2 backend enabled
3. Verify installation:

```powershell
docker --version
# Expected output: Docker version 24.x.x

docker-compose --version
# Expected output: Docker Compose version 2.x.x
```

4. Test Docker functionality:

```bash
docker run hello-world
```

5. Enable Docker daemon auto-start (Settings → General → Start Docker Desktop when you log in)

### A.1.6 Git Installation and Configuration

**Installation Steps:**

1. Download Git from https://git-scm.com/downloads
2. Install with default settings
3. Configure global user identity:

```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

4. Verify configuration:

```bash
git config --list
```

5. Clone project repository:

```bash
git clone https://github.com/username/mqtt-scanner-latest.git
cd mqtt-scanner-latest
```

### A.1.7 Arduino IDE and ESP32 Board Support (Optional)

**Arduino IDE 2.x Installation:**

1. Download Arduino IDE from https://www.arduino.cc/en/software
2. Install and launch application
3. Add ESP32 board support:
    - Navigate to File → Preferences
    - Add to "Additional Board Manager URLs":
        ```
        https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
        ```
    - Open Tools → Board → Board Manager
    - Search "esp32" and install "esp32 by Espressif Systems"

4. Install required libraries:
    - Sketch → Include Library → Manage Libraries
    - Install: PubSubClient, DHT sensor library, WiFi (built-in)

5. Select board configuration:
    - Tools → Board → ESP32 Arduino → ESP32 Dev Module
    - Tools → Port → (Select your ESP32's COM port)

---

## A.2 Complete Dependency Manifests

### A.2.1 PHP Dependencies (composer.json)

```json
{
    "name": "laravel/laravel",
    "type": "project",
    "description": "MQTT Network Security Scanner - Laravel Application",
    "keywords": ["laravel", "mqtt", "security", "scanner"],
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/tinker": "^2.10",
        "php-mqtt/client": "^2.3",
        "guzzlehttp/guzzle": "^7.9"
    },
    "require-dev": {
        "fakerphp/faker": "^2.0",
        "laravel/pint": "^1.18",
        "laravel/sail": "^2.0",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.5",
        "phpunit/phpunit": "^11.5"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi",
            "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
            "@php artisan migrate --graceful --ansi"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

### A.2.2 Node.js Dependencies (package.json)

```json
{
    "private": true,
    "type": "module",
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "preview": "vite preview"
    },
    "devDependencies": {
        "@tailwindcss/vite": "^4.0.0",
        "autoprefixer": "^10.4.20",
        "axios": "^1.7.9",
        "laravel-vite-plugin": "^1.1.1",
        "postcss": "^8.4.49",
        "tailwindcss": "^4.0.0",
        "vite": "^7.0.5"
    }
}
```

### A.2.3 Python Dependencies (requirements.txt)

```txt
flask==3.1.0
paho-mqtt==1.6.1
python-dotenv==1.0.0
requests==2.32.3
werkzeug==3.1.3

# Development dependencies
pytest==8.3.4
pytest-cov==6.0.0
flake8==7.1.1
```

**Dependency Descriptions:**

| Package       | Version | Purpose                         |
| ------------- | ------- | ------------------------------- |
| flask         | 3.1.0   | RESTful API framework           |
| paho-mqtt     | 1.6.1   | MQTT protocol client library    |
| python-dotenv | 1.0.0   | Environment variable loading    |
| requests      | 2.32.3  | HTTP client for testing         |
| werkzeug      | 3.1.3   | Flask WSGI utility library      |
| pytest        | 8.3.4   | Unit testing framework          |
| pytest-cov    | 6.0.0   | Code coverage reporting         |
| flake8        | 7.1.1   | Code linting and style checking |

---

## A.3 Extended Code Listings

### A.3.1 Complete Scanning Engine (scanner.py)

```python
#!/usr/bin/env python3
"""
MQTT Network Security Scanner - Core Scanning Engine
Version: 2.4
Description: Protocol-aware MQTT broker discovery and vulnerability assessment
"""

import socket
import ssl
import ipaddress
import paho.mqtt.client as mqtt_client
from typing import List, Dict, Optional
import logging
import time

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# MQTT Standard Ports
MQTT_PORTS = [1883, 8883]
DEFAULT_TIMEOUT = 10  # seconds

class MQTTScanner:
    """Main scanner class handling broker discovery and assessment"""

    def __init__(self):
        self.results = []
        self.scan_start_time = None
        self.scan_end_time = None

    def parse_target(self, target: str) -> List[str]:
        """
        Parse target specification into list of IP addresses
        Supports: single IP, CIDR notation, IP ranges
        """
        try:
            # CIDR notation (e.g., 192.168.1.0/24)
            if '/' in target:
                network = ipaddress.ip_network(target, strict=False)
                return [str(ip) for ip in network.hosts()]

            # Single IP address
            ipaddress.ip_address(target)
            return [target]

        except ValueError as e:
            logger.error(f"Invalid target format: {target} - {e}")
            return []

    def check_tcp_port(self, host: str, port: int, timeout: int = 3) -> bool:
        """
        Check if TCP port is open using socket connection
        Returns True if port is open, False otherwise
        """
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout)

        try:
            result = sock.connect_ex((host, port))
            sock.close()
            return result == 0
        except socket.error as e:
            logger.debug(f"Socket error checking {host}:{port} - {e}")
            return False

    def probe_mqtt_broker(self, host: str, port: int) -> Dict:
        """
        Probe MQTT broker with multiple authentication scenarios
        Returns comprehensive security assessment
        """
        result = {
            'ip_address': host,
            'port': port,
            'protocol': 'mqtts' if port == 8883 else 'mqtt',
            'timestamp': time.strftime('%Y-%m-%d %H:%M:%S'),
            'outcome': 'Unknown',
            'severity': 'Info',
            'details': '',
            'tls_enabled': False,
            'tls_version': None,
            'auth_required': False
        }

        # Test TLS configuration for secure port
        if port == 8883:
            result['tls_enabled'] = True
            tls_info = self._check_tls_certificate(host, port)
            result.update(tls_info)

        # Test anonymous access
        anonymous_result = self._test_connection(host, port, anonymous=True)

        if anonymous_result['success']:
            result['outcome'] = 'Anonymous Access Allowed'
            result['severity'] = 'Critical'
            result['details'] = 'Broker allows unauthenticated connections. Immediate remediation required.'
            result['auth_required'] = False
            return result

        # Test with authentication
        auth_result = self._test_connection(host, port, anonymous=False,
                                           username='test_user', password='test_pass')

        if 'not authorized' in auth_result['error'].lower() or 'bad username' in auth_result['error'].lower():
            result['outcome'] = 'Authentication Required'
            result['severity'] = 'Medium'
            result['details'] = 'Broker enforces authentication. Verify strong password policies.'
            result['auth_required'] = True
        elif 'connection refused' in auth_result['error'].lower():
            result['outcome'] = 'Connection Refused'
            result['severity'] = 'Low'
            result['details'] = 'Broker actively refused connection. May have firewall or ACL rules.'
        else:
            result['outcome'] = 'Connection Failed'
            result['severity'] = 'Info'
            result['details'] = f'Unable to establish MQTT connection: {auth_result["error"]}'

        return result

    def _test_connection(self, host: str, port: int, anonymous: bool = True,
                        username: Optional[str] = None, password: Optional[str] = None) -> Dict:
        """
        Attempt MQTT connection with specified credentials
        Returns connection result with success status and error message
        """
        client_id = f"scanner_{int(time.time())}"
        client = mqtt_client.Client(client_id=client_id, protocol=mqtt_client.MQTTv311)

        connection_result = {'success': False, 'error': ''}

        # Configure TLS for secure connections
        if port == 8883:
            try:
                client.tls_set(cert_reqs=ssl.CERT_NONE)
                client.tls_insecure_set(True)
            except Exception as e:
                connection_result['error'] = f"TLS configuration failed: {str(e)}"
                return connection_result

        # Set credentials if not anonymous
        if not anonymous and username and password:
            client.username_pw_set(username, password)

        # Connection callback handlers
        def on_connect(client, userdata, flags, rc, properties=None):
            if rc == 0:
                connection_result['success'] = True
            else:
                error_messages = {
                    1: "Connection refused - incorrect protocol version",
                    2: "Connection refused - invalid client identifier",
                    3: "Connection refused - server unavailable",
                    4: "Connection refused - bad username or password",
                    5: "Connection refused - not authorized"
                }
                connection_result['error'] = error_messages.get(rc, f"Connection failed with code {rc}")

        client.on_connect = on_connect

        # Attempt connection
        try:
            client.connect(host, port, keepalive=DEFAULT_TIMEOUT)
            client.loop_start()
            time.sleep(2)  # Wait for connection callback
            client.loop_stop()
            client.disconnect()
        except Exception as e:
            connection_result['error'] = str(e)

        return connection_result

    def _check_tls_certificate(self, host: str, port: int) -> Dict:
        """
        Inspect TLS certificate details for secure connections
        Returns certificate information including issuer, validity, version
        """
        tls_info = {
            'tls_version': None,
            'cert_issuer': None,
            'cert_subject': None,
            'cert_valid_from': None,
            'cert_valid_to': None
        }

        context = ssl.create_default_context()
        context.check_hostname = False
        context.verify_mode = ssl.CERT_NONE

        try:
            with socket.create_connection((host, port), timeout=5) as sock:
                with context.wrap_socket(sock, server_hostname=host) as ssock:
                    tls_info['tls_version'] = ssock.version()
                    cert = ssock.getpeercert()

                    if cert:
                        tls_info['cert_subject'] = dict(x[0] for x in cert.get('subject', []))
                        tls_info['cert_issuer'] = dict(x[0] for x in cert.get('issuer', []))
                        tls_info['cert_valid_from'] = cert.get('notBefore')
                        tls_info['cert_valid_to'] = cert.get('notAfter')

        except Exception as e:
            logger.debug(f"TLS certificate inspection failed for {host}:{port} - {e}")

        return tls_info

    def scan(self, target: str) -> List[Dict]:
        """
        Execute full scan workflow on target specification
        Returns list of scan results for all discovered brokers
        """
        logger.info(f"Starting scan of target: {target}")
        self.scan_start_time = time.time()
        self.results = []

        # Parse target into IP list
        ip_addresses = self.parse_target(target)
        logger.info(f"Resolved {len(ip_addresses)} IP addresses from target")

        # Scan each IP address
        for ip in ip_addresses:
            for port in MQTT_PORTS:
                logger.debug(f"Scanning {ip}:{port}")

                # Check TCP port availability
                if not self.check_tcp_port(ip, port):
                    logger.debug(f"Port {port} closed on {ip}")
                    continue

                # Probe MQTT broker
                try:
                    result = self.probe_mqtt_broker(ip, port)
                    self.results.append(result)
                    logger.info(f"Found broker at {ip}:{port} - {result['outcome']}")
                except Exception as e:
                    logger.error(f"Error probing {ip}:{port} - {e}")

        self.scan_end_time = time.time()
        scan_duration = self.scan_end_time - self.scan_start_time
        logger.info(f"Scan completed in {scan_duration:.2f} seconds. Found {len(self.results)} brokers.")

        return self.results

    def get_summary_statistics(self) -> Dict:
        """Calculate scan summary statistics"""
        total_brokers = len(self.results)
        severity_counts = {
            'Critical': sum(1 for r in self.results if r['severity'] == 'Critical'),
            'High': sum(1 for r in self.results if r['severity'] == 'High'),
            'Medium': sum(1 for r in self.results if r['severity'] == 'Medium'),
            'Low': sum(1 for r in self.results if r['severity'] == 'Low'),
            'Info': sum(1 for r in self.results if r['severity'] == 'Info')
        }

        return {
            'total_brokers_found': total_brokers,
            'severity_counts': severity_counts,
            'scan_duration_seconds': self.scan_end_time - self.scan_start_time if self.scan_end_time else 0
        }

def run_scan(target: str) -> Dict:
    """
    Main entry point for scanning operations
    Called by Flask API
    """
    scanner = MQTTScanner()
    results = scanner.scan(target)
    summary = scanner.get_summary_statistics()

    return {
        'success': True,
        'target': target,
        'results': results,
        'summary': summary
    }

if __name__ == '__main__':
    # CLI execution for testing
    import sys

    if len(sys.argv) < 2:
        print("Usage: python scanner.py <target>")
        print("Example: python scanner.py 192.168.1.0/24")
        sys.exit(1)

    target = sys.argv[1]
    scan_results = run_scan(target)

    print(f"\n=== Scan Results ===")
    print(f"Target: {scan_results['target']}")
    print(f"Brokers Found: {scan_results['summary']['total_brokers_found']}")
    print(f"\nSeverity Breakdown:")
    for severity, count in scan_results['summary']['severity_counts'].items():
        if count > 0:
            print(f"  {severity}: {count}")

    print(f"\nDetailed Results:")
    for result in scan_results['results']:
        print(f"\n  {result['ip_address']}:{result['port']}")
        print(f"    Outcome: {result['outcome']}")
        print(f"    Severity: {result['severity']}")
        print(f"    Details: {result['details']}")
```

### A.3.2 Complete Flask API Application (app.py)

```python
#!/usr/bin/env python3
"""
MQTT Scanner - Flask API Server
Provides RESTful middleware between Laravel and Python scanning engine
"""

from flask import Flask, request, jsonify
from scanner import run_scan
import os
import logging
import time

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Initialize Flask application
app = Flask(__name__)

# Load configuration from environment
FLASK_API_KEY = os.environ.get('FLASK_API_KEY', 'REPLACE_WITH_SECURE_KEY')
FLASK_HOST = os.environ.get('FLASK_HOST', '127.0.0.1')
FLASK_PORT = int(os.environ.get('FLASK_PORT', 5000))
FLASK_DEBUG = os.environ.get('FLASK_DEBUG', 'False').lower() == 'true'

# Rate limiting storage (simple in-memory implementation)
rate_limit_storage = {}
RATE_LIMIT_REQUESTS = 5
RATE_LIMIT_WINDOW = 60  # seconds

def check_rate_limit(client_ip: str) -> bool:
    """
    Simple sliding window rate limiter
    Returns True if request should be allowed, False if rate limit exceeded
    """
    current_time = time.time()

    if client_ip not in rate_limit_storage:
        rate_limit_storage[client_ip] = []

    # Remove requests older than time window
    rate_limit_storage[client_ip] = [
        req_time for req_time in rate_limit_storage[client_ip]
        if current_time - req_time < RATE_LIMIT_WINDOW
    ]

    # Check if limit exceeded
    if len(rate_limit_storage[client_ip]) >= RATE_LIMIT_REQUESTS:
        return False

    # Record this request
    rate_limit_storage[client_ip].append(current_time)
    return True

def require_api_key(f):
    """Decorator to enforce API key authentication"""
    def decorated_function(*args, **kwargs):
        api_key = request.headers.get('X-API-KEY')

        if not api_key:
            logger.warning(f"API request without key from {request.remote_addr}")
            return jsonify({'error': 'Missing X-API-KEY header'}), 401

        if api_key != FLASK_API_KEY:
            logger.warning(f"Invalid API key from {request.remote_addr}")
            return jsonify({'error': 'Invalid API key'}), 401

        return f(*args, **kwargs)

    decorated_function.__name__ = f.__name__
    return decorated_function

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint for monitoring"""
    return jsonify({
        'status': 'healthy',
        'service': 'mqtt-scanner-api',
        'version': '2.4',
        'timestamp': time.strftime('%Y-%m-%d %H:%M:%S')
    }), 200

@app.route('/api/scan', methods=['POST'])
@require_api_key
def api_scan():
    """
    Main scanning endpoint
    Accepts JSON payload with 'target' parameter
    Returns scan results in JSON format
    """
    # Rate limiting
    client_ip = request.remote_addr
    if not check_rate_limit(client_ip):
        logger.warning(f"Rate limit exceeded for {client_ip}")
        return jsonify({'error': 'Rate limit exceeded. Try again later.'}), 429

    # Validate request content type
    if not request.is_json:
        return jsonify({'error': 'Content-Type must be application/json'}), 400

    # Parse request data
    data = request.get_json()
    target = data.get('target')

    # Validate target parameter
    if not target:
        return jsonify({'error': 'Missing required parameter: target'}), 400

    if not isinstance(target, str):
        return jsonify({'error': 'Target must be a string'}), 400

    if len(target) > 100:
        return jsonify({'error': 'Target parameter too long (max 100 characters)'}), 400

    # Input sanitization - allow only IP addresses, CIDR, dots, slashes
    import re
    if not re.match(r'^[0-9\.\/:a-zA-Z\-]+$', target):
        return jsonify({'error': 'Invalid target format'}), 400

    # Execute scan
    try:
        logger.info(f"Starting scan for target: {target} (requested by {client_ip})")
        scan_results = run_scan(target)
        logger.info(f"Scan completed for {target}. Found {len(scan_results['results'])} brokers.")

        return jsonify(scan_results), 200

    except Exception as e:
        logger.error(f"Scan failed for target {target}: {str(e)}")
        return jsonify({
            'error': 'Scan execution failed',
            'message': str(e)
        }), 500

@app.errorhandler(404)
def not_found(error):
    """Handle 404 errors"""
    return jsonify({'error': 'Endpoint not found'}), 404

@app.errorhandler(500)
def internal_error(error):
    """Handle 500 errors"""
    logger.error(f"Internal server error: {error}")
    return jsonify({'error': 'Internal server error'}), 500

if __name__ == '__main__':
    logger.info(f"Starting Flask API server on {FLASK_HOST}:{FLASK_PORT}")
    logger.info(f"Debug mode: {FLASK_DEBUG}")

    if FLASK_API_KEY == 'REPLACE_WITH_SECURE_KEY':
        logger.warning("WARNING: Using default API key. Set FLASK_API_KEY environment variable!")

    app.run(host=FLASK_HOST, port=FLASK_PORT, debug=FLASK_DEBUG)
```

### A.3.3 Laravel Controller Implementation (MqttScannerController.php)

```php
<?php

namespace App\Http\Controllers;

use App\Models\MqttScanHistory;
use App\Models\MqttScanResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\JsonResponse;

class MqttScannerController extends Controller
{
    /**
     * Display the main dashboard
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
     * Execute MQTT scan via Flask API
     */
    public function scan(Request $request): JsonResponse
    {
        // Rate limiting
        $userId = Auth::id();
        $rateLimitKey = "scan-user:{$userId}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'error' => "Too many scan requests. Please wait {$seconds} seconds."
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

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
            'user_id' => $userId,
            'target' => $validated['target'],
            'started_at' => now(),
            'status' => 'running',
            'total_brokers_found' => 0,
            'vulnerable_count' => 0
        ]);

        Log::info("User {$userId} initiated scan", [
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

            Log::info("Scan {$scanHistory->id} completed successfully", [
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

            Log::error("Scan {$scanHistory->id} failed", [
                'error' => $e->getMessage(),
                'target' => $validated['target']
            ]);

            return response()->json([
                'error' => 'Scan failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get scan history for authenticated user
     */
    public function history(): JsonResponse
    {
        $scans = MqttScanHistory::where('user_id', Auth::id())
            ->with('results')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($scans);
    }

    /**
     * Get details for specific scan
     */
    public function show($id): JsonResponse
    {
        $scan = MqttScanHistory::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('results')
            ->firstOrFail();

        return response()->json($scan);
    }

    /**
     * Export scan results as CSV
     */
    public function export($id)
    {
        $scan = MqttScanHistory::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('results')
            ->firstOrFail();

        $filename = "mqtt_scan_{$scan->id}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ];

        $callback = function() use ($scan) {
            $file = fopen('php://output', 'w');

            // CSV header
            fputcsv($file, [
                'IP Address', 'Port', 'Outcome', 'Severity',
                'TLS Enabled', 'Auth Required', 'Details', 'Timestamp'
            ]);

            // CSV rows
            foreach ($scan->results as $result) {
                fputcsv($file, [
                    $result->ip_address,
                    $result->port,
                    $result->outcome,
                    $result->severity,
                    $result->tls_enabled ? 'Yes' : 'No',
                    $result->auth_required ? 'Yes' : 'No',
                    $result->details,
                    $result->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
```

---

## A.4 Configuration File Templates

### A.4.1 Docker Compose Broker Configuration (docker-compose.yml)

```yaml
version: "3.8"

services:
    # Insecure MQTT Broker (Port 1883, No Authentication)
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
        healthcheck:
            test:
                ["CMD", "mosquitto_sub", "-t", "$$SYS/#", "-C", "1", "-W", "1"]
            interval: 30s
            timeout: 10s
            retries: 3

    # Secure MQTT Broker (Port 8883, TLS + Authentication)
    mosquitto_secure:
        image: eclipse-mosquitto:2.0
        container_name: mqtt_broker_secure
        ports:
            - "8883:8883"
        volumes:
            - ./secure/mosquitto.conf:/mosquitto/config/mosquitto.conf:ro
            - ./secure/certs:/mosquitto/certs:ro
            - ./secure/passwords/passwordfile:/mosquitto/config/passwordfile:ro
        networks:
            - mqtt_network
        restart: unless-stopped
        healthcheck:
            test:
                [
                    "CMD",
                    "mosquitto_sub",
                    "-t",
                    "$$SYS/#",
                    "-C",
                    "1",
                    "-W",
                    "1",
                    "-p",
                    "8883",
                    "--cafile",
                    "/mosquitto/certs/ca.crt",
                    "-u",
                    "admin",
                    "-P",
                    "SecurePassword123!",
                ]
            interval: 30s
            timeout: 10s
            retries: 3

networks:
    mqtt_network:
        driver: bridge
```

### A.4.2 Insecure Mosquitto Configuration (insecure/mosquitto.conf)

```conf
# Insecure MQTT Broker Configuration
# WARNING: This configuration is intentionally vulnerable for testing purposes
# DO NOT use in production environments

listener 1883
protocol mqtt

# Allow anonymous connections (VULNERABLE)
allow_anonymous true

# No authentication required
password_file

# Disable TLS
cafile
certfile
keyfile

# Persistence settings
persistence true
persistence_location /mosquitto/data/

# Logging
log_dest stdout
log_type all
log_timestamp true

# Connection limits
max_connections -1
max_queued_messages 100

# System topics
sys_interval 10
```

### A.4.3 Secure Mosquitto Configuration (secure/mosquitto.conf)

```conf
# Secure MQTT Broker Configuration
# Production-ready configuration with TLS and authentication

listener 8883
protocol mqtt

# Require authentication
allow_anonymous false
password_file /mosquitto/config/passwordfile

# TLS Configuration
cafile /mosquitto/certs/ca.crt
certfile /mosquitto/certs/server.crt
keyfile /mosquitto/certs/server.key

# TLS Version Requirements
tls_version tlsv1.2

# Require client certificate verification (optional - comment out for server-only TLS)
# require_certificate true

# Persistence settings
persistence true
persistence_location /mosquitto/data/

# Logging
log_dest stdout
log_type error
log_type warning
log_type notice
log_type information
log_timestamp true

# Connection limits
max_connections 1000
max_queued_messages 100

# System topics
sys_interval 10

# Access Control (Optional - create acl_file for topic-level permissions)
# acl_file /mosquitto/config/acl.conf
```

### A.4.4 TLS Certificate Generation Procedure

**Step 1: Generate CA Certificate**

```bash
cd mqtt-brokers/secure/certs

# Generate CA private key
openssl genrsa -out ca.key 2048

# Generate CA certificate
openssl req -new -x509 -days 3650 -key ca.key -out ca.crt \
    -subj "/C=US/ST=State/L=City/O=Organization/OU=IT/CN=MQTT-CA"
```

**Step 2: Generate Server Certificate**

```bash
# Generate server private key
openssl genrsa -out server.key 2048

# Generate certificate signing request
openssl req -new -key server.key -out server.csr \
    -subj "/C=US/ST=State/L=City/O=Organization/OU=IT/CN=localhost"

# Sign server certificate with CA
openssl x509 -req -in server.csr -CA ca.crt -CAkey ca.key \
    -CAcreateserial -out server.crt -days 365
```

**Step 3: Generate Client Certificate (Optional)**

```bash
# Generate client private key
openssl genrsa -out client.key 2048

# Generate client certificate signing request
openssl req -new -key client.key -out client.csr \
    -subj "/C=US/ST=State/L=City/O=Organization/OU=IT/CN=mqtt-client"

# Sign client certificate with CA
openssl x509 -req -in client.csr -CA ca.crt -CAkey ca.key \
    -CAcreateserial -out client.crt -days 365
```

**Step 4: Set Permissions**

```bash
chmod 644 ca.crt server.crt
chmod 600 ca.key server.key
```

### A.4.5 Password File Creation

```bash
# Create directory
mkdir -p mqtt-brokers/secure/passwords

# Add user with password
mosquitto_passwd -c mqtt-brokers/secure/passwords/passwordfile admin

# Prompt will appear: Enter password: SecurePassword123!

# Add additional users (without -c flag to append)
mosquitto_passwd mqtt-brokers/secure/passwords/passwordfile testuser
```

---

## A.5 ESP32 Firmware Complete Listing (esp32_mixed_security.ino)

```cpp
/**
 * ESP32 Mixed Security MQTT Publisher
 *
 * Description: Dual MQTT connections demonstrating secure and insecure configurations
 * Hardware: ESP32 Dev Board, DHT11, LDR, PIR sensors
 * Version: 2.2
 */

#include <WiFi.h>
#include <PubSubClient.h>
#include <WiFiClientSecure.h>
#include <DHT.h>

// WiFi Configuration
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// MQTT Broker Configuration
const char* mqtt_server = "192.168.1.100";  // Replace with your broker IP
const int mqtt_port_insecure = 1883;
const int mqtt_port_secure = 8883;

// MQTT Authentication (for secure connection)
const char* mqtt_username = "testuser";
const char* mqtt_password = "TestPassword123!";

// Sensor Pin Definitions
#define DHT_PIN 4        // DHT11 data pin
#define LDR_PIN 34       // LDR analog pin
#define PIR_PIN 27       // PIR digital pin
#define DHT_TYPE DHT11   // DHT sensor type

// Initialize sensors
DHT dht(DHT_PIN, DHT_TYPE);

// MQTT Clients
WiFiClient insecureClient;
WiFiClientSecure secureClient;
PubSubClient mqttInsecure(insecureClient);
PubSubClient mqttSecure(secureClient);

// Timing variables
unsigned long lastSecurePublish = 0;
unsigned long lastInsecurePublish = 0;
const long secureInterval = 5000;    // 5 seconds
const long insecureInterval = 2000;  // 2 seconds

// TLS Certificate (CA Certificate - replace with your actual certificate)
const char* ca_cert = R"EOF(
-----BEGIN CERTIFICATE-----
MIIDXTCCAkWgAwIBAgIJAKL8lE9rZZUvMA0GCSqGSIb3DQEBCwUAMEUxCzAJBgNV
BAYTAlVTMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBX
aWRnaXRzIFB0eSBMdGQwHhcNMjQwMTAxMDAwMDAwWhcNMzQwMTAxMDAwMDAwWjBF
MQswCQYDVQQGEwJVUzETMBEGA1UECAwKU29tZS1TdGF0ZTEhMB8GA1UECgwYSW50
ZXJuZXQgV2lkZ2l0cyBQdHkgTHRkMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIB
CgKCAQEAw8GRX0qkECv4m3YJbJzWBJPqO2k1CZc3ybU8xD7YkXQPwXGVZvPMr4Tl
KA5LJm7wLVXkGqN7gH8sSr8GS9qOqGw6JlpG3kqBJKs5Xa4HqT4rQ+KvYxVq1m8K
TpqvBD5GGqHXqGk0j7yJpQWGX4vKqJHKvQXvN8YvZsP4TmH8LgGqXJ4VrRkNqJqB
kLGXq0KtQvXsJGvGX4pMH8VvJGqL8GsPGvXqJ0KkTqGqBJLXsVqGHkpMq8VpJGLV
X4Gs8KpJvGH4Lq8VsJXqG0KvTsGqBJLXsVqGHkpMq8VpJGLVX4Gs8KpJvGH4Lq8V
sJXqG0KvTsGqBJLXsVqGHkpMq8VpJGLVX4Gs8KpJvGH4Lq8VsJXqG0KvTsGqBJLX
sQIDAQABo1AwTjAdBgNVHQ4EFgQU8jXvVqGHkpMq8VpJGLVX4Gs8KpIwHwYDVR0j
BBgwFoAU8jXvVqGHkpMq8VpJGLVX4Gs8KpIwDAYDVR0TBAUwAwEB/zANBgkqhkiG
9w0BAQsFAAOCAQEAq8VsJXqG0KvTsGqBJLXsVqGHkpMq8VpJGLVX4Gs8KpJvGH4L
q8VsJXqG0KvTsGqBJLXsVqGHkpMq8VpJGLVX4Gs8KpJvGH4Lq8VsJXqG0KvTsGqB
-----END CERTIFICATE-----
)EOF";

void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println("\n=== ESP32 Mixed Security MQTT Publisher ===");

  // Initialize sensors
  dht.begin();
  pinMode(PIR_PIN, INPUT);
  pinMode(LDR_PIN, INPUT);

  // Connect to WiFi
  connectWiFi();

  // Configure MQTT brokers
  mqttInsecure.setServer(mqtt_server, mqtt_port_insecure);
  mqttSecure.setServer(mqtt_server, mqtt_port_secure);

  // Configure TLS for secure connection
  secureClient.setCACert(ca_cert);
  // Uncomment next line to skip certificate verification (for testing only)
  // secureClient.setInsecure();

  Serial.println("Setup completed successfully!");
}

void loop() {
  // Ensure WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi disconnected. Reconnecting...");
    connectWiFi();
  }

  // Maintain MQTT connections
  if (!mqttInsecure.connected()) {
    reconnectInsecure();
  }
  if (!mqttSecure.connected()) {
    reconnectSecure();
  }

  mqttInsecure.loop();
  mqttSecure.loop();

  // Publish to secure broker (DHT11 + LDR data)
  unsigned long now = millis();
  if (now - lastSecurePublish >= secureInterval) {
    lastSecurePublish = now;
    publishSecureData();
  }

  // Publish to insecure broker (PIR motion data)
  if (now - lastInsecurePublish >= insecureInterval) {
    lastInsecurePublish = now;
    publishInsecureData();
  }
}

void connectWiFi() {
  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi connected!");
    Serial.print("IP address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nWiFi connection failed!");
    delay(5000);
    ESP.restart();
  }
}

void reconnectInsecure() {
  Serial.print("Connecting to INSECURE MQTT broker (");
  Serial.print(mqtt_server);
  Serial.print(":");
  Serial.print(mqtt_port_insecure);
  Serial.println(")...");

  String clientId = "ESP32_Insecure_" + String(random(0xffff), HEX);

  // Connect WITHOUT authentication
  if (mqttInsecure.connect(clientId.c_str())) {
    Serial.println("Connected to insecure broker (anonymous)");
  } else {
    Serial.print("Connection failed, rc=");
    Serial.println(mqttInsecure.state());
    delay(5000);
  }
}

void reconnectSecure() {
  Serial.print("Connecting to SECURE MQTT broker (");
  Serial.print(mqtt_server);
  Serial.print(":");
  Serial.print(mqtt_port_secure);
  Serial.println(")...");

  String clientId = "ESP32_Secure_" + String(random(0xffff), HEX);

  // Connect WITH authentication and TLS
  if (mqttSecure.connect(clientId.c_str(), mqtt_username, mqtt_password)) {
    Serial.println("Connected to secure broker (authenticated + TLS)");
  } else {
    Serial.print("Connection failed, rc=");
    Serial.println(mqttSecure.state());
    Serial.println("Possible causes:");
    Serial.println("  - Invalid credentials");
    Serial.println("  - Certificate mismatch");
    Serial.println("  - Broker unreachable");
    delay(5000);
  }
}

void publishSecureData() {
  // Read DHT11 sensor
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();

  // Read LDR (light sensor)
  int lightLevel = analogRead(LDR_PIN);
  float lightPercentage = (lightLevel / 4095.0) * 100.0;

  // Check if readings are valid
  if (isnan(temperature) || isnan(humidity)) {
    Serial.println("Failed to read from DHT sensor!");
    return;
  }

  // Create JSON payload
  String payload = "{";
  payload += "\"temperature\":" + String(temperature, 1) + ",";
  payload += "\"humidity\":" + String(humidity, 1) + ",";
  payload += "\"light\":" + String(lightPercentage, 1) + ",";
  payload += "\"timestamp\":" + String(millis());
  payload += "}";

  // Publish to secure broker
  if (mqttSecure.connected()) {
    bool success = mqttSecure.publish("esp32/secure/sensors", payload.c_str());

    if (success) {
      Serial.println("[SECURE] Published: " + payload);
    } else {
      Serial.println("[SECURE] Publish failed!");
    }
  }
}

void publishInsecureData() {
  // Read PIR sensor
  int motionDetected = digitalRead(PIR_PIN);

  // Create JSON payload
  String payload = "{";
  payload += "\"motion\":" + String(motionDetected) + ",";
  payload += "\"timestamp\":" + String(millis());
  payload += "}";

  // Publish to insecure broker
  if (mqttInsecure.connected()) {
    bool success = mqttInsecure.publish("esp32/insecure/motion", payload.c_str());

    if (success) {
      Serial.println("[INSECURE] Published: " + payload);
    } else {
      Serial.println("[INSECURE] Publish failed!");
    }
  }
}
```

**Required Arduino Libraries:**

- WiFi (built-in)
- PubSubClient (install via Library Manager)
- DHT sensor library (install via Library Manager)

**Configuration Steps:**

1. Replace `YOUR_WIFI_SSID` and `YOUR_WIFI_PASSWORD` with your network credentials
2. Update `mqtt_server` with your MQTT broker IP address
3. Replace `ca_cert` with your actual CA certificate (from broker setup)
4. Update `mqtt_username` and `mqtt_password` to match broker authentication
5. Select board: Tools → Board → ESP32 Arduino → ESP32 Dev Module
6. Upload firmware to ESP32

---

## A.6 Security Control Implementation Details

### A.6.1 Input Validation Implementation

**Laravel Validation Rules:**

```php
// MqttScannerController.php - scan() method
$validated = $request->validate([
    'target' => [
        'required',               // Field must be present
        'string',                 // Must be string type
        'max:100',                // Maximum 100 characters
        'regex:/^[0-9\.\/:a-zA-Z\-]+$/'  // Alphanumeric, dots, slashes, colons, hyphens only
    ]
]);
```

**Python Flask Validation:**

```python
# app.py - api_scan() endpoint
import re

def validate_target(target: str) -> bool:
    """
    Validate target format to prevent injection attacks
    Returns True if valid, False otherwise
    """
    # Length check
    if len(target) > 100:
        return False

    # Pattern check - allow only safe characters
    pattern = r'^[0-9\.\/:a-zA-Z\-]+$'
    if not re.match(pattern, target):
        return False

    # Additional CIDR validation
    if '/' in target:
        try:
            import ipaddress
            ipaddress.ip_network(target, strict=False)
            return True
        except ValueError:
            return False

    return True
```

### A.6.2 Rate Limiting Implementation

**Laravel Rate Limiting (RouteServiceProvider):**

```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    RateLimiter::for('scan-api', function (Request $request) {
        return Limit::perUser(10)
            ->by($request->user()?->id ?: $request->ip())
            ->response(function (Request $request, array $headers) {
                return response()->json([
                    'error' => 'Rate limit exceeded. Please slow down.'
                ], 429, $headers);
            });
    });
}

// Apply to route: routes/web.php
Route::middleware(['auth', 'throttle:scan-api'])
    ->post('/api/mqtt/scan', [MqttScannerController::class, 'scan']);
```

**Flask Sliding Window Rate Limiting:**

```python
# app.py - Detailed implementation
import time
from collections import defaultdict

class SlidingWindowRateLimiter:
    def __init__(self, max_requests: int, window_seconds: int):
        self.max_requests = max_requests
        self.window_seconds = window_seconds
        self.requests = defaultdict(list)

    def is_allowed(self, client_id: str) -> bool:
        """
        Check if request is allowed under sliding window policy
        Returns True if allowed, False if rate limit exceeded
        """
        current_time = time.time()

        # Remove expired requests
        self.requests[client_id] = [
            req_time for req_time in self.requests[client_id]
            if current_time - req_time < self.window_seconds
        ]

        # Check limit
        if len(self.requests[client_id]) >= self.max_requests:
            return False

        # Record this request
        self.requests[client_id].append(current_time)
        return True

    def get_remaining(self, client_id: str) -> int:
        """Get number of remaining requests allowed"""
        return max(0, self.max_requests - len(self.requests[client_id]))

# Initialize limiter
rate_limiter = SlidingWindowRateLimiter(max_requests=5, window_seconds=60)

# Apply to endpoint
@app.route('/api/scan', methods=['POST'])
@require_api_key
def api_scan():
    client_ip = request.remote_addr

    if not rate_limiter.is_allowed(client_ip):
        return jsonify({
            'error': 'Rate limit exceeded',
            'retry_after': 60
        }), 429

    # ... scan logic ...
```

### A.6.3 Audit Logging Configuration

**Laravel Logging Configuration (config/logging.php):**

```php
'channels' => [
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'info',
        'days' => 90,  // Retain 90 days of logs
        'formatter' => env('LOG_SECURITY_FORMATTER', \Monolog\Formatter\JsonFormatter::class),
    ],

    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'info',
        'days' => 365,  // Retain 1 year for compliance
    ],
],
```

**Audit Logging Implementation:**

```php
// app/Services/AuditLogger.php
namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function logScanEvent(string $action, array $data = [])
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data
        ];

        Log::channel('audit')->info($action, $logData);
    }
}

// Usage in controller
use App\Services\AuditLogger;

AuditLogger::logScanEvent('scan_initiated', [
    'target' => $validated['target'],
    'scan_id' => $scanHistory->id
]);

AuditLogger::logScanEvent('scan_completed', [
    'scan_id' => $scanHistory->id,
    'brokers_found' => count($scanData['results']),
    'vulnerable_count' => $vulnerableCount
]);
```

### A.6.4 Credential Encryption

**Laravel Eloquent Model with Encrypted Casting:**

```php
// app/Models/MqttCredential.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MqttCredential extends Model
{
    protected $fillable = [
        'user_id',
        'broker_name',
        'username',
        'password',  // Will be encrypted
        'api_key'    // Will be encrypted
    ];

    // Automatic encryption/decryption
    protected $casts = [
        'password' => 'encrypted',
        'api_key' => 'encrypted'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Usage example
$credential = MqttCredential::create([
    'user_id' => Auth::id(),
    'broker_name' => 'Production Broker',
    'username' => 'admin',
    'password' => 'SecurePassword123!',  // Automatically encrypted in database
    'api_key' => 'abc123def456'          // Automatically encrypted in database
]);

// Retrieval - automatically decrypted
$plainPassword = $credential->password;  // "SecurePassword123!"
```

**Database Migration for Encrypted Fields:**

```php
// database/migrations/2024_01_01_000000_create_mqtt_credentials_table.php
public function up()
{
    Schema::create('mqtt_credentials', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('broker_name');
        $table->string('username');
        $table->text('password');  // Encrypted - use TEXT for encrypted data
        $table->text('api_key')->nullable();  // Encrypted
        $table->timestamps();

        $table->index('user_id');
    });
}
```

### A.6.5 CSRF Protection and Session Security

**CSRF Token Configuration:**

```php
// config/session.php
return [
    'lifetime' => 120,  // Session lifetime in minutes
    'expire_on_close' => false,
    'encrypt' => true,  // Encrypt session data
    'http_only' => true,  // Prevent JavaScript access to session cookie
    'same_site' => 'lax',  // CSRF protection
    'secure' => env('SESSION_SECURE_COOKIE', true),  // HTTPS only in production
];
```

**Blade Template CSRF Token:**

```html
<!-- resources/views/dashboard.blade.php -->
<form id="scanForm">
    @csrf
    <!-- Automatic CSRF token injection -->

    <input type="text" name="target" placeholder="Enter target IP or CIDR" />
    <button type="submit">Start Scan</button>
</form>

<script>
    document
        .getElementById("scanForm")
        .addEventListener("submit", function (e) {
            e.preventDefault();

            // CSRF token automatically included
            fetch("/api/mqtt/scan", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'input[name="_token"]',
                    ).value,
                },
                body: JSON.stringify({
                    target: document.querySelector('input[name="target"]')
                        .value,
                }),
            })
                .then((response) => response.json())
                .then((data) => console.log(data));
        });
</script>
```

---

## A.7 Additional Resources

### A.7.1 Useful Commands Reference

**Laravel Artisan Commands:**

```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Seed database with test data
php artisan db:seed

# Clear all caches
php artisan optimize:clear

# Start development server
php artisan serve --host=0.0.0.0 --port=8000
```

**Docker Commands:**

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# View container logs
docker-compose logs -f

# Restart specific service
docker-compose restart mosquitto_secure

# Execute command in container
docker-compose exec mosquitto_secure sh
```

**Python Virtual Environment:**

```bash
# Create virtual environment
python -m venv .venv

# Activate (Windows)
.\.venv\Scripts\Activate.ps1

# Activate (Linux/Mac)
source .venv/bin/activate

# Install requirements
pip install -r requirements.txt

# Deactivate environment
deactivate
```

### A.7.2 Troubleshooting Guide

**Common Issues and Solutions:**

| Issue                                   | Cause                   | Solution                                                           |
| --------------------------------------- | ----------------------- | ------------------------------------------------------------------ |
| Laravel "APP_KEY" error                 | Missing application key | Run `php artisan key:generate`                                     |
| Flask API "Connection refused"          | Server not running      | Start Flask: `python mqtt-scanner/app.py`                          |
| Docker "port already allocated"         | Port conflict           | Stop conflicting service or change port in docker-compose.yml      |
| ESP32 "Certificate verification failed" | Certificate mismatch    | Use `secureClient.setInsecure()` for testing or update certificate |
| Scan returns no results                 | Firewall blocking       | Disable firewall temporarily or add exception for MQTT ports       |
| Database connection error               | Missing database file   | Run `touch database/database.sqlite` and `php artisan migrate`     |

---

**End of Appendix A**
