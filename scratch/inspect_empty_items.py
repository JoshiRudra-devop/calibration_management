with open('scratch/file_a_ocr_results.txt', 'r', encoding='utf-8') as f:
    raw_ocr = f.read()

pages_raw = raw_ocr.split('=== FILE A PAGE ')

for p_block in pages_raw:
    if not p_block.strip():
        continue
    lines = p_block.split('\n')
    page_num = int(lines[0].split(' ')[0])
    
    # Check if page is empty or what lines it has
    content = '\n'.join(lines[1:30])
    print(f"=== PAGE {page_num} ===")
    for l in lines[1:25]:
        if l.strip():
            print("  ", l.strip())
