// Maps
var lightMap = L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}',{
    minZoom: 2,
    id: 'mapbox/light-v10',
    tileSize: 512,
    zoomOffset: -1, // offsetting tile size
    accessToken: '{{ACCESS TOKEN}}'
});

var satMap = L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}',{
  maxZoom: 18,
  id: 'mapbox/satellite-v9',
  tileSize: 512,
  zoomOffset: -1,
  accessToken: '{{ACCESS TOKEN}}'
});
var darkMap = L. tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}',{
  maxZoom: 18,
  id: 'mapbox/dark-v10',
  tileSize: 512,
  zoomOffset: -1,
  accessToken: '{{ACCESS TOKEN}}'
});

var openMap = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
  maxZoom: 18,
  attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
});
var openMap2 = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
  maxZoom: 18,
  attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
});
var openMap3 = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
  maxZoom: 18,
  attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
});

var faultsURL = "https://raw.githubusercontent.com/fraxen/tectonicplates/master/GeoJSON/PB2002_boundaries.json"
eventid = $.cookie('eventid');

//objects for tectonic fault layer, base map and overlay layers
var faults = new L.layerGroup();
var testimonyGroup = L.layerGroup();
var originGroup = L.layerGroup();

var baseMaps = {
  "Light": lightMap,
  "Dark": darkMap,
  "Satellite": satMap
};
var overlayMaps = {
  "Tectonic plates": faults,
  "Testimonies" : testimonyGroup,
  "Origins" : originGroup
};

//creates map + properties and default
var mymap = L.map("mapEq", {
    center: [earthquake[0], earthquake[1]],
    zoom: 6,
    layers: [darkMap, faults]
}); 

//previous earthquakes in the area
var mapHistory = L.map("mapHistory", {
  center: [earthquake[0], earthquake[1]],
  zoom: 6,
  layers: [openMap]
}); 

//user location map
var mapLocation = L.map("mapLocation", {
  center: [47.5260, 15.2551],
  zoom: 5, 
  layers: [openMap2]
}); 

var mapSeismicity = L.map("mapSeismicity", {
  center: [earthquake[0], earthquake[1]],
  zoom: 6,
  layers: [openMap3]
}); 

var eqSeismicityGroup = L.layerGroup().addTo(mapSeismicity);
var eqHistoryGroup = L.layerGroup().addTo(mapHistory);

// adds tectonic plate data
d3.json(faultsURL, function(plateData) {
  L.geoJson(plateData, {
    color: "orange",
    weight: 2
  })
  .addTo(faults);
});

// Add the layer control to the map
L.control.layers(baseMaps, overlayMaps, {
  collapsed: true
}).addTo(mymap);

L.control.scale({imperial: false}).addTo(mymap);

var markerGroup = L.layerGroup().addTo(mymap);

//custom icon
var epicenterIcon = L.icon({
  iconUrl: 'img/epicenter.svg',
  iconSize:     [38, 100], // size of the icon
  iconAnchor:   [19, 50], // point of the icon which will correspond to marker's location
});

//epicenter
L.marker([earthquake[0], earthquake[1]], {icon: epicenterIcon}).addTo(markerGroup);


//map testimonies
if (testimonies !== false && typeof testimonies !== 'undefined' && testimonies !== null) {

    var testimonies = testimonies.trim().split(/\n/);
    testimonies = testimonies.slice(4);

    for (var testimony of testimonies) {
        testimony = testimony.split(',');
        // testimony.length = testimony.length = 2
        circle = L.circle([testimony[1], testimony[0]], {
            color: '#4DD7FF',
            fillColor: '#4DD7FF',
            fillOpacity: 0.5,
            radius: 500,//(Math.exp(arrMag[1]/1.01-0.13))*1000,
        }).addTo(testimonyGroup);
    }
}


// get user location
function geoFindMe() {


  const status = document.querySelector('#status');
  const mapLink = document.querySelector('#map-link');

  function success(position) {
    const latitude  = position.coords.latitude;
    const longitude = position.coords.longitude;

    status.textContent = '';
    mapLink.textContent = `Latitude: ${latitude} °, Longitude: ${longitude} °`;
 

  }

  function error() {
    status.textContent = 'Unable to retrieve your location';
  }

  if(!navigator.geolocation) {
    status.textContent = 'Geolocation is not supported by your browser';
  } else {
    status.textContent = 'Locating…';
    navigator.geolocation.getCurrentPosition(success, error);

  }

}

var markerIcon = L.icon({
  iconUrl: 'img/marker_minor.png',
  iconSize:     [20 , 20], // size of the icon
  iconAnchor:   [10, 10], // point of the icon which will correspond to marker's location
});

function getColor(mag) {
  return mag >= 8 ? ["#FF0000", "eqGreat"]:
         mag >= 7 ? ["#FF6000", "eqMajor"]:
         mag >= 6 ? ["#FFA000", "eqStrong"]:
         mag >= 5 ? ["#fdc70c", "eqModerate"]:
         mag >= 4 ? ["#b5ab26", "eqLight"]:
         mag >= 2 ? ["#1b8700f1", "eqMinor"]:
                     ["#A2A2A2", "eqMicro"];
  }

setInterval(function () {
  mapHistory.invalidateSize();
  mapSeismicity.invalidateSize();
  mapLocation.invalidateSize();
}, 100);



function uploadToImgur() {
  var UPLOAD_URL = "https://api.imgur.com/3/image.json";
  var CLIENT_ID_HARD = '{{ID}}';
  // Div for image preview.
  let imgPrevDiv = document.querySelector(".img-preview");
  let urlPrev = document.querySelector("#uploadedImgUrl");

  var injectUrl = function () {
    console.log("[+] Success uploading!");
    let res = JSON.parse(this.responseText);
    imgPrevDiv.setAttribute("src", res.data.link);
    urlPrev.value = res.data.link;
  };
  var uploadToImgur = function () {
    if ('files' in this && this.files.length > 0)
      upload(this.files[0], injectUrl);
  };

  /**
   * Uploads a single file to imgur.
   * @param {File}	file 	File to upload.
   */
  var upload = function (file, cb) {
    // empty url prev data
    urlPrev.value = "";
    // Get client id on change
    var CLIENT_ID = CLIENT_ID_HARD || document.querySelector("#client-id").value;

    if (CLIENT_ID.length < 1) {
      throw new Error("I need a client Id!");
    }

    // Form data
    let fd = new FormData();
    fd.append("image", file, file.name);

    // AJAX Request
    let xhr = new XMLHttpRequest();
    xhr.addEventListener("load", cb);
    xhr.open("POST", UPLOAD_URL);
    // Send authentication headers
    xhr.setRequestHeader("Authorization", "Client-ID " + CLIENT_ID);
    // Send form data
    xhr.send(fd);
  };
}


function getSeismicity(latU, lonU) {
  $.getJSON('https://www.seismicportal.eu/fdsnws/event/1/query?format=json&lat='+latU+'&lon='+lonU+'&maxradius=2&limit=500', function (e) {
    $.each(e['features'], function (key, value) {
      //console.log(value['geometry']['coordinates']['0']);
      var lon = value['geometry']['coordinates']['0'];
      var lat = value['geometry']['coordinates']['1'];
      var mag = value['properties']['mag'];
      L.circle([lat, lon], {
        color: getColor(mag)[0],
        fillColor: getColor(mag)[0],
        fillOpacity: 0.7,
        radius: 3000 * mag,
        title: eventid
      }).addTo(eqSeismicityGroup);
    });
  });
}
function getOrigins(eventid) {
  var table = $("#originDataTable");
  $.getJSON('https://www.seismicportal.eu/fdsnws/event/1/query?format=json&includeallorigins=true&eventid=' + eventid, function (e) {
    $.each(e['properties']['origins']['features'], function (key, value) {
      var time = value['properties']['time'];
      var lat = value['properties']['lat'];
      var lon = value['properties']['lon'];
      var depth = value['properties']['depth'];
      var mag = value['properties']['mags']['0']['value'];
      var magT = value['properties']['mags']['0']['type'];
      var auth = value['properties']['auth'];
      var ndef = value['properties']['ndef'];
      var nsta = value['properties']['nsta'];
      var rms = value['properties']['rms'];
      var gap = value['properties']['gap'];
      var mindist = value['properties']['mindist'];
      var maxdist = value['properties']['maxdist'];

      table.append('<tr> <td>'+time+'</td> <td>'+lat+'</td> <td>'+lon+'</td> <td>'+depth+'</td> <td>'+mag+'('+magT+') </td> <td>'+auth+'</td> <td>'+ndef+'</td> <td>'+nsta+'</td> <td>'+rms+'</td> <td>'+gap+'</td> <td>'+mindist+'</td> <td>'+maxdist+'</td> </tr>');
      L.circle([lat, lon], {
        color: 'salmon',
        fillColor: 'salmon',
        fillOpacity: 0.7,
        radius: 3000,
        title: eventid
      }).addTo(originGroup);

   
    });


  });
}

var urlHistory = "https://www.seismicportal.eu/fdsnws/event/1/query?format=json&lat=" + earthquake[0] + "&lon=" + earthquake[1] + "&minmag=5&maxradius=2&limit=10";
var markerStrong = L.icon({
  iconUrl: 'img/marker_strong.png',
  iconSize:     [20 , 20], // size of the icon
  iconAnchor:   [10, 10], // point of the icon which will correspond to marker's location
});
function getEq(url) {

  var historyDiv = $('#historySidebar');
  $.getJSON(url, function (e) {
    $.each(e['features'], function (key, value) {
      //console.log(value['geometry']['coordinates']['0']);
      var lon = value['geometry']['coordinates']['0'];
      var lat = value['geometry']['coordinates']['1'];
      var id = value['id'];
      var mag = value['properties']['mag'];
      var depth = value['properties']['depth'];
      var location = value['properties']['flynn_region'];
      var time = value['properties']['time'];

      var circle = L.marker([lat, lon], {icon: markerStrong, title: eventid}).addTo(eqHistoryGroup);

      historyDiv.append('<div class="card w-100 mt-1 item border-0" id='+id+ '> <div class="card-body darkItem2"> <h6 class="card-title"> <span class="badge bg-danger">'+mag+ '</span> '+location+ '</h6> <p class="card-text"></p> <p class="card-text">'+time+ '</p> </div> </div>');
      circle.bindPopup('<p class="mb-1"><span class="fw-bolder">'+location +'</span><br>Magnitude: '+mag+'<br>Date: '+time+
      '<br>Depth: '+depth+' km <div class="text-center"><a class="btn btn-primary" style="color:white;" role="button" href="earthquake.php?id=' + id + '">Details</a></div></p>');
   
    });
  });
}


getSeismicity(earthquake[0],earthquake[1]);
getOrigins(eventid);
getEq(urlHistory);
