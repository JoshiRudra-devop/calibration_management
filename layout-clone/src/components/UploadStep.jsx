import React, { useState, useRef } from 'react';
import { UploadCloud, FileText, Image as ImageIcon, Loader2, Sparkles, AlertCircle } from 'lucide-react';
import { parsePDF, parseDOCX, parseImage, getMockCertificateLayout } from '../lib/documentParser';

export default function UploadStep({ onParseComplete }) {
  const [isDragActive, setIsDragActive] = useState(false);
  const [parsingState, setParsingState] = useState({ status: 'idle', message: '', step: 0 });
  const [error, setError] = useState('');
  const fileInputRef = useRef(null);

  const handleDrag = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === 'dragenter' || e.type === 'dragover') {
      setIsDragActive(true);
    } else if (e.type === 'dragleave') {
      setIsDragActive(false);
    }
  };

  const handleDrop = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragActive(false);
    
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      processFile(e.dataTransfer.files[0]);
    }
  };

  const handleFileChange = (e) => {
    if (e.target.files && e.target.files[0]) {
      processFile(e.target.files[0]);
    }
  };

  const processFile = async (file) => {
    setError('');
    const extension = file.name.split('.').pop().toLowerCase();
    
    if (!['pdf', 'docx', 'png', 'jpg', 'jpeg'].includes(extension)) {
      setError('Unsupported file format. Please upload PDF, DOCX, or PNG/JPG image.');
      return;
    }

    try {
      setParsingState({ status: 'loading', message: 'Reading document file...', step: 1 });
      
      let result;
      if (extension === 'pdf') {
        setParsingState({ status: 'loading', message: 'Extracting PDF layout coordinates...', step: 2 });
        result = await parsePDF(file);
      } else if (extension === 'docx') {
        setParsingState({ status: 'loading', message: 'Parsing DOCX structure and tables...', step: 2 });
        result = await parseDOCX(file);
      } else {
        setParsingState({ status: 'loading', message: 'Running OCR engine on image...', step: 2 });
        result = await parseImage(file);
      }

      setParsingState({ status: 'loading', message: 'Grouping text runs and detecting tables...', step: 3 });
      await new Promise(resolve => setTimeout(resolve, 800)); // Visual buffer for smooth animation

      setParsingState({ status: 'loading', message: 'Mapping fields to schema configurations...', step: 4 });
      await new Promise(resolve => setTimeout(resolve, 600));

      setParsingState({ status: 'done', message: 'Successfully parsed layout!', step: 5 });
      onParseComplete(result, file.name);
    } catch (err) {
      console.error(err);
      setError(`Failed to parse file: ${err.message || err}`);
      setParsingState({ status: 'error', message: '', step: 0 });
    }
  };

  const handleLoadSample = () => {
    setParsingState({ status: 'loading', message: 'Pre-loading sample layout template...', step: 3 });
    setTimeout(() => {
      const mock = getMockCertificateLayout();
      onParseComplete(mock, 'Sieve_Mould_Calibration_Template.pdf');
    }, 1000);
  };

  return (
    <div className="upload-container">
      <div className="card glass-card upload-card animate-fade-in">
        <h2 className="title-gradient">Upload Reference Document</h2>
        <p className="subtitle">
          Upload any certificate, report, or sticker layout (PDF, DOCX, or Image).
          We will analyze the structure, text coordinates, and dynamic fields to generate a matching jsPDF template.
        </p>

        {error && (
          <div className="alert alert-error">
            <AlertCircle size={20} />
            <span>{error}</span>
          </div>
        )}

        {parsingState.status === 'loading' ? (
          <div className="parsing-loader animate-pulse">
            <Loader2 className="spinner" size={48} />
            <h3>{parsingState.message}</h3>
            
            <div className="steps-progress">
              <div className="progress-bar-track">
                <div 
                  className="progress-bar-fill" 
                  style={{ width: `${(parsingState.step / 4) * 100}%` }}
                ></div>
              </div>
              <div className="steps-labels">
                <span className={parsingState.step >= 1 ? 'active' : ''}>Read File</span>
                <span className={parsingState.step >= 2 ? 'active' : ''}>OCR / Coordinates</span>
                <span className={parsingState.step >= 3 ? 'active' : ''}>Group Rows</span>
                <span className={parsingState.step >= 4 ? 'active' : ''}>Build Fields</span>
              </div>
            </div>
          </div>
        ) : (
          <>
            <div 
              className={`dropzone ${isDragActive ? 'drag-active' : ''}`}
              onDragEnter={handleDrag}
              onDragOver={handleDrag}
              onDragLeave={handleDrag}
              onDrop={handleDrop}
              onClick={() => fileInputRef.current.click()}
            >
              <input 
                type="file" 
                ref={fileInputRef} 
                onChange={handleFileChange} 
                accept=".pdf,.docx,.png,.jpg,.jpeg" 
                style={{ display: 'none' }}
              />
              <div className="dropzone-content">
                <div className="icon-badge">
                  <UploadCloud size={32} />
                </div>
                <h3>Drag & Drop your template</h3>
                <p>or click to browse local files</p>
                <div className="formats">
                  <span><FileText size={14} /> PDF</span>
                  <span><FileText size={14} /> DOCX</span>
                  <span><ImageIcon size={14} /> PNG, JPG</span>
                </div>
              </div>
            </div>

            <div className="divider">
              <span>OR</span>
            </div>

            <button 
              type="button" 
              className="btn btn-secondary btn-sample animate-hover"
              onClick={handleLoadSample}
            >
              <Sparkles size={18} />
              <span>Load Sample Calibration Layout</span>
            </button>
          </>
        )}
      </div>
    </div>
  );
}
