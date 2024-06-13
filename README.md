# Despliegue de Microservicios con Docker, PHP, MySQL y Kubernetes

Esta guía proporciona un proceso paso a paso para desplegar una arquitectura de microservicios utilizando Docker, PHP, MySQL, phpMyAdmin y Kubernetes en Minikube.

## Prerrequisitos

- Minikube instalado y en ejecución.
- Docker instalado y configurado.
- kubectl instalado.

## Estructura del Proyecto

```
.
|-- MySQL
|   |-- deployment-mysql.yaml
|   |-- kustomization.yaml
|   |-- pv-mysql.yaml
|   |-- pvc-mysql.yaml
|   `-- service-mysql.yaml
|-- README.md
|-- composer.json
|-- kustomization.yaml
|-- phpMyAdmin
|   |-- deployment-php.yaml
|   |-- kustomization.yaml
|   `-- service-php.yaml
`-- webApp
    |-- Dockerfile
    |-- deployment-webapp.yaml
    |-- html
    |   |-- index.html
    |   `-- submit.php
    |-- kustomization.yaml
    |-- pvc-webapp.yaml
    `-- service-webapp.yaml

4 directorios, 18 archivos
```

## Pasos para el Despliegue

### 1. Crear la Imagen Docker para la Aplicación Web en PHP

Navega al directorio `webApp` y crea una imagen Docker:

```bash
cd webApp
eval $(minikube docker-env)
minikube ip
```

Utiliza la dirección IP obtenida del comando `minikube ip` en el siguiente comando `docker build`. Reemplaza `192.168.49.2` con tu IP de Minikube si es diferente:

```bash
docker build --tag 192.168.49.2:5000/php-webserver .
```

Asegúrate de que el archivo `Dockerfile` esté presente en el directorio `webApp` al ejecutar este comando.

### 2. Aplicar Configuraciones de Kubernetes

Para cada microservicio (MySQL, phpMyAdmin, webApp), aplica las configuraciones de Kubernetes. Esto se hace navegando a cada directorio y utilizando `kubectl apply -k .`.

#### MySQL

Navega al directorio `MySQL` y aplica las configuraciones:

```bash
cd ../MySQL
kubectl apply -k .
```

#### phpMyAdmin

Navega al directorio `phpMyAdmin` y aplica las configuraciones:

```bash
cd ../phpMyAdmin
kubectl apply -k .
```

#### webApp

Navega al directorio `webApp` y aplica las configuraciones:

```bash
cd ../webApp
kubectl apply -k .
```

El archivo `kustomization.yaml` en cada directorio aplicará automáticamente todas las configuraciones necesarias (`deployment`, `service`, `pv`, `pvc`).

### 3. Verificar el Despliegue

Verifica el estado de los pods para asegurarte de que estén en ejecución:

```bash
kubectl get pods
```

Deberías ver una salida que indique que todos los pods están en estado `Running`.

### 4. Acceder a los Servicios

#### webApp

Usa el comando `minikube service` para acceder al servicio webApp:

```bash
kubectl get service
minikube service webapp-service
```

#### MySQL y phpMyAdmin

Usa `kubectl port-forward` para acceder a los servicios MySQL y phpMyAdmin:

##### MySQL

```bash
kubectl port-forward svc/mysql-service 3306:3306
```

##### phpMyAdmin

```bash
kubectl port-forward svc/phpmyadmin-service 8080:80
```

## Conclusión

Siguiendo los pasos descritos anteriormente, habrás desplegado con éxito una aplicación web en PHP con una base de datos MySQL y phpMyAdmin para la gestión de la base de datos, todo ejecutándose en un clúster de Kubernetes gestionado por Minikube. Esta configuración aprovecha Docker para la contenedorización y Kubernetes para la orquestación, asegurando una arquitectura de microservicios escalable y manejable.
