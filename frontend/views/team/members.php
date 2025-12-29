<h3 class="fw-bold mb-3">Team Members</h3>

<?php if(count($members) == 0): ?>
    <div class="text-center p-5 border rounded shadow-sm bg-light">
        <h4 class="fw-bold mb-2">😕 No members found</h4>
        <p class="text-muted">You are not part of any team yet.</p>
        <a href="/team/create" class="btn btn-success">+ Create a Team</a>
    </div>
<?php else: ?>

<div class="row g-3">

<?php foreach($members as $m): 
    $user = $m->user;
    $team = $m->team;
?>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">

            <h6 class="fw-bold"><?= $user->username ?></h6>
            <p class="small text-muted mb-1">📌 Team: <?= $team->name ?></p>
            <p class="small">⭐ Role: <?= ucfirst($m->role) ?></p>

            <a href="/team/view?id=<?= $team->id ?>"
               class="btn btn-outline-primary btn-sm w-100 mt-2">
               View Team →
            </a>

        </div>
    </div>
<?php endforeach; ?>

</div>
<?php endif; ?> 
