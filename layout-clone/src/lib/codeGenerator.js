/**
 * Generates an ES6 module containing the jsPDF renderer and schema configuration.
 */
export function generateModuleCode(layout, metadata) {
  const { name = 'Certificate', isLabel = false, width = 210, height = 297 } = metadata;
  
  // Extract all unique fields and tables
  const fields = [];
  const tables = [];

  layout.pages.forEach(page => {
    page.elements.forEach(el => {
      if (el.type === 'dynamic' && el.fieldConfig) {
        if (!fields.some(f => f.key === el.fieldConfig.key)) {
          fields.push(el.fieldConfig);
        }
      } else if (el.type === 'table-header' && el.tableData) {
        const tableKey = cleanKey(el.text);
        if (!tables.some(t => t.key === tableKey)) {
          tables.push({
            key: tableKey,
            label: el.text,
            columns: el.tableData.columns
          });
        }
      }
    });
  });

  // Build the code for drawing elements page-by-page
  let pagesRenderCode = '';
  
  layout.pages.forEach((page, pageIdx) => {
    if (pageIdx > 0) {
      pagesRenderCode += '  doc.addPage();\n';
    }

    // Sort elements by Y coordinate to render top-to-bottom
    const sortedElements = [...page.elements].sort((a, b) => a.top - b.top);

    sortedElements.forEach(el => {
      if (el.type === 'static') {
        const fontStyle = el.isBold ? 'bold' : el.isItalic ? 'italic' : 'normal';
        pagesRenderCode += `
  // Static Label
  doc.setFont("helvetica", "${fontStyle}");
  doc.setFontSize(${el.fontSize || 11});`;

        if (el.align === 'center') {
          pagesRenderCode += `
  doc.text(${JSON.stringify(el.text)}, ${el.left + el.width / 2}, ${el.top + el.height}, { align: 'center' });\n`;
        } else {
          pagesRenderCode += `
  doc.text(${JSON.stringify(el.text)}, ${el.left}, ${el.top + el.height});\n`;
        }
      } else if (el.type === 'dynamic' && el.fieldConfig) {
        const fontStyle = el.isBold ? 'bold' : el.isItalic ? 'italic' : 'normal';
        const key = el.fieldConfig.key;
        const defaultValue = el.fieldConfig.defaultValue || '';
        
        pagesRenderCode += `
  // Dynamic Value: ${el.fieldConfig.label}
  doc.setFont("helvetica", "${fontStyle}");
  doc.setFontSize(${el.fontSize || 11});`;

        if (el.fieldConfig.fieldType === 'date') {
          pagesRenderCode += `
  const val_${key} = formatDate(details.${key} !== undefined ? details.${key} : ${JSON.stringify(defaultValue)});`;
        } else {
          pagesRenderCode += `
  const val_${key} = String(details.${key} !== undefined ? details.${key} : ${JSON.stringify(defaultValue)});`;
        }

        pagesRenderCode += `
  doc.text(val_${key}, ${el.left}, ${el.top + el.height});\n`;
      } else if (el.type === 'table-header' && el.tableData) {
        const tableKey = cleanKey(el.text);
        const headers = el.tableData.columns.map(c => c.label);
        const columnKeys = el.tableData.columns.map(c => c.key);

        pagesRenderCode += `
  // Table: ${el.text}
  const tableHeaders_${tableKey} = ${JSON.stringify([headers])};
  const tableBody_${tableKey} = (details.${tableKey} || []).map(row => [
    ${columnKeys.map(k => `row.${k} !== undefined ? String(row.${k}) : ''`).join(',\n    ')}
  ]);
  
  doc.autoTable({
    head: tableHeaders_${tableKey},
    body: tableBody_${tableKey},
    startY: ${el.top},
    styles: { 
      fontSize: 10,
      lineColor: [0, 0, 0],
      textColor: [0, 0, 0],
      lineWidth: 0.1,
      halign: 'center',
      valign: 'middle'
    },
    headStyles: { 
      fillColor: [240, 240, 240],
      textColor: [0, 0, 0],
      lineColor: [0, 0, 0],
      lineWidth: 0.1
    },
    alternateRowStyles: {
      fillColor: [255, 255, 255]
    }
  });\n`;
      }
    });
  });

  // Assemble full module code
  const code = `import { jsPDF } from 'jspdf';
import 'jspdf-autotable';

// Auto-generated layout schema representing the document mapping
export const templateSchema = {
  name: ${JSON.stringify(name)},
  isLabel: ${isLabel},
  width: ${width},
  height: ${height},
  fields: ${JSON.stringify(fields, null, 2)},
  tables: ${JSON.stringify(tables, null, 2)}
};

// Date formatter helper (converts YYYY-MM-DD from React inputs to DD/MM/YYYY)
function formatDate(dateStr) {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length === 3) {
    return \`\${parts[2]}/\${parts[1]}/\${parts[0]}\`;
  }
  return dateStr;
}

/**
 * Renders the ${name} PDF document using jsPDF + jspdf-autotable.
 * @param {Object} details - Dynamic field mappings populated by the user.
 * @returns {jsPDF} doc
 */
export function generateCertificatePDF(details = {}) {
  const doc = new jsPDF({
    orientation: ${width > height ? "'l'" : "'p'"},
    unit: 'mm',
    format: [${width}, ${height}]
  });

${pagesRenderCode}
  return doc;
}
`;

  return code;
}

function cleanKey(label) {
  return label
    .toLowerCase()
    .replace(/[^a-z0-9\s]/g, '')
    .trim()
    .split(/\s+/)
    .map((word, index) => index === 0 ? word : word.charAt(0).toUpperCase() + word.slice(1))
    .join('');
}
