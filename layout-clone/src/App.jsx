import React, { useState } from 'react';
import UploadStep from './components/UploadStep';
import MappingStep from './components/MappingStep';
import PreviewStep from './components/PreviewStep';
import { generateModuleCode } from './lib/codeGenerator';
import { Sparkles, ArrowRight } from 'lucide-react';

export default function App() {
  const [step, setStep] = useState('upload'); // 'upload' | 'mapping' | 'preview'
  const [layout, setLayout] = useState(null);
  const [fileName, setFileName] = useState('');
  const [generatedCode, setGeneratedCode] = useState('');
  const [metadata, setMetadata] = useState(null);

  const handleParseComplete = (parsedLayout, name) => {
    setLayout(parsedLayout);
    setFileName(name);
    setStep('mapping');
  };

  const handleGenerate = (finalLayout, finalMetadata) => {
    const code = generateModuleCode(finalLayout, finalMetadata);
    setLayout(finalLayout);
    setMetadata(finalMetadata);
    setGeneratedCode(code);
    setStep('preview');
  };

  const handleUpdateLayout = (newLayout) => {
    setLayout(newLayout);
    if (metadata) {
      const code = generateModuleCode(newLayout, metadata);
      setGeneratedCode(code);
    }
  };

  return (
    <div className="app-wrapper">
      {/* Premium Header */}
      <header className="app-header">
        <div className="header-brand">
          <div className="logo-badge">
            <Sparkles size={20} />
          </div>
          <h1>Layout Clone</h1>
        </div>
        <div className="steps-indicator">
          <div className={`step-node ${step === 'upload' ? 'active' : ''}`}>
            <span className="node-number">1</span>
            <span className="node-label">Upload</span>
          </div>
          <div className="connector"></div>
          <div className={`step-node ${step === 'mapping' ? 'active' : ''}`}>
            <span className="node-number">2</span>
            <span className="node-label">Field Map</span>
          </div>
          <div className="connector"></div>
          <div className={`step-node ${step === 'preview' ? 'active' : ''}`}>
            <span className="node-number">3</span>
            <span className="node-label">Preview & Export</span>
          </div>
        </div>
      </header>

      {/* Main Content Area */}
      <main className="app-main">
        {step === 'upload' && (
          <UploadStep onParseComplete={handleParseComplete} />
        )}
        
        {step === 'mapping' && (
          <MappingStep 
            initialLayout={layout} 
            fileName={fileName} 
            onGenerate={handleGenerate}
          />
        )}
        
        {step === 'preview' && (
          <PreviewStep 
            layout={layout}
            metadata={metadata}
            generatedCode={generatedCode}
            onUpdateLayout={handleUpdateLayout}
            onBack={() => setStep('mapping')}
          />
        )}
      </main>
    </div>
  );
}
