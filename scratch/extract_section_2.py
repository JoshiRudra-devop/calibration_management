import re

with open('scratch/file_a_ocr_results.txt', 'r', encoding='utf-8') as f:
    raw_ocr = f.read()

pages_raw = raw_ocr.split('=== FILE A PAGE ')

file_a_real_items = []

for p_block in pages_raw:
    if not p_block.strip():
        continue
    lines = p_block.split('\n')
    page_num = int(lines[0].split(' ')[0])
    content = '\n'.join(lines[1:])
    
    cert_no = ""
    page_info = ""
    
    m_cert = re.search(r'0826/405/\d+', content)
    if m_cert: cert_no = m_cert.group(0)
    
    m_page = re.search(r'Page\s+(\d+)\s+of\s+(\d+)', content, re.IGNORECASE)
    if m_page: page_info = f"Page {m_page.group(1)} of {m_page.group(2)}"
    
    # Extract lines around "2) Instrument Information" or "Calibrated Item"
    item_val = ""
    for i, l in enumerate(lines):
        if 'Calibrated Item' in l or 'Description of Item' in l or 'Nomenclature' in l:
            # find next 3 non-empty lines
            for j in range(i+1, min(i+6, len(lines))):
                val = lines[j].strip()
                if val and val not in ['Calibrated Item', 'Description of Item', 'Nomenclature', 'Visual Inspection', 'Make/Model', 'Type', 'Range', 'Least Count', 'Serial No./ID No.:']:
                    item_val = val
                    break
        if not item_val and '2) Instrument Information' in l:
            for j in range(i+1, min(i+8, len(lines))):
                val = lines[j].strip()
                if val and val not in ['Calibrated Item', 'Visual Inspection', 'Make/Model', 'Type']:
                    item_val = val
                    break
                    
    file_a_real_items.append({
        'page': page_num,
        'cert_no': cert_no,
        'page_info': page_info,
        'item': item_val,
        'lines': lines[:35]
    })

with open('scratch/file_a_real_items.txt', 'w') as f_out:
    for item in file_a_real_items:
        f_out.write(f"Page {item['page']:3d} | Cert: {item['cert_no']:14s} | {item['page_info']:10s} | Item: {item['item']}\n")

print("Finished extracting Section 2 for all 118 pages!")
