<?php
require_once __DIR__.'/includes/functions.php';
$slug = trim($_GET['slug'] ?? '');
$s = $pdo->prepare("SELECT a.*,c.name category_name,c.slug category_slug FROM articles a JOIN categories c ON c.id=a.category_id WHERE a.slug=? AND a.status='published'");
$s->execute([$slug]);
$a = $s->fetch();

if (!$a) {
    http_response_code(404);
    exit('Article not found');
}

$page_title = $a['title'];
$page_desc = $a['excerpt'];
$canonical = article_url($a['slug']);
$breadcrumbs = [
    ['label' => $a['category_name'], 'url' => category_url($a['category_slug'])],
    ['label' => $a['title'], 'url' => null],
];
require __DIR__.'/includes/header.php';

$toc = extract_toc($a['content_html']);
$minutes = reading_time_minutes($a['content_html']);

$rs = $pdo->prepare("SELECT slug,title,excerpt,thumbnail_url FROM articles WHERE category_id=? AND id<>? AND status='published' ORDER BY published_at DESC LIMIT 4");
$rs->execute([$a['category_id'], $a['id']]);
$related = $rs->fetchAll();
?>
<article class="article-layout">
  <div class="article-main">
    <a class="category" href="<?=category_url($a['category_slug'])?>"><?=e($a['category_name'])?></a>
    <h1><?=e($a['title'])?></h1>
    <div class="meta">Published <?=e(date('F d, Y', strtotime($a['published_at'])))?> &middot; <?=$minutes?> min read</div>

    <?php if ($toc): ?>
      <div class="toc toc-mobile">
        <h2>On this page</h2>
        <ul><?php foreach ($toc as $t): ?><li><a href="#<?=e($t['id'])?>"><?=e($t['text'])?></a></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <div class="content"><?= $a['content_html'] ?></div>

    <?php if ($related): ?>
      <section class="related-articles">
        <h2>Related Articles</h2>
        <div class="grid">
          <?php foreach ($related as $r): ?>
            <article class="card">
              <a class="thumb" href="<?=article_url($r['slug'])?>">
                <?php if (!empty($r['thumbnail_url'])): ?>
                  <img src="<?=e($r['thumbnail_url'])?>" alt="<?=e($r['title'])?>" loading="lazy">
                <?php else: ?>
                  <span class="thumb-fallback">INFOROVA</span>
                <?php endif; ?>
              </a>
              <div class="card-body">
                <h3><a href="<?=article_url($r['slug'])?>"><?=e($r['title'])?></a></h3>
                <p><?=e($r['excerpt'])?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <div class="share-note">Enjoyed this article? Explore more resources from INFOROVA.</div>
  </div>

  <?php if ($toc): ?>
  <aside class="article-sidebar">
    <div class="toc toc-desktop">
      <h2>On this page</h2>
      <ul><?php foreach ($toc as $t): ?><li><a href="#<?=e($t['id'])?>"><?=e($t['text'])?></a></li><?php endforeach; ?></ul>
    </div>
  </aside>
  <?php endif; ?>
</article>
<?php require __DIR__.'/includes/footer.php'; ?>
