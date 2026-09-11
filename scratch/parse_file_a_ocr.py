import re
import json

with open('scratch/file_a_ocr_results.txt', 'r', encoding='utf-8') as f:
    raw_ocr = f.read()

pages_raw = raw_ocr.split('=== FILE A PAGE ')

file_a_certs = []

for p_block in pages_raw:
    if not p_block.strip():
        continue
    lines = p_block.split('\n')
    page_num = int(lines[0].split(' ')[0])
    content = '\n'.join(lines[1:])
    
    # Search for equipment name / description of item
    eq_name = ""
    cert_no = ""
    ulr_no = ""
    discipline = ""
    calib_item = ""
    
    for l in lines[1:]:
        l_str = l.strip()
        if 'Calibrated Item' in l_str or 'Description of Item' in l_str or 'Name of Instrument' in l_str or 'Nomenclature' in l_str or 'Name of Equipment' in l_str:
            calib_item = l_str
        if 'Certificate No' in l_str or 'CERTIFICATE NO' in l_str or 'Report No' in l_str:
            if not cert_no: cert_no = l_str
        if 'ULR No' in l_str:
            if not ulr_no: ulr_no = l_str
        if 'Discipline' in l_str:
            if not discipline: discipline = l_str
            
    file_a_certs.append({
        'page': page_num,
        'calib_item': calib_item,
        'cert_no': cert_no,
        'ulr_no': ulr_no,
        'discipline': discipline,
        'first_lines': [l.strip() for l in lines[1:25] if l.strip()]
    })

print(f"Parsed {len(file_a_certs)} pages of FILE A")

# Print summary of all pages in FILE A
for c in file_a_certs:
    print(f"FILE A Page {c['page']:3d}: Item='{c['calib_item']}' | Cert='{c['cert_no']}' | Disc='{c['discipline']}'")
    if not c['calib_item']:
        # print first few lines to see what it is
        print(f"   [Lines]: {c['first_lines'][:6]}")
