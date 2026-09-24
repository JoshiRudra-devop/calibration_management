import os, re

cert_dir = "/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates"

updates = {
    "autolevel.php": (
        r"let endY = \(doc\.lastAutoTable[\s\S]*?doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);",
        """let endY = Yalign + 4;
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
    ),
    "slumcone.php": (
        r"let endY = \(doc\.lastAutoTable[\s\S]*?doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);",
        """let endY = (typeof hori_axis !== "undefined" ? hori_axis : Yalign) + 4;
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
    ),
    "total_station.php": (
        r"let endY = \(doc\.lastAutoTable[\s\S]*?doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);",
        """let endY = Yalign + 4;
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
    ),
    "cube_mould.php": (
        r"let endY = \(doc\.lastAutoTable[\s\S]*?doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);",
        """let endY = (doc.lastAutoTable ? doc.lastAutoTable.finalY : ((doc.autoTable && doc.autoTable.previous) ? doc.autoTable.previous.finalY : tableEndY)) + 4;
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
    ),
    "rapid_moisture.php": (
        r"let endY = \(doc\.lastAutoTable[\s\S]*?doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);",
        """let endY = (doc.lastAutoTable && doc.lastAutoTable.finalY) ? doc.lastAutoTable.finalY + 2 : 180;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, endY += 8);
      doc.setFontSize(10.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 7);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 6);
      doc.text("• NOTE: This calibration report refers to 'Oven Drying Method'.", 14, endY += 6);
      let sigY = Math.max(endY + 18, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);"""
    ),
    "weight_balance.php": (
        r"let endY = doc\.lastAutoTable \? doc\.lastAutoTable\.finalY \+ 2 : 180;[\s\S]*?doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);",
        """let endY = doc.lastAutoTable ? doc.lastAutoTable.finalY + 2 : 180;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, endY += 8);
      doc.setFontSize(10.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 7);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 6);
      doc.text("• NOTE: Uncertainty is calculated at a confidence level of 95% (k=2).", 14, endY += 6);
      let sigY = Math.max(endY + 18, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);"""
    ),
    "ctm.php": (
        r"let endY = 195;[\s\S]*?doc\.text\(\"PROPRIETOR\", 170, sigY \+ 15\);",
        """let endY = 195;
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
    )
}

for filename, (pattern, replacement) in updates.items():
    filepath = os.path.join(cert_dir, filename)
    if not os.path.exists(filepath):
        continue
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    new_content = re.sub(pattern, replacement, content)
    if new_content != content:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Updated {filename}")
    else:
        print(f"FAILED TO MATCH {filename}")

