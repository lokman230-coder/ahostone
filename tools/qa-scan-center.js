#!/usr/bin/env node
/** Ahost One QA & Scan Center Pro CLI
 * Playwright ile masaüstü/mobil ekran görüntüsü, console/network hata kaydı ve HTML/JSON rapor üretir.
 */
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const { chromium } = require('@playwright/test');
const args = Object.fromEntries(process.argv.slice(2).map(a => a.startsWith('--') ? a.slice(2).split('=') : [a,true]));
const base = (args.base || process.env.AHOST_BASE_URL || 'http://localhost').replace(/\/$/, '');
const stamp = new Date().toISOString().replace(/[-:T]/g,'').slice(0,14);
const out = path.join(process.cwd(), 'storage', 'reports', 'qa-scans', stamp);
fs.mkdirSync(path.join(out,'desktop'), {recursive:true});
fs.mkdirSync(path.join(out,'mobile'), {recursive:true});
fs.mkdirSync(path.join(out,'logs'), {recursive:true});
const routes = [
  ['Site','', 'Ana Sayfa'], ['Site','products','Ürünler'], ['Site','domain','Domain'], ['Site','domain-checker','Domain Sorgulama'], ['Site','cart','Sepet'], ['Site','blog','Blog'], ['Site','knowledgebase','Bilgi Bankası'], ['Site','announcements','Duyurular'], ['Site','references','Referanslar'], ['Site','quotation','Teklif'],
  ['Admin','admin/dashboard','Admin Dashboard'], ['Admin','admin/settings','Ayarlar Merkezi'], ['Admin','admin/api-integrations','API Entegrasyonları'], ['Admin','admin/domain-center','Domain Center'], ['Admin','admin/hosting-server','Hosting & Sunucu'], ['Admin','admin/automation','Otomasyonlar'], ['Admin','admin/build-center','Build Center'], ['Admin','admin/help-center','Yardım Merkezi'], ['Admin','admin/update-center','Güncelleme Merkezi'], ['Admin','admin/qa-scan-center','QA & Scan Center'],
  ['Müşteri','client/login','Müşteri Girişi'], ['Müşteri','client/dashboard','Müşteri Dashboard'], ['Müşteri','client/services','Hizmetler'], ['Müşteri','client/domains','Domainler'], ['Müşteri','client/tickets','Destek Talepleri'], ['Müşteri','client/billing','Faturalar']
];
function safe(route){ return (route || 'home').replace(/[^a-z0-9_-]+/gi,'-').replace(/^-|-$/g,'') || 'home'; }
async function scanPage(browser, area, route, label, device){
  const viewport = device === 'mobile' ? {width:390,height:844} : {width:1440,height:1100};
  const page = await browser.newPage({viewport, isMobile: device==='mobile'});
  const consoleErrors=[]; const failed=[];
  page.on('console', msg => { if(['error','warning'].includes(msg.type())) consoleErrors.push(msg.text()); });
  page.on('requestfailed', req => failed.push(req.url()));
  const url = route ? `${base}/${route}` : base;
  let status='ERR', ms=0;
  const screenshot = `${device}/${safe(route)}.png`;
  try { const start=Date.now(); const res = await page.goto(url, {waitUntil:'networkidle', timeout:45000}); ms=Date.now()-start; status = res ? res.status() : 'NO_RESPONSE'; await page.screenshot({path:path.join(out,screenshot), fullPage:true}); }
  catch(e){ consoleErrors.push(e.message); }
  await page.close();
  return {area,route,label,device,url,status,ms,consoleErrors,failed,screenshot};
}
(async()=>{
  const browser = await chromium.launch({headless:true});
  const raw=[];
  for(const [area,route,label] of routes){ raw.push(await scanPage(browser, area, route, label, 'desktop')); raw.push(await scanPage(browser, area, route, label, 'mobile')); }
  await browser.close();
  const pages = routes.map(([area,route,label])=>{
    const d=raw.find(r=>r.route===route&&r.device==='desktop'); const m=raw.find(r=>r.route===route&&r.device==='mobile');
    const errors=[...(d.consoleErrors||[]),...(m.consoleErrors||[]),...(d.failed||[]),...(m.failed||[])];
    const badStatus = [d.status,m.status].some(s=>Number(s)>=400 || s==='ERR');
    const score = Math.max(40, 100 - errors.length*8 - (badStatus?20:0));
    return {area,path:route,label,url:route ? `${base}/${route}` : base,slug:safe(route),desktop:d.screenshot,mobile:m.screenshot,status:badStatus?'error':(errors.length?'warning':'pass'),score,notes:errors.slice(0,3).join(' | ') || 'OK'};
  });
  const summary = {id:stamp,base_url:base,generated_at:new Date().toISOString(),score:Math.round(pages.reduce((a,b)=>a+b.score,0)/pages.length),visual_pages:pages.length,desktop_screenshots:pages.length,mobile_screenshots:pages.length,pass:pages.filter(p=>p.status==='pass').length,warning:pages.filter(p=>p.status==='warning').length,error:pages.filter(p=>p.status==='error').length,broken_links:pages.filter(p=>p.status==='error').length,js_errors:raw.reduce((a,b)=>a+b.consoleErrors.length,0),duration:'CLI',routes:pages,system_rows:[]};
  fs.writeFileSync(path.join(out,'summary.json'), JSON.stringify(summary,null,2));
  fs.writeFileSync(path.join(out,'logs','console.log'), raw.map(r=>`${r.device} ${r.url}\n${r.consoleErrors.join('\n')}`).join('\n\n'));
  fs.writeFileSync(path.join(out,'logs','network.json'), JSON.stringify(raw.map(r=>({url:r.url,device:r.device,status:r.status,failed:r.failed})),null,2));
  const rows = pages.map(p=>`<tr><td>${p.area}</td><td><b>${p.label}</b><br><code>${p.url}</code></td><td>${p.status}</td><td>${p.score}/100</td><td><img src="${p.desktop}"></td><td><img src="${p.mobile}"></td><td>${p.notes}</td></tr>`).join('');
  const html = `<!doctype html><html lang="tr"><head><meta charset="utf-8"><title>Ahost One QA & Scan Center Pro</title><style>body{font-family:Arial;background:#f5f7fb;color:#0f172a;padding:24px}.hero{background:linear-gradient(135deg,#0f172a,#1d4ed8);color:white;border-radius:24px;padding:24px}.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:18px 0}.card{background:white;border:1px solid #e5e7eb;border-radius:18px;padding:16px}table{width:100%;border-collapse:collapse;background:white;border-radius:18px;overflow:hidden}th,td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}img{width:220px;border:1px solid #e5e7eb;border-radius:10px}code{white-space:normal}</style></head><body><div class="hero"><h1>QA & Scan Center Pro</h1><p>${base} — ${stamp}</p></div><div class="grid"><div class="card">Skor<br><b>${summary.score}/100</b></div><div class="card">PASS<br><b>${summary.pass}</b></div><div class="card">Warning<br><b>${summary.warning}</b></div><div class="card">Error<br><b>${summary.error}</b></div></div><table><thead><tr><th>Alan</th><th>Sayfa</th><th>Durum</th><th>Skor</th><th>Masaüstü</th><th>Mobil</th><th>Not</th></tr></thead><tbody>${rows}</tbody></table></body></html>`;
  fs.writeFileSync(path.join(out,'report.html'), html);
  try { execFileSync('zip', ['-qr','qa-scan-package.zip','report.html','summary.json','desktop','mobile','logs'], {cwd:out}); } catch(e) {}
  console.log('QA & Scan report created:', out);
})();
