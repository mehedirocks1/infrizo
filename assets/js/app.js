document.addEventListener("DOMContentLoaded", () => {
    // Scroll Effect for Navbar
    const nav = document.getElementById('main-nav');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        nav.classList.add('bg-white/90', 'backdrop-blur-md', 'border-b', 'border-slate-200', 'py-4', 'shadow-sm');
        nav.classList.remove('bg-transparent', 'border-transparent', 'py-6');
      } else {
        nav.classList.remove('bg-white/90', 'backdrop-blur-md', 'border-b', 'border-slate-200', 'py-4', 'shadow-sm');
        nav.classList.add('bg-transparent', 'border-transparent', 'py-6');
      }
    });

    // Mobile Menu Toggle Logic
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('mobile-menu-active');
        mobileMenu.classList.toggle('hidden');
        menuBtn.textContent = mobileMenu.classList.contains('hidden') ? '[≡]' : '[X]';
        });

        // Close menu when a link is clicked
        document.querySelectorAll('.mobile-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('mobile-menu-active');
                mobileMenu.classList.add('hidden');
                menuBtn.textContent = '[≡]';
            });
        });
    }
});