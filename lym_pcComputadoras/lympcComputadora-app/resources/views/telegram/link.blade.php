<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Vincular Telegram | L&M PC</title>
<style>body{font-family:Arial,sans-serif;background:#f3f6fa;margin:0;color:#18202a}.box{max-width:680px;margin:7vh auto;background:#fff;padding:32px;border-radius:14px;box-shadow:0 8px 28px #0002}code{font-size:1.2rem;letter-spacing:2px;background:#edf2f7;padding:10px;display:inline-block}button,a.btn{padding:11px 16px;border:0;border-radius:7px;background:#168acd;color:#fff;font-weight:bold;text-decoration:none;cursor:pointer}.danger{background:#b42318}.ok{padding:12px;background:#e8f7ed}.warn{padding:12px;background:#fff4d6}small{color:#5c6773}</style></head>
<body><main class="box"><a href="/php/dashboard.php">← Volver al panel</a><h1>Vincular Telegram</h1><p>Cuenta: <strong>{{ $user->usuario }}</strong> · rol: <strong>{{ $user->rol }}</strong></p>
@if(session('status'))<p class="ok">{{ session('status') }}</p>@endif
@if($account)<section class="ok"><strong>Telegram vinculado</strong><br>@if($account->username)&#64;{{ $account->username }}<br>@endif @if($account->telefono)Número verificado: {{ $account->telefono }}@else En el bot, comparte tu contacto para verificar el número.@endif</section><form method="post" action="{{ route('telegram.unlink') }}" style="margin-top:18px"><input type="hidden" name="csrf_token" value="{{ $csrf }}"><button class="danger">Desvincular</button></form>
@else
<p>Genera un código, abre el bot y envía <code>/vincular CÓDIGO</code>. El código caduca en 10 minutos y solo se usa una vez.</p>
@if(session('telegram_code'))<p class="warn">Tu código: <code>{{ session('telegram_code') }}</code></p>@endif
<form method="post" action="{{ route('telegram.link.code') }}"><input type="hidden" name="csrf_token" value="{{ $csrf }}"><button>Generar código seguro</button></form>
@endif
<hr><small>El bot solo permite acciones autorizadas para tu rol. No compartas códigos ni tokens.</small></main></body></html>
