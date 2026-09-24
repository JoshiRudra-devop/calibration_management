import os, re

cert_dir = "/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates"

for filename in sorted(os.listdir(cert_dir)):
    if not filename.endswith(".php") or filename.startswith("."):
        continue
    filepath = os.path.join(cert_dir, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    print(f"==================== {filename} ====================")
    # Search from addCertificateDetails to end of script tag
    start_pos = content.find("addCertificateDetails")
    if start_pos != -1:
        end_pos = content.find("</script>", start_pos)
        if end_pos != -1:
            print(content[start_pos:end_pos])
        else:
            print(content[start_pos:start_pos+1500])
    else:
        print("NO addCertificateDetails FUNCTION!")
