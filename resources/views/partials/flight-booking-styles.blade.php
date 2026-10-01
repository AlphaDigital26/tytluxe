<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --gold-dim: rgba(201,168,76,0.18);
    --dark: #0d0d0d; --dark-2: #141414;
    --white-80: rgba(255,255,255,0.80); --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30);
  }
  body { background: var(--dark); }

  .flr-wrap { max-width: 1100px; margin: 0 auto; padding: 0 24px 100px; display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 40px; align-items: start; }
  /* minmax(0, 1fr), not 1fr: a plain 1fr column grows to its widest child
     (e.g. the fare-rules table), pushing the card off-screen on phones
     instead of letting that child scroll inside it. */
  @media (max-width: 900px) { .flr-wrap { grid-template-columns: minmax(0, 1fr); gap: 28px; } .flr-summary { position: static; } }

  .flr-section { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 32px 34px; margin-bottom: 26px; }
  .flr-section h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: var(--gold); margin-bottom: 24px; }
  @media (max-width: 560px) { .flr-section { padding: 24px 18px; } }

  .flr-field { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
  .flr-field label { font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold); }
  .flr-field input, .flr-field select, .flr-field textarea { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; padding: 14px 16px; color: #fff; font-family: 'Jost', sans-serif; font-size: 13.5px; outline: none; width: 100%; box-sizing: border-box; transition: border-color 0.15s; }
  .flr-field input:focus, .flr-field select:focus, .flr-field textarea:focus { border-color: rgba(201,168,76,0.6); }
  .flr-field select option { background: var(--dark-2); }
  .flr-row { display: grid; grid-template-columns: 110px 1fr 1fr; gap: 14px; }
  @media (max-width: 560px) { .flr-row { grid-template-columns: 1fr; } }

  .flr-summary { position: sticky; top: 100px; background: var(--dark-2); border: 1px solid rgba(201,168,76,0.25); border-radius: 22px; padding: 30px; }
  .flr-summary h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; color: #fff; margin-bottom: 14px; }
  .flr-line { display: flex; justify-content: space-between; gap: 10px; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); padding: 8px 0; }
  .flr-line.total { border-top: 1px solid rgba(255,255,255,0.12); margin-top: 6px; padding-top: 14px; font-weight: 700; font-size: 16px; color: #fff; }
  .flr-note { font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-30); margin-top: 14px; line-height: 1.6; }
  .flr-sum-pax { font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-bottom: 6px; }
  .flr-sum-row { display: flex; justify-content: space-between; gap: 10px; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.07); font-family: 'Jost', sans-serif; font-size: 13.5px; color: var(--white-80); }
  .flr-sum-row span:last-child { color: #fff; font-weight: 600; }
  .flr-sum-toggle { background: none; border: none; padding: 0; color: inherit; font: inherit; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
  .flr-sum-toggle::after { content: '▾'; font-size: 10px; color: var(--gold); transition: transform 0.15s; }
  .flr-sum-toggle[aria-expanded="true"]::after { transform: rotate(180deg); }
  .flr-sum-sub { padding: 4px 0 8px 12px; border-bottom: 1px solid rgba(255,255,255,0.07); }
  .flr-sum-sub[hidden] { display: none; }
  .flr-sum-sub div { display: flex; justify-content: space-between; padding: 4px 0; font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); }

  .flr-submit { display: block; width: 100%; padding: 17px; border: none; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; text-align: center; text-decoration: none; cursor: pointer; margin-top: 12px; box-sizing: border-box; }
  .flr-submit.outline { background: transparent; border: 1px solid rgba(201,168,76,0.4); color: var(--gold-light); }
  .flr-submit:disabled { opacity: 0.4; cursor: not-allowed; }
  .flr-error { margin-bottom: 26px; padding: 15px 20px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: #f3a3a3; font-family: 'Jost', sans-serif; font-size: 13.5px; display: flex; flex-direction: column; gap: 6px; }

  /* Step bar */
  .flr-steps { max-width: 1100px; margin: 0 auto; padding: 108px 24px 30px; display: flex; align-items: center; gap: 14px; font-family: 'Jost', sans-serif; box-sizing: border-box; }
  .flr-step { display: flex; align-items: center; gap: 12px; flex-shrink: 0; text-decoration: none; }
  .flr-step-dot { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.15); color: var(--white-30); font-size: 13px; font-weight: 700; }
  .flr-step.active .flr-step-dot { background: linear-gradient(135deg, #c9a84c, #e8c96b); border-color: transparent; color: var(--dark); }
  .flr-step.done .flr-step-dot { border-color: var(--gold); color: var(--gold); }
  .flr-step-k { font-size: 9.5px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--white-30); }
  .flr-step-v { font-size: 13px; font-weight: 600; color: var(--white-60); }
  .flr-step.active .flr-step-k, .flr-step.done .flr-step-k { color: var(--gold); }
  .flr-step.active .flr-step-v { color: #fff; }
  .flr-step.done .flr-step-v { color: var(--gold-light); }
  .flr-step-line { flex: 1; height: 1px; background: rgba(255,255,255,0.1); min-width: 16px; }
  .flr-step-line.done { background: rgba(201,168,76,0.55); }
  @media (max-width: 760px) { .flr-step-text { display: none; } .flr-step.active .flr-step-text { display: block; } }
</style>
