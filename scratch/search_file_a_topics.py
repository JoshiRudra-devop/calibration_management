with open('scratch/file_a_ocr_results.txt', 'r', encoding='utf-8') as f:
    raw_ocr = f.read()

pages_raw = raw_ocr.split('=== FILE A PAGE ')

for p_block in pages_raw:
    if not p_block.strip(): continue
    lines = p_block.split('\n')
    page_num = lines[0].split(' ')[0]
    content = '\n'.join(lines[1:])
    
    for term in ['pH', 'Level', 'Station', 'Survey', 'Optical', 'Angle', 'Chemical', 'Voltage', 'Electro', 'Proctor', 'Vicat', 'Crushing', 'Counter']:
        if term.lower() in content.lower():
            print(f"Page {page_num:3s} mentions '{term}' -> snippet: {lines[1][:60]}")
