# UrbaWeb-library

Librairie php pour interroger l'API de Urbaweb Civadis

https://www.civadis.be/urbanisme

Installation
----

`composer require acmarche/urbaweb`

Utilisation
----

```
require_once 'vendor/autoload.php';
use AcMarche\UrbaWeb\UrbaWeb;

$urba = new UrbaWeb();
$permisId = 1234;
$permis  = $urba->informationsPermis($permisId);
$annonce = $urba->informationsAnnonceProjet($permisId);
$enquete = $urba->informationsEnquete($permisId);`
```
Recherche par numéro

```
$numero   = 'BC2xx00xxx92';
$ids = $urba->searchPermis(['numeroPermis' => $numero]);
$permisId = $ids[0];
```
