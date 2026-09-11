with open('scratch/file_b_full_text.txt', 'r', encoding='utf-8') as f:
    text = f.read()

pages = text.split('==================== FILE B PAGE ')

for p in pages:
    if not p.strip(): continue
    lines = p.split('\n')
    page_num = lines[0].split(' ')[0]
    print(f"--- Page {page_num} ---")
    for l in lines[1:25]:
        if any(k in l for k in ['CERTIFICATE', 'TEST REPORT', 'EQUIPMENT NAME', 'REF NO', 'CAPACITY & MAKE', 'SERIAL NO']):
            print("  ", l)
