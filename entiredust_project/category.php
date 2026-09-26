<?php
require_once __DIR__.'/includes/functions.php';
$slug = trim($_GET['slug'] ?? '');
$s = $pdo->prepare('SELECT * FROM categories WHERE slug=?');
$s->execute([$slug]);
$c = $s->fetch();

if (!$c) {
    http_response_code(404);
    exit('Category not found');
}

$page_title = $c['name'];
$page_desc = $c['description'];
$breadcrumbs = [['label' => $c['name'], 'url' => null]];
require __DIR__.'/includes/header.php';

$s = $pdo->prepare("SELECT a.*,c.name category_name,c.slug category_slug FROM articles a JOIN categories c ON c.id=a.category_id WHERE c.id=? AND a.status='published' ORDER BY a.published_at DESC");
$s->execute([$c['id']]);
$articles = $s->fetchAll();
?>
<section class="section-head category-hero">
  <div>
    <span class="eyebrow"><?=category_icon($c)?> CATEGORY</span>
    <h1><?=e($c['name'])?></h1>
    <p><?=e($c['description'])?></p>
    <small><?=count($articles)?> article<?=count($articles)===1?'':'s'?> in this category</small>
  </div>
</section>

<div class="grid">
  <?php foreach ($articles as $a): ?>
    <article class="card reveal">
      <a class="thumb" href="<?=article_url($a['slug'])?>">
        <?php if (!empty($a['thumbnail_url'])): ?>
          <img src="<?=e($a['thumbnail_url'])?>" alt="<?=e($a['title'])?>" loading="lazy">
        <?php else: ?>
          <span class="thumb-fallback">INFOROVA</span>
        <?php endif; ?>
      </a>
      <div class="card-body">
        <h3><a href="<?=article_url($a['slug'])?>"><?=e($a['title'])?></a></h3>
        <p><?=e($a['excerpt'])?></p>
        <small><?=e(date('M d, Y', strtotime($a['published_at'])))?> &middot; <?=reading_time_minutes($a['content_html'])?> min read</small>
      </div>
    </article>
  <?php endforeach; ?>

  <?php if (!$articles): ?>
    <p>No articles in this category yet. Please check back soon.</p>
  <?php endif; ?>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>
