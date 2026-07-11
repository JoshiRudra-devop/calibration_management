import os

dir_path = r"c:\xampp\htdocs\calibration certificate\certificates"
files = ["cloud_cube.php", "cube_mould.php", "full_lab.php", "isi_cube.php"]

for file in files:
    path = os.path.join(dir_path, file)
    if not os.path.exists(path):
        continue
    
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()
    
    fixed = content
    fixed = fixed.replace('img.crossOrigin = "Anonymous";', '// img.crossOrigin = "Anonymous";')
    fixed = fixed.replace("img.crossOrigin = 'Anonymous';", "// img.crossOrigin = 'Anonymous';")
    fixed = fixed.replace('img.crossOrigin = "Anonymous"', '// img.crossOrigin = "Anonymous"')
    fixed = fixed.replace("img.crossOrigin = 'Anonymous'", "// img.crossOrigin = 'Anonymous'")
    
    if fixed != content:
        with open(path, "w", encoding="utf-8") as f:
            f.write(fixed)
        print(f"Removed crossOrigin from {file}!")
    else:
        print(f"No changes in {file}.")
