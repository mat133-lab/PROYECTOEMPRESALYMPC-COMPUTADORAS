<?php

namespace App\Services;

use App\Mail\TelegramActionExecutedMail;
use App\Models\Cita;
use App\Models\Contacto;
use App\Models\Notificacion;
use App\Models\Producto;
use App\Models\TelegramActionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TelegramActionService
{
    private const STAFF = ['admin', 'tecnico'];

    public function createPending(User $user, string $chatId, array $intent): TelegramActionRequest
    {
        $action = (string) ($intent['accion'] ?? '');
        $data = is_array($intent['datos'] ?? null) ? $intent['datos'] : [];
        $this->validateIntent($user, $action, $data);
        $token = Str::upper(Str::random(10));
        $summary = $this->summary($action, $data);
        $request = TelegramActionRequest::create([
            'id_usuario' => $user->id_usuario, 'telegram_chat_id' => $chatId, 'accion' => $action,
            'datos' => $data, 'resumen' => $summary, 'confirmacion_hash' => hash('sha256', $token),
            'expira_en' => now()->addMinutes(10),
        ]);
        $request->setAttribute('plain_confirmation_token', $token);
        $this->audit($user, $chatId, $action, 'pendiente', ['resumen' => $summary]);
        return $request;
    }

    public function confirm(User $user, string $chatId, string $token): string
    {
        $pending = TelegramActionRequest::where('id_usuario', $user->id_usuario)->where('telegram_chat_id', $chatId)
            ->where('confirmacion_hash', hash('sha256', Str::upper($token)))->where('estado', 'pendiente')->first();
        if (! $pending || $pending->expira_en->isPast()) { if ($pending) $pending->update(['estado' => 'expirada']); return 'La confirmación no existe o venció. Solicita nuevamente la acción.'; }
        try {
            DB::transaction(function () use ($user, $pending) {
                $this->execute($user, $pending->accion, $pending->datos);
                $pending->update(['estado' => 'ejecutada', 'confirmada_en' => now()]);
            });
            $this->notify($user, $pending->accion, $pending->resumen);
            $this->audit($user, $chatId, $pending->accion, 'ejecutada', $pending->datos);
            return 'Acción realizada: '.$pending->resumen.'. También enviamos un correo a tu cuenta registrada.';
        } catch (\Throwable $e) {
            report($e); $pending->update(['estado' => 'fallida', 'error' => 'No se pudo ejecutar la acción.']);
            $this->audit($user, $chatId, $pending->accion, 'fallida', []);
            return 'No fue posible realizar la acción. No se aplicaron cambios; intenta más tarde.';
        }
    }

    private function validateIntent(User $user, string $action, array &$data): void
    {
        $role = strtolower((string) $user->rol);
        if ($action === 'crear_cita') {
            $date = Carbon::parse((string) ($data['fecha'] ?? '')); if ($date->startOfDay()->lt(today())) throw new \InvalidArgumentException('La fecha de la cita debe ser futura.');
            $data['fecha'] = $date->toDateString(); $data['motivo'] = Str::limit(trim((string) ($data['motivo'] ?? '')), 500, '');
            if ($data['motivo'] === '') throw new \InvalidArgumentException('Indica el motivo de la cita.'); return;
        }
        if ($action === 'crear_contacto') {
            $data['mensaje'] = Str::limit(trim((string) ($data['mensaje'] ?? '')), 5000, '');
            if ($data['mensaje'] === '') throw new \InvalidArgumentException('Indica el mensaje de contacto.'); return;
        }
        if (in_array($action, ['crear_producto', 'actualizar_producto', 'eliminar_producto'], true)) {
            if (! in_array($role, self::STAFF, true)) throw new \InvalidArgumentException('Tu rol no puede gestionar productos.');
            if ($action !== 'crear_producto' && ! Producto::find((int) ($data['id_producto'] ?? 0))) throw new \InvalidArgumentException('Producto no encontrado.');
            if ($action !== 'eliminar_producto' && (trim((string) ($data['nombre'] ?? '')) === '' || (float) ($data['precio'] ?? -1) < 0)) throw new \InvalidArgumentException('Producto incompleto o inválido.');
            return;
        }
        throw new \InvalidArgumentException('Esta acción no está disponible aún en el sistema.');
    }

    private function execute(User $user, string $action, array $data): void
    {
        if ($action === 'crear_cita') { Cita::create(['id_usuario' => $user->id_usuario, 'nombre' => $user->usuario, 'apellido' => '-', 'correo' => $user->correo, 'fecha' => $data['fecha'], 'telefono' => $data['telefono'] ?? 'No registrado', 'motivo' => $data['motivo']]); return; }
        if ($action === 'crear_contacto') { Contacto::create(['id_usuario' => $user->id_usuario, 'nombre' => $user->usuario, 'correo' => $user->correo, 'compania' => $data['compania'] ?? 'Particular', 'mensaje' => $data['mensaje']]); return; }
        if ($action === 'crear_producto') { Producto::create(['nombre' => $data['nombre'], 'serie' => $data['serie'] ?? Str::upper(Str::random(12)), 'fecha' => today(), 'unidades' => max(0, (int) ($data['unidades'] ?? 0)), 'precio' => $data['precio'], 'imagen' => $data['imagen'] ?? 'sin-imagen.webp', 'categoria' => $data['categoria'] ?? 'General']); return; }
        if ($action === 'actualizar_producto') { Producto::findOrFail($data['id_producto'])->update(['nombre' => $data['nombre'], 'unidades' => max(0, (int) ($data['unidades'] ?? 0)), 'precio' => $data['precio'], 'categoria' => $data['categoria'] ?? 'General']); return; }
        if ($action === 'eliminar_producto') { Producto::findOrFail($data['id_producto'])->delete(); }
    }

    private function summary(string $action, array $data): string
    {
        return match ($action) {
            'crear_cita' => "crear cita para {$data['fecha']} por: {$data['motivo']}",
            'crear_contacto' => 'enviar mensaje de contacto',
            'crear_producto' => "crear producto {$data['nombre']}",
            'actualizar_producto' => "actualizar producto #{$data['id_producto']}",
            'eliminar_producto' => "eliminar producto #{$data['id_producto']}",
        };
    }

    private function notify(User $user, string $action, string $summary): void
    {
        $title = 'Acción realizada desde Telegram';
        $description = "Se realizó la acción: {$summary}.";
        $state = 'enviado';
        try { Mail::to($user->correo)->send(new TelegramActionExecutedMail($title, $description)); } catch (\Throwable $e) { report($e); $state = 'fallido'; }
        Notificacion::create(['id_usuario' => $user->id_usuario, 'titulo' => $title, 'mensaje' => $description, 'correo_destino' => $user->correo, 'estado_correo' => $state]);
    }

    private function audit(User $user, string $chatId, string $action, string $status, array $detail): void
    {
        DB::table('telegram_action_audits')->insert(['id_usuario' => $user->id_usuario, 'telegram_chat_id' => $chatId, 'accion' => $action, 'estado' => $status, 'detalle' => json_encode($detail), 'created_at' => now()]);
    }
}
