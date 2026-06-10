  <footer class="relative bg-slate-100 pt-16 pb-8 px-6 border-t-4 border-cyan-600 z-10">
    <div class="max-w-7xl mx-auto text-center md:text-left">
      <div class="grid md:grid-cols-4 gap-12 mb-12">
        <div class="col-span-1 md:col-span-2">
          <div class="font-robot font-bold text-3xl tracking-widest text-slate-900 mb-6 flex items-center">
            <a href="index.php" class="flex items-center gap-2">
                <?php if(isset($logo_path) && $logo_path): ?>
                    <img src="uploads/<?= htmlspecialchars($logo_path) ?>" alt="Logo" class="h-8 grayscale hover:grayscale-0 transition-all">
                <?php else: ?>
                    ⟨<?= htmlspecialchars($site_name ?? 'INFRIZO') ?>⟩
                <?php endif; ?>
            </a>
          </div>
          <p class="text-slate-600 text-xs max-w-sm mx-auto md:mx-0 leading-relaxed uppercase tracking-wider font-bold">Automated IT infrastructure. Running protocol v2.0.4.</p>
        </div>
      </div>
      <div class="pt-6 border-t border-slate-300 text-[10px] tracking-[0.2em] text-slate-500 font-bold uppercase">
        © <?= date('Y') ?> <?= htmlspecialchars($site_name ?? 'INFRIZO') ?> SYS. ALL RIGHTS RESERVED.
      </div>
    </div>
  </footer>
</body>
</html>