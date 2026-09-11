import fitz

doc_b = fitz.open('/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/STANDARDIZED_CERTIFICATES_PROTOTYPE_MODIFIED.pdf')

for i, page in enumerate(doc_b):
    text = page.get_text()
    lines = [l.strip() for l in text.split('\n') if l.strip()]
    print(f"==================== FILE B PAGE {i+1} ====================")
    for l in lines:
        print(l)
    print("\n")
