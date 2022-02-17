<?php

// SMG Mobile Developer Test 1 — Tiny URI generator for images
// *** Minimum Pass Answer: Basic PHP Solution ***
// 
// Run php -S localhost:8000 in the folder containing the index.php and api.php files

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Generate Tiny URI for Uploaded Image</title>
  </head>

  <body>
    <form action="api.php" method="POST" enctype="multipart/form-data" >
      <label for="fileUpload">Select Image</label>
      <input type="file" accept="image/*" id="fileUpload" name="fileUpload"/>
      <input type="submit" value="Generate Tiny URI" id="submitImage" name="submit" disabled />
    </form>
    <img id="imagePreview" style="width: 50%;"/>

    <script>
      fileUpload.onchange = function () {
        var file = fileUpload.files[0];
        var sizeMB = parseFloat(file.size / 1000000).toFixed(1)
        if (sizeMB > 10) {
          alert(sizeMB + " MB is too big! File size limited to 10 MB.")
          fileUpload.value = ""
          file = ""
        } 
        if (!(file.type.match("image/(png|jpeg|gif|webp|bmp)"))) {
          alert("The image type, " + file.type + ", is not supported.")
          fileUpload.value = ""
          file = ""
        } else {
          imagePreview.src = URL.createObjectURL(file)
          submitImage.disabled = false
        }       
      }
    </script>
  </body>
</html>
