@verbatim
<style>
    :root {
        --ink: #1f2937; --muted: #6b7280; --line: #e5e7eb; --bg: #f3f4f6; --card: #ffffff;
        --sky: #e0f2fe; --sky-d: #0369a1; --green: #dcfce7; --green-d: #166534; --red: #fee2e2; --red-d: #991b1b;
        --amber: #fef3c7; --amber-d: #92400e; --violet: #7c3aed; --gray: #e5e7eb;
    }
    * { box-sizing: border-box; }
    body { margin: 0; background: var(--bg); color: var(--ink); font-family: "Segoe UI", Tahoma, "Noto Naskh Arabic", Arial, sans-serif; line-height: 1.85; font-size: 16px; }
    .wrap { max-width: 1040px; margin: 0 auto; padding: 24px 16px 64px; }
    header.hero { background: var(--sky); border: 1px solid #bae6fd; border-radius: 16px; padding: 28px; }
    header.hero h1 { margin: 0 0 6px; font-size: 28px; color: #0c4a6e; }
    header.hero p { margin: 0; color: #075985; }
    nav.toc { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0 24px; }
    nav.toc a { background: var(--card); border: 1px solid var(--line); border-radius: 999px; padding: 4px 14px; color: var(--sky-d); text-decoration: none; font-size: 14px; }
    nav.toc a:hover { background: var(--sky); }
    section { background: var(--card); border: 1px solid var(--line); border-radius: 16px; padding: 24px; margin-bottom: 20px; }
    h2 { margin: 0 0 14px; font-size: 22px; display: flex; align-items: center; gap: 10px; }
    h2 .num { background: var(--sky-d); color: #fff; width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; flex: none; }
    h3 { margin: 22px 0 8px; font-size: 17px; }
    p { margin: 8px 0; }
    .note, .tip, .warn, .danger { border-radius: 12px; padding: 12px 16px; margin: 14px 0; display: flex; gap: 12px; align-items: flex-start; }
    .note { background: var(--sky); border: 1px solid #bae6fd; }
    .tip { background: var(--green); border: 1px solid #bbf7d0; }
    .warn { background: var(--amber); border: 1px solid #fde68a; }
    .danger { background: var(--red); border: 1px solid #fecaca; }
    .ico { font-size: 20px; line-height: 1.6; flex: none; }
    table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 15px; }
    th, td { border: 1px solid var(--line); padding: 8px 10px; text-align: right; vertical-align: top; }
    th { background: #f9fafb; }
    .center td, .center th { text-align: center; }
    .ok { color: var(--green-d); font-weight: 700; }
    .no { color: var(--red-d); font-weight: 700; }
    .tag { display: inline-block; border-radius: 999px; padding: 0 10px; font-size: 13px; font-weight: 700; }
    .tag.g { background: var(--green); color: var(--green-d); } .tag.r { background: var(--red); color: var(--red-d); }
    .tag.a { background: var(--amber); color: var(--amber-d); } .tag.s { background: var(--sky); color: var(--sky-d); }
    .tag.n { background: var(--gray); color: #374151; }
    .mark { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #dc2626; color: #fff; font-size: 13px; font-weight: 700; flex: none; }

    /* mock of the real page */
    .mock { background: #f9fafb; border: 1px solid var(--line); border-radius: 14px; padding: 16px; margin: 14px 0; }
    .mock .panel { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 14px; }
    .mock .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .mock .grid .full { grid-column: 1 / -1; }
    .fld { position: relative; }
    .fld label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 2px; }
    .fld .box { border: 1px solid #d1d5db; border-radius: 8px; padding: 6px 10px; background: #fff; font-size: 14px; min-height: 36px; color: #374151; }
    .fld .box.empty { color: #9ca3af; }
    .fld .mark { position: absolute; top: -8px; left: -8px; box-shadow: 0 0 0 3px #fff; }
    .btns { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 12px; position: relative; }
    .btn { border-radius: 8px; padding: 6px 14px; font-weight: 700; font-size: 14px; color: #fff; position: relative; }
    .btn.violet { background: var(--violet); } .btn.green { background: #16a34a; }
    .badge { background: var(--amber); color: var(--amber-d); border: 1px solid #fde68a; border-radius: 8px; padding: 2px 10px; font-size: 13px; margin-inline-start: auto; position: relative; }
    .btn .mark, .badge .mark { position: absolute; top: -10px; left: -10px; box-shadow: 0 0 0 3px #fff; }
    .tbl { margin-top: 14px; background: #fff; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
    .tbl .row { display: grid; grid-template-columns: 1.1fr 1.1fr 2.2fr 1fr 1fr 1fr; gap: 0; border-bottom: 1px solid var(--line); }
    .tbl .row > div { padding: 8px 10px; font-size: 14px; position: relative; }
    .tbl .row.head > div { font-weight: 700; background: #f9fafb; }
    .tbl input { width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 3px 8px; background: #fff; font: inherit; }
    .toast { margin-top: 14px; display: inline-block; background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 8px 14px; box-shadow: 0 4px 12px rgba(0,0,0,.08); position: relative; }
    .legend { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 8px 20px; margin-top: 14px; }
    .legend div { display: flex; gap: 10px; align-items: flex-start; }

    /* steps */
    .steps { counter-reset: s; display: grid; gap: 12px; margin-top: 10px; }
    .step { display: flex; gap: 14px; background: #f9fafb; border: 1px solid var(--line); border-radius: 12px; padding: 12px 14px; }
    .step::before { counter-increment: s; content: counter(s); background: var(--sky-d); color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex: none; }
    .step b { display: block; }
    .step span { color: var(--muted); font-size: 14px; }

    /* flow */
    .flow { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin: 12px 0; }
    .flow .n { background: #fff; border: 2px solid var(--sky-d); border-radius: 10px; padding: 6px 12px; font-weight: 700; font-size: 14px; }
    .flow .n.opt { border-style: dashed; border-color: #9ca3af; color: #6b7280; }
    .flow .ar { color: var(--muted); font-size: 20px; }

    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-top: 12px; }
    .card { border: 1px solid var(--line); border-radius: 12px; padding: 14px; background: #fff; }
    .card h4 { margin: 0 0 6px; font-size: 16px; }
    .calc { background: #f9fafb; border: 1px dashed #9ca3af; border-radius: 12px; padding: 16px; margin-top: 12px; }
    .calc input, .calc select { font: inherit; padding: 5px 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; }
    .calc .out { margin-top: 12px; font-size: 18px; font-weight: 700; }
    footer { text-align: center; color: var(--muted); font-size: 13px; margin-top: 28px; }
    @media (max-width: 720px) { .mock .grid { grid-template-columns: 1fr; } .tbl .row { grid-template-columns: 1fr 1fr; } }
    @media print { body { background: #fff; } section { break-inside: avoid; box-shadow: none; } nav.toc { display: none; } }
</style>
@endverbatim
