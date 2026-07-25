# Layout Clone — Field Mapping JSON Schema Documentation

This document explains the schema structure used by the **Layout Clone** generator. It defines the layout, coordinate mapping, and data bindings of certificates, stickers, or reports.

Using this schema, developers can define a template **manually** (without uploading a document) and load it directly into the generator or drop the generated jsPDF module into another codebase.

---

## 1. Top-Level Structure

A layout template is represented as a single JSON object:

```json
{
  "type": "pdf",
  "isLabel": false,
  "pages": [
    {
      "pageNum": 1,
      "width": 210,
      "height": 297,
      "elements": []
    }
  ]
}
```

### Properties:
* **`type`**: The file origin type (`"pdf"` | `"docx"` | `"image"`).
* **`isLabel`**: Boolean flag. If `true`, indicates a compact 40mm x 30mm sticker/label layout. If `false`, indicates standard A4.
* **`pages`**: Array of page objects containing dimensions and children elements.

---

## 2. Page Structure

Each page inside the `pages` array has:
* **`pageNum`**: 1-based index of the page.
* **`width`**: Page width in millimeters (e.g., `210` for A4 portrait).
* **`height`**: Page height in millimeters (e.g., `297` for A4 portrait).
* **`elements`**: Array of text blocks and tables positioned on the page.

---

## 3. Element Types

Elements inside the `elements` array represent individual visual spans, labels, inputs, or tables. There are three types:

### A. Static Text (`type: "static"`)
Used for constant boilerplate, headers, and labels that remain unchanged across all certificate runs.

```json
{
  "type": "static",
  "text": "Certificate No:",
  "left": 20,
  "top": 60,
  "width": 35,
  "height": 5,
  "fontSize": 11,
  "isBold": true
}
```

### B. Dynamic Field (`type: "dynamic"`)
Binds a visual block to a form input field. The text is replaced at runtime with the value submitted in the details data.

```json
{
  "type": "dynamic",
  "text": "CM-260701",
  "left": 55,
  "top": 60,
  "width": 40,
  "height": 5,
  "fontSize": 11,
  "fieldConfig": {
    "key": "certificateNumber",
    "label": "Certificate No",
    "fieldType": "text",
    "defaultValue": "CM-260701"
  }
}
```

#### `fieldConfig` Parameters:
* **`key`**: The variable key (camelCase) to bind the field in the Javascript details object.
* **`label`**: The human-readable name of the input field shown in the React form.
* **`fieldType`**: The HTML input representation (`"text"` | `"number"` | `"date"` | `"select"` | `"boolean"`).
* **`defaultValue`**: The prefilled value on page load.

### C. Data Table (`type: "table-header"`)
Triggers rendering of a responsive data table using `jspdf-autotable`.

```json
{
  "type": "table-header",
  "text": "Calibration Results Table",
  "left": 20,
  "top": 110,
  "width": 170,
  "height": 10,
  "fontSize": 12,
  "isBold": true,
  "tableData": {
    "columns": [
      { "label": "SR.NO", "key": "srNo", "type": "text" },
      { "label": "LENGTH (mm)", "key": "length", "type": "number" },
      { "label": "HEIGHT (mm)", "key": "height", "type": "number" },
      { "label": "WIDTH (mm)", "key": "width", "type": "number" }
    ],
    "rows": [
      { "srNo": "1", "length": "150.1", "height": "150.0", "width": "150.2" }
    ]
  }
}
```

#### `tableData` Parameters:
* **`columns`**: Array of column definition objects with header labels, variable keys, and types.
* **`rows`**: Pre-filled template rows.

---

## 4. Coordinate Conversion Guidelines (PDF ↔ jsPDF)

* **PDF coordinate origin**: Bottom-left of the page. Units are in points (1 pt = 1/72 inch).
* **jsPDF coordinate origin**: Top-left of the page. Units are typically in millimeters.
* **Conversion math**:
  * `width_mm = pdf_width_points * (25.4 / 72)`
  * `height_mm = pdf_height_points * (25.4 / 72)`
  * `x_mm = pdf_x_points * (25.4 / 72)`
  * `y_mm = (pdf_page_height_points - pdf_y_points - pdf_font_size) * (25.4 / 72)`
