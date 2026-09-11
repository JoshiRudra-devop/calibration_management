import fitz
import json

doc_b = fitz.open('/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/STANDARDIZED_CERTIFICATES_PROTOTYPE_MODIFIED.pdf')

pages_b = []

for page_num in range(len(doc_b)):
    page = doc_b[page_num]
    text = page.get_text()
    lines = [l.strip() for l in text.split('\n') if l.strip()]
    
    cert_no = f"Page {page_num + 1}"
    eq_name = ""
    cert_type = ""
    ref_no = ""
    party = ""
    make_cap = ""
    serial = ""
    due_date = ""
    site = ""
    
    # Identify fields
    table_lines = []
    in_table = False
    
    for i, line in enumerate(lines):
        if any(h in line.upper() for h in ['TEST REPORT', 'CALIBRATION CERTIFICATE', 'TEST SEIVES', 'SLUMCONE', 'LABORATORY CALIBRATION REPORT']):
            if not cert_type:
                cert_type = line
        if 'EQUIPMENT NAME' in line:
            eq_name = line.split(':-')[-1].strip() if ':-' in line else line
        if 'REF NO' in line:
            ref_no = line.split(':-')[-1].strip() if ':-' in line else line
        if 'NAME OF PARTY' in line:
            party = line.split(':-')[-1].strip() if ':-' in line else line
        if 'CAPACITY & MAKE' in line:
            make_cap = line.split(':-')[-1].strip() if ':-' in line else line
        if 'SERIAL NO' in line:
            serial = line.split(':-')[-1].strip() if ':-' in line else line
            
    pages_b.append({
        'page': page_num + 1,
        'title': cert_type,
        'ref_no': ref_no,
        'equipment_name': eq_name,
        'make_cap': make_cap,
        'serial': serial,
        'all_lines': lines
    })

print(f"Parsed {len(pages_b)} pages of FILE B")
with open('scratch/file_b_parsed.json', 'w') as f:
    json.dump(pages_b, f, indent=2)
