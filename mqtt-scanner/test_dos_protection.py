#!/usr/bin/env python3
"""
Test DoS Protection - Connection Limits
Tests if broker enforces max_connections limit
"""

import paho.mqtt.client as mqtt
import time

HOST = "192.168.100.56"
PORT = 8883
USERNAME = "faris02@gmail.com"
PASSWORD = "faris123"

MAX_CONNECTIONS_EXPECTED = 100  # As configured in mosquitto.conf

print("=" * 60)
print("DoS PROTECTION TEST - Connection Limits")
print("=" * 60)
print(f"Testing against: {HOST}:{PORT}")
print(f"Expected max_connections: {MAX_CONNECTIONS_EXPECTED}")
print("\nAttempting to create excessive connections...")

clients = []
successful_connections = 0
failed_connections = 0

def on_connect(client, userdata, flags, rc, properties=None):
    global successful_connections
    if rc == 0:
        successful_connections += 1

# Test: Try to create more connections than allowed
print(f"\nCreating {MAX_CONNECTIONS_EXPECTED + 20} connections...")

for i in range(MAX_CONNECTIONS_EXPECTED + 20):
    try:
        client = mqtt.Client(client_id=f"dos_test_{i}")
        client.username_pw_set(USERNAME, PASSWORD)
        client.tls_set()
        client.tls_insecure_set(True)
        client.on_connect = on_connect

        result = client.connect(HOST, PORT, keepalive=60)

        if result == 0:
            clients.append(client)
            client.loop_start()
            print(f"  [{i+1}] Connection successful", end="\r")
        else:
            failed_connections += 1
            print(f"  [{i+1}] Connection failed (rc={result})")

    except Exception as e:
        failed_connections += 1
        if i >= MAX_CONNECTIONS_EXPECTED - 10:
            print(f"  [{i+1}] ✅ Connection rejected: {e}")

    # Small delay to avoid overwhelming
    if i % 10 == 0:
        time.sleep(0.1)

time.sleep(2)

# Cleanup
print(f"\n\nCleaning up connections...")
for client in clients:
    try:
        client.loop_stop()
        client.disconnect()
    except:
        pass

print("\n" + "=" * 60)
print("DoS PROTECTION TEST RESULTS")
print("=" * 60)
print(f"Successful connections: {successful_connections}")
print(f"Failed connections: {failed_connections}")
print(f"Expected limit: {MAX_CONNECTIONS_EXPECTED}")

if successful_connections <= MAX_CONNECTIONS_EXPECTED:
    print("\n✅ PASS: Connection limit is enforced!")
    print(f"   Broker accepted {successful_connections} connections (limit: {MAX_CONNECTIONS_EXPECTED})")
else:
    print("\n⚠️  WARNING: Connection limit may not be enforced")
    print(f"   Broker accepted {successful_connections} connections (expected limit: {MAX_CONNECTIONS_EXPECTED})")

print("=" * 60)
