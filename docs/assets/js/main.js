// Автопрокрутка баннеров на главной (без JS баннеры остаются доступными ссылками).
(function () {
    var slider = document.querySelector('.slider');
    if (!slider || document.body.classList.contains('vi')) return;
    var slides = slider.querySelectorAll('.slide');
    var dots = slider.querySelectorAll('.slider-dots button');
    var current = 0, timer;
    function show(i) {
        slides[current].classList.remove('active'); dots[current].classList.remove('active');
        current = (i + slides.length) % slides.length;
        slides[current].classList.add('active'); dots[current].classList.add('active');
    }
    function start() { timer = setInterval(function () { show(current + 1); }, 6000); }
    dots.forEach(function (d, i) { d.addEventListener('click', function () { clearInterval(timer); show(i); start(); }); });
    slider.addEventListener('mouseenter', function () { clearInterval(timer); });
    slider.addEventListener('mouseleave', start);
    start();
})();
