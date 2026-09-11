import re

with open('scratch/file_a_ocr_results.txt', 'r', encoding='utf-8') as f:
    raw_ocr = f.read()

pages_raw = raw_ocr.split('=== FILE A PAGE ')

file_a_summary = []

for p_block in pages_raw:
    if not p_block.strip():
        continue
    lines = p_block.split('\n')
    page_num = int(lines[0].split(' ')[0])
    content = '\n'.join(lines[1:])
    
    cert_no = ""
    eq_name = ""
    make_model = ""
    range_lc = ""
    serial_no = ""
    discipline = ""
    ulr = ""
    
    for i, line in enumerate(lines[1:]):
        l = line.strip()
        if 'Certificate No' in l or '0826/405/' in l:
            m = re.search(r'0826/405/\d+', l)
            if m: cert_no = m.group(0)
            elif not cert_no: cert_no = l
        if 'ULR No' in l or 'CC4592' in l:
            m = re.search(r'CC\d+F?', l)
            if m: ulr = m.group(0)
            elif not ulr: ulr = l
        if 'Calibrated Item' in l:
            # check next line or current line
            if len(lines[1:]) > i+1:
                eq_name = lines[1:][i+1].strip()
        if 'Make/Model' in l:
            if len(lines[1:]) > i+1:
                make_model = lines[1:][i+1].strip()
        if 'Serial No' in l:
            if len(lines[1:]) > i+1:
                serial_no = lines[1:][i+1].strip()
                
    file_a_summary.append({
        'page': page_num,
        'cert_no': cert_no,
        'ulr': ulr,
        'eq_name': eq_name,
        'lines': lines[1:]
    })

# Write to file
with open('scratch/file_a_summary.txt', 'w') as f_out:
    for item in file_a_summary:
        f_out.write(f"Page {item['page']:3d} | Cert: {item['cert_no']} | ULR: {item['ulr']}\n")
        # Find key lines mentioning equipment
        for line in item['lines'][:40]:
            if any(k in line for k in ['Compression', 'Testing Machine', 'Caliper', 'Gauge', 'Mould', 'Balance', 'Sieve', 'Oven', 'Bath', 'Thermometer', 'Hydrometer', 'Cylinder', 'Pycnometer', 'Slump', 'Impact', 'Penetrometer', 'Core', 'Rapid', 'Total Station', 'Auto Level', 'Level', 'Weight', 'Batcher', 'Discipline', 'Mechanical', 'Thermal', 'Mass', 'Dimension', 'Pressure']):
                f_out.write(f"   -> {line}\n")
        f_out.write("\n")

print("FILE A summary written!")
