<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('MQTT Security Scanner Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-100">
                    <h3 class="text-2xl font-bold mb-2">Welcome to MQTT Scanner</h3>
                    <p class="text-gray-400">IoT Security Analysis & DevSecOps Monitoring Platform</p>
                </div>
            </div>

            <!-- Featured: Flask MQTT Scanner Dashboard -->
            <div class="mb-6">
                <a href="http://127.0.0.1:5000/" target="_blank" class="block bg-gradient-to-r from-blue-600 to-blue-800 overflow-hidden shadow-lg sm:rounded-lg hover:from-blue-700 hover:to-blue-900 transition transform hover:scale-[1.02]">
                    <div class="p-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center mb-3">
                                    <svg class="w-10 h-10 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <h3 class="text-2xl font-bold text-white">MQTT Scanner Dashboard</h3>
                                </div>
                                <p class="text-blue-100 text-lg mb-2">Scan ESP32 devices and MQTT brokers in real-time</p>
                                <p class="text-blue-200 text-sm">Live security analysis with TLS/SSL verification, client tracking, and topic monitoring</p>
                            </div>
                            <div class="text-white">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Sensor Monitoring -->
                <a href="{{ route('sensors.dashboard') }}" class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-700 transition">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">ESP32 Sensor Data</h3>
                        <p class="text-sm text-gray-400">View stored sensor readings from database</p>
                    </div>
                </a>

                <!-- Profile Settings -->
                <a href="{{ route('profile.edit') }}" class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-700 transition">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Profile Settings</h3>
                        <p class="text-sm text-gray-400">Manage your account and preferences</p>
                    </div>
                </a>
            </div>

            <!-- Features Overview -->
            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-white mb-4">Platform Features</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-500 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-white">Security Analysis</h4>
                                <p class="text-sm text-gray-400">Comprehensive MQTT broker security scanning with TLS/SSL analysis</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-white">Client Tracking</h4>
                                <p class="text-sm text-gray-400">Identify publishers and subscribers with detailed activity monitoring</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-yellow-500 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-white">Real-time Sensors</h4>
                                <p class="text-sm text-gray-400">Monitor ESP32 IoT sensors with live temperature, humidity, and motion data</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-purple-500 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-white">DevSecOps Integration</h4>
                                <p class="text-sm text-gray-400">Automated security testing in CI/CD pipelines</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
