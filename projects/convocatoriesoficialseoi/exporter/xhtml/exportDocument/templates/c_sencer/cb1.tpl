<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">

    <!--link rel="stylesheet" href="css/basic.css"-->
    <link rel="stylesheet" href="css/main.css">

    <title>IOC - Escola Oficial d'Idiomes</title>
  </head>
  <body>
    
<!-- Side navigation -->

<div id="mySidenav" class="sidenav open">
  <div class="closebtn" onclick="closeNav()">&times;</div>
  <div class="content_index fade_in" id="myContentIndex">
      @@TOC(convocatoria_b1)@@
  </div>
</div>

<span class="hamburger hidden" id="myHamburger" onclick="openNav()"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" width="30" height="30" focusable="false"><title>Menu</title><path stroke="currentColor" stroke-width="2.4" stroke-linecap="butt" stroke-miterlimit="10" d="M4 7h22M4 15h22M4 23h22"></path></svg></span>


  <header class="main_header">
    <div class="container">
      <h1>{##title_b1##}</h1>
    </div>
  </header>

  <hr class="separador_impr">
  
  <section id="intro">
    <div class="container">

    </div>
  </section>

  <br class="salt_impr">

  <div class="container">
    {##convocatoria_b1##}
    <div id="printbtn" onclick="printDoc('../c-b1.pdf')"><span>Descarrega't el PDF</span></div>
  </div>
    
  <script src="js/main.js"></script>
  </body>
</html>
