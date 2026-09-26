<?php
require_once __DIR__.'/functions.php';
require_once __DIR__.'/content-builder.php';
require_once __DIR__.'/seed-expansion.php';
seedInforovaExpansion($pdo);

$navCategories = $pdo->query('SELECT name,slug,icon FROM categories ORDER BY name')->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($page_title??APP_NAME)?> | <?=e(APP_NAME)?></title>
<meta name="description" content="<?=e($page_desc??'Independent news, exam preparation and useful guides.')?>">
<link rel="canonical" href="<?=e($canonical??base_url(ltrim($_SERVER['REQUEST_URI']??'','/')))?>">
<link rel="preconnect" href="https://images.unsplash.com">
<link rel="stylesheet" href="<?=e(base_url('assets/css/style.css'))?>">
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1690994340482950" crossorigin="anonymous"></script>
</head><body>
<header class="site-header">
  <div class="wrap nav">
    <a class="brand" href="<?=base_url()?>">INFOROVA</a>

    <button type="button" id="navToggle" class="nav-toggle" aria-label="Menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <nav id="siteNav">
      <a href="<?=base_url()?>">Home</a>

      <div class="nav-dropdown">
        <button type="button" class="nav-dropdown-btn">Categories <i>▾</i></button>
        <div class="nav-dropdown-menu">
          <?php foreach ($navCategories as $c): ?>
            <a href="<?=category_url($c['slug'])?>"><?=category_icon($c)?> <?=e($c['name'])?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="nav-dropdown">
        <button type="button" class="nav-dropdown-btn">Resource Hubs <i>▾</i></button>
        <div class="nav-dropdown-menu">
          <a href="<?=base_url('page.php?slug=study-resources-hub')?>">📚 Study Resources Hub</a>
          <a href="<?=base_url('page.php?slug=sarkari-job-hub')?>">🏛️ Government Job Hub</a>
          <a href="<?=base_url('page.php?slug=career-scholarship-hub')?>">💼 Career &amp; Scholarship Hub</a>
        </div>
      </div>

      <a href="<?=base_url('page.php?slug=about')?>">About</a>
      <a href="<?=base_url('page.php?slug=contact')?>">Contact</a>
    </nav>

    <form class="search" action="<?=base_url('search.php')?>"><input name="q" placeholder="Search articles..." required><button aria-label="Search">🔎</button></form>
  </div>
</header>

<?php if (!empty($breadcrumbs)): ?>
<div class="breadcrumb-bar"><div class="wrap">
  <a href="<?=base_url()?>">Home</a>
  <?php foreach ($breadcrumbs as $bc): ?>
    <span class="sep">/</span>
    <?php if (!empty($bc['url'])): ?><a href="<?=e($bc['url'])?>"><?=e($bc['label'])?></a><?php else: ?><span><?=e($bc['label'])?></span><?php endif; ?>
  <?php endforeach; ?>
</div></div>
<?php endif; ?>

<main class="wrap">
