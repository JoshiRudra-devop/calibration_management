<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Sieves Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'sieves' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>

<div class="container">
    <h2 class="centered">TEST SEIVES CALIBRATION CERTIFICATE</h2>
    <form id="calibrationForm">
        <div class="title_input_pair">
            <label for="certificateNumber">Certificate No:</label>
            <input type="text" id="certificateNumber" required>
        </div>

        <div class="date">
            <div class="title_input_pair">
                <label for="calibrationDate">Date of Calibration:</label>
                <input type="date" id="calibrationDate" onchange="calculateNextDate()" required>
            </div>
            <div class="title_input_pair">
                <label for="nextCalibrationDate">Next Suggested Date:</label>
                <input type="date" id="nextCalibrationDate" required>
            </div>
        </div>

        <div class="title_input_pair">
            <label for="partyName">Name of Party:</label>
            <input type="text" id="partyName" required>
        </div>
        
        <div class="title_input_pair">
            <label for="siteLocation">Site Location:</label>
            <input type="text" id="siteLocation" required>
        </div>

        <div class="date">
            <div class="title_input_pair">
                <label for="sieveSize">SELECT SIZE OF SEIVE:</label>
                <select id="sieveSize" required>
                    <option value="Brass 200 MM DIA" selected>BRASS SEIVE 8"</option>
                    <option value="GI 300 MM DIA">GI SEIVE 12"</option>
                    <option value="GI 450 MM DIA">GI SEIVE 18"</option>
                </select>
            </div>
            <div class="title_input_pair">
                <label for="make">MAKE:</label>
                <select id="make" required>
                    <option value="STANDARD" selected>STANDARD</option>
                    <option value="ASC">ASC</option>
                </select> 
            </div>
        </div>

        <div id="subSizes" style="display:none; border: 2px solid #00796b; border-radius: 12px; padding: 20px; margin: 15px 0; background-color: #f8fafc; box-shadow: 0 4px 12px rgba(0,121,107,0.06);">
            <h3 style="margin-top: 0; color: #00796b; font-size: 1.1rem; font-weight: 700;">Select Sub-Sizes:</h3>
            <div id="checkBoxes" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; margin-bottom: 15px;"></div>
            <button type="button" id="selectFullSetBtn" style="padding: 10px 22px; background-color: #00796b; color: white; border: none; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,121,107,0.2); transition: all 0.2s;">SELECT FULL SET</button>
        </div>

        <input type="hidden" id="selectedSubSizes" name="selectedSubSizes">

        <div id="testResults">
            <h3>Test Results</h3>
            <table id="resultsTable" style="display:none;">
                <thead>
                    <tr>
                        <th>SR NO.</th>
                        <th>MAKE</th>
                        <th>SEIVE</th>
                        <th>SEIVE SIZE</th>
                        <th>RESULT</th>
                    </tr>
                </thead>
                <tbody id="resultsBody">
                </tbody>
            </table>
            <div id="selectedSizes" style="border: 2px solid #00796b; border-radius: 10px; padding: 20px; margin: 10px 0; background-color: #f9f9f9; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #00796b;">Selected Sub-Sizes:</h3>
                <div id="selectedList" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
            </div>
        </div>

        <div class="unsaved-reminder" id="unsavedReminder">
            <span>⚠️ Please save your certificate before leaving this page.</span>
        </div>

        <div class="sticker-section">
            <div class="sticker-preview-container">
                <h3 style="color: #00796b; margin-top: 0;">Info Sticker Preview</h3>
                <iframe id="stickerPreviewFrame"></iframe>
            </div>
        </div>
    </form>
</div>

<script src="<?= APP_URL ?>/assets/js/general-v3.js?v=<?= filemtime(__DIR__ . '/../assets/js/general-v3.js') ?>"></script>
<script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    window.INSTRUMENT_SLUG = 'sieves';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>

<script> 
    window.stickerPdfBlob = null;
  
    function getFormDetails() {
        const getVal = (id) => {
            const el = document.getElementById(id);
            return el ? el.value : "";
        };
        const formatDate = (val) => {
            return val ? val.split("-").reverse().join("/") : "";
        };
        const certNo = getVal("certificateNumber");
        const partyName = getVal("partyName");

        return {
            certificateNumber: certNo,
            calibrationDate: formatDate(getVal("calibrationDate")),
            siteLocation: getVal("siteLocation"),
            partyName: partyName,
            nextCalibrationDate: formatDate(getVal("nextCalibrationDate")),
            make: getVal("make"),
            sieveSize: getVal("sieveSize"),
            selectedSubSizes: getVal("selectedSubSizes"),
            saveentry: `Sieves_${partyName}_${certNo}`
        };
    }
    window.getFormDetails = getFormDetails;

    function parseSize(size) {
        if (!size) return 0;
        const str = String(size).trim();
        const match = str.match(/(\d+(?:\.\d+)?)/);
        if (!match) return 0;
        const num = parseFloat(match[1]);
        if (str.includes('μm') || str.toLowerCase().includes('micron') || str.toLowerCase().includes('microns')) {
            return num / 1000;
        }
        return num;
    }

    function normalizeSieveKey(size) {
        if (!size) return '';
        const s = String(size).trim();
        if (s.includes('8') || s.toLowerCase().includes('brass') || s.includes('200')) return 'Brass 200 MM DIA';
        if (s.includes('12') || s.includes('300')) return 'GI 300 MM DIA';
        if (s.includes('18') || s.includes('450')) return 'GI 450 MM DIA';
        return s;
    }

    function getFullSet(size) {
        const key = normalizeSieveKey(size);
        const fullSets = {
            'Brass 200 MM DIA': ['4.75 MM', '2.36 MM', '1.18 MM', '600 MICRON', '300 MICRON', '150 MICRON', '10 MICRON'],
            'GI 300 MM DIA': ['2.36mm', '4.75mm', '10mm', '20mm', '25mm', '40mm', '6.3mm', '45mm', '12.5'],
            'GI 450 MM DIA': ['2.36mm', '4.75mm', '10mm', '20mm', '25mm', '40mm', '6.3mm', '45mm', '12.5']
        };
        return fullSets[key] || fullSets[size] || [];
    }

    window.selectFullSet = function(size) {
        const fullSet = getFullSet(size);
        if (!fullSet || fullSet.length === 0) return;
        
        const normalize = s => String(s).trim().toLowerCase().replace(/\s+/g, '');
        const normalizedFullSet = fullSet.map(normalize);

        const checkboxes = document.querySelectorAll('#checkBoxes input[type="checkbox"]');
        checkboxes.forEach(cb => {
            const normCb = normalize(cb.value);
            const isMatch = normalizedFullSet.some(f => f === normCb || f === normCb.replace('mm', '') || normCb === f.replace('mm', ''));
            if (isMatch) {
                if (!cb.checked) {
                    cb.checked = true;
                    toggleRow(cb.value, true);
                }
            }
            const parentLabel = cb.parentElement;
            if (parentLabel && parentLabel.tagName === 'LABEL') {
                if (cb.checked) {
                    parentLabel.style.backgroundColor = '#e0f2f1';
                    parentLabel.style.borderColor = '#00796b';
                    parentLabel.style.color = '#004d40';
                    parentLabel.style.boxShadow = '0 2px 5px rgba(0,121,107,0.15)';
                } else {
                    parentLabel.style.backgroundColor = '#ffffff';
                    parentLabel.style.borderColor = '#cbd5e1';
                    parentLabel.style.color = '#1e293b';
                    parentLabel.style.boxShadow = '0 1px 3px rgba(0,0,0,0.04)';
                }
            }
        });
    };

    function getSubSizes(size) {
        const key = normalizeSieveKey(size);
        const sizes = {
            'Brass 200 MM DIA': ['10 MM', '4.75 MM', '3.35 mm', '2.80 mm', '2.36 MM', '2.00 mm', '1.70 mm', '1.40 mm', '1.18 MM', '1 MM', '850 microns', '710 microns', '600 MICRON', '500 microns', '425 MICRON', '355 microns', '300 MICRON', '250 microns', '212 microns', '180 microns', '150 MICRON', '125 microns', '106 microns', '90 MICRON', '75 MICRON', '63 microns', '53 microns', '45 MICRON', '10 MICRON'],
            'GI 300 MM DIA': ['125mm', '106mm', '100mm', '90mm', '80mm', '75mm', '63mm', '53mm', '50mm', '45mm', '40mm', '37.5mm', '31.5mm', '26.5mm', '25mm', '22.4mm', '20mm', '19mm', '16mm', '13.2mm', '12.5mm', '11.2mm', '10mm', '9.5mm', '8mm', '6.7mm', '6.3mm', '5.6mm', '4.75mm', '2.36mm'],
            'GI 450 MM DIA': ['125mm', '106mm', '100mm', '90mm', '80mm', '75mm', '63mm', '53mm', '50mm', '45mm', '40mm', '37.5mm', '31.5mm', '26.5mm', '25mm', '22.4mm', '20mm', '19mm', '16mm', '13.2mm', '12.5mm', '11.2mm', '10mm', '9.5mm', '8mm', '6.7mm', '6.3mm', '5.6mm', '4.75mm', '2.36mm']
        };
        const list = sizes[key] || sizes[size] || [];
        return list.slice().sort((a, b) => parseSize(b) - parseSize(a));
    }

    function sortResultsTable() {
        const tbody = document.getElementById('resultsBody');
        if (!tbody) return;
        const rows = Array.from(tbody.rows);
        if (rows.length <= 1) return;
        rows.sort((a, b) => {
            const sizeA = a.cells[3] ? a.cells[3].textContent : '';
            const sizeB = b.cells[3] ? b.cells[3].textContent : '';
            return parseSize(sizeB) - parseSize(sizeA);
        });
        rows.forEach((row, idx) => {
            if (row.cells[0]) row.cells[0].textContent = idx + 1;
            tbody.appendChild(row);
        });
    }

    function updateSelectedDisplay() {
        sortResultsTable();
        const selectedList = document.getElementById('selectedList');
        if (selectedList) selectedList.innerHTML = '';
        const tbody = document.getElementById('resultsBody');
        const savedArr = [];
        if (tbody) {
            for (let row of tbody.rows) {
                if (row.cells && row.cells[3]) {
                    const size = row.cells[3].textContent;
                    savedArr.push(size);
                    if (selectedList) {
                        const item = document.createElement('span');
                        item.style.display = 'inline-flex';
                        item.style.alignItems = 'center';
                        item.style.padding = '5px 12px';
                        item.style.border = '1px solid #b2dfdb';
                        item.style.borderRadius = '20px';
                        item.style.backgroundColor = '#e0f2f1';
                        item.style.color = '#004d40';
                        item.style.fontSize = '12px';
                        item.style.fontWeight = '600';
                        item.innerHTML = '✓ ' + size;
                        selectedList.appendChild(item);
                    }
                }
            }
        }
        const hiddenInput = document.getElementById('selectedSubSizes');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(savedArr);
        }
    }

    window.restoreSievesState = function() {
        const hiddenInput = document.getElementById('selectedSubSizes');
        const sieveSizeSelect = document.getElementById('sieveSize');
        const subSizesDiv = document.getElementById('subSizes');

        if (!hiddenInput || !hiddenInput.value) return 0;
        let savedSizes = [];
        try {
            savedSizes = JSON.parse(hiddenInput.value);
        } catch (e) {
            if (typeof hiddenInput.value === 'string' && hiddenInput.value.length > 0) {
                savedSizes = hiddenInput.value.split(',');
            }
        }
        if (!Array.isArray(savedSizes) || savedSizes.length === 0) return 0;

        if (subSizesDiv) subSizesDiv.style.display = 'block';

        const normalize = s => String(s).trim().toLowerCase().replace(/\s+/g, '');
        const normalizedSaved = savedSizes.map(normalize);

        const tbody = document.getElementById('resultsBody');
        if (tbody) tbody.innerHTML = '';

        const checkboxes = document.querySelectorAll('#checkBoxes input[type="checkbox"]');
        let restoredCount = 0;

        checkboxes.forEach(cb => {
            const normCb = normalize(cb.value);
            if (normalizedSaved.includes(normCb)) {
                cb.checked = true;
                toggleRow(cb.value, true);
                restoredCount++;
            } else {
                cb.checked = false;
            }
            const parentLabel = cb.parentElement;
            if (parentLabel && parentLabel.tagName === 'LABEL') {
                if (cb.checked) {
                    parentLabel.style.backgroundColor = '#e0f2f1';
                    parentLabel.style.borderColor = '#00796b';
                    parentLabel.style.color = '#004d40';
                    parentLabel.style.boxShadow = '0 2px 5px rgba(0,121,107,0.15)';
                } else {
                    parentLabel.style.backgroundColor = '#ffffff';
                    parentLabel.style.borderColor = '#cbd5e1';
                    parentLabel.style.color = '#1e293b';
                    parentLabel.style.boxShadow = '0 1px 3px rgba(0,0,0,0.04)';
                }
            }
        });

        // Fallback for direct reconstruction if checkbox elements are absent
        if (restoredCount === 0 && savedSizes.length > 0 && tbody) {
            const make = document.getElementById('make')?.value || 'STANDARD';
            const seive = sieveSizeSelect?.value || '';
            savedSizes.forEach((subSize, idx) => {
                const row = tbody.insertRow();
                row.insertCell().textContent = idx + 1;
                row.insertCell().textContent = make;
                row.insertCell().textContent = seive;
                row.insertCell().textContent = subSize;
                row.insertCell().textContent = 'OK';
            });
            updateSelectedDisplay();
            restoredCount = savedSizes.length;
        }
        return restoredCount;
    };

    window.updateSubSizes = function updateSubSizes() {
        const sizeSelect = document.getElementById('sieveSize');
        const size = sizeSelect ? sizeSelect.value : '';
        const subSizesDiv = document.getElementById('subSizes');
        const checkBoxesDiv = document.getElementById('checkBoxes');
        const tbody = document.getElementById('resultsBody');
        const hiddenInput = document.getElementById('selectedSubSizes');

        if (!size) {
            if (subSizesDiv) subSizesDiv.style.display = 'none';
            if (tbody) tbody.innerHTML = '';
            if (checkBoxesDiv) checkBoxesDiv.innerHTML = '';
            updateSelectedDisplay();
            return;
        }

        if (subSizesDiv) subSizesDiv.style.display = 'block';

        // Auto-select MAKE based on sieve category
        const makeSelect = document.getElementById('make');
        if (makeSelect) {
            const normKey = normalizeSieveKey(size);
            if (normKey === 'Brass 200 MM DIA' && (!makeSelect.value || makeSelect.value === 'ASC')) {
                makeSelect.value = 'STANDARD';
            } else if ((normKey === 'GI 300 MM DIA' || normKey === 'GI 450 MM DIA') && (!makeSelect.value || makeSelect.value === 'STANDARD')) {
                makeSelect.value = 'ASC';
            }
        }
        updateMake();

        if (tbody) tbody.innerHTML = '';
        if (checkBoxesDiv) checkBoxesDiv.innerHTML = '';

        const subSizes = getSubSizes(size);
        subSizes.sort((a, b) => parseSize(b) - parseSize(a));
        subSizes.forEach(sub => {
            const label = document.createElement('label');
            label.className = 'sieve-card-pill';
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.gap = '8px';
            label.style.padding = '10px 14px';
            label.style.border = '1.5px solid #cbd5e1';
            label.style.borderRadius = '8px';
            label.style.backgroundColor = '#ffffff';
            label.style.color = '#1e293b';
            label.style.cursor = 'pointer';
            label.style.transition = 'all 0.2s ease';
            label.style.fontSize = '13px';
            label.style.fontWeight = '600';
            label.style.boxShadow = '0 1px 3px rgba(0,0,0,0.04)';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = sub;
            checkbox.style.accentColor = '#00796b';
            checkbox.style.width = '16px';
            checkbox.style.height = '16px';
            checkbox.style.cursor = 'pointer';

            const updateCardStyle = () => {
                if (checkbox.checked) {
                    label.style.backgroundColor = '#e0f2f1';
                    label.style.borderColor = '#00796b';
                    label.style.color = '#004d40';
                    label.style.boxShadow = '0 2px 5px rgba(0,121,107,0.15)';
                } else {
                    label.style.backgroundColor = '#ffffff';
                    label.style.borderColor = '#cbd5e1';
                    label.style.color = '#1e293b';
                    label.style.boxShadow = '0 1px 3px rgba(0,0,0,0.04)';
                }
            };

            checkbox.onchange = () => {
                updateCardStyle();
                toggleRow(sub, checkbox.checked);
            };

            label.onmouseover = () => {
                if (!checkbox.checked) {
                    label.style.borderColor = '#00796b';
                }
            };
            label.onmouseout = () => {
                if (!checkbox.checked) {
                    label.style.borderColor = '#cbd5e1';
                }
            };

            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(sub));
            checkBoxesDiv.appendChild(label);
        });

        // Restore state if saved values exist
        if (hiddenInput && hiddenInput.value && hiddenInput.value !== '[]') {
            restoreSievesState();
        }

        // Attach event handler to SELECT FULL SET button
        const fullSetBtn = document.getElementById('selectFullSetBtn');
        if (fullSetBtn) {
            fullSetBtn.onclick = () => {
                selectFullSet(size);
            };
        }
    };

    function toggleRow(subSize, checked) {
        const tbody = document.getElementById('resultsBody');
        const make = document.getElementById('make')?.value || 'STANDARD';
        const seive = document.getElementById('sieveSize')?.value || '';
        if (checked) {
            let exists = false;
            for (let i = 0; i < tbody.rows.length; i++) {
                if (tbody.rows[i].cells[3] && tbody.rows[i].cells[3].textContent === subSize) {
                    exists = true;
                    break;
                }
            }
            if (!exists) {
                const row = tbody.insertRow();
                const srCell = row.insertCell();
                srCell.textContent = tbody.rows.length;
                const makeCell = row.insertCell();
                makeCell.textContent = make;
                const seiveCell = row.insertCell();
                seiveCell.textContent = seive;
                const sizeCell = row.insertCell();
                sizeCell.textContent = subSize;
                const resultCell = row.insertCell();
                resultCell.textContent = 'OK';
            }
        } else {
            for (let i = 0; i < tbody.rows.length; i++) {
                if (tbody.rows[i].cells[3] && tbody.rows[i].cells[3].textContent === subSize) {
                    tbody.deleteRow(i);
                    for (let j = i; j < tbody.rows.length; j++) {
                        tbody.rows[j].cells[0].textContent = j + 1;
                    }
                    break;
                }
            }
        }
        updateSelectedDisplay();
    }

    function updateMake() {
        const make = document.getElementById('make')?.value || '';
        document.querySelectorAll('#resultsBody tr').forEach(row => {
            if (row.cells[1]) row.cells[1].textContent = make;
        });
    }

    function initSievesListeners() {
        const sieveSizeSelect = document.getElementById('sieveSize');
        if (sieveSizeSelect) {
            sieveSizeSelect.removeEventListener('change', window.updateSubSizes);
            sieveSizeSelect.addEventListener('change', function(e) {
                if (e && e.isTrusted) {
                    const hiddenInput = document.getElementById('selectedSubSizes');
                    if (hiddenInput) hiddenInput.value = '';
                }
                window.updateSubSizes();
            });
            sieveSizeSelect.addEventListener('input', function() {
                window.updateSubSizes();
            });
            if (!sieveSizeSelect.value) {
                sieveSizeSelect.value = 'Brass 200 MM DIA';
            }
            window.updateSubSizes();
        }
        const makeSelect = document.getElementById('make');
        if (makeSelect) {
            makeSelect.addEventListener('change', updateMake);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSievesListeners);
    } else {
        initSievesListeners();
    }

    function addTestResultRow() {
        const testResults = document.getElementById('testResults');
        const currentRows = testResults.getElementsByClassName('test-result').length;
        const row = document.createElement('div');
        row.className = 'test-result';
        row.innerHTML = `<input type="text" placeholder="Sieves NUMBER" required>`;
        testResults.appendChild(row);
    }

    window.addCertificateDetails = function(doc, details) {
        const testResults = [];
        document.querySelectorAll('#resultsBody tr').forEach(row => {
            const cells = row.cells;
            if (cells && cells.length >= 5) {
                testResults.push([cells[0].textContent, cells[1].textContent, cells[2].textContent, cells[3].textContent, cells[4].textContent]);
            }
        });

        // Fallback for direct data if DOM table rows are absent
        if (testResults.length === 0) {
            let subSizesArr = [];
            const rawSub = details.selectedSubSizes || document.getElementById('selectedSubSizes')?.value || '';
            try {
                subSizesArr = JSON.parse(rawSub);
            } catch (e) {
                if (typeof rawSub === 'string' && rawSub.length > 0) {
                    subSizesArr = rawSub.split(',').map(s => s.trim()).filter(Boolean);
                }
            }
            if (Array.isArray(subSizesArr) && subSizesArr.length > 0) {
                const sieveName = details.sieveSize || document.getElementById('sieveSize')?.value || 'GI SIEVE';
                const makeName = details.make || document.getElementById('make')?.value || 'ASC';
                subSizesArr.forEach((sz, idx) => {
                    testResults.push([(idx + 1).toString(), makeName, sieveName, sz, 'OK']);
                });
            }
        }

        // Sort by size descending
        testResults.sort((a, b) => parseSize(b[3]) - parseSize(a[3]));

        // Reassign SR NO.
        testResults.forEach((row, index) => {
            row[0] = (index + 1).toString();
        });

        let isFirstPage = true;
        let pageIndex = 1;
        let currentY = 46;
        const rawCertNo = details.certificateNumber !== undefined && details.certificateNumber !== null ? String(details.certificateNumber).trim() : '';

        while (testResults.length > 0) {
            if (!isFirstPage) {
                doc.addPage();
                currentY = 46;
                pageIndex++;
            }

            const currentCertNo = (pageIndex > 1 && rawCertNo) ? `${rawCertNo}-${pageIndex}` : rawCertNo;

            doc.setFont("helvetica", "bold");
            doc.setFontSize(25);   
            doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth()/2, currentY, { align: 'center' });

            doc.setFont("helvetica", "bold"); 
            doc.setFontSize(10);  

            currentY = 58;
            if (currentCertNo) {
                doc.text(`REF NO:- ${currentCertNo}`, 140, currentY);
            }

            const partyPrefix = "NAME OF PARTY           :-     ";
            const partyPrefixWidth = doc.getTextWidth(partyPrefix);
            const partyLines = doc.splitTextToSize(details.partyName || "", 180 - partyPrefixWidth);
            doc.text(partyPrefix + (partyLines[0] || ""), 14, currentY);
            for (let i = 1; i < partyLines.length; i++) {
                currentY += 4.5;
                doc.text(partyLines[i], 14 + partyPrefixWidth, currentY);
            }

            currentY += 8;
            doc.text(`EQUIPMENT NAME        :-     TEST SIEVES`, 14, currentY);
            currentY += 8;

            // Site Location with auto-wrapping
            const siteLocPrefix = "SITE LOCATION             :-     ";
            const prefixWidth = doc.getTextWidth(siteLocPrefix);
            const siteLocLines = doc.splitTextToSize(details.siteLocation || "", 180 - prefixWidth);
            doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, currentY);
            for (let l = 1; l < siteLocLines.length; l++) {
                currentY += 4.5;
                doc.text(siteLocLines[l], 14 + prefixWidth, currentY);
            }
            currentY += 8;

            doc.text(`CALIBRATION DATE     :-     ${details.calibrationDate || ''}`, 14, currentY);
            currentY += 8;
            doc.text(`NEXT DUE DATE            :-     ${details.nextCalibrationDate || ''}`, 14, currentY);
            currentY += 8;

            doc.setFont("helvetica", "bold");
            doc.setFontSize(13);   
            doc.text("Test Results", doc.internal.pageSize.getWidth()/2, currentY, { align: 'center' });
            currentY += 3;

            const chunk = testResults.splice(0, 10);

            doc.autoTable({
                head: [['SR NO.', 'MAKE', 'SIEVE', 'SIEVE SIZE', 'RESULT']],
                body: chunk,
                startY: currentY,
                styles: { 
                    fontSize: 7.5,
                    textColor: [0,0,0],
                    fontStyle: "bold",  
                    lineColor: [87, 86, 85],
                    lineWidth: 0.2,
                    halign: 'center',
                    valign: 'middle',
                    cellPadding: 0.8
                },
                headStyles: {
                    fontSize: 9,
                    fillColor: [255, 255, 255],
                    textColor: [0,0,0],
                    lineColor: [0, 0, 0],
                    lineWidth: 0.2,
                    halign: 'center',
                    valign: 'middle',
                },
                alternateRowStyles: {
                    fillColor: [255, 255, 255]
                }
            });

            currentY = doc.autoTable.previous.finalY + 3;
            isFirstPage = false;

            let yPosition = currentY;
            doc.setFontSize(8.5);
            doc.setFont("helvetica", "bold");
            doc.text("*REMARKS:-", doc.internal.pageSize.getWidth()/2, yPosition+=2, { align: 'center' }); yPosition += 4;
            doc.setFont("helvetica", "bold"); 
            doc.setFontSize(7.5);
            doc.text("•THESE RESULTS ARE OBTAINED AT THE TIME OF CALIBRATION.", 14, yPosition); yPosition += 4;
            doc.text("•ANY HAND WRITTEN CORRECTION (EXCEPT @ OR PHOTOCOPIES OF THE REPORT INVALIDATES THIS CERTIFICATE).", 14, yPosition); yPosition += 4;
            doc.text("•ENVIRONMENT CONDITION DURING CALIBRATION: 20 + 2.C, 40 TO 60% RH.", 14, yPosition); yPosition += 4;
            doc.text("•THE UNCERTAINTIES ARE FOR A CONFIDENCE PROBABILITY OF NOT LESS THAN 95%. ", 14, yPosition); yPosition += 4;
            doc.text("•REFERENCE CALIBRATION METHOD NO: NCQC/CM/102.", 14, yPosition); yPosition += 4;
            doc.text("•REFERENCE STANDARD NO.IS-2-1960.", 14, yPosition); yPosition += 5;

            const master = (typeof getMasterDetails === 'function') ? getMasterDetails('digital_vernier_caliper') : {};
            const masterName = master.name || "DIGITAL VERNIER CALIPER";
            const masterSerial = master.serial_no || "ACCUPLUS/13-200";
            const masterRange = master.range_capacity || "0-200MM";
            const masterLc = master.least_count || '0.001" (0.01MM)';
            const masterCalib = master.calib_date || "02/08/2026";
            const masterDue = master.due_date || "01/08/2027";
            const masterCert = master.cert_no || "62";
            const masterLab = master.calibrated_by || "IDEMI CALIBRATION LABORATORY";

            doc.setFont("helvetica", "bold");
            doc.setFontSize(8.5);
            doc.text("*DETAILS OF OUR MASTER INSTRUMENT THROUGH WHICH SIEVES ARE CALIBRATED", doc.internal.pageSize.getWidth()/2, yPosition, { align: 'center' }); yPosition += 5;
            doc.setFont("helvetica", "bold");       
            doc.setFontSize(7.5);

            doc.text("NAME : " + masterName, 14, yPosition);
            doc.text("SERIAL NO : " + masterSerial, 124, yPosition); yPosition += 4;
            doc.text("RANGE : " + masterRange, 14, yPosition);
            doc.text("LEAST COUNT : " + masterLc, 124, yPosition); yPosition += 4;
            doc.text("CALIBRATION DATE : " + masterCalib, 14, yPosition);
            doc.text("VALID UP TO DATE : " + masterDue, 124, yPosition); yPosition += 4;
            doc.text("OUR MASTER INSTRUMENT IS CALIBRATED AND TRACEABLE TO NATIONAL STANDARD THROUGH NABL ACCREDITED ", 14, yPosition); yPosition += 4;
            doc.text('LABORATORY "' + masterLab + '"', 14, yPosition); yPosition += 4;
            doc.text("CALIBRATION CERTIFICATE NO : " + masterCert, 14, yPosition); yPosition += 4;

            doc.setFont("helvetica", "bold"); 
            doc.setFontSize(12); 

            doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, Math.max(yPosition + 12, 230));
            doc.text("PROPRIETOR", 170, Math.max(yPosition + 25, 245));
        }
    };

    async function generateInfoSticker() {
        const { jsPDF } = window.jspdf;
        const width = 60 * 2.83465;
        const height = 30 * 2.83465;
        const doc = new jsPDF({
            orientation: "landscape",
            unit: "pt",
            format: [width, height]
        });
        const details = getFormDetails();
        const primaryBlue = [19, 52, 165];
        const accentRed = [228, 34, 21];
        doc.setDrawColor(...primaryBlue);
        doc.setLineWidth(3);
        doc.rect(5, 5, width - 10, height - 10);
        const logoImg = new Image();
        logoImg.src = "../assets/images/logo.png";
        await new Promise(resolve => { logoImg.onload = resolve; logoImg.onerror = resolve; });
        if (logoImg.width) {
            doc.addImage(logoImg, "PNG", 8, 4, 12, 16);
        }
        doc.setFont("times", "bold");
        doc.setFontSize(8);
        doc.setTextColor(...accentRed);
        doc.text(window.PDF_COMPANY_NAME, 32, 13);
        doc.setFont("times", "normal");
        doc.setFontSize(4);
        doc.setTextColor(...primaryBlue);
        doc.text("SALES • SERVICE • REPAIRING • CALIBRATIONS", width / 2, 18, { align: "center" });
        const tableLeft = 15;
        const tableTop = 20;
        const tableWidth = width - 30;
        const rowHeight = 14;
        const labelWidth = tableWidth * 0.4;
        const tableData = [
            { label: "INST. ID NO.", value: details.certificateNumber || "N/A" },
            { label: "MAKE", value: details.make || "N/A" },
            { label: "CALIB. DATE", value: details.calibrationDate || "N/A" },
            { label: "NEXT DATE", value: details.nextCalibrationDate || "N/A" },
        ];
        doc.setDrawColor(0, 0, 0);
        doc.setLineWidth(1);
        doc.rect(tableLeft, tableTop, tableWidth, rowHeight * tableData.length);
        tableData.forEach((row, index) => {
            const rowY = tableTop + (index * rowHeight);
            if (index > 0) doc.line(tableLeft, rowY, tableLeft + tableWidth, rowY);
            doc.line(tableLeft + labelWidth, rowY, tableLeft + labelWidth, rowY + rowHeight);
            doc.setFillColor(255, 255, 255);
            doc.rect(tableLeft, rowY, labelWidth, rowHeight, 'F');
            const labelY = rowY + rowHeight / 2 + 1;
            const valueY = rowY + rowHeight / 2 + 1;
            doc.setFont("times", "bold");
            doc.setFontSize(4.5);
            doc.setTextColor(...primaryBlue);
            doc.text(row.label, tableLeft + 4, labelY, { baseline: 'middle' });
            doc.setFont("times", "normal");
            doc.setFontSize(4.5);
            doc.setTextColor(0, 0, 0);
            doc.text(row.value, tableLeft + labelWidth + 5, valueY, { baseline: 'middle' });
        });
        window.stickerPdfBlob = doc.output('blob');
        const pdfURL = URL.createObjectURL(stickerPdfBlob);
        const frame = document.getElementById("stickerPreviewFrame");
        frame.src = pdfURL;
        frame.style.display = "block";
        const dockDownloadBtn = document.querySelector('.side-dock #downloadStickerBtn');
        if (dockDownloadBtn) dockDownloadBtn.style.display = "block";
        frame.scrollIntoView({ behavior: 'smooth' });
    }

    async function downloadSticker() {
        if (!window.stickerPdfBlob) {
            alert('Please generate the sticker first!');
            return;
        }
        const details = getFormDetails();
        const fileName = `InfoSticker_${details.certificateNumber || 'Unknown'}_${details.make || 'Unknown'}.pdf`;
        await savePDFWithLocation(window.stickerPdfBlob, fileName);
    }

    window.generateInfoSticker = generateInfoSticker;
    window.downloadSticker = downloadSticker;
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
