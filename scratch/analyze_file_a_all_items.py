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
    
    cert_no = ""
    page_info = ""
    eq_name = ""
    discipline = ""
    
    m_cert = re.search(r'0826/405/\d+', content)
    if m_cert: cert_no = m_cert.group(0)
    
    m_page = re.search(r'Page\s+\d+\s+of\s+\d+', content, re.IGNORECASE)
    if m_page: page_info = m_page.group(0)
    
    for i, l in enumerate(lines):
        if 'Calibrated Item' in l or 'Description of Item' in l or 'Nomenclature' in l:
            # find non-empty line around here
            for k in range(i, min(i+4, len(lines))):
                text_k = lines[k].strip()
                if text_k and text_k not in ['Calibrated Item', 'Description of Item', 'Nomenclature']:
                    eq_name = text_k
                    break
        if 'Discipline' in l and i+1 < len(lines):
            discipline = lines[i+1].strip()
            
    print(f"Page {page_num:3d} | Cert: {cert_no:14s} | {page_info:12s} | Item: {eq_name[:45]:45s}")
