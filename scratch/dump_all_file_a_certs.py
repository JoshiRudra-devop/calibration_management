import re

with open('scratch/file_a_ocr_results.txt', 'r', encoding='utf-8') as f:
    raw_ocr = f.read()

pages_raw = raw_ocr.split('=== FILE A PAGE ')

for p_block in pages_raw:
    if not p_block.strip():
        continue
    lines = p_block.split('\n')
    page_num = int(lines[0].split(' ')[0])
    content = '\n'.join(lines[1:])
    
    # Header block info
    cert_no = ""
    page_info = ""
    eq_name = ""
    discipline = ""
    
    m_cert = re.search(r'0826/405/\d+', content)
    if m_cert: cert_no = m_cert.group(0)
    
    m_page = re.search(r'Page\s+\d+\s+of\s+\d+', content, re.IGNORECASE)
    if m_page: page_info = m_page.group(0)
    
    for i, l in enumerate(lines):
        if 'Discipline' in l and i+1 < len(lines):
            discipline = lines[i+1].strip()
        if ('Calibrated Item' in l or 'Description of Item' in l or 'Equipment' in l) and i+1 < len(lines):
            if not eq_name:
                eq_name = lines[i+1].strip()
                
    # Search for table headers / parameter lines
    table_headers = []
    for l in lines:
        if any(w in l for w in ['Nominal', 'Applied', 'Indicated', 'Observed', 'Error', 'Uncertainty', 'Deviation', 'Reading', 'Parameter', 'Accuracy', 'Tolerance']):
            table_headers.append(l.strip())
            
    print(f"Page {page_num:3d} | Cert: {cert_no:15s} | {page_info:12s} | Item: {eq_name[:40]:40s} | Headers: {table_headers[:3]}")
