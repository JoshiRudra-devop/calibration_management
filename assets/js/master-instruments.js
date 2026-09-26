/* assets/js/master-instruments.js - Central Master Equipment Helper */
window.getMasterDetails = function(slug) {
  if (window.MASTER_INSTRUMENTS && window.MASTER_INSTRUMENTS[slug]) {
    return window.MASTER_INSTRUMENTS[slug];
  }
  const defaults = {
    'digital_vernier_caliper': {
      slug: 'digital_vernier_caliper',
      name: 'DIGITAL CALIPER',
      serial_no: 'Accu Plus / 13-200 (DC-01)',
      range_capacity: '0-200 mm',
      least_count: '0.01 mm',
      calib_date: '02/10/2025',
      due_date: '01/10/2026',
      cert_no: '1025/392/001',
      calibrated_by: 'ACLPL, AHMEDABAD',
      traceability: 'TRACEABLE TO NATIONAL STANDARDS THROUGH ARSH CALIBRATION LABORATORY PVT. LTD. (Cert No: 1025/392/001, ULR: CC312625000003463F)'
    },
    'digital_thermo': {
      slug: 'digital_thermo',
      name: 'DIGITAL THERMOMETER',
      serial_no: 'Multi / DTM-01',
      range_capacity: '-50 to 300 °C',
      least_count: '0.1 °C',
      calib_date: '02/10/2025',
      due_date: '01/10/2026',
      cert_no: '1025/392/002',
      calibrated_by: 'ACLPL, AHMEDABAD',
      traceability: 'TRACEABLE TO NATIONAL STANDARDS THROUGH ARSH CALIBRATION LABORATORY PVT. LTD. (Cert No: 1025/392/002, ULR: CC312625000003464F)'
    },
    'standard_thermometer': {
      slug: 'standard_thermometer',
      name: 'DIGITAL THERMOMETER',
      serial_no: 'Multi / DTM-01',
      range_capacity: '-50 to 300 °C',
      least_count: '0.1 °C',
      calib_date: '02/10/2025',
      due_date: '01/10/2026',
      cert_no: '1025/392/002',
      calibrated_by: 'ACLPL, AHMEDABAD',
      traceability: 'TRACEABLE TO NATIONAL STANDARDS THROUGH ARSH CALIBRATION LABORATORY PVT. LTD. (Cert No: 1025/392/002, ULR: CC312625000003464F)'
    },
    'standard_weights': {
      slug: 'standard_weights',
      name: 'STANDARD WEIGHTS',
      serial_no: 'W-01 TO W-05',
      range_capacity: '500g - 10kg',
      least_count: '0.001g',
      calib_date: '02/10/2025',
      due_date: '01/10/2026',
      cert_no: '1025/392/003 TO 1025/392/007',
      calibrated_by: 'ACLPL, AHMEDABAD',
      traceability: 'TRACEABLE TO NATIONAL STANDARDS THROUGH ARSH CALIBRATION LABORATORY PVT. LTD. (Cert Nos: 1025/392/003-007)'
    }
  };
  return defaults[slug] || defaults['digital_vernier_caliper'];
};
