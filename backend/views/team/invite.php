<h3>Invite Member to <?= $team->name ?></h3>

<form method="POST">

    <select name="user_id" class="form-select">
        <?php foreach($users as $id => $name): ?>
            <option value="<?= $id ?>"><?= $name ?></option>
        <?php endforeach; ?>
    </select>

    <button class="btn btn-success mt-3">Add Member</button>

</form>
