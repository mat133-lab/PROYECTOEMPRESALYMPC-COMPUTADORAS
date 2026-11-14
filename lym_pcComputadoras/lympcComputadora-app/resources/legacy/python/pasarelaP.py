"""Comprobación manual de credenciales PayPal para el checkout PHP."""

import base64
import json
import os
import sys
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode
from urllib.request import Request, urlopen


def setting(name: str, default: str = "") -> str:
    value = os.environ.get(name, "").strip()
    if value:
        return value

    env_file = Path(__file__).resolve().parents[3] / ".env"
    try:
        lines = env_file.read_text(encoding="utf-8").splitlines()
    except FileNotFoundError:
        return default

    prefix = f"{name}="
    for line in lines:
        line = line.strip()
        if line.startswith(prefix):
            return line[len(prefix):].strip().strip("\"'")
    return default


def main() -> int:
    mode = setting("PAYPAL_MODE", "sandbox").lower()
    client_id = setting("PAYPAL_CLIENT_ID")
    client_secret = setting("PAYPAL_CLIENT_SECRET")

    if mode not in {"sandbox", "live"}:
        print("PAYPAL_MODE debe ser sandbox o live.", file=sys.stderr)
        return 2
    if not client_id or not client_secret:
        print(
            "Define PAYPAL_CLIENT_ID y PAYPAL_CLIENT_SECRET como variables de entorno.",
            file=sys.stderr,
        )
        return 2

    api_url = (
        "https://api-m.sandbox.paypal.com"
        if mode == "sandbox"
        else "https://api-m.paypal.com"
    )
    credentials = base64.b64encode(
        f"{client_id}:{client_secret}".encode("utf-8")
    ).decode("ascii")
    request = Request(
        f"{api_url}/v1/oauth2/token",
        data=urlencode({"grant_type": "client_credentials"}).encode("ascii"),
        headers={
            "Authorization": f"Basic {credentials}",
            "Content-Type": "application/x-www-form-urlencoded",
            "Accept": "application/json",
        },
        method="POST",
    )

    try:
        with urlopen(request, timeout=20) as response:
            result = json.loads(response.read().decode("utf-8"))
    except HTTPError as error:
        print(
            f"PayPal rechazó la configuración (HTTP {error.code}).",
            file=sys.stderr,
        )
        return 1
    except (URLError, TimeoutError, OSError) as error:
        print(f"No se pudo conectar con PayPal: {error}", file=sys.stderr)
        return 1
    except (UnicodeDecodeError, json.JSONDecodeError):
        print("PayPal devolvió una respuesta no válida.", file=sys.stderr)
        return 1

    if not result.get("access_token"):
        print("PayPal no devolvió un token de acceso.", file=sys.stderr)
        return 1

    print(f"Credenciales PayPal {mode} válidas. El token no se mostrará.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
