FROM nginx:1.24-alpine

LABEL maintainer="jenkins-lab"

# Copia configuración y contenido
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY index.html /usr/share/nginx/html/index.html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
