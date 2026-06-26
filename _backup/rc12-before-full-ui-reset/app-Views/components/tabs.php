<?php $tabs=$tabs ?? []; $active=$active ?? array_key_first($tabs); ?>
<div class="ao-tabs" data-ao-tabs>
<?php foreach($tabs as $id=>$tab): ?><button type="button" class="<?= $id===$active?'active':'' ?>" data-tab-target="<?= e($id) ?>"><?= e(is_array($tab)?($tab['label']??$id):$tab) ?></button><?php endforeach; ?>
</div>
