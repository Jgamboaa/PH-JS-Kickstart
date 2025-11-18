#!/usr/bin/env bash
set -e

APP_DIR="/var/www/html"

# Genera .env a partir del template si no existe o si forzamos regeneración
if [ ! -f "$APP_DIR/.env" ] || [ "$FORCE_ENV" = "1" ]; then
  if [ -f "$APP_DIR/.env.template" ]; then
    echo "Generating .env from .env.template with environment variables..."
    envsubst < "$APP_DIR/.env.template" > "$APP_DIR/.env"
  elif [ -f "$APP_DIR/.env_example" ]; then
    echo "No .env.template found; copying .env_example to .env"
    cp "$APP_DIR/.env_example" "$APP_DIR/.env"
  fi
fi

# Normaliza valores con espacios en .env: asegura comillas si faltan
if [ -f "$APP_DIR/.env" ]; then
  tmp_env="$(mktemp)"
  awk -f - "$APP_DIR/.env" > "$tmp_env" <<'AWK'
BEGIN{FS="="; OFS="="}
# Deja líneas vacías o comentarios intactos
/^[[:space:]]*($|#)/ {print; next}
{
  key=$1
  # valor original (todo a la derecha del primer '=')
  val=substr($0, index($0,$2))
  # No tocar si ya está entre comillas simples o dobles
  if (val ~ /^[[:space:]]*"/ || val ~ /^[[:space:]]*'/) {print key, val; next}
  # Si contiene espacios, envolver en comillas dobles
  if (val ~ /[[:space:]]/) {
    sub(/^[[:space:]]+/, "", val)
    sub(/[[:space:]]+$/, "", val)
    print key, "\"" val "\""
    next
  }
  print key, val
}
AWK
  mv "$tmp_env" "$APP_DIR/.env"
fi

# Asegura propietario y permisos del .env
if [ -f "$APP_DIR/.env" ]; then
  chown www-data:www-data "$APP_DIR/.env"
  chmod 660 "$APP_DIR/.env"
fi

cd $APP_DIR && composer migrate


exec apache2-foreground
