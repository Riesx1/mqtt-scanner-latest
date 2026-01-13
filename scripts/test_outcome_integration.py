"""
Test script to verify outcome data flows from Flask API to Laravel
"""
import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'mqtt-scanner'))

from scanner import scan_ip

print("=" * 80)
print("TESTING OUTCOME DATA INTEGRATION")
print("=" * 80)

# Test unreachable IP
test_ip = "192.168.100.254"
test_ports = [1883, 8883]

print(f"\n📡 Scanning {test_ip} on ports {test_ports}...")
print("This should produce 'Connection Timeout' or 'Network Unreachable' outcomes\n")

results = scan_ip(test_ip)

for i, result in enumerate(results, 1):
    print(f"\n{'='*80}")
    print(f"RESULT {i}: {result['ip']}:{result['port']}")
    print(f"{'='*80}")
    print(f"Classification: {result.get('classification', 'N/A')}")

    if 'outcome' in result:
        outcome = result['outcome']
        print(f"\n🎯 OUTCOME DATA:")
        print(f"  Label: {outcome.get('label', 'N/A')}")
        print(f"  Meaning: {outcome.get('meaning', 'N/A')}")
        print(f"  Evidence Signal: {outcome.get('evidence_signal', 'N/A')}")
        print(f"  Security Implication: {outcome.get('security_implication', 'N/A')}")

        print(f"\n🚨 ERROR EVIDENCE:")
        print(f"  {outcome.get('evidence_signal', 'No error captured')}")
    else:
        print("\n❌ WARNING: No 'outcome' field in result!")
        print("Available keys:", list(result.keys()))

print("\n" + "=" * 80)
print("✅ Test complete! If you see outcome data above, it's working correctly.")
print("=" * 80)
