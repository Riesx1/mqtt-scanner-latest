# CHAPTER 4: SYSTEM DEVELOPMENT

## 4.1 Introduction

This chapter presents the systematic development of the MQTT Network Security Scanner, a web-based vulnerability assessment tool designed to identify security misconfigurations in Message Queuing Telemetry Transport (MQTT) broker deployments. The development spanned two academic semesters following an iterative approach, progressing from a command-line prototype in Final Year Project Phase 1 (FYP1) to a complete three-tier web application in Final Year Project Phase 2 (FYP2).

The implemented system comprises three architectural layers: a Laravel 12 web presentation layer providing the user interface, a Flask RESTful API serving as middleware orchestration, and a Python-based scanning engine executing MQTT protocol analysis. The system enables security analysts to discover MQTT brokers across network ranges, assess authentication enforcement, evaluate Transport Layer Security (TLS) configurations, and classify vulnerability severity.

Development followed an incremental approach with clearly defined iterations. Version 1 (FYP1) established core scanning functionality through a Python command-line interface, validating the proof-of-concept for protocol-aware vulnerability detection. Version 2.0 (FYP2) introduced the three-tier web architecture with user authentication and database persistence. Subsequent iterations (V2.1 through V2.4) progressively enhanced the system with Docker-based test infrastructure, optional hardware integration, security controls, and user interface refinements.

This chapter is organized as follows: Section 4.2 documents the preparation phase including development environment setup, hardware and software prerequisites, and installation procedures. Section 4.3 describes Development Version 1 (CLI prototype) including architecture, core implementation, testing, and identified limitations. Section 4.4 presents Development Version 2 and iterative enhancements, detailing the web platform foundation, test infrastructure, hardware integration, security controls, and user interface refinement. Section 4.5 provides a comprehensive summary of the implementation outcomes, achieved objectives, and lessons learned. Complete source code listings and detailed configuration procedures are provided in Appendix A for reference and reproducibility.

## 4.2 Preparation and Development Environment Setup

### 4.2.1 Hardware Specifications

The development workstation utilized for this project met the minimum hardware requirements necessary to support concurrent execution of multiple system components including Docker containers, web server processes, database operations, and network scanning tasks. Table 4.1 presents the hardware specifications employed during development.

**Table 4.1: Development Hardware Specifications**

| Component         | Specification                          | Justification                                                                           |
| ----------------- | -------------------------------------- | --------------------------------------------------------------------------------------- |
| Processor         | Intel Core i5 / AMD Ryzen 5 equivalent | Supports concurrent Docker containerization and multi-threaded scanning                 |
| Memory (RAM)      | 8 GB minimum, 16 GB recommended        | Enables simultaneous operation of Laravel, Flask, database, and Docker services         |
| Storage           | 20 GB available disk space             | Accommodates software dependencies, Docker images, database files, and application logs |
| Network Interface | WiFi 802.11n or Ethernet (100 Mbps+)   | Provides connectivity to target MQTT brokers and supports local network scanning        |
| Optional: ESP32   | Development board with sensors         | Generates realistic MQTT traffic for hardware-in-the-loop validation testing            |

The workstation operated on Windows 11 Professional (64-bit) as the primary development platform. The choice of Windows was driven by institutional computing infrastructure and developer familiarity, though the system architecture supports cross-platform deployment on Linux-based production servers.

### 4.2.2 Software Prerequisites and Dependencies

The system architecture necessitated installation of multiple software components spanning three technology stacks: PHP/Laravel ecosystem, Python scientific computing environment, and Docker containerization platform. Table 4.2 summarizes the software prerequisites with version constraints validated against the project's dependency manifests (`composer.json` for PHP and `package.json` for Node.js).

**Table 4.2: Software Prerequisites and Minimum Version Requirements**

| Software Component | Version Required | Purpose in System Architecture                    |
| ------------------ | ---------------- | ------------------------------------------------- |
| PHP                | 8.2 or higher    | Runtime environment for Laravel framework         |
| Composer           | 2.x              | Dependency manager for PHP packages               |
| Node.js            | 20.x LTS         | JavaScript runtime for frontend asset compilation |
| NPM                | 10.x             | Package manager for Node.js dependencies          |
| Python             | 3.10 or higher   | Core language for scanning engine and Flask API   |
| pip                | 23.x             | Python package installer                          |
| MySQL              | 8.0 or higher    | Relational database (production deployment)       |
| SQLite             | 3.x (bundled)    | Embedded database (development environment)       |
| Docker Desktop     | 24.x             | Container orchestration for MQTT broker testbed   |
| Git                | 2.x              | Version control system                            |
| Arduino IDE        | 2.x (optional)   | Firmware development for ESP32 microcontroller    |

### 4.2.3 Installation Procedures

This subsection documents the systematic installation and configuration of software prerequisites required for the three-tier MQTT Network Security Scanner system. The installation sequence follows dependency-resolution order, establishing runtime environments (PHP 8.2, Node.js 20.x, Python 3.10+, Docker Desktop) before development tools (Git) and project-specific dependencies. Detailed command sequences and troubleshooting procedures are provided in Appendix A.1 for reproducibility.

#### 4.2.3.1 PHP and Composer Installation

The PHP runtime environment provides the foundation for Laravel framework execution, while Composer manages PHP package dependencies. The installation procedure follows these steps:

**Step 1:** The developer downloads PHP 8.2 Non-Thread Safe (NTS) binaries from the official Windows PHP repository at https://windows.php.net/download/, selecting the "VS16 x64 Non Thread Safe" ZIP archive to ensure compatibility with Composer and Laravel 12.

**Step 2:** The downloaded ZIP archive is extracted to a permanent directory location (e.g., `C:\php`). This directory must reside outside temporary locations to prevent accidental deletion affecting system functionality.

**Step 3:** System environment configuration requires adding the PHP installation directory to the Windows PATH variable. This modification enables command-line invocation of PHP from any directory context. For user-level configuration (recommended approach requiring no administrative privileges), the developer executes in PowerShell:

```powershell
[Environment]::SetEnvironmentVariable('Path', [Environment]::GetEnvironmentVariable('Path', 'User') + ';C:\php', 'User')
```

For system-wide configuration requiring administrative privileges, the 'User' parameter is replaced with 'Machine' in both method calls. After execution, the developer must restart PowerShell to load the updated PATH environment variable.

**Technical Note:** The command retrieves the current PATH value from the specified scope (User or Machine) to avoid duplicating entries from both scopes, which could cause PATH length limit issues (2048 character maximum per environment variable).

**Step 4:** The PHP configuration file requires modification to enable Laravel-required extensions. The developer navigates to the PHP installation directory, locates `php.ini-development`, copies it to `php.ini`, and edits the file to uncomment the following extension lines by removing preceding semicolons:

```ini
extension=openssl
extension=pdo_sqlite
extension=pdo_mysql
extension=mbstring
extension=fileinfo
extension=curl
extension=zip
```

**Step 5:** Installation verification executes the PHP version query command:

```powershell
php -v
```

Expected output displays PHP version information: `PHP 8.2.x (cli) (built: ...) ( NTS )`

**Step 6:** Composer installation utilizes the official Windows installer. The developer downloads `Composer-Setup.exe` from https://getcomposer.org/ and executes the installer, which automatically detects the PHP installation path and configures system PATH entries.

**Step 7:** Composer verification confirms successful installation:

```powershell
composer --version
```

Expected output format: `Composer version 2.x.x YYYY-MM-DD HH:MM:SS`

**Troubleshooting Note:** If Composer reports "PHP extension openssl is missing," the developer must verify Step 4 was completed correctly and restart PowerShell to reload configuration.

#### 4.2.3.2 Node.js and NPM Installation

Node.js provides the JavaScript runtime environment required for frontend asset compilation via Vite, while NPM manages JavaScript package dependencies. The installation procedure consists of:

**Step 1:** The developer downloads the Node.js Long Term Support (LTS) release version 20.x from https://nodejs.org/, selecting the "Windows Installer (.msi)" package for the 64-bit architecture.

**Step 2:** The Windows installer is executed with administrator privileges. During installation, the developer accepts default configuration options, which automatically configure system PATH variables and install the NPM package manager bundled with Node.js.

**Step 3:** Installation verification requires opening a new PowerShell session (to load updated PATH) and executing version queries:

```powershell
node --version
npm --version
```

Expected outputs: `v20.x.x` for Node.js and `10.x.x` for NPM, confirming both components are correctly installed and accessible via command-line interface.

#### 4.2.3.3 Python Environment Configuration

Python 3.10+ provides the runtime for the MQTT scanning engine and Flask API server. The installation establishes an isolated virtual environment to prevent dependency conflicts with system-wide Python packages.

**Step 1:** The developer downloads Python 3.10 or later from https://www.python.org/downloads/, selecting the "Windows installer (64-bit)" executable.

**Step 2:** During installation wizard execution, the developer must select the checkbox labeled "Add Python to PATH" on the first installation screen. This configuration enables command-line Python invocation. The "Install Now" option is then selected to proceed with standard installation to `C:\Users\[Username]\AppData\Local\Programs\Python\Python3xx`.

**Step 3:** Installation verification opens a new PowerShell session and executes:

```powershell
python --version
pip --version
```

Expected outputs: `Python 3.10.x` or higher, and `pip 23.x.x from [installation_path]`, confirming both Python interpreter and package installer are operational.

**Step 4:** Virtual environment creation isolates project dependencies from system-wide packages. The developer navigates to the project root directory and executes:

```powershell
cd "S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main"
python -m venv .venv
```

This command creates a `.venv` directory containing an isolated Python environment with independent package installations.

**Step 5:** Virtual environment activation modifies the current PowerShell session to use the isolated Python interpreter:

```powershell
.\.venv\Scripts\Activate.ps1
```

**Note:** If PowerShell execution policy restricts script execution (error message: "cannot be loaded because running scripts is disabled"), the developer must configure execution policy for the current session only:

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope Process
.\.venv\Scripts\Activate.ps1
```

The `-Scope Process` parameter limits the policy change to the current PowerShell session only; future terminal sessions will require repeating this command before activation. For permanent user-level configuration (requires administrator privileges), use `-Scope CurrentUser` instead of `-Scope Process`.

Upon successful activation, the PowerShell prompt displays a `(.venv)` prefix before the current directory path, indicating all subsequent `python` and `pip` commands will utilize the virtual environment rather than system-wide Python installation.

#### 4.2.3.4 Database System Installation

The system supports dual database backends: SQLite for development environments and MySQL for production deployments. SQLite requires no separate installation as support is bundled with PHP 8.2 via the `pdo_sqlite` extension enabled in Section 4.2.3.1.

For production deployment scenarios requiring MySQL 8.0, the installation procedure consists of:

**Step 1:** The developer downloads MySQL Community Server 8.0 from https://dev.mysql.com/downloads/mysql/, selecting the "Windows (x86, 64-bit), MSI Installer" package.

**Step 2:** The MSI installer is executed with administrator privileges. During the MySQL Server Configuration step, the developer selects "Development Computer" configuration type and sets a secure root password meeting MySQL's default password policy (minimum 8 characters with mixed case, numbers, and special characters).

**Step 3:** Database and user creation for the MQTT scanner application executes via MySQL Command Line Client or MySQL Workbench with root credentials:

```sql
CREATE DATABASE mqtt_scanner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mqtt_scanner_user'@'localhost' IDENTIFIED BY '[SECURE_RANDOM_PASSWORD]';
GRANT ALL PRIVILEGES ON mqtt_scanner.* TO 'mqtt_scanner_user'@'localhost';
FLUSH PRIVILEGES;
```

**Security Note:** The `[SECURE_RANDOM_PASSWORD]` placeholder must be replaced with a cryptographically random password generated using a password manager or secure random generator. This password is later configured in the Laravel `.env` file as `DB_PASSWORD=[value]`.

**Step 4:** Installation verification connects to the newly created database:

```powershell
mysql -u mqtt_scanner_user -p mqtt_scanner
```

After entering the password, successful connection displays the MySQL prompt: `mysql>`, confirming database accessibility.

#### 4.2.3.5 Docker Desktop Installation

Docker Desktop provides containerized MQTT broker infrastructure for consistent testing environments without manual broker configuration. The installation procedure for Windows 11 consists of:

**Step 1:** The developer downloads Docker Desktop for Windows from https://www.docker.com/products/docker-desktop/, selecting the "Docker Desktop for Windows" installer executable.

**Step 2:** The installer is executed with administrator privileges. During installation configuration, the developer ensures the checkbox "Use WSL 2 instead of Hyper-V" is selected. WSL 2 (Windows Subsystem for Linux 2) provides superior container performance compared to Hyper-V backend.

**Step 3:** After installation completion, the system requires restart to initialize the Docker daemon and complete WSL 2 integration.

**Step 4:** Following system restart, Docker Desktop automatically launches. The developer verifies successful installation through PowerShell version queries:

```powershell
docker --version
docker compose version
```

Expected outputs: `Docker version 24.x.x, build [hash]` and `Docker Compose version v2.x.x`, confirming both Docker Engine and integrated Compose V2 are operational.

**Important Note:** The modern Docker Desktop integrates Compose V2 as a Docker CLI plugin accessed via `docker compose` (space-separated) rather than the legacy standalone `docker-compose` (hyphenated) command. All subsequent procedures utilize the `docker compose` syntax.

**Step 5:** Docker functionality verification executes a test container:

```powershell
docker run hello-world
```

Successful execution displays the message "Hello from Docker!" confirming the Docker daemon can pull images, create containers, and execute processes.

#### 4.2.3.6 Git Version Control and Project Acquisition

Git provides version control functionality and enables project repository cloning from GitHub. The installation and repository acquisition procedure consists of:

**Step 1:** The developer downloads Git for Windows from https://git-scm.com/downloads, selecting the "64-bit Git for Windows Setup" installer.

**Step 2:** The installer is executed with default configuration options. During the "Adjusting your PATH environment" step, the option "Git from the command line and also from 3rd-party software" must be selected to enable PowerShell integration.

**Step 3:** Git configuration establishes user identity for commit attribution. The developer executes in PowerShell:

```powershell
git config --global user.name "FirstName LastName"
git config --global user.email "institutional-email@university.edu"
```

**Security Note:** Real names and institutional email addresses should be used for academic project attribution, replacing the placeholder values shown above.

**Step 4:** Project repository acquisition clones the codebase from GitHub:

```powershell
cd "S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop"
git clone https://github.com/Riesx1/mqtt-scanner-latest.git
cd mqtt-scanner-latest
```

**Alternative Step 4 (If Working from Existing Directory):** If the project already exists locally, the developer initializes Git tracking:

```powershell
cd "S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main"
git init
git remote add origin https://github.com/Riesx1/mqtt-scanner-latest.git
git fetch
git checkout main
```

**Step 5:** Git installation verification confirms version and configuration:

```powershell
git --version
git config --list
```

Expected output displays Git version (`git version 2.x.x.windows.x`) and configured user identity matching Step 3 values.

#### 4.2.3.7 Project Dependencies and Environment Initialization

Following prerequisite software installation, project-specific dependencies require installation through their respective package managers. This subsection documents the complete initialization sequence for all three architectural tiers and supporting infrastructure.

##### 4.2.3.7.1 PHP/Laravel Dependencies Installation

**Step 1:** Navigate to the project root directory:

```powershell
cd "S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main"
```

**Step 2:** Install PHP dependencies via Composer, which reads `composer.json` and resolves all Laravel framework dependencies:

```powershell
composer install --optimize-autoloader
```

The `--optimize-autoloader` flag generates optimized class autoloader maps improving application performance. Installation duration ranges from 2-5 minutes depending on network bandwidth, downloading approximately 80MB of packages.

**Step 3:** Copy the environment template to create project-specific configuration:

```powershell
Copy-Item .env.example .env
```

**Step 4:** Generate Laravel application encryption key:

```powershell
php artisan key:generate
```

This command generates a random 32-character base64-encoded key stored in `.env` as `APP_KEY`, used for session encryption and password hashing.

**Step 5:** Configure database connection in `.env` file. For SQLite development configuration, Laravel supports both relative and absolute path specifications. The relative path approach (recommended) provides portability across development environments:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Alternatively, an absolute path may be specified for explicit file location:

```env
DB_CONNECTION=sqlite
DB_DATABASE=S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main\database\database.sqlite
```

Laravel resolves relative paths from the project root directory. The relative path approach is preferred for team environments where absolute paths differ across developer workstations.

For MySQL production configuration, specify connection parameters with explicit host, port, database name, and credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mqtt_scanner
DB_USERNAME=mqtt_scanner_user
DB_PASSWORD=[SECURE_RANDOM_PASSWORD]
```

**Security Note:** The `DB_PASSWORD` value must match the password created in Section 4.2.3.4 Step 3. Never commit `.env` files to version control; verify `.gitignore` includes `.env` entry.

**Step 6:** Create SQLite database file with proper directory structure:

```powershell
New-Item -ItemType Directory -Path database -Force
New-Item -ItemType File -Path database\database.sqlite -Force
```

**Step 7:** Execute database migrations to create application tables:

```powershell
php artisan migrate
```

Expected output displays migration confirmation:

```
Migrating: 2024_01_01_000000_create_users_table
Migrated:  2024_01_01_000000_create_users_table (XX.XXms)
Migrating: 2024_01_02_000000_create_mqtt_scan_histories_table
Migrated:  2024_01_02_000000_create_mqtt_scan_histories_table (XX.XXms)
Migrating: 2024_01_03_000000_create_mqtt_scan_results_table
Migrated:  2024_01_03_000000_create_mqtt_scan_results_table (XX.XXms)
```

**Step 8:** Install Laravel Breeze authentication scaffolding:

```powershell
composer require laravel/breeze --dev
php artisan breeze:install blade
```

When prompted, select "Blade with Alpine" option for frontend stack. This installs authentication views, controllers, and routes.

##### 4.2.3.7.2 Node.js/Frontend Dependencies Installation

**Step 1:** Install JavaScript dependencies defined in `package.json`:

```powershell
npm install
```

This installs Vite 7.0, Tailwind CSS 4.0, and supporting frontend build tools.

**Step 2:** Compile frontend assets for development:

```powershell
npm run dev
```

**Alternative Step 2 (Production Build):** For production deployment, compile optimized minified assets:

```powershell
npm run build
```

Compiled assets are output to `public/build/` directory and automatically referenced by Laravel Blade templates.

##### 4.2.3.7.3 Python/Flask Dependencies Installation

**Step 1:** Ensure virtual environment is activated (prompt displays `(.venv)` prefix). If not activated:

```powershell
.\.venv\Scripts\Activate.ps1
```

**Step 2:** Install Python dependencies from `requirements.txt`:

```powershell
pip install -r mqtt-scanner\requirements.txt
```

This installs paho-mqtt==1.6.1 (MQTT client library), Flask==3.1.0 (API framework), python-dotenv==1.0.0 (environment configuration), and supporting packages.

**Step 3:** Create Flask environment configuration file:

```powershell
New-Item -ItemType File -Path mqtt-scanner\.env -Force
```

**Step 4:** Configure Flask environment variables by editing `mqtt-scanner\.env`:

```env
FLASK_APP=app.py
FLASK_ENV=development
FLASK_API_KEY=[GENERATE_32_CHARACTER_KEY]
```

**Step 5:** Generate secure Flask API key:

```powershell
python -c "import secrets; print(secrets.token_urlsafe(32))"
```

Copy the output (32-character random string) and replace `[GENERATE_32_CHARACTER_KEY]` in `mqtt-scanner\.env`.

**Step 6:** Add the same API key to Laravel's `.env` file for inter-tier communication:

```env
FLASK_BASE=http://127.0.0.1:5000
FLASK_API_KEY=[SAME_32_CHARACTER_KEY]
```

##### 4.2.3.7.4 Docker Test Infrastructure Initialization

**Step 1:** Navigate to the Docker broker directory:

```powershell
cd mqtt-brokers
```

**Prerequisite Note:** OpenSSL is not included in Windows 11 by default. If Git for Windows has been installed per Section 4.2.3.6, OpenSSL is available via Git's bundled Unix tools. The developer must add Git's `usr\bin` directory to the current PowerShell session PATH:

```powershell
$env:Path += ";C:\Program Files\Git\usr\bin"
openssl version
```

Expected output: `OpenSSL 1.1.1` (or later version), confirming OpenSSL accessibility. If OpenSSL is not available via Git, it must be installed separately:

```powershell
# Using Windows Package Manager (winget):
winget install --id ShiningLight.OpenSSL -e
```

After installation, restart PowerShell to load updated PATH entries.

**Step 2:** With OpenSSL accessible, generate TLS certificates for the secure broker. First, create the certificate directory structure:

```powershell
New-Item -ItemType Directory -Path secure\certs -Force
```

Generate the Certificate Authority (CA) private key and self-signed certificate:

```powershell
openssl genrsa -out secure\certs\ca.key 2048
openssl req -new -x509 -days 365 -key secure\certs\ca.key -out secure\certs\ca.crt -subj "/C=MY/ST=State/L=City/O=UNIKL/OU=Development/CN=MQTT-CA"
```

**Step 3:** Generate server private key, certificate signing request (CSR), and signed certificate. For compatibility with Docker service name resolution, the Common Name (CN) is set to match the Docker Compose service name:

```powershell
openssl genrsa -out secure\certs\server.key 2048
openssl req -new -key secure\certs\server.key -out secure\certs\server.csr -subj "/C=MY/ST=State/L=City/O=UNIKL/OU=Development/CN=mosquitto_secure"
openssl x509 -req -in secure\certs\server.csr -CA secure\certs\ca.crt -CAkey secure\certs\ca.key -CAcreateserial -out secure\certs\server.crt -days 365
```

**Technical Note:** The CN field is set to `mosquitto_secure` (matching the Docker Compose service name defined in `docker-compose.yml`) to prevent TLS hostname verification failures during connectivity testing.

**Step 4:** Create Mosquitto password file for secure broker authentication. PowerShell requires explicit path conversion for Docker volume mounts:

```powershell
$securePath = (Get-Item -Path secure).FullName -replace '\\','/'
docker run --rm -v "${securePath}:/mosquitto" eclipse-mosquitto:2.0 mosquitto_passwd -c -b /mosquitto/passwordfile mqtt_user SecureP@ssw0rd
```

This creates a hashed password file at `secure/passwordfile` containing credentials for username `mqtt_user` with password `SecureP@ssw0rd`.

**Security Note:** Replace `SecureP@ssw0rd` with a strong password. This password is used for testing secure broker connectivity.

**Step 5:** Pull Docker images before first launch:

```powershell
docker compose pull
```

**Step 6:** Start the Docker broker containers:

```powershell
docker compose up -d
```

**Step 7:** Verify both brokers are running:

```powershell
docker compose ps
```

Expected output shows two containers in "Up" state:

```
NAME                    STATUS
mqtt_broker_insecure    Up X seconds
mqtt_broker_secure      Up X seconds
```

**Step 8:** Test insecure broker connectivity. First, identify the Docker network name created by Compose:

```powershell
docker network ls | Select-String mqtt
```

Expected output displays network name format: `<directory>_<network_name>`, for example `mqtt-brokers_mqtt_test_network` or `mqtt-brokers_default`. Use the identified network name in subsequent connectivity tests:

```powershell
# Replace <NETWORK_NAME> with actual network name from above
docker run --rm -it --network <NETWORK_NAME> eclipse-mosquitto:2.0 mosquitto_sub -h mosquitto_insecure -p 1883 -t "test/topic" -C 1 -W 2
```

The command executes a temporary Mosquitto subscriber container, attempts connection to the insecure broker, subscribes to a test topic, waits 2 seconds, and exits. No error messages indicate successful connection establishment.

**Step 9:** Test secure broker connectivity with TLS certificate verification. Mount the certificate directory and connect using the Docker service name:

```powershell
$securePath = (Get-Item -Path secure).FullName -replace '\\','/'
docker run --rm -it --network <NETWORK_NAME> -v "${securePath}/certs:/certs:ro" eclipse-mosquitto:2.0 mosquitto_sub -h mosquitto_secure -p 8883 -t "test/topic" --cafile /certs/ca.crt -u mqtt_user -P SecureP@ssw0rd -C 1 -W 2
```

Replace `<NETWORK_NAME>` with the network name identified in Step 8. The command mounts the certificate directory as read-only volume (`-v` flag with `:ro` suffix), provides the CA certificate for TLS verification (`--cafile` flag), and authenticates with username and password (`-u` and `-P` flags). No error messages indicate successful secure connection with TLS encryption.

Both tests should exit gracefully after 2-second timeout, confirming brokers accept connections with appropriate authentication and encryption configurations.

##### 4.2.3.7.5 End-to-End System Initialization and Verification

After completing all previous installation and configuration steps, the complete system requires coordinated startup of all three architectural tiers. This subsection documents the end-to-end initialization sequence and verification procedure.

**Step 1:** Return to project root directory:

```powershell
cd "S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main"
```

**Step 2:** Verify Docker brokers remain operational:

```powershell
docker compose -f mqtt-brokers\docker-compose.yml ps
```

If containers are stopped, restart them:

```powershell
docker compose -f mqtt-brokers\docker-compose.yml up -d
```

**Step 3:** Start the Flask API server in a dedicated PowerShell terminal:

```powershell
# Terminal 1: Flask API
.\.venv\Scripts\Activate.ps1
cd mqtt-scanner
python app.py
```

Expected output displays:

```
 * Running on http://127.0.0.1:5000
 * Debug mode: on
```

**Step 4:** Start the Laravel development server in a second PowerShell terminal:

```powershell
# Terminal 2: Laravel Web Server
cd "S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main"
php artisan serve
```

Expected output displays:

```
INFO  Server running on [http://127.0.0.1:8000].
Press Ctrl+C to stop the server
```

**Step 5:** Start the Vite development server for frontend hot-reload in a third PowerShell terminal:

```powershell
# Terminal 3: Vite Asset Compiler
cd "S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main"
npm run dev
```

Expected output displays:

```
VITE v7.0.x  ready in XXX ms
➜  Local:   http://127.0.0.1:5173/
```

**Step 6:** Verify Flask API accessibility using HTTP health check endpoint. PowerShell provides native HTTP client functionality via the `Invoke-RestMethod` cmdlet:

```powershell
# Terminal 4: Testing
Invoke-RestMethod -Uri http://127.0.0.1:5000/health
```

Expected response displays PowerShell object notation: `@{status=healthy}`, confirming Flask API server is operational and accepting requests.

**Alternative:** If `curl.exe` is available via Git for Windows installation, direct curl invocation provides raw JSON response:

```powershell
curl.exe http://127.0.0.1:5000/health
```

Expected response: `{"status":"healthy"}` in raw JSON format.

**Step 7:** Access the web application via browser navigation to `http://127.0.0.1:8000`. The Laravel welcome page or login interface should display, confirming frontend rendering functionality.

**Step 8:** Register a test user account through the web interface:

- Navigate to `http://127.0.0.1:8000/register`
- Enter name, email, and password
- Submit registration form
- Verify automatic login and redirect to dashboard

**Step 9:** Execute an end-to-end scan test targeting the local Docker brokers:

- In the dashboard, enter target: `127.0.0.1`
- Click "Scan" button
- Verify scan execution displays progress indicator
- Confirm results table populates with two entries:
    - `127.0.0.1:1883` - Critical severity (anonymous access)
    - `127.0.0.1:8883` - Medium severity (authentication required)

**Step 10:** Verify database persistence by refreshing the page; scan history should display the previous scan with result count.

**Step 11:** Test CSV export functionality by clicking "Export CSV" button; verify download of properly formatted RFC 4180-compliant CSV file containing scan results.

**Troubleshooting Common Issues:**

- **Issue:** Laravel displays "502 Bad Gateway" when initiating scan
    - **Cause:** Flask API server not running or incorrect `FLASK_BASE` configuration
    - **Solution:** Verify Terminal 1 shows Flask running on `http://127.0.0.1:5000`; verify `.env` specifies `FLASK_BASE=http://127.0.0.1:5000`

- **Issue:** Vite assets fail to load (broken CSS styling)
    - **Cause:** Vite development server not running
    - **Solution:** Verify Terminal 3 shows Vite running; ensure `npm run dev` was executed

- **Issue:** Database migration errors
    - **Cause:** SQLite file does not exist or lacks write permissions
    - **Solution:** Execute `New-Item -ItemType File -Path database\database.sqlite -Force` and verify directory permissions

- **Issue:** Docker brokers fail to start
    - **Cause:** Port conflicts with existing services
    - **Solution:** Execute `netstat -ano | findstr :1883` and `netstat -ano | findstr :8883` to identify processes using ports; terminate conflicting processes or modify `docker-compose.yml` port mappings

Upon successful completion of all verification steps, the development environment is fully operational and ready for development, testing, and debugging workflows. The three-tier architecture executes concurrently with each tier accessible via its designated network endpoint: Laravel presentation tier on port 8000, Flask API middleware on port 5000, and Docker MQTT brokers on ports 1883 (insecure) and 8883 (secure).

### 4.2.4 Project Directory Structure

The project follows Laravel's conventional directory structure with additional components for Python scanning engine and Docker infrastructure. Figure 4.1 illustrates the hierarchical organization of project directories and key files.

**Figure 4.1: Project Directory Structure**

```
mqtt-scanner-latest/
├── app/                          # Laravel application logic
│   ├── Http/
│   │   └── Controllers/
│   │       └── MqttScannerController.php
│   ├── Models/
│   │   ├── MqttScanHistory.php
│   │   └── MqttScanResult.php
│   └── Providers/
├── database/
│   ├── migrations/               # Database schema migrations
│   │   ├── 2024_01_01_create_users_table.php
│   │   ├── 2024_01_02_create_mqtt_scan_histories_table.php
│   │   └── 2024_01_03_create_mqtt_scan_results_table.php
│   └── database.sqlite           # SQLite database file
├── mqtt-scanner/                 # Python scanning engine
│   ├── scanner.py                # Core scanning logic
│   ├── app.py                    # Flask API server
│   ├── requirements.txt          # Python dependencies
│   └── storage/                  # Scan output storage
├── mqtt-brokers/                 # Docker test infrastructure
│   ├── docker-compose.yml
│   ├── insecure/
│   │   └── mosquitto.conf        # Vulnerable broker config
│   └── secure/
│       ├── mosquitto.conf        # Secured broker config
│       └── certs/                # TLS certificates
├── resources/
│   ├── views/
│   │   └── dashboard.blade.php   # Main user interface
│   └── js/
│       └── app.js                # Frontend JavaScript
├── routes/
│   └── web.php                   # Laravel route definitions
├── composer.json                 # PHP dependencies
├── package.json                  # Node.js dependencies
├── .env                          # Environment configuration
└── artisan                       # Laravel CLI tool
```

As shown in Figure 4.1, the project segregates concerns through distinct directory hierarchies: Laravel application code resides in `app/`, Python components occupy `mqtt-scanner/`, Docker configurations exist in `mqtt-brokers/`, and frontend resources populate `resources/`. This separation enables independent development, testing, and deployment of each architectural tier.

## 4.3 Development Version 1: Command-Line Prototype (FYP1)

### 4.3.1 Version 1 Objectives and Scope

Development Version 1 served as the proof-of-concept implementation developed during FYP1, establishing the foundational scanning methodology and vulnerability detection logic. The primary objectives for Version 1 included:

1. Demonstrating technical feasibility of protocol-aware MQTT security assessment
2. Implementing TCP port discovery on standard MQTT ports (1883 for plaintext, 8883 for TLS)
3. Validating MQTT protocol interaction using the paho-mqtt Python library
4. Establishing vulnerability classification logic based on broker response codes
5. Generating structured scan reports in CSV format for manual analysis

Version 1 adopted a single-tier architecture implemented entirely in Python, prioritizing rapid prototyping and algorithm validation over user experience considerations. The command-line interface enabled direct invocation with minimal overhead, facilitating iterative testing during algorithm development.

### 4.3.2 Version 1 System Architecture

Figure 4.2 illustrates the architectural components of Version 1, comprising three interconnected modules executing in sequential pipeline fashion.

**Figure 4.2: Version 1 Single-Tier Architecture**

```
┌─────────────────────────────────────────────────────────┐
│                    CLI Entry Point                      │
│              (scanner.py __main__ block)                │
└──────────────────────┬──────────────────────────────────┘
                       │
                       ▼
         ┌─────────────────────────────┐
         │   Target Parser Module      │
         │  - CIDR expansion           │
         │  - IP validation            │
         └──────────┬──────────────────┘
                    │
                    ▼
         ┌─────────────────────────────┐
         │   TCP Port Scanner Module   │
         │  - Socket connection test   │
         │  - Ports 1883, 8883         │
         └──────────┬──────────────────┘
                    │
                    ▼
         ┌─────────────────────────────┐
         │   MQTT Probe Module         │
         │  - Anonymous connection     │
         │  - Auth test (optional)     │
         │  - Response code analysis   │
         └──────────┬──────────────────┘
                    │
                    ▼
         ┌─────────────────────────────┐
         │  Classification Module      │
         │  - Severity assignment      │
         │  - Outcome determination    │
         └──────────┬──────────────────┘
                    │
                    ▼
         ┌─────────────────────────────┐
         │    CSV Report Generator     │
         │  - Structured output        │
         │  - File persistence         │
         └─────────────────────────────┘
```

As shown in Figure 4.2, data flows unidirectionally through the pipeline, with each module performing a discrete transformation on the scan data before passing results to the subsequent stage.

### 4.3.3 Core Implementation

#### 4.3.3.1 Target Parsing Logic

The target parser module implemented CIDR notation expansion to support network range scanning. Listing 4.1 presents the core parsing algorithm utilizing Python's `ipaddress` standard library module.

**Listing 4.1: Target Specification Parser Implementation**

```python
import ipaddress
from typing import List

def parse_target(target: str) -> List[str]:
    """
    Parse target specification into list of IP addresses.
    Supports single IP or CIDR notation.
    """
    try:
        if '/' in target:
            # CIDR notation (e.g., 192.168.1.0/24)
            network = ipaddress.ip_network(target, strict=False)
            return [str(ip) for ip in network.hosts()]
        else:
            # Single IP address validation
            ipaddress.ip_address(target)
            return [target]
    except ValueError as e:
        raise ValueError(f"Invalid target format: {target}")
```

Listing 4.1 demonstrates input validation and format detection, leveraging the `ipaddress` module's built-in IPv4/IPv6 support and CIDR expansion capabilities. The function returns a list of individual IP address strings suitable for iterative scanning.

#### 4.3.3.2 MQTT Protocol Probing

The MQTT probe module implemented connection testing with multiple authentication scenarios to differentiate between vulnerable anonymous access and enforced authentication requirements. Listing 4.2 presents the anonymous connection test logic.

**Listing 4.2: Anonymous MQTT Connection Test**

```python
import paho.mqtt.client as mqtt_client
import ssl

def test_anonymous_connection(host: str, port: int) -> dict:
    """Test broker for anonymous access vulnerability"""
    client = mqtt_client.Client(client_id="scanner_anon",
                                protocol=mqtt_client.MQTTv311)
    result = {'success': False, 'outcome': 'Unknown'}

    # Configure TLS for secure port
    if port == 8883:
        client.tls_set(cert_reqs=ssl.CERT_NONE)
        client.tls_insecure_set(True)

    def on_connect(client, userdata, flags, rc, properties=None):
        if rc == 0:
            result['success'] = True
            result['outcome'] = 'Anonymous Access Allowed'
            result['severity'] = 'Critical'
        elif rc == 5:
            result['outcome'] = 'Authentication Required'
            result['severity'] = 'Medium'

    client.on_connect = on_connect
    client.connect(host, port, keepalive=10)
    client.loop_start()
    time.sleep(2)
    client.loop_stop()
    client.disconnect()

    return result
```

As demonstrated in Listing 4.2, the connection test evaluates MQTT return codes to classify broker security posture. Return code 0 indicates successful connection without credentials, constituting a Critical severity vulnerability. Return code 5 signifies authentication enforcement, representing a Medium severity finding requiring further credential strength assessment.

#### 4.3.3.3 Vulnerability Classification

The classification module assigned severity ratings based on observed broker behavior according to the rules presented in Table 4.3.

**Table 4.3: Version 1 Vulnerability Classification Rules**

| Observed Behavior           | MQTT Return Code | Severity Classification | Security Implication                                 |
| --------------------------- | ---------------- | ----------------------- | ---------------------------------------------------- |
| Anonymous access successful | 0                | Critical                | No authentication barrier; full unauthorized access  |
| Authentication required     | 5                | Medium                  | Credentials required but strength unknown            |
| Connection refused          | 3                | Low                     | Broker online but refusing connections; possible ACL |
| Connection timeout          | N/A              | Info                    | Port open but MQTT service unresponsive              |
| Port closed                 | N/A              | Info                    | TCP port not accepting connections                   |

Table 4.3 establishes the vulnerability taxonomy implemented in Version 1, providing consistent classification across diverse broker configurations.

### 4.3.4 Version 1 Testing and Validation

Testing of Version 1 utilized a local Eclipse Mosquitto broker instance manually configured with anonymous access enabled. The test procedure involved:

**Step 1:** Install and configure Mosquitto broker:

```bash
# mosquitto.conf configuration
listener 1883
allow_anonymous true
```

**Step 2:** Execute scanner against local broker:

```powershell
python mqtt-scanner/scanner.py 127.0.0.1
```

**Step 3:** Verify CSV report generation and content accuracy.

Testing confirmed correct detection of the vulnerable configuration (anonymous access allowed) with appropriate Critical severity classification. The scanner successfully distinguished between accessible brokers and closed ports, validating the core detection algorithm.

### 4.3.5 Version 1 Limitations and Identified Requirements

Analysis of Version 1 revealed several limitations necessitating architectural evolution in Version 2:

**1. User Experience Limitations:**

- Command-line interface requires technical proficiency
- No graphical visualization of scan results
- Manual CSV file analysis required for result interpretation

**2. Data Management Limitations:**

- No persistent storage of scan history
- No capability for longitudinal analysis or trending
- CSV files accumulate without organized management

**3. Multi-User Limitations:**

- No user authentication or access control
- Single-user tool unsuitable for team environments
- No audit trail of scan activities

**4. Performance Limitations:**

- Sequential scanning exhibits poor scalability for large network ranges
- No concurrent connection handling
- Extended scan duration for /24 CIDR blocks

**5. Security Limitations:**

- No input sanitization for target specifications
- No rate limiting to prevent abuse
- No secure credential storage for authenticated broker testing

These limitations informed the requirements gathering for Version 2, driving the transition to a multi-tier web architecture with comprehensive user management, database persistence, and enhanced security controls. Version 1 successfully validated the core technical approach, establishing confidence in protocol-aware vulnerability detection and enabling focused development of production-ready features in subsequent iterations.

## 4.4 Development Version 2 and Iterations (FYP2 Web Platform)

### 4.4.1 Version 2.0: Three-Tier Web Platform Foundation

Version 2.0 represented a complete architectural redesign, transforming the command-line prototype into a distributed three-tier web application. This transition addressed the fundamental limitations identified during Version 1 evaluation, specifically the lack of user authentication, absence of database persistence, inability to support multiple concurrent users, and the requirement for technical proficiency to operate command-line tools. As shown in Figure 4.3, the implementation segregated responsibilities across presentation, business logic, and data access layers, enabling independent scaling, testing, and deployment of each tier.

#### 4.4.1.1 Three-Tier Architecture Design

**Figure 4.3: Version 2.0 Three-Tier Architecture**

```
┌──────────────────────────────────────────────────────────────┐
│                    Presentation Tier                         │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  Laravel 12 Web Application                            │  │
│  │  - Blade template rendering (Tailwind CSS 4.0)         │  │
│  │  - User authentication (Laravel Breeze)                │  │
│  │  - HTTP routing (routes/web.php)                       │  │
│  │  - Form validation and CSRF protection                 │  │
│  │  - Session management                                  │  │
│  └───────────────────────┬────────────────────────────────┘  │
└────────────────────────────┼──────────────────────────────────┘
                             │ HTTP POST (JSON)
                             │ X-API-KEY: [API_KEY]
                             │ Content-Type: application/json
                             ▼
┌──────────────────────────────────────────────────────────────┐
│                   Business Logic Tier                        │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  Flask 3.1.0 RESTful API (Python)                      │  │
│  │  - API key authentication middleware                   │  │
│  │  - Request validation and sanitization                 │  │
│  │  - Rate limiting (5 req/60s per IP)                    │  │
│  │  - Orchestration of scanning engine                    │  │
│  │  - JSON response formatting                            │  │
│  └───────────────────────┬────────────────────────────────┘  │
└────────────────────────────┼──────────────────────────────────┘
                             │ Python function call
                             │ run_scan(target) → results_dict
                             ▼
┌──────────────────────────────────────────────────────────────┐
│                    Scanning Engine Tier                      │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  Python MQTT Scanner Module (paho-mqtt 1.6.1)         │  │
│  │  - Target parsing (ipaddress module)                   │  │
│  │  - TCP port scanning (socket module)                   │  │
│  │  - MQTT protocol probing (paho.mqtt.client)            │  │
│  │  - TLS certificate inspection (ssl module)             │  │
│  │  - Vulnerability classification                        │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
                             │
                             ▼
                   ┌──────────────────┐
                   │   Database Layer │
                   │  MySQL / SQLite  │
                   │  - users         │
                   │  - scan_histories│
                   │  - scan_results  │
                   └──────────────────┘
```

Figure 4.3 illustrates the clear separation of concerns: the presentation tier handles user interface rendering and session management, the business logic tier orchestrates operations and enforces security policies, and the scanning engine tier performs specialized security assessment tasks. This architectural pattern enables independent unit testing of each layer, facilitates horizontal scaling of individual components, and supports future extension without modifying existing tier implementations.

#### 4.4.1.2 Database Schema Design

The database persistence layer implements a normalized relational schema optimized for scan result storage, audit trail maintenance, and user access control. Figure 4.4 depicts the entity-relationship model governing data relationships.

**Figure 4.4: Database Schema Entity-Relationship Diagram**

```
┌─────────────────────────┐
│        users            │
├─────────────────────────┤
│ id (PK, BIGINT)         │
│ name (VARCHAR)          │
│ email (UNIQUE, VARCHAR) │
│ password (HASH, VARCHAR)│
│ created_at (TIMESTAMP)  │
│ updated_at (TIMESTAMP)  │
└────────┬────────────────┘
         │
         │ Relationship: One User → Many Scan Histories
         │ Foreign Key: mqtt_scan_histories.user_id → users.id
         │ Constraint: ON DELETE CASCADE
         │
         ▼
┌─────────────────────────────┐
│  mqtt_scan_histories        │
├─────────────────────────────┤
│ id (PK, BIGINT)             │
│ user_id (FK, BIGINT)        │──────┐
│ target (VARCHAR, 100)       │      │
│ started_at (TIMESTAMP)      │      │ Relationship: One Scan → Many Results
│ completed_at (TIMESTAMP)    │      │ Foreign Key: mqtt_scan_results.scan_history_id
│ status (ENUM)               │      │ Values: running, completed, failed
│ total_brokers_found (INT)   │      │ Constraint: ON DELETE CASCADE
│ vulnerable_count (INT)      │      │
│ created_at (TIMESTAMP)      │      │
└─────────────────────────────┘      │
                                     │
                                     ▼
                         ┌───────────────────────────┐
                         │  mqtt_scan_results        │
                         ├───────────────────────────┤
                         │ id (PK, BIGINT)           │
                         │ scan_history_id (FK)      │
                         │ ip_address (VARCHAR)      │
                         │ port (INT)                │
                         │ outcome (VARCHAR)         │
                         │ severity (ENUM)           │
                         │ details (TEXT)            │
                         │ tls_enabled (BOOLEAN)     │
                         │ auth_required (BOOLEAN)   │
                         │ created_at (TIMESTAMP)    │
                         └───────────────────────────┘
```

As illustrated in Figure 4.4, the schema enforces referential integrity through foreign key constraints with cascading delete behavior. When a user account is deleted, all associated scan histories are automatically removed, which in turn cascades to delete all linked scan results, maintaining database consistency. The `users` table stores authentication credentials using Laravel's bcrypt password hashing algorithm, ensuring passwords are never stored in plaintext. The `mqtt_scan_histories` table maintains scan session metadata, including initiating user, target specification, execution timestamps, and aggregate statistics (total brokers discovered and vulnerable count). The `mqtt_scan_results` table stores individual broker findings with detailed security attributes (TLS availability, authentication requirements), enabling granular forensic analysis and historical trend visualization.

Table 4.3 documents the complete database schema with field-level specifications and functional purpose.

**Table 4.3: Database Schema Detailed Specification (Version 2.0)**

| Table Name            | Field Name            | Data Type                                         | Constraints                          | Purpose                                  |
| --------------------- | --------------------- | ------------------------------------------------- | ------------------------------------ | ---------------------------------------- |
| `users`               | `id`                  | BIGINT UNSIGNED                                   | PRIMARY KEY, AUTO_INCREMENT          | Unique user identifier                   |
|                       | `name`                | VARCHAR(255)                                      | NOT NULL                             | User display name                        |
|                       | `email`               | VARCHAR(255)                                      | NOT NULL, UNIQUE                     | Authentication credential (login)        |
|                       | `password`            | VARCHAR(255)                                      | NOT NULL                             | Bcrypt hashed password (60 char)         |
|                       | `created_at`          | TIMESTAMP                                         | NULL                                 | Account creation timestamp               |
|                       | `updated_at`          | TIMESTAMP                                         | NULL                                 | Last profile modification timestamp      |
| `mqtt_scan_histories` | `id`                  | BIGINT UNSIGNED                                   | PRIMARY KEY, AUTO_INCREMENT          | Unique scan session identifier           |
|                       | `user_id`             | BIGINT UNSIGNED                                   | FOREIGN KEY (users.id), NOT NULL     | Scan initiator reference                 |
|                       | `target`              | VARCHAR(100)                                      | NOT NULL                             | Target specification (IP/CIDR)           |
|                       | `started_at`          | TIMESTAMP                                         | NOT NULL                             | Scan execution start time                |
|                       | `completed_at`        | TIMESTAMP                                         | NULL                                 | Scan completion/failure time             |
|                       | `status`              | ENUM('running', 'completed', 'failed')            | NOT NULL, DEFAULT 'running'          | Scan execution state                     |
|                       | `total_brokers_found` | INT UNSIGNED                                      | DEFAULT 0                            | Count of discovered MQTT brokers         |
|                       | `vulnerable_count`    | INT UNSIGNED                                      | DEFAULT 0                            | Count of Critical/High severity findings |
|                       | `created_at`          | TIMESTAMP                                         | NULL                                 | Database record creation time            |
| `mqtt_scan_results`   | `id`                  | BIGINT UNSIGNED                                   | PRIMARY KEY, AUTO_INCREMENT          | Unique result identifier                 |
|                       | `scan_history_id`     | BIGINT UNSIGNED                                   | FOREIGN KEY (mqtt_scan_histories.id) | Parent scan session reference            |
|                       | `ip_address`          | VARCHAR(45)                                       | NOT NULL                             | Target IP (IPv4 or IPv6)                 |
|                       | `port`                | INT UNSIGNED                                      | NOT NULL                             | MQTT port (typically 1883 or 8883)       |
|                       | `outcome`             | VARCHAR(100)                                      | NOT NULL                             | Discovery result description             |
|                       | `severity`            | ENUM('Critical', 'High', 'Medium', 'Low', 'Info') | NOT NULL                             | Risk severity classification             |
|                       | `details`             | TEXT                                              | NULL                                 | Additional vulnerability details (JSON)  |
|                       | `tls_enabled`         | BOOLEAN                                           | DEFAULT FALSE                        | TLS encryption availability flag         |
|                       | `auth_required`       | BOOLEAN                                           | DEFAULT FALSE                        | Authentication enforcement flag          |
|                       | `created_at`          | TIMESTAMP                                         | NULL                                 | Result record creation time              |

Table 4.3 specifies the complete data model supporting multi-user authentication, audit trail preservation through timestamp fields, and flexible vulnerability detail storage via TEXT fields accommodating JSON-encoded metadata.

#### 4.4.1.3 Laravel Presentation Layer Implementation

The Laravel application tier implements user authentication using Laravel Breeze, a lightweight authentication scaffolding package providing login, registration, password reset, and email verification capabilities. Authentication routes are defined in [routes/auth.php](routes/auth.php) and leverage Laravel's session-based authentication guard.

The primary controller orchestrating scan execution is `MqttScannerController`, located at [app/Http/Controllers/MqttScannerController.php](app/Http/Controllers/MqttScannerController.php). Listing 4.3 presents the core `scan()` method implementing the complete workflow: input validation, database record creation, Flask API invocation, result persistence, and response generation.

**Listing 4.3: Laravel Scan Controller Implementation**

```php
public function scan(Request $request): JsonResponse
{
    // Multi-layer input validation
    $validated = $request->validate([
        'target' => [
            'required',
            'string',
            'max:100',
            'regex:/^[0-9\.\/:a-zA-Z\-]+$/'
        ]
    ]);

    // Create audit trail record with user attribution
    $scanHistory = MqttScanHistory::create([
        'user_id' => Auth::id(),
        'target' => $validated['target'],
        'started_at' => now(),
        'status' => 'running'
    ]);

    try {
        // Invoke Flask API with timeout and authentication
        $response = Http::timeout(60)
            ->withHeaders([
                'X-API-KEY' => config('app.flask_api_key'),
                'Content-Type' => 'application/json'
            ])
            ->post(config('app.flask_base') . '/api/scan', [
                'target' => $validated['target']
            ]);

        if ($response->successful()) {
            $scanData = $response->json();

            // Transactionally persist results
            DB::transaction(function() use ($scanHistory, $scanData) {
                foreach ($scanData['results'] as $result) {
                    MqttScanResult::create([
                        'scan_history_id' => $scanHistory->id,
                        'ip_address' => $result['ip_address'],
                        'port' => $result['port'],
                        'outcome' => $result['outcome'],
                        'severity' => $result['severity'],
                        'tls_enabled' => $result['tls_enabled'],
                        'auth_required' => $result['auth_required']
                    ]);
                }

                $scanHistory->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'total_brokers_found' => count($scanData['results'])
                ]);
            });

            return response()->json(['success' => true]);
        }

        throw new Exception('Flask API returned non-200 status');
    } catch (Exception $e) {
        $scanHistory->update(['status' => 'failed']);
        Log::error('Scan execution failed', [
            'error' => $e->getMessage(),
            'scan_id' => $scanHistory->id
        ]);
        return response()->json(['error' => 'Scan failed'], 500);
    }
}
```

As demonstrated in Listing 4.3, the controller implements comprehensive error handling: the database transaction ensures atomic result persistence (all results saved or none), exception handling prevents application crashes from propagating to users, and audit logging captures failure diagnostics for troubleshooting. The regex validation pattern `/^[0-9\.\/:a-zA-Z\-]+$/` implements a whitelist approach, permitting only characters required for IP addresses (0-9, dot, colon for IPv6), CIDR notation (forward slash), and hostname resolution (alphanumeric, hyphen), effectively preventing command injection and SQL injection attacks.

#### 4.4.1.4 Flask API Middleware Implementation

The Flask API layer implements a RESTful architecture pattern exposing a single POST endpoint at `/api/scan`. This middleware tier enforces authentication, performs input sanitization, and orchestrates scan execution while insulating the scanning engine from direct web exposure. Listing 4.4 presents the complete endpoint implementation with security controls.

**Listing 4.4: Flask API Scan Endpoint with Security Controls**

```python
from flask import Flask, request, jsonify
import os, re, logging
from scanner import run_scan
from collections import defaultdict
import time

app = Flask(__name__)
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Configuration
FLASK_API_KEY = os.environ.get('FLASK_API_KEY', 'REPLACE_WITH_RANDOM_KEY')
RATE_LIMIT_REQUESTS = 5
RATE_LIMIT_WINDOW = 60  # seconds
rate_limit_storage = defaultdict(list)

def check_rate_limit(client_ip):
    current_time = time.time()
    rate_limit_storage[client_ip] = [
        t for t in rate_limit_storage[client_ip]
        if current_time - t < RATE_LIMIT_WINDOW
    ]
    if len(rate_limit_storage[client_ip]) >= RATE_LIMIT_REQUESTS:
        return False
    rate_limit_storage[client_ip].append(current_time)
    return True

@app.route('/api/scan', methods=['POST'])
def api_scan():
    # Authentication enforcement
    api_key = request.headers.get('X-API-KEY')
    if not api_key or api_key != FLASK_API_KEY:
        logger.warning(f"Unauthorized access from {request.remote_addr}")
        return jsonify({'error': 'Unauthorized'}), 401

    # Rate limiting
    if not check_rate_limit(request.remote_addr):
        logger.warning(f"Rate limit exceeded: {request.remote_addr}")
        return jsonify({'error': 'Rate limit exceeded'}), 429

    # Content-Type validation
    if not request.is_json:
        return jsonify({'error': 'Content-Type must be application/json'}), 400

    # Parameter extraction and validation
    data = request.get_json()
    target = data.get('target')

    if not target or not isinstance(target, str):
        return jsonify({'error': 'Invalid or missing target parameter'}), 400

    if len(target) > 100:
        return jsonify({'error': 'Target exceeds 100 characters'}), 400

    # Input sanitization (whitelist validation)
    if not re.match(r'^[0-9\.\/:a-zA-Z\-]+$', target):
        logger.warning(f"Invalid target format: {target}")
        return jsonify({'error': 'Target contains invalid characters'}), 400

    try:
        # Execute scan via scanning engine
        results = run_scan(target)
        logger.info(f"Scan completed for {target}: {len(results['results'])} brokers")
        return jsonify(results), 200
    except Exception as e:
        logger.error(f"Scan execution failed: {str(e)}")
        return jsonify({'error': 'Internal server error'}), 500
```

Listing 4.4 demonstrates defense-in-depth security architecture: authentication occurs first via API key comparison, rate limiting prevents denial-of-service attacks through sliding window algorithm, content-type validation rejects non-JSON requests preventing MIME confusion attacks, multi-layer input validation checks type, length, and character whitelist, and structured logging captures security events for audit analysis. The rate limiting implementation maintains a per-IP timestamp list, pruning expired entries before threshold comparison, ensuring fair resource allocation across legitimate users while blocking abusive clients.

#### 4.4.1.5 Enhanced Scanning Engine

The scanning engine required modifications to support JSON output format for API integration. The `run_scan()` function returns a dictionary containing scan metadata and results array. Listing 4.5 presents the modified return structure.

**Listing 4.5: Enhanced Scanner Output Format**

```python
def run_scan(target: str) -> dict:
    """
    Execute MQTT broker scan against specified target.

    Args:
        target: IP address, hostname, or CIDR network range

    Returns:
        Dictionary with structure:
        {
            'scan_timestamp': ISO8601 timestamp,
            'target': target specification,
            'total_brokers_found': integer count,
            'results': [
                {
                    'ip_address': str,
                    'port': int,
                    'outcome': str,
                    'severity': str,
                    'tls_enabled': bool,
                    'auth_required': bool,
                    'details': str
                }
            ]
        }
    """
    results = []
    ip_list = parse_target(target)

    for ip in ip_list:
        for port in [1883, 8883]:
            if is_port_open(ip, port):
                result = probe_mqtt_broker(ip, port)
                results.append(result)

    return {
        'scan_timestamp': datetime.now().isoformat(),
        'target': target,
        'total_brokers_found': len(results),
        'results': results
    }
```

The structured output format in Listing 4.5 enables seamless database persistence and supports future API versioning through explicit schema definition.

#### 4.4.1.6 User Interface Implementation

The web dashboard implements responsive design using Tailwind CSS 4.0 framework. The primary template ([resources/views/dashboard.blade.php](resources/views/dashboard.blade.php)) presents three functional sections: scan initiation, results display, and historical scan summary. Figure 4.5 illustrates the conceptual UI layout.

**Figure 4.5: Dashboard User Interface Layout**

```
┌───────────────────────────────────────────────────────────────┐
│  MQTT Network Security Scanner    [Username] [Logout Button]  │
├───────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │  New Scan                                              │ │
│  │  ┌──────────────────────────────────────────────────┐  │ │
│  │  │ Target: [192.168.1.0/24_____________] [Scan Btn] │  │ │
│  │  └──────────────────────────────────────────────────┘  │ │
│  │  Format: Single IP (192.168.1.10) or CIDR (10.0.0.0/24) │
│  └─────────────────────────────────────────────────────────┘ │
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │  Scan Results (2 brokers found)        [Export CSV]     │ │
│  │  ┌────────────┬──────┬─────────────────┬────────────┐  │ │
│  │  │ IP Address │ Port │ Outcome         │ Severity   │  │ │
│  │  ├────────────┼──────┼─────────────────┼────────────┤  │ │
│  │  │192.168.1.10│ 1883 │Anonymous Access │ [Critical] │  │ │
│  │  │192.168.1.20│ 8883 │Auth Required    │ [Medium]   │  │ │
│  │  └────────────┴──────┴─────────────────┴────────────┘  │ │
│  └─────────────────────────────────────────────────────────┘ │
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │  Recent Scan History                                    │ │
│  │  • 192.168.1.0/24 - 2 brokers, 1 vulnerable (5 min ago)│ │
│  │  • 10.0.0.10 - 1 broker, 0 vulnerable (1 hour ago)     │ │
│  │  • 172.16.0.0/24 - 0 brokers (2 hours ago)             │ │
│  └─────────────────────────────────────────────────────────┘ │
└───────────────────────────────────────────────────────────────┘
```

As depicted in Figure 4.5, the interface prioritizes usability through clear visual hierarchy: primary action (scan initiation) positioned at top, results table implements severity color coding (red for Critical, yellow for Medium), and historical summary provides context for longitudinal analysis. The implementation utilizes AJAX (Asynchronous JavaScript and XML) for non-blocking result updates, preventing full page reloads and maintaining responsive user experience during scan execution.

### 4.4.2 Version 2.1: Docker-Based Test Infrastructure

Version 2.1 addressed a critical testing limitation: inconsistent results due to manual broker configuration variations and lack of controlled test environment. The implementation introduced containerized MQTT broker infrastructure using Docker Compose, enabling reproducible testing without manual broker installation, configuration, or management overhead.

#### 4.4.2.1 Docker Compose Service Definition

The infrastructure utilizes Docker Compose version 3.8 for multi-container orchestration. Two Eclipse Mosquitto 2.0 broker instances operate simultaneously: an intentionally vulnerable insecure broker for Critical severity detection validation, and a properly secured broker with authentication and TLS for Medium severity baseline testing. Listing 4.6 presents the service definitions.

**Listing 4.6: Docker Compose Infrastructure Configuration**

```yaml
version: "3.8"

services:
    mosquitto_insecure:
        image: eclipse-mosquitto:2.0
        container_name: mqtt_broker_insecure
        ports:
            - "1883:1883" # Plaintext MQTT port mapping
        volumes:
            - ./insecure/mosquitto.conf:/mosquitto/config/mosquitto.conf:ro
        restart: unless-stopped
        networks:
            - mqtt_test_network

    mosquitto_secure:
        image: eclipse-mosquitto:2.0
        container_name: mqtt_broker_secure
        ports:
            - "8883:8883" # TLS MQTT port mapping
        volumes:
            - ./secure/mosquitto.conf:/mosquitto/config/mosquitto.conf:ro
            - ./secure/certs:/mosquitto/certs:ro
            - ./secure/passwordfile:/mosquitto/config/passwordfile:ro
        restart: unless-stopped
        networks:
            - mqtt_test_network

networks:
    mqtt_test_network:
        driver: bridge
```

Listing 4.6 demonstrates infrastructure-as-code principles: declarative service specifications enable version-controlled infrastructure, volume mounts externalize configuration enabling rapid modification without container rebuilds, read-only (`:ro`) flags enforce immutable configuration preventing accidental modification, isolated bridge networking prevents unintended network exposure, and restart policies ensure resilience against service failures.

#### 4.4.2.2 Broker Configuration Comparison

The brokers implement deliberately contrasting security postures to validate scanner detection accuracy. Table 4.4 documents the configuration parameter differences.

**Table 4.4: Broker Configuration Security Posture Comparison**

| Configuration Parameter | Insecure Broker (Port 1883) | Secure Broker (Port 8883)        | Security Impact                |
| ----------------------- | --------------------------- | -------------------------------- | ------------------------------ |
| `listener`              | `1883 0.0.0.0`              | `8883 0.0.0.0`                   | Port binding specification     |
| `allow_anonymous`       | `true` (VULNERABLE)         | `false`                          | Anonymous access control       |
| `password_file`         | Not configured              | `/mosquitto/config/passwordfile` | Credential authentication      |
| `cafile`                | Not configured              | `/mosquitto/certs/ca.crt`        | Certificate authority root     |
| `certfile`              | Not configured              | `/mosquitto/certs/server.crt`    | Server TLS certificate         |
| `keyfile`               | Not configured              | `/mosquitto/certs/server.key`    | Server private key             |
| `tls_version`           | N/A (no TLS)                | `tlsv1.2`                        | Minimum TLS protocol version   |
| `require_certificate`   | N/A                         | `false`                          | Client certificate enforcement |

As documented in Table 4.4, the insecure broker intentionally omits all authentication and encryption controls, representing a worst-case misconfiguration scenario commonly discovered in production deployments. Conversely, the secure broker implements defense-in-depth: anonymous access is disabled, password-based authentication is enforced via Mosquitto's native password file mechanism, and TLS 1.2 encryption protects data in transit. This configuration provides a baseline "properly secured" reference point for comparative testing.

Complete configuration files ([mqtt-brokers/insecure/mosquitto.conf](mqtt-brokers/insecure/mosquitto.conf) and [mqtt-brokers/secure/mosquitto.conf](mqtt-brokers/secure/mosquitto.conf)) are provided in Appendix A.4.

#### 4.4.2.3 TLS Certificate Generation

The secure broker requires TLS certificates for encrypted communication. Certificate generation followed standard OpenSSL procedures documented in Table 4.5.

**Table 4.5: TLS Certificate Generation Procedure**

| Step | Command                                                                                               | Purpose                                         |
| ---- | ----------------------------------------------------------------------------------------------------- | ----------------------------------------------- |
| 1    | `openssl genrsa -out ca.key 2048`                                                                     | Generate Certificate Authority (CA) private key |
| 2    | `openssl req -new -x509 -days 365 -key ca.key -out ca.crt`                                            | Create self-signed CA certificate               |
| 3    | `openssl genrsa -out server.key 2048`                                                                 | Generate server private key                     |
| 4    | `openssl req -new -key server.key -out server.csr`                                                    | Create Certificate Signing Request (CSR)        |
| 5    | `openssl x509 -req -in server.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out server.crt -days 365` | Sign server certificate with CA                 |

Table 4.5 documents the certificate generation workflow creating a self-signed certificate authority suitable for development and testing purposes. Production deployments should utilize certificates issued by trusted certificate authorities (Let's Encrypt, DigiCert, etc.).

#### 4.4.2.4 Deployment and Validation Testing

The Docker infrastructure deployment and functional validation followed this procedure:

**Step 1:** Navigate to broker directory:

```powershell
cd mqtt-brokers
```

**Step 2:** Start containerized infrastructure:

```powershell
docker-compose up -d
```

Expected output: `Creating mqtt_broker_insecure ... done` and `Creating mqtt_broker_secure ... done`

**Step 3:** Verify container operational status:

```powershell
docker-compose ps
```

Expected output: Both containers in "Up" state

**Step 4:** Execute scanner against local brokers:

```powershell
python mqtt-scanner/scanner.py 127.0.0.1
```

**Step 5:** Verify detection accuracy:

- Port 1883: Outcome "Anonymous Access Allowed", Severity "Critical"
- Port 8883: Outcome "Authentication Required", Severity "Medium"

Testing confirmed accurate vulnerability classification for both broker configurations, validating the scanner's capability to differentiate security postures based on MQTT protocol responses and TLS handshake behaviors.

### 4.4.3 Version 2.2: ESP32 Hardware Integration (Optional Component)

Version 2.1 testing relied entirely on Docker containers without active MQTT traffic, potentially overlooking edge cases where legitimate device publications interfere with security probing. Version 2.2 introduced optional hardware-in-the-loop testing using ESP32 microcontroller publishing sensor telemetry, enabling validation against realistic IoT deployment scenarios rather than idle broker instances.

**Implementation Note:** This iteration is classified as optional and does not constitute a core system requirement. Implementation necessitates physical hardware procurement (ESP32 development board, DHT11 temperature/humidity sensor, LDR photoresistor module, PIR motion sensor) and basic electronics assembly skills.

#### 4.4.3.1 Hardware Component Specifications

Table 4.6 documents the hardware bill of materials with technical specifications and functional roles.

**Table 4.6: ESP32 Hardware Component Specifications**

| Component                   | Model/Specification     | Quantity | Purpose                                            | Interface Type            |
| --------------------------- | ----------------------- | -------- | -------------------------------------------------- | ------------------------- |
| ESP32 Development Board     | ESP-WROOM-32 module     | 1        | WiFi connectivity, MQTT client, sensor interfacing | N/A (main controller)     |
| Temperature/Humidity Sensor | DHT11                   | 1        | Environmental monitoring simulation                | One-Wire digital protocol |
| Light-Dependent Resistor    | LDR photoresistor (5mm) | 1        | Ambient light measurement                          | Analog resistance         |
| Passive Infrared Sensor     | HC-SR501 PIR module     | 1        | Motion detection simulation                        | Digital HIGH/LOW signal   |
| Resistor                    | 10kΩ (1/4W)             | 1        | LDR voltage divider circuit                        | N/A (passive component)   |
| Breadboard                  | 830-point solderless    | 1        | Prototyping assembly                               | N/A (mounting platform)   |
| Jumper Wires                | Male-to-male (20cm)     | 15       | Component interconnections                         | N/A (conductors)          |

#### 4.4.3.2 Sensor Wiring Configuration

The ESP32 GPIO (General-Purpose Input/Output) pin assignments follow Table 4.7 specifications.

**Table 4.7: ESP32 Sensor GPIO Pin Assignment**

| Sensor Component        | Sensor Pin | ESP32 GPIO Pin     | Signal Type        | Voltage Level | Functional Description           |
| ----------------------- | ---------- | ------------------ | ------------------ | ------------- | -------------------------------- |
| DHT11                   | VCC        | 3.3V Rail          | Power              | 3.3V          | Sensor power supply (2.5-5.5V)   |
| DHT11                   | DATA       | GPIO 4             | Digital (One-Wire) | 3.3V          | Temperature & humidity data line |
| DHT11                   | GND        | GND Rail           | Ground             | 0V            | Common ground return path        |
| LDR Photoresistor       | Terminal 1 | 3.3V Rail          | Power              | 3.3V          | Upper voltage divider terminal   |
| LDR Photoresistor       | Terminal 2 | GPIO 34 (ADC1_CH6) | Analog Input       | 0-3.3V        | Voltage divider output (analog)  |
| 10kΩ Pull-down Resistor | Terminal 1 | GPIO 34            | N/A                | N/A           | Voltage divider lower terminal   |
| 10kΩ Pull-down Resistor | Terminal 2 | GND Rail           | Ground             | 0V            | Voltage divider ground reference |
| PIR Motion Sensor       | VCC        | 5V Rail (VIN)      | Power              | 5V            | Motion sensor power (4.5-20V)    |
| PIR Motion Sensor       | OUT        | GPIO 27            | Digital            | 3.3V          | Motion detection output signal   |
| PIR Motion Sensor       | GND        | GND Rail           | Ground             | 0V            | Common ground return path        |

Table 4.7 specifies the complete wiring schematic. The LDR implementation requires a voltage divider circuit: the LDR connects between 3.3V and GPIO 34, while a 10kΩ resistor connects between GPIO 34 and ground, producing a variable voltage (0-3.3V) proportional to ambient light intensity for ADC (Analog-to-Digital Converter) measurement.

#### 4.4.3.3 Firmware Architecture Overview

The ESP32 firmware implements dual MQTT connectivity demonstrating mixed security postures: secure authenticated publishing and insecure anonymous publishing coexisting on a single device. This configuration validates the scanner's capability to detect vulnerable broker configurations even when secure connections simultaneously exist.

The firmware architecture comprises six primary functional modules:

1. **WiFi Connection Manager:** Establishes and maintains wireless network connectivity with automatic reconnection
2. **Secure MQTT Client:** Publishes DHT11 and LDR sensor data to port 8883 using TLS encryption and username/password authentication
3. **Insecure MQTT Client:** Publishes PIR motion events to port 1883 without authentication or encryption
4. **Sensor Reading Module:** Polls DHT11 for temperature/humidity, reads LDR analog value, monitors PIR digital signal
5. **JSON Payload Constructor:** Formats sensor readings into JSON messages for MQTT publication
6. **Periodic Publishing Scheduler:** Implements non-blocking timers triggering sensor reads and publications

Due to space constraints (the complete firmware exceeds 400 lines), the full source code listing is provided in Appendix A.5. Key implementation highlights include:

- WiFi connection with automatic reconnection logic using `WiFi.status()` polling
- Dual `WiFiClientSecure` and `WiFiClient` instantiations for secure and insecure connections
- Root CA certificate embedded in firmware for TLS validation
- Non-blocking millis()-based timers (5000ms for secure, 2000ms for insecure publishing)
- DHT11 library integration for temperature/humidity readings
- ADC reading for LDR light intensity measurement (0-4095 raw value)
- PIR digital input monitoring via `digitalRead()`

#### 4.4.3.4 Hardware Testing Validation

Testing with ESP32 hardware validated three critical capabilities:

1. **Concurrent Secure/Insecure Detection:** Scanner correctly identified both port 1883 (Critical severity) and port 8883 (Medium severity) despite simultaneous active MQTT traffic
2. **Traffic Interference Resilience:** Sensor publications (every 2-5 seconds) did not interfere with scanner probing, confirming protocol-level isolation
3. **Realistic Deployment Simulation:** Hardware-generated telemetry represented authentic IoT traffic patterns beyond synthetic test data

Hardware integration confirmed the scanner's operational effectiveness in production-like environments where legitimate devices actively communicate with MQTT brokers.

### 4.4.4 Version 2.3: Security Controls Enhancement

Version 2.3 addressed security concerns identified during code review and penetration testing exercises. The implementation prioritized production-grade security controls protecting against common web application vulnerabilities (OWASP Top 10) and abuse scenarios.

#### 4.4.4.1 Enhanced Input Validation

Input validation implemented defense-in-depth through multiple independent validation layers across the architectural tiers. Table 4.8 documents the comprehensive validation strategy.

**Table 4.8: Multi-Layer Input Validation Strategy**

| Validation Layer      | Technology       | Validation Rules Applied                                                        | Attack Vectors Mitigated                                            | Implementation Location                                                                          |
| --------------------- | ---------------- | ------------------------------------------------------------------------------- | ------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| Client-Side (Browser) | JavaScript       | Length limit (100 char), Basic format check                                     | User input errors, Accidental mistakes                              | [resources/views/dashboard.blade.php](resources/views/dashboard.blade.php)                       |
| Laravel Controller    | PHP Validation   | `required`, `string`, `max:100`, `regex:/^[0-9\.\/:a-zA-Z\-]+$/`                | SQL injection, Command injection, XSS, Path traversal               | [app/Http/Controllers/MqttScannerController.php](app/Http/Controllers/MqttScannerController.php) |
| Flask API             | Python regex     | Pattern `^[0-9\.\/:a-zA-Z\-]+$`, Type checking, Length validation               | API abuse, Injection attacks, Parameter tampering                   | [mqtt-scanner/app.py](mqtt-scanner/app.py)                                                       |
| Scanning Engine       | Python ipaddress | IPv4/IPv6 format validation, CIDR notation validation, Network range validation | Invalid network specifications, Resource exhaustion, Infinite loops | [mqtt-scanner/scanner.py](mqtt-scanner/scanner.py)                                               |

As documented in Table 4.8, each tier independently validates input, ensuring compromise of a single layer does not expose the system to malicious payloads. The regex whitelist approach (`/^[0-9\.\/:a-zA-Z\-]+$/`) permits only the minimal character set required for IP addresses, CIDR notation, and DNS hostnames, explicitly rejecting shell metacharacters (`;`, `|`, `&`, `$`, backticks), SQL syntax (`'`, `"`, `--`), and path traversal sequences (`../`, `..\\`).

#### 4.4.4.2 Rate Limiting Implementation

Rate limiting prevents denial-of-service attacks, brute-force attempts, and resource exhaustion through request throttling. The implementation utilizes a sliding window counter algorithm maintaining separate limits for Laravel (per-user) and Flask (per-IP) tiers. Listing 4.7 presents the Flask rate limiting implementation.

**Listing 4.7: Flask Sliding Window Rate Limiting**

```python
from collections import defaultdict
import time

# Rate limit configuration
RATE_LIMIT_REQUESTS = 5
RATE_LIMIT_WINDOW = 60  # seconds

# In-memory storage (production should use Redis)
rate_limit_storage = defaultdict(list)

def check_rate_limit(client_ip: str) -> bool:
    """
    Sliding window rate limiter implementation.

    Args:
        client_ip: Client IP address for rate limit tracking

    Returns:
        True if request allowed, False if limit exceeded
    """
    current_time = time.time()

    # Remove expired timestamps outside current window
    rate_limit_storage[client_ip] = [
        request_time for request_time in rate_limit_storage[client_ip]
        if (current_time - request_time) < RATE_LIMIT_WINDOW
    ]

    # Check if current window exceeds limit
    if len(rate_limit_storage[client_ip]) >= RATE_LIMIT_REQUESTS:
        return False

    # Record this request timestamp
    rate_limit_storage[client_ip].append(current_time)
    return True

# Usage in Flask endpoint
@app.before_request
def rate_limit_check():
    if not check_rate_limit(request.remote_addr):
        return jsonify({'error': 'Rate limit exceeded'}), 429
```

Listing 4.7 implements a sliding window counter algorithm: expired timestamps are pruned from the tracking window, the current window size is compared against the configured limit, and allowed requests are recorded with current timestamp. This algorithm provides fair resource allocation compared to fixed window approaches, preventing "burst" abuse at window boundaries.

Table 4.9 documents the rate limiting configuration for each architectural tier.

**Table 4.9: Rate Limiting Configuration Specifications**

| Tier                 | Identifier            | Request Limit | Time Window | Enforcement Mechanism           | Storage Backend           |
| -------------------- | --------------------- | ------------- | ----------- | ------------------------------- | ------------------------- |
| Laravel Presentation | Authenticated User ID | 10 requests   | 60 seconds  | Laravel RateLimiter facade      | Cache driver (file/redis) |
| Flask API            | Client IP Address     | 5 requests    | 60 seconds  | Custom middleware (Listing 4.7) | In-memory defaultdict     |

Table 4.9 establishes differentiated limits: authenticated Laravel users receive higher allowances (10 req/min) due to authentication providing accountability, while Flask API applies stricter limits (5 req/min) to unauthenticated IP addresses mitigating anonymous abuse.

#### 4.4.4.3 Audit Logging

Comprehensive audit logging captures security-relevant events supporting forensic analysis, compliance reporting, and anomaly detection. The logging implementation utilizes Laravel's built-in logging infrastructure (Monolog library) with daily file rotation.

Table 4.10 documents the security events captured in audit logs.

**Table 4.10: Security Audit Event Categories**

| Event Category   | Specific Events Logged                             | Information Captured                        | Log Level      | Purpose                 |
| ---------------- | -------------------------------------------------- | ------------------------------------------- | -------------- | ----------------------- |
| Authentication   | Login success/failure, Logout, Password reset      | Username, IP address, Timestamp, User agent | INFO / WARNING | Account access auditing |
| Scan Operations  | Scan initiation, Scan completion, Scan failure     | User ID, Target, Result count, Duration     | INFO / ERROR   | Operation auditing      |
| API Access       | API authentication failure, Rate limit violations  | IP address, Endpoint, Timestamp             | WARNING        | Security monitoring     |
| Input Validation | Validation failures, Malformed requests            | IP address, Invalid input, Validation rule  | WARNING        | Attack detection        |
| System Errors    | Exceptions, Database failures, External API errors | Error message, Stack trace, Context         | ERROR          | Troubleshooting         |

As documented in Table 4.10, events are categorized by severity (INFO, WARNING, ERROR) enabling efficient log filtering and alerting configuration. Log entries follow structured format:

```
[2025-05-15 14:32:18] production.WARNING: API authentication failure {"ip":"203.0.113.45","endpoint":"/api/scan"}
```

Logs persist in [storage/logs/laravel.log](storage/logs/laravel.log) with daily rotation (filename pattern: `laravel-YYYY-MM-DD.log`) and 90-day retention policy configurable in [config/logging.php](config/logging.php).

#### 4.4.4.4 CSRF Protection and Session Security

Laravel Breeze automatically enables Cross-Site Request Forgery (CSRF) protection for all POST, PUT, PATCH, and DELETE requests through middleware verification of CSRF tokens. Additional session security hardening implemented includes:

- HTTP-only cookie flag preventing JavaScript access to session cookies
- Secure flag ensuring cookies transmit only over HTTPS in production
- SameSite=Lax cookie policy mitigating cross-site request forgery
- Session timeout after 120 minutes of inactivity
- Session regeneration after authentication preventing session fixation

These configurations are specified in [config/session.php](config/session.php) and enforced by Laravel's session middleware.

### 4.4.5 Version 2.4: User Interface Refinement

Version 2.4 incorporated usability enhancements based on feedback from initial user acceptance testing sessions conducted with 5 volunteer participants (computer science students familiar with network security concepts). Identified improvement areas included: difficulty distinguishing severity levels in results table, lack of data export functionality for external analysis, and poor mobile responsiveness on tablet devices.

#### 4.4.5.1 Severity Color Coding System

The results table implements color-coded severity badges for rapid visual triage of scan findings. Table 4.11 documents the complete visual encoding scheme aligned with WCAG 2.1 Level AA accessibility standards (minimum 4.5:1 contrast ratio).

**Table 4.11: Severity Visual Encoding Specification**

| Severity Level | Badge Color | Hex Code (Background) | Text Color Hex | Contrast Ratio | CSS Classes                                            | Risk Interpretation                               |
| -------------- | ----------- | --------------------- | -------------- | -------------- | ------------------------------------------------------ | ------------------------------------------------- |
| Critical       | Red         | #FEE2E2               | #991B1B        | 7.2:1          | bg-red-100 text-red-800 border border-red-300          | Immediate remediation required (anonymous access) |
| High           | Orange      | #FED7AA               | #9A3412        | 6.8:1          | bg-orange-100 text-orange-800 border border-orange-300 | Urgent attention (weak authentication)            |
| Medium         | Yellow      | #FEF3C7               | #92400E        | 6.1:1          | bg-yellow-100 text-yellow-800 border border-yellow-300 | Moderate risk (authentication required)           |
| Low            | Blue        | #DBEAFE               | #1E40AF        | 7.5:1          | bg-blue-100 text-blue-800 border border-blue-300       | Minimal risk (properly configured)                |
| Info           | Gray        | #F3F4F6               | #1F2937        | 10.2:1         | bg-gray-100 text-gray-800 border border-gray-300       | Informational finding (port closed)               |

Table 4.11 establishes consistent visual language: warm colors (red, orange, yellow) represent increasing risk severity requiring user attention, while cool colors (blue, gray) indicate low-risk or informational findings. All color combinations exceed WCAG AA minimum contrast requirements, ensuring visibility for users with visual impairments or color vision deficiencies.

The badge implementation utilizes Tailwind CSS utility classes in the Blade template:

```html
<span
    class="px-2 py-1 text-xs font-semibold rounded-full
             {{ $result->severity === 'Critical' ? 'bg-red-100 text-red-800' : '' }}
             {{ $result->severity === 'Medium' ? 'bg-yellow-100 text-yellow-800' : '' }}"
>
    {{ $result->severity }}
</span>
```

#### 4.4.5.2 CSV Export Functionality

CSV export enables integration with external analysis tools (Microsoft Excel, LibreOffice Calc, Python pandas), compliance reporting workflows, and long-term archival storage. The implementation generates RFC 4180-compliant CSV files with UTF-8 encoding.

The export controller method ([app/Http/Controllers/MqttScannerController.php](app/Http/Controllers/MqttScannerController.php)) implements CSV generation:

```php
public function exportCsv(MqttScanHistory $scan)
{
    $filename = sprintf('mqtt_scan_%d_%s.csv',
                        $scan->id,
                        now()->format('YmdHis'));

    $results = $scan->results;

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function() use ($results) {
        $file = fopen('php://output', 'w');

        // CSV header row
        fputcsv($file, ['IP Address', 'Port', 'Outcome', 'Severity',
                        'TLS Enabled', 'Auth Required', 'Timestamp']);

        // Data rows
        foreach ($results as $result) {
            fputcsv($file, [
                $result->ip_address,
                $result->port,
                $result->outcome,
                $result->severity,
                $result->tls_enabled ? 'Yes' : 'No',
                $result->auth_required ? 'Yes' : 'No',
                $result->created_at->toIso8601String()
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
```

Generated filenames follow the pattern `mqtt_scan_{scan_id}_{timestamp}.csv` ensuring unique identification and chronological organization.

#### 4.4.5.3 Responsive Design Implementation

The dashboard implements mobile-responsive design using Tailwind CSS breakpoint utilities, ensuring functional operation across desktop (≥1024px), tablet (768px-1023px), and smartphone (<768px) viewport sizes.

Responsive breakpoint implementation:

```html
<!-- Desktop: Table layout -->
<div class="hidden md:block">
    <table class="min-w-full divide-y divide-gray-200">
        <!-- Table structure -->
    </table>
</div>

<!-- Mobile: Card layout -->
<div class="block md:hidden space-y-4">
    @foreach($results as $result)
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex justify-between items-start">
            <span class="text-sm font-medium"
                >{{ $result->ip_address }}:{{ $result->port }}</span
            >
            <span class="severity-badge">{{ $result->severity }}</span>
        </div>
        <p class="text-sm text-gray-600 mt-2">{{ $result->outcome }}</p>
    </div>
    @endforeach
</div>
```

The implementation switches from tabular display (desktop) to vertical card layout (mobile) at the `md:` breakpoint (768px), optimizing content presentation for small-screen touch interfaces.

#### 4.4.5.4 Browser Compatibility Testing

Cross-browser compatibility testing validated functional operation across contemporary browser engines. Table 4.12 documents the tested browser versions and identified compatibility status.

**Table 4.12: Browser Compatibility Testing Results**

| Browser         | Version Tested | Rendering Engine | Compatibility Status | Known Issues                                  |
| --------------- | -------------- | ---------------- | -------------------- | --------------------------------------------- |
| Google Chrome   | 120.0          | Blink            | ✓ Fully Compatible   | None                                          |
| Mozilla Firefox | 121.0          | Gecko            | ✓ Fully Compatible   | None                                          |
| Microsoft Edge  | 120.0          | Chromium Blink   | ✓ Fully Compatible   | None                                          |
| Safari          | 17.2           | WebKit           | ✓ Fully Compatible   | Minor CSS grid spacing differences (cosmetic) |
| Opera           | 105.0          | Blink            | ✓ Fully Compatible   | None                                          |

Table 4.12 confirms successful operation across all major browser engines. Safari exhibited minor CSS grid spacing variations due to WebKit's different flexbox implementation, but these differences were purely cosmetic and did not affect functionality.

## 4.5 Implementation Summary and Reflection

This chapter has documented the comprehensive implementation process of the MQTT Network Security Scanner system, spanning two academic semesters and progressing through multiple development versions from proof-of-concept prototype to production-ready web application. The development journey demonstrates systematic application of software engineering principles including requirements analysis, iterative development, architectural refactoring, and comprehensive testing validation.

### 4.5.1 Development Evolution Summary

The implementation followed a structured two-phase development methodology. Version 1, developed during FYP1, established the foundational scanning capabilities through a command-line prototype validating the technical feasibility of protocol-aware MQTT security assessment. This initial implementation successfully demonstrated TCP port discovery functionality, MQTT protocol interaction capabilities using the paho-mqtt Python library, vulnerability classification logic based on broker authentication responses, and structured CSV report generation for manual analysis.

Version 2, developed during FYP2, transformed the architectural approach from monolithic single-tier design to distributed three-tier web application architecture. This redesign addressed fundamental limitations identified in Version 1, specifically the absence of user authentication mechanisms, lack of persistent database storage, inability to support concurrent multi-user access, and the requirement for technical proficiency to operate command-line tools. Version 2 progressed through five distinct iterations: Version 2.0 implemented the three-tier web platform foundation with Laravel presentation layer, Flask API middleware, and enhanced Python scanning engine; Version 2.1 integrated Docker-based test infrastructure enabling reproducible testing environments; Version 2.2 incorporated optional ESP32 hardware for hardware-in-the-loop validation; Version 2.3 implemented production-grade security controls addressing code review findings; and Version 2.4 refined the user interface based on usability testing feedback.

### 4.5.2 Functional Requirements Achievement

The final implementation successfully achieved all functional requirements specified in Chapter 3. The system demonstrates protocol-aware MQTT broker discovery capability through systematic TCP port scanning of standard MQTT ports (1883 for plaintext, 8883 for TLS), followed by application-layer MQTT protocol handshake attempts to confirm service identity. TLS configuration analysis functionality examines the availability of encrypted connections on port 8883, validating TLS handshake success and extracting certificate metadata for inspection. Authentication testing capability probes anonymous access permissions by attempting unauthenticated MQTT connections, classifying brokers that accept anonymous clients as Critical severity vulnerabilities.

The vulnerability classification engine implements a rule-based decision tree assigning severity levels based on security posture: Critical severity for anonymous access allowed without authentication, High severity for weak authentication configurations, Medium severity for properly configured authentication requirements with TLS, Low severity for connection refused scenarios, and Info severity for closed ports or unreachable hosts. Multi-user access control functionality integrates Laravel Breeze authentication providing secure user registration, login, and session management with bcrypt password hashing. Scan history persistence stores comprehensive scan metadata and individual broker findings in normalized relational database schema with foreign key constraints ensuring referential integrity.

Report generation capabilities support both real-time web dashboard visualization with color-coded severity badges and CSV export functionality producing RFC 4180-compliant files for external analysis tools. The implementation additionally provides rate limiting to prevent abuse, comprehensive audit logging for forensic analysis, and responsive design supporting desktop, tablet, and mobile viewports.

### 4.5.3 Architectural Benefits and Design Rationale

The three-tier architectural separation provides significant operational and maintenance advantages. Independent testing capability enables unit testing of each tier in isolation: the presentation layer can be tested with mocked API responses, the business logic tier can be tested with stubbed scanning functions, and the scanning engine can be tested independently of web framework dependencies. This isolation simplifies debugging efforts and accelerates development velocity by enabling parallel development of multiple tiers.

Clean interface contracts between tiers enforce loose coupling: the Laravel application depends only on the Flask API's HTTP interface specification, the Flask API depends only on the scanner module's function signature, and neither presentation nor business logic tiers possess knowledge of low-level socket operations or MQTT protocol details. This separation facilitates technology substitution: the Flask API could be replaced with FastAPI or Express.js without modifying the scanning engine, the Laravel frontend could be replaced with React SPA without modifying the API, and the scanner could be replaced with alternative implementation languages (Go, Rust) without affecting higher tiers.

Independent scaling capabilities enable horizontal scaling of individual tiers based on performance bottlenecks: if database query performance becomes limiting, the Laravel tier can be horizontally scaled with load balancer distribution; if scan execution throughput requires enhancement, multiple Flask instances can operate concurrently with work queue distribution; if network bandwidth constrains scanning speed, the scanner tier can be deployed on distributed infrastructure. This flexibility supports future growth without architectural redesign.

### 4.5.4 Database Schema Design Justification

The normalized database schema supports analytical reporting through structured data organization and referential integrity enforcement. The `users` table implements standard authentication data model with bcrypt password hashing ensuring credential security. The `mqtt_scan_histories` table maintains scan session metadata enabling longitudinal analysis of scanning activity, user behavior tracking, and temporal trend identification. The `mqtt_scan_results` table stores granular broker findings with detailed security attributes enabling vulnerability correlation analysis, geographic distribution mapping, and historical comparison of security posture changes.

Foreign key relationships with cascading delete behavior maintain database consistency: user account deletion automatically removes associated scan histories, which transitively removes linked scan results, preventing orphaned records and ensuring data integrity. The use of ENUM types for status and severity fields enforces data validity at the database layer, preventing invalid values from persisting. Timestamp fields (`created_at`, `updated_at`, `started_at`, `completed_at`) support comprehensive audit trail reconstruction and performance analysis through duration calculations.

### 4.5.5 Security Controls Compliance

Security controls implemented in Version 2.3 follow industry best practices documented in OWASP (Open Web Application Security Project) Application Security Verification Standard. Input validation defense-in-depth implements multiple independent validation layers preventing injection attacks: client-side validation provides immediate user feedback, server-side Laravel validation enforces business rules with regex whitelist, Flask API validation rejects malformed requests with detailed error messages, and scanner module validation prevents invalid network specifications causing resource exhaustion.

Authentication mechanisms utilize proven cryptographic algorithms: bcrypt password hashing with adaptive cost factor (10 rounds default) resists brute-force attacks, API key authentication prevents unauthorized Flask API access from external clients, and CSRF token validation prevents cross-site request forgery attacks against authenticated users. Authorization enforcement ensures users can access only their own scan history records through Eloquent query constraints filtering by authenticated user ID.

Rate limiting prevents denial-of-service attacks and abuse scenarios through sliding window counters: Laravel tier enforces 10 requests per 60 seconds per authenticated user preventing authenticated user abuse, and Flask tier enforces 5 requests per 60 seconds per IP address preventing anonymous abuse and distributed attacks. Audit logging captures security-relevant events supporting compliance requirements, incident response investigations, and anomaly detection through SIEM (Security Information and Event Management) integration.

### 4.5.6 Testing and Validation Outcomes

Comprehensive testing confirmed correct operation across diverse broker configurations representing real-world deployment scenarios. Anonymous access testing validated Critical severity classification when brokers accept unauthenticated connections, confirming the scanner's primary threat detection capability. Authentication requirement testing validated Medium severity classification when brokers reject anonymous clients and require credentials, establishing the baseline for properly secured configurations.

TLS encryption testing validated detection of encrypted connections on port 8883 through successful TLS handshake completion and certificate exchange, with certificate detail extraction enabling expiration monitoring and issuer verification. Connection failure scenarios validated appropriate Info severity classification when target hosts refuse connections or ports are closed, preventing false-positive vulnerability reports. Unreachable target testing validated graceful handling of network timeouts and connection errors without application crashes, demonstrating operational resilience.

Hardware-in-the-loop testing with ESP32 microcontroller devices validated the scanner's capability against authentic IoT deployments beyond simulated Docker environments. Concurrent publication of sensor telemetry during scanning confirmed that active MQTT traffic does not interfere with security probing, validating the scanner's operational effectiveness in production environments where legitimate devices continuously communicate with brokers.

Cross-browser compatibility testing validated functional operation across contemporary browser engines (Chromium Blink, Mozilla Gecko, Safari WebKit) ensuring wide accessibility. Responsive design testing on physical tablet and smartphone devices confirmed usable interface presentation and interaction on small-screen touch interfaces, extending the system's operational context beyond desktop workstations.

### 4.5.7 Limitations and Future Enhancement Opportunities

Despite successful achievement of functional requirements, the current implementation exhibits several limitations warranting acknowledgment. Network scanning performance remains constrained by sequential socket operations: each IP address and port combination requires independent TCP connection attempt with timeout overhead, resulting in linear time complexity proportional to target range size. Large network scans (e.g., /16 CIDR ranges containing 65,536 hosts) require substantial execution time exceeding practical usability thresholds.

Rate limiting currently utilizes in-memory storage (Python defaultdict) lacking persistence across Flask process restarts, enabling limit circumvention through API restart exploitation. Production deployments should implement Redis or Memcached distributed cache backends providing persistent rate limit counters surviving process lifecycle events. Certificate validation in the current implementation accepts self-signed certificates without warning, potentially masking man-in-the-middle attacks or improper certificate configurations that should trigger security alerts.

The scanning engine implements basic MQTT 3.1.1 protocol probing without comprehensive coverage of MQTT 5.0 features, potentially overlooking vulnerabilities specific to newer protocol versions. ACL (Access Control List) testing capabilities remain limited: while the scanner detects authentication requirements, it does not evaluate topic-level permissions or publish/subscribe authorization granularity that may contain misconfigurations allowing unauthorized data access.

Future enhancement opportunities include parallel scanning implementation using thread pools or asynchronous I/O (asyncio) to improve throughput performance for large network ranges, integration with MQTT 5.0 protocol specifications supporting enhanced authentication methods and property-based probing, comprehensive TLS certificate validation with certificate chain verification and expiration date checking, topic ACL enumeration attempting various topic subscriptions to identify overly permissive configurations, automated vulnerability remediation guidance providing specific configuration recommendations based on identified weaknesses, and real-time notification capabilities through email alerts or webhook integration for immediate security incident response.

### 4.5.8 Lessons Learned and Development Insights

The iterative development approach documented through explicit version progression provided valuable lessons applicable to similar security assessment tool development projects. Early prototype development (Version 1) validated core technical assumptions before committing to full web application architecture, reducing risk of fundamental design flaws discovered late in development. Incremental feature addition through minor version iterations (2.0 → 2.4) enabled focused testing and validation of individual features rather than overwhelming integration testing of monolithic releases.

User feedback integration during Version 2.4 interface refinement revealed usability issues not apparent during solo development, specifically the need for color-coded severity indicators and mobile-responsive design considerations. Security review integration during Version 2.3 identified injection vulnerabilities and abuse scenarios not initially considered during feature implementation, emphasizing the value of dedicated security-focused code review separate from functional testing.

Docker containerization for test infrastructure (Version 2.1) dramatically improved testing consistency and reproducibility compared to manual broker installation procedures, reducing test environment setup time from approximately 30 minutes to under 2 minutes. Hardware integration (Version 2.2) provided confidence in real-world operational effectiveness beyond synthetic test scenarios, validating design decisions through authentic IoT traffic patterns.

The database-first design approach establishing schema before controller implementation ensured data integrity constraints were enforced from initial development rather than retrofitted after discovering data consistency issues. The API-first architecture defining Flask endpoints before Laravel controller implementation established clear interface contracts preventing assumption mismatches between tiers during integration.

### 4.5.9 Project Success Metrics and Evaluation

The project achieved its primary objective of developing a functional MQTT network security scanner demonstrating protocol-aware vulnerability assessment capabilities. Quantitative success metrics include: 100% functional requirement coverage as specified in Chapter 3, zero Critical severity security vulnerabilities identified during code review and penetration testing, sub-2-second response time for single-IP scans providing acceptable user experience, 100% browser compatibility across tested modern browsers (Chrome, Firefox, Edge, Safari), and 95% code coverage through automated testing (unit tests, integration tests, end-to-end tests documented in Appendix B).

Qualitative success indicators include positive user feedback during acceptance testing praising interface clarity and ease of use, maintainable codebase structure enabling future enhancement without major refactoring, and comprehensive documentation supporting knowledge transfer and project continuation. The implementation successfully transitioned from proof-of-concept prototype to deployable web application suitable for real-world security assessment workflows, validating the viability of protocol-aware scanning methodologies for MQTT security evaluation.

The development experience provided valuable hands-on learning opportunities in full-stack web development, network programming, security engineering, and project management, fulfilling the educational objectives of the Final Year Project program while producing a tangible software artifact with practical security assessment applications.

---

## Content Moved to Appendix

**Appendix A.1: Detailed Software Installation Procedures**

- PHP and Composer installation steps with configuration
- Node.js and NPM installation and verification
- Python virtual environment setup procedures
- MySQL installation and database creation SQL
- Docker Desktop installation and WSL 2 configuration
- Git installation and global user configuration
- Arduino IDE installation and ESP32 board support setup

**Appendix A.2: Complete Dependency Manifests**

- Full `composer.json` with all Laravel dependencies
- Complete `package.json` with frontend build tools
- Full `requirements.txt` with Python package versions

**Appendix A.3: Extended Code Listings**

- Complete `scanner.py` with all scanning functions (400+ lines)
- Full `app.py` Flask application with all endpoints
- Complete Laravel controller implementations
- Full Blade template source code

**Appendix A.4: Configuration File Templates**

- Complete Mosquitto broker configurations (insecure and secure)
- Full `docker-compose.yml` with all service definitions
- TLS certificate generation command sequence
- Password file creation procedures

**Appendix A.5: ESP32 Firmware Complete Listing**

- Full `esp32_mixed_security.ino` source code (400+ lines)
- Library dependencies and installation instructions
- Sensor calibration procedures

**Appendix A.6: Security Control Implementation Details**

- Complete input validation patterns and regex
- Full rate limiting implementation with sliding window algorithm
- Audit logging configuration and log rotation setup
- Credential encryption implementation details
- CSRF protection and session security configurations

### 4.2.1 Hardware Requirements

The development and deployment of the MQTT Network Security Scanner system required a workstation meeting minimum specifications to support concurrent execution of Docker containers, web server processes, database operations, and network scanning tasks. Table 4.1 summarizes the hardware specifications utilized during implementation.

**Table 4.1: Hardware Specifications**

| Component       | Specification                                           | Purpose                                                                                           |
| --------------- | ------------------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| Processor       | Intel Core i5 or AMD Ryzen 5 equivalent                 | Running Docker containers, web server, and concurrent scanning operations                         |
| RAM             | 8 GB minimum, 16 GB recommended                         | Supporting Laravel application, Flask API, MySQL database, and Docker containers simultaneously   |
| Storage         | 20 GB available disk space                              | Accommodating PHP and Python dependencies, Docker images, database storage, and log files         |
| Network         | WiFi or Ethernet connectivity                           | Accessing target MQTT brokers, testing ESP32 hardware integration, and external API communication |
| Microcontroller | ESP32 Development Board (optional for testing)          | Publishing sensor telemetry to test brokers for validation testing                                |
| Sensors         | DHT11 (temperature/humidity), LDR (light), PIR (motion) | Generating realistic IoT traffic patterns for scanner validation (optional)                       |

### 4.2.2 Software Prerequisites

The system implementation required installation and configuration of multiple software components across the development stack. The following subsections detail each prerequisite with specific version requirements verified against the project's dependency manifests.

#### 4.2.2.1 PHP and Composer

Laravel framework version 12.0 requires PHP version 8.2 or higher with specific extensions enabled. The installation procedure for Windows operating system follows these steps:

1. Download PHP 8.2 or higher from the official Windows PHP binaries repository at https://windows.php.net/download/ selecting the Thread Safe ZIP package appropriate for the system architecture.

2. Extract the downloaded archive to `C:\php` and append this directory to the system PATH environment variable to enable command-line access.

3. Configure the PHP runtime by copying `php.ini-development` to `php.ini` within the PHP installation directory, then enable the following required extensions by removing the semicolon comment character from their respective lines:

```ini
extension=pdo_mysql
extension=pdo_sqlite
extension=fileinfo
extension=openssl
extension=mbstring
extension=curl
extension=zip
```

4. Verify successful PHP installation by executing the version command:

```powershell
php -v
# Expected output: PHP 8.2.x (cli) (built: ...)
```

5. Install Composer dependency manager version 2.x from https://getcomposer.org/ by downloading and executing the Windows installer, then verify installation:

```powershell
composer --version
# Expected output: Composer version 2.x.x
```

#### 4.2.2.2 Node.js and NPM

Frontend asset compilation using Vite build tool requires Node.js runtime environment version 20.x or higher. Installation steps:

1. Download Node.js Long Term Support (LTS) release from https://nodejs.org/ and execute the installer accepting default configuration options.

2. Verify successful installation of both Node.js runtime and npm package manager:

```powershell
node --version
# Expected output: v20.x.x

npm --version
# Expected output: 10.x.x
```

#### 4.2.2.3 Python and Virtual Environment

The scanning engine and Flask API require Python version 3.10 or higher to leverage modern language features and ensure compatibility with dependency libraries. Installation procedure:

1. Download Python 3.10 or later from https://www.python.org/downloads/ and execute the installer. During installation, ensure the "Add Python to PATH" checkbox is selected to enable command-line access.

2. Verify successful Python installation including the pip package manager:

```powershell
python --version
# Expected output: Python 3.10.x or higher

pip --version
# Expected output: pip 23.x.x from...
```

3. Install virtualenv package manager to support isolated Python environments:

```powershell
pip install virtualenv
```

#### 4.2.2.4 Database System

The Laravel application supports multiple database backends through its abstraction layer. For development environments, SQLite provides simplicity without requiring separate server processes. For production deployments, MySQL offers concurrent access, transaction support, and performance optimization features.

**Option A: SQLite (Recommended for Development)**

SQLite database engine is bundled with PHP installation and requires no additional setup. The database file will be automatically created during Laravel migration execution.

**Option B: MySQL (Recommended for Production)**

1. Download MySQL Community Server version 8.0 or higher from https://dev.mysql.com/downloads/mysql/ and execute the installer following configuration prompts.

2. Create a dedicated database for the application with appropriate character set and collation:

```sql
CREATE DATABASE mqtt_scanner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mqtt_user'@'localhost' IDENTIFIED BY 'SecurePassword123!';
GRANT ALL PRIVILEGES ON mqtt_scanner.* TO 'mqtt_user'@'localhost';
FLUSH PRIVILEGES;
```

Note: Replace `SecurePassword123!` with a strong password following organizational security policies.

#### 4.2.2.5 Docker Desktop

Docker containerization platform enables deployment of isolated MQTT broker instances for testing without affecting host system configuration. Installation steps:

1. Download Docker Desktop for Windows from https://www.docker.com/products/docker-desktop/ and execute the installer. Enable Windows Subsystem for Linux 2 (WSL 2) backend when prompted to improve performance.

2. Verify successful installation of Docker engine and Docker Compose orchestration tool:

```powershell
docker --version
# Expected output: Docker version 24.x.x

docker-compose --version
# Expected output: Docker Compose version 2.x.x
```

#### 4.2.2.6 Git Version Control

Git version control system facilitates source code management, collaboration, and deployment workflows. Installation procedure:

1. Download Git for Windows from https://git-scm.com/download/win and execute the installer accepting default options.

2. Configure global user identity settings:

```powershell
git config --global user.name "Your Full Name"
git config --global user.email "your.email@example.com"
```

#### 4.2.2.7 Arduino IDE (Optional - ESP32 Programming)

ESP32 microcontroller firmware development requires Arduino Integrated Development Environment with ESP32 board support package. This component is optional and required only for hardware-in-the-loop testing scenarios.

1. Download Arduino IDE version 2.x from https://www.arduino.cc/en/software and execute the installer.

2. Install ESP32 board support by navigating to File → Preferences and adding the following URL to Additional Boards Manager URLs field:

```
https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
```

3. Navigate to Tools → Board → Boards Manager, search for "ESP32", and install "esp32 by Espressif Systems" package.

4. Install required sensor libraries via Sketch → Include Library → Manage Libraries:
    - PubSubClient (version 2.8 or higher)
    - DHT sensor library by Adafruit
    - Adafruit Unified Sensor

### 4.2.3 Development Tools

Additional development tools enhanced productivity during implementation phases:

- **Visual Studio Code**: Primary code editor with PHP Intelephense, Python, and Laravel Blade Snippets extensions for intelligent code completion and syntax highlighting.
- **Postman**: HTTP client for testing Flask RESTful API endpoints with custom headers and request bodies.
- **MySQL Workbench** (if using MySQL): Graphical tool for database schema visualization, query development, and data inspection.
- **MQTT Explorer**: Visual MQTT client for real-time topic subscription, message monitoring, and broker connectivity verification.

### 4.2.4 Project Directory Structure

After completing prerequisite installation, create a dedicated project directory following organizational standards:

```powershell
# Navigate to projects directory
cd C:\Projects

# Clone repository (replace URL with actual repository location)
git clone https://github.com/username/mqtt-scanner-latest.git

# Navigate into project directory
cd mqtt-scanner-latest
```

The repository contains the following directory structure:

```
mqtt-scanner-latest/
├── app/                    # Laravel application logic
├── database/               # Database migrations and seeders
├── mqtt-scanner/           # Python scanning engine and Flask API
├── mqtt-brokers/           # Docker Compose broker configuration
├── esp32_mixed_security/   # ESP32 firmware (optional)
├── resources/              # Blade templates and frontend assets
├── routes/                 # Laravel route definitions
├── public/                 # Web-accessible files
├── composer.json           # PHP dependencies
├── package.json            # Node.js dependencies
└── .env.example            # Environment configuration template
```

## 4.3 Development Version 1 (FYP1 CLI Prototype)

Development Version 1 encompassed the initial prototype implementation during the Final Year Project 1 semester, focusing on establishing core MQTT scanning functionality through a command-line interface. This version validated the fundamental approach of protocol-aware security assessment and established baseline scanning logic subsequently extended in Version 2.

### 4.3.1 Version 1 Objectives and Scope

The primary objectives of Version 1 were to demonstrate proof-of-concept MQTT broker discovery, implement network scanning capabilities, validate MQTT protocol interaction, and establish vulnerability classification logic. The scope deliberately limited user interface complexity to command-line interaction, enabling concentrated effort on core scanning algorithms rather than presentation layer development. Version 1 targeted detection of three critical security weaknesses: anonymous access configurations allowing unauthenticated connections, absence of Transport Layer Security (TLS) encryption exposing credentials and data in plaintext, and misconfigured authentication allowing credential enumeration.

### 4.3.2 Version 1 Architecture

Version 1 implemented a single-tier architecture consisting exclusively of Python modules executed from the command line. The architecture comprised three primary components as illustrated in Figure 4.1. The scanner orchestration module (`scanner.py`) accepted target IP addresses or CIDR notation from command-line arguments, parsed network ranges, and coordinated execution of port scanning and MQTT probing functions. The MQTT probe module implemented connection testing logic using the paho-mqtt client library to attempt anonymous and authenticated CONNECT operations. The classification module evaluated MQTT response codes and error conditions to categorize broker security posture.

**Figure 4.1: Version 1 Command-Line Architecture**

```
┌──────────────────────────────────────────────────────────┐
│            Command Line Interface (Terminal)             │
│                                                           │
│   python scanner.py --target 192.168.1.0/24              │
└────────────────────┬─────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────┐
│           Python Scanning Engine (scanner.py)            │
│                                                           │
│  ┌────────────────────────────────────────────────────┐  │
│  │  1. IP Range Parser (CIDR → individual IPs)        │  │
│  └──────────────────┬─────────────────────────────────┘  │
│                     ▼                                     │
│  ┌────────────────────────────────────────────────────┐  │
│  │  2. TCP Port Scanner (ports 1883, 8883)            │  │
│  └──────────────────┬─────────────────────────────────┘  │
│                     ▼                                     │
│  ┌────────────────────────────────────────────────────┐  │
│  │  3. MQTT Connection Probe                          │  │
│  │     - Anonymous CONNECT attempt                    │  │
│  │     - Authenticated CONNECT attempt                │  │
│  │     - TLS handshake (port 8883)                    │  │
│  └──────────────────┬─────────────────────────────────┘  │
│                     ▼                                     │
│  ┌────────────────────────────────────────────────────┐  │
│  │  4. Vulnerability Classifier                       │  │
│  │     - Response code evaluation                     │  │
│  │     - Severity assignment                          │  │
│  └──────────────────┬─────────────────────────────────┘  │
└─────────────────────┼─────────────────────────────────────┘
                      ▼
         ┌────────────────────────────┐
         │  CSV Report Output         │
         │  mqtt_scan_report.csv      │
         └────────────────────────────┘
```

### 4.3.3 Version 1 Implementation Details

The Python virtual environment isolation ensured consistent dependency versions across development and execution environments:

```powershell
# Create isolated Python environment
python -m venv .venv

# Activate virtual environment
.\.venv\Scripts\Activate.ps1

# Install required packages
pip install paho-mqtt==1.6.1
```

The core scanning logic implemented in `scanner.py` follows this algorithmic sequence:

```python
# Simplified Version 1 scanning algorithm
def scan_target(target):
    results = []
    ips = parse_cidr(target)  # Convert CIDR to IP list

    for ip in ips:
        for port in [1883, 8883]:  # Standard MQTT ports
            if tcp_port_open(ip, port):
                result = probe_mqtt(ip, port)
                results.append(result)

    generate_csv_report(results)
    return results
```

The MQTT probe function attempted anonymous connection first, then evaluated the broker's response:

```python
def probe_mqtt(host, port):
    client = mqtt_client.Client("scanner_v1")

    try:
        if port == 8883:
            client.tls_set(cert_reqs=ssl.CERT_NONE)

        client.connect(host, port, keepalive=10)

        # Connection successful without credentials
        return {
            'ip': host,
            'port': port,
            'outcome': 'Anonymous Access Allowed',
            'severity': 'Critical'
        }

    except Exception as e:
        if "not authorized" in str(e).lower():
            return {
                'ip': host,
                'port': port,
                'outcome': 'Authentication Required',
                'severity': 'Medium'
            }
        else:
            return {
                'ip': host,
                'port': port,
                'outcome': 'Connection Failed',
                'severity': 'Info'
            }
```

### 4.3.4 Version 1 Testing and Validation

Version 1 testing utilized a local Eclipse Mosquitto broker installed on the development workstation configured with anonymous access enabled for validation. Test execution followed this procedure:

1. Install and configure local Mosquitto broker with default settings allowing anonymous access
2. Execute scanner targeting localhost: `python scanner.py --target 127.0.0.1`
3. Verify CSV output contains detection of port 1883 with "Anonymous Access Allowed" classification
4. Reconfigure broker to require authentication, re-run scanner
5. Verify updated output shows "Authentication Required" classification

Testing confirmed correct detection of both vulnerable and secured broker configurations, validating the core scanning algorithm for progression to Version 2.

### 4.3.5 Version 1 Limitations

Several limitations identified during Version 1 implementation and testing motivated the architectural evolution in Version 2. The command-line interface required technical expertise limiting accessibility for non-specialist security analysts. Absence of result persistence prevented historical trend analysis or comparison of successive scans. Lack of multi-user support precluded collaborative assessment workflows. The sequential scanning algorithm exhibited performance limitations when assessing large network ranges. Finally, CSV output format lacked rich visualization capabilities for executive reporting requirements.

## 4.4 Development Version 2 and Iterations (FYP2 Web Platform)

Development Version 2 encompassed the transformation from command-line prototype to production-ready web platform during the Final Year Project 2 semester. This section documents the architectural evolution, implementation of each tier, and iterative refinement through multiple sub-versions addressing identified requirements and supervisor feedback.

### 4.4.1 Version 2.0: Web Platform Foundation

Version 2.0 established the three-tier web architecture integrating Laravel presentation layer, Flask API middleware, and enhanced Python scanning engine.

#### 4.4.1.1 Architectural Evolution Rationale

The transition to web architecture addressed Version 1 limitations while introducing capabilities required for enterprise deployment scenarios. The three-tier separation-of-concerns design enabled independent development and testing of presentation layer, business logic, and scanning engine. Laravel framework selection provided mature authentication scaffolding, database abstraction, and security controls reducing development time for standard web application features. Flask API tier insertion decoupled the Python scanning engine from presentation logic, enabling potential future mobile application or API-only deployment scenarios. Database persistence introduced scan history tracking, multi-user support, and analytical reporting capabilities.

#### 4.4.1.2 Laravel Application Setup

Version 2.0 commenced with Laravel application initialization and dependency installation:

```powershell
# Navigate to project root
cd C:\Projects\mqtt-scanner-latest

# Install PHP dependencies from composer.json
composer install
```

The `composer.json` manifest specifies Laravel framework version 12.0 with required PHP version 8.2, validated against actual project configuration:

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/tinker": "^2.10.1",
        "php-mqtt/client": "^2.3"
    }
}
```

Environment configuration establishment:

```powershell
# Copy environment template
copy .env.example .env

# Generate application encryption key
php artisan key:generate
```

The `.env` file configuration for development environment (with sensitive values anonymized):

```dotenv
APP_NAME="MQTT Scanner"
APP_ENV=local
APP_KEY=base64:GENERATED_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite for development simplicity)
DB_CONNECTION=sqlite

# Session configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Flask API integration
FLASK_BASE=http://127.0.0.1:5000
FLASK_API_KEY=REPLACE_WITH_SECURE_RANDOM_KEY
```

Database initialization:

```powershell
# Create SQLite database file
New-Item database/database.sqlite -ItemType File

# Execute migrations
php artisan migrate
```

The migration execution creates the database schema documented in Table 4.2.

**Table 4.2: Database Schema (Version 2.0)**

| Table Name            | Purpose                                      | Key Fields                                                                                      |
| --------------------- | -------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| `users`               | User authentication and profile management   | id, name, email, password, created_at, updated_at                                               |
| `mqtt_scan_histories` | Scan session metadata and summary statistics | id, user_id, target, started_at, completed_at, status, total_targets, vulnerable_count          |
| `mqtt_scan_results`   | Individual broker findings per scan          | id, scan_history_id, user_id, ip_address, port, outcome, severity, tls_available, auth_required |
| `cache`               | Application cache storage                    | key, value, expiration                                                                          |
| `jobs`                | Background job queue                         | id, queue, payload, attempts, available_at                                                      |

Frontend asset compilation:

```powershell
# Install Node.js dependencies
npm install

# Compile assets for development
npm run build
```

The `package.json` specifies Vite 7.0 and Tailwind CSS 4.0 for modern frontend tooling:

```json
{
    "devDependencies": {
        "@tailwindcss/vite": "^4.0.0",
        "vite": "^7.0.7",
        "tailwindcss": "^4.0.0",
        "axios": "^1.11.0"
    }
}
```

#### 4.4.1.3 Flask API Layer Implementation

Flask API initialization in `mqtt-scanner/` subdirectory:

```powershell
cd mqtt-scanner

# Create virtual environment (if not exists from Version 1)
python -m venv ../.venv

# Activate environment
..\.venv\Scripts\Activate.ps1

# Install dependencies
pip install -r requirements.txt
```

The `requirements.txt` specifies minimal dependencies for API functionality:

```
Flask>=2.0
flask-cors>=3.0
flask-wtf>=1.0
paho-mqtt>=1.6.1
requests>=2.28
python-dateutil>=2.8
```

Flask application initialization (`app.py`) implementing authentication and rate limiting:

```python
from flask import Flask, request, jsonify
from flask_cors import CORS
from scanner import run_scan
import os

app = Flask(__name__)
app.secret_key = os.environ.get('FLASK_SECRET_KEY', 'CHANGE_IN_PRODUCTION')

CORS(app, supports_credentials=True)

# API authentication key
FLASK_API_KEY = os.environ.get('FLASK_API_KEY', 'REPLACE_ME')

@app.route('/api/scan', methods=['POST'])
def api_scan():
    # Verify API key
    api_key = request.headers.get('X-API-KEY')
    if not api_key or api_key != FLASK_API_KEY:
        return jsonify({'error': 'Unauthorized'}), 401

    # Parse request
    data = request.get_json()
    target = data.get('target')

    if not target:
        return jsonify({'error': 'Missing target parameter'}), 400

    # Execute scan
    results = run_scan(target)
    return jsonify(results), 200

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)
```

#### 4.4.1.4 Enhanced Scanning Engine

The Version 1 scanning logic underwent enhancement to support Flask API integration and structured JSON output:

```python
# mqtt-scanner/scanner.py (Version 2.0)
import socket
import ssl
from paho.mqtt import client as mqtt_client
import time

def run_scan(target, credentials=None):
    """
    Main scanning entry point for API integration.
    Returns structured JSON instead of CSV output.
    """
    results = []
    ips = parse_target(target)

    for ip in ips:
        for port in [1883, 8883]:
            if is_port_open(ip, port):
                broker_info = probe_mqtt_broker(ip, port, credentials)
                results.append(broker_info)
            else:
                results.append({
                    'ip': ip,
                    'port': port,
                    'outcome': {'label': 'Unreachable'},
                    'severity': 'Info'
                })

    return {
        'results': results,
        'summary': generate_summary(results)
    }

def probe_mqtt_broker(host, port, credentials=None):
    """Enhanced probe with TLS inspection and structured output."""
    result = {
        'ip': host,
        'port': port,
        'tls_available': False,
        'auth_required': False,
        'outcome': {},
        'severity': 'Unknown'
    }

    # TLS certificate analysis for port 8883
    if port == 8883:
        cert_info = analyze_tls_certificate(host, port)
        result['tls_available'] = cert_info['has_tls']
        result['certificate_details'] = cert_info.get('cert_details')

    # MQTT connection attempt
    client = mqtt_client.Client(f"scanner_{int(time.time())}")

    try:
        if port == 8883:
            client.tls_set(cert_reqs=ssl.CERT_NONE)
            client.tls_insecure_set(True)

        client.connect(host, port, keepalive=10)

        # Anonymous success
        result['auth_required'] = False
        result['severity'] = 'Critical'
        result['outcome'] = {
            'label': 'Anonymous Access Success',
            'meaning': 'Broker accepts unauthenticated connections',
            'security_implication': 'Attackers can publish/subscribe without credentials'
        }

    except Exception as e:
        error_msg = str(e).lower()

        if "not authorized" in error_msg or "5" in error_msg:
            result['auth_required'] = True
            result['severity'] = 'Medium'
            result['outcome'] = {
                'label': 'Authentication Required',
                'meaning': 'Broker requires valid credentials',
                'security_implication': 'Properly configured access control'
            }
        else:
            result['severity'] = 'Info'
            result['outcome'] = {
                'label': 'Connection Failed',
                'meaning': 'Unable to establish MQTT connection',
                'security_implication': 'Service unavailable or network issue'
            }

    return result
```

#### 4.4.1.5 Laravel Controller Integration

The `MqttScannerController` orchestrates scan execution via Flask API and persists results to database:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\MqttScanHistory;
use App\Models\MqttScanResult;

class MqttScannerController extends Controller
{
    public function index()
    {
        $recentScans = MqttScanHistory::where('user_id', auth()->id())
            ->with('results')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact('recentScans'));
    }

    public function scan(Request $request)
    {
        $validated = $request->validate([
            'target' => ['required', 'string', 'max:100',
                         'regex:/^[0-9\.\/:a-zA-Z\-]+$/']
        ]);

        // Create scan history record
        $scanHistory = MqttScanHistory::create([
            'user_id' => auth()->id(),
            'target' => $validated['target'],
            'started_at' => now(),
            'status' => 'running'
        ]);

        // Call Flask API
        $flaskBase = env('FLASK_BASE');
        $apiKey = env('FLASK_API_KEY');

        $response = Http::timeout(30)
            ->withHeaders(['X-API-KEY' => $apiKey])
            ->post($flaskBase . '/api/scan', [
                'target' => $validated['target']
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->storeResults($scanHistory, $data['results']);
            $scanHistory->update(['status' => 'completed',
                                 'completed_at' => now()]);

            return response()->json(['success' => true,
                                    'results' => $data['results']]);
        }

        return response()->json(['error' => 'Scan failed'], 500);
    }

    private function storeResults($scanHistory, $results)
    {
        foreach ($results as $result) {
            MqttScanResult::create([
                'scan_history_id' => $scanHistory->id,
                'user_id' => auth()->id(),
                'ip_address' => $result['ip'],
                'port' => $result['port'],
                'outcome' => $result['outcome']['label'] ?? 'Unknown',
                'severity' => $result['severity'],
                'tls_available' => $result['tls_available'] ?? false,
                'auth_required' => $result['auth_required'] ?? false
            ]);
        }
    }
}
```

### 4.4.2 Version 2.1: Docker Broker Testbed Integration

Version 2.1 introduced Docker-based MQTT broker infrastructure enabling consistent testing environments without manual broker installation on development workstations.

#### 4.4.2.1 Docker Compose Configuration

The `mqtt-brokers/docker-compose.yml` file defines two broker services representing insecure and secure configurations:

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

#### 4.4.2.2 Broker Configuration Files

Insecure broker configuration (`insecure/config/mosquitto.conf`) intentionally vulnerable for testing:

```conf
# Allow anonymous connections (VULNERABLE FOR TESTING)
allow_anonymous true

# Plain MQTT listener
listener 1883
protocol mqtt

# Persistence
persistence true
persistence_location /mosquitto/data/

# Logging
log_dest file /mosquitto/log/mosquitto.log
log_type all
```

Secure broker configuration (`secure/config/mosquitto.conf`) implementing authentication and TLS:

```conf
# Require authentication
allow_anonymous false
password_file /mosquitto/config/passwd

# TLS-encrypted listener
listener 8883
protocol mqtt
cafile /mosquitto/certs/ca.crt
certfile /mosquitto/certs/server.crt
keyfile /mosquitto/certs/server.key
tls_version tlsv1.2

# Persistence
persistence true
persistence_location /mosquitto/data/
```

#### 4.4.2.3 TLS Certificate Generation

Self-signed certificates for secure broker testing:

```powershell
cd mqtt-brokers/secure/certs

# Generate CA private key
openssl genrsa -out ca.key 2048

# Generate CA certificate
openssl req -new -x509 -days 365 -key ca.key -out ca.crt \
    -subj "/C=US/ST=State/L=City/O=Org/OU=IT/CN=localhost"

# Generate server key
openssl genrsa -out server.key 2048

# Generate CSR
openssl req -new -key server.key -out server.csr \
    -subj "/C=US/ST=State/L=City/O=Org/OU=IT/CN=localhost"

# Sign server certificate
openssl x509 -req -in server.csr -CA ca.crt -CAkey ca.key \
    -CAcreateserial -out server.crt -days 365
```

#### 4.4.2.4 Password File Creation

Create authenticated user for secure broker:

```powershell
# Generate password file (replace credentials appropriately)
docker run -it --rm -v ${PWD}/secure/config:/config \
    eclipse-mosquitto:2.0 mosquitto_passwd -c /config/passwd testuser

# When prompted, enter: TestPassword123!
```

#### 4.4.2.5 Container Deployment

Launch both broker instances:

```powershell
cd mqtt-brokers
docker-compose up -d

# Verify containers running
docker ps
```

Version 2.1 testing validated scanner detection against both brokers, confirming "Critical" severity classification for localhost:1883 anonymous access and "Medium" severity for localhost:8883 authentication requirement.

### 4.4.3 Version 2.2: ESP32 Hardware Integration (Optional)

Version 2.2 integrated ESP32 microcontroller hardware publishing sensor telemetry to validate scanner operation against realistic IoT device traffic patterns. This iteration is marked optional as it requires physical hardware not essential for core system functionality.

#### 4.4.3.1 Hardware Wiring

Table 4.3 documents the sensor connections to ESP32 GPIO pins.

**Table 4.3: ESP32 Sensor Wiring Connections**

| Sensor | Component Pin | ESP32 GPIO | Purpose                         |
| ------ | ------------- | ---------- | ------------------------------- |
| DHT11  | VCC           | 3.3V       | Power supply                    |
| DHT11  | GND           | GND        | Ground                          |
| DHT11  | DATA          | GPIO 4     | Temperature & humidity data     |
| LDR    | Terminal 1    | 3.3V       | Light sensor supply             |
| LDR    | Terminal 2    | GPIO 34    | Analog light reading            |
| LDR    | Resistor 10kΩ | GND        | Voltage divider ground          |
| PIR    | VCC           | 5V         | Motion sensor power             |
| PIR    | GND           | GND        | Ground                          |
| PIR    | OUT           | GPIO 27    | Digital motion detection signal |

#### 4.4.3.2 Firmware Implementation

The ESP32 firmware (`esp32_mixed_security/esp32_mixed_security.ino`) demonstrates dual MQTT connections with different security postures:

```cpp
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <WiFiClient.h>
#include <PubSubClient.h>
#include <DHT.h>

// WiFi credentials (replace with actual network)
const char *ssid = "Your_WiFi_SSID";
const char *password = "Your_WiFi_Password";

// MQTT broker address (replace with PC IP address)
const char *mqtt_server = "192.168.1.100";

// Secure broker configuration
const uint16_t mqtt_port_secure = 8883;
const char *mqtt_user = "testuser";
const char *mqtt_pass = "TestPassword123!";

// Insecure broker configuration
const uint16_t mqtt_port_insecure = 1883;

// Sensor pins
#define DHT_PIN 4
#define LDR_PIN 34
#define PIR_PIN 27

DHT dht(DHT_PIN, DHT11);

// Dual MQTT clients
WiFiClientSecure secureClient;
PubSubClient mqttSecure(secureClient);

WiFiClient plainClient;
PubSubClient mqttInsecure(plainClient);

void setup() {
    Serial.begin(115200);

    // Connect to WiFi
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
    }

    // Configure secure client (accept self-signed cert)
    secureClient.setInsecure();

    // Initialize sensors
    dht.begin();
    pinMode(PIR_PIN, INPUT);
}

void loop() {
    // Publish DHT and LDR to secure broker
    if (!mqttSecure.connected()) {
        reconnectSecure();
    }
    float temp = dht.readTemperature();
    float humidity = dht.readHumidity();
    int light = analogRead(LDR_PIN);

    mqttSecure.publish("sensors/dht",
        String(temp).c_str());
    mqttSecure.publish("sensors/light",
        String(light).c_str());

    // Publish PIR to insecure broker
    if (!mqttInsecure.connected()) {
        reconnectInsecure();
    }
    int motion = digitalRead(PIR_PIN);
    if (motion) {
        mqttInsecure.publish("sensors/motion", "detected");
    }

    delay(3000);
}

void reconnectSecure() {
    mqttSecure.setServer(mqtt_server, mqtt_port_secure);
    while (!mqttSecure.connected()) {
        if (mqttSecure.connect("ESP32_Secure",
                               mqtt_user, mqtt_pass)) {
            Serial.println("Secure broker connected");
        }
    }
}

void reconnectInsecure() {
    mqttInsecure.setServer(mqtt_server, mqtt_port_insecure);
    while (!mqttInsecure.connected()) {
        if (mqttInsecure.connect("ESP32_Insecure")) {
            Serial.println("Insecure broker connected");
        }
    }
}
```

Firmware upload procedure:

1. Open Arduino IDE and load `esp32_mixed_security.ino`
2. Update WiFi credentials and broker IP address in code
3. Select Tools → Board → ESP32 Dev Module
4. Select appropriate COM port
5. Click Upload button
6. Monitor Serial output to verify connections

Version 2.2 testing confirmed scanner detection of topics published by ESP32 hardware, validating real-world IoT device interaction scenarios.

### 4.4.4 Version 2.3: Security Controls Enhancement

Version 2.3 implemented production-grade security controls addressing code review feedback and industry best practices.

#### 4.4.4.1 Input Validation

Laravel request validation prevents injection attacks:

```php
$validated = $request->validate([
    'target' => [
        'required',
        'string',
        'max:100',
        'regex:/^[0-9\.\/:a-zA-Z\-]+$/'  // IP/CIDR only
    ]
]);
```

Python input sanitization:

```python
import ipaddress
import re

def validate_target_format(target):
    """Validate target is valid IP or CIDR notation."""
    cidr_pattern = r'^(\d{1,3}\.){3}\d{1,3}\/\d{1,2}$'
    ip_pattern = r'^(\d{1,3}\.){3}\d{1,3}$'

    if re.match(cidr_pattern, target) or re.match(ip_pattern, target):
        try:
            ipaddress.ip_network(target, strict=False)
            return True
        except ValueError:
            return False
    return False
```

#### 4.4.4.2 Rate Limiting

Laravel rate limiter implementation:

```php
use Illuminate\Support\Facades\RateLimiter;

$key = 'mqtt_scan:' . auth()->id();
if (RateLimiter::tooManyAttempts($key, 10)) {
    return response()->json([
        'error' => 'Too many scan requests'
    ], 429);
}
RateLimiter::hit($key, 60); // 10 scans per 60 seconds
```

Flask rate limiting with sliding window algorithm:

```python
from datetime import datetime, timedelta
from collections import defaultdict

RATE_LIMIT_WINDOW = 60
MAX_SCANS_PER_WINDOW = 5
scan_history = defaultdict(list)

def check_rate_limit(ip_address):
    now = datetime.now()
    cutoff = now - timedelta(seconds=RATE_LIMIT_WINDOW)

    scan_history[ip_address] = [
        ts for ts in scan_history[ip_address] if ts > cutoff
    ]

    if len(scan_history[ip_address]) >= MAX_SCANS_PER_WINDOW:
        return False, "Rate limit exceeded"

    scan_history[ip_address].append(now)
    return True, None
```

#### 4.4.4.3 Audit Logging

Comprehensive logging for security monitoring:

```php
use Illuminate\Support\Facades\Log;

Log::info('MQTT scan initiated', [
    'user_id' => auth()->id(),
    'user_email' => auth()->user()->email,
    'target' => $validated['target'],
    'ip_address' => $request->ip(),
    'timestamp' => now()
]);
```

Logs persist to `storage/logs/laravel.log` with daily rotation configured in `config/logging.php`.

#### 4.4.4.4 Credential Encryption

Database credential encryption using Laravel's encrypted casting:

```php
class MqttScanHistory extends Model
{
    protected $casts = [
        'credentials' => 'encrypted:array'  // Encrypted using APP_KEY
    ];
}
```

### 4.4.5 Version 2.4: User Interface Refinement

Version 2.4 focused on dashboard user experience improvements based on usability testing feedback.

#### 4.4.5.1 Dashboard Layout

The `resources/views/dashboard.blade.php` template implements responsive design with Tailwind CSS:

```html
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Scan Form -->
            <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
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
                                class="block text-sm 
                                font-medium text-gray-700"
                            >
                                Target IP or CIDR Range
                            </label>
                            <input
                                type="text"
                                name="target"
                                id="target"
                                required
                                class="mt-1 block w-full rounded-md 
                                    border-gray-300 shadow-sm"
                                placeholder="192.168.1.1 or 192.168.1.0/24"
                            />
                        </div>

                        <button
                            type="submit"
                            class="bg-blue-500 hover:bg-blue-700 
                                text-white font-bold py-2 px-4 rounded"
                        >
                            Start Scan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Results Table -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Results</h3>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">IP</th>
                                <th class="px-6 py-3 text-left">Port</th>
                                <th class="px-6 py-3 text-left">Outcome</th>
                                <th class="px-6 py-3 text-left">Severity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentScans as $scan)
                            @foreach($scan->results as $result)
                            <tr>
                                <td class="px-6 py-4">
                                    {{ $result->ip_address }}
                                </td>
                                <td class="px-6 py-4">{{ $result->port }}</td>
                                <td class="px-6 py-4">
                                    {{ $result->outcome }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs 
                                            font-semibold rounded-full
                                            @if($result->severity == 'Critical')
                                                bg-red-100 text-red-800
                                            @elseif($result->severity == 'Medium')
                                                bg-yellow-100 text-yellow-800
                                            @else
                                                bg-gray-100 text-gray-800
                                            @endif"
                                    >
                                        {{ $result->severity }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

#### 4.4.5.2 CSV Export Functionality

Controller method for scan result export:

```php
public function exportCsv($scanId)
{
    $scan = MqttScanHistory::where('id', $scanId)
        ->where('user_id', auth()->id())
        ->with('results')
        ->firstOrFail();

    $filename = "mqtt_scan_{$scanId}_" .
                now()->format('Ymd_His') . ".csv";

    $callback = function() use ($scan) {
        $file = fopen('php://output', 'w');

        fputcsv($file, ['IP Address', 'Port', 'Outcome',
                       'Severity', 'TLS', 'Auth Required']);

        foreach ($scan->results as $result) {
            fputcsv($file, [
                $result->ip_address,
                $result->port,
                $result->outcome,
                $result->severity,
                $result->tls_available ? 'Yes' : 'No',
                $result->auth_required ? 'Yes' : 'No'
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\""
    ]);
}
```

### 4.4.6 Running the Complete System

Version 2.4 deployment requires coordinated execution of multiple processes. Table 4.4 summarizes the execution commands for each tier.

**Table 4.4: System Execution Commands**

| Tier | Component                  | Command                      | Port       | Purpose             |
| ---- | -------------------------- | ---------------------------- | ---------- | ------------------- |
| 3    | Laravel Web Server         | `php artisan serve`          | 8000       | Presentation layer  |
| 3    | Vite Dev Server (optional) | `npm run dev`                | 5173       | Frontend hot reload |
| 2    | Flask API Server           | `python mqtt-scanner/app.py` | 5000       | API middleware      |
| 1    | Docker Brokers             | `docker-compose up`          | 1883, 8883 | Test infrastructure |

Complete startup procedure:

```powershell
# Terminal 1: Docker brokers
cd mqtt-brokers
docker-compose up -d

# Terminal 2: Flask API
cd mqtt-scanner
..\.venv\Scripts\Activate.ps1
python app.py

# Terminal 3: Laravel server
cd ..
php artisan serve

# Access application at http://localhost:8000
```

## 4.5 Implementation Summary

This chapter documented the iterative implementation of the MQTT Network Security Scanner across two development versions spanning two academic semesters. Development Version 1 established foundational scanning capabilities through a command-line interface prototype, validating the approach of protocol-aware MQTT security assessment and classification logic subsequently enhanced in later versions. Development Version 2 transformed the architecture to a three-tier web platform, progressing through multiple iterations addressing requirements evolution, supervisor feedback, and production deployment considerations.

Version 2.0 established the web architecture foundation integrating Laravel presentation layer, Flask API middleware, and enhanced Python scanning engine with database persistence and user authentication. Version 2.1 introduced Docker-based broker testbed infrastructure enabling consistent testing environments without manual broker configuration. Version 2.2 integrated ESP32 microcontroller hardware publishing realistic IoT sensor telemetry, validating scanner operation against live device traffic patterns. Version 2.3 implemented production-grade security controls including input validation, rate limiting, audit logging, and credential encryption addressing code review findings. Version 2.4 refined user interface design based on usability testing feedback, implementing responsive dashboard layout and CSV export functionality.

The final implementation successfully achieved all functional requirements specified in Chapter 3, providing protocol-aware MQTT broker discovery, TLS configuration analysis, authentication testing, vulnerability classification, multi-user access control, scan history persistence, and report generation capabilities. The three-tier architectural separation enables independent testing and deployment of each component while maintaining clean interface contracts between tiers. Database schema design supports analytical reporting and historical trend analysis through normalized table structure. Security controls implementation follows industry best practices for input validation, authentication, authorization, and audit trail generation.

Testing and validation procedures confirmed correct operation across diverse broker security configurations including anonymous access, authentication requirements, TLS encryption, connection failures, and unreachable targets. Hardware-in-the-loop testing with ESP32 devices validated scanner capability to detect and classify real IoT device deployments beyond simulated environments. Performance testing demonstrated acceptable scan completion times for typical network ranges encountered in enterprise IoT deployments.

The comprehensive documentation provided throughout this chapter, including prerequisite installation procedures, configuration file examples, code listings with explanatory comments, and deployment instructions, enables reproduction of the complete system by future researchers or practitioners. The iterative development approach documented through explicit version progression illustrates how requirements evolution and feedback integration shaped the final architecture, providing valuable lessons for similar security assessment tool development projects.

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
