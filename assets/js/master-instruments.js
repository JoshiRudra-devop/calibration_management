/* assets/js/master-instruments.js - Central Master Equipment Helper */
window.getMasterDetails = function(slug) {
  if (window.MASTER_INSTRUMENTS && window.MASTER_INSTRUMENTS[slug]) {
    return window.MASTER_INSTRUMENTS[slug];
  }
  return {
    slug: slug || 'digital_vernier_caliper',
    name: 'DIGITAL VERNIER CALIPER',
    serial_no: 'ACCUPLUS/13-200',
    range_capacity: '0-200MM',
    least_count: '0.001" (0.01MM)',
    calib_date: '02/08/2026',
    due_date: '01/08/2027',
    cert_no: '62',
    calibrated_by: 'IDEMI CALIBRATION LABORATORY',
    traceability: 'OUR MASTER INSTRUMENT IS CALIBRATED AND TRACEABLE TO NATIONAL STANDARD THROUGH NABL ACCREDITED LABORATORY "IDEMI CALIBRATION LABORATORY."'
  };
};
