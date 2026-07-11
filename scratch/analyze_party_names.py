import os
import re

dir_path = r"c:\xampp\htdocs\calibration certificate\certificates"
files = [f for f in os.listdir(dir_path) if f.endswith(".php")]

print(f"Scanning {len(files)} files...")

for file in files:
    path = os.path.join(dir_path, file)
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()
    
    # Let's find getFormDetails function
    match_func = re.search(r'function\s+getFormDetails\s*\(\s*\)\s*\{([\s\S]*?)\}', content)
    if match_func:
        func_body = match_func.group(1)
        # Find any keys inside the returned object (e.g. partyName: ... or partyname: ...)
        keys = re.findall(r'(\w+)\s*:', func_body)
        # Find input element IDs
        input_ids = re.findall(r'document\.getElementById\(\s*["\'](\w+)["\']\)', func_body)
        
        # Check if partyName is a key
        has_partyName_key = "partyName" in keys
        has_partyname_key = "partyname" in keys
        
        print(f"{file:30} | Keys: {', '.join(keys)} | Inputs: {', '.join(input_ids)}")
    else:
        print(f"{file:30} | NO getFormDetails found!")
