<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Full Lab Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'full_lab' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
        <h2 class="centered">COMPANY DETAILS</h2>
        <form id="calibrationForm">
            <div class="compnaydetails">
                <div class="title_input_pair">
                    <label for="partyName">Company Name:</label>
                    <input type="text" id="partyName" required>
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
                        <label for="siteLocation">Site Location:</label>
                        <input type="text" id="siteLocation" required>
                </div>
                <div class="title_input_pair">
                        <label for="certificateNumber">ENTER FIRST CERTIFICAT REF NO:</label>
                        <input type="text" id="certificateNumber" required>
                </div>
            </div>
            <div class="instrumentsinfo">
                <button type="button" id="ag_Button" class="toggle-button">AGGREGATE IMPACT VALUE APP </button>
                <div id="aginputContainer" class="ag-input-container">
                    <div class="title_input_pair">
                        <label for="agmake">Make:</label>
                        <input type="text" id="agmake">
                    </div>
                </div>    
                <button type="button" id="totalStationButton" class="toggle-button">TOTAL STATION</button>
                <div id="TSinputContainer" class="TS-input-container">
                    <div class="title_input_pair">
                        <label for="ts_serialno">Serial No:</label>
                        <input type="text" id="ts_serialno">
                    </div>
                    <div class="title_input_pair">
                        <label for="tsmake">Make:</label>
                        <input type="text" id="tsmake">
                    </div>
                    <div class="title_input_pair">
                        <label for="ts_model">Model:</label>
                        <input type="text" id="ts_model">
                    </div> 
                </div>
                <button type="button" id="penitometer_Button" class="toggle-button">CONE PENITO METER</button>
                <div id="penitometerinputContainer" class="penitometer-input-container">
                    <div class="title_input_pair">
                        <label for="penitometermake">Make:</label>
                        <input type="text" id="agmake">
                    </div>
                </div>  
                <button type="button" id="CTMButton" class="toggle-button">CUBE TESTING MACHINE</button>
                <div id="ctminputContainer" class="ctm-input-container">
                    <div class="title_input_pair">
                        <label for="ctmserialno">SERIAL NO:</label>
                        <input type="text" id="ctmserialno">
                    </div>
                    <div class="title_input_pair">
                        <label for="operated">Type of Machine:</label>
                        <select  id="operated">
                          <option value="Electrical Operated">Electrical Operated</option>
                          <option value="HAND Operated">HAND Operated</option>
                        </select>
                    </div>
                    <div class="title_input_pair">
                        <label for="ctmmake">MAKE:</label>
                        <input type="text" id="ctmmake">
                    </div>
                    <div class="title_input_pair">
                        <label for="ctmcapacity">Capacity:</label>
                        <select id="ctmcapacity">
                          <option value="1000 KN">1000 KN</option>
                          <option value="1200 KN">1200 KN</option>
                          <option value="1500 KN">1500 KN</option>
                          <option value="2000 KN">2000 KN</option>
                        </select> 
                    </div>
                    <div class="title_input_pair">
                        <label for="ring">PROVIRING WANT TO SELECT:</label>
                        <select id="ring" onchange="showInputBoxes()">
                          <option value="">SELECT PROVIRING</option>
                          <option value='1000KN'>1000 KN </option>
                          <option value='2000KN'>2000 KN</option>
                          <!-- <option value='3000KN'>3000 KN</option> -->
                        </select>
                    </div>  
                    <div class="READING-container">
                            <div id="reading1000" class="reading1000" style="display: none;">
                                <div class="title_input_pair">
                                <label for="input1">100</label>
                                <input type="text" id="i1" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input2">200</label>
                                <input type="text" id="i2" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input3">300</label>
                                <input type="text" id="i3" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input4">400</label>
                                <input type="text" id="i4" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input5">500</label>
                                <input type="text" id="i5" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input6">600</label>
                                <input type="text" id="i6" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input7">700</label>
                                <input type="text" id="i7" maxlength="7" >
                                </div>  
                                <div class="title_input_pair">
                                <label for="input8">800</label>
                                <input type="text" id="i8" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input9">900</label>
                                <input type="text" id="i9" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input10"> 1000</label>
                                <input type="text" id="i10" maxlength="7" >
                                </div>
                            </div>  
                            <div id="reading2000" class="reading2000" style="display: none;">
                            <div class="title_input_pair">
                                <label for="input1">200</label>
                                <input type="text" id="i21" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input2">400</label>
                                <input type="text" id="i22" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input3">600</label>
                                <input type="text" id="i23" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input4">800</label>
                                <input type="text" id="i24" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input5">1000</label>
                                <input type="text" id="i25" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input6">1200</label>
                                <input type="text" id="i26" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input7">1400</label>
                                <input type="text" id="i27" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input8">1600</label>
                                <input type="text" id="i28" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input9">1800</label>
                                <input type="text" id="i29" maxlength="7" >
                                </div>
                                <div class="title_input_pair">
                                <label for="input10"> 2000</label>
                                <input type="text" id="i210" maxlength="7" >
                                </div>
                            </div>
                    </div>
                </div>
                <button type="button" id="dg_thermometer_Button" class="toggle-button">DIGITAL THERMOMETER</button>
                <div id="dg_thermometerinputContainer" class="dg-thermometer-input-container">
                    <div class="title_input_pair">
                        <label for="dg_thermometercapicity">CAPACITY:</label>
                        <input type="text" id="dg_thermometercapicity">
                    </div>
                    <div class="title_input_pair">
                        <label for="dg_thermometermake">Make:</label>
                        <input type="text" id="dg_thermometermake">
                    </div>
                </div> 
                <button type="button" id="elogation_Button" class="toggle-button">ELOGATION GAUGE</button>
                <div id="elogationinputContainer" class="elogation-input-container">
                    <div class="title_input_pair">
                        <label for="elogationmake">Make:</label>
                        <input type="text" id="elogationmake">
                    </div>
                </div>    
                <button type="button" id="flakness_Button" class="toggle-button">FLAKNESS GAUGE</button>
                <div id="flaknessinputContainer" class="flakness-input-container">
                    <div class="title_input_pair">
                        <label for="flaknessmake">Make:</label>
                        <input type="text" id="flaknessmake">
                    </div>
                </div>
                <button type="button" id="oven_Button" class="toggle-button">ELECTRONIC HOT AIR OVEN</button>
                <div id="oveninputContainer" class="oven-input-container">
                    <div class="title_input_pair">
                        <label for="ovencapicity">CAPACITY:</label>
                        <input type="text" id="ovencapicity">
                    </div>
                    <div class="title_input_pair">
                        <label for="ovenmake">Make:</label>
                        <input type="text" id="ovenmake">
                    </div>
                    <div class="title_input_pair">
                        <label for="ovensize">SIZE:</label>
                        <input type="text" id="ovensize">
                    </div>
                </div>     
                 <button type="button" id="sandReplacementButton" class="toggle-button">SAND REPLACEMENT KIT</button>
                <div id="sandReplacementinputContainer" class="sandReplacement-input-container">
                    <!-- <div class="title_input_pair">
                        <label for="sand_replacement_sr">Serial No:</label>
                        <input type="text" id="sand_replacement_sr">
                    </div> -->
                    <div class="title_input_pair">
                        <label for="sand_replacement_make">Make:</label>
                        <input type="text" id="sand_replacement_make">
                    </div>
                    <div class="title_input_pair">
                        <label for="sand_replacement_capacity">CAPACITY:</label>
                        <input type="text" id="sand_replacement_capacity">
                    </div>
                </div>
                <button type="button" id="measuringCylinderButton" class="toggle-button">MEASURING CYLINDER</button>
                <div id="cylinderinputContainer" class="cylinder-input-container">
                    <!-- <div class="title_input_pair">
                        <label for="cylinder_serialno">Serial No:</label>
                        <input type="text" id="cylinder_serialno">
                    </div> -->
                    <div class="title_input_pair">
                        <label for="cylinder_make">Make:</label>
                        <input type="text" id="cylinder_make">
                    </div>
                    <div class="title_input_pair">
                        <label for="cylinder_capacity">CAPACITY:</label>
                        <input type="text" id="cylinder_capacity">
                    </div>
                </div>
                <button type="button" id="AutolevelButton" class="toggle-button">AUTO LEVEL</button>
                <div id="LevelinputContainer" class="level-input-container">
                    <div class="title_input_pair">
                        <label for="levelserialno">Serial No:</label>
                        <input type="text" id="levelserialno">
                    </div>
                    <div class="title_input_pair">
                        <label for="levelmake">Make:</label>
                        <input type="text" id="levelmake">
                    </div>
                    <div class="title_input_pair">
                        <label for="levelmodel">Model:</label>
                        <input type="text" id="levelmodel">
                    </div> 
                </div> 
                <button type="button" id="slumconeButton" class="toggle-button">SLUMCONE</button>
                <div id="slumconeinputContainer" class="slumcone-input-container">
                    <!-- <div class="title_input_pair">
                        <label for="slumcone_serialno">Serial No:</label>
                        <input type="text" id="slumcone_serialno">
                    </div> -->
                    <div class="title_input_pair">
                        <label for="slumcone_make">Make:</label>
                        <input type="text" id="slumcone_make">
                    </div>
                </div>
                <button type="button" id="water_bath_Button" class="toggle-button">WATER BATH</button>
                <div id="water_bathinputContainer" class="water-bath-input-container">
                    <div class="title_input_pair">
                        <label for="water_bathcapicity">CAPACITY:</label>
                        <input type="text" id="water_bathcapicity">
                    </div>
                    <div class="title_input_pair">
                        <label for="water_bathmake">Make:</label>
                        <input type="text" id="water_bathmake">
                    </div>
                </div>
                <button type="button" id="weigh_batcher_Button" class="toggle-button">WEIGH BATCHER</button>
                <div id="weigh_batcherinputContainer" class="weigh-batcher-input-container">
                    <div class="title_input_pair">
                        <label for="weigh_batchercapicity">CAPACITY:</label>
                        <input type="text" id="weigh_batchercapicity">
                    </div>
                    <div class="title_input_pair">
                        <label for="weigh_batchermake">Make:</label>
                        <input type="text" id="weigh_batchermake">
                    </div>
                    <div class="title_input_pair">
                        <label for="weigh_batchersr_no">SR NO:</label>
                        <input type="text" id="weigh_batchersr_no">
                    </div>
                </div>
            </div>
      <?php include __DIR__ . '/../includes/certificate_loader.php'; ?>
      
        </form>
    </div>
    <script src="<?= APP_URL ?>/assets/js/general.js?v=1.8"></script>
    <script>
        const INSTRUMENT_SLUG = 'full_lab';
        let pdfSaved = false;
        // Toggle input containers based on button selection
        const toggleInputContainer = (buttonId, containerId) => {
            document.getElementById(buttonId).addEventListener('click', function() {
                const container = document.getElementById(containerId);
                container.style.display = container.style.display === 'block' ? 'none' : 'block';
                this.classList.toggle('active');
            });
        };
        toggleInputContainer('AutolevelButton', 'LevelinputContainer');
        toggleInputContainer('CTMButton', 'ctminputContainer');
        toggleInputContainer('totalStationButton', 'TSinputContainer');
        toggleInputContainer('measuringCylinderButton', 'cylinderinputContainer');
        toggleInputContainer('sandReplacementButton', 'sandReplacementinputContainer');
        toggleInputContainer('slumconeButton', 'slumconeinputContainer');
        toggleInputContainer('ag_Button', 'aginputContainer');
        toggleInputContainer('penitometer_Button', 'penitometerinputContainer');
        toggleInputContainer('dg_thermometer_Button', 'dg_thermometerinputContainer');
        toggleInputContainer('elogation_Button', 'elogationinputContainer');
        toggleInputContainer('flakness_Button', 'flaknessinputContainer');
        toggleInputContainer('oven_Button', 'oveninputContainer');
        toggleInputContainer('water_bath_Button', 'water_bathinputContainer');
        toggleInputContainer('weigh_batcher_Button', 'weigh_batcherinputContainer');
        function showInputBoxes() {
            var option = document.getElementById("ring").value;
            var option1Inputs = document.getElementById("reading1000");
            var option2Inputs = document.getElementById("reading2000");
            option1Inputs.style.display = "none";
            option2Inputs.style.display = "none";
            if (option === "1000KN") {
                option1Inputs.style.display = "flex";
            } else if (option === "2000KN") {
                option2Inputs.style.display = "flex";
            }
        }
        // IMAGE PRELOADING LOGIC
        let headerImgB64, footerImgB64, stampImgB64, signImgB64;
        async function prepareImages() {
          if (!headerImgB64) headerImgB64 = await loadImageToBase64("../assets/images/header.jpeg");
          if (!footerImgB64) footerImgB64 = await loadImageToBase64("../assets/images/footer.jpeg");
          if (!stampImgB64)  stampImgB64  = await loadImageToBase64("../assets/images/stamp.jpeg");
          if (!signImgB64)   signImgB64   = await loadImageToBase64("../assets/images/sign.jpeg");
        }
        function loadImageToBase64(url) {
          return new Promise((resolve, reject) => {
            var img = new window.Image();
            // // img.crossOrigin = "Anonymous";
            img.onload = function () {
              var canvas = document.createElement("canvas");
              canvas.width = img.width;
              canvas.height = img.height;
              var ctx = canvas.getContext("2d");
              ctx.drawImage(img, 0, 0);
              resolve(canvas.toDataURL("image/png"));
            };
            img.onerror = reject;
            img.src = url;
          });
        }
        function getFormDetails() {
          return {
            partyName: document.getElementById('partyName').value,
            calibrationDate: document.getElementById('calibrationDate').value.split("-").reverse().join("/"),
            siteLocation: document.getElementById('siteLocation').value,
            nextCalibrationDate: document.getElementById('nextCalibrationDate').value.split("-").reverse().join("/"),
            certificateNumber: document.getElementById('certificateNumber').value,
            make: "Full Lab",
            modelNo: "Composite",
            serialNo: "N/A",
            saveentry: `FullLab_${document.getElementById('partyName').value}_${document.getElementById('certificateNumber').value}`
          };
        }
        function incrementCertificateNumber(certNo) {
          const match = certNo.match(/^(.*?)(\d+)$/);
          if (match) {
            const prefix = match[1];
            const numStr = match[2];
            const nextNum = parseInt(numStr, 10) + 1;
            const paddedNum = String(nextNum).padStart(numStr.length, '0');
            return prefix + paddedNum;
          }
          return certNo;
        }
        function addCertificateDetails(doc, details) {
          generateCertificate(doc);
        }
        function addImg(doc, details) {
          const pageCount = doc.internal.getNumberOfPages();
          for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            if (headerImgB64) doc.addImage(headerImgB64, 'PNG', 3, 3, 210, 30);
            if (footerImgB64) doc.addImage(footerImgB64, 'PNG', 0, 255, 210, 27);
            if (stampImgB64)  doc.addImage(stampImgB64,  'PNG', 90, 207, 40, 40);
            if (signImgB64)   doc.addImage(signImgB64,   'PNG', 160, 232, 40, 10);
          }
        }
        function generateCertificate(doc) {
            const partyName = document.getElementById('partyName').value;
            const calibrationDate= document.getElementById("calibrationDate").value.split("-").reverse().join("/");
            const siteLocation = document.getElementById('siteLocation').value;
            const nextCalibrationDate = document.getElementById('nextCalibrationDate').value.split("-").reverse().join("/");
            const first_certi_no = document.getElementById('certificateNumber').value;
            let CERTI_NO = first_certi_no;
            let firstPage = true;
            // Generate certificate for AUTOLEVEL if selected
            if (document.getElementById('AutolevelButton').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                const serialNo = document.getElementById('levelserialno').value;
                const make = document.getElementById('levelmake').value;
                const modelNo = document.getElementById('levelmodel').value;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 60, { align: 'center' });
                doc.setFontSize(15);
                doc.setFont("helvetica", "normal");   
                doc.text(`DATE:${calibrationDate}`, 155, 45);
                doc.text(`REF NO:SI-${CERTI_NO}`, 14, 45);
                doc.text(`FOR , ${make}  AUTO LEVEL`, doc.internal.pageSize.getWidth() / 2, 65, {  align: 'center' });
                doc.text(`MODLE : ${modelNo}  ,SR NO:- ${serialNo}`, doc.internal.pageSize. getWidth() / 2,70, { align: 'center' });
                doc.text(`NAME OF PARTY         :      ${partyName}`, 14, 85);
                doc.text(`EQUIPMENT NAME      :     AUTO LEVEL`, 14, 95);
                doc.text(`SERIAL NO                   :     ${serialNo}`, 14, 105);
                doc.text(`MAKE                            :     ${make}`, 14, 115);
                doc.text(`MODEL NO                   :     ${modelNo} `, 14, 125);
                doc.text(`NEXT DUE DATE         :     ${nextCalibrationDate}`, 14, 135);
                doc.text(`DATE                             :     ${calibrationDate}`, 14, 145);
                doc.setFontSize(12);
                doc.text(`This is to certify that ${make} Automatic Level-${modelNo} Serial No ${serialNo} Been Checked `, 14, 155);
                doc.text(` By us as under One year warranty.`, 14, 160);
                doc.text(`1. Level was kept At a distance of 20 meter from staff-A and 30 meter from Staff-B, Which `, 14, 170);
                doc.text(`were ).Pre-fixed at same level in our workshop (permanent bench-mark of same elevation).`, 14, 175);
                doc.text(`2. Reading of Staff-A was taken as 1,600`, 14, 180);
                doc.text(`3. Reading of Staff-B was taken as 1,600 .`, 14, 185);
                doc.text(`Hence error is NIL (Within the tolerance Level). Therefore, instrument is found free from  `, 14, 195);
                doc.text(`collimation error as of Date.`, 14, 200);
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }
            // Generate certificate for AGGRIGATE IMPACT VALUE APP if selected
            if (document.getElementById('ag_Button').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                const make=document.getElementById("agmake").value;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
                doc.setFontSize(10);
                doc.text(window.PDF_COMPANY_NAME + ": Calibration laboratory certifies that the instrument has been inspected, tested,", 12, 60);
                doc.text("and calibrated in accordance with documented procedures using measuring and test equipment traceable", 12, 65);
                doc.text("to international standards.", 12, 70);
                doc.setFontSize(12);
                doc.text(`Date: ${calibrationDate}`, 140, 80);
                doc.text(`REF No                    :     SI-${CERTI_NO}`, 14, 80);
                doc.text(`Name of Party         :     ${partyName}`, 14, 95);
                doc.text(`Instrument Name    :     CONE PENETOMETER`, 14, 110);
                doc.text(`Serial No                 :     SI-${CERTI_NO}`, 14, 125);
                doc.text(`MAKE                      :     ${make}(AS PER IS 2386)`, 14, 140);
                doc.text(`Site Location           :     ${siteLocation}`, 14, 155);
                doc.text(`Next Due Date         :     ${nextCalibrationDate}`, 14, 170);
                doc.text(`Calibration By         :     YOGESH BHAI`, 14, 185);
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }
            // Generate certificate for Cube Testing Machine if selected
            if (document.getElementById('CTMButton').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                const ring=document.getElementById("ring").value;
                const operated=document.getElementById("operated").value;
                const serialNo = document.getElementById('ctmserialno').value;
                const make = document.getElementById('ctmmake').value;
                const capacity = document.getElementById('ctmcapacity').value;
                const inputs1000 = [];
                for (let i = 1; i <= 10; i++) {
                    inputs1000.push(document.getElementById(`i${i}`).value);
                }
                const inputs2000 = [];
                for (let i = 1; i <= 10; i++) {
                    inputs2000.push(document.getElementById(`i2${i}`).value);
                }
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: "center" });
                doc.setFontSize(10);
                doc.setFont("helvetica", "bold");
                doc.setFontSize(12);
                doc.text(`REF NO:- SI-${CERTI_NO}`, 14, 60);
                doc.text(`Date:-    ${calibrationDate}`, 140, 60);
                doc.text(`Name of Party      :-    ${partyName}`, 14, 70);
                doc.text(`Instrument name :-    CUBE TESTING MACHINE (${operated})`, 14, 80);
                doc.text(`Capacity  / MAKE  :-    ${capacity}  /  ${make}`, 14, 90);
                doc.text(`Serial No                :-    ${serialNo}`, 14, 100);
                doc.text(`Next Due Date:-    ${nextCalibrationDate}`, 140, 100);
                doc.text(`Site Location         :-    ${siteLocation}`, 14, 110); 
                doc.setFontSize(10);
                doc.setFont("helvetica", "bold");
                let RING=String(ring);
                if(RING==="1000KN"){
                    doc.text(`CALIBRATION INSTRUMENT 1000KN`, 14, 120);  
                    doc.text(`PROVING RING NO:1000KN 065 IS 4169:2014`, 14, 125);  
                    doc.text(`CALIBRATED BY : NATIONAL COUNCIL FOR CEMENT AND BUILDING MATERIALS`, 14, 130);  
                    doc.setFont("helvetica", "bold");
                    doc.setFontSize(11);
                    const startX = 13;
                    const startY = 142;
                    const cellWidth = 30;
                    const cellHeight = 7;
                    doc.text(`Divisions`, 18, 141);
                    doc.text(`KN`, 174, 141);
                    const fixedTexts = ["Deflection In", "LOAD IN KN", "1st set in KN", "2nd set in KN", "3rd set in KN", "AVERAGE in"];
                    for (let k = 0; k < 6; k++) {
                        const x = startX + k * cellWidth;
                        doc.rect(x, 132, cellWidth, 10);
                        doc.text(fixedTexts[k], x + 2, 137);
                    }
                    const fixedValuesColumn1 = ["79.1", "155.2", "232.3", "308.1", "384.4", "460.1","536.7", "613.4", "689.7", "766.1"];
                    const fixedValuesColumn2 = ["100", "200", "300", "400", "5000", "600", "700", "800", "900", "1000"];
                    for (let i = 0; i < 10; i++) {
                        for (let j = 0; j < 6; j++) {
                            const x = startX + j * cellWidth;
                            const y = startY + i * cellHeight;
                            doc.rect(x, y, cellWidth, cellHeight);
                            let textValue = "";
                            if (j === 0) textValue = fixedValuesColumn1[i];
                            else if (j === 1) textValue = fixedValuesColumn2[i];
                            else textValue = inputs1000[i];
                            const textWidth = doc.getTextWidth(textValue);
                            const centeredX = x + (cellWidth - textWidth) / 2;
                            doc.text(textValue, centeredX, y + 6);
                        }
                    }
                }
                else if(RING==="2000KN"){
                    doc.text(`CALIBRATION INSTRUMENT 2000KN`, 14, 120);  
                    doc.text(`PROVING RING NO:2000KN 094 IS 4169:2014`, 14, 125);  
                    doc.text(`CALIBRATED BY : NATIONAL COUNCIL FOR CEMENT AND BUILDING MATERIALS`, 14, 130);  
                    doc.setFont("helvetica", "bold");
                    doc.setFontSize(11);
                    const startX = 13;
                    const startY = 142;
                    const cellWidth = 30;
                    const cellHeight = 7;
                    doc.text(`Divisions`, 18, 141);
                    doc.text(`KN`, 174, 141);
                    const fixedTexts = ["Deflection In", "LOAD IN KN", "1st set in KN", "2nd set in KN", "3rd set in KN", "AVERAGE in"];
                    for (let k = 0; k < 6; k++) {
                        const x = startX + k * cellWidth;
                        doc.rect(x, 132, cellWidth, 10);
                        doc.text(fixedTexts[k], x + 2, 137);
                    }
                    const fixedValuesColumn1 = ["84.1", "168.6", "254.7", "342.1", "429.1", "513.9", "600.9", "689.2", "776.7", "865.8"];
                    const fixedValuesColumn2 = ["200", "400", "600", "800", "1000", "1200", "1400", "1600", "1800", "2000"];
                    for (let i = 0; i < 10; i++) {
                        for (let j = 0; j < 6; j++) {
                            const x = startX + j * cellWidth;
                            const y = startY + i * cellHeight;
                            doc.rect(x, y, cellWidth, cellHeight);
                            let textValue = "";
                            if (j === 0) textValue = fixedValuesColumn1[i];
                            else if (j === 1) textValue = fixedValuesColumn2[i];
                            else textValue = inputs2000[i];
                            const textWidth = doc.getTextWidth(textValue);
                            const centeredX = x + (cellWidth - textWidth) / 2;
                            doc.text(textValue, centeredX, y + 6);
                        }
                    }
                }
                else if(ring.value=="3000KN"){
                    alert("THIS OPTION IS NOT AVAILABLE");
                    return;
                }
                else{
                    console.log(ring);
                    alert("Select a appropreat proviring");
                    return;
                }
                doc.text(`Calibration By      :-   YOGESH BHAI`, 14, 225); 
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }
            // Generate certificate for DIGITAL THERMOMETER if selected 
            if (document.getElementById('dg_thermometer_Button').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                const capacity=document.getElementById("dg_thermometercapicity").value;
                const make=document.getElementById("dg_thermometermake").value;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
                doc.setFontSize(12);
                doc.text(`Date:-${calibrationDate}`, 140, 60);
                doc.text(`REF NO:- SI-${CERTI_NO}`, 14, 60);
                doc.text(`Name of Party               :-   ${partyName}`, 14, 70);
                doc.text(`Name OF Instrument    :-   DIGITAL THERMOMETER`, 14, 80);
                doc.text(`Capacity & Make          :-   ${capacity} & ${make}`, 14, 90);
                doc.text(`Identification No           :-   SI-${CERTI_NO}`, 14, 100);
                doc.text(`Next Due Date               :-   ${nextCalibrationDate}`, 14, 110);
                doc.text(`Site Location                 :-   ${siteLocation}`, 14, 120);
                const tableStartY =120;
                const data = [
                [ "1", " 50"," 50"],
                [ "2", " 100"," 100" ],
                [ "3", " 150"," 150" ],
                [ "4", " 200"," 200" ],
                [ "5", " 250"," 250" ],
                [ "6", " 300"," 300" ]
                ];
                doc.autoTable({
                    head: [['SR.NO', 'STANDARD TEMPERATURE', 'STANDARD TEMPERATURE BY 1 St Bucket “A”']],
                    body: data,
                    startY: tableStartY + 10,
                    styles: { 
                        fontSize: 12 ,
                        lineColor:[87, 86, 85],
                        lineWidth: 0.2,
                        halign: 'center',
                        valign: 'middle',
                    },
                    headStyles: {
                        fontSize: 15,
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
                let tableStartY2=doc.autoTable.previous.finalY;
                doc.setFontSize(12);
                doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, tableStartY2+=5);
                doc.setFont("helvetica", "BOLD"); 
                doc.setFontSize(12); 
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }    
            // Generate certificate for ELOGATION GAUGE if selected
            if (document.getElementById('elogation_Button').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
                doc.setFontSize(12);
                doc.text(`Date:-${calibrationDate}`, 140, 60);
                doc.text(`REPORT NO:-SHREEJI/${CERTI_NO}`, 14, 60);
                doc.text(`Name OF Instrument   :-     ELOGATION GAUGE`, 14, 70);
                doc.text(`Next Due Date:-${nextCalibrationDate}`, 140, 70);
                doc.text(`As Per IS                       :-     IS 2386-1`, 14, 80);
                doc.text(`Identification No:- SI-${CERTI_NO}`, 140, 80);
                doc.text(`Name of Party               :-     ${partyName}`, 14, 90);
                doc.text(`Site Location                :-     ${siteLocation}`, 14, 100);
                const tableStartY =100;
                const sieveData = [
                    ['50.00 MM', '40.00 MM', '81.00 MM', '81.00 MM'],
                    ['40.00 MM', '31.50 MM', '64.40 MM', '64.40 MM'],
                    ['31.50 MM', '25.00 MM', '40.50 MM', '40.50 MM'],
                    ['25.00 MM', '20.00 MM', '32.40 MM', '32.40 MM'],
                    ['20.00 MM', '16.00 MM', '25.60 MM', '25.60 MM'],
                    ['16.00 MM', '12.50 MM', '20.00 MM', '20.20 MM'],
                    ['12.50 MM', '10.00 MM', '14.67 MM', '14.67 MM'],
                    ['10.00 MM', '6.30 MM', '81.00 MM', '81.00 MM']
                ];
                doc.autoTable({
                    head: [['PASSING SIEVE', 'RATAINED SIEVE', 'REQUIRED GAUGE', 'ACTUAL GAUGE SIZE']],
                    body: sieveData,
                    startY: tableStartY + 10,
                    styles: { 
                        fontSize: 8 ,
                        lineColor:[87, 86, 85],
                        lineWidth: 0.2,
                        halign: 'center',
                        valign: 'middle',
                    },
                    headStyles: {
                        fontSize: 10,
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
                let tableStartY2=doc.autoTable.previous.finalY;
                doc.text('DETAILS OF STANDARD EQUIPMENT USED FOR CALIBRATION',  doc.internal.pageSize.getWidth() / 2, tableStartY2+=10, { align: 'center' });
                doc.setFont("helvetica", "sipmle");
                doc.setFontSize(9);
                doc.text('EQUIPMENT NAME  :-DIGITAL VERNIER CALIPER', 14, tableStartY2+=7);
                doc.text('CALIBRATION BY :-ARSHI ENTERPRISE, AHMEDABAD', 100,  tableStartY2);
                doc.text('CALIBRATION DATE :-01/08/2024', 14,tableStartY2+=7);
                doc.text('NEXT DUE DATE  :-31/08/2025', 100, tableStartY2);
                doc.setFont("helvetica", "BOLD"); 
                doc.setFontSize(12); 
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }
            // Generate certificate for flakness if selected 
            if (document.getElementById('flakness_Button').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
                doc.setFontSize(12);
                doc.text(`Date:-${calibrationDate}`, 140, 60);
                doc.text(`REPORT NO:-SHREEJI/${CERTI_NO}`, 14, 60);
                doc.text(`Name OF Instrument   :-     FLAKNESS GAUGE`, 14, 70);
                doc.text(`Next Due Date:-${nextCalibrationDate}`, 140, 70);
                doc.text(`As Per IS                       :-     IS 2386-1`, 14, 80);
                doc.text(`Identification No:- SI-${CERTI_NO}`, 140, 80);
                doc.text(`Name of Party               :-     ${partyName}`, 14, 90);
                doc.text(`Site Location                :-     ${siteLocation}`, 14, 100);
                const tableStartY =100;
                const sieveData = [
                    ['63.00 mm', '50.00 mm', '33.90 mm', '33.90 mm'],
                    ['50.00 mm', '40.00 mm', '27.00 mm', '270.00 mm'],
                    ['40.00 mm', '31.00 mm', '21.45 mm', '21.45 mm'],
                    ['31.50 mm', '25.00 mm', '16.95 mm', '16.95 mm'],
                    ['25.00 mm', '20.00 mm', '13.50 mm', '13.50 mm'],
                    ['20.00 mm', '16.00 mm', '10.80 mm', '10.80 mm'],
                    ['16.00 mm', '12.50 mm', '8.55 mm', '8.55 mm'],
                    ['12.50 mm', '10.00 mm', '6.75 mm', '6.75 mm'],
                    ['10.00 mm', '6.30 mm', '4.89 mm', '4.89 mm'],
                ];
                doc.autoTable({
                    head: [['PASSING SIEVE', 'RATAINED SIEVE', 'REQUIRED GAUGE', 'ACTUAL GAUGE SIZE']],
                    body: sieveData,
                    startY: tableStartY + 10,
                    styles: { 
                        fontSize: 8 ,
                        lineColor:[87, 86, 85],
                        lineWidth: 0.2,
                        halign: 'center',
                        valign: 'middle',
                    },
                    headStyles: {
                        fontSize: 10,
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
                let tableStartY2=doc.autoTable.previous.finalY;
                doc.text('DETAILS OF STANDARD EQUIPMENT USED FOR CALIBRATION',  doc.internal.pageSize.getWidth() / 2, tableStartY2+=10, { align: 'center' });
                doc.setFont("helvetica", "sipmle");
                doc.setFontSize(9);
                doc.text('EQUIPMENT NAME  :-DIGITAL VERNIER CALIPER', 14, tableStartY2+=7);
                doc.text('CALIBRATION BY :-ARSHI ENTERPRISE, AHMEDABAD', 100,  tableStartY2);
                doc.text('CALIBRATION DATE :-01/08/2024', 14,tableStartY2+=7);
                doc.text('NEXT DUE DATE  :-31/08/2025', 100, tableStartY2);
                doc.setFont("helvetica", "BOLD"); 
                doc.setFontSize(12); 
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }
            // Generate certificate for HOT AIR OVEN if selected 
            if (document.getElementById('oven_Button').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                const make=document.getElementById("ovenmake").value;
                const size=document.getElementById("ovensize").value;
                const capacity=document.getElementById("ovencapicity").value;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
                doc.setFontSize(12);
                doc.text(`Date:-${calibrationDate}`, 140, 60);
                doc.text(`REF NO:- SI-${CERTI_NO}`, 14, 60);
                doc.text(`Name of Party               :-   ${partyName}`, 14, 70);
                doc.text(`Name OF Instrument    :-  ELECTRICAL HOT AIR OVEN(${size})`, 14, 80);
                doc.text(`Capacity & Make          :-   ${capacity} & ${make}`, 14, 90);
                doc.text(`SR No                            :-   SI-${CERTI_NO}`, 14, 100);
                doc.text(`Next Due Date               :-   ${nextCalibrationDate}`, 14, 110);
                doc.text(`Site Location                 :-   ${siteLocation}`, 14, 120);
                const tableStartY =120;
                const data = [
                    [ "1", " 50","50"],
                    [ "2", " 100","100"],
                    [ "3", " 150","15O"],
                    [ "4", " 200","200"],
                    [ "5", " 250","25O"],
                ];
                doc.autoTable({
                    head: [['SR.NO', 'STANDARD TEMPERATURE', 'STANDARD TEMPERATURE BY 1 St Bucket “A”']],
                    body: data,
                    startY: tableStartY + 10,
                    styles: { 
                        fontSize: 12 ,
                        lineColor:[87, 86, 85],
                        lineWidth: 0.2,
                        halign: 'center',
                        valign: 'middle',
                    },
                    headStyles: {
                        fontSize: 15,
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
                let tableStartY2=doc.autoTable.previous.finalY;
                doc.setFontSize(12);
                doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, tableStartY2+=10);
                doc.setFont("helvetica", "BOLD"); 
                doc.setFontSize(12); 
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }    
            // Generate certificate for TOTAL STATION  if selected
            if (document.getElementById('totalStationButton').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }   
                const make = document.getElementById("tsmake").value;
                const serialNo= document.getElementById("ts_serialno").value;
                const modelNo= document.getElementById("ts_model").value;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(15);
                doc.text(`TEST CERTIFICATE FOR`,  doc.internal.pageSize.getWidth() / 2, 37,{ align: 'center' });
                doc.text("ELECTRONIC TOTAL STATION", doc.internal.pageSize.getWidth() /2,42,{ align: 'center' });
                doc.setFontSize(25);   
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 52, { align: 'center' });
                doc.setFont("helvetica", "normal"); 
                doc.setFontSize(15);  
                doc.text(`REF NO:SI-${CERTI_NO}`, 14, 60);
                doc.text(`NAME OF PARTY          :     ${partyName}`, 14, 68);
                doc.text(`EQUIPMENT NAME      :     ELECTRONIC TOTAL STATION`, 14, 76);
                doc.text(`SERIAL NO                     :     ${serialNo}`, 14, 84);
                doc.text(`MAKE                              :     ${make}`, 14, 92);
                doc.text(`MODEL NO                     :     ${modelNo} `, 14, 100);
                doc.text(`NEXT DUE DATE          :     ${nextCalibrationDate}`, 14, 108);
                doc.text(`DATE                               :     ${calibrationDate}`, 14, 115);
                doc.setFontSize(12);
                doc.setFont("helvetica", "bold");
                doc.text(`i. GENERAL CHECKING AS UNDER. `, 14, 120);
                doc.text(`ii. HORIZONTAL CIRCLE CHECKED AS UNDER. `, 14, 145);
                doc.text(`iii. VERTICAL CIRCLE CHECKED AS UNDER.`, 14, 175);
                doc.setFont("helvetica", "normal");   
                doc.text(`•Diaphragm of the Instrument checked. Found Satisfactory`, 14, 125);
                doc.text(`•Optical Plummet checked in all 360 Degrees. Found Satisfactory `, 14, 130);
                doc.text(`•Bubble checked in all 360 degrees. Found Accurate.`, 14, 135);
                doc.text(`•Set Circle reading 0 degree, 0 minute, 0 second, point sighted 'X' approx. 30 meter away from the instrument.`, 14, 150);
                doc.text(`•Telescope reversed. Point sighted Y' approx. 15 meter away from instrument. `, 14, 155);
                doc.text(`•Alidade rotated through 180 degrees, O minute, O second, sighted point 'X' again. `, 14, 160);
                doc.text(`•Telescope reversed. It automatically sighted point 'Y' Error - Nil.`, 14, 165);
                doc.text(`•Sighted Telescope at a clearly defined object. (Point 'X' approx. 30 meter away from the Instrument) `, 14, 180);
                doc.text(`•Vertical Circle reading was 90 degree, O minute, O second.`, 14, 185);
                doc.text(`•Reversed Telescope, rotated alidade through 180 degrees, sighted same point, vertical circle Reading was 270  `, 14, 190);
                doc.text(` degrees, 0 minutes, 0 seconds, Hence Error-Nil. `, 14, 195);
                doc.text(`•Therefore the Electronic Total Station is certified as free from collimation error as date & Error - Nil`, 14, 205);
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }
            // Generate certificate for SLUM CONE  if selected
            if (document.getElementById('slumconeButton').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                const make= document.getElementById("slumcone_make").value;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
                doc.setFontSize(10);
                doc.text(window.PDF_COMPANY_NAME + ": Calibration laboratory certifies that the instrument has been inspected, tested,", 12, 60);
                doc.text("and calibrated in accordance with documented procedures using measuring and test equipment traceable", 12, 65);
                doc.text("to international standards.", 12, 70);
                let hori_axis=70;
                doc.text("From the below test this is to certify that the Slump test apparatus is meeting necessary requirements as", 12, hori_axis+=9);
                doc.text("per IS: 7320-1974 within the permissible limit. ", 12, hori_axis+=5);
                doc.setFontSize(12);
                doc.text(`REF NO:SI-${CERTI_NO}`, 14, hori_axis+=10);
                doc.text(`Date: ${calibrationDate}`, 140, hori_axis);
                doc.text(`Name of Party         :     ${partyName}`, 14, hori_axis+=10);
                doc.text(`Instrument Name    :     SLUMCONE `, 14, hori_axis+=10);
                doc.text(`Serial No / Make      :     ${CERTI_NO} / ${make}`, 14, hori_axis+=10);
                doc.text(`Site Location           :     ${siteLocation}`, 14, hori_axis+=10);
                doc.text(`Next Due Date         :     ${nextCalibrationDate}`, 14, hori_axis+=10);
                doc.text("SPECIFICATIONS:-", doc.internal.pageSize.getWidth() / 2, hori_axis+=10, { align: 'center' });
                const data = [
                    [ "TOP DIA", "100 + 3.0 - 1.5"," 100.10"],
                    [ "BOTTOM DIA", "200 + 3.0 - 1.5","200.80" ],
                    [ "HEIGHT", "300 + 3.0 - 1.5","300.40" ],
                ];
                doc.autoTable({
                    head: [['  ','AS PER IS(MM)', 'ACTUAL MEASURED(AVG. OF THREE)']],
                    body: data,
                    startY: hori_axis + 5,
                    styles: { 
                        fontSize: 12 ,
                        lineColor:[87, 86, 85],
                        lineWidth: 0.2,
                        halign: 'center',
                        valign: 'middle',
                    },
                    headStyles: {
                        fontSize: 15,
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
                let tableStartY2=doc.autoTable.previous.finalY;
                doc.setFontSize(12);
                doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, tableStartY2+=10);
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }                        
            // Generate certificate for MEASURING CYLINDER if selected
            if (document.getElementById('measuringCylinderButton').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                const make= document.getElementById("cylinder_make").value;
                const capacity= document.getElementById("cylinder_capacity").value;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
                doc.setFontSize(10);
                doc.text(window.PDF_COMPANY_NAME + ": Calibration laboratory certifies that the instrument has been inspected, tested,", 12, 60);
                doc.text("and calibrated in accordance with documented procedures using measuring and test equipment traceable", 12, 65);
                doc.text("to international standards.", 12, 70);
                doc.setFontSize(12);
                doc.text(`REF NO:SI-${CERTI_NO}`, 14, 80);
                doc.text(`Date: ${calibrationDate}`, 140, 80);
                doc.text(`Name of Party         :     ${partyName}`, 14, 95);
                doc.text(`Instrument Name    :     MEASURING CYLINDER `, 14, 110);
                doc.text(`Capacity                 :     ${capacity}`, 14, 125);
                doc.text(`Serial No / Make     :     ${CERTI_NO} / ${make}`, 14, 140);
                doc.text(`Site Location           :     ${siteLocation}`, 14, 155);
                doc.text(`Next Due Date         :     ${nextCalibrationDate}`, 14, 170);
                doc.text(`Calibration By         :     YOGESH BHAI`, 14, 185);
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }
            // Generate certificate for SAND REPLACEMENT KIT if selected
            if (document.getElementById('sandReplacementButton').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                const make= document.getElementById("sand_replacement_make").value;
                const capacity= document.getElementById("sand_replacement_capacity").value;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
                doc.setFontSize(10);
                doc.text(window.PDF_COMPANY_NAME + ": Calibration laboratory certifies that the instrument has been inspected, tested,", 12, 60);
                doc.text("and calibrated in accordance with documented procedures using measuring and test equipment traceable", 12, 65);
                doc.text("to international standards.", 12, 70);
                doc.setFontSize(12);
                doc.text(`Date: ${calibrationDate}`, 140, 80);
                doc.text(`REF NO:SI-${CERTI_NO}`, 14, 80);
                doc.text(`Name of Party         :     ${partyName}`, 14, 95);
                doc.text(`Instrument Name    :     SAND REPLACEMENT KIT  `, 14, 110);
                doc.text(`Capacity                 :     ${capacity}`, 14, 125);
                doc.text(`Serial No / Make      :     ${CERTI_NO} / ${make}`, 14, 140);
                doc.text(`Site Location           :     ${siteLocation}`, 14, 155);
                doc.text(`Next Due Date         :     ${nextCalibrationDate}`, 14, 170);
                doc.text(`Calibration By         :     YOGESH BHAI`, 14, 185);
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }
            // Generate certificate for WATER BATH if selected
            if (document.getElementById('water_bath_Button').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                const make=document.getElementById("water_bathmake").value;
                const capacity=document.getElementById("water_bathcapicity").value;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
                doc.setFontSize(12);
                doc.text(`Date:-${calibrationDate}`, 140, 60);
                doc.text(`REF NO:- SI ${CERTI_NO}`, 14, 60);
                doc.text(`Name of Party               :-   ${partyName}`, 14, 70);
                doc.text(`Name OF Instrument    :-  ELECTRICAL WATER BATH`, 14, 80);
                doc.text(`Capacity & Make          :-   ${capacity} & ${make}`, 14, 90);
                doc.text(`Identification No           :-   ${CERTI_NO}`, 14, 100);
                doc.text(`Next Due Date               :-   ${nextCalibrationDate}`, 14, 110);
                doc.text(`Site Location                 :-   ${siteLocation}`, 14, 120);
                const tableStartY =120;
                const data = [
                    [ "1", " 20'C"," 20'C"],
                    [ "2", " 40'C"," 40'C" ],
                    [ "3", " 60'C"," 60'C" ],
                    [ "4", " 80'C"," 80'C" ],
                    [ "5", " 100'C"," 100'C" ],
                    [ "6", " 110'C"," 110'C" ]
                ];
                doc.autoTable({
                    head: [['SR.NO', 'STANDARD TEMPERATURE', 'STANDARD TEMPERATURE BY 1 St Bucket “A”']],
                    body: data,
                    startY: tableStartY + 10,
                    styles: { 
                        fontSize: 12 ,
                        lineColor:[87, 86, 85],
                        lineWidth: 0.2,
                        halign: 'center',
                        valign: 'middle',
                    },
                    headStyles: {
                        fontSize: 15,
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
                let tableStartY2=doc.autoTable.previous.finalY;
                doc.setFontSize(12);
                doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, tableStartY2+=5);
                doc.setFont("helvetica", "BOLD"); 
                doc.setFontSize(12); 
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);
            }    
            // Generate certificate for WEIGH BATCHER if selected
            if (document.getElementById('weigh_batcher_Button').classList.contains('active')) {
                if (!firstPage) {
                    doc.addPage();
                    CERTI_NO = incrementCertificateNumber(CERTI_NO);
                }
                firstPage = false;
                const make=document.getElementById("weigh_batchermake").value;
                const serialNo=document.getElementById("weigh_batchersr_no").value;
                const capacity=document.getElementById("weigh_batchercapicity").value;
                doc.setFont("helvetica", "bold");
                doc.setFontSize(25);
                doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
                doc.setFontSize(12);
                doc.text(`Date:-${calibrationDate}`, 140, 60);
                doc.text(`REF NO:- SI ${CERTI_NO}`, 14, 60);
                doc.text(`Name of Party               :-   ${partyName}`, 14, 70);
                doc.text(`Name OF Instrument    :-  WEIGH BATCHER`, 14, 80);
                doc.text(`Capacity & Make          :-   ${capacity} & ${make}`, 14, 90);
                doc.text(`SR No                            :-   ${serialNo}`, 14, 100);
                doc.text(`Next Due Date               :-   ${nextCalibrationDate}`, 14, 110);
                doc.text(`Site Location                 :-   ${siteLocation}`, 14, 120);
                const tableStartY =120;
                const data = [
                    [ "1", " 50KG","50KG","50KG"],
                    [ "2", " 100KG","100KG","100KG"],
                    [ "3", " 150KG","15OKG","150KG"],
                    [ "4", " 200KG","200KG","200KG"],
                    [ "5", " 250KG","25OKG","250KG"],
                ];

                doc.autoTable({
                        head: [['SR.NO', 'STANDARD WEIGHTS', 'WEIGHT SHOWN BY 1 ST BUCKET','WEIGHT SHOWN BY 2ND BUCKET']],
                        body: data,
                        startY: tableStartY + 10,
                        styles: { 
                        fontSize: 12 ,
                        lineColor:[87, 86, 85],
                        lineWidth: 0.2,
                        halign: 'center',
                        valign: 'middle',
                        },
                        headStyles: {
                        fontSize: 15,
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
                    let tableStartY2=doc.autoTable.previous.finalY;
                // Add calibrated by
                doc.setFontSize(12);
                doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, tableStartY2+=10);

                // Add footer
                doc.setFont("helvetica", "BOLD"); 
                doc.setFontSize(12); 
                doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
                doc.text("Proprietor", 170, 245);

            }    
        }

    </script>


<?php include __DIR__ . '/../includes/footer.php'; ?>
