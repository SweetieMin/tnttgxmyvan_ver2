<?php

namespace App\Support;

use App\Models\User;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class BadgePreviewPngExporter
{
    private const CANVAS_WIDTH = 750;

    private const CANVAS_HEIGHT = 1100;

    private const DEFAULT_BACKGROUND_COLOR = '#fff3cb';

    private const DEFAULT_NAME_PANEL_COLOR = '#efd089';

    private const TEXT_COLOR = '#c5a061';

    private const HEADING_COLOR = '#c79f57';

    private const FONT_REGULAR = 'vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf';

    private const FONT_BOLD = 'vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf';

    /**
     * @param  array<string, array{x:int,y:int,w:int,h:int}>  $blocks
     * @param  array{title:string,subtitle:string,background_color:string,name_panel_color:string,favicon_path:?string}  $options
     */
    public function render(User $user, array $blocks, array $options): string
    {
        $canvas = imagecreatetruecolor(self::CANVAS_WIDTH, self::CANVAS_HEIGHT);

        if ($canvas === false) {
            throw new RuntimeException('Could not create badge canvas.');
        }

        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        $background = $this->allocateColor($canvas, $options['background_color'] ?: self::DEFAULT_BACKGROUND_COLOR);
        imagefilledrectangle($canvas, 0, 0, self::CANVAS_WIDTH, self::CANVAS_HEIGHT, $background);

        $this->drawBackgroundDots($canvas);
        $this->drawNamePanel($canvas, $blocks['name_panel'] ?? null, $options['name_panel_color'] ?: self::DEFAULT_NAME_PANEL_COLOR);
        $this->drawFavicon($canvas, $blocks['logo'] ?? null, $options['favicon_path']);
        $this->drawHeading($canvas, $blocks['heading'] ?? null, $options['title'], $options['subtitle']);
        $this->drawQr($canvas, $blocks['qr'] ?? null, $user);
        $this->drawAvatar($canvas, $blocks['avatar'] ?? null, $user);
        $this->drawLabel($canvas, $blocks['christian_name'] ?? null, Str::upper($user->christian_name ?: ''));
        $this->drawLabel($canvas, $blocks['full_name'] ?? null, Str::upper($user->full_name), 28);

        ob_start();
        imagepng($canvas, null, 9);
        $png = (string) ob_get_clean();
        imagedestroy($canvas);

        if ($png === '') {
            throw new RuntimeException('Could not render badge preview PNG.');
        }

        return $png;
    }

    /**
     * @param  array{x:int,y:int,w:int,h:int}|null  $block
     */
    private function drawNamePanel(\GdImage $canvas, ?array $block, string $hexColor): void
    {
        if ($block === null) {
            return;
        }

        [$x, $y, $width, $height] = $this->blockRect($block);
        $radius = (int) round(min($width, $height) * 0.12);
        $layer = imagecreatetruecolor(self::CANVAS_WIDTH, self::CANVAS_HEIGHT);

        imagealphablending($layer, false);
        imagesavealpha($layer, true);

        $transparent = imagecolorallocatealpha($layer, 0, 0, 0, 127);
        imagefill($layer, 0, 0, $transparent);

        $color = $this->allocateColor($layer, $hexColor, 75);
        $this->filledRoundedRectangle($layer, $x, $y, $width, $height, $radius, $color);

        imagealphablending($canvas, true);
        imagecopy($canvas, $layer, 0, 0, 0, 0, self::CANVAS_WIDTH, self::CANVAS_HEIGHT);
        imagedestroy($layer);
    }

    /**
     * @param  array{x:int,y:int,w:int,h:int}|null  $block
     */
    private function drawFavicon(\GdImage $canvas, ?array $block, ?string $faviconPath): void
    {
        if ($block === null) {
            return;
        }

        $imagePath = $this->resolveSiteImagePath($faviconPath);

        if ($imagePath === null) {
            return;
        }

        [$x, $y, $width, $height] = $this->blockRect($block);
        $diameter = min($width, $height);
        $image = $this->loadImage($imagePath);

        if ($image === null) {
            return;
        }

        $this->copyCircularImage(
            destination: $canvas,
            source: $image,
            destinationX: $x + (int) round(($width - $diameter) / 2),
            destinationY: $y + (int) round(($height - $diameter) / 2),
            diameter: $diameter,
            innerPadding: 0,
            backgroundHex: '#ffffff',
            drawBackground: false,
        );

        imagedestroy($image);
    }

    /**
     * @param  array{x:int,y:int,w:int,h:int}|null  $block
     */
    private function drawHeading(\GdImage $canvas, ?array $block, string $title, string $subtitle): void
    {
        if ($block === null) {
            return;
        }

        [$x, $y, $width, $height] = $this->blockRect($block);
        $titleFont = $this->fitFontSize(self::FONT_BOLD, $title, $width - 12, 26, 16);
        $subtitleFont = $this->fitFontSize(self::FONT_BOLD, $subtitle, $width - 12, 24, 15);
        $titleHeight = $this->textHeight(self::FONT_BOLD, $titleFont, $title);
        $subtitleHeight = $this->textHeight(self::FONT_BOLD, $subtitleFont, $subtitle);
        $gap = 8;
        $startY = $y + (int) round(($height - ($titleHeight + $gap + $subtitleHeight)) / 2) + $titleHeight;
        $color = $this->allocateColor($canvas, self::HEADING_COLOR);

        $this->drawCenteredText($canvas, self::FONT_BOLD, $titleFont, $title, $x, $width, $startY, $color);
        $this->drawCenteredText($canvas, self::FONT_BOLD, $subtitleFont, $subtitle, $x, $width, $startY + $gap + $subtitleHeight, $color);
    }

    /**
     * @param  array{x:int,y:int,w:int,h:int}|null  $block
     */
    private function drawQr(\GdImage $canvas, ?array $block, User $user): void
    {
        if ($block === null) {
            return;
        }

        [$x, $y, $width, $height] = $this->blockRect($block);
        $size = min($width, $height);
        $padding = max(4, (int) round($size * 0.02));
        $innerSize = max(24, $size - ($padding * 2));
        $qrBinary = (new Writer(new GDLibRenderer($innerSize, 1)))->writeString(url('/profile/'.$user->token));
        $qrImage = imagecreatefromstring($qrBinary);

        if ($qrImage === false) {
            return;
        }

        $cardX = $x + (int) round(($width - $size) / 2);
        $cardY = $y + (int) round(($height - $size) / 2);
        $white = $this->allocateColor($canvas, '#ffffff');

        imagefilledrectangle($canvas, $cardX, $cardY, $cardX + $size, $cardY + $size, $white);
        imagecopyresampled(
            $canvas,
            $qrImage,
            $cardX + $padding,
            $cardY + $padding,
            0,
            0,
            $size - ($padding * 2),
            $size - ($padding * 2),
            imagesx($qrImage),
            imagesy($qrImage),
        );

        imagedestroy($qrImage);
    }

    /**
     * @param  array{x:int,y:int,w:int,h:int}|null  $block
     */
    private function drawAvatar(\GdImage $canvas, ?array $block, User $user): void
    {
        if ($block === null) {
            return;
        }

        $imagePath = $this->resolveAvatarPath($user);

        if ($imagePath === null) {
            return;
        }

        [$x, $y, $width, $height] = $this->blockRect($block);
        $diameter = min($width, $height);
        $image = $this->loadImage($imagePath);

        if ($image === null) {
            return;
        }

        $this->copyCircularImage(
            destination: $canvas,
            source: $image,
            destinationX: $x + (int) round(($width - $diameter) / 2),
            destinationY: $y + (int) round(($height - $diameter) / 2),
            diameter: $diameter,
            innerPadding: 0,
            backgroundHex: '#ffffff',
            drawBackground: false,
        );

        imagedestroy($image);
    }

    /**
     * @param  array{x:int,y:int,w:int,h:int}|null  $block
     */
    private function drawLabel(\GdImage $canvas, ?array $block, string $text, int $maxFont = 26): void
    {
        if ($block === null || $text === '') {
            return;
        }

        [$x, $y, $width, $height] = $this->blockRect($block);
        $fontSize = $this->fitFontSize(self::FONT_BOLD, $text, $width - 10, $maxFont, 14);
        $textHeight = $this->textHeight(self::FONT_BOLD, $fontSize, $text);
        $baseline = $y + (int) round(($height + $textHeight) / 2) - 2;
        $color = $this->allocateColor($canvas, self::TEXT_COLOR);

        $this->drawCenteredText($canvas, self::FONT_BOLD, $fontSize, $text, $x, $width, $baseline, $color);
    }

    private function drawBackgroundDots(\GdImage $canvas): void
    {
        $dotColor = $this->allocateColor($canvas, '#d4ac59', 100);

        for ($y = 40; $y < self::CANVAS_HEIGHT; $y += 110) {
            for ($x = 70; $x < self::CANVAS_WIDTH; $x += 120) {
                imagefilledellipse($canvas, $x, $y, 4, 4, $dotColor);
            }
        }
    }

    /**
     * @param  array{x:int,y:int,w:int,h:int}  $block
     * @return array{0:int,1:int,2:int,3:int}
     */
    private function blockRect(array $block): array
    {
        return [
            (int) round((self::CANVAS_WIDTH * $block['x']) / 100),
            (int) round((self::CANVAS_HEIGHT * $block['y']) / 100),
            (int) round((self::CANVAS_WIDTH * $block['w']) / 100),
            (int) round((self::CANVAS_HEIGHT * $block['h']) / 100),
        ];
    }

    private function drawCenteredText(\GdImage $canvas, string $fontPath, int $fontSize, string $text, int $x, int $width, int $baselineY, int $color): void
    {
        $box = imagettfbbox($fontSize, 0, base_path($fontPath), $text);

        if ($box === false) {
            return;
        }

        $textWidth = (int) round($box[2] - $box[0]);
        $textX = $x + (int) round(($width - $textWidth) / 2);

        imagettftext($canvas, $fontSize, 0, $textX, $baselineY, $color, base_path($fontPath), $text);
    }

    private function textHeight(string $fontPath, int $fontSize, string $text): int
    {
        $box = imagettfbbox($fontSize, 0, base_path($fontPath), $text);

        if ($box === false) {
            return $fontSize;
        }

        return (int) round(abs($box[7] - $box[1]));
    }

    private function fitFontSize(string $fontPath, string $text, int $maxWidth, int $start, int $min): int
    {
        for ($size = $start; $size >= $min; $size--) {
            $box = imagettfbbox($size, 0, base_path($fontPath), $text);

            if ($box === false) {
                continue;
            }

            $textWidth = (int) round($box[2] - $box[0]);

            if ($textWidth <= $maxWidth) {
                return $size;
            }
        }

        return $min;
    }

    private function allocateColor(\GdImage $canvas, string $hexColor, int $alpha = 0): int
    {
        [$red, $green, $blue] = $this->hexToRgb($hexColor);

        return imagecolorallocatealpha($canvas, $red, $green, $blue, $alpha);
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private function hexToRgb(string $hexColor): array
    {
        $sanitized = ltrim($hexColor, '#');

        if (strlen($sanitized) !== 6) {
            $sanitized = ltrim(self::DEFAULT_BACKGROUND_COLOR, '#');
        }

        return [
            hexdec(substr($sanitized, 0, 2)),
            hexdec(substr($sanitized, 2, 2)),
            hexdec(substr($sanitized, 4, 2)),
        ];
    }

    private function resolveSiteImagePath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->path($path)
            : null;
    }

    private function resolveAvatarPath(User $user): ?string
    {
        $user->loadMissing('details');
        $picture = (string) ($user->details?->getRawOriginal('picture') ?? '');

        if ($picture !== '') {
            $storagePath = storage_path('app/public/images/users/'.$picture);

            if (is_file($storagePath)) {
                return $storagePath;
            }
        }

        $defaultPath = public_path('storage/images/users/default-avatar.png');

        return is_file($defaultPath) ? $defaultPath : null;
    }

    private function loadImage(string $path): ?\GdImage
    {
        $contents = @file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        return $image === false ? null : $image;
    }

    private function copyCircularImage(
        \GdImage $destination,
        \GdImage $source,
        int $destinationX,
        int $destinationY,
        int $diameter,
        int $innerPadding,
        string $backgroundHex,
        bool $drawBackground,
    ): void {
        if ($drawBackground) {
            $background = $this->allocateColor($destination, $backgroundHex);
            imagefilledellipse(
                $destination,
                $destinationX + (int) round($diameter / 2),
                $destinationY + (int) round($diameter / 2),
                $diameter,
                $diameter,
                $background,
            );
        }

        $innerDiameter = max(1, $diameter - ($innerPadding * 2));
        $mask = imagecreatetruecolor($innerDiameter, $innerDiameter);
        imagealphablending($mask, false);
        imagesavealpha($mask, true);

        $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        imagefill($mask, 0, 0, $transparent);

        $white = imagecolorallocatealpha($mask, 255, 255, 255, 0);
        imagefilledellipse($mask, (int) round($innerDiameter / 2), (int) round($innerDiameter / 2), $innerDiameter, $innerDiameter, $white);

        $rendered = imagecreatetruecolor($innerDiameter, $innerDiameter);
        imagealphablending($rendered, false);
        imagesavealpha($rendered, true);
        imagefill($rendered, 0, 0, $transparent);

        imagecopyresampled(
            $rendered,
            $source,
            0,
            0,
            0,
            0,
            $innerDiameter,
            $innerDiameter,
            imagesx($source),
            imagesy($source),
        );

        for ($x = 0; $x < $innerDiameter; $x++) {
            for ($y = 0; $y < $innerDiameter; $y++) {
                $maskPixel = imagecolorat($mask, $x, $y);

                if (($maskPixel & 0x7F000000) === 0x7F000000) {
                    imagesetpixel($rendered, $x, $y, $transparent);
                }
            }
        }

        imagecopy($destination, $rendered, $destinationX + $innerPadding, $destinationY + $innerPadding, 0, 0, $innerDiameter, $innerDiameter);

        imagedestroy($mask);
        imagedestroy($rendered);
    }

    private function filledRoundedRectangle(\GdImage $canvas, int $x, int $y, int $width, int $height, int $radius, int $color): void
    {
        imagefilledrectangle($canvas, $x + $radius, $y, $x + $width - $radius, $y + $height, $color);
        imagefilledrectangle($canvas, $x, $y + $radius, $x + $width, $y + $height - $radius, $color);

        imagefilledellipse($canvas, $x + $radius, $y + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($canvas, $x + $width - $radius, $y + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($canvas, $x + $radius, $y + $height - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($canvas, $x + $width - $radius, $y + $height - $radius, $radius * 2, $radius * 2, $color);
    }
}
