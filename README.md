# Upload de fichiers avec PHP

> ## Assurez-vous que VSCode est bien lancé en mode "Administrator". Si vous utilisez Wamp, assurez-vous qu'il est également lancé en administrateur

## Introduction

Pour uploader des fichiers avec PHP, nous allons utiliser les formulaires.

Dans un formulaire, on peut intégrer un champ `input` de type `file`.

```html
<form method="POST">
  <input type="file" name="myFile" />
  <input type="submit" value="Envoyer" />
</form>
```

Ici, que peut-on dire de ce formulaire ?

- Sa méthode est POST
- Il contient un champ d'upload
- Sa cible est le fichier lui-même

> Testez un envoi de formulaire avec un fichier dans cette configuration. Dans la même page, effectuez un `var_dump($_POST);` pour consulter le contenu du formulaire

On constate que le tableau `$_POST` est vide.

## Après $_GET, $_POST...le tableau $_FILES

Lorsqu'on effectue de l'upload de fichier, les fichiers vont dans un autre tableau superglobal de PHP : `$_FILES`. Facile à retenir.

> Effectuez un `var_dump($_FILES);`

Vous devriez voir quelque chose de ce style :

```txt
array (size=1)
  'myFile' => string 'depardieu.png' (length=13)
```

Vous retrouvez normalement le nom du fichier que vous avez sélectionné.

> A vérifier, mais si vous n'avez rien du tout dans le tableau `$_FILES`, cela peut être dû à votre version. Ce cours est réalisé avec la version 7.4.3. Dans tous les cas pas d'inquiétude, on va régler ça juste après

Cette information est insuffisante, comment fais-je pour uploader mon fichier sur mon serveur juste avec son nom ?

## Types de contenus et encodage des données de formulaire

Pour que notre fichier soit bien pris en compte, il faut spécifier le type d'encodage de notre formulaire.

Pour ce faire, nous allons **ajouter** un nouvel attribut à notre formulaire : `enctype='multipart/form-data'` :

```diff
- <form method="POST">
+ <form method="POST" enctype="multipart/form-data">
    <input type="file" name="myFile" />
    <input type="submit" value="Envoyer" />
  </form>
```

### **Faites très attention à la syntaxe : l'attribut s'appelle `enctype`, il est dans la balise `form`, et la valeur **doit être** `multipart` puis un `/` puis `form-data` (tiret du milieu entre form et data !)**

Plus d'informations sur les types de contenus des formulaires [sur le site du W3C](https://www.w3.org/TR/html401/interact/forms.html#form-content-type).

> Refaites le test, en ayant simplement ajouté l'attribut d'encodage. Vous devriez voir bien plus d'informations avec votre `var_dump`, par exemple :

```txt
array (size=1)
  'myFile' =>
    array (size=5)
      'name' => string 'depardieu.png' (length=13)
      'type' => string 'image/png' (length=9)
      'tmp_name' => string 'C:\wamp64\tmp\php4D27.tmp' (length=25)
      'error' => int 0
      'size' => int 106078
```

> Si vous n'avez rien dans votre tableau `$_FILES`, allez à la section **Propriétés du fichier et configuration PHP**

On constate que le tableau `$_FILES` est un **tableau associatif**, comme ses camarades `$_GET` et `$_POST`. Il contient pour chaque champ d'upload **une clé ayant le nom du champ** (attribut `name`).

La valeur est un autre tableau associatif contenant les propriétés du fichier en question !

## Propriétés du fichier et configuration PHP

### Propriétés du fichier

Le fichier que vous souhaitez uploader est mappé dans le tableau `$_FILES`, avec quelques propriétés :

| Nom | Description |
|---|---|
| name | Le nom du fichier sur votre machine |
| type | Le type MIME du fichier |
| tmp_name | Le chemin du fichier temporaire créé (nous verrons ça juste après, pour confirmer l'upload) |
| error | si une erreur est survenue |
| size | La taille du fichier |

### Configuration PHP

Vous pouvez configurer dans votre fichier de configuration PHP `php.ini` ou `phpForApache.ini` la **taille maximale d'upload de fichier**. Il s'agit du paramètre `upload_max_filesize` :

> Fichier : `php.ini`

```ini
; Maximum allowed size for uploaded files.
; http://php.net/upload-max-filesize
upload_max_filesize = 9M
```

Conjointement à ce paramètre, vous pouvez configurer **la taille maximale des données POST que votre serveur peut accepter**. Il s'agit du paramètre `post_max_size` :

> Fichier : `php.ini`

```ini
; Maximum size of POST data that PHP will accept.
; Its value may be 0 to disable the limit. It is ignored if POST data reading
; is disabled through enable_post_data_reading.
; http://php.net/post-max-size
post_max_size = 9M
```

## Gestion des erreurs et configuration

Ici, mettons volontairement ces 2 paramètres à `9M`, c'est-à-dire 9Mo, puis tentons d'uploader un fichier vidéo de 30Mo par exemple.

> Attention si vous uploadez des fichiers très volumineux, le serveur mettra un moment à les recevoir, c'est donc normal si l'exécution de votre script prend un moment. Dans Google Chrome, vous devriez voir apparaître en bas à gauche de la fenêtre un pourcentage avec le libellé "Transfert en cours"

Si on se réfère de nouveau à notre tableau `$_FILES`, vous devriez avoir avec un `var_dump` ce résultat :

```txt
array (size=0)
  empty
```

Par ailleurs, il est possible que vous ayez un message d'avertissement de ce type :

```txt
Warning: POST Content-Length of 33400131 bytes exceeds the limit of 9437184 bytes in Unknown on line 0
```

> La taille des données POST est supérieure à ce qu'on a configuré, donc PHP n'a même pas mappé le fichier, il a rejeté le formulaire !

Adaptez la valeur du paramètre `post_max_size`, à `980M` par exemple, redémarrez votre serveur, puis retentez l'upload :

```txt
array (size=1)
  'myFile' =>
    array (size=5)
      'name' => string 'WP_20160810_10_53_30_Pro.mp4' (length=28)
      'type' => string '' (length=0)
      'tmp_name' => string '' (length=0)
      'error' => int 1
      'size' => int 0
```

Notre fichier a bien été mappé dans le tableau `$_FILES`, mais la clé `error` contient la valeur 1. Par ailleurs, vous voyez qu'il n'a pas de `tmp_name` ni de `type`.

Le code d'erreur correspond au fait que la limite maximale d'upload de fichiers a été dépassée par ce fichier. Une constante correspondant à cette valeur est d'ailleurs disponible dans PHP : `UPLOAD_ERR_INI_SIZE`

Vous pouvez retrouver la liste des codes d'erreur et les constantes associées sur [cette page](https://www.php.net/manual/fr/features.file-upload.errors.php).

Adaptez finalement le paramètre `upload_max_filesize`, à `980M` également, par exemple, puis retentez l'expérience. Normalement tout fonctionne et le code d'erreur est bien 0.

## Enregistrement d'un fichier sur le serveur

Rappelons le processus : un client nous envoie un fichier (qui peut être un fichier image) et nous souhaitons le stocker sur notre serveur.

### Zone de transit

Lorsqu'un fichier est correctement mappé dans le tableau `$_FILES`, donc sans erreurs, avant de pouvoir l'enregistrer où on le souhaite, il est déposé automatiquement dans une zone temporaire. On peut savoir où il est déposé grâce à la valeur de la clé `tmp_name`.

> L'idée va donc être la suivante : si on veut valider l'upload du fichier, alors on va copier le fichier déposé dans le dossier temporaire vers notre destination

### Enregistrement du fichier sur le serveur

Pour récupérer notre fichier, on peut vérifier l'existence de la clé correspondant à notre nom d'input dans le tableau `$_FILES`.

Ensuite, on va effectuer ce qu'on veut avec, puis l'enregistrer avec la méthode `move_uploaded_file` si tout va bien :

```php
if (isset($_FILES['myFile'])) {
  // on met le fichier dans une variable pour une meilleure lisibilité
  $file = $_FILES['myFile'];

  // On récupère le nom du fichier
  $filename = $file['name'];

  // On construit le chemin de destination
  $destination = __DIR__ . "/img/" . $filename;

  // On bouge le fichier temporaire dans la destination
  if (move_uploaded_file($file['tmp_name'], $destination)) {
    echo $filename . " correctement enregistré<br />";
  }
}
```

Remarquez bien que c'est au moment de faire notre `move_uploaded_file` qu'on déplace notre fichier temporaire vers notre destination. Par ailleurs, on spécifie le chemin complet, nom du fichier inclus, dans la destination !

> Note : vous pouvez également utiliser la fonction `is_uploaded_file` si vous souhaitez simplement vérifier que le fichier a bien été uploadé avec la méthode HTTP POST, mais sans confirmer l'upload et déplacer comme le fait `move_uploaded_file`. Dans ce cas, essayez de garder en tête qu'**il faut transmettre à `is_uploaded_file` le chemin vers le fichier temporaire**
>
> Note : Ici on enregistre notre fichier dans un dossier '/img/'. Ce dossier doit exister avant ! La fonction `move_uploaded_file` ne crée pas le dossier pour nous

### Enregistrer plusieurs fichiers en même temps

Vous pouvez utiliser l'attribut `multiple` sur la balise `input type="file"` :

```html
<form method="POST" enctype="multipart/form-data">
  <!-- Notez bien les [] après photos pour en faire un tableau ! -->
  <input type="file" name="photos[]" multiple />
  <input type="submit" value="Envoyer" />
</form>
```

Ensuite, côté serveur, comment reçoit-il les données ?

```txt
array (size=1)
  'photos' =>
    array (size=5)
      'name' =>
        array (size=4)
          0 => string '30689022_2173861462844244_9173788516323164160_n.png' (length=51)
          1 => string '32390643_1790587770979334_7307382856911683584_o.jpg' (length=51)
          2 => string '52845255_2178439338860840_991282794128736256_o.jpg' (length=50)
          3 => string '53155175_2014283698871580_6028573627476082688_n.jpg' (length=51)
      'type' =>
        array (size=4)
          0 => string 'image/png' (length=9)
          1 => string 'image/jpeg' (length=10)
          2 => string 'image/jpeg' (length=10)
          3 => string 'image/jpeg' (length=10)
      'tmp_name' =>
        array (size=4)
          0 => string 'C:\wamp64\tmp\php4688.tmp' (length=25)
          1 => string 'C:\wamp64\tmp\php4689.tmp' (length=25)
          2 => string 'C:\wamp64\tmp\php468A.tmp' (length=25)
          3 => string 'C:\wamp64\tmp\php468B.tmp' (length=25)
      'error' =>
        array (size=4)
          0 => int 0
          1 => int 0
          2 => int 0
          3 => int 0
      'size' =>
        array (size=4)
          0 => int 106078
          1 => int 223761
          2 => int 157184
          3 => int 73876
```

Vous voyez ici le comportement de PHP : il garde un seul index `photos`, et traite chaque photo comme un nouvel élément de `name`, `size`, etc...! Il va donc falloir boucler sur un de ces tableaux !

Le tableau `error` paraît tout indiqué, pour pouvoir vérifier pour chaque fichier si tout s'est bien passé :

```php
if (isset($_FILES['photos'])) {
  foreach ($_FILES['photos']['error'] as $key => $error) {
    if ($error == UPLOAD_ERR_OK) {
      $tmp_name = $_FILES["photos"]["tmp_name"][$key];
      $filename = $_FILES["photos"]["name"][$key];
      $destination = __DIR__ . "/img/" . $filename;

      if (move_uploaded_file($tmp_name, $destination)) {
        echo $filename . " correctement enregistré<br />";
      }
    }
  }
}
```

## Lien avec une base de données

Après avoir vu comment enregistrer un fichier sur le serveur, l'idée serait de pouvoir par exemple enregistrer une photo de profil, puis l'afficher quand on en a besoin.

On a donc besoin de persister de la donnée afin de la restituer plus tard.

Concernant le fichier en lui-même, le problème semble réglé : on a déjà vu comment stocker le fichier sur le serveur.

Mais qu'en est-il de l'information qu'on souhaite restituer ?

Par exemple, une photo de profil. Elle va certainement se situer dans une table `users`, dans un champ `profilePic` ?

### Stockez dans votre champ de base de données le nom du fichier

Ce qu'il faut retenir, c'est qu'un fichier va avoir un cycle de vie dans votre application :

- Un utilisateur tente d'uploader un fichier. Après avoir passé les contrôles de tailles, éventuellement de types MIME si vous souhaitez vérifier dans votre code qu'il s'agit bien d'une image, etc...on va l'enregistrer physiquement dans le dossier /A/B/photo.png

> Pour mieux organiser notre code, on va séparer le chemin initial (/A/B), qui peut être amené à changer à l'avenir, du nom de fichier (photo.png)

- En base de données, on va donc enregistrer `photo.png`, et pas le chemin complet du fichier. Si l'endroit où on veut stocker les photo des utilisateurs change, alors on pourra le changer dans notre code, sans avoir besoin de mettre à jour toute notre base de données

- Plus tard, l'utilisateur veut afficher une page dans laquelle il faut afficher de nouveau cette photo. Alors depuis l'enregistrement récupéré de la base de données, nous sommes capables de reconstruire le chemin complet pour accéder à la photo. On va injecter dans l'attribut `src` de la balise `img` le chemin complet !

- Enfin, si l'image doit être changée ou supprimée, nous sommes toujours capables de reconstuire le chemin pour y accéder

Vous trouverez dans la partie PDO du dépôt qu'on faisait en cours l'addition de l'upload de fichiers pour un utilisateur !

Le commit avec la liste des fichiers modifiés se trouve à cette adresse : [cliquez ici](https://github.com/ld-web/ynov-b1-php-intro/commit/eef1781ebc0b6aaf20ef9e585bfe8621f2b3739f).

N'hésitez pas à pull les modifications pour les analyser dans vos fichiers.
