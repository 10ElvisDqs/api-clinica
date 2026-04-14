# =====================================================
# Stage 1: Build Angular con configuración de Docker
# =====================================================
FROM node:18-alpine AS angular-build

WORKDIR /app

# Copiar package files primero (cachea la instalación de deps)
COPY admin_clinica/package*.json ./

# --legacy-peer-deps necesario por conflictos de peer deps en Angular 16
RUN npm install --legacy-peer-deps

# Copiar el código fuente de Angular
COPY admin_clinica/ .

# Build de producción (usa src/environments/environment.ts)
RUN npm run build -- --configuration production

# =====================================================
# Stage 2: nginx sirve Angular + proxy a Laravel API
# =====================================================
FROM nginx:1.25-alpine

# Archivos compilados de Angular
COPY --from=angular-build /app/dist/preclinic-angular /usr/share/nginx/html

# Configuración de nginx
# Contexto de build es la carpeta padre (../), por eso la ruta es api-clinica/nginx/
COPY api-clinica/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
