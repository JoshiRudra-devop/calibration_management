import os, re

cert_dir = "/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates"

for filename in sorted(os.listdir(cert_dir)):
    if not filename.endswith(".php") or filename.startswith("."):
        continue
    filepath = os.path.join(cert_dir, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    orig_content = content
    
    # 1. Non-table certificates where CALIBRATION BY is already in metadata:
    # aggregate_impact.php, measuring_cyl.php, pycnometer.php, hydrometer.php, cone_penetro.php, core_cutter.php, general.php
    if filename in ["aggregate_impact.php", "measuring_cyl.php", "pycnometer.php", "hydrometer.php", "cone_penetro.php", "core_cutter.php", "general.php"]:
        # Match footer block
        pattern = r"let endY = \(doc\.lastAutoTable.*?\);[\s\S]*?doc\.setFont\(\"helvetica\", \"bold\"\);\s*doc\.setFontSize\(11\);\s*doc\.text\(\"FOR, \" \+ window\.PDF_COMPANY_NAME, 145, sigY\);\s*doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);"
        replacement = """let endY = Yalign + 4;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 7);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 6);
      let sigY = Math.max(endY + 18, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);"""
        content = re.sub(pattern, replacement, content)

    # 2. ph_meter.php
    elif filename == "ph_meter.php":
        pattern = r"let endY = \(doc\.lastAutoTable.*?\);[\s\S]*?doc\.setFont\(\"helvetica\", \"bold\"\);\s*doc\.setFontSize\(11\);\s*doc\.text\(\"FOR, \" \+ window\.PDF_COMPANY_NAME, 145, sigY\);\s*doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);"
        replacement = """let endY = tableStartY2 + 4;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, endY += 8);
      doc.setFontSize(10.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 7);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 6);
      let sigY = Math.max(endY + 18, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);"""
        content = re.sub(pattern, replacement, content)

    # 3. oven.php, water_bath.php, weigh_batcher.php
    elif filename in ["oven.php", "water_bath.php", "weigh_batcher.php"]:
        pattern = r"let endY = \(doc\.lastAutoTable.*?\);[\s\S]*?doc\.setFont\(\"helvetica\", \"bold\"\);\s*doc\.setFontSize\(11\);\s*doc\.text\(\"FOR, \" \+ window\.PDF_COMPANY_NAME, 145, sigY\);\s*doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);"
        replacement = """let endY = (typeof tableStartY2 !== "undefined" ? tableStartY2 : (doc.lastAutoTable ? doc.lastAutoTable.finalY : Yalign)) + 4;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 7);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 6);
      let sigY = Math.max(endY + 18, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);"""
        content = re.sub(pattern, replacement, content)

    # 4. digital_thermo.php
    elif filename == "digital_thermo.php":
        pattern = r"let endY = \(doc\.lastAutoTable.*?\);[\s\S]*?doc\.setFont\(\"helvetica\", \"bold\"\);\s*doc\.setFontSize\(11\);\s*doc\.text\(\"FOR, \" \+ window\.PDF_COMPANY_NAME, 145, sigY\);\s*doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);"
        replacement = """let endY = tableStartY2 + 4;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 7);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 6);
      let sigY = Math.max(endY + 18, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);"""
        content = re.sub(pattern, replacement, content)

    # 5. vernier_caliper.php
    elif filename == "vernier_caliper.php":
        pattern = r"let endY = \(doc\.lastAutoTable.*?\);[\s\S]*?doc\.setFont\(\"helvetica\", \"bold\"\);\s*doc\.setFontSize\(11\);\s*doc\.text\(\"FOR, \" \+ window\.PDF_COMPANY_NAME, 145, sigY\);\s*doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);"
        replacement = """let endY = (afterTableY || (doc.lastAutoTable ? doc.lastAutoTable.finalY : Yalign)) + 4;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 7);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 6);
      let sigY = Math.max(endY + 18, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);"""
        content = re.sub(pattern, replacement, content)

    # 6. elongation.php, flakness.php
    elif filename in ["elongation.php", "flakness.php"]:
        pattern = r"let endY = tableStartY2 \+ 2;[\s\S]*?doc\.setFont\(\"helvetica\", \"bold\"\);\s*doc\.setFontSize\(11\);\s*doc\.text\(\"FOR, \" \+ window\.PDF_COMPANY_NAME, 145, sigY\);\s*doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);"
        replacement = """let endY = tableStartY2 + 4;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, endY += 7);
      doc.setFontSize(10.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 7);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 6);
      let sigY = Math.max(endY + 18, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);"""
        content = re.sub(pattern, replacement, content)

    # 7. isi_cube.php
    elif filename == "isi_cube.php":
        pattern = r"let endY = \(doc\.lastAutoTable.*?\);[\s\S]*?doc\.setFont\(\"helvetica\", \"bold\"\);\s*doc\.setFontSize\(11\);\s*doc\.text\(\"FOR, \" \+ window\.PDF_COMPANY_NAME, 145, sigY\);\s*doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);"
        replacement = """let endY = (doc.lastAutoTable ? doc.lastAutoTable.finalY : Yalign) + 4;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, endY += 8);
      doc.setFontSize(10.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 7);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 6);
      let sigY = Math.max(endY + 18, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);"""
        content = re.sub(pattern, replacement, content)

    if content != orig_content:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Updated {filename}")
    else:
        print(f"No match/change for {filename}")
