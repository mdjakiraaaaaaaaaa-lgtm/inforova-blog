</main>
<footer>
  <div class="wrap footer-grid">
    <div class="footer-about">
      <strong>INFOROVA</strong>
      <p>Independent, in-depth articles on exam preparation, government jobs, scholarships and career skills.</p>
    </div>

    <div>
      <h4>Categories</h4>
      <?php foreach ($pdo->query('SELECT name,slug FROM categories ORDER BY name') as $c): ?>
        <a href="<?=category_url($c['slug'])?>"><?=e($c['name'])?></a>
      <?php endforeach; ?>
    </div>

    <div>
      <h4>Resource Hubs</h4>
      <a href="<?=base_url('page.php?slug=study-resources-hub')?>">Study Resources Hub</a>
      <a href="<?=base_url('page.php?slug=sarkari-job-hub')?>">Government Job Hub</a>
      <a href="<?=base_url('page.php?slug=career-scholarship-hub')?>">Career &amp; Scholarship Hub</a>
    </div>

    <div>
      <h4>Site</h4>
      <a href="<?=base_url('page.php?slug=about')?>">About</a>
      <a href="<?=base_url('page.php?slug=faq')?>">FAQ</a>
      <a href="<?=base_url('page.php?slug=editorial-policy')?>">Editorial Policy</a>
      <a href="<?=base_url('page.php?slug=advertise')?>">Advertise</a>
      <a href="<?=base_url('page.php?slug=contact')?>">Contact</a>
    </div>

    <div>
      <h4>Legal</h4>
      <a href="<?=base_url('page.php?slug=privacy-policy')?>">Privacy Policy</a>
      <a href="<?=base_url('page.php?slug=terms')?>">Terms of Use</a>
      <a href="<?=base_url('page.php?slug=disclaimer')?>">Disclaimer</a>
      <a href="<?=base_url('page.php?slug=cookie-policy')?>">Cookie Policy</a>
      <a href="<?=base_url('page.php?slug=accessibility')?>">Accessibility</a>
    </div>
  </div>

  <div class="copyright">INFOROVA &copy; <?=date('Y')?>. All rights reserved.</div>
</footer>
<script src="<?=base_url('assets/js/app.js')?>"></script>
</body></html>
