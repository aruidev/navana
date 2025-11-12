## Projecte: Items  

Alex Ruiz | DAW2 | Servidor

## Get started:

### DB

A la ubicació `db_schema`:

- Executar `Pt04_Alex_Ruiz.sql` al gestor de base de dades per crear la DB.
- S'inclou l'arxiu `test_data.sql` amb mocks d'items per poder testejar ràpidament.

### APP

- Arrencar serveis de servidor i base de dades local.
- Executar `index.php` normalment.
- S'inclou documentació generada amb `PHPdocumentor`, es pot accedir fàcilment mitjançant el link a la pàgina principal.

## Pt04: Login

Pasos d'implementació:  

#### 1. Nou schema de base de dades:  
- Afegida taula `users`:  
  - `id`
  - `username`
  - `email`
  - `password_hash`
- Afegida FK `user_id` a taula `items` (Cada item es publicat per un usuari).
- Decisió de negoci: `ON DELETE SET NULL` (Quan s'esborra un usuari, l'item **NO** s'elimina. Evitem cascade, implica que l'autor d'un item pot ser `NULL`).

#### 2. Afegir botons de Login / Register al header.
#### 3. Redirigir quan usuari no està loguejat.
- Al accedir a `form_insert` o `form_update`, així com a intentar fer `delete`, `update` o `insert`, verificar user loguejat.  
On ho comprovem?

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
- `getPaginated($limit, $offset, $term = '', $order = 'ASC')`  

> Indiquem `$limit` i `$offset` per obtenir nomès les rows que mostrarem de la base de dades.
> Es passa `$term` còm a paràmetre a les dues funcions per mantenir la funcionalitat de la cerca.
> Es passa `$order` a la segona funció per indicar l'ordre (`ORDER BY`) a la query a la base de dades (default ASC).

#### `ItemService`  

S'afegeix el següent mètode al servei:

- `getItemsPaginated($page = 1, $perPage = 6, $term = '', $order = 'ASC')`  

> Paràmetres `$page` per indicar la pàgina i `$perPage` per indicar el nombre màxim de rows que obtenim a la pàgina actual.
> Es passa `$term` còm a paràmetre per mantenir la funcionalitat de la cerca.
> Es passa `$order` per mantenir l'ordre desitjat (default ASC).

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

## Connexions PDO

### Descripció
Aplicació PHP que gestiona items utilitzant el patró MVC i la connexió a una base de dades MySQL amb PDO i Prepared Statements.

### Estructura de carpetes
- `index.php`: Punt d'entrada de l'aplicació.
- `app/controller/`: Controladors que gestionen la lògica de les peticions (`ItemController.php`).
- `app/model/connection.php`: Configuració i creació de la connexió PDO a la base de dades.
- `app/model/dao/`: Accés a dades (DAO) per interactuar amb la base de dades (`ItemDAO.php`).
- `app/model/entities/`: Definició de les entitats (classe `Item.php`).
- `app/model/services/`: Serveis que encapsulen la lògica de negoci (`ItemService.php`).
- `app/view/`: Vistes que mostren els formularis i llistats d'items.
- `sql_seed/`: Fitxer SQL amb la seed per crear la base de dades.

### Connexió a la base de dades
La connexió es fa mitjançant PDO a `app/model/connection.php`, on es defineixen els paràmetres de connexió (host, usuari, contrasenya, nom de la base de dades). Aquesta connexió s'utilitza a les DAO per fer consultes i operacions CRUD. 

> Per simplificar, utilitzem un usuari root sense contrasenya assignada.

### Flux de l'aplicació
1. L'usuari accedeix a `index.php`, que redirigeix a `app/view/list.php` (vista on es llistaràn els items).
2. El controlador (`ItemController.php`) rep la petició i utilitza els serveis i DAO per obtenir o modificar dades.
3. Les DAO utilitzen la connexió PDO per accedir a la base de dades.
4. El controlador passa les dades a la vista (`app/view/`), que mostra el resultat (llistat, formulari d'inserció/actualització, etc.).

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