import os

dir_path = r"c:\xampp\htdocs\calibration certificate\certificates"
files = ["core_cutter.php", "hydrometer.php", "measuring_cyl.php", "pycnometer.php", "sand_pouring.php", "vernier_caliper.php"]

for file in files:
    path = os.path.join(dir_path, file)
    if not os.path.exists(path):
        print(f"Skipping {file}: not found.")
        continue
    
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()
    
    fixed = content
    fixed = fixed.replace('id="partyname"', 'id="partyName"')
    fixed = fixed.replace("id='partyname'", "id='partyName'")
    fixed = fixed.replace('for="partyname"', 'for="partyName"')
    fixed = fixed.replace("for='partyname'", "for='partyName'")
    fixed = fixed.replace('partyname:', 'partyName:')
    fixed = fixed.replace('details.partyname', 'details.partyName')
    fixed = fixed.replace('getElementById("partyname")', 'getElementById("partyName")')
    fixed = fixed.replace("getElementById('partyname')", "getElementById('partyName')")
    
    if fixed != content:
        with open(path, "w", encoding="utf-8") as f:
            f.write(fixed)
        print(f"Standardized {file}!")
    else:
        print(f"No changes needed for {file}.")
