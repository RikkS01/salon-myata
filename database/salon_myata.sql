-- База данных сайта салона красоты «Мята» (учебный проект)
-- Импорт: phpMyAdmin (XAMPP) -> Импорт -> этот файл. Кодировка utf8mb4.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS messages, bookings, prices, banners, articles, sections, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  login VARCHAR(40) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL DEFAULT '',
  role ENUM('admin','manager','client') NOT NULL DEFAULT 'client',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sections (
  code VARCHAR(20) PRIMARY KEY,
  title VARCHAR(60) NOT NULL,
  description VARCHAR(255) NOT NULL,
  sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section VARCHAR(20) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  title VARCHAR(200) NOT NULL,
  summary VARCHAR(400) NOT NULL,
  body TEXT NOT NULL,
  image VARCHAR(120) NOT NULL DEFAULT '',
  price INT NULL,
  duration INT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  views INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_section_slug (section, slug),
  FULLTEXT KEY ft_search (title, summary, body),
  CONSTRAINT fk_articles_section FOREIGN KEY (section) REFERENCES sections(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE banners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  text VARCHAR(255) NOT NULL,
  link VARCHAR(255) NOT NULL,
  image VARCHAR(120) NOT NULL,
  sort INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE prices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(60) NOT NULL,
  name VARCHAR(120) NOT NULL,
  price INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  service_id INT NOT NULL,
  master_id INT NULL,
  visit_date DATE NOT NULL,
  visit_time TIME NOT NULL,
  comment VARCHAR(500) NOT NULL DEFAULT '',
  status ENUM('new','confirmed','done','cancelled') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_bookings_service FOREIGN KEY (service_id) REFERENCES articles(id),
  CONSTRAINT fk_bookings_master FOREIGN KEY (master_id) REFERENCES articles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL,
  subject VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  reply TEXT NULL,
  replied_by INT NULL,
  replied_at DATETIME NULL,
  status ENUM('new','answered','closed') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_messages_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_messages_admin FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Пользователи (пароли хранятся в виде хеша password_hash)
INSERT INTO users (login, password_hash, name, email, phone, role) VALUES ('admin', '$2y$10$7nkbT6H3NBwPqBQ/tWJUy.56aq3s8Y36jDJuMMDohPVdSNZK/E5cy', 'Администратор сайта', 'admin@myata-salon.local', '+7 (900) 000-00-01', 'admin');
INSERT INTO users (login, password_hash, name, email, phone, role) VALUES ('manager', '$2y$10$NLS.woCZHb1yx5nlWd3AH.J5MGjawenbr3Vse5g.ahHVsx7chlOhK', 'Светлана Павлова (администратор салона)', 'manager@myata-salon.local', '+7 (900) 000-00-02', 'manager');
INSERT INTO users (login, password_hash, name, email, phone, role) VALUES ('client', '$2y$10$SAs99F2KBfp741JUo1zxEe8jr8eEVZN0jtiuNxceHIEvUawXfC612', 'Наталья Кузнецова', 'client@example.com', '+7 (900) 000-00-03', 'client');

-- Разделы
INSERT INTO sections VALUES ('services', 'Услуги', 'Парикмахерские услуги, ногтевой сервис, косметология, массаж и макияж.', 1);
INSERT INTO sections VALUES ('masters', 'Мастера', 'Команда салона: опыт, специализация и сертификаты мастеров.', 2);
INSERT INTO sections VALUES ('promo', 'Акции', 'Специальные предложения и скидки салона.', 3);
INSERT INTO sections VALUES ('news', 'Новости', 'События салона, новые услуги и изменения в расписании.', 4);
INSERT INTO sections VALUES ('blog', 'Блог', 'Полезные статьи об уходе за волосами, кожей и ногтями.', 5);

-- Статьи разделов
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('services', 'strizhki-i-ukladki', 'Стрижки и укладки', 'Женские, мужские и детские стрижки, укладки на каждый день и к событию.', '<p>Стрижка начинается с консультации: мастер оценивает структуру и густоту волос, форму лица и то, сколько времени вы готовы тратить на укладку по утрам. Только после этого предлагается форма – от классического каре до многослойной стрижки с плавными переходами.</p><p>Мы работаем японскими ножницами с полуконвексной заточкой: они не «рубят» кончики, поэтому волосы дольше остаются ровными и меньше секутся. Перед стрижкой волосы моют профессиональным шампунем по типу кожи головы, после – наносят термозащиту.</p><h3>Что входит в услугу</h3><ul><li>консультация и подбор формы</li><li>мытьё головы и уходовый бальзам</li><li>стрижка</li><li>укладка феном или брашингом</li><li>рекомендации по домашнему уходу</li></ul><p>Детская стрижка (до 12 лет) проводится в игровой форме и занимает не более 30 минут. Для мужчин доступна стрижка машинкой с плавным переходом (fade) и оформление бороды.</p>', 'service-hair.png', 1800, 60, '2026-04-13 10:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('services', 'okrashivanie-volos', 'Окрашивание волос', 'Однотонное окрашивание, мелирование, балаяж, тонирование и уход после окрашивания.', '<p>Окрашивание – самая технически сложная парикмахерская услуга, поэтому каждый мастер-колорист салона проходит обучение не реже одного раза в год. Мы используем красители с пониженным содержанием аммиака и обязательно добавляем в состав средство для защиты внутренней структуры волоса.</p><p>Перед первым окрашиванием проводится тест на чувствительность за 48 часов до процедуры: это бесплатно и занимает пять минут.</p><h3>Техники</h3><ul><li>однотонное окрашивание и закрашивание седины</li><li>классическое и мелкое мелирование</li><li>балаяж и шатуш – мягкий переход от тёмных корней к светлым кончикам</li><li>тонирование – обновление оттенка без осветления</li><li>выход из тёмного цвета (в несколько этапов)</li></ul><p>Стоимость зависит от длины и густоты волос и окончательно определяется на консультации.</p>', 'service-color.png', 4500, 150, '2026-04-13 11:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('services', 'manikyur-i-pedikyur', 'Маникюр и педикюр', 'Аппаратный и комбинированный маникюр, покрытие гель-лаком, педикюр, SPA-уход для рук и ног.', '<p>Ногтевой сервис – направление, в котором безопасность важнее всего. Все металлические инструменты проходят трёхэтапную обработку: дезинфекцию, предстерилизационную очистку и стерилизацию в сухожаровом шкафу. Крафт-пакет с инструментами вскрывается при клиенте.</p><p>Пилки и бафы – одноразовые: клиент забирает их с собой или они утилизируются.</p><h3>Виды маникюра</h3><ul><li>аппаратный – обработка кутикулы фрезами, подходит для тонкой кожи</li><li>комбинированный – аппарат и ножницы, самый популярный вариант</li><li>покрытие гель-лаком с выравниванием ногтевой пластины</li><li>японский маникюр – укрепление ногтей без покрытия</li></ul><p>Педикюр выполняется в отдельном кабинете с педикюрным креслом. Для пациентов с сахарным диабетом рекомендуем аппаратный педикюр – он минимизирует риск микротравм.</p>', 'service-nails.png', 2200, 90, '2026-04-13 12:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('services', 'kosmetologiya', 'Косметология', 'Чистка лица, пилинги, уходовые программы и массаж лица по типу кожи.', '<p>Косметологический кабинет салона оказывает эстетические (немедицинские) процедуры. Каждая программа начинается с диагностики: косметолог определяет тип и состояние кожи, уточняет аллергии и принимаемые препараты.</p><p>Инъекционные процедуры в салоне не проводятся – при необходимости мы рекомендуем обратиться в медицинскую клинику с лицензией.</p><h3>Популярные процедуры</h3><ul><li>комбинированная чистка лица (ультразвук и мануальная)</li><li>поверхностные кислотные пилинги (с октября по март)</li><li>увлажняющие и успокаивающие альгинатные маски</li><li>массаж лица: классический, скульптурный, лимфодренажный</li><li>уход за кожей вокруг глаз</li></ul><p>Курс подбирается индивидуально; после процедуры клиент получает памятку по уходу дома.</p>', 'service-face.png', 3500, 75, '2026-04-13 13:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('services', 'massazh-i-spa', 'Массаж и SPA', 'Классический, расслабляющий и антицеллюлитный массаж, обёртывания и SPA-программы.', '<p>Массажный кабинет оборудован столом с электроприводом и подогревом, используются гипоаллергенные масла без отдушек. Перед первым сеансом мастер уточняет противопоказания: острые воспаления, повышенную температуру, обострения хронических заболеваний.</p><p>Продолжительность сеанса – от 30 до 90 минут. Для достижения стойкого результата обычно рекомендуют курс из 8–10 процедур.</p><h3>Программы</h3><ul><li>классический массаж спины и шейно-воротниковой зоны</li><li>расслабляющий массаж всего тела</li><li>антицеллюлитный массаж и обёртывания</li><li>SPA-программа «Мятный день» (пилинг тела, обёртывание, массаж, травяной чай)</li></ul><p>Для курса из 10 сеансов действует скидка 10 %.</p>', 'service-spa.png', 2800, 60, '2026-04-13 14:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('services', 'makiyazh-i-brovi', 'Макияж и брови', 'Дневной и вечерний макияж, свадебный образ, коррекция и окрашивание бровей, ламинирование ресниц.', '<p>Визажисты салона работают с профессиональной декоративной косметикой и одноразовыми аппликаторами. Перед свадебным макияжем проводится пробный образ: так в важный день не будет сюрпризов.</p><p>Архитектура бровей включает подбор формы по пропорциям лица, коррекцию пинцетом или воском и окрашивание краской или хной.</p><ul><li>дневной макияж – 45 минут</li><li>вечерний и фотомакияж – 60 минут</li><li>свадебный образ с пробой – 2 визита</li><li>ламинирование ресниц и бровей – 60 минут</li></ul><p>Стойкость ламинирования – 4–6 недель.</p>', 'service-makeup.png', 2000, 60, '2026-04-13 15:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('masters', 'anna-sokolova', 'Анна Соколова – стилист-колорист', 'Стаж 9 лет. Сложные окрашивания, балаяж, выход из тёмного цвета.', '<p>Анна пришла в профессию после художественной школы, поэтому к цвету относится как к живописи. Специализируется на техниках мягкого осветления и коррекции неудачных окрашиваний.</p><p>Регулярно повышает квалификацию на курсах ведущих учебных центров, участвовала в конкурсах парикмахерского искусства.</p><ul><li>Специализация: колористика, балаяж, шатуш</li><li>Опыт: 9 лет</li><li>Принимает: вт, чт, сб</li></ul>', 'master-1.png', NULL, NULL, '2026-04-13 10:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('masters', 'irina-volkova', 'Ирина Волкова – мастер ногтевого сервиса', 'Стаж 6 лет. Аппаратный маникюр, укрепление ногтей, педикюр.', '<p>Ирина – автор внутреннего стандарта салона по обработке инструментов. Любит минималистичный дизайн и «нюдовые» оттенки, но выполнит и сложный рисунок.</p><p>Владеет техникой аппаратного педикюра для клиентов с чувствительной кожей стоп.</p><ul><li>Специализация: аппаратный маникюр, педикюр</li><li>Опыт: 6 лет</li><li>Принимает: пн, ср, пт, вс</li></ul>', 'master-2.png', NULL, NULL, '2026-04-13 11:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('masters', 'elena-morozova', 'Елена Морозова – косметолог-эстетист', 'Стаж 11 лет. Чистки, пилинги, массаж лица, уходовые программы.', '<p>Елена имеет среднее медицинское образование и диплом косметолога-эстетиста. Подбирает домашний уход так, чтобы он продолжал работу, начатую в кабинете.</p><p>Ведёт в блоге салона рубрику об уходе за кожей в разные сезоны.</p><ul><li>Специализация: чистки, пилинги, массаж лица</li><li>Опыт: 11 лет</li><li>Принимает: пн–пт</li></ul>', 'master-3.png', NULL, NULL, '2026-04-13 12:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('masters', 'dmitriy-orlov', 'Дмитрий Орлов – барбер и парикмахер', 'Стаж 7 лет. Мужские стрижки, fade, оформление бороды.', '<p>Дмитрий отвечает за мужское направление салона. Сочетает классические техники с современными: машинные переходы, текстурные стрижки, моделирование бороды опасной бритвой.</p><p>Объясняет клиентам, как поддерживать форму стрижки дома без лишних средств.</p><ul><li>Специализация: мужские стрижки, борода</li><li>Опыт: 7 лет</li><li>Принимает: ср–вс</li></ul>', 'master-4.png', NULL, NULL, '2026-04-13 13:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('masters', 'olga-lebedeva', 'Ольга Лебедева – массажист', 'Стаж 12 лет. Классический, расслабляющий и антицеллюлитный массаж.', '<p>Ольга окончила медицинский колледж по специальности «Медицинский массаж». Строит каждый сеанс с учётом образа жизни клиента: офисная работа, спорт, беременность (после 1-го триместра и только по согласованию с врачом).</p><p>Автор SPA-программы «Мятный день».</p><ul><li>Специализация: массаж, SPA-программы</li><li>Опыт: 12 лет</li><li>Принимает: вт–сб</li></ul>', 'master-5.png', NULL, NULL, '2026-04-13 14:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('masters', 'mariya-kim', 'Мария Ким – визажист и бровист', 'Стаж 5 лет. Свадебный макияж, архитектура бровей, ламинирование.', '<p>Мария работает со свадебными образами и фотосессиями. Считает, что хороший макияж подчёркивает, а не скрывает лицо.</p><p>Проводит в салоне мини-уроки «Макияж за 10 минут» для клиентов.</p><ul><li>Специализация: макияж, брови, ресницы</li><li>Опыт: 5 лет</li><li>Принимает: пт–вс</li></ul>', 'master-6.png', NULL, NULL, '2026-04-13 15:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('promo', 'pervoe-poseshchenie', 'Скидка 15 % на первое посещение', 'Для новых клиентов – скидка 15 % на любую услугу при записи через сайт.', '<p>Запишитесь на любую услугу через форму онлайн-записи на сайте и укажите в комментарии «Первое посещение». Скидка 15 % применяется при оплате.</p><p>Акция не суммируется с другими предложениями и действует один раз для каждого клиента.</p>', 'promo-1.png', NULL, NULL, '2026-04-20 10:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('promo', 'privedi-podrugu', 'Приведи подругу', 'Вы и ваша подруга получаете по 500 бонусных рублей на следующие визиты.', '<p>Порекомендуйте салон подруге. Когда она впервые воспользуется любой услугой и назовёт ваше имя, вы обе получите по 500 бонусных рублей.</p><p>Бонусами можно оплатить до 30 % стоимости следующей услуги в течение трёх месяцев.</p>', 'promo-2.png', NULL, NULL, '2026-04-20 11:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('promo', 'schastlivye-chasy', 'Счастливые часы по будням', 'С понедельника по четверг с 10:00 до 13:00 – скидка 20 % на маникюр и стрижки.', '<p>В утренние часы будних дней загрузка мастеров ниже, поэтому мы делаем её выгодной для вас: скидка 20 % на маникюр, педикюр и стрижки при начале процедуры с 10:00 до 13:00.</p><p>Предварительная запись обязательна.</p>', 'promo-3.png', NULL, NULL, '2026-04-20 12:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('promo', 'den-rozhdeniya', 'В день рождения – подарок', 'Скидка 10 % на любые услуги за 3 дня до и 3 дня после дня рождения.', '<p>Покажите администратору документ с датой рождения – и получите скидку 10 % на все услуги визита. Для зарегистрированных клиентов сайта дата учитывается автоматически.</p>', 'promo-4.png', NULL, NULL, '2026-04-20 13:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('promo', 'kurs-massazha', 'Курс массажа выгоднее', 'При оплате курса из 10 сеансов массажа – скидка 10 %, сеансы можно разделить с близкими.', '<p>Курсовой массаж эффективнее разовых визитов. Оплатите 10 сеансов любой массажной программы и получите скидку 10 %. Сеансы можно использовать вдвоём с членом семьи в течение 4 месяцев.</p>', 'promo-5.png', NULL, NULL, '2026-04-20 14:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('news', 'otkrytie-kabineta-pedikyura', 'Открыт новый кабинет педикюра', 'В салоне появился отдельный кабинет с педикюрным креслом и системой вентиляции.', '<p>Мы расширились: на втором этаже открыт отдельный кабинет педикюра. Кресло с гидромассажной ванночкой, бесшумный аппарат с пылеуловителем и приточная вентиляция делают процедуру ещё комфортнее.</p><p>Записаться можно онлайн или по телефону.</p>', 'news-1.png', NULL, NULL, '2026-04-14 10:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('news', 'onlayn-zapis', 'Запустили онлайн-запись на сайте', 'Теперь записаться к мастеру можно круглосуточно – через форму на сайте.', '<p>Выберите услугу, мастера, дату и удобное время – администратор подтвердит запись в течение часа в рабочее время. Зарегистрированные пользователи видят историю записей и статусы в личном кабинете.</p>', 'news-2.png', NULL, NULL, '2026-04-21 11:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('news', 'versiya-dlya-slabovidyashchikh', 'Версия сайта для слабовидящих', 'На сайте появилась версия для слабовидящих по ГОСТ Р 52872-2019.', '<p>Кнопка «Версия для слабовидящих» в шапке сайта включает крупный шрифт, контрастные цветовые схемы, отключение изображений и увеличенный межбуквенный интервал. Настройки сохраняются для следующих посещений.</p>', 'news-3.png', NULL, NULL, '2026-05-05 12:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('news', 'novaya-programma-spa', 'Новая SPA-программа «Мятный день»', 'Пилинг тела, обёртывание, массаж и травяной чай – два с половиной часа отдыха.', '<p>Ольга Лебедева разработала авторскую программу восстановления после рабочей недели. В мае программа доступна по специальной цене.</p>', 'news-4.png', NULL, NULL, '2026-05-12 13:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('news', 'grafik-prazdnikov', 'График работы в праздничные дни', 'Салон работает 12 июня с 10:00 до 18:00.', '<p>В праздничный день 12 июня салон открыт с 10:00 до 18:00. В остальные дни – обычный график: ежедневно с 9:00 до 21:00.</p>', 'news-5.png', NULL, NULL, '2026-06-01 14:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('blog', 'kak-uhazhivat-za-okrashennymi-volosami', 'Как ухаживать за окрашенными волосами', 'Пять правил, которые продлят яркость цвета и сохранят качество волос.', '<p>Окрашенные волосы теряют пигмент в первую очередь из-за горячей воды, щелочных шампуней и ультрафиолета. Поэтому уход строится вокруг трёх задач: закрыть кутикулу, сохранить пигмент и восполнить влагу.</p><ol><li>Не мойте голову первые 48 часов после окрашивания.</li><li>Используйте шампунь с пометкой «для окрашенных волос» и кислым pH.</li><li>Смывайте средства прохладной водой.</li><li>Наносите термозащиту перед феном и утюжком.</li><li>Раз в неделю делайте питательную маску.</li></ol><p>Тонирование раз в 6–8 недель освежает оттенок без повторного осветления.</p>', 'blog-1.png', NULL, NULL, '2026-05-18 10:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('blog', 'gel-lak-vred-ili-net', 'Гель-лак: вредно ли это для ногтей', 'Разбираем мифы о покрытии гель-лаком и правила безопасного снятия.', '<p>Сам гель-лак ногтям не вредит. Проблемы возникают из-за агрессивного опила перед покрытием и неправильного снятия, когда покрытие срывают или долго держат в ацетоне.</p><p>Правильно: аккуратный опил верхнего слоя покрытия, бережное удаление остатков, восстановление маслом. Делать «перерывы» без покрытия не обязательно, если ногти не истончены.</p>', 'blog-2.png', NULL, NULL, '2026-05-18 11:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('blog', 'uhod-za-kozhey-letom', 'Уход за кожей летом', 'Почему летом важны SPF, лёгкие текстуры и отказ от агрессивных пилингов.', '<p>Летом кожа вырабатывает больше кожного сала, но при этом страдает от обезвоживания и ультрафиолета. Базовый уход: мягкое очищение, лёгкий увлажняющий флюид и солнцезащитный крем с SPF 30–50 каждый день, даже в облачную погоду.</p><p>Кислотные пилинги и ретиноиды лучше перенести на осень: они повышают чувствительность к солнцу и риск пигментации.</p>', 'blog-3.png', NULL, NULL, '2026-05-18 12:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('blog', 'kak-vybrat-strizhku', 'Как выбрать стрижку по форме лица', 'Овал, круг, квадрат, треугольник: какие формы стрижек подходят каждому типу.', '<p>Задача стрижки – визуально приблизить пропорции лица к овалу. Круглому лицу подходят асимметрия и объём на макушке, квадратному – мягкие слои, закрывающие линию челюсти, треугольному – объём в нижней части.</p><p>Но форма лица – не единственный критерий: важны структура волос, рост, стиль одежды и привычки. Поэтому окончательное решение мы принимаем вместе на консультации.</p>', 'blog-4.png', NULL, NULL, '2026-05-18 13:00:00');
INSERT INTO articles (section, slug, title, summary, body, image, price, duration, created_at) VALUES ('blog', 'massazh-dlya-ofisnyh-sotrudnikov', 'Массаж для офисных сотрудников', 'Как сидячая работа влияет на шею и спину и чем помогает курс массажа.', '<p>Многочасовая работа за компьютером приводит к перенапряжению мышц шеи и плечевого пояса, головным болям и усталости. Курс массажа шейно-воротниковой зоны из 8–10 сеансов снимает мышечные зажимы и улучшает кровообращение.</p><p>Важно дополнить массаж перерывами каждый час и правильной высотой монитора: верхний край экрана – на уровне глаз.</p>', 'blog-5.png', NULL, NULL, '2026-05-18 14:00:00');

-- Баннеры главной страницы
INSERT INTO banners (title, text, link, image, sort) VALUES ('Скидка 15 % на первое посещение', 'Запишитесь онлайн и укажите «Первое посещение»', 'article.php?s=promo&slug=pervoe-poseshchenie', 'banner-1.png', 1);
INSERT INTO banners (title, text, link, image, sort) VALUES ('SPA-программа «Мятный день»', 'Два с половиной часа отдыха и восстановления', 'article.php?s=news&slug=novaya-programma-spa', 'banner-2.png', 2);
INSERT INTO banners (title, text, link, image, sort) VALUES ('Счастливые часы по будням', 'Скидка 20 % на маникюр и стрижки с 10:00 до 13:00', 'article.php?s=promo&slug=schastlivye-chasy', 'banner-3.png', 3);

-- Прайс-лист
INSERT INTO prices (category, name, price) VALUES ('Парикмахерские услуги', 'Женская стрижка', 1800);
INSERT INTO prices (category, name, price) VALUES ('Парикмахерские услуги', 'Мужская стрижка', 1200);
INSERT INTO prices (category, name, price) VALUES ('Парикмахерские услуги', 'Детская стрижка (до 12 лет)', 900);
INSERT INTO prices (category, name, price) VALUES ('Парикмахерские услуги', 'Укладка', 1200);
INSERT INTO prices (category, name, price) VALUES ('Парикмахерские услуги', 'Окрашивание в один тон', 4500);
INSERT INTO prices (category, name, price) VALUES ('Парикмахерские услуги', 'Балаяж / шатуш', 7500);
INSERT INTO prices (category, name, price) VALUES ('Парикмахерские услуги', 'Тонирование', 3000);
INSERT INTO prices (category, name, price) VALUES ('Ногтевой сервис', 'Маникюр без покрытия', 1300);
INSERT INTO prices (category, name, price) VALUES ('Ногтевой сервис', 'Маникюр с покрытием гель-лаком', 2200);
INSERT INTO prices (category, name, price) VALUES ('Ногтевой сервис', 'Педикюр с покрытием', 2900);
INSERT INTO prices (category, name, price) VALUES ('Ногтевой сервис', 'Снятие покрытия', 400);
INSERT INTO prices (category, name, price) VALUES ('Косметология', 'Комбинированная чистка лица', 3500);
INSERT INTO prices (category, name, price) VALUES ('Косметология', 'Пилинг поверхностный', 2800);
INSERT INTO prices (category, name, price) VALUES ('Косметология', 'Массаж лица', 2500);
INSERT INTO prices (category, name, price) VALUES ('Массаж и SPA', 'Классический массаж спины (30 мин)', 1800);
INSERT INTO prices (category, name, price) VALUES ('Массаж и SPA', 'Расслабляющий массаж (60 мин)', 2800);
INSERT INTO prices (category, name, price) VALUES ('Массаж и SPA', 'SPA-программа «Мятный день»', 6900);
INSERT INTO prices (category, name, price) VALUES ('Макияж и брови', 'Дневной макияж', 2000);
INSERT INTO prices (category, name, price) VALUES ('Макияж и брови', 'Свадебный образ с пробой', 7000);
INSERT INTO prices (category, name, price) VALUES ('Макияж и брови', 'Коррекция и окрашивание бровей', 1100);
INSERT INTO prices (category, name, price) VALUES ('Макияж и брови', 'Ламинирование ресниц', 2300);

-- Демонстрационные запись и сообщение клиента
INSERT INTO bookings (user_id, name, phone, service_id, master_id, visit_date, visit_time, comment, status) VALUES (3, 'Наталья Кузнецова', '+7 (900) 000-00-03', 2, 7, '2026-06-10', '11:00:00', 'Первое посещение', 'confirmed');
INSERT INTO messages (user_id, name, email, subject, body, reply, replied_by, replied_at, status) VALUES (3, 'Наталья Кузнецова', 'client@example.com', 'Тест на краситель', 'Нужно ли приходить заранее на тест перед окрашиванием?', 'Да, тест проводится за 48 часов до окрашивания, он бесплатный и занимает 5 минут. Ждём вас!', 2, '2026-05-20 12:30:00', 'answered');
