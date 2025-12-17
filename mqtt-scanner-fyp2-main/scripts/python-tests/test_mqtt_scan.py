#!/usr/bin/env python3
"""
Test MQTT Scanner - Verify ESP32 sensor data capture
"""
import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'mqtt-scanner'))

from scanner import run_scan
import json

# Scan the local MQTT brokers
print("=" * 60)
print("Testing MQTT Scanner - ESP32 Sensor Detection")
print("=" * 60)
print("\nScanning 192.168.100.56 (localhost MQTT brokers)...")
print("- Port 1883: Insecure (PIR sensor)")
print("- Port 8883: Secure TLS (DHT + LDR sensors)")
print("\n" + "-" * 60)

# Run scan with credentials
results = run_scan('192.168.100.56', creds={'user': 'testuser', 'pass': 'testpass'})

print(f"\nScan completed! Found {len(results)} results")
print("\n" + "=" * 60)
print("SCAN RESULTS:")
print("=" * 60)

for idx, result in enumerate(results, 1):
    print(f"\n[{idx}] {result['ip']}:{result['port']}")
    print(f"    Status: {result['result']}")
    print(f"    Classification: {result['classification']}")
    print(f"    TLS: {result.get('tls', False)}")

    # Check for publishers (sensor data)
    publishers = result.get('publishers', [])
    if publishers:
        print(f"    📡 Publishers detected: {len(publishers)}")
        for pub in publishers:
            topic = pub.get('topic', 'N/A')
            print(f"       - Topic: {topic}")
            print(f"         QoS: {pub.get('qos', 'N/A')}")
            print(f"         Retained: {pub.get('retained', False)}")

            # Identify sensor type from topic
            if 'dht' in topic.lower():
                print(f"         🌡️ SENSOR TYPE: DHT (Temperature & Humidity)")
            elif 'ldr' in topic.lower():
                print(f"         💡 SENSOR TYPE: LDR (Light Sensor)")
            elif 'pir' in topic.lower():
                print(f"         👁️ SENSOR TYPE: PIR (Motion Sensor)")

    # Check for topics discovered
    topics = result.get('topics_discovered', {})
    if topics:
        print(f"    📋 Topics discovered: {len(topics)}")
        for topic, info in topics.items():
            print(f"       - {topic}")
            print(f"         Messages: {info.get('message_count', 0)}")

    # Security assessment
    security = result.get('security_assessment', {})
    if security:
        print(f"    🔒 Security:")
        print(f"       - Port Type: {security.get('port_type', 'unknown')}")
        print(f"       - Anonymous Allowed: {security.get('anonymous_allowed', False)}")
        print(f"       - Requires Auth: {security.get('requires_auth', False)}")

    # TLS analysis
    tls = result.get('tls_analysis', {})
    if tls and tls.get('has_tls'):
        print(f"    🔐 TLS Certificate:")
        cert_details = tls.get('cert_details', {})
        print(f"       - Version: {cert_details.get('tls_version', 'N/A')}")
        print(f"       - Self-Signed: {cert_details.get('self_signed', 'Unknown')}")
        print(f"       - Security Score: {tls.get('security_score', 0)}/100")
        if tls.get('security_issues'):
            print(f"       ⚠️ Issues: {', '.join(tls['security_issues'])}")

print("\n" + "=" * 60)
print("SUMMARY:")
print("=" * 60)

# Count sensors by type
dht_count = 0
ldr_count = 0
pir_count = 0
secure_count = 0
insecure_count = 0

for result in results:
    if result['classification'] == 'open_or_auth_ok':
        if result.get('tls', False) or result['port'] == 8883:
            secure_count += 1
        else:
            insecure_count += 1

        for pub in result.get('publishers', []):
            topic = pub.get('topic', '').lower()
            if 'dht' in topic:
                dht_count += 1
            elif 'ldr' in topic:
                ldr_count += 1
            elif 'pir' in topic:
                pir_count += 1

print(f"✅ Secure connections (port 8883): {secure_count}")
print(f"⚠️ Insecure connections (port 1883): {insecure_count}")
print(f"🌡️ DHT sensors detected: {dht_count}")
print(f"💡 LDR sensors detected: {ldr_count}")
print(f"👁️ PIR sensors detected: {pir_count}")

print("\n" + "=" * 60)
print("EXPECTED RESULTS:")
print("=" * 60)
print("✓ 2 connections (port 1883 + port 8883)")
print("✓ 1 secure DHT sensor on port 8883")
print("✓ 1 secure LDR sensor on port 8883")
print("✓ 1 insecure PIR sensor on port 1883")
print("=" * 60)

# Save results to JSON file
output_file = 'mqtt_scan_test_results.json'
with open(output_file, 'w') as f:
    json.dump(results, f, indent=2)
print(f"\n✅ Full results saved to: {output_file}")
