import os, re

cert_dir = "/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates"

for filename in sorted(os.listdir(cert_dir)):
    if not filename.endswith(".php") or filename.startswith("."):
        continue
    filepath = os.path.join(cert_dir, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Extract addCertificateDetails body
    match = re.search(r"window\.addCertificateDetails\s*=\s*function\s*\((.*?)\)\s*\{(.*?)\n\}", content, re.DOTALL)
    if not match:
        match = re.search(r"function\addCertificateDetails\s*\((.*?)\)\s*\{(.*?)\n\}", content, re.DOTALL)
    
    body = match.group(2) if match else "NOT FOUND"
    
    has_meta_cal_by = "CALIBRATION BY" in body or "CALIBRATED BY" in body[:len(body)//2]
    has_footer_cal_by = "CALIBRATED BY: YOGESH B JOSHI" in body
    
    print(f"=== {filename} ===")
    lines = [line.strip() for line in body.split("\n") if "CALIBRAT" in line or "REMARKS" in line or "endY" in line or "Yalign" in line or "tableStartY" in line]
    for l in lines:
        print("   ", l[:120])
