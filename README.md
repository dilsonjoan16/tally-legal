
![Logo](https://lh3.googleusercontent.com/L1srUk-qXbmX7_1uGUYx7SuwKMuiF0KgMRcPtEBUkBMyZ46SdzuDdDkDCpY8T33PDMuU=s130)

# Tally legal test

Prueba tecnica solicitada por el personal tecnico y de gestion de Tally Legal.




## **Requisitos previos**

#### Asegúrate de tener los siguientes requisitos instalados antes de continuar:

1. **Git**: Para clonar el repositorio.
2. **Docker y Docker Compose**: Laravel Sail utiliza Docker para ejecutar el proyecto.
3. **Composer**: Para instalar dependencias de Laravel.

---

### **1. Clonar el repositorio**

Clona el repositorio del proyecto desde GitHub:

```bash
git clone https://github.com/dilsonjoan16/tally-legal.git
```

---

### **2. Acceder al directorio**

Accede al directorio creado en el clone anterior:

```bash
cd tally-legal
```

---

### **3. Copiar el .env.example en el .env**

Debes copiar la configuracion del .env.example en el .env

```bash
cp .env.example .env
```

---
## Instalacion

Instalar Tally test con composer y node.

```bash
composer install
npm install
```
---

## Levantar app

```bash
./vendor/bin/sail up -d
```

---
## Ejecutar comando para configurar la app

Se desarrollo un comando para generar migraciones, correr seeders y crear el usuario maestro con el cual se pueden consultar todos los endpoints sin restriccion alguna.

```bash
./vendor/bin/sail artisan app:init
```

---

### Coleccion de POSTMAN

Con esta documentacion se pueden hacer todas las pruebas API respectivas.

- [documentacion-postman](https://app.getpostman.com/join-team?invite_code=dfa97561f745e3818a94a2b25ac1d4e8&target_code=3a01fc378be52a8273c25533ac565b43)

---

### MJML

Es un paquete para desarrollar emails de forma muy intuitiva, con su propia sintaxis y totalmente responsives, este paquete responde casi a un microFramework.

- [documentacion](https://github.com/innocenzi/vite-plugin-mjml)

- [documentacion-mjml](https://documentation.mjml.io/)

---

## Authors

- [@dilsonjoan16](https://www.github.com/dilsonjoan16)

