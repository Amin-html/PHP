<?php
// Инициализируем переменные для экрана калькулятора
$display = '0';
$expression = '';

// Проверяем, была ли нажата кнопка
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $display = $_POST['display'] ?? '0';
    $expression = $_POST['expression'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($action === 'AC' || $action === 'C') {
        // Очистить всё
        $display = '0';
        $expression = '';
    } elseif ($action === '+/-') {
        // Смена знака числового значения
        if ($display !== '0') {
            $display = str_starts_with($display, '-') ? substr($display, 1) : '-' . $display;
            // Упрощенное обновление выражения для текущего числа
            $expression = $display;
        }
    } elseif ($action === '%') {
        // Процент
        $display = (float)$display / 100;
        $expression = (string)$display;
    } elseif ($action === '=') {
        // Считаем результат с помощью PHP (простейший безопасный парсер)
        if (!empty($expression)) {
            // Заменяем символы деления и умножения на понятные для расчетов
            $calcExpr = str_replace(['×', '÷'], ['*', '/'], $expression);
            // Валидация: разрешаем только цифры и знаки операций
            if (preg_match('/^[0-9.+\-*\/]+$/', $calcExpr)) {
                try {
                    // Используем eval только после жесткой фильтрации регулярным выражением
                    $result = eval("return $calcExpr;");
                    $display = $result;
                    $expression = (string)$result;
                } catch (Throwable $e) {
                    $display = 'Ошибка';
                    $expression = '';
                }
            } else {
                $display = 'Ошибка';
                $expression = '';
            }
        }
    } else {
        // Нажата цифра или оператор (+, -, ×, ÷)
        if ($display === '0' && is_numeric($action)) {
            $display = $action;
            $expression = $action;
        } else {
            // Если нажат оператор, визуально обновляем экран, но в память пишем всё выражение
            if (in_array($action, ['+', '-', '×', '÷'])) {
                $display = $action;
            } else {
                if (in_array($display, ['+', '-', '×', '÷'])) {
                    $display = $action;
                } else {
                    $display .= $action;
                }
            }
            $expression .= $action;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iPhone Calculator</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Helvetica Neue", Arial, sans-serif;
            user-select: none;
        }
        body {
            background-color: #1c1c1e;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .calculator {
            background-color: #000000;
            width: 320px;
            height: 520px;
            border-radius: 40px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        .display-container {
            text-align: right;
            padding: 10px;
            color: #ffffff;
            margin-bottom: 10px;
        }
        .display {
            font-size: 3.5rem;
            font-weight: 300;
            overflow-x: auto;
            white-space: nowrap;
        }
        .buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        button {
            border: none;
            outline: none;
            height: 60px;
            width: 60px;
            border-radius: 50%;
            font-size: 1.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: opacity 0.1s;
        }
        button:active {
            opacity: 0.6;
        }
        /* Цвета кнопок в стиле iOS */
        .btn-gray {
            background-color: #a5a5a5;
            color: #000000;
        }
        .btn-dark {
            background-color: #333333;
            color: #ffffff;
        }
        .btn-orange {
            background-color: #ff9f0a;
            color: #ffffff;
            font-size: 1.8rem;
        }
        /* Особая широкая кнопка ноль */
        .btn-zero {
            grid-column: span 2;
            width: 132px;
            border-radius: 30px;
            text-align: left;
            padding-left: 23px;
        }
    </style>
</head>
<body>

<div class="calculator">
    <!-- Скрытая форма для отправки данных в PHP при каждом клике -->
    <form method="POST" action="">
        <input type="hidden" name="display" value="<?php echo htmlspecialchars($display); ?>">
        <input type="hidden" name="expression" value="<?php echo htmlspecialchars($expression); ?>">

        <div class="display-container">
            <div class="display"><?php echo htmlspecialchars($display); ?></div>
        </div>

        <div class="buttons">
            <!-- Верхний ряд управления -->
            <button type="submit" name="action" value="AC" class="btn-gray">AC</button>
            <button type="submit" name="action" value="+/-" class="btn-gray">±</button>
            <button type="submit" name="action" value="%" class="btn-gray">%</button>
            <button type="submit" name="action" value="÷" class="btn-orange">÷</button>

            <!-- Цифры и операции -->
            <button type="submit" name="action" value="7" class="btn-dark">7</button>
            <button type="submit" name="action" value="8" class="btn-dark">8</button>
            <button type="submit" name="action" value="9" class="btn-dark">9</button>
            <button type="submit" name="action" value="×" class="btn-orange">×</button>

            <button type="submit" name="action" value="4" class="btn-dark">4</button>
            <button type="submit" name="action" value="5" class="btn-dark">5</button>
            <button type="submit" name="action" value="6" class="btn-dark">6</button>
            <button type="submit" name="action" value="-" class="btn-orange">-</button>

            <button type="submit" name="action" value="1" class="btn-dark">1</button>
            <button type="submit" name="action" value="2" class="btn-dark">2</button>
            <button type="submit" name="action" value="3" class="btn-dark">3</button>
            <button type="submit" name="action" value="+" class="btn-orange">+</button>

            <button type="submit" name="action" value="0" class="btn-dark btn-zero">0</button>
            <button type="submit" name="action" value="." class="btn-dark">.</button>
            <button type="submit" name="action" value="=" class="btn-orange">=</button>
        </div>
    </form>
</div>

</body>
</html>
