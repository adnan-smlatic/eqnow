<?php

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

session_start();

//date_default_timezone_set("Europe/Paris");
$eventid = $_GET["id"];
setcookie("eventid", $eventid);


//API call urls with ID
$urlEarthquakeId = ("https://www.seismicportal.eu/fdsnws/event/1/query?format=xml&eventid=" . $eventid);
$urlOrigins = "https://www.seismicportal.eu/fdsnws/event/1/query?format=xml&includeallorigins=true&eventid=" . $eventid;
$urlTestimonies = "https://www.seismicportal.eu/testimonies-ws/api/search?format=xml&includeTestimonies=true&eventid=" . $eventid;
$urlMomentTensors = "https://www.seismicportal.eu/mtws/api/search?format=text&downloadAsFile=false&orderby=time-desc&eventid=" . $eventid;

//Earthquake testimonies
function downloadUnzipGetContents($url, $id)
{

  $data = file_get_contents($url);
  $path = tempnam(sys_get_temp_dir(), 'prefix');

  $temp = fopen($path, 'w');
  fwrite($temp, $data);
  fseek($temp, 0);
  fclose($temp);

  $pathExtracted = tempnam(sys_get_temp_dir(), 'prefix');

  $filenameInsideZip = $id . '.txt';

  $success = @copy("zip://" . $path . "#" . $filenameInsideZip, $pathExtracted);
  if (!$success) {
    return false;
  } else {
    $data = file_get_contents($pathExtracted);
    unlink($path);
    unlink($pathExtracted);
    return $data;
  }
};

function getEarthquake($url, $onlyLocation = false)
{
  $xml = new DOMDocument();

  if (@$xml->load($url) === false) {
    return "Error";
  }
  $xml->load($url);
  $items = array();

  if ($onlyLocation) {
    $items = array();
    $events = $xml->getElementsByTagName('event');
    foreach ($events as $event) {
      $lat = $event->getElementsByTagName('latitude')->item(0)->getElementsByTagName('value')->item(0)->nodeValue;
      $lon = $event->getElementsByTagName('longitude')->item(0)->getElementsByTagName('value')->item(0)->nodeValue;
      array_push($items, $lat, $lon);
    }
    return $items;
  }
  $events = $xml->getElementsByTagName('event');
  foreach ($events as $event) {
    $preferredOriginID = $event->getElementsByTagName('preferredOriginID')->item(0)->nodeValue;
    $eventid = explode("/", $preferredOriginID);
    $eventid = $eventid[2];

    $lat = $event->getElementsByTagName('latitude')->item(0)->getElementsByTagName('value')->item(0)->nodeValue;
    $lon = $event->getElementsByTagName('longitude')->item(0)->getElementsByTagName('value')->item(0)->nodeValue;

    $description = $event->getElementsByTagName('description')->item(0);
    $region = $description->getElementsByTagName('text')->item(0)->nodeValue;


    $dateTime = date_create($event->getElementsByTagName('time')->item(0)->getElementsByTagName('value')->item(0)->nodeValue);

    $depth = $event->getElementsByTagName('depth')->item(0)->nodeValue;

    $mag = $event->getElementsByTagName('magnitude')->item(0)->getElementsByTagName('mag')->item(0)->getElementsByTagName('value')->item(0)->nodeValue;
    $magType = $xml->getElementsByTagName('magnitude')->item(0)->getElementsByTagName('type')->item(0)->nodeValue;

    $evaluationMode = $xml->getElementsByTagName('evaluationMode')->item(0)->nodeValue;
    if ($evaluationMode == 'manual') {
      $evaluation = ' The data has been reviewed by a seismologist';
    } else {
      $evaluation = " The data has not been reviewed by a seismologist";
    }

    $author = $event->getElementsByTagName('creationInfo')->item(0)->getElementsByTagName('authorURI')->item(0)->nodeValue;
    $author = explode("/", $author)[2];

    array_push($items, $dateTime, $lat, $lon, $region, $depth, $mag, $magType, $evaluationMode, $evaluation, $author);
  }
  return $items;
}

// get Data for moment tensors
function getMomentTensors($url)
{
  $pieces = explode("|", file_get_contents($url));
  if (array_key_exists('40', $pieces)) {
    $testimonies = array_slice($pieces, 40);
    $testimonies = array_chunk($testimonies, 39);
    return $testimonies;
  } else {
    return null;
  }
}
$details = getEarthquake($urlEarthquakeId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Earthquake details</title>
  <link rel="icon" href="img/icon_eq.png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
  <link rel="stylesheet" href="style.css">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css" defer>
  <scrip src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></scrip>
</head>

<body style="background-color: #212121;">
  <script>
    var testimonies = <?php echo json_encode(downloadUnzipGetContents($urlTestimonies, $eventid));
                      ?>;
    var earthquake = <?php echo json_encode(getEarthquake($urlEarthquakeId, true)); ?>;
  </script>

  <div class="container eqContainer">

    <nav class="navbar navbar-expand-md navbar-dark nvbr darkBackground">

      <a class="navbar-brand" href="index.php"><img src="img/logo_eqv3_light.png" class="d-inline-block align-top" id="logo" alt="" style="width:150px"></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">

          <li class="nav-item">
            <a class="nav-link" href="index.php"><i class="bi bi-house"></i> Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="glossary.html"><i class="bi bi-book"></i> Glossary</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="about.html"><i class="bi bi-info-circle"></i> About</a>
          </li>

        </ul>
      </div>

    </nav>

    <div class="card mb-3 mt-3 darkItem" style="max-width: 100vw;">
      <div class="row g-0">

        <div class="col-md-6">
          <div class="card-body">
            <h5 class="card-title"><?php echo $details[3] ?></h5>
            <table class="table table-borderless">
              <thead>
                <tr>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th scope="row">Magnitude</th>
                  <td><?php echo $details[5] ?> (<?php echo $details[6] ?>)</td>
                </tr>
                <tr>
                  <th scope="row">Date</th>
                  <td><?php echo date_format($details[0], 'j F Y'); ?></td>
                </tr>
                <tr>
                  <th scope="row">Time</th>
                  <td><?php echo date_format($details[0], 'H:i:s'); ?></td>
                </tr>
                <tr>
                  <th scope="row">Latitude</th>
                  <td><?php echo $details[1] ?></td>
                </tr>
                <tr>
                  <th scope="row">Longitude</th>
                  <td><?php echo $details[2] ?></td>
                </tr>
                <tr>
                  <th scope="row">Depth</th>
                  <td><?php echo (float)$details[4] / 1000 ?> km</td>
                </tr>
                <tr>
                  <th scope="row">Evaluation</th>
                  <td><?php echo $details[7] ?><br><small class="card-text text-muted"><?php echo $details[8] ?></small></td>
                </tr>
                <tr>
                  <th scope="row">Author</th>
                  <td><?php echo $details[9] ?></td>
                </tr>
              </tbody>
            </table>

            <div class="container">
              <span class="card-text">Did you feel this earthquake? </span>
              <div class="row my-1">
                <div class="d-grid gap-2 d-md-block">
                  <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalFeltReport">Fill in a questionnaire <i class="bi bi-pencil-square"></i></button>

                  <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalPhoto">Upload a photo <i class="bi bi-card-image"></i></button>

                </div>
              </div>
            </div>
            <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>
          </div>
        </div>

        <div class="col-md-6" id="mapEq"></div>

      </div>
    </div><!--  end card -->

    <!-- Modal felt report -->
    <div class="modal fade" id="modalFeltReport" tabindex="-1" aria-labelledby="modalFeltReportLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header darkItem">
            <h5 class="modal-title" id="modalFeltReportLabel">Felt report</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body darkItem">

            <form action="" method="post" class="">
              <fieldset class="row mb-3">
                <legend class="col-form-label col-sm-2 pt-0">Did you feel it?</legend>
                <div class="col-sm-10">

                  <input class="form-check-input" type="radio" name="feltRadio" id="feltRadio1" value="YES">
                  <label class="form-check-label" for="feltRadio1">Yes </label>

                  <input class="form-check-input" type="radio" name="feltRadio" id="feltRadio2" value="NO">
                  <label class="form-check-label" for="feltRadio2">No </label>
                </div>

              </fieldset>


              <div class="row g-3">
                <!-- <div class="col-md-12">
                  <label for="customRange1" class="form-label">How strong was the felt earthquake?</label>
                  <input type="range" class="form-range" id="customRange1">
                </div> -->

                <div class="col-md-6">
                  <label class=" form-label" for="dateFelt">Date felt</label>
                  <input class="col md-6 form-control" type="date" name="dateFelt" id="dateFelt" min='<?php echo date_format($details[0], 'Y-m-d'); ?>' value="<?php echo date_format($details[0], 'Y-m-d'); ?>">
                </div>
                <div class="col-md-6">
                  <label class=" form-label" for="timeFelt">Time felt</label>
                  <input class="col md-6 form-control" type="time" name="timeFelt" id="timeFelt" value="<?php echo date('H:i'); ?>">
                </div>
              </div>
              <div class="row mt-3 ">
                <div class="input-group mb-3">

                  <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMap" aria-expanded="false" aria-controls="collapseMap">
                    Enter from map
                  </button>

                  <button type="button" class="btn btn-outline-secondary" id="find-me" onclick="geoFindMe()">Get my location</button>

                  <input type="text" class="form-control" id="location" name="location">

                </div>
              </div>

              <div class="collapse" id="collapseMap">
                <div class="card card-body mb-5 darkItem">
                  <p id="status"></p>
                  <a id="map-link" target="_blank"></a>
                  <div class="col-md-12" id="mapLocation"></div>
                </div>
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Comment</label>
                <textarea id="message" name="message" class="form-control" aria-label="With textarea"></textarea>

              </div>
              <p class="mt-3">
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContact" aria-expanded="false" aria-controls="collapseContact">
                  Optional contact information
                </button>
              </p>

              <div class="collapse" id="collapseContact">
                <div class="card card-body mb-5 darkItem">
                  <h6 class="card-subtitle mb-2 text-muted p-2">This information is not public and will only be used to contact you if we need your help with the data.</h6>
                  <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" aria-describedby="nameHelp">
                    <div id="nameHelp" class="form-text">Napoleon Bonaparate</div>
                  </div>

                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email" aria-describedby="emailHelp">
                    <div id="emailHelp" class="form-text">example@earthquakenow.cf</div>
                  </div>

                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone number</label>
                    <input type="text" class="form-control" id="phone" name="phone" aria-describedby="phoneHelp">
                    <div id="phoneHelp" class="form-text">Please include the country code</div>
                  </div>
                </div>
              </div>

          </div>
          <div class="modal-footer darkItem">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="submitTestimony">Submit report</button>
          </div>
          </form>
        </div>
      </div>
    </div><!-- end Modal felt report -->


    <!-- Modal image upload -->
    <div class="modal fade" id="modalPhoto" tabindex="-1" aria-labelledby="modalPhotoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header darkItem">
            <h5 class="modal-title" id="modalPhotoLabel">Upload a photo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body darkItem">

            <form action="" method="post" class="">

              <div class="row g-3">

                <div class="col-md-6">
                  <!-- Image Preview -->
                  <img src="img/placeholder.png" height="300px" width="auto" class="img-preview">


                  <!-- Browse Files button -->
                  <p>
                    <input type="file" class="profile-pic">
                  </p>
                  <p class="card-text text-center">This feature is currently disabled. </p>

                  <!-- URL -->
                  <!-- <p>
                    Result image url: <input type="text" id="uploadedImgUrl">
                  </p> -->
                </div>

              </div>

          </div>
          <div class="modal-footer darkItem">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="This feature is currently disabled.">
              <button type="submit" class="btn btn-primary disabled" name="submitTestimony">Upload photo</button>
            </span>
          </div>
          </form>
        </div>
      </div>
    </div><!-- end Modal image upload -->

    <!-- Pills-->
    <div class="card mb-3 mt-3 darkItem" style="max-width: 100vw;">
      <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="pills-scientific-tab" data-bs-toggle="pill" data-bs-target="#pills-scientific" type="button" role="tab" aria-controls="pills-scientific" aria-selected="false">Scientific data </button>
        </li>
        <!-- <li class="nav-item" role="presentation">
          <button class="nav-link " id="pills-maps-tab" data-bs-toggle="pill" data-bs-target="#pills-maps" type="button" role="tab" aria-controls="pills-maps" aria-selected="true">Maps</button>
        </li> -->
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pills-testimonies-tab" data-bs-toggle="pill" data-bs-target="#pills-testimonies" type="button" role="tab" aria-controls="pills-testimonies" aria-selected="false">Testimonies</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pills-Photos-tab" data-bs-toggle="pill" data-bs-target="#pills-Photos" type="button" role="tab" aria-controls="pills-Photos" aria-selected="false">Photos</button>
        </li>

        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pills-History-tab" data-bs-toggle="pill" data-bs-target="#pills-History" type="button" role="tab" aria-controls="pills-History" aria-selected="false">History </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pills-Seismicity-tab" data-bs-toggle="pill" data-bs-target="#pills-Seismicity" type="button" role="tab" aria-controls="pills-Seismicity" aria-selected="false">Seismicity </button>
        </li>
      </ul>

      <div class="tab-content darkItem" id="pills-tabContent">

        <div class="tab-pane fade darkItem" id="pills-testimonies" role="tabpanel" aria-labelledby="pills-testimonies-tab">
          <p class="card-text px-3 mb-0">Your observations can help improve earthquake response and also contribute to scientific knowledge.
            Observations (including questionnaires, pictures, etc.) can provide valuable in-situ constraints on earthquake effects and, in the long term, improve the performance of our models of earthquake impact.<br><br>
            <div class="text-center"><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFeltReport"> Submit a felt report</button></div><br>
          </p>


        </div> <!-- end row -->
        <div class="tab-pane fade darkItem show active" id="pills-scientific" role="tabpanel" aria-labelledby="pills-scientific-tab">
          <h4 class="p-2"> Event origins </h4>
          <div class="table-responsive">
            <table class="table table-dark table-striped table-bordered" id="originDataTable">
              <tr>
                <td>Date</td>
                <td>Latitude</td>
                <td>Longitude</td>
                <td>Depth</td>
                <td>Magnitude</td>
                <td>Author</td>
                <td> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Used Phase Count">pC <i class="bi bi-info-circle-fill"></i></span></td>
                <td> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Used Station Count">uSC <i class="bi bi-info-circle-fill"></i></span> </td>
                <td> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Standard Error">sE <i class="bi bi-info-circle-fill"></i></span> </td>
                <td> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Azimuthal Gap">aG <i class="bi bi-info-circle-fill"></i></span> </td>
                <td> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Minimum Distance">md <i class="bi bi-info-circle-fill"></i></span> </td>
                <td> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Maximum Distance">MD <i class="bi bi-info-circle-fill"></i></span> </td>
              </tr>
            </table>
          </div>
          <br> <br>

          <h4 class="p-2">Moment tensors</h4>

          <div class="table-responsive">
            <table class="table table-dark table-striped table-bordered">

              <thead>
                <tr>
                  <th></th>

                  <th colspan="5">Centroid</th>
                  <th colspan="3">Nodal Plan 1</th>
                  <th colspan="3">Nodal Plan 2</th>
                  <th colspan="6">Tensor</th>
                  <th colspan="3">Moment Inf.</th>
                </tr>
                <tr>
                  <th>Auth</th>


                  <th>Datetime</th>
                  <th>Lat</th>
                  <th>Lon</th>
                  <th>Depth</th>
                  <th>Mw</th>

                  <th>Strike</th>
                  <th>Dip</th>
                  <th>Rake</th>

                  <th>Strike</th>
                  <th>Dip</th>
                  <th>Rake</th>

                  <th>Mrr</th>
                  <th>Mtt</th>
                  <th>Mpp</th>
                  <th>Mrt</th>
                  <th>Mrp</th>
                  <th>Mtp</th>

                  <th>%DC</th>
                  <th>%ISO</th>
                  <th>%CLVD</th>

                </tr>
              </thead>
              <tbody>
                <?php
                $data = getMomentTensors($urlMomentTensors);
                if (isset($data)) {
                  foreach ($data as $row) {
                ?>
                    <tr ng-repeat="mt in mts" class="ng-scope">
                      <td><?php echo $row[7]; ?></td>

                      <td><?php echo $row[1]; ?></td>
                      <td><?php echo $row[2] ?></td>
                      <td><?php echo $row[3] ?></td>
                      <td><?php echo $row[6] ?></td>
                      <td><?php echo $row[5] ?></td>

                      <td><?php echo $row[14]; ?></td>
                      <td><?php echo $row[15]; ?></td>
                      <td><?php echo $row[16]; ?></td>
                      <td><?php echo $row[17]; ?></td>
                      <td><?php echo $row[18]; ?></td>
                      <td><?php echo $row[19]; ?></td>

                      <td><?php echo $row[29]; ?></td>
                      <td><?php echo $row[30]; ?></td>
                      <td><?php echo $row[31]; ?></td>
                      <td><?php echo $row[32]; ?></td>
                      <td><?php echo $row[33]; ?></td>
                      <td><?php echo $row[34]; ?></td>
                      <td><?php echo $row[35]; ?></td>
                      <td><?php echo $row[37]; ?></td>
                      <td><?php echo $row[36]; ?></td>




                    </tr>
                <?php }
                } ?>
              </tbody>
              <tfoot>
                <tr>
                  <th>Auth</th>


                  <th>Datetime UTC</th>
                  <th>lat</th>
                  <th>lon</th>
                  <th>Depth</th>
                  <th>Mw</th>

                  <th>Strike</th>
                  <th>Dip</th>
                  <th>Rake</th>

                  <th>Strike</th>
                  <th>Dip</th>
                  <th>Rake</th>

                  <th>Mrr</th>
                  <th>Mtt</th>
                  <th>Mpp</th>
                  <th>Mrt</th>
                  <th>Mrp</th>
                  <th>Mtp</th>

                  <th>%DC</th>
                  <th>%ISO</th>
                  <th>%CLVD</th>





                </tr>
              </tfoot>
            </table>
          </div>
        </div><!-- end row -->

        <div class="tab-pane fade darkItem" id="pills-Photos" role="tabpanel" aria-labelledby="pills-Photos-tab">
          <br><br><br>
          <div class="text-center">
            <h6 class="py-3"> There aren't any photos for this earthquake. </h6>
          </div>
          <br><br><br>

        </div><!-- end row -->
        <div class="tab-pane fade darkItem" id="pills-History" role="tabpanel" aria-labelledby="pills-History-tab">
          <h5 class="p-3">Previous significant earthquakes in the area </h5>


          <div class="row g-0">

            <div class="col-md-4 mb-0 pb-0" id="historySidebar">



            </div>

            <div class="col-md-8 mt-1" id="mapHistory"></div>

          </div>

        </div><!-- end row -->
        <div class="tab-pane fade darkItem" id="pills-Seismicity" role="tabpanel" aria-labelledby="pills-Seismicity-tab">
          <h5 class="p-3">Seismicity in the area </h5>


          <div class="row g-0">

            <div class="col-md-12 mt-1" id="mapSeismicity"></div>

          </div>

        </div><!-- end row -->
      </div><!-- end tab content -->

    </div><!-- end pill content-->
  </div> <!-- end pill card -->

  <footer class="footer">
    <div class="container">
      <span class="text-muted"></span>
    </div>
  </footer>


  </div><!--  end container -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.min.js"></script>
  <!-- jquery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
  <script type="text/javascript" src="earthquake.js" defer></script>
  <script type="text/javascript" src="script.js"></script>
  <?php
  $mtime = microtime();
  $mtime = explode(" ", $mtime);
  $mtime = $mtime[1] + $mtime[0];
  $endtime = $mtime;
  $totaltime = ($endtime - $starttime);
  echo "This page was created in " . $totaltime . " seconds";; ?>
</body>

</html>
