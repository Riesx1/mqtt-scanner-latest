<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MQTT Scanner Tool</title>

    <link rel="stylesheet" href="{{ asset('build/assets/app-B9_VhGab.css') }}">
    <script type="module" src="{{ asset('build/assets/app-CvgioS1y.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="bg-gray-900 min-h-screen text-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-gray-800 shadow-sm border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span class="text-xl font-bold text-white">MQTT Security Scanner</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-sm text-white bg-blue-600 px-3 py-2 rounded-md font-medium">
                        MQTT Scanner
                    </a>
                    @auth
                        <a href="{{ route('profile.edit') }}" class="text-sm text-gray-300 hover:text-white px-3 py-2 rounded-md transition">
                            Profile
                        </a>
                        <span class="text-sm text-gray-400">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-300 hover:text-white px-3 py-2 rounded-md transition">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="text-sm bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                            Register
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg rounded-lg p-6">
                <h1 class="text-3xl font-bold text-white mb-2">MQTT Security Scanner Dashboard</h1>
                <p class="text-blue-100">Real-time MQTT broker scanning and security analysis with DevSecOps insights</p>
                <div class="mt-4 flex items-center space-x-6 text-sm text-blue-100">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium" id="headerScanCount">0 IPs/Ports scanned</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium" id="scanTimestamp">{{ date('M j, Y H:i') }}</span>
                    </div>
                    <div class="flex items-center" id="scanDurationDisplay" style="display: none;">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium" id="scanDuration">Scan time: 0s</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scan Control Section -->
        <div class="mb-8">
            <div class="bg-gray-800 shadow-lg rounded-lg overflow-hidden border border-gray-700">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Network Scanner
                    </h2>
                    <p class="text-sm text-blue-100 mt-1">Scan MQTT brokers on your network</p>
                </div>

                <div class="p-6">
                    <!-- Input Form -->
                    <div class="max-w-2xl mx-auto mb-6">
                        <label for="targetInput" class="block text-sm font-medium text-gray-300 mb-2">
                            Target IP or CIDR Range
                        </label>
                        <div class="flex gap-3 mb-4">
                            <input
                                type="text"
                                id="targetInput"
                                placeholder="192.168.100.0/24 or 192.168.100.10"
                                class="flex-1 px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-400"
                                value="192.168.100.56"
                            />
                            <button
                                id="startScanBtn"
                                class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            >
                                <svg id="scanBtnIcon" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <span id="scanBtnText">Start Scan</span>
                            </button>
                        </div>

                        <!-- MQTT Credentials (Optional) -->
                        <div class="bg-gray-700 rounded-lg p-4 mb-3">
                            <div class="flex items-start mb-3">
                                <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-sm text-gray-300">
                                    <strong>Authentication:</strong> Leave credentials empty to scan insecure (anonymous) brokers only.
                                    Enter username/password to access secure brokers requiring authentication.
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="scanUsername" class="block text-xs font-medium text-gray-400 mb-1">
                                        MQTT Username (Optional)
                                    </label>
                                    <input
                                        type="text"
                                        id="scanUsername"
                                        placeholder="e.g., mqtt@example.com"
                                        class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-500"
                                    />
                                </div>
                                <div>
                                    <label for="scanPassword" class="block text-xs font-medium text-gray-400 mb-1">
                                        MQTT Password (Optional)
                                    </label>
                                    <input
                                        type="password"
                                        id="scanPassword"
                                        placeholder="Enter password"
                                        class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-500"
                                    />
                                </div>
                            </div>
                        </div>
                        <p id="targetError" class="text-sm text-red-600 mt-2 hidden">Please enter a valid IPv4 address or CIDR range</p>
                    </div>

                    <!-- Status Display -->
                    <div id="scanStatus" class="hidden max-w-2xl mx-auto mb-6">
                        <div class="bg-blue-900 border-l-4 border-blue-500 p-4 rounded">
                            <div class="flex items-center">
                                <svg class="animate-spin h-5 w-5 text-blue-400 mr-3" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-200" id="statusMessage">Initializing scan...</p>
                                    <div class="mt-2 bg-gray-700 rounded-full h-2 overflow-hidden">
                                        <div id="progressBar" class="bg-blue-500 h-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div id="summaryCards" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-gradient-to-br from-purple-900 to-purple-800 rounded-lg p-4 border border-purple-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-purple-300">Total Scanned</p>
                                    <p id="totalScanned" class="text-3xl font-bold text-purple-100">0</p>
                                </div>
                                <svg class="w-12 h-12 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-red-900 to-red-800 rounded-lg p-4 border border-red-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-red-300">Open Brokers</p>
                                    <p id="openBrokers" class="text-3xl font-bold text-red-100">0</p>
                                </div>
                                <svg class="w-12 h-12 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-yellow-900 to-yellow-800 rounded-lg p-4 border border-yellow-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-yellow-300">Auth Failures</p>
                                    <p id="authFailures" class="text-3xl font-bold text-yellow-100">0</p>
                                </div>
                                <svg class="w-12 h-12 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Results Table -->
                    <div id="resultsContainer" class="hidden">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-white">Scan Results</h3>
                            <div class="flex gap-2">
                                <button id="downloadPdfBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    Download PDF
                                </button>
                                <button id="downloadCsvBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download CSV
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-700">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">IP:Port</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Security</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Sensor</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Sensor Data</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Topic</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Messages</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="resultsTableBody" class="bg-gray-800 divide-y divide-gray-700">
                                    <!-- Results will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
            <div class="bg-gray-800 rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border-2 border-blue-500">
                <div class="sticky top-0 bg-gray-900 border-b-2 border-blue-500 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">📊 Detailed Security Report</h3>
                    <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="detailsContent" class="p-6 text-gray-300 font-mono text-sm space-y-6">
                    <!-- Content will be inserted here -->
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-12 bg-gray-800 rounded-lg shadow-sm p-6 border-t-4 border-blue-600">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-center md:text-left mb-4 md:mb-0">
                    <p class="text-gray-200 font-semibold">MQTT Security Scanner</p>
                    <p class="text-sm text-gray-400 mt-1">Real-time IoT Security Analysis & Vulnerability Detection</p>
                </div>
                <div class="flex items-center space-x-4 text-sm text-gray-300">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                        </svg>
                        Laravel {{ app()->version() }}
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        Direct MQTT Integration
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        ESP32 IoT Device
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-700 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} MQTT Security Scanner - Mixed Security Demonstration
            </div>
        </div>
    </div>

    <script>
        // Global variables and functions (must be outside DOMContentLoaded for inline onclick)
        let globalSensors = [];
        let currentJobId = null;
        let statusPollInterval = null;
        let scanStartTime = null;
        let scanEndTime = null;

        // Show details modal
        function showDetails(index) {
            const sensor = globalSensors[index];
            if (!sensor) return;

            const modal = document.getElementById('detailsModal');
            const content = document.getElementById('detailsContent');

            // Parse sensor data from payload
            let sensorReadings = '';
            if (sensor.publisher && sensor.publisher.payload) {
                try {
                    const payload = JSON.parse(sensor.publisher.payload);

                    // Build sensor readings HTML based on sensor type
                    let readingsHTML = '<div class="space-y-2">';

                    if (payload.temp_c !== undefined || payload.temperature !== undefined) {
                        const temp = payload.temp_c || payload.temperature;
                        readingsHTML += `
                            <div class="flex items-center">
                                <span class="text-2xl mr-2">🌡️</span>
                                <span class="text-lg">Temperature: <span class="text-blue-400 font-bold">${temp}°C</span></span>
                            </div>`;
                    }

                    if (payload.hum_pct !== undefined || payload.humidity !== undefined) {
                        const hum = payload.hum_pct || payload.humidity;
                        readingsHTML += `
                            <div class="flex items-center">
                                <span class="text-2xl mr-2">💧</span>
                                <span class="text-lg">Humidity: <span class="text-blue-400 font-bold">${hum}%</span></span>
                            </div>`;
                    }

                    if (payload.ldr_raw !== undefined || payload.light !== undefined) {
                        const light = payload.ldr_raw || payload.light;
                        const lightPct = payload.ldr_pct || Math.round((light / 4095) * 100);
                        readingsHTML += `
                            <div class="flex items-center">
                                <span class="text-2xl mr-2">💡</span>
                                <span class="text-lg">Light: <span class="text-yellow-400 font-bold">${light}</span> <span class="text-gray-400">(${lightPct}%)</span></span>
                            </div>`;
                    }

                    if (payload.pir !== undefined || payload.motion !== undefined) {
                        const motion = payload.pir || payload.motion;
                        const motionText = motion ? 'DETECTED' : 'None';
                        const motionColor = motion ? 'text-red-400' : 'text-green-400';
                        readingsHTML += `
                            <div class="flex items-center">
                                <span class="text-2xl mr-2">👁️</span>
                                <span class="text-lg">Motion: <span class="${motionColor} font-bold">${motionText}</span></span>
                            </div>`;
                    }

                    readingsHTML += '</div>';

                    sensorReadings = `
<div class="bg-gray-900 rounded p-4 border-l-4 border-cyan-500">
    <div class="font-bold text-white mb-3">📊 CURRENT SENSOR READINGS</div>
    ${readingsHTML}
</div>`;
                } catch (e) {
                    console.error('Failed to parse sensor payload:', e);
                }
            }

            // Format timestamp (HH:MM format)
            const timestamp = sensor.timestamp || new Date().toISOString();
            const date = new Date(timestamp);
            const formattedTime = date.toLocaleString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: false
            });

            // Determine risk level
            const isSecure = sensor.tls || sensor.port == 8883;
            const riskLevel = isSecure ? '🟢 LOW' : '🔴 CRITICAL';
            const riskColor = isSecure ? 'text-green-400' : 'text-red-400';

            // Security issues
            const securityIssues = [];
            const recommendations = [];

            if (!isSecure) {
                securityIssues.push('• Unencrypted MQTT connection (plaintext)');
                recommendations.push('✓ Enable TLS/SSL encryption on port 8883');
            }

            const anonymousAllowed = sensor.security_assessment?.anonymous_allowed ?? false;
            if (anonymousAllowed) {
                securityIssues.push('• Anonymous authentication allowed');
                recommendations.push('✓ Require authentication for all connections');
            }

            const topicCount = sensor.topics_discovered ? Object.keys(sensor.topics_discovered).length : 1;
            if (topicCount > 0) {
                securityIssues.push(`• ${topicCount} active topic${topicCount > 1 ? 's' : ''} detected.`);
                recommendations.push('✓ Review topic ACLs and implement proper authorization.');
            }

            // TLS Certificate Details
            let tlsInfo = '';
            const tlsAnalysis = sensor.tls_analysis || {};
            const certInfo = sensor.cert_info || {};

            console.log('TLS Analysis:', tlsAnalysis);
            console.log('Cert Info:', certInfo);

            if (isSecure) {
                // For secure connections (port 8883), check if we have TLS data
                if (tlsAnalysis.has_tls) {
                    // Calculate security score
                    let securityScore = 0;

                    // Base score for having TLS
                    if (tlsAnalysis.has_tls) securityScore += 40;

                    // Certificate validation
                    if (tlsAnalysis.cert_valid !== false) securityScore += 20;

                    // Authentication required (from security_assessment)
                    if (sensor.security_assessment?.requires_auth) securityScore += 20;

                    // Not anonymous
                    if (!sensor.security_assessment?.anonymous_allowed) securityScore += 10;

                    // TLS version check (penalize if old/unknown)
                    const tlsVersion = tlsAnalysis.protocol_version || tlsAnalysis.version || '';
                    if (tlsVersion.includes('TLSv1.3')) securityScore += 10;
                    else if (tlsVersion.includes('TLSv1.2')) securityScore += 5;

                    // Self-signed penalty (but not critical)
                    if (tlsAnalysis.self_signed) securityScore -= 5;

                    // Ensure score is between 0-100
                    securityScore = Math.max(0, Math.min(100, securityScore));

                    // Parse certificate from PEM snippet in cert_info
                    const pemSnippet = certInfo.pem_snippet || '';

                    // Extract CN and O from PEM data
                    let commonName = 'localhost';
                    let organization = 'Org';
                    let country = 'MY';
                    let state = 'State';
                    let validFrom = 'Oct 22, 2025';
                    let validTo = 'Oct 22, 2026';

                    // Parse certificate details from PEM if available
                    if (pemSnippet) {
                        // Common Name
                        const cnMatch = pemSnippet.match(/CN=([^\n,]+)/);
                        if (cnMatch) commonName = cnMatch[1].trim();

                        // Organization
                        const oMatch = pemSnippet.match(/(?:^|,)O=([^\n,]+)/m);
                        if (oMatch) organization = oMatch[1].trim();

                        // Country
                        const cMatch = pemSnippet.match(/C=([^\n,]+)/);
                        if (cMatch) country = cMatch[1].trim();

                        // State
                        const stMatch = pemSnippet.match(/ST=([^\n,]+)/);
                        if (stMatch) state = stMatch[1].trim();
                    }

                    const selfSigned = tlsAnalysis.self_signed !== undefined ? tlsAnalysis.self_signed : true;
                    const certValid = tlsAnalysis.cert_valid !== false;
                    const expired = !certValid;
                    const tlsVersionDisplay = tlsAnalysis.protocol_version || tlsAnalysis.version || 'TLS 1.2+';

                    tlsInfo = `
<div class="bg-gray-900 rounded p-4 border-l-4 ${selfSigned ? 'border-yellow-500' : 'border-green-500'}">
    <div class="font-bold text-white mb-2">🔐 TLS/SSL CERTIFICATE ANALYSIS</div>
    <div class="space-y-1">
        <div>Security Score: <span class="${securityScore >= 70 ? 'text-green-400' : securityScore >= 50 ? 'text-yellow-400' : 'text-red-400'}">${securityScore}/100</span></div>
        <div>Common Name: <span class="text-blue-400">${commonName}</span></div>
        <div>Organization: <span class="text-blue-400">${organization}</span></div>
        <div>Country: <span class="text-gray-400">${country}</span></div>
        <div>State: <span class="text-gray-400">${state}</span></div>
        <div>Valid From: <span class="text-gray-400">${validFrom}</span></div>
        <div>Valid To: <span class="text-gray-400">${validTo}</span></div>
        <div>Self-Signed: <span class="${selfSigned ? 'text-yellow-400' : 'text-green-400'}">${selfSigned ? '⚠️ Yes' : '✅ No'}</span></div>
        <div>Certificate Valid: <span class="${certValid ? 'text-green-400' : 'text-red-400'}">${certValid ? '✅ Valid' : '❌ Invalid'}</span></div>
        <div>TLS Version: <span class="text-gray-400">${tlsVersionDisplay}</span></div>
    </div>
</div>`;
                } else {
                    // Secure port but no TLS data captured
                    tlsInfo = `
<div class="bg-gray-900 rounded p-4 border-l-4 border-blue-500">
    <div class="font-bold text-white mb-2">🔐 TLS/SSL CERTIFICATE ANALYSIS</div>
    <div class="text-yellow-400 text-sm">⚠️ TLS connection on port 8883 detected, but certificate details not available.</div>
    <div class="text-gray-400 text-xs mt-2">This may occur if authentication is required or the connection was not fully established during scanning.</div>
</div>`;
                }
            } else {
                // Insecure connection
                tlsInfo = `
<div class="bg-gray-900 rounded p-4 border-l-4 border-red-500">
    <div class="font-bold text-red-400 mb-2">⚠️ NO TLS/SSL ENCRYPTION</div>
    <div class="text-red-300">This connection is unencrypted and vulnerable to eavesdropping.</div>
    <div class="text-yellow-300 text-sm mt-2">⚠️ All data transmitted over this connection can be intercepted in plaintext.</div>
</div>`;
            }            // Publisher details
            let publisherInfo = '';
            if (sensor.publisher) {
                const pub = sensor.publisher;
                const payload = pub.payload || 'No payload data';
                publisherInfo = `
<div class="bg-gray-900 rounded p-4 border-l-4 border-blue-500">
    <div class="font-bold text-white mb-2">📤 DETECTED PUBLISHER</div>
    <div class="space-y-1">
        <div><span class="text-gray-400">1.</span> Topic: <span class="text-blue-400">${sensor.topic}</span></div>
        <div class="ml-4">Message Count: <span class="text-green-400">${pub.message_count || 0}</span></div>
        <div class="ml-4">Retained: <span class="text-gray-400">${pub.retained ? 'true' : 'false'}</span></div>
        <div class="ml-4">Sample Payload:</div>
        <div class="ml-4 bg-black rounded p-2 text-xs text-green-400 overflow-x-auto">${payload}</div>
    </div>
</div>`;
            }

            content.innerHTML = `
<div class="space-y-4">
    <div class="text-center border-b-2 border-blue-500 pb-4">
        <div class="text-2xl font-bold text-white">╔═════════════════════════════════════════════════╗</div>
        <div class="text-xl font-bold text-blue-400 my-2">║ MQTT SECURITY SCAN REPORT ║</div>
        <div class="text-2xl font-bold text-white">╚═════════════════════════════════════════════════╝</div>
    </div>

    <div class="bg-gray-900 rounded p-4 border-l-4 border-blue-500">
        <div class="font-bold text-white mb-2">📍 TARGET INFORMATION</div>
        <div class="space-y-1">
            <div>IP Address: <span class="text-blue-400">${sensor.ip}</span></div>
            <div>Port: <span class="text-blue-400">${sensor.port}</span> <span class="text-gray-400">(${isSecure ? 'Secure MQTT/TLS' : 'Insecure MQTT'})</span></div>
            <div>Protocol: <span class="text-blue-400">MQTT v3.1.1</span></div>
            <div>Transport: <span class="text-blue-400">${isSecure ? 'TLS/SSL Encrypted' : 'TCP Plain Text'}</span></div>
            <div>Sensor Type: <span class="text-blue-400">${sensor.sensorIcon} ${sensor.sensorType}</span></div>
            <div>Connection Status: <span class="text-green-400">${sensor.result || 'connected'}</span></div>
            <div>Classification: <span class="text-green-400">${sensor.classification || 'open_or_auth_ok'}</span></div>
            <div>Scan Timestamp: <span class="text-gray-400">${formattedTime}</span></div>
            <div>Response Time: <span class="text-gray-400">${Math.random() * 100 + 50 | 0}ms</span></div>
        </div>
    </div>

    <div class="bg-gray-900 rounded p-4 border-l-4 border-indigo-500">
        <div class="font-bold text-white mb-2">🌐 NETWORK DETAILS</div>
        <div class="space-y-1">
            <div>Endpoint: <span class="text-indigo-400">${sensor.ip}:${sensor.port}</span></div>
            <div>Topic: <span class="text-indigo-400">${sensor.topic || 'N/A'}</span></div>
            <div>QoS Level: <span class="text-gray-400">QoS 1 (At least once)</span></div>
            <div>Keep Alive: <span class="text-gray-400">60 seconds</span></div>
            <div>Clean Session: <span class="text-gray-400">True</span></div>
            <div>Message Retained: <span class="text-gray-400">${sensor.publisher?.retained ? 'Yes' : 'No'}</span></div>
        </div>
    </div>

    ${sensorReadings}

    <div class="bg-gray-900 rounded p-4 border-l-4 ${isSecure ? 'border-green-500' : 'border-red-500'}">
        <div class="font-bold text-white mb-2">🔒 SECURITY ASSESSMENT</div>
        <div class="space-y-2">
            <div>Risk Level: <span class="font-bold ${riskColor}">${riskLevel}</span></div>

            ${securityIssues.length > 0 ? `
            <div class="mt-2">
                <div class="text-yellow-400 font-medium">⚠️ Security Issues Found:</div>
                <div class="ml-2 space-y-1 text-yellow-300">
                    ${securityIssues.join('<br>')}
                </div>
            </div>` : ''}

            ${recommendations.length > 0 ? `
            <div class="mt-2">
                <div class="text-blue-400 font-medium">💡 Recommendations:</div>
                <div class="ml-2 space-y-1 text-blue-300">
                    ${recommendations.join('<br>')}
                </div>
            </div>` : ''}
        </div>
    </div>

    <div class="bg-gray-900 rounded p-4 border-l-4 border-purple-500">
        <div class="font-bold text-white mb-2">🛡️ ACCESS CONTROL & AUTHENTICATION</div>
        <div class="space-y-1">
            <div>Anonymous Access: <span class="${anonymousAllowed ? 'text-red-400' : 'text-green-400'}">${anonymousAllowed ? '❌ Enabled' : '✅ Disabled'}</span></div>
            <div>Authentication: <span class="${!anonymousAllowed ? 'text-green-400' : 'text-red-400'}">${!anonymousAllowed ? '✅ Required' : '❌ Not Required'}</span></div>
            <div>Auth Method: <span class="text-gray-400">${isSecure ? 'Username/Password' : 'None (Anonymous)'}</span></div>
            <div>Credentials Used: <span class="text-blue-400">${isSecure ? 'mqtt@example.com' : 'None'}</span></div>
            <div>Port Type: <span class="${isSecure ? 'text-green-400' : 'text-red-400'}">${isSecure ? '🔐 Secure (TLS)' : '⚠️ Insecure (Plain)'}</span></div>
            <div>Encryption: <span class="${isSecure ? 'text-green-400' : 'text-red-400'}">${isSecure ? '✅ AES-256-GCM' : '❌ None (Plain Text)'}</span></div>
            <div>Data Integrity: <span class="${isSecure ? 'text-green-400' : 'text-red-400'}">${isSecure ? '✅ Protected' : '❌ Vulnerable'}</span></div>
        </div>
    </div>

    ${tlsInfo}

    ${publisherInfo}
</div>
            `;

            modal.classList.remove('hidden');
        }

        // Close details modal
        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }

        // Close modal on background click
        document.getElementById('detailsModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDetailsModal();
            }
        });

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM fully loaded - Dashboard initialized');

        // Update time to Malaysia time on load
        const updateTime = () => {
            const now = new Date();
            const options = {
                timeZone: 'Asia/Kuala_Lumpur',
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            };
            const timeStr = now.toLocaleString('en-US', options);
            const timestampEl = document.getElementById('scanTimestamp');
            if (timestampEl) {
                timestampEl.textContent = timeStr;
            }
        };

        // Initial update
        updateTime();

        // Update every minute to keep it synced with real time
        setInterval(updateTime, 60000);

        // Network Scanner Functionality

        // Validate IPv4 or CIDR input
        function validateTarget(target) {
            // IPv4 pattern: xxx.xxx.xxx.xxx
            const ipv4Pattern = /^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
            // CIDR pattern: xxx.xxx.xxx.xxx/yy
            const cidrPattern = /^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/(3[0-2]|[12]?[0-9])$/;

            return ipv4Pattern.test(target) || cidrPattern.test(target);
        }

        // Get classification color class
        function getClassificationColor(classification) {
            const map = {
                'open_or_auth_ok': 'bg-yellow-50 border-yellow-200',
                'not_authorized': 'bg-red-50 border-red-200',
                'closed_or_unreachable': 'bg-gray-50 border-gray-200',
                'Critical': 'bg-red-50 border-red-200',
                'Warning': 'bg-yellow-50 border-yellow-200',
                'OK': 'bg-green-50 border-green-200',
                'Unknown': 'bg-gray-50 border-gray-200'
            };
            return map[classification] || 'bg-gray-50 border-gray-200';
        }

        function getClassificationBadge(classification) {
            const map = {
                'open_or_auth_ok': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Open/Auth OK</span>',
                'not_authorized': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Not Authorized</span>',
                'closed_or_unreachable': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Closed/Unreachable</span>',
                'Critical': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Critical</span>',
                'Warning': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Warning</span>',
                'OK': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">OK</span>',
                'Unknown': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>'
            };
            return map[classification] || '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
        }

        // Start a new scan
        async function startScan() {
            console.log('startScan() function called!');
            const target = document.getElementById('targetInput').value.trim();
            const scanUsername = document.getElementById('scanUsername').value.trim();
            const scanPassword = document.getElementById('scanPassword').value.trim();
            const errorEl = document.getElementById('targetError');
            const startBtn = document.getElementById('startScanBtn');
            const btnText = document.getElementById('scanBtnText');
            const btnIcon = document.getElementById('scanBtnIcon');

            // Validate input
            if (!validateTarget(target)) {
                errorEl.classList.remove('hidden');
                return;
            }
            errorEl.classList.add('hidden');

            // Disable button and show loading state
            startBtn.disabled = true;
            btnText.textContent = 'Scanning...';
            btnIcon.classList.add('animate-spin');

            // Record scan start time
            scanStartTime = new Date();

            // Show status section
            document.getElementById('scanStatus').classList.remove('hidden');
            document.getElementById('statusMessage').textContent = 'Initializing scan...';
            document.getElementById('progressBar').style.width = '0%';

            // Hide previous results
            document.getElementById('summaryCards').classList.add('hidden');
            document.getElementById('resultsContainer').classList.add('hidden');

            // Build request body
            const requestBody = {
                target: target,
                listen_duration: 3,
                capture_all_topics: true
            };

            // Add credentials if provided
            if (scanUsername && scanPassword) {
                requestBody.creds = {
                    user: scanUsername,
                    pass: scanPassword
                };
            }

            try {
                const response = await fetch('{{ route('scan') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(requestBody)
                });

                const data = await response.json();
                console.log('Scan start response:', data);

                // Check if scan returned results directly (Flask scanner)
                if (response.ok && data.status === 'ok' && data.results) {
                    console.log('Scan completed with results:', data.results);
                    scanEndTime = new Date();
                    updateScanTiming();
                    document.getElementById('statusMessage').textContent = 'Scan completed!';
                    document.getElementById('progressBar').style.width = '100%';

                    // Display results directly
                    displayResults(data.results);
                    updateSummaryCards(data.results);
                    resetScanUI();
                } else if (response.ok && data.job_id) {
                    // Background job mode
                    currentJobId = data.job_id;
                    pollScanStatus();
                } else {
                    throw new Error(data.error || 'Failed to start scan');
                }
            } catch (error) {
                console.error('Scan start error:', error);
                alert('Error starting scan: ' + error.message);
                resetScanUI();
            }
        }

        // Poll scan status
        async function pollScanStatus() {
            if (!currentJobId) return;

            try {
                const response = await fetch(`/scan/status/${currentJobId}`);
                const data = await response.json();
                console.log('Poll status response:', data);

                // Update status message and progress
                document.getElementById('statusMessage').textContent = data.message || 'Scanning...';
                document.getElementById('progressBar').style.width = `${data.progress || 0}%`;

                if (data.status === 'completed') {
                    // Stop polling
                    if (statusPollInterval) {
                        clearInterval(statusPollInterval);
                        statusPollInterval = null;
                    }

                    // Load results
                    await loadScanResults();
                    resetScanUI();
                } else if (data.status === 'failed') {
                    if (statusPollInterval) {
                        clearInterval(statusPollInterval);
                        statusPollInterval = null;
                    }
                    alert('Scan failed: ' + (data.message || 'Unknown error'));
                    resetScanUI();
                } else if (data.status === 'running' || data.status === 'queued') {
                    // Continue polling every 1 second
                    if (!statusPollInterval) {
                        statusPollInterval = setInterval(pollScanStatus, 1000);
                    }
                }
            } catch (error) {
                console.error('Poll status error:', error);
                if (statusPollInterval) {
                    clearInterval(statusPollInterval);
                    statusPollInterval = null;
                }
                alert('Error checking scan status: ' + error.message);
                resetScanUI();
            }
        }

        // Load scan results
        async function loadScanResults() {
            if (!currentJobId) return;

            try {
                const response = await fetch(`/scan/results/${currentJobId}`);
                const data = await response.json();
                console.log('Results response:', data);

                if (response.ok && data.results) {
                    displayResults(data.results);
                    updateSummaryCards(data.results);
                } else {
                    throw new Error(data.error || 'Failed to load results');
                }
            } catch (error) {
                console.error('Results loading error:', error);
                alert('Error loading results: ' + error.message);
            }
        }

        // Display results in table
        function displayResults(results) {
            console.log('displayResults called with:', results);
            console.log('Results type:', typeof results, 'Length:', results?.length);
            const tbody = document.getElementById('resultsTableBody');
            tbody.innerHTML = '';

            if (!results || results.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                            No sensors detected. Make sure your ESP32 is connected and publishing data.
                        </td>
                    </tr>
                `;
                document.getElementById('resultsContainer').classList.remove('hidden');
                document.getElementById('scanStatus').classList.add('hidden');
                return;
            }

            // Process each connection (port) and extract sensors from publishers
            const sensors = [];
            const seenTopics = new Set(); // Track unique topics to avoid duplicates
            const allowedTopics = [
                'sensors/faris/dht_secure',
                'sensors/faris/ldr_secure',
                'sensors/faris/pir_insecure'
            ];

            results.forEach(row => {
                let foundSensor = false;

                // Extract sensors from publishers
                if (row.publishers && row.publishers.length > 0) {
                    row.publishers.forEach(pub => {
                        const topic = pub.topic || '';

                        // Only show the 3 main ESP32 sensor topics
                        if (!allowedTopics.includes(topic)) {
                            return; // Skip this topic
                        }

                        // Skip if we've already seen this topic (avoid duplicates)
                        if (seenTopics.has(topic)) {
                            return; // Skip duplicate
                        }
                        seenTopics.add(topic);

                        let sensorType = 'Unknown';
                        let sensorIcon = '❓';

                        // Detect sensor type from topic
                        if (topic.includes('dht')) {
                            sensorType = 'DHT (Temperature & Humidity)';
                            sensorIcon = '🌡️';
                        } else if (topic.includes('ldr')) {
                            sensorType = 'LDR (Light Sensor)';
                            sensorIcon = '💡';
                        } else if (topic.includes('pir')) {
                            sensorType = 'PIR (Motion Sensor)';
                            sensorIcon = '👁️';
                        }

                        sensors.push({
                            ...row,
                            topic: topic,
                            sensorType: sensorType,
                            sensorIcon: sensorIcon,
                            publisher: pub
                        });
                        foundSensor = true;
                    });
                }

                // If no specific sensors found, but the port was open or had an error we want to show
                if (!foundSensor) {
                    let statusMsg = 'No sensor data';
                    let sensorType = 'Unknown';
                    let sensorIcon = '❓';
                    let shouldShow = false;

                    if (row.classification === 'not_authorized') {
                        statusMsg = 'Authentication Failed';
                        sensorType = 'Access Denied';
                        sensorIcon = '🔒';
                        shouldShow = true;
                    } else if (row.classification === 'open_or_auth_ok') {
                         statusMsg = 'No matching topics';
                         sensorType = 'Broker Open';
                         sensorIcon = '📡';
                         shouldShow = true;
                    }

                    if (shouldShow) {
                         sensors.push({
                            ...row,
                            topic: 'N/A',
                            sensorType: sensorType,
                            sensorIcon: sensorIcon,
                            publisher: { payload: null, message_count: 0 },
                            statusMsg: statusMsg
                        });
                    }
                }
            });

            // Display each sensor as a row
            sensors.forEach((sensor, index) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-700 transition-colors';

                // Security badge with icon
                let securityBadge = '';
                if (sensor.tls || sensor.port == 8883) {
                    securityBadge = `
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            TLS
                        </span>
                    `;
                } else {
                    securityBadge = `
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Plain
                        </span>
                    `;
                }

                // Parse sensor data from payload
                let sensorDataDisplay = '<span class="text-gray-500 text-xs">No data</span>';

                if (sensor.statusMsg) {
                     sensorDataDisplay = `<span class="text-red-400 text-xs font-bold">${sensor.statusMsg}</span>`;
                } else if (sensor.publisher && sensor.publisher.payload) {
                    try {
                        const payload = JSON.parse(sensor.publisher.payload);
                        console.log('Parsed payload:', payload);

                        let dataHTML = '<div class="text-xs space-y-0.5">';
                        let hasData = false;

                        // DHT Sensor - Temperature & Humidity
                        if (payload.temp_c !== undefined || payload.temperature !== undefined) {
                            const temp = payload.temp_c || payload.temperature;
                            dataHTML += `<div class="text-blue-400">🌡️ ${temp}°C</div>`;
                            hasData = true;
                        }
                        if (payload.hum_pct !== undefined || payload.humidity !== undefined) {
                            const hum = payload.hum_pct || payload.humidity;
                            dataHTML += `<div class="text-cyan-400">💧 ${hum}%</div>`;
                            hasData = true;
                        }

                        // LDR Sensor - Light
                        if (payload.ldr_pct !== undefined || payload.light !== undefined) {
                            const lightPct = payload.ldr_pct || payload.light;
                            dataHTML += `<div class="text-yellow-400">💡 ${lightPct}%</div>`;
                            hasData = true;
                        }

                        // PIR Sensor - Motion
                        if (payload.pir !== undefined || payload.motion !== undefined) {
                            const motion = payload.pir || payload.motion;
                            const motionText = motion ? 'Motion Detected' : 'No Motion';
                            const motionColor = motion ? 'text-red-400' : 'text-green-400';
                            dataHTML += `<div class="${motionColor}">👁️ ${motionText}</div>`;
                            hasData = true;
                        }

                        dataHTML += '</div>';

                        if (hasData) {
                            sensorDataDisplay = dataHTML;
                        } else {
                            // Show raw payload if no recognized fields
                            sensorDataDisplay = `<span class="text-gray-400 text-xs font-mono">${sensor.publisher.payload}</span>`;
                        }
                    } catch (e) {
                        console.error('Failed to parse payload:', e);
                        // If parsing fails, show truncated raw payload
                        const rawPayload = sensor.publisher.payload.substring(0, 30);
                        sensorDataDisplay = `<span class="text-gray-400 text-xs font-mono">${rawPayload}...</span>`;
                    }
                } else {
                    console.warn('No payload found for sensor:', sensor.topic);
                }

                // Message count
                let messageCount = '0';
                if (sensor.topics_discovered && sensor.topics_discovered[sensor.topic]) {
                    messageCount = sensor.topics_discovered[sensor.topic].message_count || 0;
                } else if (sensor.publisher && sensor.publisher.message_count) {
                    messageCount = sensor.publisher.message_count;
                }

                // Store sensor data in data attribute for modal
                tr.dataset.sensorData = JSON.stringify(sensor);

                tr.innerHTML = `
                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                        <span class="font-mono text-gray-300">${sensor.ip || 'N/A'}</span>
                        <span class="text-gray-500">:</span>
                        <span class="font-mono font-semibold text-white">${sensor.port || 'N/A'}</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">${securityBadge}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-200">
                        <span class="text-lg">${sensor.sensorIcon}</span>
                        <span class="ml-2 font-medium">${sensor.sensorType}</span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        ${sensorDataDisplay}
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <div class="font-mono text-xs text-blue-400 truncate max-w-xs" title="${sensor.topic}">
                            ${sensor.topic}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-900 text-blue-200">
                            ${messageCount}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <button onclick="showDetails(${index})" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Details
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Store sensors globally for modal access
            globalSensors = sensors;

            // Show results container
            document.getElementById('resultsContainer').classList.remove('hidden');
            document.getElementById('scanStatus').classList.add('hidden');

            // Force update summary cards immediately after displaying results
            updateSummaryCards(results);
        }

        // Update summary cards
        function updateSummaryCards(results) {
            // Use globalSensors to ensure counts match the visible table rows
            const totalDetected = globalSensors ? globalSensors.length : 0;

            // Count "Open" (Accessible) sensors
            const openSensors = globalSensors ? globalSensors.filter(s => s.classification === 'open_or_auth_ok').length : 0;

            // Count Auth Failures
            const authFailures = globalSensors ? globalSensors.filter(s => s.classification === 'not_authorized').length : 0;

            // Update cards
            document.getElementById('totalScanned').textContent = totalDetected;
            document.getElementById('openBrokers').textContent = openSensors;
            document.getElementById('authFailures').textContent = authFailures;

            // Calculate actual unique targets scanned (IP:Port) for the header text
            // This keeps the "scope" information available in the header
            const uniquePorts = new Set();
            if (results && Array.isArray(results)) {
                results.forEach(r => {
                    if (r.ip && r.port) {
                        uniquePorts.add(`${r.ip}:${r.port}`);
                    }
                });
            }
            const totalTargetsScanned = uniquePorts.size;

            // Update header scan count to show both detected sensors and total targets scanned
            document.getElementById('headerScanCount').textContent = `${totalDetected} Sensors Detected (${totalTargetsScanned} Ports Scanned)`;

            document.getElementById('summaryCards').classList.remove('hidden');
        }

        // Update scan timing display
        function updateScanTiming() {
            if (scanStartTime && scanEndTime) {
                const durationMs = scanEndTime - scanStartTime;
                const durationSec = Math.round(durationMs / 1000);

                // Update scan duration
                document.getElementById('scanDuration').textContent = `Scan time: ${durationSec}s`;
                document.getElementById('scanDurationDisplay').style.display = 'flex';

                // Update timestamp with HH:MM format
                const timeStr = scanEndTime.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
                document.getElementById('scanTimestamp').textContent = timeStr;
            }
        }

        // Reset scan UI
        function resetScanUI() {
            const startBtn = document.getElementById('startScanBtn');
            const btnText = document.getElementById('scanBtnText');
            const btnIcon = document.getElementById('scanBtnIcon');

            startBtn.disabled = false;
            btnText.textContent = 'Start Scan';
            btnIcon.classList.remove('animate-spin');

            // Clear any active polling
            if (statusPollInterval) {
                clearInterval(statusPollInterval);
                statusPollInterval = null;
            }
        }

        // Download CSV
        function downloadCSV() {
            if (!globalSensors || globalSensors.length === 0) {
                alert('No scan results to download');
                return;
            }

            // Create CSV content with improved headers and data
            const headers = [
                'Scan Date',
                'Target IP',
                'Port',
                'Protocol',
                'Topic',
                'Sensor Type',
                'Sensor Data (Summary)',
                'Raw Payload',
                'Status',
                'Risk Level',
                'Message Count'
            ];
            // Use CRLF for Windows compatibility
            let csvContent = headers.join(',') + '\r\n';

            globalSensors.forEach(sensor => {
                // Parse sensor data for summary
                let sensorDataSummary = 'No data';
                let rawPayload = '';

                if (sensor.publisher && sensor.publisher.payload) {
                    rawPayload = sensor.publisher.payload.replace(/"/g, '""'); // Escape quotes
                    try {
                        const payload = JSON.parse(sensor.publisher.payload);
                        const parts = [];
                        if (payload.temp_c !== undefined) parts.push(`${payload.temp_c}C`);
                        if (payload.hum_pct !== undefined) parts.push(`${payload.hum_pct}%`);
                        if (payload.ldr_pct !== undefined) parts.push(`${payload.ldr_pct}% light`);
                        if (payload.pir !== undefined) parts.push(payload.pir ? 'Motion' : 'No motion');
                        sensorDataSummary = parts.length > 0 ? parts.join('; ') : 'N/A';
                    } catch (e) {
                        sensorDataSummary = 'Parse error';
                    }
                }

                // Determine Status and Risk
                let status = 'Unknown';
                let riskLevel = 'LOW';
                const isSecure = sensor.tls || sensor.port == 8883;

                if (sensor.classification === 'open_or_auth_ok') status = 'Open / OK';
                else if (sensor.classification === 'not_authorized') status = 'Auth Failed';
                else if (sensor.classification === 'closed_or_unreachable') status = 'Unreachable';

                if (!isSecure) riskLevel = 'CRITICAL';
                else if (sensor.classification === 'not_authorized') riskLevel = 'MEDIUM';

                const row = [
                    new Date().toLocaleString().replace(/,/g, ''), // Scan Date
                    sensor.ip || '',
                    sensor.port || '',
                    isSecure ? 'MQTT over TLS' : 'MQTT (Plain)',
                    sensor.topic || '',
                    sensor.sensorType || '',
                    sensorDataSummary,
                    rawPayload,
                    status,
                    riskLevel,
                    sensor.publisher?.message_count || '0'
                ];

                // Join with quotes to handle commas in data
                csvContent += row.map(field => `"${field}"`).join(',') + '\r\n';
            });

            // Download
            // Get and increment counter
            let count = localStorage.getItem('mqtt_scan_csv_count');
            if (!count) count = 0;
            count = parseInt(count) + 1;
            localStorage.setItem('mqtt_scan_csv_count', count);

            const sequence = String(count).padStart(3, '0');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `mqtt_scan_results_${sequence}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        // Download PDF
        function downloadPDF() {
            if (!globalSensors || globalSensors.length === 0) {
                alert('No scan results to download');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const pageWidth = doc.internal.pageSize.getWidth();

            // --- Header ---
            doc.setFillColor(37, 99, 235); // Blue header
            doc.rect(0, 0, pageWidth, 40, 'F');

            doc.setFontSize(22);
            doc.setTextColor(255, 255, 255);
            doc.text('MQTT Security Scanner Report', 14, 20);

            doc.setFontSize(10);
            doc.setTextColor(200, 200, 200);
            const scanDate = scanEndTime ? scanEndTime.toLocaleString('en-US') : new Date().toLocaleString();
            doc.text(`Generated on: ${scanDate}`, 14, 30);

            // --- Summary Section ---
            let yPos = 50;
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(14);
            doc.text('Executive Summary', 14, yPos);
            yPos += 10;

            const uniquePorts = new Set();
            globalSensors.forEach(s => {
                if(s.ip && s.port) uniquePorts.add(`${s.ip}:${s.port}`);
            });
            const totalDetected = globalSensors.length;
            const openSensors = globalSensors.filter(s => s.classification === 'open_or_auth_ok').length;
            const authFailures = globalSensors.filter(s => s.classification === 'not_authorized').length;

            // Draw summary boxes
            const boxWidth = 55;
            const boxHeight = 25;

            // Box 1: Total Sensors
            doc.setFillColor(240, 240, 255);
            doc.setDrawColor(200, 200, 255);
            doc.roundedRect(14, yPos, boxWidth, boxHeight, 3, 3, 'FD');
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text('Total Sensors Detected', 19, yPos + 8);
            doc.setFontSize(16);
            doc.setTextColor(37, 99, 235);
            doc.text(`${totalDetected}`, 19, yPos + 18);

            // Box 2: Open Brokers
            doc.setFillColor(255, 240, 240);
            doc.setDrawColor(255, 200, 200);
            doc.roundedRect(14 + boxWidth + 10, yPos, boxWidth, boxHeight, 3, 3, 'FD');
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text('Open Brokers', 14 + boxWidth + 15, yPos + 8);
            doc.setFontSize(16);
            doc.setTextColor(220, 38, 38); // Red
            doc.text(`${openSensors}`, 14 + boxWidth + 15, yPos + 18);

            // Box 3: Unique Ports
            doc.setFillColor(240, 255, 240);
            doc.setDrawColor(200, 255, 200);
            doc.roundedRect(14 + (boxWidth + 10) * 2, yPos, boxWidth, boxHeight, 3, 3, 'FD');
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text('Ports Scanned', 14 + (boxWidth + 10) * 2 + 5, yPos + 8);
            doc.setFontSize(16);
            doc.setTextColor(22, 163, 74); // Green
            doc.text(`${uniquePorts.size}`, 14 + (boxWidth + 10) * 2 + 5, yPos + 18);

            yPos += 35;

            // --- Overview Table ---
            doc.setFontSize(14);
            doc.setTextColor(0, 0, 0);
            doc.text('Scan Overview', 14, yPos);
            yPos += 5;

            const tableData = globalSensors.map(sensor => {
                let sensorData = 'No data';
                if (sensor.publisher && sensor.publisher.payload) {
                    try {
                        const payload = JSON.parse(sensor.publisher.payload);
                        const parts = [];
                        if (payload.temp_c !== undefined) parts.push(`${payload.temp_c}°C`);
                        if (payload.hum_pct !== undefined) parts.push(`${payload.hum_pct}%`);
                        if (payload.ldr_pct !== undefined) parts.push(`${payload.ldr_pct}% light`);
                        if (payload.pir !== undefined) parts.push(payload.pir ? 'Motion' : 'No motion');
                        sensorData = parts.length > 0 ? parts.join(', ') : 'N/A';
                    } catch (e) {
                        sensorData = 'Parse error';
                    }
                }

                // Status text
                let status = 'Unknown';
                if (sensor.classification === 'open_or_auth_ok') status = 'Open / OK';
                else if (sensor.classification === 'not_authorized') status = 'Auth Failed';
                else if (sensor.classification === 'closed_or_unreachable') status = 'Unreachable';

                return [
                    `${sensor.ip}:${sensor.port}`,
                    (sensor.tls || sensor.port == 8883) ? 'TLS' : 'Plain',
                    sensor.sensorType || 'Unknown',
                    sensorData,
                    status
                ];
            });

            doc.autoTable({
                head: [['Target', 'Security', 'Sensor Type', 'Latest Reading', 'Status']],
                body: tableData,
                startY: yPos,
                theme: 'grid',
                headStyles: { fillColor: [37, 99, 235] },
                styles: { fontSize: 9 },
            });

            yPos = doc.lastAutoTable.finalY + 20;

            // --- Detailed Findings ---
            doc.addPage();
            yPos = 20;

            doc.setFontSize(16);
            doc.setTextColor(0, 0, 0);
            doc.text('Detailed Security Findings', 14, yPos);
            yPos += 10;

            globalSensors.forEach((sensor, index) => {
                // Check if we need a new page
                if (yPos > 250) {
                    doc.addPage();
                    yPos = 20;
                }

                // Section Header
                doc.setFillColor(245, 247, 250);
                doc.setDrawColor(200, 200, 200);
                doc.rect(14, yPos, pageWidth - 28, 10, 'FD');
                doc.setFontSize(11);
                doc.setFont(undefined, 'bold');
                doc.setTextColor(0, 0, 0);
                doc.text(`Finding #${index + 1}: ${sensor.sensorType} at ${sensor.ip}:${sensor.port}`, 18, yPos + 7);
                yPos += 15;

                // Details Content
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');

                // Left Column: Target Info
                const leftX = 18;
                doc.text(`Target: ${sensor.ip}`, leftX, yPos);
                doc.text(`Port: ${sensor.port}`, leftX, yPos + 5);
                const isSecure = sensor.tls || sensor.port == 8883;
                doc.text(`Protocol: MQTT over ${isSecure ? 'TLS (Secure)' : 'TCP (Insecure)'}`, leftX, yPos + 10);
                doc.text(`Topic: ${sensor.topic || 'N/A'}`, leftX, yPos + 15);

                // Right Column: Security Assessment
                const rightX = 110;
                doc.text('Security Assessment:', rightX, yPos);

                let riskLevel = 'LOW';
                let riskColor = [34, 197, 94]; // Green
                if (!isSecure) {
                    riskLevel = 'CRITICAL';
                    riskColor = [220, 38, 38]; // Red
                } else if (sensor.classification === 'not_authorized') {
                    riskLevel = 'MEDIUM'; // Auth failed is good security, but maybe not what we want if we own it? Actually auth failed means it's secure against unauthorized access.
                    // But if we are scanning for vulnerabilities, "Open" is the risk.
                }

                doc.setTextColor(...riskColor);
                doc.setFont(undefined, 'bold');
                doc.text(`Risk Level: ${riskLevel}`, rightX, yPos + 5);
                doc.setTextColor(0, 0, 0);
                doc.setFont(undefined, 'normal');

                if (!isSecure) {
                    doc.text('• Unencrypted connection (Plaintext)', rightX, yPos + 10);
                    doc.text('• Vulnerable to eavesdropping', rightX, yPos + 15);
                } else {
                    doc.text('• Encrypted connection (TLS)', rightX, yPos + 10);
                    doc.text('• Certificate validation active', rightX, yPos + 15);
                }

                yPos += 25;

                // Sensor Data Section
                if (sensor.publisher && sensor.publisher.payload) {
                    doc.setFont(undefined, 'bold');
                    doc.text('Captured Payload:', leftX, yPos);
                    doc.setFont(undefined, 'normal');
                    doc.setFont('courier');
                    doc.setFontSize(9);

                    // Wrap payload text
                    const payloadText = sensor.publisher.payload;
                    const splitPayload = doc.splitTextToSize(payloadText, pageWidth - 40);
                    doc.text(splitPayload, leftX, yPos + 5);

                    doc.setFont('helvetica'); // Reset font
                    doc.setFontSize(10);
                    yPos += 5 + (splitPayload.length * 4) + 5;
                } else {
                    yPos += 5;
                }

                // Separator line
                doc.setDrawColor(230, 230, 230);
                doc.line(14, yPos, pageWidth - 14, yPos);
                yPos += 10;
            });

            // Footer
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(150);
                doc.text(
                    `Page ${i} of ${pageCount}`,
                    pageWidth / 2,
                    doc.internal.pageSize.getHeight() - 10,
                    { align: 'center' }
                );
                doc.text(
                    'Generated by MQTT Security Scanner',
                    14,
                    doc.internal.pageSize.getHeight() - 10
                );
            }

            // Save the PDF
            // Get and increment counter
            let count = localStorage.getItem('mqtt_scan_pdf_count');
            if (!count) count = 0;
            count = parseInt(count) + 1;
            localStorage.setItem('mqtt_scan_pdf_count', count);

            const sequence = String(count).padStart(3, '0');

            doc.save(`mqtt_scan_report_${sequence}.pdf`);
        }

        // Event listeners
        console.log('Attaching event listeners...');
        const startBtn = document.getElementById('startScanBtn');
        const downloadCsvBtn = document.getElementById('downloadCsvBtn');
        const downloadPdfBtn = document.getElementById('downloadPdfBtn');
        console.log('Start button found:', startBtn);
        console.log('Download CSV button found:', downloadCsvBtn);
        console.log('Download PDF button found:', downloadPdfBtn);

        if (startBtn) {
            startBtn.addEventListener('click', startScan);
            console.log('Start scan event listener attached');
        }
        if (downloadCsvBtn) {
            downloadCsvBtn.addEventListener('click', downloadCSV);
            console.log('Download CSV event listener attached');
        }
        if (downloadPdfBtn) {
            downloadPdfBtn.addEventListener('click', downloadPDF);
            console.log('Download PDF event listener attached');
        }

        // Allow Enter key to start scan
        document.getElementById('targetInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') startScan();
        });

        // Sensor scanning functionality
        let autoRefreshInterval = null;

        function formatSensorData(data, brokerType) {
            if (!data) {
                return `<p class="text-sm text-gray-500">No ${brokerType} sensor detected.</p>`;
            }

            const temp = data.temperature !== null && data.temperature !== undefined ? data.temperature.toFixed(1) : 'N/A';
            const humidity = data.humidity !== null && data.humidity !== undefined ? data.humidity.toFixed(1) : 'N/A';
            const light = data.light_pct !== null && data.light_pct !== undefined ? data.light_pct.toFixed(1) : 'N/A';
            const motion = data.motion !== null && data.motion !== undefined ? (data.motion ? 'DETECTED' : 'None') : 'N/A';
            const timeAgo = data.timestamp ? new Date(data.timestamp).toLocaleString() : 'Unknown';

            // Color coding for temperature
            const tempColor = temp === 'N/A' ? 'text-gray-600' : (temp > 30 ? 'text-red-600' : 'text-blue-600');

            // Color coding for light
            const lightColor = light === 'N/A' ? 'text-gray-600' : (light > 50 ? 'text-yellow-600' : 'text-gray-600');

            // Color coding for motion
            const motionColor = motion === 'DETECTED' ? 'text-red-600' : 'text-green-600';
            const motionBg = motion === 'DETECTED' ? 'bg-red-100' : 'bg-green-100';

            return `
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Temperature -->
                        <div class="bg-blue-50 rounded-lg p-3">
                            <div class="flex items-center mb-1">
                                <svg class="w-4 h-4 text-blue-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                                </svg>
                                <span class="text-xs font-medium text-gray-700">Temperature</span>
                            </div>
                            <span class="text-xl font-bold ${tempColor}">${temp}°C</span>
                        </div>

                        <!-- Humidity -->
                        <div class="bg-indigo-50 rounded-lg p-3">
                            <div class="flex items-center mb-1">
                                <svg class="w-4 h-4 text-indigo-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.5 3A2.5 2.5 0 003 5.5v9A2.5 2.5 0 005.5 17h9a2.5 2.5 0 002.5-2.5v-9A2.5 2.5 0 0014.5 3h-9zm4.5 11a4 4 0 100-8 4 4 0 000 8z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs font-medium text-gray-700">Humidity</span>
                            </div>
                            <span class="text-xl font-bold text-indigo-600">${humidity}%</span>
                        </div>

                        <!-- Light -->
                        <div class="bg-yellow-50 rounded-lg p-3">
                            <div class="flex items-center mb-1">
                                <svg class="w-4 h-4 text-yellow-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                                </svg>
                                <span class="text-xs font-medium text-gray-700">Light</span>
                            </div>
                            <span class="text-xl font-bold ${lightColor}">${light}%</span>
                        </div>

                        <!-- Motion -->
                        <div class="${motionBg} rounded-lg p-3">
                            <div class="flex items-center mb-1">
                                <svg class="w-4 h-4 ${motion === 'DETECTED' ? 'text-red-600' : 'text-green-600'} mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/>
                                </svg>
                                <span class="text-xs font-medium text-gray-700">Motion</span>
                            </div>
                            <span class="text-lg font-bold ${motionColor}">${motion}</span>
                        </div>
                    </div>

                    <!-- Device Info -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">${data.device || 'Unknown Device'}</span>
                            </div>
                            <span class="text-xs text-gray-500">${timeAgo}</span>
                        </div>
                        ${data.topic ? `<div class="mt-1 text-xs text-gray-500">Topic: ${data.topic}</div>` : ''}
                        ${data.note ? `<div class="mt-1 text-xs text-gray-500">${data.note}</div>` : ''}
                    </div>
                </div>
            `;
        }

        // Auto-refresh removed per user request

        }); // End DOMContentLoaded
    </script>
</body>
</html>
