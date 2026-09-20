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
        ];
    }

    private static function clip(string $value, int $length): string
    {
        return function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length);
    }
}
