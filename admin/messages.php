<?php
require __DIR__ . '/../includes/bootstrap.php';
$me = require_role('admin', 'manager');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) $_POST['id'];
    if (($_POST['action'] ?? '') === 'reply' && trim((string) $_POST['reply']) !== '') {
        db()->prepare("UPDATE messages SET reply = ?, replied_by = ?, replied_at = NOW(), status = 'answered' WHERE id = ?")
            ->execute([trim((string) $_POST['reply']), $me['id'], $id]);
        flash('Ответ сохранён и виден клиенту в личном кабинете.');
    } elseif (($_POST['action'] ?? '') === 'close') {
        db()->prepare("UPDATE messages SET status = 'closed' WHERE id = ?")->execute([$id]);
        flash('Обращение закрыто.', 'info');
    }
    redirect('admin/messages.php');
}
$rows = db()->query("SELECT m.*, u.login FROM messages m LEFT JOIN users u ON u.id = m.user_id
    ORDER BY m.status = 'new' DESC, m.created_at DESC")->fetchAll();
$title = 'Сообщения';
$crumbs = [['Панель управления', url('admin/index.php')], ['Сообщения']];
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_nav.php';
?>
<h1>Сообщения клиентов</h1>
<?php foreach ($rows as $m): ?>
    <div class="msg msg-<?= e($m['status']) ?>">
        <p class="meta">№<?= (int) $m['id'] ?> · <?= e($m['created_at']) ?> · <?= e($m['name']) ?> &lt;<?= e($m['email']) ?>&gt;
            <?= $m['login'] ? '· пользователь ' . e($m['login']) : '· гость (ответ на e-mail)' ?> · <strong><?= e($m['status']) ?></strong></p>
        <p><strong><?= e($m['subject']) ?></strong><br><?= nl2br(e($m['body'])) ?></p>
        <?php if ($m['reply']): ?><div class="reply"><strong>Ответ:</strong> <?= nl2br(e($m['reply'])) ?></div><?php endif; ?>
        <?php if ($m['status'] !== 'closed'): ?>
        <form method="post" class="form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <label>Ответ <textarea name="reply" rows="2"><?= e($m['reply'] ?? '') ?></textarea></label>
            <button class="btn" name="action" value="reply">Ответить</button>
            <button class="btn btn-light" name="action" value="close">Закрыть обращение</button></form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
