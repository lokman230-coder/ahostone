<?php
// Live Chat Admin Panel
ao_live_chat_ensure_schema();

$stats = [
    'total' => db()->query("SELECT COUNT(*) FROM chat_conversations")->fetchColumn(),
    'active' => db()->query("SELECT COUNT(*) FROM chat_conversations WHERE status='active'")->fetchColumn(),
    'pending' => db()->query("SELECT COUNT(*) FROM chat_conversations WHERE status='pending'")->fetchColumn(),
    'today' => db()->query("SELECT COUNT(*) FROM chat_conversations WHERE DATE(started_at)=CURDATE()")->fetchColumn(),
];

$conversations = db()->query("SELECT c.*, 
    d.name as department_name,
    a.name as agent_name
    FROM chat_conversations c 
    LEFT JOIN chat_departments d ON d.id=c.department_id 
    LEFT JOIN chat_agents a ON a.id=c.agent_id 
    ORDER BY c.last_message_at DESC LIMIT 50")->fetchAll();

$agents = db()->query("SELECT * FROM chat_agents ORDER BY name ASC")->fetchAll();
$departments = db()->query("SELECT * FROM chat_departments ORDER BY name ASC")->fetchAll();
?>
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
<div class="ao-page-head">
    <div>
        <h2>💬 Canlı Destek Sohbet</h2>
        <p>WhatsApp, AI chatbot ve canlı destek. Müşterilerinizle anında iletişim.</p>
    </div>
    <div class="ao-actions">
        <button class="whatsapp-btn" onclick="alert('WhatsApp entegrasyonu için WhatsApp Business API yapılandırması gerekli.')">
            <span>📱</span> WhatsApp Ayarla
        </button>
    </div>
</div>

<div class="chat-widgets">
    <div class="chat-stat">
        <strong><?= $stats['total'] ?></strong>
        <span>Toplam Sohbet</span>
    </div>
    <div class="chat-stat success">
        <strong><?= $stats['active'] ?></strong>
        <span>Aktif Sohbet</span>
    </div>
    <div class="chat-stat warning">
        <strong><?= $stats['pending'] ?></strong>
        <span>Bekleyen</span>
    </div>
    <div class="chat-stat">
        <strong><?= $stats['today'] ?></strong>
        <span>Bugün</span>
    </div>
</div>

<div class="ao-grid two" style="gap:24px">
    <!-- Conversation List -->
    <div class="ao-card">
        <h3>Sohbetler</h3>
        <div style="max-height:600px;overflow-y:auto">
        <?php foreach($conversations as $c): ?>
            <div class="conversation-item" data-id="<?= $c['id'] ?>">
                <div class="conv-avatar"><?= strtoupper(substr($c['visitor_name'] ?? 'Z', 0, 1)) ?></div>
                <div class="conv-info">
                    <div class="conv-name">
                        <?= e($c['visitor_name'] ?? 'Ziyaretçi') ?>
                        <span class="conv-status <?= e($c['status']) ?>"></span>
                    </div>
                    <div class="conv-preview"><?= e(substr($c['visitor_email'] ?? 'Sohbet devam ediyor...', 0, 50)) ?>...</div>
                    <div class="conv-meta">
                        <span class="ao-badge <?= e($c['department_name'] ?? 'Genel') ?>"><?= e($c['department_name'] ?? 'Genel') ?></span>
                        <span class="conv-time"><?= date('H:i', strtotime($c['last_message_at'] ?? $c['started_at'])) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; if(empty($conversations)): ?>
            <div style="text-align:center;padding:60px;color:#64748b">
                <div style="font-size:48px;margin-bottom:12px">💬</div>
                <p>Henüz sohbet yok.</p>
            </div>
        <?php endif; ?>
        </div>
    </div>
    
    <!-- Chat Window -->
    <div class="chat-window">
        <div class="chat-header">
            <div style="width:40px;height:40px;border-radius:50%;background:#10b981;display:flex;align-items:center;justify-content:center">👤</div>
            <div>
                <strong>Destek Ekibi</strong>
                <div style="font-size:0.85rem;opacity:0.8">Çevrimiçi</div>
            </div>
            <div style="margin-left:auto;display:flex;gap:8px">
                <button class="ao-mini-btn">📞</button>
                <button class="ao-mini-btn">❌</button>
            </div>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="chat-message ai">
                <strong>🤖 AI Asistan:</strong><br>
                Merhaba! Size nasıl yardımcı olabilirim? Hosting, domain veya fatura konularında sorunuz varsa yardımcı olabilirim.
            </div>
            <div class="chat-message visitor">
                Hosting paketi hakkında bilgi almak istiyorum.
            </div>
            <div class="chat-message agent">
                Tabii! Hangi hosting paketi ile ilgileniyorsunuz? Paylaşımlı, VPS veya özel sunucu seçeneklerimiz mevcut.
            </div>
        </div>
        
        <div class="chat-input-area">
            <input type="text" class="chat-input" id="chatInput" placeholder="Mesajınızı yazın...">
            <button class="ao-btn" onclick="sendMessage()">Gönder</button>
        </div>
    </div>
</div>

<!-- AI Chat Settings -->
<div class="ao-card" style="margin-top:24px">
    <h3>🤖 AI Chatbot Ayarları</h3>
    <div class="ao-grid three" style="gap:20px">
        <div class="ao-form">
            <label>
                <input type="checkbox" checked> AI Chatbot Aktif
            </label>
            <small style="color:#64748b">Otomatik yanıtlar için AI kullanılsın</small>
        </div>
        <div class="ao-form">
            <label>Hoşgeldin Mesajı</label>
            <input type="text" value="Merhaba! Size nasıl yardımcı olabilirim?">
        </div>
        <div class="ao-form">
            <label>Çalışma Saatleri</label>
            <input type="text" value="09:00 - 18:00">
        </div>
    </div>
</div>

<!-- WhatsApp Integration -->
<div class="ao-card" style="margin-top:24px">
    <h3>📱 WhatsApp Entegrasyonu</h3>
    <div class="ao-grid two" style="gap:20px">
        <div>
            <p style="color:#64748b;margin-bottom:16px">WhatsApp Business API ile müşterilerinize WhatsApp üzerinden de destek verebilirsiniz.</p>
            <div class="ao-form">
                <label>WhatsApp Numaranız</label>
                <input type="text" placeholder="+90 5XX XXX XX XX">
            </div>
            <div class="ao-form">
                <label>Otomatik Yanıt Mesajı</label>
                <textarea placeholder="Merhaba! Şu anda müsait değiliz. En kısa sürede size dönüş yapacağız."></textarea>
            </div>
            <button class="ao-btn" style="margin-top:12px">💾 Kaydet</button>
        </div>
        <div style="text-align:center;padding:40px;background:#f8fafc;border-radius:16px">
            <div style="font-size:80px;margin-bottom:16px">📱</div>
            <h3>WhatsApp Business API</h3>
            <p style="color:#64748b">Canlı sohbet, otomatik yanıtlar ve çoklu cihaz desteği için WhatsApp Business API'yi yapılandırın.</p>
        </div>
    </div>
</div>

<script>
function sendMessage() {
    const input = document.getElementById('chatInput');
    const messages = document.getElementById('chatMessages');
    const text = input.value.trim();
    if(!text) return;
    
    // Add user message
    messages.innerHTML += `<div class="chat-message visitor">${text}</div>`;
    input.value = '';
    
    // Scroll to bottom
    messages.scrollTop = messages.scrollHeight;
    
    // Simulate AI response
    setTimeout(() => {
        messages.innerHTML += `<div class="chat-message ai"><strong>🤖 AI:</strong><br>Mesajınız alındı. En kısa sürede yanıt vereceğiz.</div>`;
        messages.scrollTop = messages.scrollHeight;
    }, 1000);
}

// Handle Enter key
document.getElementById('chatInput')?.addEventListener('keypress', e => {
    if(e.key === 'Enter') sendMessage();
});

// Select conversation
document.querySelectorAll('.conversation-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
