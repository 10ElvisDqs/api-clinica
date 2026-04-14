#!/bin/bash
set -e

# ══════════════════════════════════════════════════════════════
# [1/6] Instalar dependencias de Composer
# ══════════════════════════════════════════════════════════════
echo "==> [1/6] Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet

# ══════════════════════════════════════════════════════════════
# [2/6] Preparar el archivo .env
# ══════════════════════════════════════════════════════════════
echo "==> [2/6] Preparando .env..."

if [ ! -f ".env" ]; then
    echo "    .env no encontrado, copiando desde .env.example..."
    cp .env.example .env
fi

# Generar APP_KEY si está vacía o es el placeholder
CURRENT_KEY=$(grep "^APP_KEY=" .env | cut -d'=' -f2-)
if [ -z "$CURRENT_KEY" ] || [ "$CURRENT_KEY" = "base64:GENERATE_ME" ]; then
    echo "    Generando APP_KEY..."
    php artisan key:generate --force --no-interaction
else
    echo "    APP_KEY ya configurada."
fi

# Generar JWT_SECRET si está vacía o es el placeholder
CURRENT_JWT=$(grep "^JWT_SECRET=" .env | cut -d'=' -f2-)
if [ -z "$CURRENT_JWT" ] || [ "$CURRENT_JWT" = "GENERATE_ME" ]; then
    echo "    Generando JWT_SECRET..."
    php artisan jwt:secret --force --no-interaction
else
    echo "    JWT_SECRET ya configurado."
fi

# ══════════════════════════════════════════════════════════════
# [3/6] Esperar a MySQL
# ══════════════════════════════════════════════════════════════
echo "==> [3/6] Esperando a MySQL (host: ${DB_HOST:-db})..."
until php -r "
  try {
    \$pdo = new PDO(
      'mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306};dbname=${DB_DATABASE}',
      '${DB_USERNAME}',
      '${DB_PASSWORD}'
    );
    exit(0);
  } catch (Exception \$e) {
    exit(1);
  }
" 2>/dev/null; do
  echo "    MySQL no disponible, reintentando en 3s..."
  sleep 3
done
echo "    MySQL disponible."

# ══════════════════════════════════════════════════════════════
# [4/6] Ejecutar migraciones
# ══════════════════════════════════════════════════════════════
echo "==> [4/6] Ejecutando migraciones..."
php artisan migrate --force --no-interaction || echo "    Advertencia: algunas migraciones ya existían, continuando..."

# ══════════════════════════════════════════════════════════════
# [5/6] Storage link y permisos
# ══════════════════════════════════════════════════════════════
echo "==> [5/6] Creando storage link y ajustando permisos..."
php artisan storage:link --no-interaction 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ══════════════════════════════════════════════════════════════
# [6/6] Iniciar servicios
# ══════════════════════════════════════════════════════════════
echo "==> [6/6] Iniciando servidor HTTP en puerto 8000..."
php artisan serve --host=0.0.0.0 --port=8000 &

echo ""
echo "╔══════════════════════════════════════════════════════╗"
echo "║  Proyecto listo. Recuerda correr los seeders:        ║"
echo "║  docker-compose exec app php artisan db:seed         ║"
echo "╚══════════════════════════════════════════════════════╝"
echo ""

echo "==> Iniciando PHP-FPM..."
exec "$@"
