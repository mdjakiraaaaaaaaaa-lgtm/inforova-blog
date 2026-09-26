<?php
declare(strict_types=1);
require_once __DIR__.'/../config/database.php';

function setting(string $key,string $default=''): string { static $cache=[]; global $pdo; if(array_key_exists($key,$cache))return $cache[$key]; $s=$pdo->prepare('SELECT setting_value FROM settings WHERE setting_key=?');$s->execute([$key]);$v=$s->fetchColumn();return $cache[$key]=($v===false?$default:(string)$v); }
function slugify(string $s): string { $s=trim(mb_strtolower($s));$s=preg_replace('/[^a-z0-9\s-]/u','',$s);$s=preg_replace('/[\s-]+/','-',(string)$s);return trim((string)$s,'-')?:'post-'.time(); }
function article_url(string $slug): string { return base_url('article.php?slug='.rawurlencode($slug)); }
function category_url(string $slug): string { return base_url('category.php?slug='.rawurlencode($slug)); }

/* Word-count based reading time, used on article cards and article pages. */
function reading_time_minutes(string $html): int
{
    $words = str_word_count((string) strip_tags($html));
    return max(1, (int) ceil($words / 200));
}

/* Table of contents: only <h2 id="..."> headings are linkable (the article
   builder always adds ids; hand-written admin headings without an id are
   simply left out of the list, the article itself is unaffected). */
function extract_toc(string $html): array
{
    if (!preg_match_all('/<h2[^>]*\bid="([^"]+)"[^>]*>(.*?)<\/h2>/is', $html, $m, PREG_SET_ORDER)) {
        return [];
    }

    $toc = [];
    foreach ($m as $row) {
        $toc[] = ['id' => $row[1], 'text' => trim(strip_tags($row[2]))];
    }
    return $toc;
}

/* Fallback icon when a category has none set yet. */
function category_icon(array $category): string
{
    $icon = trim((string) ($category['icon'] ?? ''));
    return $icon !== '' ? $icon : '📄';
}
