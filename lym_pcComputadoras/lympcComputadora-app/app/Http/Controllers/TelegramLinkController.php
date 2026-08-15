<?php

namespace App\Http\Controllers;

use App\Models\TelegramAccount;
use App\Models\TelegramLinkCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TelegramLinkController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        [$user, $csrf] = $this->legacyUser($request);
        if (! $user) return redirect('/php/login.php');

        return view('telegram.link', ['user' => $user, 'account' => TelegramAccount::where('id_usuario', $user->id_usuario)->first(), 'csrf' => $csrf]);
    }

    public function createCode(Request $request): RedirectResponse
    {
        [$user, $csrf] = $this->legacyUser($request);
        $this->assertTrustedRequest($request, $csrf);
        if (! $user) return redirect('/php/login.php');

        TelegramLinkCode::where('id_usuario', $user->id_usuario)->whereNull('usado_en')->delete();
        $code = Str::upper(Str::random(20));
        TelegramLinkCode::create([
            'id_usuario' => $user->id_usuario,
            'codigo_hash' => hash('sha256', $code),
            'expira_en' => now()->addMinutes(10),
            'ip_solicitud' => $request->ip(),
        ]);

        return redirect()->route('telegram.link')->with('telegram_code', $code);
    }

    public function unlink(Request $request): RedirectResponse
    {
        [$user, $csrf] = $this->legacyUser($request);
        $this->assertTrustedRequest($request, $csrf);
        if (! $user) return redirect('/php/login.php');
        TelegramAccount::where('id_usuario', $user->id_usuario)->delete();

        return redirect()->route('telegram.link')->with('status', 'La cuenta de Telegram fue desvinculada.');
    }

    /** @return array{0: User|null, 1: string|null} */
    private function legacyUser(Request $request): array
    {
        $originalName = session_name();
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        session_name('LEGACYSESSID');
        $id = $request->cookie('LEGACYSESSID');
        if (! is_string($id) || ! preg_match('/^[A-Za-z0-9,-]{1,48}$/', $id)) return [null, null];
        session_id($id); session_start();
        $userId = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT);
        $_SESSION['_telegram_csrf'] ??= bin2hex(random_bytes(32));
        $csrf = $_SESSION['_telegram_csrf'];
        session_write_close(); session_name($originalName);

        return [$userId ? User::find($userId) : null, $csrf];
    }

    private function assertTrustedRequest(Request $request, ?string $csrf): void
    {
        abort_unless($csrf && hash_equals($csrf, (string) $request->input('csrf_token')), 419);
        $origin = $request->header('Origin') ?: $request->header('Referer');
        abort_unless($origin && parse_url($origin, PHP_URL_HOST) === $request->getHost(), 403);
    }
}
