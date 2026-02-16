<?php
// 1. Подключение к базе (проверьте путь к config.php)
if(file_exists('../../inc/config.php')){
    require_once('../../inc/config.php');
} elseif(file_exists('inc/config.php')){
    require_once('inc/config.php');
} else {
    // На случай, если файл лежит в корне
    die("Ошибка: не найден config.php");
}

// 2. ID КЛАССА (Вы сказали номер 2)
$class_id = 2; 

// Константа для перевода миль в км
const MILE_TO_KM = 1.60934;

// 3. Функция для получения топа
function getTop($conn, $class_id, $type) {
    $sql = "";
    // Учитываем настройки таблицы (милли, секунды)
    switch ($type) {
        case 'speed': // Макс скорость
            $sql = "SELECT u.firstname, u.surname, MAX(a.max_speed) as val, a.start_date
                    FROM run_wind_activities a
                    JOIN run_users u ON a.user_id = u.id
                    WHERE a.class_id = $class_id
                    GROUP BY a.user_id
                    ORDER BY val DESC LIMIT 10";
            break;
        case 'marathon': // Марафон (дистанция > 42км (26.1 миль), лучшее время)
            $sql = "SELECT u.firstname, u.surname, MIN(a.elapsed_time) as val, a.start_date
                    FROM run_wind_activities a
                    JOIN run_users u ON a.user_id = u.id
                    WHERE a.class_id = $class_id AND a.distance >= 26.0976
                    GROUP BY a.user_id
                    ORDER BY val ASC LIMIT 10";
            break;
        case 'konyuhov': // Макс дистанция за 1 заезд
            $sql = "SELECT u.firstname, u.surname, MAX(a.distance) as val, a.start_date
                    FROM run_wind_activities a
                    JOIN run_users u ON a.user_id = u.id
                    WHERE a.class_id = $class_id
                    GROUP BY a.user_id
                    ORDER BY val DESC LIMIT 10";
            break;
        case 'gump': // Сумма всех дистанций
            $sql = "SELECT u.firstname, u.surname, SUM(a.distance) as val, MAX(a.start_date) as start_date
                    FROM run_wind_activities a
                    JOIN run_users u ON a.user_id = u.id
                    WHERE a.class_id = $class_id
                    GROUP BY a.user_id
                    ORDER BY val DESC LIMIT 10";
            break;
    }
    
    // Выполняем запрос
    // Внимание: используем глобальное соединение $db_conn из конфига или передаем его
    global $db_conn; 
    // Если у вас в конфиге используется функция db_select, используйте её:
    if (function_exists('db_select')) {
        return db_select($sql);
    } else {
        // Стандартный mysqli
        $res = mysqli_query($db_conn, $sql);
        $data = [];
        while($row = mysqli_fetch_assoc($res)) $data[] = $row;
        return $data;
    }
}

// 4. Загружаем данные
$top_speed = getTop($db_conn, $class_id, 'speed');
$top_marathon = getTop($db_conn, $class_id, 'marathon');
$top_konyuhov = getTop($db_conn, $class_id, 'konyuhov');
$top_gump = getTop($db_conn, $class_id, 'gump');

// Вспомогательная функция форматирования
function formatVal($val, $type) {
    if ($type == 'speed') return round($val * MILE_TO_KM, 2) . ' км/ч';
    if ($type == 'marathon') return gmdate("H:i:s", $val);
    return round($val * MILE_TO_KM, 2) . ' км';
}
?><!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KITE SKI | LORD OF THE WINDS</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/svg+xml" href="../../assets/img/favicon.png"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@100..800&family=Unbounded:wght@200..900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'graphite': '#0F0F0F',
                        'acid-lime': '#00F3FF',
                        'ice-white': '#F0F0F0',
                    },
                    fontFamily: {
                        'display': ['"Unbounded"', 'sans-serif'],
                        'mono': ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body { background-color: #0F0F0F; color: #F0F0F0; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Анимация для табов */
        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }
        .tab-content.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body data-class-id="9"> 
<a href="../../" class="fixed top-40 left-4 z-50 -rotate-90 origin-left font-display font-black text-2xl tracking-tighter text-white hover:text-acid-lime transition-colors mix-blend-difference py-2">
        LOW 2026
    </a>
    <nav class="fixed w-full z-50 mix-blend-difference px-4 py-6">
        <div class="container mx-auto flex justify-end items-center">
            
            <a href="../../" class="flex items-center gap-2 group">
                <span class="font-mono text-xs text-white group-hover:text-acid-lime transition-colors uppercase">На главную</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white group-hover:text-acid-lime group-hover:translate-x-1 transition-transform">
                    <line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>
    </nav>

    <section class="pt-32 pb-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 font-display text-[20vw] font-black text-white opacity-[0.02] leading-none select-none pointer-events-none">
            02
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="flex items-center gap-4 mb-4">
                <span class="border border-acid-lime text-acid-lime px-3 py-1 text-xs font-mono font-bold uppercase">Класс</span>
            </div>
            
            <h1 class="font-display text-4xl md:text-7xl lg:text-8xl text-white uppercase mb-8 leading-none">
                КАЙТ <span class="text-transparent" style="-webkit-text-stroke: 1px #FFF;">ЛЫЖИ</span>
            </h1>
            
            <p class="text-white/70 max-w-2xl text-sm md:text-base font-mono leading-relaxed border-l-2 border-acid-lime pl-6">
            Самый скоростной класс. Горные или беговые лыжи + кайт. Высокие скорости и марафонские дистанции.
            </p>
        </div>
    </section>

    <section class="py-8 border-y border-white/10 bg-white/5">
        <div class="container mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 border border-white/20 bg-black flex items-center justify-center font-bold text-white/30 text-[10px] uppercase text-center p-2">
                    LOGO
                </div>
                <div>
                    <div class="text-acid-lime font-mono text-[10px] font-bold uppercase mb-1">Официальный партнер класса</div>
                    <h3 class="font-display text-xl text-white uppercase">COMPANY NAME</h3>
                </div>
            </div>
            <div class="text-right hidden md:block">
                <a href="mailto:lordofthewinds2026@gmail.com" class="text-white/50 text-xs font-mono hover:text-white underline decoration-acid-lime">
                    Перейти на сайт партнера ->
                </a>
            </div>
        </div>
    </section>

    <section class="py-16 min-h-[600px]">
        <div class="container mx-auto px-4">
            
            <div class="flex overflow-x-auto no-scrollbar gap-8 mb-8 border-b border-white/10 pb-4">
                <button onclick="switchTab('speed')" id="btn-speed" class="tab-btn active text-lg md:text-2xl font-display uppercase whitespace-nowrap text-acid-lime transition-colors">
                    Скорость
                </button>
                <button onclick="switchTab('marathon')" id="btn-marathon" class="tab-btn text-lg md:text-2xl font-display uppercase whitespace-nowrap text-white/30 hover:text-white transition-colors">
                    Марафон
                </button>
                <button onclick="switchTab('konyuhov')" id="btn-konyuhov" class="tab-btn text-lg md:text-2xl font-display uppercase whitespace-nowrap text-white/30 hover:text-white transition-colors">
                    Конюхов
                </button>
                <button onclick="switchTab('gump')" id="btn-gump" class="tab-btn text-lg md:text-2xl font-display uppercase whitespace-nowrap text-white/30 hover:text-white transition-colors">
                    Форрест Гамп
                </button>
            </div>

            <div class="flex justify-between items-center mb-12">
                <div class="flex gap-1 bg-white/5 p-1">
                    <button class="px-4 py-2 text-xs font-mono font-bold bg-acid-lime text-black uppercase transition-colors">Общий</button>
                    <button class="px-4 py-2 text-xs font-mono font-bold text-white/50 hover:text-white hover:bg-white/10 uppercase transition-colors">Мужчины</button>
                    <button class="px-4 py-2 text-xs font-mono font-bold text-white/50 hover:text-white hover:bg-white/10 uppercase transition-colors">Женщины</button>
                </div>
                
            </div>

            <div id="tab-speed" class="tab-content active">
                <?php if($top_speed): ?>
                    <div class="overflow-x-auto bg-white/5 border border-white/10 p-6">
                        <table class="w-full text-left border-collapse">
                            <thead class="font-mono text-xs text-white/50 uppercase border-b border-white/10">
                                <tr>
                                    <th class="py-4 pl-4 w-12">#</th>
                                    <th class="py-4">Райдер</th>
                                    <th class="py-4 text-right pr-4">Результат</th>
                                    <th class="py-4 text-right pr-4 hidden md:table-cell">Дата</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm font-display text-white">
                                <?php foreach($top_speed as $i => $row): ?>
                                <tr class="border-b border-white/5 hover:bg-white/10 transition-colors group">
                                    <td class="py-4 pl-4 text-white/30 font-mono"><?= $i+1 ?></td>
                                    <td class="py-4 uppercase tracking-wider group-hover:text-acid-lime transition-colors">
                                        <?= $row['surname'] ?> <?= $row['firstname'] ?>
                                    </td>
                                    <td class="py-4 text-right pr-4 font-bold text-acid-lime text-lg">
                                        <?= formatVal($row['val'], 'speed') ?>
                                    </td>
                                    <td class="py-4 text-right pr-4 text-white/30 font-mono text-xs hidden md:table-cell">
                                        <?= date('d.m.Y', strtotime($row['start_date'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="w-full h-64 border border-white/10 bg-white/5 flex flex-col items-center justify-center text-center p-8">
                         <div class="font-display text-xl text-white/30 uppercase">Нет данных</div>
                    </div>
                <?php endif; ?>
            </div>

            <div id="tab-marathon" class="tab-content">
                <?php if($top_marathon): ?>
                    <div class="overflow-x-auto bg-white/5 border border-white/10 p-6">
                        <table class="w-full text-left border-collapse">
                            <thead class="font-mono text-xs text-white/50 uppercase border-b border-white/10">
                                <tr>
                                    <th class="py-4 pl-4 w-12">#</th>
                                    <th class="py-4">Райдер</th>
                                    <th class="py-4 text-right pr-4">Время</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm font-display text-white">
                                <?php foreach($top_marathon as $i => $row): ?>
                                <tr class="border-b border-white/5 hover:bg-white/10 transition-colors">
                                    <td class="py-4 pl-4 text-white/30 font-mono"><?= $i+1 ?></td>
                                    <td class="py-4 uppercase tracking-wider"><?= $row['surname'] ?> <?= $row['firstname'] ?></td>
                                    <td class="py-4 text-right pr-4 font-bold text-acid-lime text-lg">
                                        <?= formatVal($row['val'], 'marathon') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="w-full h-64 border border-white/10 bg-white/5 flex flex-col items-center justify-center text-center p-8">
                        <div class="font-display text-2xl text-white mb-2 uppercase">Марафон</div>
                        <p class="text-white/50 font-mono text-sm">Никто еще не преодолел 42 км.</p>
                    </div>
                <?php endif; ?>
            </div>

          <div id="tab-konyuhov" class="tab-content">
                <?php if($top_konyuhov): ?>
                    <div class="overflow-x-auto bg-white/5 border border-white/10 p-6">
                        <table class="w-full text-left border-collapse">
                            <thead class="font-mono text-xs text-white/50 uppercase border-b border-white/10">
                                <tr>
                                    <th class="py-4 pl-4 w-12">#</th>
                                    <th class="py-4">Герой</th>
                                    <th class="py-4 text-right pr-4">Дистанция</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm font-display text-white">
                                <?php foreach($top_konyuhov as $i => $row): ?>
                                <tr class="border-b border-white/5 hover:bg-white/10 transition-colors">
                                    <td class="py-4 pl-4 text-white/30 font-mono"><?= $i+1 ?></td>
                                    <td class="py-4 uppercase tracking-wider"><?= $row['surname'] ?> <?= $row['firstname'] ?></td>
                                    <td class="py-4 text-right pr-4 font-bold text-acid-lime text-lg">
                                        <?= formatVal($row['val'], 'konyuhov') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="w-full h-64 border border-white/10 bg-white/5 flex flex-col items-center justify-center text-center p-8">
                         <div class="font-display text-xl text-white/30 uppercase">Нет данных</div>
                    </div>
                <?php endif; ?>
            </div>

            <div id="tab-gump" class="tab-content">
                <?php if($top_gump): ?>
                    <div class="overflow-x-auto bg-white/5 border border-white/10 p-6">
                        <table class="w-full text-left border-collapse">
                            <thead class="font-mono text-xs text-white/50 uppercase border-b border-white/10">
                                <tr>
                                    <th class="py-4 pl-4 w-12">#</th>
                                    <th class="py-4">Упорный райдер</th>
                                    <th class="py-4 text-right pr-4">Всего км</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm font-display text-white">
                                <?php foreach($top_gump as $i => $row): ?>
                                <tr class="border-b border-white/5 hover:bg-white/10 transition-colors">
                                    <td class="py-4 pl-4 text-white/30 font-mono"><?= $i+1 ?></td>
                                    <td class="py-4 uppercase tracking-wider"><?= $row['surname'] ?> <?= $row['firstname'] ?></td>
                                    <td class="py-4 text-right pr-4 font-bold text-acid-lime text-lg">
                                        <?= formatVal($row['val'], 'gump') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="w-full h-64 border border-white/10 bg-white/5 flex flex-col items-center justify-center text-center p-8">
                         <div class="font-display text-xl text-white/30 uppercase">Нет данных</div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>
    
    <footer class="bg-black py-12 border-t border-white/10 text-center">
        <div class="text-white/30 text-xs font-mono">© 2026 LORD OF THE WINDS</div>
    </footer>

    <script>
        function switchTab(tabId) {
            // 1. Сброс кнопок
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('text-acid-lime');
                btn.classList.add('text-white/30');
            });
            // 2. Активная кнопка
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.classList.remove('text-white/30');
            activeBtn.classList.add('text-acid-lime');

            // 3. Скрытие контента
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            // 4. Показ контента
            document.getElementById('tab-' + tabId).classList.add('active');
        }
    </script>
</body>
</html>
