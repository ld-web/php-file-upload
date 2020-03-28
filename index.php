<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>

<body>
  <?php
  var_dump($_POST); // ne contient pas les fichiers
  var_dump($_FILES);

  // Fichier simple
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

  // Fichiers multiples
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

  ?>

  <!-- Ne pas oublier l'attribut enctype -->
  <form method="POST" enctype="multipart/form-data">
    <input type="file" name="myFile" />
    <input type="submit" value="Envoyer" />
  </form>

  <form method="POST" enctype="multipart/form-data">
    <input type="file" name="photos[]" multiple />
    <input type="submit" value="Envoyer" />
  </form>
</body>

</html>