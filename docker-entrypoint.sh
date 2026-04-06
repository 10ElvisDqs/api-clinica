#!/bin/bash
set -e

echo "==> [1/5] Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet

echo "==> [2/5] Esperando a MySQL (host: ${DB_HOST:-db})..."
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
echo "==> MySQL disponible."

echo "==> [3/5] Ejecutando migraciones..."
php artisan migrate --force --no-interaction || echo "Advertencia: algunas migraciones fallaron (tablas ya existen), continuando..."

echo "==> [4/5] Creando link de storage (public/storage -> storage/app/public)..."
php artisan storage:link --no-interaction 2>/dev/null || true

echo "==> [5/5] Ajustando permisos de storage y bootstrap/cache..."
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "==> Iniciando servidor HTTP en puerto 8000 (php artisan serve)..."
php artisan serve --host=0.0.0.0 --port=8000 &

echo "==> Iniciando PHP-FPM..."
exec "$@"
