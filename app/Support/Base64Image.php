<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Base64Image
{
    public static function decode(string $image): array
    {
        if (! preg_match('/^data:image\/(?<extension>png|jpg|jpeg);base64,(?<data>.+)$/i', $image, $matches)) {
            throw new InvalidArgumentException('La imagen enviada no tiene un formato valido.');
        }

        $bytes = base64_decode(str_replace(' ', '+', $matches['data']), true);

        if ($bytes === false) {
            throw new InvalidArgumentException('No se ha podido procesar la imagen.');
        }

        $extension = strtolower($matches['extension']) === 'jpeg' ? 'jpg' : strtolower($matches['extension']);

        return [
            'bytes' => $bytes,
            'extension' => $extension,
            'mime' => "image/{$extension}",
        ];
    }

    public static function storeReference(string $image, int $userId): string
    {
        $decoded = self::decode($image);
        $path = "face-references/user-{$userId}.{$decoded['extension']}";

        Storage::disk('local')->put($path, $decoded['bytes']);

        return $path;
    }

    public static function temporaryPath(string $image, string $prefix = 'face-capture'): string
    {
        $decoded = self::decode($image);
        $directory = storage_path('app/tmp');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.'/'.Str::slug($prefix).'-'.Str::uuid().'.'.$decoded['extension'];

        file_put_contents($path, $decoded['bytes']);

        return $path;
    }
}
