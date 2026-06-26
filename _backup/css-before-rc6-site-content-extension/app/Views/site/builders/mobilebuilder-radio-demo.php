<section class="builder-public-page">
    <div class="builder-shell">
        <div class="builder-head">
            <span class="builder-badge">🎵 Radyo Uygulaması</span>
            <h1>Radyo Uygulaması Oluştur</h1>
            <p>Canlı radyo dinleme, program rehberi, podcast ve daha fazlası ile profesyonel radyo uygulamanızı oluşturun.</p>
        </div>
        
        <!-- Radio App Preview -->
        <div class="radio-app-builder">
            <div class="radio-phone-frame">
                <div class="radio-phone-mock">
                    <div class="radio-status-bar">
                        <span>9:41</span>
                        <span>📶 📡 🔋</span>
                    </div>
                    
                    <!-- Splash Screen Preview -->
                    <div class="radio-splash-screen" id="splashPreview">
                        <div class="radio-splash-logo" id="radioLogoPreview">
                            <span style="font-size: 48px;">📻</span>
                        </div>
                        <h3 id="splashAppName">Radyo Uygulaması</h3>
                        <div class="radio-splash-loader"></div>
                    </div>
                    
                    <!-- Main App Screen -->
                    <div class="radio-main-screen" id="radioAppPreview" style="display: none;">
                        <!-- Header -->
                        <div class="radio-header">
                            <div class="radio-logo-small">
                                <span id="headerLogoPreview" style="font-size: 24px;">📻</span>
                            </div>
                            <h4 id="headerAppName">Radyo Uygulaması</h4>
                            <div class="radio-header-actions">
                                <span id="whatsappHeaderBtn" style="cursor:pointer;">💬</span>
                            </div>
                        </div>
                        
                        <!-- Now Playing -->
                        <div class="radio-now-playing">
                            <div class="radio-station-art" id="stationArt">
                                <span style="font-size: 64px;">📻</span>
                            </div>
                            <div class="radio-station-info">
                                <span class="radio-live-badge">🔴 CANLI</span>
                                <h3 id="stationName">Radyo Adı</h3>
                                <p id="currentShow">Şu an: Program Adı</p>
                            </div>
                        </div>
                        
                        <!-- Player Controls -->
                        <div class="radio-player">
                            <button class="radio-play-btn" id="playBtn">▶</button>
                            <div class="radio-volume">
                                <span>🔈</span>
                                <input type="range" min="0" max="100" value="75" class="radio-volume-slider">
                                <span>🔊</span>
                            </div>
                        </div>
                        
                        <!-- Social Links -->
                        <div class="radio-social-links" id="socialLinksPreview">
                            <a href="#" class="radio-social-btn" style="background:#1877f2;">f</a>
                            <a href="#" class="radio-social-btn" style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);">📷</a>
                            <a href="#" class="radio-social-btn" style="background:#1da1f2;">𝕏</a>
                            <a href="#" class="radio-social-btn" style="background:#ff0000;">▶</a>
                            <a href="#" class="radio-social-btn" style="background:#25d366;" id="whatsappBtn">💬</a>
                        </div>
                        
                        <!-- Bottom Navigation -->
                        <div class="radio-bottom-nav">
                            <button class="radio-nav-btn active">🏠 Ana Sayfa</button>
                            <button class="radio-nav-btn">📅 Programlar</button>
                            <button class="radio-nav-btn">🎙 Podcast</button>
                            <button class="radio-nav-btn">📞</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Configuration Panel -->
            <div class="radio-config-panel">
                <form class="demo-form" id="radioDemoForm" method="get" action="<?= url('mobilebuilder/preview-public') ?>">
                    <h2>📻 Radyo Ayarları</h2>
                    
                    <div class="radio-form-section">
                        <h4>📱 Temel Bilgiler</h4>
                        
                        <label for="appName">Uygulama Adı</label>
                        <input type="text" id="appName" name="app_name" placeholder="Radyo Adı" value="Radyo Adı">
                        
                        <label for="appSlogan">Slogan</label>
                        <input type="text" id="appSlogan" name="slogan" placeholder="En iyi müzik, en iyi radyo" value="En iyi müzik, en iyi radyo">
                        
                        <label for="phoneNumber">Telefon Numarası</label>
                        <input type="tel" id="phoneNumber" name="phone" placeholder="+90 555 000 00 00" value="+90 555 000 00 00">
                    </div>
                    
                    <div class="radio-form-section">
                        <h4>🎵 Radyo Stream</h4>
                        
                        <label for="streamUrl">Stream URL</label>
                        <input type="url" id="streamUrl" name="stream_url" placeholder="https://stream.radio.com/live" value="https://stream.radio.com/live">
                        
                        <label for="streamType">Stream Tipi</label>
                        <select id="streamType" name="stream_type">
                            <option value="mp3">MP3</option>
                            <option value="aac">AAC</option>
                            <option value="ogg">OGG</option>
                            <option value="icecast">Icecast</option>
                            <option value="shoutcast">Shoutcast</option>
                        </select>
                        
                        <label for="bitrate">Bitrate</label>
                        <select id="bitrate" name="bitrate">
                            <option value="64">64 kbps</option>
                            <option value="128" selected>128 kbps</option>
                            <option value="192">192 kbps</option>
                            <option value="256">256 kbps</option>
                            <option value="320">320 kbps</option>
                        </select>
                    </div>
                    
                    <div class="radio-form-section">
                        <h4>👥 Sosyal Medya</h4>
                        
                        <div class="radio-social-inputs">
                            <div class="radio-social-row">
                                <span style="background:#1877f2; color:#fff; padding:8px 12px; border-radius:8px;">f</span>
                                <input type="url" name="facebook" placeholder="Facebook URL" value="https://facebook.com/radyo">
                            </div>
                            <div class="radio-social-row">
                                <span style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888); color:#fff; padding:8px 12px; border-radius:8px;">📷</span>
                                <input type="url" name="instagram" placeholder="Instagram URL" value="https://instagram.com/radyo">
                            </div>
                            <div class="radio-social-row">
                                <span style="background:#1da1f2; color:#fff; padding:8px 12px; border-radius:8px;">𝕏</span>
                                <input type="url" name="twitter" placeholder="Twitter/X URL" value="https://x.com/radyo">
                            </div>
                            <div class="radio-social-row">
                                <span style="background:#ff0000; color:#fff; padding:8px 12px; border-radius:8px;">▶</span>
                                <input type="url" name="youtube" placeholder="YouTube URL" value="https://youtube.com/radyo">
                            </div>
                            <div class="radio-social-row">
                                <span style="background:#25d366; color:#fff; padding:8px 12px; border-radius:8px;">💬</span>
                                <input type="tel" name="whatsapp" placeholder="WhatsApp numarası" value="+90 555 000 00 00">
                            </div>
                            <div class="radio-social-row">
                                <span style="background:#0088cc; color:#fff; padding:8px 12px; border-radius:8px;">📧</span>
                                <input type="email" name="email" placeholder="E-posta" value="info@radyo.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="radio-form-section">
                        <h4>🎨 Görsel Ayarlar</h4>
                        
                        <label for="primaryColor">Ana Renk</label>
                        <input type="color" id="primaryColor" name="primary_color" value="#e91e63">
                        
                        <label for="logoUpload">Logo Yükle</label>
                        <input type="file" id="logoUpload" accept="image/*">
                        
                        <label for="splashColor">Splash Ekranı Rengi</label>
                        <input type="color" id="splashColor" name="splash_color" value="#e91e63">
                        
                        <label for="enableSplash">
                            <input type="checkbox" id="enableSplash" name="enable_splash" checked>
                            Splash ekranı aktif
                        </label>
                    </div>
                    
                    <div class="radio-form-section">
                        <h4>ℹ️ İletişim Bilgileri</h4>
                        
                        <label for="address">Adres</label>
                        <textarea id="address" name="address" rows="2" placeholder="Radyo adresi...">İstanbul, Türkiye</textarea>
                        
                        <label for="about">Hakkında</label>
                        <textarea id="about" name="about" rows="3" placeholder="Radyo hakkında...">En iyi müzik, en iyi programlar ile yanınızdayız.</textarea>
                    </div>
                    
                    <div class="radio-form-section">
                        <h4>📺 PWA Ayarları</h4>
                        
                        <label for="pwaName">PWA Görünen Adı</label>
                        <input type="text" id="pwaName" name="pwa_name" value="Radyo">
                        
                        <label>
                            <input type="checkbox" name="pwa_installable" checked>
                            Uygulama yüklenebilir olsun
                        </label>
                        <label>
                            <input type="checkbox" name="pwa_notification" checked>
                            Bildirimlere izin ver
                        </label>
                    </div>
                    
                    <input type="hidden" name="template" value="radio">
                    <input type="hidden" name="demo_mode" value="radio_config">
                    
                    <button type="submit" class="site-btn" style="width:100%; margin-top:16px;">🎵 Radyo Uygulamasını Önizle</button>
                    <a class="site-btn secondary" href="<?= url('mobilebuilder/build') ?>" style="width:100%; margin-top:8px; display:block; text-align:center;">APK/AAB Oluştur</a>
                </form>
                
                <div class="radio-features-box">
                    <h4>🎁 Radyo Uygulaması Özellikleri</h4>
                    <ul>
                        <li>✓ Canlı radyo dinleme (MP3/AAC/OGG)</li>
                        <li>✓ ICEcast/Shoutcast desteği</li>
                        <li>✓ Program rehberi</li>
                        <li>✓ Podcast desteği</li>
                        <li>✓ WhatsApp iletişim butonu</li>
                        <li>✓ Sosyal medya entegrasyonu</li>
                        <li>✓ Özel logo ve tema</li>
                        <li>✓ Splash screen</li>
                        <li>✓ PWA (Progressive Web App)</li>
                        <li>✓ Android APK çıktısı</li>
                        <li>✓ Bildirim desteği</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.radio-app-builder {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 32px;
    margin-top: 32px;
}

@media (max-width: 900px) {
    .radio-app-builder {
        grid-template-columns: 1fr;
    }
}

.radio-phone-frame {
    display: flex;
    justify-content: center;
}

.radio-phone-mock {
    width: 300px;
    background: #1a1a2e;
    border-radius: 40px;
    padding: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.radio-status-bar {
    display: flex;
    justify-content: space-between;
    padding: 8px 16px;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
}

.radio-splash-screen {
    background: linear-gradient(135deg, #e91e63, #9c27b0);
    border-radius: 28px;
    padding: 60px 20px;
    text-align: center;
    min-height: 500px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.radio-splash-logo {
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.radio-splash-loader {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-top: 24px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.radio-main-screen {
    background: #16213e;
    border-radius: 28px;
    padding: 16px;
    min-height: 500px;
    color: #fff;
    display: flex;
    flex-direction: column;
}

.radio-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.radio-logo-small {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.radio-header h4 {
    flex: 1;
    margin: 0;
    font-size: 16px;
}

.radio-now-playing {
    text-align: center;
    padding: 24px 0;
}

.radio-station-art {
    width: 140px;
    height: 140px;
    background: linear-gradient(135deg, #e91e63, #9c27b0);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    animation: rotate 20s linear infinite;
    box-shadow: 0 8px 32px rgba(233, 30, 99, 0.4);
}

@keyframes rotate {
    to { transform: rotate(360deg); }
}

.radio-live-badge {
    display: inline-block;
    background: #ff0000;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.radio-station-info h3 {
    margin: 8px 0;
    font-size: 18px;
}

.radio-station-info p {
    margin: 0;
    font-size: 13px;
    color: rgba(255,255,255,0.7);
}

.radio-player {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    margin: 16px 0;
}

.radio-play-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e91e63, #9c27b0);
    border: none;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(233, 30, 99, 0.4);
}

.radio-volume {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
}

.radio-volume-slider {
    flex: 1;
    height: 4px;
    -webkit-appearance: none;
    background: rgba(255,255,255,0.3);
    border-radius: 2px;
}

.radio-social-links {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 16px 0;
}

.radio-social-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
    text-decoration: none;
}

.radio-bottom-nav {
    margin-top: auto;
    display: flex;
    justify-content: space-around;
    padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.radio-nav-btn {
    background: none;
    border: none;
    color: rgba(255,255,255,0.5);
    font-size: 11px;
    cursor: pointer;
    padding: 8px;
}

.radio-nav-btn.active {
    color: #e91e63;
}

.radio-config-panel {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.radio-form-section {
    background: rgba(30, 41, 59, 0.8);
    border-radius: 16px;
    padding: 20px;
    border: 1px solid rgba(71, 85, 105, 0.5);
}

.radio-form-section h4 {
    margin: 0 0 16px;
    font-size: 14px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.radio-form-section label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #94a3b8;
    margin-bottom: 6px;
}

.radio-form-section input[type="text"],
.radio-form-section input[type="url"],
.radio-form-section input[type="tel"],
.radio-form-section input[type="email"],
.radio-form-section textarea,
.radio-form-section select {
    width: 100%;
    padding: 10px 12px;
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(71, 85, 105, 0.5);
    border-radius: 10px;
    color: #f8fafc;
    font-size: 14px;
    margin-bottom: 12px;
    box-sizing: border-box;
}

.radio-form-section input[type="color"] {
    width: 100%;
    height: 40px;
    padding: 4px;
    cursor: pointer;
}

.radio-form-section input[type="checkbox"] {
    margin-right: 8px;
}

.radio-social-inputs {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.radio-social-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.radio-social-row input {
    flex: 1;
    margin-bottom: 0 !important;
}

.radio-features-box {
    background: linear-gradient(135deg, rgba(233, 30, 99, 0.1), rgba(156, 39, 176, 0.1));
    border: 1px solid rgba(233, 30, 99, 0.3);
    border-radius: 16px;
    padding: 20px;
}

.radio-features-box h4 {
    margin: 0 0 12px;
    color: #e91e63;
}

.radio-features-box ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
}

.radio-features-box li {
    font-size: 13px;
    color: #94a3b8;
}

.site-btn.secondary {
    background: linear-gradient(135deg, #475569, #334155) !important;
    color: #f8fafc !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle splash screen visibility
    const enableSplash = document.getElementById('enableSplash');
    const splashPreview = document.getElementById('splashPreview');
    const radioAppPreview = document.getElementById('radioAppPreview');
    
    if (enableSplash) {
        enableSplash.addEventListener('change', function() {
            if (this.checked) {
                splashPreview.style.display = 'flex';
                radioAppPreview.style.display = 'none';
            } else {
                splashPreview.style.display = 'none';
                radioAppPreview.style.display = 'flex';
            }
        });
        
        // Auto switch after 2 seconds
        setTimeout(function() {
            if (enableSplash.checked) {
                splashPreview.style.display = 'none';
                radioAppPreview.style.display = 'flex';
            }
        }, 2000);
    }
    
    // Update preview on input change
    const updatePreview = () => {
        const appName = document.getElementById('appName')?.value || 'Radyo';
        const primaryColor = document.getElementById('primaryColor')?.value || '#e91e63';
        
        // Update header
        const headerName = document.getElementById('headerAppName');
        if (headerName) headerName.textContent = appName;
        
        const splashName = document.getElementById('splashAppName');
        if (splashName) splashName.textContent = appName;
        
        // Update colors
        document.querySelectorAll('.radio-station-art, .radio-splash-screen, .radio-play-btn').forEach(el => {
            el.style.background = `linear-gradient(135deg, ${primaryColor}, ${adjustColor(primaryColor, -30)})`;
        });
    };
    
    ['appName', 'primaryColor'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updatePreview);
    });
    
    // Play button toggle
    const playBtn = document.getElementById('playBtn');
    if (playBtn) {
        playBtn.addEventListener('click', function() {
            if (this.textContent === '▶') {
                this.textContent = '⏸';
                document.querySelector('.radio-station-art').style.animationPlayState = 'running';
            } else {
                this.textContent = '▶';
                document.querySelector('.radio-station-art').style.animationPlayState = 'paused';
            }
        });
    }
});

function adjustColor(color, amount) {
    const hex = color.replace('#', '');
    const r = Math.max(0, Math.min(255, parseInt(hex.substr(0, 2), 16) + amount));
    const g = Math.max(0, Math.min(255, parseInt(hex.substr(2, 2), 16) + amount));
    const b = Math.max(0, Math.min(255, parseInt(hex.substr(4, 2), 16) + amount));
    return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}
</script>
