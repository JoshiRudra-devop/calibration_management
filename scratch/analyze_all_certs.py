import glob
import os
import re

cert_dir = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates'
files = sorted(glob.glob(os.path.join(cert_dir, '*.php')))

print(f"Total certificate files found: {len(files)}\n")

results = []

for fpath in files:
    fname = os.path.basename(fpath)
    with open(fpath, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()

    # Find name of instrument from file or header/title
    title_match = re.search(r'<h2>([^<]+)</h2>', content)
    inst_name = title_match.group(1).strip() if title_match else fname.replace('.php', '').replace('_', ' ').title()

    # Check for serialNo input element
    has_serial_input = bool(re.search(r'id=["\']serialNo["\']', content))

    # Collect lines in JS that output "SERIAL NO" or "SR NO"
    pdf_lines = []
    for line in content.splitlines():
        if 'doc.text' in line and ('SERIAL NO' in line or 'SR NO' in line or 'SERIAL' in line):
            pdf_lines.append(line.strip())

    # Check JS object mapping
    get_details_serial = ""
    m_js = re.search(r'serialNo\s*:\s*([^\n,]+)', content)
    if m_js:
        get_details_serial = m_js.group(1).strip()

    # Determine behavior
    uses_cert_as_serial = False
    uses_own_serial = False
    notes = ""

    for pl in pdf_lines:
        if 'details.certificateNumber' in pl:
            uses_cert_as_serial = True
        elif 'details.serialNo' in pl or 'serialNo' in pl or 'masterSerial' in pl:
            uses_own_serial = True

    if 'certificateNumber' in get_details_serial:
        uses_cert_as_serial = True

    category = "UNKNOWN"
    if uses_cert_as_serial and not has_serial_input:
        category = "CERT_NO_AS_SERIAL_NO"
    elif has_serial_input or (uses_own_serial and not uses_cert_as_serial):
        category = "DEDICATED_SERIAL_NO"
    elif 'cube' in fname or 'isi' in fname or 'sieves' in fname:
        category = "MULTIPLE_OR_MASTER_SERIALS"
        if 'isi_cube' in fname:
            notes = "Multiple cube serial numbers (Cube #1, #2, etc.)"
        elif 'cloud_cube' in fname or 'cube_mould' in fname:
            notes = "Multiple cube serial numbers generated per cube quantity"
        elif 'sieves' in fname:
            notes = "Master Sieve serial used for certificate header"
    elif uses_cert_as_serial and has_serial_input:
        category = "HYBRID_CERT_AS_SERIAL"
    else:
        category = "OTHER"

    results.append({
        'filename': fname,
        'instrument_name': inst_name,
        'has_serial_input': has_serial_input,
        'get_details_serial': get_details_serial,
        'pdf_lines': pdf_lines,
        'category': category,
        'notes': notes
    })

print(f"{'FILENAME':<22} | {'CATEGORY':<26} | {'SERIAL INPUT?':<13} | {'PDF SERIAL EXPR'}")
print("-" * 100)

for r in results:
    pdf_str = r['pdf_lines'][0] if r['pdf_lines'] else "None"
    if len(pdf_str) > 50:
        pdf_str = pdf_str[:47] + "..."
    print(f"{r['filename']:<22} | {r['category']:<26} | {str(r['has_serial_input']):<13} | {pdf_str}")
