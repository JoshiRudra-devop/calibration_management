import fitz
import os

pdf_path = "STANDARDIZED_CERTIFICATES_PROTOTYPE.pdf"
output_temp_path = "STANDARDIZED_CERTIFICATES_PROTOTYPE_TEMP.pdf"

master_data = [
    # Page 1: Aggregate Impact
    {
        "name": "Digital Vernier Caliper & Weight Set",
        "serial": "MC-AI-02",
        "range": "0 - 300 mm / 0 - 20 kg",
        "valid": "19/09/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/AI/26-88"
    },
    # Page 2: Auto Level
    {
        "name": "Optical Collimator System / Master Level",
        "serial": "CS/AL-08",
        "range": "0 - 360° / 30 m",
        "valid": "15/05/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab (IDEMI)",
        "cert_no": "NABL/AL/2026/102"
    },
    # Page 3: Cube Mould (150 MM)
    {
        "name": "Digital Vernier Caliper & Micrometer",
        "serial": "VC-CM-09",
        "range": "0 - 300 mm",
        "valid": "15/07/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/CM/26-211"
    },
    # Page 4: Cone Penetrometer
    {
        "name": "Digital Vernier Caliper & Digital Timer",
        "serial": "MC-CP-05",
        "range": "0 - 50 mm / 0 - 60 sec",
        "valid": "04/06/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/CP/26-67"
    },
    # Page 5: Core Cutter
    {
        "name": "Digital Vernier Caliper & Precision Balance",
        "serial": "MC-CC-07",
        "range": "0 - 300 mm / 0 - 15 kg",
        "valid": "08/04/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/CC/26-53"
    },
    # Page 6: Cube Testing Machine
    {
        "name": "Master Proving Ring / Load Cell (2000 kN)",
        "serial": "PR-2000/05",
        "range": "0 - 2000 kN",
        "valid": "10/08/2027",
        "traceability": "Calibrated & Traceable to NPL India via NABL Accredited Lab",
        "cert_no": "NPL/CTM/26-501"
    },
    # Page 7: Cube Mould (70.6 MM)
    {
        "name": "Digital Vernier Caliper & Micrometer",
        "serial": "VC-CM-12",
        "range": "0 - 300 mm",
        "valid": "15/07/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/CM/26-210"
    },
    # Page 8: Digital Thermometer
    {
        "name": "Master Temperature Calibrator / RTD Sensor",
        "serial": "TC-RTD-11",
        "range": "-50°C to +300°C",
        "valid": "12/04/2027",
        "traceability": "Calibrated & Traceable to NABL Accredited Calibration Lab",
        "cert_no": "NABL/TEMP/26-109"
    },
    # Page 9: Elongation Gauge
    {
        "name": "Digital Vernier Caliper",
        "serial": "VC-EG-01",
        "range": "0 - 200 mm",
        "valid": "21/08/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/EG/26-14"
    },
    # Page 10: Flakiness Gauge
    {
        "name": "Digital Vernier Caliper",
        "serial": "VC-FG-02",
        "range": "0 - 200 mm",
        "valid": "21/08/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/FG/26-15"
    },
    # Page 11: Comprehensive Full Lab Report
    {
        "name": "Master Reference Standards (Proving Ring, Caliper, Thermometer, Weights)",
        "serial": "ML-REF-SET-01",
        "range": "Multi-Parameter Calibration Standards",
        "valid": "26/08/2027",
        "traceability": "All Reference Standards Calibrated & Traceable to NPL / NABL Labs",
        "cert_no": "NABL/FL/26-1000"
    },
    # Page 12: General Format
    {
        "name": "Digital Vernier Caliper & Master Weights",
        "serial": "GEN-M-01",
        "range": "0 - 300 mm / 0 - 50 kg",
        "valid": "25/08/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/GEN/26-500"
    },
    # Page 13: Soil Hydrometer
    {
        "name": "Master Standard Reference Hydrometer & Digital Thermometer",
        "serial": "RH-HYD-01",
        "range": "0.995 - 1.050 g/ml",
        "valid": "02/10/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/HYD/26-441"
    },
    # Page 14: ISI Cube Mould
    {
        "name": "Digital Vernier Caliper",
        "serial": "VC-ISI-03",
        "range": "0 - 300 mm",
        "valid": "15/07/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/ICM/26-212"
    },
    # Page 15: Graduated Measuring Cylinder
    {
        "name": "Precision Analytical Balance & Volumetric Standard",
        "serial": "AB-MC-04",
        "range": "0 - 1000 ml",
        "valid": "17/11/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/MC/26-309"
    },
    # Page 16: Laboratory Hot Air Oven
    {
        "name": "Master Temperature Data Logger & Thermocouple Sensor",
        "serial": "TH-LOG-03",
        "range": "0°C to +300°C",
        "valid": "18/07/2027",
        "traceability": "Calibrated & Traceable to NABL Accredited Calibration Lab",
        "cert_no": "NABL/OVEN/26-44"
    },
    # Page 17: Digital pH Meter
    {
        "name": "Certified Standard pH Buffer Solutions (pH 4.01, 7.00, 9.20) & Calibrator",
        "serial": "BUF-NIST-2026",
        "range": "0 - 14 pH",
        "valid": "05/09/2027",
        "traceability": "Traceable to NIST / NPL Standards via NABL Accredited Lab",
        "cert_no": "NABL/PH/26-78"
    },
    # Page 18: Pycnometer Bottle
    {
        "name": "Precision Balance & Digital Vernier Caliper",
        "serial": "PB-PYC-03",
        "range": "0 - 2000 g / 0 - 300 mm",
        "valid": "28/05/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/PYC/26-92"
    },
    # Page 19: Rapid Moisture Meter
    {
        "name": "Standard Pressure Gauge & Precision Balance",
        "serial": "PG-RM-06",
        "range": "0 - 50 %",
        "valid": "13/03/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/RM/26-177"
    },
    # Page 20: Sand Pouring Cylinder
    {
        "name": "Digital Vernier Caliper & Precision Balance",
        "serial": "VC-SPC-08",
        "range": "0 - 300 mm / 0 - 10 kg",
        "valid": "09/01/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/SPC/26-61"
    },
    # Page 21: Test Sieves
    {
        "name": "Digital Vernier Caliper",
        "serial": "ACCUPLUS/13-200",
        "range": "0 - 200 mm",
        "valid": "03/07/2026",
        "traceability": "Calibrated & Traceable to National Standard through IDEMI Calibration Laboratory",
        "cert_no": "62"
    },
    # Page 22: Slump Cone
    {
        "name": "Digital Vernier Caliper & Steel Rule",
        "serial": "VC-SC-10",
        "range": "0 - 300 mm",
        "valid": "14/02/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/SC/26-83"
    },
    # Page 23: Total Station
    {
        "name": "Optical Collimator Bench & Precision Polygon Mirror",
        "serial": "TS-CAL-04",
        "range": "0 - 360° / 3000 m",
        "valid": "20/06/2027",
        "traceability": "Calibrated & Traceable to National Standards via NABL Lab",
        "cert_no": "NABL/TS/2026/84"
    },
    # Page 24: Vernier Caliper
    {
        "name": "Gauge Block Set Grade 0 (Slip Gauges)",
        "serial": "GBS-01/88",
        "range": "0.5 mm - 100 mm",
        "valid": "14/03/2027",
        "traceability": "Calibrated & Traceable to NPL India via NABL Accredited Lab",
        "cert_no": "NABL/VC/26-301"
    },
    # Page 25: Water Bath
    {
        "name": "Master Digital Thermometer & Temperature Sensor",
        "serial": "MDT-WB-09",
        "range": "0°C to 100°C",
        "valid": "11/05/2027",
        "traceability": "Calibrated & Traceable to NABL Accredited Calibration Lab",
        "cert_no": "NABL/WB/26-118"
    },
    # Page 26: Weigh Batcher
    {
        "name": "Standard M1 Class Test Weights",
        "serial": "M1-TW-500",
        "range": "1 kg - 500 kg",
        "valid": "30/01/2027",
        "traceability": "Calibrated & Traceable to NABL Accredited Calibration Lab",
        "cert_no": "NABL/WBC/26-412"
    },
    # Page 27: Weight Balance
    {
        "name": "Standard E2 / F1 Class Weights Set",
        "serial": "SW-E2-500",
        "range": "1 mg - 30 kg",
        "valid": "22/02/2027",
        "traceability": "Calibrated & Traceable to NPL / Regional Reference Standard Laboratory",
        "cert_no": "RRSL/WB/26-905"
    }
]

doc = fitz.open(pdf_path)
print(f"Loaded PDF with {len(doc)} pages.")

for i, page in enumerate(doc):
    info = master_data[i]
    blocks = page.get_text("blocks")
    
    # Find max y1 of remarks/table content before footer
    max_y = 350.0
    for b in blocks:
        text = b[4].strip()
        if "FOR, SHREEJI" in text or "PROPRIETOR" in text or "[QR CODE]" in text:
            continue
        if b[3] > max_y and b[3] < 665.0:
            max_y = b[3]
    
    # Determine Y position for Master Instrument section box
    start_y = max_y + 10.0
    box_height = 68.0
    
    # If remarks end late (e.g. Page 19), place box below remarks (start_y ~ 665.0)
    if start_y + box_height > 620.0 and start_y < 650.0:
        start_y = 665.0
        
    box_rect = fitz.Rect(39.7, start_y, 395.0, start_y + box_height)
    
    # Draw border rectangle with subtle background fill
    shape = page.new_shape()
    shape.draw_rect(box_rect)
    shape.finish(color=(0.15, 0.35, 0.55), fill=(0.97, 0.98, 1.0), width=0.8)
    shape.commit()
    
    # Header background inside box
    header_rect = fitz.Rect(39.7, start_y, 395.0, start_y + 14.0)
    shape_h = page.new_shape()
    shape_h.draw_rect(header_rect)
    shape_h.finish(color=(0.15, 0.35, 0.55), fill=(0.15, 0.35, 0.55), width=0.4)
    shape_h.commit()
    
    # Header Title Text
    page.insert_text(
        fitz.Point(44.0, start_y + 10.0),
        "DETAILS & TRACEABILITY OF MASTER INSTRUMENT",
        fontsize=7.5,
        fontname="hebo",
        color=(1, 1, 1)
    )
    
    # Row 1: Master Name
    y_row1 = start_y + 25.0
    page.insert_text(fitz.Point(44.0, y_row1), "MASTER INST:", fontsize=7, fontname="hebo", color=(0, 0, 0))
    page.insert_text(fitz.Point(105.0, y_row1), info["name"][:52], fontsize=7, fontname="helv", color=(0, 0, 0))
    
    # Row 2: Serial No & Valid Until
    y_row2 = start_y + 36.0
    page.insert_text(fitz.Point(44.0, y_row2), "SERIAL NO:", fontsize=7, fontname="hebo", color=(0, 0, 0))
    page.insert_text(fitz.Point(105.0, y_row2), info["serial"], fontsize=7, fontname="helv", color=(0, 0, 0))
    
    page.insert_text(fitz.Point(245.0, y_row2), "VALID UNTIL:", fontsize=7, fontname="hebo", color=(0, 0, 0))
    page.insert_text(fitz.Point(305.0, y_row2), info["valid"], fontsize=7, fontname="helv", color=(0, 0, 0))
    
    # Row 3: Range / Capacity
    y_row3 = start_y + 47.0
    page.insert_text(fitz.Point(44.0, y_row3), "RANGE / CAP:", fontsize=7, fontname="hebo", color=(0, 0, 0))
    page.insert_text(fitz.Point(105.0, y_row3), info["range"], fontsize=7, fontname="helv", color=(0, 0, 0))
    
    page.insert_text(fitz.Point(245.0, y_row3), "CALIB CERT:", fontsize=7, fontname="hebo", color=(0, 0, 0))
    page.insert_text(fitz.Point(305.0, y_row3), info["cert_no"], fontsize=7, fontname="helv", color=(0, 0, 0))

    # Row 4: Traceability
    y_row4 = start_y + 58.0
    page.insert_text(fitz.Point(44.0, y_row4), "TRACEABILITY:", fontsize=7, fontname="hebo", color=(0, 0, 0))
    page.insert_text(fitz.Point(105.0, y_row4), info["traceability"][:55], fontsize=6.8, fontname="helv", color=(0, 0, 0))

doc.save(output_temp_path)
doc.close()
os.replace(output_temp_path, pdf_path)
print(f"Successfully updated {pdf_path} directly with zero collisions!")
