import glob
import os
import re

cert_dir = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates'
files = sorted(glob.glob(os.path.join(cert_dir, '*.php')))

for fpath in files:
    fname = os.path.basename(fpath)
    if fname in ['sieves.php', 'ctm.php']:
        continue

    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Pattern for duplicate footer injection:
    # Matches:
    # let endY = ... doc.text("CALIBRATED BY: YOGESH B JOSHI" ... doc.text("PROPRIETOR"...)
    # appearing twice or having duplicate CALIBRATED BY / REMARKS blocks inside addCertificateDetails
    
    # 1. Deduplicate consecutive/double footer blocks
    # Match from the first 'let endY = ...' or 'let tableStartY2 = ...' down to 'PROPRIETOR'
    pattern = r'(\s*(?:let\s+endY\s*=\s*[\s\S]*?|\s*doc\.text\(\s*["\']CALIBRATED BY: YOGESH B JOSHI["\'][\s\S]*?)(?:PROPRIETOR["\'][^\n]*\);|\}\s*$))'
    
    # Find all occurrences of CALIBRATED BY: YOGESH B JOSHI in addCertificateDetails
    cal_pos = [m.start() for m in re.finditer(r'doc\.text\(\s*["\']CALIBRATED BY: YOGESH B JOSHI["\']', content)]
    if len(cal_pos) > 1:
        # Keep only the last occurrence block, remove the previous duplicate footer blocks
        # First occurrence:
        idx1 = cal_pos[0]
        idx2 = cal_pos[-1]
        
        # Find start of block 1 (backtrack to 'let endY' or 'doc.setFont' before idx1)
        block1_start = content.rfind('let endY', 0, idx1)
        if block1_start == -1 or idx1 - block1_start > 300:
            block1_start = content.rfind('doc.setFont', 0, idx1)
            
        # Find end of block 1 (after PROPRIETOR or before second let endY)
        block2_start = content.rfind('let endY', idx1, idx2)
        if block2_start == -1:
            block2_start = content.rfind('doc.setFont', idx1, idx2)
            
        if block1_start != -1 and block2_start != -1 and block1_start < block2_start:
            content = content[:block1_start] + content[block2_start:]
            print(f"Cleaned duplicate footer block in {fname}")

    # Remove extra hardcoded CALIBRATION BY :- YOGESH BHAI line if followed by let endY
    content = re.sub(r'\s*doc\.text\(\s*["\']CALIBRATION BY\s*:-\s*YOGESH BHAI["\']\s*,\s*14\s*,\s*(?:Yalign|\d+)[^\n]*\);', '', content)

    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(content)

print("Second cleanup pass completed.")
