<?php

namespace App\Support;

/**
 * Gold line icons for the cruise page (the "Why book with us" strip and the
 * onboard experience cards). Admins pick one by
 * name; badges saved before the picker existed hold an emoji, which is
 * matched to the nearest icon (anything unknown shows the star).
 */
class StripIcons
{
    private const ICONS = [
        'ship'     => ['Cruise ship', '<path d="M3 17c2 1.5 4 1.5 6 0s4-1.5 6 0 4 1.5 6 0"/><path d="M5 14l-1-4h16l-1 4"/><path d="M8 10V6h8v4"/><path d="M11 6V3h2v3"/>'],
        'dining'   => ['Dining', '<path d="M7 3v8"/><path d="M5 3v4a2 2 0 0 0 4 0V3"/><path d="M7 11v10"/><path d="M17 3c-2 0-3 2-3 5v5h3"/><path d="M17 3v18"/>'],
        'veg'      => ['Leaf (veg)', '<path d="M5 19c0-8 5-14 14-15-1 9-7 14-15 15z"/><path d="M5 19l7-7"/>'],
        'support'  => ['Headset (support, DJ)', '<path d="M4 13a8 8 0 0 1 16 0"/><path d="M4 13v4a2 2 0 0 0 2 2h1v-6H4zM20 13v4a2 2 0 0 1-2 2h-1v-6h3z"/>'],
        'phone'    => ['Phone', '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>'],
        'star'     => ['Star', '<path d="M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.6 6.6 19.5l1.2-6-4.5-4.2 6.1-.7z"/>'],
        'shield'   => ['Shield (trust)', '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/><path d="M9 12l2 2 4-4"/>'],
        'tag'      => ['Price tag', '<path d="M20 12l-8 8-9-9V3h8z"/><circle cx="7.5" cy="7.5" r="1.5"/>'],
        'heart'    => ['Heart', '<path d="M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.5-7 10-7 10z"/>'],
        'family'   => ['Family', '<circle cx="9" cy="7" r="3"/><circle cx="17" cy="9" r="2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M15 20c0-2 1-4 3-4s3 2 3 4"/>'],
        'calendar' => ['Calendar', '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>'],
        'globe'    => ['Globe', '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>'],
        'masks'    => ['Theatre masks', '<path d="M4 5h7v6a3.5 3.5 0 0 1-7 0z"/><path d="M13 9h7v6a3.5 3.5 0 0 1-7 0z"/><path d="M6 8.5h.01M9 8.5h.01M15 12.5h.01M18 12.5h.01"/>'],
        'music'    => ['Music', '<path d="M9 18V5l11-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="17" cy="16" r="3"/>'],
        'film'     => ['Movies', '<rect x="3" y="8" width="18" height="12" rx="1"/><path d="M3 8l3-5M9 8l3-5M15 8l3-5"/>'],
        'sparkles' => ['Sparkles', '<path d="M12 3l2 5 5 2-5 2-2 5-2-5-5-2 5-2z"/><path d="M19 15l.8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8z"/>'],
        'sun'      => ['Sun (all day)', '<circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M5.6 18.4L7 17M17 7l1.4-1.4"/>'],
        'tent'     => ['Circus tent', '<path d="M3 20l9-15 9 15z"/><path d="M12 5v15"/><path d="M8 20l4-7 4 7"/>'],
        'glass'    => ['Drinks', '<path d="M8 3h8l-1 8a3 3 0 0 1-6 0z"/><path d="M12 14v7M8 21h8"/>'],
        'sunset'   => ['Sunset', '<path d="M17 18a5 5 0 0 0-10 0"/><path d="M12 9v2M4.2 10.2l1.4 1.4M18.4 11.6l1.4-1.4M2 18h20M5 22h14"/>'],
        'moon'     => ['Night', '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/>'],
        'spa'      => ['Spa', '<path d="M12 20c-4 0-8-2.5-8-7 3 0 6 1.5 8 4 2-2.5 5-4 8-4 0 4.5-4 7-8 7z"/><path d="M12 17c-1.5-2-2-4-2-6 0-2.5 1-4.5 2-6 1 1.5 2 3.5 2 6 0 2-.5 4-2 6z"/>'],
        'fitness'  => ['Fitness', '<path d="M6 7v10M18 7v10M3 10v4M21 10v4M6 12h12"/>'],
        'climb'    => ['Adventure', '<path d="M3 20l6-11 4 6 3-4 5 9z"/><path d="M14 6a2 2 0 1 0 0-.01"/>'],
        'shopping' => ['Shopping', '<path d="M5 8h14l-1 13H6z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>'],
        'boat'     => ['Boat trips', '<path d="M3 15l2 4h14l2-4z"/><path d="M12 15V4l6 9h-6"/><path d="M2 21c2 1 4 1 6 0s4-1 6 0 4 1 6 0"/>'],
        'kids'     => ['Kids', '<circle cx="12" cy="9" r="6"/><path d="M12 3v12M6 9h12M7.8 4.8l8.4 8.4M16.2 4.8l-8.4 8.4"/><path d="M8 21l4-6 4 6"/>'],
        'briefcase'=> ['Business', '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18"/>'],
        'rings'    => ['Weddings', '<circle cx="9" cy="14" r="5"/><circle cx="15" cy="14" r="5"/><path d="M10 5l2-2 2 2-2 3z"/>'],
    ];

    private const EMOJI = [
        '🚢' => 'ship', '⛴' => 'ship', '🛳' => 'ship', '🍽' => 'dining', '🍴' => 'dining',
        '🙏' => 'veg', '🥗' => 'veg', '🌱' => 'veg', '📞' => 'phone', '☎' => 'phone', '🎧' => 'support',
        '⭐' => 'star', '✨' => 'sparkles', '🛡' => 'shield', '✅' => 'shield', '💰' => 'tag', '🏷' => 'tag',
        '❤' => 'heart', '💖' => 'heart', '👨' => 'family', '📅' => 'calendar', '🌍' => 'globe', '🌏' => 'globe',
        '🎭' => 'masks', '🎵' => 'music', '🎶' => 'music', '🎤' => 'music', '🎬' => 'film', '🎥' => 'film',
        '🎪' => 'tent', '☀' => 'sun', '🥂' => 'glass', '🍸' => 'glass', '🍷' => 'glass', '🌅' => 'sunset',
        '🌙' => 'moon', '💆' => 'spa', '🧖' => 'spa', '💪' => 'fitness', '🏋' => 'fitness', '🧗' => 'climb',
        '🛍' => 'shopping', '🚤' => 'boat', '⛵' => 'boat', '🎡' => 'kids', '🧸' => 'kids', '💼' => 'briefcase',
        '💍' => 'rings', '💒' => 'rings',
    ];

    /** Choices for the admin picker: name => label. */
    public static function options(): array
    {
        return array_map(fn ($icon) => $icon[0], self::ICONS);
    }

    /** The icon name for a saved value (a name, or an emoji from older saves). */
    public static function resolve(?string $value): string
    {
        $value = trim((string) $value);
        if (isset(self::ICONS[$value])) {
            return $value;
        }
        foreach (self::EMOJI as $emoji => $name) {
            if ($value !== '' && str_starts_with($value, $emoji)) {
                return $name;
            }
        }

        return 'star';
    }

    /** Inline SVG for a saved value; inherits its colour from the text. */
    public static function svg(?string $value): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            .self::ICONS[self::resolve($value)][1].'</svg>';
    }
}
