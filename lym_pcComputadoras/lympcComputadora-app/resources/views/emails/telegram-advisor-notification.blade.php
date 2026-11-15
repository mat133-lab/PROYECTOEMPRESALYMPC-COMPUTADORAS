Nueva solicitud recibida desde Telegram (#{{ $contacto->id_mensaje }}).

Cliente: {{ $contacto->nombre }}
Correo para responder: {{ $contacto->correo }}
Tipo: {{ $contacto->compania }}
Fecha: {{ $contacto->fecha }}

Mensaje:
{{ $contacto->mensaje }}

Puedes responder directamente a este correo; la respuesta se enviará al cliente.
También puedes revisar la bandeja de solicitudes en el panel de asistente.
