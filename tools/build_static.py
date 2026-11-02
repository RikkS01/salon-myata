"""Сборка демонстрационной статической версии сайта для GitHub Pages (папка docs/).

Публичные страницы снимаются с работающего PHP-сайта, ссылки переписываются на .html,
а функции, которым нужен сервер (вход, регистрация, запись, сообщения, личный кабинет,
панель управления, поиск), выполняются скриптом assets/js/demo.js в браузере посетителя
(данные хранятся в localStorage).
"""
import json
import os
import re
import shutil
import subprocess
import sys
import urllib.request
from html import unescape

BASE = 'http://localhost/salon-myata/'
SITE = '/home/claude/w4/site/salon-myata'
OUT = sys.argv[1] if len(sys.argv) > 1 else '/home/claude/w4/site/docs'
PREFIX = '/salon-myata'
HOST = 'https://rikks01.github.io'

SEED_PASSWORDS = {'admin': 'Admin_2026', 'manager': 'Manager_2026', 'client': 'Client_2026'}


def get(path):
    with urllib.request.urlopen(BASE + path) as r:
        return r.read().decode('utf-8')


def get_any(path):
    try:
        return get(path)
    except urllib.error.HTTPError as e:
        return e.read().decode('utf-8')


def mysql(query):
    out = subprocess.run(['mysql', '--batch', '--raw', 'salon_myata', '-e', query],
                         capture_output=True, text=True, check=True).stdout.rstrip('\n').split('\n')
    head = out[0].split('\t')
    rows = []
    for line in out[1:]:
        vals = [None if v == 'NULL' else v for v in line.split('\t')]
        rows.append(dict(zip(head, vals)))
    return rows


def rewrite(html):
    """Переписать адреса PHP-страниц на статические."""
    def repl(m):
        attr, quote, url = m.group(1), m.group(2), unescape(m.group(3))
        if not url.startswith(PREFIX):
            return m.group(0)
        path = url[len(PREFIX):].lstrip('/')
        new = None
        mm = re.match(r'section\.php\?s=(\w+)$', path)
        if mm:
            new = f'section-{mm.group(1)}.html'
        mm = re.match(r'article\.php\?s=(\w+)&slug=([\w-]+)$', path)
        if mm:
            new = f'article-{mm.group(1)}-{mm.group(2)}.html'
        if new is None:
            mm = re.match(r'admin/(\w+)\.php(\?.*)?$', path)
            if mm:
                new = 'admin.html' + (f'#{mm.group(1)}' if mm.group(1) != 'index' else '')
        if new is None:
            if path == 'sitemap.xml.php':
                new = 'sitemap.xml'
            elif path == '':
                new = ''
            else:
                new = re.sub(r'^([\w.-]+?)\.php', r'\1.html', path)
        return f'{attr}={quote}{PREFIX}/{new.replace("&", "&amp;")}{quote}'
    html = re.sub(r'(href|src|action)=(["\'])([^"\']*)\2', repl, html)
    # inline style с фоном баннера
    html = html.replace(f"url('{PREFIX}/assets/img/", f"url('{PREFIX}/assets/img/")
    return html


DEMO_NOTE = ('<div class="demo-note" role="note">Демонстрационная версия сайта на GitHub Pages: вход, запись, '
             'сообщения и панель управления работают в браузере, данные хранятся только у вас на устройстве. '
             'Полная версия на PHP и MySQL – в <a href="https://github.com/RikkS01/salon-myata">репозитории</a>.</div>')


def finish(html):
    html = rewrite(html)
    html = html.replace('</head>', f'    <link rel="stylesheet" href="{PREFIX}/assets/css/vi.css" media="not all" '
                                   f'id="vi-css">\n    <link rel="stylesheet" href="{PREFIX}/assets/css/demo.css">\n'
                                   f'    <script src="{PREFIX}/assets/js/demo-data.js"></script>\n'
                                   f'    <script src="{PREFIX}/assets/js/demo.js" defer></script>\n</head>')
    html = re.sub(r'<input type="hidden" name="csrf" value="[0-9a-f]+">', '', html)
    html = html.replace('<footer class="site-footer">', DEMO_NOTE + '\n<footer class="site-footer">')
    return html


def write(name, html):
    path = os.path.join(OUT, name)
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(html)


def shell(template_html, title, main_html):
    """Страница на основе шаблона с заменой содержимого main."""
    html = re.sub(r'<main id="main" class="wrap">.*?</main>',
                  f'<main id="main" class="wrap">\n{main_html}\n</main>', template_html, flags=re.S)
    html = re.sub(r'<title>.*?</title>', f'<title>{title} – Салон красоты «Мята»</title>', html)
    return html


def main():
    if os.path.isdir(OUT):
        shutil.rmtree(OUT)
    os.makedirs(OUT)
    shutil.copytree(f'{SITE}/assets', f'{OUT}/assets')
    shutil.copy(f'{SITE}/favicon.ico', OUT)

    pages = {'index.html': '', 'about.html': 'about.php', 'prices.html': 'prices.php',
             'contacts.html': 'contacts.php', 'booking.html': 'booking.php', 'login.html': 'login.php',
             'register.html': 'register.php', 'sitemap.html': 'sitemap.php', 'search.html': 'search.php'}
    sections = [r['code'] for r in mysql('SELECT code FROM sections ORDER BY sort')]
    for code in sections:
        pages[f'section-{code}.html'] = f'section.php?s={code}'
    arts = mysql('SELECT id, section, slug, title, summary, body, image, price, duration, created_at '
                 'FROM articles WHERE is_published = 1 ORDER BY id')
    for a in arts:
        pages[f'article-{a["section"]}-{a["slug"]}.html'] = f'article.php?s={a["section"]}&slug={a["slug"]}'
    for name, path in pages.items():
        html = get(path)
        if name.startswith('article-'):
            html = re.sub(r' · просмотров: \d+', '', html)
        write(name, finish(html))
    write('404.html', finish(get_any('net-takoy-stranicy')))
    tpl = finish(get('about.php'))
    write('cabinet.html', shell(tpl, 'Личный кабинет', '<div id="app" data-page="cabinet"></div>'))
    write('admin.html', shell(tpl, 'Панель управления', '<div id="app" data-page="admin"></div>'))
    write('logout.html', shell(tpl, 'Выход', '<div id="app" data-page="logout"></div>'))
    write('vi.html', shell(tpl, 'Версия для слабовидящих', '<div id="app" data-page="vi"></div>'))
    # sitemap.xml
    urls = [''] + [n for n in pages if n not in ('index.html',)]
    with open(f'{OUT}/sitemap.xml', 'w', encoding='utf-8') as f:
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')
        for u in urls:
            f.write(f'  <url><loc>{HOST}{PREFIX}/{u}</loc></url>\n')
        f.write('</urlset>\n')
    # данные для демо-режима
    users = mysql('SELECT id, login, name, email, phone, role, is_active FROM users ORDER BY id')
    for u in users:
        u['password'] = SEED_PASSWORDS.get(u['login'], '')
        u['id'] = int(u['id']); u['is_active'] = int(u['is_active'])
    data = {
        'sections': mysql('SELECT code, title FROM sections ORDER BY sort'),
        'articles': [{'id': int(a['id']), 'section': a['section'], 'slug': a['slug'], 'title': a['title'],
                      'summary': a['summary'], 'text': re.sub(r'<[^>]+>', ' ', a['body']),
                      'price': int(a['price']) if a['price'] else None,
                      'duration': int(a['duration']) if a['duration'] else None} for a in arts],
        'users': users,
        'bookings': [{**b, 'id': int(b['id']), 'user_id': int(b['user_id']) if b['user_id'] else None,
                      'service_id': int(b['service_id']),
                      'master_id': int(b['master_id']) if b['master_id'] else None}
                     for b in mysql('SELECT id, user_id, name, phone, service_id, master_id, visit_date, '
                                    'LEFT(visit_time, 5) visit_time, comment, status, created_at FROM bookings')],
        'messages': [{**m, 'id': int(m['id']), 'user_id': int(m['user_id']) if m['user_id'] else None}
                     for m in mysql('SELECT id, user_id, name, email, subject, body, reply, replied_at, status, '
                                    'created_at FROM messages')],
        'banners': mysql('SELECT id, title, text, link, sort, is_active FROM banners ORDER BY sort'),
    }
    with open(f'{OUT}/assets/js/demo-data.js', 'w', encoding='utf-8') as f:
        f.write('window.DEMO_SEED = ' + json.dumps(data, ensure_ascii=False) + ';\n')
    open(f'{OUT}/.nojekyll', 'w').close()
    print('ok', len(os.listdir(OUT)), 'файлов в', OUT)


if __name__ == '__main__':
    main()
