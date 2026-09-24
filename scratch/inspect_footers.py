import os, re

cert_dir = "/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates"

for filename in sorted(os.listdir(cert_dir)):
    if not filename.endswith(".php") or filename.startswith("."):
        continue
    filepath = os.path.join(cert_dir, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    fn_start = content.find("addCertificateDetails")
    if fn_start == -1:
        continue
    
    fn_end = content.find("</script>", fn_start)
    fn_code = content[fn_start:fn_end]
    
    print(f"=== {filename} ===")
    # Print the last 15 lines of addCertificateDetails
    lines = fn_code.split("\n")
    # Find where endY or footer starts
    footer_lines = []
    capture = False
    for line in lines:
        if "endY" in line or "CALIBRATED BY" in line or "REMARKS" in line or "PROPRIETOR" in line or "sigY" in line:
            footer_lines.append(line.strip())
    print("\n".join(footer_lines))
