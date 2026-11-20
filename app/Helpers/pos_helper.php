<?php

function field_value(string $field, mixed $default = ''): string
{
    return esc((string) old($field, $default, false));
}
function money(mixed $value): string
{
    return '₱' . number_format((float) $value, 2);
}
function media_url(string $kind, ?string $filename): string
{
    return $filename ? site_url('media/' . $kind . '/' . $filename) : base_url('assets/placeholder.svg');
}
