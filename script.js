document.addEventListener('DOMContentLoaded', function () {

    // --- Animasi Elemen saat Muncul di Layar ---
    const animatedElements = document.querySelectorAll('.animated-element');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                // Optional: unobserve setelah animasi berjalan sekali
                // observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1 // Memicu saat 10% elemen terlihat
    });

    animatedElements.forEach(el => {
        observer.observe(el);
    });

    // --- Animasi Penghitung Angka (Counter) ---
    const statsSection = document.querySelector('.info');
    if (statsSection) {
        const animateCounter = (element, target) => {
            const duration = 2000; // 2 detik
            const startTime = performance.now();
            
            const step = (currentTime) => {
                const elapsedTime = currentTime - startTime;
                const progress = Math.min(elapsedTime / duration, 1);
                const currentValue = Math.floor(progress * target);

                element.textContent = currentValue.toLocaleString('id-ID');

                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    element.textContent = target.toLocaleString('id-ID');
                }
            };
            requestAnimationFrame(step);
        };
        
        const statsObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumbers = entry.target.querySelectorAll('.stat-number');
                    statNumbers.forEach(stat => {
                        const text = stat.textContent.trim();
                        if (text.includes('/')) return; // Abaikan "24/7"

                        const target = parseInt(text.replace(/,|\./g, ''));
                        if (!isNaN(target)) {
                            animateCounter(stat, target);
                        }
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        statsObserver.observe(statsSection);
    }

    // --- Bootstrap 5 ScrollSpy Initialization ---
    // Pastikan tag <body> memiliki atribut: data-bs-spy="scroll" data-bs-target="#mainNavbar"
    // Dan pastikan #mainNavbar memiliki .nav-link dengan href yang cocok dengan ID section
    const scrollSpy = new bootstrap.ScrollSpy(document.body, {
        target: '#mainNavbar',
        offset: 100 // Sesuaikan offset jika perlu
    });

});