<!doctype html>
<html lang="ca">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
    <!-- Own files -->
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/{##estil##}.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/modernizr-1.7.min.js"></script>
    <script type="text/javascript" src="js/Hyphenator.js"></script>
    <script type="text/javascript" src="js/build.js"></script>
    <title>IOC - Utilitats d'activitats - {##titol##}</title>
</head>
<body>
  <header class="main_header">
    <div class="container">
      <h1>{##titol##}</h1>
      <h2>{##subtitol##}</h2>
    </div>
  </header>

  <hr class="separador_impr">
  
  <section id="intro">
    <div class="container">
<WIOCCL:IF condition="{##mostrarAutor##}==true">        
      <p>autor: {##nom_real##}</p>
</WIOCCL:IF>      
<WIOCCL:IF condition="{#_IS_STR_EMPTY(''{##entitatResponsable##}'')_#}!=true">        
      <p>Editat per: {##entitatResponsable##}</p>
</WIOCCL:IF>      
      <p>data: {##data_fitxercontinguts##}</p>
    </div>
  </section>

  <br class="salt_impr">

  <div class="container">
    {##documentPartsHtml##}  
    <div id="printbtn" onclick="printDoc('{##pdf_filename_toprint##}')"><span>Descarrega't el PDF</span></div>
  </div>

</body>
</html>
