<?php
/** Вспомогательные функции вывода, навигации и защиты форм. */

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return ROOT_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function db(): PDO
{
    global $pdo;
    return $pdo;
}

function cfg(string $key): string
{
    global $CONFIG;
    return (string) ($CONFIG[$key] ?? '');
}

/* ---------- CSRF-защита форм ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Форма устарела. Обновите страницу и повторите попытку.');
    }
}

/* ---------- сообщения пользователю ---------- */
function flash(string $text, string $type = 'ok'): void
{
    $_SESSION['flash'][] = [$type, $text];
}

function flashes(): string
{
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as [$type, $text]) {
        $html .= '<div class="alert alert-' . e($type) . '" role="status">' . e($text) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

/* ---------- данные ---------- */
function sections(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT * FROM sections ORDER BY sort') as $row) {
            $cache[$row['code']] = $row;
        }
    }
    return $cache;
}

function articles(string $section, int $limit = 100): array
{
    $order = in_array($section, ['news', 'blog', 'promo'], true) ? 'created_at DESC' : 'id';
    $st = db()->prepare("SELECT * FROM articles WHERE section = ? AND is_published = 1 ORDER BY $order LIMIT ?");
    $st->bindValue(1, $section);
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
}

function article_url(array $a): string
{
    return url('article.php?s=' . urlencode($a['section']) . '&slug=' . urlencode($a['slug']));
}

function img(string $file, string $alt, string $class = ''): string
{
    if ($file === '') {
        return '';
    }
    $src = str_starts_with($file, 'upload-') ? url('uploads/' . $file) : url('assets/img/' . $file);
    return '<img src="' . e($src) . '" alt="' . e($alt) . '" class="' . e($class) . '" loading="lazy">';
}

function ru_date(string $date): string
{
    $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа',
        'сентября', 'октября', 'ноября', 'декабря'];
    $t = strtotime($date);
    return date('j', $t) . ' ' . $months[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
}

function money(?int $v): string
{
    return $v === null ? '' : number_format($v, 0, ',', ' ') . ' ₽';
}

/** Разрешённые теги в тексте статей (контент вводит персонал через админ-панель). */
function safe_html(string $html): string
{
    $clean = strip_tags($html, '<p><h3><h4><ul><ol><li><strong><em><b><i><br><a><blockquote>');
    return preg_replace('/\s(on\w+|style)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
}

/* ---------- версия для слабовидящих (ГОСТ Р 52872-2019) ---------- */
function vi(): array
{
    return [
        'on'     => ($_COOKIE['vi'] ?? '0') === '1',
        'size'   => in_array($_COOKIE['vi_size'] ?? '', ['1', '2', '3'], true) ? $_COOKIE['vi_size'] : '1',
        'scheme' => in_array($_COOKIE['vi_scheme'] ?? '', ['bw', 'wb', 'blue', 'brown'], true)
            ? $_COOKIE['vi_scheme'] : 'bw',
        'img'    => ($_COOKIE['vi_img'] ?? '1') === '1',
        'space'  => ($_COOKIE['vi_space'] ?? '0') === '1',
    ];
}

function vi_link(string $param, string $value, string $label, bool $active): string
{
    $back = urlencode($_SERVER['REQUEST_URI'] ?? url());
    return '<a href="' . e(url("vi.php?$param=$value&back=$back")) . '" class="vi-btn'
        . ($active ? ' active' : '') . '"' . ($active ? ' aria-current="true"' : '') . '>' . $label . '</a>';
}
