import glob
import os
import re

cert_dir = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates'
files = sorted(glob.glob(os.path.join(cert_dir, '*.php')))

remarks_code = '''      doc.setFont("helvetica", "bold");
      doc.setFontSize(8.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, 212);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, 217);
'''

for fpath in files:
    fname = os.path.basename(fpath)
    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()

    if 'This certificate is valid for 12 months' in content:
        print(f"Skipping {fname} (already has remarks)")
        continue

    # Search for FOR, ... PDF_COMPANY_NAME or PROPRIETOR in addCertificateDetails
    # We want to place remarks right before FOR, PDF_COMPANY_NAME
    pattern = r'(\n\s*doc\.text\(\s*["\']FOR,\s*["\']\s*\+\s*\(?window\.PDF_COMPANY_NAME|\n\s*doc\.text\(\s*["\']FOR,\s*["\']\s*\+\s*PDF_COMPANY_NAME)'
    
    m = re.search(pattern, content)
    if m:
        content = content.replace(m.group(0), '\n' + remarks_code + m.group(0), 1)
        with open(fpath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {fname} with REMARKS")
    else:
        # Fallback: search for PROPRIETOR
        p_prop = r'(\n\s*doc\.text\(\s*["\']PROPRIETOR["\'])'
        m2 = re.search(p_prop, content)
        if m2:
            content = content.replace(m2.group(0), '\n' + remarks_code + m2.group(0), 1)
            with open(fpath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated {fname} with REMARKS (fallback proprietor)")
        else:
            print(f"WARNING: Could not find footer anchor in {fname}")

print("Done processing certificates.")
