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
<?php /* RC12: inline style removed; visual layer is centralized. */ ?>
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
