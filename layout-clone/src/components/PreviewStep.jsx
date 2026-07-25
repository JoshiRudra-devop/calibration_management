import React, { useState, useEffect, useRef } from 'react';
import { Download, Code as CodeIcon, Copy, Check, FileText, Plus, Trash2, ArrowLeft, Edit3 } from 'lucide-react';

export default function PreviewStep({ layout, metadata, generatedCode, onUpdateLayout, onBack }) {
  const [formDetails, setFormDetails] = useState({});
  const [pdfUrl, setPdfUrl] = useState('');
  const [activeTab, setActiveTab] = useState('preview'); // 'preview' | 'code'
  const [leftPanelMode, setLeftPanelMode] = useState('form'); // 'form' | 'editor'
  const [editorPageIdx, setEditorPageIdx] = useState(0);
  const [copied, setCopied] = useState(false);

  const editorCanvasRef = useRef(null);
  const [editorCanvasWidth, setEditorCanvasWidth] = useState(400);

  // ResizeObserver to scale fonts in inline editor
  useEffect(() => {
    if (editorCanvasRef.current) {
      const resizeObserver = new ResizeObserver(entries => {
        for (let entry of entries) {
          setEditorCanvasWidth(entry.contentRect.width);
        }
      });
      resizeObserver.observe(editorCanvasRef.current);
      return () => resizeObserver.disconnect();
    }
  }, [leftPanelMode]);

  // Extract schemas
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
            columns: el.tableData.columns,
            defaultRows: el.tableData.rows || []
          });
        }
      }
    });
  });

  // Populate default form details
  useEffect(() => {
    const defaults = {};
    fields.forEach(f => {
      defaults[f.key] = f.defaultValue || '';
    });
    tables.forEach(t => {
      defaults[t.key] = t.defaultRows.length > 0 ? t.defaultRows : [
        { srNo: '1', length: '150.0', height: '150.0', width: '150.0' }
      ];
    });
    setFormDetails(defaults);
  }, [layout]);

  // Update PDF preview on formDetails changes
  useEffect(() => {
    if (Object.keys(formDetails).length === 0) return;

    try {
      const doc = localGeneratePDF(formDetails, layout, metadata);
      const blob = doc.output('blob');
      
      if (pdfUrl) {
        URL.revokeObjectURL(pdfUrl);
      }
      const newUrl = URL.createObjectURL(blob);
      setPdfUrl(newUrl);
    } catch (err) {
      console.error('Failed to generate preview PDF:', err);
    }

    return () => {
      if (pdfUrl) URL.revokeObjectURL(pdfUrl);
    };
  }, [formDetails, layout, metadata]);

  const handleInputChange = (key, value) => {
    setFormDetails(prev => ({
      ...prev,
      [key]: value
    }));
  };

  const handleInlineTextChange = (pageIdx, elIdx, newText, isDynamic) => {
    const newPages = [...layout.pages];
    const el = newPages[pageIdx].elements[elIdx];
    
    if (isDynamic && el.fieldConfig) {
      newPages[pageIdx].elements[elIdx] = {
        ...el,
        text: newText,
        fieldConfig: {
          ...el.fieldConfig,
          defaultValue: newText
        }
      };
      
      // Also update the form details immediately so the preview updates!
      setFormDetails(prev => ({
        ...prev,
        [el.fieldConfig.key]: newText
      }));
    } else {
      newPages[pageIdx].elements[elIdx] = {
        ...el,
        text: newText
      };
    }
    
    // Call parent handler to update layout and re-generate code!
    onUpdateLayout({ ...layout, pages: newPages });
  };

  const handleAddTableRow = (tableKey, columns) => {
    const currentRows = formDetails[tableKey] || [];
    const newRow = {};
    columns.forEach(col => {
      if (col.key === 'srNo') {
        newRow[col.key] = String(currentRows.length + 1);
      } else {
        newRow[col.key] = '';
      }
    });

    setFormDetails(prev => ({
      ...prev,
      [tableKey]: [...currentRows, newRow]
    }));
  };

  const handleRemoveTableRow = (tableKey, rowIdx) => {
    const currentRows = formDetails[tableKey] || [];
    const updated = currentRows.filter((_, idx) => idx !== rowIdx).map((row, idx) => {
      if (row.srNo !== undefined) {
        return { ...row, srNo: String(idx + 1) };
      }
      return row;
    });

    setFormDetails(prev => ({
      ...prev,
      [tableKey]: updated
    }));
  };

  const handleTableCellChange = (tableKey, rowIdx, colKey, value) => {
    const currentRows = [...(formDetails[tableKey] || [])];
    currentRows[rowIdx] = {
      ...currentRows[rowIdx],
      [colKey]: value
    };

    setFormDetails(prev => ({
      ...prev,
      [tableKey]: currentRows
    }));
  };

  const handleDownloadPDF = () => {
    const doc = localGeneratePDF(formDetails, layout, metadata);
    doc.save(`${metadata.name.replace(/\s+/g, '_')}_Report.pdf`);
  };

  const handleDownloadCode = () => {
    const blob = new Blob([generatedCode], { type: 'text/javascript;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${cleanKey(metadata.name)}Template.js`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  };

  const handleCopyCode = () => {
    navigator.clipboard.writeText(generatedCode);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const currentPage = layout.pages[editorPageIdx];

  return (
    <div className="preview-container animate-fade-in">
      <div className="preview-header">
        <button className="btn-back animate-hover" onClick={onBack}>
          <ArrowLeft size={16} />
          <span>Back to Mapper</span>
        </button>
        <h2>{metadata.name} Template</h2>
        
        <div className="tab-group">
          <button 
            className={`tab-item ${activeTab === 'preview' ? 'active' : ''}`}
            onClick={() => setActiveTab('preview')}
          >
            Form & PDF Preview
          </button>
          <button 
            className={`tab-item ${activeTab === 'code' ? 'active' : ''}`}
            onClick={() => setActiveTab('code')}
          >
            <CodeIcon size={16} />
            Generated Code
          </button>
        </div>
      </div>

      {activeTab === 'preview' ? (
        <div className="preview-grid">
          {/* Left Configuration Panel */}
          <div className="card glass-card form-panel">
            <div className="panel-mode-selector">
              <button 
                className={`mode-btn ${leftPanelMode === 'form' ? 'active' : ''}`}
                onClick={() => setLeftPanelMode('form')}
              >
                <FileText size={16} />
                <span>Fill Out Form</span>
              </button>
              <button 
                className={`mode-btn ${leftPanelMode === 'editor' ? 'active' : ''}`}
                onClick={() => setLeftPanelMode('editor')}
              >
                <Edit3 size={16} />
                <span>WYSIWYG Word Editor</span>
              </button>
            </div>

            {leftPanelMode === 'form' ? (
              <form className="dynamic-form" onSubmit={(e) => e.preventDefault()}>
                <h3>Form Data Fields</h3>
                <div className="form-fields-grid">
                  {fields.map(f => (
                    <div key={f.key} className="form-group">
                      <label>{f.label}</label>
                      {f.fieldType === 'date' ? (
                        <input 
                          type="date"
                          value={formDetails[f.key] || ''}
                          onChange={(e) => handleInputChange(f.key, e.target.value)}
                        />
                      ) : f.fieldType === 'number' ? (
                        <input 
                          type="number"
                          value={formDetails[f.key] || ''}
                          onChange={(e) => handleInputChange(f.key, e.target.value)}
                        />
                      ) : f.fieldType === 'select' ? (
                        <select 
                          value={formDetails[f.key] || ''}
                          onChange={(e) => handleInputChange(f.key, e.target.value)}
                        >
                          <option value={f.defaultValue}>{f.defaultValue || 'Select...'}</option>
                          <option value="Option A">Option A</option>
                          <option value="Option B">Option B</option>
                        </select>
                      ) : f.fieldType === 'boolean' ? (
                        <div className="checkbox-wrapper">
                          <input 
                            type="checkbox"
                            checked={formDetails[f.key] === true}
                            onChange={(e) => handleInputChange(f.key, e.target.checked)}
                          />
                          <span>Enable Field</span>
                        </div>
                      ) : (
                        <input 
                          type="text"
                          value={formDetails[f.key] || ''}
                          onChange={(e) => handleInputChange(f.key, e.target.value)}
                        />
                      )}
                    </div>
                  ))}
                </div>

                {/* Dynamic Tables */}
                {tables.map(t => (
                  <div key={t.key} className="form-table-group">
                    <h4>{t.label}</h4>
                    <div className="table-wrapper">
                      <table>
                        <thead>
                          <tr>
                            {t.columns.map(col => (
                              <th key={col.key}>{col.label}</th>
                            ))}
                            <th style={{ width: '40px' }}></th>
                          </tr>
                        </thead>
                        <tbody>
                          {(formDetails[t.key] || []).map((row, rowIdx) => (
                            <tr key={rowIdx}>
                              {t.columns.map(col => (
                                <td key={col.key}>
                                  <input 
                                    type="text"
                                    value={row[col.key] || ''}
                                    onChange={(e) => handleTableCellChange(t.key, rowIdx, col.key, e.target.value)}
                                    disabled={col.key === 'srNo'}
                                  />
                                </td>
                              ))}
                              <td>
                                <button 
                                  type="button"
                                  className="btn-icon-danger"
                                  onClick={() => handleRemoveTableRow(t.key, rowIdx)}
                                  disabled={(formDetails[t.key] || []).length <= 1}
                                >
                                  <Trash2 size={14} />
                                </button>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>

                    <button 
                      type="button" 
                      className="btn btn-secondary btn-sm"
                      onClick={() => handleAddTableRow(t.key, t.columns)}
                      style={{ marginTop: '0.75rem' }}
                    >
                      <Plus size={14} /> Add Row
                    </button>
                  </div>
                ))}
              </form>
            ) : (
              /* Inline WYSIWYG Document Editor (Word-Style) */
              <div className="wysiwyg-editor-container">
                <h3>Inline Word-Style Editor</h3>
                <p className="editor-tip">
                  💡 <strong>Tip:</strong> Click any text inside the A4 sheet below to edit it directly. Click outside to save the layout changes.
                </p>

                <div className="page-tabs" style={{ marginBottom: '1rem' }}>
                  {layout.pages.map((p, idx) => (
                    <button 
                      key={idx}
                      className={`tab-btn ${editorPageIdx === idx ? 'active' : ''}`}
                      onClick={() => setEditorPageIdx(idx)}
                    >
                      Page {p.pageNum}
                    </button>
                  ))}
                </div>

                <div 
                  className="wysiwyg-document-sheet"
                  ref={editorCanvasRef}
                  style={{ 
                    aspectRatio: `${metadata.width} / ${metadata.height}`,
                    maxWidth: '100%',
                    position: 'relative',
                    background: 'white',
                    boxShadow: '0 8px 24px rgba(0,0,0,0.15)',
                    border: '1px solid #e2e8f0',
                    borderRadius: '6px',
                    overflow: 'hidden'
                  }}
                >
                  {currentPage && currentPage.elements.map((el, elIdx) => {
                    const leftPct = (el.left / metadata.width) * 100;
                    const topPct = (el.top / metadata.height) * 100;
                    const widthPct = (el.width / metadata.width) * 100;
                    const heightPct = (el.height / metadata.height) * 100;
                    const scaleFont = (el.fontSize / metadata.width) * editorCanvasWidth;

                    const isDynamic = el.type === 'dynamic';
                    const isTable = el.type === 'table-header';

                    if (isTable) {
                      return (
                        <div
                          key={elIdx}
                          className="wysiwyg-element wysiwyg-table-placeholder"
                          style={{
                            left: `${leftPct}%`,
                            top: `${topPct}%`,
                            width: `${widthPct}%`,
                            height: `${heightPct}%`,
                            fontSize: `${scaleFont}px`
                          }}
                        >
                          📊 Table: {el.text}
                        </div>
                      );
                    }

                    return (
                      <div 
                        key={elIdx}
                        className={`wysiwyg-element ${isDynamic ? 'wysiwyg-dynamic' : 'wysiwyg-static'}`}
                        contentEditable={true}
                        suppressContentEditableWarning={true}
                        onBlur={(e) => handleInlineTextChange(editorPageIdx, elIdx, e.currentTarget.textContent || '', isDynamic)}
                        style={{
                          left: `${leftPct}%`,
                          top: `${topPct}%`,
                          width: `${widthPct}%`,
                          height: `${heightPct}%`,
                          fontSize: `${scaleFont}px`,
                          fontWeight: el.isBold ? 'bold' : 'normal',
                          fontStyle: el.isItalic ? 'italic' : 'normal',
                          textAlign: el.align || 'left',
                          color: isDynamic ? '#10b981' : '#1e293b'
                        }}
                      >
                        {el.text}
                      </div>
                    );
                  })}
                </div>
              </div>
            )}
          </div>

          {/* Interactive PDF Preview */}
          <div className="preview-pdf-panel">
            <div className="preview-pdf-header">
              <h3>Live PDF Output</h3>
              <button className="btn btn-primary" onClick={handleDownloadPDF}>
                <Download size={16} />
                Download PDF
              </button>
            </div>
            
            <div className="pdf-iframe-container">
              {pdfUrl ? (
                <iframe src={pdfUrl} title="Live PDF Preview"></iframe>
              ) : (
                <div className="iframe-placeholder">
                  <FileText size={48} className="spinner" />
                  <p>Generating live PDF preview...</p>
                </div>
              )}
            </div>
          </div>
        </div>
      ) : (
        /* Generated Code Module Viewer */
        <div className="code-panel-container">
          <div className="code-header">
            <h3><FileText size={18} /> ES6 Template Module</h3>
            
            <div className="code-actions">
              <button className="btn btn-secondary" onClick={handleCopyCode}>
                {copied ? <Check size={16} style={{ color: 'var(--success)' }} /> : <Copy size={16} />}
                <span>{copied ? 'Copied!' : 'Copy Code'}</span>
              </button>
              
              <button className="btn btn-primary" onClick={handleDownloadCode}>
                <Download size={16} />
                <span>Download .js Module</span>
              </button>
            </div>
          </div>

          <pre className="code-viewer">
            <code>{generatedCode}</code>
          </pre>
        </div>
      )}
    </div>
  );
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

export function localGeneratePDF(details, layout, metadata) {
  const format = [metadata.width, metadata.height];
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({
    orientation: metadata.width > metadata.height ? 'l' : 'p',
    unit: 'mm',
    format: format
  });

  layout.pages.forEach((page, pageIdx) => {
    if (pageIdx > 0) doc.addPage();

    const sortedElements = [...page.elements].sort((a, b) => a.top - b.top);
    
    sortedElements.forEach(el => {
      if (el.type === 'static') {
        const fontStyle = el.isBold ? 'bold' : el.isItalic ? 'italic' : 'normal';
        doc.setFont("helvetica", fontStyle);
        doc.setFontSize(el.fontSize || 11);
        if (el.align === 'center') {
          doc.text(el.text, el.left + el.width / 2, el.top + el.height, { align: 'center' });
        } else {
          doc.text(el.text, el.left, el.top + el.height);
        }
      } else if (el.type === 'dynamic' && el.fieldConfig) {
        const fontStyle = el.isBold ? 'bold' : el.isItalic ? 'italic' : 'normal';
        const key = el.fieldConfig.key;
        let val = details[key] !== undefined ? details[key] : el.fieldConfig.defaultValue;
        if (el.fieldConfig.fieldType === 'date' && val) {
          const parts = String(val).split('-');
          if (parts.length === 3) {
            val = `${parts[2]}/${parts[1]}/${parts[0]}`;
          }
        }
        doc.setFont("helvetica", fontStyle);
        doc.setFontSize(el.fontSize || 11);
        doc.text(String(val || ''), el.left, el.top + el.height);
      } else if (el.type === 'table-header' && el.tableData) {
        const tableKey = cleanKey(el.text);
        const headers = el.tableData.columns.map(c => c.label);
        const columnKeys = el.tableData.columns.map(c => c.key);
        const rows = (details[tableKey] || []).map(row => 
          columnKeys.map(k => row[k] !== undefined ? String(row[k]) : '')
        );

        doc.autoTable({
          head: [headers],
          body: rows,
          startY: el.top,
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
        });
      }
    });
  });

  return doc;
}
