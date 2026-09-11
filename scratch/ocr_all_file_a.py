import fitz
import subprocess
import os

pdf_path = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/405_Shanti Procon LLP._Check Done.pdf'
doc = fitz.open(pdf_path)
total_pages = len(doc)
print(f"Total pages in FILE A: {total_pages}")

out_txt = 'scratch/file_a_ocr_results.txt'

with open(out_txt, 'w', encoding='utf-8') as f_out:
    for i in range(total_pages):
        page = doc[i]
        pix = page.get_pixmap(dpi=150)
        temp_img = f"scratch/temp_page_{i+1}.png"
        pix.save(temp_img)
        
        # Run OCR binary
        res = subprocess.run(['./scratch/ocr_bin', temp_img], capture_output=True, text=True)
        ocr_text = res.stdout
        
        # Remove temp img
        if os.path.exists(temp_img):
            os.remove(temp_img)
            
        f_out.write(f"=== FILE A PAGE {i+1} ===\n")
        f_out.write(ocr_text)
        f_out.write("\n\n")
        
        if (i+1) % 10 == 0 or (i+1) == total_pages:
            print(f"Processed {i+1}/{total_pages} pages")

print("OCR complete!")
