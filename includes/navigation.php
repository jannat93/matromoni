<!-- ============================  Navigation Start =========================== -->
<!-- Font Awesome 4.7 CDN added for proper icon rendering -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<div class="navbar navbar-inverse-blue navbar"style="margin-bottom: 1;">
  <!--<div class="navbar navbar-inverse-blue navbar-fixed-top">-->
  <div class="navbar-inner navbar-inner_1" style="padding:5px px 0;">
  <div class="container" style="display: flex; align-items: left; justify-content: space-between;">
      <div class="navigation">
        <nav id="colorNav">
          <ul>
            <li class="green">
              <!-- Replaced 'icon-home' with proper Font Awesome class -->
              <a href="#" class=""></a>
              <ul>
                <?php 
                if (isloggedin()) {
                  $id = $_SESSION['id'];
                  echo "<li><a href=\"userhome.php?id=$id\">Home</a></li>";
                  echo "<li><a href=\"logout.php\">Logout</a></li>";
                } else {
                  echo "<li><a href=\"login.php\">Login</a></li>";
                  echo "<li><a href=\"register.php\">Register</a></li>";
                }
                ?>
              </ul>
            </li>
          </ul>
        </nav>
      </div>

      <div class="navbar navbar-inverse-blue navbar">
    <div class="navbar-inner navbar-inner_1">
        <div class="container" style="display: flex; justify-content: space-between; align-items: left;">
        <a class="brand" href="index.php" style="display: flex; align-items: center; text-decoration: none;">
    <img src="images/logo.jpg" alt="logo" style="max-height: 50px; width: auto; margin-right: 5px;">
    <div style="display: flex; align-items: baseline;">
        <span style="font-size: 20px; color: #fff; font-weight: bold; white-space: nowrap;">MatchMingle</span>
        <span style="font-size: 12px; color: #ddd; margin-left: 5px;">Choose your partner</span>
    </div>
</a>

            <div class="pull-right" style="float: none !important;"> <nav class="navbar nav_bottom" role="navigation">
                    <div class="navbar-header nav_2">
                        <button type="button" class="navbar-toggle collapsed navbar-toggle1" data-toggle="collapse" data-target="#bs-megadropdown-tabs">
                            Menu
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="#"></a>
                    </div>

                    <div class="collapse navbar-collapse" id="bs-megadropdown-tabs">
    <ul class="nav navbar-nav nav_1" style="margin: 0; display: flex; justify-content: flex-end;">
        <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="about.php"><i class="fa fa-info-circle"></i> About</a></li>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-search"></i> Search<span class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
                <li><a href="search.php">Regular Search</a></li>
                <li><a href="search-id.php">Search By Profile ID</a></li>
                <li><a href="faq.php">Faq</a></li>
            </ul>
        </li>
        <li class="last"><a href="contact.php"><i class="fa fa-envelope"></i> Contacts</a></li>
    </ul>
</div>
                </nav>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

        </nav>
      </div>
      <div class="clearfix"> </div>
    </div>
  </div>
</div>
<!-- ============================  Navigation End ============================ -->