import React, { useState, useRef, useEffect } from 'react';
import { Eye, Edit3, Settings, Database, Trash2, Plus, ArrowRight, Settings2, Minimize2, Maximize2 } from 'lucide-react';

export default function MappingStep({ initialLayout, fileName, onGenerate }) {
  const [layout, setLayout] = useState(initialLayout);
  const [selectedEl, setSelectedEl] = useState(null);
  const [selectedPageIdx, setSelectedPageIdx] = useState(0);
  const [metadata, setMetadata] = useState({
    name: fileName.replace(/\.[^/.]+$/, '').replace(/_/g, ' ') || 'Calibration Certificate',
    isLabel: initialLayout.isLabel || false,
    width: initialLayout.pages[0]?.width || 210,
    height: initialLayout.pages[0]?.height || 297,
    orientation: (initialLayout.pages[0]?.width > initialLayout.pages[0]?.height) ? 'l' : 'p'
  });

  const canvasRef = useRef(null);
  const [canvasWidth, setCanvasWidth] = useState(500);

  // Measure canvas width to scale fonts proportionally
  useEffect(() => {
    if (canvasRef.current) {
      const resizeObserver = new ResizeObserver(entries => {
        for (let entry of entries) {
          setCanvasWidth(entry.contentRect.width);
        }
      });
      resizeObserver.observe(canvasRef.current);
      return () => resizeObserver.disconnect();
    }
  }, []);

  const handleSelectElement = (pageIdx, elIdx) => {
    setSelectedEl({ pageIdx, elIdx });
  };

  const handleUpdateElement = (updatedFields) => {
    if (!selectedEl) return;
    const { pageIdx, elIdx } = selectedEl;
    
    const newPages = [...layout.pages];
    newPages[pageIdx].elements[elIdx] = {
      ...newPages[pageIdx].elements[elIdx],
      ...updatedFields
    };
    
    setLayout({ ...layout, pages: newPages });
  };

  const handleToggleType = (type) => {
    if (!selectedEl) return;
    const { pageIdx, elIdx } = selectedEl;
    const el = layout.pages[pageIdx].elements[elIdx];
    
    let updated = { type };
    if (type === 'dynamic' && !el.fieldConfig) {
      updated.fieldConfig = {
        key: cleanKey(el.text),
        label: el.text.replace(/[:-=\s]+$/g, '').trim(),
        fieldType: 'text',
        defaultValue: el.text
      };
    } else if (type === 'table-header' && !el.tableData) {
      updated.tableData = {
        columns: [
          { label: 'SR.NO', key: 'srNo' },
          { label: 'PARAMETER', key: 'parameter' },
          { label: 'VALUE', key: 'value' }
        ],
        rows: []
      };
    }
    
    handleUpdateElement(updated);
  };

  const handleAddCustomField = () => {
    const pageIdx = selectedPageIdx;
    const newPages = [...layout.pages];
    const newElement = {
      type: 'dynamic',
      text: '[New Field]',
      left: 20,
      top: 50 + (newPages[pageIdx].elements.length * 5) % 150,
      width: 50,
      height: 6,
      fontSize: 11,
      fieldConfig: {
        key: 'customField' + newPages[pageIdx].elements.length,
        label: 'Custom Field',
        fieldType: 'text',
        defaultValue: 'Value'
      }
    };
    newPages[pageIdx].elements.push(newElement);
    setLayout({ ...layout, pages: newPages });
    setSelectedEl({ pageIdx, elIdx: newPages[pageIdx].elements.length - 1 });
  };

  const handleRemoveElement = () => {
    if (!selectedEl) return;
    const { pageIdx, elIdx } = selectedEl;
    const newPages = [...layout.pages];
    newPages[pageIdx].elements.splice(elIdx, 1);
    setLayout({ ...layout, pages: newPages });
    setSelectedEl(null);
  };

  const handleMetaChange = (field, value) => {
    const newMeta = { ...metadata, [field]: value };
    if (field === 'isLabel') {
      if (value) {
        newMeta.width = 40;
        newMeta.height = 30;
      } else {
        newMeta.width = 210;
        newMeta.height = 297;
      }
    }
    setMetadata(newMeta);
  };

  const getSelectedObject = () => {
    if (!selectedEl) return null;
    return layout.pages[selectedEl.pageIdx].elements[selectedEl.elIdx];
  };

  const selectedObj = getSelectedObject();
  const currentPage = layout.pages[selectedPageIdx];

  return (
    <div className="mapping-container animate-fade-in">
      <div className="mapping-header">
        <div>
          <h2 className="title-gradient">Document Field Mapper</h2>
          <p className="subtitle">
            Configure dynamic fields, toggle static labels, and setup data tables.
          </p>
        </div>
        <button 
          className="btn btn-primary btn-generate"
          onClick={() => onGenerate(layout, metadata)}
        >
          <span>Generate Code & Form</span>
          <ArrowRight size={18} />
        </button>
      </div>

      <div className="mapping-grid">
        {/* Left Toolbar / Metadata Panel */}
        <div className="card glass-card sidebar-panel">
          <h3><Settings2 size={16} /> Template Configuration</h3>
          
          <div className="form-group">
            <label>Template Name</label>
            <input 
              type="text" 
              value={metadata.name}
              onChange={(e) => handleMetaChange('name', e.target.value)}
            />
          </div>

          <div className="form-group">
            <label>Template Size</label>
            <select 
              value={metadata.isLabel ? 'label' : 'a4'}
              onChange={(e) => handleMetaChange('isLabel', e.target.value === 'label')}
            >
              <option value="a4">Standard Document (A4)</option>
              <option value="label">Info Sticker / Label (40x30mm)</option>
            </select>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label>Width (mm)</label>
              <input 
                type="number" 
                value={metadata.width}
                onChange={(e) => handleMetaChange('width', parseInt(e.target.value) || 0)}
              />
            </div>
            <div className="form-group">
              <label>Height (mm)</label>
              <input 
                type="number" 
                value={metadata.height}
                onChange={(e) => handleMetaChange('height', parseInt(e.target.value) || 0)}
              />
            </div>
          </div>

          <div className="form-group">
            <label>Orientation</label>
            <select 
              value={metadata.orientation}
              onChange={(e) => handleMetaChange('orientation', e.target.value)}
            >
              <option value="p">Portrait</option>
              <option value="l">Landscape</option>
            </select>
          </div>

          <button 
            type="button" 
            className="btn btn-secondary btn-full-width"
            onClick={handleAddCustomField}
            style={{ marginTop: '1rem' }}
          >
            <Plus size={16} />
            <span>Add Custom Field</span>
          </button>
        </div>

        {/* Visual Document Layout Canvas */}
        <div className="canvas-wrapper">
          <div className="page-tabs">
            {layout.pages.map((p, idx) => (
              <button 
                key={idx}
                className={`tab-btn ${selectedPageIdx === idx ? 'active' : ''}`}
                onClick={() => { setSelectedPageIdx(idx); setSelectedEl(null); }}
              >
                Page {p.pageNum}
              </button>
            ))}
          </div>

          <div 
            className="document-canvas-container"
            ref={canvasRef}
            style={{ 
              aspectRatio: `${metadata.width} / ${metadata.height}`,
              maxWidth: metadata.isLabel ? '320px' : '550px'
            }}
          >
            {currentPage && currentPage.elements.map((el, elIdx) => {
              const leftPct = (el.left / metadata.width) * 100;
              const topPct = (el.top / metadata.height) * 100;
              const widthPct = (el.width / metadata.width) * 100;
              const heightPct = (el.height / metadata.height) * 100;
              const scaleFont = (el.fontSize / metadata.width) * canvasWidth;

              const isSelected = selectedEl && selectedEl.pageIdx === selectedPageIdx && selectedEl.elIdx === elIdx;

              let typeClass = 'el-static';
              if (el.type === 'dynamic') typeClass = 'el-dynamic';
              if (el.type === 'table-header') typeClass = 'el-table';

              return (
                <div 
                  key={elIdx}
                  className={`canvas-element ${typeClass} ${isSelected ? 'selected' : ''}`}
                  onClick={() => handleSelectElement(selectedPageIdx, elIdx)}
                  style={{
                    left: `${leftPct}%`,
                    top: `${topPct}%`,
                    width: `${widthPct}%`,
                    height: `${heightPct}%`,
                    fontSize: `${scaleFont}px`,
                    fontWeight: el.isBold ? 'bold' : 'normal',
                    fontStyle: el.isItalic ? 'italic' : 'normal',
                    textAlign: el.align || 'left'
                  }}
                  title={`${el.type}: ${el.text}`}
                >
                  {el.type === 'dynamic' && el.fieldConfig ? `{${el.fieldConfig.key}}` : el.text}
                </div>
              );
            })}
          </div>
        </div>

        {/* Right Inspector / Properties Panel */}
        <div className="card glass-card sidebar-panel properties-panel">
          <h3><Edit3 size={16} /> Field Inspector</h3>

          {selectedObj ? (
            <div className="inspector-content">
              <div className="button-group type-toggle">
                <button 
                  className={`btn-toggle ${selectedObj.type === 'static' ? 'active static' : ''}`}
                  onClick={() => handleToggleType('static')}
                >
                  Static Text
                </button>
                <button 
                  className={`btn-toggle ${selectedObj.type === 'dynamic' ? 'active dynamic' : ''}`}
                  onClick={() => handleToggleType('dynamic')}
                >
                  Dynamic
                </button>
                <button 
                  className={`btn-toggle ${selectedObj.type === 'table-header' ? 'active table' : ''}`}
                  onClick={() => handleToggleType('table-header')}
                >
                  Table
                </button>
              </div>

              <div className="form-group" style={{ marginTop: '1.25rem' }}>
                <label>Text Content / Preview</label>
                <textarea 
                  value={selectedObj.text}
                  onChange={(e) => handleUpdateElement({ text: e.target.value })}
                  rows={2}
                />
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label>X Pos (mm)</label>
                  <input 
                    type="number" 
                    value={Math.round(selectedObj.left)}
                    onChange={(e) => handleUpdateElement({ left: parseInt(e.target.value) || 0 })}
                  />
                </div>
                <div className="form-group">
                  <label>Y Pos (mm)</label>
                  <input 
                    type="number" 
                    value={Math.round(selectedObj.top)}
                    onChange={(e) => handleUpdateElement({ top: parseInt(e.target.value) || 0 })}
                  />
                </div>
              </div>

              {selectedObj.type === 'dynamic' && selectedObj.fieldConfig && (
                <div className="dynamic-section">
                  <h4><Database size={14} /> Field Binding</h4>
                  
                  <div className="form-group">
                    <label>Variable Key (camelCase)</label>
                    <input 
                      type="text" 
                      value={selectedObj.fieldConfig.key}
                      onChange={(e) => handleUpdateElement({
                        fieldConfig: { ...selectedObj.fieldConfig, key: cleanKey(e.target.value) }
                      })}
                    />
                  </div>

                  <div className="form-group">
                    <label>Field Display Name</label>
                    <input 
                      type="text" 
                      value={selectedObj.fieldConfig.label}
                      onChange={(e) => handleUpdateElement({
                        fieldConfig: { ...selectedObj.fieldConfig, label: e.target.value }
                      })}
                    />
                  </div>

                  <div className="form-group">
                    <label>Input Type</label>
                    <select 
                      value={selectedObj.fieldConfig.fieldType}
                      onChange={(e) => handleUpdateElement({
                        fieldConfig: { ...selectedObj.fieldConfig, fieldType: e.target.value }
                      })}
                    >
                      <option value="text">Text Field</option>
                      <option value="number">Number Field</option>
                      <option value="date">Date Picker</option>
                      <option value="select">Dropdown Select</option>
                      <option value="boolean">Checkbox</option>
                    </select>
                  </div>

                  <div className="form-group">
                    <label>Default Value</label>
                    <input 
                      type="text" 
                      value={selectedObj.fieldConfig.defaultValue}
                      onChange={(e) => handleUpdateElement({
                        fieldConfig: { ...selectedObj.fieldConfig, defaultValue: e.target.value }
                      })}
                    />
                  </div>
                </div>
              )}

              {selectedObj.type === 'table-header' && selectedObj.tableData && (
                <div className="table-section">
                  <h4><Database size={14} /> Table Configuration</h4>
                  <div className="columns-editor">
                    <div className="editor-headers">
                      <span>Column Title</span>
                      <span>Key Binding</span>
                    </div>
                    {selectedObj.tableData.columns.map((col, colIdx) => (
                      <div key={colIdx} className="column-row">
                        <input 
                          type="text" 
                          value={col.label} 
                          placeholder="Header"
                          onChange={(e) => {
                            const cols = [...selectedObj.tableData.columns];
                            cols[colIdx] = { ...cols[colIdx], label: e.target.value };
                            handleUpdateElement({ tableData: { ...selectedObj.tableData, columns: cols } });
                          }}
                        />
                        <input 
                          type="text" 
                          value={col.key} 
                          placeholder="key"
                          onChange={(e) => {
                            const cols = [...selectedObj.tableData.columns];
                            cols[colIdx] = { ...cols[colIdx], key: cleanKey(e.target.value) };
                            handleUpdateElement({ tableData: { ...selectedObj.tableData, columns: cols } });
                          }}
                        />
                        <button 
                          className="btn-icon-danger"
                          onClick={() => {
                            const cols = selectedObj.tableData.columns.filter((_, idx) => idx !== colIdx);
                            handleUpdateElement({ tableData: { ...selectedObj.tableData, columns: cols } });
                          }}
                          title="Remove column"
                        >
                          <Trash2 size={14} />
                        </button>
                      </div>
                    ))}
                    <button 
                      type="button" 
                      className="btn btn-secondary btn-sm"
                      onClick={() => {
                        const cols = [...selectedObj.tableData.columns, { label: 'NEW COL', key: 'newCol' }];
                        handleUpdateElement({ tableData: { ...selectedObj.tableData, columns: cols } });
                      }}
                    >
                      <Plus size={12} /> Add Column
                    </button>
                  </div>
                </div>
              )}

              <div className="inspector-actions">
                <button 
                  className="btn btn-danger btn-full-width"
                  onClick={handleRemoveElement}
                >
                  <Trash2 size={16} />
                  <span>Delete Element</span>
                </button>
              </div>
            </div>
          ) : (
            <div className="properties-empty">
              <Eye size={36} />
              <p>Select any item on the canvas to inspect and configure its attributes.</p>
            </div>
          )}
        </div>
      </div>
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
