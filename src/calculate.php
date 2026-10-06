<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['history']) || !is_array($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

/**
 * Небольшой безопасный вычислитель арифметических выражений.
 * Поддерживает: + - * / % ( ) унарный минус, десятичные числа.
 * Никакого eval() — ручной токенайзер + рекурсивный спуск.
 */
final class MathError extends \Exception {}

final class Tokenizer
{
    /** @var array<int,array{type:string,value:string}> */
    private array $tokens = [];
    private int $pos = 0;

    public function __construct(string $expr)
    {
        $len = strlen($expr);
        $i = 0;
        while ($i < $len) {
            $ch = $expr[$i];

            if ($ch === ' ' || $ch === "\t") {
                $i++;
                continue;
            }

            if (ctype_digit($ch) || $ch === '.') {
                $num = '';
                while ($i < $len && (ctype_digit($expr[$i]) || $expr[$i] === '.')) {
                    $num .= $expr[$i];
                    $i++;
                }
                if (substr_count($num, '.') > 1) {
                    throw new MathError('Некорректное число');
                }
                $this->tokens[] = ['type' => 'num', 'value' => $num];
                continue;
            }

            if (in_array($ch, ['+', '-', '*', '/', '%', '(', ')'], true)) {
                $this->tokens[] = ['type' => 'op', 'value' => $ch];
                $i++;
                continue;
            }

            throw new MathError('Недопустимый символ: ' . $ch);
        }
    }

    public function peek(): ?array
    {
        return $this->tokens[$this->pos] ?? null;
    }

    public function next(): ?array
    {
        return $this->tokens[$this->pos++] ?? null;
    }
}

final class Parser
{
    private Tokenizer $tok;

    public function __construct(Tokenizer $tok)
    {
        $this->tok = $tok;
    }

    public function parse(): float
    {
        $result = $this->parseExpression();
        if ($this->tok->peek() !== null) {
            throw new MathError('Лишние символы в выражении');
        }
        return $result;
    }

    // expression := term (('+' | '-') term)*
    private function parseExpression(): float
    {
        $value = $this->parseTerm();
        while (($t = $this->tok->peek()) && in_array($t['value'], ['+', '-'], true)) {
            $this->tok->next();
            $rhs = $this->parseTerm();
            $value = $t['value'] === '+' ? $value + $rhs : $value - $rhs;
        }
        return $value;
    }

    // term := factor (('*' | '/' | '%') factor)*
    private function parseTerm(): float
    {
        $value = $this->parseFactor();
        while (($t = $this->tok->peek()) && in_array($t['value'], ['*', '/', '%'], true)) {
            $this->tok->next();
            $rhs = $this->parseFactor();
            if (($t['value'] === '/' || $t['value'] === '%') && $rhs == 0.0) {
                throw new MathError('Деление на ноль');
            }
            if ($t['value'] === '*') {
                $value *= $rhs;
            } elseif ($t['value'] === '/') {
                $value /= $rhs;
            } else {
                $value = fmod($value, $rhs);
            }
        }
        return $value;
    }

    // factor := ('+' | '-') factor | number | '(' expression ')'
    private function parseFactor(): float
    {
        $t = $this->tok->peek();
        if ($t === null) {
            throw new MathError('Неожиданный конец выражения');
        }

        if ($t['value'] === '-' || $t['value'] === '+') {
            $this->tok->next();
            $val = $this->parseFactor();
            return $t['value'] === '-' ? -$val : $val;
        }

        if ($t['value'] === '(') {
            $this->tok->next();
            $val = $this->parseExpression();
            $close = $this->tok->next();
            if ($close === null || $close['value'] !== ')') {
                throw new MathError('Не хватает закрывающей скобки');
            }
            return $val;
        }

        if ($t['type'] === 'num') {
            $this->tok->next();
            return (float) $t['value'];
        }

        throw new MathError('Ожидалось число или скобка');
    }
}

function format_number(float $n): string
{
    if (is_nan($n) || is_infinite($n)) {
        return 'Ошибка';
    }
    // убираем хвостовые нули, но не плодим экспоненциальную запись
    $rounded = round($n, 10);
    if ($rounded == (int) $rounded && abs($rounded) < 1e15) {
        return (string) (int) $rounded;
    }
    return rtrim(rtrim(sprintf('%.10F', $rounded), '0'), '.');
}

$raw = $_POST['expression'] ?? '';
$expression = is_string($raw) ? trim($raw) : '';

if ($expression === '') {
    echo json_encode(['ok' => false, 'error' => 'Пустое выражение']);
    exit;
}

if (strlen($expression) > 200) {
    echo json_encode(['ok' => false, 'error' => 'Слишком длинное выражение']);
    exit;
}

try {
    $tokenizer = new Tokenizer($expression);
    $parser = new Parser($tokenizer);
    $value = $parser->parse();
    $resultStr = format_number($value);

    $entry = [
        'expression' => $expression,
        'result' => $resultStr,
        'time' => date('H:i:s'),
    ];
    $_SESSION['history'][] = $entry;
    if (count($_SESSION['history']) > 50) {
        $_SESSION['history'] = array_slice($_SESSION['history'], -50);
    }

    echo json_encode([
        'ok' => true,
        'result' => $resultStr,
        'history' => array_reverse($_SESSION['history']),
    ]);
} catch (MathError $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'Ошибка вычисления']);
}