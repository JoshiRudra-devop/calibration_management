import re
import json

with open('scratch/file_a_ocr_results.txt', 'r', encoding='utf-8') as f:
    raw_ocr = f.read()

pages_raw = raw_ocr.split('=== FILE A PAGE ')

file_a_pages = []

for p_block in pages_raw:
    if not p_block.strip():
        continue
    lines = p_block.split('\n')
    page_num = int(lines[0].split(' ')[0])
    content = '\n'.join(lines[1:])
    
    # Extract key metadata
    cert_no = ""
    page_str = ""
    ulr = ""
    discipline = ""
    eq_name = ""
    make_model = ""
    range_lc = ""
    serial_no = ""
    
    m_cert = re.search(r'0826/405/\d+', content)
    if m_cert: cert_no = m_cert.group(0)
    
    m_page = re.search(r'Page\s+(\d+)\s+of\s+(\d+)', content, re.IGNORECASE)
    if m_page: page_str = f"Page {m_page.group(1)} of {m_page.group(2)}"
    
    m_ulr = re.search(r'CC4592\d+F?', content)
    if m_ulr: ulr = m_ulr.group(0)
    
    # Search lines for equipment details
    for i, line in enumerate(lines[1:]):
        l = line.strip()
        if 'Calibrated Item' in l or 'Description of Item' in l or 'Nomenclature' in l:
            # Look at surrounding lines
            for j in range(i+1, min(i+6, len(lines[1:]))):
                val = lines[1:][j].strip()
                if val and val not in ['Calibrated Item', 'Description of Item', 'Nomenclature', 'Visual Inspection', 'Make/Model', 'Type', 'Range', 'Least Count']:
                    if not eq_name: eq_name = val
        if 'Discipline' in l:
            for j in range(i+1, min(i+4, len(lines[1:]))):
                val = lines[1:][j].strip()
                if val in ['Mechanical', 'Thermal', 'Electro-Technical', 'Fluid Flow']:
                    discipline = val
                    break
                    
    file_a_pages.append({
        'page': page_num,
        'cert_no': cert_no,
        'page_str': page_str,
        'ulr': ulr,
        'discipline': discipline,
        'eq_name': eq_name,
        'content': content
    })

# Group pages by cert_no or single pages
certs_dict = {}
for p in file_a_pages:
    c_key = p['cert_no'] if p['cert_no'] else f"PAGE_{p['page']}"
    if c_key not in certs_dict:
        certs_dict[c_key] = {
            'cert_no': c_key,
            'pages': [],
            'eq_name': p['eq_name'],
            'discipline': p['discipline'],
            'ulr': p['ulr']
        }
    certs_dict[c_key]['pages'].append(p['page'])
    if not certs_dict[c_key]['eq_name'] and p['eq_name']:
        certs_dict[c_key]['eq_name'] = p['eq_name']
    if not certs_dict[c_key]['discipline'] and p['discipline']:
        certs_dict[c_key]['discipline'] = p['discipline']

print(f"Total Pages: {len(file_a_pages)}, Total Certificates: {len(certs_dict)}")

# Output summary of certificates
with open('scratch/file_a_certificates_summary.txt', 'w') as f_out:
    for c_key, c_info in certs_dict.items():
        pages_lst = ", ".join(map(str, c_info['pages']))
        f_out.write(f"Cert: {c_key:15s} | Pages: {pages_lst:10s} | Disc: {c_info['discipline']:15s} | Item: {c_info['eq_name']}\n")

print("Written scratch/file_a_certificates_summary.txt")
