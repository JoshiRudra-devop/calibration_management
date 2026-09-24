import glob
import os
import re

cert_dir = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates'
files = sorted(glob.glob(os.path.join(cert_dir, '*.php')))

for fpath in files:
    fname = os.path.basename(fpath)
    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Pattern for duplicate let endY lines back-to-back:
    # Matches:
    # let endY = ...
    # doc.setFont("helvetica", "bold");
    # let endY = ...
    pattern = r'(\s*let\s+endY\s*=\s*\([^;\n]+\);?\s*(?:doc\.setFont\([^;\n]+\);?\s*)*(?:doc\.setFontSize\([^;\n]+\);?\s*)*)(let\s+endY\s*=\s*\([^;\n]+\);?)'
    
    if re.search(pattern, content):
        content = re.sub(pattern, r'\1', content)
        with open(fpath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Fixed duplicate endY in {fname}")

print("Syntax fix script complete.")
