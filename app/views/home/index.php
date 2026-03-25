<?php include 'app/views/shares/header.php'; ?>

<div class="showcase-shell reveal-up">
    <div class="showcase-copy text-center mb-5">
        <h1 class="section-title">Khong gian trung bay san pham theo phong cach showroom hien dai</h1>
        <p class="section-subtitle mx-auto">San pham duoc dua vao trung tam, ban co the dieu huong bằng hai nut hai ben va mo ngay trang chi tiet san pham da co san trong he thong.</p>
    </div>

    <?php if (!empty($featuredProducts)): ?>
        <div class="showcase-stage">
            <button class="showcase-nav showcase-nav-left" id="showcase-prev" aria-label="San pham truoc">
                <i class="fa-solid fa-arrow-left-long"></i>
            </button>

            <div class="showcase-center">
                <div class="showcase-card-frame">
                    <?php foreach ($featuredProducts as $index => $product): ?>
                        <article
                            class="showcase-card <?php echo $index === 0 ? 'is-active' : ''; ?>"
                            data-showcase-card
                            data-index="<?php echo $index; ?>"
                        >
                            <a href="/webbanhang/ProductController/show/<?php echo $product->Id; ?>" class="showcase-card-link">
                                <div class="showcase-card-media">
                                    <?php if (!empty($product->Image)): ?>
                                        <img src="/webbanhang/<?php echo htmlspecialchars($product->Image); ?>" alt="<?php echo htmlspecialchars($product->Name); ?>">
                                    <?php else: ?>
                                        <div class="showcase-media-placeholder">
                                            <i class="fa-solid fa-box-open"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="showcase-card-body">
                                    <div class="showcase-chip">
                                        <?php echo htmlspecialchars($product->category_name ?? 'San pham noi bat'); ?>
                                    </div>
                                    <h2><?php echo htmlspecialchars($product->Name); ?></h2>
                                    <p><?php echo htmlspecialchars($product->Description); ?></p>
                                    <div class="showcase-meta">
                                        <strong><?php echo number_format($product->Price); ?> VND</strong>
                                        <span>Xem chi tiet</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="showcase-dots">
                    <?php foreach ($featuredProducts as $index => $product): ?>
                        <button
                            class="showcase-dot <?php echo $index === 0 ? 'is-active' : ''; ?>"
                            data-showcase-dot
                            data-index="<?php echo $index; ?>"
                            aria-label="Chon san pham <?php echo $index + 1; ?>"
                        ></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <button class="showcase-nav showcase-nav-right" id="showcase-next" aria-label="San pham tiep theo">
                <i class="fa-solid fa-arrow-right-long"></i>
            </button>
        </div>
    <?php else: ?>
        <div class="surface-card empty-state">
            <i class="fa-solid fa-box-open"></i>
            <h3 class="h5 font-weight-bold">Chua co san pham de hien thi</h3>
            <p class="mb-0">Hay them san pham moi de bat dau khu vuc trinh chieu tai trang chu.</p>
        </div>
    <?php endif; ?>

</div>

<style>
    .showcase-shell {
        position: relative;
        padding: 0 0 8px;
    }

    .showcase-stage {
        display: grid;
        grid-template-columns: 92px minmax(0, 1fr) 92px;
        gap: 18px;
        align-items: center;
        min-height: 540px;
        margin-top: -42px;
    }

    .showcase-nav {
        width: 82px;
        height: 82px;
        border-radius: 28px;
        border: 1px solid rgba(255,255,255,0.82);
        background: rgba(255,255,255,0.88);
        color: var(--primary-color);
        box-shadow: var(--shadow-soft);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.55rem;
    }

    .showcase-nav:hover {
        transform: translateY(-4px) scale(1.03);
        background: #fff7ed;
        color: var(--accent-color);
    }

    .showcase-center {
        position: relative;
        min-height: 540px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .showcase-card-frame {
        position: relative;
        width: min(100%, 820px);
        height: 460px;
        margin: 0 auto;
        perspective: 1600px;
    }

    .showcase-card {
        position: absolute;
        inset: 0;
        opacity: 0;
        pointer-events: none;
        transform: translateX(0) scale(0.8) rotateY(0deg);
        transform-origin: center center;
        transition: transform 0.7s cubic-bezier(.22,.61,.36,1), opacity 0.45s ease, filter 0.45s ease;
        backface-visibility: hidden;
        will-change: transform, opacity;
        filter: blur(0px);
        z-index: 1;
    }

    .showcase-card.is-active {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(0) scale(1) rotateY(0deg);
        filter: blur(0px);
        z-index: 4;
    }

    .showcase-card.is-prev {
        opacity: 0.42;
        pointer-events: auto;
        transform: translateX(-250px) scale(0.62) rotateY(24deg);
        filter: blur(3px) saturate(0.85);
        z-index: 2;
    }

    .showcase-card.is-next {
        opacity: 0.42;
        pointer-events: auto;
        transform: translateX(250px) scale(0.62) rotateY(-24deg);
        filter: blur(3px) saturate(0.85);
        z-index: 2;
    }

    .showcase-card.is-hidden-left {
        opacity: 0;
        transform: translateX(-360px) scale(0.48) rotateY(34deg);
        filter: blur(8px);
        z-index: 1;
    }

    .showcase-card.is-hidden-right {
        opacity: 0;
        transform: translateX(360px) scale(0.48) rotateY(-34deg);
        filter: blur(8px);
        z-index: 1;
    }

    .showcase-card.is-orbit-in-next,
    .showcase-card.is-orbit-in-prev,
    .showcase-card.is-orbit-out-next,
    .showcase-card.is-orbit-out-prev {
        opacity: 1;
    }

    .showcase-card.is-orbit-in-next {
        animation: orbitInNext 0.82s cubic-bezier(.22,.61,.36,1) forwards;
        z-index: 5;
    }

    .showcase-card.is-orbit-out-next {
        animation: orbitOutNext 0.82s cubic-bezier(.22,.61,.36,1) forwards;
        z-index: 3;
    }

    .showcase-card.is-orbit-in-prev {
        animation: orbitInPrev 0.82s cubic-bezier(.22,.61,.36,1) forwards;
        z-index: 5;
    }

    .showcase-card.is-orbit-out-prev {
        animation: orbitOutPrev 0.82s cubic-bezier(.22,.61,.36,1) forwards;
        z-index: 3;
    }

    .showcase-card-link {
        height: 100%;
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
        gap: 26px;
        padding: 22px;
        border-radius: 32px;
        text-decoration: none !important;
        color: inherit;
        background:
            linear-gradient(145deg, rgba(15,23,42,0.96), rgba(30,41,59,0.92) 45%, rgba(255,248,242,0.90) 100%);
        box-shadow: 0 28px 80px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        position: relative;
    }

    .showcase-card-link::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top left, rgba(6, 182, 212, 0.20), transparent 28%),
            radial-gradient(circle at right center, rgba(249, 115, 22, 0.18), transparent 34%);
        pointer-events: none;
    }

    .showcase-card-media,
    .showcase-card-body {
        position: relative;
        z-index: 1;
    }

    .showcase-card-media {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 30px;
        background: rgba(255,255,255,0.10);
        backdrop-filter: blur(18px);
        overflow: hidden;
        min-height: 100%;
    }

    .showcase-card-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scale(1.02);
        transition: transform 0.7s ease;
    }

    .showcase-card-link:hover .showcase-card-media img {
        transform: scale(1.08);
    }

    .showcase-media-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.8);
        font-size: 3rem;
    }

    .showcase-card-body {
        padding: 8px 8px 8px 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: #fff;
    }

    .showcase-chip {
        display: inline-flex;
        align-items: center;
        align-self: flex-start;
        margin-bottom: 18px;
        padding: 9px 14px;
        border-radius: 999px;
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.16);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .showcase-card-body h2 {
        font-size: clamp(1.7rem, 2.6vw, 2.6rem);
        font-weight: 800;
        line-height: 1.02;
        letter-spacing: -0.05em;
        margin-bottom: 12px;
    }

    .showcase-card-body p {
        color: rgba(255,255,255,0.74);
        font-size: 0.94rem;
        line-height: 1.7;
        max-width: 360px;
        margin-bottom: 20px;
    }

    .showcase-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid rgba(255,255,255,0.14);
    }

    .showcase-meta strong {
        font-size: 1.2rem;
        color: #fff7ed;
    }

    .showcase-meta span {
        color: rgba(255,255,255,0.78);
        font-weight: 700;
    }

    .showcase-dots {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 26px;
    }

    .showcase-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        border: 0;
        background: rgba(100, 116, 139, 0.28);
    }

    .showcase-dot.is-active {
        width: 34px;
        background: linear-gradient(135deg, var(--accent-color), #fb923c);
    }

    @keyframes orbitInNext {
        0% {
            opacity: 0.18;
            transform: translateX(-250px) scale(0.62) rotateY(24deg);
            filter: blur(3px);
        }
        55% {
            opacity: 1;
            transform: translateX(-36px) scale(0.93) rotateY(10deg);
            filter: blur(0.6px);
        }
        100% {
            opacity: 1;
            transform: translateX(0) scale(1) rotateY(0deg);
            filter: blur(0px);
        }
    }

    @keyframes orbitOutNext {
        0% {
            opacity: 1;
            transform: translateX(0) scale(1) rotateY(0deg);
            filter: blur(0px);
        }
        45% {
            opacity: 0.85;
            transform: translateX(96px) scale(0.86) rotateY(-12deg);
            filter: blur(1px);
        }
        100% {
            opacity: 0.42;
            transform: translateX(250px) scale(0.62) rotateY(-24deg);
            filter: blur(3px);
        }
    }

    @keyframes orbitInPrev {
        0% {
            opacity: 0.18;
            transform: translateX(250px) scale(0.62) rotateY(-24deg);
            filter: blur(3px);
        }
        55% {
            opacity: 1;
            transform: translateX(36px) scale(0.93) rotateY(-10deg);
            filter: blur(0.6px);
        }
        100% {
            opacity: 1;
            transform: translateX(0) scale(1) rotateY(0deg);
            filter: blur(0px);
        }
    }

    @keyframes orbitOutPrev {
        0% {
            opacity: 1;
            transform: translateX(0) scale(1) rotateY(0deg);
            filter: blur(0px);
        }
        45% {
            opacity: 0.85;
            transform: translateX(-96px) scale(0.86) rotateY(12deg);
            filter: blur(1px);
        }
        100% {
            opacity: 0.42;
            transform: translateX(-250px) scale(0.62) rotateY(24deg);
            filter: blur(3px);
        }
    }

    @media (max-width: 991px) {
        .showcase-stage {
            grid-template-columns: 64px minmax(0, 1fr) 64px;
            min-height: auto;
        }

        .showcase-card-frame {
            height: auto;
            min-height: 520px;
        }

        .showcase-card.is-prev {
            transform: translateX(-170px) scale(0.56) rotateY(20deg);
        }

        .showcase-card.is-next {
            transform: translateX(170px) scale(0.56) rotateY(-20deg);
        }

        .showcase-card-link {
            grid-template-columns: 1fr;
        }

    }

    @media (max-width: 767px) {
        .showcase-stage {
            grid-template-columns: 1fr;
        }

        .showcase-nav {
            width: 100%;
            height: 56px;
            border-radius: 18px;
        }

        .showcase-nav-left {
            order: 2;
        }

        .showcase-center {
            order: 1;
            min-height: auto;
        }

        .showcase-nav-right {
            order: 3;
        }

        .showcase-card-frame {
            min-height: 500px;
        }

        .showcase-card.is-prev,
        .showcase-card.is-next,
        .showcase-card.is-hidden-left,
        .showcase-card.is-hidden-right {
            opacity: 0;
            pointer-events: none;
        }
    }
</style>

<?php if (!empty($featuredProducts)): ?>
    <script>
        (function () {
            const cards = Array.from(document.querySelectorAll('[data-showcase-card]'));
            const dots = Array.from(document.querySelectorAll('[data-showcase-dot]'));
            const prevButton = document.getElementById('showcase-prev');
            const nextButton = document.getElementById('showcase-next');

            if (!cards.length || !prevButton || !nextButton) return;

            let currentIndex = 0;
            let autoplayId = null;
            let animationId = null;

            function syncDots() {
                dots.forEach((dot, dotIndex) => {
                    dot.classList.toggle('is-active', dotIndex === currentIndex);
                });
            }

            function applyLayout(activeIndex) {
                cards.forEach((card, index) => {
                    clearCardStates(card);

                    const prevIndex = (activeIndex - 1 + cards.length) % cards.length;
                    const nextIndex = (activeIndex + 1) % cards.length;

                    if (index === activeIndex) {
                        card.classList.add('is-active');
                    } else if (index === prevIndex) {
                        card.classList.add('is-prev');
                    } else if (index === nextIndex) {
                        card.classList.add('is-next');
                    } else if (index < activeIndex) {
                        card.classList.add('is-hidden-left');
                    } else {
                        card.classList.add('is-hidden-right');
                    }
                });
            }

            function clearCardStates(card) {
                card.classList.remove(
                    'is-active',
                    'is-prev',
                    'is-next',
                    'is-hidden-left',
                    'is-hidden-right',
                    'is-orbit-in-next',
                    'is-orbit-in-prev',
                    'is-orbit-out-next',
                    'is-orbit-out-prev'
                );
            }

            function render(index, direction = 'next') {
                const nextIndex = (index + cards.length) % cards.length;
                const currentCard = cards[currentIndex];
                const nextCard = cards[nextIndex];

                if (!currentCard || !nextCard || nextIndex === currentIndex) {
                    currentIndex = nextIndex;
                    applyLayout(currentIndex);
                    syncDots();
                    return;
                }

                if (animationId) {
                    window.clearTimeout(animationId);
                    animationId = null;
                }

                cards.forEach((card) => clearCardStates(card));
                applyLayout(currentIndex);

                if (direction === 'next') {
                    currentCard.classList.add('is-orbit-out-next');
                    nextCard.classList.add('is-orbit-in-next');
                } else {
                    currentCard.classList.add('is-orbit-out-prev');
                    nextCard.classList.add('is-orbit-in-prev');
                }

                void nextCard.offsetWidth;

                currentCard.classList.remove('is-active');
                nextCard.classList.add('is-active');
                nextCard.classList.remove('is-orbit-out-next', 'is-orbit-out-prev');
                currentCard.classList.remove('is-orbit-in-next', 'is-orbit-in-prev');

                animationId = window.setTimeout(() => {
                    applyLayout(nextIndex);
                }, 650);

                currentIndex = nextIndex;
                syncDots();
            }

            function startAutoplay() {
                stopAutoplay();
                autoplayId = window.setInterval(() => render(currentIndex + 1), 4500);
            }

            function stopAutoplay() {
                if (autoplayId) {
                    window.clearInterval(autoplayId);
                    autoplayId = null;
                }
            }

            prevButton.addEventListener('click', function () {
                render(currentIndex - 1, 'prev');
                startAutoplay();
            });

            nextButton.addEventListener('click', function () {
                render(currentIndex + 1, 'next');
                startAutoplay();
            });

            dots.forEach((dot) => {
                dot.addEventListener('click', function () {
                    const targetIndex = Number(this.dataset.index || 0);
                    const direction = targetIndex < currentIndex ? 'prev' : 'next';
                    render(targetIndex, direction);
                    startAutoplay();
                });
            });

            cards.forEach((card) => {
                card.addEventListener('mouseenter', stopAutoplay);
                card.addEventListener('mouseleave', startAutoplay);
            });

            applyLayout(0);
            render(0, 'next');
            startAutoplay();
        })();
    </script>
<?php endif; ?>

<?php include 'app/views/shares/footer.php'; ?>
