*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Hind Siliguri',sans-serif;background:#f0f0f0;min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:24px 16px}
.btn{padding:9px 20px;border-radius:8px;cursor:pointer;border:none;font-family:inherit;font-size:0.9rem;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-primary{background:#1a4731;color:#fff}.btn-outline{background:#fff;border:1px solid #bbb;color:#333}
.card-set{display:flex;flex-direction:column;align-items:center;gap:24px;width:100%}
.card-wrap{display:flex;flex-direction:column;align-items:center;gap:6px}
.card-label{font-size:0.72rem;color:#666;font-weight:600;text-transform:uppercase;letter-spacing:1px}

/* 3in × 2in = 288px × 192px at 96dpi */
.id-card{
  width:288px;height:192px;
  background:#fff;
  border:1.5px solid #222;
  border-radius:10px;
  padding:11px 13px;
  font-family:'Hind Siliguri',sans-serif;
  position:relative;overflow:hidden;
  box-shadow:0 4px 20px rgba(0,0,0,0.18);
}

/* Gradient strip at top */
.id-card .top-strip{
  position:absolute;top:0;left:0;right:0;height:5px;
  background:linear-gradient(90deg,#1a4731,#2d6a4f,#f4a261);
}
/* Watermark */
.id-card .wm{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;z-index:0}
.id-card .wm img{width:100px;height:100px;object-fit:contain;opacity:0.06;filter:grayscale(100%)}
.id-card .wm-text{font-size:3rem;font-weight:700;opacity:0.05;color:#000;transform:rotate(-20deg)}
.id-card>*:not(.top-strip):not(.wm){position:relative;z-index:1}

/* Card header */
.card-hdr{text-align:center;padding-top:4px;border-bottom:1px solid #ddd;padding-bottom:5px;margin-bottom:6px}
.card-hdr img{height:20px;margin-bottom:2px;filter:grayscale(100%)}
.card-hdr h3{font-family:'Merriweather',serif;font-size:0.75rem;color:#111;margin:0;line-height:1.2}
.card-hdr .tl{font-size:0.5rem;color:#555;margin-top:1px}
.card-hdr .ad{font-size:0.48rem;color:#666}

/* Card body rows */
.card-row{display:flex;gap:4px;margin-bottom:2px}
.card-lbl{font-size:0.62rem;color:#666;min-width:34px;flex-shrink:0;line-height:1.6}
.card-val{font-size:0.65rem;font-weight:600;flex:1;line-height:1.6;word-break:break-word}

/* Donor badge */
.donor-tag{display:inline-block;background:#1a4731;color:#fff;font-size:0.46rem;padding:1px 5px;border-radius:3px;letter-spacing:0.5px;vertical-align:middle}

/* ID strip at bottom */
.card-id-strip{
  position:absolute;bottom:0;left:0;right:0;
  background:#1a4731;color:#fff;
  text-align:center;padding:4px;
  font-size:0.82rem;font-weight:700;letter-spacing:3px;border-radius:0 0 8px 8px;
}

/* BACK CARD */
.card-back .back-hdr{text-align:center;border-bottom:1px solid #ddd;padding-bottom:5px;margin-bottom:5px;padding-top:2px}
.card-back .back-hdr h4{font-size:0.68rem;font-weight:700;color:#111}
.card-back .back-hdr small{font-size:0.5rem;color:#666}
.back-rules{font-size:0.54rem;line-height:1.6;color:#222;padding-bottom:22px}
.back-rules ul{padding-left:11px}
.back-rules li{margin-bottom:1px}
.back-rules strong{color:#111}
.back-footer{position:absolute;bottom:0;left:0;right:0;background:#f5f5f5;border-top:1px solid #ddd;border-radius:0 0 8px 8px;display:flex;justify-content:space-between;padding:3px 10px;font-size:0.48rem;color:#666}

/* Print */
@media print{
  html,body{background:#fff!important;padding:0!important;margin:0!important}
  .no-print{display:none!important}
  .card-set{gap:0}
  .card-label{display:none}
  /* Gap between pages */
  .card-wrap{page-break-after:always;padding-bottom:0;margin-bottom:0}
  .card-wrap:last-child{page-break-after:avoid}
  .id-card{
    width:288px!important;height:192px!important;
    box-shadow:none!important;
    border:1.5px solid #222!important;
    -webkit-print-color-adjust:exact;print-color-adjust:exact;
  }
  /* Light gap between front and back */
  @page{margin:10mm}
}
