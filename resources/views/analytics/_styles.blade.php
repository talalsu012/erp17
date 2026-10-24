{{-- أنماط اللوحة معزولة داخل .an-dash حتى لا تتعارض مع تنسيقات Filament --}}
<style>
.an-dash {
    --ink:#0f172a; --muted:#64748b; --line:#e2e8f0; --bg:#f1f5f9; --card:#fff; --teal:#0d9488; --blue:#3b4fd4; --amber:#d97706; --red:#dc2626; --violet:#7c3aed;
    color:var(--ink); font-size:14px; line-height:1.55;
    & *, & *::before, & *::after { box-sizing: border-box; }
    .num { font-family: ui-monospace, "Cascadia Mono", Consolas, monospace; font-variant-numeric: tabular-nums; }
    .top { display:flex; flex-wrap:wrap; gap:12px; align-items:center; background:var(--card); border:1px solid var(--line); border-radius:12px; padding:10px 16px; }
    .brand b { font-size:18px; display:block; }
    .brand small { color:var(--muted); }
    .actions { margin-inline-start:auto; display:flex; gap:12px; align-items:center; }
    .link { color:#0369a1; text-decoration:none; font-weight:600; font-size:13px; }
    details.filters { margin:12px 0; background:var(--card); border:1px solid var(--line); border-radius:12px; }
    details.filters summary { cursor:pointer; list-style:none; padding:8px 16px; color:var(--muted); font-weight:700; font-size:13px; }
    details.filters summary::-webkit-details-marker { display:none; }
    details.filters form { display:flex; flex-wrap:wrap; gap:10px; align-items:end; padding:4px 14px 12px; }
    .filters label { display:flex; flex-direction:column; font-size:12px; color:var(--muted); gap:2px; }
    .filters select { border:1px solid #cbd5e1; border-radius:8px; padding:5px 8px; background:#fff; min-width:140px; font:inherit; color:var(--ink); }
    .btn { border:0; background:#0f172a; color:#fff; border-radius:8px; padding:7px 18px; cursor:pointer; font:inherit; font-weight:700; }
    .ghost { color:var(--muted); text-decoration:none; font-size:13px; padding:7px 4px; }
    .kpis { display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin:12px 0; }
    .kpi { background:var(--card); border:1px solid var(--line); border-bottom:4px solid var(--c); border-radius:12px; padding:12px 14px; position:relative; }
    .kpi.has-ring { padding-inline-start: 62px; }
    .kpi .l { font-size:11px; letter-spacing:.04em; color:var(--muted); font-weight:700; }
    .kpi .v { font-size:28px; font-weight:800; line-height:1.2; }
    .kpi .v small { font-size:15px; color:var(--muted); font-weight:700; }
    .kpi .s { font-size:12px; color:var(--muted); }
    .ring { position:absolute; inset-inline-start:12px; top:50%; transform:translateY(-50%); width:40px; height:40px; border-radius:50%; background:conic-gradient(var(--c) calc(var(--p) * 1%), #e2e8f0 0); }
    .ring::after { content:""; position:absolute; inset:7px; background:#fff; border-radius:50%; }
    .board { display:grid; grid-template-columns:repeat(12,1fr); gap:12px; }
    .card { background:var(--card); border:1px solid var(--line); border-radius:12px; padding:14px 16px; min-width:0; }
    .card h3 { margin:0 0 10px; font-size:14px; font-weight:700; display:flex; justify-content:space-between; align-items:center; gap:8px; }
    .pill { background:#e0f2fe; color:#0369a1; border-radius:999px; font-size:11px; font-weight:700; padding:1px 10px; }
    .s4 { grid-column: span 4; } .s6 { grid-column: span 6; } .s12 { grid-column: span 12; }
    .row { display:grid; grid-template-columns: minmax(70px,1.1fr) 2fr 62px; gap:10px; align-items:center; padding:5px 0; font-size:13px; }
    .row .nm { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .row .pc { text-align:left; font-weight:700; }
    .bar { display:block; height:9px; background:#eef2f7; border-radius:99px; overflow:hidden; }
    .bar i { display:block; height:100%; border-radius:99px; }
    .hist { display:flex; align-items:flex-end; gap:6px; height:140px; }
    .hist div { flex:1; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; height:100%; }
    .hist i { width:100%; border-radius:5px 5px 0 0; min-height:2px; }
    .hist b { font-size:11px; }
    .hist span { font-size:10px; color:var(--muted); margin-top:4px; text-align:center; }
    .scroll { overflow-x:auto; }
    & table { width:100%; border-collapse:collapse; font-size:13px; }
    & th { text-align:right; color:var(--muted); font-size:11px; font-weight:700; padding:6px 6px; border-bottom:1px solid var(--line); white-space:nowrap; }
    & td { padding:6px 6px; border-bottom:1px solid #f1f5f9; white-space:nowrap; }
    & td.n, & th.n { text-align:left; }
    & tr.total td { font-weight:800; background:#f8fafc; border-top:2px solid var(--line); }
    .person { display:flex; justify-content:space-between; align-items:center; padding:7px 10px; border-radius:8px; background:#f8fafc; margin-bottom:6px; gap:8px; }
    .person small { color:var(--muted); display:block; }
    .rank { width:22px; height:22px; border-radius:50%; background:#0f172a; color:#fff; font-size:12px; display:inline-flex; align-items:center; justify-content:center; margin-inline-end:8px; flex:none; }
    .money { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:12px; }
    .money div { background:#f8fafc; border-radius:10px; padding:8px 10px; }
    .money small { color:var(--muted); display:block; font-size:11px; }
    .money b { font-size:18px; }
    .collect { display:flex; gap:16px; align-items:center; margin-bottom:12px; }
    .collect .big { width:92px; height:92px; border-radius:50%; flex:none; position:relative; background:conic-gradient(var(--c) calc(var(--p) * 1%), #e2e8f0 0); }
    .collect .big::after { content:""; position:absolute; inset:11px; background:#fff; border-radius:50%; }
    .collect .big b { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:20px; z-index:1; }
    .collect .money { flex:1; margin:0; }
    .empty { color:var(--muted); padding:18px 0; text-align:center; }
    .foot { text-align:center; color:var(--muted); font-size:12px; margin-top:14px; }
    @media (max-width: 1100px) { .kpis { grid-template-columns:repeat(3,1fr); } .s4, .s6 { grid-column: span 12; } }
    @media (max-width: 640px) { .kpis { grid-template-columns:repeat(2,1fr); } }
    @media print { .filters, .link { display:none; } .card, .kpi { break-inside: avoid; } }
}
</style>
