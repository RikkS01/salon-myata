/* Демонстрационный режим сайта «Мята» для статического хостинга (GitHub Pages).
   Функции, которым в полной версии нужен PHP и MySQL, выполняются в браузере:
   данные хранятся в localStorage посетителя. Исходные данные – window.DEMO_SEED. */
(function () {
    'use strict';
    var PREFIX = '/salon-myata';
    var DB_KEY = 'myata_db_v1', UID_KEY = 'myata_uid', VI_KEY = 'myata_vi', FLASH_KEY = 'myata_flash';
    var ROLES = {admin: 'Администратор сайта', manager: 'Администратор салона', client: 'Клиент'};
    var STATUSES = {'new': 'Ожидает подтверждения', confirmed: 'Подтверждена', done: 'Выполнена', cancelled: 'Отменена'};
    var MSG_STATUSES = {'new': 'новое', answered: 'отвечено', closed: 'закрыто'};
    var TIMES = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];

    function store(key, value) {
        try {
            if (value === undefined) { return window.localStorage.getItem(key); }
            if (value === null) { window.localStorage.removeItem(key); } else { window.localStorage.setItem(key, value); }
        } catch (e) { /* хранилище недоступно – работаем в памяти */ }
        return null;
    }
    var memoryDb = null;
    function db() {
        if (memoryDb) { return memoryDb; }
        var raw = store(DB_KEY);
        memoryDb = raw ? JSON.parse(raw) : JSON.parse(JSON.stringify(window.DEMO_SEED));
        return memoryDb;
    }
    function save() { store(DB_KEY, JSON.stringify(db())); }
    function nextId(list) { return list.reduce(function (m, x) { return Math.max(m, x.id); }, 0) + 1; }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
        });
    }
    function url(p) { return PREFIX + '/' + (p || ''); }
    function money(v) { return v == null ? '' : String(v).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽'; }
    function ruDate(d) {
        var m = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября',
            'октября', 'ноября', 'декабря'];
        var p = String(d).slice(0, 10).split('-');
        return parseInt(p[2], 10) + ' ' + m[parseInt(p[1], 10) - 1] + ' ' + p[0];
    }
    function today(offset) {
        var d = new Date(); d.setDate(d.getDate() + (offset || 0));
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function nowStr() { var d = new Date(); return today() + ' ' + d.toTimeString().slice(0, 8); }
    function param(name) { return new URLSearchParams(window.location.search).get(name); }
    function user() {
        var id = parseInt(store(UID_KEY), 10);
        return db().users.filter(function (u) { return u.id === id && u.is_active; })[0] || null;
    }
    function article(id) { return db().articles.filter(function (a) { return a.id === id; })[0]; }
    function articleUrl(a) { return url('article-' + a.section + '-' + a.slug + '.html'); }
    function flash(text, type) {
        var list = JSON.parse(store(FLASH_KEY) || '[]');
        list.push([type || 'ok', text]);
        store(FLASH_KEY, JSON.stringify(list));
    }
    function go(p) { window.location.href = url(p); }
    function alertBox(errors) {
        return '<div class="alert alert-error" role="alert">' + errors.map(esc).join('<br>') + '</div>';
    }
    function main() { return document.getElementById('main'); }
    function showErrors(form, errors) {
        var old = form.parentNode.querySelector('.alert-error');
        if (old) { old.parentNode.removeChild(old); }
        if (errors.length) { form.insertAdjacentHTML('beforebegin', alertBox(errors)); window.scrollTo(0, 0); }
        return errors.length === 0;
    }
    function v(form, name) { var el = form.elements[name]; return el ? String(el.value).trim() : ''; }

    /* ---------------- версия для слабовидящих ---------------- */
    function viSettings() {
        var s = JSON.parse(store(VI_KEY) || '{}');
        return {on: s.on === '1', size: s.size || '1', scheme: s.scheme || 'bw', img: s.img !== '0', space: s.space === '1', raw: s};
    }
    function viLink(p, val, label, active) {
        var back = encodeURIComponent(window.location.pathname + window.location.search + window.location.hash);
        return '<a href="' + url('vi.html?' + p + '=' + val + '&back=' + back) + '" class="vi-btn' + (active ? ' active' : '') +
            '"' + (active ? ' aria-current="true"' : '') + '>' + label + '</a>';
    }
    function applyVi() {
        var s = viSettings();
        if (!s.on) { return; }
        var css = document.getElementById('vi-css');
        if (css) { css.media = 'all'; }
        var b = document.body;
        b.classList.add('vi', 'vi-size-' + s.size, 'vi-' + s.scheme);
        if (!s.img) { b.classList.add('vi-noimg'); }
        if (s.space) { b.classList.add('vi-space'); }
        var btn = document.querySelector('.btn-vi');
        if (btn) { btn.parentNode.removeChild(btn); }
        var panel = '<div class="vi-panel" role="region" aria-label="Настройки версии для слабовидящих"><div class="wrap vi-panel-inner">' +
            '<span class="vi-group">Размер шрифта: ' + viLink('vi_size', '1', 'A', s.size === '1') + viLink('vi_size', '2', 'A+', s.size === '2') +
            viLink('vi_size', '3', 'A++', s.size === '3') + '</span>' +
            '<span class="vi-group">Цвет: ' + viLink('vi_scheme', 'bw', 'Ч/Б', s.scheme === 'bw') + viLink('vi_scheme', 'wb', 'Б/Ч', s.scheme === 'wb') +
            viLink('vi_scheme', 'blue', 'Синий', s.scheme === 'blue') + viLink('vi_scheme', 'brown', 'Бежевый', s.scheme === 'brown') + '</span>' +
            '<span class="vi-group">Изображения: ' + viLink('vi_img', '1', 'Вкл.', s.img) + viLink('vi_img', '0', 'Выкл.', !s.img) + '</span>' +
            '<span class="vi-group">Интервал: ' + viLink('vi_space', '0', 'Обычный', !s.space) + viLink('vi_space', '1', 'Большой', s.space) + '</span>' +
            viLink('vi', '0', 'Обычная версия сайта', false) + '</div></div>';
        var skip = document.querySelector('.skip');
        (skip || b.firstChild).insertAdjacentHTML('afterend', panel);
    }
    function pageVi() {
        var s = viSettings().raw;
        var allowed = {vi: ['0', '1'], vi_size: ['1', '2', '3'], vi_scheme: ['bw', 'wb', 'blue', 'brown'], vi_img: ['0', '1'], vi_space: ['0', '1']};
        var names = {vi: 'on', vi_size: 'size', vi_scheme: 'scheme', vi_img: 'img', vi_space: 'space'};
        Object.keys(allowed).forEach(function (k) {
            var val = param(k);
            if (val !== null && allowed[k].indexOf(val) >= 0) { s[names[k]] = val; }
        });
        store(VI_KEY, JSON.stringify(s));
        var back = param('back') || url();
        if (back.charAt(0) !== '/' || back.slice(0, 2) === '//') { back = url(); }
        window.location.replace(back);
    }

    /* ---------------- шапка и сообщения ---------------- */
    function header() {
        var u = user(), box = document.querySelector('.header-actions');
        if (!box) { return; }
        var links = box.querySelectorAll('a:not(.btn-vi)');
        Array.prototype.forEach.call(links, function (a) { a.parentNode.removeChild(a); });
        Array.prototype.slice.call(box.childNodes).forEach(function (n) {
            if (n.nodeType === 3 && n.textContent.trim() === '·') { box.removeChild(n); }
        });
        box.insertAdjacentHTML('beforeend', u ?
            '<a href="' + url(u.role === 'client' ? 'cabinet.html' : 'admin.html') + '">' + esc(u.login) + ' (' + ROLES[u.role] + ')</a> · ' +
            '<a href="' + url('logout.html') + '">Выйти</a>' :
            '<a href="' + url('login.html') + '">Вход</a> · <a href="' + url('register.html') + '">Регистрация</a>');
    }
    function showFlash() {
        var list = JSON.parse(store(FLASH_KEY) || '[]');
        if (!list.length) { return; }
        store(FLASH_KEY, null);
        var html = list.map(function (f) {
            return '<div class="alert alert-' + esc(f[0]) + '" role="status">' + esc(f[1]) + '</div>';
        }).join('');
        var crumbs = main().querySelector('.crumbs');
        if (crumbs) { crumbs.insertAdjacentHTML('afterend', html); } else { main().insertAdjacentHTML('afterbegin', html); }
    }

    /* ---------------- вход, регистрация, выход ---------------- */
    function pageLogin(form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var login = v(form, 'login'), pass = form.elements.password.value;
            var u = db().users.filter(function (x) { return (x.login === login || x.email === login) && x.is_active; })[0];
            if (!u || u.password !== pass) { showErrors(form, ['Неверный логин или пароль.']); return; }
            store(UID_KEY, String(u.id));
            flash('Здравствуйте, ' + u.name + '!');
            var next = param('next');
            if (next && next.charAt(0) === '/' && next.slice(0, 2) !== '//') { window.location.href = next; return; }
            go(u.role === 'client' ? 'cabinet.html' : 'admin.html');
        });
    }
    function pageRegister(form) {
        if (user()) { go('cabinet.html'); return; }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var d = db(), errors = [], login = v(form, 'login'), email = v(form, 'email'), phone = v(form, 'phone');
            var pass = form.elements.password.value;
            if (!/^[a-zA-Z0-9_.-]{3,40}$/.test(login)) { errors.push('Логин: 3–40 латинских букв, цифр или знаков _ . -'); }
            if (v(form, 'name').length < 2) { errors.push('Укажите имя.'); }
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { errors.push('Укажите корректный e-mail.'); }
            if (phone && !/^\+?[\d\s()\-]{10,20}$/.test(phone)) { errors.push('Телефон указан неверно.'); }
            if (pass.length < 8 || !/\d/.test(pass) || !/[a-zа-яё]/i.test(pass)) { errors.push('Пароль: не менее 8 символов, буквы и цифры.'); }
            if (pass !== form.elements.password2.value) { errors.push('Пароли не совпадают.'); }
            if (!form.elements.agree.checked) { errors.push('Необходимо согласие на обработку персональных данных.'); }
            if (!errors.length && d.users.some(function (u) { return u.login === login || u.email === email; })) {
                errors.push('Пользователь с таким логином или e-mail уже существует.');
            }
            if (!showErrors(form, errors)) { return; }
            var u = {id: nextId(d.users), login: login, name: v(form, 'name'), email: email, phone: phone,
                role: 'client', is_active: 1, password: pass};
            d.users.push(u); save();
            store(UID_KEY, String(u.id));
            flash('Учётная запись создана. Добро пожаловать!');
            go('cabinet.html');
        });
    }
    function pageLogout() {
        store(UID_KEY, null);
        flash('Вы вышли из учётной записи.', 'info');
        go('');
    }

    /* ---------------- запись, сообщения, поиск ---------------- */
    function pageBooking(form) {
        var u = user();
        if (u) {
            form.elements.name.value = u.name; form.elements.phone.value = u.phone || '';
            var lead = main().querySelector('.lead a');
            if (lead) { lead.parentNode.removeChild(lead.nextSibling); lead.parentNode.removeChild(lead); }
        }
        if (param('service')) { form.elements.service_id.value = param('service'); }
        if (param('master')) { form.elements.master_id.value = param('master'); }
        form.elements.visit_date.min = today();
        form.elements.visit_date.value = today(1);
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var d = db(), errors = [];
            var service = parseInt(v(form, 'service_id'), 10), master = v(form, 'master_id') ? parseInt(v(form, 'master_id'), 10) : null;
            var date = v(form, 'visit_date'), time = v(form, 'visit_time');
            if (v(form, 'name').length < 2) { errors.push('Укажите имя.'); }
            if (!/^\+?[\d\s()\-]{10,20}$/.test(v(form, 'phone'))) { errors.push('Укажите телефон в формате +7 (900) 123-45-67.'); }
            if (!article(service) || article(service).section !== 'services') { errors.push('Выберите услугу.'); }
            if (!date || date < today() || date > today(60)) { errors.push('Дата визита – от сегодняшнего дня до 60 дней вперёд.'); }
            if (TIMES.indexOf(time) < 0) { errors.push('Выберите время из списка.'); }
            if (!errors.length && master && d.bookings.some(function (b) {
                return b.master_id === master && b.visit_date === date && b.visit_time === time && (b.status === 'new' || b.status === 'confirmed');
            })) { errors.push('Это время у выбранного мастера уже занято. Выберите другое.'); }
            if (!showErrors(form, errors)) { return; }
            d.bookings.push({id: nextId(d.bookings), user_id: u ? u.id : null, name: v(form, 'name'), phone: v(form, 'phone'),
                service_id: service, master_id: master, visit_date: date, visit_time: time,
                comment: v(form, 'comment').slice(0, 500), status: 'new', created_at: nowStr()});
            save();
            flash('Заявка принята! Администратор подтвердит запись по телефону.' + (u ? ' Статус записи – в личном кабинете.' : ''));
            go(u ? 'cabinet.html' : 'booking.html');
        });
    }
    function pageContacts(form) {
        var u = user();
        if (u) { form.elements.name.value = u.name; form.elements.email.value = u.email; }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var errors = [];
            if (v(form, 'name').length < 2) { errors.push('Укажите имя.'); }
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v(form, 'email'))) { errors.push('Укажите корректный e-mail.'); }
            if (!v(form, 'subject')) { errors.push('Укажите тему сообщения.'); }
            if (v(form, 'body').length < 10) { errors.push('Сообщение должно содержать не менее 10 символов.'); }
            if (!showErrors(form, errors)) { return; }
            var d = db();
            d.messages.push({id: nextId(d.messages), user_id: u ? u.id : null, name: v(form, 'name'), email: v(form, 'email'),
                subject: v(form, 'subject').slice(0, 150), body: v(form, 'body'), reply: null, replied_at: null,
                status: 'new', created_at: nowStr()});
            save();
            flash(u ? 'Сообщение отправлено. Ответ появится в личном кабинете.' : 'Сообщение отправлено. Мы ответим на ваш e-mail.');
            go('contacts.html');
        });
    }
    function pageSearch() {
        var q = (param('q') || '').trim(), m = main();
        Array.prototype.forEach.call(document.querySelectorAll('input[name="q"]'), function (i) { i.value = q; });
        if (!q) { return; }
        var titles = {};
        db().sections.forEach(function (s) { titles[s.code] = s.title; });
        if (q.length < 2) { m.insertAdjacentHTML('beforeend', '<p>Введите не менее двух символов.</p>'); return; }
        var low = q.toLowerCase();
        var found = db().articles.filter(function (a) {
            return (a.title + ' ' + a.summary + ' ' + a.text).toLowerCase().indexOf(low) >= 0;
        }).sort(function (a, b) {
            return (b.title.toLowerCase().indexOf(low) >= 0) - (a.title.toLowerCase().indexOf(low) >= 0);
        });
        m.insertAdjacentHTML('beforeend', '<p>По запросу «' + esc(q) + '» найдено: ' + found.length + '.</p><ol class="search-results">' +
            found.map(function (a) {
                return '<li><a href="' + articleUrl(a) + '">' + esc(a.title) + '</a> <span class="tag">' + esc(titles[a.section]) +
                    '</span><p>' + esc(a.summary) + '</p></li>';
            }).join('') + '</ol>');
    }

    /* ---------------- личный кабинет ---------------- */
    function requireLogin() {
        var u = user();
        if (!u) {
            flash('Войдите на сайт, чтобы продолжить.', 'info');
            window.location.href = url('login.html?next=' + encodeURIComponent(window.location.pathname + window.location.hash));
        }
        return u;
    }
    function pageCabinet(app) {
        var u = requireLogin();
        if (!u) { return; }
        var d = db();
        var bookings = d.bookings.filter(function (b) { return b.user_id === u.id; })
            .sort(function (a, b) { return (b.visit_date + b.visit_time).localeCompare(a.visit_date + a.visit_time); });
        var msgs = d.messages.filter(function (x) { return x.user_id === u.id; }).reverse();
        app.innerHTML = '<h1>Личный кабинет</h1><p>Вы вошли как <strong>' + esc(u.login) + '</strong> (' + ROLES[u.role] + ').' +
            (u.role !== 'client' ? ' <a href="' + url('admin.html') + '">Перейти в панель управления</a>' : '') + '</p>' +
            '<section><h2>Мои записи</h2><table class="data"><thead><tr><th>Дата и время</th><th>Услуга</th><th>Мастер</th><th>Статус</th><th></th></tr></thead><tbody>' +
            bookings.map(function (b) {
                var s = article(b.service_id), m = b.master_id ? article(b.master_id) : null;
                return '<tr><td>' + ruDate(b.visit_date) + ', ' + esc(b.visit_time) + '</td><td>' + esc(s ? s.title : '') + '</td><td>' +
                    esc(m ? m.title : 'любой') + '</td><td><span class="status status-' + b.status + '">' + STATUSES[b.status] + '</span></td><td>' +
                    (b.status === 'new' ? '<button class="link" data-cancel="' + b.id + '">Отменить</button>' : '') + '</td></tr>';
            }).join('') + '</tbody></table><a class="btn" href="' + url('booking.html') + '">Новая запись</a></section>' +
            '<section class="two-cols"><div><h2>Сообщения</h2>' +
            msgs.map(function (x) {
                return '<div class="msg"><p class="meta">' + ruDate(x.created_at) + ' · <strong>' + esc(x.subject) + '</strong></p><p>' +
                    esc(x.body) + '</p>' + (x.reply ? '<div class="reply"><strong>Ответ салона:</strong> ' + esc(x.reply) + '</div>' :
                    '<p class="small">Ожидает ответа</p>') + '</div>';
            }).join('') +
            '<form class="form" id="msg-form"><label>Тема <input name="subject" required maxlength="150"></label>' +
            '<label>Сообщение <textarea name="body" rows="4" required minlength="10"></textarea></label>' +
            '<button class="btn" type="submit">Отправить сообщение</button></form></div>' +
            '<div><h2>Профиль</h2><form class="form" id="profile-form"><label>Имя <input name="name" value="' + esc(u.name) + '"></label>' +
            '<label>Телефон <input name="phone" value="' + esc(u.phone) + '"></label><label>E-mail <input value="' + esc(u.email) + '" disabled></label>' +
            '<button class="btn btn-light" type="submit">Сохранить</button></form></div></section>';
        Array.prototype.forEach.call(app.querySelectorAll('[data-cancel]'), function (btn) {
            btn.addEventListener('click', function () {
                d.bookings.forEach(function (b) { if (b.id === +btn.dataset.cancel && b.user_id === u.id && b.status === 'new') { b.status = 'cancelled'; } });
                save(); flash('Запись отменена.', 'info'); window.location.reload();
            });
        });
        app.querySelector('#msg-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var f = e.target;
            if (!v(f, 'subject') || v(f, 'body').length < 10) { showErrors(f, ['Заполните тему и текст сообщения (не менее 10 символов).']); return; }
            d.messages.push({id: nextId(d.messages), user_id: u.id, name: u.name, email: u.email, subject: v(f, 'subject'),
                body: v(f, 'body'), reply: null, replied_at: null, status: 'new', created_at: nowStr()});
            save(); flash('Сообщение отправлено администратору салона.'); window.location.reload();
        });
        app.querySelector('#profile-form').addEventListener('submit', function (e) {
            e.preventDefault();
            u.name = v(e.target, 'name') || u.name; u.phone = v(e.target, 'phone');
            save(); flash('Профиль сохранён.'); window.location.reload();
        });
    }

    /* ---------------- панель управления ---------------- */
    function pageAdmin(app) {
        var u = requireLogin();
        if (!u) { return; }
        if (u.role === 'client') {
            app.innerHTML = '<section class="page-404"><h1>Доступ запрещён (403)</h1><p>Вашей роли «' + ROLES[u.role] +
                '» недостаточно прав для этого раздела.</p><p><a class="btn" href="' + url() + '">На главную</a></p></section>';
            return;
        }
        var d = db(), tab = (window.location.hash || '#index').slice(1);
        var tabs = {index: 'Сводка', articles: 'Контент', bookings: 'Записи', messages: 'Сообщения', banners: 'Баннеры'};
        if (u.role === 'admin') { tabs.users = 'Пользователи'; }
        var nav = '<nav class="admin-nav" aria-label="Панель управления">' + Object.keys(tabs).map(function (k) {
            return '<a href="' + url('admin.html#' + k) + '"' + (k === tab ? ' class="active"' : '') + '>' + tabs[k] + '</a>';
        }).join('') + '</nav>';
        var html = '';
        if (!tabs[tab]) {
            html = '<section class="page-404"><h1>Доступ запрещён (403)</h1><p>Вашей роли «' + ROLES[u.role] +
                '» недостаточно прав для этого раздела.</p></section>';
        } else if (tab === 'index') {
            html = '<h1>Панель управления</h1><p>Роль: <strong>' + ROLES[u.role] + '</strong>. ' +
                (u.role === 'admin' ? 'Доступны все разделы, включая управление пользователями.' : 'Доступны контент, записи, сообщения и баннеры.') +
                '</p><div class="stats"><a href="' + url('admin.html#bookings') + '"><strong>' + d.bookings.filter(function (b) { return b.status === 'new'; }).length +
                '</strong> новых записей</a><a href="' + url('admin.html#messages') + '"><strong>' + d.messages.filter(function (m) { return m.status === 'new'; }).length +
                '</strong> новых сообщений</a><a href="' + url('admin.html#articles') + '"><strong>' + d.articles.length + '</strong> материалов</a><span><strong>' +
                d.bookings.length + '</strong> записей всего</span><span><strong>' + d.users.length + '</strong> пользователей</span></div>';
        } else if (tab === 'articles') {
            html = '<h1>Управление контентом</h1><p class="small">В демонстрационной версии материалы только просматриваются; ' +
                'создание, редактирование и загрузка изображений работают в полной версии на PHP.</p><table class="data"><thead><tr><th>ID</th>' +
                '<th>Раздел</th><th>Заголовок</th><th>Цена</th></tr></thead><tbody>' + d.articles.map(function (a) {
                    var sec = d.sections.filter(function (s) { return s.code === a.section; })[0];
                    return '<tr><td>' + a.id + '</td><td>' + esc(sec.title) + '</td><td><a href="' + articleUrl(a) + '">' + esc(a.title) +
                        '</a></td><td>' + (a.price ? 'от ' + money(a.price) : '') + '</td></tr>';
                }).join('') + '</tbody></table>';
        } else if (tab === 'bookings') {
            html = '<h1>Записи клиентов</h1><table class="data"><thead><tr><th>№</th><th>Дата, время</th><th>Клиент</th><th>Телефон</th>' +
                '<th>Услуга</th><th>Мастер</th><th>Комментарий</th><th>Статус</th></tr></thead><tbody>' +
                d.bookings.slice().sort(function (a, b) { return (b.status === 'new') - (a.status === 'new'); }).map(function (b) {
                    var s = article(b.service_id), m = b.master_id ? article(b.master_id) : null;
                    return '<tr><td>' + b.id + '</td><td>' + esc(b.visit_date) + ' ' + esc(b.visit_time) + '</td><td>' + esc(b.name) +
                        (b.user_id ? ' <span class="tag">с сайта</span>' : '') + '</td><td>' + esc(b.phone) + '</td><td>' + esc(s ? s.title : '') +
                        '</td><td>' + esc(m ? m.title : 'любой') + '</td><td>' + esc(b.comment) + '</td><td><select data-booking="' + b.id +
                        '" aria-label="Статус записи ' + b.id + '">' + Object.keys(STATUSES).map(function (k) {
                            return '<option value="' + k + '"' + (k === b.status ? ' selected' : '') + '>' + STATUSES[k] + '</option>';
                        }).join('') + '</select></td></tr>';
                }).join('') + '</tbody></table>';
        } else if (tab === 'messages') {
            html = '<h1>Сообщения клиентов</h1>' + d.messages.slice().reverse().map(function (m) {
                var login = m.user_id ? (d.users.filter(function (x) { return x.id === m.user_id; })[0] || {}).login : null;
                return '<div class="msg msg-' + m.status + '"><p class="meta">№' + m.id + ' · ' + esc(m.created_at) + ' · ' + esc(m.name) +
                    ' &lt;' + esc(m.email) + '&gt; · ' + (login ? 'пользователь ' + esc(login) : 'гость (ответ на e-mail)') + ' · <strong>' +
                    MSG_STATUSES[m.status] + '</strong></p><p><strong>' + esc(m.subject) + '</strong><br>' + esc(m.body) + '</p>' +
                    (m.reply ? '<div class="reply"><strong>Ответ:</strong> ' + esc(m.reply) + '</div>' : '') +
                    (m.status !== 'closed' ? '<form class="form" data-msg="' + m.id + '"><label>Ответ <textarea name="reply" rows="2">' +
                        esc(m.reply || '') + '</textarea></label><button class="btn" name="action" value="reply">Ответить</button> ' +
                        '<button class="btn btn-light" name="action" value="close">Закрыть обращение</button></form>' : '') + '</div>';
            }).join('');
        } else if (tab === 'banners') {
            html = '<h1>Баннеры главной страницы</h1><table class="data"><thead><tr><th>Порядок</th><th>Заголовок</th><th>Текст</th>' +
                '<th>Показывать</th></tr></thead><tbody>' + d.banners.map(function (b) {
                    return '<tr><td>' + esc(b.sort) + '</td><td>' + esc(b.title) + '</td><td>' + esc(b.text) + '</td><td>' +
                        (b.is_active === '1' || b.is_active === 1 ? 'да' : 'нет') + '</td></tr>';
                }).join('') + '</tbody></table>';
        } else if (tab === 'users') {
            html = '<h1>Пользователи и роли</h1><table class="data"><thead><tr><th>ID</th><th>Логин</th><th>Имя</th><th>E-mail</th><th>Роль</th>' +
                '<th>Статус</th><th></th></tr></thead><tbody>' + d.users.map(function (x) {
                    return '<tr><td>' + x.id + '</td><td>' + esc(x.login) + '</td><td>' + esc(x.name) + '</td><td>' + esc(x.email) + '</td><td>' +
                        '<select data-role="' + x.id + '" aria-label="Роль ' + esc(x.login) + '">' + Object.keys(ROLES).map(function (k) {
                            return '<option value="' + k + '"' + (k === x.role ? ' selected' : '') + '>' + ROLES[k] + '</option>';
                        }).join('') + '</select></td><td>' + (x.is_active ? 'активна' : '<em>заблокирована</em>') + '</td><td><button class="link" data-block="' +
                        x.id + '">' + (x.is_active ? 'Заблокировать' : 'Разблокировать') + '</button></td></tr>';
                }).join('') + '</tbody></table><h2>Создать пользователя</h2><form class="form form-wide" id="user-form">' +
                '<label>Логин <input name="login" required></label><label>Имя <input name="name"></label>' +
                '<label>E-mail <input type="email" name="email" required></label><label>Пароль <input type="password" name="password" required minlength="8"></label>' +
                '<label>Роль <select name="role">' + Object.keys(ROLES).map(function (k) { return '<option value="' + k + '">' + ROLES[k] + '</option>'; }).join('') +
                '</select></label><button class="btn" type="submit">Создать</button></form>';
        }
        app.innerHTML = nav + html;
        var crumbs = main().querySelector('.crumbs span');
        if (crumbs) { crumbs.textContent = 'Панель управления'; }
        Array.prototype.forEach.call(app.querySelectorAll('[data-booking]'), function (sel) {
            sel.addEventListener('change', function () {
                d.bookings.forEach(function (b) { if (b.id === +sel.dataset.booking) { b.status = sel.value; } });
                save(); flash('Статус записи изменён.'); window.location.reload();
            });
        });
        Array.prototype.forEach.call(app.querySelectorAll('form[data-msg]'), function (f) {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                var action = e.submitter ? e.submitter.value : 'reply';
                d.messages.forEach(function (m) {
                    if (m.id !== +f.dataset.msg) { return; }
                    if (action === 'close') { m.status = 'closed'; flash('Обращение закрыто.', 'info'); }
                    else if (v(f, 'reply')) { m.reply = v(f, 'reply'); m.replied_at = nowStr(); m.status = 'answered';
                        flash('Ответ сохранён и виден клиенту в личном кабинете.'); }
                });
                save(); window.location.reload();
            });
        });
        Array.prototype.forEach.call(app.querySelectorAll('[data-role]'), function (sel) {
            sel.addEventListener('change', function () {
                if (+sel.dataset.role === u.id) { flash('Нельзя менять роль собственной учётной записи.', 'error'); window.location.reload(); return; }
                d.users.forEach(function (x) { if (x.id === +sel.dataset.role) { x.role = sel.value; } });
                save(); flash('Роль изменена.'); window.location.reload();
            });
        });
        Array.prototype.forEach.call(app.querySelectorAll('[data-block]'), function (btn) {
            btn.addEventListener('click', function () {
                if (+btn.dataset.block === u.id) { flash('Нельзя блокировать собственную учётную запись.', 'error'); window.location.reload(); return; }
                d.users.forEach(function (x) { if (x.id === +btn.dataset.block) { x.is_active = x.is_active ? 0 : 1; } });
                save(); flash('Статус учётной записи изменён.'); window.location.reload();
            });
        });
        var uf = app.querySelector('#user-form');
        if (uf) {
            uf.addEventListener('submit', function (e) {
                e.preventDefault();
                var login = v(uf, 'login'), email = v(uf, 'email'), pass = uf.elements.password.value;
                if (!/^[a-zA-Z0-9_.-]{3,40}$/.test(login) || pass.length < 8 || !/^[^@\s]+@[^@\s]+$/.test(email)) {
                    showErrors(uf, ['Проверьте данные: логин латиницей, пароль от 8 символов, корректный e-mail.']); return;
                }
                if (d.users.some(function (x) { return x.login === login || x.email === email; })) { showErrors(uf, ['Логин или e-mail уже заняты.']); return; }
                d.users.push({id: nextId(d.users), login: login, name: v(uf, 'name') || login, email: email, phone: '',
                    role: uf.elements.role.value, is_active: 1, password: pass});
                save(); flash('Пользователь создан.'); window.location.reload();
            });
        }
    }

    /* ---------------- запуск ---------------- */
    document.addEventListener('DOMContentLoaded', function () {
        var app = document.getElementById('app');
        if (app && app.dataset.page === 'vi') { pageVi(); return; }
        if (app && app.dataset.page === 'logout') { pageLogout(); return; }
        applyVi();
        header();
        var page = window.location.pathname.split('/').pop() || 'index.html';
        var form = main() && main().querySelector('form.form');
        if (page === 'login.html' && form) { pageLogin(form); }
        if (page === 'register.html' && form) { pageRegister(form); }
        if (page === 'booking.html' && form) { pageBooking(form); }
        if (page === 'contacts.html' && form) { pageContacts(form); }
        if (page === 'search.html') { pageSearch(); }
        if (app && app.dataset.page === 'cabinet') { pageCabinet(app); }
        if (app && app.dataset.page === 'admin') {
            pageAdmin(app);
            window.addEventListener('hashchange', function () { pageAdmin(app); });
        }
        showFlash();
    });
})();
