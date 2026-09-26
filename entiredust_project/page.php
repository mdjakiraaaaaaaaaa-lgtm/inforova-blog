<?php
require_once __DIR__.'/includes/functions.php';
$slug = trim($_GET['slug'] ?? '');
$s = $pdo->prepare("SELECT * FROM pages WHERE slug=? AND status='published'");
$s->execute([$slug]);
$p = $s->fetch();

if (!$p) {
    http_response_code(404);
    exit('Page not found');
}

$page_title = $p['title'];
$page_desc = $p['meta_description'];
$breadcrumbs = [['label' => $p['title'], 'url' => null]];
require __DIR__.'/includes/header.php';
?>
<article class="article">
  <h1><?=e($p['title'])?></h1>
  <div class="content"><?=$p['content_html']?></div>
</article>
<?php require __DIR__.'/includes/footer.php'; ?>
