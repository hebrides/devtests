<?php

// SMG Mobile Developer Test 1 — Tiny URI generator for images 
// *** Minimum Pass Answer: Basic PHP Solution ***

// TODO: - Add support for .pdf and other complex image data types
//       - Persist image in blockstorage / database
//       - Use a presigned URL to improve upload speeds
//       - Add client side image compression to save user bandwidth
//       - Improve security by scanning uploaded files for malicious code
//       - Improve UX with Bootstrap, Google Fonts & Websockets

// Step 1: Validate submission 
if(isset($_POST["submit"]) && is_uploaded_file($_FILES["fileUpload"]["tmp_name"])) {
  // Check filesize 
    if ($_FILES["fileUpload"]["size"] > 10000000) {
      exitWithError("Exceeded filesize limit.");
    }

  // Check valid image. This strategy can be improved for safety, ref:
  //  https://stackoverflow.com/questions/21525125/check-image-for-malicious-code-and-delete-it#21525203
  // 
  // - We'll remove the file extension and change the image to read only 
  // in another step as a guard against malicious code execution.
  //
  // - We should also consider scanning the file for keywords that
  // could be executed client side before releasing this app to the wild
    $check = getimagesize($_FILES["fileUpload"]["tmp_name"]);
    if(!$check) {
      exitWithError("File type is not supported.");
    }

  // Check MIME type
    $types=["jpeg","jpg","png","gif","bmp","webp"];
    if (!in_array(substr($check["mime"],6),$types)) {
      exitWithError("File type is not supported.");
    } 

} else {
  exitWithError("Something went wrong, please try again.");
}

// Step 2: Generate tiny URI--
// Ref: https://www.php.net/manual/en/features.file-upload.php
//      https://stackoverflow.com/questions/8238860/maximum-number-of-files-directories-on-linux#8238973
// Notes: 
// - We'll write to a unique folder / file for now. 
// - THIS WORKS BUT IS SUBOPTIMAL!! 
// - Should OPTIMIZE later via Database, Blockstorage, Pre-signed URLs, Compression, etc.:
//   https://softwareontheroad.com/aws-s3-secure-direct-upload/

$requestURI=getRequestURI(); 
$tinyURI = randomString(9);  
if ($requestURI && 
    $tinyURI && 
    mkdir($tinyURI) && 
    move_uploaded_file($_FILES["fileUpload"]["tmp_name"], "$tinyURI/$tinyURI") &&
    writeImageViewPage($requestURI, $tinyURI, $tinyURI)) {
  // make image file read only
  chmod("$tinyURI/$tinyURI", 0600);
  echo "Success! Your image is available at <a href=\"$requestURI/$tinyURI/\">$requestURI/$tinyURI/</a>";
} else {
  exitWithError("Something went wrong, please try again.");
}

function writeImageViewPage($requestURI, $localPath, $imageName) {
  $html = "
  <!DOCTYPE html>
  <meta property=\"og:title\" content=\"Generated from Tiny URI for Images\" />
  <meta property=\"og:type\" content=\"website\" />
  <meta property=\"og:url\" content=\"$requestURI/$localPath/\" />
  <meta property=\"og:image\" content=\"$requestURI/$localPath/$imageName\" />
  <html>
    <head>
      <title>Generated Tiny URI for Image</title>
    </head>
    <body>
      <img src=\"$requestURI/$localPath/$imageName\" style=\"width: 50%;\" />
    </body>
  </html>
  "; 
  // Ref: https://www.php.net/manual/en/function.file-put-contents.php
  return (file_put_contents("./$localPath/index.html", $html, LOCK_EX) !== false);
}

function getRequestURI() {
  // The request URI can be set manually in an ENV VAR, then imported, or directly set here like this: 
  // return "http://localhost:8000" // or
  // return "https://tinyuri.jodiethecodeninja.io"
  
  // The below automatically generates the request URI using PHP's predefined variables
  // which (should) allow the script to run "as is" anywhere it's hosted. 
  // Test how the script behaves on your web server before deploying:
  // Ref: https://www.php.net/manual/en/reserved.variables.php
  //      https://www.designcise.com/web/tutorial/how-to-check-for-https-request-in-php
  //      https://stackoverflow.com/questions/1283327/how-to-get-url-of-current-page-in-php#1283330
  // 
  $isHttps = $_SERVER['HTTPS'] ?? $_SERVER['REQUEST_SCHEME'] ?? $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
  $requestScheme = ($isHttps && (strcasecmp('on', $isHttps) == 0 || strcasecmp('https', $isHttps) == 0)) ? 
  "https" : "http";  
  return "$requestScheme://{$_SERVER['HTTP_HOST']}";
}

// From: https://stackoverflow.com/questions/4757392/php-fast-random-string-function#27371037
function randomString($length) {
  // Use base64 to generate a URI with upper- and lowercase letters, numbers 0-9
  // (26+26+10)^8 = 218,340,105,584,896 => ~218 trillion unique strings
  // (26+26+10)^9 =  13,537,086,550,000,000 => ~13 quadrillion unique strings
  // Ref: https://www.mathsisfun.com/combinatorics/combinations-permutations-calculator.html
   $result = null;
   $replace = array('/', '+', '=');
   while(!isset($result[$length-1])) {
      $result.= str_replace($replace, NULL, base64_encode(random_bytes($length)));
   }
   return substr($result, 0, $length);
}

function exitWithError($message) {
  die($message);
}

  //
  //        _.--._
  // \`--._/ ---- \_.--'/
  //  `.( )/__\/__\( ).'
  //    `-\ / .. \ /-'
  //     ./\_`--'_/\.
  //    /  \ `--'  / \
  //
  // "Do or do not, there is no try."
  // https://www.youtube.com/watch?v=fxCR2bFWHxM

?>

