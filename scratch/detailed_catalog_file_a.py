import re

with open('scratch/file_a_ocr_results.txt', 'r', encoding='utf-8') as f:
    raw_ocr = f.read()

pages_raw = raw_ocr.split('=== FILE A PAGE ')

file_a_detailed = []

for p_block in pages_raw:
    if not p_block.strip():
        continue
    lines = p_block.split('\n')
    page_num = int(lines[0].split(' ')[0])
    content = '\n'.join(lines[1:])
    
    cert_no = ""
    page_str = ""
    ulr = ""
    discipline = ""
    eq_name = ""
    
    m_cert = re.search(r'0826/405/\d+', content)
    if m_cert: cert_no = m_cert.group(0)
    
    m_page = re.search(r'Page\s+(\d+)\s+of\s+(\d+)', content, re.IGNORECASE)
    if m_page: page_str = f"Page {m_page.group(1)} of {m_page.group(2)}"
    
    # Extract item name by scanning for known equipment names or after 'Calibrated Item' / 'Description of Item'
    known_items = [
        'Compression Testing Machine', 'Digital Weighing Balance', 'Weighing Balance', 'Weight Balance',
        'Temperature Indicator', 'Digital Thermometer', 'Glass Thermometer', 'Cube Mould', 'Beam Mould',
        'Cylindrical Mould', 'Coating Thickness Gauge', 'Measuring Cylinder', 'Digital Caliper', 'Vernier Caliper',
        'Slump Cone', 'Test Sieve', 'Elongation Gauge', 'Flakiness Gauge', 'Rapid Moisture Meter',
        'Sand Replacement Cylinder', 'Soil Core Cutter', 'Counter Meter', 'Aggregate Crushing Value',
        'Hydrometer', 'Oven', 'Water Bath', 'Proving Ring', 'Pycnometer', 'Total Station', 'Auto Level',
        'Pressure Gauge', 'Sieve Shaker', 'Vicatt Needle', 'Micro Meter', 'Height Gauge', 'Dial Gauge'
    ]
    
    for item in known_items:
        if re.search(r'\b' + re.escape(item) + r'\b', content, re.IGNORECASE):
            eq_name = item
            break
            
    if not eq_name:
        # Fallback to text parsing
        m_item = re.search(r'(?:Calibrated Item|Description of Item|Nomenclature)\s*\n([^\n]+)', content)
        if m_item:
            eq_name = m_item.group(1).strip()
            
    # Discipline
    if 'Mechanical' in content: discipline = 'Mechanical'
    elif 'Thermal' in content: discipline = 'Thermal'
    elif 'Electro-Technical' in content: discipline = 'Electro-Technical'
    elif 'Fluid Flow' in content: discipline = 'Fluid Flow'
    
    file_a_detailed.append({
        'page': page_num,
        'cert_no': cert_no,
        'page_str': page_str,
        'discipline': discipline,
        'eq_name': eq_name,
        'snippet': [l.strip() for l in lines[1:20] if l.strip()]
    })

for d in file_a_detailed:
    print(f"Page {d['page']:3d} | Cert: {d['cert_no']:14s} | {d['page_str']:10s} | Disc: {d['discipline']:12s} | Item: {d['eq_name']}")
