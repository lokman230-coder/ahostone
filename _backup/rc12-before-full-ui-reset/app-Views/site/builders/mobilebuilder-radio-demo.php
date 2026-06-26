<section class="ao-site-content ao-mobilebuilder-radio-demo-page builder-public-page">
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
