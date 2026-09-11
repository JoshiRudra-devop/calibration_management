import re
import json

with open('scratch/file_a_ocr_results.txt', 'r', encoding='utf-8') as f:
    raw_ocr = f.read()

pages_raw = raw_ocr.split('=== FILE A PAGE ')

cert_groups = {}
page_to_cert = {}

for p_block in pages_raw:
    if not p_block.strip():
        continue
    lines = p_block.split('\n')
    page_num = int(lines[0].split(' ')[0])
    content = '\n'.join(lines[1:])
    
    # Extract Certificate No (e.g. 0826/405/001, 0826/405/002, etc.)
    cert_no = ""
    page_str = ""
    discipline = ""
    calib_item = ""
    ulr_no = ""
    
    m_cert = re.search(r'0826/405/\d+', content)
    if m_cert:
        cert_no = m_cert.group(0)
        
    m_page = re.search(r'Page\s+(\d+)\s+of\s+(\d+)', content, re.IGNORECASE)
    if m_page:
        page_str = f"Page {m_page.group(1)} of {m_page.group(2)}"
        
    m_ulr = re.search(r'CC4592\d+', content)
    if m_ulr:
        ulr_no = m_ulr.group(0)
        
    # Search lines for discipline and item
    for i, l in enumerate(lines):
        l_s = l.strip()
        if 'Discipline' in l_s and i+1 < len(lines):
            discipline = lines[i+1].strip()
        if ('Calibrated Item' in l_s or 'Description of Item' in l_s) and i+1 < len(lines):
            calib_item = lines[i+1].strip()
            
    page_to_cert[page_num] = {
        'page': page_num,
        'cert_no': cert_no,
        'page_str': page_str,
        'ulr': ulr_no,
        'discipline': discipline,
        'calib_item': calib_item,
        'content_snippet': lines[:35]
    }

print(f"Mapped {len(page_to_cert)} pages in FILE A")

with open('scratch/file_a_mapped_pages.json', 'w') as f:
    json.dump(page_to_cert, f, indent=2)
