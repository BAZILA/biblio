<?php
/** Accès à la base : une connexion PDO partagée et quelques raccourcis. */

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = $GLOBALS['config']['db'];
        $pdo = new PDO(
            "mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4",
            $c['user'],
            $c['password'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

/** Toutes les lignes. */
function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

/** Première ligne ou null. */
function row(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}

/** Première colonne de la première ligne. */
function val(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

/** INSERT à partir d'un tableau associatif ; renvoie l'id créé. */
function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(', ', $cols),
        implode(', ', array_map(fn($c) => ':' . $c, $cols))
    );
    q($sql, $data);
    return (int) db()->lastInsertId();
}

/** UPDATE ... WHERE id = :id à partir d'un tableau associatif. */
function update(string $table, int $id, array $data): void
{
    $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
    $data['id'] = $id;
    q("UPDATE $table SET $set WHERE id = :id", $data);
}
