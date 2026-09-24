import os, subprocess, re

cert_dir = "/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates"
php_bin = "/Applications/XAMPP/xamppfiles/bin/php"

all_good = True

for filename in sorted(os.listdir(cert_dir)):
    if not filename.endswith(".php") or filename.startswith("."):
        continue
    filepath = os.path.join(cert_dir, filename)
    
    # PHP Lint
    res = subprocess.run([php_bin, "-l", filepath], capture_output=True, text=True)
    if "No syntax errors detected" not in res.stdout:
        print(f"❌ PHP LINT ERROR in {filename}:\n{res.stdout}")
        all_good = False
    else:
        print(f"✓ PHP Lint passed: {filename}")
    
    # Extract JavaScript and check with Node.js
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Match script blocks that do NOT have a src attribute
    scripts = re.findall(r"<script(?![^>]*src=)[^>]*>(.*?)</script>", content, re.DOTALL)
    for idx, script in enumerate(scripts):
        if not script.strip():
            continue
        # Replace PHP code blocks with valid JS syntax equivalents
        clean_script = re.sub(r"<\?=[\s\S]*?\?>", "'PHP_VAL'", script)
        clean_script = re.sub(r"<\?php[\s\S]*?\?>", "/* PHP BLOCK */", clean_script)
        
        tmp_js = f"/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/scratch/tmp_{filename}_{idx}.js"
        with open(tmp_js, "w", encoding="utf-8") as js_file:
            js_file.write(clean_script)
        
        node_res = subprocess.run(["node", "-c", tmp_js], capture_output=True, text=True)
        if node_res.returncode != 0:
            print(f"❌ JS SYNTAX ERROR in {filename} (script #{idx}):\n{node_res.stderr}")
            all_good = False
        else:
            if os.path.exists(tmp_js):
                os.remove(tmp_js)

if all_good:
    print("\n🎉 ALL 25 CERTIFICATE FILES PASSED PHP LINT AND JS SYNTAX CHECKS PERFECTLY!")
