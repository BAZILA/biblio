<?php
/** Messages reçus par le formulaire de contact. */

if ($action === 'voir') {
    $m = row('SELECT * FROM messages WHERE id = ?', [$id]) ?? admin_introuvable('Message');
    if (!$m['lu']) {
        q('UPDATE messages SET lu = 1 WHERE id = ?', [$id]);
    }
    admin_page('message_voir', ['m' => $m], $m['sujet'], 'messages');
}

if ($action === 'supprimer' || $action === 'non-lu') {
    exiger_post();
    if ($action === 'supprimer') {
        exiger('messages.supprimer');
        q('DELETE FROM messages WHERE id = ?', [$id]);
        flash('succes', 'Message supprimé.');
    } else {
        q('UPDATE messages SET lu = 0 WHERE id = ?', [$id]);
    }
    redirect(admin_url('messages'));
}

$pg = pagination_admin((int) val('SELECT COUNT(*) FROM messages'), 30);
admin_page('messages', [
    'liste' => rows("SELECT * FROM messages ORDER BY recu_le DESC LIMIT {$pg['parPage']} OFFSET {$pg['offset']}"),
    'pg' => $pg,
], 'Messages', 'messages');
