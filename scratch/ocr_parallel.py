import fitz
import subprocess
import os
import time
from concurrent.futures import ProcessPoolExecutor

pdf_path = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/405_Shanti Procon LLP._Check Done.pdf'

def process_page(page_num):
    doc = fitz.open(pdf_path)
    page = doc[page_num]
    pix = page.get_pixmap(dpi=150)
    temp_img = f"scratch/temp_p_{page_num+1}.png"
    pix.save(temp_img)
    
    res = subprocess.run(['./scratch/ocr_bin', temp_img], capture_output=True, text=True)
    ocr_text = res.stdout
    
    if os.path.exists(temp_img):
        os.remove(temp_img)
        
    return page_num + 1, ocr_text

if __name__ == '__main__':
    t0 = time.time()
    doc = fitz.open(pdf_path)
    total_pages = len(doc)
    doc.close()
    
    print(f"Starting parallel OCR for {total_pages} pages...")
    results = {}
    with ProcessPoolExecutor(max_workers=8) as executor:
        futures = [executor.submit(process_page, i) for i in range(total_pages)]
        for fut in futures:
            p_num, text = fut.result()
            results[p_num] = text
            if p_num % 10 == 0:
                print(f"Completed page {p_num}/{total_pages}")
                
    out_txt = 'scratch/file_a_ocr_results.txt'
    with open(out_txt, 'w', encoding='utf-8') as f:
        for p in range(1, total_pages + 1):
            f.write(f"=== FILE A PAGE {p} ===\n")
            f.write(results.get(p, ''))
            f.write("\n\n")
            
    print(f"All {total_pages} pages OCR completed in {time.time()-t0:.2f}s!")
