import os
import re

dir_path = r"c:\xampp\htdocs\calibration certificate\certificates"
files = [f for f in os.listdir(dir_path) if f.endswith(".php")]

print(f"Scanning {len(files)} files for drawing functions...")

for file in files:
    path = os.path.join(dir_path, file)
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()
    
    has_details = "function addCertificateDetails" in content
    has_addImg = "function addImg" in content
    
    # Check if they exist under slightly different names or declarations
    # e.g., window.addCertificateDetails = ... or function addImg(doc)
    if not has_details:
        has_details = re.search(r'addCertificateDetails\s*=', content) is not None
    if not has_addImg:
        has_addImg = re.search(r'addImg\s*=', content) is not None
        
    status = "OK"
    if not has_details or not has_addImg:
        status = f"MISSING: {'addCertificateDetails' if not has_details else ''} {'addImg' if not has_addImg else ''}"
        
    print(f"{file:30} | {status}")
