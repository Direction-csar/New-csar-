<style>
    .an-header { background: linear-gradient(135deg, #5a9e3d, #7cb342); border-radius: 12px; padding: 18px 24px; color: #fff; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .an-header h2 { margin: 0; font-weight: 700; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; }
    .an-header .an-sub { font-size: .85rem; opacity: .9; margin-top: 4px; }
    .an-back { background: rgba(255,255,255,.2); color: #fff; border: none; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: .85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .an-back:hover { background: #fff; color: #5a9e3d; }

    .an-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.05); padding: 16px; }
    .an-card + .an-card { margin-top: 16px; }
    .an-card-title { font-size: .9rem; font-weight: 700; color: #1e293b; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #f1f5f9; display: flex; align-items: center; gap: 8px; }
    .an-card-title i { color: #f59e0b; }

    .an-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .an-kpi { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 14px; text-decoration: none; color: inherit; display: block; transition: transform .15s, box-shadow .15s; }
    a.an-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.08); color: inherit; }
    .an-kpi .v { font-size: 1.6rem; font-weight: 800; color: #1e293b; line-height: 1.1; }
    .an-kpi .l { font-size: .75rem; color: #64748b; font-weight: 600; margin-top: 2px; }

    .an-filters { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
    .an-filters .form-group { display: flex; flex-direction: column; gap: 4px; min-width: 150px; flex: 1 1 150px; }
    .an-filters label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; font-weight: 700; }
    .an-filters .form-control, .an-filters .form-select { font-size: .85rem; border-radius: 8px; }
    .an-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .an-btn { border-radius: 8px; font-size: .82rem; font-weight: 600; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
    .an-btn-primary { background: #5a9e3d; color: #fff; }
    .an-btn-primary:hover { background: #4a8530; color: #fff; }
    .an-btn-light { background: #f3f4f6; color: #374151; border-color: #e5e7eb; }
    .an-btn-light:hover { background: #e5e7eb; color: #111827; }

    .an-count { font-size: .9rem; color: #374151; }
    .an-count strong { font-size: 1.3rem; color: #5a9e3d; }

    .an-table-wrap { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
    .an-table thead th { background: #f8fafc; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; white-space: nowrap; border-bottom: 2px solid #e5e7eb; }
    .an-table tbody td { font-size: .85rem; vertical-align: middle; }
    .an-sort { color: inherit; text-decoration: none; display: inline-flex; gap: 6px; align-items: center; }
    .an-sort i { opacity: .4; }
    .an-sort.active, .an-sort.active i { color: #5a9e3d; opacity: 1; }
    .an-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb; display: inline-flex; align-items: center; justify-content: center; background: #f1f5f9; color: #94a3b8; }
    .an-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
    .an-pill-f { background: #fce7f3; color: #be185d; }
    .an-pill-m { background: #e0f2fe; color: #0369a1; }
    .an-pill-nr { background: #f3f4f6; color: #6b7280; }
    .an-pill-contrat { background: #ede9fe; color: #6d28d9; }
    .an-pill-danger { background: #fee2e2; color: #b91c1c; }
    .an-pill-warn { background: #fef3c7; color: #b45309; }
    .an-pill-ok { background: #dcfce7; color: #15803d; }
    .an-empty { text-align: center; padding: 40px 10px; color: #9ca3af; }
    .an-empty i { font-size: 2rem; margin-bottom: 10px; display: block; }

    .an-notice { border-left: 4px solid #f59e0b; background: #fffbeb; color: #78350f; border-radius: 8px; padding: 12px 14px; font-size: .85rem; }
    .an-notice.info { border-color: #3b82f6; background: #eff6ff; color: #1e3a8a; }
    .an-notice ul { margin: 6px 0 0; padding-left: 18px; }

    .an-bars { display: flex; flex-direction: column; gap: 8px; }
    .an-bar { display: grid; grid-template-columns: 170px 1fr 40px; align-items: center; gap: 10px; font-size: .8rem; text-decoration: none; color: #374151; }
    .an-bar:hover { color: #5a9e3d; }
    .an-bar .track { height: 10px; background: #f1f5f9; border-radius: 999px; overflow: hidden; display: flex; }
    .an-bar .seg-f { background: #ec407a; height: 100%; }
    .an-bar .seg-m { background: #0d9488; height: 100%; }
    .an-bar .seg-nr { background: #cbd5e1; height: 100%; }
    .an-bar .n { font-weight: 700; text-align: right; }
    .an-legend { display: flex; gap: 14px; font-size: .75rem; color: #64748b; margin-top: 8px; }
    .an-legend span::before { content: ''; display: inline-block; width: 10px; height: 10px; border-radius: 2px; margin-right: 5px; vertical-align: -1px; }
    .an-legend .lf::before { background: #ec407a; } .an-legend .lm::before { background: #0d9488; } .an-legend .lnr::before { background: #cbd5e1; }

    .an-chip { display: inline-block; background: #f3f4f6; color: #374151; padding: 4px 12px; border-radius: 20px; font-size: .78rem; font-weight: 500; margin: 2px; text-decoration: none; }
    .an-chip:hover { background: #5a9e3d; color: #fff; }
    .an-chip b { color: #5a9e3d; } .an-chip:hover b { color: #fff; }

    @media (max-width: 768px) {
        .an-table thead { display: none; }
        .an-table tbody tr { display: block; border-bottom: 8px solid #f1f5f9; }
        .an-table tbody td { display: flex; justify-content: space-between; gap: 12px; border: none; padding: 6px 12px; }
        .an-table tbody td::before { content: attr(data-label); font-size: .7rem; text-transform: uppercase; color: #64748b; font-weight: 700; }
        .an-table tbody td:first-child, .an-table tbody td:last-child { display: none; }
        .an-bar { grid-template-columns: 1fr 40px; }
        .an-bar .track { grid-column: 1 / -1; }
    }
</style>
