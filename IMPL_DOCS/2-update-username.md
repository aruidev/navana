# Update de Username

- Se añadió un handler en `app/controller/UserController.php` que recibe `POST change_username`, valida sesión activa, sanea el nuevo username, aplica validaciones básicas (no vacío, distinto del actual) y verifica unicidad vía `UserService::usernameExists`. Usa flash para éxito o error y redirige de vuelta a Account Settings.
- `UserService::changeUsername` ya existía; ahora se usa desde el controller para ejecutar la actualización y refrescar `$_SESSION['username']` cuando tiene éxito.
- La vista `app/view/account-settings.php` ahora muestra el username actual, incluye un formulario para cambiarlo y conserva el valor ingresado cuando hay errores. Renderiza errores de validación en bloque y sigue mostrando los toasts existentes para feedback rápido.
- Se mantiene la protección para usuarios no autenticados: Account Settings muestra mensaje y enlaces de regreso/login si no hay sesión.
