import os, re

cert_dir = "/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates"

for filename in sorted(os.listdir(cert_dir)):
    if not filename.endswith(".php") or filename.startswith("."):
        continue
    filepath = os.path.join(cert_dir, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Check if doc.autoTable exists in file
    has_autotable = "doc.autoTable" in content or "autoTable(" in content
    
    # Check how endY is calculated
    endY_matches = re.findall(r"let endY = [^\n;]*", content)
    
    print(f"=== {filename} (Has autoTable: {has_autotable}) ===")
    for m in endY_matches:
        print("   ", m)
