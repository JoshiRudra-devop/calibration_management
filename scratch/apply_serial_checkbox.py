import os
import glob
import re

cert_dir = '/Applications/XAMPP/xamppfiles/htdocs/calibration(v3)/certificates'

target_files = [
    'aggregate_impact.php',
    'cone_penetro.php',
    'digital_thermo.php',
    'elongation.php',
    'flakness.php',
    'hydrometer.php',
    'measuring_cyl.php',
    'oven.php',
    'ph_meter.php',
    'pycnometer.php',
    'sand_pouring.php',
    'vernier_caliper.php'
]

html_serial_block = '''      <div class="title_input_pair">
        <label for="serialNo">Serial No:</label>
        <div style="display: flex; flex-direction: column; gap: 4px; width: 100%;">
          <input type="text" id="serialNo" required>
          <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
            <input type="checkbox" id="useCertNoAsSerial" style="width: auto; margin: 0; cursor: pointer;" onchange="toggleCertNoAsSerial()">
            <label for="useCertNoAsSerial" style="font-weight: normal; font-size: 12px; cursor: pointer; display: inline; margin: 0; user-select: none;">Use Certificate No. as Serial No.</label>
          </div>
        </div>
      </div>'''

js_helper_block = '''
    function toggleCertNoAsSerial() {
      const useCertCheck = document.getElementById("useCertNoAsSerial");
      const certNumInput = document.getElementById("certificateNumber");
      const serialNoInput = document.getElementById("serialNo");
      
      if (!useCertCheck || !serialNoInput) return;
      
      if (useCertCheck.checked) {
        if (certNumInput) {
          serialNoInput.value = certNumInput.value;
        }
        serialNoInput.readOnly = true;
        serialNoInput.style.backgroundColor = "#e2e8f0";
      } else {
        serialNoInput.readOnly = false;
        serialNoInput.style.backgroundColor = "";
      }
    }

    document.addEventListener("DOMContentLoaded", function() {
      const certNumInput = document.getElementById("certificateNumber");
      if (certNumInput) {
        certNumInput.addEventListener("input", function() {
          const useCertCheck = document.getElementById("useCertNoAsSerial");
          if (useCertCheck && useCertCheck.checked) toggleCertNoAsSerial();
        });
        certNumInput.addEventListener("change", function() {
          const useCertCheck = document.getElementById("useCertNoAsSerial");
          if (useCertCheck && useCertCheck.checked) toggleCertNoAsSerial();
        });
      }
    });
'''

for fname in target_files:
    fpath = os.path.join(cert_dir, fname)
    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Add HTML input if not present
    if 'id="serialNo"' not in content and "id='serialNo'" not in content:
        # Insert after certificateNumberError div or certificateNumber pair
        if '<div id="certificateNumberError"' in content:
            pattern = r'(<div id="certificateNumberError"[^>]*>.*?</div>)'
            content = re.sub(pattern, r'\1\n' + html_serial_block, content, count=1, flags=re.DOTALL)
        elif 'id="certificateNumber"' in content:
            # find end of title_input_pair for certificateNumber
            m = re.search(r'(<div class="title_input_pair">\s*<label for="certificateNumber".*?</div>)', content, re.DOTALL)
            if m:
                content = content.replace(m.group(1), m.group(1) + '\n' + html_serial_block, 1)

    # 2. Add JS helper if not present
    if 'toggleCertNoAsSerial' not in content:
        # insert right after window.INSTRUMENT_SLUG = ... line or inside first script tag after INSTRUMENT_SLUG
        m = re.search(r"(window\.INSTRUMENT_SLUG\s*=\s*['\"][^'\"]+['\"];)", content)
        if m:
            content = content.replace(m.group(1), m.group(1) + js_helper_block, 1)

    # 3. Update getFormDetails()
    # Find window.getFormDetails = function() { ... return { ... }; }
    m_fn = re.search(r'window\.getFormDetails\s*=\s*function\s*\(\)\s*\{([\s\S]*?);?\s*\}', content)
    if m_fn:
        fn_body = m_fn.group(0)
        # Check if serialNo is in returned object
        if 'serialNo:' not in fn_body:
            # Add variables at top of getFormDetails
            var_prefix = """window.getFormDetails = function() {
      const useCertCheck = document.getElementById("useCertNoAsSerial");
      const certNo = document.getElementById("certificateNumber") ? document.getElementById("certificateNumber").value : "";
      const serialNoVal = document.getElementById("serialNo") ? document.getElementById("serialNo").value : "";"""
            
            # replace return { ... }
            fn_body_mod = re.sub(r'window\.getFormDetails\s*=\s*function\s*\(\)\s*\{', var_prefix, fn_body, count=1)
            # Add serialNo prop to returned object before nextCalibrationDate or closing brace
            serial_prop = "        serialNo: (useCertCheck && useCertCheck.checked) ? certNo : serialNoVal,\n"
            if 'certificateNumber:' in fn_body_mod:
                fn_body_mod = re.sub(r'(certificateNumber:\s*[^\n]+,)', r'\1\n' + serial_prop, fn_body_mod, count=1)
            content = content.replace(fn_body, fn_body_mod, 1)

    # 4. Update PDF line: replace SERIAL NO / SR NO ${details.certificateNumber} with ${details.serialNo}
    # Note: REF NO ${details.certificateNumber} should NOT be touched!
    lines = content.splitlines()
    new_lines = []
    for line in lines:
        if ('SERIAL NO' in line or 'SR NO' in line) and 'details.certificateNumber' in line and 'REF NO' not in line:
            line = line.replace('details.certificateNumber', 'details.serialNo')
        new_lines.append(line)
    
    content = '\n'.join(new_lines)

    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Updated {fname}")

print("Batch update completed.")
