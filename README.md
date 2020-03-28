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
