import os
import re

dir_path = r"c:\xampp\htdocs\calibration certificate\certificates"
files = [f for f in os.listdir(dir_path) if f.endswith(".php")]

print(f"Scanning {len(files)} files for missing DOM elements in getFormDetails...")

for file in files:
    path = os.path.join(dir_path, file)
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()
    
    # Extract getFormDetails function body
    match_func = re.search(r'function\s+getFormDetails\s*\(\s*\)\s*\{([\s\S]*?)\}', content)
    if not match_func:
        print(f"{file:30} | NO getFormDetails found")
        continue
        
    func_body = match_func.group(1)
    
    # Find all document.getElementById("...") or '...'
    el_ids = re.findall(r'document\.getElementById\(\s*["\']([^"\']+)["\']\s*\)', func_body)
    
    missing = []
    for el_id in el_ids:
        # Check if id="el_id" or id='el_id' is in the content
        id_pattern = rf'id=["\']{el_id}["\']'
        if not re.search(id_pattern, content):
            # Also check if it's dynamically generated or inside php, but a simple check first
            missing.append(el_id)
            
    if missing:
        print(f"{file:30} | MISSING HTML IDs: {', '.join(missing)}")
    else:
        print(f"{file:30} | OK")
