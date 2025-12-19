
 <div class="" >
        <div class="" >
            <div class="container-fluid fixed">
                <div class="row menu" style="background-color: rgba(0, 0, 0, 0.24)">
                    <div class="col-md-12 ">
                        <nav class="navbar navbar-expand-lg navbar-light" style="height:4em">
                            <a class="navbar-brand" href="index.php"><img src="images/icons/logo2_footer.png" alt=""
                                    height="75px" width="100px"></a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse"
                                data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <span class="icon-menu"></span>
                            </button>
                            <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                                <ul class="navbar-nav">
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
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
                                        <a class="nav-link" href="#" aria-haspopup="true" aria-expanded="false" data-toggle="modal" data-target="#staticBackdrop">
                                            <span class="icon-magnifier"></span>
                                            Rechercher
                                        </a>
                                       
                                    </li>

                                    <?php
if(!isset($_SESSION['user'])){
?>
                                    <li class="nav-item active">
                                    <a href="auth/login_signup.php" class="btn btn-outline-light top-btn">
                                            Login/Signup</a>
                                    </li>

                                    <?php }else {
?>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="viewwish.php?user=<?php echo $_SESSION['user']?>"  >
                                            <span class="icon-user  "></span>
                                            edit profile
                                        </a>
                                       
                                    </li>
                                    
                                    <li class="nav-item active">
                                    <a href="auth/login_signup.php?logout" class="btn btn-outline-light btn-dark top-btn">
                                            Logout</a>
                                    </li>

                                    <?php
                                    } ?>


                                    <li class="nav-item">
                                        <a href="restopropal.html" class="btn btn-outline-light btn-danger top-btn"><span class="ti-plus"></span> Recommander un
                            établissement</a>
                                    </li>

                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>    
