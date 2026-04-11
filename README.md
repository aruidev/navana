# Projecte: Navana
Gestió de bookmarks online.

Alex Ruiz | DAW2 | Servidor

## Get started:

### DB

A la ubicació `db_schema`:

- Executar `Pt0X_Alex_Ruiz.sql` al gestor de base de dades per crear la DB.
- Executar `test_data.sql`. Aquest arxiu conté usuaris, administrador i mocks d'items per poder testejar ràpidament.

### APP

- Arrencar serveis de servidor i base de dades local.
- Accedir a la ruta des d'el client.
- S'inclou documentació generada amb `PHPdocumentor`.

### Credencials Admin seed:


| username | email         | password   |
|----------|---------------|------------|
| Admin    | admin@admin.com   | `P@ssw0rd` |

> Important!  
> Es necessari executar `test_data.sql` per tenir al menys un usuari administrador.

## PrjF4
### Consumir API
### Proveïr API
### Ajax

## Refactor de Routing
Ara el sistema de rutes s’ha refactoritzat per garantir claredat i mantenibilitat:  

- Totes les rutes es defineixen i centralitzen a un únic punt de veritat (routes.php).
- Les utilitats per construir, normalitzar i resoldre rutes es gestionen a route_helpers.php.
- El front controller (index.php) llegeix la ruta sol·licitada i carrega la vista o controlador corresponent segons la taula de rutes.

## PrjF3
### Social Authentication & Gestió credencials
 - [Canvi/Recuperació de contrasenya ](#gestió-de-contrasenya)
 - [Autenticació Social (OAuth, HybridAuth)](#social-authentication)
 - [Configuracions de seguretat: Deixar constància al README de les configuracions de seguretat, entre d'altres al fitxer .htaccess](#configuracions-de-seguretat-a-lhtaccess)

 #### Gestió de contrasenya
 ##### Canvi de contrasenya

S'ha afegit la funcionalitat de canvi de contrasenya. Inclou:
- Nova branca POST a `UserController.php` per gestionar el canvi, validant la sessió, comprovant la contrasenya actual i aplicant les mateixes regles de validació que al registre.
- S'ha creat un helper reutilitzable per validar contrasenyes i s'ha actualitzat el procés de hash a `UserService.php`, amb suport al DAO per desar el nou hash.
- La vista de configuració de compte (`account-settings.php`) ara mostra el formulari de canvi de contrasenya i els errors/feedback corresponents.

##### Recuperació de contrasenya

S'ha implementat un flux de recuperació per correu electrònic amb token temporal. El controlador (`UserController.php`) valida la petició, el servei (`PasswordResetService.php`) genera un parell `selector` + `validator`, desa només el hash del token a base de dades i envia l'enllaç per email amb `MailService.php`. En confirmar el formulari (`reset_confirm.php`), el token es valida, es consumeix i es desa la nova contrasenya amb hash.

##### Social authentication

S'ha afegit autenticació social amb Google i GitHub. Google es gestiona amb `GoogleOAuthService.php` i GitHub amb HybridAuth a `GithubAuthService.php`, mentre que els callbacks es resolen a `app/controller/auth/`. El servei `UserService.php` s'encarrega de trobar o crear l'usuari local i de vincular el compte extern a la taula `user_oauth_accounts`, amb opció de link/unlink des de `account-settings.php`.

## PrjF2
### Pt05: Miscelània  

 - [Ordenació dels articles](#ordenacio-dels-articles)
 - [Remember me: Ha de recordar contrasenya amb token](#implementacio-remember-me-amb-token)
 - [Editar perfil: Modificar username, email, contrasenya](#editar-usuari)
 - [Usuari amb rol Admin que pot esborrar altres usuaris](#usuari-amb-rol-admin)
 - [Barra de cerca](#barra-de-cerca)
 - [Configuracions de seguretat: Deixar constància al README de les configuracions de seguretat, entre d'altres al fitxer .htaccess](#configuracions-de-seguretat-a-lhtaccess)


#### Ordenació dels articles

Els articles es poden ordenar per data (ASC/DESC) des de la vista (`library.php`, `explore.php`). El paràmetre `$order` es passa al DAO (`ItemDAO.php`, `UserDAO.php`) i es manté a la paginació (`pagination.php`). Les consultes utilitzen `ORDER BY` i el valor es conserva al canviar de pàgina.

#### Implementació Remember me amb token

Sistema amb token dividit en `selector` i `validator` (hash SHA-256). Fitxers clau: `RememberMeService.php`, `RememberMeTokenDAO.php`, `session.php`, `UserController.php`. El token es valida, es rota i s'esborra si falla. La cookie és segura (`httponly`, `samesite=Lax`). Les taules i consultes estan a `db_schema/remember_me_tokens.sql`.

#### Editar usuari
Canvi de nom d'usuari i email des de `account-settings.php`. Validació de sessió, unicitat i format. Controlador: `UserController.php`, servei: `UserService.php`. Només usuaris autenticats poden modificar dades. Les vistes mostren errors i feedback.

#### Usuari amb rol admin
Admins identificats amb el camp `isAdmin` a la taula `users`. Poden esborrar usuaris i donar/revocar permisos d'admin des de `account-settings.php`. No poden esborrar-se a si mateixos, però sí a altres usuaris administradors. Control i validació a `UserController.php`.

#### Barra de cerca
Permet filtrar items per títol o tag amb la variable `$term` (input). El DAO (`ItemDAO.php`) fa la consulta amb `LIKE` i PDO preparat. El filtre es manté a la paginació.

#### reCAPTCHA v2 (checkbox)
Després de 3 intents fallits de login, es mostra el CAPTCHA a la vista. El token es valida amb Google. Les claus estan a `environments/`.  
Els intents de login es controlen amb helpers de sessió (`session.php`). El CAPTCHA es valida amb Google (`RecaptchaService.php`). Les claus es configuren a l'entorn (`env.local.php` & `env.prod.php`). La vista mostra el widget si cal.

#### Configuracions de seguretat a l'.htaccess
L'.htaccess activa rutes amigables (`RewriteEngine On`), redirigeix errors (`error404`, `error401`) i evita exposar fitxers interns. Si la ruta no existeix, mostra la pàgina d'error personalitzada.

---

## PrjF1 
### Pt04: Login

Pasos d'implementació:  

#### 1. Nou schema de base de dades:  

##### Taula `users`

| Propietat      | Tipus de dada     | Descripció                        |
|----------------|------------------|-----------------------------------|
| id             | INT (PK, AI)     | Identificador únic de l'usuari    |
| username       | VARCHAR(50)      | Nom d'usuari                      |
| email          | VARCHAR(100)     | Correu electrònic                 |
| password_hash  | VARCHAR(255)     | Hash de la contrasenya            |

##### Taula `items`

| Propietat      | Tipus de dada     | Descripció                                 |
|----------------|------------------|--------------------------------------------|
| id             | INT (PK, AI)     | Identificador únic de l'item               |
| title          | VARCHAR(100)     | Títol de l'item                            |
| description    | TEXT             | Descripció de l'item                       |
| link           | VARCHAR(255)     | Enllaç relacionat amb l'item               |
| created_at     | DATETIME         | Data de creació                            |
| updated_at     | DATETIME         | Data d'actualització                       |
| user_id        | INT (FK, NULL)   | Identificador de l'usuari autor (nullable) |

- Afegida FK `user_id` a taula `items` (Cada item es publicat per un usuari).
- Decisió de negoci: `ON DELETE SET NULL` (Quan s'esborra un usuari, l'item **NO** s'elimina. Evitem cascade, implica que l'autor d'un item pot ser `NULL`).

#### 2. MVC per control d'usuaris.

Utilitzem `session`, `UserController`, `UserDAO`, `User` entity, `UserService` i vistes relacionades (`login`, `register`...) per tractar el control d'usuaris. 
El funcionament és el següent:
- Utilitzem el controlador per iniciar `SESSION`, instanciar el servei i controlar el login i el registre i validar les dades introduides per client a travès del servei.
- El servei inicialitza el DAO i implementa els mètodes de login i register, utilitzant els mètodes del dao (`create`, `verifyCredentials`, etc.)
- El DAO inicialitza la connexió amb la base de dades i implementa diversos mètodes que interactúen directament amb la base de dades.
- L'entitat User modela les propietats de l'usuari i permet construir i tractar objecters `User`.

#### 3. Auth i Header.

Al header, si no hi ha usuari loguejat, es mostren botons de Login / Register. Si hi ha un usuari loguejat, es mostra el `username` i opció de Logout.

Com el header es el primer arxiu que carreguem i el requerim a totes les vistes, inicialitzem la sessió al header per inicialitzar-la sense tenir que fer-ho a cada vista per separat.

El mètode `startSession()` del header està implementat a `session.php`. Aquest arxiu s'utilitza per centralitzar la configuració de la sessió i implementar el mètode `start_session()` amb una serie de validacions i configuracions adicionals:
- Defineix el temps total de la sessió en segons.
- Verifica que no hi hagi una sessió activa abans d'inicialitzar la sessió per evitar dobles trucades.

#### 4. Redirigir quan usuari no està loguejat.

Si usuari no està loguejat, nomès pot accedit a la pàgina principal i veure el contingut general. No apareixen controls per editar / esborrar, i al accedir a pàgines protegides com `my_items.php` o `form_insert.php`(pàgina per afegir item), es mostra un missatge d'error i dirigeix l'usuari a login / registre.
Si hi ha usuari loguejat, l'usuari pot afegir items, consultar els seus items, i apareixen els controls per editar / esborrar els items que contenen el seu `user_id`.

#### 5. Login i Register forms:

Si l'usuari marca el checkbox "Remember me", guardem l'username de l'usuari si es logueja amb éxit, el recordem i el recuperem al formulari amb la cookie `$_COOKIE['remembered_user']`. 

#### 6. My Account.

Es crea la pàgina `account.php` per gestionar accions i preferencies de l'usuari com poden ser:
- Canviar username.
- Canviar correu electrònic.
- Canviar contrasenya.

## Paginació

### Descripció

La paginació a aquest projecte permet:  

- Llistar items per pàgines (vista `list.php`).
- Definir quants elements mostrar per pàgina amb un selector (`$perPage`): valors permesos 1, 5, 10.
- Navegar entre pàgines (`$page`).
- Cercar per títol mitjançant el paràmetre `$term`, sense perdre la cerca al canviar de pàgina.
- Ordenar els resultats mitjançant el paràmetre `$order`, sense perdre l'ordre al canviar de pàgina.

### Components que intervenen:  

- `ItemDAO`: mètodes per comptar i obtenir resultats limitats directament desde la base de dades.
- `ItemService`: funció que amb la lògica del càlcul d'offset i limit, retorna items + total.
- `list.php`: Processa el GET, mostra llista d'items i inclou el component de paginació.
- `pagination.php`: Component amb la lògica de paginació que renderitza els controls i el selector `$perPage`.

---

### Implementació:  

#### `ItemDAO`

S'afegeixen dos mètodes al DAO:  

- `count($term = '')`
- `getPaginated($limit, $offset, $term = '', $order = 'DESC')`  

> Indiquem `$limit` i `$offset` per obtenir nomès les rows que mostrarem de la base de dades.
> Es passa `$term` còm a paràmetre a les dues funcions per mantenir la funcionalitat de la cerca.
> Es passa `$order` a la segona funció per indicar l'ordre (`ORDER BY`) a la query a la base de dades (default ASC).

#### `ItemService`  

S'afegeix el següent mètode al servei:

- `getItemsPaginated($page = 1, $perPage = 6, $term = '', $order = 'DESC')`  

> Paràmetres `$page` per indicar la pàgina i `$perPage` per indicar el nombre màxim de rows que obtenim a la pàgina actual.
> Es passa `$term` còm a paràmetre per mantenir la funcionalitat de la cerca.
> Es passa `$order` per mantenir l'ordre desitjat (default DESC).

#### `list.php`

Conté els paràmetres GET:

- `$page`
- `$totalPages`
- `$term`
- `$perPage`
- `$order`

#### `pagination.php`

Component que espera els paràmetres de `list.php` i conté:

- Funció `pageUrl($pageNumber, $term = '', $perPage = null, $order = null)`
  - Genera enllaços pels controls: Anterior / números de pàgina (1, 2, 3...) / Següent.
  - Construeix una URL vàlida per `list.php` passant els paràmetres de consulta.
  - Passem `$pageNumber` per indicar la pàgina.
  - Passem `$term`, `$perPage` i `$order` per mantenir els paràmetres seleccionats per l'usuari al canviar de pàgina (cerca, rows per pàgina i ordre).

- Desplegable per canviar `$perPage` amb opcions 1, 5 o 10 items per pàgina.

> Important: el component s'inclou amb `include_once` o `require_once` per evitar error: `Cannot redeclare pageUrl()`.

---

## Connectionns PDO

### Descripció
Aplicació PHP que gestiona items utilitzant el patró MVC i la connexió a una base de dades MySQL amb PDO i Prepared Statements.

### Estructura de carpetes
- `index.php`: Punt d'entrada de l'aplicació.
- `app/controller/`: Controladors que gestionen la lògica de les peticions (`ItemController.php`).
- `app/model/connection.php`: Configuració i creació de la connexió PDO a la base de dades.
- `app/model/dao/`: Accés a dades (DAO) per interactuar amb la base de dades (`ItemDAO.php`).
- `app/model/entities/`: Definició de les entitats (classe `Item.php`).
- `app/services/`: Serveis que encapsulen la lògica de negoci (`ItemService.php`).
- `app/view/`: Vistes que mostren els formularis i llistats d'items.
- `sql_seed/`: Fitxer SQL amb la seed per crear la base de dades.

### Connexió a la base de dades
La connexió es fa mitjançant PDO a `app/model/connection.php`, on es defineixen els paràmetres de connexió (host, usuari, contrasenya, nom de la base de dades). Aquesta connexió s'utilitza a les DAO per fer consultes i operacions CRUD. 

> Per simplificar, utilitzem un usuari root sense contrasenya assignada.

### Flux de l'aplicació
1. L'usuari accedeix a `index.php`, que actua com a front controller i llegeix la ruta sol·licitada.
2. La ruta es resol via `app/helpers/routes.php` (i les utilitats de `app/helpers/route_helpers.php`) per decidir si cal carregar una vista o un controlador.
3. Si la ruta apunta a un controlador (p. ex. `ItemController.php`), aquest utilitza serveis i DAO per obtenir o modificar dades.
4. Les DAO accedeixen a la base de dades amb PDO i el resultat es renderitza en una vista de `app/view/` o es redirigeix a una altra ruta interna.

### Resum
El projecte separa la lògica en MVC i utilitza PDO per a la connexió segura a la base de dades.

---

## Entity

L'aplicació té l'objectiu de poder gestionar `items` modelats amb les següents propietats:  

- `id`
- `title`
- `description`
- `link`
- `created_at`
- `updated_at`

---