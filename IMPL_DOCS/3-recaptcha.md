## reCAPTCHA v2 (checkbox)

- **Cuándo aparece**: tras 3 intentos fallidos de login en ≤15 min. El contador se reinicia al expirar la ventana o al login correcto.
- **Vista**: el formulario muestra el widget de reCAPTCHA v2 (checkbox) y carga el script oficial solo cuando es necesario.
- **Flujo**:
	1) Usuario envía login; si ya superó el umbral, debe marcar el CAPTCHA.
	2) El token del CAPTCHA se valida en servidor contra la API de Google.
	3) Si la validación falla, se bloquea el login y se incrementa el contador; si pasa, se comprueban credenciales.
	4) En un login exitoso se reinicia el contador y se procede a la sesión/remember-me.
- **Configuración**: claves `recaptcha_site_key` y `recaptcha_secret_key` en `environments/env.php` y `environments/env.prod.php`.
- **Objetivo**: mitigar fuerza bruta simple obligando a interacción humana tras varios fallos, manteniendo fricción cero para usuarios legítimos.

### Detalle técnico
- **Control de intentos**: helpers en `app/model/session.php` (`incrementLoginAttempts`, `resetLoginAttempts`, `isLoginCaptchaRequired`) guardan contador y timestamp en `$_SESSION` con TTL de 15 minutos.
- **Validación CAPTCHA**: clase `app/model/services/RecaptchaService.php` envía el token a la API `siteverify` de Google usando la `recaptcha_secret_key` del entorno.
- **Control**: `app/controller/UserController.php#L33-L123` aplica la lógica: si el umbral se superó exige token, lo valida; en fallo suma intentos y muestra error; en éxito resetea intentos y continúa con sesión/remember-me.
- **Presentación**: `app/view/login.php#L1-L75` decide si mostrar el widget según `isLoginCaptchaRequired()` y carga el script de Google sólo entonces; además muestra el mensaje de error de CAPTCHA.
- **Configuración**: claves declaradas en `environments/env.php` y `environments/env.prod.php`; se leen en el login para renderizar el widget y en el controlador para verificar tokens.
