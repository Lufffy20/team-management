<?php use yii\helpers\Html; ?>

<h3 class="fw-bold mb-3">Add Member - <?= Html::encode($team->name) ?></h3>

<div class="card p-4 shadow-sm" style="max-width:500px">

    <?= Html::beginForm(['/team/add-member-email'], 'post') ?>

        <?= Html::hiddenInput('team_id', $team->id) ?>

        <!-- Email Input -->
        <label class="fw-semibold">User Email</label>
        <input type="email" name="email" class="form-control mb-3" 
               placeholder="Enter user email" required>

        
        <!-- 🔥 Multi Board Selection -->
        <label class="fw-semibold mb-1">Assign Boards</label>

        <select name="board_id[]" class="form-control mb-3" multiple required>
            <option disabled>-- Select Boards --</option>

            <?php if (isset($boards) && is_iterable($boards)): ?>
                <?php foreach ($boards as $b): ?>
                    <option value="<?= $b->id ?>">
                        <?= Html::encode($b->title) ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option disabled>No boards available</option>
            <?php endif; ?>
        </select>

        <small class="text-muted d-block mb-3">
            (Hold Ctrl / Command to select multiple boards)
        </small>

        <!-- Submit -->
        <button class="btn btn-success w-100 fw-bold">Add Member & Assign</button>

    <?= Html::endForm() ?>

    <!-- Back -->
    <a href="/team/view?id=<?= $team->id ?>" 
       class="btn btn-light mt-3 w-100">← Back to Team</a>

</div>
