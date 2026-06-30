# Build stage
FROM node:20-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Production stage
FROM nginx:alpine AS production
COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx/default.conf.template /etc/nginx/conf.d/default.conf.template

# Backend service URL — override at runtime with:
#   docker run -e BACKEND_URL=http://your-laravel-host ...
ENV BACKEND_URL=http://laravel

EXPOSE 80

# Substitute ${BACKEND_URL} in the nginx template at startup, then start nginx.
# Single-quoting '${BACKEND_URL}' tells envsubst to ONLY replace that variable
# and leave all other nginx $variables untouched.
CMD ["/bin/sh", "-c", "envsubst '${BACKEND_URL}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"]
