# UrbaWeb-library

Librairie php pour interroger l'API de Urbaweb Civadis

https://www.civadis.be/urbanisme

Installation
----

`composer require acmarche/urbaweb:dev-master`

Configuration
-----------------

###  Définir les variables d'environnements:

En créant un fichier .env.local.php à la racine de votre installation ou  
via les variables d'environnment de votre système d'exploitation

```php
<?php
//.env.local.php
return array (
  'URBA_URL'      => 'http://urbaweb.domain.be/permis/app/rest',
  'URBA_USER'     => 'username',
  'URBA_PASSWORD' => 'mdp',
  'APP_ENV'       => 'prod',
);
```

Utilisation
----

```php
require_once 'vendor/autoload.php';

use AcMarche\UrbaWeb\UrbaWeb;

$urba = new UrbaWeb();
$permisId = 1234;
$permis  = $urba->informationsPermis($permisId);
$annonce = $urba->informationsAnnonceProjet($permisId);
$enquete = $urba->informationsEnquete($permisId);
```
Recherche par numéro

```php
$numero   = 'BC2xx00xxx92';
$ids = $urba->searchPermis(['numeroPermis' => $numero]);
$permisId = $ids[0];
```
