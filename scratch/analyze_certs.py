import os, re

cert_dir = "/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates"

for filename in sorted(os.listdir(cert_dir)):
    if not filename.endswith(".php") or filename.startswith("."):
        continue
    filepath = os.path.join(cert_dir, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    cal_by_matches = re.findall(r"CALIBRAT[ED|ION]+ BY[^\n'\"]*", content, re.IGNORECASE)
    remarks_matches = re.findall(r"REMARKS[^\n'\"]*", content, re.IGNORECASE)
    
    print(f"=== {filename} ===")
    print(f"  CAL BY matches ({len(cal_by_matches)}): {cal_by_matches}")
    print(f"  REMARKS matches ({len(remarks_matches)}): {remarks_matches}")
