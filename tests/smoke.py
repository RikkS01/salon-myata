"""Функциональная проверка сайта: страницы, роли, регистрация, запись, сообщения."""
import http.cookiejar
import re
import sys
import urllib.parse
import urllib.request

BASE = sys.argv[1] if len(sys.argv) > 1 else 'http://localhost/salon-myata/'
ok = fail = 0


def client():
    jar = http.cookiejar.CookieJar()
    op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    return op


def get(op, path, data=None):
    url = urllib.parse.quote(urllib.parse.urljoin(BASE, path), safe=':/?&=%')
    body = urllib.parse.urlencode(data).encode() if data is not None else None
    try:
        r = op.open(url, body)
        return r.status, r.read().decode('utf-8'), r.geturl()
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode('utf-8', 'replace'), url


def check(name, cond):
    global ok, fail
    print(('OK   ' if cond else 'FAIL ') + name)
    ok, fail = ok + bool(cond), fail + (not cond)


def csrf(html):
    return re.search(r'name="csrf" value="([0-9a-f]+)"', html).group(1)


g = client()
pages = ['', 'about.php', 'section.php?s=services', 'section.php?s=masters', 'section.php?s=promo',
         'section.php?s=news', 'section.php?s=blog', 'prices.php', 'contacts.php', 'booking.php',
         'search.php?q=маникюр', 'sitemap.php', 'sitemap.xml.php', 'login.php', 'register.php',
         'article.php?s=services&slug=kosmetologiya']
for p in pages:
    s, h, _ = get(g, p)
    check(f'страница {p or "/"} -> 200', s == 200 and ('Мята' in h or '<urlset' in h))
for sec in ('services', 'masters', 'promo', 'news', 'blog'):
    s, h, _ = get(g, f'section.php?s={sec}')
    n = len(re.findall(r'<article class="card">', h))
    check(f'раздел {sec}: статей {n} >= 5', n >= 5)
s, h, _ = get(g, 'article.php?s=news&slug=net-takoy')
check('несуществующая статья -> 404', s == 404 and 'Страница не найдена' in h)
s, h, _ = get(g, 'admin/index.php')
check('гость не попадает в админку (редирект на вход)', 'Вход в личный кабинет' in h)
s, h, _ = get(g, 'search.php?q=гель-лак')
check('поиск находит статью блога', 'Гель-лак: вредно ли это для ногтей' in h)

# версия для слабовидящих
s, h, _ = get(g, 'vi.php?vi=1&back=/salon-myata/')
check('включение версии для слабовидящих', 'vi-panel' in h and 'vi.css' in h)
get(g, 'vi.php?vi=0&back=/salon-myata/')

# регистрация клиента
c = client()
s, h, _ = get(c, 'register.php')
import random
login = f'test{random.randint(1000, 9999)}'
s, h, u = get(c, 'register.php', {'csrf': csrf(h), 'login': login, 'name': 'Тест Тестов', 'email': f'{login}@ex.com',
                                    'phone': '+7 (900) 111-22-33', 'password': 'Passw0rd1', 'password2': 'Passw0rd1',
                                    'agree': '1'})
check('регистрация -> личный кабинет', 'Личный кабинет' in h and login in h)
s, h, _ = get(c, 'booking.php')
s, h, _ = get(c, 'booking.php', {'csrf': csrf(h), 'name': 'Тест Тестов', 'phone': '+7 (900) 111-22-33',
                                  'service_id': '3', 'master_id': '8', 'visit_date': '2099-01-01',
                                  'visit_time': '10:00', 'comment': ''})
check('запись на дату вне диапазона отклоняется', 'Дата визита' in h)
import datetime
d = (datetime.date.today() + datetime.timedelta(days=3)).isoformat()
s, h, _ = get(c, 'booking.php', {'csrf': csrf(h), 'name': 'Тест Тестов', 'phone': '+7 (900) 111-22-33',
                                  'service_id': '3', 'master_id': '8', 'visit_date': d,
                                  'visit_time': '10:00', 'comment': 'автотест'})
check('онлайн-запись создана и видна в кабинете', 'Маникюр и педикюр' in h and 'Ожидает подтверждения' in h)
s, h, _ = get(c, 'cabinet.php', {'csrf': csrf(h), 'action': 'message', 'subject': 'Вопрос',
                                  'body': 'Можно ли перенести запись на вечер?'})
check('сообщение из кабинета отправлено', 'Можно ли перенести запись на вечер?' in h)
s, h, _ = get(c, 'admin/index.php')
check('клиент не имеет доступа к админке (403)', s == 403)

# менеджер отвечает
m = client()
s, h, _ = get(m, 'login.php')
s, h, _ = get(m, 'login.php', {'csrf': csrf(h), 'login': 'manager', 'password': 'Manager_2026', 'next': ''})
check('вход менеджера -> панель управления', 'Панель управления' in h)
s, h, _ = get(m, 'admin/users.php')
check('менеджер не управляет пользователями (403)', s == 403)
s, h, _ = get(m, 'admin/messages.php')
mid = re.findall(r'№(\d+) · [^<]*Тест Тестов', h)[0]
s, h, _ = get(m, 'admin/messages.php', {'csrf': csrf(h), 'id': mid, 'action': 'reply',
                                         'reply': 'Да, перенесём на 18:00.'})
check('ответ на сообщение сохранён', 'Да, перенесём на 18:00.' in h)
s, h, _ = get(c, 'cabinet.php')
check('клиент видит ответ салона', 'Да, перенесём на 18:00.' in h)
s, h, _ = get(m, 'admin/edit.php?s=news')
s, h, _ = get(m, 'admin/edit.php?s=news', {'csrf': csrf(h), 'section': 'news', 'slug': '', 'title': 'Тестовая новость',
                                            'summary': 'Кратко', 'body': '<p>' + 'Текст новости. ' * 6 + '</p>',
                                            'price': '', 'duration': '', 'is_published': '1'})
check('менеджер добавил новость', 'Тестовая новость' in h)
s, h, _ = get(g, 'article.php?s=news&slug=testovaya-novost')
check('новость доступна на сайте (slug транслитом)', s == 200 and 'Тестовая новость' in h)

# администратор
a = client()
s, h, _ = get(a, 'login.php')
s, h, _ = get(a, 'login.php', {'csrf': csrf(h), 'login': 'admin', 'password': 'Admin_2026', 'next': ''})
s, h, _ = get(a, 'admin/users.php')
check('администратор видит пользователей', s == 200 and login in h)
s, h, _ = get(a, 'login.php')
bad = client()
s, h, _ = get(bad, 'login.php')
s, h, _ = get(bad, 'login.php', {'csrf': csrf(h), 'login': 'admin', 'password': 'wrong', 'next': ''})
check('неверный пароль отклоняется', 'Неверный логин или пароль' in h)
s, h, _ = get(bad, 'contacts.php', {'csrf': 'bad', 'name': 'x', 'email': 'x@x.ru', 'subject': 's', 'body': '1234567890'})
check('форма без CSRF-токена отклоняется', s == 400)
s, h, _ = get(g, "search.php?q=%27%20OR%201%3D1%20--")
check('SQL-инъекция в поиске не срабатывает', s == 200 and 'найдено: 0' in h)
print(f'\nИтого: {ok} пройдено, {fail} не пройдено')
