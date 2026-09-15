<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * 2FA TOTP untuk Super Admin & persetujuan pencairan pinjaman bernilai besar
 * (rules/security.md §1), kompatibel dengan Google Authenticator dkk.
 */
class TwoFactorAuthenticationService
{
    private readonly Google2FA $engine;

    public function __construct()
    {
        $this->engine = new Google2FA;
    }

    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function getQrCodeSvg(User $user, string $secret): string
    {
        $otpauthUrl = $this->engine->getQRCodeUrl(
            company: config('app.name'),
            holder: $user->email,
            secret: $secret,
        );

        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($otpauthUrl);
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->engine->verifyKey($secret, $code) !== false;
    }

    /**
     * @return array<int, string> Kode pemulihan plaintext — hanya ditampilkan satu kali ke pengguna.
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }

    /**
     * @param  array<int, string>  $plaintextCodes
     * @return array<int, string> Hash bcrypt untuk disimpan (bukan plaintext).
     */
    public function hashRecoveryCodes(array $plaintextCodes): array
    {
        return array_map(fn (string $code) => Hash::make($code), $plaintextCodes);
    }

    /**
     * Memverifikasi & "membakar" satu kode pemulihan (sekali pakai).
     *
     * @param  array<int, string>  $hashedCodes
     * @return array<int, string>|null Daftar hash tersisa jika cocok, atau null jika tidak ada yang cocok.
     */
    public function consumeRecoveryCode(array $hashedCodes, string $submittedCode): ?array
    {
        foreach ($hashedCodes as $index => $hash) {
            if (Hash::check($submittedCode, $hash)) {
                unset($hashedCodes[$index]);

                return array_values($hashedCodes);
            }
        }

        return null;
    }
}
