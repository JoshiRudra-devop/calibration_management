import glob
import os

cert_dir = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates'
files = sorted(glob.glob(os.path.join(cert_dir, '*.php')))

for fpath in files:
    fname = os.path.basename(fpath)
    if fname == 'sieves.php': continue

    with open(fpath, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    # Find the index of line with FOR, window.PDF_COMPANY_NAME or PROPRIETOR inside addCertificateDetails
    for_idx = -1
    for i, line in enumerate(lines):
        if ('FOR,' in line or 'FOR ,' in line) and ('PDF_COMPANY_NAME' in line or 'SHREEJI' in line):
            for_idx = i
            break

    if for_idx == -1:
        print(f"Skipping {fname}: FOR line not found")
        continue

    # Look backwards from for_idx to find where CALIBRATED BY or tableStartY2 or finalY or static remarks start
    start_idx = for_idx
    while start_idx > 0 and (for_idx - start_idx < 12):
        prev_line = lines[start_idx - 1]
        if any(keyword in prev_line for keyword in [
            'CALIBRATED BY', 'tableStartY2', 'finalY', 'REMARKS:', 'This certificate refers', 'NOTE:', 'setFont'
        ]):
            start_idx -= 1
        else:
            break

    # Look forward from for_idx to find PROPRIETOR line
    end_idx = for_idx
    while end_idx < len(lines) - 1 and (end_idx - for_idx < 5):
        if 'PROPRIETOR' in lines[end_idx]:
            end_idx += 1
            break
        end_idx += 1

    extra_notes = ""
    if fname == 'weight_balance.php':
        extra_notes = '      doc.text("• NOTE: Uncertainty is calculated at a confidence level of 95% (k=2).", 14, endY += 4);\n'
    elif fname == 'rapid_moisture.php':
        extra_notes = '      doc.text("• NOTE: This calibration report refers to \'Oven Drying Method\'.", 14, endY += 4);\n'

    dynamic_footer_lines = [
        '      let endY = (doc.lastAutoTable && doc.lastAutoTable.finalY) ? doc.lastAutoTable.finalY : ((doc.autoTable && doc.autoTable.previous && doc.autoTable.previous.finalY) ? doc.autoTable.previous.finalY : (typeof tableStartY2 !== "undefined" ? tableStartY2 : (typeof finalY !== "undefined" ? finalY : (typeof Yalign !== "undefined" ? Yalign : (typeof hori_axis !== "undefined" ? hori_axis : 160)))));\n',
        '      doc.setFont("helvetica", "bold");\n',
        '      doc.setFontSize(9.5);\n',
        '      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, endY += 8);\n',
        '      doc.setFontSize(8.5);\n',
        '      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 5);\n',
        '      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 4);\n',
        extra_notes,
        '      let sigY = Math.max(endY + 15, 225);\n',
        '      doc.setFont("helvetica", "bold");\n',
        '      doc.setFontSize(11);\n',
        '      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);\n',
        '      doc.text("PROPRIETOR", 170, sigY + 15);\n'
    ]
    # Filter empty lines in dynamic_footer_lines
    dynamic_footer_lines = [l for l in dynamic_footer_lines if l.strip() != '']

    # Replace lines[start_idx:end_idx] with dynamic_footer_lines
    new_lines = lines[:start_idx] + dynamic_footer_lines + lines[end_idx:]

    with open(fpath, 'w', encoding='utf-8') as f:
        f.writelines(new_lines)

    print(f"Updated {fname} (lines {start_idx+1}-{end_idx})")

print("Clean footer replacement done.")
