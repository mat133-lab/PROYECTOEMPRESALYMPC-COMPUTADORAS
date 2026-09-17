import json
import re
import sys
import unicodedata


def normalizar(texto):
    texto = texto.lower().strip()
    texto = unicodedata.normalize("NFD", texto)
    return "".join(letra for letra in texto if unicodedata.category(letra) != "Mn")


def contiene(texto, palabras):
    return any(palabra in texto for palabra in palabras)


def contiene_opcion(texto, numero):
    """Match a menu number without confusing it with a model number."""
    return bool(re.search(rf"(?:^|\s){numero}(?:[.)\-:]|\s|$)", texto))


def extraer_modelo(texto, marcas):
    coincidencia = re.search(r"\bmodelo\s*[:#-]?\s*([^,.;\n]+)", texto)
    if coincidencia:
        modelo = coincidencia.group(1).strip()
        if modelo:
            return modelo

    for marca in marcas:
        if marca in texto:
            return marca.upper()

    return "no indicado"


def tiene_datos_soporte(texto):
    marcas = [
        "asus", "acer", "lenovo", "dell", "hp", "hewlett", "msi", "apple",
        "toshiba", "samsung", "epson", "canon", "huawei",
    ]
    fallas = [
        "no enciende", "no prende", "no arranca", "se apaga", "se reinicia",
        "pantalla", "sonido", "sonidos", "ruido", "pitido", "pitidos", "lento",
        "calienta", "sobrecalienta", "virus", "error", "falla", "problema",
        "no carga", "carga", "bateria", "batería",
    ]
    tiempo = ["desde ayer", "desde hoy", "desde anoche", "hace ", "desde ", "ayer", "hoy"]
    return (
        contiene(texto, marcas)
        or contiene(texto, fallas)
        or "modelo" in texto
        or contiene(texto, tiempo)
    ) and contiene(texto, fallas)


def caso_avanzado(texto):
    no_encendido = contiene(texto, ["no enciende", "no prende", "no arranca"])
    sonidos = contiene(texto, ["sonido", "sonidos", "ruido", "pitido", "pitidos"])
    señales_peligro = contiene(texto, ["olor a quemado", "humo", "liquido", "se sobrecalienta", "sobrecalienta"])
    comportamiento_repetido = contiene(texto, ["se apaga", "se reinicia"])
    return (no_encendido and sonidos) or señales_peligro or comportamiento_repetido


def respuesta_soporte(texto):
    marcas = [
        "asus", "acer", "lenovo", "dell", "hp", "hewlett", "msi", "apple",
        "toshiba", "samsung", "huawei",
    ]
    marca = next((marca.upper() for marca in marcas if marca in texto), "no indicada")
    modelo = extraer_modelo(texto, marcas)

    if caso_avanzado(texto):
        return (
            f"Gracias por la información. Registré un equipo {marca}, modelo {modelo}, con una falla que requiere revisión. "
            "Como comprobación básica, desconecta los periféricos y verifica el cargador una sola vez. "
            "No sigas intentando encenderlo si el sonido continúa, se calienta o huele a quemado. "
            "Tu caso quedará en espera de un asistente de soporte."
        )

    if contiene(texto, ["no enciende", "no prende", "no arranca"]):
        return (
            f"Gracias por la información del equipo {marca}, modelo {modelo}. "
            "Como comprobación básica, revisa el cargador y prueba otro tomacorriente una sola vez. "
            "Si continúa sin encender, comunícate con un asistente para revisarlo."
        )

    return (
        f"Gracias por la información del equipo {marca}, modelo {modelo}. "
        "Prueba reiniciarlo una vez y verifica que sus conexiones estén firmes. "
        "Si la falla continúa, comunícate con un asistente de soporte."
    )


def responder(mensaje):
    texto = normalizar(mensaje)
    necesita_humano = False
    tema = "Consulta general"
    mostrar_opciones = True

    opciones = (
        "\n\nTambien puedes escribir una de estas opciones:"
        "\n1. Soporte tecnico"
        "\n2. Horarios disponibles"
        "\n3. Direccion o ubicacion"
        "\n4. Informacion de la empresa"
        "\n5. Hablar con asistente"
    )

    if contiene(texto, ["humano", "asistente", "persona real", "asesor", "especializada", "especializado", "no entiendo"]):
        necesita_humano = True
        tema = "Asistencia personalizada"
        respuesta = (
            "Entiendo. Te voy a pasar con alguien de soporte para que revise tu caso con mas detalle. "
            "Por favor deja escrito marca, modelo del equipo, falla principal y un numero de contacto si aplica."
        )
    elif contiene_opcion(texto, 1) or contiene(texto, ["soporte", "tecnico", "reparacion", "arreglo", "mantenimiento", "formatear"]) or tiene_datos_soporte(texto):
        tema = "Soporte tecnico"
        if tiene_datos_soporte(texto):
            respuesta = respuesta_soporte(texto)
            necesita_humano = caso_avanzado(texto)
            mostrar_opciones = False
        else:
            respuesta = (
                "Para soporte tecnico, dime marca, modelo, que problema presenta, si enciende correctamente y desde cuando ocurre la falla. "
                "Si ya tienes esos datos, escríbelos juntos y te dare una orientacion concreta."
            )
    elif contiene(texto, ["manana", "mañana", "hoy", "dia", "fecha", "cuando", "disponibilidad", "hora"]) or contiene_opcion(texto, 2) or contiene(texto, ["horario", "horarios", "disponible", "disponibles", "dias", "cita", "agenda"]):
        tema = "Horarios disponibles"
        respuesta = (
            "Para revisar disponibilidad, indicame si te sirve mañana o en otro dia y el tipo de servicio. "
            "Si después necesitas una hora exacta o una confirmación final, entonces te paso con asistencia."
        )
    elif contiene_opcion(texto, 3) or contiene(texto, ["direccion", "ubicacion", "donde", "local", "tienda", "llegar", "mapa"]):
        tema = "Direccion y ubicacion"
        respuesta = (
            "La ubicación de L&M PC Computadoras es: Ecuador, Quito, sector La Ecuatoriana, por la Joaquín Játiva. "
            "Referencia: Las Orquídeas / Oe9 Martha Bucaram / S37-49 / S37A. "
            "Puedes abrir la ubicación en Google Maps: https://maps.app.goo.gl/V8ghM2XY9M4H8v6Z9"
        )
        mostrar_opciones = False
    elif contiene_opcion(texto, 4) or contiene(texto, ["empresa", "informacion", "lympc", "computadoras", "productos", "ventas", "servicios"]):
        tema = "Informacion de la empresa"
        respuesta = (
            "L&M PC Computadoras ofrece venta de equipos, accesorios, mantenimiento y reparacion. "
            "Tambien puedes consultar por laptops, PCs, impresoras, tintas y soporte tecnico."
        )
    elif contiene_opcion(texto, 5) or contiene(texto, ["hablar", "contactar"]):
        necesita_humano = True
        tema = "Solicitud de asistente"
        respuesta = "Claro. Te paso con un asistente real para continuar la conversacion y resolver tus dudas especificas."
    elif contiene(texto, ["hola", "buenas", "buenos dias", "buen dia", "saludos"]):
        respuesta = (
            "Hola, soy la asistencia virtual de L&M PC Computadoras. "
            "Puedo ayudarte con soporte tecnico, horarios, direccion o informacion de la empresa."
        )
    else:
        tema = "Consulta general"
        respuesta = (
            "Puedo ayudarte con horarios, direccion del local o soporte tecnico. "
            "Si luego necesitas una hora exacta o detalles especificos, te paso con asistencia."
        )

    if mostrar_opciones and "Tambien puedes escribir" not in respuesta and not necesita_humano:
        respuesta += opciones

    return {
        "respuesta": respuesta,
        "necesita_humano": necesita_humano,
        "tema": tema,
    }


if __name__ == "__main__":
    entrada = json.loads(sys.stdin.read() or "{}")
    print(json.dumps(responder(entrada.get("mensaje", "")), ensure_ascii=False))
