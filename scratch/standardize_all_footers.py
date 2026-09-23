import glob
import os
import re

cert_dir = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates'
files = sorted(glob.glob(os.path.join(cert_dir, '*.php')))

for fpath in files:
    fname = os.path.basename(fpath)
    if fname == 'sieves.php': continue

    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Determine custom notes if any
    extra_notes = ""
    if fname == 'weight_balance.php':
        extra_notes = '      doc.text("• NOTE: Uncertainty is calculated at a confidence level of 95% (k=2).", 14, endY += 4);\n'
    elif fname == 'rapid_moisture.php':
        extra_notes = '      doc.text("• NOTE: This calibration report refers to \'Oven Drying Method\'.", 14, endY += 4);\n'

    # Build the dynamic replacement snippet
    dynamic_footer = f"""      let endY = (doc.lastAutoTable && doc.lastAutoTable.finalY) ? doc.lastAutoTable.finalY : ((doc.autoTable && doc.autoTable.previous && doc.autoTable.previous.finalY) ? doc.autoTable.previous.finalY : (typeof tableStartY2 !== 'undefined' ? tableStartY2 : (typeof finalY !== 'undefined' ? finalY : (typeof Yalign !== 'undefined' ? Yalign : (typeof hori_axis !== 'undefined' ? hori_axis : 160)))));
      doc.setFont("helvetica", "bold");
      doc.setFontSize(9.5);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, endY += 8);
      doc.setFontSize(8.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 5);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 4);
{extra_notes}
      let sigY = Math.max(endY + 15, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);
"""

    # We want to replace from CALIBRATED BY or doc.text("FOR, ... up to PROPRIETOR line
    # Match pattern starting from CALIBRATED BY or previous tableStartY2 / finalY calculation down to PROPRIETOR
    pattern = r'(\s*(?:let tableStartY2 =.*?\n|let finalY =.*?\n|let endY =.*?\n|doc\.text\(\s*["\']CALIBRATED BY[^"\']*["\'].*?\n)?(?:\s*doc\.setFont[^\n]*\n)*(?:\s*doc\.setFontSize[^\n]*\n)*(?:\s*doc\.text\(\s*["\']• REMARKS:[^\n]*\n)*(?:\s*doc\.text\(\s*["\']• This certificate refers[^\n]*\n)*(?:\s*doc\.text\(\s*["\']• NOTE:[^\n]*\n)*\s*let sigY =.*?\n)?\s*doc\.text\(\s*["\']FOR,\s*["\']\s*\+\s*\(?window\.PDF_COMPANY_NAME[\s\S]*?doc\.text\(\s*["\']PROPRIETOR["\'][^\n]*\);)'

    m = re.search(pattern, content)
    if m:
        content_new = content.replace(m.group(1), dynamic_footer, 1)
        with open(fpath, 'w', encoding='utf-8') as f:
            f.write(content_new)
        print(f"Standardized {fname}")
    else:
        # Try simpler pattern matching from doc.text("FOR, ...
        p_simple = r'(\s*(?:doc\.setFontSize[^\n]*\n)?\s*(?:doc\.setFont[^\n]*\n)?\s*(?:doc\.setFontSize[^\n]*\n)?\s*(?:doc\.text\(\s*["\']• REMARKS:[^\n]*\n)*(?:\s*doc\.text\(\s*["\']• This certificate refers[^\n]*\n)*\s*doc\.text\(\s*["\']FOR,\s*["\']\s*\+\s*\(?window\.PDF_COMPANY_NAME[\s\S]*?doc\.text\(\s*["\']PROPRIETOR["\'][^\n]*\);)'
        m_simple = re.search(p_simple, content)
        if m_simple:
            content_new = content.replace(m_simple.group(1), dynamic_footer, 1)
            with open(fpath, 'w', encoding='utf-8') as f:
                f.write(content_new)
            print(f"Standardized {fname} (simple pattern)")
        else:
            print(f"WARNING: Could not match footer in {fname}")

print("Batch footer standardization done.")
