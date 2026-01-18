# CHAPTER 3: METHODOLOGY

---

## 3.1 Introduction

This chapter presents the systematic methodology employed in designing, developing, and validating an MQTT network security scanner for Internet of Things (IoT) environments. The research addressed the identified gap in lightweight, protocol-aware security assessment tools capable of detecting common MQTT misconfigurations including anonymous broker access, absence of Transport Layer Security (TLS) encryption, inadequate authentication enforcement, and unrestricted topic subscriptions.

The methodology encompassed five principal components: evaluation and selection of an appropriate software development model suited to academic constraints and evolving requirements, structured development activities across two academic semesters (FYP1 and FYP2), comprehensive system design incorporating architectural and behavioral modeling, specification of hardware and software requirements with budget considerations, and project planning with risk management strategies. The adopted approach facilitated iterative refinement while maintaining rigorous documentation standards expected in academic research.

This chapter demonstrates how the research objectives articulated in Chapter 1 were systematically operationalized into concrete development activities, ultimately yielding a functional three-tier web-based scanning platform comprising a Laravel dashboard for user interaction, a Flask RESTful API for orchestration, and a Python-based MQTT scanner engine for protocol-level assessment. The methodological framework presented herein directly informs the implementation details documented in Chapter 4 and the empirical testing outcomes presented in Chapter 5.

---

## 3.2 Software Development Model Evaluation

Prior to commencing development activities, five established software development models were evaluated against project-specific criteria including academic timeline constraints (28 weeks across two semesters), requirement volatility expectations, resource availability, and the need for iterative refinement based on testing feedback. The evaluation process considered the Waterfall model, Agile methodology, Spiral model, V-Model, and Prototype model, assessing each against suitability for IoT security tool development in an academic research context.

### 3.2.1 Model Descriptions and Comparative Analysis

**Waterfall Model:**
The Waterfall model prescribes linear sequential progression through distinct phases—requirements analysis, system design, implementation, testing, deployment, and maintenance—with minimal provision for revisiting completed phases. While its structured nature provides clear milestone definitions suitable for projects with stable, well-understood requirements, the model's rigidity proved incompatible with this research project's exploratory nature. The inability to accommodate mid-development requirement modifications based on supervisor feedback or testing discoveries rendered the Waterfall approach unsuitable.

**Agile Methodology:**
Agile advocates iterative and incremental development through short, timeboxed development cycles, emphasizing adaptive planning, continuous stakeholder collaboration, and regular delivery of functional increments. The methodology's inherent flexibility enables accommodation of evolving requirements and incorporation of lessons learned across iterations. Agile's emphasis on working software over comprehensive documentation aligned well with the project's objectives of delivering a functional prototype within academic constraints.

**Spiral Model:**
The Spiral model integrates iterative development with explicit risk management, requiring formal risk analysis during each iteration cycle. While valuable for large-scale, high-risk commercial projects, the overhead associated with formal risk documentation and analysis activities was deemed excessive for a two-semester academic project where time resources were constrained and formal risk management processes would consume development capacity without commensurate benefit.

**V-Model (Verification and Validation):**
The V-Model extends Waterfall principles by mandating parallel development and testing activities, where each development phase has a corresponding validation phase. The model's emphasis on rigorous testing procedures suits projects requiring high reliability certification. However, its predetermined test specification approach conflicted with this project's research orientation, where testing outcomes informed design decisions rather than validating pre-existing specifications.

**Prototype Model:**
The Prototype model advocates building initial working prototypes to clarify requirements through stakeholder feedback before committing to full-scale development. While prototyping activities were incorporated during FYP1 to validate core scanning concepts, a pure Prototype model approach lacked the structural framework necessary for FYP2's production-grade implementation encompassing web dashboard integration, database persistence, and hardware testbed configuration.

**Table 3.1: Software Development Model Comparison Matrix**

| Model     | Flexibility                   | Documentation Overhead                      | Testing Approach                       | Suitability for FYP | Selection Rationale                                                                              |
| --------- | ----------------------------- | ------------------------------------------- | -------------------------------------- | ------------------- | ------------------------------------------------------------------------------------------------ |
| Waterfall | Low - sequential phases       | Heavy - formal at each stage                | Late - post-implementation             | Unsuitable          | Inability to accommodate requirement evolution discovered during FYP1 testing                    |
| **Agile** | **High - iterative cycles**   | **Moderate - working software prioritized** | **Continuous - throughout lifecycle**  | **Selected**        | **Flexibility for requirement adaptation, iterative refinement, continuous testing integration** |
| Spiral    | High - risk-driven iterations | Heavy - extensive risk documentation        | Iterative - after each cycle           | Unsuitable          | Excessive overhead for 28-week academic timeline; formal risk analysis impractical               |
| V-Model   | Low - predetermined path      | Heavy - parallel documentation              | Structured - phase-specific validation | Unsuitable          | Predetermined test approach conflicted with exploratory research methodology                     |
| Prototype | Moderate - prototype-focused  | Light - minimal formal requirements         | Prototype validation only              | Partially suitable  | Useful for FYP1 validation but insufficient structural framework for FYP2 expansion              |

---

## 3.3 Selected Development Model and Justification

Based on the comparative analysis presented in Section 3.2, **Agile methodology** was selected as the foundational development approach for this research project. The selection decision was driven by four primary justification factors that aligned Agile principles with project-specific requirements and constraints.

**Justification Factor 1: Requirement Evolution Accommodation**
Initial project requirements specified during FYP1 planning focused on command-line interface (CLI) based MQTT scanning with basic CSV report generation. As development progressed and prototype testing revealed additional security assessment capabilities, requirements evolved to encompass web-based dashboard integration, database persistence for scan history, RESTful API architecture for modularity, and hardware testbed validation using both containerized and physical MQTT brokers. Agile's adaptive planning mechanism facilitated seamless integration of these emergent requirements without requiring complete project restructuring.

**Justification Factor 2: Iterative Refinement and Feedback Integration**
The transition from FYP1's CLI prototype to FYP2's production-grade web platform necessitated incremental development where each iteration built upon preceding work while incorporating lessons learned from testing activities and supervisor consultations. The iterative nature of Agile development enabled systematic progression through distinct capability increments: FYP1 established core scanning engine and CSV reporting; FYP2 Iteration 1 introduced web dashboard and Flask API layer; FYP2 Iteration 2 integrated hardware testbed and real broker validation; FYP2 Iteration 3 implemented security hardening and comprehensive testing.

**Justification Factor 3: Continuous Validation Throughout Development**
Agile's emphasis on continuous testing throughout the development lifecycle proved essential for validating MQTT protocol behavior, TLS certificate inspection logic, and authentication handling mechanisms. Regular testing activities after each development iteration enabled early detection of defects such as TLS handshake failures, connection timeout handling errors, and database concurrency issues, facilitating prompt resolution before they escalated into project-blocking impediments.

**Justification Factor 4: Academic Timeline Compatibility**
The timeboxed iteration structure inherent to Agile methodology aligned naturally with academic semester scheduling. FYP1's 14-week timeline was structured into distinct phases with clear deliverables (requirements, design, CLI prototype, testing, documentation), while FYP2's 14-week timeline was organized into iterations focused on web platform development, hardware integration, security validation, and comprehensive testing, ensuring tangible progress demonstrations for supervisor evaluation at regular intervals.

**Alternative Model Rejection Rationale:**
The Waterfall model was rejected due to its inability to accommodate the requirement changes identified during FYP1 testing, particularly the decision to transition from CLI-only operation to web-based dashboard integration. The Spiral model's formal risk analysis overhead would have consumed valuable development time without proportionate benefit in an academic context where technical risks were managed through iterative testing rather than formal documentation. The V-Model's predetermined test specification paradigm conflicted with the exploratory testing approach employed, where unexpected broker behaviors informed design refinements. The Prototype model, while useful for FYP1 validation, lacked sufficient structure for FYP2's multi-tier system implementation encompassing Laravel, Flask, Python scanner, MySQL database, and Docker testbed configuration.

**Table 3.2: Development Iteration Mapping Across FYP1 and FYP2**

| Iteration                                       | Timeline                | Primary Objectives                                                                                                                   | Deliverables                                                                                       | Validation Method                                                                        |
| ----------------------------------------------- | ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| **FYP1 - Initial Prototype**                    | Weeks 1-14 (Semester 1) | Requirements definition, system design, CLI scanner development, CSV reporting                                                       | Functional CLI scanner, CSV output format, design documentation, FYP1 report                       | Local Mosquitto broker testing, CLI execution validation                                 |
| **FYP2 - Iteration 1: Web Platform Foundation** | Weeks 1-5 (Semester 2)  | Laravel dashboard development, Flask API implementation, MySQL schema design, authentication system                                  | Laravel web UI, Flask REST API, database migrations, user authentication                           | Browser-based testing, API endpoint validation, database query testing                   |
| **FYP2 - Iteration 2: Hardware Integration**    | Weeks 6-9               | Docker broker deployment, ESP32 firmware development, sensor integration (DHT11, LDR, PIR), real broker testing                      | Docker Compose configuration, ESP32 telemetry publisher, validated scanning against 192.168.100.57 | Hardware-in-the-loop testing, live MQTT traffic observation                              |
| **FYP2 - Iteration 3: Security and Validation** | Weeks 10-14             | Security hardening (CSRF, input validation, rate limiting), comprehensive functional testing, performance measurement, documentation | Production-ready system, test reports, performance metrics, FYP2 final report                      | Security penetration testing, end-to-end functional validation, performance benchmarking |

---

## 3.4 Development Activities and Phase Deliverables

This section details the systematic activities performed during each development phase across FYP1 and FYP2, documenting the methodologies employed, technical decisions made, and concrete artifacts produced.

### 3.4.1 Phase 1: Requirements Analysis and System Design (FYP1 Weeks 1-5)

**Requirements Elicitation Activities:**
The requirements analysis process commenced with comprehensive literature review findings from Chapter 2, identifying functional gaps in existing MQTT security tools. Requirements were categorized into functional specifications (network scanning, broker detection, TLS inspection, authentication testing, topic observation, vulnerability classification) and non-functional constraints (scan completion time under 60 seconds for /24 subnet, cross-platform compatibility, modular architecture for future extensibility).

**System Architecture Specification:**
The system architecture was designed following a three-tier separation-of-concerns pattern: Tier 1 (Scanning Engine) - Python-based MQTT scanner utilizing paho-mqtt library for protocol interactions; Tier 2 (API Layer) - Flask RESTful API providing JSON endpoints for scan invocation and result retrieval; Tier 3 (Presentation Layer) - Laravel web dashboard with authentication, database persistence, and result visualization. This architectural separation ensured that frontend modifications would not impact backend scanning logic, and new scanning modules could be integrated without web interface changes.

**Figure 3.1: Three-Tier System Architecture**

The system architecture diagram (Figure 3.1) illustrates the layered design with clear component boundaries. The Laravel dashboard (Tier 3) handles user authentication via Laravel Breeze, renders HTML views using Blade templates, and queries MySQL database for scan history. The Flask API (Tier 2) exposes `/api/scan` endpoint for scan initiation and `/api/results` for result retrieval, enforcing API key authentication and rate limiting. The Python scanner (Tier 1) performs TCP port scanning on ports 1883 and 8883, attempts anonymous and authenticated MQTT connections, inspects TLS certificates when TLS is available, subscribes to `#` wildcard topic to observe published messages, and classifies brokers into security categories.

**Design Modeling Activities:**
Multiple design artifacts were produced to guide implementation. Use case diagrams captured actor interactions (security analyst initiating scans, viewing results, exporting reports). Sequence diagrams documented message flows for scan request processing (user submits scan → Laravel sends POST to Flask → Flask invokes scanner → scanner probes brokers → Flask returns JSON → Laravel stores in database → user views results). Activity diagrams illustrated concurrent scanning operations across multiple target IPs. Flowcharts detailed decision logic for connection outcome classification (open broker, authentication required, TLS required, connection refused, timeout, network unreachable).

**Figure 3.2: Scan Request Sequence Diagram**

The sequence diagram (Figure 3.2) shows the complete request-response cycle: User submits target IP via Laravel dashboard, Laravel validates session authentication and constructs HTTP POST request with API key header, Flask API validates API key and input format (IP/CIDR regex), Flask invokes Python scanner's `scan_target()` function, Scanner performs TCP port scan on 1883/8883 and MQTT connection probes, Scanner returns structured results dictionary, Flask serializes to JSON and returns HTTP 200, Laravel parses JSON and inserts records into `mqtt_scan_results` table, Dashboard renders results table with severity color-coding.

**Deliverables:**
Requirements specification document (functional and non-functional requirements), system architecture diagram documenting three-tier design, use case diagram showing actor interactions, sequence diagrams for scan workflows, activity diagrams for concurrent operations, flowchart for classification logic, design document consolidating all modeling artifacts.

### 3.4.2 Phase 2: CLI Prototype Implementation (FYP1 Weeks 6-10)

**Development Environment Configuration:**
A Python virtual environment was established using `venv` module to isolate project dependencies from system Python installation. Core dependencies installed included paho-mqtt (v1.6.1) for MQTT protocol communication, standard library modules (socket for TCP scanning, ssl for certificate inspection, csv for report generation, ipaddress for CIDR parsing, argparse for CLI parameter handling).

**Core Scanner Module Implementation:**
The `scanner.py` module implemented the orchestration logic, exposing a `scan_target(target, ports=[1883, 8883])` function that accepted either single IP addresses or CIDR notation. The function performed TCP port scanning using socket connections with 3-second timeout, iterating through discovered open ports to invoke MQTT probing logic. The `mqtt_probe.py` module implemented connection testing, first attempting anonymous connection (no credentials), then authenticated connection with test credentials if anonymous attempt failed. For TLS-enabled brokers (port 8883), the module captured X.509 certificate details including subject, issuer, and validity period. If successful connection was established, the scanner subscribed to `#` wildcard topic with 5-second observation window to capture live messages.

**Classification Logic:**
The `categorizer.py` module implemented vulnerability classification based on connection response codes: response code 0 (connection accepted) with no credentials classified as "Open Broker" with Critical severity; response code 5 (not authorized) classified as "Authentication Required" with Medium severity; TLS handshake failure classified as "TLS Required" with Medium severity; connection refused classified as "Unreachable" with Informational severity. This classification scheme aligned with OWASP IoT security guidelines prioritizing anonymous access as highest risk.

**Figure 3.3: Scan Workflow Flowchart**

The flowchart (Figure 3.3) documents the algorithmic decision flow: Start → Accept target input → Validate IP/CIDR format → Parse IP range → For each IP, scan ports 1883/8883 → Check if port open → If open, attempt anonymous MQTT CONNECT → If connection succeeds, classify as Open Broker → If connection fails, attempt with test credentials → If credential attempt succeeds, classify as Authenticated Broker → If both fail, check error type (TLS vs refused vs timeout) → Store classification → Generate CSV report → End.

**CSV Reporting Implementation:**
The `reporter.py` module generated structured CSV output with columns: IP Address, Port, Outcome Category, TLS Available (Yes/No), Authentication Required (Yes/No), Certificate Subject, Certificate Issuer, Captured Topics, Timestamp. UTF-8 encoding ensured proper handling of special characters in certificate fields.

**Deliverables:**
Functional CLI scanner executable via `python scanner.py <target>`, CSV report generation capability, unit test scripts validating individual functions, README documentation with usage examples, FYP1 interim report documenting prototype functionality.

### 3.4.3 Phase 3: Web Platform Development (FYP2 Weeks 1-5)

**Flask API Layer Implementation:**
The Flask API was developed in `app.py` exposing two primary endpoints: `POST /api/scan` accepted JSON payload `{"target": "192.168.1.0/24"}`, validated API key from Authorization header, sanitized target input using regex, invoked scanner module, and returned JSON array of broker findings; `GET /api/results` retrieved scan history from CSV files. Rate limiting middleware restricted requests to 5 scans per minute per IP address to prevent abuse. API key authentication was implemented using environment variable `FLASK_API_KEY` checked against request header value.

**Laravel Dashboard Development:**
Laravel 10 framework was installed with Laravel Breeze authentication scaffolding providing login, registration, and password reset functionality. Database migrations created three tables: `users` (id, name, email, password_hash, created_at, updated_at), `mqtt_scan_history` (id, user_id, target, scan_start_time, scan_completion_time, broker_count_found), `mqtt_scan_results` (id, scan_id, ip_address, port, outcome, severity, tls_available, auth_required, certificate_subject, captured_topics). The `MqttScannerController` implemented three actions: `index()` displayed dashboard with scan initiation form, `executeScan(Request $request)` validated CSRF token and target input, sent HTTP POST to Flask API with API key, parsed JSON response, performed batch insertion into database, and returned results view, `exportCsv($scanId)` generated CSV download from database records.

**User Interface Design:**
Blade templates implemented responsive design using Tailwind CSS framework. The dashboard view presented a scan initiation form with target input field (validated client-side via JavaScript regex), a results table with sortable columns (IP, Port, Outcome, TLS, Authentication), severity color-coding (Critical in red, High in orange, Medium in yellow, Low in blue, Info in gray), and export buttons for CSV and PDF downloads. The scan history view displayed previous scans in chronological order with metadata (target, timestamp, broker count).

**Figure 3.4: Dashboard Activity Diagram**

The activity diagram (Figure 3.4) shows parallel user and system activities: User authenticates and navigates to dashboard (swim lane 1), enters target IP and clicks Scan button, monitors progress indicator; System validates session and CSRF token (swim lane 2), sends request to Flask API, receives results, stores in database, renders table; Flask API validates API key and input (swim lane 3), invokes scanner, returns JSON; Scanner performs port scans and MQTT probes concurrently (swim lane 4), aggregates results.

**Database Integration:**
Eloquent ORM facilitated database operations without raw SQL queries, automatically escaping user inputs to prevent SQL injection. The `ScanHistory` model defined relationships to `ScanResult` model via `hasMany` relationship, enabling efficient query of scan details. Database queries employed eager loading (`with('results')`) to minimize N+1 query problems when displaying scan history.

**Deliverables:**
Functional Flask API with documented endpoints, Laravel dashboard with authentication system, database schema migrations, integration test scripts validating Laravel-Flask communication, deployment configuration files (`.env` templates).

### 3.4.4 Phase 4: Hardware Testbed Integration (FYP2 Weeks 6-9)

**Docker Broker Deployment:**
Docker Compose configuration (`docker-compose.yml`) defined two Eclipse Mosquitto broker containers. The insecure broker (service name: `mosquitto_insecure`) mapped port 1883 with configuration disabling authentication (`allow_anonymous true`) and TLS. The secure broker (service name: `mosquitto_secure`) mapped port 8883 with configuration requiring username/password authentication (`allow_anonymous false`, `password_file /mosquitto/passwd/passwordfile`) and TLS encryption using self-signed certificates (`cafile`, `certfile`, `keyfile` parameters).

**TLS Certificate Generation:**
OpenSSL was employed to generate self-signed CA certificate and broker server certificate. The CA private key was generated using RSA 2048-bit encryption, followed by self-signed CA certificate with 10-year validity. Broker certificate signing request (CSR) was created specifying Common Name matching broker hostname, then signed by CA to produce server certificate. Certificate files were mounted into Docker container via volume mapping.

**ESP32 Firmware Development:**
Arduino-based firmware for ESP32 microcontroller integrated three sensors: DHT11 temperature/humidity sensor connected to GPIO 4 (digital pin), LDR photoresistor connected to GPIO 34 (analog input with voltage divider circuit), PIR motion sensor connected to GPIO 27 (digital pin). The firmware established dual MQTT client instances using PubSubClient library: one client published DHT11 and LDR readings to secure broker (port 8883) over TLS-encrypted connection with hardcoded credentials, another client published PIR motion events to insecure broker (port 1883) in plaintext.

**Physical Broker Integration:**
A commercial MQTT broker device accessible at IP address 192.168.100.57 was incorporated into the testing infrastructure to validate scanner operation against real-world network-accessible brokers beyond localhost simulation. The device was configured with default settings (port 1883 open, no authentication) to represent typical insecure IoT deployments encountered in production environments.

**Figure 3.5: Hardware Testbed Network Topology**

The network topology diagram (Figure 3.5) illustrates: Development workstation hosting Laravel (port 8000), Flask (port 5000), and MySQL (port 3306) on 127.0.0.1 loopback interface; Docker containers hosting Mosquitto insecure (172.18.0.2:1883) and secure (172.18.0.3:8883) brokers on Docker bridge network; ESP32 microcontroller on local WiFi network (192.168.1.x) publishing to both Docker brokers; Physical broker device at 192.168.100.57 accessible via LAN; Development workstation scanner targeting all four broker endpoints for validation testing.

**Validation Testing:**
The scanner was executed against all four broker targets to confirm correct detection across diverse configurations. Localhost port 1883 (Docker insecure) correctly detected as "Open Broker - Critical"; localhost port 8883 (Docker secure) correctly detected as "Authentication Required - Medium" with TLS certificate details captured; 192.168.100.57 port 1883 correctly detected as "Open Broker - Critical"; ESP32 publishing verified through topic observation, with scanner capturing `sensor/temperature`, `sensor/humidity`, `sensor/light`, `sensor/motion` topics during subscription test.

**Deliverables:**
Docker Compose configuration for broker deployment, TLS certificate files and generation scripts, ESP32 Arduino firmware with sensor integration, hardware wiring documentation, network configuration guide, validated scanning against four distinct broker configurations.

### 3.4.5 Phase 5: Security Hardening and Testing (FYP2 Weeks 10-14)

**Security Implementation Activities:**
Laravel authentication middleware (`auth`) was applied to all dashboard routes to enforce session-based access control. CSRF protection was implemented through Laravel's built-in `@csrf` directive in Blade forms, with middleware validating tokens on state-changing POST requests. Input validation employed Laravel validation rules (e.g., `required|ip|regex:/^[\d.\/]+$/` for target field) and server-side sanitization. SQL injection prevention was ensured through exclusive use of Eloquent ORM and parameterized queries. Cross-Site Scripting (XSS) protection was provided by Blade's automatic HTML entity escaping (`{{ $variable }}` syntax).

**Functional Testing Methodology:**
Comprehensive functional testing validated all scan outcome classifications across representative broker configurations. Test cases included: network unreachable (target 192.168.255.255), connection refused (valid IP with no MQTT service), connection timeout (firewall blocking 1883/8883), anonymous access success (Docker insecure broker), authentication required (Docker secure broker with incorrect credentials), authentication success (Docker secure broker with correct credentials), TLS validation (certificate details correctly extracted from 8883). Each test case was executed multiple times to verify consistency.

**Performance Measurement:**
Scan performance was measured across different subnet sizes: single IP scan averaged 2.3 seconds, /29 subnet (8 IPs) averaged 6.1 seconds, /24 subnet (256 IPs) averaged 94.2 seconds. Database query performance for result retrieval was measured: 100 scan records retrieved in 23ms, 1000 records in 156ms, demonstrating acceptable performance for dashboard rendering. CSV export performance was measured: 100-record export completed in 42ms, 1000-record export in 287ms.

**Security Testing:**
Attempted security attacks were performed to validate defenses: SQL injection attempts using crafted input strings (`' OR '1'='1`, `'); DROP TABLE users;--`) were properly escaped by Eloquent ORM and rejected. CSRF attacks simulating direct POST requests without valid tokens were rejected with HTTP 419 status. Rate limit testing confirmed requests exceeding 5 per minute threshold received HTTP 429 responses. Unauthenticated access attempts to protected routes correctly redirected to login page.

**Deliverables:**
Security-hardened application with CSRF, SQL injection, and XSS protections, comprehensive functional test report documenting 15 test cases, performance measurement data, security penetration test report, FYP2 final implementation ready for demonstration.

---

## 3.5 System Requirements

This section specifies the hardware and software resources required for developing, deploying, and operating the MQTT network security scanner. Requirements were determined based on functional specifications, performance expectations, and budget constraints typical of academic research projects.

### 3.5.1 Hardware Requirements

The hardware infrastructure comprised development equipment and IoT testbed components. The development machine required minimum specifications: Intel Core i5 or AMD Ryzen 5 processor (quad-core), 8GB RAM, 256GB storage, network interface card supporting WiFi and Ethernet connectivity. This configuration provided sufficient capacity to concurrently run Laravel development server, Flask API server, MySQL database server, Docker containers, and web browser for testing.

The IoT testbed hardware simulated realistic deployment scenarios. One ESP32-WROOM-32 development board with integrated WiFi provided the telemetry publishing platform. Three sensors generated representative IoT data: DHT11 digital temperature/humidity sensor (operating range -40°C to 80°C, ±2°C accuracy), LDR photoresistor light sensor (resistance range 1MΩ in darkness to 10kΩ in bright light), HC-SR501 PIR motion sensor (detection range 7 meters, 120° angle). Supporting components included 400-tie-point breadboard for prototyping, 20 male-to-male jumper wires, 10kΩ resistor for LDR voltage divider circuit, Micro-USB cable for ESP32 programming and power.

**Table 3.3: Hardware Requirements and Budget**

| Component               | Specification                        | Quantity | Unit Price (MYR) | Total (MYR) | Purpose                                               |
| ----------------------- | ------------------------------------ | -------- | ---------------- | ----------- | ----------------------------------------------------- |
| Development Laptop      | Intel Core i5, 8GB RAM, 256GB SSD    | 1        | Existing         | 0.00        | Host Laravel, Flask, Docker, MySQL, development tools |
| ESP32 DevKit            | ESP32-WROOM-32, WiFi, Bluetooth      | 1        | 25.00            | 25.00       | MQTT telemetry publisher for testbed realism          |
| DHT11 Sensor            | Temperature/humidity, digital output | 1        | 8.00             | 8.00        | Generate temperature/humidity telemetry               |
| PIR Motion Sensor       | HC-SR501, 7m range                   | 1        | 5.00             | 5.00        | Generate motion detection events                      |
| LDR Photoresistor       | Light-dependent resistor, analog     | 1        | 2.00             | 2.00        | Generate light intensity measurements                 |
| Breadboard              | 400 tie-points                       | 1        | 10.00            | 10.00       | Sensor circuit prototyping                            |
| Jumper Wires            | Male-to-male, 20cm length            | 20       | 0.40             | 8.00        | Sensor-to-ESP32 connections                           |
| Resistor                | 10kΩ, 1/4W carbon film               | 1        | 0.50             | 0.50        | LDR voltage divider circuit                           |
| USB Cable               | Micro-USB, 1m length                 | 1        | 6.00             | 6.00        | ESP32 programming and power supply                    |
| **Total Hardware Cost** |                                      |          |                  | **64.50**   |                                                       |

### 3.5.2 Software Requirements

The software stack comprised open-source components selected for zero licensing cost, active community support, comprehensive documentation, and proven production reliability. Backend development utilized Python 3.9+ for scanner engine implementation, with paho-mqtt library (v1.6.1) providing MQTT protocol support. The Flask microframework (v2.3+) implemented RESTful API layer. Frontend development employed Laravel 10 PHP framework with MySQL 8.0+ relational database for persistence. Docker Engine (v20.10+) facilitated containerized broker deployment using Eclipse Mosquitto (v2.0+) images.

Development tools included Visual Studio Code IDE with extensions for Python, PHP, and Blade syntax support. Git version control (v2.30+) with GitHub remote repository enabled source code management and backup. Dependency management utilized pip for Python packages, Composer for PHP/Laravel dependencies, and npm for frontend asset compilation. Testing frameworks included PHPUnit for Laravel unit testing and pytest for Python unit testing.

**Table 3.4: Software Requirements and Justification**

| Category         | Software           | Version | License              | Purpose                          | Selection Rationale                                          |
| ---------------- | ------------------ | ------- | -------------------- | -------------------------------- | ------------------------------------------------------------ |
| Operating System | Windows 10/11      | Latest  | Commercial           | Development environment host     | Pre-existing on development machine                          |
| Backend Language | Python             | 3.9+    | Open Source (PSF)    | Scanner engine implementation    | Extensive library ecosystem for network protocols            |
| MQTT Library     | paho-mqtt          | 1.6.1+  | Open Source (EPL)    | MQTT protocol communication      | Official Eclipse Foundation library, well-documented         |
| API Framework    | Flask              | 2.3+    | Open Source (BSD)    | RESTful API layer                | Lightweight, flexible, minimal overhead                      |
| Web Framework    | Laravel            | 10.x    | Open Source (MIT)    | Dashboard and persistence layer  | Mature ecosystem, built-in authentication, ORM               |
| Database         | MySQL              | 8.0+    | Open Source (GPL)    | Scan history and results storage | Industry-standard relational database, Laravel compatibility |
| Containerization | Docker Engine      | 20.10+  | Open Source (Apache) | Broker testbed deployment        | Simplified multi-broker configuration management             |
| MQTT Broker      | Eclipse Mosquitto  | 2.0+    | Open Source (EPL)    | Target brokers for testing       | Widely deployed in production IoT environments               |
| IDE              | VS Code            | Latest  | Open Source (MIT)    | Code editing and debugging       | Excellent extension ecosystem, multi-language support        |
| Version Control  | Git + GitHub       | 2.30+   | Open Source (GPL)    | Source code management           | Industry-standard VCS, free remote hosting                   |
| Package Managers | pip, Composer, npm | Latest  | Open Source          | Dependency management            | Official package managers for respective ecosystems          |

**Software Justification:**
All software components were selected based on open-source licensing to ensure zero cost and legal compliance in academic research context. Python was chosen for scanner engine due to extensive network protocol libraries and rapid development capability. Flask provided lightweight API layer without excessive framework overhead. Laravel offered mature authentication system, database migrations, and ORM reducing development time for web dashboard. MySQL ensured reliable persistence with widespread deployment knowledge. Docker simplified broker configuration management compared to manual installations.

---

## 3.6 Project Planning and Schedule

Project planning activities encompassed timeline development, milestone definition, and risk identification. The 28-week development period (14 weeks FYP1, 14 weeks FYP2) required careful scheduling to ensure adequate time allocation for requirements analysis, design, implementation, testing, and documentation while maintaining buffer capacity for unanticipated challenges.

### 3.6.1 FYP1 Development Schedule

FYP1 focused on establishing foundational understanding, requirements specification, system design, and CLI prototype development. The 14-week timeline was structured to emphasize early design activities to inform implementation decisions, with dedicated testing and documentation periods ensuring deliverable quality.

**Table 3.5: FYP1 Gantt Chart (14 Weeks)**

| Week  | Primary Activities                                                                  | Key Deliverables                                        | Status    |
| ----- | ----------------------------------------------------------------------------------- | ------------------------------------------------------- | --------- |
| 1-2   | Literature review completion, requirements elicitation, project proposal refinement | Requirements specification document, project proposal   | Completed |
| 3-4   | System architecture design, use case modeling, sequence diagram development         | Architecture diagram, use case diagram, design document | Completed |
| 5     | Flowchart development, detailed design specifications                               | Flowchart, detailed module specifications               | Completed |
| 6-7   | Python environment setup, scanner core module implementation                        | Functional scanner module with port scanning            | Completed |
| 8-9   | MQTT probing implementation, TLS certificate inspection, classification logic       | Complete CLI scanner with categorization                | Completed |
| 10    | CSV reporting implementation, CLI interface development                             | CSV output generation, command-line interface           | Completed |
| 11    | Testing against local Mosquitto broker, bug fixes                                   | Test results, validated CLI prototype                   | Completed |
| 12-13 | FYP1 report writing, presentation preparation                                       | FYP1 final report, presentation slides                  | Completed |
| 14    | FYP1 presentation and demonstration                                                 | Completed FYP1 defense                                  | Completed |

### 3.6.2 FYP2 Development Schedule

FYP2 transitioned from CLI prototype to production-grade web platform with hardware testbed integration. The 14-week timeline allocated significant effort to web development (Weeks 1-5), hardware integration (Weeks 6-9), and comprehensive testing and security validation (Weeks 10-14).

**Table 3.6: FYP2 Gantt Chart (14 Weeks)**

| Week  | Primary Activities                                                                      | Key Deliverables                                        | Status    |
| ----- | --------------------------------------------------------------------------------------- | ------------------------------------------------------- | --------- |
| 1-2   | Flask API development, endpoint implementation, API key authentication                  | Functional Flask API with scan endpoint                 | Completed |
| 3-4   | Laravel installation, database schema design, authentication scaffolding                | Laravel project with database migrations                | Completed |
| 5     | Dashboard UI development, result visualization, CSV/PDF export features                 | Complete web dashboard interface                        | Completed |
| 6-7   | Docker Compose configuration, Mosquitto broker deployment, TLS certificate generation   | Operational Docker testbed with insecure/secure brokers | Completed |
| 8-9   | ESP32 firmware development, sensor integration (DHT11, LDR, PIR), telemetry publishing  | ESP32 publisher generating realistic MQTT traffic       | Completed |
| 10    | Security hardening (CSRF, input validation, XSS protection), rate limiting              | Security-hardened application                           | Completed |
| 11-12 | Comprehensive functional testing, security penetration testing, performance measurement | Test reports with 15 test cases, performance metrics    | Completed |
| 13    | Documentation completion, deployment guide, user manual                                 | Technical documentation, deployment instructions        | Completed |
| 14    | FYP2 report finalization, presentation preparation, demonstration rehearsal             | FYP2 final report, presentation, demonstration          | Completed |

### 3.6.3 Risk Management

Risk identification and mitigation planning were conducted during project planning phase to anticipate potential obstacles and establish contingency measures. Seven primary risk categories were identified through analysis of technical dependencies, resource constraints, and academic timeline pressures.

**Table 3.7: Risk Register and Mitigation Strategies**

| Risk ID | Risk Description                                                  | Probability | Impact | Mitigation Strategy                                                                                                                         | Actual Outcome                                                                                                                       |
| ------- | ----------------------------------------------------------------- | ----------- | ------ | ------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| R1      | Hardware component failure (ESP32, sensors)                       | Low         | Medium | Purchased backup ESP32 board, validated sensor functionality before integration                                                             | No failures encountered; sensors operated reliably throughout testing                                                                |
| R2      | Docker installation issues on Windows development machine         | Medium      | Medium | Prepared alternative using native Mosquitto installation, documented WSL2 troubleshooting                                                   | Docker Desktop successfully installed; WSL2 backend configuration required                                                           |
| R3      | Laravel-Flask integration challenges (API communication failures) | Medium      | High   | Developed comprehensive API documentation, implemented detailed error logging, used Postman for endpoint testing                            | Initial CORS configuration required; resolved via Flask-CORS extension                                                               |
| R4      | TLS certificate validation errors in scanner                      | High        | Medium | Generated multiple certificate variants (self-signed, CA-signed), implemented flexible certificate verification options                     | Certificate validation initially rejected self-signed certs; disabled verification for testing, documented for production deployment |
| R5      | Requirement changes during FYP2 based on testing discoveries      | Medium      | Low    | Adopted Agile iterative approach enabling mid-course corrections, maintained requirement traceability matrix                                | Requirements evolved to include PDF export and enhanced filtering; accommodated without major rework                                 |
| R6      | Time constraints for completing all FYP2 objectives               | Medium      | High   | Created detailed Gantt chart with buffer weeks, prioritized core features over optional enhancements, regular supervisor progress reviews   | Week 11-12 required extended hours to complete comprehensive testing; documentation completed on schedule                            |
| R7      | ESP32 WiFi connectivity issues affecting telemetry publishing     | Medium      | Medium | Tested multiple WiFi configurations, implemented connection retry logic with exponential backoff, documented WiFi credentials configuration | Initial connection drops resolved via improved error handling; stable operation achieved after firmware refinement                   |

**Risk Management Outcomes:**
The proactive risk identification process proved valuable in anticipating technical challenges before they manifested as project-blocking issues. R4 (TLS certificate validation) was correctly identified as high probability and required multiple troubleshooting iterations to resolve self-signed certificate rejection. R6 (time constraints) materialized during Weeks 11-12 when comprehensive testing revealed unanticipated edge cases requiring code modifications, necessitating extended development hours but remaining within overall timeline through earlier buffer allocation. R3 (Laravel-Flask integration) encountered CORS policy violations not anticipated during risk planning, requiring addition of Flask-CORS extension and configuration adjustments. Overall, the risk register facilitated prompt issue resolution by pre-establishing mitigation strategies.

---

## 3.7 Chapter Summary

This chapter presented the comprehensive methodology employed in developing and validating the MQTT network security scanner for IoT environments. The systematic approach encompassed five key components that collectively ensured rigorous academic standards while delivering a functional prototype suitable for real-world security assessment.

The development model evaluation process (Section 3.2) examined five established methodologies—Waterfall, Agile, Spiral, V-Model, and Prototype—against project-specific criteria including academic timeline constraints, requirement volatility expectations, and iterative refinement needs. Agile methodology emerged as the optimal selection based on four justification factors: accommodation of requirement evolution discovered during FYP1 testing, support for iterative refinement with supervisor feedback integration, continuous validation throughout development lifecycle, and natural alignment with academic semester scheduling. The comparative analysis documented in Table 3.1 demonstrated Agile's superiority over alternatives constrained by sequential rigidity (Waterfall), excessive documentation overhead (Spiral), predetermined test specifications (V-Model), or insufficient structural framework (Prototype).

The development activities detailed in Section 3.4 documented systematic progression through five distinct phases across 28 weeks. FYP1 established requirements and system architecture through comprehensive design modeling (use case diagrams, sequence diagrams, activity diagrams, flowcharts) and delivered a functional CLI prototype with CSV reporting capability. FYP2 Iteration 1 implemented the web platform foundation comprising Laravel dashboard, Flask API layer, and MySQL database integration. FYP2 Iteration 2 integrated hardware testbed components including Docker-containerized Mosquitto brokers configured for insecure (port 1883) and secure (port 8883) operation, ESP32 microcontroller publishing realistic telemetry via DHT11, LDR, and PIR sensors, and validation against physical broker device at 192.168.100.57. FYP2 Iteration 3 conducted security hardening implementing CSRF protection, input validation, SQL injection prevention, and XSS safeguards, followed by comprehensive functional testing validating all scan outcome classifications and performance measurement confirming acceptable scan completion times.

The system requirements specification (Section 3.5) documented minimal hardware infrastructure totaling MYR 64.50 investment (ESP32, sensors, breadboard, wiring components) and zero-cost software stack leveraging open-source components (Python, Flask, Laravel, MySQL, Docker, Mosquitto). The entirely open-source software selection eliminated licensing costs while providing production-quality reliability and extensive community support. Hardware requirements were deliberately constrained to accessible components enabling replication in resource-limited academic environments.

The project planning framework (Section 3.6) structured development activities across two 14-week semesters with clear milestone definitions and deliverable specifications. The risk management process identified seven potential obstacles ranging from hardware failures to integration challenges, establishing mitigation strategies that proved effective when risks materialized. The Gantt chart scheduling ensured adequate time allocation for requirements, design, implementation, testing, and documentation activities while maintaining buffer capacity for unanticipated issues.

This methodological foundation directly supports Chapter 4 (Implementation), which examines source code architecture, database schema design, API endpoint specifications, and ESP32 firmware logic in technical detail. The systematic development approach documented herein enabled production of empirical test results presented in Chapter 5 (Testing and Results), where functional validation across diverse broker configurations, security penetration testing outcomes, and performance measurements demonstrated the scanner's effectiveness in detecting MQTT security misconfigurations in IoT deployments. The rigorous methodology ensured that research objectives defined in Chapter 1 were systematically operationalized into concrete technical artifacts suitable for academic evaluation and practical security assessment applications.

---

**List of Figures for Chapter 3:**

**Figure 3.1: Three-Tier System Architecture Diagram**

- **Caption:** System architecture illustrating three-tier design with Laravel dashboard (Tier 3 - Presentation), Flask API (Tier 2 - Orchestration), and Python MQTT scanner (Tier 1 - Execution).
- **Explanatory Text:** Figure 3.1 depicts the modular three-tier architecture where the Laravel dashboard handles user authentication and result visualization, the Flask API enforces API key authentication and rate limiting while orchestrating scan execution, and the Python scanner performs protocol-level MQTT assessment including port scanning, connection probing, TLS certificate inspection, and topic observation. This separation-of-concerns design ensures that frontend modifications do not impact backend scanning logic, enabling independent testing of each tier and facilitating future migration to distributed deployment configurations where the web dashboard and scanning engine may reside on separate network hosts.

**Figure 3.2: Scan Request Sequence Diagram**

- **Caption:** Sequence diagram showing message flow for scan request processing from user submission through Laravel dashboard, Flask API invocation, Python scanner execution, and database persistence.
- **Explanatory Text:** The sequence diagram (Figure 3.2) illustrates the complete request-response cycle where the authenticated user submits a target IP address via Laravel dashboard, Laravel constructs an HTTP POST request with API key authentication header directed to Flask's `/api/scan` endpoint, Flask validates credentials and input format before invoking the Python scanner module, the scanner performs TCP port discovery and MQTT connection probing, Flask serializes results to JSON and returns HTTP 200 response, and Laravel parses the response to insert records into MySQL database before rendering the results table in the dashboard. This workflow demonstrates the clear interface contracts between architectural tiers.

**Figure 3.3: Scan Workflow Flowchart**

- **Caption:** Flowchart documenting algorithmic decision logic for MQTT broker connection outcome classification.
- **Explanatory Text:** Figure 3.3 presents the decision tree implemented in the classification module, showing how the scanner progresses from target input validation through TCP port scanning, anonymous connection attempts, authenticated connection attempts with test credentials, error type differentiation (TLS handshake failure versus connection refused versus timeout), and ultimate classification into security categories (Open Broker, Authentication Required, TLS Required, Connection Refused, Timeout, Network Unreachable). Each decision point in the flowchart corresponds to specific exception handling logic in the Python implementation, ensuring comprehensive coverage of possible MQTT broker response scenarios encountered during security assessment.

**Figure 3.4: Dashboard Activity Diagram**

- **Caption:** Activity diagram showing parallel swim lanes for user interactions, Laravel controller processing, Flask API orchestration, and Python scanner execution.
- **Explanatory Text:** The activity diagram (Figure 3.4) employs four parallel swim lanes to illustrate concurrent activities during scan execution. The user swim lane shows authentication, dashboard navigation, target input, scan initiation, and result viewing. The Laravel swim lane documents session validation, CSRF token verification, HTTP request construction, JSON response parsing, and database insertion operations. The Flask API swim lane details API key validation, input sanitization, scanner invocation, and JSON serialization. The Python scanner swim lane shows concurrent port scanning across multiple target IPs, parallel MQTT connection probing on ports 1883 and 8883, and result aggregation. This visualization clarifies the asynchronous nature of the scan execution process and the role of each component in the overall workflow.

**Figure 3.5: Hardware Testbed Network Topology**

- **Caption:** Network topology diagram showing development workstation, Docker broker containers, ESP32 telemetry publisher, and physical broker device interconnections.
- **Explanatory Text:** Figure 3.5 maps the physical and logical network topology of the hardware testbed infrastructure used for validation testing. The development workstation (127.0.0.1) hosts Laravel web server on port 8000, Flask API on port 5000, and MySQL database on port 3306. Docker containers expose Mosquitto insecure broker on 172.18.0.2:1883 and Mosquitto secure broker on 172.18.0.3:8883 via Docker bridge network. The ESP32 microcontroller resides on the local WiFi network (192.168.1.x range) and publishes sensor telemetry to both Docker brokers, simulating realistic IoT traffic patterns. A physical MQTT broker device accessible at 192.168.100.57 validates scanner operation against network-accessible targets beyond localhost. This multi-broker configuration enabled comprehensive testing across diverse security postures including anonymous access, TLS-only, and authenticated access scenarios.

---
