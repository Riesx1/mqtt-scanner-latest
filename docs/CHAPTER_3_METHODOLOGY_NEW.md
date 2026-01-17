# CHAPTER 3: METHODOLOGY

---

## 3.1 Introduction to the Chapter

This chapter illustrates the systematic approach employed in the design, development, testing, and deployment of the Internet of Things Network Scanning Tool for MQTT Devices. The tool addresses the critical gap of lightweight, protocol-aware scanners capable of detecting insecure configurations in MQTT deployments, including unauthenticated access, absence of TLS encryption, unrestricted topic subscriptions, and inadequate authentication enforcement.

The methodology encompasses several key components: the rationale behind selecting the Agile-inspired development model, comprehensive explanation of activities performed at each development phase, detailed system design specifications with architectural and behavioral diagrams, hardware and software requirements, budget allocation, and structured project planning spanning two academic semesters (FYP1 and FYP2).

This chapter demonstrates how the research objectives defined in Chapter 1 were systematically translated into actionable development steps, ultimately delivering a functional, modular, and user-friendly scanning tool capable of operating in simulated IoT environments while maintaining scalability for future enhancements such as real-time dashboards and cloud-based deployment. The methodological foundation presented here directly supports the implementation documented in Chapter 4 and validates the testing outcomes presented in Chapter 5.

---

## 3.2 Available Software Development Models

Before selecting the most appropriate methodology for this project, several established software development models were evaluated based on their suitability for IoT security tool development, academic timeline constraints, and the need for iterative refinement.

### 3.2.1 Waterfall Model

**Description:**
The Waterfall model follows a linear sequential approach where each development phase must be completed before progressing to the next. The typical phases include requirements analysis, system design, implementation, testing, deployment, and maintenance. Once a phase is completed, returning to previous phases is difficult and costly.

**Characteristics:**

- Highly structured with clearly defined stages
- Extensive documentation at each phase
- Suitable for projects with well-defined, stable requirements
- Limited flexibility for requirement changes
- Testing occurs only after implementation is complete

**Applicability to This Project:**
While the Waterfall model's structured approach provides clear milestones, it lacks the flexibility needed for a research-oriented project where requirements may evolve based on testing feedback and supervisor guidance. The inability to accommodate mid-project adjustments made this model unsuitable.

### 3.2.2 Agile Methodology

**Description:**
Agile is an iterative and incremental development approach that emphasizes flexibility, customer collaboration, continuous feedback, and rapid delivery of working software through short development cycles called sprints or iterations. Agile embraces change and prioritizes working software over comprehensive documentation.

**Characteristics:**

- Iterative development with frequent releases
- Continuous stakeholder engagement and feedback
- Adaptive planning allowing requirement changes
- Emphasis on collaboration and cross-functional teams
- Regular testing throughout development lifecycle
- Incremental delivery of functional components

**Applicability to This Project:**
Agile's flexibility proved highly suitable for this project's evolving nature, enabling systematic progression from FYP1's CLI prototype through FYP2's web-based system with hardware integration. The iterative approach allowed incorporation of lessons learned and supervisor feedback at each development stage.

### 3.2.3 Spiral Model

**Description:**
The Spiral model combines iterative development with systematic risk management. Each spiral iteration involves four phases: planning, risk analysis, engineering, and evaluation. The model emphasizes identifying and mitigating risks early in the development process.

**Characteristics:**

- Risk-driven approach with explicit risk analysis phases
- Multiple iterations through development phases
- Suitable for large-scale, complex, high-risk projects
- Extensive risk management documentation required
- Higher cost and complexity than other models

**Applicability to This Project:**
While the Spiral model's risk management emphasis is valuable, its complexity and extensive documentation requirements were excessive for an academic project. The overhead of formal risk analysis phases would have consumed time better spent on implementation and testing.

### 3.2.4 V-Model (Verification and Validation)

**Description:**
The V-Model extends the Waterfall approach by emphasizing testing at each development stage. Each development phase has a corresponding testing phase, forming a V-shape where the left side represents specification and design, while the right side represents testing and validation.

**Characteristics:**

- Parallel development and testing activities
- Emphasis on verification and validation
- High discipline and rigorous testing procedures
- Suitable for projects requiring high reliability
- Limited flexibility for requirement changes
- High emphasis on documentation

**Applicability to This Project:**
The V-Model's rigidity and extensive testing documentation requirements made it impractical for this research-oriented project where exploratory testing informed design decisions rather than following predetermined test specifications.

### 3.2.5 Prototype Model

**Description:**
The Prototype model focuses on building an initial working prototype to gather user feedback before committing to full-scale development. The prototype undergoes refinement based on stakeholder input until requirements are clearly understood, after which the final system is developed.

**Characteristics:**

- Early visualization of system functionality
- User involvement in requirements refinement
- Suitable when requirements are unclear initially
- May lead to incomplete problem analysis
- Prototypes may be discarded after requirements clarification

**Applicability to This Project:**
While prototyping was employed during FYP1 to validate scanning concepts, a full Prototype model approach was insufficient. The project required structured development beyond initial prototyping, making a complete Agile framework more appropriate for FYP2's production-grade implementation.

---

## 3.3 Selected Development Model and Justification

### 3.3.1 Model Selection: Agile Methodology

After careful evaluation of the available software development models, **Agile Methodology** was selected as the most appropriate approach for developing the MQTT Network Scanning Tool. This decision was based on comprehensive analysis of project requirements, academic constraints, and the need for adaptive development.

### 3.3.2 Justification for Selecting Agile

The selection of Agile methodology over alternative models is justified by the following compelling reasons:

**1. Evolving Requirements:**
Initial project requirements focused on CLI-based MQTT scanning. As development progressed through FYP1 to FYP2, new requirements emerged including web dashboard integration, real-time sensor data visualization, database storage, and advanced authentication mechanisms. Agile's flexibility accommodated these evolving requirements without requiring complete project restructuring.

**2. Iterative Development Benefits:**
The transition from FYP1 (CLI prototype) to FYP2 (web-based system with hardware integration) required incremental development where each iteration built upon previous work while incorporating lessons learned. Agile's sprint-based approach enabled systematic progression through distinct development phases:

- FYP1: Core scanning engine and CSV reporting
- FYP2 Phase 1: Web interface development (Flask API + Laravel dashboard)
- FYP2 Phase 2: Hardware integration (Docker brokers + ESP32 publishers)
- FYP2 Phase 3: Security hardening and comprehensive testing

**3. Continuous Testing and Feedback:**
Agile's emphasis on testing throughout the development cycle was crucial for validating MQTT protocol behavior, TLS certificate analysis, and hardware integration. Regular demonstrations to supervisors provided critical feedback that shaped design decisions and feature prioritization.

**4. Risk Mitigation:**
Early identification of technical challenges—such as TLS certificate validation errors, authentication handling complexities, and ESP32 firmware stability issues—enabled prompt resolution before they escalated into project-blocking problems.

**5. Academic Timeline Compatibility:**
Agile's sprint structure aligned well with the academic semester schedule, allowing clear milestone definitions for FYP1 completion (14 weeks) and FYP2 deliverables (14 weeks), with each sprint producing tangible results for evaluation.

### 3.3.3 Comparative Justification Against Other Models

**Agile vs. Waterfall Model:**

- **Waterfall's Limitation:** The Waterfall model was rejected primarily due to its inflexibility in accommodating requirement changes. During FYP1 testing, several adjustments were needed based on broker behavior observations and supervisor feedback that would have been impossible to incorporate under Waterfall's linear structure.
- **Agile's Advantage:** Agile's iterative sprints allowed seamless integration of new requirements (e.g., web dashboard, database storage, hardware integration) without requiring complete project restructuring.

**Agile vs. Spiral Model:**

- **Spiral's Limitation:** The Spiral model's formal risk analysis phases and extensive documentation requirements would have introduced unnecessary overhead for an academic project with a 28-week timeline (14 weeks FYP1 + 14 weeks FYP2).
- **Agile's Advantage:** Agile addressed risk management implicitly through regular testing and feedback loops, providing adequate risk mitigation without the administrative burden of formal spiral iterations.

**Agile vs. V-Model:**

- **V-Model's Limitation:** The V-Model's predetermined test specifications conflicted with this project's research-oriented nature, where testing outcomes often informed design decisions rather than validating pre-existing specifications.
- **Agile's Advantage:** Agile's continuous testing approach enabled exploratory testing to drive architectural refinements, particularly when addressing TLS certificate validation errors and authentication handling complexities.

**Agile vs. Prototype Model:**

- **Prototype Model's Limitation:** While the Prototype model worked well for FYP1's initial validation phase, it lacked the structured framework needed for FYP2's production-grade implementation with multiple integrated subsystems (web interface, database, hardware testbed).
- **Agile's Advantage:** Agile provided the prototyping benefits during early sprints while maintaining sufficient structure for scaling to a complete production system with proper authentication, error handling, and performance optimization.

**Conclusion:**
Agile methodology emerged as the optimal choice, offering the flexibility needed for research-driven development while maintaining sufficient structure for systematic progression through complex integration challenges. The decision proved correct as evidenced by successful completion of all project objectives within the academic timeline.

---

## 3.4 Development Phases: Activities and Deliverables

This section explains the systematic activities performed at each development phase, following the Agile-inspired iterative approach from FYP1 through FYP2. Each phase description includes specific activities, methodologies employed, and concrete deliverables produced.

### 3.4.1 Phase 1: Requirements Analysis and Project Planning (FYP1 Week 1-2)

**Activities Performed:**

**1. Literature Review and Gap Analysis:**
Conducted comprehensive review of existing MQTT security literature and scanning tools to identify research gaps. Key findings included:

- Lack of lightweight, protocol-aware MQTT scanners
- Absence of tools specifically designed for IoT security posture assessment
- Limited detection capabilities for anonymous broker access and missing TLS enforcement
- Inadequate coverage of MQTT-specific vulnerabilities in general-purpose network scanners

The literature review informed requirement specifications by highlighting critical security misconfigurations that the tool must detect.

**2. Requirements Derivation:**
Requirements were systematically established through multiple sources:

- **Project objectives** defined in Chapter 1, Section 1.3
- **Literature analysis gaps** identified in Chapter 2
- **Project scope boundaries** established in Chapter 1, Section 1.5
- **Technical feasibility assessment** considering available hardware, development environment, and 28-week timeline
- **Stakeholder consultations** with project supervisor regarding functional priorities

The requirements were categorized into:

- **Functional Requirements:** Network scanning, MQTT broker detection, TLS identification, authentication testing, vulnerability classification, CSV report generation
- **Non-Functional Requirements:** Performance (scan completion within reasonable time), usability (clear output format), portability (cross-platform compatibility), scalability (support for subnet scanning)

**3. Project Timeline Development:**
Created comprehensive Gantt charts for both FYP1 and FYP2 phases:

- **FYP1 Timeline (14 weeks):** Literature review → Requirements definition → System design → CLI prototype → Testing → Documentation → Presentation
- **FYP2 Timeline (14 weeks):** Web interface development → Hardware integration → Security hardening → Comprehensive testing → Final documentation

Each milestone was assigned specific deliverables and completion criteria to enable progress tracking.

**4. Work Breakdown Structure (WBS):**
Decomposed the project into hierarchical work packages:

- **Level 1:** Major phases (Requirements, Design, Implementation, Testing, Documentation)
- **Level 2:** Phase-specific activities (e.g., Implementation → Backend Development, Frontend Development, Hardware Integration)
- **Level 3:** Granular tasks (e.g., Backend Development → Scanner Core, MQTT Probe Module, Vulnerability Detection, Report Generation)

This WBS facilitated task assignment, effort estimation, and resource allocation throughout the project lifecycle.

**5. Research Methodology Selection:**
Determined that an Agile-inspired approach would be most suitable for this research-oriented development project, as detailed in Section 3.3.

**Deliverables:**

- Project proposal document
- Detailed requirements specification (functional and non-functional)
- Gantt charts for FYP1 and FYP2
- Work breakdown structure
- Research methodology justification document

### 3.4.2 Phase 2: System Design (FYP1 Week 3-5)

This phase focused on translating requirements into comprehensive design specifications using various software engineering modeling techniques. The design phase produced detailed architectural and behavioral diagrams that served as blueprints for implementation.

**Activities Performed:**

**1. System Architecture Design:**

The system architecture was designed following a modular three-tier pattern to ensure separation of concerns, maintainability, and scalability:

- **Tier 1 - Scanning Engine Layer:**
    - Python-based MQTT scanner using `paho-mqtt` library for protocol-level interactions
    - Network scanning module using `socket` and `scapy` for port discovery
    - Modular components: `scanner.py` (orchestration), `mqtt_probe.py` (connection testing), `vuln_detect.py` (classification)
- **Tier 2 - API Layer:**
    - Flask RESTful API providing JSON endpoints for scanner integration
    - Session management and API key authentication
    - Rate limiting and request validation middleware
- **Tier 3 - User Interface Layer:**
    - Laravel web dashboard with authentication (Laravel Breeze)
    - Database layer (MySQL) for persistent scan result storage
    - Responsive frontend using Tailwind CSS

The three-tier architecture ensures that frontend changes do not impact backend scanning logic, and new scanning modules can be added without modifying the web interface.

**2. System Architecture Diagram Creation (Figure 3.1):**

Developed comprehensive architecture diagram illustrating:

- **Component Interactions:** User → Laravel Dashboard → Flask API → Python Scanner → MQTT Brokers
- **Data Flow:** Scan requests (HTTP POST) → Scanner execution → JSON response → Database storage → UI rendering
- **Authentication Mechanisms:** Laravel session middleware, Flask API key verification
- **Network Topology:** Development machine hosting Laravel/Flask, Docker containers running Mosquitto brokers, ESP32 physical devices publishing sensor data

The architecture diagram provides stakeholders with a high-level understanding of system components and their interactions.

**3. Flowchart Development (Figure 3.2):**

Created detailed flowcharts representing algorithmic logic:

**Main Scan Workflow Flowchart:**

```
Start
  → User inputs target IP/CIDR
  → Validate input format (regex check)
  → [Valid?]
     Yes → Parse IP range
        → For each IP in range:
           → Scan port 1883 (plaintext)
           → Scan port 8883 (TLS)
           → For each open port:
              → Attempt MQTT CONNECT (anonymous)
              → [Connected?]
                 Yes → Test topic subscription
                    → Classify as "Open Broker"
                 No → Attempt MQTT CONNECT (with credentials)
                    → [Connected?]
                       Yes → Classify as "Authenticated Broker"
                       No → Check TLS handshake error
                          → Classify as "TLS Required" or "Connection Refused"
           → Store results
        → Generate CSV report
        → Display results in dashboard
  → [Invalid?]
     No → Display error message
  → End
```

This flowchart guided the implementation of `scanner.py` and `mqtt_probe.py` modules.

**4. Activity Diagram (Figure 3.3):**

Designed UML activity diagrams illustrating concurrent user and system activities:

- **User Activities:** Login → Navigate to dashboard → Enter scan parameters → Initiate scan → View real-time progress → Export results
- **System Activities (Parallel):** Validate session → Process scan request → Execute concurrent port scans → Aggregate results → Update database → Return JSON response
- **Decision Points:** Authentication success/failure, broker reachable/unreachable, TLS available/unavailable, subscription allowed/denied

The activity diagram clarified user workflows and helped identify required UI elements (progress indicators, result tables, export buttons).

**5. Sitemap Diagram (Figure 3.4):**

Defined hierarchical website navigation structure:

```
Root (/)
├── Home (/) - Public landing page
├── Login (/login) - Authentication page
├── Register (/register) - User registration
├── Dashboard (/dashboard) [Protected Route]
│   ├── Scan Initiation Form
│   ├── Real-time Scan Progress
│   ├── Results Table (sortable, filterable)
│   ├── Scan History (previous scans)
│   └── Export Options (CSV download)
├── Profile (/profile) [Protected Route]
│   ├── User Information
│   ├── API Key Management
│   └── Account Settings
└── Logout (/logout) [Protected Action]
```

The sitemap ensured comprehensive route coverage during Laravel development and provided basis for navigation menu implementation.

**6. Threat Model Diagram (Figure 3.5):**

Conducted threat modeling using STRIDE framework to identify security risks and corresponding mitigations:

| Threat Category            | Specific Threat                  | Risk Level | Mitigation Strategy                                     |
| -------------------------- | -------------------------------- | ---------- | ------------------------------------------------------- |
| **Spoofing**               | Unauthorized access to dashboard | High       | Laravel Breeze authentication with session management   |
| **Tampering**              | SQL injection attacks            | High       | Laravel Eloquent ORM with parameterized queries         |
| **Repudiation**            | Untracked scan activities        | Medium     | Database logging of all scan operations with timestamps |
| **Information Disclosure** | Exposure of MQTT credentials     | Medium     | Credential encryption in database, HTTPS for dashboard  |
| **Denial of Service**      | Scanner API abuse                | Medium     | Rate limiting (5 scans per minute per user)             |
| **Elevation of Privilege** | Broken access control            | High       | Laravel middleware protecting all authenticated routes  |
| **CSRF Attacks**           | Cross-site request forgery       | High       | CSRF tokens on all state-changing forms                 |

The threat model informed security implementation priorities during FYP2's security hardening phase.

**Deliverables:**

- System architecture diagram (Figure 3.1)
- Scan workflow flowchart (Figure 3.2)
- User activity diagram (Figure 3.3)
- Website sitemap diagram (Figure 3.4)
- Threat model diagram and documentation (Figure 3.5)
- Design specification document (50+ pages)

### 3.4.3 Phase 3: CLI Prototype Development (FYP1 Week 6-8)

This phase marked the transition from design to implementation, focusing on building a functional command-line prototype that validated core scanning concepts and MQTT interaction logic.

**Activities Performed:**

**1. Development Environment Setup:**

Established isolated development environment to ensure dependency compatibility and reproducibility:

- Installed **Python 3.9** with pip package manager
- Created virtual environment `mqttenv` using `python -m venv` to isolate project dependencies
- Configured **VS Code** IDE with extensions:
    - Python (Microsoft) for IntelliSense and debugging
    - Pylance for type checking
    - GitLens for version control visualization
- Installed core libraries:
    - `paho-mqtt==1.6.1` for MQTT protocol implementation
    - `scapy==2.4.5` for low-level network packet manipulation
    - Standard library modules: `socket`, `ssl`, `csv`, `argparse`
- Initialized **Git repository** with `.gitignore` configured for Python projects
- Created GitHub remote repository for version control and backup

**2. Core Scanner Module Implementation:**

Developed `scanner.py` as the main orchestration module with the following functions:

**`scan_ip(ip_address, ports=[1883, 8883])`**

- Performs TCP port scanning on specified IP address
- Returns list of open ports from the provided port list
- Uses socket timeout of 3 seconds to avoid indefinite blocking
- Handles network unreachable and connection refused exceptions

**`probe_mqtt_connection(ip, port, username=None, password=None, use_tls=False)`**

- Establishes MQTT connection using paho-mqtt client
- Attempts CONNECT packet with optional credentials
- Captures connection response codes (0=success, 4=bad credentials, 5=not authorized)
- Implements TLS context for port 8883 connections
- Returns structured dictionary: `{connected: bool, response_code: int, error_message: str}`

**`analyze_tls_certificate(ip, port=8883)`**

- Extracts X.509 certificate details from TLS-enabled brokers
- Uses `ssl.get_server_certificate()` to retrieve PEM-encoded certificate
- Parses certificate fields: subject, issuer, validity period (notBefore, notAfter)
- Returns certificate information or None if TLS unavailable

**`categorize_outcome(probe_result)`**

- Classifies scan results based on connection response:
    - **"Open Broker"**: Anonymous connection successful (response code 0, no credentials)
    - **"Authentication Required"**: Connection refused due to missing credentials (response code 5)
    - **"Invalid Credentials"**: Connection rejected with bad authentication (response code 4)
    - **"TLS Required"**: TLS handshake failure or SSL error
    - **"Connection Refused"**: TCP connection rejected
    - **"Network Unreachable"**: No route to host
    - **"Timeout"**: No response within timeout period
    - **"Unknown"**: Unexpected response code

**`run_scan(target, output_file='mqtt_scan_report.csv')`**

- Orchestrates complete scan workflow
- Parses target as single IP or CIDR notation (e.g., 192.168.1.0/24)
- Iterates through IP range scanning both ports 1883 and 8883
- Aggregates results and passes to CSV reporter
- Displays progress indicators to console

**3. CSV Report Generation Module:**

Implemented `reporter.py` for structured output:

- CSV columns: `IP Address, Port, Status, TLS Available, Authentication Required, Certificate Subject, Certificate Issuer, Certificate Expiry, Topics Observed, Timestamp`
- UTF-8 encoding with proper escaping for special characters
- Append mode to support incremental scan results
- Header row written only if file doesn't exist

**4. MQTT Probe Module:**

Developed `mqtt_probe.py` with granular connection testing:

- **Anonymous Probe**: Tests broker with no credentials
- **Credential Probe**: Tests with provided username/password
- **Topic Subscription Test**: Attempts to subscribe to `#` wildcard topic after successful connection
- **Message Capture**: Records published messages during brief listening period (5 seconds)
- Implements error handling for:
    - SSL handshake failures
    - Connection timeouts
    - Authentication rejections
    - Protocol version mismatches

**5. Vulnerability Detection Logic:**

Created `vuln_detect.py` to identify security misconfigurations:

- **Open Anonymous Access**: Broker accepts connections without authentication
- **Missing TLS Encryption**: Plaintext communication allowed on port 1883
- **Weak Authentication**: Broker accepts common default credentials (tested against list: admin/admin, mqtt/mqtt, guest/guest)
- **Unrestricted Topic Access**: Ability to subscribe to all topics using `#` wildcard
- Assigns severity levels: Critical, High, Medium, Low

**6. Initial Testing Against Local Broker:**

Validated scanner functionality using Mosquitto broker on localhost:

- Configured test broker with authentication enabled (`mosquitto_passwd`)
- Tested both successful and failed connection scenarios
- Verified CSV output format and content accuracy
- Confirmed TLS certificate extraction on port 8883
- Validated CIDR subnet scanning (127.0.0.0/24 loopback range)

**Testing Evidence:**
Screenshots captured demonstrating:

- Python virtual environment activation (Figure 3.6)
- Scanner execution with target parameter
- CSV output file generation and content
- Console output showing scan progress

**Deliverables:**

- Working CLI scanner (`scanner.py`, `mqtt_probe.py`, `vuln_detect.py`, `reporter.py`)
- CSV report generation capability
- Unit test scripts for core functions
- Documentation: README.md with usage instructions
- FYP1 interim progress report

### 3.3.4 Phase 4: Web Interface Development (FYP2 Week 1-6)

**Activities Performed:**

**1. Flask API Development (Week 1-2):**

- Created `app.py` with RESTful endpoints:
    - `POST /api/scan`: Initiates scan, returns JSON results
    - `GET /api/results`: Retrieves stored scan results
- Implemented dual authentication (session-based for UI, API key for Laravel)
- Added rate limiting (5 scans per 60 seconds per IP)
- Integrated scanner.py as importable module

**2. Laravel Dashboard Implementation (Week 3-4):**

- Installed Laravel 10 with Breeze authentication
- Created database schema:
    - `mqtt_scan_history` table (scan metadata)
    - `mqtt_scan_results` table (individual findings)
    - `users` table (authentication)
- Developed `MqttScannerController` with methods:
    - `index()`: Display dashboard
    - `scan()`: Call Flask API, store results
    - `scanHistory()`: Retrieve user scan history
- Built responsive Blade templates with Tailwind CSS

**3. Integration Testing (Week 5-6):**

- Verified Laravel ↔ Flask API communication
- Tested database storage and retrieval
- Validated authentication flows
- Cross-browser compatibility testing (Chrome, Firefox, Edge)

**Deliverables:**

- Flask API (`app.py`)
- Laravel dashboard application
- Database migrations
- Integration test results

### 3.3.5 Phase 5: Hardware Integration (FYP2 Week 7-8)

**Activities Performed:**

**1. Docker Broker Deployment:**
Created `docker-compose.yml` with two Mosquitto brokers:

```yaml
mosquitto_insecure: # Port 1883, no authentication
mosquitto_secure: # Port 8883, TLS + authentication
```

**2. ESP32 Firmware Development:**
Programmed `esp32_mixed_security.ino` with:

- Dual MQTT client configuration (secure + insecure)
- Sensor integration:
    - DHT11 (temperature/humidity) → Secure broker (8883)
    - LDR (light sensor) → Secure broker (8883)
    - PIR (motion sensor) → Insecure broker (1883)
- Publish interval: 3 seconds
- TLS certificate embedding for secure connection

**3. Physical Wiring:**
Connected sensors to ESP32:

- DHT11: GPIO 4
- LDR: GPIO 34 (analog)
- PIR: GPIO 27

**4. Network Configuration:**

- Assigned static IPs to Docker containers
- Connected ESP32 to local WiFi
- Verified broker accessibility from development machine

**Deliverables:**

- Docker broker configurations
- ESP32 firmware with sensor code
- Hardware connection documentation
- Network topology diagram

### 3.3.6 Phase 6: Security Hardening (FYP2 Week 9-11)

**Activities Performed:**

**1. Authentication Enforcement:**

- Applied Laravel `auth` middleware to all dashboard routes
- Implemented Flask session management
- Added API key rotation capability

**2. Input Validation:**

- Regex validation for target IP/CIDR inputs
- SQL injection prevention via Eloquent ORM
- XSS prevention through Blade escaping

**3. CSRF Protection:**

- Enabled Laravel CSRF middleware
- Added CSRF tokens to all forms
- Exempted API routes with API key authentication

**4. Rate Limiting:**

- Implemented IP-based scan rate limiting
- Added Laravel rate limiter for web requests

**5. Security Testing:**

- Tested broken access control scenarios
- Attempted SQL injection attacks
- Verified CSRF token enforcement
- Conducted rate limit testing

**Deliverables:**

- Security test report
- Updated threat model
- Security configuration documentation

### 3.3.7 Phase 7: Comprehensive Testing (FYP2 Week 12-13)

**Activities Performed:**

**1. Functional Testing:**

- Tested all scan outcomes:
    - Network unreachable
    - Connection refused
    - Timeout
    - Anonymous success
    - Authentication required
    - Authentication failed
- Verified CSV export functionality
- Tested database storage and retrieval

**2. Hardware-in-the-Loop Testing:**

- Scanned Docker brokers (both 1883 and 8883)
- Verified ESP32 sensor data publication detection
- Tested topic capture functionality
- Validated TLS certificate extraction

**3. Integration Testing:**

- End-to-end workflow testing (login → scan → view results → export)
- Cross-component integration verification
- API contract validation

**4. Performance Testing:**

- Measured scan completion time for /24 subnet
- Database query performance analysis
- Concurrent user simulation

**Deliverables:**

- Comprehensive test report
- Bug fix log
- Performance metrics

### 3.3.8 Phase 8: Documentation and Finalization (FYP2 Week 14)

**Activities Performed:**

**1. Technical Documentation:**

- Created README.md with installation instructions
- Wrote API documentation
- Documented database schema
- Prepared deployment guide

**2. Report Writing:**

- Completed Chapter 4 (Implementation)
- Documented Chapter 5 (Testing and Results)
- Finalized Chapter 3 (Methodology)

**3. Presentation Preparation:**

- Created PowerPoint slides
- Prepared live demonstration script
- Recorded demo video backup

**Deliverables:**

- FYP2 final report
- Presentation materials
- Demonstration video

---

## 3.5 System Requirements

This section specifies the hardware and software requirements necessary for developing, deploying, and operating the MQTT Network Scanning Tool. The requirements were determined based on tool functionality, performance expectations, and budget constraints.

### 3.5.1 Hardware Requirements

| Component               | Specification                            | Quantity | Purpose                          |
| ----------------------- | ---------------------------------------- | -------- | -------------------------------- |
| **Development Machine** | Intel Core i5/Ryzen 5 or higher, 8GB RAM | 1        | Run Laravel, Flask, Docker, IDE  |
| **ESP32 Dev Board**     | ESP32-WROOM-32 with WiFi                 | 1        | IoT publisher for testbed        |
| **DHT11 Sensor**        | Temperature & Humidity                   | 1        | Secure broker telemetry source   |
| **LDR Sensor**          | Light-dependent resistor                 | 1        | Secure broker telemetry source   |
| **PIR Sensor**          | Motion detector                          | 1        | Insecure broker telemetry source |
| **Jumper Wires**        | Male-to-male, male-to-female             | 20       | Sensor connections               |
| **Breadboard**          | 830 tie-points                           | 1        | Prototyping circuit              |
| **USB Cable**           | Micro-USB or USB-C for ESP32             | 1        | ESP32 programming and power      |

**Hardware Connection Diagram:**

```
ESP32 DevKit
│
├── GPIO 4 ──────► DHT11 (Data)
├── GPIO 34 ─────► LDR (Analog Input)
├── GPIO 27 ─────► PIR (Digital Output)
├── 3.3V ────────► Sensor VCC
└── GND ─────────► Sensor GND
```

**Justification:**
The hardware requirements were kept minimal to ensure accessibility for academic and small-organization deployments. The ESP32 and sensors provide realistic IoT device simulation while remaining budget-friendly. The development machine specifications represent typical laptop configurations available to students and researchers.

### 3.5.2 Software Requirements

| Category                     | Software                       | Version | Purpose                      |
| ---------------------------- | ------------------------------ | ------- | ---------------------------- |
| **Operating System**         | Windows 10/11 or Ubuntu 20.04+ | Latest  | Development environment      |
| **Backend Language**         | Python                         | 3.9+    | Scanning engine              |
| **Web Framework (Backend)**  | Flask                          | 2.3+    | API server                   |
| **Web Framework (Frontend)** | Laravel                        | 10.x    | Dashboard interface          |
| **Database**                 | MySQL                          | 8.0+    | Scan results storage         |
| **MQTT Library**             | paho-mqtt                      | 1.6+    | MQTT protocol implementation |
| **Containerization**         | Docker                         | 20.10+  | Broker deployment            |
| **MQTT Broker**              | Eclipse Mosquitto              | 2.0+    | Testbed brokers              |
| **IDE**                      | VS Code                        | Latest  | Code editing                 |
| **Version Control**          | Git                            | 2.30+   | Source control               |
| **Package Manager (Python)** | pip                            | Latest  | Python dependencies          |
| **Package Manager (PHP)**    | Composer                       | 2.x     | Laravel dependencies         |
| **Package Manager (Node)**   | npm                            | 8.x+    | Frontend assets              |

**Justification:**
All software components were selected based on open-source availability, active community support, comprehensive documentation, and proven reliability in production environments. The Python-Flask-Laravel stack provides excellent separation between scanning logic and user interface while maintaining development efficiency.

### 3.5.3 Budget and Costing

**Hardware Components:**

| Item                      | Unit Price (MYR) | Quantity | Total (MYR) |
| ------------------------- | ---------------- | -------- | ----------- |
| ESP32 DevKit              | 25.00            | 1        | 25.00       |
| DHT11 Sensor              | 8.00             | 1        | 8.00        |
| PIR Sensor                | 5.00             | 1        | 5.00        |
| LDR Sensor                | 2.00             | 1        | 2.00        |
| Breadboard                | 10.00            | 1        | 10.00       |
| Jumper Wires (Pack of 40) | 8.00             | 1        | 8.00        |
| USB Cable                 | 6.00             | 1        | 6.00        |
| **Hardware Subtotal**     |                  |          | **64.00**   |

**Software Components:**

| Item                  | Cost (MYR) | Notes             |
| --------------------- | ---------- | ----------------- |
| Python                | Free       | Open source       |
| Flask                 | Free       | Open source       |
| Laravel               | Free       | Open source       |
| MySQL                 | Free       | Community edition |
| Docker                | Free       | Community edition |
| VS Code               | Free       | Open source       |
| Git                   | Free       | Open source       |
| paho-mqtt             | Free       | Open source       |
| Eclipse Mosquitto     | Free       | Open source       |
| **Software Subtotal** | **0.00**   | All open source   |

**Miscellaneous:**

| Item                       | Cost (MYR)                                  |
| -------------------------- | ------------------------------------------- |
| Internet connectivity      | Existing infrastructure                     |
| Electricity                | Negligible for development scale            |
| Domain name (optional)     | 50.00/year (not required for local testing) |
| **Miscellaneous Subtotal** | **0.00** (excluding optional domain)        |

**Total Project Budget: MYR 64.00**

**Budget Justification:**
The project was designed with cost-effectiveness as a primary consideration, demonstrating that sophisticated IoT security tools can be developed with minimal financial investment. The entire software stack consists of free and open-source components, eliminating licensing costs. Hardware expenses were limited to affordable IoT development boards and sensors, with total investment under MYR 70. This budget-conscious approach makes the solution highly accessible for educational institutions, research laboratories, and small organizations with limited funding, while still delivering production-quality functionality.

**Cost Comparison:**
Commercial IoT security scanning solutions typically cost thousands of Malaysian Ringgit annually in licensing fees. This project demonstrates a viable alternative at less than 1% of commercial solution costs, making MQTT security assessment accessible to resource-constrained organizations.

---

## 3.6 Project Planning

### 3.6.1 Gantt Chart (FYP1 - 14 Weeks)

| Week  | Task                                        | Deliverable                     | Status       |
| ----- | ------------------------------------------- | ------------------------------- | ------------ |
| 1-2   | Literature Review & Requirements Definition | Project Proposal                | ✅ Completed |
| 3-5   | System Architecture Design                  | Design Documentation (Diagrams) | ✅ Completed |
| 6-8   | CLI Scanner Development                     | Working Prototype               | ✅ Completed |
| 9-10  | Testing & Debugging                         | Test Reports                    | ✅ Completed |
| 11-12 | Documentation                               | FYP1 Report                     | ✅ Completed |
| 13-14 | Presentation Preparation                    | FYP1 Defense                    | ✅ Completed |

### 3.6.2 Gantt Chart (FYP2 - 14 Weeks)

| Week  | Phase        | Tasks                                           | Milestone           | Status       |
| ----- | ------------ | ----------------------------------------------- | ------------------- | ------------ |
| 1-2   | Setup        | Flask API + Laravel Setup, Docker Configuration | Environment Ready   | ✅ Completed |
| 3-4   | Backend      | Scanner Engine Refinement, API Endpoints        | Backend Functional  | ✅ Completed |
| 5-6   | Frontend     | Dashboard UI, Authentication, Results Display   | UI Complete         | ✅ Completed |
| 7-8   | Hardware     | Docker Brokers Setup, ESP32 Firmware, Sensors   | Hardware Integrated | ✅ Completed |
| 9     | Integration  | End-to-End Testing, Bug Fixes                   | System Integrated   | ✅ Completed |
| 10-11 | Security     | Rate Limiting, CSRF, Security Testing           | Security Hardened   | ✅ Completed |
| 12-13 | Testing      | Comprehensive Testing, Performance Tuning       | Testing Complete    | ✅ Completed |
| 14    | Finalization | Documentation, Report Writing, Presentation     | Project Completed   | ✅ Completed |

### 3.6.3 Work Breakdown Structure (WBS)

```
MQTT Security Scanner Project
│
├── 1.0 Project Initiation (FYP1 - Week 1-2)
│   ├── 1.1 Literature Review
│   ├── 1.2 Requirements Derivation
│   ├── 1.3 Gantt Chart Creation
│   └── 1.4 WBS Development
│
├── 2.0 Design Phase (FYP1 - Week 3-5)
│   ├── 2.1 System Architecture Diagram
│   ├── 2.2 Flowchart Design
│   ├── 2.3 Activity Diagram
│   ├── 2.4 Sitemap Design
│   └── 2.5 Threat Model Diagram
│
├── 3.0 CLI Prototype (FYP1 - Week 6-8)
│   ├── 3.1 Python Environment Setup
│   ├── 3.2 Scanner Core Development
│   ├── 3.3 CSV Reporting Module
│   └── 3.4 Initial Testing
│
├── 4.0 Web Interface (FYP2 - Week 1-6)
│   ├── 4.1 Flask API Development
│   ├── 4.2 Laravel Dashboard
│   ├── 4.3 Database Schema
│   └── 4.4 Integration Testing
│
├── 5.0 Hardware Integration (FYP2 - Week 7-8)
│   ├── 5.1 Docker Broker Setup
│   ├── 5.2 ESP32 Firmware Development
│   ├── 5.3 Sensor Wiring
│   └── 5.4 Network Configuration
│
├── 6.0 Security Hardening (FYP2 - Week 9-11)
│   ├── 6.1 Authentication Implementation
│   ├── 6.2 Input Validation
│   ├── 6.3 CSRF Protection
│   ├── 6.4 Rate Limiting
│   └── 6.5 Security Testing
│
├── 7.0 Testing and Validation (FYP2 - Week 12-13)
│   ├── 7.1 Functional Testing
│   ├── 7.2 Integration Testing
│   ├── 7.3 Hardware-in-the-Loop Testing
│   └── 7.4 Performance Testing
│
└── 8.0 Documentation and Deployment (FYP2 - Week 14)
    ├── 8.1 Technical Documentation
    ├── 8.2 Report Writing
    ├── 8.3 Presentation Preparation
    └── 8.4 Demo Video Recording
```

### 3.6.4 Risk Management

| Risk ID | Risk Description                            | Probability | Impact | Mitigation Strategy                                                     |
| ------- | ------------------------------------------- | ----------- | ------ | ----------------------------------------------------------------------- |
| R1      | Hardware component failure (ESP32, sensors) | Low         | Medium | Purchased backup components; tested thoroughly before integration       |
| R2      | Docker installation issues on Windows       | Medium      | Medium | Prepared alternative: Native Mosquitto installation                     |
| R3      | Laravel-Flask integration challenges        | Medium      | High   | Developed comprehensive API documentation; implemented error handling   |
| R4      | TLS certificate validation errors           | High        | Medium | Created self-signed certificates; implemented flexible validation       |
| R5      | Requirement changes during FYP2             | Medium      | Low    | Agile methodology accommodated changes through iterative sprints        |
| R6      | Time constraints for FYP2 completion        | Medium      | High   | Detailed Gantt chart; weekly milestone tracking; buffer weeks allocated |
| R7      | ESP32 WiFi connectivity issues              | Medium      | Medium | Tested multiple WiFi configurations; documented troubleshooting steps   |

---

## 3.7 Summary and Conclusion

This chapter has presented a comprehensive methodology for the design, development, and deployment of the MQTT Network Scanning Tool for IoT security assessment. The systematic approach demonstrated how research objectives were translated into actionable development phases, ultimately delivering a functional, cost-effective, and academically rigorous solution.

**Key Methodological Achievements:**

**1. Structured Development Framework:**
The eight-phase development approach (Requirements → Design → CLI Prototype → Web Interface → Hardware Integration → Security Hardening → Testing → Documentation) provided clear structure while maintaining flexibility through Agile principles. Each phase produced tangible deliverables that built incrementally toward the final system.

**2. Justified Model Selection:**
The comparative analysis of five software development models (Waterfall, Agile, Spiral, V-Model, Prototype) established Agile methodology as the optimal choice for this research-oriented project. The justification was supported by specific project requirements including evolving specifications, iterative refinement needs, continuous testing integration, risk management requirements, and academic timeline alignment.

**3. Comprehensive System Design:**
Multiple design artifacts were produced to guide implementation:

- **System Architecture Diagram** clarifying three-tier structure (Scanner Engine, API Layer, User Interface)
- **Flowcharts** detailing scanning algorithms and decision logic
- **Activity Diagrams** illustrating user workflows and concurrent system operations
- **Sitemap** defining complete navigation structure for web dashboard
- **Threat Model** identifying security risks and corresponding mitigations based on STRIDE framework

These design documents facilitated stakeholder communication, guided implementation decisions, and provided reference materials for future maintenance and enhancement.

**4. Cost-Effective Implementation:**
The project demonstrated that sophisticated IoT security tools can be developed with minimal budget (MYR 64.00 total hardware cost, zero software licensing costs) by leveraging open-source technologies and affordable development boards. This accessibility enables educational institutions and resource-constrained organizations to deploy MQTT security assessment capabilities without commercial tool licensing expenses.

**5. Rigorous Quality Assurance:**
Multiple testing phases validated system correctness:

- **FYP1:** CLI prototype validation against local Mosquitto brokers
- **FYP2:** Functional testing across diverse broker configurations
- **FYP2:** Hardware-in-the-loop testing with physical ESP32 publishers
- **FYP2:** Security testing validating authentication, rate limiting, and CSRF protection
- **FYP2:** Integration testing confirming Laravel-Flask-Scanner-Broker communication chain

**6. Proactive Risk Management:**
Seven identified risks (hardware failures, Docker installation challenges, integration difficulties, TLS validation errors, requirement changes, time constraints, WiFi connectivity issues) were mitigated through contingency planning, iterative development allowing adaptation, comprehensive documentation, and buffer time allocation in project schedule.

**7. Academic Rigor:**
The methodology adheres to software engineering best practices including:

- Formal requirements specification (functional and non-functional)
- Multiple design modeling techniques (UML, flowcharts, architecture diagrams)
- Version control with Git/GitHub
- Comprehensive documentation at each phase
- Structured project planning with Gantt charts and Work Breakdown Structure
- Threat modeling and security-by-design principles

**Alignment with Research Objectives:**

The methodology directly addresses the project objectives defined in Chapter 1:

- **Objective 1 (Literature Review):** Phase 1 requirements analysis incorporated comprehensive literature review findings to identify gaps in existing MQTT scanning tools
- **Objective 2 (Tool Development):** Phases 3-6 implemented modular scanning system with CLI and web interfaces
- **Objective 3 (Testbed Evaluation):** Phase 5 hardware integration created controlled testing environment with Docker brokers and ESP32 publishers
- **Objective 4 (Security Assessment):** Phase 6 security hardening validated tool's own security posture
- **Objective 5 (Documentation):** Phase 8 produced comprehensive technical and academic documentation

**Transition to Implementation:**

The methodological framework established in this chapter provides the foundation for Chapter 4 (Implementation), which will detail:

- Actual source code architecture and key algorithms
- Database schema design and relationships
- API endpoint specifications and request/response formats
- Hardware wiring diagrams and ESP32 firmware logic
- User interface design and interaction flows

Chapter 5 (Testing and Results) will then present empirical evidence of the system's effectiveness through:

- Functional test results demonstrating detection of all MQTT vulnerability categories
- Performance metrics (scan completion times, database query performance)
- Security test outcomes validating authentication and rate limiting
- Comparative analysis against identified requirements

**Replicability and Future Research:**

This methodology provides a replicable framework for future IoT security research projects. The systematic approach to tool development, testbed creation, and security validation can be adapted for other IoT protocols (CoAP, AMQP, DDS) or extended for cloud-based deployment, real-time dashboard enhancements, machine learning-based anomaly detection, or integration with existing Security Information and Event Management (SIEM) systems.

**Final Remarks:**

The Agile-inspired methodology proved highly effective for managing the complexity of developing a production-quality security tool within academic constraints. The structured phases ensured comprehensive coverage of all software engineering activities while maintaining flexibility to incorporate feedback and address emergent challenges. The result is a robust, well-documented, and academically rigorous IoT security assessment tool that contributes to the broader field of MQTT security research and provides practical value for organizations deploying MQTT-based IoT systems.

The systematic approach documented in this chapter ensures that the project outcomes are not merely functional software artifacts, but represent a disciplined application of software engineering methodology combined with domain-specific security expertise. This methodological rigor strengthens the academic contribution of the research and increases the practical applicability of the developed tool.

---

**Chapter 3 Summary Statistics:**

- **Word Count:** Approximately 6,200 words
- **Development Phases:** 8 distinct phases across 28 weeks
- **Design Artifacts:** 5 diagram types (Architecture, Flowchart, Activity, Sitemap, Threat Model)
- **Tables:** 10 (Requirements, Hardware specs, Software specs, Budget, FYP1 Gantt, FYP2 Gantt, WBS, Risk matrix)
- **Budget:** MYR 64.00 (hardware only, all software open-source)
- **Timeline:** 14 weeks FYP1 + 14 weeks FYP2 = 28 weeks total

---
