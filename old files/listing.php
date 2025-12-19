<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="SamTech">
    <meta name="description" content="Retrouvez et réservez les meilleurs restaurants Halal et sans alcool partout en France avec le bon resto halal ">
    <meta name="keywords" content="halal hallal restaurant muslim">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css" />

    <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>

    <script src="create.js " defer></script>
    <!-- Favicons -->
    <link rel="shortcut icon" href="#">
    <!-- Page Title -->
    <title>Le bon resto annuaire des restaurants Halal en France</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,400i,500,700,900" rel="stylesheet">
    <!-- Simple line Icon -->
    <!-- Hover Effects -->
    <link rel="stylesheet" href="css/set1.css">
    <!-- Main CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
    <!-- Bootstrap 5-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">



</head>

<body>
    <?php

    if (isset($_POST['adresse'])) {
        $addr = $_POST['adresse'];
        $type = $_POST['foodType'];
        $radius = $_POST['searchRadius'];
        $page = 1;
    } else {
        $addr = $_GET['adresse'];
        
        $type = $_GET['foodType'];
       
        $radius = $_GET['searchRadius'];
        
        $page        = $_GET['page'];
        
    }
   




    ?>

    <p id="paddr" style='display:none'><?php echo $addr; ?> </p>
    <p  id="pradius" style='display:none'><?php echo $radius; ?></p>
    <p  id="pType" style='display:none'><?php echo $type; ?></p>


    <p style='display:none' id="maLoc"></p>
    <p id="uAddr" style='display:none'></p>
    <p id="currentPage" style='display:none'><?php echo $page ?> </p>
    <button id='addResto' style='display:none'>GET DATA</button>



    <!--============================= HEADER =============================-->
    <div class="dark-bg sticky-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <a class="navbar-brand" href="index.html"><img src="images/icons/logo2_footer.png" alt="" height="75px" width="100px"></a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="icon-menu"></span>
                        </button>
                        <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a class="nav-link" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Explore
                                        <span class="icon-arrow-down"></span>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                    </div>
                                </li>
                                <li class="nav-item dropdown">
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                    </div>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link" href="#" id="navbarDropdownMenuLink2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Pages
                                        <span class="icon-arrow-down"></span>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                    </div>
                                </li>
                                <li class="nav-item active">
                                    <a class="nav-link" href="#">About</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">Blog</a>
                                </li>
                                <li><a href="#" class="btn btn-outline-light top-btn"><span class="ti-plus"></span> Add Listing</a></li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!--//END HEADER -->
    <!--============================= DETAIL =============================-->
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-7 responsive-wrap">
                    <div class="row detail-filter-wrap">
                        <div class="col-md-4 featured-responsive">
                            <div class="detail-filter-text">
                                <p>adresse : <span id="addrspan"></span></p>
                            </div>
                        </div>
                        <div class="col-md-8 featured-responsive">
                            <div class="detail-filter">
                                <p>Filtrer</p>
                                <form class="filter-dropdown">
                                    <select class="custom-select mb-2 mr-sm-2 mb-sm-0" id="inlineFormCustomSelect">
                                        <option selected>+ Pertinent</option>
                                        <option value="1">Alphabétique</option>
                                        <option value="2">Distance</option>
                                        <option value="3">Bon marché</option>
                                    </select>
                                </form>
                                <form class="filter-dropdown">
                                    <select class="custom-select mb-2 mr-sm-2 mb-sm-0" id="inlineFormCustomSelect1">
                                        <option selected>spécialités </option>
                                        <option value="1">spécialité 1</option>
                                        <option value="2">spécialité 1</option>
                                        <option value="3">spécialité 1</option>
                                    </select>
                                </form>
                                <div class="map-responsive-wrap">
                                    <a class="map-icon" href="#"><span class="icon-location-pin"></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row detail-checkbox-wrap">
                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3">

                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Halal</span>
                            </label>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Vegan </span>
                            </label>
                        </div>

                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3">

                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Casher </span>
                            </label>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Bio</span>
                            </label>
                        </div>

                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3">

                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Livraison</span>
                            </label>
                        </div>


                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3">

                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Pets Friendly</span>
                            </label>

                        </div>
                    </div>


                    <!-- Resultat de la recherche -->


                    <div class="row light-bg detail-options-wrap">
                   <!-- // style='display:none' -->
                    <p id='currentPage' style='display:none'><?php echo $_GET['page']?> </p>

                        <div class="col-sm-6 col-lg-12 col-xl-12 featured-responsive">

                            <div class="container col-md-offset-5">
                                
                                <nav>

                                    <ul class="pagination ulPage">

                                        <!-- Lien vers la page précédente (désactivé si on se trouve sur la 1ère page) -->

                                        <li id='pageDown'> <a class="page-link aLink" href="javascript:void(0)">Précédente</a> </li>

                                        <ul class="pagination" id='paginationU'>

                                        </ul>

                                        <!-- Lien vers la page suivante (désactivé si on se trouve sur la dernière page) -->

                                        <li id='pageUp'>

                                            <a class="page-link aLink" href="javascript:void(0)">Suivante</a>

                                        </li>

                                    </ul>

                                </nav>

                            </div>






                            <div class="featured-place-wrap">


                                <div class='row' id="divHigh">


                                    <!-- A REPETER AVEC MYSQL !!!!!! -->

                                    <!-- <section class="search-result-item col-12"> -->

                                    <!-- <img  class="image sqlImg" src="https://images-na.ssl-images-amazon.com/images/I/412Q6swf2yL._SY355_.jpg"> -->

                                    <!-- <div class="search-result-item-body col-10"> -->
                                    <!-- <div class="row" > -->
                                    <!-- <div class="col-10" > -->
                                    <!-- <h4 class="search-result-item-heading"><a href="#" class='aH4'>The Grill House</a> <span class="badge bg-danger fw-normal pull-right">Recommandé !</span></h4> -->
                                    <!-- <p class="info">127 rue des sans dents 93200 Saint Denis</p> -->
                                    <!-- <p class="description">Bla bla bla la viande c'est bon!!!!! Hummm Charal.</p> -->
                                    <!-- </div> -->
                                    <!-- <div class="col-2 text-align-right"> -->
                                    <!-- <p class="value3 mt-sm">Restaurant de grillades</p> -->
                                    <!-- <div id='divIty'> -->
                                    <!-- <p class="fs-mini text-muted">$$$</p>                       <a href="www.google.fr"><img id='imgtest' src="images/icons/itinerary.png" heigth='50px' whidth alt="qsdqsdqsd"><figcaption>Itinéraire</figcaption></a>    -->
                                    <!-- </div> -->
                                    <!-- </p><a class="btn btn btn-outline-dark btn-sm" href="#">En savoir plus</a> -->
                                    <!-- </div> -->
                                    <!-- </div> -->
                                    <!-- </div> -->
                                    <!-- </section>    -->


                                    <!-- FIN DE A REPETER AVEC MYSQL !!!!!! -->


                                </div>




                            </div>
                            <div class="container col-md-offset-5">
                             
                                <nav>
                                
                                    <ul class="pagination ulPage">
                                    
                                        <!-- Lien vers la page précédente (désactivé si on se trouve sur la 1ère page) -->

                                        <li id='pageDown'> <a class="page-link aLink" href="javascript:void(0)">Précédente</a> </li>

                                        <ul class="pagination" id='paginationD'>

                                        </ul>

                                        <!-- Lien vers la page suivante (désactivé si on se trouve sur la dernière page) -->

                                        <li id='pageUp'>

                                            <a class="page-link aLink" href="javascript:void(0)">Suivante</a>

                                        </li>

                                    </ul>

                                </nav>

                            </div>

                        </div>



                    </div>
                </div>
                <div class="col-md-5 responsive-wrap map-wrap">

                    <!-- data-toggle="affix" -->
                    <!--  map will appear here! Edit the Latitude, Longitude and Zoom Level below using data-attr-*  -->
                    <div id="map"></div>

                </div>
            </div>
        </div>
    </section>
    <!--//END DETAIL -->
    <!--============================= FOOTER =============================-->
    <footer class="main-block dark-bg">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="copyright">
                        <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                        <p>On n'en est pas encore là !!!!!!</p>
                        <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                        <ul>
                            <li><a href="#"><span class="ti-facebook"></span></a></li>
                            <li><a href="#"><span class="ti-twitter-alt"></span></a></li>
                            <li><a href="#"><span class="ti-instagram"></span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!--//END FOOTER -->








    <!-- jQuery, Bootstrap JS. -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-3.2.1.min.js"></script>



    <script>



















    </script>


</body>

</html>