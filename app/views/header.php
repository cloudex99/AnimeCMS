<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="<?=@$description?>">

    <title><?=@$title?></title>

    <!-- Bootstrap CSS & Fontawesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script defer src="https://use.fontawesome.com/releases/v5.0.0/js/all.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css?v=0.002">

    <!-- Fake favicon, replace with real one-->
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
    <script type="text/javascript">
        var onPage = '<?=PAGE?>';
    </script>
</head>
<body class="bg-dark">


<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">AnimeCMS</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/anime-list">Anime</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/browse-cartoons">Cartoons</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/anime-movies">Movies</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/genres">Genres</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/ongoing-anime">Ongoing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?=Anime::random()->url()?>">Random</a>
                </li>
                <?php if(User::isLoggedIn() && User::hasAccess()):?>
                    <li class="nav-item">
                        <a class="nav-link" href="/settings" >Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout" >Logout</a>
                    </li>
                <?php else:?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login" >Login</a>
                    </li>
                <?php endif;?>
            </ul>
            <form id="search_form" class="form-inline my-2 my-lg-0" action="/search" method="get">
                <input class="form-control mr-sm-2" name="term" type="search" id="search_value" placeholder="Search" aria-label="Search">
                <button class="btn btn-success btn-sm my-2 my-sm-0 px-1 rounded-0" id="asbtn" type="submit"><i class="fas fa-search"></i> Anime</button>
                <button class="btn btn-secondary btn-sm my-2 my-sm-0 px-1 rounded-0" id="csbtn" type="submit"><i class="fas fa-search"></i> Cartoon</button>
            </form>

        </div>
    </div>
</nav>

<div id="wrapper" class="container">
    <div class="row">


