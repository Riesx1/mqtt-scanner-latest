# How to Prove Port Unreachable - Complete Guide

## Overview

To prove a port is unreachable, you need to demonstrate that:

1. The host doesn't respond to connection attempts
2. The connection times out (no response)
3. Network diagnostics confirm the host is offline/unreachable

---

## Method 1: Network Diagnostics (Fastest Proof)

### Step 1: Ping Test

```powershell
# Test if host is reachable at all
ping 192.168.100.254

# Or for public IP
ping 8.8.8.8
```

**Expected Results:**

-   **Unreachable IP (192.168.100.254):**

    ```
    Ping request could not find host 192.168.100.254. Please check the name and try again.
    OR
    Request timed out. (x4)
    ```

-   **Reachable IP (8.8.8.8):**
    ```
    Reply from 8.8.8.8: bytes=32 time=10ms TTL=117
    ```

### Step 2: Test MQTT Port Specifically

```powershell
# Test if MQTT port 1883 is reachable
Test-NetConnection -ComputerName 192.168.100.254 -Port 1883

# Test Google DNS on MQTT port (should timeout)
Test-NetConnection -ComputerName 8.8.8.8 -Port 1883
```

**Expected Results for Unreachable:**

```
ComputerName     : 192.168.100.254
RemoteAddress    : 192.168.100.254
RemotePort       : 1883
InterfaceAlias   : Wi-Fi
SourceAddress    : 192.168.100.57
TcpTestSucceeded : False
```

---

## Method 2: Visual Proof with Scanner

### Create Visual Proof Script

Create: `prove_unreachable.py`

```python
#!/usr/bin/env python3
"""
Visual proof that an IP is unreachable
Shows detailed timing and error information
"""

import time
import socket
from scanner import run_scan, TIMEOUT
import json

def test_with_timing(ip, description):
    """Test an IP and show detailed timing"""
    print("\n" + "="*70)
    print(f"Testing: {description}")
    print(f"Target IP: {ip}")
    print("="*70)

    # Manual socket test with timing
    print("\n[Step 1] Testing TCP connection to port 1883...")
    start_time = time.time()

    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.settimeout(TIMEOUT)
        print(f"⏱️  Timeout set to: {TIMEOUT} seconds")
        print("⏳ Attempting connection...")

        s.connect((ip, 1883))
        elapsed = time.time() - start_time
        print(f"✅ Connected in {elapsed:.2f} seconds")
        s.close()

    except socket.timeout:
        elapsed = time.time() - start_time
        print(f"⏰ TIMEOUT after {elapsed:.2f} seconds")
        print("❌ No response from host - PORT UNREACHABLE")

    except ConnectionRefusedError:
        elapsed = time.time() - start_time
        print(f"🚫 Connection refused after {elapsed:.2f} seconds")
        print("❌ Host responded but port closed - PORT CLOSED")

    except OSError as e:
        elapsed = time.time() - start_time
        print(f"❌ Network error after {elapsed:.2f} seconds: {e}")
        print("❌ Network unreachable or host offline")

    # Run scanner test
    print("\n[Step 2] Running MQTT Scanner...")
    scan_start = time.time()
    results = run_scan(ip)
    scan_elapsed = time.time() - scan_start

    print(f"\n⏱️  Total scan time: {scan_elapsed:.2f} seconds")
    print(f"📊 Results found: {len(results)}")

    # Display results
    for result in results:
        print(f"\n--- Port {result['port']} ---")
        outcome = result.get('outcome', {})
        print(f"Outcome: {outcome.get('label', 'N/A')}")
        print(f"Meaning: {outcome.get('meaning', 'N/A')}")
        print(f"Classification: {result.get('classification', 'N/A')}")
        print(f"Result: {result.get('result', 'N/A')}")

    # Full JSON output
    print("\n[Step 3] Full JSON Output:")
    print(json.dumps(results, indent=2, default=str))

    return results

def main():
    print("\n╔════════════════════════════════════════════════════════════════╗")
    print("║        PROOF OF UNREACHABLE PORT - DIAGNOSTIC TEST           ║")
    print("╚════════════════════════════════════════════════════════════════╝")

    # Test 1: Non-existent IP in local subnet
    test_with_timing(
        "192.168.100.254",
        "Non-existent IP in local subnet (likely no device)"
    )

    # Test 2: Public IP that blocks MQTT
    print("\n" + "="*70)
    input("Press Enter to test Google DNS (8.8.8.8)...")
    test_with_timing(
        "8.8.8.8",
        "Google Public DNS (blocks MQTT ports)"
    )

    # Test 3: Compare with reachable IP
    print("\n" + "="*70)
    input("Press Enter to compare with REACHABLE IP (127.0.0.1)...")
    test_with_timing(
        "127.0.0.1",
        "Localhost (should be reachable)"
    )

    print("\n" + "="*70)
    print("PROOF COMPLETE")
    print("="*70)

if __name__ == '__main__':
    main()
```

**Run it:**

```bash
cd mqtt-scanner
python prove_unreachable.py
```

---

## Method 3: Side-by-Side Comparison

### Compare Reachable vs Unreachable

```powershell
# Create comparison script
cd mqtt-scanner
```

Create: `compare_reachable_vs_unreachable.py`

```python
from scanner import run_scan
import json
import time

print("\n" + "="*80)
print("COMPARISON: REACHABLE vs UNREACHABLE")
print("="*80)

# Test 1: Reachable (your broker)
print("\n[TEST 1] REACHABLE IP: 127.0.0.1")
print("-" * 80)
start = time.time()
reachable_results = run_scan('127.0.0.1')
reachable_time = time.time() - start

print(f"\n✅ Scan completed in {reachable_time:.2f} seconds")
for r in reachable_results:
    print(f"  Port {r['port']}: {r.get('outcome', {}).get('label', r.get('classification'))}")

# Test 2: Unreachable
print("\n[TEST 2] UNREACHABLE IP: 192.168.100.254")
print("-" * 80)
start = time.time()
unreachable_results = run_scan('192.168.100.254')
unreachable_time = time.time() - start

print(f"\n❌ Scan completed in {unreachable_time:.2f} seconds")
for r in unreachable_results:
    print(f"  Port {r['port']}: {r.get('outcome', {}).get('label', r.get('classification'))}")

# Summary
print("\n" + "="*80)
print("SUMMARY - PROOF OF UNREACHABLE")
print("="*80)
print(f"Reachable IP (127.0.0.1):    ✅ Connected in ~{reachable_time:.1f}s")
print(f"Unreachable IP (192.168.100.254): ❌ Timeout in ~{unreachable_time:.1f}s")
print(f"\nTime difference: {unreachable_time - reachable_time:.1f}s longer (due to timeout)")
```

---

## Method 4: Network Trace Proof

### Step-by-Step Network Diagnostics

```powershell
# 1. Check your own IP
ipconfig | findstr IPv4

# 2. Check if target is in ARP table (local network)
arp -a | findstr 192.168.100.254

# 3. Trace route to target
tracert -h 5 192.168.100.254

# 4. Test specific port
Test-NetConnection -ComputerName 192.168.100.254 -Port 1883 -InformationLevel Detailed

# 5. Show all network connections (your active connections)
netstat -an | findstr 1883
```

---

## Method 5: Screenshot Evidence

### Create Visual Proof Document

1. **Screenshot 1: Ping Test**

    ```powershell
    ping 192.168.100.254
    ```

    Shows: "Request timed out" or "Destination host unreachable"

2. **Screenshot 2: Port Test**

    ```powershell
    Test-NetConnection -ComputerName 192.168.100.254 -Port 1883
    ```

    Shows: `TcpTestSucceeded : False`

3. **Screenshot 3: Scanner Output**

    ```bash
    python -c "from scanner import run_scan; import json; print(json.dumps(run_scan('192.168.100.254'), indent=2))"
    ```

    Shows: `"label": "Unreachable / Timeout"`

4. **Screenshot 4: Compare with Working**
    ```bash
    python -c "from scanner import run_scan; import json; print(json.dumps(run_scan('127.0.0.1'), indent=2))"
    ```
    Shows: `"label": "Connected (1883)"` - proves scanner works!

---

## Method 6: Log File Evidence

Create automated proof log:

```python
# save_proof.py
from scanner import run_scan
import json
from datetime import datetime

def create_proof_log():
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    filename = f"unreachable_proof_{timestamp}.txt"

    with open(filename, 'w') as f:
        f.write("="*80 + "\n")
        f.write("PROOF OF UNREACHABLE PORT - AUTOMATED LOG\n")
        f.write(f"Generated: {datetime.now().isoformat()}\n")
        f.write("="*80 + "\n\n")

        # Test unreachable
        f.write("TEST: Unreachable IP (192.168.100.254)\n")
        f.write("-"*80 + "\n")
        results = run_scan('192.168.100.254')
        f.write(json.dumps(results, indent=2, default=str))
        f.write("\n\n")

        # Test reachable for comparison
        f.write("COMPARISON: Reachable IP (127.0.0.1)\n")
        f.write("-"*80 + "\n")
        results2 = run_scan('127.0.0.1')
        f.write(json.dumps(results2, indent=2, default=str))
        f.write("\n\n")

        f.write("="*80 + "\n")
        f.write("END OF PROOF LOG\n")

    print(f"✅ Proof saved to: {filename}")
    return filename

if __name__ == '__main__':
    create_proof_log()
```

---

## Quick Proof Commands (Copy-Paste)

```powershell
# 1. Quick ping test (will fail for unreachable)
ping -n 2 192.168.100.254

# 2. Quick port test (will show False for unreachable)
Test-NetConnection -ComputerName 192.168.100.254 -Port 1883

# 3. Quick scanner test
cd mqtt-scanner
python -c "from scanner import run_scan; r=run_scan('192.168.100.254'); print(f'Port 1883: {r[0].get(\"outcome\",{}).get(\"label\",\"N/A\")}')"
```

---

## Expected Evidence for "Unreachable"

✅ **Ping fails:** Request timed out  
✅ **Port test fails:** TcpTestSucceeded = False  
✅ **Scanner shows:** "Unreachable / Timeout"  
✅ **Takes 2+ seconds:** Due to timeout waiting  
✅ **No ARP entry:** Host not in local network table

---

## Example Proof Report

**Target:** 192.168.100.254  
**Date:** January 13, 2026  
**Tester:** [Your Name]

**Evidence:**

1. ❌ Ping test: 100% packet loss (4/4 timeouts)
2. ❌ Port 1883: Connection timeout after 2.0 seconds
3. ❌ Port 8883: Connection timeout after 2.0 seconds
4. ❌ ARP table: No entry for 192.168.100.254
5. ✅ Scanner outcome: "Unreachable / Timeout"
6. ✅ Comparison test: 127.0.0.1 connected successfully (proves scanner works)

**Conclusion:** Port is proven unreachable. Network diagnostics confirm no host responds at IP 192.168.100.254.
