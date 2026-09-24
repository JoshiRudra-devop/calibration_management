import os, re

cert_dir = "/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates"

for filename in sorted(os.listdir(cert_dir)):
    if not filename.endswith(".php") or filename.startswith("."):
        continue
    filepath = os.path.join(cert_dir, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    print(f"==================== {filename} ====================")
    match = re.search(r"(window\.addCertificateDetails\s*=\s*function.*?\n\s*\})", content, re.DOTALL)
    if match:
        print(match.group(1))
    else:
        print("NO addCertificateDetails FUNCTION FOUND!")
