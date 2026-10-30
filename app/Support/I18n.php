<?php
namespace App\Support;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class I18n {
    public static function providerType(?string $name): string {
        if (!$name) return '';
        $variants = [];
        // A: lowercase, all non-alnum → _
        $v = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($name)), '_');
        if ($v) $variants[] = $v;
        // B: Laravel slug (strips / and special)
        $s = Str::slug($name, '_');
        if ($s) $variants[] = $s;
        // C: space only → _
        $w = str_replace(' ', '_', strtolower(trim($name)));
        if ($w) $variants[] = $w;
        // D: kebab of A
        if ($v) $variants[] = str_replace('_', '-', $v);

        foreach (array_unique($variants) as $slug) {
            if (!$slug) continue;
            $key = 'messages.provider_type_' . $slug;
            if (Lang::has($key)) return __($key);
        }
        return $name;
    }

    public static function serviceCategory(?string $name): string {
        if (!$name) return '';
        $variants = [];
        $v = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($name)), '_');
        if ($v) $variants[] = $v;
        $s = Str::slug($name, '_');
        if ($s) $variants[] = $s;
        $w = str_replace(' ', '_', strtolower(trim($name)));
        if ($w) $variants[] = $w;
        foreach (array_unique($variants) as $slug) {
            if (!$slug) continue;
            $key = 'messages.service_category_' . $slug;
            if (Lang::has($key)) return __($key);
        }
        return $name;
    }

    public static function difficulty(?string $v): string {
        if (!$v) return '';
        $key = 'messages.difficulty_' . strtolower(trim($v));
        return Lang::has($key) ? __($key) : $v;
    }
    public static function serviceTypeBadge(?string $name): string {
        if (!$name) return '';
        $variants = [];
        $v = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($name)), '_');
        if ($v) $variants[] = $v;
        $s = Str::slug($name, '_');
        if ($s) $variants[] = $s;
        foreach (array_unique($variants) as $slug) {
            if (!$slug) continue;
            $key = 'messages.service_type_' . $slug;
            if (Lang::has($key)) return __($key);
        }
        return strtoupper($name);
    }

    public static function serviceTypeText(?string $name): string {
        if (!$name) return '';
        $variants = [];
        $v = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($name)), '_');
        if ($v) $variants[] = $v;
        $s = Str::slug($name, '_');
        if ($s) $variants[] = $s;
        foreach (array_unique($variants) as $slug) {
            if (!$slug) continue;
            $key = 'messages.service_type_text_' . $slug;
            if (Lang::has($key)) return __($key);
        }
        return strtoupper($name);
    }
}
