<?php

session_start();

$dateNowStart = (new \DateTime())->modify('-24 hours')->format('Y-m-d');
$dateNowEnd = (new \DateTime())->modify('+24 hours')->format('Y-m-d');

$urlQuakeToday = "https://www.seismicportal.eu/fdsnws/event/1/query?format=xml&start=" . date('Y-m-d') . "&end=" . $dateNowEnd . "&minmag=4&limit=50";

//function that gets earthquake data
function getEarthquakes($url)
{

    $xml = new DOMDocument();

    //@ error suppression, remove to see what hapen 
    if (@$xml->load($url) === false) {
        return null;
    }
    $xml->load($url);
    $items = array();

    $events = $xml->getElementsByTagName('event');
    foreach ($events as $event) {
        $preferredOriginID = $event->getElementsByTagName('preferredOriginID')->item(0)->nodeValue;
        $eventid = explode("/", $preferredOriginID);
        $eventid = $eventid[2];

        $lat = $event->getElementsByTagName('latitude')->item(0)->getElementsByTagName('value')->item(0)->nodeValue;
        $lon = $event->getElementsByTagName('longitude')->item(0)->getElementsByTagName('value')->item(0)->nodeValue;

        $description = $event->getElementsByTagName('description')->item(0);
        $location = $description->getElementsByTagName('text')->item(0)->nodeValue;


        $date1 = date_create($event->getElementsByTagName('time')->item(0)->getElementsByTagName('value')->item(0)->nodeValue);
        $date = $date1->format('d-m-Y H:i:s');

        $depth = $event->getElementsByTagName('depth')->item(0)->nodeValue;

        $mag = $event->getElementsByTagName('magnitude')->item(0)->getElementsByTagName('mag')->item(0)->getElementsByTagName('value')->item(0)->nodeValue;
        //$magType = $xml->getElementsByTagName('magnitude')->item(0)->getElementsByTagName('type')->item(0)->nodeValue;

        $earthquake = array();
        array_push($earthquake, $eventid, $lat, $lon, $location, $date, $depth, $mag);
        $items[] = $earthquake;
    }
    return $items;
}


//generate query url
function generateQuery($onlyParamater = false)
{

    $url = "https://www.seismicportal.eu/fdsnws/event/1/query?format=xml&limit=100&";

    $start = $_GET['start'];
    $end = $_GET['end'];
    $minmag = $_GET['minmag'];
    $maxmag = $_GET['maxmag'];
    $magtype = $_GET['magtype'];
    $mindepth = $_GET['mindepth'];
    $maxdepth = $_GET['maxdepth'];
    $orderby = $_GET['orderby'];

    $minlat = $_GET['minlat'];
    $maxlat = $_GET['maxlat'];
    $minlon = $_GET['minlon'];
    $maxlon = $_GET['maxlon'];

    $lat = $_GET['lat'];
    $lon = $_GET['lon'];
    $minradius = $_GET['minradius'];
    $maxradius = $_GET['maxradius'];

    // compact , filter removes empty paramaters
    $query = compact("orderby", "start", "end", "minmag", "maxmag", "magtype", "mindepth", "maxdepth", "minlat", "maxlat", "minlon", "maxlon", "lat", "lon", "minradius", "maxradius");
    $query = array_filter($query, fn ($value) => !empty($value));

    //if true returns only name and value, no &, = etc. 
    if ($onlyParamater) {
        return $query;
    } else {
        $test = http_build_query($query) . "\n";
        return $url . $test;
    }
}


function getColor($mag)
{
    return $mag >= 8 ? ["#FF0000", "eqGreat"] : ($mag >= 7 ? ["#FF6000", "eqMajor"] : ($mag >= 6 ? ["#FFA000", "eqStrong"] : ($mag >= 5 ? ["#FFF071", "eqModerate"] : ($mag >= 4 ? ["#FFE500", "eqLight"] : ($mag >= 2 ? ["#1b8700f1", "eqMinor"] :
        ["#A2A2A2", "eqMicro"])))));
}

function returnUrl($paramaters, $cururl = false)
{
    $dateNowStart = (new \DateTime())->modify('-24 hours')->format('Y-m-d');
    $dateNowEnd = (new \DateTime())->modify('+24 hours')->format('Y-m-d');

    $dateStartWeek = (new \DateTime())->modify('-1 week')->format('Y-m-d');
    $dateStartMonth = (new \DateTime())->modify('-1 month')->format('Y-m-d');
    $dateStartYear = (new \DateTime())->modify('-1 year')->format('Y-m-d');

    $url = "https://www.seismicportal.eu/fdsnws/event/1/query?format=xml&start=" . $dateNowStart . "&end=" . $dateNowEnd . "&limit=50";
    $urlWeek = "https://www.seismicportal.eu/fdsnws/event/1/query?format=xml&start=" . $dateStartWeek . "&end=" . $dateNowEnd . "&limit=50";
    $urlMonth = "https://www.seismicportal.eu/fdsnws/event/1/query?format=xml&start=" . $dateStartMonth . "&end=" . $dateNowEnd . "&limit=50";
    $urlYear = "https://www.seismicportal.eu/fdsnws/event/1/query?format=xml&start=" . $dateStartYear . "&end=" . $dateNowEnd . "&limit=50";

    switch ($paramaters) {
        case "tall":
            return [$url . '&orderby=time', "Most recent (Today)"];
            break;
        case "teurope":
            return [$url . '&minlat=10&minlon=-30&maxlon=65&orderby=time', 'Most recent (Europe)'];
            break;
        case "stoday":
            return [$url . '&orderby=magnitude', 'Strongest (Today)'];
            break;
        case "sweek":
            return [$urlWeek . '&orderby=magnitude', 'Strongest (Week)'];
            break;
        case "smonth":
            return [$urlMonth . '&orderby=magnitude', 'Strongest (Month)'];
            break;
        case "syear":
            return [$urlYear . '&orderby=magnitude', 'Strongest (Year)'];
            break;
        case "sall":
            return ['https://www.seismicportal.eu/fdsnws/event/1/query?format=xml&minmag=7&orderby=magnitude&limit=50', 'Strongest (All time)'];
            break;
        case "reu":
            return [$url . '&minlat=10&minlon=-30&maxlon=65', 'Region (Europe)'];
            break;
        case "roc":
            return [$url . '&minlat=10&minlon=-30&maxlon=65', 'Region (Oceania)'];
            break;
        case "all":
            return [$url . '&orderby=magnitude-asc', 'Region (All)'];
            break;
        default:
            echo "Error";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset=utf-8 />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property=“og:image” content=“img/eq_social.png” />
    <meta property=”og:image:width” content=”1200″ />
    <meta property=”og:image:height” content=”627″ />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://earthquakenow.cf/"/>
    <meta property="og:title" content="Earthquake Now" />
    <meta property="og:description" content="Information about real-time earthquakes, online catalog search of archives, seismicity maps and statistics." />

    <meta name="description" lang="en" content="Information about real-time earthquakes, online catalog search of archives, seismicity maps and statistics.">
    <meta name="keywords" content="earthquake, last earthquake,earthquake today,real time seismicity,seismic,seismicity,seismology,sismologie,EMSC,CSEM,sumatra,tsunami,map,richter,mercalli,moment tensor,epicenter,magnitude,seismology,foreshock,aftershock,tremor">
    <title>Earthquake Now - Latest earthquake information</title>
    <link rel="icon" href="img/icon_eq.png" defer>
    <link rel="stylesheet" href="style.css" defer>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <!-- leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

    <script src="https://cdn.jsdelivr.net/npm/sockjs-client@1/dist/sockjs.min.js"></script>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css" defer>
    <style>
        html,
        body {
            overflow: hidden;
        }
    </style>


</head>

<script>
    var earthquakes = <?php
                        if (isset($_GET['submitCustomFilter'])) {
                            $earthquakesArr = getEarthquakes(generateQuery());
                            echo json_encode($earthquakesArr);
                        } else if (isset($_GET['nextBtn'])) {
                            $earthquakesArr =  getEarthquakes("https://www.seismicportal.eu/fdsnws/event/1/query?format=xml&start=20-03-2021&end=" . $dateNowEnd . "&minmag=3&5offset=50&limit=50");
                            echo json_encode($earthquakesArr);
                        } else {

                            if (isset($_GET['quickFilter'])) {
                                $earthquakesArr = getEarthquakes(returnUrl($_GET['quickFilter'])[0]);
                                echo json_encode($earthquakesArr);
                            } else {
                                $earthquakesArr = getEarthquakes($urlQuakeToday);
                                echo json_encode($earthquakesArr);
                            }
                        }
                        ?>;
</script>

<body>


    <nav class="navbar navbar-expand-md navbar-dark fixed-top nvbr darkBackground">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><img src="img/logo_eqv3_light.png" class="d-inline-block align-top" id="logo" alt="" style="width:150px"></a>
            <button class="navbar-toggler" type="button" onclick="showSidebar()" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">

                <ul class="navbar-nav">

                    <li class="nav-item" id="navGlos">
                        <a class="nav-link active" aria-current="page" href="glossary.html">Glossary</a>
                    </li>
                    <li class="nav-item" id="navAbt">
                        <a class="nav-link active" aria-current="page" href="about.html">About</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container-fluid m-0 p-0">

        <div id="sidebar" class="darkBackground">
            <div class="sidebarItems px-1 pb-5">

                <?php
                //Cards showing current selection, filters, etc.
                if (isset($_GET['submitCustomFilter'])) {
                    $submitParamaters = generateQuery(true);
                    echo '<div class="card w-100 mt-1 border-0 darkItem"><div class="card-body darkItem"> <h5 class="card-title">Results for </h5> <div class="table-responsive"> <table class="table">';
                    foreach ($submitParamaters as $key => $value) {
                        echo "<tr><td>$key<td><td>$value</td></tr>";
                    }
                    echo '</table></div> <a class="btn btn-primary" href="index.php" role="button">Reset filter</a></div></div>';
                } else if (isset($_GET['quickFilter'])) {
                    echo '<div class="card w-100 mt-1 border-0"><div class="card-body darkItem"><h5 class="card-title">' . returnUrl($_GET['quickFilter'])[1] . '</h5><h6 class="card-subtitle mb-2 text-muted">' . date('Y-m-d H:i:s T') . '</h6> <a class="btn btn-primary" href="index.php" role="button">Reset filter</a></div></div>';
                } else {
                    echo '<div class="card w-100 mt-1 border-0"><div class="card-body darkItem"><h5 class="card-title">Significant (24h) </h5><h6 class="card-subtitle mb-2 text-muted">' . date('Y-m-d H:i:s T') . '</h6></div></div>';
                }
                ?>



                <div class="card w-100 mt-1 border-0">
                    <div class="card-body darkItem">

                        <!-- Quick filter form -->
                        <form class="input-group" name="formReg" id="formReg" action="" method="GET">
                            <select class="form-select" id="quickFilter" aria-label="Example" onchange="this.form.submit()" name="quickFilter">
                                <option value="" disabled selected hidden>Quick filter</option>
                                <optgroup label="Time">
                                    <option value="tall">Most recent (All/24h)</option>
                                    <option value="teurope">Most recent (Europe)</option>
                                </optgroup>
                                <optgroup label="Stength">
                                    <option value="stoday">Strongest (24h)</option>
                                    <option value="sweek">Strongest (Week)</option>
                                    <option value="smonth">Strongest (Month)</option>
                                    <option value="syear">Strongest (Year)</option>
                                    <option value="sall">Strongest (All time)</option>
                                </optgroup>

                                <optgroup label="Region">
                                    <option value="reu">Europe</option>
                                    <option value="roc">Oceania</option>
                                </optgroup>

                            </select>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal"> Advanced <i class="bi bi-funnel"></i></button>
                        </form>
                        <!-- Advanced filter -->
                        <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content ">

                                    <div class="modal-header darkItem">
                                        <div class="modal-title">Advanced search</div>
                                        <button class="btn close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body darkItem">

                                        <form action="" method="get" class="row g-3">

                                            <div class="col-md-6">
                                                <label for="start" class="form-label">Start date</label>
                                                <input type="date" class="form-control" id="start" name="start" max='<?php echo date('Y-m-d'); ?>'>

                                            </div>
                                            <div class="col-md-6">
                                                <label for="end" class="form-label">End date</label>
                                                <input type="date" class="form-control" id="end" name="end">
                                            </div>

                                            <div class="col-md-4">
                                                <label for="minmag" class="form-label">Minimum magnitude</label>
                                                <input type="text" class="form-control" id="minmag" name="minmag">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="maxmag" class="form-label">Maximum magnitude</label>
                                                <input type="text" class="form-control" id="maxmag" name="maxmag">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="magtype" class="form-label">Magnitude type</label>
                                                <input type="text" class="form-control" id="magtype" name="magtype">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="mindepth" class="form-label">Minimum depth</label>
                                                <input type="text" class="form-control" id="mindepth" name="mindepth">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="maxdepth" class="form-label">Maximum depth</label>
                                                <input type="text" class="form-control" id="maxdepth" name="maxdepth">
                                            </div>

                                            <div class="col-md-12">
                                                <label for="origin" class="form-label">Order by: </label>

                                                <select class="form-select" aria-label="Default select example" name="orderby">
                                                    <option selected>time</option>
                                                    <option value="time-asc">time-asc</option>
                                                    <option value="magnitude">magnitude</option>
                                                    <option value="magnitude-asc">magnitude-asc</option>
                                                </select>
                                            </div>
                                            <hr>
                                            <div class="container-fluid">
                                                <p class="row g-o mx-1">
                                                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConstraint" aria-expanded="false" aria-controls="collapseConstraint">
                                                        Geographic constraints
                                                    </button>
                                                </p>
                                                <div class="collapse mt-1" id="collapseConstraint">

                                                    <h6 class="mb-2">Box area constraints</h6>
                                                    <div class="row g-3 darkItem">
                                                        <div class="col-md-3">
                                                            <label for="minlat" class="form-label">Minimum latitude</label>
                                                            <input type="text" class="form-control" id="minlat" name="minlat" aria-describedby="minlatHelp" placeholder="Default: -90">
                                                            <div id="minlatHelp" class="form-text">Degrees: -90.0 - 90.0</div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="maxlat" class="form-label">Maximum longitude</label>
                                                            <input type="text" class="form-control" id="maxlat" name="maxlat" aria-describedby="maxlatHelp" placeholder="Default: -90">
                                                            <div id="maxlatHelp" class="form-text">Degrees: -90.0 - 90.0</div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="minlon" class="form-label">Minimum latitude</label>
                                                            <input type="text" class="form-control" id="minlon" name="minlon" aria-describedby="minlonHelp" placeholder="Default: -180">
                                                            <div id="maxlonHelp" class="form-text">Degrees: -180.0 - 180.0</div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="maxlon" class="form-label">Maximum longitude</label>
                                                            <input type="text" class="form-control" id="maxlon" name="maxlon" aria-describedby="maxlonHelp" placeholder="Default: -180">
                                                            <div id="maxlonHelp" class="form-text">Degrees: -180.0 - 180.0</div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <h6 class="mb-2 ">Circuit constraints</h6>
                                                    <div class="row g-3 darkItem">
                                                        <div class="col-md-3">
                                                            <label for="lat" class="form-label">Latitude</label>
                                                            <input type="text" class="form-control" id="lat" name="lat" aria-describedby="latHelp">
                                                            <div id="latHelp" class="form-text">Degrees: -90.0 - 90.0</div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="lon" class="form-label">Longitude</label>
                                                            <input type="text" class="form-control" id="lon" name="lon" aria-describedby="lonHelp">
                                                            <div id="lonHelp" class="form-text">Degrees: -180.0 - 180.0</div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="minradius" class="form-label">Minimum radius</label>
                                                            <input type="text" class="form-control" id="minradius" name="minradius" placeholder="Default: 0" aria-describedby="minradiusHelp">
                                                            <div id="minradiusHelp" class="form-text">Degrees: 0 - 180.0</div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="maxradius" class="form-label">Maximum radius</label>
                                                            <input type="text" class="form-control" id="maxradius" name="maxradius" aria-described-by="maxradiusHelp" placeholder="Default: 180">
                                                            <div id="maxradiusHelp" class="form-text">Degrees: 0 - 180.0</div>
                                                        </div>
                                                    </div>

                                                </div> <!-- End dropdown -->
                                            </div><!-- End container fluid -->
                                    </div>
                                    <div class="modal-footer darkItem">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close </button>
                                        <button type="submit" class="btn btn-primary" name="submitCustomFilter"> Search </button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div><!-- End filter modal -->

                    </div>
                </div> <!-- End filter card -->


                <?php

                // Fill in sidebar with cards containing earthquake data
                if ($earthquakesArr != null) {
                    foreach ($earthquakesArr as $earthquake) {
                        echo '<div class="card w-100 mt-1 item darkItem border-0" id=' . $earthquake[0] . ' "> <div class="card-body darkItem"> <h6 class="card-title"><span class="badge ' . getColor($earthquake[6])[1] . '">'
                            . $earthquake[6] . '</span>' . "  " . $earthquake[3] . '</h6><p class="card-text"></p> <p class="card-text">' . $earthquake[4] . '</p></div></div>';
                    }
                } else {
                    echo '<div class="card w-100 mt-1 item darkItem border-0" "> <div class="card-body darkItem"> <h6 class="card-title"><span class="badge bg-danger "> Error' .
                        '</span></h6><p class="card-text"></p> <p class="card-text"> There aren\'t any earthquakes in this area. </p></div></div>';
                }


                echo '<div class="card w-100 mt-1 border-0"><div class="card-body darkItem"><h5 class="card-title"><a href="about.html" style="text-decoration: none; color:white;">About</a> </h5><h6 class="card-subtitle mb-2 text-muted">Adnan Smlatić 2021</h6></div></div>';


                ?>

            </div>
        </div><!-- end sidebar -->
        <div class="mapContainer darkItem">
            <div id="mapid"></div>

            <div class="toast-container position-absolute  top-50 end-0 translate-middle-y p-3" id="toastContainer" style="z-index: 1001" aria-live="polite" aria-atomic="true">
            </div>


        </div>
    </div> <!-- end container -->


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.5.0/d3.min.js"></script>

    <!-- jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>


    <script type="text/javascript" src="map.js"></script>
    <script type="text/javascript" src="script.js"></script>
</body>

</html>