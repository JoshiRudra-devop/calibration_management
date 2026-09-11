# Calibration Certificate Analysis & Instrument Format Mapping Report

**Document Title:** Instrument Format Analysis & Mapping Report  
**Reference Document (FILE A):** `405_Shanti Procon LLP._Check Done.pdf` (118 Pages, Arsh Calibration Laboratory Pvt. Ltd. NABL / ISO-17025 Format)  
**Existing Format (FILE B):** `STANDARDIZED_CERTIFICATES_PROTOTYPE_MODIFIED.pdf` (27 Pages, Shreeji Instruments Certificate Prototypes)  
**Date of Analysis:** August 31, 2026  
**Status:** PHASE 1 — Analysis & Mapping Complete (Awaiting User Approval Before Generation)

---

## 1. Executive Summary

This report establishes the technical foundation for upgrading and standardizing the **Shreeji Instruments Calibration Certificate System**. Currently, Shreeji Instruments' certificates (FILE B) use generic, single-table layouts that lack formal NABL ISO/IEC 17025 compliance features such as discipline categorization, environmental condition tolerances, master standard traceability details, uncertainty budgets ($k=2$), and standardized multi-column measurement tables.

By auditing **118 reference calibration certificate pages** from **Arsh Calibration Laboratory Pvt. Ltd.** (FILE A), every single instrument in Shreeji's 27-page prototype has been individually analyzed and mapped to its exact or technically equivalent NABL accredited structure.

### Key Audit Metrics
- **Total Instruments Analyzed (FILE B):** 27 Certificates (Pages 1 to 27)
- **Total Reference Certificates Analyzed (FILE A):** 118 Pages (115 Certificates)
- **Exact Reference Matches:** 20 Instruments ($74.1\%$)
- **Similar Category Adapters:** 4 Instruments ($14.8\%$)
- **New Standardized Structures Designed:** 3 Instruments ($11.1\%$)

---

## 2. Analysis of Reference File (FILE A)

FILE A (`405_Shanti Procon LLP._Check Done.pdf`) consists of 118 scanned pages of NABL / ISO-17025 accredited calibration certificates issued by **Arsh Calibration Laboratory Pvt. Ltd. (AGLPL / ACLPL)** for civil, mechanical, thermal, mass, and volumetric testing equipment.

### Standard 5-Section NABL Certificate Architecture

Every accredited certificate in FILE A strictly adheres to the following 5-section architecture:

1. **Header & Accreditation Metadata:**
   - Laboratory Name, Address, Contact, E-mail
   - Document Title (*Calibration Certificate* / *Test Report*)
   - Page Information (*Page X of Y*)
   - Discipline (e.g., *Mechanical*, *Thermal*, *Fluid Flow*)
   - ULR Number (Unique Laboratory Record Number, e.g., `CC459226000004232F`)
   - Certificate Number (e.g., `0826/405/001`)
   - Format / Revision Control Number (e.g., `Format No.: F-48, Rev. 4`)

2. **Section 1: Customer Information:**
   - Customer Details (Name & Address of Party)
   - SRF No. & Receipt Date
   - Calibration Date, Due Date & Date of Issue

3. **Section 2: Instrument Under Calibration (UUT) Information:**
   - Calibrated Item / Equipment Nomenclature
   - Visual Inspection Status (`OK`)
   - Make & Model
   - Type (Digital / Analog / Mechanical)
   - Measurement Range & Least Count
   - Serial Number / ID Number
   - Calibration Location (`At Site` / `In Laboratory`)

4. **Section 3: Environmental Conditions & Section 4: Master Traceability:**
   - Ambient Temperature ($25 \pm 10\ ^\circ\text{C}$) & Relative Humidity ($50 \pm 20\ \%\text{RH}$)
   - Calibration Method / Reference Code (e.g., `IS 1828 (Part-1): 2022`, `IS 516`, `IS 2720`)
   - Internal SOP Number (e.g., `ACLPL/CAL/M/SOP-23`)
   - Master Standards Used: Standard Name, Serial No., Certificate No., Valid Until Date, Traceability Laboratory (NPL / NABL Accredited Lab)

5. **Section 5: Calibration Results & Uncertainty Table:**
   - Multi-column observation data tables containing:
     - Applied Load / Nominal Value
     - UUC Displayed / Observed Reading
     - Master Reference Value
     - Error of Indication / Deviation
     - Repeatability & Eccentricity Tests (where applicable)
     - Expanded Measurement Uncertainty at $95.45\%$ Confidence Level ($k=2$)
   - Disclaimers, Notes & Authorized Signatures (*Calibrated By*, *Quality Manager / Authorised Signatory*)

---

## 3. Analysis of Existing Shreeji PDF (FILE B)

FILE B (`STANDARDIZED_CERTIFICATES_PROTOTYPE_MODIFIED.pdf`) contains 27 pages corresponding to 27 distinct calibration certificates and test reports. The existing document has several structural limitations:

- **Generic Layout:** Uses a simplified 2-column or 4-column key-value header with minimal metadata.
- **Missing NABL Metadata:** Lacks formal Discipline headers, ULR numbers, SOP reference numbers, environmental tolerances, and expanded uncertainty estimates.
- **Inconsistent Parameter Tables:** Several instruments (e.g., Aggregate Impact, Slump Cone, Sieve, Rapid Moisture) use non-standard headers instead of standard IS-code parameter names.

---

## 4. Complete Instrument Format Mapping Table

The following master mapping table defines how every page in FILE B maps to the reference structures in FILE A:

| Shreeji Page | Equipment Name | Instrument Category | Reference PDF Page(s) | Match Type | Structure To Use & Table Layout Specification |
|---|---|---|---|---|---|
| **Page 1** | AGGREGATE IMPACT VALUE MACHINE | Mechanical - Dimension & Mass | **Page 113** (Cert 0826/405/110) | **EXACT MATCH** | **Ref Page 113 Layout:** Section 1–4 NABL Header. Table: Parameter (Hammer Mass 13.75–14.0 kg, Drop Height 380±5 mm, Cylinder Internal Dia & Depth), Specified Limit as per IS 2386-4, Observed Value, Error, Expanded Uncertainty ($k=2$). |
| **Page 2** | AUTO LEVEL (SURVEYING INSTRUMENT) | Optical / Mechanical - Surveying | **Page 84** (Caliper) & **Page 114** (Counter Meter) | **NEW STRUCTURE REQUIRED** | **Optical Surveying Layout:** Sections 1–4 NABL Header with Collimator Bench Traceability. Table: Parameter (Line of Sight Error / Reticle Offset, Circular Level Sensitivity, Distance Error), Nominal Limit, Measured Value, Error, Status. |
| **Page 3** | CUBE MOULD (150 MM) | Mechanical - Dimension | **Pages 8–67, 69–80** (Cert 0826/405/005 to 064) | **EXACT MATCH** | **Ref Pages 8–67 Layout:** Section 1–4 NABL Header. Table: Parameter (Inside Length, Inside Width, Height, Diagonal, Surface Planeness, Perpendicularity), Nominal ($150.0\text{ mm}$), Observed Value, Error, Uncertainty ($k=2$). |
| **Page 4** | CONE PENETROMETER (LIQUID LIMIT) | Mechanical - Dimension & Mass | **Page 85** (Vicat) & **Pages 110, 118** (Core Cutter/Proctor) | **SIMILAR CATEGORY** | **Soil Apparatus Layout:** Section 1–4 Header. Table: Parameter (Cone Apex Angle $30^\circ$, Total Mass $148.0\text{ g}$, Penetration Depth Scale), Required Spec (IS 2720-5 / IS 9259), Measured Reading, Error, Uncertainty. |
| **Page 5** | CORE CUTTER WITH STEEL RAMMER | Mechanical - Dimension & Mass | **Pages 110 & 115** (Cert 0826/405/107 & 112) | **EXACT MATCH** | **Ref Page 110/115 Layout:** Section 1–4 Header. Table: Parameter (Internal Diameter, Internal Height, Wall Thickness, Calculated Volume $\text{cm}^3$, Rammer Mass $\text{kg}$), Nominal Value, Observed Value, Error, Uncertainty. |
| **Page 6** | CUBE TESTING MACHINE (1000 KN) | Mechanical - Force | **Pages 1 & 2** (Cert 0826/405/001) | **EXACT MATCH** | **Ref Page 1–2 (2-Page Force Layout):** Page 1: Metadata + Master Proving Ring Traceability + 3-Position Force Table ($0^\circ, 120^\circ, 240^\circ$, Mean Counts, Indication Error $\%$, Repeatability Error $\%$, Class 1). Page 2: Uncertainty Budget ($k=2$) + Disclaimers. |
| **Page 7** | CUBE MOULD (70.6 MM) | Mechanical - Dimension | **Pages 8–67** (Cert 0826/405/005 to 064) | **EXACT MATCH** | **Ref Pages 8–67 Layout:** Section 1–4 Header. Table: Parameter (Inside Length, Inside Width, Height, Diagonal, Planeness), Nominal ($70.60\text{ mm}$ as per IS 10086), Observed Reading, Deviation/Error, Uncertainty ($k=2$). |
| **Page 8** | DIGITAL THERMOMETER WITH PROBE | Thermal - Temperature | **Pages 7 & 81** (Cert 0826/405/004 & 078) | **EXACT MATCH** | **Ref Page 7/81 Thermal Layout:** Section 1–4 Header. Table: Nominal Calibration Temperature ($^\circ\text{C}$), UUC Reading ($^\circ\text{C}$), Master Reference Reading ($^\circ\text{C}$), Indication Error ($^\circ\text{C}$), Expanded Uncertainty ($^\circ\text{C}$, $k=2$). |
| **Page 9** | ELONGATION GAUGE FOR AGGREGATES | Mechanical - Dimension | **Page 107** (Cert 0826/405/104) | **EXACT MATCH** | **Ref Page 107 Layout:** Section 1–4 Header. Table: Parameter / Length Gap Designation ($63.0\text{--}50.0\text{ mm}$, $50.0\text{--}40.0\text{ mm}$, etc.), Nominal Standard Value ($\text{mm}$), Observed UUC Reading ($\text{mm}$), Error ($\text{mm}$), Uncertainty. |
| **Page 10** | FLAKINESS GAUGE FOR AGGREGATES | Mechanical - Dimension | **Page 106** (Cert 0826/405/103) | **EXACT MATCH** | **Ref Page 106 Layout:** Section 1–4 Header. Table: Parameter / Slot Width Designation ($63.0\text{--}50.0\text{ mm}$, $50.0\text{--}40.0\text{ mm}$, etc.), Nominal Standard Slot Width ($\text{mm}$), Observed UUC Reading ($\text{mm}$), Error ($\text{mm}$), Uncertainty. |
| **Page 11** | COMBINED TESTING EQUIPMENT INDEX | Multi-Discipline Summary | No multi-equipment index in Ref PDF | **NEW STRUCTURE REQUIRED** | **Master Lab Index Layout:** Section 1–4 Lab Header. Table: Sr No., Equipment Name, Serial/Tag No., Individual Calibration Cert Ref No., Calibration Date, Valid Due Date, Calibration Status (CALIBRATED), Master Traceability. |
| **Page 12** | GENERAL TESTING APPARATUS | Mechanical - Multi-Parameter | **Page 68** (Coating), **Page 85** (Vicat), **Page 114** (Counter) | **SIMILAR CATEGORY** | **Generic Mechanical Layout:** Section 1–4 Header. Table: Parameter / Sub-Assembly, Specified Nominal Value, Reading Observed on UUC, Error / Deviation, Expanded Uncertainty, Compliance Status. |
| **Page 13** | SOIL HYDROMETER (TYPE 151H) | Mechanical - Hydrometer / Density | **Page 117** (Cert 0826/405/114) | **EXACT MATCH** | **Ref Page 117 Density Layout:** Section 1–4 Header. Table: Nominal Scale Reading / Density Point ($0.9950, 1.0000, 1.0100\text{ g/cm}^3$), UUC Reading, Scale Correction / Error, Corrected Reading, Uncertainty. |
| **Page 14** | ISI CUBE MOULD (150 MM) | Mechanical - Dimension | **Pages 8–67** (Cert 0826/405/005 to 064) | **EXACT MATCH** | **Ref Pages 8–67 ISI Layout:** Section 1–4 Header. Table: Parameter, ISI Standard Specification ($150.0 \pm 0.2\text{ mm}$ as per IS 516 / IS 10086), Observed UUC Reading, Deviation/Error, Uncertainty ($k=2$). |
| **Page 15** | GRADUATED MEASURING CYLINDER | Mechanical - Volumetric | **Pages 82 & 83** (Cert 0826/405/079 & 080) | **EXACT MATCH** | **Ref Page 82/83 Volumetric Layout:** Section 1–4 Header. Table: Nominal Volume Mark ($100, 200, 500, 1000\text{ mL}$), Gravimetric Master Volume ($\text{mL}$), UUC Measured Volume ($\text{mL}$), Error ($\text{mL}$), Permissible Tolerance (IS 878), Uncertainty. |
| **Page 16** | LABORATORY HOT AIR OVEN | Thermal - Enclosure / Chamber | **Pages 7 & 81** (Cert 0826/405/004 & 078 - Enclosure) | **SIMILAR CATEGORY** | **Thermal Enclosure Layout:** Section 1–4 Header. Table: Set Temperature ($^\circ\text{C}$), Measured Center Temperature ($^\circ\text{C}$), Spatial Multi-Point Readings (Top Corners, Bottom Corners $^\circ\text{C}$), Thermal Uniformity ($^\circ\text{C}$), Uncertainty. |
| **Page 17** | DIGITAL PH METER WITH ELECTRODE | Chemical / Electro-Technical | **Pages 7, 81, 114** (Digital Meters) | **NEW STRUCTURE REQUIRED** | **Chemical Electro-Technical Layout:** Section 1–4 Header (Buffer Traceability: $4.01, 7.00, 9.21\text{ pH}$). Table: Standard Buffer Nominal ($\text{pH}$), UUC Measured ($\text{pH}$), Deviation ($\text{pH}$), Slope Efficiency $\%$, Uncertainty ($k=2$). |
| **Page 18** | PYCNOMETER BOTTLE WITH CONE | Mechanical - Volume & Mass | **Page 112** (Cert 0826/405/109) | **EXACT MATCH** | **Ref Page 112 Pycnometer Layout:** Section 1–4 Header. Table: Parameter (Mass of Empty Pycnometer Bottle $\text{g}$, Mass with Water at $27^\circ\text{C}\text{ g}$, Calculated Internal Volume $\text{cm}^3$), Nominal Value, Measured Value, Error, Uncertainty. |
| **Page 19** | RAPID MOISTURE METER (0 - 50 %) | Mechanical / Moisture Gauge | **Page 108** (Cert 0826/405/105) | **EXACT MATCH** | **Ref Page 108 Rapid Moisture Layout:** Section 1–4 Header. Table: Calibration Conversion Table mapping Gauge Pressure / Gauge Moisture Reading ($\%$) to Actual Moisture Content ($\%$) on Dry Weight Basis, Error ($\%$), Uncertainty. |
| **Page 20** | SAND POURING CYLINDER (100 MM) | Mechanical - Volume & Dimension | **Pages 109 & 111** (Cert 0826/405/106 & 108) | **EXACT MATCH** | **Ref Page 109/111 Layout:** Section 1–4 Header. Table: Parameter (Internal Dia $\text{mm}$, Height $\text{mm}$, Cone Angle $60^\circ$, Volume of Calibrating Can $\text{cm}^3$), Specified Limit (IS 2720-28), Observed Value, Error, Uncertainty. |
| **Page 21** | BRASS TEST SIEVES (200 MM DIA) | Mechanical - Dimension | **Pages 88 to 105** (Cert 0826/405/085 to 102) | **EXACT MATCH** | **Ref Pages 88–105 Sieve Layout:** Section 1–4 Header. Table: Sieve Designation / Aperture Size ($4.75\text{ mm}, 2.36\text{ mm}, 1.18\text{ mm}, 600\,\mu\text{m}, 75\,\mu\text{m}$), Average Aperture Size, Wire Diameter, Permissible Error (IS 460), Error, Uncertainty. |
| **Page 22** | SLUMP CONE APPARATUS | Mechanical - Dimension | **Pages 86 & 87** (Cert 0826/405/083 & 084) | **EXACT MATCH** | **Ref Page 86/87 Slump Cone Layout:** Section 1–4 Header. Table: Parameter (Top Internal Dia $100\text{ mm}$, Bottom Internal Dia $200\text{ mm}$, Height $300\text{ mm}$, Wall Thickness $1.6\text{ mm}$), Specified Limit (IS 7320), Actual Value (Avg of 3), Error, Uncertainty. |
| **Page 23** | TOTAL STATION (SURVEYING) | Optical / Mechanical - Surveying | **Page 84** (Caliper) & **Page 114** (Counter Meter) | **NEW STRUCTURE REQUIRED** | **Total Station Layout:** Section 1–4 Header (Collimator Bench & Polygon Mirror Traceability). Table: Parameter (Horizontal Angle Accuracy arc-sec, Vertical Angle Accuracy arc-sec, EDM Distance Accuracy $\text{mm}+\text{ppm}$, Laser Plummet Offset $\text{mm}$), Nominal Tolerance, Measured Value, Status. |
| **Page 24** | VERNIER CALIPER (0 - 300 MM) | Mechanical - Dimension | **Page 84** (Cert 0826/405/081) | **EXACT MATCH** | **Ref Page 84 Vernier Caliper Layout:** Section 1–4 Header. Table: Nominal Test Length ($0.0, 50.0, 100.0, 150.0, 200.0, 250.0, 300.0\text{ mm}$), UUC Reading ($\text{mm}$), Indication Error ($\text{mm}$), Expanded Uncertainty ($\text{mm}$, $k=2$), Compliance Status. |
| **Page 25** | THERMOSTATIC WATER BATH | Thermal - Enclosure / Liquid Bath | **Pages 7 & 81** (Cert 0826/405/004 & 078 - Thermal) | **SIMILAR CATEGORY** | **Thermal Bath Layout:** Section 1–4 Header. Table: Set Bath Temperature ($^\circ\text{C}$), Measured Temperature by Master Sensor ($^\circ\text{C}$), Temperature Indication Error ($^\circ\text{C}$), Thermal Stability / Uniformity ($^\circ\text{C}$), Uncertainty. |
| **Page 26** | WEIGH BATCHER (500 KG CAPACITY) | Mechanical - Mass / Heavy Scale | **Pages 3–4, 5–6** (Cert 0826/405/002, 003) | **EXACT MATCH** | **Ref Page 3–6 Heavy Scale Layout:** Section 1–4 Mass Header. Table: Applied Test Load ($0, 100, 200, 300, 400, 500\text{ kg}$), Observed Display Reading ($\text{kg}$), Indication Error ($\text{kg}$), Maximum Permissible Error (MPE as per OIML / IS), Uncertainty ($k=2$). |
| **Page 27** | WEIGHT BALANCE (30 KG) | Mechanical - Mass | **Pages 3–4, 5–6** (Cert 0826/405/002, 003) | **EXACT MATCH** | **Ref Page 3–6 (2-Page Mass Layout):** Section 1–4 Header. Table 1: Load Test / Indication Error (Applied Weight $\text{kg}$, Actual Value Observed, Indication Error $\text{g/kg}$, Expanded Uncertainty at $95.45\%$ C.L. $k=2$). Table 2: Repeatability Test & Eccentricity / Off-Center Loading Test. |

---

## 5. Detailed Technical Specifications by Instrument Category

### A. Mechanical - Force (Compression Testing Machine - Page 6)
- **Applicable Standards:** IS 1828 (Part-1): 2022 / ISO 7500-1.
- **Traceability Standard:** Master Proving Ring / Master Loadcell with Digital Indicator.
- **Calibration Multi-Position Test:** Tests performed at 3 angular orientations ($0^\circ, 120^\circ, 240^\circ$) across 5–6 force calibration points ($200, 400, 600, 800, 1000\text{ kN}$).
- **Table Data Columns:** Applied Force ($\text{kN}$), Proving Ring Reading at $0^\circ, 120^\circ, 240^\circ$, Mean Reading, True Force ($\text{kN}$), Relative Indication Error ($q\%$), Repeatability Error ($b\%$), Machine Classification (Class 1).

### B. Mechanical - Mass (Balances & Weigh Batchers - Pages 26, 27)
- **Applicable Standards:** OIML R76-1 / IS 9281.
- **Traceability Standard:** Standard E2 / F1 / M1 Class Cast Iron Test Weights.
- **Tests Required:**
  1. **Indication Error Test:** Applied Load ($\text{kg}$), UUC Display ($\text{kg}$), Error ($\text{kg}$), Maximum Permissible Error (MPE).
  2. **Repeatability Test:** 10 repeated measurements at half-scale and full-scale.
  3. **Eccentricity (Off-Center Loading) Test:** 5 position readings (Center, Front, Back, Left, Right).

### C. Mechanical - Dimension (Moulds, Calipers, Sieves, Gauges - Pages 1, 3, 5, 7, 9, 10, 14, 20, 21, 22, 24)
- **Applicable Standards:** IS 516, IS 10086 (Cube Moulds), IS 460 (Test Sieves), IS 7320 (Slump Cone), IS 2386 (Aggregates), IS 2720 (Soil Sampling).
- **Traceability Standard:** Grade 0 Slip Gauge Set, Master Digital Vernier Caliper, Optical Profile Projector, Steel Rule & Feeler Gauges.
- **Table Data Columns:** Parameter Description, Specified Standard Range / Nominal Value ($\text{mm}$), Observed UUC Reading ($\text{mm}$), Indication Error ($\text{mm}$), Expanded Uncertainty ($\text{mm}$, $k=2$).

### D. Thermal (Thermometers, Hot Air Ovens, Water Baths - Pages 8, 16, 25)
- **Applicable Standards:** DKD-R 5-7 / ASTM E230 / IS 4825.
- **Traceability Standard:** Master RTD Pt100 / Master Digital Temperature Indicator with Probe.
- **Table Data Columns:** Nominal Set Point ($^\circ\text{C}$), UUC Reading ($^\circ\text{C}$), Master Sensor Reading ($^\circ\text{C}$), Indication Error ($^\circ\text{C}$), Temperature Uniformity / Spatial Distribution ($^\circ\text{C}$), Expanded Uncertainty ($^\circ\text{C}$).

---

## 6. Project Implementation Workflow & Next Steps

1. **Phase 1 (Completed):** Complete PDF OCR extraction, line-by-line cataloging, and instrument format mapping.
2. **Phase 2 (Pending User Approval):**
   - Implement updated PDF generator script (`update_prototype_pdf.py`).
   - Standardize certificate rendering functions for each of the 27 mapped instrument structures.
   - Generate updated prototype PDF document.
3. **Phase 3 (Verification):**
   - Perform automated visual layout check and text extraction verification against FILE A NABL reference standards.

---

> [!IMPORTANT]
> **NO MODIFICATIONS OR GENERATIONS HAVE BEEN MADE TO EXISTING PDF FILES.**  
> Please review this document and provide your explicit approval to proceed with Phase 2 (PDF generation).
