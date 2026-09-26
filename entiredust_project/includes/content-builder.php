<?php
declare(strict_types=1);
require_once __DIR__.'/functions.php';

/*
 * Builds one article's content_html from plain text pieces, so the actual
 * writing (in seed-expansion.php) never has to touch HTML markup.
 * Every <h2> gets an id, which extract_toc() in functions.php turns into
 * the sidebar table of contents on the article page.
 */
function inforova_build_article_html(
    string $intro,
    array $quickFacts,
    array $sections,
    array $faqs,
    string $conclusion,
    string $heroImg = '',
    string $heroAlt = ''
): string {
    $html = '<p>' . $intro . '</p>';

    if ($quickFacts) {
        $html .= '<div class="quick-facts"><h3>✅ Key Takeaways</h3><ul>';
        foreach ($quickFacts as $fact) {
            $html .= '<li>👉 ' . $fact . '</li>';
        }
        $html .= '</ul></div>';
    }

    if ($heroImg !== '') {
        $html .= '<img src="' . $heroImg . '" alt="' . htmlspecialchars($heroAlt, ENT_QUOTES, 'UTF-8') . '" loading="lazy" class="article-image">';
    }

    $sectionEmojis = ['📌', '🎯', '💡', '🔍', '📝', '⚡', '🧭', '🛠️'];
    foreach ($sections as $i => $section) {
        $id = slugify($section['heading']);
        $emoji = $sectionEmojis[$i % count($sectionEmojis)];
        $html .= '<h2 id="' . $id . '">' . $emoji . ' ' . $section['heading'] . '</h2>';

        foreach ((array) ($section['body'] ?? []) as $para) {
            $html .= '<p>' . $para . '</p>';
        }

        if (!empty($section['list'])) {
            $html .= '<ul>';
            foreach ($section['list'] as $item) {
                $html .= '<li>✔️ ' . $item . '</li>';
            }
            $html .= '</ul>';
        }

        if (!empty($section['image']['url'])) {
            $alt = htmlspecialchars((string) ($section['image']['alt'] ?? ''), ENT_QUOTES, 'UTF-8');
            $html .= '<img src="' . $section['image']['url'] . '" alt="' . $alt . '" loading="lazy" class="article-image">';
        }
    }

    if ($faqs) {
        $html .= '<h2 id="frequently-asked-questions">🙋 Frequently Asked Questions</h2>';
        foreach ($faqs as $faq) {
            $html .= '<div class="faq-item"><h3>❓ ' . $faq['q'] . '</h3><p>' . $faq['a'] . '</p></div>';
        }
    }

    $html .= '<div class="quick-facts"><h3>💬 In Short</h3><p>' . $conclusion . '</p></div>';

    return $html;
}

/* A small rotating pool of stable, freely-usable Unsplash photos, grouped by
   topic so every article gets an image that actually matches its subject. */
function inforova_image(string $topic, int $index = 0): string
{
    $pool = [
        'study' => ['1523240795612-9a054b0db644', '1503676260728-1c00da094a0b', '1522202176988-66273c2fd55f', '1434030216411-0b793f4b4173'],
        'exam' => ['1517245386807-bb43f82c33c4', '1513258496099-48168024aec0', '1554415707-6e8cfc93fe23', '1580582932707-520aed937b7b'],
        'office' => ['1521737604893-d14cc237f11d', '1497032628192-86f99bcd76bc', '1556761175-4b46a572b786', '1454165804606-c3d57bc86b40'],
        'career' => ['1521791136064-7986c2920216', '1552664730-d307ca884978', '1552581234-26160f608093', '1600880292203-757bb62b4baf'],
        'scholarship' => ['1523050854058-8df90110c9f1', '1571260899304-425eee4c7efc', '1523580494863-6f3031224c94', '1509062522246-3755977927d7'],
        'skills' => ['1519389950473-47ba0277781c', '1522071820081-009f0129c71c', '1531482615713-2afd69097998', '1531538606174-0f90ff5dce83'],
        'news' => ['1495020689067-958852a7765e', '1504711434969-e33886168f5c', '1585829365295-ab7cd400c167', '1526628953301-3e589a6a8b74'],
    ];

    $group = $pool[$topic] ?? $pool['study'];
    $id = $group[$index % count($group)];

    return 'https://images.unsplash.com/photo-' . $id . '?auto=format&fit=crop&w=1200&q=70';
}
