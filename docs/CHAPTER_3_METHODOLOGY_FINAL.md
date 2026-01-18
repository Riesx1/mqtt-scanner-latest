# CHAPTER 3: METHODOLOGY

## 3.1 Introduction

This chapter describes the systematic methodology employed to design, develop, and validate an MQTT network security scanning tool for Internet of Things (IoT) environments. The research addressed the need for protocol-aware assessment tools capable of detecting common MQTT broker misconfigurations including anonymous access, absence of Transport Layer Security (TLS) encryption, and inadequate authentication enforcement. The development process spanned two academic semesters, progressing from FYP1's command-line interface prototype to FYP2's production-grade web-based platform integrating Laravel dashboard, Flask API orchestration layer, and Python scanning engine. The methodology was structured around an Agile-inspired iterative approach that accommodated evolving requirements discovered through testing and supervisor feedback while maintaining rigorous documentation standards expected in academic research. This chapter presents the evaluation of software development models, justification for the selected approach, detailed activities and deliverables from each development phase, system requirements specification, budget analysis, and project planning schedules that guided the successful implementation documented in Chapter 4 and validated through testing outcomes presented in Chapter 5.

## 3.2 Available Software Development Models

Five established software development models were evaluated against project-specific criteria including academic timeline constraints, requirement volatility expectations, resource availability, and the need for iterative refinement based on testing feedback. The evaluation considered the Waterfall model, Agile methodology, Spiral model, V-Model, and Prototype model, assessing each against suitability for IoT security tool development within an academic research context.

### 3.2.1 Waterfall Model

The Waterfall model prescribes linear sequential progression through distinct phases including requirements analysis, system design, implementation, testing, deployment, and maintenance, with minimal provision for revisiting completed phases. While its structured nature provides clear milestone definitions suitable for projects with stable and well-understood requirements, the model's rigidity proved incompatible with this research project's exploratory nature where requirement modifications based on supervisor feedback and testing discoveries were anticipated.

### 3.2.2 Agile Methodology

Agile advocates iterative and incremental development through short timeboxed cycles, emphasizing adaptive planning, continuous stakeholder collaboration, and regular delivery of functional increments. The methodology's inherent flexibility enables accommodation of evolving requirements and incorporation of lessons learned across iterations. Agile's emphasis on working software over comprehensive documentation aligned well with the project's objectives of delivering a functional prototype within academic constraints while maintaining sufficient documentation for academic evaluation.

### 3.2.3 Spiral Model

The Spiral model integrates iterative development with explicit risk management, requiring formal risk analysis during each iteration cycle. While valuable for large-scale commercial projects requiring extensive risk documentation, the overhead associated with formal risk analysis activities was deemed excessive for a two-semester academic project where time resources were constrained and risk management could be handled informally through regular supervisor consultations and iterative testing.

### 3.2.4 V-Model

The V-Model extends Waterfall principles by mandating parallel development and testing activities, where each development phase has a corresponding validation phase. The model's emphasis on rigorous testing procedures suits projects requiring high reliability certification. However, its predetermined test specification approach conflicted with this project's research orientation where testing outcomes informed design decisions rather than validating pre-existing specifications.

### 3.2.5 Prototype Model

The Prototype model advocates building initial working prototypes to clarify requirements through stakeholder feedback before committing to full-scale development. While prototyping activities were incorporated during FYP1 to validate core scanning concepts, a pure Prototype model approach lacked the structural framework necessary for FYP2's production-grade implementation encompassing web dashboard integration, database persistence, and hardware testbed configuration.

**Table 3.1: Software Development Model Comparison Matrix**

| Criterion              | Waterfall                        | Agile                                | Spiral                               | V-Model                          | Prototype                      |
| ---------------------- | -------------------------------- | ------------------------------------ | ------------------------------------ | -------------------------------- | ------------------------------ |
| Flexibility            | Low - sequential                 | High - iterative                     | High - risk-driven                   | Low - predetermined              | Moderate - prototype-focused   |
| Documentation          | Heavy - formal stages            | Moderate - working software priority | Heavy - risk analysis                | Heavy - parallel validation      | Light - minimal formal         |
| Testing Approach       | Late - post-implementation       | Continuous - throughout              | Iterative - after cycles             | Structured - phase-specific      | Prototype validation only      |
| Academic Suitability   | Unsuitable - requirement changes | Selected - adaptive to feedback      | Unsuitable - excessive overhead      | Unsuitable - predetermined tests | Partially suitable - FYP1 only |
| Timeline Compatibility | Poor - 28 weeks insufficient     | Excellent - timeboxed iterations     | Poor - formal documentation overhead | Moderate - parallel activities   | Good - rapid prototyping       |

## 3.3 Selected Development Model and Justification

Based on the comparative analysis presented in Table 3.1, an Agile-inspired iterative methodology was selected as the foundational development approach for this research project. The selection decision was driven by three primary justification factors that aligned Agile principles with project-specific requirements and constraints. First, requirement evolution accommodation was essential as initial FYP1 requirements specified command-line interface scanning with CSV reporting, which evolved through testing to encompass web-based dashboard integration, database persistence for scan history, RESTful API architecture for modularity, and hardware testbed validation using Docker-containerized and physical MQTT brokers. Second, iterative refinement enabled systematic progression from FYP1's CLI prototype through FYP2's web platform with incremental capability additions validated at each stage through testing and supervisor consultations. Third, academic timeline compatibility was achieved through timeboxed iteration structure that aligned naturally with FYP1's 14-week semester focused on requirements, design, and CLI prototype development, followed by FYP2's 14-week semester structured into iterations for web platform development, hardware integration, security validation, and comprehensive testing.

**Table 3.2: Development Iteration Mapping Across FYP1 and FYP2**

| Phase                       | Timeline                | Primary Objectives                                                                                  | Deliverables                                                                                       | Validation Method                                           |
| --------------------------- | ----------------------- | --------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------- | ----------------------------------------------------------- |
| FYP1 - Initial Prototype    | Weeks 1-14 (Semester 1) | Requirements definition, system design, CLI scanner development, CSV reporting                      | Functional CLI scanner, CSV output format, design documentation, FYP1 report                       | Local Mosquitto broker testing, CLI execution validation    |
| FYP2 - Web Platform         | Weeks 1-5 (Semester 2)  | Laravel dashboard development, Flask API implementation, MySQL schema design, authentication system | Laravel web UI, Flask REST API, database migrations, user authentication                           | Browser-based testing, API endpoint validation              |
| FYP2 - Hardware Integration | Weeks 6-9               | Docker broker deployment, ESP32 firmware development, sensor integration, real broker testing       | Docker Compose configuration, ESP32 telemetry publisher, validated scanning against 192.168.100.57 | Hardware-in-the-loop testing, live MQTT traffic observation |
| FYP2 - Validation           | Weeks 10-14             | Security controls implementation, comprehensive functional testing, documentation                   | Production-ready system, test reports, FYP2 final report                                           | End-to-end functional validation                            |

## 3.4 Development Activities and Deliverables by Phase

### 3.4.1 Requirements and Planning (FYP1)

The requirements analysis process commenced with comprehensive literature review findings identifying functional gaps in existing MQTT security tools. Requirements were categorized into functional specifications encompassing network scanning, broker detection, TLS inspection, authentication testing, topic observation, and vulnerability classification, alongside non-functional constraints including scan completion time targets and cross-platform compatibility. Stakeholder consultations with the project supervisor clarified expectations for deliverables and established evaluation criteria for both FYP1 and FYP2 milestones. The planning activities produced a requirements specification document detailing functional and non-functional requirements, use case definitions for security analyst interactions, and preliminary project schedules allocating time resources across requirements, design, implementation, testing, and documentation activities.

### 3.4.2 System Design (FYP1, Updated to FYP2 Prototype)

The system architecture was designed following a three-tier separation-of-concerns pattern to ensure that frontend modifications would not impact backend scanning logic and that new scanning modules could be integrated without web interface changes. As shown in Figure 3.1 (System Architecture of the MQTT Network Scanning Tool, archi.jpg), the architecture comprises Tier 1 (Scanning Engine) implemented in Python utilizing the paho-mqtt library for protocol interactions, Tier 2 (API Layer) providing RESTful endpoints via Flask framework with X-API-KEY authentication and rate limiting, and Tier 3 (Presentation Layer) implemented using Laravel framework with user authentication, MySQL database persistence for scan history, and result visualization capabilities. The three-tier design enables independent testing of each component and facilitates future distributed deployment configurations where the web dashboard and scanning engine may reside on separate network hosts.

As shown in Figure 3.2 (Use Case Diagram for MQTT Vulnerability Scanner Dashboard, use case.jpg), the system supports three primary actor interactions: the security analyst initiating network scans by submitting target IP addresses or CIDR notation, viewing scan results retrieved from database persistence with severity color-coding and filtering capabilities, and exporting reports in CSV format for offline analysis. The use case model informed the Laravel controller design and database schema specifications.

### 3.4.3 CLI Prototype Implementation (FYP1)

A Python virtual environment was established using the venv module to isolate project dependencies from system Python installation, with core dependencies including paho-mqtt version 1.6.1 for MQTT protocol communication and standard library modules for socket-based TCP scanning, SSL certificate inspection, CSV report generation, and IP address parsing. The scanner.py module implemented orchestration logic exposing a scan_target function accepting either single IP addresses or CIDR notation, performing TCP port scanning on ports 1883 and 8883 with three-second timeout values, and invoking MQTT probing logic for discovered open ports. The mqtt_probe.py module implemented connection testing logic that first attempted anonymous MQTT CONNECT operations without credentials, then authenticated CONNECT operations with test credentials if the anonymous attempt failed, and for TLS-enabled brokers on port 8883, captured X.509 certificate details including subject, issuer, and validity period. As shown in Figure 3.5 (Flowchart of Protocol-Aware MQTT Scanning Logic, Flowchart Diagram.drawio.png), the classification logic implemented in categorizer.py evaluated MQTT response codes where code 0 (connection accepted) without credentials classified as "Open Broker" with Critical severity, code 5 (not authorized) classified as "Authentication Required" with Medium severity, TLS handshake failure classified as "TLS Required" with Medium severity, and connection refused classified as "Unreachable" with Informational severity. The flowchart documents the algorithmic decision flow from target input validation through TCP port scanning, anonymous and authenticated connection attempts, error type differentiation, and ultimate classification into security categories.

### 3.4.4 Web Integration (FYP2 Laravel + Flask)

The Flask API layer was developed in app.py exposing POST /api/scan endpoint accepting JSON payload with target parameter, validating X-API-KEY from Authorization header, sanitizing target input using regular expressions, invoking the scanner module, and returning JSON array of broker findings. As shown in Figure 3.3 (Sequence Diagram for Scan Execution and Result Retrieval, sequence.png), the scan request workflow begins with the authenticated user submitting a target IP via Laravel dashboard, Laravel constructing HTTP POST request with API key header directed to Flask's /api/scan endpoint, Flask validating credentials and input format before invoking Python scanner's scan_target function, the scanner performing TCP port discovery and MQTT connection probes, Flask serializing results to JSON and returning HTTP 200 response, and Laravel parsing JSON to insert records into the mqtt_scan_results database table before rendering the results table in the dashboard. This sequence diagram illustrates the clear interface contracts between architectural tiers and the synchronous nature of scan execution. The Laravel dashboard implemented using Laravel 10 framework with Breeze authentication scaffolding provided login, registration, and session management functionality. Database migrations created tables for users, mqtt_scan_history storing scan metadata (user_id, target, scan_start_time, scan_completion_time, broker_count_found), and mqtt_scan_results storing individual broker findings (scan_id, ip_address, port, outcome, severity, tls_available, auth_required, certificate_subject, captured_topics). The MqttScannerController implemented index action displaying dashboard with scan initiation form, executeScan action validating CSRF token and target input before sending HTTP POST to Flask API, and exportCsv action generating CSV downloads from database records. Blade templates implemented responsive design using Tailwind CSS framework with results table featuring sortable columns and severity color-coding.

### 3.4.5 Persistence (CSV + Database Scan History)

Dual persistence mechanisms were implemented to serve distinct purposes: CSV file generation provided portable evidence artifacts for security assessment documentation, while MySQL database storage enabled scan history retrieval for dashboard refresh operations and longitudinal analysis of scan results over time. The Python scanner module generated structured CSV output with columns for IP Address, Port, Outcome Category, TLS Available, Authentication Required, Certificate Subject, Certificate Issuer, Captured Topics, and Timestamp using UTF-8 encoding to properly handle special characters in certificate fields. The Laravel application persisted scan results to MySQL database using Eloquent ORM to automatically escape user inputs and prevent SQL injection vulnerabilities. Database queries employed eager loading techniques to minimize query overhead when displaying scan history, and the exportCsv controller action provided on-demand CSV generation from database records for users preferring spreadsheet analysis workflows.

### 3.4.6 Hardware Validation (ESP32 Publishing)

A hardware testbed infrastructure was deployed to validate scanner operation against realistic IoT traffic patterns and diverse broker security configurations. Docker Compose configuration defined two Eclipse Mosquitto broker containers: an insecure broker mapping port 1883 with anonymous access enabled and TLS disabled, and a secure broker mapping port 8883 with username/password authentication required and TLS encryption enabled using self-signed certificates generated via OpenSSL. ESP32 microcontroller firmware developed using Arduino framework integrated three sensors: DHT11 temperature and humidity sensor connected to GPIO 4, LDR photoresistor for light intensity measurement connected to GPIO 34 with voltage divider circuit, and PIR motion sensor connected to GPIO 27. The firmware established dual MQTT client instances using PubSubClient library, with one client publishing DHT11 and LDR readings to the secure broker over TLS-encrypted connection with hardcoded credentials, and another client publishing PIR motion events to the insecure broker in plaintext. A commercial MQTT broker device accessible at IP address 192.168.100.57 was incorporated to validate scanner operation against network-accessible brokers beyond localhost simulation, representing typical insecure IoT deployments with port 1883 open and authentication disabled. Validation testing confirmed correct detection across all four broker endpoints: localhost port 1883 detected as "Open Broker - Critical", localhost port 8883 detected as "Authentication Required - Medium" with TLS certificate details captured, 192.168.100.57 port 1883 detected as "Open Broker - Critical", and ESP32 publishing verified through topic observation capturing sensor/temperature, sensor/humidity, sensor/light, and sensor/motion topics during subscription tests.

### 3.4.7 Controls and Testing Used

As shown in Figure 3.4 (Activity Diagram for User Scan Workflow, activity.jpg), the user workflow incorporates security controls at multiple interaction points: authentication enforcement through Laravel's auth middleware applied to all dashboard routes requiring valid session credentials, CSRF protection implemented via Laravel's built-in token validation on state-changing POST requests, input validation using Laravel validation rules with regular expression patterns for target field (required|ip|regex:/^[\d.\/]+$/), and server-side sanitization preventing malicious input injection. The activity diagram illustrates parallel swim lanes for user authentication and dashboard navigation, system session validation and CSRF token verification, Flask API credential validation and scanner invocation, and scanner execution of concurrent port scanning operations. Comprehensive functional testing validated all scan outcome classifications across representative broker configurations including network unreachable targets, connection refused scenarios, connection timeout cases, anonymous access success on Docker insecure broker, authentication requirements on Docker secure broker with incorrect credentials, authentication success with correct credentials, and TLS certificate validation extracting details from port 8883 connections. Each test case was executed multiple times to verify consistency, with results documented in test reports confirming correct behavior across diverse network conditions and broker security postures.

## 3.5 System Requirements

### 3.5.1 Hardware Requirements

The hardware infrastructure comprised development equipment and IoT testbed components necessary for implementing and validating the MQTT security scanner.

**Table 3.10A: Development Hardware Requirements**

| Component          | Specification                                    | Quantity | Purpose                                                                              |
| ------------------ | ------------------------------------------------ | -------- | ------------------------------------------------------------------------------------ |
| Development Laptop | Intel Core i5 or AMD Ryzen 5, 8GB RAM, 256GB SSD | 1        | Host Laravel server, Flask API, MySQL database, Docker containers, development tools |

**Table 3.10B: Deployment Hardware Requirements (IoT Testbed)**

| Component         | Specification                        | Quantity | Purpose                                                   |
| ----------------- | ------------------------------------ | -------- | --------------------------------------------------------- |
| ESP32 DevKit      | ESP32-WROOM-32, WiFi, Bluetooth      | 1        | MQTT telemetry publisher for realistic traffic generation |
| DHT11 Sensor      | Temperature/humidity, digital output | 1        | Generate temperature and humidity telemetry data          |
| PIR Motion Sensor | HC-SR501, 7m detection range         | 1        | Generate motion detection event messages                  |
| LDR Photoresistor | Light-dependent resistor, analog     | 1        | Generate light intensity measurements                     |
| Breadboard        | 400 tie-points                       | 1        | Sensor circuit prototyping platform                       |
| Jumper Wires      | Male-to-male, 20cm length            | 20       | Sensor-to-ESP32 electrical connections                    |
| Resistor          | 10kΩ, 1/4W carbon film               | 1        | LDR voltage divider circuit component                     |

### 3.5.2 Software Requirements

**Table 3.11: Software Requirements and Justification**

| Category         | Software          | Version | License              | Purpose                               | Selection Rationale                               |
| ---------------- | ----------------- | ------- | -------------------- | ------------------------------------- | ------------------------------------------------- |
| Operating System | Windows 10/11     | Latest  | Commercial           | Development environment host          | Pre-existing on development machine               |
| Backend Language | Python            | 3.9+    | Open Source (PSF)    | Scanner engine implementation         | Extensive library ecosystem for network protocols |
| MQTT Library     | paho-mqtt         | 1.6.1+  | Open Source (EPL)    | MQTT protocol communication           | Official Eclipse Foundation library               |
| API Framework    | Flask             | 2.3+    | Open Source (BSD)    | RESTful API orchestration layer       | Lightweight with minimal overhead                 |
| Web Framework    | Laravel           | 10.x    | Open Source (MIT)    | Dashboard and persistence layer       | Built-in authentication and ORM                   |
| Database         | MySQL             | 8.0+    | Open Source (GPL)    | Scan history and results storage      | Industry-standard relational database             |
| Containerization | Docker Engine     | 20.10+  | Open Source (Apache) | Broker testbed deployment             | Simplified multi-broker configuration             |
| MQTT Broker      | Eclipse Mosquitto | 2.0+    | Open Source (EPL)    | Target brokers for validation testing | Widely deployed in production IoT                 |
| IDE              | VS Code           | Latest  | Open Source (MIT)    | Code editing and debugging            | Multi-language support and extensions             |
| Version Control  | Git + GitHub      | 2.30+   | Open Source (GPL)    | Source code management                | Industry-standard version control                 |

## 3.6 Budget and Costing

The project budget was constrained to essential hardware components for IoT testbed deployment, with all software components selected from open-source options to ensure zero licensing costs. Development laptop hardware was pre-existing and therefore excluded from budget calculations.

**Table 3.12: Project Budget (Purchased Items Only)**

| Item                    | Specification            | Quantity | Unit Price (MYR) | Total (MYR) | Notes                                 |
| ----------------------- | ------------------------ | -------- | ---------------- | ----------- | ------------------------------------- |
| ESP32 DevKit            | ESP32-WROOM-32           | 1        | 25.00            | 25.00       | MQTT publisher                        |
| DHT11 Sensor            | Temperature/humidity     | 1        | 8.00             | 8.00        | Telemetry generation                  |
| PIR Motion Sensor       | HC-SR501                 | 1        | 5.00             | 5.00        | Motion detection                      |
| LDR Photoresistor       | Light-dependent resistor | 1        | 2.00             | 2.00        | Light sensing                         |
| Breadboard              | 400 tie-points           | 1        | 10.00            | 10.00       | Prototyping                           |
| Jumper Wires            | Male-to-male, 20cm       | 20       | 0.40             | 8.00        | Wiring                                |
| Resistor                | 10kΩ, 1/4W               | 1        | 0.50             | 0.50        | Voltage divider                       |
| USB Cable               | Micro-USB, 1m            | 1        | 6.00             | 6.00        | Optional (can reuse existing)         |
| **Total Hardware Cost** |                          |          |                  | **64.50**   | Software cost: MYR 0.00 (open source) |

## 3.7 Project Planning

### 3.7.1 FYP1 Schedule

**Table 3.13: FYP1 Development Schedule (14 Weeks)**

| Week  | Primary Activities                                                                  | Key Deliverables                                        | Status    |
| ----- | ----------------------------------------------------------------------------------- | ------------------------------------------------------- | --------- |
| 1-2   | Literature review completion, requirements elicitation, project proposal refinement | Requirements specification, project proposal            | Completed |
| 3-4   | System architecture design, use case modeling, sequence diagram development         | Architecture diagram, use case diagram, design document | Completed |
| 5     | Flowchart development, detailed design specifications                               | Flowchart, module specifications                        | Completed |
| 6-7   | Python environment setup, scanner core module implementation                        | Functional scanner with port scanning                   | Completed |
| 8-9   | MQTT probing implementation, TLS certificate inspection, classification logic       | Complete CLI scanner with categorization                | Completed |
| 10    | CSV reporting implementation, CLI interface development                             | CSV output generation, command-line interface           | Completed |
| 11    | Testing against local Mosquitto broker, bug fixes                                   | Test results, validated CLI prototype                   | Completed |
| 12-13 | FYP1 report writing, presentation preparation                                       | FYP1 final report, presentation slides                  | Completed |
| 14    | FYP1 presentation and demonstration                                                 | Completed FYP1 defense                                  | Completed |

### 3.7.2 FYP2 Schedule

**Table 3.14: FYP2 Development Schedule (14 Weeks)**

| Week  | Primary Activities                                                                    | Key Deliverables                                        | Status    |
| ----- | ------------------------------------------------------------------------------------- | ------------------------------------------------------- | --------- |
| 1-2   | Flask API development, endpoint implementation, API key authentication                | Functional Flask API with scan endpoint                 | Completed |
| 3-4   | Laravel installation, database schema design, authentication scaffolding              | Laravel project with database migrations                | Completed |
| 5     | Dashboard UI development, result visualization, CSV export features                   | Complete web dashboard interface                        | Completed |
| 6-7   | Docker Compose configuration, Mosquitto broker deployment, TLS certificate generation | Operational Docker testbed with insecure/secure brokers | Completed |
| 8-9   | ESP32 firmware development, sensor integration, telemetry publishing                  | ESP32 publisher generating realistic MQTT traffic       | Completed |
| 10    | Security controls implementation (CSRF, input validation), rate limiting              | Security-hardened application                           | Completed |
| 11-12 | Comprehensive functional testing, security validation, performance measurement        | Test reports with documented test cases                 | Completed |
| 13    | Documentation completion, deployment guide, user manual                               | Technical documentation                                 | Completed |
| 14    | FYP2 report finalization, presentation preparation, demonstration                     | FYP2 final report, presentation                         | Completed |

### 3.7.3 Work Breakdown Structure

```yaml
MQTT_Security_Scanner:
    FYP1_Phase:
        Requirements_Analysis:
            - Literature_Review
            - Stakeholder_Consultation
            - Requirements_Documentation
        System_Design:
            - Architecture_Specification
            - Use_Case_Modeling
            - Sequence_Diagram_Development
            - Flowchart_Design
        CLI_Prototype_Implementation:
            - Python_Environment_Setup
            - Scanner_Module_Development
            - MQTT_Probing_Logic
            - TLS_Certificate_Inspection
            - Classification_Algorithm
            - CSV_Reporting
        Testing_and_Validation:
            - Local_Broker_Testing
            - Bug_Fixing
            - FYP1_Documentation
    FYP2_Phase:
        Web_Platform_Development:
            - Flask_API_Implementation
            - Laravel_Dashboard_Development
            - Database_Schema_Design
            - Authentication_System
            - Result_Visualization
        Hardware_Testbed_Integration:
            - Docker_Broker_Deployment
            - TLS_Certificate_Generation
            - ESP32_Firmware_Development
            - Sensor_Integration_DHT11_LDR_PIR
            - Physical_Broker_Configuration
        Security_and_Controls:
            - CSRF_Protection
            - Input_Validation
            - API_Authentication
            - Rate_Limiting
        Comprehensive_Testing:
            - Functional_Testing
            - Security_Validation
            - Performance_Measurement
            - Documentation_Completion
```

## 3.8 Summary

This chapter presented the systematic methodology employed in developing and validating the MQTT network security scanner for IoT environments. The development model evaluation process examined five established methodologies, with Agile-inspired iterative approach selected based on requirement evolution accommodation, iterative refinement capability, and academic timeline compatibility. The development activities progressed through FYP1's CLI prototype implementation with CSV reporting to FYP2's production-grade web platform integrating Laravel dashboard, Flask API layer, Python scanning engine, dual persistence mechanisms (CSV and MySQL database), and hardware testbed validation using Docker-containerized Mosquitto brokers and ESP32 microcontroller publishing realistic sensor telemetry. System requirements specified minimal development hardware (existing laptop) and deployment hardware totaling MYR 64.50 investment for ESP32 and sensors, with entirely open-source software stack eliminating licensing costs. Project planning structured activities across two 14-week semesters with clear milestone definitions documented in Gantt charts and work breakdown structure. This methodological foundation enabled the technical implementation detailed in Chapter 4 and empirical testing outcomes presented in Chapter 5, demonstrating effective detection of MQTT security misconfigurations including anonymous broker access, absent TLS encryption, and inadequate authentication enforcement across diverse broker configurations validated through Docker testbed and physical broker device at 192.168.100.57.
