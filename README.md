### Generar Namespaces en Kubernetes para cada Servicio

Esto es una guía a través del proceso de crear namespaces separados en Kubernetes para cada uno de tus servicios (`webApp`, `phpMyAdmin`, `MySQL`) y luego conectarlos. Seguiremos estos pasos:

1. Crear namespaces.
2. Desplegar cada servicio en su namespace correspondiente.
3. Conectar los servicios entre ellos.

### Paso 1: Crear Namespaces

Primero, vamos a crear namespaces separados para `webApp`, `phpMyAdmin` y `MySQL`.

#### Crear Namespace para webApp

Ejecuta el siguiente comando para crear el namespace `webapp-namespace`:

```sh
kubectl create namespace webapp-namespace
```

#### Crear Namespace para phpMyAdmin

Ejecuta el siguiente comando para crear el namespace `phpmyadmin-namespace`:

```sh
kubectl create namespace phpmyadmin-namespace
```

#### Crear Namespace para MySQL

Ejecuta el siguiente comando para crear el namespace `mysql-namespace`:

```sh
kubectl create namespace mysql-namespace
```

### Paso 2: Desplegar Servicios en sus Namespaces

Vamos a desplegar cada servicio en su namespace correspondiente.

#### Desplegar MySQL

1. Asegúrate de que los archivos de configuración de MySQL estén actualizados. Aquí tienes un ejemplo de `deployment-mysql.yaml`:

    ```yaml
    apiVersion: apps/v1
    kind: Deployment
    metadata:
      name: mysql
      namespace: mysql-namespace
    spec:
      selector:
        matchLabels:
          app: mysql
      replicas: 1
      template:
        metadata:
          labels:
            app: mysql
        spec:
          containers:
          - name: mysql
            image: mysql:5.7
            env:
            - name: MYSQL_ROOT_PASSWORD
              value: "admin01" # Cambia esto por una contraseña segura
            - name: MYSQL_DATABASE
              value: "BASE-01A" # Cambia esto por el nombre de tu base de datos
            - name: MYSQL_USER
              value: "user" # Cambia esto por el nombre de tu usuario
            - name: MYSQL_PASSWORD
              value: "password" # Cambia esto por una contraseña segura para el usuario
            ports:
            - containerPort: 3306
            volumeMounts:
            - name: mysql-persistent-storage
              mountPath: /var/lib/mysql
            - name: initdb
              mountPath: /docker-entrypoint-initdb.d
          volumes:
          - name: mysql-persistent-storage
            persistentVolumeClaim:
              claimName: mysql-pvc # Asegúrate de que este sea el nombre correcto de tu PVC
          - name: initdb
            configMap:
              name: mysql-initdb-config
    ```

2. Desplegar MySQL usando `kubectl`:

    ```sh
   kubectl apply -k . -n mysql-namespace
    ```

#### Desplegar phpMyAdmin

1. Asegúrate de que los archivos de configuración de phpMyAdmin estén actualizados. Aquí tienes un ejemplo de `deployment-php.yaml`:

    ```yaml
    apiVersion: apps/v1
    kind: Deployment
    metadata:
      name: phpmyadmin
      namespace: phpmyadmin-namespace
    spec:
      selector:
        matchLabels:
          app: phpmyadmin
      replicas: 1
      template:
        metadata:
          labels:
            app: phpmyadmin
        spec:
          containers:
          - name: phpmyadmin
            image: phpmyadmin/phpmyadmin
            imagePullPolicy: IfNotPresent
            env:
            - name: PMA_HOST
              value: "mysql.mysql-namespace.svc.cluster.local" # Esto debe coincidir con el nombre del servicio de MySQL
            ports:
            - containerPort: 80
    ```

2. Desplegar phpMyAdmin usando `kubectl`:

    ```sh
   kubectl apply -k . -n mysql-namespace
    ```

#### Desplegar webApp

1. Asegúrate de que los archivos de configuración de webApp estén actualizados. Aquí tienes un ejemplo de `deployment-webapp.yaml`:

    ```yaml
    apiVersion: apps/v1
    kind: Deployment
    metadata:
      name: webapp
      namespace: webapp-namespace
    spec:
      replicas: 1
      selector:
        matchLabels:
          app: webapp
      template:
        metadata:
          labels:
            app: webapp
        spec:
          containers:
          - name: webapp
            image: 192.168.49.2:5000/php-webserver:latest
            imagePullPolicy: IfNotPresent
            ports:
            - containerPort: 80
            volumeMounts:
            - mountPath: /usr/local/apache2/htdocs/
              name: webapp-storage
          volumes:
          - name: webapp-storage
            persistentVolumeClaim:
              claimName: pvc-webapp
    ```

2. Desplegar webApp usando `kubectl`:

    ```sh
   kubectl apply -k . -n mysql-namespace
    ```

### Paso 3: Conectar los Servicios

Para conectar los servicios entre sí, asegúrate de que las aplicaciones usen los nombres de servicio DNS correctos en Kubernetes. Los servicios se pueden resolver por `<service-name>.<namespace>.svc.cluster.local`.

#### Actualización de submit.php para Conectarse a MySQL

Asegúrate de que `submit.php` en la aplicación web use el nombre de servicio correcto para MySQL. Aquí hay un ejemplo:

```php
<?php
$servername = "mysql.mysql-namespace.svc.cluster.local"; // El nombre del servicio MySQL en Kubernetes
$username = "user"; // El nombre de usuario de MySQL configurado
$password = "password"; // La contraseña de MySQL configurada
$dbname = "BASE-01A"; // El nombre de la base de datos MySQL configurada

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener datos del formulario
$name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];

// Preparar y ejecutar la consulta de inserción
$sql = "INSERT INTO form_data (name, email, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $email, $message);

if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Cerrar conexión
$stmt->close();
$conn->close();
?>
```

### Resumen de Comandos

1. Crear namespaces:

    ```sh
    kubectl create namespace webapp-namespace
    kubectl create namespace phpmyadmin-namespace
    kubectl create namespace mysql-namespace
    ```

2. Desplegar MySQL:

    ```sh
    kubectl apply -f MySQL/pv-mysql.yaml -n mysql-namespace
    kubectl apply -f MySQL/pvc-mysql.yaml -n mysql-namespace
    kubectl apply -f MySQL/configmap.yaml -n mysql-namespace
    kubectl apply -f MySQL/deployment-mysql.yaml -n mysql-namespace
    kubectl apply -f MySQL/service-mysql.yaml -n mysql-namespace
    ```

3. Desplegar phpMyAdmin:

    ```sh
    kubectl apply -f phpMyAdmin/deployment-php.yaml -n phpmyadmin-namespace
    kubectl apply -f phpMyAdmin/service-php.yaml -n phpmyadmin-namespace
    ```

4. Desplegar webApp:

    ```sh
    kubectl apply -f webApp/pvc-webapp.yaml -n webapp-namespace
    kubectl apply -f webApp/deployment-webapp.yaml -n webapp-namespace
    kubectl apply -f webApp/service-webapp.yaml -n webapp-namespace
    ```

### Conclusión

Siguiendo estos pasos, habrás creado namespaces separados para cada uno de tus servicios en Kubernetes y habrás desplegado y conectado los servicios entre sí correctamente. Esto mejora la organización y la gestión de tus recursos en un entorno Kubernetes.
