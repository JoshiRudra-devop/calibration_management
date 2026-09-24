import os, re

cert_dir = "/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates"

reports = []

for filename in sorted(os.listdir(cert_dir)):
    if not filename.endswith(".php") or filename.startswith("."):
        continue
    filepath = os.path.join(cert_dir, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Find addCertificateDetails function block
    start_idx = content.find("addCertificateDetails")
    if start_idx == -1:
        reports.append(f"=== {filename} === (NO addCertificateDetails)")
        continue
    
    # Get text from addCertificateDetails to end of script
    code = content[start_idx:start_idx+4000]
    
    cal_bys = re.findall(r"CALIBRAT(?:ED|ION)\s*BY[^\n'\"]*", code, re.IGNORECASE)
    rem_font_sizes = re.findall(r"setFontSize\((\d+(?:\.\d+)?)\)", code)
    endY_lines = [line.strip() for line in code.split("\n") if "endY" in line or "afterTableY" in line]
    
    reports.append(f"=== {filename} ===")
    reports.append(f"  CAL BY matches ({len(cal_bys)}): {cal_bys}")
    reports.append(f"  endY line(s): {endY_lines}")

with open("/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/scratch/audit_summary.txt", "w", encoding="utf-8") as f:
    f.write("\n".join(reports))

print("Done")
