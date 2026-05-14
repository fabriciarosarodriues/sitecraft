# SITECRAFT - Sistema de Autenticación ✅

Sistema completo de login y dashboards para profesores y estudiantes.

## 🚀 Inicio Rápido

### 1️⃣ Acceder al Login
```
http://localhost/login.php
```

### 2️⃣ Credenciales de Prueba

**Profesor:**
- 📧 laura.martin@sitecraft.local
- 🔑 password

**Estudiante:**
- 📧 ana.lopez@sitecraft.local
- 🔑 password

### 3️⃣ Dashboards

Después de iniciar sesión, serás redirigido automáticamente a:

- **Profesor**: `/teacher/dashboard.php` - Gestiona tus cursos y estudiantes
- **Estudiante**: `/student/dashboard.php` - Accede a tus cursos inscritos

## 📚 Documentación

Lee más en: [docs/autenticacion.md](docs/autenticacion.md)

## 🔑 Funcionalidades

✨ **Login seguro** con validación  
✨ **Dashboards personalizados** por rol  
✨ **Control de acceso** automático  
✨ **Menú de usuario** en header  
✨ **Cierre de sesión** seguro  

## 📁 Estructura de Archivos

```
public/
├── login.php                 # 🔓 Página de login
├── logout.php                # 🚪 Cierre de sesión
├── teacher/dashboard.php     # 📊 Dashboard profesor
├── student/dashboard.php     # 📚 Dashboard estudiante
└── assets/css/pages/auth/
    └── login.css             # 💄 Estilos login

app/
├── helpers/
│   ├── auth.php              # 🔐 Funciones de autenticación
│   └── index.php             # 📦 Cargador
└── views/
    ├── auth/login.php        # 📄 Vista login
    └── layouts/
        └── header-dashboard.php
```

## ⚡ Flujo de Funcionamiento

```
Inicio (index.php)
    ↓
¿Autenticado?
    ├─ NO → Mostrar botones "Login/Registro"
    └─ SÍ → Mostrar nombre de usuario + "Logout"
    
Login (login.php)
    ↓
Validar credenciales
    ├─ ERROR → Mostrar error
    └─ VÁLIDO → Iniciar sesión
        ↓
    Redirigir según rol
        ├─ teacher → /teacher/dashboard.php
        └─ student → /student/dashboard.php
```

## 🔒 Seguridad Implementada

✅ Contraseñas hasheadas (`password_hash`)  
✅ Sesiones regeneradas tras login  
✅ Validación de roles en cada página  
✅ Protección contra SQL injection (prepared statements)  
✅ Sanitización de salidas (`htmlspecialchars`)  
✅ Redirección automática si no está autenticado  

## 🎯 Próximas Mejoras

- [ ] Sistema de registro
- [ ] Recuperación de contraseña
- [ ] 2FA (Autenticación doble)
- [ ] Perfiles de usuario editable
- [ ] Historial de acceso

---

**¿Problemas?** Verifica que:
1. Las bases de datos estén cargadas (schema.sql, seeds)
2. El usuario esté marcado como `is_active = 1`
3. El rol del usuario esté correctamente asignado en `user_roles`
