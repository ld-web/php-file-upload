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

  foreach ($_FILES as $file) {
    $filename = $file['name'];
    $destination = __DIR__ . "/img/" . $filename;
    if (move_uploaded_file($file['tmp_name'], __DIR__ . "/img/" . $filename)) {
      echo $filename . " correctement enregistré<br />";
    }
  }

  ?>

  <!-- Ne pas oublier l'attribut enctype -->
  <form method="POST" enctype="multipart/form-data">
    <input type="file" name="myFile" />
    <input type="submit" value="Envoyer" />
  </form>
</body>
</html>