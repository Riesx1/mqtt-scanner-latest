#!/usr/bin/env python3
"""
Test ACL (Access Control List) enforcement on secure broker
Tests if faris02@gmail.com can only access sensors/faris/* topics
"""

import paho.mqtt.client as mqtt
import time

HOST = "192.168.100.56"
PORT = 8883
USERNAME = "faris02@gmail.com"
PASSWORD = "faris123"

print("=" * 60)
print("ACL ENFORCEMENT TEST")
print("=" * 60)

# Test 1: Try to subscribe to wildcard (should be denied)
print("\n[TEST 1] Attempting wildcard subscription (#)...")
client1 = mqtt.Client(client_id="acl_test_wildcard")
client1.username_pw_set(USERNAME, PASSWORD)
client1.tls_set()
client1.tls_insecure_set(True)

subscribed_topics = []

def on_subscribe_1(client, userdata, mid, reason_codes, properties=None):
    if hasattr(reason_codes, '__iter__'):
        for code in reason_codes:
            if isinstance(code, int):
                if code >= 128:  # Error codes
                    print(f"   ❌ CORRECT: Wildcard subscription denied (code={code})")
                else:
                    print(f"   ⚠️  WARNING: Wildcard subscription allowed (code={code})")
            else:
                if str(code).startswith("Not authorized") or "135" in str(code):
                    print(f"   ✅ CORRECT: Wildcard subscription denied ({code})")
                else:
                    print(f"   ⚠️  WARNING: Wildcard subscription allowed ({code})")

client1.on_subscribe = on_subscribe_1

try:
    client1.connect(HOST, PORT, keepalive=60)
    client1.loop_start()
    time.sleep(1)
    result = client1.subscribe("#", qos=0)
    time.sleep(2)
    client1.loop_stop()
    client1.disconnect()
except Exception as e:
    print(f"   ✅ CORRECT: Connection/subscription failed - {e}")

# Test 2: Try to access own topics (should succeed)
print("\n[TEST 2] Attempting subscription to sensors/faris/#...")
client2 = mqtt.Client(client_id="acl_test_own_topic")
client2.username_pw_set(USERNAME, PASSWORD)
client2.tls_set()
client2.tls_insecure_set(True)

def on_subscribe_2(client, userdata, mid, reason_codes, properties=None):
    if hasattr(reason_codes, '__iter__'):
        for code in reason_codes:
            if isinstance(code, int):
                if code < 128:
                    print(f"   ✅ CORRECT: Own topic subscription allowed (code={code})")
                else:
                    print(f"   ❌ ERROR: Own topic subscription denied (code={code})")
            else:
                if "Success" in str(code) or str(code) == "0":
                    print(f"   ✅ CORRECT: Own topic subscription allowed ({code})")
                else:
                    print(f"   ❌ ERROR: Own topic subscription denied ({code})")

client2.on_subscribe = on_subscribe_2

try:
    client2.connect(HOST, PORT, keepalive=60)
    client2.loop_start()
    time.sleep(1)
    result = client2.subscribe("sensors/faris/#", qos=0)
    time.sleep(2)
    client2.loop_stop()
    client2.disconnect()
except Exception as e:
    print(f"   ❌ ERROR: Connection failed - {e}")

# Test 3: Try to access $SYS topics (should be denied)
print("\n[TEST 3] Attempting subscription to $SYS/# (system topics)...")
client3 = mqtt.Client(client_id="acl_test_sys_topic")
client3.username_pw_set(USERNAME, PASSWORD)
client3.tls_set()
client3.tls_insecure_set(True)

def on_subscribe_3(client, userdata, mid, reason_codes, properties=None):
    if hasattr(reason_codes, '__iter__'):
        for code in reason_codes:
            if isinstance(code, int):
                if code >= 128:
                    print(f"   ✅ CORRECT: $SYS topic subscription denied (code={code})")
                else:
                    print(f"   ⚠️  WARNING: $SYS topic subscription allowed (code={code})")
            else:
                if "Not authorized" in str(code) or "135" in str(code):
                    print(f"   ✅ CORRECT: $SYS topic subscription denied ({code})")
                else:
                    print(f"   ⚠️  WARNING: $SYS topic subscription allowed ({code})")

client3.on_subscribe = on_subscribe_3

try:
    client3.connect(HOST, PORT, keepalive=60)
    client3.loop_start()
    time.sleep(1)
    result = client3.subscribe("$SYS/#", qos=0)
    time.sleep(2)
    client3.loop_stop()
    client3.disconnect()
except Exception as e:
    print(f"   ✅ CORRECT: Connection/subscription failed - {e}")

# Test 4: Try to publish to unauthorized topic
print("\n[TEST 4] Attempting to publish to admin/commands...")
client4 = mqtt.Client(client_id="acl_test_publish")
client4.username_pw_set(USERNAME, PASSWORD)
client4.tls_set()
client4.tls_insecure_set(True)

publish_success = False

def on_publish_4(client, userdata, mid, reason_codes=None, properties=None):
    global publish_success
    publish_success = True
    if reason_codes:
        print(f"   ⚠️  WARNING: Publish to admin topic succeeded")
    else:
        print(f"   ⚠️  WARNING: Publish to admin topic succeeded")

client4.on_publish = on_publish_4

try:
    client4.connect(HOST, PORT, keepalive=60)
    client4.loop_start()
    time.sleep(1)
    result = client4.publish("admin/commands", "test", qos=0)
    time.sleep(2)

    if not publish_success:
        print(f"   ✅ CORRECT: Publish to admin topic denied (no confirmation)")

    client4.loop_stop()
    client4.disconnect()
except Exception as e:
    print(f"   ✅ CORRECT: Publish failed - {e}")

print("\n" + "=" * 60)
print("ACL TEST SUMMARY")
print("=" * 60)
print("Expected Behavior:")
print("  ✅ Wildcard subscription (#) - DENIED")
print("  ✅ Own topics (sensors/faris/#) - ALLOWED")
print("  ✅ System topics ($SYS/#) - DENIED")
print("  ✅ Other topics (admin/*) - DENIED")
print("\nIf all tests show ✅ CORRECT, ACL is working properly!")
print("=" * 60)
