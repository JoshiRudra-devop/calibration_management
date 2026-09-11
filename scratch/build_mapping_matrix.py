import fitz
import json

doc_b = fitz.open('/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/STANDARDIZED_CERTIFICATES_PROTOTYPE_MODIFIED.pdf')

b_pages = []

for i in range(len(doc_b)):
    page = doc_b[i]
    text = page.get_text()
    lines = [l.strip() for l in text.split('\n') if l.strip()]
    
    eq_name = ""
    ref_no = ""
    title = ""
    cap_make = ""
    serial = ""
    
    for line in lines:
        if 'EQUIPMENT NAME' in line:
            eq_name = line.split(':-')[-1].strip() if ':-' in line else line
        if 'REF NO' in line:
            ref_no = line.split(':-')[-1].strip() if ':-' in line else line
        if 'CAPACITY & MAKE' in line:
            cap_make = line.split(':-')[-1].strip() if ':-' in line else line
        if 'SERIAL NO' in line:
            serial = line.split(':-')[-1].strip() if ':-' in line else line
        if any(h in line for h in ['CALIBRATION CERTIFICATE', 'TEST REPORT', 'SLUMCONE', 'TEST SEIVES']):
            if not title: title = line
            
    # Collect table lines
    table_snippet = []
    found_table = False
    for line in lines:
        if any(w in line for w in ['PARAMETER', 'NOMINAL', 'ACTUAL', 'SET TEMP', 'APPLIED', 'SR NO', 'OBSERVED', 'ERROR', 'STATUS', 'RESULT']):
            found_table = True
        if found_table and not any(w in line for w in ['CALIBRATED BY', 'REMARKS', 'DETAILS OF MASTER', 'PROPRIETOR']):
            table_snippet.append(line)
            
    b_pages.append({
        'page': i + 1,
        'eq_name': eq_name,
        'ref_no': ref_no,
        'title': title,
        'cap_make': cap_make,
        'serial': serial,
        'table_lines': table_snippet[:15]
    })

print(f"Loaded {len(b_pages)} pages from FILE B")

for b in b_pages:
    print(f"Page {b['page']:2d} | Ref: {b['ref_no']:10s} | Eq: {b['eq_name']}")
    print(f"   Table: {b['table_lines'][:4]}")
