<?php
require_once __DIR__.'/includes/functions.php';
$page_title = 'Home';
$page_desc = 'Latest exam news, government job updates, scholarships, study tips and practical career guides.';
require __DIR__.'/includes/header.php';

$totalArticles = (int) $pdo->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn();
$totalCategories = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$categories = $pdo->query('SELECT id,name,slug,description,icon FROM categories ORDER BY name')->fetchAll();

$catCounts = [];
foreach ($pdo->query("SELECT category_id,COUNT(*) c FROM articles WHERE status='published' GROUP BY category_id") as $row) {
    $catCounts[(int) $row['category_id']] = (int) $row['c'];
}
?>
<section class="hero">
  <div>
    <span class="eyebrow">INFOROVA</span>
    <h1>Useful information, explained simply.</h1>
    <p>In-depth guides on exam preparation, government jobs, scholarships, study techniques and career skills, written to actually be useful.</p>
    <div class="hero-stats">
      <div><strong><?=$totalArticles?>+</strong><span>Articles</span></div>
      <div><strong><?=$totalCategories?></strong><span>Categories</span></div>
      <div><strong>Weekly</strong><span>Updates</span></div>
    </div>
  </div>
</section>

<section class="section-head"><h2>Browse by Category</h2></section>
<div class="category-grid">
  <?php foreach ($categories as $c): ?>
    <a class="category-card" href="<?=category_url($c['slug'])?>">
      <span class="category-card-icon"><?=category_icon($c)?></span>
      <span class="category-card-name"><?=e($c['name'])?></span>
      <span class="category-card-desc"><?=e($c['description'])?></span>
      <span class="category-card-count"><?=(int) ($catCounts[$c['id']] ?? 0)?> articles</span>
    </a>
  <?php endforeach; ?>
</div>

<section class="section-head"><h2>Latest Articles</h2><a href="<?=base_url('search.php')?>">View all</a></section>
<div class="grid">
  <?php
  $s = $pdo->query("SELECT a.*,c.name category_name,c.slug category_slug FROM articles a JOIN categories c ON c.id=a.category_id WHERE a.status='published' ORDER BY a.published_at DESC LIMIT 12");
  foreach ($s as $a):
  ?>
    <article class="card reveal">
      <a class="thumb" href="<?=article_url($a['slug'])?>">
        <?php if (!empty($a['thumbnail_url'])): ?>
          <img src="<?=e($a['thumbnail_url'])?>" alt="<?=e($a['title'])?>" loading="lazy">
        <?php else: ?>
          <span class="thumb-fallback">INFOROVA</span>
        <?php endif; ?>
      </a>
      <div class="card-body">
        <a class="category" href="<?=category_url($a['category_slug'])?>"><?=e($a['category_name'])?></a>
        <h3><a href="<?=article_url($a['slug'])?>"><?=e($a['title'])?></a></h3>
        <p><?=e($a['excerpt'])?></p>
        <small><?=e(date('M d, Y', strtotime($a['published_at'])))?> &middot; <?=reading_time_minutes($a['content_html'])?> min read</small>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<section class="section-head"><h2>Resource Hubs</h2></section>
<div class="hub-cards">
  <a class="hub-card" href="<?=base_url('page.php?slug=study-resources-hub')?>">
    <span class="hub-card-icon">📚</span>
    <h3>Complete Study Resources Hub</h3>
    <p>Every exam preparation, note-taking and motivation guide, organized in one place.</p>
  </a>
  <a class="hub-card" href="<?=base_url('page.php?slug=sarkari-job-hub')?>">
    <span class="hub-card-icon">🏛️</span>
    <h3>Government Job Complete Guide</h3>
    <p>From reading a job notice to tracking your result, step by step.</p>
  </a>
  <a class="hub-card" href="<?=base_url('page.php?slug=career-scholarship-hub')?>">
    <span class="hub-card-icon">💼</span>
    <h3>Career &amp; Scholarship Hub</h3>
    <p>Scholarships, resumes, interviews and workplace skills together.</p>
  </a>
</div>

<section class="section-head"><h2>Why Read INFOROVA</h2></section>
<div class="feature-grid">
  <div class="feature"><span>✅</span><h3>Practical, Not Generic</h3><p>Every guide is written to be genuinely usable, with specific steps rather than vague advice.</p></div>
  <div class="feature"><span>🔄</span><h3>Regularly Updated</h3><p>New articles and updates are added across exam, career and scholarship categories.</p></div>
  <div class="feature"><span>🔍</span><h3>Clear and Organized</h3><p>Content is grouped into categories and resource hubs, so related guides are easy to find.</p></div>
  <div class="feature"><span>📖</span><h3>Written to Explain, Not Confuse</h3><p>Complex topics like normalization or pay scales are broken down in plain language.</p></div>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>
