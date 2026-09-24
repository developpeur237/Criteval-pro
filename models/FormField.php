<?php
declare(strict_types=1);

/** Server-side contract for fields created by the form builder. */
class FormField
{
    public const TYPES = ['text', 'email', 'number', 'date', 'textarea', 'select', 'checkbox', 'radio', 'file', 'heading', 'paragraph', 'divider', 'section'];

    public static function normalize(array $item, int $index): array
    {
        $type = (string) ($item['type'] ?? '');
        if (!in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('Type de champ non autorise.');
        }
        $name = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) ($item['name'] ?? 'champ_' . $index));
        $name = trim((string) $name, '_') ?: 'champ_' . $index;
        $options = array_values(array_filter(array_map(static fn ($option): string => trim(strip_tags((string) $option)), preg_split('/[,;\r\n]+/', (string) ($item['options'] ?? '')) ?: []), static fn (string $option): bool => $option !== ''));
        $clamp = static fn (mixed $value, int $min, int $max, int $default): int => max($min, min($max, (int) ($value ?? $default)));
        $choice = static fn (mixed $value, array $allowed, string $default): string => in_array((string) $value, $allowed, true) ? (string) $value : $default;
        $hex = static fn (mixed $value, string $default): string => preg_match('/^#[0-9a-f]{6}$/i', (string) $value) ? (string) $value : $default;
        return [
            'id' => substr((string) ($item['id'] ?? 'field-' . $index), 0, 80),
            'type' => $type,
            'label' => self::clip(trim(strip_tags((string) ($item['label'] ?? 'Champ'))), 180),
            'name' => substr($name, 0, 80),
            'placeholder' => self::clip(trim(strip_tags((string) ($item['placeholder'] ?? ''))), 500),
            'options' => self::clip(implode("\n", $options), 2000),
            'required' => !empty($item['required']),
            'span' => max(1, min(12, (int) ($item['span'] ?? 6))),
            'rowSpan' => max(1, min(6, (int) ($item['rowSpan'] ?? 1))),
            'scale' => max(75, min(125, (int) ($item['scale'] ?? 100))),
            'background' => preg_match('/^#[0-9a-f]{6}$/i', (string) ($item['background'] ?? '')) ? $item['background'] : '#ffffff',
            'color' => preg_match('/^#[0-9a-f]{6}$/i', (string) ($item['color'] ?? '')) ? $item['color'] : '#1e293b',
            'radius' => max(0, min(24, (int) ($item['radius'] ?? 10))),
            'widthValue' => $clamp($item['widthValue'] ?? 100, 1, 2000, 100), 'widthUnit' => $choice($item['widthUnit'] ?? '%', ['%', 'px', 'auto'], '%'),
            'heightValue' => $clamp($item['heightValue'] ?? 0, 0, 2000, 0), 'heightUnit' => $choice($item['heightUnit'] ?? 'auto', ['auto', 'px', '%'], 'auto'),
            'marginTop' => $clamp($item['marginTop'] ?? 0, -200, 500, 0), 'marginRight' => $clamp($item['marginRight'] ?? 0, -200, 500, 0), 'marginBottom' => $clamp($item['marginBottom'] ?? 0, -200, 500, 0), 'marginLeft' => $clamp($item['marginLeft'] ?? 0, -200, 500, 0),
            'paddingTop' => $clamp($item['paddingTop'] ?? 14, 0, 500, 14), 'paddingRight' => $clamp($item['paddingRight'] ?? 14, 0, 500, 14), 'paddingBottom' => $clamp($item['paddingBottom'] ?? 14, 0, 500, 14), 'paddingLeft' => $clamp($item['paddingLeft'] ?? 14, 0, 500, 14),
            'position' => $choice($item['position'] ?? 'normal', ['normal', 'relative', 'absolute'], 'normal'), 'offsetX' => $clamp($item['offsetX'] ?? 0, -2000, 2000, 0), 'offsetY' => $clamp($item['offsetY'] ?? 0, -2000, 2000, 0), 'zIndex' => $clamp($item['zIndex'] ?? 1, 0, 9999, 1),
            'borderWidth' => $clamp($item['borderWidth'] ?? 1, 0, 30, 1), 'borderStyle' => $choice($item['borderStyle'] ?? 'solid', ['none', 'solid', 'dashed', 'dotted', 'double'], 'solid'), 'borderColor' => $hex($item['borderColor'] ?? '#d5dce7', '#d5dce7'),
            'opacity' => $clamp($item['opacity'] ?? 100, 0, 100, 100), 'shadow' => $choice($item['shadow'] ?? 'soft', ['none', 'soft', 'medium', 'strong'], 'soft'), 'textAlign' => $choice($item['textAlign'] ?? 'left', ['left', 'center', 'right', 'justify'], 'left'),
            'fontSize' => $clamp($item['fontSize'] ?? 14, 8, 72, 14), 'fontWeight' => $choice($item['fontWeight'] ?? '600', ['400', '500', '600', '700', '800'], '600'), 'lineHeight' => $clamp($item['lineHeight'] ?? 140, 80, 240, 140), 'letterSpacing' => $clamp($item['letterSpacing'] ?? 0, -10, 30, 0),
            'visibility' => $choice($item['visibility'] ?? 'visible', ['visible', 'hidden'], 'visible'), 'overflow' => $choice($item['overflow'] ?? 'visible', ['visible', 'hidden', 'auto'], 'visible'),
        ];
    }

    private static function clip(string $value, int $length): string
    {
        return function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length);
    }
}
