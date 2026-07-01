# ---- Stage 1: build static assets ----
FROM node:20-alpine AS build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Stage 2: serve via nginx ----
FROM nginx:alpine
# Apply available Alpine security patches on top of the base image (e.g. the
# libexpat DoS CVE-2026-45186) so the published image passes the Trivy gate.
RUN apk upgrade --no-cache
RUN rm /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/conf.d/app.conf
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
