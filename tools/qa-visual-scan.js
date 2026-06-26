#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const { chromium } = require('@playwright/test');
const args = Object.fromEntries(process.argv.slice(2).map(a => a.startsWith('--') ? a.slice(2).split('=') : [a,true]));
const base = (args.base || process.env.AHOST_BASE_URL || 'http://localhost').replace(/\/$/, '');
const stamp = new Date().toISOString().replace(/[-:T]/g,'').slice(0,14);
const out = path.join(process.cwd(), 'storage', 'reports', 'qa-scans', stamp);
fs.mkdirSync(path.join(out,'screenshots','desktop'), {recursive:true});
fs.mkdirSync(path.join(out,'screenshots','mobile'), {recursive:true});
const routes = [
  ['', 'Site Ana Sayfa'], ['products','Ürünler'], ['domain','Domain'], ['cart','Sepet'], ['blog','Blog'], ['knowledgebase','Bilgi Bankası'], ['references','Referanslar'], ['quotation','Teklif'],
  ['admin/dashboard','Admin Dashboard'], ['admin/settings','Ayarlar Merkezi'], ['admin/api-integrations','API Entegrasyonları'], ['admin/domain-center','Domain Center'], ['admin/hosting-server','Sunucular'], ['admin/automation','Otomasyonlar'], ['admin/build-center','Build Center'], ['admin/build-center/logs','Build Logları'], ['admin/build-center/sdk-tools','SDK & Araçlar'], ['admin/help-center','Yardım Merkezi'],
  ['client/login','Müşteri Girişi'], ['client/dashboard','Müşteri Dashboard'], ['client/services','Hizmetler'], ['client/domains','Domainler'], ['client/tickets','Destek']
];
async function scanPage(browser, route, label, device){
  const viewport = device === 'mobile' ? {width:390,height:844} : {width:1440,height:1100};
  const page = await browser.newPage({viewport, isMobile: device==='mobile'});
  const consoleErrors=[]; const failed=[];
  page.on('console', msg => { if(['error','warning'].includes(msg.type())) consoleErrors.push(msg.text()); });
  page.on('requestfailed', req => failed.push(req.url()));
  const url = `${base}/${route}`.replace(/\/$/,'') || base;
  const safe = (route || 'home').replace(/[^a-z0-9_-]+/gi,'-').replace(/^-|-$/g,'') || 'home';
  let status='ERR', ms=0;
  try { const start=Date.now(); const res = await page.goto(url, {waitUntil:'networkidle', timeout:45000}); ms=Date.now()-start; status = res ? res.status() : 'NO_RESPONSE'; await page.screenshot({path:path.join(out,'screenshots',device,`${safe}.png`), fullPage:true}); }
  catch(e){ consoleErrors.push(e.message); }
  await page.close();
  return {route,label,device,url,status,ms,consoleErrors,failed,screenshot:`screenshots/${device}/${safe}.png`};
}
(async()=>{
  const browser = await chromium.launch({headless:true});
  const results=[];
  for(const [route,label] of routes){ results.push(await scanPage(browser, route, label, 'desktop')); results.push(await scanPage(browser, route, label, 'mobile')); }
  await browser.close();
  fs.writeFileSync(path.join(out,'results.json'), JSON.stringify(results,null,2));
  const rows = routes.map(([route,label])=>{
    const d=results.find(r=>r.route===route&&r.device==='desktop'); const m=results.find(r=>r.route===route&&r.device==='mobile');
    const warn=[...(d.consoleErrors||[]),...(m.consoleErrors||[]),...(d.failed||[]),...(m.failed||[])].slice(0,4).join('<br>') || '-';
    return `<tr><td>${label}</td><td><code>${base}/${route}</code></td><td>${d.status}<br>${d.ms}ms<br><img src="${d.screenshot}"></td><td>${m.status}<br>${m.ms}ms<br><img src="${m.screenshot}"></td><td>${warn}</td></tr>`;
  }).join('');
  const html = `<!doctype html><html lang="tr"><head><meta charset="utf-8"><title>Ahost One QA Görsel Rapor</title><style>body{font-family:Arial;background:#f8fafc;color:#0f172a;padding:24px}h1{margin:0 0 6px}.card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;padding:22px;box-shadow:0 18px 50px rgba(15,23,42,.08)}table{width:100%;border-collapse:collapse;margin-top:18px}th,td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}th{color:#64748b;font-size:12px;text-transform:uppercase}img{width:240px;max-height:340px;object-fit:cover;border:1px solid #e5e7eb;border-radius:10px;margin-top:6px}code{white-space:normal}</style></head><body><div class="card"><h1>Ahost One QA Görsel Rapor</h1><p>Base: ${base} — ${stamp}</p><table><thead><tr><th>Sayfa</th><th>URL</th><th>Masaüstü</th><th>Mobil</th><th>Uyarılar</th></tr></thead><tbody>${rows}</tbody></table></div></body></html>`;
  fs.writeFileSync(path.join(out,'report.html'), html);
  console.log('QA visual report created:', out);
})();
