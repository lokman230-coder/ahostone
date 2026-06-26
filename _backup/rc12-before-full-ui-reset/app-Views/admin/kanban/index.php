<?php
// Kanban Board - Admin View
$board_id = (int)($_GET['board'] ?? 1);
$boards = db()->query("SELECT * FROM kanban_boards ORDER BY created_at DESC")->fetchAll();
$columns = db()->query("SELECT * FROM kanban_columns WHERE board_id=$board_id ORDER BY sort_order ASC")->fetchAll();
$cards = db()->query("SELECT c.*, col.name as column_name 
    FROM kanban_cards c 
    LEFT JOIN kanban_columns col ON col.id=c.column_id 
    WHERE col.board_id=$board_id 
    ORDER BY c.position ASC")->fetchAll();

// Group cards by column
$cardsByColumn = [];
foreach($cards as $card) {
    $cardsByColumn[$card['column_id']][] = $card;
}
?>
<style>
.kanban-container { display: flex; gap: 16px; overflow-x: auto; padding: 20px 0; min-height: 600px; }
.kanban-column { flex: 0 0 300px; background: #f8fafc; border-radius: 16px; padding: 16px; max-height: calc(100vh - 200px); overflow-y: auto; }
.kanban-column.drag-over { background: #e0f2fe; border: 2px dashed #2563eb; }
.kanban-column-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid; }
.kanban-column-title { font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 8px; }
.kanban-column-count { background: #e2e8f0; padding: 2px 10px; border-radius: 999px; font-size: 0.85rem; font-weight: 600; }
.kanban-card { background: #fff; border-radius: 12px; padding: 14px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); cursor: grab; transition: transform 0.2s, box-shadow 0.2s; }
.kanban-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
.kanban-card.dragging { opacity: 0.5; transform: scale(0.98); }
.kanban-card.drag-target { border: 2px dashed #2563eb; }
.kanban-card-title { font-weight: 600; margin-bottom: 8px; color: #1e293b; }
.kanban-card-meta { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; font-size: 0.85rem; color: #64748b; }
.kanban-priority { padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
.kanban-priority.high, .kanban-priority.urgent { background: #fef2f2; color: #dc2626; }
.kanban-priority.medium { background: #fef3c7; color: #d97706; }
.kanban-priority.low { background: #f0fdf4; color: #16a34a; }
.kanban-due { display: flex; align-items: center; gap: 4px; }
.kanban-due.overdue { color: #dc2626; }
.kanban-empty { text-align: center; padding: 40px 20px; color: #94a3b8; font-size: 0.95rem; }
.kanban-add-card { width: 100%; padding: 12px; border: 2px dashed #cbd5e1; border-radius: 12px; background: transparent; color: #64748b; cursor: pointer; transition: all 0.2s; }
.kanban-add-card:hover { border-color: #2563eb; color: #2563eb; background: #eff6ff; }
.kanban-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 16px; }
.kanban-boards { display: flex; gap: 8px; }
.kanban-board-btn { padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; font-weight: 500; transition: all 0.2s; }
.kanban-board-btn:hover, .kanban-board-btn.active { background: #2563eb; color: #fff; border-color: #2563eb; }
</style>

<div class="ao-page-head">
    <div>
        <h2>📋 Kanban Board</h2>
        <p>Proje ve görev yönetimi. Kartları sürükleyerek durumlarını değiştirin.</p>
    </div>
    <div class="ao-actions">
        <a class="ao-btn" href="<?= url('admin/kanban/board') ?>">+ Yeni Pano</a>
    </div>
</div>

<div class="kanban-header">
    <div class="kanban-boards">
        <?php foreach($boards as $b): ?>
        <a href="<?= url('admin/kanban?board='.$b['id']) ?>" class="kanban-board-btn <?= $b['id']==$board_id?'active':'' ?>"><?= e($b['name']) ?></a>
        <?php endforeach; ?>
    </div>
    <div>
        <button class="ao-btn secondary" onclick="addCard(<?= $columns[0]['id'] ?? 0 ?>)">+ Yeni Görev</button>
    </div>
</div>

<div class="kanban-container" id="kanbanBoard">
    <?php foreach($columns as $col): ?>
    <div class="kanban-column" data-column-id="<?= $col['id'] ?>" style="border-bottom: 3px solid <?= e($col['color']) ?>">
        <div class="kanban-column-header">
            <span class="kanban-column-title">
                <span style="width:10px;height:10px;border-radius:50%;background:<?= e($col['color']) ?>"></span>
                <?= e($col['name']) ?>
            </span>
            <span class="kanban-column-count"><?= count($cardsByColumn[$col['id']] ?? []) ?></span>
        </div>
        
        <div class="kanban-cards" data-column="<?= $col['id'] ?>">
            <?php foreach($cardsByColumn[$col['id']] ?? [] as $card): ?>
            <div class="kanban-card" draggable="true" data-card-id="<?= $card['id'] ?>">
                <div class="kanban-card-title"><?= e($card['title']) ?></div>
                <?php if($card['description']): ?>
                <p style="font-size:0.85rem;color:#64748b;margin:4px 0 8px"><?= e(substr($card['description'], 0, 80)) ?>...</p>
                <?php endif; ?>
                <div class="kanban-card-meta">
                    <span class="kanban-priority <?= e($card['priority']) ?>"><?= e($card['priority']) ?></span>
                    <?php if($card['due_date']): ?>
                    <span class="kanban-due <?= strtotime($card['due_date']) < time() ? 'overdue' : '' ?>">
                        📅 <?= date('d.m', strtotime($card['due_date'])) ?>
                    </span>
                    <?php endif; ?>
                    <?php if($card['comments_count'] > 0): ?>
                    <span>💬 <?= $card['comments_count'] ?></span>
                    <?php endif; ?>
                </div>
                <div style="margin-top:10px;display:flex;gap:6px">
                    <a href="<?= url('admin/kanban/card?id='.$card['id']) ?>" class="ao-mini-btn">Düzenle</a>
                    <a href="<?= url('admin/kanban/card-delete?id='.$card['id'].'&csrf_token='.csrf_token()) ?>" class="ao-mini-btn danger" onclick="return confirm('Silinecek?')">Sil</a>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($cardsByColumn[$col['id']])): ?>
            <div class="kanban-empty">Henüz görev yok</div>
            <?php endif; ?>
        </div>
        
        <button class="kanban-add-card" onclick="addCard(<?= $col['id'] ?>)">+ Görev Ekle</button>
    </div>
    <?php endforeach; ?>
</div>

<script>
// Drag and Drop
document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragstart', handleDragStart);
    card.addEventListener('dragend', handleDragEnd);
});

document.querySelectorAll('.kanban-cards').forEach(col => {
    col.addEventListener('dragover', handleDragOver);
    col.addEventListener('drop', handleDrop);
    col.addEventListener('dragenter', handleDragEnter);
    col.addEventListener('dragleave', handleDragLeave);
});

let dragSrc = null;

function handleDragStart(e) {
    dragSrc = e.target;
    e.target.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
    e.target.classList.remove('dragging');
    document.querySelectorAll('.kanban-column').forEach(c => c.classList.remove('drag-over'));
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
}

function handleDragEnter(e) {
    e.preventDefault();
    e.currentTarget.closest('.kanban-column').classList.add('drag-over');
}

function handleDragLeave(e) {
    if (!e.currentTarget.contains(e.relatedTarget)) {
        e.currentTarget.closest('.kanban-column').classList.remove('drag-over');
    }
}

function handleDrop(e) {
    e.preventDefault();
    const col = e.currentTarget;
    col.closest('.kanban-column').classList.remove('drag-over');
    
    if (!dragSrc) return;
    
    const cardId = dragSrc.dataset.cardId;
    const newColumnId = col.dataset.column;
    
    // Move card via AJAX
    fetch('<?= url('admin/kanban/move-card') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'card_id=' + cardId + '&column_id=' + newColumnId + '&csrf_token=<?= csrf_token() ?>'
    }).then(() => location.reload());
}

function addCard(columnId) {
    const title = prompt('Görev başlığı:');
    if (!title) return;
    
    fetch('<?= url('admin/kanban/add-card') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'column_id=' + columnId + '&title=' + encodeURIComponent(title) + '&csrf_token=<?= csrf_token() ?>'
    }).then(() => location.reload());
}
</script>
