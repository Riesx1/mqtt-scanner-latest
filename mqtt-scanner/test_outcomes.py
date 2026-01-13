#!/usr/bin/env python3
"""
Test script to verify outcome labels are working correctly
"""

from scanner import run_scan
import json

print("Testing MQTT Scanner Outcome Labels")
print("=" * 60)

# Test scanning localhost
print("\n1. Scanning localhost (should detect running brokers)...")
results = run_scan('127.0.0.1', creds={'user': 'faris02@gmail.com', 'pass': 'Faris02!'})

print(f"\nFound {len(results)} results:\n")

for i, result in enumerate(results, 1):
    print(f"\n--- Result {i} ---")
    print(f"IP: {result['ip']}")
    print(f"Port: {result['port']}")

    if 'outcome' in result:
        outcome = result['outcome']
        print(f"\n✓ OUTCOME INFORMATION:")
        print(f"  Label: {outcome['label']}")
        print(f"  Meaning: {outcome['meaning']}")
        print(f"  Evidence: {outcome['evidence_signal']}")
        print(f"  Security Implication: {outcome['security_implication']}")
    else:
        print(f"\n⚠ No outcome information (fallback to classification)")
        print(f"  Result: {result.get('result', 'N/A')}")
        print(f"  Classification: {result.get('classification', 'N/A')}")

print("\n" + "=" * 60)
print("\nFull JSON output:")
print(json.dumps(results, indent=2, default=str))
