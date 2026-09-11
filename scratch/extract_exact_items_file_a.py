import re

with open('scratch/file_a_ocr_results.txt', 'r', encoding='utf-8') as f:
    raw_ocr = f.read()

pages_raw = raw_ocr.split('=== FILE A PAGE ')

file_a_catalog = []

for p_block in pages_raw:
    if not p_block.strip():
        continue
    lines = p_block.split('\n')
    page_num = int(lines[0].split(' ')[0])
    content = '\n'.join(lines[1:])
    
    cert_no = ""
    page_info = ""
    discipline = ""
    item_name = ""
    table_headers = []
    
    m_cert = re.search(r'0826/405/\d+', content)
    if m_cert: cert_no = m_cert.group(0)
    
    m_page = re.search(r'Page\s+\d+\s+of\s+\d+', content, re.IGNORECASE)
    if m_page: page_info = m_page.group(0)
    
    # Extract item name between "Calibrated Item" and "Visual Inspection" or "Make/Model"
    item_match = re.search(r'Calibrated Item\s*\n([^\n]+)', content)
    if item_match:
        cand = item_match.group(1).strip()
        if cand not in ['Visual Inspection', 'Make/Model', 'Type']:
            item_name = cand
            
    if not item_name:
        item_match2 = re.search(r'Description of Item\s*\n([^\n]+)', content)
        if item_match2:
            item_name = item_match2.group(1).strip()
            
    if not item_name:
        item_match3 = re.search(r'Nomenclature\s*\n([^\n]+)', content)
        if item_match3:
            item_name = item_match3.group(1).strip()

    # Search for discipline
    disc_match = re.search(r'Discipline\s*\n([^\n]+)', content)
    if disc_match:
        discipline = disc_match.group(1).strip()
        
    # Table headers detection
    th = []
    for l in lines:
        l_str = l.strip()
        if any(w in l_str for w in ['Parameter', 'Nominal', 'Applied', 'Indicated', 'Observed', 'Reading', 'Deviation', 'Error', 'Uncertainty']):
            if len(l_str) < 100:
                th.append(l_str)
                
    file_a_catalog.append({
        'page': page_num,
        'cert_no': cert_no,
        'page_info': page_info,
        'discipline': discipline,
        'item_name': item_name,
        'sample_lines': lines[5:25]
    })

for item in file_a_catalog:
    print(f"Page {item['page']:3d} | Cert: {item['cert_no']:14s} | {item['page_info']:10s} | Disc: {item['discipline']:15s} | Item: {item['item_name']}")
