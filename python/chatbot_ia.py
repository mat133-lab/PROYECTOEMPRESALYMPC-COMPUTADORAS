import json
import sys
import unicodedata


def normalizar(texto):
    texto = texto.lower().strip()
    texto = unicodedata.normalize("NFD", texto)
    return "".join(letra for letra in texto if unicodedata.category(letra) != "Mn")


def contiene(texto, palabras):
    return any(palabra in texto for palabra in palabras)


def responder(mensaje):
    texto = normalizar(mensaje)
    necesita_humano = False
    tema = "Consulta general"

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
    elif contiene(texto, ["1", "soporte", "tecnico", "reparacion", "arreglo", "mantenimiento", "formatear", "lento", "virus", "pantalla", "enciende"]):
        tema = "Soporte tecnico"
        respuesta = (
            "Para soporte tecnico, dime por favor: marca del equipo, modelo, que problema presenta, "
            "si enciende correctamente y desde cuando ocurre la falla. "
            "Con eso puedo darte una orientacion inicial."
        )
    elif contiene(texto, ["2", "horario", "horarios", "disponible", "disponibles", "dias", "cita", "agenda"]):
        tema = "Horarios disponibles"
        respuesta = (
            "Para revisar disponibilidad, indicame que dia te queda mejor y si necesitas revision, "
            "mantenimiento, instalacion de software o reparacion. Si necesitas confirmar una hora exacta, "
            "puedo pasarte con asistencia."
        )
    elif contiene(texto, ["3", "direccion", "ubicacion", "donde", "local", "tienda", "llegar", "mapa"]):
        tema = "Direccion y ubicacion"
        respuesta = (
            "Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, "
            "envio o coordenadas, te paso con asistencia para darte informacion precisa."
        )
    elif contiene(texto, ["4", "empresa", "informacion", "lympc", "computadoras", "productos", "ventas", "servicios"]):
        tema = "Informacion de la empresa"
        respuesta = (
            "L&M PC Computadoras ofrece venta de equipos, accesorios, mantenimiento y reparacion. "
            "Tambien puedes consultar por laptops, PCs, impresoras, tintas y soporte tecnico."
        )
    elif contiene(texto, ["5", "hablar", "contactar", "asesor"]):
        necesita_humano = True
        tema = "Solicitud de asistente"
        respuesta = (
            "Claro. Te paso con un asistente real para continuar la conversacion y resolver tus dudas especificas."
        )
    elif contiene(texto, ["hola", "buenas", "buenos dias", "buen dia", "saludos"]):
        respuesta = (
            "Hola, soy la asistencia virtual de L&M PC Computadoras. "
            "Puedo ayudarte con soporte tecnico, horarios, direccion o informacion de la empresa."
        ) + opciones
    else:
        necesita_humano = True
        respuesta = (
            "Puedo orientarte con informacion general, pero tu consulta necesita mas detalle. "
            "Te voy a pasar con alguien de soporte para que revise tu caso de forma personalizada."
        )

    if not necesita_humano and "Tambien puedes escribir" not in respuesta:
        respuesta += opciones

    return {
        "respuesta": respuesta,
        "necesita_humano": necesita_humano,
        "tema": tema,
    }


if __name__ == "__main__":
    entrada = json.loads(sys.stdin.read() or "{}")
    print(json.dumps(responder(entrada.get("mensaje", "")), ensure_ascii=False))
