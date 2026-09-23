import glob
import os
import re

cert_dir = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates'
files = sorted(glob.glob(os.path.join(cert_dir, '*.php')))

for fpath in files:
    fname = os.path.basename(fpath)
    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Skip sieves.php footer as it has its own detailed master details table
    if fname == 'sieves.php':
        continue

    # We want to standardize the end of addCertificateDetails function
    # Search for addCertificateDetails block
    m = re.search(r'window\.addCertificateDetails\s*=\s*function[\s\S]*?\{([\s\S]*?)\n\}', content)
    if not m:
        m = re.search(r'function addCertificateDetails[\s\S]*?\{([\s\S]*?)\n\}', content)

    if not m:
        print(f"Skipping {fname}: could not find addCertificateDetails")
        continue

    fn_body = m.group(1)

    # Remove any previous hardcoded static 212/217 REMARKS lines if present
    content_clean = re.sub(r'\s*doc\.text\(\s*["\']• REMARKS: This certificate is valid for 12 months[^"\']*["\']\s*,\s*14\s*,\s*212\s*\);', '', content)
    content_clean = re.sub(r'\s*doc\.text\(\s*["\']• This certificate refers to the value obtained[^"\']*["\']\s*,\s*14\s*,\s*217\s*\);', '', content_clean)

    # Let's inspect how the footer is structured in this file
    # We want to replace CALIBRATED BY / REMARKS / FOR, PDF_COMPANY_NAME block with dynamic block
    
    # Pattern to match from CALIBRATED BY or doc.autoTable.previous.finalY or FOR, PDF_COMPANY_NAME to the end of function
    # Let's check if CALIBRATED BY exists
    
    dynamic_footer = """
      let endY = (doc.lastAutoTable && doc.lastAutoTable.finalY) ? doc.lastAutoTable.finalY : ((doc.autoTable && doc.autoTable.previous && doc.autoTable.previous.finalY) ? doc.autoTable.previous.finalY : (typeof Yalign !== 'undefined' ? Yalign : (typeof hori_axis !== 'undefined' ? hori_axis : 160)));
      doc.setFont("helvetica", "bold");
      doc.setFontSize(9.5);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, endY += 8);
      doc.setFontSize(8.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 5);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 4);

      let sigY = Math.max(endY + 15, 230);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);
"""

    # Check if there is an existing CALIBRATED BY / FOR, block at the end of function
    # Regex matching from CALIBRATED BY or FOR, window.PDF_COMPANY_NAME up to the closing brace of function
    pattern = r'(\s*(?:doc\.text\(\s*["\']CALIBRATED BY[^"\']*["\'].*?|let tableStartY2 =.*?)?\s*doc\.text\(\s*["\']FOR,\s*["\']\s*\+\s*\(?window\.PDF_COMPANY_NAME[\s\S]*?doc\.text\(\s*["\']PROPRIETOR["\'].*?\);)'

    if re.search(pattern, content_clean):
        new_content = re.sub(pattern, dynamic_footer, content_clean, count=1)
        with open(fpath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Updated {fname} with dynamic footer math")
    else:
        print(f"Pattern match failed for {fname}")

print("Batch update done.")
