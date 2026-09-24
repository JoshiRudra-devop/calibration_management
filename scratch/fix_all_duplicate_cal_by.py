import glob
import os
import re

cert_dir = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates'
files = sorted(glob.glob(os.path.join(cert_dir, '*.php')))

for fpath in files:
    fname = os.path.basename(fpath)

    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Remove duplicate CALIBRATION BY :- YOGESH BHAI line if followed by footer CALIBRATED BY
    pattern_cal_by = r'\s*doc\.text\(\s*[`"\']CALIBRATION BY\s*:-\s*YOGESH BHAI[`"\']\s*,\s*14\s*,\s*(?:Yalign|\w+)(?:\s*\+=\s*\d+)?\s*\);'
    if re.search(pattern_cal_by, content):
        content = re.sub(pattern_cal_by, '', content)
        print(f"Removed duplicate CALIBRATION BY line in {fname}")

    # 2. In sand_pouring.php specifically: adjust font size 15 to 11 and REMARKS font size to 10.5
    if fname == 'sand_pouring.php':
        content = content.replace('doc.setFontSize(15);', 'doc.setFontSize(11);')
        content = content.replace('doc.setFontSize(8.5);', 'doc.setFontSize(10.5);')

    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(content)

print("Duplicate CALIBRATION BY cleanup complete.")
