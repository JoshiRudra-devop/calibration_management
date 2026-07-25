import * as pdfjs from 'pdfjs-dist';
import mammoth from 'mammoth';
import { createWorker } from 'tesseract.js';

// Setup pdf.js worker using unpkg CDN matching the installed version (6.1.200)
pdfjs.GlobalWorkerOptions.workerSrc = 'https://unpkg.com/pdfjs-dist@6.1.200/build/pdf.worker.min.mjs';

// Helper to convert PDF points (1/72 inch) to millimeters
const PT_TO_MM = 25.4 / 72;

/**
 * Parses a PDF file to extract text items and group them into lines and layouts.
 */
export async function parsePDF(file) {
  const arrayBuffer = await file.arrayBuffer();
  const loadingTask = pdfjs.getDocument({ data: new Uint8Array(arrayBuffer) });
  const pdf = await loadingTask.promise;
  const pages = [];

  for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
    const page = await pdf.getPage(pageNum);
    const viewport = page.getViewport({ scale: 1.0 });
    const widthMm = viewport.width * PT_TO_MM;
    const heightMm = viewport.height * PT_TO_MM;

    const textContent = await page.getTextContent();
    const rawItems = textContent.items.map(item => {
      // transform matrix: [scaleX, skewX, skewY, scaleY, transX, transY]
      const tx = item.transform[4];
      const ty = item.transform[5];
      const fontSize = Math.sqrt(item.transform[0] ** 2 + item.transform[1] ** 2);
      
      // Convert to top-left origin coordinate system
      const left = tx * PT_TO_MM;
      const top = (viewport.height - ty - fontSize) * PT_TO_MM;
      const width = item.width * PT_TO_MM;
      const height = fontSize * PT_TO_MM;

      return {
        text: item.str,
        left,
        top,
        width,
        height,
        fontSize,
        fontName: item.fontName || 'sans-serif'
      };
    });

    // Cluster items into lines and merge horizontal neighbors
    const mergedLines = clusterAndMergeItems(rawItems, fontSize => fontSize * 0.4);

    // Apply static/dynamic heuristics and build schema elements
    const elements = processTextElements(mergedLines);

    pages.push({
      pageNum,
      width: widthMm,
      height: heightMm,
      elements
    });
  }

  return {
    type: 'pdf',
    pages,
    isLabel: pages[0]?.width < 100 // treats small pages as label layouts
  };
}

/**
 * Parses a DOCX file using mammoth to extract HTML structure, then builds a flowing layout.
 */
export async function parseDOCX(file) {
  const arrayBuffer = await file.arrayBuffer();
  const result = await mammoth.convertToHtml({ arrayBuffer });
  const htmlContent = result.value;

  // Render on a virtual A4 page (210mm x 297mm)
  const width = 210;
  const height = 297;
  const elements = [];
  let currentY = 25; // 25mm top margin
  const marginX = 20; // 20mm side margin

  // Parse HTML string using standard browser DOMParser
  const parser = new DOMParser();
  const doc = parser.parseFromString(htmlContent, 'text/html');

  // Traverse children of body
  const children = Array.from(doc.body.children);
  for (const child of children) {
    if (currentY > height - 25) {
      // Very basic paging boundary
      currentY = 25;
    }

    if (child.tagName === 'TABLE') {
      // Extract table headers and cells
      const rows = Array.from(child.querySelectorAll('tr'));
      if (rows.length > 0) {
        const headers = Array.from(rows[0].querySelectorAll('td, th')).map(cell => cell.textContent.trim());
        
        // Add header element representation
        elements.push({
          type: 'table-header',
          text: `Table Columns: ${headers.join(' | ')}`,
          left: marginX,
          top: currentY,
          width: width - (marginX * 2),
          height: 10,
          fontSize: 12,
          isBold: true,
          tableData: {
            columns: headers.map(h => ({ label: h, key: cleanKey(h) })),
            rows: []
          }
        });
        currentY += 15;
      }
    } else {
      const text = child.textContent.trim();
      if (!text) continue;

      const isHeading = ['H1', 'H2', 'H3', 'H4'].includes(child.tagName);
      const fontSize = isHeading ? 16 : 11;
      const isBold = isHeading || child.querySelector('strong, b') !== null;
      const isItalic = child.querySelector('em, i') !== null;

      // Assign coordinates
      elements.push({
        type: 'text',
        text,
        left: marginX,
        top: currentY,
        width: width - (marginX * 2),
        height: fontSize * 0.4,
        fontSize,
        isBold,
        isItalic
      });

      currentY += (fontSize * 0.4) + 6; // Move Y down by element height + line spacing
    }
  }

  // Apply heuristics to classify runs
  const processedElements = processTextElements(elements);

  return {
    type: 'docx',
    pages: [{
      pageNum: 1,
      width,
      height,
      elements: processedElements
    }],
    isLabel: false
  };
}

/**
 * Parses an Image using Tesseract.js OCR.
 */
export async function parseImage(file) {
  const worker = await createWorker('eng');
  const ret = await worker.recognize(file);
  await worker.terminate();

  const { lines, width: imgWidth, height: imgHeight } = ret.data;
  
  // Scale to standard A4 (210mm x 297mm)
  const a4Width = 210;
  const a4Height = 297;
  const scaleX = a4Width / imgWidth;
  const scaleY = a4Height / imgHeight;

  const elements = lines.map(line => {
    const box = line.bbox; // { x0, y0, x1, y1 }
    const left = box.x0 * scaleX;
    const top = box.y0 * scaleY;
    const width = (box.x1 - box.x0) * scaleX;
    const height = (box.y1 - box.y0) * scaleY;
    
    // Convert height to approx pt size (1mm ~ 2.83 pt)
    const fontSize = Math.max(9, Math.round(height * 2.83));

    return {
      type: 'text',
      text: line.text.trim(),
      left,
      top,
      width,
      height,
      fontSize,
      isBold: line.text.toLowerCase().includes('report') || line.text.toLowerCase().includes('certificate')
    };
  });

  const processedElements = processTextElements(elements);

  return {
    type: 'image',
    pages: [{
      pageNum: 1,
      width: a4Width,
      height: a4Height,
      elements: processedElements
    }],
    isLabel: false
  };
}

/**
 * Clusters text runs vertically and horizontally to assemble unified text lines.
 */
function clusterAndMergeItems(items, thresholdFn) {
  if (items.length === 0) return [];

  // 1. Group items by vertical rows (similar top coordinate)
  const rows = [];
  // Sort items primarily by top position
  const sortedItems = [...items].sort((a, b) => a.top - b.top);

  sortedItems.forEach(item => {
    let placed = false;
    for (const row of rows) {
      // Find row with similar vertical band
      const averageTop = row.reduce((sum, i) => sum + i.top, 0) / row.length;
      const threshold = thresholdFn(item.fontSize);
      if (Math.abs(item.top - averageTop) < threshold) {
        row.push(item);
        placed = true;
        break;
      }
    }
    if (!placed) {
      rows.push([item]);
    }
  });

  // 2. Sort each row horizontally and merge neighboring text blocks
  const mergedElements = [];

  rows.forEach(row => {
    // Sort columns left-to-right
    row.sort((a, b) => a.left - b.left);

    let current = { ...row[0] };
    mergedElements.push(current);

    for (let j = 1; j < row.length; j++) {
      const next = row[j];
      const spaceThreshold = current.fontSize * 0.8 * PT_TO_MM; // allowed spacing gap
      const distance = next.left - (current.left + current.width);

      if (distance < spaceThreshold && distance >= -1) {
        // Merge texts together
        current.text += (distance > 1 ? ' ' : '') + next.text;
        current.width = (next.left + next.width) - current.left;
        current.height = Math.max(current.height, next.height);
      } else {
        // Start a new text block on the same line
        current = { ...next };
        mergedElements.push(current);
      }
    }
  });

  return mergedElements;
}

/**
 * Applies heuristics to classify text elements as static vs dynamic, splitting inputs if needed.
 */
function processTextElements(elements) {
  const processed = [];

  elements.forEach(el => {
    const text = el.text.trim();
    if (!text) return;

    // Check for common label patterns: ":-", ": ", "=", "___"
    const splitIndex = findSplitIndex(text);

    if (splitIndex !== -1) {
      // Split into Static Label and Dynamic Field Value
      const labelPart = text.substring(0, splitIndex).trim();
      const valPart = text.substring(splitIndex).trim();

      // Static label element
      processed.push({
        ...el,
        type: 'static',
        text: labelPart,
        width: el.width * (splitIndex / text.length)
      });

      // Dynamic value element
      const cleanVal = valPart.replace(/^[:-=\s]+|[:-=\s]+$/g, '').trim();
      const cleanLabel = labelPart.replace(/[:-=\s]+$/g, '').trim();
      const key = cleanKey(cleanLabel);
      
      processed.push({
        ...el,
        type: 'dynamic',
        text: cleanVal || `[${cleanLabel}]`,
        left: el.left + el.width * (splitIndex / text.length),
        width: el.width * (1 - (splitIndex / text.length)),
        fieldConfig: {
          key,
          label: cleanLabel,
          fieldType: inferFieldType(cleanLabel, cleanVal),
          defaultValue: cleanVal
        }
      });
    } else {
      // Fallback classification
      const isDynamic = isProbablyDynamic(text);
      if (isDynamic) {
        const key = cleanKey(text);
        processed.push({
          ...el,
          type: 'dynamic',
          fieldConfig: {
            key,
            label: text,
            fieldType: inferFieldType(text, text),
            defaultValue: text
          }
        });
      } else {
        processed.push({
          ...el,
          type: 'static'
        });
      }
    }
  });

  return processed;
}

function findSplitIndex(text) {
  const markers = [':-', ':', '='];
  let minIndex = -1;

  for (const marker of markers) {
    const idx = text.indexOf(marker);
    if (idx !== -1) {
      if (minIndex === -1 || idx < minIndex) {
        minIndex = idx + marker.length;
      }
    }
  }

  // Fallback check for long underscores (like "Name __________")
  const underIdx = text.search(/_{3,}/);
  if (underIdx !== -1) {
    if (minIndex === -1 || underIdx < minIndex) {
      minIndex = underIdx;
    }
  }

  return minIndex;
}

function isProbablyDynamic(text) {
  // Common placeholders or variable-like formatting
  if (text.startsWith('[') && text.endsWith(']')) return true;
  if (text.match(/^\d{2}\/\d{2}\/\d{4}$/)) return true; // Date format DD/MM/YYYY
  if (text.match(/^\d{4}-\d{2}-\d{2}$/)) return true; // Date format YYYY-MM-DD
  return false;
}

function cleanKey(label) {
  // camelCase generator from string label
  return label
    .toLowerCase()
    .replace(/[^a-z0-9\s]/g, '')
    .trim()
    .split(/\s+/)
    .map((word, index) => index === 0 ? word : word.charAt(0).toUpperCase() + word.slice(1))
    .join('');
}

function inferFieldType(label, val) {
  const lbl = label.toLowerCase();
  const v = val.toLowerCase();

  if (lbl.includes('date')) return 'date';
  if (lbl.includes('qty') || lbl.includes('quantity') || lbl.includes('number') || lbl.includes('count')) return 'number';
  if (lbl.includes('size') || lbl.includes('select') || lbl.includes('option')) return 'select';
  if (v === 'true' || v === 'false') return 'boolean';
  return 'text';
}

/**
 * Returns a static mockup schema of a Calibration Certificate for instant testing.
 */
export function getMockCertificateLayout() {
  return {
    type: 'pdf',
    pages: [{
      pageNum: 1,
      width: 210,
      height: 297,
      elements: [
        { type: 'static', text: 'SHREEJI INSTRUMENTS', left: 20, top: 25, width: 170, height: 10, fontSize: 22, isBold: true, align: 'center' },
        { type: 'static', text: 'CALIBRATION REPORT FOR SIEVE MOULD', left: 20, top: 40, width: 170, height: 8, fontSize: 16, isBold: true, align: 'center' },
        
        { type: 'static', text: 'Certificate No:', left: 20, top: 60, width: 35, height: 5, fontSize: 11, isBold: true },
        {
          type: 'dynamic',
          text: 'CM-260701',
          left: 55,
          top: 60,
          width: 40,
          height: 5,
          fontSize: 11,
          fieldConfig: { key: 'certificateNumber', label: 'Certificate No', fieldType: 'text', defaultValue: 'CM-260701' }
        },
        
        { type: 'static', text: 'Calibration Date:', left: 115, top: 60, width: 35, height: 5, fontSize: 11, isBold: true },
        {
          type: 'dynamic',
          text: '2026-07-25',
          left: 150,
          top: 60,
          width: 40,
          height: 5,
          fontSize: 11,
          fieldConfig: { key: 'calibrationDate', label: 'Calibration Date', fieldType: 'date', defaultValue: '2026-07-25' }
        },

        { type: 'static', text: 'Name of Party:', left: 20, top: 70, width: 35, height: 5, fontSize: 11, isBold: true },
        {
          type: 'dynamic',
          text: 'LARSEN & TOUBRO LTD',
          left: 55,
          top: 70,
          width: 135,
          height: 5,
          fontSize: 11,
          fieldConfig: { key: 'partyName', label: 'Name of Party', fieldType: 'text', defaultValue: 'LARSEN & TOUBRO LTD' }
        },

        { type: 'static', text: 'Site Location:', left: 20, top: 80, width: 35, height: 5, fontSize: 11, isBold: true },
        {
          type: 'dynamic',
          text: 'AHMEDABAD METRO PROJECT',
          left: 55,
          top: 80,
          width: 135,
          height: 5,
          fontSize: 11,
          fieldConfig: { key: 'siteLocation', label: 'Site Location', fieldType: 'text', defaultValue: 'AHMEDABAD METRO PROJECT' }
        },
        
        { type: 'static', text: 'Next Suggested Date:', left: 20, top: 90, width: 45, height: 5, fontSize: 11, isBold: true },
        {
          type: 'dynamic',
          text: '2027-07-24',
          left: 65,
          top: 90,
          width: 40,
          height: 5,
          fontSize: 11,
          fieldConfig: { key: 'nextCalibrationDate', label: 'Next Suggested Date', fieldType: 'date', defaultValue: '2027-07-24' }
        },

        // Represent table columns
        {
          type: 'table-header',
          text: 'Calibration Results Table',
          left: 20,
          top: 110,
          width: 170,
          height: 10,
          fontSize: 12,
          isBold: true,
          tableData: {
            columns: [
              { label: 'SR.NO', key: 'srNo', type: 'text' },
              { label: 'LENGTH (mm)', key: 'length', type: 'number' },
              { label: 'HEIGHT (mm)', key: 'height', type: 'number' },
              { label: 'WIDTH (mm)', key: 'width', type: 'number' }
            ],
            rows: [
              { srNo: '1', length: '150.1', height: '150.0', width: '150.2' },
              { srNo: '2', length: '150.0', height: '149.9', width: '150.1' },
              { srNo: '3', length: '150.2', height: '150.1', width: '150.0' }
            ]
          }
        },

        { type: 'static', text: 'CALIBRATED BY: YOGESH B JOSHI', left: 20, top: 220, width: 80, height: 5, fontSize: 11 },
        { type: 'static', text: 'FOR, SHREEJI INSTRUMENTS', left: 130, top: 220, width: 60, height: 5, fontSize: 11, isBold: true },
        { type: 'static', text: 'PROPRIETOR', left: 145, top: 235, width: 40, height: 5, fontSize: 11, isBold: true }
      ]
    }],
    isLabel: false
  };
}
