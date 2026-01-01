<h2 class="text-success">Payment Successful </h2>

<p><strong>Status:</strong> <?= $session->payment_status ?></p>
<p><strong>Amount:</strong> ₹<?= $session->amount_total / 100 ?></p>
<p><strong>Payment ID:</strong> <?= $session->payment_intent ?></p>